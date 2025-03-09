<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_start();
    require_once __DIR__ . "/../Models/Reservation.php";
    require_once __DIR__ . "/../Models/client.php";
    require_once __DIR__ . "/../Models/voiture.php";
    require_once __DIR__ . "/../Models/Facture.php"; // Vous devrez créer ce modèle

    // Vérifier si l'utilisateur est connecté et est un gérant
    if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'gérant') {
        header('Location: login.php');
        exit();
    }

    // Instancier les modèles
    $reservationModel = new Reservation();
    $clientModel = new Client();
    $voitureModel = new Voiture();
    $factureModel = new Facture();
    
    // Récupérer toutes les factures
    $factures = $factureModel->getAllFactures();

    // Récupérer les réservations pour lesquelles il n'y a pas encore de facture
    $reservationsSansFacture = $factureModel->getReservationsSansFacture();

    // Filtrer les factures par statut si demandé
    $statut_filter = $_GET['statut'] ?? null;
    if ($statut_filter) {
        $factures = array_filter($factures, function($facture) use ($statut_filter) {
            return $facture['statut'] === $statut_filter;
        });
    }

    // Recherche de factures
    $search_term = $_GET['search'] ?? null;
    if ($search_term) {
        $factures = $factureModel->searchFactures($search_term);
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturation - NDAAMAR</title>
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
        .invoice-template {
            background-color: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .invoice-header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .company-details, .client-details {
            margin-bottom: 20px;
        }
        .company-logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .invoice-details {
            margin-bottom: 30px;
        }
        .invoice-table th {
            background-color: #f8f9fa;
        }
        .invoice-total {
            border-top: 2px solid #333;
            font-weight: bold;
        }
        .invoice-footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            font-size: 0.9rem;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            #invoice-container, #invoice-container * {
                visibility: visible;
            }
            #invoice-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
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
                    <a href="facturation.php" class="menu-item active">
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
                        <form class="d-flex search-bar" method="GET" action="facturation.php">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Rechercher une facture..." name="search" value="<?php echo $search_term ?? ''; ?>">
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
                        <h1>Gestion de la facturation</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="AcceuilGerant.php">Application</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Facturation</li>
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
                    
                    <!-- Statistiques de facturation -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stats-card bg-light-blue">
                                <div class="card-body">
                                    <h6 class="card-title text-secondary">Total factures</h6>
                                    <h3 class="card-text"><?php echo count($factures); ?></h3>
                                    <p class="card-text"><small class="text-muted"><i class="fas fa-file-invoice"></i> Factures émises</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card bg-light-green">
                                <div class="card-body">
                                    <h6 class="card-title text-secondary">Factures payées</h6>
                                    <h3 class="card-text">
                                        <?php 
                                        $facturesPayees = array_filter($factures, function($facture) {
                                            return $facture['statut'] === 'payée';
                                        });
                                        echo count($facturesPayees);
                                        ?>
                                    </h3>
                                    <p class="card-text"><small class="text-muted"><i class="fas fa-check-circle"></i> Complètement réglées</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card bg-light-orange">
                                <div class="card-body">
                                    <h6 class="card-title text-secondary">En attente</h6>
                                    <h3 class="card-text">
                                        <?php 
                                        $facturesEnAttente = array_filter($factures, function($facture) {
                                            return $facture['statut'] === 'en_attente';
                                        });
                                        echo count($facturesEnAttente);
                                        ?>
                                    </h3>
                                    <p class="card-text"><small class="text-muted"><i class="fas fa-clock"></i> Paiement en attente</small></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card bg-light-purple">
                                <div class="card-body">
                                    <h6 class="card-title text-secondary">Montant total</h6>
                                    <h3 class="card-text">
                                        <?php 
                                        $montantTotal = array_reduce($factures, function($total, $facture) {
                                            return $total + $facture['montant_total'];
                                        }, 0);
                                        echo number_format($montantTotal, 0, ',', ' ') . ' FCFA';
                                        ?>
                                    </h3>
                                    <p class="card-text"><small class="text-muted"><i class="fas fa-money-bill-wave"></i> Toutes factures</small></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Liste des factures -->
                    <div class="card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Liste des factures</h5>
                            <div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">
                                    <i class="fas fa-plus me-2"></i>Créer une facture
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="btn-group" role="group">
                                    <a href="facturation.php" class="btn <?php echo !$statut_filter ? 'btn-primary' : 'btn-outline-primary'; ?>">Toutes</a>
                                    <a href="facturation.php?statut=en_attente" class="btn <?php echo $statut_filter === 'en_attente' ? 'btn-primary' : 'btn-outline-primary'; ?>">En attente</a>
                                    <a href="facturation.php?statut=payée" class="btn <?php echo $statut_filter === 'payée' ? 'btn-primary' : 'btn-outline-primary'; ?>">Payées</a>
                                    <a href="facturation.php?statut=annulée" class="btn <?php echo $statut_filter === 'annulée' ? 'btn-primary' : 'btn-outline-primary'; ?>">Annulées</a>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="invoicesTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>N° Facture</th>
                                            <th>Client</th>
                                            <th>Réservation</th>
                                            <th>Date émission</th>
                                            <th>Date échéance</th>
                                            <th>Montant</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($factures)): ?>
                                            <?php foreach ($factures as $facture): ?>
                                                <?php
                                                // Récupérer les informations du client
                                                $client = $clientModel->getClientById($facture['client_id']);
                                                
                                                // Définir la classe et le texte du statut
                                                $statusClass = '';
                                                $statusText = '';
                                                
                                                switch ($facture['statut']) {
                                                    case 'en_attente':
                                                        $statusClass = 'bg-warning text-dark';
                                                        $statusText = 'En attente';
                                                        break;
                                                    case 'payée':
                                                        $statusClass = 'bg-success';
                                                        $statusText = 'Payée';
                                                        break;
                                                    case 'annulée':
                                                        $statusClass = 'bg-danger';
                                                        $statusText = 'Annulée';
                                                        break;
                                                    default:
                                                        $statusClass = 'bg-secondary';
                                                        $statusText = 'Inconnue';
                                                }
                                                ?>
                                                <tr>
                                                    <td>FACT-<?php echo str_pad($facture['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                                    <td><?php echo $client ? ($client['prenom'] . ' ' . $client['nom']) : 'Client inconnu'; ?></td>
                                                    <td>RES-<?php echo str_pad($facture['reservation_id'], 5, '0', STR_PAD_LEFT); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($facture['date_emission'])); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($facture['date_echeance'])); ?></td>
                                                    <td><?php echo number_format($facture['montant_total'], 0, ',', ' ') . ' FCFA'; ?></td>
                                                    <td><span class="badge <?php echo $statusClass; ?> status-badge"><?php echo $statusText; ?></span></td>
                                                    <td class="action-buttons">
                                                        <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#viewInvoiceModal" data-id="<?php echo $facture['id']; ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-success" onclick="printInvoice(<?php echo $facture['id']; ?>)">
                                                            <i class="fas fa-print"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-primary" onclick="downloadInvoice(<?php echo $facture['id']; ?>)">
                                                            <i class="fas fa-download"></i>
                                                        </button>
                                                        <?php if ($facture['statut'] === 'en_attente'): ?>
                                                            <button class="btn btn-sm btn-warning text-white" data-bs-toggle="modal" data-bs-target="#markAsPaidModal" data-id="<?php echo $facture['id']; ?>">
                                                                <i class="fas fa-check-circle"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center">Aucune facture trouvée</td>
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
    
    <!-- Modal Créer une facture -->
    <div class="modal fade" id="createInvoiceModal" tabindex="-1" aria-labelledby="createInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createInvoiceModalLabel">Créer une nouvelle facture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="../Controller/FactureController.php" id="createInvoiceForm">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="mb-3">
                            <label for="reservation_id" class="form-label">Réservation</label>
                            <select class="form-select" name="reservation_id" id="reservation_id" required onchange="updateInvoiceDetails(this)">
                                <option value="">Sélectionnez une réservation</option>
                                <?php foreach ($reservationsSansFacture as $reservation): ?>
                                    <?php
                                    $client = $clientModel->getClientByUtilisateurId($reservation['utilisateur_id']);
                                    $vehicule = $voitureModel->getVoitureById($reservation['voiture_id']);
                                    
                                    if (!$client || !$vehicule) {
                                        continue;
                                    }
                                    ?>
                                    <option value="<?php echo $reservation['id']; ?>" 
                                            data-client-id="<?php echo $client['id']; ?>" 
                                            data-montant="<?php echo $reservation['prix_total']; ?>">
                                        RES-<?php echo str_pad($reservation['id'], 5, '0', STR_PAD_LEFT); ?> - 
                                        <?php echo $client['prenom'] . ' ' . $client['nom']; ?> - 
                                        <?php echo $vehicule['marque'] . ' ' . $vehicule['modele']; ?> - 
                                        <?php echo number_format($reservation['prix_total'], 0, ',', ' ') . ' FCFA'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <script>
                            function updateInvoiceDetails(selectElement) {
                                const selectedOption = selectElement.options[selectElement.selectedIndex];
                                const clientId = selectedOption.getAttribute('data-client-id');
                                const montant = selectedOption.getAttribute('data-montant');
                                
                                document.getElementById('client_id').value = clientId;
                                document.getElementById('montant_total').value = montant;
                                
                                console.log('Client ID set to:', clientId);
                                console.log('Montant set to:', montant);
                            }
                            </script>
                        </div>
                        
                        <input type="hidden" name="client_id" id="client_id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_emission" class="form-label">Date d'émission</label>
                                    <input type="date" class="form-control" name="date_emission" id="date_emission" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_echeance" class="form-label">Date d'échéance</label>
                                    <input type="date" class="form-control" name="date_echeance" id="date_echeance" value="<?php echo date('Y-m-d', strtotime('+15 days')); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="montant_total" class="form-label">Montant total</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" name="montant_total" id="montant_total" required readonly>
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" id="notes" rows="3"></textarea>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Créer la facture</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php if (isset($_SESSION['success']) && strpos($_SESSION['success'], 'email') !== false): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-envelope me-2"></i> Un e-mail de confirmation a été envoyé au client.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Modal Visualiser une facture -->
    <div class="modal fade" id="viewInvoiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de la facture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="invoice-container" class="invoice-template">
                        <div class="invoice-header d-flex justify-content-between align-items-start">
                            <div class="company-details">
                                <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740850713/Grey_and_Black2_Car_Rental_Service_Logo_nrbxc0.png" alt="NDAAMAR" class="company-logo">
                                <h4>NDAAMAR Location de voitures</h4>
                                <p>123 Avenue de la République<br>Dakar, Sénégal</p>
                                <p>Tel: (+221) 33 123 45 67<br>Email: contact@ndaamar.com</p>
                            </div>
                            <div class="text-end">
                                <h1 class="invoice-title">FACTURE</h1>
                                <p class="mb-1"><strong>N° Facture:</strong> <span id="viewInvoiceNumber">FACT-00000</span></p>
                                <p class="mb-1"><strong>Date d'émission:</strong> <span id="viewInvoiceDate">00/00/0000</span></p>
                                <p><strong>Date d'échéance:</strong> <span id="viewInvoiceDueDate">00/00/0000</span></p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="client-details">
                                    <h5>Facturé à:</h5>
                                    <h6 id="viewClientName">Prénom Nom</h6>
                                    <p id="viewClientAddress">Adresse du client</p>
                                    <p id="viewClientEmail">email@client.com</p>
                                    <p id="viewClientPhone">Téléphone du client</p>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="invoice-details">
                                    <h5>Détails de la réservation:</h5>
                                    <p><strong>N° Réservation:</strong> <span id="viewReservationNumber">RES-00000</span></p>
                                    <p><strong>Véhicule:</strong> <span id="viewVehicleDetails">Marque Modèle</span></p>
                                    <p><strong>Période:</strong> <span id="viewRentalPeriod">00/00/0000 - 00/00/0000</span></p>
                            <p><strong>Durée:</strong> <span id="viewRentalDuration">0 jour(s)</span></p>
                        </div>
                    </div>
                </div>
                
                <div class="invoice-items mt-4">
                    <table class="table invoice-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-end">Prix unitaire</th>
                                <th class="text-end">Quantité</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody id="invoiceItemsTable">
                            <tr>
                                <td>Location de véhicule - Marque Modèle</td>
                                <td class="text-end">0 FCFA</td>
                                <td class="text-end">0 jour(s)</td>
                                <td class="text-end">0 FCFA</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Sous-total:</strong></td>
                                <td class="text-end" id="viewSubtotal">0 FCFA</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>TVA (18%):</strong></td>
                                <td class="text-end" id="viewTax">0 FCFA</td>
                            </tr>
                            <tr class="invoice-total">
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end" id="viewTotal">0 FCFA</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div class="payment-status mb-4">
                    <h5>Statut de paiement</h5>
                    <div id="viewPaymentStatus" class="badge bg-warning text-dark p-2">En attente</div>
                </div>
                
                <div id="viewInvoiceNotes" class="mb-4">
                    <h5>Notes</h5>
                    <p>Notes additionnelles sur la facture...</p>
                </div>
                
                <div class="invoice-footer text-center">
                    <p>Merci de votre confiance!</p>
                    <p>Pour toute question concernant cette facture, veuillez contacter notre service client.</p>
                    <p>NDAAMAR Location de voitures - SIRET: 123 456 789 00010 - RC: 123456789</p>
                </div>
            </div>
        </div>
        <div class="modal-footer no-print">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            <button type="button" class="btn btn-success" onclick="printCurrentInvoice()">
                <i class="fas fa-print me-2"></i>Imprimer
            </button>
            <button type="button" class="btn btn-primary" onclick="downloadCurrentInvoice()">
                <i class="fas fa-download me-2"></i>Télécharger
            </button>
            <button type="button" class="btn btn-warning text-white" id="markAsPaidButton" data-id="">
                <i class="fas fa-check-circle me-2"></i>Marquer comme payée
            </button>
        </div>
    </div>
