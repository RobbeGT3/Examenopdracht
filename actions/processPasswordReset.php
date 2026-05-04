<?php

require_once __DIR__ . '/../common/dbconnection.php';

header('Content-Type: application/json');

$token = $_POST['token'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';

if (empty($token) || empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => 'Token of wachtwoord ontbreekt']);
    exit;
}

if (strlen($newPassword) < 6) {
    echo json_encode(['success' => false, 'message' => 'Wachtwoord moet minimaal 6 tekens bevatten']);
    exit;
}

// Controleer of token geldig is en niet verlopen
$stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Ongeldige of verlopen reset link. Vraag een nieuwe link aan.']);
    $stmt->close();
    $conn->close();
    exit;
}

$resetData = $result->fetch_assoc();
$stmt->close();

// Controleer of token niet verlopen is
if (strtotime($resetData['expires_at']) < time()) {
    echo json_encode(['success' => false, 'message' => 'Reset link is verlopen. Vraag een nieuwe link aan.']);
    $conn->close();
    exit;
}

$email = $resetData['email'];

// Hash het nieuwe wachtwoord
$salt = "9Q3z8T";
$saltedPassword = $newPassword . $salt;
$hashedPassword = password_hash($saltedPassword, PASSWORD_DEFAULT);

// Update wachtwoord in Klanten tabel
$stmt = $conn->prepare("UPDATE Klanten SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $hashedPassword, $email);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Fout bij bijwerken wachtwoord: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();

// Verwijder de gebruikte token
$stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->close();

$conn->close();

echo json_encode(['success' => true, 'message' => 'Wachtwoord succesvol gewijzigd! U kunt nu inloggen met uw nieuwe wachtwoord.']);
?>
