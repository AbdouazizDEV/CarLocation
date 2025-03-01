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
            // Ajoutez ce débogage pour vérifier le mot de passe
            // echo "<pre>Mot de passe fourni : $password</pre>";
            // echo "<pre>Mot de passe haché dans la base : " . $user['mot_de_passe'] . "</pre>";
    
            if ($password === $user['mot_de_passe']) {
                session_start();
                $_SESSION['user'] = $user;
                require_once __DIR__ . "/../Views/Acceuil.php"; 
                exit;
            } else {
                echo "Mot de passe incorrect.";
            }
        } else {
            echo "Aucun utilisateur trouvé avec cet email.";
        }
    }
}