</div>
</div>

<!-- Modal Marquer comme payée -->
<div class="modal fade" id="markAsPaidModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Marquer la facture comme payée</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
<p>Êtes-vous sûr de vouloir marquer cette facture comme payée ?</p>
<p>Cette action enregistrera un paiement pour cette facture à la date d'aujourd'hui.</p>

<form method="POST" action="../Controller/FactureController.php" id="markAsPaidForm">
<input type="hidden" name="action" value="markAsPaid">
<input type="hidden" name="facture_id" id="paid_facture_id">

<div class="mb-3">
    <label for="methode_paiement" class="form-label">Méthode de paiement</label>
    <select class="form-select" name="methode_paiement" id="methode_paiement" required>
        <option value="espèces">Espèces</option>
        <option value="carte bancaire">Carte bancaire</option>
        <option value="virement">Virement bancaire</option>
        <option value="mobile money">Mobile Money</option>
        <option value="chèque">Chèque</option>
    </select>
</div>

<div class="mb-3">
    <label for="reference_paiement" class="form-label">Référence de paiement (optionnel)</label>
    <input type="text" class="form-control" name="reference_paiement" id="reference_paiement">
    <small class="form-text text-muted">Numéro de transaction, numéro de chèque, etc.</small>
</div>
</form>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
<button type="button" class="btn btn-success" onclick="document.getElementById('markAsPaidForm').submit()">
    <i class="fas fa-check-circle me-2"></i>Confirmer le paiement
