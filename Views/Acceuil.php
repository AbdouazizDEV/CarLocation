<?php
// Initialiser la session si nécessaire

// Vérifier si l'utilisateur est connecté (à adapter selon votre système d'authentification)
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - NDAAMAR Location de Voitures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .main-container {
            flex: 1;
            display: flex;
            flex-direction: row; /* Changé en row pour aligner correctement */
        }
        .sidebar {
            background-color: white;
            width: 250px; /* Largeur fixe pour la sidebar */
            height: calc(100vh - 78px); /* Hauteur totale moins la hauteur de la navbar */
            padding: 20px 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 78px; /* Hauteur de la navbar */
            overflow-y: auto; /* Permettre le défilement si nécessaire */
        }
        .sidebar .nav-link {
            color: #333;
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .sidebar .nav-link:hover {
            background-color: #f0f7ff;
            color: #0d47a1;
            transform: translateX(5px);
        }
        .sidebar .nav-link.active {
            background-color: #e6f2ff;
            color: #0d47a1;
            border-left: 4px solid #0d47a1;
        }
        .sidebar-logo {
            padding: 15px 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .sidebar-logo img {
            max-width: 90%;
            height: auto;
        }
        .content-area {
            flex: 1; /* Prend tout l'espace restant */
            padding: 20px 30px;
            overflow-y: auto;
        }
        .gallery-header {
            padding: 15px 25px;
            background-color: white;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .vehicle-card {
            margin-bottom: 25px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        .vehicle-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        .vehicle-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .vehicle-card:hover img {
            transform: scale(1.05);
        }
        .pagination {
            margin-top: 30px;
            justify-content: center;
        }
        .pagination .page-item.active .page-link {
            background-color: #0d47a1;
            border-color: #0d47a1;
        }
        .pagination .page-link {
            color: #0d47a1;
            border-radius: 5px;
            margin: 0 3px;
        }
        .filter-buttons {
            margin-bottom: 25px;
        }
        .filter-buttons .btn {
            margin-right: 10px;
            border-radius: 20px;
            padding: 8px 18px;
            transition: all 0.3s ease;
            font-weight: 500;
            background-color: #f0f7ff;
            color: #555;
            border: none;
        }
        .filter-buttons .btn:hover {
            background-color: #d8e8ff;
        }
        .filter-buttons .btn.active {
            background-color: #0d47a1;
            color: white;
            box-shadow: 0 4px 8px rgba(13, 71, 161, 0.3);
        }
        .search-box {
            position: relative;
        }
        .search-box input {
            border-radius: 25px;
            padding: 10px 20px;
            padding-right: 45px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
            width: 250px;
        }
        .search-box input:focus {
            box-shadow: 0 0 15px rgba(13, 71, 161, 0.1);
            border-color: #0d47a1;
            width: 300px;
        }
        .search-box button {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #555;
            transition: color 0.3s ease;
        }
        .search-box button:hover {
            color: #0d47a1;
        }
        .top-nav-icons .btn {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
            transition: all 0.3s ease;
            background-color: #f0f7ff;
            color: #555;
        }
        .top-nav-icons .btn:hover {
            background-color: #d8e8ff;
            color: #0d47a1;
            transform: translateY(-2px);
        }
        .locked-icon {
            margin-left: 5px;
            color: #6c757d;
        }
        .btn-primary {
            background-color: #0d47a1;
            border-color: #0d47a1;
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 500;
            box-shadow: 0 4px 8px rgba(13, 71, 161, 0.3);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0a3882;
            border-color: #0a3882;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(13, 71, 161, 0.4);
        }
        .gallery-content h4 {
            margin-bottom: 20px;
            color: #333;
            font-weight: 600;
        }
        .breadcrumb-item a {
            color: #0d47a1;
            text-decoration: none;
        }
        .breadcrumb-item.active {
            color: #6c757d;
        }
        /* Animation pour les cartes de véhicules */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .vehicle-card {
            animation: fadeIn 0.5s ease-out forwards;
        }
        .vehicle-card:nth-child(2) { animation-delay: 0.1s; }
        .vehicle-card:nth-child(3) { animation-delay: 0.2s; }
        .vehicle-card:nth-child(4) { animation-delay: 0.3s; }
        .vehicle-card:nth-child(5) { animation-delay: 0.4s; }
        .vehicle-card:nth-child(6) { animation-delay: 0.5s; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <div class="ms-auto d-flex align-items-center">
                <div class="search-box me-3">
                    <input type="text" class="form-control shadow-none" placeholder="Rechercher...">
                    <button type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="top-nav-icons d-flex">
                    <button class="btn">
                        <i class="fas fa-flag"></i>
                    </button>
                    <button class="btn">
                        <i class="fas fa-bell"></i>
                    </button>
                    <button class="btn">
                        <i class="fas fa-cog"></i>
                    </button>
                    <a href="login.php" class="btn btn-primary ms-3">Connexion</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Container principal avec sidebar et contenu -->
    <div class="main-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-logo">
                <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740850713/Grey_and_Black2_Car_Rental_Service_Logo_nrbxc0.png" alt="NDAAMAR" class="img-fluid">
            </div>
            <div class="nav flex-column">
                <a href="#" class="nav-link active">
                    <span><i class="fas fa-tag me-2"></i> Offres</span>
                </a>
                <a href="#" class="nav-link">
                    <span><i class="fas fa-heart me-2"></i> Favorites</span>
                    <i class="fas fa-lock locked-icon"></i>
                </a>
                <a href="#" class="nav-link">
                    <span><i class="fas fa-calendar-check me-2"></i> Réservations</span>
                    <i class="fas fa-lock locked-icon"></i>
                </a>
                <a href="#" class="nav-link">
                    <span><i class="fas fa-car me-2"></i> Locations</span>
                    <i class="fas fa-lock locked-icon"></i>
                </a>
                <a href="#" class="nav-link">
                    <span><i class="fas fa-search me-2"></i> Rechercher</span>
                    <i class="fas fa-lock locked-icon"></i>
                </a>
                <a href="#" class="nav-link">
                    <span><i class="fas fa-envelope me-2"></i> Contactez-nous</span>
                </a>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="content-area">
            <div class="gallery-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-images me-2"></i>Galerie</h5>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#"><i class="fas fa-home me-1"></i>Application</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Galerie</li>
                    </ol>
                </nav>
            </div>

            <div class="gallery-content">
                <h4>Galerie Photos</h4>
                
                <div class="filter-buttons">
                    <button class="btn btn-sm active">Tous</button>
                    <button class="btn btn-sm">4 x 4</button>
                    <button class="btn btn-sm">Bus</button>
                    <button class="btn btn-sm">Berline</button>
                    <button class="btn btn-sm">Autres</button>
                </div>

                <div class="row">
                    <!-- Véhicule 1 - SUV -->
                    <div class="col-md-4">
                        <div class="vehicle-card">
                            <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg" alt="SUV" class="card-img-top">
                        </div>
                    </div>

                    <!-- Véhicule 2 - Berline -->
                    <div class="col-md-4">
                        <div class="vehicle-card">
                            <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg" alt="Berline" class="card-img-top">
                        </div>
                    </div>

                    <!-- Véhicule 3 - Bus -->
                    <div class="col-md-4">
                        <div class="vehicle-card">
                            <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg" alt="Bus" class="card-img-top">
                        </div>
                    </div>

                    <!-- Véhicule 4 - Wagon -->
                    <div class="col-md-4">
                        <div class="vehicle-card">
                            <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg" alt="Wagon" class="card-img-top">
                        </div>
                    </div>

                    <!-- Véhicule 5 - Camping Car -->
                    <div class="col-md-4">
                        <div class="vehicle-card">
                            <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg" alt="Camping Car" class="card-img-top">
                        </div>
                    </div>

                    <!-- Véhicule 6 - Pickup -->
                    <div class="col-md-4">
                        <div class="vehicle-card">
                            <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg" alt="Pickup" class="card-img-top">
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <li class="page-item">
                            <a class="page-link" href="#" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
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
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <script>
        // Script pour gérer les filtres de véhicules
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-buttons .btn');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Retirer la classe active de tous les boutons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Ajouter la classe active au bouton cliqué
                    this.classList.add('active');
                    
                    // Ici, vous pouvez ajouter la logique pour filtrer les véhicules
                    // Par exemple, avec AJAX ou en modifiant l'affichage des cartes
                });
            });
        });
    </script>
</body>
</html>