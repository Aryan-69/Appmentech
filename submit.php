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

list($ok, $detail) = smtp_send($cfg, $subject, $body, $email, oneLine($name));

if ($ok) {
    echo json_encode(['ok' => true]);
} else {
    error_log('contact submit SMTP error: ' . $detail);
    fail('Email could not be sent. Please try again later.', 502);
}

// ---- minimal SMTP-over-SSL client (no external dependencies) ----
function smtp_send($cfg, $subject, $body, $replyTo, $replyName) {
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
    $put('RCPT TO:<' . $cfg['to'] . '>');     $r = $read(); if (!in_array($code($r), ['250', '251'])) return [false, $r];
    $put('DATA');               $r = $read(); if ($code($r) !== '354') return [false, $r];

    $fromHeader = mb_encode_mimeheader($cfg['from_name'], 'UTF-8') . ' <' . $cfg['from'] . '>';
    $replyHeader = ($replyName !== '' ? mb_encode_mimeheader($replyName, 'UTF-8') . ' ' : '') . '<' . $replyTo . '>';

    $headers = [
        'Date: ' . date('r'),
        'From: ' . $fromHeader,
        'To: <' . $cfg['to'] . '>',
        'Reply-To: ' . $replyHeader,
        'Subject: ' . mb_encode_mimeheader($subject, 'UTF-8'),
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    ];

    // normalize body to CRLF and dot-stuff lines starting with '.'
    $normalized = preg_replace("/\r\n|\r|\n/", "\r\n", $body);
    $lines = explode("\r\n", $normalized);
    foreach ($lines as &$line) {
        if (isset($line[0]) && $line[0] === '.') {
            $line = '.' . $line;
        }
    }
    unset($line);

    $message = implode("\r\n", $headers) . "\r\n\r\n" . implode("\r\n", $lines) . "\r\n.";
    $put($message);
    $r = $read(); if ($code($r) !== '250') return [false, $r];

    $put('QUIT');
    fclose($fp);
    return [true, 'sent'];
}
