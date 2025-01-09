<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: PUT");

include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$tagetDir = "upload/qr-code/";

if (isset($data)) {
    
$name = $data['name'];
$floor = $data['floor'];
$status = $data['status'];
$id = $data['table_id'];
    if (isset($_FILES['file'])) {
        if ($_FILES["file"]["error"] > 0) {
            echo json_encode(["status" => "error", "message" => "Error" . $_FILES["file"]["error"]]);
        } else {
            $tagetFile = $tagetDir . basename($_FILES["file"]["name"]);
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $tagetFile)) {
                $stmt = $conn->prepare("UPDATE tables SET name = ?, floor = ?, status = ?, qr_code = ? WHERE table_id = ?");
                if (!$stmt) {
                    echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
                } else {
                    $stmt->bind_param("siisi", $name, $floor, $status, $tagetFile, $id);
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
        $stmt = $conn->prepare("UPDATE tables SET name = ?, floor = ?, status = ? WHERE table_id = ?");
        if (!$stmt) {
            echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        } else {
            $stmt->bind_param("siii", $name, $floor, $status, $id);
            if ($stmt->execute()) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Execution failed: " . $stmt->error]);
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>
