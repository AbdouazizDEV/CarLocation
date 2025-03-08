<?php
require_once __DIR__ . "/../Database/Connect.php";
require_once __DIR__ . "/voiture.php";
require_once __DIR__ . "/client.php";

class Reservation {
    private $db;
    private $lastError = null;
    private $voitureModel;
    private $clientModel;

    public function __construct() {
        $connect = new Connect();
        $this->db = $connect->getConnection();
        $this->voitureModel = new Voiture();
        $this->clientModel = new Client();
    }

    public function getLastError() {
        return $this->lastError;
    }

    /**
     * Crée une nouvelle réservation
     */
    public function create($utilisateur_id, $voiture_id, $date_debut, $date_fin, $prix_total, $notes = null, $offre_id = null) {
        try {
            // Vérifier si le véhicule est disponible pour la période demandée
            if (!$this->isVehicleAvailable($voiture_id, $date_debut, $date_fin)) {
                $this->lastError = "Le véhicule n'est pas disponible pour les dates sélectionnées.";
                return false;
            }

            // Vérification de la connexion
            if (!$this->db) {
                $this->lastError = "Connexion à la base de données échouée.";
                return false;
            }

            // Début de la transaction
            $this->db->beginTransaction();

            // Insérer la réservation
            $query = "INSERT INTO Reservations (utilisateur_id, voiture_id, date_debut, date_fin, prix_total, notes, offre_id, date_reservation, statut) 
                     VALUES (:utilisateur_id, :voiture_id, :date_debut, :date_fin, :prix_total, :notes, :offre_id, NOW(), 'en_attente')";

            $stmt = $this->db->prepare($query);
            
            $params = [
                ':utilisateur_id' => $utilisateur_id,
                ':voiture_id' => $voiture_id,
                ':date_debut' => $date_debut,
                ':date_fin' => $date_fin,
                ':prix_total' => $prix_total,
                ':notes' => $notes,
                ':offre_id' => $offre_id
            ];
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                $this->lastError = "Erreur lors de la création de la réservation: " . implode(", ", $stmt->errorInfo());
                $this->db->rollBack();
                return false;
            }
            
            $reservation_id = $this->db->lastInsertId();
            
            // Commit de la transaction
            $this->db->commit();
            
            return $reservation_id;
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->lastError = "Exception: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Vérifie si un véhicule est disponible pour une période donnée
     */
    public function isVehicleAvailable($voiture_id, $date_debut, $date_fin, $exclude_reservation_id = null) {
        try {
            // Vérifier que le véhicule existe et est marqué comme disponible
            $vehicule = $this->voitureModel->getVoitureById($voiture_id);
            if (!$vehicule || $vehicule['disponibilite'] != 1) {
                // Si le véhicule n'est pas dans un état "disponible" initialement
                return false;
            }

            // Préparer la requête pour vérifier les chevauchements de réservations
            $query = "SELECT COUNT(*) FROM Reservations 
                    WHERE voiture_id = :voiture_id 
                    AND statut IN ('en_attente', 'confirmee', 'en_cours') 
                    AND NOT (date_fin < :date_debut OR date_debut > :date_fin)";
            
            $params = [
                ':voiture_id' => $voiture_id,
                ':date_debut' => $date_debut,
                ':date_fin' => $date_fin
            ];
            
            // Si on exclut une réservation spécifique (utile pour les mises à jour)
            if ($exclude_reservation_id) {
                $query .= " AND id != :exclude_id";
                $params[':exclude_id'] = $exclude_reservation_id;
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            // Si le comptage est supérieur à 0, le véhicule n'est pas disponible
            return $stmt->fetchColumn() == 0;
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la vérification de disponibilité: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Récupère toutes les réservations
     */
    public function getAllReservations() {
        try {
            $query = "SELECT * FROM Reservations ORDER BY date_reservation DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des réservations: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Récupère les réservations filtrées par statut
     */
    public function getReservationsByStatus($statut) {
        try {
            $query = "SELECT * FROM Reservations WHERE statut = :statut ORDER BY date_reservation DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':statut', $statut, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des réservations: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Récupère une réservation par son ID, avec les détails du client et du véhicule
     */
    public function getReservationById($id) {
        try {
            $query = "SELECT * FROM Reservations WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($reservation) {
                // Récupérer les informations du client
                $client = $this->clientModel->getClientById($reservation['utilisateur_id']);
                $reservation['client'] = $client;
                
                // Récupérer les informations du véhicule
                $vehicule = $this->voitureModel->getVoitureById($reservation['voiture_id']);
                $reservation['vehicule'] = $vehicule;
            }
            
            return $reservation;
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération de la réservation: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Récupère les réservations d'un client
     */
    public function getReservationsByClient($client_id) {
        try {
            $query = "SELECT * FROM Reservations WHERE utilisateur_id = :client_id ORDER BY date_reservation DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des réservations: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Récupère les réservations pour un véhicule
     */
    public function getReservationsByVehicle($voiture_id) {
        try {
            $query = "SELECT * FROM Reservations WHERE voiture_id = :voiture_id ORDER BY date_debut ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':voiture_id', $voiture_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des réservations: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Met à jour une réservation existante
     */
    public function update($id, $utilisateur_id, $voiture_id, $date_debut, $date_fin, $statut, $prix_total, $notes = null, $offre_id = null) {
        try {
            // Vérifier si le véhicule est disponible pour la période demandée (en excluant cette réservation)
            if ($statut != 'annulee' && !$this->isVehicleAvailable($voiture_id, $date_debut, $date_fin, $id)) {
                $this->lastError = "Le véhicule n'est pas disponible pour les dates sélectionnées.";
                return false;
            }
            
            // Début de la transaction
            $this->db->beginTransaction();
            
            // Mettre à jour la réservation
            $query = "UPDATE Reservations SET 
                     utilisateur_id = :utilisateur_id,
                     voiture_id = :voiture_id,
                     date_debut = :date_debut,
                     date_fin = :date_fin,
                     statut = :statut,
                     prix_total = :prix_total,
                     notes = :notes,
                     offre_id = :offre_id
                     WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            
            $params = [
                ':id' => $id,
                ':utilisateur_id' => $utilisateur_id,
                ':voiture_id' => $voiture_id,
                ':date_debut' => $date_debut,
                ':date_fin' => $date_fin,
                ':statut' => $statut,
                ':prix_total' => $prix_total,
                ':notes' => $notes,
                ':offre_id' => $offre_id
            ];
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                $this->lastError = "Erreur lors de la mise à jour de la réservation: " . implode(", ", $stmt->errorInfo());
                $this->db->rollBack();
                return false;
            }
            
            // Si la réservation passe à "confirmée", mettre à jour le statut du véhicule à "loué"
            if ($statut == 'confirmee') {
                $this->voitureModel->updateDisponibilite($voiture_id, 2); // 2 = loué
            }
            // Si la réservation est annulée et que le véhicule était loué, le remettre en disponible
            else if ($statut == 'annulee') {
                $vehicule = $this->voitureModel->getVoitureById($voiture_id);
                if ($vehicule && $vehicule['disponibilite'] == 2) {
                    $this->voitureModel->updateDisponibilite($voiture_id, 1); // 1 = disponible
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
     * Change le statut d'une réservation
     */
    public function updateStatus($id, $statut, $motif = null) {
        try {
            // Récupérer la réservation actuelle
            $reservation = $this->getReservationById($id);
            if (!$reservation) {
                $this->lastError = "Réservation non trouvée.";
                return false;
            }
            
            // Début de la transaction
            $this->db->beginTransaction();
            
            // Mettre à jour le statut
            $query = "UPDATE Reservations SET statut = :statut";
            
            // Ajouter le motif si fourni (pour les annulations)
            if ($motif && $statut == 'annulee') {
                $query .= ", notes = CONCAT(IFNULL(notes, ''), ' [Motif d''annulation: ', :motif, ']')";
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':statut', $statut, PDO::PARAM_STR);
            
            if ($motif && $statut == 'annulee') {
                $stmt->bindParam(':motif', $motif, PDO::PARAM_STR);
            }
            
            $result = $stmt->execute();
            
            if (!$result) {
                $this->lastError = "Erreur lors de la mise à jour du statut: " . implode(", ", $stmt->errorInfo());
                $this->db->rollBack();
                return false;
            }
            
            // Mettre à jour le statut du véhicule en fonction du nouveau statut de la réservation
            $voiture_id = $reservation['voiture_id'];
            
            if ($statut == 'confirmee') {
                $this->voitureModel->updateDisponibilite($voiture_id, 2); // 2 = loué
            } 
            else if ($statut == 'en_cours') {
                $this->voitureModel->updateDisponibilite($voiture_id, 2); // 2 = loué
                
                // Créer une entrée dans la table Locations
                $this->createLocation($id, date('Y-m-d'));
            }
            else if ($statut == 'terminee') {
                $this->voitureModel->updateDisponibilite($voiture_id, 1); // 1 = disponible
                
                // Mettre à jour la location correspondante
                $this->completeLocation($id, date('Y-m-d'));
            }
            else if ($statut == 'annulee') {
                // Si le véhicule était loué pour cette réservation, le remettre en disponible
                $vehicule = $this->voitureModel->getVoitureById($voiture_id);
                if ($vehicule && $vehicule['disponibilite'] == 2) {
                    $this->voitureModel->updateDisponibilite($voiture_id, 1); // 1 = disponible
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
     * Crée une entrée dans la table Locations lorsque la location commence
     */
    private function createLocation($reservation_id, $date_debut_reelle, $km_depart = 0, $etat_depart = "Bon état") {
        try {
            $query = "INSERT INTO Locations (reservation_id, date_debut_reelle, km_depart, etat_depart, statut)
                     VALUES (:reservation_id, :date_debut_reelle, :km_depart, :etat_depart, 'en_cours')";
            
            $stmt = $this->db->prepare($query);
            
            $params = [
                ':reservation_id' => $reservation_id,
                ':date_debut_reelle' => $date_debut_reelle,
                ':km_depart' => $km_depart,
                ':etat_depart' => $etat_depart
            ];
            
            return $stmt->execute($params);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la création de la location: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Met à jour une location lorsqu'elle se termine
     */
    private function completeLocation($reservation_id, $date_fin_reelle, $km_retour = null, $etat_retour = null, $frais_supplementaires = 0) {
        try {
            $query = "UPDATE Locations SET 
                     date_fin_reelle = :date_fin_reelle,
                     statut = 'terminee'";
            
            $params = [
                ':reservation_id' => $reservation_id,
                ':date_fin_reelle' => $date_fin_reelle
            ];
            
            if ($km_retour !== null) {
                $query .= ", km_retour = :km_retour";
                $params[':km_retour'] = $km_retour;
            }
            
            if ($etat_retour !== null) {
                $query .= ", etat_retour = :etat_retour";
                $params[':etat_retour'] = $etat_retour;
            }
            
            if ($frais_supplementaires > 0) {
                $query .= ", frais_supplementaires = :frais_supplementaires";
                $params[':frais_supplementaires'] = $frais_supplementaires;
            }
            
            $query .= " WHERE reservation_id = :reservation_id";
            
            $stmt = $this->db->prepare($query);
            return $stmt->execute($params);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la clôture de la location: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Supprime une réservation (uniquement si elle est en attente)
     */
    public function delete($id) {
        try {
            // Vérifier si la réservation existe et est en attente
            $reservation = $this->getReservationById($id);
            if (!$reservation) {
                $this->lastError = "Réservation non trouvée.";
                return false;
            }
            
            if ($reservation['statut'] != 'en_attente') {
                $this->lastError = "Seules les réservations en attente peuvent être supprimées.";
                return false;
            }
            
            $query = "DELETE FROM Reservations WHERE id = :id AND statut = 'en_attente'";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la suppression de la réservation: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Récupère les statistiques de réservations
     */
    public function getStats() {
        try {
            $stats = [];
            
            // Total des réservations
            $query = "SELECT COUNT(*) as total FROM Reservations";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Réservations par statut
            $query = "SELECT statut, COUNT(*) as count FROM Reservations GROUP BY statut";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Chiffre d'affaires total (réservations confirmées et terminées)
            $query = "SELECT SUM(prix_total) as revenue FROM Reservations WHERE statut IN ('confirmee', 'en_cours', 'terminee')";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?: 0;
            
            // Réservations du mois en cours
            $query = "SELECT COUNT(*) as count, SUM(prix_total) as revenue 
                     FROM Reservations 
                     WHERE MONTH(date_reservation) = MONTH(CURRENT_DATE())
                     AND YEAR(date_reservation) = YEAR(CURRENT_DATE())";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['current_month'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des statistiques: " . $e->getMessage();
            return null;
        }
    }
}
?>