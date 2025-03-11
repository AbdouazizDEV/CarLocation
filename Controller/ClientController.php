<?php
session_start();
require_once __DIR__ . "/../Models/voiture.php";
require_once __DIR__ . "/../Models/offre.php";
require_once __DIR__ . "/../Models/favoris.php";
require_once __DIR__ . "/../Models/Reservation.php";

// Vérifier si l'utilisateur est connecté et est un client
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'client') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé.']);
    exit();
}

// Instancier les objets nécessaires
$voitureModel = new Voiture();
$offreModel = new Offre();
$favorisModel = new Favoris();
$reservationModel = new Reservation();

// Déterminer l'action à effectuer
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// Traiter l'action
switch ($action) {
    // Actions pour les favoris
    case 'toggle_favori_offre':
        handleToggleFavoriOffre();
        break;
    
    case 'toggle_favori_voiture':
        handleToggleFavoriVoiture();
        break;
    
    // Actions pour les détails
    case 'get_offre_details':
        handleGetOffreDetails();
        break;
    
    case 'get_voiture_details':
        handleGetVoitureDetails();
        break;
    
    // Actions pour les réservations
    case 'reserver_offre':
        handleReserverOffre();
        break;
    
    case 'reserver_voiture':
        handleReserverVoiture();
        break;
    
    // Actions pour les filtres
    case 'filter_voitures':
        handleFilterVoitures();
        break;
    
    // Action par défaut
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Action non reconnue.']);
        break;
}

// === Fonctions de gestion des favoris ===

/**
 * Gère l'ajout/suppression d'une offre aux favoris
 */
