<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un client
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'client') {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NDAAMAR - Espace Client</title>
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
        .menu-item .lock-icon {
            position: absolute;
            right: 15px;
            font-size: 14px;
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
        .gallery-filters {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 10px;
        }
        .filter-buttons {
            display: flex;
        }
        .filter-button {
            margin-right: 10px;
            padding: 5px 15px;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
        }
        .filter-button.active {
            color: #0d6efd;
            border-bottom: 2px solid #0d6efd;
        }
        .filter-dropdown {
            background: none;
            border: none;
            color: #666;
        }
        .vehicle-card {
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .vehicle-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        .page-item.active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
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
                                    <img src="../Assets/Images/us-flag.png" alt="English" width="20" height="15">
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><img src="../Assets/Images/us-flag.png" alt="English" width="20" height="15"> English</a></li>
                                    <li><a class="dropdown-item" href="#"><img src="../Assets/Images/fr-flag.png" alt="Français" width="20" height="15"> Français</a></li>
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
                                    <img src="../Assets/Images/user-avatar.png" alt="Profile" width="32" height="32" class="rounded-circle me-2">
                                    <div>
                                        <div class="fw-bold"><?php echo $_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']; ?></div>
                                        <small class="text-muted">Client</small>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="profile.php">Mon Profil</a></li>
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
                        <h1>Galerie</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Application</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Galerie</li>
                            </ol>
                        </nav>
                    </div>
                    
                    <h4 class="mb-3">Galerie Photos</h4>
                    
                    <div class="gallery-filters">
                        <div class="filter-buttons">
                            <button class="filter-button active">Tous</button>
                            <button class="filter-button">4 ×4</button>
                            <button class="filter-button">Bus</button>
                            <button class="filter-button">Berline</button>
                            <button class="filter-button filter-dropdown">
                                Autres <i class="fas fa-caret-down"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Vehicule 1 -->
                        <div class="col-md-4 mb-4">
                            <div class="card vehicle-card">
                                <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg" class="vehicle-image" alt="SUV">
                                <div class="card-body">
                                    <h5 class="card-title">Jeep Grand Cherokee</h5>
                                    <p class="card-text text-muted">SUV 4x4 | 5 places</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-primary">65.000 FCFA/jour</span>
                                        <a href="reservation.php?id=1" class="btn btn-sm btn-outline-primary">Réserver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Vehicule 2 -->
                        <div class="col-md-4 mb-4">
                            <div class="card vehicle-card">
                                <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg" class="vehicle-image" alt="Tesla">
                                <div class="card-body">
                                    <h5 class="card-title">Tesla Model 3</h5>
                                    <p class="card-text text-muted">Berline électrique | 5 places</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-primary">85.000 FCFA/jour</span>
                                        <a href="reservation.php?id=2" class="btn btn-sm btn-outline-primary">Réserver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Vehicule 3 -->
                        <div class="col-md-4 mb-4">
                            <div class="card vehicle-card">
                                <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg" class="vehicle-image" alt="Bus">
                                <div class="card-body">
                                    <h5 class="card-title">Mercedes Sprinter</h5>
                                    <p class="card-text text-muted">Bus | 18 places</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-primary">120.000 FCFA/jour</span>
                                        <a href="reservation.php?id=3" class="btn btn-sm btn-outline-primary">Réserver</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ajoutez plus de véhicules selon vos besoins -->
                    </div>
                    
                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <li class="page-item">
                                <a class="page-link" href="#" aria-label="Previous">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <li class="page-item"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item active"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">4</a></li>
                            <li class="page-item"><a class="page-link" href="#">...</a></li>
                            <li class="page-item"><a class="page-link" href="#">9</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#" aria-label="Next">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>