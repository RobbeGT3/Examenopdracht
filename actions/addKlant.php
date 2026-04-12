<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once  __DIR__. '/../common/dbconnection.php';

$stmt = $conn->prepare("
INSERT INTO Klanten 
(voornaam, achternaam, adres, postcode, woonplaats, telefoonnummer, `e-mailadres`, aantal_volwassen, aantal_kinderen,`aantal_baby's`,`status`,registratie_datum)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Goedgekeurd', ?)
");
$registratieDatum = date('Y-m-d');;

$stmt->bind_param(
  "sssssssiiis",
  $_POST['voornaam'],
  $_POST['achternaam'],
  $_POST['adres'],
  $_POST['postcode'],
  $_POST['woonplaats'],
  $_POST['telefoonnummer'],
  $_POST['email'],
  $_POST['volwassenen'],
  $_POST['kinderen'],
  $_POST['babys'],
  $registratieDatum
);

$stmt->execute();
$klantId = $stmt->insert_id;

if (!empty($_POST['wensen'])) {
  foreach ($_POST['wensen'] as $wensId) {
    $stmt2 = $conn->prepare("
      INSERT INTO Klanten_has_Klantenwensen 
      (Klanten_idKlanten, Klantenwensen_idKlantenwensen)
      VALUES (?, ?)
    ");
    $stmt2->bind_param("ii", $klantId, $wensId);
    $stmt2->execute();
  }
}

$allergieën = json_decode($_POST['allergieën'], true);

if (!empty($allergieën)) {
  foreach ($allergieën as $allergie) {
    $stmt3 = $conn->prepare("
      INSERT INTO Klanten_allergenen 
      (Klanten_idKlanten, omschrijving)
      VALUES (?, ?)
    ");
    $stmt3->bind_param("is", $klantId, $allergie);
    $stmt3->execute();
  }
}

?>