<?php
require_once __DIR__ . '/../../common/dbconnection.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

header('Content-Type: application/json');
$rawData = file_get_contents("php://input");

$data = json_decode($rawData, true);

$voornaam = $data['voornaam'];
$achternaam = $data['achternaam'];
$gezinsnaam = 'Familie '. $achternaam;
$adres = $data['adres'];
$postcode = $data['postcode'];
$woonplaats = $data['woonplaats'];
$telefoon = $data['telefoon'];
$email = $data['email'];
$volwassenen = $data['volwassenen'];
$kinderen = $data['kinderen'];
$babies = $data['babies'];
$registratieDatum = date('Y-m-d');

try {

    $conn->begin_transaction(); 

    $stmt = $conn->prepare("
        INSERT INTO Klanten 
        (voornaam, achternaam, gezinsnaam, adres, postcode, woonplaats, telefoonnummer, `e-mailadres`, aantal_volwassen, aantal_kinderen, aantal_babies, `status`, registratie_datum)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Inactief', ?)
    ");

    $stmt->bind_param(
        "ssssssssiiis",
        $voornaam,
        $achternaam,
        $gezinsnaam,
        $adres,
        $postcode,
        $woonplaats,
        $telefoon,
        $email,
        $volwassenen,
        $kinderen,
        $babies,
        $registratieDatum
    );

    $stmt->execute();
    $klantId = $stmt->insert_id;

    if (!empty($data['wensen'])) {
        $stmt2 = $conn->prepare("
            INSERT INTO Klanten_has_Klantenwensen 
            (Klanten_idKlanten, Klantenwensen_idKlantenwensen)
            VALUES (?, ?)
        ");

        foreach ($data['wensen'] as $wensId) {
            $stmt2->bind_param("ii", $klantId, $wensId);
            $stmt2->execute();
        }
    }

    if (!empty($data['allergieen'])) {
        $stmt3 = $conn->prepare("
            INSERT INTO Klanten_allergenen 
            (Klanten_idKlanten, omschrijving)
            VALUES (?, ?)
        ");

        foreach ($data['allergieen'] as $allergie) {
            $stmt3->bind_param("is", $klantId, $allergie);
            $stmt3->execute();
        }
    }

    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "Klant succesvol aangemaakt"
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