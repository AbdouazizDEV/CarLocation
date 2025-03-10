<?php
require_once __DIR__ . "/../Database/Connect.php";

class Voiture {
    private $db;
    private $lastError = null;

    public function __construct() {
        $connect = new Connect();
        $this->db = $connect->getConnection();
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function create($marque, $modele, $annee, $prix_location, $code, $categorie, $disponibilite = 1, $imagesPath = null, $description = null) {
        try {
            // Début de la transaction
            $this->db->beginTransaction();
            
            // Vérification de la connexion
            $conn = $this->db;
            if (!$conn) {
                $this->lastError = "Connexion à la base de données échouée.";
                error_log("Erreur: connexion à la base de données échouée");
                return false;
            }
    
            // Vérification des valeurs avant l'insertion
            if (empty($marque) || empty($modele) || empty($annee) || empty($prix_location) || empty($code)) {
                $this->lastError = "Tous les champs obligatoires doivent être remplis.";
                error_log("Erreur: Champs obligatoires manquants");
                return false;
            }
            
            // Extraire la première image pour la table Voitures si des images sont fournies
            $imagePrincipale = null;
            $imagesArray = [];
            
            if (!empty($imagesPath)) {
                $imagesArray = explode(',', $imagesPath);
                $imagePrincipale = $imagesArray[0]; // La première image sera l'image principale
            }
    
            // Log des valeurs avant insertion
            error_log("Préparation de la requête d'insertion avec les valeurs : " . 
                     "marque=" . $marque . ", " . 
                     "modele=" . $modele . ", " . 
                     "annee=" . $annee . ", " . 
                     "prix_location=" . $prix_location . ", " . 
                     "code=" . $code . ", " . 
                     "categorie=" . $categorie . ", " . 
                     "disponibilite=" . $disponibilite . ", " . 
                     "images=" . $imagePrincipale . ", " . 
                     "description=" . $description);
    
            // Préparation de la requête avec description
            $query = "INSERT INTO Voitures (marque, modele, annee, prix_location, code, categorie, disponibilite, images, description) 
                     VALUES (:marque, :modele, :annee, :prix_location, :code, :categorie, :disponibilite, :images, :description)";
    
            error_log("Requête SQL: " . $query);
    
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                $this->lastError = "Erreur lors de la préparation de la requête: " . print_r($conn->errorInfo(), true);
                error_log("Erreur lors de la préparation: " . print_r($conn->errorInfo(), true));
                $this->db->rollBack();
                return false;
            }
    
            // Exécution de la requête avec les valeurs
            $params = [
                ':marque' => $marque,
                ':modele' => $modele,
                ':annee' => $annee,
                ':prix_location' => $prix_location,
                ':code' => $code,
                ':categorie' => $categorie,
                ':disponibilite' => $disponibilite,
                ':images' => $imagePrincipale,
                ':description' => $description
            ];
    
            error_log("Paramètres de la requête: " . json_encode($params));
    
            $result = $stmt->execute($params);
            
            if (!$result) {
                $this->lastError = "Erreur lors de l'exécution de la requête: " . implode(", ", $stmt->errorInfo());
                error_log("Erreur lors de l'exécution: " . print_r($stmt->errorInfo(), true));
                $this->db->rollBack();
                return false;
            }
    
            $voitureId = $conn->lastInsertId();
            
            // Si nous avons des images, les insérer dans la table Images
            if (!empty($imagesArray)) {
                foreach ($imagesArray as $index => $imagePath) {
                    $est_principale = ($index === 0) ? 1 : 0;
                    $this->addImage($voitureId, $imagePath, $est_principale);
                }
            }
            
            // Commit de la transaction
            $this->db->commit();
            
            error_log("Insertion réussie, ID généré: " . $voitureId);
            return $voitureId;
        } 
        catch (PDOException $e) {
            $this->db->rollBack();
            $this->lastError = "PDOException: " . $e->getMessage() . " [" . $e->getCode() . "]";
            error_log("PDOException: " . $e->getMessage() . " [" . $e->getCode() . "]");
            return false;
        }
        catch (Exception $e) {
            $this->db->rollBack();
            $this->lastError = "Exception: " . $e->getMessage();
            error_log("Exception: " . $e->getMessage());
            return false;
        }
    }

