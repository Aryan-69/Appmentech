<?php
// lib/googledrive.php — upload a contact-form attachment to Google Drive,
// under UserRequirements/{UserRequirementId}/{filename}.
//
// Two ways to authenticate, both server-to-server with no user present:
//
//   A. Service account — set service_account_email and private_key from the
//      JSON key file. Works with a Shared Drive, or with a Workspace user via
//      'impersonate' (domain-wide delegation). A service account has no
//      storage quota of its own, so uploading into an ordinary personal Drive
//      folder fails with storageQuotaExceeded; use a Shared Drive or
//      impersonation for that case.
//
//   B. OAuth refresh token — set client_id, client_secret and refresh_token.
//      Files are owned by that account, so this is the option for a personal
//      Google account. Obtain the refresh token once with the consent flow,
//      requesting scope https://www.googleapis.com/auth/drive.file offline.
//
// Every function returns [ok, detailOrLink] so the caller can degrade to the
// "please resend the attachment" message rather than failing the submission.

// drive.file grants access only to files this app creates, and is the one
// Drive scope Google does not class as sensitive or restricted. That keeps a
// personal-Gmail OAuth client publishable without review, which in turn stops
// its refresh tokens expiring after seven days the way a Testing client's do.
const GOOGLE_DRIVE_SCOPE = 'https://www.googleapis.com/auth/drive.file';
const GOOGLE_TOKEN_URL = 'https://oauth2.googleapis.com/token';
const GOOGLE_DRIVE_API = 'https://www.googleapis.com/drive/v3';
const GOOGLE_DRIVE_UPLOAD = 'https://www.googleapis.com/upload/drive/v3';

function google_drive_setting(array $cfg, $key) {
    return isset($cfg['googledrive'][$key]) ? trim((string) $cfg['googledrive'][$key]) : '';
}

function google_drive_placeholder($value) {
    return $value === ''
        || stripos($value, 'YOUR_') === 0
        || strpos($value, ' ') !== false && stripos($value, '-----BEGIN') !== 0;
}

/**
 * True only when one complete set of credentials is present. Half-filled or
 * placeholder values must read as "not configured", otherwise every
 * attachment would attempt a doomed upload.
 */
function google_drive_configured(array $cfg) {
    if (empty($cfg['googledrive'])) {
        return false;
    }
    // A. service account
    $email = google_drive_setting($cfg, 'service_account_email');
    $key = google_drive_setting($cfg, 'private_key');
    if ($email !== '' && $key !== '') {
        return strpos($email, '@') !== false
            && strpos($email, '.iam.gserviceaccount.com') !== false
            && strpos($key, '-----BEGIN') === 0;
    }

    // B. installed-app OAuth
    $clientId = google_drive_setting($cfg, 'client_id');
    $secret = google_drive_setting($cfg, 'client_secret');
    $refresh = google_drive_setting($cfg, 'refresh_token');
    return !google_drive_placeholder($clientId)
        && !google_drive_placeholder($secret)
        && !google_drive_placeholder($refresh)
        && strpos($clientId, '.apps.googleusercontent.com') !== false;
}

function google_base64url($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/** Signed JWT assertion for the service-account grant. */
function google_drive_assertion(array $g) {
    $now = time();
    $header = ['alg' => 'RS256', 'typ' => 'JWT'];
    $claims = [
        'iss'   => $g['service_account_email'],
        'scope' => GOOGLE_DRIVE_SCOPE,
        'aud'   => GOOGLE_TOKEN_URL,
        'iat'   => $now,
        'exp'   => $now + 3600,
    ];
    if (!empty($g['impersonate'])) {
        $claims['sub'] = $g['impersonate'];
    }

    $input = google_base64url(json_encode($header)) . '.' . google_base64url(json_encode($claims));

    // The JSON key file stores the newlines escaped; PHP config may keep them
    // that way, so restore them before openssl sees the key.
    $key = str_replace(['\\n', "\r\n"], "\n", $g['private_key']);
    $signature = '';
    if (!openssl_sign($input, $signature, $key, 'sha256WithRSAEncryption')) {
        return [false, 'could not sign the assertion — check private_key'];
    }
    return [true, $input . '.' . google_base64url($signature)];
}

function google_drive_post_form($url, array $fields) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($fields),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return [false, 'token request failed: ' . $error];
    }
    $data = json_decode($response, true);
    if ($status < 200 || $status >= 300 || empty($data['access_token'])) {
        $detail = isset($data['error_description']) ? $data['error_description']
            : (isset($data['error']) ? $data['error'] : 'HTTP ' . $status);
        return [false, 'token endpoint: ' . $detail];
    }
    return [true, $data['access_token']];
}

