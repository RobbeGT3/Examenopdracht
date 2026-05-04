<?php

require_once __DIR__ . '/../../common/dbconnection.php';
header('Content-Type: application/json');

$sql='SELECT 
    k.idKlanten,
    k.gezinsnaam,
    k.voornaam,
    k.achternaam,
    k.adres,
    k.postcode,
    k.woonplaats,
    k.telefoonnummer,
    k.`e-mailadres` AS email,
    k.`status` as status,
    k.aantal_volwassen as volwassenen,
    k.aantal_kinderen as kinderen,
    k.aantal_babies as babies,

    GROUP_CONCAT(DISTINCT kw.idKlantenwensen) AS wensen_ids,
    GROUP_CONCAT(DISTINCT kw.klantenwens) AS wensen,
    GROUP_CONCAT(DISTINCT ka.omschrijving) AS allergenen,

    (
        SELECT v.idVoedselpakketten
        FROM Voedselpakketten v
        WHERE v.Klanten_idKlanten = k.idKlanten
        ORDER BY v.samenstellings_datum DESC
        LIMIT 1
    ) AS idVoedselpakketten,

    MAX(v.samenstellings_datum) AS laatste_samenstelling,
    MAX(v.uitgifte_datum) AS uitgifte_datum,
    COUNT(DISTINCT v.idVoedselpakketten) AS aantal_pakketten

FROM Klanten k
LEFT JOIN Voedselpakketten v 
    ON v.Klanten_idKlanten = k.idKlanten
LEFT JOIN Klanten_has_Klantenwensen khkw 
    ON k.idKlanten = khkw.Klanten_idKlanten
LEFT JOIN Klantenwensen kw 
    ON khkw.Klantenwensen_idKlantenwensen = kw.idKlantenwensen
LEFT JOIN Klanten_allergenen ka 
    ON k.idKlanten = ka.Klanten_idKlanten

GROUP BY k.idKlanten
ORDER BY v.samenstellings_datum ASC';

$result = mysqli_query($conn, $sql);

$klanten = [];

while ($row = mysqli_fetch_assoc($result)) {
    $klanten[] = $row;
}

echo json_encode($klanten);