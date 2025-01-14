<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Cấu hình JWT
define('JWT_SECRET_KEY', 'your_secret_key');
define('JWT_ALGO', 'HS256');

// Lấy token từ header Authorization
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    echo json_encode(["status" => "error", "message" => "Authorization header not found"]);
    exit;
}

$authHeader = $headers['Authorization'];
$token = str_replace('Bearer ', '', $authHeader);

try {
    // Giải mã và xác thực token
    $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, JWT_ALGO));
    $userData = $decoded->data;

    // Phản hồi thành công
    echo json_encode([
        "status" => "success",
        "message" => "Token is valid",
        "user" => $userData
    ]);
} catch (Exception $e) {
    // Xử lý lỗi token không hợp lệ
    echo json_encode(["status" => "error", "message" => "Invalid token", "error" => $e->getMessage()]);
    exit;
}
?>
