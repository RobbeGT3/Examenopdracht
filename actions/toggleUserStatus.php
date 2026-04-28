<?php

session_start();
require_once __DIR__ . '/../common/dbconnection.php';

header('Content-Type: application/json');

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$username = $data['username'] ?? '';
$status = $data['status'] ?? '';

if (empty($username) || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Gebruikersnaam of status ontbreekt']);
    exit;
}

if (!in_array($status, ['Actief', 'Inactief'])) {
    echo json_encode(['success' => false, 'message' => 'Ongeldige status']);
    exit;
}

$stmt = $conn->prepare("UPDATE Gebruiker SET status = ? WHERE username = ?");
$stmt->bind_param("ss", $status, $username);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Status succesvol gewijzigd']);
} else {
    echo json_encode(['success' => false, 'message' => 'Fout bij wijzigen status: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
