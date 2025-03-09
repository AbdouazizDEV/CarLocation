<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_start();
    require_once __DIR__ . "/../Models/client.php";
    require_once __DIR__ . "/../Models/voiture.php";
    require_once __DIR__ . "/../Models/Reservation.php";

    // Vérifier si l'utilisateur est connecté et est un gérant
    if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'gérant') {
        header('Location: login.php');
        exit();
    }

    // Instancier les modèles
    $clientModel = new Client();
    $voitureModel = new Voiture();
    $reservationModel = new Reservation();
    
    // Récupérer tous les clients
    $clients = $clientModel->getAllClients();

    // Filtrer les clients par statut si demandé
    $statut_filter = $_GET['statut'] ?? null;
    if ($statut_filter) {
        $clients = array_filter($clients, function($client) use ($statut_filter) {
            return $client['statut'] === $statut_filter;
        });
    }

    // Recherche de clients
    $search_term = $_GET['search'] ?? null;
    if ($search_term) {
        $clients = $clientModel->searchClients($search_term);
    }
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestion des clients - NDAAMAR</title>
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
            .client-info-card {
                transition: transform 0.2s;
            }
            .client-info-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            }
            .status-badge {
                font-size: 0.8rem;
                padding: 0.25rem 0.5rem;
            }
            .action-buttons .btn {
                margin-right: 5px;
            }
            .stats-card {
                border-left: 4px solid;
                transition: transform 0.2s;
            }
            .stats-card:hover {
                transform: translateY(-3px);
            }
            .bg-light-blue {
                background-color: #e3f2fd;
                border-left-color: #42a5f5;
            }
            .bg-light-green {
                background-color: #e8f5e9;
                border-left-color: #66bb6a;
            }
            .bg-light-orange {
                background-color: #fff8e1;
                border-left-color: #ffb74d;
            }
            .bg-light-purple {
                background-color: #f3e5f5;
                border-left-color: #ab47bc;
            }
            .client-avatar {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                object-fit: cover;
            }
            .nav-tabs .nav-link {
                border: none;
                color: #6c757d;
                padding: 0.5rem 1rem;
                border-bottom: 2px solid transparent;
            }
            .nav-tabs .nav-link.active {
                color: #0d6efd;
                border-bottom: 2px solid #0d6efd;
                background: transparent;
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
                        <a href="gestion_clients.php" class="menu-item active">
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
                            <form class="d-flex search-bar" method="GET" action="gestion_clients.php">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Rechercher un client..." name="search" value="<?php echo $search_term ?? ''; ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
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
                            <h1>Gestion des clients</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="AcceuilGerant.php">Application</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Gestion des clients</li>
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
                        
                        <!-- Statistiques clients -->
                        <div class="row mb-4">
                            <?php 
                            // Récupérer les statistiques des clients
                            $stats = $clientModel->getClientStats();
                            ?>
                            <div class="col-md-3">
                                <div class="card stats-card bg-light-blue">
                                    <div class="card-body">
                                        <h6 class="card-title text-secondary">Total Clients</h6>
                                        <h3 class="card-text"><?php echo $stats['total'] ?? 0; ?></h3>
                                        <p class="card-text"><small class="text-muted"><i class="fas fa-users"></i> Clients enregistrés</small></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stats-card bg-light-green">
                                    <div class="card-body">
                                        <h6 class="card-title text-secondary">Nouveaux Clients</h6>
                                        <h3 class="card-text"><?php echo $stats['new_this_month'] ?? 0; ?></h3>
                                        <p class="card-text"><small class="text-muted"><i class="fas fa-user-plus"></i> Ce mois-ci</small></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stats-card bg-light-orange">
                                    <div class="card-body">
                                        <h6 class="card-title text-secondary">Clients Actifs</h6>
                                        <h3 class="card-text">
                                            <?php 
                                            $activeClients = array_filter($clients, function($client) {
                                                return $client['statut'] === 'actif';
                                            });
                                            echo count($activeClients);
                                            ?>
                                        </h3>
                                        <p class="card-text"><small class="text-muted"><i class="fas fa-user-check"></i> Comptes actifs</small></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stats-card bg-light-purple">
                                    <div class="card-body">
                                        <h6 class="card-title text-secondary">Réservations</h6>
                                        <h3 class="card-text">
                                            <?php 
                                            $allReservations = $reservationModel->getAllReservations();
                                            echo count($allReservations);
                                            ?>
                                        </h3>
                                        <p class="card-text"><small class="text-muted"><i class="fas fa-calendar-check"></i> Total des réservations</small></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Liste des clients</h5>
                                <div>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">
                                        <i class="fas fa-plus me-2"></i>Ajouter un client
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="btn-group" role="group">
                                        <a href="gestion_clients.php" class="btn <?php echo !$statut_filter ? 'btn-primary' : 'btn-outline-primary'; ?>">Tous</a>
                                        <a href="gestion_clients.php?statut=actif" class="btn <?php echo $statut_filter === 'actif' ? 'btn-primary' : 'btn-outline-primary'; ?>">Actifs</a>
                                        <a href="gestion_clients.php?statut=inactif" class="btn <?php echo $statut_filter === 'inactif' ? 'btn-primary' : 'btn-outline-primary'; ?>">Inactifs</a>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table id="clientsTable" class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nom</th>
                                                <th>Email</th>
                                                <th>Téléphone</th>
                                                <th>Adresse</th>
                                                <th>Inscription</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($clients)): ?>
                                                <?php foreach ($clients as $client): ?>
                                                    <tr>
                                                        <td><?php echo $client['id']; ?></td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1741271258/user-6380868_1280_zguwih.webp" alt="Avatar" class="client-avatar me-2">
                                                                <div>
                                                                    <div class="fw-bold"><?php echo $client['prenom'] . ' ' . $client['nom']; ?></div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td><?php echo $client['email']; ?></td>
                                                        <td><?php echo $client['telephone'] ?? 'Non renseigné'; ?></td>
                                                        <td><?php echo $client['adresse'] ?? 'Non renseignée'; ?></td>
                                                        <td><?php echo isset($client['date_inscription']) ? date('d/m/Y', strtotime($client['date_inscription'])) : 'N/A'; ?></td>
                                                        <td>
                                                            <span class="badge <?php echo $client['statut'] === 'actif' ? 'bg-success' : 'bg-danger'; ?> status-badge">
                                                                <?php echo $client['statut'] === 'actif' ? 'Actif' : 'Inactif'; ?>
                                                            </span>
                                                        </td>
                                                        <td class="action-buttons">
                                                            <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#viewClientModal" data-id="<?php echo $client['id']; ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-warning text-white" data-bs-toggle="modal" data-bs-target="#editClientModal" data-id="<?php echo $client['id']; ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <?php if ($client['statut'] === 'actif'): ?>
                                                                <button class="btn btn-sm btn-danger" onclick="confirmDeactivate(<?php echo $client['id']; ?>)">
                                                                    <i class="fas fa-ban"></i>
                                                                </button>
                                                            <?php else: ?>
                                                                <button class="btn btn-sm btn-success" onclick="confirmActivate(<?php echo $client['id']; ?>)">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="8" class="text-center">Aucun client trouvé</td>
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
        
        <!-- Modal Ajouter Client -->
        <div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addClientModalLabel">Ajouter un client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="../Controller/ClientController.php" id="addClientForm">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nom" class="form-label">Nom</label>
                                        <input type="text" class="form-control" id="nom" name="nom" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="prenom" class="form-label">Prénom</label>
                                        <input type="text" class="form-control" id="prenom" name="prenom" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="mot_de_passe" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="telephone" name="telephone">
                            </div>
                            
                            <div class="mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <textarea class="form-control" id="adresse" name="adresse" rows="2"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Statut</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="statut" id="statut_actif" value="actif" checked>
                                    <label class="form-check-label" for="statut_actif">
                                        Actif
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="statut" id="statut_inactif" value="inactif">
                                    <label class="form-check-label" for="statut_inactif">
                                        Inactif
                                    </label>
                                </div>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">Ajouter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Voir Client -->
        <div class="modal fade" id="viewClientModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Détails du client</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card mb-4">
                                    <div class="card-body text-center">
                                        <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1741271258/user-6380868_1280_zguwih.webp" alt="Avatar" class="rounded-circle img-fluid" style="width: 120px;">
                                        <h5 class="mt-3 mb-0" id="viewClientFullName">Chargement...</h5>
                                        <p class="text-muted mb-3" id="viewClientEmail">Chargement...</p>
                                        <div id="viewClientStatus"></div>
                                    </div>
                                </div>
                                
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Informations de contact</h5>
                                        <div class="mb-3">
                                            <span class="text-muted"><i class="fas fa-phone-alt me-2"></i>Téléphone</span>
                                            <p class="mb-0" id="viewClientPhone">Chargement...</p>
                                        </div>
                                        <div class="mb-3">
                                            <span class="text-muted"><i class="fas fa-envelope me-2"></i>Email</span>
                                            <p class="mb-0" id="viewClientEmailDetail">Chargement...</p>
                                        </div>
                                        <div>
                                            <span class="text-muted"><i class="fas fa-map-marker-alt me-2"></i>Adresse</span>
                                            <p class="mb-0" id="viewClientAddress">Chargement...</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Statistiques client</h5>
                                        <div class="mb-3">
                                            <span class="text-muted">Date d'inscription</span>
                                            <p class="mb-0" id="viewClientRegDate">Chargement...</p>
                                        </div>
                                        <div class="mb-3">
                                            <span class="text-muted">Nombre de réservations</span>
                                            <p class="mb-0 fw-bold" id="viewClientReservationCount">Chargement...</p>
                                        </div>
                                        <div>
                                            <span class="text-muted">Montant total dépensé</span>
                                            <p class="mb-0 fw-bold" id="viewClientTotalSpent">Chargement...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-8">
                                <ul class="nav nav-tabs mb-3" id="clientTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="reservations-tab" data-bs-toggle="tab" data-bs-target="#reservations" type="button" role="tab">
                                            <i class="fas fa-calendar-check me-2"></i>Réservations
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab">
                                            <i class="fas fa-money-bill-wave me-2"></i>Paiements
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="favorites-tab" data-bs-toggle="tab" data-bs-target="#favorites" type="button" role="tab">
                                            <i class="fas fa-heart me-2"></i>Favoris
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab">
                                            <i class="fas fa-sticky-note me-2"></i>Notes
                                        </button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content" id="clientTabsContent">
                                    <div class="tab-pane fade show active" id="reservations" role="tabpanel" aria-labelledby="reservations-tab">
                                        <div class="card">
                                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Historique des réservations</h6>
                                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addReservationModal" id="addReservationForClient">
                        <i class="fas fa-plus me-2"></i>Nouvelle réservation
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="clientReservationsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Véhicule</th>
                                    <th>Dates</th>
                                    <th>Prix</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="viewClientReservations">
                                <!-- Les réservations seront chargées via JavaScript -->
                                <tr>
                                    <td colspan="6" class="text-center">Chargement des réservations...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="tab-pane fade" id="payments" role="tabpanel" aria-labelledby="payments-tab">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Historique des paiements</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="clientPaymentsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Réservation</th>
                                    <th>Montant</th>
                                    <th>Méthode</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody id="viewClientPayments">
                                <!-- Les paiements seront chargés via JavaScript -->
                                <tr>
                                    <td colspan="6" class="text-center">Chargement des paiements...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="tab-pane fade" id="favorites" role="tabpanel" aria-labelledby="favorites-tab">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Véhicules favoris</h6>
                </div>
                <div class="card-body">
                    <div class="row" id="viewClientFavorites">
                        <!-- Les favoris seront chargés via JavaScript -->
                        <div class="col-12 text-center">Chargement des favoris...</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Notes et commentaires</h6>
                    <button class="btn btn-sm btn-primary" id="addNoteBtn">
                        <i class="fas fa-plus me-2"></i>Ajouter une note
                    </button>
                </div>
                <div class="card-body">
                    <div id="notesContainer">
                        <div id="viewClientNotes">
                            <!-- Les notes seront chargées via JavaScript -->
                            <p class="text-center">Chargement des notes...</p>
                        </div>
                        
                        <div id="addNoteForm" class="mt-3" style="display: none;">
                            <form id="noteForm">
                                <input type="hidden" id="noteClientId" name="client_id">
                                <div class="mb-3">
                                    <label for="noteContent" class="form-label">Nouvelle note</label>
                                    <textarea class="form-control" id="noteContent" name="note" rows="3" required></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-secondary me-2" id="cancelNoteBtn">Annuler</button>
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
<button type="button" class="btn btn-primary" id="editClientBtn">Modifier</button>
</div>
</div>
</div>
</div>

