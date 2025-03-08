<?php
require_once __DIR__ . "/../Database/Connect.php";

class Client {
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
     * Crée un nouveau client
     */
    public function create($utilisateur_id, $telephone = null, $adresse = null) {
        try {
            // Vérifier si le client existe déjà
            if ($this->getClientByUtilisateurId($utilisateur_id)) {
                $this->lastError = "Un client avec cet ID utilisateur existe déjà.";
                return false;
            }

            $query = "INSERT INTO Clients (utilisateur_id, telephone, adresse, date_inscription) 
                     VALUES (:utilisateur_id, :telephone, :adresse, NOW())";
            
            $stmt = $this->db->prepare($query);
            
            $params = [
                ':utilisateur_id' => $utilisateur_id,
                ':telephone' => $telephone,
                ':adresse' => $adresse
            ];
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                $this->lastError = "Erreur lors de la création du client: " . implode(", ", $stmt->errorInfo());
                return false;
            }
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            $this->lastError = "Exception: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Récupère tous les clients avec leurs informations utilisateur
     */
    public function getAllClients() {
        try {
            $query = "SELECT c.*, u.nom, u.prenom, u.email, u.statut 
                     FROM Clients c 
                     JOIN Utilisateurs u ON c.utilisateur_id = u.id 
                     ORDER BY u.nom, u.prenom";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des clients: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Récupère un client par son ID
     */
    public function getClientById($id) {
        try {
            $query = "SELECT c.*, u.nom, u.prenom, u.email, u.statut 
                     FROM Clients c 
                     JOIN Utilisateurs u ON c.utilisateur_id = u.id 
                     WHERE c.id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération du client: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Récupère un client par son ID utilisateur
     */
    public function getClientByUtilisateurId($utilisateur_id) {
        try {
            $query = "SELECT c.*, u.nom, u.prenom, u.email, u.statut 
                     FROM Clients c 
                     JOIN Utilisateurs u ON c.utilisateur_id = u.id 
                     WHERE c.utilisateur_id = :utilisateur_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération du client: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Recherche des clients par nom, prénom ou email
     */
    public function searchClients($term) {
        try {
            $term = "%$term%"; // Préparer le terme pour la recherche LIKE
            
            $query = "SELECT c.*, u.nom, u.prenom, u.email, u.statut 
                     FROM Clients c 
                     JOIN Utilisateurs u ON c.utilisateur_id = u.id 
                     WHERE u.nom LIKE :term OR u.prenom LIKE :term OR u.email LIKE :term
                     ORDER BY u.nom, u.prenom";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':term', $term, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la recherche des clients: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Met à jour les informations d'un client
     */
    public function update($id, $telephone = null, $adresse = null) {
        try {
            $query = "UPDATE Clients SET 
                     telephone = :telephone,
                     adresse = :adresse
                     WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            
            $params = [
                ':id' => $id,
                ':telephone' => $telephone,
                ':adresse' => $adresse
            ];
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                $this->lastError = "Erreur lors de la mise à jour du client: " . implode(", ", $stmt->errorInfo());
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            $this->lastError = "Exception: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Récupère l'historique des réservations d'un client
     */
    public function getClientReservations($client_id) {
        try {
            $query = "SELECT r.*, v.marque, v.modele 
                     FROM Reservations r
                     JOIN Voitures v ON r.voiture_id = v.id
                     WHERE r.utilisateur_id = (SELECT utilisateur_id FROM Clients WHERE id = :client_id)
                     ORDER BY r.date_reservation DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des réservations du client: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Récupère les favoris d'un client
     */
    public function getClientFavorites($client_id) {
        try {
            $query = "SELECT f.*, v.marque, v.modele, v.prix_location, v.images
                     FROM Favoris f
                     JOIN Voitures v ON f.voiture_id = v.id
                     WHERE f.utilisateur_id = (SELECT utilisateur_id FROM Clients WHERE id = :client_id)
                     ORDER BY f.date_ajout DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des favoris du client: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Récupère les paiements d'un client
     */
    public function getClientPayments($client_id) {
        try {
            $query = "SELECT p.*, r.date_debut, r.date_fin, v.marque, v.modele
                     FROM Paiements p
                     JOIN Reservations r ON p.reservation_id = r.id
                     JOIN Voitures v ON r.voiture_id = v.id
                     WHERE r.utilisateur_id = (SELECT utilisateur_id FROM Clients WHERE id = :client_id)
                     ORDER BY p.date_paiement DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des paiements du client: " . $e->getMessage();
            return [];
        }
    }

    /**
     * Calcule le montant total dépensé par un client
     */
    public function getClientTotalSpent($client_id) {
        try {
            $query = "SELECT SUM(prix_total) as total_spent
                     FROM Reservations 
                     WHERE utilisateur_id = (SELECT utilisateur_id FROM Clients WHERE id = :client_id)
                     AND statut IN ('confirmee', 'en_cours', 'terminee')";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_spent'] ?: 0;
        } catch (Exception $e) {
            $this->lastError = "Erreur lors du calcul des dépenses du client: " . $e->getMessage();
            return 0;
        }
    }

    /**
     * Compte le nombre de réservations d'un client
     */
    public function getClientReservationCount($client_id) {
        try {
            $query = "SELECT COUNT(*) as count
                     FROM Reservations 
                     WHERE utilisateur_id = (SELECT utilisateur_id FROM Clients WHERE id = :client_id)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?: 0;
        } catch (Exception $e) {
            $this->lastError = "Erreur lors du comptage des réservations: " . $e->getMessage();
            return 0;
        }
    }

    /**
     * Récupère les statistiques des clients
     */
    public function getClientStats() {
        try {
            $stats = [];
            
            // Nombre total de clients
            $query = "SELECT COUNT(*) as total FROM Clients";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Clients inscrits dans le mois en cours
            $query = "SELECT COUNT(*) as count 
                     FROM Clients 
                     WHERE MONTH(date_inscription) = MONTH(CURRENT_DATE())
                     AND YEAR(date_inscription) = YEAR(CURRENT_DATE())";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['new_this_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Top 5 des clients qui dépensent le plus
            $query = "SELECT c.id, u.nom, u.prenom, COUNT(r.id) as reservations_count, SUM(r.prix_total) as total_spent
                     FROM Clients c
                     JOIN Utilisateurs u ON c.utilisateur_id = u.id
                     JOIN Reservations r ON r.utilisateur_id = c.utilisateur_id
                     WHERE r.statut IN ('confirmee', 'en_cours', 'terminee')
                     GROUP BY c.id, u.nom, u.prenom
                     ORDER BY total_spent DESC
                     LIMIT 5";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['top_spenders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Clients avec le plus de réservations
            $query = "SELECT c.id, u.nom, u.prenom, COUNT(r.id) as reservations_count
                     FROM Clients c
                     JOIN Utilisateurs u ON c.utilisateur_id = u.id
                     JOIN Reservations r ON r.utilisateur_id = c.utilisateur_id
                     GROUP BY c.id, u.nom, u.prenom
                     ORDER BY reservations_count DESC
                     LIMIT 5";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['most_reservations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des statistiques: " . $e->getMessage();
            return null;
        }
    }

    /**
     * Désactive un client
     */
    public function deactivateClient($client_id) {
        try {
            // Récupérer l'ID utilisateur associé
            $client = $this->getClientById($client_id);
            if (!$client) {
                $this->lastError = "Client non trouvé.";
                return false;
            }
            
            $utilisateur_id = $client['utilisateur_id'];
            
            // Mettre à jour le statut dans la table Utilisateurs
            $query = "UPDATE Utilisateurs SET statut = 'inactif' WHERE id = :utilisateur_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la désactivation du client: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Réactive un client
     */
    public function activateClient($client_id) {
        try {
            // Récupérer l'ID utilisateur associé
            $client = $this->getClientById($client_id);
            if (!$client) {
                $this->lastError = "Client non trouvé.";
                return false;
            }
            
            $utilisateur_id = $client['utilisateur_id'];
            
            // Mettre à jour le statut dans la table Utilisateurs
            $query = "UPDATE Utilisateurs SET statut = 'actif' WHERE id = :utilisateur_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':utilisateur_id', $utilisateur_id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la réactivation du client: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Ajoute une note ou un commentaire sur un client
     */
    public function addClientNote($client_id, $note) {
        try {
            // Vérifier si la table existé déjà, sinon la créer
            $this->createNotesTableIfNotExists();
            
            $query = "INSERT INTO Clients (client_id, note, date_ajout) 
                     VALUES (:client_id, :note, NOW())";
            
            $stmt = $this->db->prepare($query);
            
            $params = [
                ':client_id' => $client_id,
                ':note' => $note
            ];
            
            $result = $stmt->execute($params);
            
            if (!$result) {
                $this->lastError = "Erreur lors de l'ajout de la note: " . implode(", ", $stmt->errorInfo());
                return false;
            }
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            $this->lastError = "Exception: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Crée la table de notes si elle n'existe pas
     */
    private function createNotesTableIfNotExists() {
        try {
            $query = "CREATE TABLE IF NOT EXISTS Clients (
                     id INT AUTO_INCREMENT PRIMARY KEY,
                     client_id INT NOT NULL,
                     note TEXT NOT NULL,
                     date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
                     FOREIGN KEY (client_id) REFERENCES Clients(id) ON DELETE CASCADE
                     )";
            
            $this->db->exec($query);
            return true;
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la création de la table de notes: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Récupère les notes d'un client
     */
    public function getClientNotes($client_id) {
        try {
            // Vérifier si la table existe
            $this->createNotesTableIfNotExists();
            
            $query = "SELECT * FROM Clients WHERE client_id = :client_id ORDER BY date_ajout DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->lastError = "Erreur lors de la récupération des notes: " . $e->getMessage();
            return [];
        }
    }
}
?>