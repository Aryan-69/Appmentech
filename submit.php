<?php
// submit.php — receives the contact form POST: validates it, stores the
// requirement, uploads any attachment to Google Drive, and notifies the team
// over Hostinger SMTP.
// Credentials live in config.php (gitignored). Copy config.sample.php to config.php first.

header('Content-Type: application/json; charset=utf-8');

function fail($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Method not allowed.', 405);
}

$configPath = __DIR__ . '/config.php';
if (!is_file($configPath)) {
    fail('Server email is not configured yet.', 500);
}
$cfg = require $configPath;

require_once __DIR__ . '/lib/requirements.php';
require_once __DIR__ . '/lib/googledrive.php';

const MAX_ATTACHMENT_BYTES = 10 * 1024 * 1024;

// ---- collect + sanitize input ----
function field($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}
// strip CR/LF from anything that goes into a header (prevents header injection)
function oneLine($v) {
    return str_replace(["\r", "\n"], ' ', $v);
}

$name         = field('name');
$company      = field('company');
$email        = field('email');
$phone        = field('phone');
$phoneCountry = field('phone_country');
$phoneCode    = field('phone_country_code');
$phoneNumber  = field('phone_number');
$industry     = field('industry');
$solution     = field('solution');
$currency     = field('currency');
$budgetAmount = field('budget_amount');
$budgetGuide  = field('budget_guidance') !== '';
$timeline     = field('timeline');
$bestLocal    = field('best_time_local');
$bestTz       = field('best_time_timezone');
$bestUtc      = field('best_time_utc');
$description  = field('description');

if ($phone === '' && ($phoneCode !== '' || $phoneNumber !== '')) {
    $phone = trim($phoneCode . ' ' . $phoneNumber);
}
$budget = $budgetGuide
    ? 'Not sure — would like guidance'
    : ($budgetAmount !== '' ? trim($currency . ' ' . $budgetAmount) : '');

