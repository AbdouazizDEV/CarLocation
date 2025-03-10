<?php
/**
 * Contrôleur pour gérer les réservations côté client
 * 
 * Ce fichier gère toutes les opérations liées aux réservations effectuées par les clients :
 * - Création de réservation
 * - Consultation de réservation
 * - Annulation de réservation
 * - Confirmation de réservation
 */

session_start();
require_once __DIR__ . "/../Models/Reservation.php";
require_once __DIR__ . "/../Models/voiture.php";
require_once __DIR__ . "/../Models/client.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    // Rediriger vers la page de connexion avec un message d'erreur
    $_SESSION['error'] = "Vous devez être connecté pour effectuer cette action.";
    header('Location: ../Views/login.php');
    exit();
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
        
    case 'cancel':
        // Annulation d'une réservation
        handleCancelReservation();
        break;
        
    case 'get':
        // Récupération des détails d'une réservation
        handleGetReservation();
        break;
        
    case 'list':
        // Liste des réservations de l'utilisateur
        handleListReservations();
        break;
        
    case 'check_availability':
        // Vérification de la disponibilité d'un véhicule
        handleCheckAvailability();
        break;
        
    default:
        // Action non reconnue
        $_SESSION['error'] = "Action non reconnue.";
        header('Location: ../Views/reservations_client.php');
        exit();
}

/**
 * Gère la création d'une nouvelle réservation
 */
function handleAddReservation() {
    global $reservationModel, $voitureModel;
    
    // Vérifier si tous les champs requis sont présents
    if (!isset($_POST['voiture_id']) || !isset($_POST['date_debut']) || !isset($_POST['date_fin'])) {
        $_SESSION['error'] = "Tous les champs requis doivent être remplis.";
        header('Location: ../Views/reservations_client.php');
        exit();
    }
    
    // Récupérer l'ID de l'utilisateur connecté
    $utilisateur_id = $_SESSION['user_id'];
    
    // Récupérer les données du formulaire
    $voiture_id = intval($_POST['voiture_id']);
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $prix_total = isset($_POST['prix_total']) ? floatval($_POST['prix_total']) : 0;
    $notes = $_POST['notes'] ?? null;
    $offre_id = isset($_POST['offre_id']) ? intval($_POST['offre_id']) : null;
    
    // Vérifier la validité des dates
    $today = date('Y-m-d');
    if ($date_debut < $today) {
        $_SESSION['error'] = "La date de début ne peut pas être antérieure à aujourd'hui.";
        header('Location: ../Views/reservations_client.php');
        exit();
    }
    
    if ($date_fin <= $date_debut) {
        $_SESSION['error'] = "La date de fin doit être postérieure à la date de début.";
        header('Location: ../Views/reservations_client.php');
        exit();
    }
    
    // Vérifier le prix total
    if ($prix_total <= 0) {
        // Calculer le prix total si non fourni ou invalide
        $vehicule = $voitureModel->getVoitureById($voiture_id);
        if (!$vehicule) {
            $_SESSION['error'] = "Véhicule non trouvé.";
            header('Location: ../Views/reservations_client.php');
            exit();
        }
        
        $debut = new DateTime($date_debut);
        $fin = new DateTime($date_fin);
        $duree = $debut->diff($fin)->days;
        $prix_total = $vehicule['prix_location'] * $duree;
    }
    
    // Créer la réservation
    $reservation_id = $reservationModel->create(
        $utilisateur_id,
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
    $_SESSION['success'] = "Votre réservation a été enregistrée avec succès! Un agent vous contactera prochainement pour la confirmation.";
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
        header('Location: ../Views/reservations_client.php');
        exit();
    }
    
    $reservation_id = intval($_POST['reservation_id']);
    $utilisateur_id = $_SESSION['user_id'];
    
    // Récupérer la réservation
    $reservation = $reservationModel->getReservationById($reservation_id);
    
    // Vérifier que la réservation existe et appartient à l'utilisateur
    if (!$reservation || $reservation['utilisateur_id'] != $utilisateur_id) {
        $_SESSION['error'] = "Réservation introuvable ou vous n'êtes pas autorisé à l'annuler.";
        header('Location: ../Views/reservations_client.php');
        exit();
    }
    
    // Vérifier que la réservation peut être annulée (en attente ou confirmée)
    if (!in_array($reservation['statut'], ['en_attente', 'confirmee'])) {
        $_SESSION['error'] = "Cette réservation ne peut plus être annulée.";
        header('Location: ../Views/reservations_client.php');
        exit();
    }
    
    // Récupérer le motif d'annulation
    $motif = $_POST['cancel_reason'] ?? "Annulation par le client";
    
    // Si le motif est "other", utiliser la raison personnalisée
    if ($motif === 'other' && isset($_POST['other_reason']) && !empty($_POST['other_reason'])) {
        $motif = $_POST['other_reason'];
    }
    
    // Annuler la réservation
    $result = $reservationModel->updateStatus($reservation_id, 'annulee', $motif);
    
    if (!$result) {
        $_SESSION['error'] = "Erreur lors de l'annulation de la réservation: " . $reservationModel->getLastError();
        header('Location: ../Views/reservations_client.php');
        exit();
    }
    
    // Succès
    $_SESSION['success'] = "Votre réservation a été annulée avec succès.";
    header('Location: ../Views/reservations_client.php');
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
    $utilisateur_id = $_SESSION['user_id'];
    
    // Récupérer la réservation avec ses détails
    $reservation = $reservationModel->getReservationById($reservation_id);
    
    // Vérifier que la réservation existe et appartient à l'utilisateur (si non gérant)
    if (!$reservation || ($reservation['utilisateur_id'] != $utilisateur_id && $_SESSION['user_role'] !== 'gérant')) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Réservation introuvable ou accès non autorisé']);
        exit();
    }
    
    // Renvoyer les données au format JSON
    header('Content-Type: application/json');
    echo json_encode($reservation);
    exit();
}

