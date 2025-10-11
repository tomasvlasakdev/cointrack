<?php
define('DB_NAME', 'insertName');
define('DB_USER', 'insertUser');
define('DB_PASSWORD', 'insertPassword');
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
