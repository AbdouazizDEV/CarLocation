<?php
session_start();
require_once __DIR__ . "/../Models/voiture.php";
require_once __DIR__ . "/../Models/offre.php";

// Vérifier si l'utilisateur est connecté et est un client
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'client') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

$voitureModel = new Voiture();
$offreModel = new Offre();

// Déterminer l'action à effectuer
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'get':
                // Récupérer les détails d'un véhicule
                if (isset($_GET['id'])) {
                    $vehicule = $voitureModel->getVoitureById($_GET['id']);
                    
                    if ($vehicule) {
                        // Vérifier si le véhicule est disponible
                        if ($vehicule['disponibilite'] == 1 || $vehicule['statut'] === 'disponible') {
                            // Vérifier si le véhicule est en promotion
                            $prixInfo = $offreModel->calculateDiscountedPrice($vehicule['id']);
                            $offre = null;
                            
                            if ($prixInfo && isset($prixInfo['offre']) && $prixInfo['discount'] > 0) {
                                $offre = $prixInfo['offre'];
                            }
                            
                            // Récupérer la galerie d'images si elle existe
                            if (!isset($vehicule['all_images']) && isset($vehicule['id'])) {
                                $vehicule['all_images'] = $voitureModel->getImagesForVoiture($vehicule['id']);
                            }
                            
                            // Simulation d'options (dans une vraie application, cela viendrait de la base de données)
                            if (!isset($vehicule['options'])) {
                                $vehicule['options'] = [
                                    'Climatisation', 
                                    'Radio/CD/MP3', 
                                    'Bluetooth', 
                                    'GPS', 
                                    'Verrouillage centralisé', 
                                    'Airbags', 
                                    'ABS', 
                                    'Direction assistée'
                                ];
                            }
                            
                            header('Content-Type: application/json');
                            echo json_encode(['success' => true, 'vehicule' => $vehicule, 'offre' => $offre]);
                        } else {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => false, 'message' => 'Ce véhicule n\'est pas disponible']);
                        }
                    } else {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => 'Véhicule non trouvé']);
                    }
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'ID du véhicule non spécifié']);
                }
                break;
                
            case 'getAvailableVehicles':
                // Récupérer tous les véhicules disponibles
                $vehicules = $voitureModel->getVoituresByStatus('disponible');
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'vehicules' => $vehicules]);
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
