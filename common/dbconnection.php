<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "127.0.0.1";
$username = "root";
$password = "LKP908H1";

// $servername = "voedselhulpmaaskantje.cj2yck2ymf52.eu-north-1.rds.amazonaws.com";
// $username = "MaasAdmin";
// $password = "MaasKantje";
$dbname = "Examenopdracht";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
return $conn
?>