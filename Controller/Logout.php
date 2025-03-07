<?php
/**
 * Contrôleur de déconnexion
 * Ce fichier gère la déconnexion de l'utilisateur en détruisant sa session
 * et en le redirigeant vers la page d'accueil visiteur.
 */
session_start();

// Détruire toutes les variables de session
$_SESSION = array();

// Si un cookie de session existe, le détruire
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Détruire la session
session_destroy();

// Rediriger vers la page d'accueil visiteur
header("Location: ../Views/AcceuilVIsiteur.php");
exit();