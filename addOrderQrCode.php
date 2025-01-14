<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode the input JSON data
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        echo json_encode(["status" => "error", "message" => "Invalid input data format"]);
        exit;
    }

    

    $table_id = $data[0]['table_id'];
    // echo "Table ID: " . $table_id;
    $total_amount = 0;

    // Insert the order into the `Orders` table
    $stmt_order = $conn->prepare("INSERT INTO orders (table_id, total_amount) VALUES (?, ?)");
    if (!$stmt_order) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }
    $stmt_order->bind_param("id", $table_id, $total_amount);
    if (!$stmt_order->execute()) {
        echo json_encode(["status" => "error", "message" => "Order insertion failed: " . $stmt_order->error]);
        exit;
    }
    $order_id = $stmt_order->insert_id; // Get the ID of the inserted order
    $stmt_order->close();

    // Insert items into `Order_Details`
    $stmt_details = $conn->prepare("INSERT INTO order_details (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)");
    if (!$stmt_details) {
        echo json_encode(["status" => "error", "message" => "Prepare failed for order_details: " . $conn->error]);
        exit;
    }

    foreach ($data as $item) {
        $item_id = $item['item_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        $total_item_order = $item['totalItemOder']; // Calculated in the JSON input

        // Add the item's total to the order's total amount
        $total_amount += $total_item_order;

        // Insert the item into the order_details table
        $stmt_details->bind_param("iiid", $order_id, $item_id, $quantity, $price);
        if (!$stmt_details->execute()) {
            echo json_encode(["status" => "error", "message" => "Item insertion failed: " . $stmt_details->error]);
            exit;
        }
    }
    $stmt_details->close();

    // Update the total_amount in the `Orders` table
    $stmt_update_order = $conn->prepare("UPDATE orders SET total_amount = ? WHERE order_id = ?");
    if (!$stmt_update_order) {
        echo json_encode(["status" => "error", "message" => "Prepare failed for update: " . $conn->error]);
        exit;
    }
    $stmt_update_order->bind_param("di", $total_amount, $order_id);
    if (!$stmt_update_order->execute()) {
        echo json_encode(["status" => "error", "message" => "Update total_amount failed: " . $stmt_update_order->error]);
        exit;
    }
    $stmt_update_order->close();

    $conn->query("UPDATE tables SET status = 3 WHERE table_id = $table_id AND status = 0");
    // Return success response
    echo json_encode(["status" => "success", "order_id" => $order_id, "total_amount" => $total_amount]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method", "method" => $_SERVER['REQUEST_METHOD']]);
}

$conn->close();
?>
