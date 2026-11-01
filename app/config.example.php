<?php
// Copy to config.php. Keep this file outside the web document root.
return [
    'dsn' => 'mysql:host=localhost;dbname=graphite;charset=utf8mb4',
    'db_user' => 'graphite_user',
    'db_password' => 'CHANGE_ME',
    'base_url' => 'https://graphiteindustries.co.tz',
    'session_name' => 'graphite_admin',
    'secure_cookies' => true,
    'rate_limit_key' => 'REPLACE_WITH_64_RANDOM_HEX_CHARACTERS',
    // Enquiry notifications. Use your mailbox provider's SMTP details; keep disabled until tested.
    'mail' => [
        'enabled' => false,
        'transport' => 'smtp', // or 'mail' for PHP mail()
        'host' => 'mail.graphiteindustries.co.tz',
        'port' => 587,
        'encryption' => 'tls', // 'tls' (STARTTLS), 'ssl' or 'none'
        'username' => 'website@graphiteindustries.co.tz',
        'password' => 'CHANGE_ME',
        'from' => 'website@graphiteindustries.co.tz',
        'from_name' => 'Graphite Industries website',
        'to' => '', // blank: CMS notification email, then the public company email
    ],
];
