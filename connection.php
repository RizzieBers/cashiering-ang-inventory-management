<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "WEB"; // Change this to your actual database name

$con = new mysqli($host, $user, $password, $database);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>