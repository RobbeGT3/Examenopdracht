<?php

require_once __DIR__ . '/../common/dbconnection.php';

header('Content-Type: application/json');

$email = $_POST['email'] ?? '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'E-mailadres ontbreekt']);
    exit;
}

// Controleer of klant bestaat
// $stmt = $conn->prepare("SELECT idKlanten, email FROM Klanten WHERE email = ?");
$stmt = $conn->prepare("SELECT idGebruiker, email FROM Gebruiker WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Geen klant gevonden met dit e-mailadres']);
    $stmt->close();
    $conn->close();
    exit;
}

$klant = $result->fetch_assoc();
$stmt->close();

// Genereer unieke token
$token = bin2hex(random_bytes(32));
$expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Check of password_resets tabel bestaat, anders maak aan
$checkTable = $conn->query("SHOW TABLES LIKE 'password_resets'");
if ($checkTable->num_rows === 0) {
    $createTable = "CREATE TABLE password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        token VARCHAR(64) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($createTable);
}

// Verwijder oude tokens voor dit e-mailadres
$stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->close();

// Sla nieuwe token op
$stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $token, $expiry);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Fout bij opslaan token: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();
$conn->close();

// Genereer reset link (pas dit aan naar je domein)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$resetLink = "$protocol://$host/reset-password.php?token=" . $token;

// Stuur e-mail (gebruik PHP mail() - voor productie gebruik PHPMailer)
$to = $email;
$subject = "Wachtwoord Reset - Voedselbank";
$message = "Beste klant,\n\nU heeft een verzoek gedaan om uw wachtwoord te resetten.\n\nKlik op de onderstaande link om uw wachtwoord te wijzigen:\n$resetLink\n\nDeze link is 1 uur geldig.\n\nAls u dit verzoek niet heeft gedaan, negeer dan deze e-mail.\n\nMet vriendelijke groet,\nVoedselbank";
$headers = "From: noreply@voedselbank.nl\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

if (mail($to, $subject, $message, $headers)) {
    echo json_encode(['success' => true, 'message' => 'Reset link succesvol verzonden naar ' . $email]);
} else {
    echo json_encode(['success' => false, 'message' => 'Fout bij verzenden e-mail. Controleer server configuratie.']);
}
?>
