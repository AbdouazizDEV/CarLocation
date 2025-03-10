<?php
require_once __DIR__ . "/../Database/Connect.php";

class Favoris {
    private $db;
    private $lastError = null;

    public function __construct() {
        $connect = new Connect();
        $this->db = $connect->getConnection();
    }

    public function getLastError() {
        return $this->lastError;
    }

    /**
     * Ajouter une offre aux favoris
     * @param int $userId ID de l'utilisateur
     * @param int $offreId ID de l'offre
     * @return bool Succès de l'opération
     */
    public function addOffreFavorite($userId, $offreId) {
        try {
            // Vérifier si l'offre est déjà en favoris
            if ($this->isOffreFavorite($userId, $offreId)) {
                return true; // Déjà en favoris, pas besoin de l'ajouter
            }
            
            $query = "INSERT INTO FavorisOffres (utilisateur_id, offre_id, date_ajout) 
                     VALUES (:utilisateur_id, :offre_id, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':offre_id', $offreId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de l'ajout de l'offre aux favoris: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Ajouter un véhicule aux favoris
     * @param int $userId ID de l'utilisateur
     * @param int $voitureId ID du véhicule
     * @return bool Succès de l'opération
     */
    public function addVoitureFavorite($userId, $voitureId) {
        try {
            // Vérifier si le véhicule est déjà en favoris
            if ($this->isVoitureFavorite($userId, $voitureId)) {
                return true; // Déjà en favoris, pas besoin de l'ajouter
            }
            
            $query = "INSERT INTO FavorisVoitures (utilisateur_id, voiture_id, date_ajout) 
                     VALUES (:utilisateur_id, :voiture_id, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':voiture_id', $voitureId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de l'ajout du véhicule aux favoris: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Supprimer une offre des favoris
     * @param int $userId ID de l'utilisateur
     * @param int $offreId ID de l'offre
     * @return bool Succès de l'opération
     */
    public function removeOffreFavorite($userId, $offreId) {
        try {
            $query = "DELETE FROM FavorisOffres 
                     WHERE utilisateur_id = :utilisateur_id AND offre_id = :offre_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':offre_id', $offreId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la suppression de l'offre des favoris: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Supprimer un véhicule des favoris
     * @param int $userId ID de l'utilisateur
     * @param int $voitureId ID du véhicule
     * @return bool Succès de l'opération
     */
    public function removeVoitureFavorite($userId, $voitureId) {
        try {
            $query = "DELETE FROM FavorisVoitures 
                     WHERE utilisateur_id = :utilisateur_id AND voiture_id = :voiture_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':voiture_id', $voitureId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la suppression du véhicule des favoris: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Vérifier si une offre est dans les favoris
     * @param int $userId ID de l'utilisateur
     * @param int $offreId ID de l'offre
     * @return bool True si l'offre est en favoris, sinon False
     */
    public function isOffreFavorite($userId, $offreId) {
        try {
            $query = "SELECT COUNT(*) FROM FavorisOffres 
                     WHERE utilisateur_id = :utilisateur_id AND offre_id = :offre_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':offre_id', $offreId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la vérification des favoris: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Vérifier si un véhicule est dans les favoris
     * @param int $userId ID de l'utilisateur
     * @param int $voitureId ID du véhicule
     * @return bool True si le véhicule est en favoris, sinon False
     */
    public function isVoitureFavorite($userId, $voitureId) {
        try {
            $query = "SELECT COUNT(*) FROM FavorisVoitures 
                     WHERE utilisateur_id = :utilisateur_id AND voiture_id = :voiture_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':voiture_id', $voitureId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la vérification des favoris: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Récupérer toutes les offres favorites d'un utilisateur
     * @param int $userId ID de l'utilisateur
     * @return array Liste des offres favorites
     */
    public function getOffresFavorites($userId) {
        try {
            $query = "SELECT o.* FROM Offres o
                     JOIN FavorisOffres f ON o.id = f.offre_id
                     WHERE f.utilisateur_id = :utilisateur_id
                     ORDER BY f.date_ajout DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des offres favorites: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Récupérer tous les véhicules favoris d'un utilisateur
     * @param int $userId ID de l'utilisateur
     * @return array Liste des véhicules favoris
     */
    public function getVoituresFavorites($userId) {
        try {
            $query = "SELECT v.* FROM Voitures v
                     JOIN FavorisVoitures f ON v.id = f.voiture_id
                     WHERE f.utilisateur_id = :utilisateur_id
                     ORDER BY f.date_ajout DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des véhicules favoris: " . $e->getMessage();
            return [];
        }
    }
    
    /**
     * Récupérer le nombre d'offres favorites d'un utilisateur
     * @param int $userId ID de l'utilisateur
     * @return int Nombre d'offres favorites
     */
    public function getCountOffresFavorites($userId) {
        try {
            $query = "SELECT COUNT(*) FROM FavorisOffres 
                     WHERE utilisateur_id = :utilisateur_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            $this->lastError = "Erreur lors du comptage des offres favorites: " . $e->getMessage();
            return 0;
        }
    }
    
    /**
     * Récupérer le nombre de véhicules favoris d'un utilisateur
     * @param int $userId ID de l'utilisateur
     * @return int Nombre de véhicules favoris
     */
    public function getCountVoituresFavorites($userId) {
        try {
            $query = "SELECT COUNT(*) FROM FavorisVoitures 
                     WHERE utilisateur_id = :utilisateur_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':utilisateur_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch (Exception $e) {
            $this->lastError = "Erreur lors du comptage des véhicules favoris: " . $e->getMessage();
            return 0;
        }
    }

    /**
     * Récupère les IDs des offres favorites d'un client
     * @param int $clientId ID du client
     * @return array Liste des IDs des offres favorites
     */
    public function getFavorisByClientId($clientId) {
        try {
            $query = "SELECT offre_id FROM FavorisOffres 
                    WHERE utilisateur_id = :client_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':client_id', $clientId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des favoris: " . $e->getMessage();
            return [];
        }
    }
}
?>