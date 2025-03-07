
<?php


require_once __DIR__ . "/../Models/offre.php";

class recupoffres {
    private $offreModel;

    public function __construct() {
        $this->offreModel = new offre(); // Utilisation du modèle offre
    }

    public function offre() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {


          
            $voiture_id = $_POST['voiture_id'] ?? ''; // ID de la voiture choisi par l'utilisateur
            $description = $_POST['description'] ?? ''; 
            $date_debut = $_POST['date_fin'] ?? '';
            $date_fin = $_POST['date_debut'] ?? '';  
            $prix_special = $_POST['prix_special'] ?? '';
         
            
           
            $result = $this->offreModel->create( $voiture_id,$description, $date_debut,$date_fin,$prix_special);

            if ($result) {
               
               require_once __DIR__ . "/../Views/offres.php"; 
               exit;
             
            } else {
                echo "Erreur lors de le creation d une nouvelle offre.";
            }
        }
    }
}

// Utilisation du contrôleur
$offre= new recupoffres();
$offre->offre();

?>
