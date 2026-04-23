<?php

session_start();
require_once  __DIR__. '/../common/dbconnection.php';

header('Content-Type: application/json');

// Get form data
$originalUsername = $_POST['originalUsername'] ?? '';
$newUsername = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$rol = $_POST['role'] ?? '';
$wachtwoord = $_POST['password'] ?? '';

if (empty($originalUsername)) {
    echo json_encode(['success' => false, 'message' => 'Originele gebruikersnaam ontbreekt']);
    exit;
}

// Map role to database ID
$roleMapping = [
    'directeur' => 1,
    'magazijnmedewerker' => 2,
    'vrijwilliger' => 3
];

$roleId = $roleMapping[$rol] ?? null;

if ($roleId === null) {
    echo json_encode(['success' => false, 'message' => 'Ongeldige rol geselecteerd']);
    exit;
}

// Fetch current user data
$stmt = $conn->prepare("SELECT username, email, Gebruikerrollen_idGebruikerrollen FROM Gebruiker WHERE username = ?");
$stmt->bind_param("s", $originalUsername);
$stmt->execute();
$result = $stmt->get_result();
$currentUser = $result->fetch_assoc();
$stmt->close();

if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Gebruiker niet gevonden']);
    exit;
}

// Track which fields changed
$updates = [];
$types = "";
$values = [];

// Check if username changed
if ($newUsername !== $currentUser['username']) {
    $updates[] = "username = ?";
    $types .= "s";
    $values[] = $newUsername;
}

// Check if email changed
if ($email !== $currentUser['email']) {
    $updates[] = "email = ?";
    $types .= "s";
    $values[] = $email;
}

// Check if role changed
if ($roleId != $currentUser['Gebruikerrollen_idGebruikerrollen']) {
    $updates[] = "Gebruikerrollen_idGebruikerrollen = ?";
    $types .= "i";
    $values[] = $roleId;
}

// Check if password should be updated (only if not empty)
if (!empty($wachtwoord)) {
    $salt = "9Q3z8T";
    $saltedWachtwoord = $wachtwoord.$salt;
    $hashedPassword = password_hash($saltedWachtwoord, PASSWORD_DEFAULT);
    
    $updates[] = "password = ?";
    $types .= "s";
    $values[] = $hashedPassword;
}

// If no fields changed, return success without doing anything
if (empty($updates)) {
    echo json_encode(['success' => true, 'message' => 'Geen wijzigingen doorgevoerd', 'changed' => false]);
    exit;
}

// Build and execute update query
$query = "UPDATE Gebruiker SET " . implode(", ", $updates) . " WHERE username = ?";
$types .= "s";
$values[] = $originalUsername;

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Gebruiker succesvol bijgewerkt', 'changed' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Fout bij bijwerken gebruiker: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