function google_drive_token(array $cfg) {
    $g = $cfg['googledrive'];

    if (!empty($g['service_account_email']) && !empty($g['private_key'])) {
        list($ok, $assertion) = google_drive_assertion($g);
        if (!$ok) {
            return [false, $assertion];
        }
        return google_drive_post_form(GOOGLE_TOKEN_URL, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $assertion,
        ]);
    }

    return google_drive_post_form(GOOGLE_TOKEN_URL, [
        'grant_type'    => 'refresh_token',
        'client_id'     => $g['client_id'],
        'client_secret' => $g['client_secret'],
        'refresh_token' => $g['refresh_token'],
    ]);
}

function google_drive_request($method, $url, $token, $body = null, array $headers = []) {
    $ch = curl_init($url);
    $headers[] = 'Authorization: Bearer ' . $token;
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_HTTPHEADER     => $headers,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $location = null;
    curl_close($ch);

    if ($response === false) {
        return [0, null, $error];
    }
    return [$status, json_decode($response, true), $response];
}

/** Drive has no paths: find a child folder by name, or create it. */
function google_drive_folder($token, $parentId, $name) {
    $query = sprintf(
        "name = '%s' and '%s' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
        str_replace("'", "\\'", $name),
        str_replace("'", "\\'", $parentId)
    );
    $url = GOOGLE_DRIVE_API . '/files?q=' . rawurlencode($query)
         . '&fields=files(id)&supportsAllDrives=true&includeItemsFromAllDrives=true';

    list($status, $data) = google_drive_request('GET', $url, $token);
    if ($status >= 200 && $status < 300 && !empty($data['files'][0]['id'])) {
        return [true, $data['files'][0]['id']];
    }
    if ($status < 200 || $status >= 300) {
        return [false, 'folder lookup returned HTTP ' . $status];
    }

    $metadata = json_encode([
        'name'     => $name,
        'mimeType' => 'application/vnd.google-apps.folder',
        'parents'  => [$parentId],
    ]);
    list($status, $data) = google_drive_request(
        'POST',
        GOOGLE_DRIVE_API . '/files?fields=id&supportsAllDrives=true',
        $token,
        $metadata,
        ['Content-Type: application/json; charset=UTF-8']
    );
    if ($status < 200 || $status >= 300 || empty($data['id'])) {
        return [false, 'folder create returned HTTP ' . $status];
    }
    return [true, $data['id']];
}

/**
 * Upload one file to UserRequirements/{requirementId}/ inside the configured
 * folder. Files up to 5 MB go in a single multipart request; larger ones use a
 * resumable session, which is what Drive requires past that size.
 *
 * @return array{0:bool,1:string} [ok, shareable link or error detail]
 */
