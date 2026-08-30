<?php
// google-auth.php — one-shot helper to obtain a Google Drive refresh token.
//
// Needed only for the OAuth option (a personal Google account). Steps:
//   1. Google Cloud console -> APIs & Services -> Credentials -> Create
//      OAuth client ID -> Web application.
//   2. Add this exact Authorised redirect URI:
//         https://appmentech.in/google-auth.php
//   3. Put client_id and client_secret into config.php under 'googledrive'.
//   4. Open https://appmentech.in/google-auth.php?i-will-delete-this=yes
//      and approve. The page prints the refresh token once.
//   5. Paste it into config.php as 'refresh_token', then DELETE THIS FILE.
//
// The token is a credential: do not leave this page reachable.

header('Content-Type: text/plain; charset=utf-8');

// Google's callback arrives as ?code=...&state=..., without our flag — a web
// client's redirect URI cannot carry a query string, so the flag guards only
// the starting step. The callback is tied to the browser that began the flow
// by a state value echoed back through a cookie.
$isCallback = isset($_GET['code']);

if (!$isCallback && (!isset($_GET['i-will-delete-this']) || $_GET['i-will-delete-this'] !== 'yes')) {
    echo "Add ?i-will-delete-this=yes to the URL to run this helper,\n";
    echo "then delete google-auth.php from the server.\n";
    exit;
}

if ($isCallback) {
    $expected = isset($_COOKIE['gauth_state']) ? $_COOKIE['gauth_state'] : '';
    $given = isset($_GET['state']) ? $_GET['state'] : '';
    if ($expected === '' || !hash_equals($expected, $given)) {
        echo "This callback did not come from a flow started in this browser.\n";
        echo "Start again at ?i-will-delete-this=yes\n";
        exit;
    }
}

$configPath = __DIR__ . '/config.php';
if (!is_file($configPath)) {
    exit("config.php is missing next to this file.\n");
}
$cfg = require $configPath;

$clientId = isset($cfg['googledrive']['client_id']) ? trim($cfg['googledrive']['client_id']) : '';
$secret = isset($cfg['googledrive']['client_secret']) ? trim($cfg['googledrive']['client_secret']) : '';
if ($clientId === '' || $secret === '' || strpos($clientId, '.apps.googleusercontent.com') === false) {
    exit("Set googledrive.client_id and googledrive.client_secret in config.php first.\n");
}

$redirect = 'https://' . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?');

// Step one: no code yet, so send the visitor to Google's consent screen.
if (!$isCallback) {
    $state = bin2hex(random_bytes(16));
    setcookie('gauth_state', $state, [
        'expires'  => time() + 900,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax', // must survive the redirect back from Google
    ]);

    $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id'     => $clientId,
        'redirect_uri'  => $redirect,
        'response_type' => 'code',
        'scope'         => 'https://www.googleapis.com/auth/drive.file',
        // offline + consent is what makes Google return a refresh token.
        'access_type'   => 'offline',
        'prompt'        => 'consent',
        'state'         => $state,
    ]);
    echo "Open this URL, approve access, and you will come back here:\n\n";
    echo $url . "\n\n";
    echo "Redirect URI in use: " . $redirect . "\n";
    echo "It must match the one registered on the OAuth client exactly.\n";
    exit;
}

// Step two: exchange the authorisation code for a refresh token.
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query([
        'code'          => $_GET['code'],
        'client_id'     => $clientId,
        'client_secret' => $secret,
        'redirect_uri'  => $redirect,
        'grant_type'    => 'authorization_code',
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20,
]);
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode((string) $response, true);
if ($status < 200 || $status >= 300 || empty($data['refresh_token'])) {
    echo "Token exchange failed (HTTP $status).\n";
    if (isset($data['error'])) {
        echo 'error: ' . $data['error'] . "\n";
    }
    if (isset($data['error_description'])) {
        echo 'detail: ' . $data['error_description'] . "\n";
    }
    if (isset($data['access_token']) && empty($data['refresh_token'])) {
        echo "\nGoogle returned an access token but no refresh token. That happens\n";
        echo "when this account already granted the app. Remove the grant at\n";
        echo "https://myaccount.google.com/permissions and run this again.\n";
    }
    exit;
}

echo "Refresh token (paste into config.php as googledrive.refresh_token):\n\n";
echo $data['refresh_token'] . "\n\n";
echo "Now delete google-auth.php from the server.\n";
