<?php
    session_start();

    // Vérifier si l'utilisateur est connecté et est un gérant
    if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'gérant') {
        header('Location: login.php');
        exit();
    }

    // Inclure les classes nécessaires
    require_once __DIR__ . "/../Models/Reservation.php";
    require_once __DIR__ . "/../Models/voiture.php";
    require_once __DIR__ . "/../Models/client.php";

    // Instancier les objets
    $reservationModel = new Reservation();
    $voitureModel = new Voiture();
    $clientModel = new Client();

    // Récupérer les statistiques
    $reservationStats = $reservationModel->getStats();
    $clientStats = $clientModel->getClientStats();
    // Récupérer les réservations récentes
    $recentReservations = $reservationModel->getAllReservations();
    $recentReservations = array_slice($recentReservations, 0, 4); // Limiter à 4 réservations
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
                    <a href="Offres.php" class="menu-item">
                        <i class="fas fa-chart-bar"></i> Offres
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
    });
</script>
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
                                <div class="value">
                                    <?php 
                                    $activeReservations = 0;
                                    foreach ($reservationStats['by_status'] as $status) {
                                        if ($status['statut'] === 'en_attente' || $status['statut'] === 'confirmee' || $status['statut'] === 'en_cours') {
                                            $activeReservations += $status['count'];
                                        }
                                    }
                                    echo $activeReservations;
                                    ?>
                                </div>
                                <div class="trend"><i class="fas fa-arrow-up"></i> +12% cette semaine</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <h5>Véhicules disponibles</h5>
                                <div class="value">
                                    <?php 
                                    $availableVehicles = count($voitureModel->getVoituresByStatus('disponible'));
                                    echo $availableVehicles;
                                    ?>
                                </div>
                                <div class="trend negative"><i class="fas fa-arrow-down"></i> -3% cette semaine</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <h5>Revenus mensuels</h5>
                                <div class="value">
                                    <?php 
                                    $monthlyRevenue = isset($reservationStats['current_month']['revenue']) ? 
                                                    number_format($reservationStats['current_month']['revenue'], 0, ',', '.') : 0;
                                    echo $monthlyRevenue . ' FCFA';
                                    ?>
                                </div>
                                <div class="trend"><i class="fas fa-arrow-up"></i> +8% ce mois</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card">
                                <h5>Nouveaux clients</h5>
                                <div class="value">
                                    <?php 
                                    echo $clientStats['new_this_month'];
                                    ?>
                                </div>
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
                                        <?php if (empty($recentReservations)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center">Aucune réservation récente</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recentReservations as $reservation): ?>
                                                <?php 
                                                // Récupérer les détails complets de la réservation
                                                $reservationDetails = $reservationModel->getReservationById($reservation['id']);
                                                $client = $reservationDetails['client'];
                                                $vehicle = $reservationDetails['vehicule'];
                                                
                                                // Définir la classe CSS pour le badge de statut
                                                $statusClass = '';
                                                switch($reservation['statut']) {
                                                    case 'confirmee':
                                                        $statusClass = 'bg-success';
                                                        break;
                                                    case 'en_attente':
                                                        $statusClass = 'bg-warning text-dark';
                                                        break;
                                                    case 'en_cours':
                                                        $statusClass = 'bg-info';
                                                        break;
                                                    case 'terminee':
                                                        $statusClass = 'bg-primary';
                                                        break;
                                                    case 'annulee':
                                                        $statusClass = 'bg-danger';
                                                        break;
                                                    default:
                                                        $statusClass = 'bg-secondary';
                                                }
                                                ?>
                                                <tr>
                                                    <td>#<?php echo $reservation['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($client['prenom'] . ' ' . $client['nom']); ?></td>
                                                    <td><?php echo htmlspecialchars($vehicle['marque'] . ' ' . $vehicle['modele']); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($reservation['date_debut'])); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($reservation['date_fin'])); ?></td>
                                                    <td><span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($reservation['statut']); ?></span></td>
                                                    <td class="action-buttons">
                                                        <a href="details_reservation.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                                        <a href="edit_reservation.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                                                        <?php if ($reservation['statut'] === 'en_attente'): ?>
                                                            <button class="btn btn-sm btn-outline-danger delete-reservation" data-id="<?php echo $reservation['id']; ?>"><i class="fas fa-trash"></i></button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
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
                                    <a href="gestion_vehicules.php" class="btn btn-sm btn-primary">Voir tous les véhicules</a>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <?php 
                                        // Récupérer le nombre total de véhicules
                                        $allVehicles = $voitureModel->getAllVoitures();
                                        $totalVehicles = count($allVehicles);
                                        
                                        // Véhicules disponibles
                                        $availableVehicles = count($voitureModel->getVoituresByStatus('disponible'));
                                        $availablePercentage = $totalVehicles > 0 ? round(($availableVehicles / $totalVehicles) * 100) : 0;
                                        
                                        // Véhicules indisponibles (loués, en maintenance, etc.)
                                        $unavailableVehicles = $totalVehicles - $availableVehicles;
                                        $unavailablePercentage = $totalVehicles > 0 ? round(($unavailableVehicles / $totalVehicles) * 100) : 0;
                                        ?>
                                        <div class="vehicle-status">
                                            <i class="fas fa-car text-success"></i>
                                            <span>Disponibles</span>
                                            <span class="badge bg-success"><?php echo $availableVehicles; ?></span>
                                            <div class="progress mt-2 mb-2">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $availablePercentage; ?>%" aria-valuenow="<?php echo $availablePercentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="text-muted"><?php echo $availablePercentage; ?>% des véhicules disponibles</span>
                                            <div class="mt-2">
                                                <a href="gestion_vehicules.php?statut=disponible" class="btn btn-sm btn-outline-primary">Voir tous</a>
                                            </div>
                                        </div>
                                        <div class="vehicle-status">
                                            <i class="fas fa-car text-danger"></i>
                                            <span>Indisponibles</span>
                                            <span class="badge bg-danger"><?php echo $unavailableVehicles; ?></span>
                                            <div class="progress mt-2 mb-2">
                                                <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $unavailablePercentage; ?>%" aria-valuenow="<?php echo $unavailablePercentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="text-muted"><?php echo $unavailablePercentage; ?>% des véhicules indisponibles</span>
                                            <div class="mt-2">
                                                <a href="gestion_vehicules.php?statut=indisponible" class="btn btn-sm btn-outline-danger">Voir tous</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0">Statut des clients</h4>
                                    <a href="gestion_clients.php" class="btn btn-sm btn-primary">Voir tous les clients</a>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div class="client-status">
                                            <i class="fas fa-users text-primary"></i>
                                            <span>Nouveaux clients</span>
                                            <span class="badge bg-primary"><?php echo $clientStats['new_this_month']; ?></span>
                                            <div class="progress mt-2 mb-2">
                                                <?php 
                                                $newClientsPercentage = $clientStats['total'] > 0 ? 
                                                                    round(($clientStats['new_this_month'] / $clientStats['total']) * 100) : 0;
                                                ?>
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $newClientsPercentage; ?>%" aria-valuenow="<?php echo $newClientsPercentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="text-muted"><?php echo $newClientsPercentage; ?>% des nouveaux clients</span>
                                            <div class="mt-2">
                                                <a href="gestion_clients.php?statut=nouveau" class="btn btn-sm btn-outline-primary">Voir tous</a>
                                                <a href="gestion_clients.php?statut=actif" class="btn btn-sm btn-outline-success">Voir les actifs</a>
                                                <a href="gestion_clients.php?statut=inactif" class="btn btn-sm btn-outline-danger">Voir les inactifs</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mt-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0">Statut des réservations</h4>
                                    <a href="gestion_reservations.php" class="btn btn-sm btn-primary">Voir toutes les réservations</a>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <?php
                                        // Calculer le nombre de réservations confirmées
                                        $confirmedReservations = 0;
                                        $totalReservations = isset($reservationStats['total']) ? $reservationStats['total'] : 0;
                                        
                                        foreach ($reservationStats['by_status'] as $status) {
                                            if ($status['statut'] === 'confirmee') {
                                                $confirmedReservations = $status['count'];
                                            }
                                        }
                                        
                                        $confirmedPercentage = $totalReservations > 0 ? 
                                                            round(($confirmedReservations / $totalReservations) * 100) : 0;
                                        ?>
                                        <div class="reservation-status">
                                            <i class="fas fa-calendar-check text-success"></i>
                                            <span>Confirmées</span>
                                            <span class="badge bg-success"><?php echo $confirmedReservations; ?></span>
                                            <div class="progress mt-2 mb-2">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $confirmedPercentage; ?>%" aria-valuenow="<?php echo $confirmedPercentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="text-muted"><?php echo $confirmedPercentage; ?>% des réservations confirmées</span>
                                            <div class="mt-2">
                                                <a href="gestion_reservations.php?statut=confirmee" class="btn btn-sm btn-outline-success">Voir confirmées</a>
                                                <a href="gestion_reservations.php?statut=en_attente" class="btn btn-sm btn-outline-warning">Voir en attente</a>
                                                <a href="gestion_reservations.php?statut=annulee" class="btn btn-sm btn-outline-danger">Voir annulées</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script>
                    $(document).ready(function() {
                        // Gestion de la suppression des réservations
                        $('.delete-reservation').click(function() {
                            const reservationId = $(this).data('id');
                            if (confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')) {
                                $.ajax({
                                    url: '../Controller/DeleteReservation.php',
                                    type: 'POST',
                                    data: { id: reservationId },
                                    success: function(response) {
                                        if (response === 'success') {
                                            alert('Réservation supprimée avec succès !');
                                            location.reload();
                                        } else {
                                            alert('Erreur lors de la suppression : ' + response);
                                        }
                                    },
                                    error: function() {
                                        alert('Erreur de connexion au serveur.');
                                    }
                                });
                            }
                        });
                    });
                </script>
                <!-- /.container-fluid -->
                 <script>
                    $(document).ready(function() {
                        $('#dataTable').DataTable();
                    });
                </script>
            </div>
        </div>
    </div>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->  

    <!-- Main Footer -->
    <?php include('includes/footer.php'); ?>


