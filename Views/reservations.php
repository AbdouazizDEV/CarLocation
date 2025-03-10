<?php
session_start();
require_once __DIR__ . "/../Models/Reservation.php";
require_once __DIR__ . "/../Models/voiture.php";
require_once __DIR__ . "/../Models/offre.php";

// Vérifier si l'utilisateur est connecté et est un client
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'client') {
    header('Location: login.php');
    exit();
}

// Instancier les objets nécessaires
$reservationModel = new Reservation();
$voitureModel = new Voiture();
$offreModel = new Offre();

// Récupérer toutes les réservations du client
$reservations = $reservationModel->getReservationsByClient($_SESSION['user_id']);

// Séparer les réservations par statut
$reservationsEnCours = [];
$reservationsPassees = [];
$reservationsAVenir = [];

$today = date('Y-m-d');

foreach ($reservations as $reservation) {
    // Ajout des détails du véhicule
    $vehicule = $voitureModel->getVoitureById($reservation['voiture_id']);
    $reservation['vehicule'] = $vehicule;
    
    // Calcul de la durée de réservation en jours
    $dateDebut = new DateTime($reservation['date_debut']);
    $dateFin = new DateTime($reservation['date_fin']);
    $duree = $dateDebut->diff($dateFin)->days;
    
    $reservation['duree'] = $duree;
    
    // Classement des réservations
    if ($reservation['statut'] === 'en_cours' || ($today >= $reservation['date_debut'] && $today <= $reservation['date_fin'] && $reservation['statut'] !== 'annulee')) {
        $reservationsEnCours[] = $reservation;
    } elseif ($today < $reservation['date_debut'] && $reservation['statut'] !== 'annulee') {
        $reservationsAVenir[] = $reservation;
    } else {
        $reservationsPassees[] = $reservation;
    }
    
    // CORRECTION: Si une réservation est confirmée et la date actuelle est dans la période,
    // elle devrait être marquée comme "en cours"
    if ($reservation['statut'] === 'confirmee' && $today >= $reservation['date_debut'] && $today <= $reservation['date_fin']) {
        // Mise à jour du statut en "en cours"
        $reservationModel->updateStatus($reservation['id'], 'en_cours');
    }
}

