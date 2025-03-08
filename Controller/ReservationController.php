<?php
 ini_set('display_errors', 1);
 ini_set('display_startup_errors', 1);
 error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/../Models/Reservation.php";
require_once __DIR__ . "/../Models/voiture.php";
require_once __DIR__ . "/../Models/client.php";

// Vérifier si l'utilisateur est connecté et est un gérant
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'gérant') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
        // Pour les requêtes AJAX de récupération de données, renvoyer une erreur JSON
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Accès non autorisé']);
        exit();
    } else {
        // Pour les autres requêtes, rediriger vers la page de connexion
        header('Location: ../Views/login.php');
        exit();
    }
}

// Instanciation des modèles
$reservationModel = new Reservation();
$voitureModel = new Voiture();
$clientModel = new Client();

// Traitement selon l'action demandée
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            // Création d'une nouvelle réservation
            $client_id = $_POST['client_id'] ?? '';
            $voiture_id = $_POST['voiture_id'] ?? '';
            $date_debut = $_POST['date_debut'] ?? '';
            $date_fin = $_POST['date_fin'] ?? '';
            $prix_total = $_POST['prix_total'] ?? 1;
            $notes = $_POST['notes'] ?? null;
            
            // Validation des données
            $errors = [];
            
            if (empty($client_id)) {
                $errors[] = "Veuillez sélectionner un client.";
            }
            
            if (empty($voiture_id)) {
                $errors[] = "Veuillez sélectionner un véhicule.";
            }
            
            if (empty($date_debut) || empty($date_fin)) {
                $errors[] = "Les dates de début et de fin sont obligatoires.";
            } elseif (strtotime($date_debut) > strtotime($date_fin)) {
                $errors[] = "La date de début doit être antérieure à la date de fin.";
            } elseif (strtotime($date_debut) < strtotime(date('Y-m-d'))) {
                $errors[] = "La date de début ne peut pas être dans le passé.";
            }
            
            if (empty($prix_total) || $prix_total <= 0) {
                $errors[] = "Le prix total doit être supérieur à zéro.";
            }
            
            if (empty($errors)) {
                // Récupérer l'ID utilisateur associé au client
                $client = $clientModel->getClientById($client_id);
                if (!$client) {
                    $_SESSION['error'] = "Client non trouvé.";
                    header("Location: ../Views/gestion_reservations.php");
                    exit();
                }
                
                $utilisateur_id = $client['utilisateur_id'];
                
                // Création de la réservation
                $result = $reservationModel->create(
                    $utilisateur_id,
                    $voiture_id,
                    $date_debut,
                    $date_fin,
                    $prix_total,
                    $notes
                );
                
                if ($result) {
                    $_SESSION['success'] = "La réservation a été créée avec succès.";
                } else {
                    $_SESSION['error'] = "Erreur lors de la création de la réservation : " . $reservationModel->getLastError();
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
            }
            
            header("Location: ../Views/gestion_reservations.php");
            exit();
            break;
            
        case 'update':
            // Mise à jour d'une réservation existante
            $reservation_id = $_POST['reservation_id'] ?? '';
            $client_id = $_POST['client_id'] ?? '';
            $voiture_id = $_POST['voiture_id'] ?? '';
            $date_debut = $_POST['date_debut'] ?? '';
            $date_fin = $_POST['date_fin'] ?? '';
            $statut = $_POST['statut'] ?? '';
            $prix_total = $_POST['prix_total'] ?? 0;
            $notes = $_POST['notes'] ?? null;
            
            // Validation des données
            $errors = [];
            
            if (empty($reservation_id)) {
                $errors[] = "ID de réservation manquant.";
            }
            
            if (empty($client_id)) {
                $errors[] = "Veuillez sélectionner un client.";
            }
            
            if (empty($voiture_id)) {
                $errors[] = "Veuillez sélectionner un véhicule.";
            }
            
            if (empty($date_debut) || empty($date_fin)) {
                $errors[] = "Les dates de début et de fin sont obligatoires.";
            } elseif (strtotime($date_debut) > strtotime($date_fin)) {
                $errors[] = "La date de début doit être antérieure à la date de fin.";
            }
            
            if (empty($statut)) {
                $errors[] = "Le statut est obligatoire.";
            }
            
            if (empty($prix_total) || $prix_total <= 0) {
                $errors[] = "Le prix total doit être supérieur à zéro.";
            }
            
            if (empty($errors)) {
                // Récupérer l'ID utilisateur associé au client
                $client = $clientModel->getClientById($client_id);
                if (!$client) {
                    $_SESSION['error'] = "Client non trouvé.";
                    header("Location: ../Views/gestion_reservations.php");
                    exit();
                }
                
                $utilisateur_id = $client['utilisateur_id'];
                
                // Mise à jour de la réservation
                $result = $reservationModel->update(
                    $reservation_id,
                    $utilisateur_id,
                    $voiture_id,
                    $date_debut,
                    $date_fin,
                    $statut,
                    $prix_total,
                    $notes
                );
                
                if ($result) {
                    $_SESSION['success'] = "La réservation a été mise à jour avec succès.";
                } else {
                    $_SESSION['error'] = "Erreur lors de la mise à jour de la réservation : " . $reservationModel->getLastError();
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
            }
            
            header("Location: ../Views/gestion_reservations.php");
            exit();
            break;
            
        case 'confirm':
            // Confirmation d'une réservation
            $reservation_id = $_POST['reservation_id'] ?? '';
            
            if (empty($reservation_id)) {
                $_SESSION['error'] = "ID de réservation manquant.";
                header("Location: ../Views/gestion_reservations.php");
                exit();
            }
            
            $result = $reservationModel->updateStatus($reservation_id, 'confirmee');
            
            if ($result) {
                $_SESSION['success'] = "La réservation a été confirmée avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la confirmation de la réservation : " . $reservationModel->getLastError();
            }
            
            header("Location: ../Views/gestion_reservations.php");
            exit();
            break;
            
        case 'cancel':
            // Annulation d'une réservation
            $reservation_id = $_POST['reservation_id'] ?? '';
            $cancel_reason = $_POST['cancel_reason'] ?? '';
            $other_reason = $_POST['other_reason'] ?? '';
            
            if (empty($reservation_id)) {
                $_SESSION['error'] = "ID de réservation manquant.";
                header("Location: ../Views/gestion_reservations.php");
                exit();
            }
            
            if (empty($cancel_reason)) {
                $_SESSION['error'] = "Veuillez sélectionner un motif d'annulation.";
                header("Location: ../Views/gestion_reservations.php");
                exit();
            }
            
            // Si le motif est "Autre", utiliser le motif spécifié
            $motif = ($cancel_reason === 'other' && !empty($other_reason)) ? $other_reason : $cancel_reason;
            
            $result = $reservationModel->updateStatus($reservation_id, 'annulee', $motif);
            
            if ($result) {
                $_SESSION['success'] = "La réservation a été annulée avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de l'annulation de la réservation : " . $reservationModel->getLastError();
            }
            
            header("Location: ../Views/gestion_reservations.php");
            exit();
            break;
            
        case 'start':
            // Démarrer la location (passage à "en cours")
            $reservation_id = $_POST['reservation_id'] ?? '';
            $km_depart = $_POST['km_depart'] ?? 0;
            $etat_depart = $_POST['etat_depart'] ?? '';
            
            if (empty($reservation_id)) {
                $_SESSION['error'] = "ID de réservation manquant.";
                header("Location: ../Views/gestion_reservations.php");
                exit();
            }
            
            // Mise à jour du statut de la réservation
            $result = $reservationModel->updateStatus($reservation_id, 'en_cours');
            
            if ($result) {
                $_SESSION['success'] = "La location a été démarrée avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors du démarrage de la location : " . $reservationModel->getLastError();
            }
            
            header("Location: ../Views/gestion_reservations.php");
            exit();
            break;
            
        case 'end':
            // Terminer la location
            $reservation_id = $_POST['reservation_id'] ?? '';
            $km_retour = $_POST['km_retour'] ?? 0;
            $etat_retour = $_POST['etat_retour'] ?? '';
            $frais_supplementaires = $_POST['frais_supplementaires'] ?? 0;
            
            if (empty($reservation_id)) {
                $_SESSION['error'] = "ID de réservation manquant.";
                header("Location: ../Views/gestion_reservations.php");
                exit();
            }
            
            // Mise à jour du statut de la réservation
            $result = $reservationModel->updateStatus($reservation_id, 'terminee');
            
            if ($result) {
                $_SESSION['success'] = "La location a été terminée avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la clôture de la location : " . $reservationModel->getLastError();
            }
            
            header("Location: ../Views/gestion_reservations.php");
            exit();
            break;
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    
    switch ($action) {
        case 'get':
            // Récupérer les détails d'une réservation au format JSON
            $id = intval($_GET['id'] ?? 0);
            
            if (empty($id)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'ID de réservation manquant']);
                exit();
            }
            
            $reservation = $reservationModel->getReservationById($id);
            
            if (!$reservation) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Réservation non trouvée']);
                exit();
            }
            
            header('Content-Type: application/json');
            echo json_encode($reservation);
            exit();
            break;
    }
}

// Par défaut, rediriger vers la page de gestion des réservations
header("Location: ../Views/gestion_reservations.php");
exit();
?>