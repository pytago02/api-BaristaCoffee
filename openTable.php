<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Đọc dữ liệu JSON từ body request
    $data = json_decode(file_get_contents("php://input"), true);
    // echo json_encode($data);

    // Kiểm tra xem dữ liệu có tồn tại key 'table_id' hay không
    if (!isset($data['table_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing table_id in request"]);
        exit;
    }

    // Lấy dữ liệu table_id từ body request
    $table_id = $data["table_id"];
    
    // Kiểm tra xem table_id có hợp lệ không
    if (empty($table_id)) {
        echo json_encode(["status" => "error", "message" => "Invalid table_id"]);
        exit;
    }
    $conn->query("UPDATE tables SET status = 1 WHERE table_id = $table_id");

    // Bước 1: Thêm mới đơn hàng vào bảng orders
    $stmt = $conn->prepare("INSERT INTO orders (table_id) VALUES (?)");
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    } else {
        $stmt->bind_param("i", $table_id);
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;  // Lấy order_id vừa được tạo từ bảng orders
            $stmt->close();

            // Bước 2: Thêm chi tiết đơn hàng vào bảng order_details
            $stmt = $conn->prepare("INSERT INTO order_details (order_id) VALUES (?)");
            if (!$stmt) {
                echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
                exit;
            } else {
                $stmt->bind_param("i", $order_id);
                if (!$stmt->execute()) {
                    echo json_encode(["status" => "error", "message" => "Execution failed: " . $stmt->error]);
                    exit;
                }
                $stmt->close();
            }

            // Nếu không có lỗi, trả về thành công
            echo json_encode(["status" => "success", "order_id" => $order_id, "data: "=> $data]);

        } else {
            echo json_encode(["status" => "error", "message" => "Execution failed: " . $stmt->error]);
            exit;
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method", "method" => $_SERVER['REQUEST_METHOD']]);
}

$conn->close();
?>
