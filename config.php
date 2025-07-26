<?php
$host = 'localhost';
$user = 'root';  // change if needed
$pass = '';
$db = 'account_book';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
