<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$database = "dbummpgmvvli4x";
$username = "utkv7mm8ro4eo";
$password = "oojhazvnlsc7";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// Uncomment the line below to check if connection is successful
// echo "Connected successfully";
?> 
