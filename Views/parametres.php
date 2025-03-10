<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Définir le rôle pour adapter l'interface
$role = $_SESSION['user_role'] ?? 'client';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - NDAAMAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f5f5;
        }
        .sidebar {
            background-color: white;
            min-height: 100vh;
            border-right: 1px solid #e0e0e0;
        }
        .logo-container {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }
        .logo {
            max-width: 150px;
        }
        .menu-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: #333;
            text-decoration: none;
            transition: background-color 0.3s;
            position: relative;
        }
        .menu-item:hover {
            background-color: #f0f0f0;
        }
        .menu-item.active {
            background-color: #e8f0fe;
            font-weight: bold;
        }
        .menu-item i {
            margin-right: 10px;
        }
        .search-bar {
            max-width: 350px;
            margin-right: 15px;
        }
        .profile-section {
            display: flex;
            align-items: center;
        }
        .profile-section .dropdown-toggle::after {
            display: none;
        }
        .content-container {
            padding: 20px;
        }
        .settings-nav {
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .settings-nav .nav-link {
            color: #555;
            padding: 10px 15px;
            border-radius: 5px;
        }
        .settings-nav .nav-link:hover {
            background-color: #f8f9fa;
        }
        .settings-nav .nav-link.active {
            background-color: #e8f0fe;
            color: #0d6efd;
            font-weight: 500;
        }
        .settings-nav .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .settings-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .settings-card h5 {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar">
                <div class="logo-container">
                    <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740850713/Grey_and_Black2_Car_Rental_Service_Logo_nrbxc0.png" alt="NDAAMAR" class="logo">
                </div>
                <div class="menu">
                    <?php if ($role === 'gérant'): ?>
                    <a href="AcceuilGerant.php" class="menu-item">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                    <a href="gestion_vehicules.php" class="menu-item">
                        <i class="fas fa-car"></i> Gestion des véhicules
                    </a>
                    <a href="gestion_reservations.php" class="menu-item">
                        <i class="fas fa-calendar-check"></i> Réservations
                    </a>
                    <a href="gestion_clients.php" class="menu-item">
                        <i class="fas fa-users"></i> Clients
                    </a>
                    <a href="facturation.php" class="menu-item">
                        <i class="fas fa-file-invoice-dollar"></i> Facturation
                    </a>
                    <a href="Offres.php" class="menu-item">
                        <i class="fas fa-chart-bar"></i> Offres
                    </a>
                    <a href="parametres.php" class="menu-item active">
                        <i class="fas fa-cogs"></i> Paramètres
                    </a>
                    <?php else: ?>
                    <a href="AcceuilClient.php" class="menu-item">
                        <i class="fas fa-tag"></i> Offres
                    </a>
                    <a href="favoris.php" class="menu-item">
                        <i class="fas fa-heart"></i> Favorites
                    </a>
                    <a href="reservations.php" class="menu-item">
                        <i class="fas fa-calendar-alt"></i> Réservations
                    </a>
                    <a href="locations.php" class="menu-item">
                        <i class="fas fa-car"></i> Locations
                    </a>
                    <a href="recherche.php" class="menu-item">
                        <i class="fas fa-search"></i> Rechercher
                    </a>
                    <a href="parametres.php" class="menu-item active">
                        <i class="fas fa-cogs"></i> Paramètres
                    </a>
                    <a href="contact.php" class="menu-item">
                        <i class="fas fa-envelope"></i> Contactez-nous
                    </a>
                    <?php endif; ?>
                </div>
                <div class="mt-auto">
                    <a href="../Controller/Logout.php" class="menu-item text-danger">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-0">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                    <div class="container-fluid">
                        <form class="d-flex search-bar">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Rechercher..." aria-label="Rechercher">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                        <div class="navbar-nav ms-auto profile-section">
                        <div class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown">
                                    <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1741275355/Flag_of_the_United_Kingdom__3-5_.svg_uuwyft.png" alt="English" width="20" height="15">
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1741275355/Flag_of_the_United_Kingdom__3-5_.svg_uuwyft.png" alt="English" width="20" height="15"> English</a></li>
                                    <li><a class="dropdown-item" href="#"><img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1741275393/Flag_of_France.svg_pu9ohf.png" alt="Français" width="20" height="15"> Français</a></li>
                                </ul>
                            </div>
                            <a class="nav-link" href="notifications.php">
                                <i class="fas fa-bell"></i>
                            </a>
                            <a class="nav-link" href="parametres.php">
                                <i class="fas fa-cog"></i>
                            </a>
                            <div class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                                    <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1741271258/user-6380868_1280_zguwih.webp" alt="Profile" width="32" height="32" class="rounded-circle me-2">
                                    <div>
                                        <div class="fw-bold"><?php echo $_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']; ?></div>
                                        <small class="text-muted"><?php echo $_SESSION['user_role']; ?></small>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#profileModal">Mon Profil</a></li>
                                    <li><a class="dropdown-item" href="parametres.php">Paramètres</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="../Controller/Logout.php">Déconnexion</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
                
                <!-- Content -->
                <div class="content-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1>Paramètres</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Application</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Paramètres</li>
                            </ol>
                        </nav>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="settings-nav">
                                <ul class="nav flex-column" id="settingsTabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="account-tab" data-bs-toggle="pill" href="#account" role="tab">
                                            <i class="fas fa-user"></i> Compte
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="security-tab" data-bs-toggle="pill" href="#security" role="tab">
                                            <i class="fas fa-shield-alt"></i> Sécurité
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="notification-tab" data-bs-toggle="pill" href="#notification" role="tab">
                                            <i class="fas fa-bell"></i> Notifications
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="appearance-tab" data-bs-toggle="pill" href="#appearance" role="tab">
                                            <i class="fas fa-paint-brush"></i> Apparence
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="language-tab" data-bs-toggle="pill" href="#language" role="tab">
                                            <i class="fas fa-language"></i> Langue
                                        </a>
                                    </li>
                                    <?php if ($role === 'gérant'): ?>
                                    <li class="nav-item">
                                        <a class="nav-link" id="admin-tab" data-bs-toggle="pill" href="#admin" role="tab">
                                            <i class="fas fa-user-cog"></i> Administration
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="col-md-9">
                            <div class="tab-content" id="settingsTabContent">
                                <!-- Compte -->
                                <div class="tab-pane fade show active" id="account" role="tabpanel" aria-labelledby="account-tab">
                                    <div class="settings-card">
                                        <h5>Informations personnelles</h5>
                                        <form action="../Controller/UpdateProfile.php" method="POST">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="updatePrenom" class="form-label">Prénom</label>
                                                    <input type="text" class="form-control" id="updatePrenom" name="prenom" value="<?php echo $_SESSION['user_prenom']; ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="updateNom" class="form-label">Nom</label>
                                                    <input type="text" class="form-control" id="updateNom" name="nom" value="<?php echo $_SESSION['user_nom']; ?>">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="updateEmail" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="updateEmail" name="email" value="<?php echo $_SESSION['user_email']; ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label for="updateTelephone" class="form-label">Téléphone</label>
                                                <input type="tel" class="form-control" id="updateTelephone" name="telephone" value="">
                                            </div>
                                            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                                        </form>
                                    </div>
                                    
                                    <div class="settings-card">
                                        <h5>Photo de profil</h5>
                                        <div class="row align-items-center">
                                            <div class="col-md-3 text-center">
                                                <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1741271258/user-6380868_1280_zguwih.webp" alt="Profile" class="rounded-circle img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                                            </div>
                                            <div class="col-md-9">
                                                <form action="../Controller/UpdateProfilePicture.php" method="POST" enctype="multipart/form-data">
                                                    <div class="mb-3">
                                                        <label for="profilePicture" class="form-label">Changer votre photo</label>
                                                        <input class="form-control" type="file" id="profilePicture" name="profile_picture">
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Télécharger</button>
                                                    <button type="button" class="btn btn-outline-danger">Supprimer</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Sécurité -->
                                <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                                    <div class="settings-card">
                                        <h5>Sécurité du compte</h5>
                                        <form action="../Controller/ChangePassword.php" method="POST">
                                            <div class="mb-3">
                                                <label for="currentPassword" class="form-label">Mot de passe actuel</label>
                                                <input type="password" class="form-control" id="currentPassword" name="current_password">
                                            </div>
                                            <div class="mb-3">
                                                <label for="newPassword" class="form-label">Nouveau mot de passe</label>
                                                <input type="password" class="form-control" id="newPassword" name="new_password">
                                            </div>
                                            <div class="mb-3">
                                                <label for="confirmPassword" class="form-label">Confirmer le nouveau mot de passe</label>
                                                <input type="password" class="form-control" id="confirmPassword" name="confirm_password">
                                            </div>
                                            <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
                                        </form>
                                    </div>
                                    
                                    <div class="settings-card">
                                        <h5>Authentification à deux facteurs</h5>
                                        <p>Renforcez la sécurité de votre compte en activant l'authentification à deux facteurs.</p>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="twoFactorAuth">
                                            <label class="form-check-label" for="twoFactorAuth">Activer l'authentification à deux facteurs</label>
                                        </div>
                                        <div class="mb-3" id="twoFactorSection" style="display: none;">
                                            <p class="mb-3">Scannez le code QR avec votre application d'authentification (Google Authenticator, Authy, etc.).</p>
                                            <div class="text-center mb-3">
                                                <img src="../Assets/Images/qr-code-placeholder.png" alt="QR Code" width="180" height="180">
                                            </div>
                                            <div class="mb-3">
                                                <label for="verificationCode" class="form-label">Code de vérification</label>
                                                <input type="text" class="form-control" id="verificationCode" placeholder="Entrez le code à 6 chiffres">
                                            </div>
                                            <button type="button" class="btn btn-primary">Vérifier et activer</button>
                                        </div>
                                    </div>
                                    
                                    <div class="settings-card">
                                        <h5>Sessions actives</h5>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                                <div>
                                                    <h6 class="mb-1">Session actuelle</h6>
                                                    <small class="text-muted">Dakar, Sénégal • Chrome • Windows</small>
                                                </div>
                                                <span class="badge bg-success">Actif</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                                <div>
                                                    <h6 class="mb-1">iPhone 12</h6>
                                                    <small class="text-muted">Dakar, Sénégal • Safari • iOS 15.0</small>
                                                </div>
                                                <button class="btn btn-sm btn-outline-danger">Déconnecter</button>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Tablette Android</h6>
                                                    <small class="text-muted">Dakar, Sénégal • Chrome • Android 12</small>
                                                </div>
                                                <button class="btn btn-sm btn-outline-danger">Déconnecter</button>
                                            </div>
                                        </div>
                                        <button class="btn btn-danger">Déconnecter toutes les autres sessions</button>
                                    </div>
                                </div>
                                
                                <!-- Notifications -->
                                <div class="tab-pane fade" id="notification" role="tabpanel" aria-labelledby="notification-tab">
                                    <div class="settings-card">
                                        <h5>Préférences de notifications</h5>
                                        <form action="../Controller/UpdateNotificationSettings.php" method="POST">
                                            <div class="mb-3">
                                                <label class="form-label">Notifications par email</label>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="emailBookingConfirmation" name="email_notifications[]" value="booking_confirmation" checked>
                                                    <label class="form-check-label" for="emailBookingConfirmation">Confirmation de réservation</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="emailBookingReminder" name="email_notifications[]" value="booking_reminder" checked>
                                                    <label class="form-check-label" for="emailBookingReminder">Rappel de réservation</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="emailPromotion" name="email_notifications[]" value="promotion">
                                                    <label class="form-check-label" for="emailPromotion">Offres promotionnelles</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="emailNewsletter" name="email_notifications[]" value="newsletter">
                                                    <label class="form-check-label" for="emailNewsletter">Newsletter
                                                    <input class="form-check-input" type="checkbox" id="emailNewsletter" name="email_notifications[]" value="newsletter">
                                                    <label class="form-check-label" for="emailNewsletter">Newsletter</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Notifications dans l'application</label>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="appBookingStatus" name="app_notifications[]" value="booking_status" checked>
                                                    <label class="form-check-label" for="appBookingStatus">Statut de réservation</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="appReminders" name="app_notifications[]" value="reminders" checked>
                                                    <label class="form-check-label" for="appReminders">Rappels</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="appPromotions" name="app_notifications[]" value="promotions">
                                                    <label class="form-check-label" for="appPromotions">Offres promotionnelles</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="appSystemUpdates" name="app_notifications[]" value="system_updates" checked>
                                                    <label class="form-check-label" for="appSystemUpdates">Mises à jour système</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Notifications SMS</label>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="smsBookingConfirmation" name="sms_notifications[]" value="booking_confirmation">
                                                    <label class="form-check-label" for="smsBookingConfirmation">Confirmation de réservation</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" id="smsBookingReminder" name="sms_notifications[]" value="booking_reminder">
                                                    <label class="form-check-label" for="smsBookingReminder">Rappel de réservation</label>
                                                </div>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">Enregistrer les préférences</button>
                                        </form>
                                    </div>
                                </div>
                                
                                <!-- Apparence -->
                                <div class="tab-pane fade" id="appearance" role="tabpanel" aria-labelledby="appearance-tab">
                                    <div class="settings-card">
                                        <h5>Thème de l'application</h5>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="themeMode" id="lightMode" value="light" checked>
                                                <label class="form-check-label" for="lightMode">
                                                    <i class="fas fa-sun me-2"></i> Mode clair
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="themeMode" id="darkMode" value="dark">
                                                <label class="form-check-label" for="darkMode">
                                                    <i class="fas fa-moon me-2"></i> Mode sombre
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="themeMode" id="systemMode" value="system">
                                                <label class="form-check-label" for="systemMode">
                                                    <i class="fas fa-laptop me-2"></i> Suivre le système
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="settings-card">
                                        <h5>Couleur d'accentuation</h5>
                                        <div class="mb-3 d-flex flex-wrap gap-2">
                                            <button class="btn btn-primary rounded-circle p-3" data-color="#0d6efd" style="width: 50px; height: 50px;"></button>
                                            <button class="btn rounded-circle p-3" data-color="#dc3545" style="width: 50px; height: 50px; background-color: #dc3545;"></button>
                                            <button class="btn rounded-circle p-3" data-color="#198754" style="width: 50px; height: 50px; background-color: #198754;"></button>
                                            <button class="btn rounded-circle p-3" data-color="#fd7e14" style="width: 50px; height: 50px; background-color: #fd7e14;"></button>
                                            <button class="btn rounded-circle p-3" data-color="#6610f2" style="width: 50px; height: 50px; background-color: #6610f2;"></button>
                                            <button class="btn rounded-circle p-3" data-color="#20c997" style="width: 50px; height: 50px; background-color: #20c997;"></button>
                                        </div>
                                    </div>
                                    
                                    <div class="settings-card">
                                        <h5>Taille de la police</h5>
                                        <div class="mb-3">
                                            <label for="fontSize" class="form-label">Taille de la police</label>
                                            <input type="range" class="form-range" min="80" max="120" step="5" value="100" id="fontSize">
                                            <div class="d-flex justify-content-between">
                                                <span>Petit</span>
                                                <span>Normal</span>
                                                <span>Grand</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Langue -->
                                <div class="tab-pane fade" id="language" role="tabpanel" aria-labelledby="language-tab">
                                    <div class="settings-card">
                                        <h5>Langue de l'application</h5>
                                        <div class="mb-3">
                                            <select class="form-select" id="appLanguage">
                                                <option value="fr" selected>Français</option>
                                                <option value="en">English</option>
                                                <option value="es">Español</option>
                                                <option value="ar">العربية</option>
                                                <option value="wo">Wolof</option>
                                            </select>
                                        </div>
                                        <button class="btn btn-primary">Appliquer</button>
                                    </div>
                                </div>
                                
                                <!-- Administration (pour les gérants uniquement) -->
                                <?php if ($role === 'gérant'): ?>
                                <div class="tab-pane fade" id="admin" role="tabpanel" aria-labelledby="admin-tab">
                                    <div class="settings-card">
                                        <h5>Paramètres généraux</h5>
                                        <div class="mb-3">
                                            <label for="siteTitle" class="form-label">Titre du site</label>
                                            <input type="text" class="form-control" id="siteTitle" value="NDAAMAR Location de voitures">
                                        </div>
                                        <div class="mb-3">
                                            <label for="contactEmail" class="form-label">Email de contact</label>
                                            <input type="email" class="form-control" id="contactEmail" value="contact@ndaamar.com">
                                        </div>
                                        <div class="mb-3">
                                            <label for="timezone" class="form-label">Fuseau horaire</label>
                                            <select class="form-select" id="timezone">
                                                <option value="UTC+0" selected>Sénégal (UTC+0)</option>
                                                <option value="UTC+1">Europe Centrale (UTC+1)</option>
                                                <option value="UTC+2">Europe de l'Est (UTC+2)</option>
                                                <option value="UTC-4">Est des États-Unis (UTC-4)</option>
                                                <option value="UTC-7">Ouest des États-Unis (UTC-7)</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="settings-card">
                                        <h5>Paramètres de réservation</h5>
                                        <div class="mb-3">
                                            <label for="minBookingPeriod" class="form-label">Durée minimale de location (en jours)</label>
                                            <input type="number" class="form-control" id="minBookingPeriod" value="1">
                                        </div>
                                        <div class="mb-3">
                                            <label for="depositPercentage" class="form-label">Pourcentage d'acompte</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="depositPercentage" value="30">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="cancellationPolicy" class="form-label">Politique d'annulation</label>
                                            <select class="form-select" id="cancellationPolicy">
                                                <option value="flexible">Flexible (remboursement complet jusqu'à 24h avant)</option>
                                                <option value="moderate" selected>Modérée (remboursement 50% jusqu'à 3 jours avant)</option>
                                                <option value="strict">Stricte (remboursement 25% jusqu'à 7 jours avant)</option>
                                                <option value="non-refundable">Non remboursable</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="settings-card">
                                        <h5>Gestion des utilisateurs</h5>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="allowRegistration" checked>
                                                <label class="form-check-label" for="allowRegistration">Autoriser l'inscription de nouveaux utilisateurs</label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="emailVerification" checked>
                                                <label class="form-check-label" for="emailVerification">Exiger la vérification de l'email</label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="userApproval" class="form-label">Approbation des nouveaux utilisateurs</label>
                                            <select class="form-select" id="userApproval">
                                                <option value="automatic" selected>Automatique</option>
                                                <option value="manual">Manuelle (approbation par admin)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Profil -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center pb-4">
                    <div class="mb-4">
                        <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1741271258/user-6380868_1280_zguwih.webp" alt="Photo de profil" class="rounded-circle img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                        <h4 class="mt-3 mb-0"><?php echo $_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']; ?></h4>
                        <p class="text-muted"><?php echo $_SESSION['user_role']; ?></p>
                    </div>
                    
                    <div class="border rounded-3 p-3 mb-3 text-start">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Informations personnelles</h5>
                            <button class="btn btn-sm btn-outline-primary rounded-pill" id="editProfileBtn">
                                <i class="fas fa-pen"></i> Modifier
                            </button>
                        </div>
                        
                        <div id="profileInfo">
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Email</div>
                                <div class="col-8"><?php echo $_SESSION['user_email']; ?></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Statut</div>
                                <div class="col-8">
                                    <span class="badge <?php echo $_SESSION['user_statut'] === 'actif' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $_SESSION['user_statut']; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 text-muted">ID Compte</div>
                                <div class="col-8"><?php echo $_SESSION['user_id']; ?></div>
                            </div>
                        </div>
                        
                        <div id="profileEditForm" style="display: none;">
                            <form action="../Controller/UpdateProfile.php" method="POST">
                                <div class="mb-3">
                                    <label for="editPrenom" class="form-label">Prénom</label>
                                    <input type="text" class="form-control" id="editPrenom" name="prenom" value="<?php echo $_SESSION['user_prenom']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="editNom" class="form-label">Nom</label>
                                    <input type="text" class="form-control" id="editNom" name="nom" value="<?php echo $_SESSION['user_nom']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="editEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="editEmail" name="email" value="<?php echo $_SESSION['user_email']; ?>">
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                    <button type="button" class="btn btn-outline-secondary" id="cancelEditBtn">Annuler</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="border rounded-3 p-3 text-start">
                        <h5 class="mb-3">Sécurité du compte</h5>
                        <button class="btn btn-outline-primary mb-2 w-100 text-start" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="fas fa-lock me-2"></i> Changer le mot de passe
                        </button>
                        <button class="btn btn-outline-warning mb-2 w-100 text-start">
                            <i class="fas fa-shield-alt me-2"></i> Activer l'authentification à deux facteurs
                        </button>
                        <button class="btn btn-outline-danger w-100 text-start">
                            <i class="fas fa-user-slash me-2"></i> Désactiver le compte
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Changement de mot de passe -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Changer le mot de passe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="../Controller/ChangePassword.php" method="POST">
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Mot de passe actuel</label>
                            <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="newPassword" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script pour le formulaire d'édition de profil
        document.addEventListener('DOMContentLoaded', function() {
            const editProfileBtn = document.getElementById('editProfileBtn');
            const cancelEditBtn = document.getElementById('cancelEditBtn');
            const profileInfo = document.getElementById('profileInfo');
            const profileEditForm = document.getElementById('profileEditForm');
            
            if(editProfileBtn && cancelEditBtn && profileInfo && profileEditForm) {
                editProfileBtn.addEventListener('click', function() {
                    profileInfo.style.display = 'none';
                    profileEditForm.style.display = 'block';
                });
                
                cancelEditBtn.addEventListener('click', function() {
                    profileInfo.style.display = 'block';
                    profileEditForm.style.display = 'none';
                });
            }
            
            // Pour l'authentification à deux facteurs
            const twoFactorAuth = document.getElementById('twoFactorAuth');
            const twoFactorSection = document.getElementById('twoFactorSection');
            
            if(twoFactorAuth && twoFactorSection) {
                twoFactorAuth.addEventListener('change', function() {
                    if(this.checked) {
                        twoFactorSection.style.display = 'block';
                    } else {
                        twoFactorSection.style.display = 'none';
                    }
                });
            }
            
            // Pour les couleurs d'accentuation
            const colorButtons = document.querySelectorAll('[data-color]');
            
            colorButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Enlever la classe active de tous les boutons
                    colorButtons.forEach(btn => {
                        btn.classList.remove('btn-primary');
                        btn.style.border = 'none';
                    });
                    
                    // Ajouter une bordure au bouton sélectionné
                    this.style.border = '2px solid #000';
                    
                    // Appliquer la couleur (simulé ici)
                    console.log('Couleur sélectionnée:', this.dataset.color);
                });
            });
        });
    </script>
</body>
</html>