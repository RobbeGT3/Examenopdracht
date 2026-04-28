<?php

session_start();
require_once __DIR__ . '/../common/dbconnection.php';

header('Content-Type: application/json');

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$id = $data['id'] ?? '';
$bedrijfsnaam = $data['bedrijfsnaam'] ?? '';
$adres = $data['adres'] ?? '';
$contactpersoon = $data['contactpersoon'] ?? '';
$email = $data['email'] ?? '';
$telefoon = $data['telefoon'] ?? '';
$volgende_levering = $data['volgende_levering'] ?? '';
$leverfrequentie = $data['leverfrequentie'] ?? '';

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Leverancier ID ontbreekt']);
    exit;
}

$stmt = $conn->prepare("UPDATE Leverancier SET bedrijfsnaam = ?, adres = ?, contactpersoon = ?, email = ?, telefoon = ?, volgende_levering = ?, leverfrequentie = ? WHERE idLeverancier = ?");
$stmt->bind_param("sssssssi", $bedrijfsnaam, $adres, $contactpersoon, $email, $telefoon, $volgende_levering, $leverfrequentie, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Leverancier succesvol bijgewerkt']);
} else {
    echo json_encode(['success' => false, 'message' => 'Fout bij bijwerken leverancier: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
