<?php
session_start();
require_once __DIR__ . "/../Models/favoris.php";

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user']) || !isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Utilisateur non connecté'
    ]);
    exit;
}

$favorisModel = new Favoris();

// Action par défaut
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Traiter les différentes actions
switch ($action) {
    case 'toggle':
        // Ajouter ou retirer des favoris
        $offre_id = isset($_POST['offre_id']) ? intval($_POST['offre_id']) : 0;
        $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : $_SESSION['user_id'];
        
        // Vérifier si l'offre est déjà en favoris
        $isFavorite = $favorisModel->isOffreFavorite($client_id, $offre_id);
        
        if ($isFavorite) {
            // Si déjà en favoris, le retirer
            $result = $favorisModel->removeOffreFavorite($client_id, $offre_id);
            $added = false;
        } else {
            // Sinon, l'ajouter aux favoris
            $result = $favorisModel->addOffreFavorite($client_id, $offre_id);
            $added = true;
        }
        
        // Répondre en JSON
        header('Content-Type: application/json');
        if ($result) {
            echo json_encode([
                'success' => true,
                'added' => $added,
                'message' => $added ? 'Offre ajoutée aux favoris' : 'Offre retirée des favoris'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $favorisModel->getLastError() ?: 'Erreur lors de la gestion des favoris'
            ]);
        }
        break;
        
    case 'add_offre':
        // Ajouter une offre aux favoris
        $offre_id = isset($_POST['offre_id']) ? intval($_POST['offre_id']) : 0;
        $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : $_SESSION['user_id'];
        
        $result = $favorisModel->addOffreFavorite($client_id, $offre_id);
        
        // Répondre en JSON
        header('Content-Type: application/json');
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Offre ajoutée aux favoris'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $favorisModel->getLastError() ?: 'Erreur lors de l\'ajout aux favoris'
            ]);
        }
        break;
        
    case 'remove_offre':
        // Retirer une offre des favoris
        $offre_id = isset($_POST['offre_id']) ? intval($_POST['offre_id']) : 0;
        $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : $_SESSION['user_id'];
        
        $result = $favorisModel->removeOffreFavorite($client_id, $offre_id);
        
        // Répondre en JSON
        header('Content-Type: application/json');
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Offre retirée des favoris'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $favorisModel->getLastError() ?: 'Erreur lors du retrait des favoris'
            ]);
        }
        break;
        
    case 'add_voiture':
        // Ajouter un véhicule aux favoris
        $voiture_id = isset($_POST['voiture_id']) ? intval($_POST['voiture_id']) : 0;
        $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : $_SESSION['user_id'];
        
        $result = $favorisModel->addVoitureFavorite($client_id, $voiture_id);
        
        // Répondre en JSON
        header('Content-Type: application/json');
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Véhicule ajouté aux favoris'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $favorisModel->getLastError() ?: 'Erreur lors de l\'ajout aux favoris'
            ]);
        }
        break;
        
    case 'remove_voiture':
        // Retirer un véhicule des favoris
        $voiture_id = isset($_POST['voiture_id']) ? intval($_POST['voiture_id']) : 0;
        $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : $_SESSION['user_id'];
        
        $result = $favorisModel->removeVoitureFavorite($client_id, $voiture_id);
        
        // Répondre en JSON
        header('Content-Type: application/json');
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Véhicule retiré des favoris'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $favorisModel->getLastError() ?: 'Erreur lors du retrait des favoris'
            ]);
        }
        break;
        
    default:
        // Action inconnue
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Action non reconnue'
        ]);
        break;
}
?>