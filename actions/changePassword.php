<?php

session_start();
require_once __DIR__ . '/../common/dbconnection.php';

header('Content-Type: application/json');

// Haal formuliergegevens op
$username = $_POST['username'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';

if (empty($username) || empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => 'Gebruikersnaam of wachtwoord ontbreekt']);
    exit;
}

if (strlen($newPassword) < 6) {
    echo json_encode(['success' => false, 'message' => 'Wachtwoord moet minimaal 6 tekens bevatten']);
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

// Hash het nieuwe wachtwoord
$salt = "9Q3z8T";
$saltedPassword = $newPassword . $salt;
$hashedPassword = password_hash($saltedPassword, PASSWORD_DEFAULT);

// Update het wachtwoord
$stmt = $conn->prepare("UPDATE Gebruiker SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $hashedPassword, $username);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Wachtwoord succesvol gewijzigd']);
} else {
    echo json_encode(['success' => false, 'message' => 'Fout bij wijzigen wachtwoord: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