<!-- Modal Modifier Client -->
<div class="modal fade" id="editClientModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Modifier le client</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
<form method="POST" action="../Controller/ClientController.php" id="editClientForm">
<input type="hidden" name="action" value="update">
<input type="hidden" name="client_id" id="edit_client_id">

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="edit_nom" class="form-label">Nom</label>
            <input type="text" class="form-control" id="edit_nom" name="nom" required>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="edit_prenom" class="form-label">Prénom</label>
            <input type="text" class="form-control" id="edit_prenom" name="prenom" required>
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="edit_email" class="form-label">Email</label>
    <input type="email" class="form-control" id="edit_email" name="email" required>
</div>

<div class="mb-3">
    <label for="edit_telephone" class="form-label">Téléphone</label>
    <input type="tel" class="form-control" id="edit_telephone" name="telephone">
</div>

<div class="mb-3">
    <label for="edit_adresse" class="form-label">Adresse</label>
    <textarea class="form-control" id="edit_adresse" name="adresse" rows="2"></textarea>
</div>

<div class="mb-3">
    <label class="form-label">Statut</label>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="statut" id="edit_statut_actif" value="actif">
        <label class="form-check-label" for="edit_statut_actif">
            Actif
        </label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="statut" id="edit_statut_inactif" value="inactif">
        <label class="form-check-label" for="edit_statut_inactif">
            Inactif
        </label>
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

