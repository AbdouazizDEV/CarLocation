<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un gérant
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'gérant') {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NDAAMAR - Espace Gérant</title>
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
        .stats-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            height: 100%;
        }
        .stats-card h5 {
            color: #666;
            margin-bottom: 15px;
        }
        .stats-card .value {
            font-size: 24px;
            font-weight: bold;
        }
        .stats-card .trend {
            color: #28a745;
            font-size: 14px;
        }
        .stats-card .trend.negative {
            color: #dc3545;
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
        .action-buttons .btn {
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
                    <a href="AcceuilGerant.php" class="menu-item active">
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
                    <a href="rapports.php" class="menu-item">
                        <i class="fas fa-chart-bar"></i> Rapports
                    </a>
                    <a href="parametres_systeme.php" class="menu-item">
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
                                        <small class="text-muted">Gérant</small>
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
                        <h1>Tableau de bord</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Application</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Tableau de bord</li>
                            </ol>
                        </nav>
                    </div>
                    
                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stats-card">
                                <h5>Réservations actives</h5>
                                <div class="value">24</div>
                                <div class="trend"><i class="fas fa-arrow-up"></i> +12% cette semaine</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <h5>Véhicules disponibles</h5>
                                <div class="value">18</div>
                                <div class="trend negative"><i class="fas fa-arrow-down"></i> -3% cette semaine</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <h5>Revenus mensuels</h5>
                                <div class="value">5.2M FCFA</div>
                                <div class="trend"><i class="fas fa-arrow-up"></i> +8% ce mois</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <h5>Nouveaux clients</h5>
                                <div class="value">32</div>
                                <div class="trend"><i class="fas fa-arrow-up"></i> +15% ce mois</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Bookings -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Réservations récentes</h4>
                            <a href="gestion_reservations.php" class="btn btn-sm btn-primary">Voir tout</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Client</th>
                                            <th>Véhicule</th>
                                            <th>Date de début</th>
                                            <th>Date de fin</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>#1258</td>
                                            <td>Amadou Diallo</td>
                                            <td>Tesla Model 3</td>
                                            <td>05/03/2025</td>
                                            <td>08/03/2025</td>
                                            <td><span class="badge bg-success">Confirmée</span></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#1257</td>
                                            <td>Fatou Sow</td>
                                            <td>Toyota Hilux</td>
                                            <td>04/03/2025</td>
                                            <td>10/03/2025</td>
                                            <td><span class="badge bg-warning text-dark">En attente</span></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#1256</td>
                                            <td>Cheikh Diop</td>
                                            <td>Mercedes Sprinter</td>
                                            <td>03/03/2025</td>
                                            <td>05/03/2025</td>
                                            <td><span class="badge bg-success">Confirmée</span></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#1255</td>
                                            <td>Mariama Bâ</td>
                                            <td>Peugeot 308</td>
                                            <td>02/03/2025</td>
                                            <td>09/03/2025</td>
                                            <td><span class="badge bg-danger">Annulée</span></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vehicles Status -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0">Statut des véhicules</h4>
                                    <a href="gestion_vehicules.php" class="btn btn