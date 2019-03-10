<?php
session_start();
ini_set('max_execution_time', 0);
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "scraper2";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
?>