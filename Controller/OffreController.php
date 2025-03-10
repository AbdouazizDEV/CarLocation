<?php
session_start();
require_once __DIR__ . "/../Models/offre.php";
require_once __DIR__ . "/../Models/voiture.php";
require_once __DIR__ . "/../Models/favoris.php";

// Instancier les modèles
$offreModel = new Offre();
$voitureModel = new Voiture();
$favorisModel = new Favoris();

// Vérification de base pour toutes les requêtes
if (!isset($_SESSION['user']) || !isset($_SESSION['user_id'])) {
    // Si la requête est AJAX, retourner une réponse JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Non connecté'
        ]);
        exit();
    }
    // Sinon, rediriger vers la page de connexion
    else {
        header('Location: login.php');
        exit();
    }
}

// Traiter les actions GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    switch ($action) {
        case 'get':
            // Récupérer une offre par son ID (accessible pour tous les utilisateurs connectés)
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            
            if (!$id) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'ID offre non valide'
                ]);
                exit();
            }
            
            $offre = $offreModel->getOffreById($id);
            
            if ($offre) {
                // Convertir les dates au format Y-m-d pour la cohérence
                $offre['date_debut'] = date('Y-m-d', strtotime($offre['date_debut']));
                $offre['date_fin'] = date('Y-m-d', strtotime($offre['date_fin']));
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'offre' => $offre
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $offreModel->getLastError() ?: 'Offre non trouvée'
                ]);
            }
            exit();
            break;
            
        case 'getVehicules':
            // Récupérer tous les véhicules disponibles (pour les gérants uniquement)
            if ($_SESSION['user_role'] !== 'gérant' && $_SESSION['user_role'] !== 'admin') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Non autorisé'
                ]);
                exit();
            }
            
            $vehicules = $voitureModel->getVoituresByStatus('disponible');
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'vehicules' => $vehicules
            ]);
            exit();
            break;
            
        case 'filter':
            // Filtrer les offres (accessible pour tous les utilisateurs connectés)
            $categorie = isset($_GET['categorie']) ? $_GET['categorie'] : '';
            $reductionMin = isset($_GET['reduction_min']) ? intval($_GET['reduction_min']) : 0;
            $sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'newest';
            
            // Cette partie nécessiterait une implémentation spécifique dans votre modèle Offre
            // Je donne un exemple simplifié ici
            $offres = $offreModel->getActiveOffres(); // Récupérer toutes les offres actives
            
            // Filtrer par catégorie si spécifié
            if (!empty($categorie)) {
                $offres = array_filter($offres, function($offre) use ($categorie, $offreModel) {
                    $vehicules = $offreModel->getVehiculesForOffre($offre['id']);
                    foreach ($vehicules as $vehicule) {
                        if ($vehicule['categorie'] === $categorie) {
                            return true;
                        }
                    }
                    return false;
                });
            }
            
            // Filtrer par réduction minimale
            if ($reductionMin > 0) {
                $offres = array_filter($offres, function($offre) use ($reductionMin) {
                    return $offre['reduction'] >= $reductionMin;
                });
            }
            
            // Tri
            switch ($sortBy) {
                case 'highest_discount':
                    usort($offres, function($a, $b) {
                        return $b['reduction'] - $a['reduction'];
                    });
                    break;
                case 'lowest_price':
                    // Nécessiterait des informations supplémentaires sur les prix
                    // Tri par défaut pour l'instant
                    break;
                case 'newest':
                default:
                    usort($offres, function($a, $b) {
                        return strtotime($b['date_debut']) - strtotime($a['date_debut']);
                    });
                    break;
            }
            
            // Ajouter des informations supplémentaires pour chaque offre
            foreach ($offres as &$offre) {
                $vehicules = $offreModel->getVehiculesForOffre($offre['id']);
                $offre['vehicule_image'] = !empty($vehicules) && isset($vehicules[0]['images']) ? 
                                        "../" . $vehicules[0]['images'] : 
                                        "https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg";
                $offre['is_favorite'] = $favorisModel->isOffreFavorite($_SESSION['user_id'], $offre['id']);
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'offres' => array_values($offres) // array_values pour réindexer le tableau après filtrage
            ]);
            exit();
            break;
            
        default:
            // Rediriger vers la page appropriée selon le rôle
            $redirect = ($_SESSION['user_role'] === 'client') ? 'AcceuilClient.php' : 'Offres.php';
            header('Location: ' . $redirect);
            exit();
            break;
    }
}

