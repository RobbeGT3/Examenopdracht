<?php

session_start();
require_once  __DIR__. '/../common/dbconnection.php';

$gebruikersnaam = $_POST['username'];
$wachtwoord = $_POST['password'];
$email = $_POST['email'];
$salt = "9Q3z8T";
$saltedWachtwoord = $wachtwoord.$salt;

$hashedPassword = password_hash($saltedWachtwoord, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO Gebruiker (username, email, password, status, Gebruikerrollen_idGebruikerrollen) VALUES (?,? ?, 'Actief', ?)");
$stmt->bind_param("sssi", $gebruikersnaam,$email, $hashedPassword,$_POST['role']);
$stmt->execute();

$stmt->close();
$conn->close();


?>