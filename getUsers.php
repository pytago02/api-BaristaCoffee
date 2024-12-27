<?php
header('Content-Type: application/json');

include 'db.php';  // Kết nối đến MySQL 

// Lấy dữ liệu từ bảng users
$sql = "SELECT * FROM users";
$result = $conn->query(query: $sql);

if ($result->num_rows > 0) {
    // Đưa dữ liệu vào mảng
    $users = array();
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode($users);  // Trả về dữ liệu dưới dạng JSON
} else {
    echo json_encode([]);  // Nếu không có dữ liệu, trả về mảng rỗng
}

$conn->close();
?>