function handleToggleFavoriOffre() {
    global $favorisModel;
    
    // Vérifier les données requises
    if (!isset($_POST['offre_id']) || !isset($_POST['client_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
        exit();
    }
    
    $offreId = intval($_POST['offre_id']);
    $clientId = intval($_POST['client_id']);
    
    // Vérifier si l'offre est déjà en favoris
    $isFavorite = $favorisModel->isOffreFavorite($clientId, $offreId);
    
    if ($isFavorite) {
        // Supprimer des favoris
        $result = $favorisModel->removeOffreFavorite($clientId, $offreId);
        $added = false;
    } else {
        // Ajouter aux favoris
        $result = $favorisModel->addOffreFavorite($clientId, $offreId);
        $added = true;
    }
    
    // Retourner le résultat
    header('Content-Type: application/json');
    if ($result) {
        echo json_encode(['success' => true, 'added' => $added]);
    } else {
        echo json_encode(['success' => false, 'message' => $favorisModel->getLastError()]);
    }
}

/**
 * Gère l'ajout/suppression d'un véhicule aux favoris
 */
function handleToggleFavoriVoiture() {
    global $favorisModel;
    
    // Vérifier les données requises
    if (!isset($_POST['voiture_id']) || !isset($_POST['client_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
        exit();
    }
    
    $voitureId = intval($_POST['voiture_id']);
    $clientId = intval($_POST['client_id']);
    
    // Vérifier si le véhicule est déjà en favoris
    $isFavorite = $favorisModel->isVoitureFavorite($clientId, $voitureId);
    
    if ($isFavorite) {
        // Supprimer des favoris
        $result = $favorisModel->removeVoitureFavorite($clientId, $voitureId);
        $added = false;
    } else {
        // Ajouter aux favoris
        $result = $favorisModel->addVoitureFavorite($clientId, $voitureId);
        $added = true;
    }
    
    // Retourner le résultat
    header('Content-Type: application/json');
    if ($result) {
        echo json_encode(['success' => true, 'added' => $added]);
    } else {
        echo json_encode(['success' => false, 'message' => $favorisModel->getLastError()]);
    }
}

// === Fonctions pour obtenir les détails ===

/**
 * Récupère les détails d'une offre
 */
function handleGetOffreDetails() {
    global $offreModel;
    
    // Vérifier les données requises
    if (!isset($_GET['offre_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID de l\'offre manquant.']);
        exit();
    }
    
    $offreId = intval($_GET['offre_id']);
    
    // Récupérer les détails de l'offre
    $offre = $offreModel->getOffreWithVehicules($offreId);
    
    // Retourner les détails
    header('Content-Type: application/json');
    if ($offre) {
        echo json_encode(['success' => true, 'offre' => $offre]);
    } else {
        echo json_encode(['success' => false, 'message' => $offreModel->getLastError() ?: 'Offre non trouvée.']);
    }
}

/**
 * Récupère les détails d'un véhicule
 */
function handleGetVoitureDetails() {
    global $voitureModel;
    
    // Vérifier les données requises
    if (!isset($_GET['voiture_id'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID du véhicule manquant.']);
        exit();
    }
    
    $voitureId = intval($_GET['voiture_id']);
    
    // Récupérer les détails du véhicule
    $voiture = $voitureModel->getVoitureById($voitureId);
    
    // Retourner les détails
    header('Content-Type: application/json');
    if ($voiture) {
        echo json_encode(['success' => true, 'voiture' => $voiture]);
    } else {
        echo json_encode(['success' => false, 'message' => $voitureModel->getLastError() ?: 'Véhicule non trouvé.']);
    }
}

// === Fonctions pour les réservations ===

/**
 * Gère la réservation avec une offre
 */
function handleReserverOffre() {
    global $reservationModel, $offreModel, $voitureModel;
    
    // Vérifier les données requises
    $requiredFields = ['client_id', 'vehicule_id', 'date_debut', 'date_fin', 'offre_id', 'accept_terms'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $_SESSION['error'] = "Tous les champs requis doivent être remplis.";
            header('Location: ../Views/AcceuilClient.php');
            exit();
        }
    }
    
    $clientId = intval($_POST['client_id']);
    $vehiculeId = intval($_POST['vehicule_id']);
    $offreId = intval($_POST['offre_id']);
    $dateDebut = $_POST['date_debut'];
    $dateFin = $_POST['date_fin'];
    $codePromo = isset($_POST['code_promo']) ? $_POST['code_promo'] : null;
    
    // Valider les dates
    $today = date('Y-m-d');
    if ($dateDebut < $today) {
        $_SESSION['error'] = "La date de début ne peut pas être antérieure à aujourd'hui.";
        header('Location: ../Views/AcceuilClient.php');
        exit();
    }
    
    if ($dateFin <= $dateDebut) {
        $_SESSION['error'] = "La date de fin doit être postérieure à la date de début.";
        header('Location: ../Views/AcceuilClient.php');
        exit();
    }
    
    // Récupérer les informations de l'offre et du véhicule
    $offre = $offreModel->getOffreById($offreId);
    $vehicule = $voitureModel->getVoitureById($vehiculeId);
    
    if (!$offre || !$vehicule) {
        $_SESSION['error'] = "Offre ou véhicule introuvable.";
        header('Location: ../Views/AcceuilClient.php');
        exit();
    }
    
    // Vérifier que l'offre est active et que le véhicule est associé à l'offre
    $vehiculesOffre = $offreModel->getVehiculesForOffre($offreId);
    $vehiculeIds = array_column($vehiculesOffre, 'id');
    
    if (!in_array($vehiculeId, $vehiculeIds)) {
        $_SESSION['error'] = "Ce véhicule n'est pas associé à l'offre sélectionnée.";
        header('Location: ../Views/AcceuilClient.php');
        exit();
    }
    
    // Calculer le prix total avec la réduction
    $prixJournalier = $vehicule['prix_location'];
    $reduction = $offre['reduction'];
    $prixJournalierReduit = $prixJournalier * (1 - $reduction / 100);
    
    // Calculer la durée en jours
    $date1 = new DateTime($dateDebut);
    $date2 = new DateTime($dateFin);
    $duree = $date1->diff($date2)->days;
    
    $prixTotal = $prixJournalierReduit * $duree;
    
    // Créer la réservation
    $reservationId = $reservationModel->create(
        $clientId,
        $vehiculeId,
        $dateDebut,
        $dateFin,
        $prixTotal,
        "Réservation avec offre spéciale: {$offre['titre']} (-{$reduction}%)",
        $offreId
    );
    
    if ($reservationId) {
        $_SESSION['success'] = "Votre réservation a été enregistrée avec succès. Référence: #" . $reservationId;
        header('Location: ../Views/reservations.php');
    } else {
        $_SESSION['error'] = "Erreur lors de la réservation: " . $reservationModel->getLastError();
        header('Location: ../Views/AcceuilClient.php');
    }
}

/**
 * Gère la réservation d'un véhicule (sans offre)
 */
function handleReserverVoiture() {
    global $reservationModel, $voitureModel, $offreModel;
    
    // Vérifier les données requises
    $requiredFields = ['client_id', 'vehicule_id', 'date_debut', 'date_fin', 'accept_terms'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $_SESSION['error'] = "Tous les champs requis doivent être remplis.";
            header('Location: ../Views/AcceuilClient.php');
            exit();
        }
    }
    
    $clientId = intval($_POST['client_id']);
    $vehiculeId = intval($_POST['vehicule_id']);
    $dateDebut = $_POST['date_debut'];
    $dateFin = $_POST['date_fin'];
    $codePromo = isset($_POST['code_promo']) ? $_POST['code_promo'] : null;
    
    // Valider les dates
    $today = date('Y-m-d');
    if ($dateDebut < $today) {
        $_SESSION['error'] = "La date de début ne peut pas être antérieure à aujourd'hui.";
        header('Location: ../Views/AcceuilClient.php');
        exit();
    }
    
    if ($dateFin <= $dateDebut) {
        $_SESSION['error'] = "La date de fin doit être postérieure à la date de début.";
        header('Location: ../Views/AcceuilClient.php');
        exit();
    }
    
    // Récupérer les informations du véhicule
    $vehicule = $voitureModel->getVoitureById($vehiculeId);
    
    if (!$vehicule) {
        $_SESSION['error'] = "Véhicule introuvable.";
        header('Location: ../Views/AcceuilClient.php');
        exit();
    }
    
    // Vérifier si un code promo est utilisé
    $offreId = null;
    $reduction = 0;
    $noteOffre = "";
    
    if ($codePromo) {
        // Rechercher une offre correspondant au code promo
        $offre = $offreModel->getOffreByCode($codePromo);
        if ($offre) {
            // Vérifier que l'offre est active et que sa période est valide
            $today = date('Y-m-d');
            if ($offre['statut'] === 'active' && $offre['date_debut'] <= $today && $offre['date_fin'] >= $today) {
                $offreId = $offre['id'];
                $reduction = $offre['reduction'];
                $noteOffre = "Code promo appliqué: {$codePromo} (-{$reduction}%)";
            }
        }
    }
    
    // Calculer le prix total
    $prixJournalier = $vehicule['prix_location'];
    $prixJournalierReduit = $prixJournalier * (1 - $reduction / 100);
    
    // Calculer la durée en jours
    $date1 = new DateTime($dateDebut);
    $date2 = new DateTime($dateFin);
    $duree = $date1->diff($date2)->days;
    
    $prixTotal = $prixJournalierReduit * $duree;
    
    // Créer la réservation
    $notes = "Réservation standard";
    if (!empty($noteOffre)) {
        $notes .= " - " . $noteOffre;
    }
    
    $reservationId = $reservationModel->create(
        $clientId,
        $vehiculeId,
        $dateDebut,
        $dateFin,
        $prixTotal,
        $notes,
        $offreId
    );
    
    if ($reservationId) {
        $_SESSION['success'] = "Votre réservation a été enregistrée avec succès. Référence: #" . $reservationId;
        header('Location: ../Views/reservations.php');
    } else {
        $_SESSION['error'] = "Erreur lors de la réservation: " . $reservationModel->getLastError();
        header('Location: ../Views/AcceuilClient.php');
    }
}

// === Fonctions pour les filtres ===

/**
 * Filtre les véhicules selon les critères
 */
function handleFilterVoitures() {
    global $voitureModel, $favorisModel;
    
    $categorie = isset($_GET['categorie']) ? $_GET['categorie'] : '';
    $prixMax = isset($_GET['prix_max']) ? intval($_GET['prix_max']) : 0;
    $sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'newest';
    
    // Préparer les critères de recherche
    $criteria = [];
    
    if (!empty($categorie)) {
        $criteria['categorie'] = $categorie;
    }
    
    if ($prixMax > 0) {
        $criteria['prix_max'] = $prixMax;
    }
    
    // Limiter la recherche aux véhicules disponibles
    $criteria['disponibilite'] = 1; // 1 = disponible
    
    // Effectuer la recherche
    $voitures = $voitureModel->searchVoitures($criteria);
    
    // Trier les résultats
    if (!empty($voitures)) {
        switch ($sortBy) {
            case 'price_asc':
                usort($voitures, function($a, $b) {
                    return $a['prix_location'] - $b['prix_location'];
                });
                break;
            
            case 'price_desc':
                usort($voitures, function($a, $b) {
                    return $b['prix_location'] - $a['prix_location'];
                });
                break;
            
            case 'newest':
            default:
                // Par défaut, les résultats sont déjà triés par ID décroissant (les plus récents en premier)
                break;
        }
        
        // Marquer les favoris du client
        $clientId = $_SESSION['user_id'];
        $clientFavoris = $favorisModel->getFavorisByClientId($clientId);
        $favorisIds = array_column($clientFavoris, 'offre_id');
        
        foreach ($voitures as &$voiture) {
            $voiture['is_favorite'] = in_array($voiture['id'], $favorisIds);
        }
    }
    
    // Retourner les résultats
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'voitures' => $voitures]);
}
?>