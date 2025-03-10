<?php

// Modèle : Voiture.php
class Voiture {
    public $id;
    public $marque;

    public function __construct($id, $marque) {
        $this->id = $id;
        $this->marque = $marque;
    }

    // Récupérer les voitures disponibles
    public static function getVoituresDisponibles() {
        // Connexion à la base de données et récupération des voitures disponibles
        // Exemple fictif avec des données statiques
        return [
            new Voiture(1, "Toyota Corolla"),
            new Voiture(2, "Honda Civic"),
            new Voiture(3, "BMW 320i")
        ];
    }

    // Ajouter une réservation
    public static function ajouterReservation($voiture_id, $date_debut, $date_fin, $statut, $montant_total) {
        // Logique d'ajout de réservation dans la base de données
        // Exemple fictif
        return true;
    }
}
?>