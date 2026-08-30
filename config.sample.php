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

    // --- OneDrive (attachment storage via Microsoft Graph) -------------------
    // Azure app registration with the APPLICATION permission Files.ReadWrite.All
    // (admin consent granted). Files land in
    //   /UserRequirements/{UserRequirementId}/{filename}
    // While these stay blank the attachment is emailed instead of uploaded.
    'onedrive' => [
        'tenant_id'     => 'YOUR_TENANT_ID',
        'client_id'     => 'YOUR_CLIENT_ID',
        'client_secret' => 'YOUR_CLIENT_SECRET',
        // Target drive. Either 'users/contact@appmentech.in/drive'
        // or 'drives/{driveId}' for a SharePoint document library.
        'drive'         => 'YOUR_DRIVE',
    ],

    // --- Optional malware scan ----------------------------------------------
    // Absolute path to clamdscan/clamscan. Empty means uploads are accepted on
    // the extension + MIME checks alone.
    'clamscan' => '',
];