// Traiter les actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pour les actions POST, seul un gérant peut effectuer des modifications
    if ($_SESSION['user_role'] !== 'gérant' && $_SESSION['user_role'] !== 'admin') {
        $_SESSION['error'] = "Vous n'avez pas l'autorisation d'effectuer cette action.";
        header('Location: AcceuilClient.php');
        exit();
    }
    
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'create':
            // Récupérer les données du formulaire
            $titre = isset($_POST['titre']) ? $_POST['titre'] : '';
            $description = isset($_POST['description']) ? $_POST['description'] : '';
            $reduction = isset($_POST['reduction']) ? floatval($_POST['reduction']) : 0;
            $date_debut = isset($_POST['date_debut']) ? $_POST['date_debut'] : '';
            $date_fin = isset($_POST['date_fin']) ? $_POST['date_fin'] : '';
            $code_promo = isset($_POST['code_promo']) ? $_POST['code_promo'] : null;
            $vehicules = isset($_POST['vehicules']) ? $_POST['vehicules'] : [];
            
            $result = $offreModel->create($titre, $description, $reduction, $date_debut, $date_fin, $code_promo, $vehicules);
            
            if ($result) {
                $_SESSION['success'] = "L'offre a été créée avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la création de l'offre: " . $offreModel->getLastError();
            }
            
            header('Location: Offres.php');
            exit();
            break;
            
        case 'update':
            // Récupérer les données du formulaire
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $titre = isset($_POST['titre']) ? $_POST['titre'] : '';
            $description = isset($_POST['description']) ? $_POST['description'] : '';
            $reduction = isset($_POST['reduction']) ? floatval($_POST['reduction']) : 0;
            $date_debut = isset($_POST['date_debut']) ? $_POST['date_debut'] : '';
            $date_fin = isset($_POST['date_fin']) ? $_POST['date_fin'] : '';
            $statut = isset($_POST['statut']) ? $_POST['statut'] : 'active';
            $code_promo = isset($_POST['code_promo']) ? $_POST['code_promo'] : null;
            $vehicules = isset($_POST['vehicules']) ? $_POST['vehicules'] : [];
            
            $result = $offreModel->update($id, $titre, $description, $reduction, $date_debut, $date_fin, $statut, $code_promo, $vehicules);
            
            if ($result) {
                $_SESSION['success'] = "L'offre a été mise à jour avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour de l'offre: " . $offreModel->getLastError();
            }
            
            header('Location: Offres.php');
            exit();
            break;
            
        case 'delete':
            // Supprimer une offre
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            
            $result = $offreModel->delete($id);
            
            if ($result) {
                $_SESSION['success'] = "L'offre a été supprimée avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression de l'offre: " . $offreModel->getLastError();
            }
            
            header('Location: Offres.php');
            exit();
            break;
            
        case 'reactivate':
            // Réactiver une offre expirée
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $date_fin = isset($_POST['date_fin']) ? $_POST['date_fin'] : '';
            
            $result = $offreModel->reactivateOffer($id, $date_fin);
            
            if ($result) {
                $_SESSION['success'] = "L'offre a été réactivée avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la réactivation de l'offre: " . $offreModel->getLastError();
            }
            
            header('Location: Offres.php');
            exit();
            break;
            
        default:
            header('Location: Offres.php');
            exit();
            break;
    }
}

// Si aucune action n'est spécifiée, rediriger vers la page appropriée
$redirect = ($_SESSION['user_role'] === 'client') ? 'AcceuilClient.php' : 'Offres.php';
header('Location: ' . $redirect);
exit();
?>