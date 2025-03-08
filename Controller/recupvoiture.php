<?php
session_start();
require_once __DIR__ . "/../Models/voiture.php";

class recupvoiture{
    private $voitureModel;

    public function __construct() {
        $this->voitureModel = new Voiture();
    }

    public function voiture() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Récupérer les données du formulaire
            $marque = $_POST['marque'] ?? '';
            $modele = $_POST['modele'] ?? '';
            $annee = $_POST['annee'] ?? '';
            $prix_location = $_POST['prix_location'] ?? '';
            $code = $_POST['code'] ?? '';
            $categorie = $_POST['categorie'] ?? '';
            $description = $_POST['description'] ?? '';
            $statut = $_POST['statut'] ?? 'disponible';
            
            // Convertir le statut en valeur numérique pour la disponibilité
            $disponibilite = match($statut) {
                'disponible' => 1,
                'loué' => 2,
                'maintenance' => 3,
                'indisponible' => 0,
                default => 1
            };
            
            // Débogage - Afficher les valeurs pour vérification
            error_log("Données de formulaire : " . json_encode([
                'marque' => $marque,
                'modele' => $modele,
                'annee' => $annee,
                'prix_location' => $prix_location,
                'code' => $code,
                'categorie' => $categorie,
                'description' => $description,
                'statut' => $statut,
                'disponibilite' => $disponibilite
            ]));
            
            // Validation des données
            $errors = [];
            
            if (empty($marque) || empty($modele) || empty($annee) || empty($prix_location) || empty($code) || empty($categorie)) {
                $errors[] = "Tous les champs obligatoires doivent être remplis.";
            }
            
            // Traitement des images
            $imagesPath = '';
            
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                // Utiliser un chemin relatif à la racine du projet
                $uploadDir = __DIR__ . '/../uploads/';
                
                // Vérifier si le dossier existe, sinon le créer
                if (!file_exists($uploadDir)) {
                    if (!mkdir($uploadDir, 0755, true)) {
                        $errors[] = "Impossible de créer le dossier d'upload. Veuillez contacter l'administrateur.";
                    }
                }
                
                // Vérifier les permissions du dossier
                if (!is_writable($uploadDir)) {
                    $errors[] = "Le dossier d'upload n'a pas les permissions d'écriture. Veuillez contacter l'administrateur.";
                }
                
                if (empty($errors)) {
                    $uploadedImages = [];
                    
                    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                        if (empty($tmp_name)) continue; // Ignorer les entrées vides
                        
                        $filename = $_FILES['images']['name'][$key];
                        $fileTmpName = $_FILES['images']['tmp_name'][$key];
                        $fileSize = $_FILES['images']['size'][$key];
                        $fileError = $_FILES['images']['error'][$key];
                        
                        // Vérifier s'il y a des erreurs
                        if ($fileError === 0) {
                            // Générer un nom unique pour éviter les doublons
                            $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
                            $newFilename = uniqid('veh_') . '.' . $fileExtension;
                            $destination = $uploadDir . $newFilename;
                            
                            // Déplacer le fichier avec gestion d'erreur
                            if (move_uploaded_file($fileTmpName, $destination)) {
                                // Stocker un chemin relatif pour l'accès via l'application web
                                $uploadedImages[] = 'uploads/' . $newFilename;
                            } else {
                                $uploadError = error_get_last();
                                $errors[] = "Erreur lors de l'upload de l'image {$filename}. " . ($uploadError ? $uploadError['message'] : '');
                            }
                        } else {
                            // Traduire les codes d'erreur PHP
                            $errorMessage = match($fileError) {
                                UPLOAD_ERR_INI_SIZE => "L'image dépasse la taille maximale définie dans php.ini",
                                UPLOAD_ERR_FORM_SIZE => "L'image dépasse la taille maximale définie dans le formulaire",
                                UPLOAD_ERR_PARTIAL => "L'image n'a été que partiellement téléchargée",
                                UPLOAD_ERR_NO_FILE => "Aucun fichier n'a été téléchargé",
                                UPLOAD_ERR_NO_TMP_DIR => "Dossier temporaire manquant",
                                UPLOAD_ERR_CANT_WRITE => "Échec d'écriture du fichier sur le disque",
                                UPLOAD_ERR_EXTENSION => "Une extension PHP a arrêté l'upload",
                                default => "Erreur inconnue"
                            };
                            $errors[] = "Erreur lors de l'upload de l'image {$filename}: {$errorMessage}";
                        }
                    }
                    
