<?php
require_once __DIR__ . '/../../common/dbconnection.php';
header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$rawData = file_get_contents("php://input");

$data = json_decode($rawData, true);

$id = $data['id'];
$gezinsnaam = 'Familie '. $data['achternaam'];

try {

    $conn->begin_transaction(); // 🔥 START TRANSACTION

    // ✅ 1. UPDATE klant
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

    $stmt->bind_param(
        "ssssssssiiii",
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
    );

    $stmt->execute();

    $stmtDelete = $conn->prepare("DELETE FROM Klanten_has_Klantenwensen WHERE Klanten_idKlanten = ?");
    $stmtDelete->bind_param("i", $id);
    $stmtDelete->execute();

    $newWensen = $data['wensen'] ?? [];

    if (!empty($newWensen)) {
        $stmt2 = $conn->prepare("
            INSERT INTO Klanten_has_Klantenwensen 
            (Klanten_idKlanten, Klantenwensen_idKlantenwensen)
            VALUES (?, ?)
        ");

        foreach ($newWensen as $wensId) {
            $stmt2->bind_param("ii", $id, $wensId);
            $stmt2->execute();
        }
    }

    $stmt3 = $conn->prepare("
        SELECT omschrijving 
        FROM Klanten_allergenen 
        WHERE Klanten_idKlanten = ?
    ");
    $stmt3->bind_param("i", $id);
    $stmt3->execute();
    $result = $stmt3->get_result();

    $oldAllergies = [];
    while ($row = $result->fetch_assoc()) {
        $oldAllergies[] = $row['omschrijving'];
    }

    $newAllergies = $data['allergieen'] ?? [];

    $toAdd = array_diff($newAllergies, $oldAllergies);
    $toRemove = array_diff($oldAllergies, $newAllergies);

    if (!empty($toAdd)) {
        $stmt4 = $conn->prepare("
            INSERT INTO Klanten_allergenen (Klanten_idKlanten, omschrijving)
            VALUES (?, ?)
        ");

        foreach ($toAdd as $allergy) {
            $stmt4->bind_param("is", $id, $allergy);
            $stmt4->execute();
        }
    }

    if (!empty($toRemove)) {
        $stmt5 = $conn->prepare("
            DELETE FROM Klanten_allergenen 
            WHERE Klanten_idKlanten = ? AND omschrijving = ?
        ");

        foreach ($toRemove as $allergy) {
            $stmt5->bind_param("is", $id, $allergy);
            $stmt5->execute();
        }
    }

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Klant succesvol bijgewerkt"
    ]);

} catch (mysqli_sql_exception $e) {

    $conn->rollback(); 

    if ($e->getCode() == 1062) {
        echo json_encode([
            "success" => false,
            "message" => "Email of naam bestaat al"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Database fout: " . $e->getMessage()
        ]);
    }
}