</button>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>
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

// Fonction pour calculer le nombre de jours entre deux dates
function getDaysDifference(dateStart, dateEnd) {
    const start = new Date(dateStart);
    const end = new Date(dateEnd);
    const diffTime = Math.abs(end - start);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
}

// Fonction pour charger les détails d'une facture
function loadInvoiceDetails(invoiceId) {
    $.ajax({
        url: '../Controller/FactureController.php',
        type: 'GET',
        data: { action: 'get', id: invoiceId },
        dataType: 'json',
        success: function(invoice) {
            // Mise à jour de l'entête de facture
            $('#viewInvoiceNumber').text('FACT-' + invoice.id.toString().padStart(5, '0'));
            $('#viewInvoiceDate').text(formatDate(invoice.date_emission));
            $('#viewInvoiceDueDate').text(formatDate(invoice.date_echeance));
            
            // Mise à jour des informations client
            $('#viewClientName').text(invoice.client.prenom + ' ' + invoice.client.nom);
            $('#viewClientAddress').text(invoice.client.adresse || 'Adresse non renseignée');
            $('#viewClientEmail').text(invoice.client.email);
            $('#viewClientPhone').text(invoice.client.telephone || 'Téléphone non renseigné');
            
            // Mise à jour des détails de réservation
            $('#viewReservationNumber').text('RES-' + invoice.reservation_id.toString().padStart(5, '0'));
            $('#viewVehicleDetails').text(invoice.vehicule.marque + ' ' + invoice.vehicule.modele);
            $('#viewRentalPeriod').text(formatDate(invoice.reservation.date_debut) + ' - ' + formatDate(invoice.reservation.date_fin));
            
            const days = getDaysDifference(invoice.reservation.date_debut, invoice.reservation.date_fin);
            $('#viewRentalDuration').text(days + ' jour(s)');
            
            // Mise à jour des items de facture
            const pricePerDay = invoice.reservation.prix_total / days;
            const subtotal = invoice.reservation.prix_total;
            const tax = subtotal * 0.18; // TVA 18%
            const total = subtotal + tax;
            
            let itemsHtml = `
                <tr>
                    <td>Location de véhicule - ${invoice.vehicule.marque} ${invoice.vehicule.modele}</td>
                    <td class="text-end">${formatPrice(pricePerDay)}</td>
                    <td class="text-end">${days} jour(s)</td>
                    <td class="text-end">${formatPrice(subtotal)}</td>
                </tr>
            `;
            
            $('#invoiceItemsTable').html(itemsHtml);
            $('#viewSubtotal').text(formatPrice(subtotal));
            $('#viewTax').text(formatPrice(tax));
            $('#viewTotal').text(formatPrice(total));
            
            // Mise à jour du statut de paiement
            let statusClass = '';
            let statusText = '';
            
            switch (invoice.statut) {
                case 'en_attente':
                    statusClass = 'bg-warning text-dark';
                    statusText = 'En attente';
                    $('#markAsPaidButton').show();
                    break;
                case 'payée':
                    statusClass = 'bg-success';
                    statusText = 'Payée';
                    $('#markAsPaidButton').hide();
                    break;
                case 'annulée':
                    statusClass = 'bg-danger';
                    statusText = 'Annulée';
                    $('#markAsPaidButton').hide();
                    break;
                default:
                    statusClass = 'bg-secondary';
                    statusText = 'Inconnue';
                    $('#markAsPaidButton').hide();
            }
            
            $('#viewPaymentStatus').removeClass().addClass('badge p-2 ' + statusClass).text(statusText);
            
            // Mise à jour des notes
            if (invoice.notes) {
                $('#viewInvoiceNotes').html(`<h5>Notes</h5><p>${invoice.notes}</p>`);
            } else {
                $('#viewInvoiceNotes').html('<h5>Notes</h5><p>Aucune note supplémentaire.</p>');
            }
            
            // Configurer le bouton de paiement
            $('#markAsPaidButton').data('id', invoice.id);
        },
        error: function(xhr, status, error) {
            console.error('Erreur lors de la récupération des détails de la facture:', error);
            alert('Erreur lors de la récupération des détails de la facture.');
        }
    });
}

