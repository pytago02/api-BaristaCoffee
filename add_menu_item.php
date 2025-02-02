<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $tagetDir = "upload/";
    if (!is_dir($tagetDir)) {
        mkdir($tagetDir, 0777, true);
    }

    if (isset($_FILES['file'])) {
        if ($_FILES["file"]["error"] > 0) {
            echo json_encode(["status" => "error", "message" => "Error" . $_FILES["file"]["error"]]);
        } else {
            $tagetFile = $tagetDir . basename($_FILES["file"]["name"]);
            $category_id = $_POST["category_id"];
            $name = $_POST["name"];
            $price = $_POST["price"];
            $description = $_POST["description"];
            $availability = $_POST["availability"];
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $tagetFile)) {
                $stmt = $conn->prepare("INSERT INTO menu_items (category_id, name, price, description, availability, img) VALUES (?, ?, ?, ?, ?, ?)");
                if (!$stmt) {
                    echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
                } else {
                    $stmt->bind_param("isdsis", $category_id, $name, $price, $description, $availability, $tagetFile);
                    if ($stmt->execute()) {
                        echo json_encode(["status" => "success"]);
                    } else {
                        echo json_encode(["status" => "error", "message" => "Execution failed: " . $stmt->error]);
                    }
                    $stmt->close();
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Sorry, there was an error uploading your file."]);
            }
        }
    } else {
        echo json_encode(["status" => "error", "message" => "No file uploaded or incorrect key."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method", "method" => $_SERVER['REQUEST_METHOD']]);
}

$conn->close();
?>
