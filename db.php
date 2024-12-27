<?php
$host = 'localhost';
$user = 'root';  // Mặc định là root trong XAMPP
$password = '';  // Mặc định không có mật khẩu trong XAMPP
$database = 'tdb-02';

// Kết nối MySQL
$conn = new mysqli($host, $user, $password, $database);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
