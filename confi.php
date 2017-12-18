<?php
$servername = "localhost";
$username = "dbadmin";
$password = "hqckint0sh";

// Create connection
$conn = mysql_connect($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
mysql_select_db('copaMundialFutbol', $conn);
//echo "Connected successfully";

mysql_set_charset('utf8',$conn);
