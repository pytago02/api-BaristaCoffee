<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $table_id = $data["table_id"];
    $order_id = $data["order_id"];
    $payment_method = $data["payment_method"];
    $amount_paid = $data["amount_paid"];
    if (isset($table_id, $order_id, $payment_method)) {
        try {
            // Cập nhật trạng thái của bảng
            $stmt1 = $conn->prepare("UPDATE tables SET status = 0 WHERE table_id = ?");
            $stmt1->bind_param("i", $table_id);
            $stmt1->execute();
        
            // Thực hiện chèn dữ liệu vào bảng payments
            $stmt2 = $conn->prepare("INSERT INTO payments (order_id, payment_method, amount_paid) VALUES (?, ?, ?)");
            $stmt2->bind_param("isi", $order_id, $payment_method, $amount_paid);
            $stmt2->execute();
        
            // Nếu tất cả thành công, commit giao dịch
            $conn->commit();
            echo json_encode(["status" => "success"]);
        } catch (Exception $e) {
            // Nếu có lỗi, rollback giao dịch
            $conn->rollback();
            echo json_encode(["status" => "error", "message" => "Transaction failed: " . $e->getMessage()]);
        }
        
        // Đóng các statement
        $stmt1->close();
        $stmt2->close();
    }else echo json_encode(['statuss: ' => 'error', 'message'=> 'not isser Data']);

    // if ($conn->query("UPDATE tables SET status = 0")) {
    //     echo json_encode(["status:" => "success", "message" => "update table status"]);
    // } else {
    //     echo json_encode(["status:" => "error", "message" => " update table status failed: " . $conn->error]);
    // }

} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method", "method" => $_SERVER['REQUEST_METHOD']]);
}

$conn->close();
?>