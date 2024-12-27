<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'db.php';

$sql = "SELECT * FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Đưa dữ liệu vào mảng
    $users = array();
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Dữ liệu mẫu (thay thế bằng database thực tế)
// $users = [
//     ["user_id" => "1", "username" => "admin", "password" => "1", "role" => "admin"],
//     ["user_id" => "2", "username" => "owner", "password" => "1", "role" => "owner"],
//     ["user_id" => "3", "username" => "staff01", "password" => "1", "role" => "client"],
//     ["user_id" => "4", "username" => "staff02", "password" => "1", "role" => "client"]
// ];

// Lấy dữ liệu từ request
$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

// Kiểm tra username và password
foreach ($users as $user) {
    if ($user['username'] === $username && $user['password'] === $password) {
        echo json_encode([
            "status" => "success",
            "user_id" => $user['user_id'],
            "username" => $user['username'],
            "role" => $user['role']
        ]);
        exit;
    }
}

// Trả về lỗi nếu thông tin không đúng
echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
?>
