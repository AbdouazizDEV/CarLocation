<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi des Reservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
    <div class="container my-4">
        <h1 class="mb-4">Mes Reservations</h1>
        <div class="row">
            <?php if (isset($reservations) && is_array($reservations)) : ?>
                <?php foreach ($reservations as $reservation) : ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Reservation pour la voiture <?= isset($reservation['voiture_id']) ? htmlspecialchars($reservation['voiture_id']) : '' ?></h5>
                                <p class="card-text"><strong>Date de début :</strong> <?= isset($reservation['date_debut']) ? htmlspecialchars($reservation['date_debut']) : '' ?></p>
                                <p class="card-text"><strong>Date de fin :</strong> <?= isset($reservation['date_fin']) ? htmlspecialchars($reservation['date_fin']) : '' ?></p>
                                <p class="card-text"><strong>Statut :</strong> <?= isset($reservation['statut']) ? htmlspecialchars($reservation['statut']) : '' ?></p>
                                <form method="POST" action="/changer_statut.php">
                                    <input type="hidden" name="reservation_id" value="<?= isset($reservation['id']) ? htmlspecialchars($reservation['id']) : '' ?>">
                                    <div class="form-group">
                                        <label for="statut">Modifier le statut :</label>
                                        <select name="statut" class="form-select">
                                            <option value="en_attente" <?= (isset($reservation['statut']) && $reservation['statut'] == 'en_attente') ? 'selected' : '' ?>>En attente</option>
                                            <option value="confirmee" <?= (isset($reservation['statut']) && $reservation['statut'] == 'confirmee') ? 'selected' : '' ?>>Confirmee</option>
                                            <option value="en_cours" <?= (isset($reservation['statut']) && $reservation['statut'] == 'en_cours') ? 'selected' : '' ?>>En cours</option>
                                            <option value="terminee" <?= (isset($reservation['statut']) && $reservation['statut'] == 'terminee') ? 'selected' : '' ?>>Terminee</option>
                                            <option value="annulee" <?= (isset($reservation['statut']) && $reservation['statut'] == 'annulee') ? 'selected' : '' ?>>Annulée</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary mt-2">Modifier</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="col-12">
                    <div class="alert alert-warning">Aucune reservation trouvee.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
