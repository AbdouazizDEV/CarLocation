<?php
    session_start();
    require_once __DIR__ . "/../Models/voiture.php";
    require_once __DIR__ . "/../Models/offre.php";

    // Instancier les objets nécessaires pour récupérer les données
    $voitureModel = new Voiture();
    $offreModel = new Offre();

    // Récupérer les offres actives
    $offresActives = $offreModel->getActiveOffres();

    // Récupérer toutes les voitures (pour la galerie)
    $voitures = $voitureModel->getAllVoitures();

    // Filtrer les voitures par catégorie si demandé
    $categorie_filter = isset($_GET['categorie']) ? $_GET['categorie'] : null;
    if ($categorie_filter) {
        $voitures = array_filter($voitures, function($voiture) use ($categorie_filter) {
            return strtolower($voiture['categorie']) === strtolower($categorie_filter);
        });
    }

    // Récupérer toutes les catégories disponibles
    $categories = [];
    foreach ($voitures as $voiture) {
        if (!in_array($voiture['categorie'], $categories)) {
            $categories[] = $voiture['categorie'];
        }
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NDAAMAR - Location de Voitures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Public/CSS/visitorStyl.css">
 
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
                    <a href="AcceuilVisiteur.php" class="menu-item active">
                        <i class="fas fa-tag"></i> Offres
                    </a>
                    <a href="login.php" class="menu-item">
                        <i class="fas fa-heart"></i> Favoris
                        <i class="fas fa-lock lock-icon text-muted"></i>
                    </a>
                    <a href="login.php" class="menu-item">
                        <i class="fas fa-calendar-alt"></i> Réservations
                        <i class="fas fa-lock lock-icon text-muted"></i>
                    </a>
                    <a href="login.php" class="menu-item">
                        <i class="fas fa-car"></i> Locations
                        <i class="fas fa-lock lock-icon text-muted"></i>
                    </a>
                    <a href="#" class="menu-item">
                        <i class="fas fa-search"></i> Rechercher
                    </a>
                    <a href="contact.php" class="menu-item">
                        <i class="fas fa-envelope"></i> Contactez-nous
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
                            <a class="nav-link" href="#">
                                <i class="fas fa-bell"></i>
                            </a>
                            <a class="nav-link" href="#">
                                <i class="fas fa-cog"></i>
                            </a>
                            <a href="login.php" class="btn btn-primary ms-3">Connexion</a>
                        </div>
                    </div>
                </nav>
                
                <!-- Content -->
                <div class="content-container">
                    <!-- Carousel Banner -->
                    <div class="carousel-container">
                        <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                            </div>
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1741573075/imagesNDAMAR2.jpg_pgwnf7.jpg" class="d-block w-100" alt="Banner" style="height: 400px; object-fit: cover;">
                                    <div class="carousel-caption d-none d-md-block">
                                        <h2>NDAAMAR Location de Voitures</h2>
                                        <p>Les meilleurs véhicules aux meilleurs prix pour vos déplacements au Sénégal</p>
                                        <a href="login.php" class="btn btn-primary">Réserver maintenant</a>
                                    </div>
                                </div>
                                <div class="carousel-item">
                                    <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg" class="d-block w-100" alt="Banner" style="height: 400px; object-fit: cover;">
                                    <div class="carousel-caption d-none d-md-block">
                                        <h2>Offres spéciales</h2>
                                        <p>Découvrez nos promotions et offres spéciales pour la saison</p>
                                        <a href="#offres-speciales" class="btn btn-primary">Voir les offres</a>
                                    </div>
                                </div>
                                <div class="carousel-item">
                                    <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1741573075/image22_c0svy6.jpg" class="d-block w-100" alt="Banner" style="height: 400px; object-fit: cover;">
                                    <div class="carousel-caption d-none d-md-block">
                                        <h2>Notre flotte</h2>
                                        <p>Une large gamme de véhicules pour tous vos besoins</p>
                                        <a href="#gallery" class="btn btn-primary">Explorer la flotte</a>
                                    </div>
                                </div>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Précédent</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Suivant</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Offers Section -->
                    <?php if (!empty($offresActives)): ?>
                    <div class="special-offers-section" id="offres-speciales">
                        <h2>Offres Spéciales</h2>
                        <div class="row">
                            <?php foreach ($offresActives as $offre): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card offer-card">
                                        <?php
                                        // Récupérer les détails des véhicules associés à cette offre
                                        $offre_vehicules = $offreModel->getVehiculesForOffre($offre['id']);
                                        $vehicule_image = !empty($offre_vehicules) && isset($offre_vehicules[0]['images']) ? 
                                                        "../" . $offre_vehicules[0]['images'] : 
                                                        "https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg";
                                        ?>
                                        <img src="<?php echo $vehicule_image; ?>" class="offer-image" alt="<?php echo htmlspecialchars($offre['titre']); ?>">
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
                                                    <i class="fas fa-eye"></i> Voir détails
                                                </button>
                                            </div>
                                            <br><br>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Gallery Section -->
                    <div id="gallery">
                        <h2 class="mb-3">Notre Flotte</h2>
                        
                        <div class="gallery-filters">
                            <div class="filter-buttons">
                                <a href="AcceuilVisiteur.php" class="filter-button <?php echo !$categorie_filter ? 'active' : ''; ?>">Tous</a>
                                
                            </div>
                        </div>
                        
                        <div class="row">
                            <?php if (!empty($voitures)): ?>
                                <?php foreach ($voitures as $voiture): ?>
                                    <?php if ($voiture['disponibilite'] == 1): // Afficher uniquement les véhicules disponibles ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card vehicle-card">
                                            <?php 
                                            // Vérifier si le véhicule est en promotion
                                            $estEnPromotion = false;
                                            $reductionPct = 0;
                                            foreach ($offresActives as $offre) {
                                                $offre_vehicules = $offreModel->getVehiculesForOffre($offre['id']);
                                                $voiture_ids = array_column($offre_vehicules, 'id');
                                                if (in_array($voiture['id'], $voiture_ids)) {
                                                    $estEnPromotion = true;
                                                    $reductionPct = $offre['reduction'];
                                                    break;
                                                }
                                            }
                                            ?>
                                            
                                            <?php if (!empty($voiture['images'])): ?>
                                                <img src="../<?php echo $voiture['images']; ?>" class="vehicle-image" alt="<?php echo $voiture['marque'] . ' ' . $voiture['modele']; ?>">
                                            <?php else: ?>
                                                <img src="https://res.cloudinary.com/dhivn2ahm/image/upload/v1740519207/imageNDAAMAR_ov0d7x.jpg" class="vehicle-image" alt="<?php echo $voiture['marque'] . ' ' . $voiture['modele']; ?>">
                                            <?php endif; ?>
                                            
                                            <?php if ($estEnPromotion): ?>
                                                <span class="offer-badge">-<?php echo $reductionPct; ?>%</span>
                                            <?php endif; ?>
                                            
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo $voiture['marque'] . ' ' . $voiture['modele']; ?></h5>
                                                <p class="card-text text-muted"><?php echo $voiture['categorie']; ?></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <?php if ($estEnPromotion): 
                                                        $prixReduit = $voiture['prix_location'] * (1 - $reductionPct/100);
                                                    ?>
                                                        <div>
                                                            <span class="text-muted text-decoration-line-through"><?php echo number_format($voiture['prix_location'], 0, ',', ' ') . ' FCFA/jour'; ?></span><br>
                                                            <span class="fw-bold text-danger"><?php echo number_format($prixReduit, 0, ',', ' ') . ' FCFA/jour'; ?></span>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="fw-bold text-primary"><?php echo number_format($voiture['prix_location'], 0, ',', ' ') . ' FCFA/jour'; ?></span>
                                                    <?php endif; ?>
                                                    <a href="login.php" class="btn btn-sm btn-outline-primary">Réserver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        Aucun véhicule disponible pour le moment.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Pagination -->
                       <!--  <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <li class="page-item">
                                    <a class="page-link" href="#" aria-label="Previous">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#" aria-label="Next">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav> -->
                    </div>
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
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fonction pour préparer le modal d'affichage de l'offre
        function prepareViewModal(id) {
            console.log("Préparation du modal de vue pour l'offre ID:", id);
            
            // Charger les détails de l'offre via AJAX
            $.ajax({
                url: '../Controller/OffreControllerClient.php',
                type: 'GET',
                data: { action: 'get', id: id },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        const offre = data.offre;
                        
                        // Mettre à jour les détails de l'offre
                        $('#viewOfferTitle').text(offre.titre);
                        $('#viewOfferDescription').text(offre.description || 'Aucune description disponible');
                        
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
                                        <td>
                                            <a href="login.php" class="btn btn-sm btn-primary">Réserver</a>
                                        </td>
                                    </tr>
                                `;
                                
                                vehiclesContainer.append(row);
                            });
                        } else {
                            vehiclesContainer.html('<tr><td colspan="6" class="text-center">Aucun véhicule associé à cette offre</td></tr>');
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

        // Scroll doux vers les sections
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>