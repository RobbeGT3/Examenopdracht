<?php

session_start();
require_once  __DIR__. '/../common/dbconnection.php';

$gebruikersnaam = $_POST['username'];
$wachtwoord = $_POST['password'];
$email = $_POST['email'];
$rol = $_POST['role'];

// Rol toewijzen aan database ID
$roleMapping = [
    'directeur' => 1,
    'magazijnmedewerker' => 2,
    'vrijwilliger' => 3
];

$roleId = $roleMapping[$rol] ?? 1; // Standaard directeur als niet gevonden

$salt = "9Q3z8T";
$saltedWachtwoord = $wachtwoord.$salt;
$hashedPassword = password_hash($saltedWachtwoord, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO Gebruiker (username, password, email, status, Gebruikerrollen_idGebruikerrollen) VALUES (?, ?, ?, 'Actief', ?)");
$stmt->bind_param("sssi", $gebruikersnaam, $hashedPassword, $email, $roleId);

$stmt->execute();

$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'message' => 'Gebruiker succesvol aangemaakt']);
?>