                    if (!empty($uploadedImages)) {
                        $imagesPath = implode(',', $uploadedImages);
                    }
                }
            }
            
            // Si aucune erreur, procéder à l'ajout
            if (empty($errors)) {
                try {
                    error_log("Tentative d'ajout en BDD avec les données : " . json_encode([
                        'marque' => $marque,
                        'modele' => $modele,
                        'annee' => $annee,
                        'prix_location' => $prix_location,
                        'code' => $code,
                        'categorie' => $categorie,
                        'description' => $description,
                        'disponibilite' => $disponibilite,
                        'images' => $imagesPath
                    ]));
                    
                    $result = $this->voitureModel->create(
                        $marque, 
                        $modele, 
                        $annee, 
                        $prix_location, 
                        $code, 
                        $categorie, 
                        $disponibilite,  // Passez la disponibilité en fonction du statut
                        $imagesPath,     // Chemin des images (séparés par des virgules)
                        $description     // Description
                    );
                    
                    if ($result) {
                        error_log("Ajout réussi, ID: " . $result);
                        $_SESSION['success'] = "Le véhicule a été ajouté avec succès. (ID: " . $result . ")";
                        header("Location: ../Views/gestion_vehicules.php");
                        exit();
                    } else {
                        error_log("Ajout échoué, aucun ID retourné. Erreur: " . $this->voitureModel->getLastError());
                        $_SESSION['error'] = "Une erreur s'est produite lors de l'ajout du véhicule dans la base de données: " . 
                                            $this->voitureModel->getLastError();
                    }
                } catch (Exception $e) {
                    error_log("Exception lors de l'ajout en BDD: " . $e->getMessage());
                    $_SESSION['error'] = "Exception: " . $e->getMessage();
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
                error_log("Erreurs de validation: " . implode(', ', $errors));
            }
            
            // En cas d'erreur, rediriger vers la page de gestion des véhicules
            header("Location: ../Views/gestion_vehicules.php");
            exit();
        } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && isset($_GET['id'])) {
            // Actions supplémentaires pour gérer les images
            $voitureId = intval($_GET['id']);
            $action = $_GET['action'];
            
            switch ($action) {
                case 'set_main_image':
                    if (isset($_GET['image_id'])) {
                        $imageId = intval($_GET['image_id']);
                        if ($this->voitureModel->updateMainImage($voitureId, $imageId)) {
                            $_SESSION['success'] = "L'image principale a été mise à jour avec succès.";
                        } else {
                            $_SESSION['error'] = "Erreur lors de la mise à jour de l'image principale.";
                        }
                    }
                    break;
                
                case 'delete_image':
                    if (isset($_GET['image_id'])) {
                        $imageId = intval($_GET['image_id']);
                        if ($this->voitureModel->deleteImage($imageId)) {
                            $_SESSION['success'] = "L'image a été supprimée avec succès.";
                        } else {
                            $_SESSION['error'] = "Erreur lors de la suppression de l'image.";
                        }
                    }
                    break;
            }
            
            // Rediriger vers la page de détails du véhicule
            header("Location: ../Views/gestion_vehicules.php?id=" . $voitureId);
            exit();
        }
    }
}

// Instanciation et exécution du contrôleur
$recupVoiture = new recupvoiture();
$recupVoiture->voiture();
?>