<!-- Modal de confirmation de désactivation -->
<div class="modal fade" id="deactivateModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Désactiver le client</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
<p>Êtes-vous sûr de vouloir désactiver ce client ?</p>
<p class="text-muted">Le client ne pourra plus se connecter ni effectuer de réservations tant qu'il sera désactivé.</p>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
<form method="POST" action="../Controller/ClientController.php">
<input type="hidden" name="action" value="deactivate">
<input type="hidden" name="client_id" id="deactivate_client_id">
<button type="submit" class="btn btn-danger">Désactiver</button>
</form>
</div>
</div>
</div>
</div>

<!-- Modal de confirmation d'activation -->
<div class="modal fade" id="activateModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Activer le client</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
<p>Êtes-vous sûr de vouloir activer ce client ?</p>
<p class="text-muted">Le client pourra à nouveau se connecter et effectuer des réservations.</p>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
<form method="POST" action="../Controller/ClientController.php">
<input type="hidden" name="action" value="activate">
<input type="hidden" name="client_id" id="activate_client_id">
<button type="submit" class="btn btn-success">Activer</button>
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
// Fonction pour formater un prix
function formatPrice(price) {
return new Intl.NumberFormat('fr-FR').format(price) + ' FCFA';
}

// Fonction pour formater une date
function formatDate(dateString) {
const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
return new Date(dateString).toLocaleDateString('fr-FR', options);
}

