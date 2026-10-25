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
];
