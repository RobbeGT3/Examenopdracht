<?php
require_once __DIR__ . '/../../common/dbconnection.php';
header('Content-Type: application/json');

$sql = "
SELECT 
    p.idProducts,
    p.productnaam,
    p.aantal,
    c.idCategories,
    c.product_categorie
FROM Products p
INNER JOIN Categories c 
    ON p.Categories_idCategories = c.idCategories
WHERE p.aantal > 0
";

$result = mysqli_query($conn, $sql);

$products = [];

while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

echo json_encode($products);