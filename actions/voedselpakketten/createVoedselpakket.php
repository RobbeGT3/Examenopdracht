<?php
require_once __DIR__ . '/../../common/dbconnection.php';

header('Content-Type: application/json');

$rawData = file_get_contents("php://input");

$data = json_decode($rawData, true);

$klantId = $data['klantId'];
$producten = $data['producten'];
$samenstelling_datum = date("Y-m-d");

$stmt = $conn->prepare('INSERT INTO Voedselpakketten (klanten_idKlanten, samenstellings_datum, uitgifte_datum)
        VALUES (?, ?, NULL)');
$stmt->bind_param("is", $klantId, $samenstelling_datum);
$stmt->execute();
$voedselpakketId = $stmt->insert_id;


$stmtProduct = $conn->prepare('INSERT INTO Voedselpakketten_has_Products
               (Voedselpakketten_idVoedselpakketten, Products_idProducts, aantal)
               VALUES (?, ?, ?)');

foreach($producten as $product){
    $productId = $product['id'];
    $aantal = $product['amount'];

    $stmtProduct->bind_param("iii", $voedselpakketId, $productId, $aantal);
    $stmtProduct->execute();
}

$stmt->close();
$stmtProduct->close();
$conn->close();