// CORRECTION: Définir l'onglet actif par défaut en fonction de l'URL
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'upcoming';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NDAAMAR - Mes Réservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Public/CSS/ClientStyle.css">
    <style>
        /* Style additionnel pour les cartes de réservation */
        .reservation-card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        .reservation-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .status-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1;
        }
        .status-countdown {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
        .vehicle-img {
            height: 180px;
            object-fit: cover;
        }
        .booking-detail {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .booking-detail i {
            width: 20px;
            margin-right: 10px;
            color: #6c757d;
        }
        .price-box {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .actions-bar {
            margin-top: 1rem;
        }
        /* Style pour l'état vide */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
        }
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 15px;
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
                    <a href="AcceuilClient.php" class="menu-item">
                        <i class="fas fa-tag"></i> Offres
                    </a>
                    <a href="favoris.php" class="menu-item">
                        <i class="fas fa-heart"></i> Favoris
                    </a>
                    <a href="reservations.php" class="menu-item active">
                        <i class="fas fa-calendar-alt"></i> Réservations
                    </a>
                    <a href="locations.php" class="menu-item">
                        <i class="fas fa-car"></i> Locations
                    </a>
                    <a href="recherche.php" class="menu-item">
                        <i class="fas fa-search"></i> Rechercher
                    </a>
                    <a href="contact.php" class="menu-item">
                        <i class="fas fa-envelope"></i> Contactez-nous
                    </a>
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
                                        <small class="text-muted">Client</small>
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
                        <h1>Mes Réservations</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="AcceuilClient.php">Accueil</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Réservations</li>
                            </ol>
                        </nav>
                    </div>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Onglets des réservations -->
                    <ul class="nav nav-pills mb-4" id="reservationTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $activeTab === 'upcoming' ? 'active' : ''; ?>" id="upcoming-tab" data-bs-toggle="pill" data-bs-target="#upcoming" type="button" role="tab" aria-controls="upcoming" aria-selected="true">
                                <i class="fas fa-hourglass-start me-2"></i>À venir <span class="badge bg-primary ms-1"><?php echo count($reservationsAVenir); ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $activeTab === 'ongoing' ? 'active' : ''; ?>" id="ongoing-tab" data-bs-toggle="pill" data-bs-target="#ongoing" type="button" role="tab" aria-controls="ongoing" aria-selected="false">
                                <i class="fas fa-clock me-2"></i>En cours <span class="badge bg-success ms-1"><?php echo count($reservationsEnCours); ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $activeTab === 'past' ? 'active' : ''; ?>" id="past-tab" data-bs-toggle="pill" data-bs-target="#past" type="button" role="tab" aria-controls="past" aria-selected="false">
                                <i class="fas fa-history me-2"></i>Passées <span class="badge bg-secondary ms-1"><?php echo count($reservationsPassees); ?></span>
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="reservationTabContent">
                        <!-- Réservations à venir -->
                        <div class="tab-pane fade <?php echo $activeTab === 'upcoming' ? 'show active' : ''; ?>" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
                            <div class="row">
                                <?php if (!empty($reservationsAVenir)): ?>
                                    <?php foreach ($reservationsAVenir as $reservation): ?>
                                        <div class="col-md-4 mb-4">
                                            <div class="card reservation-card">
                                                <?php 
                                                // CORRECTION: Utilisation correcte du chemin d'image
                                                $vehicule_image = isset($reservation['vehicule']['images']) && !empty($reservation['vehicule']['images']) ? 
                                                                "../" . $reservation['vehicule']['images'] : 
                                                                "https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg";
                                                ?>
                                                <img src="<?php echo $vehicule_image; ?>" class="card-img-top vehicle-img" alt="<?php echo $reservation['vehicule']['marque'] . ' ' . $reservation['vehicule']['modele']; ?>">
                                                
                                                <span class="status-badge badge bg-info">À venir</span>
                                                <?php
                                                // Calculer le nombre de jours avant le début de la réservation
                                                $dateDebut = new DateTime($reservation['date_debut']);
                                                $aujourdhui = new DateTime();
                                                $joursRestants = $aujourdhui->diff($dateDebut)->days;
                                                ?>
                                                <span class="status-countdown badge bg-light text-dark">
                                                    <i class="fas fa-calendar-day me-1"></i>
                                                    <?php if ($joursRestants == 0): ?>
                                                        Commence aujourd'hui
                                                    <?php else: ?>
                                                        Dans <?php echo $joursRestants; ?> jour<?php echo $joursRestants > 1 ? 's' : ''; ?>
                                                    <?php endif; ?>
                                                </span>
                                                
                                                <div class="card-body">
                                                    <h5 class="card-title"><?php echo htmlspecialchars($reservation['vehicule']['marque'] . ' ' . $reservation['vehicule']['modele']); ?></h5>
                                                    
                                                    <div class="booking-detail">
                                                        <i class="fas fa-calendar-alt"></i>
                                                        <span>Du <?php echo date('d/m/Y', strtotime($reservation['date_debut'])); ?> au <?php echo date('d/m/Y', strtotime($reservation['date_fin'])); ?></span>
                                                    </div>
                                                    
                                                    <div class="booking-detail">
                                                        <i class="fas fa-clock"></i>
                                                        <span>Durée: <?php echo $reservation['duree']; ?> jour<?php echo $reservation['duree'] > 1 ? 's' : ''; ?></span>
                                                    </div>
                                                    
                                                    <div class="booking-detail">
                                                        <i class="fas fa-car"></i>
                                                        <span>Catégorie: <?php echo htmlspecialchars($reservation['vehicule']['categorie']); ?></span>
                                                    </div>
                                                    
                                                    <div class="price-box">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span>Total:</span>
                                                            <span class="h5 mb-0 text-primary"><?php echo number_format($reservation['prix_total'], 0, ',', ' '); ?> FCFA</span>
                                                        </div>
                                                        <?php if (isset($reservation['reduction']) && $reservation['reduction'] > 0): ?>
                                                            <div class="text-success small">
                                                                <i class="fas fa-tags me-1"></i>Réduction appliquée: <?php echo $reservation['reduction']; ?>%
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="actions-bar">
                                                        <div class="d-flex justify-content-between">
                                                            <button class="btn btn-sm btn-info" onclick="viewReservationDetails(<?php echo $reservation['id']; ?>)" data-bs-toggle="modal" data-bs-target="#viewReservationModal">
                                                                <i class="fas fa-eye me-1"></i>Détails
                                                            </button>
                                                            <!-- CORRECTION: Bouton d'annulation pour toutes les réservations à venir -->
                                                            <button class="btn btn-sm btn-danger" onclick="prepareCancel(<?php echo $reservation['id']; ?>)" data-bs-toggle="modal" data-bs-target="#cancelReservationModal">
                                                                <i class="fas fa-times me-1"></i>Annuler
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="empty-state">
                                            <i class="fas fa-calendar-plus"></i>
                                            <h4>Aucune réservation à venir</h4>
                                            <p class="text-muted">Vous n'avez pas encore de réservations planifiées.</p>
                                            <a href="AcceuilClient.php" class="btn btn-primary mt-3">
                                                <i class="fas fa-search me-2"></i>Parcourir les offres
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Réservations en cours -->
                        <div class="tab-pane fade <?php echo $activeTab === 'ongoing' ? 'show active' : ''; ?>" id="ongoing" role="tabpanel" aria-labelledby="ongoing-tab">
                            <div class="row">
                                <?php if (!empty($reservationsEnCours)): ?>
                                    <?php foreach ($reservationsEnCours as $reservation): ?>
                                        <div class="col-md-4 mb-4">
                                            <div class="card reservation-card">
                                                <?php 
                                                // CORRECTION: Utilisation correcte du chemin d'image
                                                $vehicule_image = isset($reservation['vehicule']['images']) && !empty($reservation['vehicule']['images']) ? 
                                                                "../" . $reservation['vehicule']['images'] : 
                                                                "https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg";
                                                ?>
                                                <img src="<?php echo $vehicule_image; ?>" class="card-img-top vehicle-img" alt="<?php echo $reservation['vehicule']['marque'] . ' ' . $reservation['vehicule']['modele']; ?>">
                                                
                                                <span class="status-badge badge bg-success">En cours</span>
                                                <?php
                                                // Calculer le nombre de jours restants
                                                $dateFin = new DateTime($reservation['date_fin']);
                                                $aujourdhui = new DateTime();
                                                $joursRestants = $aujourdhui->diff($dateFin)->days;
                                                ?>
                                                <span class="status-countdown badge bg-light text-dark">
                                                    <i class="fas fa-hourglass-half me-1"></i>
                                                    <?php if ($joursRestants == 0): ?>
                                                        Se termine aujourd'hui
                                                    <?php else: ?>
                                                        Reste <?php echo $joursRestants; ?> jour<?php echo $joursRestants > 1 ? 's' : ''; ?>
                                                    <?php endif; ?>
                                                </span>
                                                
                                                <div class="card-body">
                                                    <h5 class="card-title"><?php echo htmlspecialchars($reservation['vehicule']['marque'] . ' ' . $reservation['vehicule']['modele']); ?></h5>
                                                    
                                                    <div class="booking-detail">
                                                        <i class="fas fa-calendar-alt"></i>
                                                        <span>Du <?php echo date('d/m/Y', strtotime($reservation['date_debut'])); ?> au <?php echo date('d/m/Y', strtotime($reservation['date_fin'])); ?></span>
                                                    </div>
                                                    
                                                    <div class="booking-detail">
                                                        <i class="fas fa-clock"></i>
                                                        <span>Durée: <?php echo $reservation['duree']; ?> jour<?php echo $reservation['duree'] > 1 ? 's' : ''; ?></span>
                                                    </div>
                                                    
                                                    <div class="booking-detail">
                                                        <i class="fas fa-car"></i>
                                                        <span>Catégorie: <?php echo htmlspecialchars($reservation['vehicule']['categorie']); ?></span>
                                                    </div>
                                                    
                                                    <div class="price-box">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span>Total:</span>
                                                            <span class="h5 mb-0 text-primary"><?php echo number_format($reservation['prix_total'], 0, ',', ' '); ?> FCFA</span>
                                                        </div>
                                                        <?php if (isset($reservation['reduction']) && $reservation['reduction'] > 0): ?>
                                                            <div class="text-success small">
                                                                <i class="fas fa-tags me-1"></i>Réduction appliquée: <?php echo $reservation['reduction']; ?>%
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="actions-bar">
                                                        <div class="d-flex justify-content-between">
                                                            <button class="btn btn-sm btn-info" onclick="viewReservationDetails(<?php echo $reservation['id']; ?>)" data-bs-toggle="modal" data-bs-target="#viewReservationModal">
                                                                <i class="fas fa-eye me-1"></i>Détails
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-primary" onclick="prepareExtend(<?php echo $reservation['id']; ?>)" data-bs-toggle="modal" data-bs-target="#extendReservationModal">
                                                                <i class="fas fa-calendar-plus me-1"></i>Prolonger
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="empty-state">
                                            <i class="fas fa-car"></i>
                                            <h4>Aucune réservation en cours</h4>
                                            <p class="text-muted">Vous n'avez pas de réservation active en ce moment.</p>
                                            <a href="AcceuilClient.php" class="btn btn-primary mt-3">
                                                <i class="fas fa-search me-2"></i>Parcourir les offres
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Réservations passées -->
                        <div class="tab-pane fade <?php echo $activeTab === 'past' ? 'show active' : ''; ?>" id="past" role="tabpanel" aria-labelledby="past-tab">
                            <div class="row">
                                <?php if (!empty($reservationsPassees)): ?>
                                    <?php foreach ($reservationsPassees as $reservation): ?>
                                        <div class="col-md-4 mb-4">
                                            <div class="card reservation-card">
                                                <?php 
                                                // CORRECTION: Utilisation correcte du chemin d'image
                                                $vehicule_image = isset($reservation['vehicule']['images']) && !empty($reservation['vehicule']['images']) ? 
                                                                "../" . $reservation['vehicule']['images'] : 
                                                                "https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg";
                                                ?>
                                                <img src="<?php echo $vehicule_image; ?>" class="card-img-top vehicle-img" alt="<?php echo $reservation['vehicule']['marque'] . ' ' . $reservation['vehicule']['modele']; ?>">
                                                
                                                <?php if ($reservation['statut'] === 'annulee'): ?>
                                                    <span class="status-badge badge bg-danger">Annulée</span>
                                                <?php elseif ($reservation['statut'] === 'terminee'): ?>
                                                    <span class="status-badge badge bg-secondary">Terminée</span>
                                                <?php else: ?>
                                                    <span class="status-badge badge bg-secondary">Passée</span>
                                                <?php endif; ?>
                                                
                                                <div class="card-body">
                                                    <h5 class="card-title"><?php echo htmlspecialchars($reservation['vehicule']['marque'] . ' ' . $reservation['vehicule']['modele']); ?></h5>
                                                    
                                                    <div class="booking-detail">
                                                        <i class="fas fa-calendar-alt"></i>
                                                        <span>Du <?php echo date('d/m/Y', strtotime($reservation['date_debut'])); ?> au <?php echo date('d/m/Y', strtotime($reservation['date_fin'])); ?></span>
                                                    </div>
                                                    
                                                    <div class="booking-detail">
                                                        <i class="fas fa-clock"></i>
                                                        <span>Durée: <?php echo $reservation['duree']; ?> jour<?php echo $reservation['duree'] > 1 ? 's' : ''; ?></span>
                                                    </div>
                                                    
                                                    <div class="booking-detail">
                                                        <i class="fas fa-car"></i>
                                                        <span>Catégorie: <?php echo htmlspecialchars($reservation['vehicule']['categorie']); ?></span>
                                                    </div>
                                                    
                                                    <div class="price-box">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span>Total:</span>
                                                            <span class="h5 mb-0 text-primary"><?php echo number_format($reservation['prix_total'], 0, ',', ' '); ?> FCFA</span>
                                                        </div>
                                                        <?php if (isset($reservation['reduction']) && $reservation['reduction'] > 0): ?>
                                                            <div class="text-success small">
                                                                <i class="fas fa-tags me-1"></i>Réduction appliquée: <?php echo $reservation['reduction']; ?>%
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="actions-bar">
                                                        <div class="d-flex justify-content-between">
                                                            <button class="btn btn-sm btn-info" onclick="viewReservationDetails(<?php echo $reservation['id']; ?>)" data-bs-toggle="modal" data-bs-target="#viewReservationModal">
                                                                <i class="fas fa-eye me-1"></i>Détails
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-primary" onclick="prepareRebook(<?php echo $reservation['vehicule_id']; ?>)">
                                                                <i class="fas fa-redo me-1"></i>Réserver à nouveau
                                                                </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="empty-state">
                                            <i class="fas fa-history"></i>
                                            <h4>Aucune réservation passée</h4>
                                            <p class="text-muted">Votre historique de réservations est vide.</p>
                                            <a href="AcceuilClient.php" class="btn btn-primary mt-3">
                                                <i class="fas fa-search me-2"></i>Parcourir les offres
                                            </a>
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
    
    <!-- Modal détails de réservation -->
    <div class="modal fade" id="viewReservationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de la réservation</h5>
                    <button 
type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div id="vehicleImageContainer">
                                <img src="" id="modalVehicleImage" class="img-fluid rounded" alt="Véhicule">
                            </div>
                            <div class="mt-3">
                                <h5 id="modalVehicleName">Chargement...</h5>
                                <p class="text-muted" id="modalVehicleCategory">Chargement...</p>
                            </div>
                            <div class="mt-4">
                                <h6>Caractéristiques du véhicule</h6>
                                <div id="vehicleFeatures" class="mt-2">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-car-side me-3 text-muted"></i>
                                        <span>Chargement...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Informations de réservation</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <td><i class="fas fa-hashtag me-2 text-muted"></i>Référence:</td>
                                                <td id="modalBookingRef" class="fw-bold">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-clipboard-check me-2 text-muted"></i>Statut:</td>
                                                <td id="modalBookingStatus">
                                                    <span class="badge bg-info">Chargement...</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-calendar-alt me-2 text-muted"></i>Période:</td>
                                                <td id="modalBookingPeriod">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-clock me-2 text-muted"></i>Durée:</td>
                                                <td id="modalBookingDuration">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-money-bill-wave me-2 text-muted"></i>Prix:</td>
                                                <td id="modalBookingPrice" class="fw-bold text-primary">Chargement...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="card" id="promoCardContainer">
                                <div class="card-body bg-light">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-tag fa-2x text-success"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">Réduction appliquée</h6>
                                            <p class="mb-0" id="modalPromoDetails">Chargement...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <div id="modalActionButton">
                        <!-- Les boutons d'action seront ajoutés dynamiquement en fonction du statut de la réservation -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal annulation de réservation -->
    <div class="modal fade" id="cancelReservationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Annuler la réservation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention:</strong> L'annulation d'une réservation est définitive.
                    </div>
                    <p>Êtes-vous sûr de vouloir annuler cette réservation ?</p>
                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0" id="cancelModalVehicleImg">
                                    <img src="" alt="Véhicule" class="img-thumbnail" style="width: 80px; height: 60px; object-fit: cover;">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 id="cancelModalVehicleName">Chargement...</h6>
                                    <p class="mb-0 small text-muted" id="cancelModalBookingPeriod">Chargement...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cancelReason" class="form-label">Motif d'annulation (optionnel):</label>
                        <select class="form-select" id="cancelReason">
                            <option value="changement_plans">Changement de plans</option>
                            <option value="meilleure_offre">J'ai trouvé une meilleure offre</option>
                            <option value="probleme_financier">Problème financier</option>
                            <option value="erreur">Erreur lors de la réservation</option>
                            <option value="autre">Autre raison</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="otherReasonContainer" style="display: none;">
                        <label for="otherReason" class="form-label">Précisez:</label>
                        <textarea class="form-control" id="otherReason" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form method="POST" action="../Controller/ReservationControllerClient.php">
                        <input type="hidden" name="action" value="cancel">
                        <input type="hidden" name="reservation_id" id="cancelReservationId">
                        <input type="hidden" name="cancel_reason" id="hiddenCancelReason">
                        <button type="submit" class="btn btn-danger">Confirmer l'annulation</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal prolongation de réservation -->
    <div class="modal fade" id="extendReservationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Prolonger la réservation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="extendForm" method="POST" action="../Controller/ReservationControllerClient.php">
                        <input type="hidden" name="action" value="extend">
                        <input type="hidden" name="reservation_id" id="extendReservationId">
                        
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0" id="extendModalVehicleImg">
                                        <img src="" alt="Véhicule" class="img-thumbnail" style="width: 80px; height: 60px; object-fit: cover;">
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 id="extendModalVehicleName">Chargement...</h6>
                                        <p class="mb-0 small text-muted" id="extendModalBookingPeriod">Chargement...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="currentEndDate" class="form-label">Date de fin actuelle</label>
                                <input type="date" class="form-control" id="currentEndDate" disabled>
                            </div>
                            <div class="col-md-6">
                                <label for="newEndDate" class="form-label">Nouvelle date de fin</label>
                                <input type="date" class="form-control" id="newEndDate" name="new_end_date" required>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <span>Le prix sera ajusté en fonction de la durée supplémentaire.</span>
                        </div>
                        
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Résumé</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <p class="mb-1">Jours supplémentaires: <span id="extraDays">0</span></p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-1">Prix journalier: <span id="dailyPrice">0 FCFA</span></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Coût supplémentaire:</span>
                                    <span class="h5 mb-0 text-primary" id="extraCost">0 FCFA</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span>Nouveau total:</span>
                                    <span class="h5 mb-0 text-primary" id="newTotalPrice">0 FCFA</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary" id="confirmExtendBtn">Confirmer la prolongation</button>
                        </div>
                    </form>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Données des réservations stockées pour un accès facile
        const reservationsData = <?php echo json_encode(array_merge($reservationsAVenir, $reservationsEnCours, $reservationsPassees)); ?>;
        
        // Fonctions pour les modals
        function viewReservationDetails(reservationId) {
            // Trouver la réservation dans nos données
            const reservation = reservationsData.find(r => r.id == reservationId);
            if (!reservation) return;
            
            // CORRECTION: Mise à jour des informations du véhicule dans le modal
            $('#modalVehicleName').text(reservation.vehicule.marque + ' ' + reservation.vehicule.modele);
            $('#modalVehicleCategory').text(reservation.vehicule.categorie || 'Non spécifiée');
            $('#modalBookingRef').text('#' + reservation.id);
            
            // Statut de la réservation avec badge approprié
            let statusClass = 'bg-secondary';
            let statusText = 'Passée';
            
            if (reservation.statut === 'en_attente') {
                statusClass = 'bg-warning text-dark';
                statusText = 'En attente';
            } else if (reservation.statut === 'confirmee' || reservation.statut === 'en_cours') {
                const today = new Date().toISOString().split('T')[0];
                if (today < reservation.date_debut) {
                    statusClass = 'bg-info';
                    statusText = 'À venir';
                } else if (today >= reservation.date_debut && today <= reservation.date_fin) {
                    statusClass = 'bg-success';
                    statusText = 'En cours';
                }
            } else if (reservation.statut === 'annulee') {
                statusClass = 'bg-danger';
                statusText = 'Annulée';
            } else if (reservation.statut === 'terminee') {
                statusClass = 'bg-secondary';
                statusText = 'Terminée';
            }
            
            $('#modalBookingStatus').html(`<span class="badge ${statusClass}">${statusText}</span>`);
            
            // Dates et durée
            $('#modalBookingPeriod').text(`Du ${formatDate(reservation.date_debut)} au ${formatDate(reservation.date_fin)}`);
            $('#modalBookingDuration').text(`${reservation.duree} jour${reservation.duree > 1 ? 's' : ''}`);
            
            // Prix
            $('#modalBookingPrice').text(formatCurrency(reservation.prix_total));
            
            // CORRECTION: Image du véhicule - Utiliser le chemin complet
            const vehiculeImage = reservation.vehicule.images ? 
                              '../' + reservation.vehicule.images : 
                              'https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg';
            $('#modalVehicleImage').attr('src', vehiculeImage);
            
            // CORRECTION: Caractéristiques du véhicule - Afficher les informations complètes
            let featuresHtml = '';
            
            featuresHtml += `
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-car-side me-3 text-muted"></i>
                    <span>${reservation.vehicule.marque} ${reservation.vehicule.modele} (${reservation.vehicule.annee || 'N/A'})</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-tag me-3 text-muted"></i>
                    <span>Catégorie: ${reservation.vehicule.categorie || 'Non spécifiée'}</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-money-bill-wave me-3 text-muted"></i>
                    <span>Prix journalier: ${formatCurrency(reservation.vehicule.prix_location)}</span>
                </div>
            `;
            
            if (reservation.vehicule.description) {
                featuresHtml += `
                    <div class="d-flex align-items-start mb-2">
                        <i class="fas fa-info-circle me-3 text-muted mt-1"></i>
                        <span>${reservation.vehicule.description}</span>
                    </div>
                `;
            }
            
            $('#vehicleFeatures').html(featuresHtml);
            
            // Informations sur la promo
            if (reservation.reduction && reservation.reduction > 0) {
                $('#promoCardContainer').show();
                $('#modalPromoDetails').html(`Réduction de <strong>${reservation.reduction}%</strong> sur cette réservation`);
            } else {
                $('#promoCardContainer').hide();
            }
            
            // Boutons d'action en fonction du statut
            let actionButtonHtml = '';
            
            if (statusText === 'À venir') {
                actionButtonHtml = `
                    <button type="button" class="btn btn-danger" onclick="prepareCancel(${reservation.id})" data-bs-toggle="modal" data-bs-target="#cancelReservationModal" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                `;
            } else if (statusText === 'En cours') {
                actionButtonHtml = `
                    <button type="button" class="btn btn-primary" onclick="prepareExtend(${reservation.id})" data-bs-toggle="modal" data-bs-target="#extendReservationModal" data-bs-dismiss="modal">
                        <i class="fas fa-calendar-plus me-1"></i>Prolonger
                    </button>
                `;
            } else if (statusText === 'Passée' || statusText === 'Terminée') {
                actionButtonHtml = `
                    <button type="button" class="btn btn-outline-primary" onclick="prepareRebook(${reservation.vehicule_id})">
                        <i class="fas fa-redo me-1"></i>Réserver à nouveau
                    </button>
                `;
            }
            
            $('#modalActionButton').html(actionButtonHtml);
        }
        
        function prepareCancel(reservationId) {
            // Trouver la réservation dans nos données
            const reservation = reservationsData.find(r => r.id == reservationId);
            if (!reservation) return;
            
            // Mettre à jour les informations dans le modal
            $('#cancelReservationId').val(reservationId);
            $('#cancelModalVehicleName').text(reservation.vehicule.marque + ' ' + reservation.vehicule.modele);
            $('#cancelModalBookingPeriod').text(`Du ${formatDate(reservation.date_debut)} au ${formatDate(reservation.date_fin)}`);
            
            // CORRECTION: Image du véhicule - Utiliser le chemin complet
            const vehiculeImage = reservation.vehicule.images ? 
                              '../' + reservation.vehicule.images : 
                              'https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg';
            $('#cancelModalVehicleImg img').attr('src', vehiculeImage);
            
            // Gérer le champ de raison d'annulation
            $('#cancelReason').change(function() {
                if ($(this).val() === 'autre') {
                    $('#otherReasonContainer').show();
                } else {
                    $('#otherReasonContainer').hide();
                }
                
                // Mettre à jour la valeur cachée pour le formulaire
                updateCancelReason();
            });
            
            $('#otherReason').on('input', updateCancelReason);
            
            // Initialiser les champs
            $('#cancelReason').val('changement_plans');
            $('#otherReason').val('');
            $('#otherReasonContainer').hide();
            updateCancelReason();
        }
        
        function updateCancelReason() {
            const reason = $('#cancelReason').val();
            if (reason === 'autre') {
                $('#hiddenCancelReason').val($('#otherReason').val() || 'autre');
            } else {
                $('#hiddenCancelReason').val(reason);
            }
        }
        
        function prepareExtend(reservationId) {
            // Trouver la réservation dans nos données
            const reservation = reservationsData.find(r => r.id == reservationId);
            if (!reservation) return;
            
            // Mettre à jour les informations dans le modal
            $('#extendReservationId').val(reservationId);
            $('#extendModalVehicleName').text(reservation.vehicule.marque + ' ' + reservation.vehicule.modele);
            $('#extendModalBookingPeriod').text(`Du ${formatDate(reservation.date_debut)} au ${formatDate(reservation.date_fin)}`);
            
            // CORRECTION: Image du véhicule - Utiliser le chemin complet
            const vehiculeImage = reservation.vehicule.images ? 
                              '../' + reservation.vehicule.images : 
                              'https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg';
            $('#extendModalVehicleImg img').attr('src', vehiculeImage);
            
            // Configurer les dates
            $('#currentEndDate').val(reservation.date_fin);
            
            // Définir la date minimale pour la nouvelle date de fin (actuelle + 1 jour)
            const minDate = new Date(reservation.date_fin);
            minDate.setDate(minDate.getDate() + 1);
            $('#newEndDate').attr('min', minDate.toISOString().split('T')[0]);
            $('#newEndDate').val(minDate.toISOString().split('T')[0]);
            
            // Stocker le prix journalier et le prix total original
            const dailyPrice = reservation.vehicule.prix_location;
const originalTotal = reservation.prix_total;
const originalEndDate = new Date(reservation.date_fin);

// Afficher le prix journalier
$('#dailyPrice').text(formatCurrency(dailyPrice));

// Calculer et mettre à jour le coût supplémentaire lors du changement de date
$('#newEndDate').on('change', function() {
    const newEndDate = new Date($(this).val());
    
    // Vérifier que la date est valide et postérieure à la date de fin actuelle
    if (newEndDate <= originalEndDate) {
        alert('La nouvelle date de fin doit être postérieure à la date de fin actuelle.');
        $(this).val(minDate.toISOString().split('T')[0]);
        newEndDate = new Date(minDate);
    }
    
    // Calculer le nombre de jours supplémentaires
    const diffTime = Math.abs(newEndDate - originalEndDate);
    const extraDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    // Calculer le coût supplémentaire
    const extraCost = extraDays * dailyPrice;
    const newTotal = originalTotal + extraCost;
    
    // Mettre à jour les informations affichées
    $('#extraDays').text(extraDays);
    $('#extraCost').text(formatCurrency(extraCost));
    $('#newTotalPrice').text(formatCurrency(newTotal));
    
    // Activer/désactiver le bouton de confirmation
    if (extraDays > 0) {
        $('#confirmExtendBtn').prop('disabled', false);
    } else {
        $('#confirmExtendBtn').prop('disabled', true);
    }
});

// Déclencher un événement de changement pour initialiser les calculs
$('#newEndDate').trigger('change');
}

function prepareRebook(vehiculeId) {
    // Rediriger vers la page de détail du véhicule pour une nouvelle réservation
    window.location.href = `detail_vehicule.php?id=${vehiculeId}`;
}

// Fonctions utilitaires
function formatDate(dateString) {
    const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
    return new Date(dateString).toLocaleDateString('fr-FR', options);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', { 
        style: 'currency', 
        currency: 'XOF',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount).replace('XOF', 'FCFA');
}

// Gestion du modal de profil
$(document).ready(function() {
    $('#editProfileBtn').click(function() {
        $('#profileInfo').hide();
        $('#profileEditForm').show();
    });
    
    $('#cancelEditBtn').click(function() {
        $('#profileEditForm').hide();
        $('#profileInfo').show();
    });
    
    // Au chargement, activer l'onglet correct en fonction de l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    
    if (tab) {
        const tabElement = document.querySelector(`#${tab}-tab`);
        if (tabElement) {
            const tabTrigger = new bootstrap.Tab(tabElement);
            tabTrigger.show();
        }
    }
    
    // Mettre à jour l'URL lorsqu'un onglet est sélectionné
    $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
        const tabId = $(e.target).attr('id').replace('-tab', '');
        history.replaceState(null, null, `?tab=${tabId}`);
    });
});
</script>
</body>
</html>