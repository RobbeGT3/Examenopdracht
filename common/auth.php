<?php

// Controleer of de gebruiker is ingelogd
// Zo niet, stuur ze terug naar de login pagina
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}
?>
