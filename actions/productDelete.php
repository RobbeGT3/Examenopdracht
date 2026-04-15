<?php

session_start();
require_once  __DIR__. '/../common/dbconnection.php';


$rawData = file_get_contents("php://input");

$data = json_decode($rawData, true); // true = array
$id = $data['id'];

$stmtDeleteProduct= $conn->prepare('DELETE FROM Products p WHERE p.idProducts = ?; ');
$stmtDeleteProduct->bind_param("i", $id);
$stmtDeleteProduct->execute();

$stmtDeleteProduct->close();
$conn->close();

?>