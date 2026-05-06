<?php
// =====================================================
// ADD LEVERANCIER - PHP ACTION FILE
// =====================================================
// Dit bestand ontvangt data van het formulier en slaat
// het op in de database + maakt leveringen aan

// Start PHP sessie
session_start();

// Verbind met database
require_once __DIR__ . '/../common/dbconnection.php';

// Zeg dat we JSON terugsturen (voor AJAX)
header('Content-Type: application/json');

// =====================================================
// STAP 1: Ontvang de data van JavaScript
// =====================================================
// php://input = lees de "raw" data die is verstuurd
$rawData = file_get_contents("php://input");

// Zet de JSON data om naar een PHP array
$data = json_decode($rawData, true);

// =====================================================
// STAP 2: Controleer of alle verplichte velden er zijn
// =====================================================
if (!isset($data['bedrijfsnaam'], $data['contactpersoon'], $data['email'], $data['telefoonnummer'])) {
    // Stuur een foutmelding terug
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ontbrekende verplichte velden']);
    exit; // Stop hier
}

// =====================================================
// STAP 3: Sla de leverancier op in de database
// =====================================================
$stmt = $conn->prepare("
    INSERT INTO Leverancier 
    (bedrijfsnaam, contactpersoon, `e-mailadres`, telefoonnummer, adres, postcode, plaats)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

// "sssssss" = 7 strings (type van elke ? parameter)
$stmt->bind_param(
    "sssssss",
    $data['bedrijfsnaam'],
    $data['contactpersoon'],
    $data['email'],
    $data['telefoonnummer'],
    $data['adres'],
    $data['postcode'],
    $data['plaats']
);

// Voer de query uit
if ($stmt->execute()) {
    // Haal het ID op van de zojuist toegevoegde leverancier
    $leverancierId = $stmt->insert_id;
    
    // =====================================================
    // STAP 4: Maak leveringsdatums aan voor 3 maanden
    // =====================================================
    $stmtLevering = $conn->prepare("
        INSERT INTO leveringen (Leverancier_idLeverancier, leverings_datum) 
        VALUES (?, ?)
    ");
    
    // Begin vandaag
    $startDatum = new DateTime();
    // Eindig over 3 maanden
    $eindDatum = (new DateTime())->modify('+3 months');
    
    // Bepaal hoe vaak we leveren (dagelijks/wekelijks/maandelijks)
    $frequentie = $data['leverfrequentie'] ?? 'wekelijks';
    
    // Kies het juiste interval
    switch($frequentie) {
        case 'dagelijks':
            $interval = new DateInterval('P1D');  // P1D = 1 dag
            break;
        case 'wekelijks':
            $interval = new DateInterval('P1W');  // P1W = 1 week
            break;
        case 'maandelijks':
            $interval = new DateInterval('P1M');  // P1M = 1 maand
            break;
        default:
            $interval = new DateInterval('P1W');  // Default: wekelijks
    }
    
    // Maak een periode van datums
    $periode = new DatePeriod($startDatum, $interval, $eindDatum);
    $aantalLeveringen = 0;
    
    // Loop door elke datum in de periode
    foreach ($periode as $datum) {
        // Formatteer als YYYY-MM-DD HH:MM:SS
        $leveringsDatum = $datum->format('Y-m-d H:i:s');
        
        // Voeg deze levering toe aan de database
        $stmtLevering->bind_param("is", $leverancierId, $leveringsDatum);
        $stmtLevering->execute();
        $aantalLeveringen++;
    }
    
    $stmtLevering->close();
    
    // =====================================================
    // STAP 5: Stuur succesmelding terug naar JavaScript
    // =====================================================
    echo json_encode([
        'success' => true, 
        'id' => $leverancierId, 
        'message' => "Leverancier toegevoegd met {$aantalLeveringen} leveringen voor de komende 3 maanden"
    ]);
} else {
    // Er ging iets mis
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database fout: ' . $stmt->error]);
}

// Sluit alles netjes af
$stmt->close();
$conn->close();
?>
