<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'db.php';
require_once 'vendor/autoload.php';
use \Firebase\JWT\JWT;  // Đảm bảo bạn đã include Firebase JWT library

$secretKey = "your_secret_key";  // Đặt secret key cho JWT

$sql = "SELECT * FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $users = array();
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

foreach ($users as $user) {
    if ($user['username'] === $username && $user['password'] === $password) {
        // Tạo JWT token cho tất cả người dùng
        $issuedAt = time();
        $expirationTime = $issuedAt + 86400;  // Token hết hạn sau 1 giờ
        $payload = array(
            "user_id" => $user['user_id'],
            "username" => $user['username'],
            "role" => $user['role'],
            "iat" => $issuedAt,
            "exp" => $expirationTime
        );

        // Tạo token
        $jwt = JWT::encode($payload, $secretKey, 'HS256');

        echo json_encode([
            "status" => "success",
            "token" => $jwt,
            "role" => $user['role'] 
        ]);
        exit;
    }
}

echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
?>
