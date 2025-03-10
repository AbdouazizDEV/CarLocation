<?php
session_start();
require_once __DIR__ . "/../Models/voiture.php";
require_once __DIR__ . "/../Models/offre.php";
require_once __DIR__ . "/../Models/reservation.php";

// Vérifier si l'utilisateur est connecté et est un client
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'client') {
    $_SESSION['error'] = 'Vous devez être connecté en tant que client pour effectuer une réservation.';
    header('Location: login.php');
    exit();
}

$voitureModel = new Voiture();
$offreModel = new Offre();
$reservationModel = new Reservation();

// Traitement du formulaire de réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                // Récupérer les données du formulaire
                $vehicule_id = isset($_POST['vehicule_id']) ? $_POST['vehicule_id'] : null;
                $offre_id = !empty($_POST['offre_id']) ? $_POST['offre_id'] : null;
                $date_debut = isset($_POST['date_debut']) ? $_POST['date_debut'] : null;
                $date_fin = isset($_POST['date_fin']) ? $_POST['date_fin'] : null;
                $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
                $prix_total = isset($_POST['prix_total']) ? $_POST['prix_total'] : 0;
                
                // Valider les données
                $errors = [];
                
                if (!$vehicule_id) {
                    $errors[] = 'Véhicule non spécifié.';
                }
                
                if (!$date_debut) {
                    $errors[] = 'Date de début non spécifiée.';
                }
                
                if (!$date_fin) {
                    $errors[] = 'Date de fin non spécifiée.';
                }
                
                if (strtotime($date_fin) <= strtotime($date_debut)) {
                    $errors[] = 'La date de fin doit être postérieure à la date de début.';
                }
                
                if ($prix_total <= 0) {
                    $errors[] = 'Le prix total n\'est pas valide.';
                }
                
                // Vérifier si le véhicule existe et est disponible
                $vehicule = $voitureModel->getVoitureById($vehicule_id);
                if (!$vehicule) {
                    $errors[] = 'Véhicule non trouvé.';
                } elseif ($vehicule['disponibilite'] != 1 && $vehicule['statut'] !== 'disponible') {
                    $errors[] = 'Ce véhicule n\'est pas disponible.';
                }
                
                // Vérifier si le véhicule est disponible pour la période demandée
                if (!$reservationModel->isVehicleAvailable($vehicule_id, $date_debut, $date_fin)) {
                    $errors[] = 'Ce véhicule n\'est pas disponible pour les dates sélectionnées.';
                }
                
                // Vérifier si l'offre est valide (si une offre est spécifiée)
                if ($offre_id) {
                    $offre = $offreModel->getOffreById($offre_id);
                    if (!$offre) {
                        $errors[] = 'Offre non trouvée.';
                    } elseif ($offre['statut'] !== 'active') {
                        $errors[] = 'Cette offre n\'est plus active.';
                    }
                    
                    // Vérifier si le véhicule est associé à cette offre
                    $vehiculesOffre = $offreModel->getVehiculesForOffre($offre_id);
                    $vehiculeIds = array_column($vehiculesOffre, 'id');
                    if (!in_array($vehicule_id, $vehiculeIds)) {
                        $errors[] = 'Ce véhicule n\'est pas associé à cette offre.';
                    }
                }
                
                // S'il y a des erreurs, rediriger avec un message d'erreur
                if (!empty($errors)) {
                    $_SESSION['error'] = implode(' ', $errors);
                    header('Location: ../Views/AcceuilClient.php');
                    exit();
                }
                
                // Créer la réservation
                $utilisateur_id = $_SESSION['user_id'];
                $result = $reservationModel->create($utilisateur_id, $vehicule_id, $date_debut, $date_fin, $prix_total, $notes, $offre_id);
                
                if ($result) {
                    $_SESSION['success'] = 'Votre réservation a été créée avec succès. Nous vous contacterons pour confirmer les détails.';
                    header('Location: ../Views/reservations.php');
                    exit();
                } else {
                    $_SESSION['error'] = 'Erreur lors de la création de la réservation: ' . $reservationModel->getLastError();
                    header('Location: ../Views/AcceuilClient.php');
                    exit();
                }
                break;
                
            case 'cancel':
                // Annuler une réservation
                $reservation_id = isset($_POST['reservation_id']) ? $_POST['reservation_id'] : null;
                $motif = isset($_POST['motif']) ? $_POST['motif'] : '';
                
                if (!$reservation_id) {
                    $_SESSION['error'] = 'Réservation non spécifiée.';
                    header('Location: ../Views/reservations.php');
                    exit();
                }
                
                // Vérifier si la réservation appartient à l'utilisateur
                $reservation = $reservationModel->getReservationById($reservation_id);
                if (!$reservation || $reservation['utilisateur_id'] != $_SESSION['user_id']) {
                    $_SESSION['error'] = 'Vous n\'avez pas l\'autorisation d\'annuler cette réservation.';
                    header('Location: ../Views/reservations.php');
                    exit();
                }
                
                // Vérifier si la réservation peut être annulée
                if ($reservation['statut'] !== 'en_attente' && $reservation['statut'] !== 'confirmee') {
                    $_SESSION['error'] = 'Cette réservation ne peut pas être annulée.';
                    header('Location: ../Views/reservations.php');
                    exit();
                }
                
                // Annuler la réservation
                $result = $reservationModel->updateStatus($reservation_id, 'annulee', $motif);
                
                if ($result) {
                    $_SESSION['success'] = 'Votre réservation a été annulée avec succès.';
                    header('Location: ../Views/reservations.php');
                    exit();
                } else {
                    $_SESSION['error'] = 'Erreur lors de l\'annulation de la réservation: ' . $reservationModel->getLastError();
                    header('Location: ../Views/reservations.php');
                    exit();
                }
                break;
                
            default:
                $_SESSION['error'] = 'Action non reconnue.';
                header('Location: ../Views/AcceuilClient.php');
                exit();
        }
    } else {
        $_SESSION['error'] = 'Aucune action spécifiée.';
        header('Location: ../Views/AcceuilClient.php');
        exit();
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Traitement des actions GET
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'check_availability':
                // Vérifier la disponibilité d'un véhicule pour une période donnée
                $vehicule_id = isset($_GET['vehicule_id']) ? $_GET['vehicule_id'] : null;
                $date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : null;
                $date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : null;
                
                if (!$vehicule_id || !$date_debut || !$date_fin) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
                    exit();
                }
                
                $isAvailable = $reservationModel->isVehicleAvailable($vehicule_id, $date_debut, $date_fin);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'available' => $isAvailable,
                    'message' => $isAvailable ? 'Véhicule disponible pour les dates sélectionnées' : 'Véhicule indisponible pour les dates sélectionnées'
                ]);
                exit();
                break;
                
            default:
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
                exit();
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Aucune action spécifiée']);
        exit();
    }
} else {
    // Méthode HTTP non prise en charge
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Méthode HTTP non prise en charge']);
    exit();
}
?>