<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: PUT");

include 'db.php';

$data = json_decode(file_get_contents("php://input"));

if (isset($data->category_id, $data->name, $data->price, $data->availability, $data->item_id)) {
    $category = $data->category_id;
    $name = $data->name;
    $price = $data->price;
    // $description = isset($data->description) ? $data->description : null;
    $description = $data->description;
    $availability = $data->availability;
    
    $sql = "UPDATE menu_items SET category_id = ?, name = ?, price = ?, description = ?, availability = ? WHERE item_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssii", $category, $name, $price, $description, $availability, $data->item_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Menu item updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update menu item", "error" => $stmt->error]);
    }

    $stmt->close();
}
$conn->close();
?>