if ($name === '' || $email === '' || $description === '') {
    fail('Name, Email, and Project Description are required.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !valid_email_domain($email)) {
    fail('Please provide a valid email address, including a real domain ending such as .com or .in.');
}
$phoneDigits = preg_replace('/\D+/', '', $phone);
if (strlen($phoneDigits) < 6 || strlen($phoneDigits) > 15) {
    fail('Please provide a valid phone number (6-15 digits).');
}

// ---- attachment: extension, MIME, size, then an optional malware scan ----
$attachment = null;   // ['tmp','name','mime','size','id']
$uploadError = null;

if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['attachment'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        fail($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE
            ? 'Maximum file size is 10 MB.'
            : 'Attachment upload failed. Please try again.');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        fail('Attachment upload failed. Please try again.');
    }
    if ($file['size'] > MAX_ATTACHMENT_BYTES) {
        fail('Maximum file size is 10 MB.');
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = allowed_attachment_types();
    if (!isset($allowed[$extension])) {
        fail('File type not allowed. Use PDF, PNG, JPG, JPEG, DOCX, XLSX, PPTX or TXT.');
    }

    $detected = detect_mime($file['tmp_name']);
    if ($detected !== null && !in_array($detected, $allowed[$extension], true)) {
        fail('The file contents do not match its extension. Please upload a valid ' . strtoupper($extension) . ' file.');
    }

    list($clean, $scanDetail) = scan_for_malware($cfg, $file['tmp_name']);
    if (!$clean) {
        error_log('contact submit: attachment rejected by scanner: ' . $scanDetail);
        fail('The attachment did not pass our security scan. Please send it to ' . $cfg['to'] . ' instead.');
    }

    $attachment = [
        'tmp'  => $file['tmp_name'],
        'name' => safe_file_name($file['name']),
        'mime' => $detected !== null ? $detected : $allowed[$extension][0],
        'size' => (int) $file['size'],
    ];
}

// ---- persist: one active row per contact, previous version audited --------
$requirementId = guid_v4();
$attachmentStatus = $attachment ? 'Pending' : null;
$pdo = null;

// The attachment id is the requirement id, so the Drive folder and the
// json record line up: /UserRequirements/{UserRequirementId}/{filename}
$details = [
    'userRequirementId' => $requirementId,
    'name'              => $name,
    'company'           => $company,
    'email'             => $email,
    'phone'             => $phone,
    'phoneCountry'      => $phoneCountry,
    'phoneCountryCode'  => $phoneCode,
    'phoneNumber'       => $phoneNumber,
    'industry'          => $industry,
    'solutionRequired'  => $solution,
    'estimatedBudget'   => $budget,
    'budgetCurrency'    => $budgetGuide ? null : $currency,
    'budgetAmount'      => $budgetGuide ? null : $budgetAmount,
    'budgetGuidance'    => $budgetGuide,
    'projectTimeline'   => $timeline,
    'bestTimeToContact' => [
        'local'    => $bestLocal,
        'timezone' => $bestTz,
        'utc'      => $bestUtc,
    ],
    'projectDescription' => $description,
    'attachment'        => $attachment ? [
        'attachmentId' => $requirementId,
        'fileName'     => $attachment['name'],
        'mimeType'     => $attachment['mime'],
        'sizeBytes'    => $attachment['size'],
        'path'         => '/UserRequirements/' . $requirementId . '/' . $attachment['name'],
        'status'       => 'Pending',
    ] : null,
    'submittedAtUtc'    => gmdate('c'),
];

// Whether storage was even asked for, so a silent skip can be told apart
// from a genuine failure in the response and in the notification email.
$dbConfigured = !empty($cfg['db']) && !empty($cfg['db']['host']) && !empty($cfg['db']['name']);
$storageStatus = $dbConfigured ? 'Pending' : 'Not configured';
$storageDetail = null;

try {
    $pdo = db_connect($cfg);
} catch (Throwable $e) {
    error_log('contact submit: database connection failed: ' . $e->getMessage());
    $storageStatus = 'Failed';
    $storageDetail = 'connection: ' . $e->getMessage();
    $pdo = null;
}

if ($pdo) {
    try {
        $row = [
            'UserRequirementId'  => $requirementId,
            'Name'               => $name,
            'Company'            => $company !== '' ? $company : null,
            'Email'              => $email,
            'Phone'              => $phone !== '' ? $phone : null,
            'PhoneCountryCode'   => $phoneCode !== '' ? $phoneCode : null,
            'PhoneNumber'        => $phoneNumber !== '' ? $phoneNumber : null,
            'PhoneNormalized'    => normalize_phone($phone),
            'Industry'           => $industry !== '' ? $industry : null,
            'SolutionRequired'   => $solution !== '' ? $solution : null,
            'EstimatedBudget'    => $budget !== '' ? $budget : null,
            'ProjectTimeline'    => $timeline !== '' ? $timeline : null,
            'ProjectDescription' => $description,
            'RequirementDetails' => json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'ContactKey'         => contact_key($name, $email, $phone),
            'AttachmentId'       => $attachment ? $requirementId : null,
            'AttachmentFileName' => $attachment ? $attachment['name'] : null,
            'AttachmentPath'     => $attachment ? $details['attachment']['path'] : null,
            'AttachmentStatus'   => $attachmentStatus,
        ];
        $saved = save_requirement($pdo, $row);
        $requirementId = $saved['id'];
        $storageStatus = $saved['mode'] === 'updated' ? 'Updated' : 'Inserted';
    } catch (Throwable $e) {
        // Storage problems must not lose the enquiry — the email still goes out.
        error_log('contact submit: could not save requirement: ' . $e->getMessage());
        $storageStatus = 'Failed';
        $storageDetail = $e->getMessage();
        $pdo = null;
    }
}

// ---- attachment -> Google Drive --------------------------------------------
$attachmentWarning = null;

if ($attachment) {
    if (google_drive_configured($cfg)) {
        list($uploaded, $detail) = google_drive_upload(
            $cfg, $requirementId, $attachment['tmp'], $attachment['name'], $attachment['mime']
        );
        $attachmentStatus = $uploaded ? 'Uploaded' : 'Failed';
        if ($uploaded) {
            $details['attachment']['path'] = $detail;
        } else {
            error_log('contact submit: Google Drive upload failed: ' . $detail);
            $attachmentWarning = 'Your attachment failed to send — please resend it, or email it directly to ' . $cfg['to'] . '.';
        }
    } else {
        // No cloud storage wired up yet: the file rides along on the email.
        $attachmentStatus = 'Pending';
        $attachmentWarning = 'Google Drive is not configured yet, so your attachment was emailed to '
            . $cfg['to'] . ' instead of being filed.';
    }
    $details['attachment']['status'] = $attachmentStatus;

    if ($pdo) {
        try {
            update_attachment_status($pdo, $requirementId, $attachmentStatus, $details['attachment']['path']);
        } catch (Exception $e) {
            error_log('contact submit: could not update attachment status: ' . $e->getMessage());
        }
    }
}

// ---- notify ----------------------------------------------------------------
$subject = 'Project Requirement from ' . oneLine($name);
$bodyLines = [
    'Name: ' . $name,
    'Company: ' . $company,
    'Email: ' . $email,
    'Phone: ' . $phone,
    'Industry: ' . $industry,
    'Solution Required: ' . $solution,
    'Estimated Budget: ' . $budget,
    'Project Timeline: ' . $timeline,
    'Best Time to Contact: ' . ($bestLocal !== '' ? $bestLocal . ' (' . $bestTz . ') = ' . $bestUtc : 'No preference'),
    'Requirement ID: ' . $requirementId,
];
if ($attachment) {
    $bodyLines[] = 'Attachment: ' . $attachment['name'] . ' (' . $attachmentStatus . ')';
    $bodyLines[] = 'Attachment path: ' . $details['attachment']['path'];
}
$bodyLines[] = 'Storage: ' . $storageStatus . ($storageDetail !== null ? ' — ' . $storageDetail : '');
$bodyLines[] = '';
$bodyLines[] = 'Project Description:';
$bodyLines[] = $description;
$body = implode("\r\n", $bodyLines);

// 1) Notify the team. Reply-To is the visitor so a reply reaches them.
$mailAttachments = $attachment ? [[
    'path' => $attachment['tmp'],
    'name' => $attachment['name'],
    'mime' => $attachment['mime'],
]] : [];

list($ok, $detail) = smtp_send(
    $cfg, $cfg['to'], $subject, $body, $email, oneLine($name), null, $mailAttachments
);

if (!$ok) {
    error_log('contact submit SMTP error (notify): ' . $detail);
    fail('Email could not be sent. Please try again later.', 502);
}

// 2) Auto-reply to the customer. Best-effort — never fail the request if this part errors.
$replySubject = 'We received your message — Appmentech Technologies';
$replyBody = auto_reply_body($name);
$replyHtml = auto_reply_html($name);
list($ackOk, $ackDetail) = smtp_send($cfg, $email, $replySubject, $replyBody, $cfg['to'], 'Appmentech Technologies', $replyHtml);
if (!$ackOk) {
    error_log('contact submit SMTP error (auto-reply): ' . $ackDetail);
}

$response = ['ok' => true, 'requirement_id' => $requirementId, 'storage' => $storageStatus];
if ($attachmentWarning !== null) {
    $response['attachment_warning'] = $attachmentWarning;
}
if ($storageStatus === 'Failed') {
    $response['storage_warning'] = 'Your enquiry reached us by email, but it could not be saved to the database.';
}
echo json_encode($response);

// ---- validation helpers ----------------------------------------------------

/** Extension => the MIME types finfo may legitimately report for it. */
function allowed_attachment_types() {
    return [
        'pdf'  => ['application/pdf'],
        'png'  => ['image/png'],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'docx' => [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
        ],
        'xlsx' => [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
        ],
        'pptx' => [
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/zip',
        ],
        'txt'  => ['text/plain', 'text/csv', 'application/csv'],
    ];
}

function detect_mime($path) {
    if (!function_exists('finfo_open')) {
        return null; // cannot check on this host; extension check still applies
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) {
        return null;
    }
    $mime = finfo_file($finfo, $path);
    finfo_close($finfo);
    return $mime === false ? null : $mime;
}

/** Keep the visitor's filename recognisable but safe as a path segment. */
function safe_file_name($name) {
    $base = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($name));
    $base = ltrim($base, '.');
    if ($base === '') {
        $base = 'attachment';
    }
    return strlen($base) > 180 ? substr($base, -180) : $base;
}

