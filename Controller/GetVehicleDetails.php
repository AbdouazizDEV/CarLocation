<?php
session_start();
require_once __DIR__ . "/../Models/voiture.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'gérant') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Accès non autorisé']);
    exit();
}

// Vérifier si l'ID du véhicule est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID du véhicule non spécifié']);
    exit();
}

$vehicleId = intval($_GET['id']);

// Instancier le modèle Voiture
$voitureModel = new Voiture();

// Récupérer les détails du véhicule, y compris toutes les images
$vehicle = $voitureModel->getVoitureById($vehicleId);

if (!$vehicle) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Véhicule non trouvé']);
    exit();
}

// Retourner les détails du véhicule au format JSON
header('Content-Type: application/json');
echo json_encode($vehicle);
?>