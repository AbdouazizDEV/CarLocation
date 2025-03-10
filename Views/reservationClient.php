<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réservation de Voiture</title>
</head>
<body>
    <h1>Reservez une voiture</h1>
    <form action="ReserverClient.php " method="POST">
        
        <label for="voiture">Choisissez une voiture</label>
        <select name="voiture" id="voiture">
            <?php foreach ($voituresDisponibles as $voiture): ?>
                <option value="<?= $voiture ?>"><?= $voiture ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="date">Date debut (JJ-MM-AAAA)</label>
        <input type="text" id="date" name="date_debut" required><br><br>

        <label for="date_fin">Date fin (JJ-MM-AAAA)</label>
        <input type="text" id="date_fin" name="date_fin" required><br><br>
        
                          <div>
                                <label for="edit_statut" class="form-label">Statut</label>
                                <select class="form-select" name="statut" id="edit_statut" required>
                                    <option value="en_attente">En attente</option>
                                    <option value="confirmee">Confirmee</option>
                                    <option value="en_cours">En cours</option>
                                    <option value="terminee">Terminee</option>
                                    <option value="annulee">Annulée</option>
                                </select>
                            </div>
        <label for="prix">montant  total</label>
        <input type="text" id="prix" name="montant _total" required><br><br>

        <input type="submit" value="Réserver">
    </form>
</body>
</html>
