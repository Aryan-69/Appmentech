<?php
// submit.php — receives the contact form POST and sends it over Hostinger SMTP.
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

// ---- collect + sanitize input ----
function field($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}
// strip CR/LF from anything that goes into a header (prevents header injection)
function oneLine($v) {
    return str_replace(["\r", "\n"], ' ', $v);
}

$name        = field('name');
$company     = field('company');
$email       = field('email');
$phone       = field('phone');
$industry    = field('industry');
$solution    = field('solution');
$budget      = field('budget');
$timeline    = field('timeline');
$description = field('description');

if ($name === '' || $email === '' || $description === '') {
    fail('Name, Email, and Project Description are required.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Please provide a valid email address.');
}

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
    '',
    'Project Description:',
    $description,
];
$body = implode("\r\n", $bodyLines);

// 1) Notify the team. Reply-To is the visitor so a reply reaches them.
list($ok, $detail) = smtp_send($cfg, $cfg['to'], $subject, $body, $email, oneLine($name));

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

echo json_encode(['ok' => true]);

function auto_reply_body($name) {
    $first = trim($name) !== '' ? ' ' . $name : '';
    return
        "Hi" . $first . ",\r\n\r\n" .
        "Thank you for reaching out to Appmentech Technologies. We have received your " .
        "project requirement and a member of our team will get back to you within 1 business day.\r\n\r\n" .
        "If your request is urgent, you can reply directly to this email or call us at +91 12345 67890.\r\n\r\n" .
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
    return
'<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#475569;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 0;">
    <tr><td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(15,23,42,0.08);">
        <tr>
          <td style="background:#12235B;padding:28px 32px;">
            <span style="font-size:22px;font-weight:800;color:#ffffff;">Appmentech<span style="color:#F5A71C;">.</span></span>
          </td>
        </tr>
        <tr>
          <td style="height:4px;background:#F5A71C;font-size:0;line-height:0;">&nbsp;</td>
        </tr>
        <tr>
          <td style="padding:32px;">
            <h1 style="margin:0 0 16px;font-size:22px;color:#12235B;font-weight:800;">Thanks for reaching out' . $greetName . '!</h1>
            <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">
              We\'ve received your project requirement and a member of our team will get back to you
              <strong>within 1 business day</strong>.
            </p>
            <p style="margin:0 0 24px;font-size:15px;line-height:1.6;">
              If your request is urgent, just reply to this email or call us at
              <strong style="color:#12235B;">+91 12345 67890</strong>.
            </p>
            <table role="presentation" cellpadding="0" cellspacing="0">
              <tr><td style="border-radius:8px;background:#F5A71C;">
                <a href="https://appmentech.in" style="display:inline-block;padding:12px 24px;font-size:14px;font-weight:700;color:#12235B;text-decoration:none;">Visit our website</a>
              </td></tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:24px 32px;background:#0F172A;">
            <p style="margin:0 0 6px;font-size:14px;font-weight:700;color:#F8FAFC;">Appmentech Technologies</p>
            <p style="margin:0 0 10px;font-size:12px;color:#94A3B8;">Web | Mobile | AI | SaaS | Cloud | Automation | Enterprise | Quality Engineering</p>
            <p style="margin:0;font-size:12px;color:#94A3B8;">
              <a href="mailto:contact@appmentech.in" style="color:#F5A71C;text-decoration:none;">contact@appmentech.in</a>
              &nbsp;&middot;&nbsp;
              <a href="https://appmentech.in" style="color:#F5A71C;text-decoration:none;">appmentech.in</a>
            </p>
          </td>
        </tr>
      </table>
      <p style="margin:16px 0 0;font-size:11px;color:#94A3B8;max-width:600px;">
        This is an automated confirmation. Please don\'t share sensitive information by email.
      </p>
    </td></tr>
  </table>
</body>
</html>';
}

// ---- minimal SMTP-over-SSL client (no external dependencies) ----
function smtp_send($cfg, $to, $subject, $body, $replyTo, $replyName, $html = null) {
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
        $boundary = 'bnd_' . bin2hex(random_bytes(12));
        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
        $bodyText =
            '--' . $boundary . "\r\n" .
            "Content-Type: text/plain; charset=UTF-8\r\n" .
            "Content-Transfer-Encoding: 8bit\r\n\r\n" .
            $body . "\r\n\r\n" .
            '--' . $boundary . "\r\n" .
            "Content-Type: text/html; charset=UTF-8\r\n" .
            "Content-Transfer-Encoding: 8bit\r\n\r\n" .
            $html . "\r\n\r\n" .
            '--' . $boundary . "--\r\n";
    } else {
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'Content-Transfer-Encoding: 8bit';
        $bodyText = $body;
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