/** Require a domain with a plausible alphabetic TLD (.com, .in, .tech ...). */
function valid_email_domain($email) {
    $domain = substr(strrchr($email, '@'), 1);
    if ($domain === false || $domain === '') {
        return false;
    }
    return (bool) preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)*\.[a-z]{2,}$/i', $domain);
}

/**
 * Scan the upload when a scanner is configured (config.php: 'clamscan' =>
 * '/usr/bin/clamdscan'). With no scanner available the file is accepted on the
 * strength of the extension and MIME checks above.
 *
 * @return array{0:bool,1:string}
 */
function scan_for_malware(array $cfg, $path) {
    $binary = isset($cfg['clamscan']) ? trim((string) $cfg['clamscan']) : '';
    if ($binary === '' || !function_exists('exec') || !is_executable($binary)) {
        return [true, 'no scanner configured'];
    }
    $output = [];
    $status = 1;
    exec(escapeshellcmd($binary) . ' --no-summary ' . escapeshellarg($path) . ' 2>&1', $output, $status);
    if ($status === 0) {
        return [true, 'clean'];
    }
    return [false, 'exit ' . $status . ': ' . implode(' ', $output)];
}

function auto_reply_body($name) {
    $first = trim($name) !== '' ? ' ' . $name : '';
    return
        "Hi" . $first . ",\r\n\r\n" .
        "Thank you for reaching out to Appmentech Technologies. We have received your " .
        "project requirement and a member of our team will get back to you within 1 business day.\r\n\r\n" .
        "If your request is urgent, you can reply directly to this email or call us at +91 73030 21135.\r\n\r\n" .
        "Warm regards,\r\n" .
        "Appmentech Technologies\r\n" .
        "Web | Mobile | AI | SaaS | Cloud | Automation | Enterprise | Quality Engineering\r\n" .
        "https://appmentech.in\r\n\r\n" .
        "---\r\n" .
        "This is an automated confirmation. Please do not share sensitive information by email.";
}

