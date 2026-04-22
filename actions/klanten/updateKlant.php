<?php

require_once __DIR__ . '/../../common/dbconnection.php';
header('Content-Type: application/json');

$rawData = file_get_contents("php://input");

$data = json_decode($rawData, true);

$id = $data['id'];
$gezinsnaam = 'Familie '. $data['achternaam'];

$stmt = $conn->prepare("
    UPDATE Klanten SET
        gezinsnaam = ?,
        voornaam = ?,
        achternaam = ?,
        adres = ?,
        postcode = ?,
        woonplaats = ?,
        telefoonnummer = ?,
        `e-mailadres` = ?,
        aantal_volwassen = ?,
        aantal_kinderen = ?,
        aantal_babies = ?
    WHERE idKlanten = ?
");

$stmt->execute([
    $gezinsnaam,
    $data['voornaam'],
    $data['achternaam'],
    $data['adres'],
    $data['postcode'],
    $data['woonplaats'],
    $data['telefoon'],
    $data['email'],
    $data['volwassenen'],
    $data['kinderen'],
    $data['babies'],
    $id
]);

$stmt = $conn->prepare("DELETE FROM Klanten_has_Klantenwensen WHERE Klanten_idKlanten = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$newWensen = $data['wensen'] ?? [];

foreach ($newWensen as $wensId) {
    $stmt = $conn->prepare("INSERT INTO Klanten_has_Klantenwensen 
      (Klanten_idKlanten, Klantenwensen_idKlantenwensen)
      VALUES (?, ?)");
    $stmt->bind_param("ii", $id, $wensId);
    $stmt->execute();
}

$stmt = $conn->prepare("SELECT k.omschrijving FROM Klanten_allergenen k where k.Klanten_idKlanten = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$oldAllergies = [];
while ($row = $result->fetch_assoc()) {
    $oldAllergies[] = $row['omschrijving'];
}

$newAllergies = $data['allergieen'] ?? [];

$toAddAll = array_diff($newAllergies, $oldAllergies);
$toRemoveAll = array_diff($oldAllergies, $newAllergies);


foreach ($toAddAll as $allergy) {
    $stmt = $conn->prepare("INSERT INTO Klanten_allergenen (Klanten_idKlanten, omschrijving) VALUES (?, ?)");
    $stmt->bind_param("is", $id, $allergy);
    $stmt->execute();
}

foreach ($toRemoveAll as $allergy) {
    $stmt = $conn->prepare("DELETE FROM Klanten_allergenen WHERE Klanten_idKlanten = ? AND omschrijving = ?");
    $stmt->bind_param("is", $id, $allergy);
    $stmt->execute();
}

$stmt->close();
$conn->close();