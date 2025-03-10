<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . "/../Models/Reservation.php";
require_once __DIR__ . "/../Models/offre.php";
require_once __DIR__ . "/../Models/voiture.php";

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user']) || !isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour effectuer cette action.";
    header('Location: ../Views/login.php');
    exit;
}

// Vérifier que l'utilisateur est un client
if ($_SESSION['user_role'] !== 'client') {
    $_SESSION['error'] = "Seuls les clients peuvent effectuer des réservations.";
    header('Location: ../Views/login.php');
    exit;
}

$reservationModel = new Reservation();
$offreModel = new Offre();
$voitureModel = new Voiture();

// Action par défaut
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Traiter les différentes actions
switch ($action) {
    case 'create':
        // Récupérer les données du formulaire
        $clientId = isset($_POST['client_id']) ? intval($_POST['client_id']) : $_SESSION['user_id'];
        $vehiculeId = isset($_POST['vehicule_id']) ? intval($_POST['vehicule_id']) : 0;
        $dateDebut = isset($_POST['date_debut']) ? $_POST['date_debut'] : null;
        $dateFin = isset($_POST['date_fin']) ? $_POST['date_fin'] : null;
        $offreId = isset($_POST['offre_id']) ? intval($_POST['offre_id']) : null;
        $codePromo = isset($_POST['code_promo']) ? $_POST['code_promo'] : null;
        
        // Validation des données
        if (!$vehiculeId || !$dateDebut || !$dateFin) {
            $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
            header('Location: ../Views/AcceuilClient.php');
            exit;
        }
        
        // Vérifier que le véhicule est disponible
        $vehicule = $voitureModel->getVoitureById($vehiculeId);
        if (!$vehicule || $vehicule['disponibilite'] != 1) {
            $_SESSION['error'] = "Ce véhicule n'est pas disponible à la réservation.";
            header('Location: ../Views/AcceuilClient.php');
            exit;
        }
        
        // Vérifier manuellement la disponibilité du véhicule pour les dates sélectionnées
        $isAvailable = $reservationModel->isVehicleAvailable($vehiculeId, $dateDebut, $dateFin);
        if (!$isAvailable) {
            $_SESSION['error'] = "Ce véhicule n'est pas disponible pour les dates sélectionnées.";
            header('Location: ../Views/AcceuilClient.php');
            exit;
        }
        
        // Calculer la durée de location en jours pour obtenir le prix total
        $debut = new DateTime($dateDebut);
        $fin = new DateTime($dateFin);
        $duree = $debut->diff($fin)->days;
        
        // Calculer le prix avec réduction si une offre est sélectionnée
        if ($offreId) {
            $prixDetails = $offreModel->calculateDiscountedPrice($vehiculeId, $offreId);
            if ($prixDetails) {
                $prixBase = $vehicule['prix_location'];
                $reduction = $prixDetails['discount'];
                $prixJour = $prixDetails['discounted_price'];
                $prixTotal = $prixJour * $duree;
            } else {
                $prixBase = $vehicule['prix_location'];
                $prixTotal = $prixBase * $duree;
                $reduction = 0;
            }
        } else {
            $prixBase = $vehicule['prix_location'];
            $prixTotal = $prixBase * $duree;
            $reduction = 0;
        }
        
        // Vérifier le code promo si fourni
        if ($codePromo && !$offreId) {
            $offre = $offreModel->validatePromoCode($codePromo);
            if ($offre) {
                // Vérifier que le véhicule est associé à cette offre
                $vehiculesOffre = $offreModel->getVehiculesForOffre($offre['id']);
                $vehiculeIds = array_column($vehiculesOffre, 'id');
                
                if (in_array($vehiculeId, $vehiculeIds)) {
                    $reduction = $offre['reduction'];
                    $prixTotal = $prixBase * $duree * (1 - $reduction / 100);
                    $offreId = $offre['id'];
                }
            }
        }
        
        // Préparer une note pour la réservation
        $notes = "Réservation effectuée en ligne. Durée: {$duree} jours.";
        if ($reduction > 0) {
            $notes .= " Réduction appliquée: {$reduction}%.";
        }
        
        // Créer la réservation
        $result = $reservationModel->create(
            $clientId,
            $vehiculeId,
            $dateDebut,
            $dateFin,
            $prixTotal,
            $notes,
            $offreId
        );
        
        if ($result) {
            $_SESSION['success'] = "Votre réservation a été effectuée avec succès !";
            header('Location: ../Views/reservations.php');
        } else {
            $_SESSION['error'] = "Erreur lors de la création de la réservation : " . $reservationModel->getLastError();
            header('Location: ../Views/AcceuilClient.php');
        }
        break;
        
    case 'cancel':
        // Annuler une réservation
        $reservationId = isset($_POST['reservation_id']) ? intval($_POST['reservation_id']) : 0;
        
        if (!$reservationId) {
            $_SESSION['error'] = "ID de réservation invalide.";
            header('Location: ../Views/reservations.php');
            exit;
        }
        
        // Vérifier que la réservation appartient bien à l'utilisateur
        $reservation = $reservationModel->getReservationById($reservationId);
        if (!$reservation || $reservation['utilisateur_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = "Vous n'avez pas l'autorisation d'annuler cette réservation.";
            header('Location: ../Views/reservations.php');
            exit;
        }
        
        // Vérifier que la réservation est dans un état où elle peut être annulée
        if (!in_array($reservation['statut'], ['en_attente', 'confirmee'])) {
            $_SESSION['error'] = "Cette réservation ne peut plus être annulée car son statut est : " . $reservation['statut'];
            header('Location: ../Views/reservations.php');
            exit;
        }
        
        $result = $reservationModel->updateStatus($reservationId, 'annulee', "Annulation par le client via l'interface web");
        
        if ($result) {
            $_SESSION['success'] = "Votre réservation a été annulée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'annulation de la réservation : " . $reservationModel->getLastError();
        }
        
        header('Location: ../Views/reservations.php');
        break;
        
    default:
        // Action inconnue, rediriger vers la page des réservations
        header('Location: ../Views/reservations.php');
        break;
}
?>