function auto_reply_html($name) {
    $safeName = htmlspecialchars(trim($name), ENT_QUOTES, 'UTF-8');
    $greetName = $safeName !== '' ? ' ' . $safeName : '';

    // Table layout with inline styles: the only thing every mail client agrees
    // on. Colours are the site tokens — dark #0b0e17, amber #f2a13c, body
    // #c3cbdd — and the mark is loaded from the site so a blocked-image client
    // still shows the wordmark beside it.
    return
'<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="x-apple-disable-message-reformatting">
<title>We received your message</title>
</head>
<body style="margin:0;padding:0;background:#f6f7fa;font-family:\'Segoe UI\',-apple-system,Roboto,Helvetica,Arial,sans-serif;color:#3f4757;">
  <div style="display:none;max-height:0;overflow:hidden;opacity:0;">Your project requirement reached us. A member of the team replies within 1 business day.</div>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f6f7fa;padding:28px 12px;">
    <tr><td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 18px 40px -28px rgba(11,14,23,0.45);">

        <tr>
          <td style="background:#0b0e17;padding:26px 32px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="vertical-align:middle;padding-right:12px;">
                  <img src="https://appmentech.in/assets/favicon-512.png" width="38" height="38" alt="Appmentech"
                       style="display:block;width:38px;height:38px;border:0;">
                </td>
                <td style="vertical-align:middle;">
                  <span style="font-size:22px;font-weight:700;letter-spacing:-0.5px;color:#ffffff;">Appmentech<span style="color:#f2a13c;">.</span></span>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr><td style="height:3px;background:#f2a13c;font-size:0;line-height:0;">&nbsp;</td></tr>

        <tr>
          <td style="padding:36px 32px 8px;">
            <p style="margin:0 0 14px;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#b8791f;">Requirement received</p>
            <h1 style="margin:0 0 18px;font-size:26px;line-height:1.2;font-weight:700;letter-spacing:-0.6px;color:#0b0e17;">Thanks for reaching out' . $greetName . '</h1>
            <p style="margin:0 0 16px;font-size:15px;line-height:1.65;color:#4a5365;">
              Your project requirement is with our team. Someone will read it properly and come back to you
              <strong style="color:#0b0e17;">within 1 business day</strong> with next steps or a couple of questions.
            </p>
            <p style="margin:0 0 26px;font-size:15px;line-height:1.65;color:#4a5365;">
              If it is urgent, reply straight to this email or call
              <a href="tel:+917303021135" style="color:#0b0e17;font-weight:600;text-decoration:none;">+91 73030 21135</a>.
            </p>
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="border-radius:999px;background:#f2a13c;">
                  <a href="https://appmentech.in" style="display:inline-block;padding:14px 30px;font-size:14.5px;font-weight:700;color:#17110a;text-decoration:none;border-radius:999px;">Visit our website &rarr;</a>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <tr>
          <td style="padding:30px 32px 34px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #e8eaf0;">
              <tr><td style="padding-top:22px;">
                <p style="margin:0 0 12px;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#8b95ac;">What we do</p>
                <p style="margin:0;font-size:13.5px;line-height:1.9;color:#4a5365;">
                  Web Development &nbsp;&middot;&nbsp; Mobile Applications &nbsp;&middot;&nbsp; AI &amp; Intelligent Solutions<br>
                  Cloud &amp; Enterprise &nbsp;&middot;&nbsp; Business Automation &nbsp;&middot;&nbsp; Quality Engineering<br>
                  DevOps &amp; CI/CD &nbsp;&middot;&nbsp; API &amp; System Integration
                </p>
              </td></tr>
            </table>
          </td>
        </tr>

        <tr>
          <td style="padding:26px 32px;background:#080b13;">
            <p style="margin:0 0 6px;font-size:15px;font-weight:700;color:#ffffff;">Appmentech Technologies<span style="color:#f2a13c;">.</span></p>
            <p style="margin:0 0 14px;font-size:12.5px;line-height:1.6;color:#8b95ac;">Your all-in-one digital and software solutions partner.</p>
            <p style="margin:0;font-size:12.5px;color:#8b95ac;">
              <a href="mailto:contact@appmentech.in" style="color:#f4c690;text-decoration:none;">contact@appmentech.in</a>
              &nbsp;&middot;&nbsp;
              <a href="https://appmentech.in" style="color:#f4c690;text-decoration:none;">appmentech.in</a>
            </p>
          </td>
        </tr>

      </table>
      <p style="margin:16px auto 0;max-width:600px;font-size:11px;line-height:1.6;color:#8b95ac;">
        This is an automated confirmation. Please do not share passwords or payment details by email.
      </p>
    </td></tr>
  </table>
</body>
</html>';
}

