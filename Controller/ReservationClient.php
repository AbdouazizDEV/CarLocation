<?php


require_once 'Voitures.php';

class ReservationClient {

    public function afficherFormulaireReservation() {
        // Récupérer les voitures disponibles depuis le modèle
        $voituresDisponibles = Voiture::getVoituresDisponibles();

        // Inclure la vue pour afficher le formulaire
        include 'reserverVoitureView.php';
    }

    public function reserverVoiture() {
        // Vérifier si les données du formulaire sont envoyées
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $voiture_id = $_POST['voiture'];
            $date_debut = $_POST['date_debut'];
            $date_fin = $_POST['date_fin'];
            $statut = $_POST['statut'];
            $montant_total = $_POST['montant_total'];

            // Ajouter la réservation via le modèle
            $reservationAjoutee = Voiture::ajouterReservation($voiture_id, $date_debut, $date_fin, $statut, $montant_total);

            if ($reservationAjoutee) {
                // Redirection ou message de succès
                echo "Réservation réussie !";
            } else {
                // Message d'erreur
                echo "Erreur lors de la réservation.";
            }
        }
    }
}

// Créer une instance du contrôleur et afficher le formulaire
$reservation = new ReservationClient();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reservation->reserverVoiture();
} else {
    $reservation->afficherFormulaireReservation();
}
?>