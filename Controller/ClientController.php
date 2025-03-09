<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/../Models/client.php";
require_once __DIR__ . "/../Models/User.php";
require_once __DIR__ . "/../Models/Reservation.php";

// Vérifier si l'utilisateur est connecté et est un gérant
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'gérant') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && in_array($_GET['action'], ['get', 'getReservations', 'getPayments', 'getFavorites', 'getNotes'])) {
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
$clientModel = new Client();
$userModel = new User();
$reservationModel = new Reservation();

// Traitement des requêtes GET (pour récupérer des données)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    
    switch ($action) {
        case 'get':
            // Récupérer les détails d'un client
            $id = $_GET['id'] ?? 0;
            
            if (empty($id)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'ID client manquant']);
                exit();
            }
            
            $client = $clientModel->getClientById($id);
            
            if (!$client) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Client non trouvé']);
                exit();
            }
            
            // Ajouter des statistiques supplémentaires
            $client['reservation_count'] = $clientModel->getClientReservationCount($id);
            $client['total_spent'] = $clientModel->getClientTotalSpent($id);
            
            header('Content-Type: application/json');
            echo json_encode($client);
            exit();
            break;
            
        case 'getReservations':
            // Récupérer les réservations d'un client
            $id = $_GET['id'] ?? 0;
            
            if (empty($id)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'ID client manquant']);
                exit();
            }
            
            $reservations = $clientModel->getClientReservations($id);
            
            header('Content-Type: application/json');
            echo json_encode($reservations);
            exit();
            break;
            
        case 'getPayments':
            // Récupérer les paiements d'un client
            $id = $_GET['id'] ?? 0;
            
            if (empty($id)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'ID client manquant']);
                exit();
            }
            
            $payments = $clientModel->getClientPayments($id);
            
            header('Content-Type: application/json');
            echo json_encode($payments);
            exit();
            break;
            
        case 'getFavorites':
            // Récupérer les favoris d'un client
            $id = $_GET['id'] ?? 0;
            
            if (empty($id)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'ID client manquant']);
                exit();
            }
            
            $favorites = $clientModel->getClientFavorites($id);
            
            header('Content-Type: application/json');
            echo json_encode($favorites);
            exit();
            break;
            
        case 'getNotes':
            // Récupérer les notes d'un client
            $id = $_GET['id'] ?? 0;
            
            if (empty($id)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'ID client manquant']);
                exit();
            }
            
            $notes = $clientModel->getClientNotes($id);
            
            header('Content-Type: application/json');
            echo json_encode($notes);
            exit();
            break;
    }
}

