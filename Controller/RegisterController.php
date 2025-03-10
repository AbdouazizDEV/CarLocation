<?php
session_start();
require_once __DIR__ . "/../Models/User.php";
require_once __DIR__ . "/../Models/client.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirm_mot_de_passe = $_POST['confirm_mot_de_passe'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $adresse= $_POST['adresse'] ?? '';
    $statut = $_POST['statut'] ?? 'actif';
    
    // Validation
    $errors = [];
    
    if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe)) {
        $errors[] = "Tous les champs sont obligatoires.";
    }
    
    if ($mot_de_passe !== $confirm_mot_de_passe) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    // Vérifier si l'email existe déjà
    $userModel = new User();
    if ($userModel->findByEmail($email)) {
        $errors[] = "Cet email est déjà utilisé.";
    }
    
    if (empty($errors)) {
        // Hachage du mot de passe
        $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        
        // Créer l'utilisateur
        $userId = $userModel->create($nom, $prenom, $email, $hashed_password, 'client', $statut);
        
        if ($userId) {
            // Ajouter l'utilisateur dans la table Clients
            $clientModel = new client();
            $clientId = $clientModel->create($userId, $telephone, $adresse);

            
            if (!$clientId) {
                // Si l'ajout client échoue, enregistrer l'erreur
                error_log("Erreur lors de l'ajout du client: " . $clientModel->getLastError());
                // On continue quand même, l'utilisateur est créé
            }
            
            $_SESSION['success'] = "Inscription réussie. Vous pouvez maintenant vous connecter.";
            header("Location: ../Views/login.php");
            exit;
        } else {
            $_SESSION['error'] = "Une erreur s'est produite lors de l'inscription.";
        }
    } else {
        $_SESSION['errors'] = $errors;
    }
    
    // En cas d'erreur, rediriger vers la page de login
    header("Location: ../Views/login.php");
    exit;
}
?>