<?php
define('DB_SERVER', 'mysql-luismarkustorres.alwaysdata.net');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('DB_NAME', '');

session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; 
}

if (!isset($_SESSION['experience_codes'])) {
    $_SESSION['experience_codes'] = [];
}
?>