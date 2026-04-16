<?php

session_start();
require_once  __DIR__. '/../common/dbconnection.php';


$rawData = file_get_contents("php://input");

$data = json_decode($rawData, true); // true = array

file_put_contents('klanten.txt', $rawData, FILE_APPEND);

?>