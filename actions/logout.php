<?php
    session_start();
    session_unset(); //Delete $_SESSION variables
    session_destroy(); // Eindigt de sessie.
    header("Location: ../index.php");
    exit;

?>