<?php

require_once __DIR__ . '/../../common/dbconnection.php';
header('Content-Type: application/json');

$sql = "
SELECT 
    k.idKlanten,
    k.voornaam,
    k.achternaam,
    k.adres,
    k.postcode,
    k.woonplaats,
    k.aantal_volwassen,
    k.aantal_kinderen,
    k.aantal_babies,
    GROUP_CONCAT(DISTINCT kw.klantenwens) AS wensen,
    GROUP_CONCAT(DISTINCT ka.omschrijving) AS allergenen
FROM Klanten k
LEFT JOIN Klanten_has_Klantenwensen khkw 
    ON k.idKlanten = khkw.Klanten_idKlanten
LEFT JOIN Klantenwensen kw 
    ON khkw.Klantenwensen_idKlantenwensen = kw.idKlantenwensen
LEFT JOIN Klanten_allergenen ka 
    ON k.idKlanten = ka.Klanten_idKlanten
WHERE k.status = 'Goedgekeurd'
GROUP BY k.idKlanten
";

$result = mysqli_query($conn, $sql);

$klanten = [];

while ($row = mysqli_fetch_assoc($result)) {
    $klanten[] = $row;
}

echo json_encode($klanten);