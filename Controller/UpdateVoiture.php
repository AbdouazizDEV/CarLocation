<?php
session_start();
require_once __DIR__ . "/../Models/voiture.php";

// Vérifier si l'utilisateur est connecté et est un gérant
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'gérant') {
    header('Location: ../Views/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $id = $_POST['id'] ?? '';
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
    
    // Validation des données
    $errors = [];
    
    if (empty($id) || empty($marque) || empty($modele) || empty($annee) || empty($prix_location) || empty($code) || empty($categorie)) {
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
    
    // Si aucune erreur, procéder à la mise à jour
    if (empty($errors)) {
        // Instancier le modèle Voiture
        $voitureModel = new Voiture();
        
        try {
            // On ne met à jour l'image principale que si de nouvelles images ont été téléchargées
            $newImages = null;
            if (!empty($imagesPath)) {
                $newImages = $uploadedImages;
            }
            
            // Mise à jour des informations du véhicule
            $result = $voitureModel->update(
                $id,
                $marque,
                $modele,
                $annee,
                $prix_location,
                $code,
                $categorie,
                $disponibilite,
                $description,
                $newImages
            );
            
            if ($result) {
                $_SESSION['success'] = "Le véhicule a été mis à jour avec succès.";
                
                // Si de nouvelles images ont été ajoutées, les ajouter à la table Images
                if (!empty($uploadedImages)) {
                    foreach ($uploadedImages as $index => $imagePath) {
                        // La première image téléchargée sera définie comme principale
                        // uniquement s'il n'y a pas déjà d'images pour ce véhicule
                        $existingImages = $voitureModel->getImagesForVoiture($id);
                        $est_principale = (empty($existingImages) && $index === 0) ? 1 : 0;
                        
                        $voitureModel->addImage($id, $imagePath, $est_principale);
                    }
                }
            } else {
                $_SESSION['error'] = "Une erreur s'est produite lors de la mise à jour du véhicule. " . $voitureModel->getLastError();
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Exception: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode('<br>', $errors);
    }
    
    // Rediriger vers la page de gestion des véhicules
    header("Location: ../Views/gestion_vehicules.php");
    exit();
}

// Si la méthode n'est pas POST, rediriger vers la page de gestion des véhicules
header("Location: ../Views/gestion_vehicules.php");
exit();
?>