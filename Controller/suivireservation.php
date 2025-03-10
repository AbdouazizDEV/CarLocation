<?php

// Controller: ReservationController.php
require_once __DIR__ . '/../Models/suivireservation.php';


class suivireservation {
    private $reservation;
    private $notification;

    public function __construct() {
        $this->reservation = new suivireservation();
        $this->notification = new Notifications();
    }

    public function afficherReservations($client_id) {
        $reservations = $this->reservation->getReservationsByUser($client_id);
        include __DIR__ . '/../Views/reservations.php';
    }

    public function changerStatutReservation($reservationId, $statut) {
        if ($this->reservation->updateReservationStatut($reservationId, $statut)) {
            $this->notification->envoyerNotification($reservationId, "Le statut de votre reservation a ete mis a jour : $statut");
        }
        header("Location: /ReservationClient.php");
    }
}

?>
