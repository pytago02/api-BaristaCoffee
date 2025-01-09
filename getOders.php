<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'db.php';  // Đảm bảo đã có kết nối cơ sở dữ liệu

// Lấy table_id từ tham số GET
$table_id = isset($_GET['table_id']) ? (int) $_GET['table_id'] : 0;  // Mặc định là 0 nếu không có
// echo json_encode([$table_id]);

// Kiểm tra xem table_id có hợp lệ không
if ($table_id <= 0) {
    echo json_encode(["error" => "Invalid table_id"]);
    exit();
}

// Câu lệnh SQL để lấy hóa đơn chưa thanh toán
$sql = "
    SELECT 
        o.order_id, 
        o.table_id, 
        o.order_status, 
        o.created_at,
        GROUP_CONCAT(CONCAT(mi.name, ' (', od.quantity, ' x ', od.price, ')') SEPARATOR ', ') AS items,
        SUM(od.quantity * od.price) AS total_amount
    FROM 
        orders o
    LEFT JOIN  
        order_details od ON o.order_id = od.order_id
    LEFT JOIN  
        menu_items mi ON od.item_id = mi.item_id
    LEFT JOIN 
        payments p ON o.order_id = p.order_id
    WHERE 
        o.table_id = ?
        AND p.order_id IS NULL
    GROUP BY 
        o.order_id, o.table_id, o.order_status, o.created_at;
";

// Sử dụng prepared statement để tránh SQL Injection
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $table_id);  // Liên kết table_id với câu lệnh SQL

$stmt->execute();
$result = $stmt->get_result();

// Kiểm tra nếu có dữ liệu
if ($result->num_rows > 0) {
    // Đưa dữ liệu vào mảng
    $invoices = array();
    while ($row = $result->fetch_assoc()) {
        $invoices[] = $row;
    }
    echo json_encode($invoices);  // Trả về dữ liệu dưới dạng JSON
} else {
    echo json_encode([]);  // Nếu không có hóa đơn chưa thanh toán, trả về mảng rỗng
}

// Đóng kết nối
$stmt->close();
$conn->close();
?>