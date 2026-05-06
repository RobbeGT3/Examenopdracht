<?php
session_start();
require_once __DIR__ . '/../common/dbconnection.php';
header('Content-Type: application/json');

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!isset($data['bedrijfsnaam'], $data['contactpersoon'], $data['email'], $data['telefoonnummer'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ontbrekende verplichte velden']);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO Leverancier 
    (bedrijfsnaam, adres, contactpersoon, email, telefoonnummer, eerstvolgende_levering, leverfrequentie)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssssss",
    $data['bedrijfsnaam'],
    $data['adres'],
    $data['contactpersoon'],
    $data['email'],
    $data['telefoonnummer'],
    $data['eerstvolgende_levering'],
    $data['leverfrequentie']
);

if ($stmt->execute()) {
    $id = $stmt->insert_id;
    echo json_encode(['success' => true, 'id' => $id, 'message' => 'Leverancier toegevoegd']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database fout: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
