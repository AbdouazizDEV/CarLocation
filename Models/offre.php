<?php
require_once __DIR__ . "/../Database/Connect.php"; 

class Offre {
    private $db;

    public function __construct() {
        $this->db = new Connect();
    }

    public function create($marque, $modele, $description, $date_debut, $date_fin, $prix_special) {
        try {
            $conn = $this->db->getConnection();
            if (!$conn) {
                echo "Connexion à la base de données échouée.";
                return false;
            }

            // Récupérer l'ID de la voiture
            $stmt = $conn->prepare("
                SELECT id FROM voitures WHERE marque = :marque AND modele = :modele LIMIT 1
            ");
            $stmt->execute([
                ':marque' => $marque,
                ':modele' => $modele
            ]);
            $voiture_id = $stmt->fetchColumn(); // Récupérer l'ID de la voiture

            if (!$voiture_id) {
                echo "Aucune voiture trouvée pour cette marque et ce modèle.";
                return false;
            }

            // Insérer l'offre
            $stmt = $conn->prepare("
                INSERT INTO offres (voiture_id, description, date_debut, date_fin, prix_special)
                VALUES (:voiture_id, :description, :date_debut, :date_fin, :prix_special)
            ");
            $result = $stmt->execute([
                ':voiture_id' => $voiture_id,
                ':description' => $description,
                ':date_debut' => $date_debut,
                ':date_fin' => $date_fin,
                ':prix_special' => $prix_special
            ]);

            return $result; // Retourne true si l'insertion est réussie
        } 
        catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
            return false;
        }
    }
}

$offre = new Offre();
$offre->create("BMW", "Bmw I7", "Promo spéciale week-end", "2025-03-15", "2025-03-17", "20000");


?>
