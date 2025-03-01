<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "Database/Connect.php";

try {
    $connect = new Connect();
    $connection = $connect->getConnection();
    echo "Connexion réussie !";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
