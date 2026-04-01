<?php

// echo '<pre>';
// var_dump($_POST);
// echo '</pre>';
// exit;

session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once  __DIR__. '/../common/dbconnection.php';
$gebruikersnaam = $_POST['username'];
$wachtwoord = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM Gebruiker WHERE username = ? AND password = ? AND status = 'Actief'");
$stmt->bind_param("ss", $gebruikersnaam, $wachtwoord);
$stmt->execute();

$result = $stmt->get_result();
$gebruiker = $result->fetch_assoc();

if ($result->num_rows > 0) {
    header('location: ../page2.php');
    $_SESSION['is_logged_in'] = true;
    $_SESSION['userrole'] = $gebruiker['Gebruikerrollen_idGebruikerrollen'];
    exit;
}else{
    header('location: ../index.php?error=Verkeerde Inloggegevens');
    exit;
}
    

// if ($gebruiker && password_verify($wachtwoord, $gebruiker['password'])) {
//     header('location: voorraad.php');
//     $_SESSION['is_logged_in'] = true;
//     $_SESSION['userrole'] = $gebruiker['Role'];
//     exit;
// } else {
//     header('location: index.php?error=Verkeerde Inloggegevens');
// }

$stmt->close();
$conn->close();

?>