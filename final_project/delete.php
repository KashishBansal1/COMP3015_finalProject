<?php
require 'includes/functions.php';

session_start();
if (!isset($_SESSION['loggedin'])) {
    setcookie('error_message', 'You are not logged in.');
    header('Location: index.php');
    exit();
}

if (preg_match("/^[0-9]+$/", $_GET['id'])) {
    deleteProduct($_GET['id']);
}

header('Location: index.php');
exit();