// Fonction pour charger les détails d'un client
function loadClientDetails(clientId) {
// Effectuer une requête AJAX pour récupérer les détails du client
$.ajax({
url: '../Controller/ClientController.php',
type: 'GET',
data: { action: 'get', id: clientId },
dataType: 'json',
success: function(client) {
    // Mise à jour des informations du client
    $('#viewClientFullName').text(client.prenom + ' ' + client.nom);
    $('#viewClientEmail, #viewClientEmailDetail').text(client.email);
    
    // Statut
    let statusClass = client.statut === 'actif' ? 'bg-success' : 'bg-danger';
    let statusText = client.statut === 'actif' ? 'Actif' : 'Inactif';
    $('#viewClientStatus').html(`<span class="badge ${statusClass}">${statusText}</span>`);
    
    // Informations de contact
    $('#viewClientPhone').text(client.telephone || 'Non renseigné');
    $('#viewClientAddress').text(client.adresse || 'Non renseignée');
    
    // Statistiques
    $('#viewClientRegDate').text(formatDate(client.date_inscription));
    $('#viewClientReservationCount').text(client.reservation_count || '0');
    $('#viewClientTotalSpent').text(formatPrice(client.total_spent || 0));
    
    // Charger les réservations, paiements et favoris du client
    loadClientReservations(clientId);
    loadClientPayments(clientId);
    loadClientFavorites(clientId);
    loadClientNotes(clientId);
    
    // Configurer le bouton d'édition
    $('#editClientBtn').off('click').on('click', function() {
        $('#viewClientModal').modal('hide');
        loadClientForEdit(clientId);
        $('#editClientModal').modal('show');
    });
    
    // Configurer le formulaire d'ajout de note
    $('#noteClientId').val(clientId);
    
    // Configurer le bouton d'ajout de réservation
    $('#addReservationForClient').off('click').on('click', function() {
        $('#viewClientModal').modal('hide');
        $('#addReservationModal').find('#client').val(clientId).trigger('change');
        $('#addReservationModal').modal('show');
    });
},
error: function(xhr, status, error) {
    console.error('Erreur lors de la récupération des détails du client:', error);
    alert('Erreur lors de la récupération des détails du client.');
}
});
}

