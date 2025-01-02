<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'db.php';

// Lấy giá trị category_id từ tham số GET
$floor = isset($_GET['floor']) ? $_GET['floor'] : -1;
$status = isset($_GET['status']) ? $_GET['status'] : -1;

if ($floor != -1 && $status != -1) {
    $sql = "SELECT * FROM tables WHERE floor = $floor AND status = $status";
} elseif ($floor != -1) {
    $sql = "SELECT * FROM tables WHERE floor = $floor";
} elseif ($status != -1) {
    $sql = "SELECT * FROM tables WHERE status = $status";
} else {
    $sql = "SELECT * FROM tables";
}




$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Đưa dữ liệu vào mảng
    $menus = array();
    while ($row = $result->fetch_assoc()) {
        $menus[] = $row;
    }
    echo json_encode($menus);  // Trả về dữ liệu dưới dạng JSON
} else {
    echo json_encode([]);  // Nếu không có dữ liệu, trả về mảng rỗng
}

$conn->close();
?>
