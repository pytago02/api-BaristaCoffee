<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'db.php';

$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : -1;
$status = isset($_GET['status']) ? $_GET['status'] : -1;

if($category_id != -1 && $status != -1){
    $sql = "SELECT * FROM menu_items WHERE category_id = $category_id AND availability = $status";
} else if($category_id != -1 && $status == -1){
    $sql = "SELECT * FROM menu_items WHERE category_id = $category_id";
}
else if($category_id == -1 && $status != -1){
    $sql = "SELECT * FROM menu_items WHERE availability = $status";
} else {
    // Nếu không có category_id, lấy tất cả menu items
    $sql = "SELECT * FROM menu_items";
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
