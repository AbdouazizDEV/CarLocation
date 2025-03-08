<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../Models/User.php"; 
require_once __DIR__ . "/../Database/Connect.php"; 

class Authentication {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login($email, $password) {
        $user = $this->userModel->findByEmail($email);
    
        if ($user) {
            // Utiliser password_verify pour comparer avec le mot de passe haché
            if (password_verify($password, $user['mot_de_passe'])) {
                // Démarrer la session et stocker toutes les informations de l'utilisateur
                session_start();
                $_SESSION['user'] = $user;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_statut'] = $user['statut'];
                
                // Rediriger en fonction du rôle
                if ($user['role'] === 'client') {
                    header("Location: ../Views/AcceuilClient.php");
                } else if ($user['role'] === 'gérant') {
                    header("Location: ../Views/AcceuilGerant.php");
                } else {
                    // Par défaut, si le rôle n'est pas reconnu
                    header("Location: ../Views/AcceuilVIsiteur.php");
                }
                exit;
            } else {
                $_SESSION['error'] = "Mot de passe incorrect.";
                header("Location: ../Views/login.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Aucun utilisateur trouvé avec cet email.";
            header("Location: ../Views/login.php");
            exit;
        }
    }
}