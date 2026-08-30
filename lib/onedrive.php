<?php
// lib/onedrive.php — upload a contact-form attachment to OneDrive via Microsoft
// Graph, under /UserRequirements/{UserRequirementId}/{filename}.
//
// Auth is the client-credentials flow against an Azure app registration with
// the application permission Files.ReadWrite.All. Fill the values in
// config.php: onedrive.tenant_id, client_id, client_secret and drive (either
// "drives/{driveId}" or "users/{userPrincipalName}/drive").
//
// Every function returns [ok, detailOrPath] so the caller can degrade to the
// "please resend the attachment" message rather than failing the submission.

/**
 * True only when all four settings look like real credentials. Placeholder
 * values — blanks, YOUR_*, all-zero GUIDs, the sample's prose — must read as
 * "not configured", otherwise every attachment would attempt a doomed upload
 * and the visitor would be told to resend a file that was never in trouble.
 */
function onedrive_configured(array $cfg) {
    if (empty($cfg['onedrive'])) {
        return false;
    }

    $value = function ($key) use ($cfg) {
        return isset($cfg['onedrive'][$key]) ? trim((string) $cfg['onedrive'][$key]) : '';
    };

    foreach (['tenant_id', 'client_id', 'client_secret', 'drive'] as $key) {
        $v = $value($key);
        if ($v === '' || stripos($v, 'YOUR_') === 0) {
            return false;
        }
    }

    // tenant and client are GUIDs (the tenant may instead be a domain), and an
    // all-zero GUID is the placeholder shape, not an identifier.
    $guid = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
    $zero = '00000000-0000-0000-0000-000000000000';

    $client = $value('client_id');
    if (!preg_match($guid, $client) || strcasecmp($client, $zero) === 0) {
        return false;
    }

    $tenant = $value('tenant_id');
    $tenantLooksReal = (preg_match($guid, $tenant) && strcasecmp($tenant, $zero) !== 0)
        || strpos($tenant, '.') !== false; // e.g. contoso.onmicrosoft.com
    if (!$tenantLooksReal) {
        return false;
    }

    // Entra client secrets are long and have no spaces; sample prose has both.
    $secret = $value('client_secret');
    if (strlen($secret) < 20 || preg_match('/\s/', $secret)) {
        return false;
    }

    // Graph addresses a drive as users/{upn}/drive or drives/{driveId}.
    $drive = trim($value('drive'), '/');
    return strpos($drive, 'users/') === 0 || strpos($drive, 'drives/') === 0;
}

function onedrive_token(array $one) {
    $url = 'https://login.microsoftonline.com/' . rawurlencode($one['tenant_id']) . '/oauth2/v2.0/token';
    $body = http_build_query([
        'client_id'     => $one['client_id'],
        'client_secret' => $one['client_secret'],
        'scope'         => 'https://graph.microsoft.com/.default',
        'grant_type'    => 'client_credentials',
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
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
        return [false, 'token endpoint returned HTTP ' . $status];
    }
    return [true, $data['access_token']];
}

/**
 * Upload one file. Files up to 4 MB go in a single PUT; larger ones use a
 * Graph upload session in 3.2 MB chunks (the API requires a multiple of 320 KiB).
 *
 * @return array{0:bool,1:string} [ok, remote path or error detail]
 */
function onedrive_upload(array $cfg, $requirementId, $tmpPath, $fileName, $mimeType) {
    if (!onedrive_configured($cfg)) {
        return [false, 'OneDrive is not configured'];
    }
    if (!function_exists('curl_init')) {
        return [false, 'cURL extension is not available'];
    }

    list($ok, $token) = onedrive_token($cfg['onedrive']);
    if (!$ok) {
        return [false, $token];
    }

    $drive = trim($cfg['onedrive']['drive'], '/');
    $remotePath = 'UserRequirements/' . $requirementId . '/' . $fileName;
    $encoded = implode('/', array_map('rawurlencode', explode('/', $remotePath)));
    $base = 'https://graph.microsoft.com/v1.0/' . $drive . '/root:/' . $encoded;
    $size = filesize($tmpPath);

    if ($size <= 4 * 1024 * 1024) {
        $handle = fopen($tmpPath, 'rb');
        if (!$handle) {
            return [false, 'could not read the uploaded file'];
        }
        $ch = curl_init($base . ':/content');
        curl_setopt_array($ch, [
            CURLOPT_PUT            => true,
            CURLOPT_INFILE         => $handle,
            CURLOPT_INFILESIZE     => $size,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: ' . $mimeType,
            ],
        ]);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        fclose($handle);

        if ($response === false) {
            return [false, 'upload failed: ' . $error];
        }
        if ($status < 200 || $status >= 300) {
            return [false, 'Graph upload returned HTTP ' . $status];
        }
        return [true, '/' . $remotePath];
    }

    return onedrive_upload_session($base, $token, $tmpPath, $size, $remotePath);
}

function onedrive_upload_session($base, $token, $tmpPath, $size, $remotePath) {
    $ch = curl_init($base . ':/createUploadSession');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['item' => ['@microsoft.graph.conflictBehavior' => 'replace']]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ],
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $session = json_decode((string) $response, true);
    if ($status < 200 || $status >= 300 || empty($session['uploadUrl'])) {
        return [false, 'createUploadSession returned HTTP ' . $status];
    }

    $chunkSize = 3276800; // 3.2 MB — a multiple of 320 KiB, as Graph requires
    $handle = fopen($tmpPath, 'rb');
    if (!$handle) {
        return [false, 'could not read the uploaded file'];
    }

    for ($offset = 0; $offset < $size; $offset += $chunkSize) {
        $chunk = fread($handle, $chunkSize);
        $end = $offset + strlen($chunk) - 1;

        $ch = curl_init($session['uploadUrl']);
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

        // 202 = chunk accepted, 200/201 = final chunk stored.
        if (!in_array($status, [200, 201, 202], true)) {
            fclose($handle);
            return [false, 'chunk upload returned HTTP ' . $status];
        }
    }
    fclose($handle);
    return [true, '/' . $remotePath];
}
