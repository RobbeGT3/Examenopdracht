<?php
// =====================================================
// EDIT/UPDATE LEVERANCIER - PHP ACTION FILE
// =====================================================
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
    
    // Bereid de UPDATE query voor
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
    
    // Bind alle parameters (7 strings + 1 integer voor ID)
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
    
    // Voer de query uit
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Leverancier succesvol bijgewerkt'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database fout: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Fout: ' . $e->getMessage()]);
}
?>