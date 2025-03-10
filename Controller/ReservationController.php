<?php
/**
 * Contrôleur pour gérer les réservations côté administrateur (gérant)
 * 
 * Ce fichier gère toutes les opérations liées aux réservations effectuées par l'administrateur :
 * - Création, modification, suppression de réservation
 * - Consultation des détails d'une réservation
 * - Changement de statut (confirmation, annulation, etc.)
 */

session_start();
require_once __DIR__ . "/../Models/Reservation.php";
require_once __DIR__ . "/../Models/voiture.php";
require_once __DIR__ . "/../Models/client.php";

// Vérifier si l'utilisateur est connecté et est un gérant
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'gérant') {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        // Si c'est une requête AJAX, retourner une erreur JSON
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Accès non autorisé']);
        exit();
    } else {
        // Sinon, rediriger vers la page de connexion
        $_SESSION['error'] = "Vous devez être connecté en tant que gérant pour effectuer cette action.";
        header('Location: ../Views/login.php');
        exit();
    }
}

// Instancier les modèles nécessaires
$reservationModel = new Reservation();
$voitureModel = new Voiture();
$clientModel = new Client();

// Récupérer l'action demandée
$action = $_POST['action'] ?? $_GET['action'] ?? null;

// Traiter l'action en fonction de la demande
switch ($action) {
    case 'add':
        // Création d'une nouvelle réservation
        handleAddReservation();
        break;
        
    case 'update':
        // Mise à jour d'une réservation existante
        handleUpdateReservation();
        break;
        
    case 'confirm':
        // Confirmation d'une réservation
        handleConfirmReservation();
        break;
        
    case 'cancel':
        // Annulation d'une réservation
        handleCancelReservation();
        break;
        
    case 'get':
        // Récupération des détails d'une réservation
        handleGetReservation();
        break;
        
    case 'delete':
        // Suppression d'une réservation (uniquement en attente)
        handleDeleteReservation();
        break;
        
    default:
        // Action non reconnue
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Action non reconnue']);
            exit();
        } else {
            $_SESSION['error'] = "Action non reconnue.";
            header('Location: ../Views/gestion_reservations.php');
            exit();
        }
}

/**
 * Gère la création d'une nouvelle réservation par le gérant
 */
function handleAddReservation() {
    global $reservationModel;
    
    // Vérifier si tous les champs requis sont présents
    if (!isset($_POST['client_id']) || !isset($_POST['voiture_id']) || !isset($_POST['date_debut']) || !isset($_POST['date_fin'])) {
        $_SESSION['error'] = "Tous les champs requis doivent être remplis.";
        header('Location: ../Views/gestion_reservations.php');
        exit();
    }
    
    // Récupérer les données du formulaire
    $client_id = intval($_POST['client_id']);
    $voiture_id = intval($_POST['voiture_id']);
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $prix_total = isset($_POST['prix_total']) ? floatval($_POST['prix_total']) : 0;
    $notes = $_POST['notes'] ?? null;
    $offre_id = isset($_POST['offre_id']) ? intval($_POST['offre_id']) : null;
    
    // Vérifier la validité des dates
    if ($date_fin <= $date_debut) {
        $_SESSION['error'] = "La date de fin doit être postérieure à la date de début.";
        header('Location: ../Views/gestion_reservations.php');
        exit();
    }
    
    // Créer la réservation
    $reservation_id = $reservationModel->create(
        $client_id,
        $voiture_id,
        $date_debut,
        $date_fin,
        $prix_total,
        $notes,
        $offre_id
    );
    
    if (!$reservation_id) {
        // En cas d'erreur
        $_SESSION['error'] = "Erreur lors de la création de la réservation: " . $reservationModel->getLastError();
        header('Location: ../Views/gestion_reservations.php');
        exit();
    }
    
    // Succès
    $_SESSION['success'] = "La réservation a été créée avec succès.";
    header('Location: ../Views/gestion_reservations.php');
    exit();
}

/**
 * Gère la mise à jour d'une réservation existante
 */
function handleUpdateReservation() {
    global $reservationModel;
    
    // Vérifier si tous les champs requis sont présents
    if (!isset($_POST['reservation_id']) || !isset($_POST['client_id']) || !isset($_POST['voiture_id']) || 
        !isset($_POST['date_debut']) || !isset($_POST['date_fin']) || !isset($_POST['statut'])) {
        $_SESSION['error'] = "Tous les champs requis doivent être remplis.";
        header('Location: ../Views/gestion_reservations.php');
        exit();
    }
    
    // Récupérer les données du formulaire
    $reservation_id = intval($_POST['reservation_id']);
    $client_id = intval($_POST['client_id']);
    $voiture_id = intval($_POST['voiture_id']);
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $statut = $_POST['statut'];
    $prix_total = isset($_POST['prix_total']) ? floatval($_POST['prix_total']) : 0;
    $notes = $_POST['notes'] ?? null;
    $offre_id = isset($_POST['offre_id']) ? intval($_POST['offre_id']) : null;
    
    // Vérifier la validité des dates
    if ($date_fin <= $date_debut) {
        $_SESSION['error'] = "La date de fin doit être postérieure à la date de début.";
        header('Location: ../Views/gestion_reservations.php');
        exit();
    }
    
    // Mettre à jour la réservation
    $result = $reservationModel->update(
        $reservation_id,
        $client_id,
        $voiture_id,
        $date_debut,
        $date_fin,
        $statut,
        $prix_total,
        $notes,
        $offre_id
    );
    
    if (!$result) {
        // En cas d'erreur
        $_SESSION['error'] = "Erreur lors de la mise à jour de la réservation: " . $reservationModel->getLastError();
        header('Location: ../Views/gestion_reservations.php');
        exit();
    }
    
    // Succès
    $_SESSION['success'] = "La réservation a été mise à jour avec succès.";
    header('Location: ../Views/gestion_reservations.php');
    exit();
}

