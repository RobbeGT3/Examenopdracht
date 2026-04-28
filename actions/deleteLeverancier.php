<?php

session_start();
require_once __DIR__ . '/../common/dbconnection.php';

header('Content-Type: application/json');

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$id = $data['id'] ?? '';

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Leverancier ID ontbreekt']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM Leverancier WHERE idLeverancier = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Leverancier succesvol verwijderd']);
} else {
    echo json_encode(['success' => false, 'message' => 'Fout bij verwijderen leverancier: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