    // Ajouter une image pour une voiture
    public function addImage($voitureId, $chemin, $est_principale = 0) {
        try {
            $stmt = $this->db->prepare("INSERT INTO Images (voiture_id, chemin, est_principale) VALUES (:voiture_id, :chemin, :est_principale)");
            return $stmt->execute([
                ':voiture_id' => $voitureId,
                ':chemin' => $chemin,
                ':est_principale' => $est_principale
            ]);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de l'ajout de l'image : " . $e->getMessage();
            error_log("Erreur lors de l'ajout de l'image : " . $e->getMessage());
            return false;
        }
    }

    // Récupérer toutes les images d'une voiture
    public function getImagesForVoiture($voitureId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM Images WHERE voiture_id = :voiture_id ORDER BY est_principale DESC, id ASC");
            $stmt->bindParam(':voiture_id', $voitureId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des images : " . $e->getMessage();
            error_log("Erreur lors de la récupération des images : " . $e->getMessage());
            return [];
        }
    }

    // Mettre à jour l'image principale d'une voiture
    public function updateMainImage($voitureId, $imageId) {
        try {
            // Commencer par réinitialiser toutes les images comme non principales
            $stmt1 = $this->db->prepare("UPDATE Images SET est_principale = 0 WHERE voiture_id = :voiture_id");
            $stmt1->bindParam(':voiture_id', $voitureId, PDO::PARAM_INT);
            $stmt1->execute();
            
            // Définir la nouvelle image principale
            $stmt2 = $this->db->prepare("UPDATE Images SET est_principale = 1 WHERE id = :id AND voiture_id = :voiture_id");
            $stmt2->bindParam(':id', $imageId, PDO::PARAM_INT);
            $stmt2->bindParam(':voiture_id', $voitureId, PDO::PARAM_INT);
            $stmt2->execute();
            
            // Mettre à jour l'image principale dans la table Voitures
            $stmt3 = $this->db->prepare("UPDATE Voitures SET images = (SELECT chemin FROM Images WHERE id = :id) WHERE id = :voiture_id");
            $stmt3->bindParam(':id', $imageId, PDO::PARAM_INT);
            $stmt3->bindParam(':voiture_id', $voitureId, PDO::PARAM_INT);
            return $stmt3->execute();
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la mise à jour de l'image principale : " . $e->getMessage();
            error_log("Erreur lors de la mise à jour de l'image principale : " . $e->getMessage());
            return false;
        }
    }

