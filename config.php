<?php
define('DB_NAME', ''your_user);
define('DB_USER', 'your_user');
define('DB_PASSWORD', 'your_password');
define('DB_HOST', '127.0.0.1');

global $pdo;
$pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASSWORD,
        array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        )
);
?>
