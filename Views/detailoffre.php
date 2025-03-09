<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails Client</title>
    <style>
    </style>
</head>
<body>
  
<?php
try {
    $conn = $this->db->getConnection();
    if (!$conn) {
        echo "Connexion à la base de données échouée.";
        return false;
    }

    if (isset($_POST['detail'])) {
        $voiture_id = $_POST['detail'];

        $req = "SELECT * FROM offres WHERE voiture_id = ?";
        $res = $conn->prepare($req);  // Utilisez $conn pour la connexion
        $res->execute([$voiture_id]);

        $offres = $res->fetch(PDO::FETCH_ASSOC);

        if ($offres) {  // Vérification correcte si des données sont récupérées
            echo "<h2>Détails des offres</h2>";
            echo "<p><strong>Voiture ID: </strong>" . htmlspecialchars($offres['voiture_id']) . "</p>";
            echo "<p><strong>Description:</strong> " . htmlspecialchars($offres['description']) . "</p>";
            echo "<p><strong>Date de début: </strong>" . htmlspecialchars($offres['date_debut']) . "</p>";
            echo "<p><strong>Date de fin: </strong>" . htmlspecialchars($offres['date_fin']) . "</p>";
            echo "<p><strong>Prix spécial: </strong>" . htmlspecialchars($offres['prix_special']) . "</p>";
        } else {
            echo "<p>Offre non trouvée.</p>";
        }
    } else {
        echo "<p>ID de l'offre manquant.</p>";
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    return false;
}
?>
<a href="offres.php">Retour</a>

</body>
</html>
