<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once  __DIR__. '/../common/dbconnection.php';

$id = $_POST['id'];
$stmt = $conn->prepare("
UPDATE Klanten 
SET voornaam=?, achternaam=?, adres=?, postcode=?, woonplaats=?, telefoonnummer=?, `e-mailadres`=?, aantal_volwassen=?, aantal_kinderen=?, aantal_babies=?
WHERE idKlanten=?
");
$stmt->bind_param(
  "sssssssiiii",
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
  $id
);
$stmt->execute();

if (!empty($_POST['allergieën_toegevoegd'])) {
    $toegevoegdAllergenen = json_decode($_POST['allergieën_toegevoegd'], true);
    if (!empty($toegevoegdAllergenen)) {
        $stmtInsertAllergie = $conn->prepare("
        INSERT INTO Klanten_allergenen (Klanten_idKlanten, omschrijving)
        VALUES (?, ?)
        ");

        foreach ($toegevoegdAllergenen as $a) {
            $stmtInsertAllergie->bind_param("is", $id, $a);
            $stmtInsertAllergie->execute(); 
        }
    }
}

if (!empty($_POST['allergieën_verwijderd'])) {
    $verwijderdAllergenen = json_decode($_POST['allergieën_verwijderd'], true);
    if (!empty($verwijderdAllergenen)) {
        $stmtDeleteAllergie = $conn->prepare("
        DELETE FROM Klanten_allergenen 
        WHERE Klanten_idKlanten = ? AND omschrijving = ?
        ");
        foreach ($verwijderdAllergenen as $a) {
            $stmtDeleteAllergie->bind_param("is", $id, $a);
            $stmtDeleteAllergie->execute(); 
            
        }
    }
}

if (!empty($_POST['wensen_toegevoegd'])) {
    $toegevoegdWensen = json_decode($_POST['wensen_toegevoegd'], true);
    if (!empty($toegevoegdWensen)) {
        $stmtInsertWens = $conn->prepare("
        INSERT INTO Klanten_has_Klantenwensen 
        (Klanten_idKlanten, Klantenwensen_idKlantenwensen)
        VALUES (?, ?)
        ");

        foreach ($toegevoegdWensen as $wensId) {
            $stmtInsertWens->bind_param("ii", $id, $wensId);
            $stmtInsertWens->execute(); 
            
        }
    }
}

if (!empty($_POST['wensen_verwijderd'])) {
    $verwijderdWensen = json_decode($_POST['wensen_verwijderd'], true);
    if (!empty($verwijderdWensen)) {
        $stmtDeleteWens = $conn->prepare("
            DELETE FROM Klanten_has_Klantenwensen 
            WHERE Klanten_idKlanten = ? 
            AND Klantenwensen_idKlantenwensen = ?
        ");

        foreach ($verwijderdWensen as $wensId) {
            $stmtDeleteWens->bind_param("ii", $id, $wensId);
            $stmtDeleteWens->execute(); 
            
        }
    }
}





?>
