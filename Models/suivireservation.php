<?php

require_once __DIR__ . "/../Models/suivireservation .php";
// pour suivre les reservations 
class suivireservation {
    private $message;

    public function __construct() {
        
        $this->db->setTable("reservations");
        $this->db->setPrimaryKey("id");
        $this->db->setColumns(["client_id", "voiture_id", "date_debut", "date_fin", "statut"]);
    }

    public function getReservationsByUser($client_id) {
        $this->db->query("SELECT * FROM reservations WHERE client_id = :client_id");
        $this->db->bind(":client_id", $client_id);
        return $this->db->resultSet();
    }

    public function updateReservationStatut($reservationId, $statut) {
        $this->db->query("UPDATE reservations SET statut = :statut WHERE id = :id");
        $this->db->bind(":statut", $statut);
        $this->db->bind(":id", $reservationId);
        return $this->db->execute();
    }
}

//pour  Recevoir des notifications
class Notifications {
    private $notif;

    public function __construct() {
        $this->notif = new notif();
    }

    public function envoyerNotification($client_id, $message) {
        $notification = ["client_id" => $client_id, "message" => $message];
        
    }
}

// Utilisation
$notification = new Notifications();
$notification->envoyerNotification(1, "Votre réservation a été confirmée !");

?>