// Fonction pour imprimer la facture courante
function printCurrentInvoice() {
    window.print();
}

// Fonction pour télécharger la facture courante en PDF
function downloadCurrentInvoice() {
    const element = document.getElementById('invoice-container');
    const invoiceNumber = document.getElementById('viewInvoiceNumber').textContent;
    
    html2canvas(element, {
        onrendered: function(canvas) {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'mm', 'a4');
            const width = pdf.internal.pageSize.getWidth();
            const height = (canvas.height * width) / canvas.width;
            
            pdf.addImage(imgData, 'PNG', 0, 0, width, height);
            pdf.save(invoiceNumber + '.pdf');
        }
    });
}

// Fonction pour imprimer une facture spécifique
function printInvoice(invoiceId) {
    // Ouvrir la modal
    $('#viewInvoiceModal').modal('show');
    
    // Charger les détails de la facture
    loadInvoiceDetails(invoiceId);
    
    // Attendre un peu pour que la modal soit chargée
    setTimeout(function() {
        printCurrentInvoice();
    }, 1000);
}

// Fonction pour télécharger une facture spécifique
function downloadInvoice(invoiceId) {
    // Ouvrir la modal
    $('#viewInvoiceModal').modal('show');
    
    // Charger les détails de la facture
    loadInvoiceDetails(invoiceId);
    
    // Attendre un peu pour que la modal soit chargée
    setTimeout(function() {
        downloadCurrentInvoice();
    }, 1000);
}

