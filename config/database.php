<?php
$servername = "localhost";
$username = "root"; // Mặc định của XAMPP là root
$password = "";     // Mặc định của XAMPP là rỗng
$dbname = "thuctapchuyennganh";

try {
    // Cấu trúc kết nối PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Thiết lập chế độ báo lỗi (Cực quan trọng để debug)
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // echo "Kết nối thành công!"; 
} catch (PDOException $e) {
    die("Lỗi kết nối: " . $e->getMessage());
}
?>