<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once  __DIR__. '/../common/dbconnection.php';

$data = json_encode($_POST);

file_put_contents('test.txt', $data);

$id = $_POST['id'];

$stmt = $conn->prepare("
UPDATE Klanten 
SET voornaam=?, achternaam=?, adres=?, postcode=?, woonplaats=?, telefoonnummer=?, `e-mailadres`=?, aantal_volwassen=?, aantal_kinderen=?, `aantal_baby's`=?
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

// $stmt->execute();


?>
