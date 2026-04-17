<?php

require_once __DIR__ . '/../../common/dbconnection.php';
header('Content-Type: application/json');

$sql = 'SELECT
    vp.idVoedselpakketten AS pakket_id,
    k.gezinsnaam,
    k.voornaam,
    k.achternaam,
    k.postcode,
    vp.uitgifte_datum,
    vp.samenstellings_datum,
    p.productnaam,
    c.product_categorie,
    vhp.aantal,
    totals.totaal_items
FROM Voedselpakketten vp
INNER JOIN Klanten k 
    ON vp.Klanten_idKlanten = k.idKlanten
INNER JOIN Voedselpakketten_has_Products vhp 
    ON vp.idVoedselpakketten = vhp.Voedselpakketten_idVoedselpakketten
INNER JOIN Products p  
    ON vhp.Products_idProducts = p.idProducts
INNER JOIN Categories c
	ON c.idCategories = p.Categories_idCategories
INNER JOIN (
    SELECT 
        Voedselpakketten_idVoedselpakketten,
        SUM(aantal) AS totaal_items
    FROM Voedselpakketten_has_Products
    GROUP BY Voedselpakketten_idVoedselpakketten
) totals 
    ON vp.idVoedselpakketten = totals.Voedselpakketten_idVoedselpakketten
ORDER BY vp.idVoedselpakketten;';

$result = mysqli_query($conn, $sql);

$pakketten = [];

while ($row = $result->fetch_assoc()) {

    $id = $row['pakket_id'];
    if (!isset($pakketten[$id])) {
        $pakketten[$id] = [
            "id" => $id,
            "gezinsnaam" => $row['gezinsnaam'],
            "voornaam" => $row['voornaam'],
            "achternaam" => $row['achternaam'],
            "postcode" => $row['postcode'],
            "uitgiftedatum" => $row['uitgifte_datum'],
            "samenstellings_datum" => $row['samenstellings_datum'],
            "producten_totaal"=>$row['totaal_items'],
            "producten" => []
        ];
    }
    $pakketten[$id]["producten"][] = [
        "naam" => $row['productnaam'],
        "aantal" => (int)$row['aantal'],
        "categorie" => $row['product_categorie']
    ];
}

echo json_encode(array_values($pakketten));

?>