/**
 * Gère la confirmation d'une réservation
 */
function handleConfirmReservation() {
    global $reservationModel;
    
    // Vérifier si l'ID de réservation est présent
    if (!isset($_POST['reservation_id'])) {
        $_SESSION['error'] = "ID de réservation manquant.";
        header('Location: ../Views/gestion_reservations.php');
        exit();
    }
    
    $reservation_id = intval($_POST['reservation_id']);
    
    // Récupérer la réservation
    $reservation = $reservationModel->getReservationById($reservation_id);
    
    // Vérifier que la réservation existe et est en attente
    if (!$reservation || $reservation['statut'] !== 'en_attente') {
        $_SESSION['error'] = "La réservation est introuvable ou n'est pas en attente de confirmation.";
        header('Location: ../Views/gestion_reservations.php');
        exit();
    }
    
    // Confirmer la réservation
    $result = $reservationModel->updateStatus($reservation_id, 'confirmee');
    
    if (!$result) {
        $_SESSION['error'] = "Erreur lors de la confirmation de la réservation: " . $reservationModel->getLastError();
        header('Location: ../Views/gestion_reservations.php');
        exit();
    }
    
    // Succès
    $_SESSION['success'] = "La réservation a été confirmée avec succès.";
    header('Location: ../Views/gestion_reservations.php');
    exit();
}

/**
 * Gère l'annulation d'une réservation
 */
function handleCancelReservation() {
    global $reservationModel;
    
    // Vérifier si l'ID de réservation est présent
    if (!isset($_POST['reservation_id'])) {
        $_SESSION['error'] = "ID de réservation manquant.";
        header('Location: ../Views/gestion_reservations.php');
        exit();
    }
    
    $reservation_id = intval($_POST['reservation_id']);
    
    // Récupérer la réservation
    $reservation = $reservationModel->getReservationById($reservation_id);
    
    // Vérifier que la réservation existe et peut être annulée
    if (!$reservation || !in_array($reservation['statut'], ['en_attente', 'confirmee'])) {
        $_SESSION['error'] = "La réservation est introuvable ou ne peut pas être annulée.";
        header('Location: ../Views/gestion_reservations.php');
        exit();
    }
    
    // Récupérer le motif d'annulation
    $motif = $_POST['cancel_reason'] ?? "Annulation par l'administrateur";
    
    // Si le motif est "other", utiliser la raison personnalisée
    if ($motif === 'other' && isset($_POST['other_reason']) && !empty($_POST['other_reason'])) {
        $motif = $_POST['other_reason'];
    }
    
    // Annuler la réservation
    $result = $reservationModel->updateStatus($reservation_id, 'annulee', $motif);
    
    if (!$result) {
        $_SESSION['error'] = "Erreur lors de l'annulation de la réservation: " . $reservationModel->getLastError();
        header('Location: ../Views/gestion_reservations.php');
        exit();
    }
    
    // Succès
    $_SESSION['success'] = "La réservation a été annulée avec succès.";
    header('Location: ../Views/gestion_reservations.php');
    exit();
}

/**
 * Récupère les détails d'une réservation (format JSON)
 */
function handleGetReservation() {
    global $reservationModel;
    
    // Vérifier si l'ID est présent
    if (!isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID de réservation manquant']);
        exit();
    }
    
    $reservation_id = intval($_GET['id']);
    
    // Récupérer la réservation avec ses détails
    $reservation = $reservationModel->getReservationById($reservation_id);
    
    // Vérifier que la réservation existe
    if (!$reservation) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Réservation introuvable']);
        exit();
    }
    
    // Renvoyer les données au format JSON
    header('Content-Type: application/json');
    echo json_encode($reservation);
    exit();
}

/**
 * Supprime une réservation en attente
 */
function handleDeleteReservation() {
    global $reservationModel;
    
    // Vérifier si l'ID est présent
    if (!isset($_POST['reservation_id'])) {
        $_SESSION['error'] = "ID de réservation manquant.";
        header('Location: ../Views/gestion_reservations.php');
        exit();
    }
    
    $reservation_id = intval($_POST['reservation_id']);
    
    // Supprimer la réservation
    $result = $reservationModel->delete($reservation_id);
    
    if (!$result) {
        $_SESSION['error'] = "Erreur lors de la suppression de la réservation: " . $reservationModel->getLastError();
        header('Location: ../Views/gestion_reservations.php');
        exit();
    }
    
    // Succès
    $_SESSION['success'] = "La réservation a été supprimée avec succès.";
    header('Location: ../Views/gestion_reservations.php');
    exit();
}
?>