// Fonction pour charger les réservations d'un client
function loadClientReservations(clientId) {
$.ajax({
url: '../Controller/ClientController.php',
type: 'GET',
data: { action: 'getReservations', id: clientId },
dataType: 'json',
success: function(reservations) {
    let html = '';
    
    if (reservations.length === 0) {
        html = '<tr><td colspan="6" class="text-center">Aucune réservation trouvée</td></tr>';
    } else {
        for (let res of reservations) {
            // Déterminer la classe et le texte du statut
            let statusClass = '';
            let statusText = '';
            
            switch (res.statut) {
                case 'en_attente':
                    statusClass = 'bg-warning text-dark';
                    statusText = 'En attente';
                    break;
                case 'confirmee':
                    statusClass = 'bg-info text-white';
                    statusText = 'Confirmée';
                    break;
                case 'en_cours':
                    statusClass = 'bg-primary';
                    statusText = 'En cours';
                    break;
                case 'terminee':
                    statusClass = 'bg-success';
                    statusText = 'Terminée';
                    break;
                case 'annulee':
                    statusClass = 'bg-danger';
                    statusText = 'Annulée';
                    break;
                default:
                    statusClass = 'bg-secondary';
                    statusText = 'Inconnue';
            }
            
            html += `
            <tr>
                <td>${res.id}</td>
                <td>${res.marque} ${res.modele}</td>
                <td>${formatDate(res.date_debut)} - ${formatDate(res.date_fin)}</td>
                <td>${formatPrice(res.prix_total)}</td>
                <td><span class="badge ${statusClass}">${statusText}</span></td>
                <td>
                    <button class="btn btn-sm btn-info text-white" onclick="window.location.href='gestion_reservations.php?id=${res.id}'">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
            `;
        }
    }
    
    $('#viewClientReservations').html(html);
}
});
}

// Fonction pour charger les paiements d'un client
function loadClientPayments(clientId) {
$.ajax({
url: '../Controller/ClientController.php',
type: 'GET',
data: { action: 'getPayments', id: clientId },
dataType: 'json',
success: function(payments) {
    let html = '';
    
    if (payments.length === 0) {
        html = '<tr><td colspan="6" class="text-center">Aucun paiement trouvé</td></tr>';
    } else {
        for (let payment of payments) {
            let statusClass = payment.statut === 'validé' ? 'bg-success' : 'bg-warning';
            
            html += `
            <tr>
                <td>${payment.id}</td>
                <td>${formatDate(payment.date_paiement)}</td>
                <td>Réservation #${payment.reservation_id}</td>
                <td>${formatPrice(payment.montant)}</td>
                <td>${payment.methode}</td>
                <td><span class="badge ${statusClass}">${payment.statut}</span></td>
            </tr>
            `;
        }
    }
    
    $('#viewClientPayments').html(html);
}
});
}

// Fonction pour charger les favoris d'un client
function loadClientFavorites(clientId) {
$.ajax({
url: '../Controller/ClientController.php',
type: 'GET',
data: { action: 'getFavorites', id: clientId },
dataType: 'json',
success: function(favorites) {
    let html = '';
    
    if (favorites.length === 0) {
        html = '<div class="col-12 text-center">Aucun favori trouvé</div>';
    } else {
        for (let fav of favorites) {
            let image = fav.images ? '../' + fav.images.split(',')[0] : 'https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg';
            
            html += `
            <div class="col-md-4 col-sm-6 mb-3">
                <div class="card h-100">
                    <img src="${image}" class="card-img-top" alt="${fav.marque} ${fav.modele}" style="height: 150px; object-fit: cover;">
                    <div class="card-body">
                        <h6 class="card-title">${fav.marque} ${fav.modele}</h6>
                        <p class="card-text">${formatPrice(fav.prix_location)}/jour</p>
                        <a href="gestion_vehicules.php?id=${fav.voiture_id}" class="btn btn-sm btn-outline-primary">Voir le véhicule</a>
                    </div>
                </div>
            </div>
            `;
        }
    }
    
    $('#viewClientFavorites').html(html);
}
});
}

