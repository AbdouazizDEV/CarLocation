<?php
require_once __DIR__ . "/../Database/Connect.php";
require_once __DIR__ . "/voiture.php";
require_once __DIR__ . "/client.php";
require_once __DIR__ . "/Reservation.php";

class Facture {
    private $db;
    private $lastError = null;
    private $voitureModel;
    private $clientModel;
    private $reservationModel;

    public function __construct() {
        $connect = new Connect();
        $this->db = $connect->getConnection();
        $this->voitureModel = new Voiture();
        $this->clientModel = new Client();
        $this->reservationModel = new Reservation();
    }

    public function getLastError() {
        return $this->lastError;
    }

    /**
     * Crée une nouvelle facture pour une réservation
     */
    public function create($reservation_id, $client_id, $date_emission, $date_echeance, $montant_total, $notes = null) {
        try {
            // Vérifier si une facture existe déjà pour cette réservation
            if ($this->getFactureByReservationId($reservation_id)) {
                $this->lastError = "Une facture existe déjà pour cette réservation.";
                return false;
            }

            $query = "INSERT INTO Factures (reservation_id, client_id, date_emission, date_echeance, montant_total, notes, statut) 
                     VALUES (:reservation_id, :client_id, :date_emission, :date_echeance, :montant_total, :notes, 'en_attente')";
            
            $stmt = $this->db->prepare($query);
            
            $params = [
                ':reservation_id' => $reservation_id,
                ':client_id' => $client_id,
                ':date_emission' => $date_emission,
                ':date_echeance' => $date_echeance,
                ':montant_total' => $montant_total,
                ':notes' => $notes
            ];
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                $this->lastError = "Erreur lors de la création de la facture: " . implode(", ", $stmt->errorInfo());
                return false;
            }
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            $this->lastError = "Exception: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Récupère toutes les factures
     */
    public function getAllFactures() {
        try {
            $query = "SELECT * FROM Factures ORDER BY date_emission DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des factures: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Récupère une facture par son ID avec tous les détails associés
     */
    public function getFactureById($id) {
        try {
            $query = "SELECT * FROM Factures WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $facture = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($facture) {
                // Récupérer les détails de la réservation
                $reservation = $this->reservationModel->getReservationById($facture['reservation_id']);
                $facture['reservation'] = $reservation;
                
                // Récupérer les détails du client
                $client = $this->clientModel->getClientById($facture['client_id']);
                $facture['client'] = $client;
                
                // Récupérer les détails du véhicule
                $vehicule = $this->voitureModel->getVoitureById($reservation['voiture_id']);
                $facture['vehicule'] = $vehicule;
            }
            
            return $facture;
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération de la facture: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Récupère une facture par ID de réservation
     */
    public function getFactureByReservationId($reservation_id) {
        try {
            $query = "SELECT * FROM Factures WHERE reservation_id = :reservation_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':reservation_id', $reservation_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération de la facture: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Récupère les réservations qui n'ont pas encore de facture
     */
    public function getReservationsSansFacture() {
        try {
            $query = "SELECT r.* FROM Reservations r
                     LEFT JOIN Factures f ON r.id = f.reservation_id
                     WHERE f.id IS NULL AND r.statut IN ('confirmee', 'en_cours', 'terminee')
                     ORDER BY r.date_reservation DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des réservations sans facture: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Met à jour le statut d'une facture
     */
    public function updateStatus($id, $statut) {
        try {
            $query = "UPDATE Factures SET statut = :statut WHERE id = :id";
            $stmt = $this->db->prepare($query);
            
            $params = [
                ':id' => $id,
                ':statut' => $statut
            ];
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                $this->lastError = "Erreur lors de la mise à jour du statut: " . implode(", ", $stmt->errorInfo());
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            $this->lastError = "Exception: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Marque une facture comme payée et crée un enregistrement de paiement
     */
    public function markAsPaid($id, $methode_paiement, $reference_paiement = null) {
        try {
            // Début de la transaction
            $this->db->beginTransaction();
            
            // Mettre à jour le statut de la facture
            $updateResult = $this->updateStatus($id, 'payée');
            
            if (!$updateResult) {
                throw new Exception("Erreur lors de la mise à jour du statut de la facture.");
            }
            
            // Récupérer les détails de la facture
            $facture = $this->getFactureById($id);
            
            if (!$facture) {
                throw new Exception("Facture non trouvée.");
            }
            
            // Créer un enregistrement de paiement
            $query = "INSERT INTO Paiements (facture_id, reservation_id, montant, methode, reference, date_paiement, statut) 
                     VALUES (:facture_id, :reservation_id, :montant, :methode, :reference, NOW(), 'validé')";
            
            $stmt = $this->db->prepare($query);
            
            $params = [
                ':facture_id' => $id,
                ':reservation_id' => $facture['reservation_id'],
                ':montant' => $facture['montant_total'],
                ':methode' => $methode_paiement,
                ':reference' => $reference_paiement
            ];
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                throw new Exception("Erreur lors de la création de l'enregistrement de paiement: " . implode(", ", $stmt->errorInfo()));
            }
            
            // Valider la transaction
            $this->db->commit();
            
            return true;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->rollBack();
            $this->lastError = "Exception: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Recherche des factures par numéro, client ou réservation
     */
    public function searchFactures($term) {
        try {
            $term = "%$term%"; // Préparer le terme pour la recherche LIKE
            
            $query = "SELECT f.* 
                     FROM Factures f 
                     JOIN Clients c ON f.client_id = c.id
                     JOIN Utilisateurs u ON c.utilisateur_id = u.id
                     WHERE f.id LIKE :term 
                     OR f.reservation_id LIKE :term 
                     OR CONCAT(u.prenom, ' ', u.nom) LIKE :term
                     OR u.email LIKE :term
                     ORDER BY f.date_emission DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':term', $term, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la recherche des factures: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Récupère les factures filtrées par statut
     */
    public function getFacturesByStatus($statut) {
        try {
            $query = "SELECT * FROM Factures WHERE statut = :statut ORDER BY date_emission DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':statut', $statut, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des factures: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Annule une facture
     */
    public function cancelInvoice($id, $motif = null) {
        try {
            // Début de la transaction
            $this->db->beginTransaction();
            
            // Mettre à jour le statut de la facture
            $query = "UPDATE Factures SET statut = 'annulée'";
            
            if ($motif) {
                $query .= ", notes = CONCAT(IFNULL(notes, ''), ' [Annulée: ', :motif, ']')";
            }
            
            $query .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($motif) {
                $stmt->bindParam(':motif', $motif, PDO::PARAM_STR);
            }
            
            $result = $stmt->execute();
            
            if (!$result) {
                throw new Exception("Erreur lors de l'annulation de la facture: " . implode(", ", $stmt->errorInfo()));
            }
            
            // Valider la transaction
            $this->db->commit();
            
            return true;
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->rollBack();
            $this->lastError = "Exception: " . $e->getMessage();
            return false;
        }
    }
}
?>