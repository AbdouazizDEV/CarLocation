<?php
    session_start();
    require_once __DIR__ . "/../Models/voiture.php";
    require_once __DIR__ . "/../Models/offre.php";
    require_once __DIR__ . "/../Models/favoris.php";
    require_once __DIR__ . "/../Models/Reservation.php";

    // Vérifier si l'utilisateur est connecté et est un client
    if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'client') {
        header('Location: login.php');
        exit();
    }

    // Instancier les objets nécessaires
    $voitureModel = new Voiture();
    $offreModel = new Offre();
    $favorisModel = new Favoris();
    $reservationModel = new Reservation();

    // Récupérer toutes les offres actives
    $offres = $offreModel->getActiveOffres();

    // Récupérer les voitures disponibles
    $voitures = $voitureModel->getVoituresByStatus('disponible');

    // Récupérer les favoris du client (offres et voitures)
    $favorisOffres = $favorisModel->getOffresFavorites($_SESSION['user_id']);
    $favorisVoitures = $favorisModel->getVoituresFavorites($_SESSION['user_id']);
    $favorisOffresIds = array_column($favorisOffres, 'id');
    $favorisVoituresIds = array_column($favorisVoitures, 'id');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NDAAMAR - Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Public/CSS/ClientStyle.css">
    <style>
        .card-offer, .card-vehicle {
            transition: transform 0.3s;
            height: 100%;
        }
        .card-offer:hover, .card-vehicle:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .offer-timer, .vehicle-badge {
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
        .vehicle-price {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: #198754;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .offer-vehicle-image, .vehicle-image {
            height: 180px;
            object-fit: cover;
        }
        .offer-details, .vehicle-details {
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
        .category-header {
            padding: 10px 0;
            margin: 30px 0 20px;
            border-bottom: 2px solid #f0f0f0;
            position: relative;
        }
        .category-header:after {
            content: '';
            position: absolute;
            width: 100px;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #0d6efd;
        }
        .vehicle-features {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .vehicle-features i {
            width: 20px;
            text-align: center;
            margin-right: 5px;
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
                        <h1>Accueil</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item active" aria-current="page">Accueil</li>
                            </ol>
                        </nav>
                    </div>
                    
                    <!-- Banner de promotion -->
                    <div class="promo-banner mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="mb-2">Bienvenue chez NDAAMAR</h3>
                                <p class="mb-0">Trouvez le véhicule idéal pour tous vos besoins. Économisez jusqu'à 30% sur les locations longue durée !</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#promoDetailsModal">Voir les promos</button>
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
                    
                    <!-- Filtres rapides pour les véhicules -->
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
                                <label for="prixMaxFilter" class="form-label">Prix maximum</label>
                                <select class="form-select" id="prixMaxFilter">
                                    <option value="">Tous les prix</option>
                                    <option value="25000">< 25 000 FCFA / jour</option>
                                    <option value="50000">< 50 000 FCFA / jour</option>
                                    <option value="75000">< 75 000 FCFA / jour</option>
                                    <option value="100000">< 100 000 FCFA / jour</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="sortFilter" class="form-label">Trier par</label>
                                <select class="form-select" id="sortFilter">
                                    <option value="newest">Plus récents</option>
                                    <option value="price_asc">Prix croissant</option>
                                    <option value="price_desc">Prix décroissant</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button class="btn btn-primary w-100" id="applyFilters">
                                    <i class="fas fa-filter me-2"></i>Appliquer les filtres
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section des offres spéciales -->
                    <h2 class="category-header">Offres Spéciales</h2>
                    
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
                                $isFavorite = in_array($offre['id'], $favorisOffresIds);
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
                                        <button class="btn icon-btn favorite-btn <?php echo $isFavorite ? 'active' : ''; ?>" 
                                                onclick="toggleFavoriteOffre(<?php echo $offre['id']; ?>, this)" 
                                                data-offre-id="<?php echo $offre['id']; ?>" 
                                                title="<?php echo $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                        <button class="btn icon-btn view-btn" onclick="prepareViewOffreModal(<?php echo $offre['id']; ?>)" data-bs-toggle="modal" data-bs-target="#viewOfferModal" title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn icon-btn reserve-btn" onclick="prepareReservationOffreModal(<?php echo $offre['id']; ?>)" data-bs-toggle="modal" data-bs-target="#reservationOffreModal" title="Réserver">
                                            <i class="fas fa-calendar-plus"></i>
                                        </button>
                                    </div>
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
                    
                    <!-- Voir toutes les offres -->
                    <div class="text-center mb-5">
                        <a href="#" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>Voir toutes les offres
                        </a>
                    </div>
                    
                    <!-- Section des véhicules disponibles -->
                    <h2 class="category-header">Véhicules Disponibles</h2>
                    
                    <div class="row" id="vehiculesContainer">
                        <?php 
                        if (!empty($voitures)): 
                            foreach ($voitures as $voiture): 
                                // Récupérer l'image du véhicule
                                $vehicule_image = isset($voiture['images']) && !empty($voiture['images']) ? 
                                                "../" . $voiture['images'] : 
                                                "https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg";
                                
                                // Vérifier si ce véhicule est dans les favoris du client
                                $isFavorite = in_array($voiture['id'], $favorisVoituresIds);
                        ?>
                        <div class="col-md-4 mb-4">
                            <div class="card card-vehicle">
                                <img src="<?php echo $vehicule_image; ?>" class="card-img-top vehicle-image" alt="<?php echo htmlspecialchars($voiture['marque'] . ' ' . $voiture['modele']); ?>">
                                <span class="vehicle-badge bg-success"><i class="fas fa-check-circle me-1"></i>Disponible</span>
                                <span class="vehicle-price"><?php echo number_format($voiture['prix_location'], 0, ',', ' '); ?> FCFA/jour</span>
                                
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($voiture['marque'] . ' ' . $voiture['modele']); ?></h5>
                                    <div class="vehicle-features mb-3">
                                        <div><i class="fas fa-car"></i> <?php echo htmlspecialchars($voiture['categorie']); ?></div>
                                        <div><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($voiture['annee']); ?></div>
                                        <?php if(isset($voiture['description']) && !empty($voiture['description'])): ?>
                                        <div class="text-truncate"><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars(substr($voiture['description'], 0, 30)) . (strlen($voiture['description']) > 30 ? '...' : ''); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <button class="btn icon-btn favorite-btn <?php echo $isFavorite ? 'active' : ''; ?>" 
                                                onclick="toggleFavoriteVoiture(<?php echo $voiture['id']; ?>, this)" 
                                                data-voiture-id="<?php echo $voiture['id']; ?>" 
                                                title="<?php echo $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                        <button class="btn icon-btn view-btn" onclick="prepareViewVoitureModal(<?php echo $voiture['id']; ?>)" data-bs-toggle="modal" data-bs-target="#viewVehicleModal" title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn icon-btn reserve-btn" onclick="prepareReservationVoitureModal(<?php echo $voiture['id']; ?>)" data-bs-toggle="modal" data-bs-target="#reservationVoitureModal" title="Réserver">
                                            <i class="fas fa-calendar-plus"></i>
                                        </button>
                                        <br>
                                        <br><br><br>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php 
                            endforeach; 
                        else: 
                        ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i>Aucun véhicule disponible pour le moment.
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Voir tous les véhicules -->
                    <div class="text-center mb-5">
                        <a href="recherche.php" class="btn btn-outline-primary">
                            <i class="fas fa-car me-2"></i>Voir tous les véhicules
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal détails de l'offre -->
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
                    <button type="button" class="btn btn-primary" onclick="switchToReservationOffreModal()">Réserver maintenant</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal détails du véhicule -->
    <div class="modal fade" id="viewVehicleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails du véhicule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div id="vehicleCarousel" class="carousel slide mb-3" data-bs-ride="carousel">
                                <div class="carousel-inner" id="vehicleImagesCarousel">
                                    <!-- Images carousel sera ajouté ici -->
                                    <div class="carousel-item active">
                                        <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg" class="d-block w-100 rounded" alt="Véhicule">
                                    </div>
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#vehicleCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Précédent</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#vehicleCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Suivant</span>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h3 id="viewVehicleTitle">Chargement...</h3>
                            <p id="viewVehicleDescription" class="text-muted">Chargement...</p>
                            
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Caractéristiques</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <td><i class="fas fa-car text-muted me-2"></i>Marque:</td>
                                                <td id="viewVehicleBrand" class="fw-bold">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-car-side text-muted me-2"></i>Modèle:</td>
                                                <td id="viewVehicleModel">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-calendar-alt text-muted me-2"></i>Année:</td>
                                                <td id="viewVehicleYear">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-tags text-muted me-2"></i>Catégorie:</td>
                                                <td id="viewVehicleCategory">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td><i class="fas fa-money-bill-wave text-muted me-2"></i>Prix de location:</td>
                                                <td id="viewVehiclePrice" class="fw-bold text-success">Chargement...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" onclick="switchToReservationVoitureModal()">Réserver maintenant</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal réservation offre -->
    <div class="modal fade" id="reservationOffreModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Réservation avec offre spéciale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="reservationOffreForm" action="../Controller/ClientController.php" method="POST">
                        <input type="hidden" name="action" value="reserver_offre">
                        <input type="hidden" name="offre_id" id="reservation_offre_id">
                        <input type="hidden" name="client_id" value="<?php echo $_SESSION['user_id']; ?>">
                        
                        <div class="mb-4">
                            <h4 id="reservationOfferTitle">Offre: Chargement...</h4>
                            <p class="text-muted" id="reservationOfferDescription">Chargement...</p>
                            <div class="alert alert-info">
                                <i class="fas fa-tag me-2"></i>
                                <span>Réduction appliquée: <strong id="reservationOfferDiscount">-0%</strong></span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="offre_vehicule_id" class="form-label">Sélectionner un véhicule</label>
                                <select class="form-select" id="offre_vehicule_id" name="vehicule_id" required>
                                    <option value="">Sélectionner un véhicule</option>
                                    <!-- Options chargées dynamiquement -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="offre_code_promo" class="form-label">Code promo (optionnel)</label>
                                <input type="text" class="form-control" id="offre_code_promo" name="code_promo">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="offre_date_debut" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="offre_date_debut" name="date_debut" required>
                            </div>
                            <div class="col-md-6">
                                <label for="offre_date_fin" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="offre_date_fin" name="date_fin" required>
                            </div>
                        </div>
                        
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Résumé de la réservation</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1">Véhicule: <span id="offre_resume_vehicule">Non sélectionné</span></p>
                                        <p class="mb-1">Durée: <span id="offre_resume_duree">0 jours</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">Prix normal: <span id="offre_resume_prix_normal">0 FCFA</span></p>
                                        <p class="mb-1">Réduction: <span id="offre_resume_reduction">0%</span></p>
                                        <p class="mb-1 fw-bold">Prix total: <span id="offre_resume_prix_total" class="text-danger">0 FCFA</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="offre_accept_terms" name="accept_terms" required>
                            <label class="form-check-label" for="offre_accept_terms">
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
    
    <!-- Modal réservation véhicule -->
    <div class="modal fade" id="reservationVoitureModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Réservation de véhicule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="reservationVoitureForm" action="../Controller/ClientController.php" method="POST">
                        <input type="hidden" name="action" value="reserver_voiture">
                        <input type="hidden" name="vehicule_id" id="reservation_voiture_id">
                        <input type="hidden" name="client_id" value="<?php echo $_SESSION['user_id']; ?>">
                        
                        <div class="mb-4">
                            <h4 id="reservationVehicleTitle">Véhicule: Chargement...</h4>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-success me-2">Disponible</span>
                                <span id="reservationVehiclePrice" class="fw-bold">0 FCFA / jour</span>
                            </div>
                            <p class="text-muted" id="reservationVehicleDescription">Chargement...</p>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="voiture_date_debut" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="voiture_date_debut" name="date_debut" required>
                            </div>
                            <div class="col-md-6">
                                <label for="voiture_date_fin" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="voiture_date_fin" name="date_fin" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="voiture_code_promo" class="form-label">Code promo (optionnel)</label>
                                <input type="text" class="form-control" id="voiture_code_promo" name="code_promo">
                            </div>
                        </div>
                        
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title">Résumé de la réservation</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1">Véhicule: <span id="voiture_resume_vehicule">Chargement...</span></p>
                                        <p class="mb-1">Durée: <span id="voiture_resume_duree">0 jours</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">Prix journalier: <span id="voiture_resume_prix_journalier">0 FCFA</span></p>
                                        <p class="mb-1 fw-bold">Prix total: <span id="voiture_resume_prix_total" class="text-danger">0 FCFA</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="voiture_accept_terms" name="accept_terms" required>
                            <label class="form-check-label" for="voiture_accept_terms">
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
    
    <!-- Modal détails des promotions -->
    <div class="modal fade" id="promoDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nos promotions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">30% de réduction sur les locations longue durée</h5>
                                </div>
                                <div class="card-body">
                                    <p>Bénéficiez de 30% de réduction sur toutes les locations de plus de 2 semaines.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-dark">Code: NDAAMAR30</span>
                                        <small class="text-muted">Valable jusqu'au 31/12/2023</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">15% de réduction sur les SUV</h5>
                                </div>
                                <div class="card-body">
                                    <p>Réduction spéciale sur notre gamme de SUV et 4x4 pour vos aventures.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-dark">Code: SUV15</span>
                                        <small class="text-muted">Valable jusqu'au 30/09/2023</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0">Offre weekend: 20% de réduction</h5>
                                </div>
                                <div class="card-body">
                                    <p>Profitez de 20% de réduction sur toutes les locations du vendredi au lundi.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-dark">Code: WEEKEND20</span>
                                        <small class="text-muted">Tous les weekends</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Offre fidélité</h5>
                                </div>
                                <div class="card-body">
                                    <p>Les clients ayant déjà effectué au moins 3 locations bénéficient d'une réduction permanente de 10%.</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-dark">Automatique</span>
                                        <small class="text-muted">Permanent</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
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
        // Variables globales pour les données
        let currentOffreId = null;
        let currentVoitureId = null;
        let offresData = {};
        let voituresData = {};

        // Formatage des dates et montants
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

        // Document ready
        $(document).ready(function() {
            // Initialiser les dates par défaut pour les formulaires de réservation
            const today = new Date();
            const todayISO = today.toISOString().split('T')[0];
            
            $('#offre_date_debut, #voiture_date_debut').val(todayISO);
            $('#offre_date_debut, #voiture_date_debut').attr('min', todayISO);
            
            const nextWeek = new Date();
            nextWeek.setDate(today.getDate() + 7);
            const nextWeekISO = nextWeek.toISOString().split('T')[0];
            
            $('#offre_date_fin, #voiture_date_fin').val(nextWeekISO);
            $('#offre_date_fin, #voiture_date_fin').attr('min', todayISO);

            // Événements pour les dates de réservation et les véhicules
            $('#offre_date_debut, #offre_date_fin, #offre_vehicule_id').change(updateOffreReservationSummary);
            $('#voiture_date_debut, #voiture_date_fin').change(updateVoitureReservationSummary);
            
            // Gestion du profil utilisateur
            $('#editProfileBtn').click(function() {
                $('#profileInfo').hide();
                $('#profileEditForm').show();
            });
            
            $('#cancelEditBtn').click(function() {
                $('#profileEditForm').hide();
                $('#profileInfo').show();
            });
            
            // Filtres
            $('#applyFilters').click(function() {
                const categorie = $('#categorieFilter').val();
                const prixMax = $('#prixMaxFilter').val();
                const sortBy = $('#sortFilter').val();
                
                filterVehicles(categorie, prixMax, sortBy);
            });
        });

        // Fonctions pour les favoris
        function toggleFavoriteOffre(offreId, button) {
            $(button).addClass('disabled').attr('disabled', true);
            
            $.ajax({
                url: '../Controller/ClientController.php',
                type: 'POST',
                data: {
                    action: 'toggle_favori_offre',
                    offre_id: offreId,
                    client_id: <?php echo $_SESSION['user_id']; ?>
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (response.added) {
                            $(button).addClass('active');
                            $(button).attr('title', 'Retirer des favoris');
                        } else {
                            $(button).removeClass('active');
                            $(button).attr('title', 'Ajouter aux favoris');
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
        //fonction pour les voitures favories
        function toggleFavoriteVoiture(voitureId, button) {
            $(button).addClass('disabled').attr('disabled', true);

            $.ajax({
                url: '../Controller/ClientController.php',
                type: 'POST',
                data: {
                    action: 'toggle_favori_voiture',
                    voiture_id: voitureId,
                    client_id: <?php echo $_SESSION['user_id'];?>
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (response.added) {
                            $(button).addClass('active');
                            $(button).attr('title', 'Retirer des favoris');
                        } else {
                            $(button).removeClass('active');
                            $(button).attr('title', 'Ajouter aux favoris');
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
        // Préparation des modals de détail
        function prepareViewOffreModal(offreId) {
            currentOffreId = offreId;
            
            // Charger les détails de l'offre
            $.ajax({
                url: '../Controller/ClientController.php',
                type: 'GET',
                data: {
                    action: 'get_offre_details',
                    offre_id: offreId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const offre = response.offre;
                        offresData[offreId] = offre;
                        
                        // Mise à jour du modal avec les détails de l'offre
                        $('#viewOfferTitle').text(offre.titre);
                        $('#viewOfferDiscount').text(`-${offre.reduction}%`);
                        $('#viewOfferDescription').text(offre.description || 'Aucune description disponible');
                        
                        $('#viewOfferReduction').text(`-${offre.reduction}%`);
                        $('#viewOfferPeriod').text(`${formatDate(offre.date_debut)} à ${formatDate(offre.date_fin)}`);
                        $('#viewOfferCode').text(offre.code_promo || 'Aucun code promo');
                        
                        $('#viewOfferVehiclesCount').text(offre.vehicules ? offre.vehicules.length : 0);
                        $('#viewOfferStartDate').text(formatDate(offre.date_debut));
                        $('#viewOfferEndDate').text(formatDate(offre.date_fin));
                        
                        // Afficher les véhicules liés à l'offre
                        const vehiclesContainer = $('#viewOfferVehicles');
                        vehiclesContainer.empty();
                        
                        if (offre.vehicules && offre.vehicules.length > 0) {
                            offre.vehicules.forEach(vehicule => {
                                voituresData[vehicule.id] = vehicule;
                                
                                const prixNormal = parseInt(vehicule.prix_location);
                                const reduction = parseFloat(offre.reduction);
                                const prixReduit = Math.round(prixNormal * (1 - reduction / 100));
                                
                                const row = `
                                    <tr>
                                        <td>
                                            <img src="../${vehicule.images || 'Public/images/default-car.jpg'}" alt="${vehicule.marque} ${vehicule.modele}" class="car-image">
                                        </td>
                                        <td>${vehicule.marque} ${vehicule.modele}</td>
                                        <td>${vehicule.categorie}</td>
                                        <td>${formatCurrency(prixNormal)}</td>
                                        <td class="text-danger fw-bold">${formatCurrency(prixReduit)}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="prepareReservationOffreModal(${offre.id}, ${vehicule.id})">
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
                        alert('Erreur lors du chargement des détails: ' + response.message);
                    }
                },
                error: function() {
                    alert('Erreur de communication avec le serveur');
                }
            });
        }
        
        function prepareViewVoitureModal(voitureId) {
            currentVoitureId = voitureId;
            
            // Charger les détails du véhicule
            $.ajax({
                url: '../Controller/ClientController.php',
                type: 'GET',
                data: {
                    action: 'get_voiture_details',
                    voiture_id: voitureId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const voiture = response.voiture;
                        voituresData[voitureId] = voiture;
                        
                        // Mise à jour du modal avec les détails du véhicule
                        $('#viewVehicleTitle').text(`${voiture.marque} ${voiture.modele}`);
                        $('#viewVehicleDescription').text(voiture.description || 'Aucune description disponible');
                        
                        $('#viewVehicleBrand').text(voiture.marque);
                        $('#viewVehicleModel').text(voiture.modele);
                        $('#viewVehicleYear').text(voiture.annee);
                        $('#viewVehicleCategory').text(voiture.categorie);
                        $('#viewVehiclePrice').text(formatCurrency(voiture.prix_location) + ' / jour');
                        
                        // Afficher les images du véhicule dans le carousel
                        const carouselContainer = $('#vehicleImagesCarousel');
                        carouselContainer.empty();
                        
                        if (voiture.all_images && voiture.all_images.length > 0) {
                            voiture.all_images.forEach((image, index) => {
                                const isActive = index === 0 ? 'active' : '';
                                const slide = `
                                    <div class="carousel-item ${isActive}">
                                        <img src="../${image.chemin}" class="d-block w-100 rounded" alt="${voiture.marque} ${voiture.modele}">
                                    </div>
                                `;
                                carouselContainer.append(slide);
                            });
                        } else {
                            // Image par défaut si aucune image n'est disponible
                            carouselContainer.html(`
                                <div class="carousel-item active">
                                    <img src="../Public/images/default-car.jpg" class="d-block w-100 rounded" alt="${voiture.marque} ${voiture.modele}">
                                </div>
                            `);
                        }
                    } else {
                        alert('Erreur lors du chargement des détails: ' + response.message);
                    }
                },
                error: function() {
                    alert('Erreur de communication avec le serveur');
                }
            });
        }

        // Préparation des modals de réservation
        function prepareReservationOffreModal(offreId, vehiculeId = null) {
            currentOffreId = offreId;
            $('#reservation_offre_id').val(offreId);
            
            // Si on a déjà les données de l'offre
            if (offresData[offreId]) {
                populateOffreReservationModal(offresData[offreId], vehiculeId);
            } else {
                // Sinon, charger les détails
                $.ajax({
                    url: '../Controller/ClientController.php',
                    type: 'GET',
                    data: {
                        action: 'get_offre_details',
                        offre_id: offreId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            offresData[offreId] = response.offre;
                            populateOffreReservationModal(response.offre, vehiculeId);
                        } else {
                            alert('Erreur lors du chargement des détails: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Erreur de communication avec le serveur');
                    }
                });
            }
        }
        
        function populateOffreReservationModal(offre, vehiculeId = null) {
            // Mise à jour des informations de l'offre
            $('#reservationOfferTitle').text(`Offre: ${offre.titre}`);
            $('#reservationOfferDescription').text(offre.description || 'Aucune description disponible');
            $('#reservationOfferDiscount').text(`-${offre.reduction}%`);
            
            // Pré-remplir le code promo si disponible
            if (offre.code_promo) {
                $('#offre_code_promo').val(offre.code_promo);
            }
            
            // Remplir la liste des véhicules disponibles
            const vehiculeSelect = $('#offre_vehicule_id');
            vehiculeSelect.empty();
            vehiculeSelect.append('<option value="">Sélectionner un véhicule</option>');
            
            if (offre.vehicules && offre.vehicules.length > 0) {
                offre.vehicules.forEach(vehicule => {
                    voituresData[vehicule.id] = vehicule;
                    
                    const prixNormal = parseInt(vehicule.prix_location);
                    const reduction = parseFloat(offre.reduction);
                    const prixReduit = Math.round(prixNormal * (1 - reduction / 100));
                    
                    const option = new Option(
                        `${vehicule.marque} ${vehicule.modele} - ${formatCurrency(prixReduit)} / jour (au lieu de ${formatCurrency(prixNormal)})`,
                        vehicule.id
                    );
                    
                    vehiculeSelect.append(option);
                });
                
                // Sélectionner le véhicule si fourni
                if (vehiculeId && voituresData[vehiculeId]) {
                    vehiculeSelect.val(vehiculeId);
                }
            }
            
            // Mettre à jour le résumé
            updateOffreReservationSummary();
            
            // Fermer le modal de détails s'il est ouvert
            $('#viewOfferModal').modal('hide');
            
            // Ouvrir le modal de réservation
            $('#reservationOffreModal').modal('show');
        }
        
        function prepareReservationVoitureModal(voitureId) {
            currentVoitureId = voitureId;
            $('#reservation_voiture_id').val(voitureId);
            
            // Si on a déjà les données du véhicule
            if (voituresData[voitureId]) {
                populateVoitureReservationModal(voituresData[voitureId]);
            } else {
                // Sinon, charger les détails
                $.ajax({
                    url: '../Controller/ClientController.php',
                    type: 'GET',
                    data: {
                        action: 'get_voiture_details',
                        voiture_id: voitureId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            voituresData[voitureId] = response.voiture;
                            populateVoitureReservationModal(response.voiture);
                        } else {
                            alert('Erreur lors du chargement des détails: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Erreur de communication avec le serveur');
                    }
                });
            }
        }
        
        function populateVoitureReservationModal(voiture) {
            // Mise à jour des informations du véhicule
            $('#reservationVehicleTitle').text(`Véhicule: ${voiture.marque} ${voiture.modele}`);
            $('#reservationVehicleDescription').text(voiture.description || 'Aucune description disponible');
            $('#reservationVehiclePrice').text(formatCurrency(voiture.prix_location) + ' / jour');
            
            // Mettre à jour le résumé
            $('#voiture_resume_vehicule').text(`${voiture.marque} ${voiture.modele}`);
            $('#voiture_resume_prix_journalier').text(formatCurrency(voiture.prix_location));
            
            // Mettre à jour le résumé de la réservation
            updateVoitureReservationSummary();
            
            // Fermer le modal de détails s'il est ouvert
            $('#viewVehicleModal').modal('hide');
            
            // Ouvrir le modal de réservation
            $('#reservationVoitureModal').modal('show');
        }

        // Calcul des résumés de réservation
        function updateOffreReservationSummary() {
            const vehiculeId = $('#offre_vehicule_id').val();
            const dateDebut = new Date($('#offre_date_debut').val());
            const dateFin = new Date($('#offre_date_fin').val());
            
            if (!vehiculeId || !dateDebut || !dateFin || isNaN(dateDebut.getTime()) || isNaN(dateFin.getTime())) {
                $('#offre_resume_vehicule').text('Non sélectionné');
                $('#offre_resume_duree').text('0 jours');
                $('#offre_resume_prix_normal').text('0 FCFA');
                $('#offre_resume_reduction').text('0%');
                $('#offre_resume_prix_total').text('0 FCFA');
                return;
            }
            
            // Calcul de la durée en jours
            const diffTime = Math.abs(dateFin - dateDebut);
            const duree = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (duree <= 0) {
                $('#offre_resume_duree').text('Date de fin invalide');
                return;
            }
            
            const vehicule = voituresData[vehiculeId];
            const offre = offresData[currentOffreId];
            
            if (vehicule && offre) {
                const prixNormal = parseInt(vehicule.prix_location);
                const reduction = parseFloat(offre.reduction);
                const prixReduit = Math.round(prixNormal * (1 - reduction / 100));
                
                const prixTotalNormal = prixNormal * duree;
                const prixTotalReduit = prixReduit * duree;
                
                // Mise à jour du résumé
                $('#offre_resume_vehicule').text(`${vehicule.marque} ${vehicule.modele}`);
                $('#offre_resume_duree').text(`${duree} jour${duree > 1 ? 's' : ''}`);
                $('#offre_resume_prix_normal').text(formatCurrency(prixTotalNormal));
                $('#offre_resume_reduction').text(`-${reduction}%`);
                $('#offre_resume_prix_total').text(formatCurrency(prixTotalReduit));
            }
        }
        
        function updateVoitureReservationSummary() {
            const dateDebut = new Date($('#voiture_date_debut').val());
            const dateFin = new Date($('#voiture_date_fin').val());
            
            if (!dateDebut || !dateFin || isNaN(dateDebut.getTime()) || isNaN(dateFin.getTime())) {
                $('#voiture_resume_duree').text('0 jours');
                $('#voiture_resume_prix_total').text('0 FCFA');
                return;
            }
            
            // Calcul de la durée en jours
            const diffTime = Math.abs(dateFin - dateDebut);
            const duree = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (duree <= 0) {
                $('#voiture_resume_duree').text('Date de fin invalide');
                return;
            }
            
            const vehicule = voituresData[currentVoitureId];
            
            if (vehicule) {
                const prixJournalier = parseInt(vehicule.prix_location);
                const prixTotal = prixJournalier * duree;
                
                // Mise à jour du résumé
                $('#voiture_resume_duree').text(`${duree} jour${duree > 1 ? 's' : ''}`);
                $('#voiture_resume_prix_total').text(formatCurrency(prixTotal));
            }
        }

        // Passage d'un modal à l'autre
        function switchToReservationOffreModal() {
            $('#viewOfferModal').modal('hide');
            setTimeout(() => {
                prepareReservationOffreModal(currentOffreId);
            }, 500);
        }
        
        function switchToReservationVoitureModal() {
            $('#viewVehicleModal').modal('hide');
            setTimeout(() => {
                prepareReservationVoitureModal(currentVoitureId);
            }, 500);
        }

        // Filtrer les véhicules
        function filterVehicles(categorie, prixMax, sortBy) {
            $('#vehiculesContainer').addClass('opacity-50');
            
            $.ajax({
                url: '../Controller/ClientController.php',
                type: 'GET',
                data: {
                    action: 'filter_voitures',
                    categorie: categorie,
                    prix_max: prixMax,
                    sort_by: sortBy
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Mettre à jour l'affichage des véhicules
                        updateVehiclesDisplay(response.voitures);
                    } else {
                        alert('Erreur lors du filtrage: ' + response.message);
                    }
                    $('#vehiculesContainer').removeClass('opacity-50');
                },
                error: function() {
                    alert('Erreur de communication avec le serveur');
                    $('#vehiculesContainer').removeClass('opacity-50');
                }
            });
        }
        
        function updateVehiclesDisplay(voitures) {
            const container = $('#vehiculesContainer');
            container.empty();
            
            if (voitures && voitures.length > 0) {
                voitures.forEach(voiture => {
                    voituresData[voiture.id] = voiture;
                    
                    const vehiculeImage = voiture.images ? 
                                    `../${voiture.images}` : 
                                    'https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg';
                    
                    const isFavorite = voiture.is_favorite ? 'active' : '';
                    
                    const vehiculeHtml = `
                        <div class="col-md-4 mb-4">
                            <div class="card card-vehicle">
                                <img src="${vehiculeImage}" class="card-img-top vehicle-image" alt="${voiture.marque} ${voiture.modele}">
                                <span class="vehicle-badge bg-success"><i class="fas fa-check-circle me-1"></i>Disponible</span>
                                <span class="vehicle-price">${formatCurrency(voiture.prix_location)}/jour</span>
                                
                                <div class="card-body">
                                    <h5 class="card-title">${voiture.marque} ${voiture.modele}</h5>
                                    <div class="vehicle-features mb-3">
                                        <div><i class="fas fa-car"></i> ${voiture.categorie}</div>
                                        <div><i class="fas fa-calendar"></i> ${voiture.annee}</div>
                                        ${voiture.description ? `<div class="text-truncate"><i class="fas fa-info-circle"></i> ${voiture.description.substring(0, 30)}${voiture.description.length > 30 ? '...' : ''}</div>` : ''}
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <button class="btn icon-btn favorite-btn ${isFavorite}" 
                                                onclick="toggleFavoriteVoiture(${voiture.id}, this)" 
                                                data-voiture-id="${voiture.id}" 
                                                title="${isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'}">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                        <button class="btn icon-btn view-btn" onclick="prepareViewVoitureModal(${voiture.id})" data-bs-toggle="modal" data-bs-target="#viewVehicleModal" title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn icon-btn reserve-btn" onclick="prepareReservationVoitureModal(${voiture.id})" data-bs-toggle="modal" data-bs-target="#reservationVoitureModal" title="Réserver">
                                            <i class="fas fa-calendar-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    container.append(vehiculeHtml);
                });
            } else {
                container.html(`
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle me-2"></i>Aucun véhicule ne correspond à vos critères.
                        </div>
                    </div>
                `);
            }
        }
    </script>
</body>
</html>