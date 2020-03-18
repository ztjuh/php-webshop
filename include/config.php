<?php
//Show errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

setlocale(LC_MONETARY, 'nl_NL.utf8');

//SQL data
$sql_host = "localhost";
$sql_user = "webshop";
$sql_pass = "LXyZp8T3ndmufU4u";
$sql_db = "webshop_bootstrap4";

//Webshop naam
$webshop_name = "Webshop";

//E-Mail
$from_email = "norepy@ztjuh.tk";
$from_name = "Alex Mester";

//Session directory
$location = "webshop-bootstrap4/";
$session_dir = "/webshop_session";
    
// Check the DB connection
try {
    $pdo = new PDO("mysql:host={$sql_host};dbname={$sql_db}", $sql_user, $sql_pass);
} 
catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}