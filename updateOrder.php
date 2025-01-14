<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: PUT");

include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data)) {
    $order_id = $data['order_id'];
    $table_id = $data['table_id'];

    // Cập nhật trạng thái của bàn
    $stmt = $conn->prepare("UPDATE tables SET status = 1 WHERE table_id = ?");
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "TABLE Prepare failed: " . $conn->error]);
    } else {
        $stmt->bind_param("i", $table_id);
        if ($stmt->execute()) {
            // Cập nhật trạng thái của đơn hàng
            $stmt2 = $conn->prepare("UPDATE orders SET order_status = 'delivered' WHERE order_id = ?");
            if (!$stmt2) {
                echo json_encode(["status" => "error", "message" => "ORDER Prepare failed: " . $conn->error]);
            } else {
                $stmt2->bind_param("i", $order_id);
                if ($stmt2->execute()) {
                    echo json_encode(["status" => "success", "message" => "ORDER STATUS updated successfully"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Failed to update order status: " . $stmt2->error]);
                }
                $stmt2->close();
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update table status: " . $stmt->error]);
        }
        $stmt->close();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid input data"]);
}

$conn->close();
?>
