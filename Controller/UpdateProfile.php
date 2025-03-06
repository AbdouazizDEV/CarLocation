<?php
session_start();
require_once __DIR__ . "/../Models/User.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour modifier votre profil.";
    header('Location: ../Views/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userModel = new User();
    $userId = $_SESSION['user_id'];
    $prenom = $_POST['prenom'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? null;
    
    // Valider les données
    $errors = [];
    
    if (empty($prenom) || empty($nom) || empty($email)) {
        $errors[] = "Les champs prénom, nom et email sont obligatoires.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide.";
    }
    
    // Vérifier si l'email existe déjà et appartient à un autre utilisateur
    $existingUser = $userModel->findByEmail($email);
    if ($existingUser && $existingUser['id'] != $userId) {
        $errors[] = "Cet email est déjà utilisé par un autre compte.";
    }
    
    // Mettre à jour le profil si aucune erreur
    if (empty($errors)) {
        $result = $userModel->update($userId, $prenom, $nom, $email, $telephone);
        
        if ($result) {
            // Mettre à jour les données de session
            $_SESSION['user_prenom'] = $prenom;
            $_SESSION['user_nom'] = $nom;
            $_SESSION['user_email'] = $email;
            
            $_SESSION['success'] = "Votre profil a été mis à jour avec succès.";
            
            // Rediriger en fonction de la source de la requête
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            $errors[] = "Une erreur s'est produite lors de la mise à jour du profil.";
        }
    }
    
    // S'il y a des erreurs, les stocker et rediriger
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}