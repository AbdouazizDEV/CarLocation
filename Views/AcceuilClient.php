<?php

    session_start();
    require_once __DIR__ . "/../Models/voiture.php";
    require_once __DIR__ . "/../Models/offre.php";
    require_once __DIR__ . "/../Models/favoris.php";

    // Vérifier si l'utilisateur est connecté et est un client
    if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'client') {
        header('Location: login.php');
        exit();
    }

    // Instancier les objets nécessaires
    $voitureModel = new Voiture();
    $offreModel = new Offre();
    $favorisModel = new Favoris();

    // Récupérer toutes les offres actives
    $offres = $offreModel->getActiveOffres();

    // Récupérer les favoris du client
    $favoris = $favorisModel->getOffresFavorites($_SESSION['user_id']); // Cette ligne a été modifiée
    $favorisIds = array_column($favoris, 'id'); // Assurez-vous que cela correspond aux clés retournées par getOffresFavorites
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NDAAMAR - Offres</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Public/CSS/ClientStyle.css">
    <style>
        .card-offer {
            transition: transform 0.3s;
            height: 100%;
        }
        .card-offer:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .offer-timer {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(0,0,0,0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        .offer-status {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        .offer-discount {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .offer-vehicle-image {
            height: 180px;
            object-fit: cover;
        }
        .offer-details {
            padding: 15px;
        }
        .icon-btn {
            width: 36px;
            height: 36px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            transition: all 0.3s;
        }
        .favorite-btn {
            color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
        }
        .favorite-btn:hover {
            background-color: rgba(220, 53, 69, 0.2);
        }
        .favorite-btn.active {
            color: white;
            background-color: #dc3545;
        }
        .view-btn {
            color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.1);
        }
        .view-btn:hover {
            background-color: rgba(13, 110, 253, 0.2);
        }
        .reserve-btn {
            color: #198754;
            background-color: rgba(25, 135, 84, 0.1);
        }
        .reserve-btn:hover {
            background-color: rgba(25, 135, 84, 0.2);
        }
        .car-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .filter-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .promo-banner {
            background: linear-gradient(45deg, #3498db, #2c3e50);
            border-radius: 10px;
            padding: 20px;
            color: white;
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
                    <a href="AcceuilClient.php" class="menu-item active">
                        <i class="fas fa-tag"></i> Offres
                    </a>
                    <a href="favoris.php" class="menu-item">
                        <i class="fas fa-heart"></i> Favoris
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
                        <h1>Offres Spéciales</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Offres</li>
                            </ol>
                        </nav>
                    </div>
                    
                    <!-- Banner de promotion -->
                    <div class="promo-banner mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="mb-2">Économisez jusqu'à 30% sur les locations longue durée</h3>
                                <p class="mb-0">Utilisez le code promo <span class="badge bg-warning text-dark">NDAAMAR30</span> lors de votre réservation !</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <button class="btn btn-light">En savoir plus</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filtres -->
                    <div class="filter-section mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="categorieFilter" class="form-label">Catégorie</label>
                                <select class="form-select" id="categorieFilter">
                                    <option value="">Toutes les catégories</option>
                                    <option value="SUV">SUV / 4x4</option>
                                    <option value="Berline">Berline</option>
                                    <option value="Citadine">Citadine</option>
                                    <option value="Bus">Bus / Minibus</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="reductionFilter" class="form-label">Réduction minimale</label>
                                <select class="form-select" id="reductionFilter">
                                    <option value="0">Toutes les réductions</option>
                                    <option value="10">10% et plus</option>
                                    <option value="20">20% et plus</option>
                                    <option value="30">30% et plus</option>
                                    <option value="40">40% et plus</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="sortFilter" class="form-label">Trier par</label>
                                <select class="form-select" id="sortFilter">
                                    <option value="newest">Plus récentes</option>
                                    <option value="highest_discount">Réduction la plus élevée</option>
                                    <option value="lowest_price">Prix le plus bas</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button class="btn btn-primary w-100" id="applyFilters">
                                    <i class="fas fa-filter me-2"></i>Appliquer les filtres
                                </button>
                            </div>
                        </div>
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
                    
                    <!-- Afficher les offres -->
                    <div class="row" id="offresContainer">
                        <?php 
                        if (!empty($offres)): 
                            foreach ($offres as $offre): 
                                // Récupérer les détails des véhicules associés à cette offre
                                $offre_vehicules = $offreModel->getVehiculesForOffre($offre['id']);
                                $vehicule_image = !empty($offre_vehicules) && isset($offre_vehicules[0]['images']) ? 
                                                "../" . $offre_vehicules[0]['images'] : 
                                                "https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg";
                                
                                // Vérifier si cette offre est dans les favoris du client
                                $isFavorite = in_array($offre['id'], $favorisIds);
                        ?>
                        <div class="col-md-4 mb-4">
                            <div class="card card-offer">
                                <img src="<?php echo $vehicule_image; ?>" class="card-img-top offer-vehicle-image" alt="<?php echo htmlspecialchars($offre['titre']); ?>">
                                <span class="offer-timer"><i class="far fa-clock me-1"></i>Expire le <?php echo date('d/m/Y', strtotime($offre['date_fin'])); ?></span>
                                <span class="offer-discount">-<?php echo $offre['reduction']; ?>%</span>
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($offre['titre']); ?></h5>
                                    <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($offre['description'], 0, 100)) . (strlen($offre['description']) > 100 ? '...' : ''); ?></p>
                                    <div class="mb-3">
                                        <span class="badge bg-light text-dark me-1"><i class="fas fa-calendar-alt me-1"></i>Jusqu'au <?php echo date('d/m/Y', strtotime($offre['date_fin'])); ?></span>
                                        <?php if (!empty($offre['code_promo'])): ?>
                                            <span class="badge bg-dark"><i class="fas fa-ticket-alt me-1"></i><?php echo htmlspecialchars($offre['code_promo']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <button class="btn icon-btn favorite-btn <?php echo $isFavorite ? 'active' : ''; ?>" onclick="toggleFavorite(<?php echo $offre['id']; ?>, this)" data-offre-id="<?php echo $offre['id']; ?>" title="Ajouter aux favoris">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                        <button class="btn icon-btn view-btn" onclick="prepareViewModal(<?php echo $offre['id']; ?>)" data-bs-toggle="modal" data-bs-target="#viewOfferModal" title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn icon-btn reserve-btn" onclick="prepareReservationModal(<?php echo $offre['id']; ?>)" data-bs-toggle="modal" data-bs-target="#reservationModal" title="Réserver">
                                            <i class="fas fa-calendar-plus"></i>
                                        </button>
                                    </div>
                                    <br><br>
                                </div>
                            </div>
                        </div>
                        <?php 
                            endforeach; 
                        else: 
                        ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i>Aucune offre disponible pour le moment.
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Voir les détails de l'offre -->
    <div class="modal fade" id="viewOfferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de l'offre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 id="viewOfferTitle">Chargement...</h3>
                                <span id="viewOfferDiscount" class="badge bg-danger">-20%</span>
                            </div>
                            <p id="viewOfferDescription" class="text-muted">Chargement...</p>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Informations</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <td>Réduction:</td>
                                                <td id="viewOfferReduction" class="fw-bold text-danger">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td>Période:</td>
                                                <td id="viewOfferPeriod">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td>Code promo:</td>
                                                <td id="viewOfferCode">Chargement...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">À propos</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <td>Véhicules concernés:</td>
                                                <td id="viewOfferVehiclesCount" class="fw-bold">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td>Disponible depuis:</td>
                                                <td id="viewOfferStartDate">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td>Disponible jusqu'au:</td>
                                                <td id="viewOfferEndDate">Chargement...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <h5>Véhicules disponibles avec cette offre</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Marque/Modèle</th>
                                            <th>Catégorie</th>
                                            <th>Prix normal</th>
                                            <th>Prix avec réduction</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="viewOfferVehicles">
                                        <!-- Les véhicules seront ajoutés dynamiquement -->
                                        <tr>
                                            <td colspan="6" class="text-center">Chargement...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" onclick="switchToReservationModal()">Réserver maintenant</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Réservation -->
    <div class="modal fade" id="reservationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Réservation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="reservationForm" action="../Controller/ReservationControllerClient.php" method="POST">
                        <input type="hidden" name="offre_id" id="reservation_offre_id">
                        <input type="hidden" name="client_id" value="<?php echo $_SESSION['user_id']; ?>">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="mb-4">
                            <h4 id="reservationOfferTitle">Offre: Chargement...</h4>
                            <p class="text-muted" id="reservationOfferDescription">Chargement...</p>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="vehicule_id" class="form-label">Sélectionner un véhicule</label>
                                <select class="form-select" id="vehicule_id" name="vehicule_id" required>
                                    <option value="">Sélectionner un véhicule</option>
                                    <!-- Options chargées dynamiquement -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="code_promo" class="form-label">Code promo (optionnel)</label>
                                <input type="text" class="form-control" id="code_promo" name="code_promo">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date_debut" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="date_debut" name="date_debut" required>
                            </div>
                            <div class="col-md-6">
                                <label for="date_fin" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="date_fin" name="date_fin" required>
                            </div>
                        </div>
                        
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Résumé de la réservation</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1">Véhicule: <span id="resume_vehicule">Non sélectionné</span></p>
                                        <p class="mb-1">Durée: <span id="resume_duree">0 jours</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">Prix normal: <span id="resume_prix_normal">0 FCFA</span></p>
                                        <p class="mb-1">Réduction: <span id="resume_reduction">0%</span></p>
                                        <p class="mb-1 fw-bold">Prix total: <span id="resume_prix_total" class="text-danger">0 FCFA</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="accept_terms" name="accept_terms" required>
                            <label class="form-check-label" for="accept_terms">
                                J'accepte les conditions générales de location
                            </label>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Confirmer la réservation</button>
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
        // Variable globale pour l'ID de l'offre en cours
        let currentOfferId = null;
        // Données des offres et véhicules
        let offresData = {};
        let vehiculesData = {};

        // Document ready
        $(document).ready(function() {
            // Initialiser les dates par défaut pour le formulaire de réservation
            const today = new Date();
            const todayISO = today.toISOString().split('T')[0];
            $('#date_debut').val(todayISO);
            $('#date_debut').attr('min', todayISO);
            
            const nextWeek = new Date();
            nextWeek.setDate(today.getDate() + 7);
            const nextWeekISO = nextWeek.toISOString().split('T')[0];
            $('#date_fin').val(nextWeekISO);
            $('#date_fin').attr('min', todayISO);

            // Événements pour les dates de réservation
            $('#date_debut, #date_fin, #vehicule_id').change(updateReservationSummary);
            
            // Gestion du profil utilisateur
            $('#editProfileBtn').on('click', function() {
                $('#profileInfo').hide();
                $('#profileEditForm').show();
            });
            
            $('#cancelEditBtn').on('click', function() {
                $('#profileInfo').show();
                $('#profileEditForm').hide();
            });
            
            // Validation du formulaire de réservation
            $('#reservationForm').on('submit', function(e) {
                const dateDebut = new Date($('#date_debut').val());
                const dateFin = new Date($('#date_fin').val());
                
                if (dateFin <= dateDebut) {
                    e.preventDefault();
                    alert('La date de fin doit être supérieure à la date de début.');
                    return false;
                }
                
                return true;
            });
        });

        // Fonction pour ajouter/retirer des favoris
        function toggleFavorite(offreId, button) {
            $(button).addClass('disabled').attr('disabled', true);
            
            $.ajax({
                url: '../Controller/FavorisController.php',
                type: 'POST',
                data: {
                    action: 'toggle',
                    offre_id: offreId,
                    client_id: <?php echo $_SESSION['user_id']; ?>
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (response.added) {
                            $(button).addClass('active');
                        } else {
                            $(button).removeClass('active');
                        }
                    } else {
                        alert('Erreur: ' + response.message);
                    }
                    $(button).removeClass('disabled').attr('disabled', false);
                },
                error: function() {
                    alert('Erreur de communication avec le serveur');
                    $(button).removeClass('disabled').attr('disabled', false);
                }
            });
        }

        // Fonction pour préparer le modal de visualisation
        function prepareViewModal(id) {
            currentOfferId = id;
            console.log("Préparation du modal de vue pour l'offre ID:", id);
            
            // Charger les détails de l'offre via AJAX
            $.ajax({
                url: '../Controller/OffreController.php',
                type: 'GET',
                data: { action: 'get', id: id },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        const offre = data.offre;
                        offresData[id] = offre;
                        
                        // Mettre à jour les détails de l'offre
                        $('#viewOfferTitle').text(offre.titre);
                        $('#viewOfferDiscount').text('-' + offre.reduction + '%');
                        $('#viewOfferDescription').text(offre.description || 'Aucune description disponible');
                        
                        // Informations
                        $('#viewOfferReduction').text('-' + offre.reduction + '%');
                        $('#viewOfferPeriod').text(formatDate(offre.date_debut) + ' à ' + formatDate(offre.date_fin));
                        $('#viewOfferCode').text(offre.code_promo || 'Aucun code promo');
                        
                        // À propos
                        $('#viewOfferVehiclesCount').text(offre.vehicules ? offre.vehicules.length : 0);
                        $('#viewOfferStartDate').text(formatDate(offre.date_debut));
                        $('#viewOfferEndDate').text(formatDate(offre.date_fin));
                        
                        // Véhicules
                        const vehiclesContainer = $('#viewOfferVehicles');
                        vehiclesContainer.empty();
                        
                        if (offre.vehicules && offre.vehicules.length > 0) {
                            offre.vehicules.forEach(vehicule => {
                                vehiculesData[vehicule.id] = vehicule;
                                
                                const prixNormal = parseInt(vehicule.prix_location);
                                const reduction = parseFloat(offre.reduction);
                                const prixReduit = Math.round(prixNormal * (1 - reduction / 100));
                                
                                const row = `
                                    <tr>
                                        <td>
                                            <img src="../${vehicule.images || 'path/to/default-image.jpg'}" 
                                                alt="${vehicule.marque} ${vehicule.modele}" 
                                                class="car-image">
                                        </td>
                                        <td>${vehicule.marque} ${vehicule.modele}</td>
                                        <td>${vehicule.categorie}</td>
                                        <td>${formatCurrency(prixNormal)}</td>
                                        <td class="text-danger fw-bold">${formatCurrency(prixReduit)}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" 
                                                onclick="prepareReservationModal(${offre.id}, ${vehicule.id})">
                                                <i class="fas fa-calendar-plus me-1"></i> Réserver
                                            </button>
                                        </td>
                                    </tr>
                                `;
                                
                                vehiclesContainer.append(row);
                            });
                        } else {
                            vehiclesContainer.html('<tr><td colspan="6" class="text-center">Aucun véhicule associé à cette offre</td></tr>');
                        }
                    } else {
                        alert('Erreur lors du chargement des détails de l\'offre: ' + (data.message || 'Erreur inconnue'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Erreur AJAX:", status, error);
                    alert('Erreur de communication avec le serveur.');
                }
            });
        }

        // Fonction pour préparer le modal de réservation
        function prepareReservationModal(offreId, vehiculeId = null) {
            currentOfferId = offreId;
            $('#reservation_offre_id').val(offreId);
            
            // Si on a déjà les données
            if (offresData[offreId]) {
                populateReservationModal(offresData[offreId], vehiculeId);
            } else {
                // Sinon, charger les détails de l'offre via AJAX
                $.ajax({
                    url: '../Controller/OffreController.php',
                    type: 'GET',
                    data: { action: 'get', id: offreId },
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            offresData[offreId] = data.offre;
                            populateReservationModal(data.offre, vehiculeId);
                        } else {
                            alert('Erreur lors du chargement des détails de l\'offre: ' + (data.message || 'Erreur inconnue'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Erreur AJAX:", status, error);
                        alert('Erreur de communication avec le serveur.');
                    }
                });
            }
        }

        // Fonction pour remplir le modal de réservation
        function populateReservationModal(offre, vehiculeId = null) {
            // Titre et description
            $('#reservationOfferTitle').text('Offre: ' + offre.titre);
            $('#reservationOfferDescription').text(offre.description || 'Aucune description disponible');
            
            // Si un code promo est associé à l'offre, le pré-remplir
            if (offre.code_promo) {
                $('#code_promo').val(offre.code_promo);
            } else {
                $('#code_promo').val('');
            }
            
            // Remplir la liste des véhicules
            const vehiculeSelect = $('#vehicule_id');
            vehiculeSelect.empty();
            vehiculeSelect.append('<option value="">Sélectionner un véhicule</option>');
            
            if (offre.vehicules && offre.vehicules.length > 0) {
                offre.vehicules.forEach(vehicule => {
                    vehiculesData[vehicule.id] = vehicule;
                    
                    const prixNormal = parseInt(vehicule.prix_location);
                    const reduction = parseFloat(offre.reduction);
                    const prixReduit = Math.round(prixNormal * (1 - reduction / 100));
                    
                    const option = new Option(
                        `${vehicule.marque} ${vehicule.modele} - ${formatCurrency(prixReduit)} / jour (au lieu de ${formatCurrency(prixNormal)})`, 
                        vehicule.id
                    );
                    
                    vehiculeSelect.append(option);
                });
                
                // Si un vehiculeId est spécifié, le sélectionner
                if (vehiculeId && vehiculesData[vehiculeId]) {
                    vehiculeSelect.val(vehiculeId);
                }
            }
            
            // Mettre à jour le résumé
            updateReservationSummary();
            
            // Fermer le modal de visualisation s'il est ouvert
            $('#viewOfferModal').modal('hide');
            
            // Ouvrir le modal de réservation
            $('#reservationModal').modal('show');
        }

        // Fonction pour mettre à jour le résumé de la réservation
        function updateReservationSummary() {
            const vehiculeId = $('#vehicule_id').val();
            const dateDebut = new Date($('#date_debut').val());
            const dateFin = new Date($('#date_fin').val());
            
            if (!vehiculeId || !dateDebut || !dateFin || isNaN(dateDebut.getTime()) || isNaN(dateFin.getTime())) {
                // Réinitialiser le résumé si les données sont incomplètes
                $('#resume_vehicule').text('Non sélectionné');
                $('#resume_duree').text('0 jours');
                $('#resume_prix_normal').text('0 FCFA');
                $('#resume_reduction').text('0%');
                $('#resume_prix_total').text('0 FCFA');
                return;
            }
            
            // Calcul de la durée en jours
            const diffTime = Math.abs(dateFin - dateDebut);
            const duree = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (duree <= 0) {
                $('#resume_duree').text('Date de fin invalide');
                return;
            }
            
            const vehicule = vehiculesData[vehiculeId];
            const offre = offresData[currentOfferId];
            
            if (vehicule && offre) {
                const prixNormal = parseInt(vehicule.prix_location);
                const reduction = parseFloat(offre.reduction);
                const prixReduit = Math.round(prixNormal * (1 - reduction / 100));
                
                const prixTotalNormal = prixNormal * duree;
                const prixTotalReduit = prixReduit * duree;
                
                // Mettre à jour le résumé
                $('#resume_vehicule').text(`${vehicule.marque} ${vehicule.modele}`);
                $('#resume_duree').text(`${duree} jour${duree > 1 ? 's' : ''}`);
                $('#resume_prix_normal').text(formatCurrency(prixTotalNormal));
                $('#resume_reduction').text(`-${reduction}%`);
                $('#resume_prix_total').text(formatCurrency(prixTotalReduit));
            }
        }

        // Fonction pour passer du modal de visualisation au modal de réservation
        function switchToReservationModal() {
            $('#viewOfferModal').modal('hide');
            setTimeout(() => {
                prepareReservationModal(currentOfferId);
            }, 500);
        }

        // Fonction pour formater les dates
        function formatDate(dateString) {
            const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
            return new Date(dateString).toLocaleDateString('fr-FR', options);
        }
        
        // Fonction pour formater les montants en FCFA
        function formatCurrency(amount) {
            return new Intl.NumberFormat('fr-FR', { 
                style: 'currency', 
                currency: 'XOF',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        }

        // Fonctions pour les filtres
        $('#applyFilters').on('click', function() {
            const categorie = $('#categorieFilter').val();
            const reductionMin = parseInt($('#reductionFilter').val()) || 0;
            const sortBy = $('#sortFilter').val();
            
            // Appliquer les filtres via AJAX
            $.ajax({
                url: '../Controller/OffreController.php',
                type: 'GET',
                data: { 
                    action: 'filter', 
                    categorie: categorie,
                    reduction_min: reductionMin,
                    sort_by: sortBy
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Mettre à jour l'affichage des offres
                        updateOffresDisplay(response.offres);
                    } else {
                        alert('Erreur lors du filtrage des offres: ' + response.message);
                    }
                },
                error: function() {
                    alert('Erreur de communication avec le serveur');
                }
            });
        });

        // Fonction pour mettre à jour l'affichage des offres après filtrage
        function updateOffresDisplay(offres) {
            const container = $('#offresContainer');
            container.empty();
            
            if (offres && offres.length > 0) {
                offres.forEach(offre => {
                    // Récupérer l'image du premier véhicule
                    const vehicule_image = offre.vehicule_image || "https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg";
                    const isFavorite = offre.is_favorite ? 'active' : '';
                    
                    const offreHtml = `
                        <div class="col-md-4 mb-4">
                            <div class="card card-offer">
                                <img src="${vehicule_image}" class="card-img-top offer-vehicle-image" alt="${offre.titre}">
                                <span class="offer-timer"><i class="far fa-clock me-1"></i>Expire le ${formatDate(offre.date_fin)}</span>
                                <span class="offer-discount">-${offre.reduction}%</span>
                                
                                <div class="card-body">
                                    <h5 class="card-title">${offre.titre}</h5>
                                    <p class="card-text text-muted small">${offre.description ? offre.description.substring(0, 100) + (offre.description.length > 100 ? '...' : '') : ''}</p>
                                    <div class="mb-3">
                                        <span class="badge bg-light text-dark me-1"><i class="fas fa-calendar-alt me-1"></i>Jusqu'au ${formatDate(offre.date_fin)}</span>
                                        ${offre.code_promo ? `<span class="badge bg-dark"><i class="fas fa-ticket-alt me-1"></i>${offre.code_promo}</span>` : ''}
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <button class="btn icon-btn favorite-btn ${isFavorite}" onclick="toggleFavorite(${offre.id}, this)" data-offre-id="${offre.id}" title="Ajouter aux favoris">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                        <button class="btn icon-btn view-btn" onclick="prepareViewModal(${offre.id})" data-bs-toggle="modal" data-bs-target="#viewOfferModal" title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn icon-btn reserve-btn" onclick="prepareReservationModal(${offre.id})" data-bs-toggle="modal" data-bs-target="#reservationModal" title="Réserver">
                                            <i class="fas fa-calendar-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    container.append(offreHtml);
                });
            } else {
                container.html(`
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>Aucune offre ne correspond à vos critères.
                        </div>
                    </div>
                `);
            }
        }
    </script>
</body>
</html>