// ---- minimal SMTP-over-SSL client (no external dependencies) ----
function smtp_send($cfg, $to, $subject, $body, $replyTo, $replyName, $html = null, $attachments = []) {
    $errno = 0; $errstr = '';
    $fp = @stream_socket_client(
        'ssl://' . $cfg['host'] . ':' . $cfg['port'],
        $errno, $errstr, 20
    );
    if (!$fp) {
        return [false, "connect failed: $errstr ($errno)"];
    }
    stream_set_timeout($fp, 20);

    $read = function () use ($fp) {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            // last line of a reply has a space at position 3 (e.g. "250 OK")
            if (strlen($line) < 4 || $line[3] === ' ') break;
        }
        return $data;
    };
    $put = function ($cmd) use ($fp) {
        fwrite($fp, $cmd . "\r\n");
    };
    $code = function ($resp) {
        return substr($resp, 0, 3);
    };

    $r = $read();               if ($code($r) !== '220') return [false, $r];
    $put('EHLO ' . $cfg['helo']); $r = $read(); if ($code($r) !== '250') return [false, $r];
    $put('AUTH LOGIN');         $r = $read(); if ($code($r) !== '334') return [false, $r];
    $put(base64_encode($cfg['username'])); $r = $read(); if ($code($r) !== '334') return [false, $r];
    $put(base64_encode($cfg['password'])); $r = $read(); if ($code($r) !== '235') return [false, 'authentication failed'];
    $put('MAIL FROM:<' . $cfg['from'] . '>'); $r = $read(); if ($code($r) !== '250') return [false, $r];
    $put('RCPT TO:<' . $to . '>');            $r = $read(); if (!in_array($code($r), ['250', '251'])) return [false, $r];
    $put('DATA');               $r = $read(); if ($code($r) !== '354') return [false, $r];

    $fromHeader = mb_encode_mimeheader($cfg['from_name'], 'UTF-8') . ' <' . $cfg['from'] . '>';
    $replyHeader = ($replyName !== '' ? mb_encode_mimeheader($replyName, 'UTF-8') . ' ' : '') . '<' . $replyTo . '>';

    $headers = [
        'Date: ' . date('r'),
        'From: ' . $fromHeader,
        'To: <' . $to . '>',
        'Reply-To: ' . $replyHeader,
        'Subject: ' . mb_encode_mimeheader($subject, 'UTF-8'),
        'MIME-Version: 1.0',
    ];

    if ($html !== null) {
        // multipart/alternative: plain-text fallback + branded HTML
        $altBoundary = 'alt_' . bin2hex(random_bytes(12));
        $contentType = 'multipart/alternative; boundary="' . $altBoundary . '"';
        $bodyText =
            '--' . $altBoundary . "\r\n" .
            "Content-Type: text/plain; charset=UTF-8\r\n" .
            "Content-Transfer-Encoding: 8bit\r\n\r\n" .
            $body . "\r\n\r\n" .
            '--' . $altBoundary . "\r\n" .
            "Content-Type: text/html; charset=UTF-8\r\n" .
            "Content-Transfer-Encoding: 8bit\r\n\r\n" .
            $html . "\r\n\r\n" .
            '--' . $altBoundary . "--\r\n";
    } else {
        $contentType = 'text/plain; charset=UTF-8';
        $bodyText = $body;
    }

    if (!empty($attachments)) {
        // Wrap whatever the body turned out to be in a multipart/mixed part,
        // then append each file base64-encoded in 76-character lines.
        $mixBoundary = 'mix_' . bin2hex(random_bytes(12));
        $parts =
            '--' . $mixBoundary . "\r\n" .
            'Content-Type: ' . $contentType . "\r\n" .
            ($html === null ? "Content-Transfer-Encoding: 8bit\r\n" : '') .
            "\r\n" . $bodyText . "\r\n";

        foreach ($attachments as $file) {
            $data = @file_get_contents($file['path']);
            if ($data === false) {
                error_log('contact submit: could not attach ' . $file['name']);
                continue;
            }
            $parts .=
                '--' . $mixBoundary . "\r\n" .
                'Content-Type: ' . $file['mime'] . '; name="' . $file['name'] . '"' . "\r\n" .
                "Content-Transfer-Encoding: base64\r\n" .
                'Content-Disposition: attachment; filename="' . $file['name'] . '"' . "\r\n\r\n" .
                chunk_split(base64_encode($data), 76, "\r\n") . "\r\n";
        }
        $parts .= '--' . $mixBoundary . "--\r\n";

        $headers[] = 'Content-Type: multipart/mixed; boundary="' . $mixBoundary . '"';
        $bodyText = $parts;
    } else {
        $headers[] = 'Content-Type: ' . $contentType;
        if ($html === null) {
            $headers[] = 'Content-Transfer-Encoding: 8bit';
        }
    }

    // normalize whole DATA payload to CRLF and dot-stuff lines starting with '.'
    $payload = implode("\r\n", $headers) . "\r\n\r\n" . $bodyText;
    $normalized = preg_replace("/\r\n|\r|\n/", "\r\n", $payload);
    $lines = explode("\r\n", $normalized);
    foreach ($lines as &$line) {
        if (isset($line[0]) && $line[0] === '.') {
            $line = '.' . $line;
        }
    }
    unset($line);

    $put(implode("\r\n", $lines) . "\r\n.");
    $r = $read(); if ($code($r) !== '250') return [false, $r];

    $put('QUIT');
    fclose($fp);
    return [true, 'sent'];
}
