<?php
// lib/requirements.php — persistence for contact-form submissions.
//
// One active row per contact (normalized name + email + phone). A repeat
// submission from the same contact copies the current row into the audit
// table and then updates the active row in place, inside one transaction.

function guid_v4() {
    $b = random_bytes(16);
    $b[6] = chr((ord($b[6]) & 0x0f) | 0x40); // version 4
    $b[8] = chr((ord($b[8]) & 0x3f) | 0x80); // variant 10
    $hex = bin2hex($b);
    return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4)
         . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20, 12);
}

function normalize_name($v) {
    return preg_replace('/\s+/', ' ', mb_strtolower(trim((string) $v), 'UTF-8'));
}

function normalize_email($v) {
    return mb_strtolower(trim((string) $v), 'UTF-8');
}

/**
 * Digits only, reduced to the trailing ten, so that "+91 98765 43210",
 * "09876543210" and "98765 43210" all compare equal. Without the trim to ten
 * the same person switching between the international and national form of
 * their number would be treated as a new contact.
 */
function normalize_phone($v) {
    $digits = preg_replace('/\D+/', '', (string) $v);
    if ($digits === '') {
        return '';
    }
    $digits = ltrim($digits, '0');
    return strlen($digits) > 10 ? substr($digits, -10) : $digits;
}

function contact_key($name, $email, $phone) {
    return hash('sha256', normalize_name($name) . '|' . normalize_email($email) . '|' . normalize_phone($phone));
}

/**
 * @return PDO|null null when the database is not configured; callers treat a
 *                  missing database as non-fatal so the email still goes out.
 */
function db_connect(array $cfg) {
    if (empty($cfg['db']) || empty($cfg['db']['host']) || empty($cfg['db']['name'])) {
        return null;
    }
    $db = $cfg['db'];
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $db['host'],
        isset($db['port']) ? (int) $db['port'] : 3306,
        $db['name']
    );
    return new PDO($dsn, $db['user'], $db['password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}

/** Columns shared by the active table and its audit copy, in one order. */
function requirement_columns() {
    return [
        'Name', 'Company', 'Email', 'Phone', 'PhoneCountryCode', 'PhoneNumber',
        'PhoneNormalized', 'Industry', 'SolutionRequired', 'EstimatedBudget',
        'ProjectTimeline', 'ProjectDescription', 'RequirementDetails', 'ContactKey',
        'AttachmentId', 'AttachmentFileName', 'AttachmentPath', 'AttachmentStatus',
    ];
}

/**
 * Insert a new requirement or update the existing one for this contact,
 * auditing the previous version first.
 *
 * @param array $row  Column => value, without UserRequirementId.
 * @return array{id:string,mode:string}
 */
function save_requirement(PDO $pdo, array $row) {
    $pdo->beginTransaction();
    try {
        $find = $pdo->prepare('SELECT * FROM UserRequirements WHERE ContactKey = :key FOR UPDATE');
        $find->execute([':key' => $row['ContactKey']]);
        $existing = $find->fetch();

        if ($existing) {
            archive_requirement($pdo, $existing);

            $sets = [];
            foreach (requirement_columns() as $col) {
                $sets[] = $col . ' = :' . $col;
            }
            $sets[] = 'ModifiedDate = UTC_TIMESTAMP(6)';
            $sql = 'UPDATE UserRequirements SET ' . implode(', ', $sets)
                 . ' WHERE UserRequirementId = :id';

            $params = [':id' => $existing['UserRequirementId']];
            foreach (requirement_columns() as $col) {
                $params[':' . $col] = isset($row[$col]) ? $row[$col] : null;
            }
            $pdo->prepare($sql)->execute($params);
            $pdo->commit();
            return ['id' => $existing['UserRequirementId'], 'mode' => 'updated'];
        }

        $id = isset($row['UserRequirementId']) ? $row['UserRequirementId'] : guid_v4();
        $cols = array_merge(['UserRequirementId'], requirement_columns());
        $sql = 'INSERT INTO UserRequirements (' . implode(', ', $cols) . ') VALUES ('
             . implode(', ', array_map(function ($c) { return ':' . $c; }, $cols)) . ')';

        $params = [':UserRequirementId' => $id];
        foreach (requirement_columns() as $col) {
            $params[':' . $col] = isset($row[$col]) ? $row[$col] : null;
        }
        $pdo->prepare($sql)->execute($params);
        $pdo->commit();
        return ['id' => $id, 'mode' => 'inserted'];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function archive_requirement(PDO $pdo, array $existing) {
    $cols = array_merge(['UserRequirementId'], requirement_columns(), ['CreatedDate', 'ModifiedDate']);
    $sql = 'INSERT INTO UserRequirementsAudit (' . implode(', ', $cols) . ') VALUES ('
         . implode(', ', array_map(function ($c) { return ':' . $c; }, $cols)) . ')';
    $params = [];
    foreach ($cols as $col) {
        $params[':' . $col] = isset($existing[$col]) ? $existing[$col] : null;
    }
    $pdo->prepare($sql)->execute($params);
}

/** Record the outcome of the OneDrive upload once it is known. */
function update_attachment_status(PDO $pdo, $id, $status, $path = null) {
    $pdo->prepare(
        'UPDATE UserRequirements
            SET AttachmentStatus = :status,
                AttachmentPath = COALESCE(:path, AttachmentPath),
                ModifiedDate = UTC_TIMESTAMP(6)
          WHERE UserRequirementId = :id'
    )->execute([':status' => $status, ':path' => $path, ':id' => $id]);
}
