<?php

require_once __DIR__ . "/../Database/Connect.php"; // Vérifiez que ce fichier existe

class offre {
    private $db;

    // Ajout du constructeur pour initialiser la connexion à la base de données
    public function __construct() {
        $this->db = new Connect(); 
    }

    public function create( $voiture_id,$description, $date_debut, $date_fin, $prix_special ) {
        try {
            // Vérification de la connexion
            $conn = $this->db->getConnection();
            if (!$conn) {
                echo ("Connexion à la base de données échouée.");
            }

        
            $stmt = $conn->prepare("
    INSERT INTO offres (voiture_id, description, date_debut, date_fin, prix_special)
    VALUES (:voiture_id, :description, :date_debut, :date_fin, :prix_special)
    ");

// Exécution de la requête avec les valeurs
     $result = $stmt->execute([
    ':voiture_id' => $voiture_id, // Ajoute la valeur de voiture_id
    ':description' => $description,
    ':date_debut' => $date_debut,
    ':date_fin' => $date_fin,
    ':prix_special' => $prix_special
]);


            return $result; // Retourne true si l'insertion est réussie

        } 
        catch (PDOException $e) {
            echo "Erreur : ". $e->getMessage();
            return false; // Retourne false si une erreur survient
        }
    }
}

?>