/**
 * Récupère la liste des réservations de l'utilisateur (format JSON)
 */
function handleListReservations() {
    global $reservationModel, $voitureModel;
    
    $utilisateur_id = $_SESSION['user_id'];
    
    // Récupérer toutes les réservations de l'utilisateur
    $reservations = $reservationModel->getReservationsByClient($utilisateur_id);
    
    // Enrichir les données avec les informations des véhicules
    foreach ($reservations as &$reservation) {
        $vehicule = $voitureModel->getVoitureById($reservation['voiture_id']);
        
        // Ajouter les informations du véhicule à la réservation
        $reservation['vehicule'] = $vehicule ? [
            'marque' => $vehicule['marque'],
            'modele' => $vehicule['modele'],
            'categorie' => $vehicule['categorie'],
            'prix_location' => $vehicule['prix_location'],
            'images' => $vehicule['images']
        ] : null;
        
        // Calculer la durée en jours
        $debut = new DateTime($reservation['date_debut']);
        $fin = new DateTime($reservation['date_fin']);
        $reservation['duree'] = $debut->diff($fin)->days;
    }
    
    // Renvoyer les données au format JSON
    header('Content-Type: application/json');
    echo json_encode($reservations);
    exit();
}

/**
 * Vérifie la disponibilité d'un véhicule pour des dates données
 */
function handleCheckAvailability() {
    global $reservationModel;
    
    // Vérifier si tous les paramètres sont présents
    if (!isset($_GET['voiture_id']) || !isset($_GET['date_debut']) || !isset($_GET['date_fin'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Paramètres manquants']);
        exit();
    }
    
    $voiture_id = intval($_GET['voiture_id']);
    $date_debut = $_GET['date_debut'];
    $date_fin = $_GET['date_fin'];
    
    // Vérifier la disponibilité
    $isAvailable = $reservationModel->isVehicleAvailable($voiture_id, $date_debut, $date_fin);
    
    // Renvoyer le résultat
    header('Content-Type: application/json');
    echo json_encode([
        'available' => $isAvailable,
        'error' => $isAvailable ? null : $reservationModel->getLastError()
    ]);
    exit();
}
?>