<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "voedselhulpmaaskantje.cj2yck2ymf52.eu-north-1.rds.amazonaws.com";
$username = "MaasAdmin";
$password = "MaasKantje";
$dbname = "Examenopdracht";

$conn = mysqli_init();

mysqli_ssl_set(
    $conn,
    NULL,
    NULL,
    __DIR__. "/rds-ca.pem",
    NULL,
    NULL
);

mysqli_real_connect(
    $conn,
    $servername,
    $username,
    $password,
    $dbname,
    3306,
    NULL,
    MYSQLI_CLIENT_SSL
);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
return $conn
?>