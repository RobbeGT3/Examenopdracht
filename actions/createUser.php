<?php

session_start();
require_once  __DIR__. '/../common/dbconnection.php';

$gebruikersnaam = $_POST['username'];
$wachtwoord = $_POST['password'];
$salt = "9Q3z8T";
$saltedWachtwoord = $wachtwoord.$salt;
$hashedPassword = password_hash($saltedWachtwoord, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO Gebruiker (username, password, status, Gebruikerrollen_idGebruikerrollen) VALUES (?, ?, 'Actief', 1)");
$stmt->bind_param("ss", $gebruikersnaam, $hashedPassword);

$stmt->execute();

$stmt->close();
$conn->close();


?>