<?php
$servername = "localhost";
$username = "root"; // Mặc định của XAMPP là root
$password = "";     // Mặc định của XAMPP là rỗng
$dbname = "thuctapchuyennganh";

// Tạo kết nối
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
// Set font chữ tiếng Việt
mysqli_set_charset($conn, 'utf8');
