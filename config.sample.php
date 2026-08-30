<?php
// Copy this file to config.php and fill in the real values.
// config.php is gitignored — NEVER commit real credentials.
return [
    // --- SMTP (Hostinger mailbox) -------------------------------------------
    'host'      => 'smtp.hostinger.com',
    'port'      => 465,
    'username'  => 'contact@appmentech.in',
    'password'  => 'YOUR_MAILBOX_PASSWORD_HERE',
    'from'      => 'contact@appmentech.in', // must be a mailbox on this domain
    'from_name' => 'Appmentech Website',
    'to'        => 'contact@appmentech.in', // where submissions are delivered
    'helo'      => 'appmentech.in',

    // --- MySQL (UserRequirements) -------------------------------------------
    // Create the tables from db/schema.sql first. Leave host/name empty to run
    // the form without storage: submissions are still emailed.
    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => '',                        // e.g. u123456789_appmentech
        'user'     => '',
        'password' => 'YOUR_DB_PASSWORD_HERE',
    ],

    // --- Google Drive (attachment storage) ----------------------------------
    // Files land in  UserRequirements/{UserRequirementId}/{filename}  inside
    // the folder named by folder_id. Fill ONE of the two credential sets.
    // While they stay blank the attachment is emailed instead of uploaded.
    'googledrive' => [
        // Drive folder ID: open the folder in Drive and copy the last path
        // segment of the URL (.../folders/THIS_PART).
        'folder_id' => '',

        // Option A - service account (Google Cloud -> IAM -> Service accounts
        // -> Keys -> JSON). Share the folder with the service account email as
        // Editor. A service account has no storage quota of its own, so this
        // works with a Shared Drive, or with a Workspace user set in
        // 'impersonate' via domain-wide delegation.
        'service_account_email' => '',
        'private_key'           => '',
        'impersonate'           => '',

        // Option B - OAuth, for a personal Google account. Create a Web
        // application OAuth client, then run google-auth.php once to get the
        // refresh token.
        'client_id'     => '',
        'client_secret' => '',
        'refresh_token' => '',
    ],

    // --- Optional malware scan ----------------------------------------------
    // Absolute path to clamdscan/clamscan. Empty means uploads are accepted on
    // the extension + MIME checks alone.
    'clamscan' => '',
];
