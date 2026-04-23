<?php

session_start();
require_once  __DIR__. '/../common/dbconnection.php';

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);
file_put_contents('klanten2.txt', $rawData, FILE_APPEND);


// $categoryId = null;

// if (!is_null($data['new_categorie'])) {
//     $categoryNaam = $data['new_categorie'];
//     $stmtAddCategory= $conn->prepare('INSERT INTO Categories (product_categorie) VALUES (?);');
//     $stmtAddCategory->bind_param("s", $categoryNaam);
//     $stmtAddCategory->execute();

//     $categoryId = $stmtAddCategory->insert_id;
//     // $stmtAddCategory->close();
// }else{
//     $categoryId = $data['categorie_id'];
// }

// $ean = $data['ean'];
// $productnaam = $data['productnaam'];
// $aantal = $data['aantal'];
// $eenheid = "stuks"; 
// $ontvangstdatum = date("Y-m-d"); 

// $stmtProduct = $conn->prepare("
//     INSERT INTO Products 
//     (`EAN-nummer`, productnaam, aantal, eenheid, ontvangst_datum, Categories_idCategories)
//     VALUES (?, ?, ?, ?, ?, ?)
// ");

// $stmtProduct->bind_param("ssissi", 
//     $ean, 
//     $productnaam, 
//     $aantal, 
//     $eenheid, 
//     $ontvangstdatum, 
//     $categoryId
// );

// $stmtProduct->execute();
// $stmtProduct->close();
// $conn->close();



?>