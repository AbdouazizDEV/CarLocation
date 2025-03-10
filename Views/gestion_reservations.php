<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_start();
    require_once __DIR__ . "/../Models/voiture.php";
    require_once __DIR__ . "/../Models/client.php";
    require_once __DIR__ . "/../Models/Reservation.php";

    // Vérifier si l'utilisateur est connecté et est un gérant
    if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'gérant') {
        header('Location: login.php');
        exit();
    }

    // Instancier les modèles
    $reservationModel = new Reservation();
    $voitureModel = new Voiture();
    $clientModel = new client();
    
    // Récupérer toutes les réservations
    $reservations = $reservationModel->getAllReservations();

    // Filtrer les réservations par statut si demandé
    $statut_filter = $_GET['statut'] ?? null;
    if ($statut_filter) {
        $reservations = array_filter($reservations, function($reservation) use ($statut_filter) {
            return $reservation['statut'] === $statut_filter;
        });
    }
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestion des réservations - NDAAMAR</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
        <link rel="stylesheet" href="../Public/CSS/ReservationStyle.css">
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
                        <a href="gestion_reservations.php" class="menu-item active">
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
                    
                    <!-- Content -->
                    <div class="content-container">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1>Gestion des réservations</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="AcceuilGerant.php">Application</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Gestion des réservations</li>
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
                                <h5 class="mb-0">Liste des réservations</h5>
                                <div>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReservationModal">
                                        <i class="fas fa-plus me-2"></i>Nouvelle réservation
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="btn-group" role="group">
                                        <a href="gestion_reservations.php" class="btn <?php echo !$statut_filter ? 'btn-primary' : 'btn-outline-primary'; ?>">Toutes</a>
                                        <a href="gestion_reservations.php?statut=en_attente" class="btn <?php echo $statut_filter === 'en_attente' ? 'btn-primary' : 'btn-outline-primary'; ?>">En attente</a>
                                        <a href="gestion_reservations.php?statut=confirmee" class="btn <?php echo $statut_filter === 'confirmee' ? 'btn-primary' : 'btn-outline-primary'; ?>">Confirmées</a>
                                        <a href="gestion_reservations.php?statut=en_cours" class="btn <?php echo $statut_filter === 'en_cours' ? 'btn-primary' : 'btn-outline-primary'; ?>">En cours</a>
                                        <a href="gestion_reservations.php?statut=terminee" class="btn <?php echo $statut_filter === 'terminee' ? 'btn-primary' : 'btn-outline-primary'; ?>">Terminées</a>
                                        <a href="gestion_reservations.php?statut=annulee" class="btn <?php echo $statut_filter === 'annulee' ? 'btn-primary' : 'btn-outline-primary'; ?>">Annulées</a>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table id="reservationsTable" class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Client</th>
                                                <th>Véhicule</th>
                                                <th>Date début</th>
                                                <th>Date fin</th>
                                                <th>Durée</th>
                                                <th>Prix total</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($reservations)): ?>
                                                <?php foreach ($reservations as $reservation): ?>
                                                    <?php
                                                    // Récupérer les détails du véhicule et du client
                                                    $vehicule = $voitureModel->getVoitureById($reservation['voiture_id']);
                                                    $client = $clientModel->getClientById($reservation['utilisateur_id']);
                                                    
                                                    // Calculer la durée en jours
                                                    $dateDebut = new DateTime($reservation['date_debut']);
                                                    $dateFin = new DateTime($reservation['date_fin']);
                                                    $duree = $dateDebut->diff($dateFin)->days;
                                                    
                                                    // Définir la classe et le texte du statut
                                                    $statusClass = '';
                                                    switch ($reservation['statut']) {
                                                        case 'en_attente':
                                                            $statusClass = 'bg-warning text-dark';
                                                            $statusText = 'En attente';
                                                            break;
                                                        case 'confirmee':
                                                            $statusClass = 'bg-info text-white';
                                                            $statusText = 'Confirmée';
                                                            break;
                                                        case 'en_cours':
                                                            $statusClass = 'bg-primary';
                                                            $statusText = 'En cours';
                                                            break;
                                                        case 'terminee':
                                                            $statusClass = 'bg-success';
                                                            $statusText = 'Terminée';
                                                            break;
                                                        case 'annulee':
                                                            $statusClass = 'bg-danger';
                                                            $statusText = 'Annulée';
                                                            break;
                                                        default:
                                                            $statusClass = 'bg-secondary';
                                                            $statusText = 'Inconnue';
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $reservation['id']; ?></td>
                                                        <td><?php echo $client ? ($client['prenom'] . ' ' . $client['nom']) : 'Client inconnu'; ?></td>
                                                        <td><?php echo $vehicule ? ($vehicule['marque'] . ' ' . $vehicule['modele']) : 'Véhicule inconnu'; ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($reservation['date_debut'])); ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($reservation['date_fin'])); ?></td>
                                                        <td><?php echo $duree . ' jour' . ($duree > 1 ? 's' : ''); ?></td>
                                                        <td><?php echo number_format($reservation['prix_total'], 0, ',', ' ') . ' FCFA'; ?></td>
                                                        <td><span class="badge <?php echo $statusClass; ?> status-badge"><?php echo $statusText; ?></span></td>
                                                        <td class="action-buttons">
                                                            <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#viewReservationModal" data-id="<?php echo $reservation['id']; ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-warning text-white" data-bs-toggle="modal" data-bs-target="#editReservationModal" data-id="<?php echo $reservation['id']; ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <?php if ($reservation['statut'] === 'en_attente'): ?>
                                                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#confirmReservationModal" data-id="<?php echo $reservation['id']; ?>">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                            <?php if (in_array($reservation['statut'], ['en_attente', 'confirmee'])): ?>
                                                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#cancelReservationModal" data-id="<?php echo $reservation['id']; ?>">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="9" class="text-center">Aucune réservation trouvée</td>
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
        
        <!-- Modal Nouvelle réservation -->
        <div class="modal fade" id="addReservationModal" tabindex="-1" aria-labelledby="addReservationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addReservationModalLabel">Nouvelle réservation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="../Controller/ReservationPourLesClient.php" id="addReservationForm">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="client" class="form-label">Client</label>
                                        <select class="form-select" name="client_id" id="client" required>
                                            <option value="">Sélectionnez un client</option>
                                            <?php 
                                            $clients = $clientModel->getAllClients();
                                            foreach ($clients as $client): 
                                            ?>
                                                <option value="<?php echo $client['id']; ?>"><?php echo $client['prenom'] . ' ' . $client['nom'] . ' (' . $client['email'] . ')'; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="vehicule" class="form-label">Véhicule</label>
                                        <select class="form-select" name="voiture_id" id="vehicule" required>
                                            <option value="">Sélectionnez un véhicule</option>
                                            <?php 
                                            $vehicules = $voitureModel->getVoituresByStatus('disponible');
                                            foreach ($vehicules as $vehicule): 
                                            ?>
                                                <option value="<?php echo $vehicule['id']; ?>" data-price="<?php echo $vehicule['prix_location']; ?>">
                                                    <?php echo $vehicule['marque'] . ' ' . $vehicule['modele'] . ' (' . $vehicule['prix_location'] . ' FCFA/jour)'; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="date_debut" class="form-label">Date de début</label>
                                        <input type="date" class="form-control" name="date_debut" id="date_debut" min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="date_fin" class="form-label">Date de fin</label>
                                        <input type="date" class="form-control" name="date_fin" id="date_fin" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="date-range-info">
                                Durée de la location: <span id="daysCount" class="days-count">0 jour(s)</span>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" id="notes" rows="3"></textarea>
                            </div>
                            
                            <div class="price-calculation">
                                <h6>Détail du prix:</h6>
                                <div class="price-row">
                                    <span>Prix du véhicule:</span>
                                    <span id="vehiclePrice">0 FCFA/jour</span>
                                </div>
                                <div class="price-row">
                                    <span>Durée:</span>
                                    <span id="durationDays">0 jour(s)</span>
                                </div>
                                <div class="price-row price-total">
                                    <span>Prix total:</span>
                                    <span id="totalPrice">0 FCFA</span>
                                </div>
                                <input type="hidden" name="prix_total" id="prix_total_input" value="0">
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">Créer la réservation</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
document.addEventListener('DOMContentLoaded', function() {
    const vehicleSelect = document.getElementById('vehicule');
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');
    const daysCount = document.getElementById('daysCount');
    const durationDays = document.getElementById('durationDays');
    const vehiclePrice = document.getElementById('vehiclePrice');
    const totalPrice = document.getElementById('totalPrice');
    const prixTotalInput = document.getElementById('prix_total_input');
    
    // Fonction pour calculer la durée et le prix total
    function calculateTotalPrice() {
        // Vérifier si les dates sont valides
        if (dateDebut.value && dateFin.value) {
            // Convertir les dates en objets Date
            const startDate = new Date(dateDebut.value);
            const endDate = new Date(dateFin.value);
            
            // Calculer la différence en jours
            const timeDiff = endDate - startDate;
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
            
            if (daysDiff > 0) {
                // Mettre à jour l'affichage de la durée
                daysCount.textContent = daysDiff + ' jour(s)';
                durationDays.textContent = daysDiff + ' jour(s)';
                
                // Récupérer le prix du véhicule sélectionné
                const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    const pricePerDay = parseFloat(selectedOption.dataset.price);
                    
                    // Mettre à jour l'affichage du prix par jour
                    vehiclePrice.textContent = pricePerDay.toLocaleString() + ' FCFA/jour';
                    
                    // Calculer et afficher le prix total
                    const total = pricePerDay * daysDiff;
                    totalPrice.textContent = total.toLocaleString() + ' FCFA';
                    
                    // Mettre à jour le champ caché pour la soumission du formulaire
                    prixTotalInput.value = total;
                }
            } else {
                // Si la date de fin est avant la date de début
                daysCount.textContent = '0 jour(s)';
                durationDays.textContent = '0 jour(s)';
                totalPrice.textContent = '0 FCFA';
                prixTotalInput.value = 0;
            }
        }
    }
    
    // Ajouter des écouteurs d'événements pour les changements de date et de véhicule
    dateDebut.addEventListener('change', calculateTotalPrice);
    dateFin.addEventListener('change', calculateTotalPrice);
    vehicleSelect.addEventListener('change', calculateTotalPrice);
    
    // Validation du formulaire avant soumission
    document.getElementById('addReservationForm').addEventListener('submit', function(event) {
        const total = parseFloat(prixTotalInput.value);
        
        if (isNaN(total) || total <= 0) {
            event.preventDefault();
            alert('Le prix total doit être supérieur à zéro. Veuillez vérifier les dates et le véhicule sélectionné.');
        }
    });
});
</script>
        <!-- Modal Voir les détails de la réservation -->
        <div class="modal fade" id="viewReservationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Détails de la réservation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Informations de la réservation</h5>
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <td>Numéro de réservation:</td>
                                            <td id="viewReservationId">Chargement...</td>
                                        </tr>
                                        <tr>
                                            <td>Date de réservation:</td>
                                            <td id="viewReservationDate">Chargement...</td>
                                        </tr>
                                        <tr>
                                            <td>Période:</td>
                                            <td id="viewReservationPeriod">Chargement...</td>
                                        </tr>
                                        <tr>
                                            <td>Durée:</td>
                                            <td id="viewReservationDuration">Chargement...</td>
                                        </tr>
                                        <tr>
                                            <td>Prix total:</td>
                                            <td id="viewReservationPrice">Chargement...</td>
                                        </tr>
                                        <tr>
                                            <td>Statut:</td>
                                            <td id="viewReservationStatus">Chargement...</td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <h5>Notes</h5>
                                <p id="viewReservationNotes">Chargement...</p>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Client</h5>
                                    </div>
                                    <div class="card-body">
                                        <h6 id="viewClientName">Chargement...</h6>
                                        <p class="mb-1"><i class="fas fa-envelope me-2"></i><span id="viewClientEmail"><?php echo $client['email'] ; ?></span></p>
                                        <p class="mb-1"><i class="fas fa-phone me-2"></i><span id="viewClientPhone"><?php echo $client['telephone'] ; ?></span></p>
                                        <p class="mb-0"><i class="fas fa-map-marker-alt me-2"></i><span id="viewClientAddress"><?php echo $client['adresse'] ; ?></span></p>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Véhicule</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <img id="viewVehicleImage" src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg" alt="Véhicule" class="car-image">
                                            </div>
                                            <div>
                                                ⅛<?php $Voiture= $voitureModel-> getAllVoitures();?>
                                                <h6 id="viewVehicleName">Chargement...</h6>
                                                <p class="mb-1"><strong>Catégorie:</strong> <span id="viewVehicleCategory"><?php echo $Voiture['categorie'] ?></span></p>
                                                <p class="mb-0"><strong>Prix par jour:</strong> <span id="viewVehiclePrice"><?php echo $Voiture['prix_location'] ?></span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="button" class="btn btn-primary" id="editReservationBtn">Modifier</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Modifier la réservation -->
        <div class="modal fade" id="editReservationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier la réservation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="../Controller/ReservationController.php" id="editReservationForm">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="reservation_id" id="edit_reservation_id">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="edit_client" class="form-label">Client</label>
                                        <select class="form-select" name="client_id" id="edit_client" required>
                                            <option value="">Sélectionnez un client</option>
                                            <?php 
                                            $clients = $clientModel->getAllClients();
                                            foreach ($clients as $client): 
                                            ?>
                                                <option value="<?php echo $client['id']; ?>"><?php echo $client['prenom'] . ' ' . $client['nom'] . ' (' . $client['email'] . ')'; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="edit_vehicule" class="form-label">Véhicule</label>
                                        <select class="form-select" name="voiture_id" id="edit_vehicule" required>
                                            <option value="">Sélectionnez un véhicule</option>
                                            <?php 
                                            // Récupérer tous les véhicules pour l'édition
                                            $allVehicules = $voitureModel->getAllVoitures();
                                            foreach ($allVehicules as $vehicule): 
                                            ?>
                                                <option value="<?php echo $vehicule['id']; ?>" data-price="<?php echo $vehicule['prix_location']; ?>">
                                                    <?php echo $vehicule['marque'] . ' ' . $vehicule['modele'] . ' (' . $vehicule['prix_location'] . ' FCFA/jour)'; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="edit_date_debut" class="form-label">Date de début</label>
                                        <input type="date" class="form-control" name="date_debut" id="edit_date_debut" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="edit_date_fin" class="form-label">Date de fin</label>
                                        <input type="date" class="form-control" name="date_fin" id="edit_date_fin" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="date-range-info">
                                Durée de la location: <span id="edit_daysCount" class="days-count">0 jour(s)</span>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="edit_statut" class="form-label">Statut</label>
                                <select class="form-select" name="statut" id="edit_statut" required>
                                    <option value="en_attente">En attente</option>
                                    <option value="confirmee">Confirmée</option>
                                    <option value="en_cours">En cours</option>
                                    <option value="terminee">Terminée</option>
                                    <option value="annulee">Annulée</option>
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="edit_notes" class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" id="edit_notes" rows="3"></textarea>
                            </div>
                            
                            <div class="price-calculation">
                                <h6>Détail du prix:</h6>
                                <div class="price-row">
                                    <span>Prix du véhicule:</span>
                                    <span id="edit_vehiclePrice">0 FCFA/jour</span>
                                </div>
                                <div class="price-row">
                                    <span>Durée:</span>
                                    <span id="edit_durationDays">0 jour(s)</span>
                                </div>
                                <div class="price-row price-total">
                                    <span>Prix total:</span>
                                    <span id="edit_totalPrice">0 FCFA</span>
                                </div>
                                <input type="hidden" name="prix_total" id="edit_prix_total_input" value="0">
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
        
        <!-- Modal Confirmer la réservation -->
        <div class="modal fade" id="confirmReservationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmer la réservation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir confirmer cette réservation ?</p>
                        <p>Le véhicule sera marqué comme loué pour la période réservée.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <form method="POST" action="../Controller/ReservationController.php">
                            <input type="hidden" name="action" value="confirm">
                            <input type="hidden" name="reservation_id" id="confirm_reservation_id">
                            <button type="submit" class="btn btn-success">Confirmer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Annuler la réservation -->
        <div class="modal fade" id="cancelReservationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Annuler la réservation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Êtes-vous sûr de vouloir annuler cette réservation ?</p>
                        <form method="POST" action="../Controller/ReservationController.php" id="cancelReservationForm">
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="reservation_id" id="cancel_reservation_id">
                            
                            <div class="form-group mb-3">
                                <label for="cancel_reason" class="form-label">Motif d'annulation</label>
                                <select class="form-select" name="cancel_reason" id="cancel_reason" required>
                                    <option value="">Sélectionnez un motif</option>
                                    <option value="client_request">Demande du client</option>
                                    <option value="vehicle_unavailable">Véhicule indisponible</option>
                                    <option value="payment_issue">Problème de paiement</option>
                                    <option value="other">Autre</option>
                                </select>
                            </div>
                            
                            <div class="form-group mb-3" id="other_reason_container" style="display: none;">
                                <label for="other_reason" class="form-label">Précisez</label>
                                <textarea class="form-control" name="other_reason" id="other_reason" rows="2"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="button" class="btn btn-danger" onclick="document.getElementById('cancelReservationForm').submit();">Annuler la réservation</button>
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
            // Fonction pour calculer le nombre de jours entre deux dates
            function getDaysDifference(dateStart, dateEnd) {
                const start = new Date(dateStart);
                const end = new Date(dateEnd);
                const diffTime = Math.abs(end - start);
                return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            }
            
            // Fonction pour formater le prix
            function formatPrice(price) {
                return new Intl.NumberFormat('fr-FR').format(price) + ' FCFA';
            }
            
            // Fonction pour mettre à jour le calcul du prix pour le formulaire d'ajout
            function updatePriceCalculation() {
                const vehicleSelect = document.getElementById('vehicule');
                const dateDebut = document.getElementById('date_debut').value;
                const dateFin = document.getElementById('date_fin').value;
                
                if (vehicleSelect.value && dateDebut && dateFin) {
                    const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
                    const pricePerDay = parseInt(selectedOption.dataset.price);
                    const days = getDaysDifference(dateDebut, dateFin);
                    
                    document.getElementById('vehiclePrice').textContent = formatPrice(pricePerDay) + '/jour';
                    document.getElementById('durationDays').textContent = days + ' jour(s)';
                    document.getElementById('daysCount').textContent = days + ' jour(s)';
                    
                    const totalPrice = pricePerDay * days;
                    document.getElementById('totalPrice').textContent = formatPrice(totalPrice);
                    document.getElementById('prix_total_input').value = totalPrice;
                }
            }
            
            // Fonction pour mettre à jour le calcul du prix pour le formulaire de modification
            function updateEditPriceCalculation() {
                const vehicleSelect = document.getElementById('edit_vehicule');
                const dateDebut = document.getElementById('edit_date_debut').value;
                const dateFin = document.getElementById('edit_date_fin').value;
                
                if (vehicleSelect.value && dateDebut && dateFin) {
                    const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
                    const pricePerDay = parseInt(selectedOption.dataset.price);
                    const days = getDaysDifference(dateDebut, dateFin);
                    
                    document.getElementById('edit_vehiclePrice').textContent = formatPrice(pricePerDay) + '/jour';
                    document.getElementById('edit_durationDays').textContent = days + ' jour(s)';
                    document.getElementById('edit_daysCount').textContent = days + ' jour(s)';
                    
                    const totalPrice = pricePerDay * days;
                    document.getElementById('edit_totalPrice').textContent = formatPrice(totalPrice);
                    document.getElementById('edit_prix_total_input').value = totalPrice;
                }
            }
            
            // Fonction pour charger les détails de la réservation dans la modal
            function loadReservationDetails(reservationId) {
                // Effectuez une requête AJAX pour récupérer les détails de la réservation
                $.ajax({
                    url: '../Controller/ReservationController.php',
                    type: 'GET',
                    data: { action: 'get', id: reservationId },
                    dataType: 'json',
                    success: function(reservation) {
                        // Mise à jour des informations de la réservation
                        $('#viewReservationId').text('#' + reservation.id);
                        $('#viewReservationDate').text(formatDate(reservation.date_reservation));
                        $('#viewReservationPeriod').text(formatDate(reservation.date_debut) + ' au ' + formatDate(reservation.date_fin));
                        
                        const days = getDaysDifference(reservation.date_debut, reservation.date_fin);
                        $('#viewReservationDuration').text(days + ' jour(s)');
                        
                        $('#viewReservationPrice').text(formatPrice(reservation.prix_total));
                        
                        // Définir le statut
                        let statusClass = '';
                        let statusText = '';
                        
                        switch (reservation.statut) {
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
                        
                        $('#viewReservationStatus').html(`<span class="badge ${statusClass}">${statusText}</span>`);
                        $('#viewReservationNotes').text(reservation.notes || 'Aucune note disponible');
                        
                        // Mise à jour des informations du client
                        $('#viewClientName').text(reservation.client.prenom + ' ' + reservation.client.nom);
                        $('#viewClientEmail').text(reservation.client.email);
                        $('#viewClientPhone').text(reservation.client.telephone || 'Non renseigné');
                        $('#viewClientAddress').text(reservation.client.adresse || 'Non renseignée');
                        
                        // Mise à jour des informations du véhicule
                        $('#viewVehicleName').text(reservation.vehicule.marque + ' ' + reservation.vehicule.modele);
                        $('#viewVehicleCategory').text(reservation.vehicule.categorie);
                        $('#viewVehiclePrice').text(formatPrice(reservation.vehicule.prix_location) + '/jour');
                        
                        if (reservation.vehicule.images) {
                            $('#viewVehicleImage').attr('src', '../' + reservation.vehicule.images);
                        } else {
                            $('#viewVehicleImage').attr('src', 'https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg');
                        }
                        
                        // Configurer le bouton d'édition
                        $('#editReservationBtn').off('click').on('click', function() {
                            $('#viewReservationModal').modal('hide');
                            loadReservationForEdit(reservationId);
                            $('#editReservationModal').modal('show');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Erreur lors de la récupération des détails de la réservation:', error);
                        alert('Erreur lors de la récupération des détails de la réservation.');
                    }
                });
            }
            
            // Fonction pour charger les détails de la réservation dans le formulaire de modification
            function loadReservationForEdit(reservationId) {
                $('#edit_reservation_id').val(reservationId);
                
                $.ajax({
                    url: '../Controller/ReservationController.php',
                    type: 'GET',
                    data: { action: 'get', id: reservationId },
                    dataType: 'json',
                    success: function(reservation) {
                        // Remplir le formulaire avec les données de la réservation
                        $('#edit_client').val(reservation.utilisateur_id);
                        $('#edit_vehicule').val(reservation.voiture_id);
                        $('#edit_date_debut').val(reservation.date_debut);
                        $('#edit_date_fin').val(reservation.date_fin);
                        $('#edit_statut').val(reservation.statut);
                        $('#edit_notes').val(reservation.notes);
                        
                        // Mettre à jour le calcul du prix
                        updateEditPriceCalculation();
                    },
                    error: function(xhr, status, error) {
                        console.error('Erreur lors de la récupération des détails de la réservation:', error);
                        alert('Erreur lors de la récupération des détails de la réservation.');
                    }
                });
            }
            
            // Fonction pour formater une date
            function formatDate(dateString) {
                const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
                return new Date(dateString).toLocaleDateString('fr-FR', options);
            }
            
            // Document ready
            $(document).ready(function() {
                // Initialisation de DataTables avec traduction en français
                $('#reservationsTable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
                    },
                    order: [[0, 'desc']] // Trier par ID (plus récent d'abord)
                });
                
                // Événements pour le calcul du prix dans le formulaire d'ajout
                $('#vehicule, #date_debut, #date_fin').on('change', updatePriceCalculation);
                
                // Événements pour le calcul du prix dans le formulaire de modification
                $('#edit_vehicule, #edit_date_debut, #edit_date_fin').on('change', updateEditPriceCalculation);
                
                // Afficher/masquer le champ "Autre raison" dans le formulaire d'annulation
                $('#cancel_reason').on('change', function() {
                    if ($(this).val() === 'other') {
                        $('#other_reason_container').show();
                    } else {
                        $('#other_reason_container').hide();
                    }
                });
                
                // Gestionnaire d'événements pour l'ouverture de la modal de visualisation
                $('#viewReservationModal').on('show.bs.modal', function(event) {
                    const button = $(event.relatedTarget);
                    const reservationId = button.data('id');
                    loadReservationDetails(reservationId);
                });
                
                // Gestionnaire d'événements pour l'ouverture de la modal de modification
                $('#editReservationModal').on('show.bs.modal', function(event) {
                    const button = $(event.relatedTarget);
                    if (button.length) {
                        const reservationId = button.data('id');
                        loadReservationForEdit(reservationId);
                    }
                });
                
                // Gestionnaire d'événements pour l'ouverture de la modal de confirmation
                $('#confirmReservationModal').on('show.bs.modal', function(event) {
                    const button = $(event.relatedTarget);
                    const reservationId = button.data('id');
                    $('#confirm_reservation_id').val(reservationId);
                });
                
                // Gestionnaire d'événements pour l'ouverture de la modal d'annulation
                $('#cancelReservationModal').on('show.bs.modal', function(event) {
                    const button = $(event.relatedTarget);
                    const reservationId = button.data('id');
                    $('#cancel_reservation_id').val(reservationId);
                    $('#cancel_reason').val('');
                    $('#other_reason').val('');
                    $('#other_reason_container').hide();
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