<?php
require_once __DIR__ . "/../Database/Connect.php"; 

class User {
    private $db;

    public function __construct() {
        $this->db = new Connect();
    }

    public function findByEmail($email) {
        $stmt = $this->db->getConnection()->prepare("SELECT * FROM Utilisateurs WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
    
        // Ajoutez ce débogage pour vérifier les données récupérées
        /* if ($user) {
            echo "<pre>Utilisateur trouvé : ";
            print_r($user);
            echo "</pre>";
        } else {
            echo "<pre>Aucun utilisateur trouvé pour l'email : $email</pre>";
        } */
        
        return $user;
    }
    public function create($nom, $prenom, $email, $mot_de_passe, $statut) {
        $hashedPassword = password_hash($mot_de_passe, PASSWORD_BCRYPT);

        $stmt = $this->db->getConnection()->prepare("
            INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, statut)
            VALUES (:nom, :prenom, :email, :mot_de_passe, :statut)
        ");

        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':mot_de_passe' => $hashedPassword,
            ':statut' => $statut
        ]);

        return $this->db->getConnection()->lastInsertId(); // Retourne l'ID de l'utilisateur créé
    }
    // pour l'inscription
    public function getConnection() {
        return $this->db->getConnection();
    }
    
}
