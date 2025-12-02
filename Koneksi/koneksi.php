<?php
$servername = "localhost";
$username   = "root";
$password   = ""; 
$database   = "db_ukopia";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    
}
?>