// Traitement des requêtes POST (pour modifier des données)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            // Ajouter un nouveau client
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $email = $_POST['email'] ?? '';
            $mot_de_passe = $_POST['mot_de_passe'] ?? '';
            $telephone = $_POST['telephone'] ?? '';
            $adresse = $_POST['adresse'] ?? '';
            $statut = $_POST['statut'] ?? 'actif';
            
            // Validation des données
            $errors = [];
            
            if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe)) {
                $errors[] = "Tous les champs obligatoires doivent être remplis.";
            }
            
            // Vérifier si l'email existe déjà
            if ($userModel->findByEmail($email)) {
                $errors[] = "Cet email est déjà utilisé par un autre utilisateur.";
            }
            
            if (empty($errors)) {
                // Hacher le mot de passe
                $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                
                // Début de la transaction globale
                $clientModel->db->beginTransaction();
                
                try {
                    // Créer d'abord l'utilisateur
                    $userId = $userModel->create($nom, $prenom, $email, $hashed_password, 'client', $statut);
                    
                    if (!$userId) {
                        throw new Exception("Erreur lors de la création de l'utilisateur: " . $userModel->getLastError());
                    }
                    
                    // Ensuite créer le client associé
                    $clientId = $clientModel->create($userId, $telephone, $adresse);
                    
                    if (!$clientId) {
                        throw new Exception("Erreur lors de la création du client: " . $clientModel->getLastError());
                    }
                    
                    // Tout s'est bien passé, on valide la transaction
                    $clientModel->db->commit();
                    
                    $_SESSION['success'] = "Le client a été ajouté avec succès.";
                } catch (Exception $e) {
                    // En cas d'erreur, annuler toutes les modifications
                    $clientModel->db->rollBack();
                    $_SESSION['error'] = $e->getMessage();
                }
            } else {
                $_SESSION['error'] = implode("<br>", $errors);
            }
            
            header('Location: ../Views/gestion_clients.php');
            exit();
            break;
            
        case 'update':
            // Mettre à jour un client existant
            $client_id = $_POST['client_id'] ?? 0;
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $email = $_POST['email'] ?? '';
            $telephone = $_POST['telephone'] ?? '';
            $adresse = $_POST['adresse'] ?? '';
            $statut = $_POST['statut'] ?? '';
            
            // Validation des données
            $errors = [];
            
            if (empty($client_id) || empty($nom) || empty($prenom) || empty($email)) {
                $errors[] = "Les champs obligatoires doivent être remplis.";
            }
            
            if (empty($errors)) {
                // Récupérer le client pour avoir son ID utilisateur
                $client = $clientModel->getClientById($client_id);
                
                if (!$client) {
                    $_SESSION['error'] = "Client non trouvé.";
                    header('Location: ../Views/gestion_clients.php');
                    exit();
                }
                
                $utilisateur_id = $client['utilisateur_id'];
                
                // Début de la transaction
                $clientModel->db->beginTransaction();
                
                try {
                    // Mettre à jour l'utilisateur
                    $userUpdated = $userModel->update($utilisateur_id, $prenom, $nom, $email);
                    
                    if (!$userUpdated) {
                        throw new Exception("Erreur lors de la mise à jour de l'utilisateur.");
                    }
                    
                    // Mettre à jour le statut de l'utilisateur si nécessaire
                    if (!empty($statut) && $statut !== $client['statut']) {
                        $statusQuery = "UPDATE Utilisateurs SET statut = :statut WHERE id = :id";
                        $stmt = $clientModel->db->prepare($statusQuery);
                        $stmt->bindParam(':statut', $statut, PDO::PARAM_STR);
                        $stmt->bindParam(':id', $utilisateur_id, PDO::PARAM_INT);
                        
                        if (!$stmt->execute()) {
                            throw new Exception("Erreur lors de la mise à jour du statut.");
                        }
                    }
                    
                    // Mettre à jour les informations du client
                    $clientUpdated = $clientModel->update($client_id, $telephone, $adresse);
                    
                    if (!$clientUpdated) {
                        throw new Exception("Erreur lors de la mise à jour du client: " . $clientModel->getLastError());
                    }
                    
                    // Tout s'est bien passé, on valide la transaction
                    $clientModel->db->commit();
                    
                    $_SESSION['success'] = "Les informations du client ont été mises à jour avec succès.";
                } catch (Exception $e) {
                    // En cas d'erreur, annuler toutes les modifications
                    $clientModel->db->rollBack();
                    $_SESSION['error'] = $e->getMessage();
                }
            } else {
                $_SESSION['error'] = implode("<br>", $errors);
            }
            
            header('Location: ../Views/gestion_clients.php');
            exit();
            break;
            
        case 'deactivate':
            // Désactiver un client
            $client_id = $_POST['client_id'] ?? 0;
            
            if (empty($client_id)) {
                $_SESSION['error'] = "ID client manquant.";
                header('Location: ../Views/gestion_clients.php');
                exit();
            }
            
            if ($clientModel->deactivateClient($client_id)) {
                $_SESSION['success'] = "Le client a été désactivé avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la désactivation du client: " . $clientModel->getLastError();
            }
            
            header('Location: ../Views/gestion_clients.php');
            exit();
            break;
            
        case 'activate':
            // Activer un client
            $client_id = $_POST['client_id'] ?? 0;
            
            if (empty($client_id)) {
                $_SESSION['error'] = "ID client manquant.";
                header('Location: ../Views/gestion_clients.php');
                exit();
            }
            
            if ($clientModel->activateClient($client_id)) {
                $_SESSION['success'] = "Le client a été activé avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de l'activation du client: " . $clientModel->getLastError();
            }
            
            header('Location: ../Views/gestion_clients.php');
            exit();
            break;
            
        case 'addNote':
            // Ajouter une note à un client
            $client_id = $_POST['client_id'] ?? 0;
            $note = $_POST['note'] ?? '';
            
            if (empty($client_id) || empty($note)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'ID client ou note manquant']);
                exit();
            }
            
            $result = $clientModel->addClientNote($client_id, $note);
            
            header('Content-Type: application/json');
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Note ajoutée avec succès']);
            } else {
                echo json_encode(['error' => 'Erreur lors de l\'ajout de la note: ' . $clientModel->getLastError()]);
            }
            exit();
            break;
    }
}

// Si aucune action correspondante n'a été traitée, rediriger vers la page de gestion des clients
header('Location: ../Views/gestion_clients.php');
exit();
?>