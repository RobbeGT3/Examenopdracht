<?php

session_start();
require_once  __DIR__. '/../common/dbconnection.php';
$gebruikersnaam = $_POST['username'];
$wachtwoord = $_POST['password'];

$salt = "9Q3z8T";
$saltedWachtwoord = $wachtwoord.$salt;

$stmt = $conn->prepare("SELECT g.idGebruiker, g.username, g.password, g.`status`, r.rolnaam FROM Gebruiker g 
INNER JOIN Gebruikerrollen r ON r.idGebruikerrollen = g.Gebruikerrollen_idGebruikerrollen 
WHERE g.username = ? AND g.`status` = 'Actief';");

// $stmt = $conn->prepare("SELECT g.idGebruiker, g.username, g.password, g.`status`, r.rolnaam FROM Gebruiker g 
// INNER JOIN Gebruikerrollen r ON r.idGebruikerrollen = g.Gebruikerrollen_idGebruikerrollen 
// WHERE g.email = ? AND g.`status` = 'Actief';");
$stmt->bind_param("s", $gebruikersnaam);
$stmt->execute();

$result = $stmt->get_result();
$gebruiker = $result->fetch_assoc();   

if ($gebruiker && password_verify($saltedWachtwoord, $gebruiker['password'])) {
    header('location: ../dashboard.php');
    $_SESSION['is_logged_in'] = true;
    $_SESSION['userrole'] = $gebruiker['rolnaam'];
    $_SESSION['username'] = $gebruiker['username'];
    exit;
} else {
    header('location: ../index.php?error=Verkeerde Inloggegevens');
    exit;
}

$stmt->close();
$conn->close();

?>