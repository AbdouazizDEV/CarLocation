<?php

require_once __DIR__ . "/../Database/Connect.php"; // Vérifiez que ce fichier existe

class Voiture {
    private $db;

    // Ajout du constructeur pour initialiser la connexion à la base de données
    public function __construct() {
        $this->db = new Connect(); 
    }

    public function create($marque, $modele, $annee, $prix_location, $code, $categorie, $description, $images) {
        try {
            // Vérification de la connexion
            $conn = $this->db->getConnection();
            if (!$conn) {
                echo ("Connexion à la base de données échouée.");
            }

            // Vérification des valeurs avant l'insertion
            if (empty($marque) || empty($modele) || empty($annee) || empty($prix_location) || empty($code) || empty($categorie)) {
                echo ("Tous les champs obligatoires doivent être remplis.");
            }

            // Préparation de la requête
            $stmt = $conn->prepare("
                INSERT INTO voitures (marque, modele, annee, prix_location, code, categorie, description, images)
                VALUES (:marque, :modele, :annee, :prix_location, :code, :categorie, :description, :images)
            ");

            // Exécution de la requête avec les valeurs
            $result = $stmt->execute([
                ':marque' => $marque,
                ':modele' => $modele,
                ':annee' => $annee,
                ':prix_location' => $prix_location,
                ':code' => $code,
                ':categorie' => $categorie,
                ':description' => $description,
                ':images' => $images,
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
