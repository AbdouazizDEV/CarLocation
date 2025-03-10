<?php
session_start();
require_once __DIR__ . "/../Models/offre.php";
require_once __DIR__ . "/../Models/voiture.php";

$offreModel = new Offre();
$voitureModel = new Voiture();

// Déterminer l'action à effectuer
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'get':
                // Récupérer les détails d'une offre
                if (isset($_GET['id'])) {
                    $offre = $offreModel->getOffreById($_GET['id']);
                    
                    if ($offre) {
                        // Vérifier si l'offre est active
                        if ($offre['statut'] === 'active') {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => true, 'offre' => $offre]);
                        } else {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => false, 'message' => 'Cette offre n\'est plus disponible']);
                        }
                    } else {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => 'Offre non trouvée']);
                    }
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'ID de l\'offre non spécifié']);
                }
                break;
                
            case 'getActiveOffers':
                // Récupérer toutes les offres actives
                $offres = $offreModel->getActiveOffres();
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'offres' => $offres]);
                break;
                
            default:
                // Action non reconnue
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
                break;
        }
    } else {
        // Aucune action spécifiée
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Aucune action spécifiée']);
    }
} else {
    // Méthode HTTP non prise en charge
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Méthode HTTP non prise en charge']);
}
?>