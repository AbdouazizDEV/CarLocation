<?php
require_once __DIR__ . "/../Database/Connect.php";
require_once __DIR__ . "/voiture.php";

class Offre {
    private $db;
    private $lastError = null;
    private $voitureModel;

    public function __construct() {
        $connect = new Connect();
        $this->db = $connect->getConnection();
        $this->voitureModel = new Voiture();
    }

    public function getLastError() {
        return $this->lastError;
    }

    /**
     * Crée une nouvelle offre
     */
    public function create($titre, $description, $reduction, $date_debut, $date_fin, $code_promo = null, $vehicules = []) {
        try {
            // Vérifier que les dates sont valides
            $debut = new DateTime($date_debut);
            $fin = new DateTime($date_fin);
            
            if ($fin <= $debut) {
                $this->lastError = "La date de fin doit être postérieure à la date de début.";
                return false;
            }
            
            // Vérifier que la réduction est valide
            if ($reduction <= 0 || $reduction > 90) {
                $this->lastError = "La réduction doit être comprise entre 1 et 90%.";
                return false;
            }
            
            // Vérifier que le titre n'est pas vide
            if (empty($titre)) {
                $this->lastError = "Le titre de l'offre ne peut pas être vide.";
                return false;
            }
            
            // Vérifier qu'au moins un véhicule est sélectionné
            if (empty($vehicules)) {
                $this->lastError = "Au moins un véhicule doit être associé à l'offre.";
                return false;
            }
            
            // Début de la transaction
            $this->db->beginTransaction();
            
            // Insérer l'offre
            $query = "INSERT INTO Offres (titre, description, reduction, date_debut, date_fin, code_promo, statut) 
                     VALUES (:titre, :description, :reduction, :date_debut, :date_fin, :code_promo, 'active')";
            
            $stmt = $this->db->prepare($query);
            
            $params = [
                ':titre' => $titre,
                ':description' => $description,
                ':reduction' => $reduction,
                ':date_debut' => $date_debut,
                ':date_fin' => $date_fin,
                ':code_promo' => $code_promo
            ];
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                $this->lastError = "Erreur lors de la création de l'offre: " . implode(", ", $stmt->errorInfo());
                $this->db->rollBack();
                return false;
            }
            
            $offreId = $this->db->lastInsertId();
            
            // Associer les véhicules à l'offre
            $query = "INSERT INTO OffresVehicules (offre_id, voiture_id) VALUES (:offre_id, :voiture_id)";
            $stmt = $this->db->prepare($query);
            
            foreach ($vehicules as $vehiculeId) {
                $stmt->bindParam(':offre_id', $offreId, PDO::PARAM_INT);
                $stmt->bindParam(':voiture_id', $vehiculeId, PDO::PARAM_INT);
                
                if (!$stmt->execute()) {
                    $this->lastError = "Erreur lors de l'association des véhicules à l'offre: " . implode(", ", $stmt->errorInfo());
                    $this->db->rollBack();
                    return false;
                }
            }
            
            // Commit de la transaction
            $this->db->commit();
            
            return $offreId;
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->lastError = "Exception: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Récupère toutes les offres
     */
    public function getAllOffres() {
        try {
            // Vérifier les offres dont la date de fin est dépassée et les marquer comme inactives
            $this->updateExpiredOffers();
            
            $query = "SELECT * FROM Offres ORDER BY date_debut DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des offres: " . $e->getMessage();
            return [];
        }
    }
    
    /**
     * Récupère les offres actives
     */
    public function getActiveOffres() {
        try {
            // Vérifier les offres dont la date de fin est dépassée et les marquer comme inactives
            $this->updateExpiredOffers();
            
            $query = "SELECT * FROM Offres WHERE statut = 'active' ORDER BY date_debut DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des offres actives: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Met à jour les offres expirées (date_fin < aujourd'hui) en les marquant comme inactives
     */
    private function updateExpiredOffers() {
        try {
            $query = "UPDATE Offres SET statut = 'inactive' 
                      WHERE statut = 'active' AND date_fin < CURRENT_DATE()";
            $this->db->exec($query);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la mise à jour des offres expirées: " . $e->getMessage();
        }
    }

    /**
     * Récupère une offre par son ID avec les véhicules associés
     */
    public function getOffreById($id) {
        try {
            $query = "SELECT * FROM Offres WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $offre = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($offre) {
                // Récupérer les véhicules associés à cette offre
                $offre['vehicules'] = $this->getVehiculesForOffre($id);
            }
            
            return $offre;
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération de l'offre: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Récupère les véhicules associés à une offre
     */
    public function getVehiculesForOffre($offreId) {
        try {
            $query = "SELECT v.* FROM Voitures v 
                     JOIN OffresVehicules ov ON v.id = ov.voiture_id 
                     WHERE ov.offre_id = :offre_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':offre_id', $offreId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des véhicules de l'offre: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Met à jour une offre existante
     */
    public function update($id, $titre, $description, $reduction, $date_debut, $date_fin, $statut, $code_promo = null, $vehicules = []) {
        try {
            // Vérifier que les dates sont valides
            $debut = new DateTime($date_debut);
            $fin = new DateTime($date_fin);
            
            if ($fin <= $debut) {
                $this->lastError = "La date de fin doit être postérieure à la date de début.";
                return false;
            }
            
            // Vérifier que la réduction est valide
            if ($reduction <= 0 || $reduction > 90) {
                $this->lastError = "La réduction doit être comprise entre 1 et 90%.";
                return false;
            }
            
            // Vérifier que le titre n'est pas vide
            if (empty($titre)) {
                $this->lastError = "Le titre de l'offre ne peut pas être vide.";
                return false;
            }
            
            // Vérifier qu'au moins un véhicule est sélectionné
            if (empty($vehicules)) {
                $this->lastError = "Au moins un véhicule doit être associé à l'offre.";
                return false;
            }
            
            // Début de la transaction
            $this->db->beginTransaction();
            
            // Mettre à jour l'offre
            $query = "UPDATE Offres SET 
                      titre = :titre, 
                      description = :description, 
                      reduction = :reduction, 
                      date_debut = :date_debut, 
                      date_fin = :date_fin, 
                      statut = :statut, 
                      code_promo = :code_promo 
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            
            $params = [
                ':id' => $id,
                ':titre' => $titre,
                ':description' => $description,
                ':reduction' => $reduction,
                ':date_debut' => $date_debut,
                ':date_fin' => $date_fin,
                ':statut' => $statut,
                ':code_promo' => $code_promo
            ];
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                $this->lastError = "Erreur lors de la mise à jour de l'offre: " . implode(", ", $stmt->errorInfo());
                $this->db->rollBack();
                return false;
            }
            
            // Supprimer les associations actuelles
            $query = "DELETE FROM OffresVehicules WHERE offre_id = :offre_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':offre_id', $id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $this->lastError = "Erreur lors de la suppression des associations de véhicules: " . implode(", ", $stmt->errorInfo());
                $this->db->rollBack();
                return false;
            }
            
            // Associer les nouveaux véhicules à l'offre
            $query = "INSERT INTO OffresVehicules (offre_id, voiture_id) VALUES (:offre_id, :voiture_id)";
            $stmt = $this->db->prepare($query);
            
            foreach ($vehicules as $vehiculeId) {
                $stmt->bindParam(':offre_id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':voiture_id', $vehiculeId, PDO::PARAM_INT);
                
                if (!$stmt->execute()) {
                    $this->lastError = "Erreur lors de l'association des véhicules à l'offre: " . implode(", ", $stmt->errorInfo());
                    $this->db->rollBack();
                    return false;
                }
            }
            
            // Commit de la transaction
            $this->db->commit();
            
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->lastError = "Exception: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Supprime une offre
     */
    public function delete($id) {
        try {
            $this->db->beginTransaction();
            
            // Supprimer d'abord les associations avec les véhicules
            $query = "DELETE FROM OffresVehicules WHERE offre_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $this->lastError = "Erreur lors de la suppression des associations de véhicules: " . implode(", ", $stmt->errorInfo());
                $this->db->rollBack();
                return false;
            }
            
            // Supprimer l'offre
            $query = "DELETE FROM Offres WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $this->lastError = "Erreur lors de la suppression de l'offre: " . implode(", ", $stmt->errorInfo());
                $this->db->rollBack();
                return false;
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->lastError = "Exception: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Réactive une offre expirée en mettant à jour sa date de fin
     */
    public function reactivateOffer($id, $date_fin) {
        try {
            // Vérifier que la nouvelle date de fin est valide
            $finDate = new DateTime($date_fin);
            $today = new DateTime();
            
            if ($finDate <= $today) {
                $this->lastError = "La date de fin doit être postérieure à la date d'aujourd'hui.";
                return false;
            }
            
            $query = "UPDATE Offres SET statut = 'active', date_fin = :date_fin WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':date_fin', $date_fin);
            
            return $stmt->execute();
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la réactivation de l'offre: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Calcule le prix réduit d'un véhicule à partir d'une offre
     */
    public function calculateDiscountedPrice($vehicleId, $offreId = null) {
        try {
            // Si aucun ID d'offre n'est fourni, trouver l'offre active pour ce véhicule
            if ($offreId === null) {
                $query = "SELECT o.* FROM Offres o
                         JOIN OffresVehicules ov ON o.id = ov.offre_id
                         WHERE ov.voiture_id = :vehicule_id
                         AND o.statut = 'active'
                         AND CURRENT_DATE() BETWEEN o.date_debut AND o.date_fin
                         ORDER BY o.reduction DESC
                         LIMIT 1";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':vehicule_id', $vehicleId, PDO::PARAM_INT);
                $stmt->execute();
                
                $offre = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$offre) {
                    // Aucune offre active pour ce véhicule, retourner le prix normal
                    $vehicule = $this->voitureModel->getVoitureById($vehicleId);
                    return [
                        'price' => $vehicule['prix_location'],
                        'discounted_price' => $vehicule['prix_location'],
                        'discount' => 0,
                        'offre' => null
                    ];
                }
                
                $offreId = $offre['id'];
                $reduction = $offre['reduction'];
            } else {
                // Récupérer les détails de l'offre spécifiée
                $offre = $this->getOffreById($offreId);
                
                if (!$offre) {
                    $this->lastError = "Offre non trouvée.";
                    return false;
                }
                
                $reduction = $offre['reduction'];
                
                // Vérifier que le véhicule est associé à cette offre
                $vehiculesOffre = $this->getVehiculesForOffre($offreId);
                $vehiculeIds = array_column($vehiculesOffre, 'id');
                
                if (!in_array($vehicleId, $vehiculeIds)) {
                    $this->lastError = "Ce véhicule n'est pas associé à cette offre.";
                    return false;
                }
            }
            
            // Récupérer le prix normal du véhicule
            $vehicule = $this->voitureModel->getVoitureById($vehicleId);
            $prixNormal = $vehicule['prix_location'];
            
            // Calculer le prix réduit
            $prixReduit = $prixNormal * (1 - $reduction / 100);
            
            return [
                'price' => $prixNormal,
                'discounted_price' => round($prixReduit),
                'discount' => $reduction,
                'offre' => $offre
            ];
        } catch (Exception $e) {
            $this->lastError = "Erreur lors du calcul du prix réduit: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Vérifie si un code promo est valide et retourne l'offre associée
     */
    public function validatePromoCode($code) {
        try {
            $query = "SELECT * FROM Offres 
                     WHERE code_promo = :code 
                     AND statut = 'active' 
                     AND CURRENT_DATE() BETWEEN date_debut AND date_fin";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->execute();
            
            $offre = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($offre) {
                return $offre;
            } else {
                $this->lastError = "Code promo invalide ou expiré.";
                return false;
            }
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la validation du code promo: " . $e->getMessage();
            return false;
        }
    }
}
?>