function google_drive_upload(array $cfg, $requirementId, $tmpPath, $fileName, $mimeType) {
    if (!google_drive_configured($cfg)) {
        return [false, 'Google Drive is not configured'];
    }
    if (!function_exists('curl_init')) {
        return [false, 'cURL extension is not available'];
    }
    if (!function_exists('openssl_sign') && !empty($cfg['googledrive']['private_key'])) {
        return [false, 'OpenSSL extension is not available'];
    }

    list($ok, $token) = google_drive_token($cfg);
    if (!$ok) {
        return [false, $token];
    }

    // No folder_id: put UserRequirements at the top of the account's Drive.
    $root = google_drive_setting($cfg, 'folder_id');
    if ($root === '' || stripos($root, 'YOUR_') === 0) {
        $root = 'root';
    }
    list($ok, $parent) = google_drive_folder($token, $root, 'UserRequirements');
    if (!$ok) {
        return [false, $parent];
    }
    list($ok, $folder) = google_drive_folder($token, $parent, $requirementId);
    if (!$ok) {
        return [false, $folder];
    }

    $size = filesize($tmpPath);
    $metadata = ['name' => $fileName, 'parents' => [$folder]];

    if ($size <= 5 * 1024 * 1024) {
        return google_drive_upload_multipart($token, $metadata, $tmpPath, $mimeType);
    }
    return google_drive_upload_resumable($token, $metadata, $tmpPath, $size, $mimeType);
}

function google_drive_upload_multipart($token, array $metadata, $tmpPath, $mimeType) {
    $data = @file_get_contents($tmpPath);
    if ($data === false) {
        return [false, 'could not read the uploaded file'];
    }

    $boundary = 'gdb_' . bin2hex(random_bytes(12));
    $body =
        '--' . $boundary . "\r\n" .
        "Content-Type: application/json; charset=UTF-8\r\n\r\n" .
        json_encode($metadata) . "\r\n" .
        '--' . $boundary . "\r\n" .
        'Content-Type: ' . $mimeType . "\r\n\r\n" .
        $data . "\r\n" .
        '--' . $boundary . "--";

    list($status, $parsed, $raw) = google_drive_request(
        'POST',
        GOOGLE_DRIVE_UPLOAD . '/files?uploadType=multipart&supportsAllDrives=true&fields=id,webViewLink',
        $token,
        $body,
        ['Content-Type: multipart/related; boundary=' . $boundary]
    );
    if ($status < 200 || $status >= 300 || empty($parsed['id'])) {
        return [false, 'upload returned HTTP ' . $status];
    }
    return [true, isset($parsed['webViewLink']) ? $parsed['webViewLink'] : $parsed['id']];
}

function google_drive_upload_resumable($token, array $metadata, $tmpPath, $size, $mimeType) {
    // Start the session: the location header is where the bytes go.
    $ch = curl_init(GOOGLE_DRIVE_UPLOAD . '/files?uploadType=resumable&supportsAllDrives=true&fields=id,webViewLink');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($metadata),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json; charset=UTF-8',
            'X-Upload-Content-Type: ' . $mimeType,
            'X-Upload-Content-Length: ' . $size,
        ],
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status < 200 || $status >= 300 || !preg_match('/^location:\s*(\S+)/mi', (string) $response, $m)) {
        return [false, 'resumable session returned HTTP ' . $status];
    }
    $sessionUrl = trim($m[1]);

    $handle = fopen($tmpPath, 'rb');
    if (!$handle) {
        return [false, 'could not read the uploaded file'];
    }
    $chunkSize = 4 * 1024 * 1024; // a multiple of 256 KiB, as Drive requires

    for ($offset = 0; $offset < $size; $offset += $chunkSize) {
        $chunk = fread($handle, $chunkSize);
        $end = $offset + strlen($chunk) - 1;

        $ch = curl_init($sessionUrl);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => $chunk,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_HTTPHEADER     => [
                'Content-Length: ' . strlen($chunk),
                'Content-Range: bytes ' . $offset . '-' . $end . '/' . $size,
            ],
        ]);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 308 = chunk stored, send the next one. 200/201 = upload complete.
        if ($status === 308) {
            continue;
        }
        if ($status === 200 || $status === 201) {
            fclose($handle);
            $parsed = json_decode((string) $response, true);
            return [true, isset($parsed['webViewLink']) ? $parsed['webViewLink'] : $parsed['id']];
        }
        fclose($handle);
        return [false, 'chunk upload returned HTTP ' . $status];
    }

    fclose($handle);
    return [false, 'upload ended without a final response'];
}
