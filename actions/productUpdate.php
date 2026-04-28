<?php

session_start();
require_once  __DIR__. '/../common/dbconnection.php';


$rawData = file_get_contents("php://input");

$data = json_decode($rawData, true); 
$productID = $data['id'];

if (!is_null($data['new_categorie'])) {
    $categoryNaam = $data['new_categorie'];
    $stmtAddCategory= $conn->prepare('INSERT INTO Categories (product_categorie) VALUES (?);');
    $stmtAddCategory->bind_param("s", $categoryNaam);
    $stmtAddCategory->execute();

    $categoryId = $stmtAddCategory->insert_id;
    $stmtAddCategory->close();
}else{
    $categoryId = $data['categorie_id'];
}

$ean = $data['ean'];
$productnaam = $data['productnaam'];
$aantal = $data['aantal'];

$stmtUpdateProduct = $conn->prepare("
    UPDATE Products p SET p.`EAN-nummer` = ?, p.productnaam = ?, p.aantal = ?, p.Categories_idCategories =? WHERE p.idProducts = ?;
");


$stmtUpdateProduct->bind_param("ssiii", 
    $ean, 
    $productnaam, 
    $aantal,  
    $categoryId,
    $productID
);

$stmtUpdateProduct->execute();
$stmtUpdateProduct->close();
$conn->close();

?>