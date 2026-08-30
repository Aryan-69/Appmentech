<?php
// drive-check.php — one-shot diagnostic for the Google Drive attachment path.
//
// Open https://appmentech.in/drive-check.php?i-will-delete-this=yes, read the
// report, then DELETE THIS FILE. It never prints a secret: credentials are
// reported only by shape and length.

header('Content-Type: text/plain; charset=utf-8');

if (!isset($_GET['i-will-delete-this']) || $_GET['i-will-delete-this'] !== 'yes') {
    echo "Add ?i-will-delete-this=yes to the URL to run this check,\n";
    echo "then delete drive-check.php from the server.\n";
    exit;
}

function line($label, $value) {
    echo str_pad($label, 26) . $value . "\n";
}

/** Describe a credential without revealing it. */
function shape($value) {
    $value = (string) $value;
    if (trim($value) === '') {
        return 'EMPTY';
    }
    $note = strlen($value) . ' chars';
    if ($value !== trim($value)) {
        $note .= ', HAS LEADING/TRAILING SPACE';
    }
    if (strpos(trim($value), ' ') !== false) {
        $note .= ', CONTAINS A SPACE';
    }
    return $note;
}

echo "Appmentech — Google Drive check\n";
echo str_repeat('=', 52) . "\n\n";

line('PHP version', PHP_VERSION);
line('cURL', function_exists('curl_init') ? 'available' : 'MISSING');
line('OpenSSL', function_exists('openssl_sign') ? 'available' : 'missing (service account only)');

$configPath = __DIR__ . '/config.php';
if (!is_file($configPath)) {
    exit("\nFAIL  config.php is missing next to this file.\n");
}
$cfg = require $configPath;

$libPath = __DIR__ . '/lib/googledrive.php';
if (!is_file($libPath)) {
    exit("\nFAIL  lib/googledrive.php is missing. Upload it from the build.\n");
}
require_once $libPath;

// The first release required folder_id; a stale copy is the usual reason a
// filled-in config still reads as unconfigured.
$source = file_get_contents($libPath);
line('lib/googledrive.php', strpos($source, "google_drive_setting(\$cfg, 'folder_id')\n") !== false
    || strpos($source, 'No folder_id') !== false ? 'current (folder_id optional)' : 'OLD COPY — folder_id required, re-upload it');
line('scope in use', defined('GOOGLE_DRIVE_SCOPE') ? GOOGLE_DRIVE_SCOPE : 'unknown');

echo "\n-- config.php googledrive block --\n";
if (empty($cfg['googledrive'])) {
    exit("FAIL  There is no 'googledrive' key in config.php.\n");
}
foreach (['folder_id', 'service_account_email', 'private_key', 'impersonate',
          'client_id', 'client_secret', 'refresh_token'] as $key) {
    line($key, array_key_exists($key, $cfg['googledrive']) ? shape($cfg['googledrive'][$key]) : 'not present');
}

echo "\n-- what the code decides --\n";
$clientId = google_drive_setting($cfg, 'client_id');
$secret = google_drive_setting($cfg, 'client_secret');
$refresh = google_drive_setting($cfg, 'refresh_token');
$email = google_drive_setting($cfg, 'service_account_email');

line('credential set', $email !== '' ? 'service account' : 'OAuth refresh token');
if ($email === '') {
    line('client_id shape', strpos($clientId, '.apps.googleusercontent.com') !== false
        ? 'ok' : 'WRONG — must end .apps.googleusercontent.com');
    line('client_secret usable', google_drive_placeholder($secret) ? 'NO — blank, YOUR_, or contains a space' : 'ok');
    line('refresh_token usable', google_drive_placeholder($refresh) ? 'NO — blank, YOUR_, or contains a space' : 'ok');
}

$configured = google_drive_configured($cfg);
line('google_drive_configured', $configured ? 'TRUE' : 'FALSE  <-- this is why the form says "not configured"');

if (!$configured) {
    echo "\nFix whichever field is flagged above, then run this again.\n";
    exit;
}

echo "\n-- live token request --\n";
list($ok, $token) = google_drive_token($cfg);
if (!$ok) {
    echo "FAIL  $token\n";
    echo "\ninvalid_client  -> client_id or client_secret is wrong.\n";
    echo "invalid_grant   -> the refresh token was revoked, expired, or belongs\n";
    echo "                   to a different OAuth client. Re-run google-auth.php.\n";
    exit;
}
line('access token', 'received, ' . strlen($token) . ' chars');

echo "\n-- folder --\n";
$root = google_drive_setting($cfg, 'folder_id');
if ($root === '' || stripos($root, 'YOUR_') === 0) {
    $root = 'root';
}
line('parent', $root === 'root' ? 'top of My Drive' : $root);

list($ok, $folder) = google_drive_folder($token, $root, 'UserRequirements');
if (!$ok) {
    echo "FAIL  $folder\n";
    echo "\nA 404 here with a folder_id set means the drive.file scope cannot see\n";
    echo "that folder, because this app did not create it. Leave folder_id blank.\n";
    exit;
}
line('UserRequirements', 'ready, id ' . substr($folder, 0, 8) . '...');

echo "\nOK    Google Drive is wired up correctly.\n";
echo "\nDelete drive-check.php now that you have the report.\n";
