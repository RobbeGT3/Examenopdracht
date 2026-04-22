<?php
require_once __DIR__ . '/../../common/dbconnection.php';

header('Content-Type: application/json');

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);
$id = $data['id'];

$stmt1 = $conn->prepare("DELETE FROM Klanten_allergenen WHERE Klanten_idKlanten = ?");
$stmt1->bind_param("i", $id);
$stmt1->execute();
$stmt2 = $conn->prepare("DELETE FROM Klanten_has_Klantenwensen WHERE Klanten_idKlanten = ?");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$stmt3 = $conn->prepare("DELETE FROM Klanten WHERE idKlanten = ?");
$stmt3->bind_param("i", $id);
$stmt3->execute();

$stmt1->close();
$stmt2->close();
$stmt3->close();
$conn->close();