<?php


header('Content-Type: application/json');

$response = [
    'one'=>'Test',
    'two'=>'test2'
];
echo json_encode($response);

?>