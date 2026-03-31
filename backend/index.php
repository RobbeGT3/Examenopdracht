<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/getVoorraad' && $method === 'GET') {
    require 'actions/action1.php';

}

if ($uri === '/getVoorraad' && $method === 'POST') {
    require 'actions/action1.php';
}

?>