<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: DELETE");

include 'db.php';

if (isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];

    $sql = "DELETE FROM menu_items WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Menu item deleted successfully", "item_id" => $item_id]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete menu item", "error" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Item ID is required"]);
}
?>
