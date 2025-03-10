<?php
/**
 * Contrôleur pour gérer les clients (côté administrateur/gérant)
 * 
 * Ce fichier gère toutes les opérations liées aux clients :
 * - Création, modification, suppression de client
 * - Consultation des détails d'un client
 * - Activation/désactivation d'un client
 * - Gestion des notes et commentaires
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/../Models/client.php";
require_once __DIR__ . "/../Models/Reservation.php";

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
        header('Location: ../View/login.php');
        exit();
    }
}

// Instancier les modèles nécessaires
$clientModel = new Client();
$reservationModel = new Reservation();

// Récupérer l'action demandée
$action = $_POST['action'] ?? $_GET['action'] ?? null;

// Traiter l'action en fonction de la demande
switch ($action) {
    case 'add':
        // Création d'un nouveau client
        handleAddClient();
        break;
        
    case 'update':
        // Mise à jour d'un client existant
        handleUpdateClient();
        break;
        
    case 'activate':
        // Activation d'un client
        handleActivateClient();
        break;
        
    case 'deactivate':
        // Désactivation d'un client
        handleDeactivateClient();
        break;
        
    case 'get':
        // Récupération des détails d'un client
        handleGetClient();
        break;
        
    case 'getReservations':
        // Récupération des réservations d'un client
        handleGetClientReservations();
        break;
        
    case 'getPayments':
        // Récupération des paiements d'un client
        handleGetClientPayments();
        break;
        
    case 'getFavorites':
        // Récupération des favoris d'un client
        handleGetClientFavorites();
        break;
        
    case 'getNotes':
        // Récupération des notes d'un client
        handleGetClientNotes();
        break;
        
    case 'addNote':
        // Ajout d'une note sur un client
        handleAddClientNote();
        break;
        
    default:
        // Action non reconnue
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Action non reconnue']);
            exit();
        } else {
            $_SESSION['error'] = "Action non reconnue.";
            header('Location: ../Views/gestion_clients.php');
            exit();
        }
}

/**
 * Gère la création d'un nouveau client
 */
function handleAddClient() {
    global $clientModel;
    
    // Vérifier si tous les champs requis sont présents
    if (!isset($_POST['nom']) || !isset($_POST['prenom']) || !isset($_POST['email']) || !isset($_POST['mot_de_passe'])) {
        $_SESSION['error'] = "Tous les champs requis doivent être remplis.";
        header('Location: ../Views/gestion_clients.php');
        exit();
    }
    
    // Récupérer les données du formulaire
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $telephone = $_POST['telephone'] ?? null;
    $adresse = $_POST['adresse'] ?? null;
    $statut = $_POST['statut'] ?? 'actif';
    
    // Créer l'utilisateur d'abord
    // Attention : Ceci est une simplification. Dans un système réel, vous devriez utiliser un modèle Utilisateur séparé.
    try {
        // Hash du mot de passe
        $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        
        // Connexion directe à la base de données pour insérer un utilisateur
        $db = $clientModel->db;
        
        // Vérifier si l'email est déjà utilisé
        $checkQuery = "SELECT id FROM Utilisateurs WHERE email = :email";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            $_SESSION['error'] = "Cette adresse email est déjà utilisée.";
            header('Location: ../Views/gestion_clients.php');
            exit();
        }
        
        // Insérer le nouvel utilisateur en utilisant le model client.php

        $query = "INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, statut) VALUES (:nom, :prenom, :email, :mot_de_passe, :statut)";
        $stmt = $db->prepare($query);
        
        $params = [
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':mot_de_passe' => $mot_de_passe_hash,
            ':statut' => $statut
        ];
        
        $result = $stmt->execute($params);
        
        if (!$result) {
            $_SESSION['error'] = "Erreur lors de la création de l'utilisateur: " . implode(", ", $stmt->errorInfo());
            header('Location: ../Views/gestion_clients.php');
            exit();
        }
        
        $utilisateur_id = $db->lastInsertId();
        
        // Créer le client associé à cet utilisateur
        $client_id = $clientModel->create($utilisateur_id, $telephone, $adresse);
        
        if (!$client_id) {
            $_SESSION['error'] = "Erreur lors de la création du client: " . $clientModel->getLastError();
            header('Location: ../Views/gestion_clients.php');
            exit();
        }
        
        // Succès
        $_SESSION['success'] = "Le client a été créé avec succès.";
        header('Location: ../Views/gestion_clients.php');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Exception: " . $e->getMessage();
        header('Location: ../Views/gestion_clients.php');
        exit();
    }
}

