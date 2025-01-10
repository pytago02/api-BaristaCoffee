<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'db.php';

$table_id = isset($_GET['table_id']) ? (int) $_GET['table_id'] : 0;  // Mặc định là 0 nếu không có
// echo json_encode([$table_id]);

if ($table_id <= 0) {
    echo json_encode(["error" => "Invalid table_id"]);
    exit();
}

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

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $table_id);

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $invoices = array();
    while ($row = $result->fetch_assoc()) {
        $invoices[] = $row;
    }
    echo json_encode($invoices);
} else {
    echo json_encode([]);
}

$stmt->close();
$conn->close();
?>