<?php


error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');



try {
    // Verbind met database
    require_once __DIR__ . '/../../common/dbconnection.php';
    
    // php://input = lees de "raw" data die is verstuurd
    $rawData = file_get_contents("php://input");
    
    // Zet de JSON data om naar een PHP array
    $data = json_decode($rawData, true);
    
    // Controleer of JSON geldig was
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Ongeldige JSON data ontvangen']);
        exit;
    }
    
    // Controleer verplichte velden
    if (empty($data['bedrijfsnaam']) || empty($data['contactpersoon']) || empty($data['email']) || empty($data['telefoonnummer'])) {
        echo json_encode(['success' => false, 'message' => 'Vul alle verplichte velden in']);
        exit;
    }
    
    // Zet lege strings als default voor optionele velden
    $adres = $data['adres'] ?? '';
    $postcode = $data['postcode'] ?? '';
    $plaats = $data['plaats'] ?? '';

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
        $adres,
        $postcode,
        $plaats
    );

// Voer de query uit
if ($stmt->execute()) {
    // Haal het ID op van de zojuist toegevoegde leverancier
    $leverancierId = $stmt->insert_id;

    $stmtLevering = $conn->prepare("
        INSERT INTO Leveringen (Leverancier_idLeverancier, leverings_datum) 
        VALUES (?, ?)
    ");
    
    // Gebruik de gekozen startdatum uit het formulier, of vandaag als niet ingevuld
    $startDatumString = $data['eersteLevering'] ?? null;
    if ($startDatumString) {
        $startDatum = new DateTime($startDatumString);
    } else {
        $startDatum = new DateTime(); // Vandaag
    }
    
    // Kopieer startdatum en tel er 3 maanden bij op voor het eind
    $eindDatum = clone $startDatum;
    $eindDatum->modify('+3 months');
    
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
    
} catch (Exception $e) {
    // Als er een fout is, stuur JSON terug met de foutmelding
    echo json_encode(['success' => false, 'message' => 'Fout: ' . $e->getMessage()]);
}



?>