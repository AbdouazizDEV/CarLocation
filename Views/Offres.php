<?php
    session_start();
    require_once __DIR__ . "/../Models/voiture.php";
    require_once __DIR__ . "/../Models/offre.php";

    // Vérifier si l'utilisateur est connecté et est un gérant
    if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'gérant') {
        header('Location: login.php');
        exit();
    }

    // Instancier les objets nécessaires
    $voitureModel = new Voiture();
    $offreModel = new Offre();

    // Récupérer toutes les offres actives
    $offres = $offreModel->getAllOffres();

    // Récupérer les véhicules disponibles pour créer des offres
    $vehiculesDisponibles = $voitureModel->getVoituresByStatus('disponible');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des offres - NDAAMAR</title>
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
        .action-buttons .btn {
            margin-right: 5px;
        }
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
        .car-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
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
                    <a href="Offres.php" class="menu-item active">
                        <i class="fas fa-tag"></i> Offres
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
                        <h1>Gestion des offres</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="AcceuilGerant.php">Application</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Gestion des offres</li>
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
                    
                    <!-- Dashboard Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Offres actives</h6>
                                            <h2 class="mt-2 mb-0">
                                                <?php 
                                                $activeCount = 0;
                                                foreach ($offres as $offre) {
                                                    if ($offre['statut'] === 'active') {
                                                        $activeCount++;
                                                    }
                                                }
                                                echo $activeCount;
                                                ?>
                                            </h2>
                                        </div>
                                        <i class="fas fa-tag fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Offres expirées</h6>
                                            <h2 class="mt-2 mb-0">
                                                <?php 
                                                $inactiveCount = 0;
                                                foreach ($offres as $offre) {
                                                    if ($offre['statut'] === 'inactive') {
                                                        $inactiveCount++;
                                                    }
                                                }
                                                echo $inactiveCount;
                                                ?>
                                            </h2>
                                        </div>
                                        <i class="fas fa-calendar-times fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Réduction moyenne</h6>
                                            <h2 class="mt-2 mb-0">
                                                <?php 
                                                $totalReduction = 0;
                                                $offreCount = count($offres);
                                                
                                                if ($offreCount > 0) {
                                                    foreach ($offres as $offre) {
                                                        $totalReduction += $offre['reduction'];
                                                    }
                                                    echo round($totalReduction / $offreCount, 1) . '%';
                                                } else {
                                                    echo '0%';
                                                }
                                                ?>
                                            </h2>
                                        </div>
                                        <i class="fas fa-percent fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-0">Véhicules disponibles</h6>
                                            <h2 class="mt-2 mb-0"><?php echo count($vehiculesDisponibles); ?></h2>
                                        </div>
                                        <i class="fas fa-car fa-3x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Offres Tab & Create Button -->
                    <div class="card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <ul class="nav nav-tabs card-header-tabs" id="offresTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab" aria-controls="active" aria-selected="true">Offres actives</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="false">Toutes les offres</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="expired-tab" data-bs-toggle="tab" data-bs-target="#expired" type="button" role="tab" aria-controls="expired" aria-selected="false">Offres expirées</button>
                                </li>
                            </ul>
                            <div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOfferModal">
                                    <i class="fas fa-plus me-2"></i>Créer une offre
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="offresTabsContent">
                                <!-- Active Offers Tab -->
                                <div class="tab-pane fade show active" id="active" role="tabpanel" aria-labelledby="active-tab">
                                    <div class="row">
                                        <?php 
                                        $activeOffresCount = 0;
                                        foreach ($offres as $offre): 
                                            if ($offre['statut'] === 'active'):
                                                $activeOffresCount++;
                                            ?>
                                            <div class="col-md-4 mb-4">
                                                <div class="card card-offer">
                                                    <?php
                                                    // Récupérer les détails des véhicules associés à cette offre
                                                    $offre_vehicules = $offreModel->getVehiculesForOffre($offre['id']);
                                                    $vehicule_image = !empty($offre_vehicules) && isset($offre_vehicules[0]['images']) ? 
                                                                    "../" . $offre_vehicules[0]['images'] : 
                                                                    "https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg";
                                                    ?>
                                                    <img src="<?php echo $vehicule_image; ?>" class="card-img-top offer-vehicle-image" alt="<?php echo htmlspecialchars($offre['titre']); ?>">
                                                    <span class="offer-status bg-success text-white"><?php echo $offre['statut'] === 'active' ? 'Active' : 'Inactive'; ?></span>
                                                    <span class="offer-timer"><i class="far fa-clock me-1"></i><?php echo date('d/m/Y', strtotime($offre['date_fin'])); ?></span>
                                                    <span class="offer-discount">-<?php echo $offre['reduction']; ?>%</span>
                                                    
                                                    <div class="card-body">
                                                        <h5 class="card-title"><?php echo htmlspecialchars($offre['titre']); ?></h5>
                                                        <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($offre['description'], 0, 100)) . (strlen($offre['description']) > 100 ? '...' : ''); ?></p>
                                                        <div class="mb-2">
                                                            <span class="badge bg-light text-dark me-1"><i class="fas fa-calendar-alt me-1"></i><?php echo date('d/m/Y', strtotime($offre['date_debut'])); ?> - <?php echo date('d/m/Y', strtotime($offre['date_fin'])); ?></span>
                                                            <?php if (!empty($offre['code_promo'])): ?>
                                                                <span class="badge bg-dark"><i class="fas fa-ticket-alt me-1"></i><?php echo htmlspecialchars($offre['code_promo']); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="text-end">
                                                            <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#viewOfferModal" onclick="prepareViewModal(<?php echo $offre['id']; ?>)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-warning text-white" data-bs-toggle="modal" data-bs-target="#editOfferModal" onclick="prepareEditModal(<?php echo $offre['id']; ?>)">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteOfferModal" onclick="prepareDeleteModal(<?php echo $offre['id']; ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                        <br><br>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        
                                        if ($activeOffresCount === 0):
                                        ?>
                                            <div class="col-12">
                                                <div class="alert alert-info text-center">
                                                    <i class="fas fa-info-circle me-2"></i>Aucune offre active pour le moment.
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- All Offers Tab -->
                                <div class="tab-pane fade" id="all" role="tabpanel" aria-labelledby="all-tab">
                                    <div class="table-responsive">
                                        <table id="allOffersTable" class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Titre</th>
                                                    <th>Réduction</th>
                                                    <th>Date début</th>
                                                    <th>Date fin</th>
                                                    <th>Code Promo</th>
                                                    <th>Statut</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($offres)): ?>
                                                    <?php foreach ($offres as $offre): ?>
                                                        <tr>
                                                            <td><?php echo $offre['id']; ?></td>
                                                            <td><?php echo htmlspecialchars($offre['titre']); ?></td>
                                                            <td><?php echo $offre['reduction']; ?>%</td>
                                                            <td><?php echo date('d/m/Y', strtotime($offre['date_debut'])); ?></td>
                                                            <td><?php echo date('d/m/Y', strtotime($offre['date_fin'])); ?></td>
                                                            <td>
                                                                <?php if (!empty($offre['code_promo'])): ?>
                                                                    <span class="badge bg-dark"><?php echo htmlspecialchars($offre['code_promo']); ?></span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <span class="badge <?php echo $offre['statut'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                                                    <?php echo $offre['statut'] === 'active' ? 'Active' : 'Inactive'; ?>
                                                                </span>
                                                            </td>
                                                            <td class="action-buttons">
                                                                <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#viewOfferModal" onclick="prepareViewModal(<?php echo $offre['id']; ?>)">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-warning text-white" data-bs-toggle="modal" data-bs-target="#editOfferModal" onclick="prepareEditModal(<?php echo $offre['id']; ?>)">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteOfferModal" onclick="prepareDeleteModal(<?php echo $offre['id']; ?>)">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="8" class="text-center">Aucune offre trouvée</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Expired Offers Tab -->
                                <div class="tab-pane fade" id="expired" role="tabpanel" aria-labelledby="expired-tab">
                                    <div class="table-responsive">
                                        <table id="expiredOffersTable" class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Titre</th>
                                                    <th>Réduction</th>
                                                    <th>Date début</th>
                                                    <th>Date fin</th>
                                                    <th>Code Promo</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $expiredOffresCount = 0;
                                                foreach ($offres as $offre): 
                                                    if ($offre['statut'] === 'inactive'):
                                                        $expiredOffresCount++;
                                                ?>
                                                    <tr>
                                                        <td><?php echo $offre['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($offre['titre']); ?></td>
                                                        <td><?php echo $offre['reduction']; ?>%</td>
                                                        <td><?php echo date('d/m/Y', strtotime($offre['date_debut'])); ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($offre['date_fin'])); ?></td>
                                                        <td>
                                                            <?php if (!empty($offre['code_promo'])): ?>
                                                                <span class="badge bg-dark"><?php echo htmlspecialchars($offre['code_promo']); ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="action-buttons">
                                                            <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#viewOfferModal" onclick="prepareViewModal(<?php echo $offre['id']; ?>)">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#reactivateOfferModal" onclick="prepareReactivateModal(<?php echo $offre['id']; ?>)">
                                                                <i class="fas fa-redo"></i> Réactiver
                                                            </button>
                                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteOfferModal" onclick="prepareDeleteModal(<?php echo $offre['id']; ?>)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php 
                                                    endif;
                                                endforeach; 
                                                
                                                if ($expiredOffresCount === 0):
                                                ?>
                                                    <tr>
                                                        <td colspan="7" class="text-center">Aucune offre expirée</td>
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
        </div>
    </div>
    
    <!-- Modal Ajouter une offre -->
    <div class="modal fade" id="addOfferModal" tabindex="-1" aria-labelledby="addOfferModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addOfferModalLabel">Créer une nouvelle offre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>  
                <div class="modal-body">
                    <form method="POST" action="../Controller/OffreController.php" id="addOfferForm">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="titre" class="form-label">Titre de l'offre</label>
                                <input type="text" class="form-control" name="titre" id="titre" required>
                            </div>
                            <div class="col-md-4">
                            <label for="reduction" class="form-label">Réduction (%)</label>
                                <input type="number" class="form-control" name="reduction" id="reduction" min="1" max="90" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date_debut" class="form-label">Date de début</label>
                                <input type="date" class="form-control" name="date_debut" id="date_debut" required>
                            </div>
                            <div class="col-md-6">
                                <label for="date_fin" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" name="date_fin" id="date_fin" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="code_promo" class="form-label">Code promo (optionnel)</label>
                            <input type="text" class="form-control" name="code_promo" id="code_promo">
                            <div class="form-text">Laissez vide pour ne pas utiliser de code promo.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Véhicules concernés</label>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;"></th>
                                            <th style="width: 80px;">Image</th>
                                            <th>Marque/Modèle</th>
                                            <th>Catégorie</th>
                                            <th>Prix/jour</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($vehiculesDisponibles)): ?>
                                            <?php foreach ($vehiculesDisponibles as $vehicule): ?>
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="vehicules[]" value="<?php echo $vehicule['id']; ?>" id="vehicule_<?php echo $vehicule['id']; ?>">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($vehicule['images'])): ?>
                                                            <img src="../<?php echo $vehicule['images']; ?>" alt="<?php echo $vehicule['marque'] . ' ' . $vehicule['modele']; ?>" class="car-image">
                                                        <?php else: ?>
                                                            <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg" alt="Pas d'image" class="car-image">
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo $vehicule['marque'] . ' ' . $vehicule['modele']; ?></td>
                                                    <td><?php echo $vehicule['categorie']; ?></td>
                                                    <td><?php echo number_format($vehicule['prix_location'], 0, ',', ' ') . ' FCFA'; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Aucun véhicule disponible</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Créer l'offre</button>
                        </div>
                    </form>
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
                                <span id="viewOfferStatus" class="badge bg-success">Active</span>
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
                                    <h6 class="mb-0">Statistiques</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <td>Véhicules concernés:</td>
                                                <td id="viewOfferVehiclesCount" class="fw-bold">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td>Début de l'offre:</td>
                                                <td id="viewOfferStartDate">Chargement...</td>
                                            </tr>
                                            <tr>
                                                <td>Fin de l'offre:</td>
                                                <td id="viewOfferEndDate">Chargement...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <h5>Véhicules concernés</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Marque/Modèle</th>
                                            <th>Catégorie</th>
                                            <th>Prix normal</th>
                                            <th>Prix avec réduction</th>
                                        </tr>
                                    </thead>
                                    <tbody id="viewOfferVehicles">
                                        <!-- Les véhicules seront ajoutés dynamiquement -->
                                        <tr>
                                            <td colspan="5" class="text-center">Chargement...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" id="editOfferFromViewBtn" onclick="switchToEditModal()">Modifier</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Modifier l'offre -->
    <div class="modal fade" id="editOfferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier l'offre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="../Controller/OffreController.php" id="editOfferForm">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="edit_titre" class="form-label">Titre de l'offre</label>
                                <input type="text" class="form-control" name="titre" id="edit_titre" required>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_reduction" class="form-label">Réduction (%)</label>
                                <input type="number" class="form-control" name="reduction" id="edit_reduction" min="1" max="90" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_date_debut" class="form-label">Date de début</label>
                                <input type="date" class="form-control" name="date_debut" id="edit_date_debut" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_date_fin" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" name="date_fin" id="edit_date_fin" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_code_promo" class="form-label">Code promo (optionnel)</label>
                            <input type="text" class="form-control" name="code_promo" id="edit_code_promo">
                            <div class="form-text">Laissez vide pour ne pas utiliser de code promo.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_statut" class="form-label">Statut</label>
                            <select class="form-select" name="statut" id="edit_statut" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Véhicules concernés</label>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;"></th>
                                            <th style="width: 80px;">Image</th>
                                            <th>Marque/Modèle</th>
                                            <th>Catégorie</th>
                                            <th>Prix/jour</th>
                                        </tr>
                                    </thead>
                                    <tbody id="edit_vehicules_list">
                                        <!-- Les véhicules seront ajoutés dynamiquement -->
                                        <tr>
                                            <td colspan="5" class="text-center">Chargement des véhicules...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
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
    
    <!-- Modal Supprimer l'offre -->
    <div class="modal fade" id="deleteOfferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cette offre ? Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form method="POST" action="../Controller/OffreController.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Réactiver l'offre -->
    <div class="modal fade" id="reactivateOfferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Réactiver l'offre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Veuillez sélectionner une nouvelle date de fin pour réactiver cette offre.</p>
                    <form method="POST" action="../Controller/OffreController.php" id="reactivateOfferForm">
                        <input type="hidden" name="action" value="reactivate">
                        <input type="hidden" name="id" id="reactivate_id">
                        
                        <div class="mb-3">
                            <label for="reactivate_date_fin" class="form-label">Nouvelle date de fin</label>
                            <input type="date" class="form-control" name="date_fin" id="reactivate_date_fin" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" form="reactivateOfferForm" class="btn btn-success">Réactiver</button>
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
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Variable globale pour l'ID de l'offre en cours
        let currentOfferId = null;

        // Initialisation des DataTables
        $(document).ready(function() {
            $('#allOffersTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
                }
            });
            
            $('#expiredOffersTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
                }
            });
            
            // Initialiser les dates par défaut pour le formulaire d'ajout
            const today = new Date();
            const todayISO = today.toISOString().split('T')[0];
            $('#date_debut').val(todayISO);
            
            const nextMonth = new Date();
            nextMonth.setDate(today.getDate() + 30);
            const nextMonthISO = nextMonth.toISOString().split('T')[0];
            $('#date_fin').val(nextMonthISO);
            
            // Gestion du profil utilisateur
            $('#editProfileBtn').on('click', function() {
                $('#profileInfo').hide();
                $('#profileEditForm').show();
            });
            
            $('#cancelEditBtn').on('click', function() {
                $('#profileInfo').show();
                $('#profileEditForm').hide();
            });
            
            // Validation des formulaires
            $('#addOfferForm').on('submit', function(e) {
                const dateDebut = new Date($('#date_debut').val());
                const dateFin = new Date($('#date_fin').val());
                
                if (dateFin <= dateDebut) {
                    e.preventDefault();
                    alert('La date de fin doit être supérieure à la date de début.');
                    return false;
                }
                
                const vehiculesChecked = $('input[name="vehicules[]"]:checked').length;
                if (vehiculesChecked === 0) {
                    e.preventDefault();
                    alert('Veuillez sélectionner au moins un véhicule pour l\'offre.');
                    return false;
                }
                
                return true;
            });
            
            $('#editOfferForm').on('submit', function(e) {
                const dateDebut = new Date($('#edit_date_debut').val());
                const dateFin = new Date($('#edit_date_fin').val());
                
                if (dateFin <= dateDebut) {
                    e.preventDefault();
                    alert('La date de fin doit être supérieure à la date de début.');
                    return false;
                }
                
                const vehiculesChecked = $('input[name="vehicules[]"]:checked').length;
                if (vehiculesChecked === 0) {
                    e.preventDefault();
                    alert('Veuillez sélectionner au moins un véhicule pour l\'offre.');
                    return false;
                }
                
                return true;
            });
        });

        // Fonctions pour préparer les modals
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
                        
                        // Mettre à jour les détails de l'offre
                        $('#viewOfferTitle').text(offre.titre);
                        $('#viewOfferDescription').text(offre.description || 'Aucune description disponible');
                        
                        // Statut
                        const statusClass = offre.statut === 'active' ? 'bg-success' : 'bg-danger';
                        const statusText = offre.statut === 'active' ? 'Active' : 'Inactive';
                        $('#viewOfferStatus').removeClass('bg-success bg-danger').addClass(statusClass).text(statusText);
                        
                        // Informations
                        $('#viewOfferReduction').text('-' + offre.reduction + '%');
                        $('#viewOfferPeriod').text(formatDate(offre.date_debut) + ' à ' + formatDate(offre.date_fin));
                        $('#viewOfferCode').text(offre.code_promo || 'Aucun code promo');
                        
                        // Statistiques
                        $('#viewOfferVehiclesCount').text(offre.vehicules ? offre.vehicules.length : 0);
                        $('#viewOfferStartDate').text(formatDate(offre.date_debut));
                        $('#viewOfferEndDate').text(formatDate(offre.date_fin));
                        
                        // Véhicules
                        const vehiclesContainer = $('#viewOfferVehicles');
                        vehiclesContainer.empty();
                        
                        if (offre.vehicules && offre.vehicules.length > 0) {
                            offre.vehicules.forEach(vehicule => {
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
                                    </tr>
                                `;
                                
                                vehiclesContainer.append(row);
                            });
                        } else {
                            vehiclesContainer.html('<tr><td colspan="5" class="text-center">Aucun véhicule associé à cette offre</td></tr>');
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

        function prepareEditModal(id) {
            currentOfferId = id;
            $('#edit_id').val(id);
            console.log("Préparation du modal d'édition pour l'offre ID:", id);
            
            // Charger les détails de l'offre via AJAX
            $.ajax({
                url: '../Controller/OffreController.php',
                type: 'GET',
                data: { action: 'get', id: id },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        const offre = data.offre;
                        
                        // Remplir le formulaire avec les données de l'offre
                        $('#edit_titre').val(offre.titre);
                        $('#edit_reduction').val(offre.reduction);
                        $('#edit_description').val(offre.description);
                        $('#edit_date_debut').val(offre.date_debut);
                        $('#edit_date_fin').val(offre.date_fin);
                        $('#edit_code_promo').val(offre.code_promo);
                        $('#edit_statut').val(offre.statut);
                        
                        // Charger la liste des véhicules disponibles
                        loadVehiclesForEdit(id, offre.vehicules || []);
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

        function loadVehiclesForEdit(offreId, selectedVehicles) {
            $.ajax({
                url: '../Controller/OffreController.php',
                type: 'GET',
                data: { action: 'getVehicules' },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        const vehicules = data.vehicules;
                        const vehiculesContainer = $('#edit_vehicules_list');
                        vehiculesContainer.empty();
                        
                        // Créer un tableau d'IDs de véhicules associés à l'offre
                        const selectedVehiculeIds = selectedVehicles.map(v => v.id.toString());
                        
                        if (vehicules && vehicules.length > 0) {
                            vehicules.forEach(vehicule => {
                                const isSelected = selectedVehiculeIds.includes(vehicule.id.toString());
                                
                                const row = `
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="vehicules[]" 
                                                value="${vehicule.id}" id="edit_vehicule_${vehicule.id}" 
                                                ${isSelected ? 'checked' : ''}>
                                            </div>
                                        </td>
                                        <td>
                                            <img src="../${vehicule.images || 'path/to/default-image.jpg'}" 
                                                alt="${vehicule.marque} ${vehicule.modele}" 
                                                class="car-image">
                                        </td>
                                        <td>${vehicule.marque} ${vehicule.modele}</td>
                                        <td>${vehicule.categorie}</td>
                                        <td>${formatCurrency(vehicule.prix_location)}</td>
                                    </tr>
                                `;
                                
                                vehiculesContainer.append(row);
                            });
                        } else {
                            vehiculesContainer.html('<tr><td colspan="5" class="text-center">Aucun véhicule disponible</td></tr>');
                        }
                    } else {
                        alert('Erreur lors du chargement des véhicules: ' + (data.message || 'Erreur inconnue'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Erreur AJAX (vehicules):", status, error);
                    alert('Erreur de communication avec le serveur lors du chargement des véhicules.');
                }
            });
        }

        function prepareDeleteModal(id) {
            $('#delete_id').val(id);
            console.log("Préparation du modal de suppression pour l'offre ID:", id);
        }

        function prepareReactivateModal(id) {
            $('#reactivate_id').val(id);
            console.log("Préparation du modal de réactivation pour l'offre ID:", id);
            
            // Définir la date minimale à aujourd'hui
            const today = new Date();
            const todayISO = today.toISOString().split('T')[0];
            $('#reactivate_date_fin').attr('min', todayISO);
            
            // Proposer une date par défaut (aujourd'hui + 30 jours)
            const defaultDate = new Date();
            defaultDate.setDate(today.getDate() + 30);
            const defaultDateISO = defaultDate.toISOString().split('T')[0];
            $('#reactivate_date_fin').val(defaultDateISO);
        }

        function switchToEditModal() {
            // Fermer le modal de visualisation
            $('#viewOfferModal').modal('hide');
            
            // Ouvrir le modal d'édition avec les données de l'offre
            setTimeout(function() {
                prepareEditModal(currentOfferId);
                $('#editOfferModal').modal('show');
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
    </script>
</body>
</html>