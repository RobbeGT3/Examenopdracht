<?php

session_start();
require_once  __DIR__. '/../common/dbconnection.php';

header('Content-Type: application/json');

// Haal formuliergegevens op
$originalUsername = $_POST['originalUsername'] ?? '';
$newUsername = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$rol = $_POST['role'] ?? '';
$wachtwoord = $_POST['password'] ?? '';

if (empty($originalUsername)) {
    echo json_encode(['success' => false, 'message' => 'Originele gebruikersnaam ontbreekt']);
    exit;
}

// Rol toewijzen aan database ID
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

// Huidige gebruikersgegevens ophalen
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

// Bijhouden welke velden gewijzigd zijn
$updates = [];
$types = "";
$values = [];

// Controleer of gebruikersnaam gewijzigd is
if ($newUsername !== $currentUser['username']) {
    $updates[] = "username = ?";
    $types .= "s";
    $values[] = $newUsername;
}

// Controleer of e-mail gewijzigd is
if ($email !== $currentUser['email']) {
    $updates[] = "email = ?";
    $types .= "s";
    $values[] = $email;
}

// Controleer of rol gewijzigd is
if ($roleId != $currentUser['Gebruikerrollen_idGebruikerrollen']) {
    $updates[] = "Gebruikerrollen_idGebruikerrollen = ?";
    $types .= "i";
    $values[] = $roleId;
}

// Controleer of wachtwoord geüpdatet moet worden (alleen als niet leeg)
if (!empty($wachtwoord)) {
    $salt = "9Q3z8T";
    $saltedWachtwoord = $wachtwoord.$salt;
    $hashedPassword = password_hash($saltedWachtwoord, PASSWORD_DEFAULT);
    
    $updates[] = "password = ?";
    $types .= "s";
    $values[] = $hashedPassword;
}

// Als er geen velden gewijzigd zijn, geef succes terug zonder actie
if (empty($updates)) {
    echo json_encode(['success' => true, 'message' => 'Geen wijzigingen doorgevoerd', 'changed' => false]);
    exit;
}

// Update query bouwen en uitvoeren
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
