<?php
require_once __DIR__ . '/../../common/dbconnection.php';

header('Content-Type: application/json');

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);
$id = $data['id'];

$stmt1 = $conn->prepare("UPDATE Klanten k SET k.`status` = 'Actief' WHERE idKlanten = ?");
$stmt1->bind_param("i", $id);
$stmt1->execute();

$stmt1->close();
$conn->close();