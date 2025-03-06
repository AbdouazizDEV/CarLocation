<?php
require_once __DIR__ . "/../Models/User.php";
session_start();

// Utilisation du contrôleur
$registerController = new RegisterController();
$registerController->register();

class RegisterController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $email = $_POST['email'] ?? '';
            $mot_de_passe = $_POST['mot_de_passe'] ?? '';
            $confirm_mot_de_passe = $_POST['confirm_mot_de_passe'] ?? '';
            $statut = $_POST['statut'] ?? 'actif'; // Par défaut "actif"
            $role = 'client'; // Par défaut, les nouveaux utilisateurs sont des clients

            // Validation des données
            $errors = [];

            if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe) || empty($confirm_mot_de_passe)) {
                $errors[] = "Tous les champs sont obligatoires.";
            }

            if ($mot_de_passe !== $confirm_mot_de_passe) {
                $errors[] = "Les mots de passe ne correspondent pas.";
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'email n'est pas valide.";
            }

            // Vérifier si l'utilisateur existe déjà
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser) {
                $errors[] = "Un utilisateur avec cet email existe déjà.";
            }

            // Si aucune erreur, procéder à l'inscription
            if (empty($errors)) {
                $userId = $this->userModel->create($nom, $prenom, $email, $mot_de_passe, $role, $statut);

                if ($userId) {
                    // Rediriger vers une page de succès ou de connexion
                    header("Location: ../Views/login.php");
                    exit();
                } else {
                    $errors[] = "Une erreur s'est produite lors de l'inscription.";
                }
            }

            // Stocker les erreurs dans la session pour les afficher
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                header("Location: ../Views/login.php");
                exit();
            }
        }
    }
}
?>