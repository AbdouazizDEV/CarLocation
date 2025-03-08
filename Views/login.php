<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . "/../Controller/Authentication.php";

    $auth = new Authentication();
    $auth->login($_POST['email'], $_POST['mot_de_passe']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Location de Voitures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .bg-image {
            background-image: url('https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .bg-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.7);
        }
        .register-height {
            min-height: 100vh;
        }
        .nav-pills .nav-link {
            border-radius: 0;
            color: #6c757d;
        }
        .nav-pills .nav-link.active {
            background-color: transparent;
            color: #000;
            border-bottom: 2px solid #000;
        }
        .btn-primary {
            background-color: #0d47a1;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Section de gauche - Formulaire -->
            <div class="col-md-6 d-flex align-items-center register-height">
                <div class="container py-5">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="mb-4 pb-3 border-bottom">
                                <h4 class="fw-bold">Bienvenue chez NDAAMAR Location de voitures</h4>
                                <p class="text-muted small">Connectez-vous ou créez un compte</p>
                            </div>
                            
                            <ul class="nav nav-pills nav-fill mb-4" id="accountTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="login-tab" data-bs-toggle="pill" data-bs-target="#login" type="button" role="tab">Login</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="register-tab" data-bs-toggle="pill" data-bs-target="#register" type="button" role="tab">Register</button>
                                </li>
                            </ul>
                            
                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger">
                                    <?php 
                                    echo $_SESSION['error'];
                                    unset($_SESSION['error']);
                                    ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION['errors'])): ?>
                                <div class="alert alert-danger">
                                    <ul>
                                    <?php 
                                    foreach ($_SESSION['errors'] as $error) {
                                        echo "<li>" . $error . "</li>";
                                    }
                                    unset($_SESSION['errors']);
                                    ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <div class="tab-content" id="accountTabsContent">
                                <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="username" name="email" required>
                                        </div>
                                        <div class="mb-4">
                                            <label for="password" class="form-label">Mot de passe</label>
                                            <input type="password" class="form-control" id="password" name="mot_de_passe" required>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">Se connecter</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
                                    <form method="POST" action="../Controller/RegisterController.php">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="nom" class="form-label">Nom</label>
                                                <input type="text" class="form-control" id="nom" name="nom" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="prenom" class="form-label">Prénom</label>
                                                <input type="text" class="form-control" id="prenom" name="prenom" required>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="mot_de_passe" class="form-label">Mot de passe</label>
                                            <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="confirm_mot_de_passe" class="form-label">Confirmer le mot de passe</label>
                                            <input type="password" class="form-control" id="confirm_mot_de_passe" name="confirm_mot_de_passe" required>
                                        </div>
                                        <!-- ajouter le  telephone et l'adresse-->
                                         <div class="mb-3">
                                            <label for="telephone" class="form-label">Téléphone</label>
                                            <input type="tel" class="form-control" id="telephone" name="telephone" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="adresse" class="form-label">Adresse</label>
                                            <input type="text" class="form-control" id="adresse" name="adresse" required>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="form-label d-block">Statut</label>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="statut" id="statut_actif" value="actif" checked>
                                                <label class="form-check-label" for="statut_actif">Actif</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="statut" id="statut_inactif" value="inactif">
                                                <label class="form-check-label" for="statut_inactif">Inactif</label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4 form-check">
                                            <input type="checkbox" class="form-check-input" id="terms" required>
                                            <label class="form-check-label" for="terms">J'accepte les conditions d'utilisation et la politique de confidentialité</label>
                                        </div>
                                        
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">S'inscrire</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Section de droite - Image avec texte -->
            <div class="col-md-6 bg-dark text-white register-height bg-image">
                <div class="bg-overlay"></div>
                <div class="d-flex flex-column justify-content-center h-100 position-relative p-5">
                    <div class="z-index-1 position-relative">
                        <h1 class="display-6 fw-bold mb-3">Service de location de voitures haut de gamme</h1>
                        <p>Découvrez le luxe et le confort avec notre large sélection de véhicules. Réservez votre trajet idéal dès aujourd'hui.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>