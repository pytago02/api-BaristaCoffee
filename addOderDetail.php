<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $name = $data["name"];
    $table_id = $data["table_id"];
    $order_id = $data["order_id"];
    $item_id = $data["item_id"];
    $price = $data["price"];
    $quantity = $data["quantity"];
    $total_amount = $data["total_amount"];

    $stmt = $conn->prepare("INSERT INTO order_details (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
    } else {
        $stmt->bind_param("iiid", $order_id, $item_id, $quantity, $price);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Execution failed: " . $stmt->error]);
        }
        $stmt->close();
    }

    if(!$conn->query("UPDATE orders SET total_amount = $total_amount WHERE order_id = $order_id")){
        echo json_encode(["Error update total_amount table orders"]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method", "method" => $_SERVER['REQUEST_METHOD']]);
}

$conn->close();
?>