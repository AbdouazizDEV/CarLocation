
<?php


require_once __DIR__ . "/../Models/voiture.php";

class recupoffres {
    private $voitureModel;

    public function __construct() {
        $this->voitureModel = new voiture(); // Utilisation du modèle voiture
    }

    public function voiture() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
           
            $marque = $_POST['marque'] ?? '';
            $modele = $_POST['modele'] ?? '';
            $annee = $_POST['annee'] ?? '';
            $prix_location = $_POST['prix_location'] ?? '';
            $code = $_POST['code'] ?? '';
            $categorie = $_POST['categorie'] ?? ''; 
            $description = $_POST['description'] ?? ''; 
            $images = $_POST['images'] ?? ''; 
            
           
            $result = $this->voitureModel->create($marque, $modele, $annee, $prix_location, $code, $categorie, $description, $images);

            if ($result) {
                // Redirection après succès
                //header("Location:http://localhost/TPS/LocationVoiture/CarLocation/Views/ajoutvoiture.php");
               require_once __DIR__ . "/../Views/ajoutvoiture.php"; 
               exit;
                //exit();
            } else {
                echo "Erreur lors de l'ajout de la voiture.";
            }
        }
    }
}

// Utilisation du contrôleur
$recupvoiture = new Recupvoiture();
$recupvoiture->voiture();
?>
