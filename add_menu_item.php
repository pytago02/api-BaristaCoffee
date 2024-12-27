<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['category_id'], $data['name'], $data['price'], $data['availability'])) {
        $category = $data['category_id'];
        $name = $data['name'];
        $price = $data['price'];
        $description = $data['description'];
        $availability = $data['availability'];

        $sql = "INSERT INTO menu_items (category_id, name, price, description, availability) VALUES ('$category', '$name', '$price', '$description', '$availability')";

        $stmt = $conn->prepare($sql);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Menu item added successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to add menu item", "error" => $stmt->error]);
        }
        

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method", "method" => $_SERVER['REQUEST_METHOD']]);
}

$conn->close();
?>
