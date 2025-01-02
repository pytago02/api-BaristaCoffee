<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'db.php';

$sql = "SELECT * FROM tables";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $tables = array();
    while ($row = $result->fetch_assoc()) {
        $tables[] = $row;
    }
    echo json_encode($tables);
} else {
    echo json_encode([]);
}

$conn->close();

?>