// Document ready
$(document).ready(function() {
    // Initialisation de DataTables avec traduction en français
    $('#invoicesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
        },
        order: [[0, 'desc']] // Trier par ID (plus récent d'abord)
    });
    
    // Gestionnaire d'événements pour le changement de réservation
    $('#reservation_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const clientId = selectedOption.data('client-id');
        const montant = selectedOption.data('montant');
        
        console.log('Client ID:', clientId);
        console.log('Montant:', montant);
        
        $('#client_id').val(clientId);
        $('#montant_total').val(montant);
    });
    
    // Gestionnaire d'événements pour l'ouverture de la modal de visualisation
    $('#viewInvoiceModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        if (button.length) {
            const invoiceId = button.data('id');
            loadInvoiceDetails(invoiceId);
        }
    });
    
    // Gestionnaire d'événements pour l'ouverture de la modal de paiement
    $('#markAsPaidModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const invoiceId = button.data('id');
        $('#paid_facture_id').val(invoiceId);
    });
    
    // Gestionnaire d'événements pour le bouton dans la modal de visualisation
    $('#markAsPaidButton').on('click', function() {
        const invoiceId = $(this).data('id');
        $('#paid_facture_id').val(invoiceId);
        $('#viewInvoiceModal').modal('hide');
        $('#markAsPaidModal').modal('show');
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