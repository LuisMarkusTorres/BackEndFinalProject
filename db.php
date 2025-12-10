<?php
$DB_SERVER   = "mysql-luismarkustorres.alwaysdata.net";
$DB_USERNAME = "443649";
$DB_PASSWORD = "3#Pb*qDYLj3xV6]";
$DB_NAME     = "luismarkustorres_pw3db";

try {
    $pdo = new PDO(
        "mysql:host=$DB_SERVER;dbname=$DB_NAME;charset=utf8",
        $DB_USERNAME,
        $DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]
    );

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
