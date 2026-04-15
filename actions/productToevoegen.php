<?php

session_start();
require_once  __DIR__. '/../common/dbconnection.php';

// Lees raw JSON input
$rawData = file_get_contents("php://input");

$data = json_decode($rawData, true); // true = array

file_put_contents('klanten.txt', $rawData, FILE_APPEND);

if (!is_null($data['new_categorie'])) {
    $categoryNaam = $data['new_categorie'];
    // $stmtAddCategory= $conn->prepare('');
    // $stmtInsertAllergie->bind_param("s", $a);
    // $stmtAddCategory->execute();

    echo 'test';
}

echo 'test2';

?>