/**
 * Gère la mise à jour d'un client existant
 */
function handleUpdateClient() {
    global $clientModel;
    
    // Vérifier si tous les champs requis sont présents
    if (!isset($_POST['client_id']) || !isset($_POST['nom']) || !isset($_POST['prenom']) || !isset($_POST['email'])) {
        $_SESSION['error'] = "Tous les champs requis doivent être remplis.";
        header('Location: ../Views/gestion_clients.php');
        exit();
    }
    
    // Récupérer les données du formulaire
    $client_id = intval($_POST['client_id']);
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'] ?? null;
    $adresse = $_POST['adresse'] ?? null;
    $statut = $_POST['statut'] ?? 'actif';
    
    try {
        // Récupérer le client pour obtenir l'utilisateur_id
        $client = $clientModel->getClientById($client_id);
        
        if (!$client) {
            $_SESSION['error'] = "Client non trouvé.";
            header('Location: ../Views/gestion_clients.php');
            exit();
        }
        
        $utilisateur_id = $client['utilisateur_id'];
        
        // Connexion directe à la base de données pour mettre à jour l'utilisateur
        $db = $clientModel->db;
        
        // Vérifier si l'email est déjà utilisé par un autre utilisateur
        $checkQuery = "SELECT id FROM Utilisateurs WHERE email = :email AND id != :utilisateur_id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':email', $email);
        $checkStmt->bindParam(':utilisateur_id', $utilisateur_id);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            $_SESSION['error'] = "Cette adresse email est déjà utilisée par un autre utilisateur.";
            header('Location: ../Views/gestion_clients.php');
            exit();
        }
        
        // Mettre à jour l'utilisateur
        $query = "UPDATE Utilisateurs SET 
                 nom = :nom,
                 prenom = :prenom,
                 email = :email,
                 statut = :statut
                 WHERE id = :utilisateur_id";
        
        $stmt = $db->prepare($query);
        
        $params = [
            ':utilisateur_id' => $utilisateur_id,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':statut' => $statut
        ];
        
        $result = $stmt->execute($params);
        
        if (!$result) {
            $_SESSION['error'] = "Erreur lors de la mise à jour de l'utilisateur: " . implode(", ", $stmt->errorInfo());
            header('Location: ../Views/gestion_clients.php');
            exit();
        }
        
        // Mettre à jour le client
        $result = $clientModel->update($client_id, $telephone, $adresse);
        
        if (!$result) {
            $_SESSION['error'] = "Erreur lors de la mise à jour du client: " . $clientModel->getLastError();
            header('Location: ../Views/gestion_clients.php');
            exit();
        }
        
        // Succès
        $_SESSION['success'] = "Le client a été mis à jour avec succès.";
        header('Location: ../Views/gestion_clients.php');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Exception: " . $e->getMessage();
        header('Location: ../Views/gestion_clients.php');
        exit();
    }
}

/**
 * Gère l'activation d'un client
 */
function handleActivateClient() {
    global $clientModel;
    
    // Vérifier si l'ID du client est présent
    if (!isset($_POST['client_id'])) {
        $_SESSION['error'] = "ID de client manquant.";
        header('Location: ../Views/gestion_clients.php');
        exit();
    }
    
    $client_id = intval($_POST['client_id']);
    
    // Activer le client
    $result = $clientModel->activateClient($client_id);
    
    if (!$result) {
        $_SESSION['error'] = "Erreur lors de l'activation du client: " . $clientModel->getLastError();
        header('Location: ../Views/gestion_clients.php');
        exit();
    }
    
    // Succès
    $_SESSION['success'] = "Le client a été activé avec succès.";
    header('Location: ../Views/gestion_clients.php');
    exit();
}

/**
 * Gère la désactivation d'un client
 */
