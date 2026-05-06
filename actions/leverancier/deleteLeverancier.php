<?php
// =====================================================
// DELETE LEVERANCIER - PHP ACTION FILE
// =====================================================
// Dit bestand verwijdert een leverancier uit de database

// Zet PHP errors uit zodat ze de JSON niet verstoren
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../common/dbconnection.php';

header('Content-Type: application/json');

try {
    // Lees de data van JavaScript
    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);
    
    // Controleer of we een ID hebben
    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'Geen leverancier ID opgegeven']);
        exit;
    }
    
    $id = intval($data['id']); // Zet om naar integer voor veiligheid
    
    // Eerst de gerelateerde leveringen verwijderen (om foreign key errors te voorkomen)
    $stmtLeveringen = $conn->prepare("DELETE FROM leveringen WHERE Leverancier_idLeverancier = ?");
    $stmtLeveringen->bind_param("i", $id);
    $stmtLeveringen->execute();
    $stmtLeveringen->close();
    
    // Dan de leverancier zelf verwijderen
    $stmt = $conn->prepare("DELETE FROM Leverancier WHERE idLeverancier = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Leverancier succesvol verwijderd'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Leverancier niet gevonden']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database fout: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Fout: ' . $e->getMessage()]);
}
?>