// Fonction pour charger les notes d'un client
function loadClientNotes(clientId) {
$.ajax({
url: '../Controller/ClientController.php',
type: 'GET',
data: { action: 'getNotes', id: clientId },
dataType: 'json',
success: function(notes) {
    let html = '';
    
    if (notes.length === 0) {
        html = '<p class="text-center">Aucune note disponible</p>';
    } else {
        for (let note of notes) {
            html += `
            <div class="card mb-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <small class="text-muted">Le ${formatDate(note.date_ajout)}</small>
                    </div>
                    <p class="card-text">${note.note}</p>
                </div>
            </div>
            `;
        }
    }
    
    $('#viewClientNotes').html(html);
}
});
}

// Fonction pour charger un client dans le formulaire d'édition
function loadClientForEdit(clientId) {
$('#edit_client_id').val(clientId);

$.ajax({
url: '../Controller/ClientController.php',
type: 'GET',
data: { action: 'get', id: clientId },
dataType: 'json',
success: function(client) {
    // Remplir le formulaire avec les données du client
    $('#edit_nom').val(client.nom);
    $('#edit_prenom').val(client.prenom);
    $('#edit_email').val(client.email);
    $('#edit_telephone').val(client.telephone || '');
    $('#edit_adresse').val(client.adresse || '');
    
    if (client.statut === 'actif') {
        $('#edit_statut_actif').prop('checked', true);
    } else {
        $('#edit_statut_inactif').prop('checked', true);
    }
},
error: function(xhr, status, error) {
    console.error('Erreur lors de la récupération des détails du client:', error);
    alert('Erreur lors de la récupération des détails du client.');
}
});
}

// Fonction pour confirmer la désactivation d'un client
function confirmDeactivate(clientId) {
$('#deactivate_client_id').val(clientId);
$('#deactivateModal').modal('show');
}

// Fonction pour confirmer l'activation d'un client
function confirmActivate(clientId) {
$('#activate_client_id').val(clientId);
$('#activateModal').modal('show');
}

// Document ready
$(document).ready(function() {
// Initialisation de DataTables avec traduction en français
$('#clientsTable').DataTable({
language: {
    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
}
});

// Gestionnaire d'événements pour l'ouverture de la modal de détails client
$('#viewClientModal').on('show.bs.modal', function(event) {
const button = $(event.relatedTarget);
const clientId = button.data('id');
loadClientDetails(clientId);
});

// Gestionnaire d'événements pour l'ouverture de la modal d'édition client
$('#editClientModal').on('show.bs.modal', function(event) {
const button = $(event.relatedTarget);
if (button.length) {
    const clientId = button.data('id');
    loadClientForEdit(clientId);
}
});

// Gestionnaire pour les boutons d'ajout et d'annulation de note
$('#addNoteBtn').click(function() {
$('#addNoteForm').show();
$('#addNoteBtn').hide();
});

$('#cancelNoteBtn').click(function() {
$('#addNoteForm').hide();
$('#addNoteBtn').show();
});

// Gestion du formulaire d'ajout de note
$('#noteForm').submit(function(e) {
e.preventDefault();
const clientId = $('#noteClientId').val();
const note = $('#noteContent').val();

$.ajax({
    url: '../Controller/ClientController.php',
    type: 'POST',
    data: {
        action: 'addNote',
        client_id: clientId,
        note: note
    },
    success: function(response) {
        // Recharger les notes
        loadClientNotes(clientId);
        // Réinitialiser le formulaire
        $('#noteContent').val('');
        $('#addNoteForm').hide();
        $('#addNoteBtn').show();
    },
    error: function(xhr, status, error) {
        console.error('Erreur lors de l\'ajout de la note:', error);
        alert('Erreur lors de l\'ajout de la note.');
    }
});
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
});
</script>
</body>
</html>