function handleDeactivateClient() {
    global $clientModel;
    
    // Vérifier si l'ID du client est présent
    if (!isset($_POST['client_id'])) {
        $_SESSION['error'] = "ID de client manquant.";
        header('Location: ../Views/gestion_clients.php');
        exit();
    }
    
    $client_id = intval($_POST['client_id']);
    
    // Désactiver le client
    $result = $clientModel->deactivateClient($client_id);
    
    if (!$result) {
        $_SESSION['error'] = "Erreur lors de la désactivation du client: " . $clientModel->getLastError();
        header('Location: ../Views/gestion_clients.php');
        exit();
    }
    
    // Succès
    $_SESSION['success'] = "Le client a été désactivé avec succès.";
    header('Location: ../Views/gestion_clients.php');
    exit();
}

/**
 * Récupère les détails d'un client (format JSON)
 */
function handleGetClient() {
    global $clientModel, $reservationModel;
    
    // Vérifier si l'ID est présent
    if (!isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID de client manquant']);
        exit();
    }
    
    $client_id = intval($_GET['id']);
    
    // Récupérer le client
    $client = $clientModel->getClientById($client_id);
    
    // Vérifier que le client existe
    if (!$client) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Client introuvable']);
        exit();
    }
    
    // Enrichir avec des statistiques supplémentaires
    $client['reservation_count'] = $clientModel->getClientReservationCount($client_id);
    $client['total_spent'] = $clientModel->getClientTotalSpent($client_id);
    
    // Renvoyer les données au format JSON
    header('Content-Type: application/json');
    echo json_encode($client);
    exit();
}

/**
 * Récupère les réservations d'un client (format JSON)
 */
function handleGetClientReservations() {
    global $clientModel;
    
    // Vérifier si l'ID est présent
    if (!isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID de client manquant']);
        exit();
    }
    
    $client_id = intval($_GET['id']);
    
    // Récupérer les réservations
    $reservations = $clientModel->getClientReservations($client_id);
    
    // Renvoyer les données au format JSON
    header('Content-Type: application/json');
    echo json_encode($reservations);
    exit();
}

/**
 * Récupère les paiements d'un client (format JSON)
 */
function handleGetClientPayments() {
    global $clientModel;
    
    // Vérifier si l'ID est présent
    if (!isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID de client manquant']);
        exit();
    }
    
    $client_id = intval($_GET['id']);
    
    // Récupérer les paiements
    $payments = $clientModel->getClientPayments($client_id);
    
    // Renvoyer les données au format JSON
    header('Content-Type: application/json');
    echo json_encode($payments);
    exit();
}

/**
 * Récupère les favoris d'un client (format JSON)
 */
function handleGetClientFavorites() {
    global $clientModel;
    
    // Vérifier si l'ID est présent
    if (!isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID de client manquant']);
        exit();
    }
    
    $client_id = intval($_GET['id']);
    
    // Récupérer les favoris
    $favorites = $clientModel->getClientFavorites($client_id);
    
    // Renvoyer les données au format JSON
    header('Content-Type: application/json');
    echo json_encode($favorites);
    exit();
}

/**
 * Récupère les notes d'un client (format JSON)
 */
function handleGetClientNotes() {
    global $clientModel;
    
    // Vérifier si l'ID est présent
    if (!isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'ID de client manquant']);
        exit();
    }
    
    $client_id = intval($_GET['id']);
    
    // Récupérer les notes
    $notes = $clientModel->getClientNotes($client_id);
    
    // Renvoyer les données au format JSON
    header('Content-Type: application/json');
    echo json_encode($notes);
    exit();
}

/**
 * Ajoute une note sur un client
 */
function handleAddClientNote() {
    global $clientModel;
    
    // Vérifier si tous les champs requis sont présents
    if (!isset($_POST['client_id']) || !isset($_POST['note']) || empty($_POST['note'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Les champs requis sont manquants']);
        exit();
    }
    
    $client_id = intval($_POST['client_id']);
    $note = $_POST['note'];
    
    // Ajouter la note
    $result = $clientModel->addClientNote($client_id, $note);
    
    if (!$result) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erreur lors de l\'ajout de la note: ' . $clientModel->getLastError()]);
        exit();
    }
    
    // Succès
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'note_id' => $result]);
    exit();
}
?>