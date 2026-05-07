<?php
// Dit bestand werkt een bestaande leverancier bij in de database
// Zet PHP errors uit zodat ze de JSON niet verstoren
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../common/dbconnection.php';

header('Content-Type: application/json');

try {
    // Lees de data van JavaScript
    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);
    
    // Controleer of we een ID hebben (nodig voor update)
    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'Geen leverancier ID opgegeven']);
        exit;
    }
    
    // Controleer verplichte velden
    if (empty($data['bedrijfsnaam']) || empty($data['contactpersoon']) || empty($data['email'])) {
        echo json_encode(['success' => false, 'message' => 'Vul alle verplichte velden in']);
        exit;
    }
    
    // === STAP 1: Update de Leverancier gegevens ===
    $stmt = $conn->prepare("
        UPDATE Leverancier 
        SET bedrijfsnaam = ?, 
            contactpersoon = ?, 
            `e-mailadres` = ?, 
            telefoonnummer = ?, 
            adres = ?, 
            postcode = ?, 
            plaats = ?
        WHERE idLeverancier = ?
    ");
    
    $stmt->bind_param(
        "sssssssi",
        $data['bedrijfsnaam'],
        $data['contactpersoon'],
        $data['email'],
        $data['telefoonnummer'],
        $data['adres'],
        $data['postcode'],
        $data['plaats'],
        $data['id']
    );
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Database fout: ' . $stmt->error]);
        $stmt->close();
        $conn->close();
        exit;
    }
    
    $stmt->close();
    
    // === STAP 2: Als er een nieuwe leverdatum is, update de leveringen ===
    if (!empty($data['eersteLevering'])) {
        $leverancierId = $data['id'];
        $startDatumString = $data['eersteLevering'];
        $frequentie = $data['leverfrequentie'] ?? 'wekelijks';
        
        // Verwijder oude toekomstige leveringen
        $stmtDelete = $conn->prepare("DELETE FROM Leveringen WHERE Leverancier_idLeverancier = ? AND leverings_datum > NOW()");
        $stmtDelete->bind_param("i", $leverancierId);
        $stmtDelete->execute();
        $stmtDelete->close();
        
        // Bepaal het interval
        switch ($frequentie) {
            case 'dagelijks':
                $interval = new DateInterval('P1D');
                break;
            case 'maandelijks':
                $interval = new DateInterval('P1M');
                break;
            case 'wekelijks':
            default:
                $interval = new DateInterval('P1W');
                break;
        }
        
        // Startdatum en einddatum (3 maanden)
        $startDatum = new DateTime($startDatumString);
        $eindDatum = clone $startDatum;
        $eindDatum->modify('+3 months');
        
        // Bereid insert voor
        $stmtLevering = $conn->prepare("INSERT INTO Leveringen (Leverancier_idLeverancier, leverings_datum) VALUES (?, ?)");
        
        // Genereer nieuwe leveringen
        $periode = new DatePeriod($startDatum, $interval, $eindDatum);
        $aantalLeveringen = 0;
        
        foreach ($periode as $datum) {
            $leveringsDatum = $datum->format('Y-m-d H:i:s');
            $stmtLevering->bind_param("is", $leverancierId, $leveringsDatum);
            $stmtLevering->execute();
            $aantalLeveringen++;
        }
        
        $stmtLevering->close();
        
        echo json_encode([
            'success' => true, 
            'message' => "Leverancier bijgewerkt met {$aantalLeveringen} nieuwe leveringen voor de komende 3 maanden"
        ]);
    } else {
        echo json_encode([
            'success' => true, 
            'message' => 'Leverancier succesvol bijgewerkt'
        ]);
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Fout: ' . $e->getMessage()]);
}
?>