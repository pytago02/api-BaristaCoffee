<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'db.php';

// Nhận các tham số từ phía frontend
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : -1;
$status = isset($_GET['status']) ? $_GET['status'] : -1;
$min = isset($_GET['min']) ? $_GET['min'] : null;
$max = isset($_GET['max']) ? $_GET['max'] : null;
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : ''; 

// Xây dựng câu lệnh SQL dựa vào các tham số nhận được
$sql = "SELECT * FROM menu_items WHERE 1";

// Điều kiện cho category_id
if ($category_id != -1) {
    $sql .= " AND category_id = $category_id";
}

// Điều kiện cho status
if ($status != -1) {
    $sql .= " AND availability = $status";
}

// Điều kiện cho min (giá trị nhỏ nhất)
if ($min !== "null") {
    $sql .= " AND price >= $min";  // Giả sử 'price' là tên cột giá của menu_items
}

if ($max !== "null") {
    $sql .= " AND price <= $max";  // Giả sử 'price' là tên cột giá của menu_items
}

// Điều kiện cho từ khóa tìm kiếm
if (!empty($keyword)) {
    // Giả sử bạn muốn tìm kiếm từ khóa trong tên món ăn
    $sql .= " AND name LIKE '%$keyword%'";
}

// echo $sql;

// Thực thi câu lệnh SQL
$result = $conn->query($sql);

// Kiểm tra nếu có kết quả trả về
if ($result->num_rows > 0) {

    $menus = array();
    while ($row = $result->fetch_assoc()) {
        $menus[] = $row;
    }

    // Trả kết quả dưới dạng JSON
    echo json_encode($menus); 
} else {
    // Trả về mảng rỗng nếu không có kết quả
    echo json_encode([]);  
}

$conn->close();
?>
