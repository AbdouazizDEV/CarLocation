<?php
    session_start();
    require_once __DIR__ . "/../Models/voiture.php";

    // Vérifier si l'utilisateur est connecté et est un gérant
    if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'gérant') {
        header('Location: login.php');
        exit();
    }

    // Instancier le modèle voiture pour récupérer la liste des voitures
    $voitureModel = new Voiture();
    // Récupérer toutes les voitures
    $voitures = $voitureModel->getAllVoitures();

    // Filtrer les voitures par statut si demandé
    $statut_filter = $_GET['statut'] ?? null;
    if ($statut_filter) {
        $voitures = array_filter($voitures, function($voiture) use ($statut_filter) {
            return $voiture['statut'] === $statut_filter;
        });
    }
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestion des véhicules - NDAAMAR</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
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
            .car-image {
                width: 80px;
                height: 60px;
                object-fit: cover;
                border-radius: 4px;
            }
            .action-buttons .btn {
                margin-right: 5px;
            }
            .status-badge {
                font-size: 0.8rem;
                padding: 0.25rem 0.5rem;
            }
            /* Style pour le modal d'ajout de voiture */
            .modal-dialog.modal-xl {
                max-width: 1140px;
            }
            .form-group {
                margin-bottom: 1rem;
            }
            .custom-file-upload {
                border: 1px solid #ccc;
                display: inline-block;
                padding: 6px 12px;
                cursor: pointer;
                border-radius: 4px;
            }
            .img-preview {
                width: 100px;
                height: 75px;
                object-fit: cover;
                margin: 5px;
                border-radius: 4px;
            }
            .image-item {
                position: relative;
                margin: 5px;
                border: 1px solid #ddd;
                border-radius: 4px;
                overflow: hidden;
            }
            
            .image-item img {
                width: 150px;
                height: 100px;
                object-fit: cover;
            }
            
            .image-actions {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                background-color: rgba(0, 0, 0, 0.7);
                padding: 5px;
                display: flex;
                justify-content: space-between;
            }
            
            .image-actions button {
                font-size: 0.7rem;
                padding: 2px 5px;
            }
            
            .main-image-badge {
                position: absolute;
                top: 5px;
                right: 5px;
                background-color: rgba(0, 123, 255, 0.8);
                color: white;
                font-size: 0.7rem;
                padding: 2px 6px;
                border-radius: 10px;
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
                        <a href="AcceuilGerant.php" class="menu-item">
                            <i class="fas fa-tachometer-alt"></i> Tableau de bord
                        </a>
                        <a href="gestion_vehicules.php" class="menu-item active">
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
                        <a href="rapports.php" class="menu-item">
                            <i class="fas fa-chart-bar"></i> Rapports
                        </a>
                        <a href="parametres.php" class="menu-item">
                            <i class="fas fa-cogs"></i> Paramètres
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
                            <h1>Gestion des véhicules</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="AcceuilGerant.php">Application</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Gestion des véhicules</li>
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
                        
                        <div class="card mb-4">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Liste des véhicules</h5>
                                <div>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                                        <i class="fas fa-plus me-2"></i>Ajouter un véhicule
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="btn-group" role="group">
                                        <a href="gestion_vehicules.php" class="btn <?php echo !$statut_filter ? 'btn-primary' : 'btn-outline-primary'; ?>">Tous</a>
                                        <a href="gestion_vehicules.php?statut=disponible" class="btn <?php echo $statut_filter === 'disponible' ? 'btn-primary' : 'btn-outline-primary'; ?>">Disponibles</a>
                                        <a href="gestion_vehicules.php?statut=loué" class="btn <?php echo $statut_filter === 'loué' ? 'btn-primary' : 'btn-outline-primary'; ?>">Loués</a>
                                        <a href="gestion_vehicules.php?statut=maintenance" class="btn <?php echo $statut_filter === 'maintenance' ? 'btn-primary' : 'btn-outline-primary'; ?>">En maintenance</a>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table id="vehiclesTable" class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Image</th>
                                                <th>Marque</th>
                                                <th>Modèle</th>
                                                <th>Catégorie</th>
                                                <th>Année</th>
                                                <th>Prix/jour</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($voitures)): ?>
                                                <?php foreach ($voitures as $voiture): ?>
                                                    <tr>
                                                        <td><?php echo $voiture['id']; ?></td>
                                                        <td>
                                                            <?php if (!empty($voiture['images'])): ?>
                                                                <img src="<?php echo "../".$voiture['images']; ?>" alt="<?php echo $voiture['marque'] . ' ' . $voiture['modele']; ?>" class="car-image">
                                                            <?php else: ?>
                                                                <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg" alt="Pas d'image" class="car-image">
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?php echo $voiture['marque']; ?></td>
                                                        <td><?php echo $voiture['modele']; ?></td>
                                                        <td><?php echo $voiture['categorie']; ?></td>
                                                        <td><?php echo $voiture['annee']; ?></td>
                                                        <td><?php echo number_format($voiture['prix_location'], 0, ',', ' ') . ' FCFA'; ?></td>
                                                        <td>
                                                            <?php
                                                            $statusClass = 'bg-success';
                                                            $statusText = 'Disponible';
                                                            
                                                            if (isset($voiture['statut'])) {
                                                                switch ($voiture['statut']) {
                                                                    case 'loué':
                                                                        $statusClass = 'bg-primary';
                                                                        $statusText = 'Loué';
                                                                        break;
                                                                    case 'maintenance':
                                                                        $statusClass = 'bg-warning text-dark';
                                                                        $statusText = 'En maintenance';
                                                                        break;
                                                                    case 'indisponible':
                                                                        $statusClass = 'bg-danger';
                                                                        $statusText = 'Indisponible';
                                                                        break;
                                                                }
                                                            }
                                                            ?>
                                                            <span class="badge <?php echo $statusClass; ?> status-badge"><?php echo $statusText; ?></span>
                                                        </td>
                                                        <td class="action-buttons">
                                                            <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#viewVehicleModal" data-id="<?php echo $voiture['id']; ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-warning text-white" data-bs-toggle="modal" data-bs-target="#editVehicleModal" data-id="<?php echo $voiture['id']; ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteVehicleModal" data-id="<?php echo $voiture['id']; ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="9" class="text-center">Aucun véhicule trouvé</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Ajout de véhicule -->
        <div class="modal fade" id="addVehicleModal" tabindex="-1" aria-labelledby="addVehicleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addVehicleModalLabel">Ajouter une voiture</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>  
                    <div class="modal-body">
                        <form method="POST" action="../Controller/recupvoiture.php" enctype="multipart/form-data" id="addVehicleForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="marque" class="form-label">Marque</label>
                                        <select class="form-select" name="marque" id="marque" required>
                                            <option value="">Sélectionnez une marque</option>
                                            <option value="Peugeot">Peugeot</option>
                                            <option value="Audi">Audi</option>
                                            <option value="BMW">BMW</option>
                                            <option value="Tesla">Tesla</option>
                                            <option value="Ferrari">Ferrari</option>
                                            <option value="Mercedes">Mercedes</option>
                                            <option value="Toyota">Toyota</option>
                                            <option value="Honda">Honda</option>
                                            <option value="Nissan">Nissan</option>
                                            <option value="Subaru">Subaru</option>
                                            <option value="Mitsubishi">Mitsubishi</option>
                                            <option value="Mazda">Mazda</option>
                                            <option value="Suzuki">Suzuki</option>
                                            <option value="Volkswagen">Volkswagen</option>
                                            <option value="Ford">Ford</option>
                                            <option value="Jaguar">Jaguar</option>
                                            <option value="Kia">Kia</option>
                                            <option value="Dodge">Dodge</option>
                                            <option value="Chevrolet">Chevrolet</option>
                                            <option value="Lexus">Lexus</option>
                                            <option value="Volvo">Volvo</option>
                                            <option value="Hyundai">Hyundai</option>
                                            <option value="Infiniti">Infiniti</option>
                                            <option value="Scion">Scion</option>
                                            <option value="Chrysler">Chrysler</option>
                                            <option value="Buick">Buick</option>
                                            <option value="Lamborghini">Lamborghini</option>
                                            <option value="Land Rover"> Land Rover</option>

                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="modele" class="form-label">Modèle</label>
                                        <select class="form-select" name="modele" id="modele" required>
                                            <option value="">Sélectionnez d'abord une marque</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="annee" class="form-label">Année</label>
                                        <input type="date" class="form-control" name="annee" id="annee" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="prix_location" class="form-label">Prix de location (FCFA/jour)</label>
                                        <input type="number" class="form-control" name="prix_location" id="prix_location" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="code" class="form-label">Code</label>
                                        <input type="text" class="form-control" name="code" id="code" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="categorie" class="form-label">Catégorie</label>
                                        <select class="form-select" name="categorie" id="categorie" required>
                                            <option value="">Sélectionnez une catégorie</option>
                                            <option value="categorie A">Catégorie A</option>
                                            <option value="categorie B">Catégorie B</option>
                                            <option value="categorie C">Catégorie C</option>
                                            <option value="categorie E">Catégorie E</option>
                                            <option value="categorie F">Catégorie F</option>
                                            <option value="categorie S">Catégorie S</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="statut" class="form-label">Statut</label>
                                        <select class="form-select" name="statut" id="statut" required>
                                            <option value="disponible">Disponible</option>
                                            <option value="maintenance">En maintenance</option>
                                            <option value="indisponible">Indisponible</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="vehicleImages" class="form-label">Images</label>
                                <input type="file" class="form-control" name="images[]" id="vehicleImages" multiple accept="image/*">
                                <div id="imagePreviewContainer" class="mt-2 d-flex flex-wrap"></div>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary" name="valider" value="ajouter">Ajouter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Voir les détails du véhicule -->
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
                                <div id="vehicleCarousel" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-indicators" id="carouselIndicators">
                                        <!-- Les indicateurs seront ajoutés dynamiquement -->
                                    </div>
                                    <div class="carousel-inner" id="vehicleImageContainer">
                                        <!-- Les images seront ajoutées dynamiquement -->
                                        <div class="carousel-item active">
                                            <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg" class="d-block w-100" alt="Véhicule">
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
                                <h4 id="vehicleTitle">Chargement...</h4>
                                <p id="vehicleCategory" class="badge bg-info text-white mb-3">Catégorie</p>
                                
                                <div class="mb-3">
                                    <h5>Informations techniques</h5>
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <td>Marque:</td>
                                                <td id="vehicleBrand">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td>Modèle:</td>
                                                <td id="vehicleModel">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td>Année:</td>
                                                <td id="vehicleYear">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td>Code:</td>
                                                <td id="vehicleCode">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td>Prix de location:</td>
                                                <td id="vehiclePrice">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td>Statut:</td>
                                                <td id="vehicleStatus">Chargement...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div>
                                    <h5>Description</h5>
                                    <p id="vehicleDescription">Chargement...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="button" class="btn btn-primary" id="editVehicleBtn">Modifier</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Modifier le véhicule -->
        <div class="modal fade" id="editVehicleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier le véhicule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="../Controller/UpdateVoiture.php" enctype="multipart/form-data" id="editVehicleForm">
                            <input type="hidden" name="id" id="edit_id">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="edit_marque" class="form-label">Marque</label>
                                        <select class="form-select" name="marque" id="edit_marque" required>
                                            <option value="">Sélectionnez une marque</option>
                                            <option value="Peugeot">Peugeot</option>
                                            <option value="Audi">Audi</option>
                                            <option value="BMW">BMW</option>
                                            <option value="Tesla">Tesla</option>
                                            <option value="Ferrari">Ferrari</option>
                                            <option value="Mercedes">Mercedes</option>
                                            <option value="Ford">Ford</option>
                                            <option value="Toyota">Toyota</option>
                                            <option value="Honda">Honda</option>
                                            <option value="Nissan">Nissan</option>
                                            <option value="Subaru">Subaru</option>
                                            <option value="Mitsubishi">Mitsubishi</option>
                                            <option value="Mazda">Mazda</option>
                                            <option value="Suzuki">Suzuki</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="edit_modele" class="form-label">Modèle</label>
                                        <select class="form-select" name="modele" id="edit_modele">
                                            <option value="">Sélectionnez d'abord une marque</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="edit_annee" class="form-label">Année</label>
                                        <input type="date" class="form-control" name="annee" id="edit_annee" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="edit_prix_location" class="form-label">Prix de location (FCFA/jour)</label>
                                        <input type="number" class="form-control" name="prix_location" id="edit_prix_location" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="edit_code" class="form-label">Code</label>
                                        <input type="text" class="form-control" name="code" id="edit_code" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                            <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="edit_categorie" class="form-label">Catégorie</label>
                                        <select class="form-select" name="categorie" id="edit_categorie" required>
                                            <option value="">Sélectionnez une catégorie</option>
                                            <option value="categorie A">Catégorie A</option>
                                            <option value="categorie B">Catégorie B</option>
                                            <option value="categorie C">Catégorie C</option>
                                            <option value="categorie E">Catégorie E</option>
                                            <option value="categorie F">Catégorie F</option>
                                            <option value="categorie S">Catégorie S</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="edit_statut" class="form-label">Statut</label>
                                        <select class="form-select" name="statut" id="edit_statut" required>
                                            <option value="disponible">Disponible</option>
                                            <option value="loué">Loué</option>
                                            <option value="maintenance">En maintenance</option>
                                            <option value="indisponible">Indisponible</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="edit_description" class="form-label">Description</label>
                                <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="form-label">Images actuelles</label>
                                <div id="current_images" class="mb-3">
                                    <div class="row" id="vehicle_images_container">
                                        <!-- Les images actuelles seront ajoutées dynamiquement ici -->
                                    </div>
                                </div>
                                
                                <label for="edit_vehicleImages" class="form-label">Ajouter de nouvelles images</label>
                                <input type="file" class="form-control" name="images[]" id="edit_vehicleImages" multiple accept="image/*">
                                <div id="edit_imagePreviewContainer" class="mt-2 d-flex flex-wrap"></div>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Supprimer le véhicule -->
        <div class="modal fade" id="deleteVehicleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmer la suppression</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir supprimer ce véhicule ? Cette action est irréversible.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <form method="POST" action="../Controller/DeleteVoiture.php">
                            <input type="hidden" name="id" id="delete_id">
                            <button type="submit" class="btn btn-danger">Supprimer</button>
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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
        <script>
            // Fonction pour charger les détails du véhicule dans la modal de visualisation
            function loadVehicleDetails(vehicleId) {
                $.ajax({
                    url: '../Controller/GetVehicleDetails.php',
                    type: 'GET',
                    data: { id: vehicleId },
                    dataType: 'json',
                    success: function(vehicle) {
                        // Mise à jour des informations du véhicule
                        $('#vehicleTitle').text(vehicle.marque + ' ' + vehicle.modele);
                        $('#vehicleCategory').text(vehicle.categorie);
                        $('#vehicleBrand').text(vehicle.marque);
                        $('#vehicleModel').text(vehicle.modele);
                        $('#vehicleYear').text(vehicle.annee);
                        $('#vehicleCode').text(vehicle.code);
                        $('#vehiclePrice').text(number_format(vehicle.prix_location, 0, ',', ' ') + ' FCFA/jour');
                        
                        // Définir la classe du badge de statut
                        let statusClass = '';
                        let statusText = '';
                        
                        switch(vehicle.statut) {
                            case 'disponible':
                                statusClass = 'bg-success';
                                statusText = 'Disponible';
                                break;
                            case 'loué':
                                statusClass = 'bg-primary';
                                statusText = 'Loué';
                                break;
                            case 'maintenance':
                                statusClass = 'bg-warning text-dark';
                                statusText = 'En maintenance';
                                break;
                            default:
                                statusClass = 'bg-danger';
                                statusText = 'Indisponible';
                        }
                        
                        $('#vehicleStatus').html(`<span class="badge ${statusClass}">${statusText}</span>`);
                        $('#vehicleDescription').text(vehicle.description || 'Aucune description disponible');
                        
                        // Charger les images du véhicule dans le carousel
                        const carouselInner = $('#vehicleImageContainer');
                        const indicators = $('#carouselIndicators');
                        
                        carouselInner.empty();
                        indicators.empty();
                        
                        if (vehicle.all_images && vehicle.all_images.length > 0) {
                            // Ajouter chaque image au carousel
                            vehicle.all_images.forEach((image, index) => {
                                const isActive = index === 0 ? 'active' : '';
                                
                                // Ajouter l'indicateur
                                indicators.append(`<button type="button" data-bs-target="#vehicleCarousel" data-bs-slide-to="${index}" class="${isActive}" aria-current="${isActive ? 'true' : 'false'}" aria-label="Slide ${index + 1}"></button>`);
                                
                                // Ajouter l'image
                                carouselInner.append(`
                                    <div class="carousel-item ${isActive}">
                                        <img src="../${image.chemin}" class="d-block w-100" alt="${vehicle.marque} ${vehicle.modele}">
                                    </div>
                                `);
                            });
                        } else if (vehicle.images) {
                            // Fallback sur l'image principale si aucune image n'est trouvée dans all_images
                            carouselInner.append(`
                                <div class="carousel-item active">
                                    <img src="../${vehicle.images}" class="d-block w-100" alt="${vehicle.marque} ${vehicle.modele}">
                                </div>
                            `);
                            indicators.append(`<button type="button" data-bs-target="#vehicleCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>`);
                        } else {
                            // Image par défaut si aucune image n'est disponible
                            carouselInner.append(`
                                <div class="carousel-item active">
                                    <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg" class="d-block w-100" alt="Pas d'image">
                                </div>
                            `);
                            indicators.append(`<button type="button" data-bs-target="#vehicleCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>`);
                        }
                        
                        // Configurer le bouton d'édition pour ouvrir la modal d'édition
                        $('#editVehicleBtn').off('click').on('click', function() {
                            $('#viewVehicleModal').modal('hide');
                            loadVehicleForEdit(vehicleId);
                            $('#editVehicleModal').modal('show');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Erreur lors de la récupération des détails du véhicule:', error);
                        alert('Erreur lors de la récupération des détails du véhicule.');
                    }
                });
            }
            
            // Fonction pour charger les détails du véhicule dans le formulaire d'édition
            function loadVehicleForEdit(vehicleId) {
                // Définir l'ID du véhicule dans le formulaire
                $('#edit_id').val(vehicleId);
                
                $.ajax({
                    url: '../Controller/GetVehicleDetails.php',
                    type: 'GET',
                    data: { id: vehicleId },
                    dataType: 'json',
                    success: function(vehicle) {
                        // Remplir le formulaire avec les données du véhicule
                        $('#edit_marque').val(vehicle.marque);
                        $('#edit_marque').trigger('change'); // Pour déclencher la mise à jour des modèles
                        
                        // Attendre que les options de modèle soient mises à jour
                        setTimeout(() => {
                            $('#edit_modele').val(vehicle.modele);
                        }, 100);
                        
                        // Autres champs du formulaire
                        $('#edit_annee').val(vehicle.annee);
                        $('#edit_prix_location').val(vehicle.prix_location);
                        $('#edit_code').val(vehicle.code);
                        $('#edit_categorie').val(vehicle.categorie);
                        
                        // Convertir le statut texte en valeur pour le select
                        $('#edit_statut').val(vehicle.statut);
                        
                        $('#edit_description').val(vehicle.description);
                        
                        // Afficher les images actuelles du véhicule
                        const imagesContainer = $('#vehicle_images_container');
                        imagesContainer.empty();
                        
                        if (vehicle.all_images && vehicle.all_images.length > 0) {
                            vehicle.all_images.forEach(image => {
                                // Créer l'élément d'image avec les boutons d'action
                                const imageElement = `
                                    <div class="col-md-3 col-sm-4 col-6 mb-3">
                                        <div class="image-item">
                                            <img src="../${image.chemin}" alt="${vehicle.marque} ${vehicle.modele}">
                                            ${parseInt(image.est_principale) === 1 ? '<span class="main-image-badge">Principale</span>' : ''}
                                            <div class="image-actions">
                                                ${parseInt(image.est_principale) !== 1 ? 
                                                `<button type="button" class="btn btn-sm btn-primary set-main-image" data-image-id="${image.id}">
                                                    <i class="fas fa-star"></i>
                                                </button>` : ''}
                                                <button type="button" class="btn btn-sm btn-danger delete-image" data-image-id="${image.id}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                
                                imagesContainer.append(imageElement);
                            });
                            
                            // Ajouter des gestionnaires d'événements pour les boutons d'action d'image
                            $('.set-main-image').on('click', function() {
                                const imageId = $(this).data('image-id');
                                // Rediriger vers le contrôleur pour définir l'image principale
                                window.location.href = `../Controller/recupvoiture.php?action=set_main_image&id=${vehicleId}&image_id=${imageId}`;
                            });
                            
                            $('.delete-image').on('click', function() {
                                if (confirm('Êtes-vous sûr de vouloir supprimer cette image ?')) {
                                    const imageId = $(this).data('image-id');
                                    // Rediriger vers le contrôleur pour supprimer l'image
                                    window.location.href = `../Controller/recupvoiture.php?action=delete_image&id=${vehicleId}&image_id=${imageId}`;
                                }
                            });
                        } else {
                            imagesContainer.append('<div class="col-12"><p class="text-muted">Aucune image disponible</p></div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erreur lors de la récupération des détails du véhicule:', error);
                        alert('Erreur lors de la récupération des détails du véhicule.');
                    }
                });
            }
            // Pour le formulaire d'édition de profil
            document.addEventListener('DOMContentLoaded', function() {
                // Initialisation de DataTables
                $('#vehiclesTable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
                    }
                });
                
                // Gestionnaire d'événements pour l'ouverture de la modal de visualisation
                $('#viewVehicleModal').on('show.bs.modal', function(event) {
                    const button = $(event.relatedTarget);
                    const vehicleId = button.data('id');
                    loadVehicleDetails(vehicleId);
                });
                
                // Gestionnaire d'événements pour l'ouverture de la modal d'édition directement
                $('#editVehicleModal').on('show.bs.modal', function(event) {
                    const button = $(event.relatedTarget);
                    if (button.length) {  // Si le modal est ouvert via un bouton (pas depuis la modal de visualisation)
                        const vehicleId = button.data('id');
                        loadVehicleForEdit(vehicleId);
                    }
                });
                
                // Modal delete vehicle - set vehicle ID for form submission
                $('#deleteVehicleModal').on('show.bs.modal', function (event) {
                    const button = $(event.relatedTarget);
                    const vehicleId = button.data('id');
                    $('#delete_id').val(vehicleId);
                });
                
                // Formulaire d'édition de profil
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
                
                // Prévisualisation des images à l'ajout
                const vehicleImages = document.getElementById('vehicleImages');
                const imagePreviewContainer = document.getElementById('imagePreviewContainer');
                
                if(vehicleImages && imagePreviewContainer) {
                    vehicleImages.addEventListener('change', function() {
                        imagePreviewContainer.innerHTML = '';
                        
                        for (let i = 0; i < this.files.length; i++) {
                            const file = this.files[i];
                            if (file.type.match('image.*')) {
                                const reader = new FileReader();
                                
                                reader.onload = function(e) {
                                    const imgElement = document.createElement('img');
                                    imgElement.src = e.target.result;
                                    imgElement.classList.add('img-preview');
                                    imagePreviewContainer.appendChild(imgElement);
                                };
                                
                                reader.readAsDataURL(file);
                            }
                        }
                    });
                }
                
                // Prévisualisation des images à la modification
                const editVehicleImages = document.getElementById('edit_vehicleImages');
                const editImagePreviewContainer = document.getElementById('edit_imagePreviewContainer');
                
                if(editVehicleImages && editImagePreviewContainer) {
                    editVehicleImages.addEventListener('change', function() {
                        editImagePreviewContainer.innerHTML = '';
                        
                        for (let i = 0; i < this.files.length; i++) {
                            const file = this.files[i];
                            if (file.type.match('image.*')) {
                                const reader = new FileReader();
                                
                                reader.onload = function(e) {
                                    const imgElement = document.createElement('img');
                                    imgElement.src = e.target.result;
                                    imgElement.classList.add('img-preview');
                                    editImagePreviewContainer.appendChild(imgElement);
                                };
                                
                                reader.readAsDataURL(file);
                            }
                        }
                    });
                }
                
                // Dynamically populate models based on selected brand
                const brandSelect = document.getElementById('marque');
                const modelSelect = document.getElementById('modele');
                const editBrandSelect = document.getElementById('edit_marque');
                const editModelSelect = document.getElementById('edit_modele');
                
                const modelsByBrand = {
                    'Peugeot': ['peugeot-2008', 'peugeot-208', 'peugeot-3008', 'peugeot-301', 'peugeot-308'],
                    'Audi': ['Audi A3', 'Audi Q3', 'Audi A4', 'Audi TT', 'Audi A5 Sportback'],
                    'BMW': ['Mini', 'Rolls-Royce', 'BMW iX1', 'Bmw Ix2', 'Bmw I7'],
                    'Tesla': ['Tesla Model S', 'Tesla Model 3', 'Tesla Model X', 'Tesla Model Y', 'Tesla Cybertruck'],
                    'Ferrari': ['Ferrari 12cilindri', 'Ferrari 296', 'Ferrari 488', 'Ferrari Daytona Sp3', 'Ferrari 812 Gts'],
                    'Mercedes': ['EQE Berline', 'Classe A Berline', 'Classe E Berline', 'Classe S', 'Classe S Limousine'],
                    'Toyota': ['Toyota Corolla', 'Toyota Camry', 'Toyota Rav4', 'Toyota Prius', 'Toyota Yaris'],
                    'Honda': ['Honda Civic', 'Honda Accord', 'Honda CR-V', 'Honda Fit', 'Honda Civic Type R'],
                    'Nissan': ['Nissan Qashqai', 'Nissan Leaf', 'Nissan GTR', 'Nissan GT-R', 'Nissan Silvia'],
                    'Subaru': ['Subaru Impreza', 'Subaru Outback', 'Subaru Forester', 'Subaru WRX', 'Subaru Legacy'],
                    'Mitsubishi': ['Mitsubishi Mirage', 'Mitsubishi Outlander', 'Mitsubishi Pajero', 'Mitsubishi Lancer', 'Mitsubishi Lancer Evolution'],
                    'Mazda': ['Mazda 3', 'Mazda 6', 'Mazda 6 GTS', 'Mazda 5', 'Mazda CX-5'],
                    'Suzuki': ['Suzuki Swift', 'Suzuki Jimny', 'Suzuki Vitara', 'Suzuki Celerio', 'Suzuki Ertiga'],
                    'Ford': ['Ford Focus', 'Ford Mustang', 'Ford F-150', 'Ford Mustang Mach-E', 'Ford Ranger'],
                    'Chevrolet': ['Chevrolet Cruze', 'Chevrolet Tahoe', 'Chevrolet Corvette', 'Chevrolet Camaro', 'Chevrolet Malibu'],
                    'Volkswagen': ['Volkswagen Golf', 'Volkswagen Passat', 'Volkswagen Tiguan', 'Volkswagen Polo', 'Volkswagen Jetta'],
                    'Lamborghini': ['Lamborghini Huracan', 'Lamborghini Urus', 'Lamborghini Aventador', 'Lamborghini Veneno', 'Lamborghini Aventador S'],
                    'Porsche': ['Porsche 911', 'Porsche 928', 'Porsche 944', 'Porsche 968', 'Porsche 918 Spyder'],
                    'Jaguar': ['Jaguar E-Type', 'Jaguar XF', 'Jaguar F-Type', 'Jaguar XJ', 'Jaguar I-Pace'],
                    'Bentley': ['Bentley Continental GT', 'Bentley Mulsanne', 'Bentley Continental GT C', 'Bentley Continental Flying Spur', 'Bentley Continental GT R'],
                    'Dodge': ['Dodge Charger', 'Dodge Ram', 'Dodge Challenger', 'Dodge Durango', 'Dodge Nitro'],
                    'Fiat': ['Fiat Punto', 'Fiat 500', 'Fiat 126p', 'Fiat 1300', 'Fiat 147'],
                    'Renault': ['Renault Megane', 'Renault Kangoo', 'Renault Duster', 'Renault Symbol', 'Renault Trafic'],
                    'Citroën': ['Citroën C3', 'Citroën C4', 'Citroën C5', 'Citroën C6', 'Citroën C8'],
                    'Hyundai': ['Hyundai Accent', 'Hyundai Sonata', 'Hyundai Tucson', 'Hyundai Elantra', 'Hyundai Kona'],
                    'Volvo': ['Volvo XC90', 'Volvo S60', 'Volvo C70', 'Volvo V70', 'Volvo XC70'],
                    'Infiniti': ['Infiniti Q50', 'Infiniti Q70', 'Infiniti QX50', 'Infiniti G35', 'Infiniti G37'],
                    'Kia': ['Kia Optima', 'Kia Soul', 'Kia Rio', 'Kia Forte', 'Kia Sportage'],
                    'Land Rover': ['Land Rover Range Rover Sport', 'Land Rover Range Rover Evoque', 'Land Rover Discovery Sport', 'Land Rover Range Rover', 'Land Rover Fusion'],
                };
                
                function populateModelSelect(brandSelect, modelSelect) {
                    if (!brandSelect || !modelSelect) return;
                    
                    brandSelect.addEventListener('change', function() {
                        const selectedBrand = this.value;
                        
                        // Clear current options
                        modelSelect.innerHTML = '<option value="">Sélectionnez un modèle</option>';
                        
                        // Add new options based on selected brand
                        if (selectedBrand && modelsByBrand[selectedBrand]) {
                            modelsByBrand[selectedBrand].forEach(model => {
                                const option = document.createElement('option');
                                option.value = model;
                                option.textContent = model;
                                modelSelect.appendChild(option);
                            });
                        }
                    });
                }
                
                populateModelSelect(brandSelect, modelSelect);
                populateModelSelect(editBrandSelect, editModelSelect);
            });
            
            // Fonction utilitaire pour formater les nombres
            function number_format(number, decimals, dec_point, thousands_sep) {
                number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
                const n = !isFinite(+number) ? 0 : +number;
                const prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
                const sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep;
                const dec = (typeof dec_point === 'undefined') ? '.' : dec_point;
                
                let s = '';
                
                const toFixedFix = function (n, prec) {
                    const k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
                
                s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
                if (s[0].length > 3) {
                    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
                }
                if ((s[1] || '').length < prec) {
                    s[1] = s[1] || '';
                    s[1] += new Array(prec - s[1].length + 1).join('0');
                }
                
                return s.join(dec);
            }
        </script>
    </body>
    </html>