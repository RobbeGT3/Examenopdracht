<?php

session_start();
require_once __DIR__ . '/../common/dbconnection.php';

header('Content-Type: application/json');

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$bedrijfsnaam = $data['bedrijfsnaam'] ?? '';
$adres = $data['adres'] ?? '';
$contactpersoon = $data['contactpersoon'] ?? '';
$email = $data['email'] ?? '';
$telefoon = $data['telefoon'] ?? '';
$volgende_levering = $data['volgende_levering'] ?? '';
$leverfrequentie = $data['leverfrequentie'] ?? '';

if (empty($bedrijfsnaam) || empty($contactpersoon) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Verplichte velden ontbreken']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO Leverancier (bedrijfsnaam, adres, contactpersoon, email, telefoon, volgende_levering, leverfrequentie) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $bedrijfsnaam, $adres, $contactpersoon, $email, $telefoon, $volgende_levering, $leverfrequentie);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Leverancier succesvol toegevoegd']);
} else {
    echo json_encode(['success' => false, 'message' => 'Fout bij toevoegen leverancier: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
