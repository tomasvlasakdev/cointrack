<?php
define('DB_NAME', 'vlasato23');
define('DB_USER', 'vlasato23');
define('DB_PASSWORD', 'HzQmSPbF');
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