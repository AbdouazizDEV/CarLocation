<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réservation de Voiture</title>
</head>
<body>
    <h1>Reservez une voiture</h1>
    <form action="ReserverClient.php " method="POST">
        
        <label for="voiture">Choisissez une voiture</label>
        <select name="voiture" id="voiture">
            <?php foreach ($voituresDisponibles as $voiture): ?>
                <option value="<?= $voiture ?>"><?= $voiture ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="date">Date debut (JJ-MM-AAAA)</label>
        <input type="text" id="date" name="date_debut" required><br><br>

        <label for="date_fin">Date fin (JJ-MM-AAAA)</label>
        <input type="text" id="date_fin" name="date_fin" required><br><br>
        
                          <div>
                                <label for="edit_statut" class="form-label">Statut</label>
                                <select class="form-select" name="statut" id="edit_statut" required>
                                    <option value="en_attente">En attente</option>
                                    <option value="confirmee">Confirmee</option>
                                    <option value="en_cours">En cours</option>
                                    <option value="terminee">Terminee</option>
                                    <option value="annulee">Annulée</option>
                                </select>
                            </div>
        <label for="prix">montant  total</label>
        <input type="text" id="prix" name="montant _total" required><br><br>

        <input type="submit" value="Réserver">
    </form>
</body>
</html>
<?php
require_once __DIR__ . "/../Database/Connect.php";

class User {
    private $db;

    public function __construct() {
        $connect = new Connect();
        $this->db = $connect->getConnection();
    }

    public function create($nom, $prenom, $email, $mot_de_passe, $role = 'client', $statut = 'actif') {
        $query = "INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, role, statut) VALUES (:nom, :prenom, :email, :mot_de_passe, :role, :statut)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mot_de_passe', $mot_de_passe);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':statut', $statut);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    public function findByEmail($email) {
        $query = "SELECT * FROM Utilisateurs WHERE email = :email";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $result = $stmt->fetchAll();
        
        if (!empty($result)) {
            return $result[0];
        } else {
            return false;
        }
    }

    public function findById($id) {
        $query = "SELECT * FROM Utilisateurs WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $result = $stmt->fetchAll();
        
        if (count($result) > 0) {
            return $result[0];
        } else {
            return false;
        }
    }

    public function update($id, $prenom, $nom, $email, $telephone = null) {
        $query = "UPDATE Utilisateurs SET prenom = :prenom, nom = :nom, email = :email";
        
        $params = [
            ':prenom' => $prenom,
            ':nom' => $nom,
            ':email' => $email,
            ':id' => $id
        ];
        
        if ($telephone !== null) {
            $query .= ", telephone = :telephone";
            $params[':telephone'] = $telephone;
        }
        
        $query .= " WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'utilisateur : " . $e->getMessage());
            return false;
        }
    }
    // Vous pouvez ajouter d'autres méthodes si nécessaire
}