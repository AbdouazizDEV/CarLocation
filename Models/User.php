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

    // Vous pouvez ajouter d'autres méthodes si nécessaire
}