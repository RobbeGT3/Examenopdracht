<?php
session_start();
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    die("Page not available");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>test</title>
</head>
<body>
    <a href="actions/logout.php">Uitloggen</a></li>
    
</body>
</html>