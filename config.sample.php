<?php
// Copy this file to config.php and fill in the real mailbox password.
// config.php is gitignored — NEVER commit real credentials.
return [
    'host'      => 'smtp.hostinger.com',
    'port'      => 465,
    'username'  => 'administrator@appmentech.in',
    'password'  => 'YOUR_MAILBOX_PASSWORD_HERE',
    'from'      => 'administrator@appmentech.in', // must be a mailbox on this domain
    'from_name' => 'Appmentech Website',
    'to'        => 'administrator@appmentech.in', // where submissions are delivered
    'helo'      => 'appmentech.in',
];
