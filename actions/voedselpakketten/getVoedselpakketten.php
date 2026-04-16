<?php

require_once __DIR__ . '/../../common/dbconnection.php';
header('Content-Type: application/json');

$sql = "
SELECT
vp.idVoedselpakketten AS pakket_id,
k.gezinsnaam,
k.voornaam,
k.achternaam,
k.postcode,
vp.uitgifte_datum,
vp.samenstellings_datum,
p.productnaam,
vhp.aantal
FROM Voedselpakketten vp
INNER JOIN Klanten k ON vp.Klanten_idKlanten = k.idKlanten
INNER JOIN Voedselpakketten_has_Products vhp ON  vp.idVoedselpakketten = vhp.Voedselpakketten_idVoedselpakketten
INNER JOIN Products p  ON vhp.Products_idProducts = p.idProducts
ORDER BY vp.idVoedselpakketten;
";

$result = mysqli_query($conn, $sql);

$pakketten = [];

while ($row = $result->fetch_assoc()) {

    $id = $row['pakket_id'];

    // bestaat pakket al?
    if (!isset($pakketten[$id])) {
        $pakketten[$id] = [
            "id" => $id,
            "gezinsnaam" => $row['gezinsnaam'],
            "voornaam" => $row['voornaam'],
            "achternaam" => $row['achternaam'],
            "postcode" => $row['postcode'],
            "uitgiftedatum" => $row['uitgiftedatum'],
            "samenstellings_datum" => $row['uitgiftedatum'],
            "producten" => []
        ];
    }

    // voeg product toe
    $pakketten[$id]["producten"][] = [
        "naam" => $row['productnaam'],
        "aantal" => (int)$row['aantal']
    ];
}

echo json_encode($pakketten);

?>