    // Supprimer une image
    public function deleteImage($imageId) {
        try {
            // Vérifier si c'est l'image principale
            $stmt = $this->db->prepare("SELECT voiture_id, est_principale, chemin FROM Images WHERE id = :id");
            $stmt->bindParam(':id', $imageId, PDO::PARAM_INT);
            $stmt->execute();
            $image = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$image) {
                return false;
            }
            
            // Supprimer l'image
            $stmt = $this->db->prepare("DELETE FROM Images WHERE id = :id");
            $stmt->bindParam(':id', $imageId, PDO::PARAM_INT);
            $success = $stmt->execute();
            
            // Si c'était l'image principale et qu'elle a été supprimée avec succès
            if ($success && $image['est_principale']) {
                // Chercher une autre image pour la définir comme principale
                $stmt = $this->db->prepare("SELECT id FROM Images WHERE voiture_id = :voiture_id LIMIT 1");
                $stmt->bindParam(':voiture_id', $image['voiture_id'], PDO::PARAM_INT);
                $stmt->execute();
                $newMainImage = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($newMainImage) {
                    // Définir la nouvelle image principale
                    $this->updateMainImage($image['voiture_id'], $newMainImage['id']);
                } else {
                    // Aucune autre image disponible, mettre à NULL l'image dans la table Voitures
                    $stmt = $this->db->prepare("UPDATE Voitures SET images = NULL WHERE id = :voiture_id");
                    $stmt->bindParam(':voiture_id', $image['voiture_id'], PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
            
            return $success;
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la suppression de l'image : " . $e->getMessage();
            error_log("Erreur lors de la suppression de l'image : " . $e->getMessage());
            return false;
        }
    }

    public function getAllVoitures() {
        try {
            $stmt = $this->db->prepare("SELECT v.*, 
                CASE 
                    WHEN v.disponibilite = 1 THEN 'disponible'
                    WHEN v.disponibilite = 2 THEN 'loué'
                    WHEN v.disponibilite = 3 THEN 'maintenance'
                    ELSE 'indisponible'
                END as statut
                FROM Voitures v
                ORDER BY v.id DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des véhicules : " . $e->getMessage();
            error_log("Erreur lors de la récupération des véhicules : " . $e->getMessage());
            return [];
        }
    }

    public function getVoitureById($id) {
        try {
            $stmt = $this->db->prepare("SELECT v.*, 
                CASE 
                    WHEN v.disponibilite = 1 THEN 'disponible'
                    WHEN v.disponibilite = 2 THEN 'loué'
                    WHEN v.disponibilite = 3 THEN 'maintenance'
                    ELSE 'indisponible'
                END as statut
                FROM Voitures v
                WHERE v.id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $voiture = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($voiture) {
                // Récupérer toutes les images de la voiture
                $voiture['all_images'] = $this->getImagesForVoiture($id);
            }
            
            return $voiture;
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération du véhicule : " . $e->getMessage();
            error_log("Erreur lors de la récupération du véhicule : " . $e->getMessage());
            return false;
        }
    }

    public function getVoituresByStatus($statut) {
        try {
            // Convertir le statut texte en valeur numérique si nécessaire
            $disponibilite = is_numeric($statut) ? $statut : match($statut) {
                'disponible' => 1,
                'loué' => 2,
                'maintenance' => 3,
                'indisponible' => 0,
                default => 1
            };
            
            $stmt = $this->db->prepare("SELECT v.*, 
                CASE 
                    WHEN v.disponibilite = 1 THEN 'disponible'
                    WHEN v.disponibilite = 2 THEN 'loué'
                    WHEN v.disponibilite = 3 THEN 'maintenance'
                    ELSE 'indisponible'
                END as statut
                FROM Voitures v
                WHERE v.disponibilite = :disponibilite
                ORDER BY v.id DESC");
            $stmt->bindParam(':disponibilite', $disponibilite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des véhicules par statut : " . $e->getMessage();
            error_log("Erreur lors de la récupération des véhicules par statut : " . $e->getMessage());
            return [];
        }
    }

    public function update($id, $marque, $modele, $annee, $prix_location, $code, $categorie, $disponibilite, $description = null, $images = null) {
        try {
            // Commencer la transaction
            $this->db->beginTransaction();
            
            // Construction de la requête
            $query = "UPDATE Voitures SET 
                      marque = :marque, 
                      modele = :modele, 
                      annee = :annee, 
                      prix_location = :prix_location, 
                      code = :code, 
                      categorie = :categorie, 
                      disponibilite = :disponibilite,
                      description = :description";
            
            // Ajouter les images si elles sont fournies
            $params = [
                ':id' => $id,
                ':marque' => $marque,
                ':modele' => $modele,
                ':annee' => $annee,
                ':prix_location' => $prix_location,
                ':code' => $code,
                ':categorie' => $categorie,
                ':disponibilite' => $disponibilite,
                ':description' => $description
            ];

            if ($images !== null) {
                $query .= ", images = :images";
                $params[':images'] = $images;
            }

            $query .= " WHERE id = :id";

            // Préparation et exécution de la requête
            $stmt = $this->db->prepare($query);
            $success = $stmt->execute($params);
            
            if (!$success) {
                $this->lastError = "Erreur lors de la mise à jour: " . implode(", ", $stmt->errorInfo());
                error_log("Erreur lors de la mise à jour: " . print_r($stmt->errorInfo(), true));
                $this->db->rollBack();
                return false;
            }
            
            // Si nous avons de nouvelles images à ajouter (format: tableau d'images)
            if (is_array($images) && !empty($images)) {
                foreach ($images as $index => $imagePath) {
                    $est_principale = ($index === 0) ? 1 : 0;
                    $this->addImage($id, $imagePath, $est_principale);
                }
            }
            
            // Commit de la transaction
            $this->db->commit();
            
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->lastError = "Erreur lors de la mise à jour du véhicule : " . $e->getMessage();
            error_log("Erreur lors de la mise à jour du véhicule : " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            // Les images seront supprimées automatiquement grâce à la contrainte ON DELETE CASCADE
            $stmt = $this->db->prepare("DELETE FROM Voitures WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $success = $stmt->execute();
            
            if (!$success) {
                $this->lastError = "Erreur lors de la suppression: " . implode(", ", $stmt->errorInfo());
                error_log("Erreur lors de la suppression: " . print_r($stmt->errorInfo(), true));
            }
            
            return $success;
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la suppression du véhicule : " . $e->getMessage();
            error_log("Erreur lors de la suppression du véhicule : " . $e->getMessage());
            return false;
        }
    }

    public function updateDisponibilite($id, $disponibilite) {
        try {
            $stmt = $this->db->prepare("UPDATE Voitures SET disponibilite = :disponibilite WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':disponibilite', $disponibilite, PDO::PARAM_INT);
            $success = $stmt->execute();
            
            if (!$success) {
                $this->lastError = "Erreur lors de la mise à jour de la disponibilité: " . implode(", ", $stmt->errorInfo());
                error_log("Erreur lors de la mise à jour de la disponibilité: " . print_r($stmt->errorInfo(), true));
            }
            
            return $success;
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la mise à jour de la disponibilité : " . $e->getMessage();
            error_log("Erreur lors de la mise à jour de la disponibilité : " . $e->getMessage());
            return false;
        }
    }

    public function searchVoitures($criteria) {
        try {
            $query = "SELECT v.*, 
                CASE 
                    WHEN v.disponibilite = 1 THEN 'disponible'
                    WHEN v.disponibilite = 2 THEN 'loué'
                    WHEN v.disponibilite = 3 THEN 'maintenance'
                    ELSE 'indisponible'
                END as statut
                FROM Voitures v
                WHERE 1=1";
            $params = [];

            if (!empty($criteria['marque'])) {
                $query .= " AND v.marque LIKE :marque";
                $params[':marque'] = '%' . $criteria['marque'] . '%';
            }
            
            if (!empty($criteria['modele'])) {
                $query .= " AND v.modele LIKE :modele";
                $params[':modele'] = '%' . $criteria['modele'] . '%';
            }
            
            if (!empty($criteria['categorie'])) {
                $query .= " AND v.categorie = :categorie";
                $params[':categorie'] = $criteria['categorie'];
            }
            
            if (isset($criteria['disponibilite'])) {
                $query .= " AND v.disponibilite = :disponibilite";
                $params[':disponibilite'] = $criteria['disponibilite'];
            }
            
            if (!empty($criteria['prix_min'])) {
                $query .= " AND v.prix_location >= :prix_min";
                $params[':prix_min'] = $criteria['prix_min'];
            }
            
            if (!empty($criteria['prix_max'])) {
                $query .= " AND v.prix_location <= :prix_max";
                $params[':prix_max'] = $criteria['prix_max'];
            }

            $query .= " ORDER BY v.id DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la recherche de véhicules : " . $e->getMessage();
            error_log("Erreur lors de la recherche de véhicules : " . $e->getMessage());
            return [];
        }
    }
    /**
 * Récupère toutes les voitures disponibles à la location
 * @return array Liste des voitures disponibles
 */
public function getVoituresDisponibles() {
    try {
        $query = "SELECT v.* FROM Voitures v 
                 WHERE v.statut = 'disponible' 
                 ORDER BY v.marque, v.modele";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $this->lastError = "Erreur lors de la récupération des voitures disponibles: " . $e->getMessage();
        return [];
    }
}
}
?>