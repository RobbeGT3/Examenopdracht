<?php
require_once __DIR__ . '/../../common/dbconnection.php';

header('Content-Type: application/json');
$rawData = file_get_contents("php://input");

$data = json_decode($rawData, true);
$voedselpakketId = $data['id'];
$uitgaveDatum = date("Y-m-d");

$stmt = $conn->prepare('UPDATE Voedselpakketten v SET v.uitgifte_datum = ? WHERE v.idVoedselpakketten = ?');
$stmt->bind_param("si", $uitgaveDatum ,$voedselpakketId);
$stmt->execute();

