<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "Database/Connect.php"; // Assurez-vous que le chemin est correct

try {
    // Créer une instance de la classe Connect
    $connect = new Connect();

    // Obtenir la connexion PDO
    $connection = $connect->getConnection();

    // Exécuter une requête simple pour vérifier la connexion
    $stmt = $connection->query("SELECT 1");
    $result = $stmt->fetch();

    if ($result) {
        echo "Connexion à la base de données réussie!";
    } else {
        echo "Erreur lors de la connexion à la base de données.";
    }
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}