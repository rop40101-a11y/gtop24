<?php
// Example API Endpoint
header('Content-Type: application/json');
include '../../common/config.php';

$prods = [];
$res = $conn->query("SELECT * FROM products");
while($r = $res->fetch_assoc()) {
    $prods[] = $r;
}
echo json_encode(['status'=>'success', 'data'=>$prods]);
?>