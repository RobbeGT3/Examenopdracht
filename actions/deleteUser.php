<?php

session_start();
require_once __DIR__ . '/../common/dbconnection.php';

header('Content-Type: application/json');

// Haal gebruikersnaam op uit POST data
$username = $_POST['username'] ?? '';

if (empty($username)) {
    echo json_encode(['success' => false, 'message' => 'Gebruikersnaam ontbreekt']);
    exit;
}

// Controleer of gebruiker bestaat
$stmt = $conn->prepare("SELECT username FROM Gebruiker WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Gebruiker niet gevonden']);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();

// Verwijder de gebruiker
$stmt = $conn->prepare("DELETE FROM Gebruiker WHERE username = ?");
$stmt->bind_param("s", $username);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Gebruiker succesvol verwijderd']);
} else {
    echo json_encode(['success' => false, 'message' => 'Fout bij verwijderen gebruiker: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
