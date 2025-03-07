<?php
session_start();
require_once __DIR__ . "/../Models/voiture.php";

// Vérifier si l'utilisateur est connecté et est un gérant
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'gérant') {
    header('Location: ../Views/login.php');
    exit();
}

// Vérifier si l'ID du véhicule est fourni
if (!isset($_POST['id']) || empty($_POST['id'])) {
    $_SESSION['error'] = "ID du véhicule non spécifié.";
    header("Location: ../Views/gestion_vehicules.php");
    exit();
}

$vehicleId = intval($_POST['id']);

// Instancier le modèle Voiture
$voitureModel = new Voiture();

// Supprimer les fichiers d'images associés
$vehicleDetails = $voitureModel->getVoitureById($vehicleId);
if ($vehicleDetails && isset($vehicleDetails['all_images']) && !empty($vehicleDetails['all_images'])) {
    foreach ($vehicleDetails['all_images'] as $image) {
        $imagePath = __DIR__ . '/../' . $image['chemin'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}

// Supprimer le véhicule de la base de données
// La suppression dans la table Images se fera automatiquement grâce à la contrainte ON DELETE CASCADE
$result = $voitureModel->delete($vehicleId);

if ($result) {
    $_SESSION['success'] = "Le véhicule a été supprimé avec succès.";
} else {
    $_SESSION['error'] = "Une erreur s'est produite lors de la suppression du véhicule: " . $voitureModel->getLastError();
}

// Rediriger vers la page de gestion des véhicules
header("Location: ../Views/gestion_vehicules.php");
exit();
?>  