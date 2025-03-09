<?php
session_start();

// Vérifier si l'utilisateur est connecté et est un gérant
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'gérant') {
    echo "Non autorisé";
    exit();
}

// Vérifier si l'ID de la réservation est fourni
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo "ID de réservation non fourni";
    exit();
}

require_once __DIR__ . "/../Model/reservation.php";

$reservationModel = new Reservation();
$result = $reservationModel->delete($_POST['id']);

if ($result) {
    echo "success";
} else {
    echo $reservationModel->getLastError();
}
?>