<?php
// db-check.php — one-shot diagnostic for the UserRequirements storage.
//
// Open https://appmentech.in/db-check.php in a browser, read the report, then
// DELETE THIS FILE. It prints no passwords, but it does confirm which database
// the site talks to, so it should not stay on a live server.

header('Content-Type: text/plain; charset=utf-8');

function line($label, $value) {
    echo str_pad($label, 26) . $value . "\n";
}

echo "Appmentech — database check\n";
echo str_repeat('=', 52) . "\n\n";

line('PHP version', PHP_VERSION);
line('PDO drivers', implode(', ', PDO::getAvailableDrivers()) ?: '(none)');

$configPath = __DIR__ . '/config.php';
if (!is_file($configPath)) {
    echo "\nFAIL  config.php is missing next to this file.\n";
    echo "      Copy config.sample.php to config.php and fill it in.\n";
    exit;
}
$cfg = require $configPath;
line('config.php', 'found');

if (empty($cfg['db'])) {
    echo "\nFAIL  config.php has no 'db' section. Copy it from config.sample.php.\n";
    exit;
}
$db = $cfg['db'];
line('db.host', $db['host'] !== '' ? $db['host'] : '(empty)');
line('db.port', isset($db['port']) ? $db['port'] : 3306);
line('db.name', $db['name'] !== '' ? $db['name'] : '(empty)');
line('db.user', $db['user'] !== '' ? $db['user'] : '(empty)');
line('db.password', $db['password'] !== '' ? '(set, ' . strlen($db['password']) . ' chars)' : '(EMPTY)');

if ($db['host'] === '' || $db['name'] === '') {
    echo "\nFAIL  host or name is blank, so submit.php skips the database entirely.\n";
    echo "      This is the silent-skip case: the email sends, nothing is stored.\n";
    exit;
}

require_once __DIR__ . '/lib/requirements.php';

echo "\n-- connection --\n";
try {
    $pdo = db_connect($cfg);
    if (!$pdo) {
        echo "FAIL  db_connect() returned null (host or name blank).\n";
        exit;
    }
    line('connect', 'OK');
    line('server version', $pdo->getAttribute(PDO::ATTR_SERVER_VERSION));
    line('current database', (string) $pdo->query('SELECT DATABASE()')->fetchColumn());
} catch (Throwable $e) {
    echo 'FAIL  ' . get_class($e) . ': ' . $e->getMessage() . "\n";
    exit;
}

echo "\n-- tables --\n";
foreach (['UserRequirements', 'UserRequirementsAudit'] as $table) {
    try {
        $count = $pdo->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
        line($table, 'present, ' . $count . ' row(s)');
    } catch (Throwable $e) {
        line($table, 'MISSING — ' . $e->getMessage());
    }
}

echo "\n-- test write (rolled back, nothing is kept) --\n";
try {
    $pdo->beginTransaction();
    $row = [
        'UserRequirementId'  => guid_v4(),
        'Name'               => 'db-check probe',
        'Company'            => null,
        'Email'              => 'probe@example.com',
        'Phone'              => '+91 0000000000',
        'PhoneCountryCode'   => '+91',
        'PhoneNumber'        => '0000000000',
        'PhoneNormalized'    => normalize_phone('+91 0000000000'),
        'Industry'           => null,
        'SolutionRequired'   => null,
        'EstimatedBudget'    => null,
        'ProjectTimeline'    => null,
        'ProjectDescription' => 'probe',
        'RequirementDetails' => json_encode(['probe' => true]),
        'ContactKey'         => contact_key('db-check probe', 'probe@example.com', '+91 0000000000'),
        'AttachmentId'       => null,
        'AttachmentFileName' => null,
        'AttachmentPath'     => null,
        'AttachmentStatus'   => null,
    ];
    $cols = array_merge(['UserRequirementId'], requirement_columns());
    $sql = 'INSERT INTO UserRequirements (' . implode(', ', $cols) . ') VALUES ('
         . implode(', ', array_map(function ($c) { return ':' . $c; }, $cols)) . ')';
    $params = [];
    foreach ($cols as $c) {
        $params[':' . $c] = $row[$c];
    }
    $pdo->prepare($sql)->execute($params);
    $pdo->rollBack();
    echo "OK    insert succeeded and was rolled back.\n";
    echo "\nStorage is working. If submissions still are not appearing, check the\n";
    echo "PHP error log for lines starting 'contact submit:'.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo 'FAIL  ' . get_class($e) . ': ' . $e->getMessage() . "\n";
    echo "\nThat message is the reason rows are not being written.\n";
}

echo "\nDelete db-check.php now that you have the report.\n";
