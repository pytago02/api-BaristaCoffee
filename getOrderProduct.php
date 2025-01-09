<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Nhận tham số từ GET request
    $start_date = $_GET["start_date"];
    $end_date = $_GET["end_date"] . ' 23:59:59'; // Đảm bảo rằng kết thúc ngày là cuối ngày
    $filter_type = $_GET["filter_type"];

    // Xây dựng câu truy vấn SQL tùy theo filter_type
    $sql = "";

    // Truy vấn theo ngày, tuần hoặc tháng
    if ($filter_type == 'date') {
        // Truy vấn theo ngày
        $sql = "
            SELECT od.item_id, mi.name AS item_name, SUM(od.quantity) AS total_quantity
            FROM Payments p
            JOIN Orders o ON p.order_id = o.order_id
            JOIN Order_Details od ON p.order_id = od.order_id
            JOIN Menu_Items mi ON od.item_id = mi.item_id
            WHERE p.paid_at BETWEEN ? AND ? 
            GROUP BY od.item_id, mi.name
            ORDER BY total_quantity DESC
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
    } elseif ($filter_type == 'week') {
        // Truy vấn theo tuần
        $start_year = substr($start_date, 0, 4);
        $start_week = substr($start_date, 6, 2);
        $end_year = substr($end_date, 0, 4);
        $end_week = substr($end_date, 6, 2);

        if ($start_year === $end_year) {
            // Nếu cùng năm, truy vấn tuần trong năm đó
            $sql = "
                SELECT od.item_id, mi.name AS item_name, SUM(od.quantity) AS total_quantity
                FROM Payments p
                JOIN Orders o ON p.order_id = o.order_id
                JOIN Order_Details od ON p.order_id = od.order_id
                JOIN Menu_Items mi ON od.item_id = mi.item_id
                WHERE YEAR(p.paid_at) = ? AND WEEK(p.paid_at, 1) BETWEEN ? AND ?
                GROUP BY od.item_id, mi.name
                ORDER BY total_quantity DESC
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $start_year, $start_week, $end_week);
        } else {
            // Nếu khác năm, cần truy vấn cho từng năm
            $sql = "
                SELECT od.item_id, mi.name AS item_name, SUM(od.quantity) AS total_quantity
                FROM Payments p
                JOIN Orders o ON p.order_id = o.order_id
                JOIN Order_Details od ON p.order_id = od.order_id
                JOIN Menu_Items mi ON od.item_id = mi.item_id
                WHERE 
                    (YEAR(p.paid_at) = ? AND WEEK(p.paid_at, 1) >= ?)
                    OR
                    (YEAR(p.paid_at) = ? AND WEEK(p.paid_at, 1) <= ?)
                GROUP BY od.item_id, mi.name
                ORDER BY total_quantity DESC
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiii", $start_year, $start_week, $end_year, $end_week);
        }
    } elseif ($filter_type == 'month') {
        // Truy vấn theo tháng
        $start_year = substr($start_date, 0, 4);
        $start_month = substr($start_date, 5, 2);
        $end_year = substr($end_date, 0, 4);
        $end_month = substr($end_date, 5, 2);

        if ($start_year === $end_year) {
            // Nếu cùng năm, truy vấn tháng trong năm đó
            $sql = "
                SELECT od.item_id, mi.name AS item_name, SUM(od.quantity) AS total_quantity
                FROM Payments p
                JOIN Orders o ON p.order_id = o.order_id
                JOIN Order_Details od ON p.order_id = od.order_id
                JOIN Menu_Items mi ON od.item_id = mi.item_id
                WHERE YEAR(p.paid_at) = ? AND MONTH(p.paid_at) BETWEEN ? AND ?
                GROUP BY od.item_id, mi.name
                ORDER BY total_quantity DESC
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $start_year, $start_month, $end_month);
        } else {
            // Nếu khác năm, cần truy vấn cho từng năm
            $sql = "
                SELECT od.item_id, mi.name AS item_name, SUM(od.quantity) AS total_quantity
                FROM Payments p
                JOIN Orders o ON p.order_id = o.order_id
                JOIN Order_Details od ON p.order_id = od.order_id
                JOIN Menu_Items mi ON od.item_id = mi.item_id
                WHERE 
                    (YEAR(p.paid_at) = ? AND MONTH(p.paid_at) >= ?)
                    OR
                    (YEAR(p.paid_at) = ? AND MONTH(p.paid_at) <= ?)
                GROUP BY od.item_id, mi.name
                ORDER BY total_quantity DESC
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiii", $start_year, $start_month, $end_year, $end_month);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid filter type"]);
        exit;
    }

    // Execute the query and handle the result
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    } else {
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }

            echo json_encode(["status" => "success", "data" => $items]);
        } else {
            echo json_encode(["status" => "error", "message" => "No data found"]);
        }

        $stmt->close();
    }

} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

$conn->close();
?>
