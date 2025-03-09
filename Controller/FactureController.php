<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/../Models/Facture.php";
require_once __DIR__ . "/../Models/Reservation.php";
require_once __DIR__ . "/../Models/client.php";
require_once __DIR__ . "/../Models/voiture.php";

// Vérifier si l'utilisateur est connecté et est un gérant
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'gérant') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
        // Pour les requêtes AJAX de récupération de données, renvoyer une erreur JSON
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Accès non autorisé']);
        exit();
    } else {
        // Pour les autres requêtes, rediriger vers la page de connexion
        header('Location: ../Views/login.php');
        exit();
    }
}

// Instanciation des modèles
$factureModel = new Facture();
$reservationModel = new Reservation();
$clientModel = new Client();
$voitureModel = new Voiture();

// Traitement des requêtes GET (pour récupérer des données)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    
    switch ($action) {
        case 'get':
            // Récupérer les détails d'une facture
            $id = $_GET['id'] ?? 0;
            
            if (empty($id)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'ID facture manquant']);
                exit();
            }
            
            $facture = $factureModel->getFactureById($id);
            
            if (!$facture) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Facture non trouvée']);
                exit();
            }
            
            header('Content-Type: application/json');
            echo json_encode($facture);
            exit();
            break;
    }
}

// Traitement des requêtes POST (pour modifier des données)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            // Créer une nouvelle facture
            $reservation_id = $_POST['reservation_id'] ?? '';
            $client_id = $_POST['client_id'] ?? '';
            $date_emission = $_POST['date_emission'] ?? date('Y-m-d');
            $date_echeance = $_POST['date_echeance'] ?? date('Y-m-d', strtotime('+15 days'));
            $montant_total = $_POST['montant_total'] ?? 0;
            $notes = $_POST['notes'] ?? null;
            
            // Validation des données
            $errors = [];
            
            if (empty($reservation_id)) {
                $errors[] = "Veuillez sélectionner une réservation.";
            }
            
            if (empty($client_id)) {
                $errors[] = "ID client manquant.";
            }
            
            if (empty($date_emission) || empty($date_echeance)) {
                $errors[] = "Les dates d'émission et d'échéance sont obligatoires.";
            }
            
            if (empty($montant_total) || $montant_total <= 0) {
                $errors[] = "Le montant total doit être supérieur à zéro.";
            }
            
            if (empty($errors)) {
                $result = $factureModel->create(
                    $reservation_id,
                    $client_id,
                    $date_emission,
                    $date_echeance,
                    $montant_total,
                    $notes
                );
                
                if ($result) {
                    $_SESSION['success'] = "La facture a été créée avec succès.";
                } else {
                    $_SESSION['error'] = "Erreur lors de la création de la facture: " . $factureModel->getLastError();
                }
            } else {
                $_SESSION['error'] = implode("<br>", $errors);
            }
            
            header('Location: ../Views/facturation.php');
            exit();
            break;
            
            case 'markAsPaid':
                // Marquer une facture comme payée
                $facture_id = $_POST['facture_id'] ?? '';
                $methode_paiement = $_POST['methode_paiement'] ?? '';
                $reference_paiement = $_POST['reference_paiement'] ?? null;
                
                // Validation des données
                $errors = [];
                
                if (empty($facture_id)) {
                    $errors[] = "ID facture manquant.";
                }
                
                if (empty($methode_paiement)) {
                    $errors[] = "Veuillez sélectionner une méthode de paiement.";
                }
                
                if (empty($errors)) {
                    $result = $factureModel->markAsPaid($facture_id, $methode_paiement, $reference_paiement);
                    
                    if ($result) {
                        // Récupérer les détails complets de la facture pour l'email
                        $facture = $factureModel->getFactureById($facture_id);
                        
                        // Inclure la classe EmailSender
                        require_once __DIR__ . "/../Utils/EmailSender.php";
                        
                        // Générer le PDF de la facture
                        $pdfFileName = 'facture_' . $facture_id . '.pdf';
                        $pdfPath = __DIR__ . '/../temp/' . $pdfFileName;
                        
                        // Vérifier si le dossier temp existe, sinon le créer
                        if (!file_exists(__DIR__ . '/../temp/')) {
                            mkdir(__DIR__ . '/../temp/', 0755, true);
                        }
                        
                        // Générer le PDF (nous allons implémenter cette fonction)
                        if (generateInvoicePDF($facture, $pdfPath)) {
                            // Envoyer l'email avec le PDF joint
                            $emailSent = EmailSender::sendInvoiceEmail($facture, $pdfPath);
                            
                            if ($emailSent) {
                                $_SESSION['success'] = "La facture a été marquée comme payée avec succès et un email de confirmation a été envoyé au client.";
                            } else {
                                $_SESSION['success'] = "La facture a été marquée comme payée avec succès mais l'envoi de l'email a échoué.";
                            }
                            
                            // Supprimer le fichier temporaire
                            @unlink($pdfPath);
                        } else {
                            // Envoyer l'email sans le PDF
                            $emailSent = EmailSender::sendInvoiceEmail($facture);
                            
                            if ($emailSent) {
                                $_SESSION['success'] = "La facture a été marquée comme payée avec succès et un email de confirmation a été envoyé au client (sans PDF joint).";
                            } else {
                                $_SESSION['success'] = "La facture a été marquée comme payée avec succès mais l'envoi de l'email a échoué.";
                            }
                        }
                    } else {
                        $_SESSION['error'] = "Erreur lors du marquage de la facture comme payée: " . $factureModel->getLastError();
                    }
                } else {
                    $_SESSION['error'] = implode("<br>", $errors);
                }
                
                header('Location: ../Views/facturation.php');
                exit();
                break;
    }
}
/**
 * Génère un fichier PDF de la facture
 *
 * @param array $facture La facture avec ses relations
 * @param string $outputPath Chemin complet où enregistrer le PDF
 * @return bool Succès ou échec de la génération
 */
function generateInvoicePDF($facture, $outputPath) {
    // Si vous n'avez pas de bibliothèque PDF installée, utilisez cette version simple avec FPDF
    // Nécessite l'installation de FPDF: composer require setasign/fpdf
    
    if (!class_exists('FPDF')) {
        // Si FPDF n'est pas disponible, essayez de l'inclure directement
        $fpdfPath = __DIR__ . '/../vendor/fpdf/fpdf.php';
        if (file_exists($fpdfPath)) {
            require_once $fpdfPath;
        } else {
            // Si FPDF n'est pas disponible, retournez false
            return false;
        }
    }
    
    try {
        // Créer une nouvelle instance de FPDF
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Titre de la facture
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'FACTURE', 0, 1, 'C');
        $pdf->Ln(5);
        
        // Informations de la société
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'NDAAMAR Location de voitures', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, '123 Avenue de la République', 0, 1);
        $pdf->Cell(0, 6, 'Dakar, Sénégal', 0, 1);
        $pdf->Cell(0, 6, 'Tel: (+221) 33 123 45 67', 0, 1);
        $pdf->Cell(0, 6, 'Email: contact@ndaamar.com', 0, 1);
        $pdf->Ln(10);
        
        // Détails de la facture
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Facture N°: FACT-' . str_pad($facture['id'], 5, '0', STR_PAD_LEFT), 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, 'Date d\'émission: ' . date('d/m/Y', strtotime($facture['date_emission'])), 0, 1);
        $pdf->Cell(0, 6, 'Date d\'échéance: ' . date('d/m/Y', strtotime($facture['date_echeance'])), 0, 1);
        $pdf->Cell(0, 6, 'Statut: PAYÉE', 0, 1);
        $pdf->Ln(10);
        
        // Informations client
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Facturé à:', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, $facture['client']['prenom'] . ' ' . $facture['client']['nom'], 0, 1);
        $pdf->Cell(0, 6, 'Email: ' . $facture['client']['email'], 0, 1);
        if (!empty($facture['client']['telephone'])) {
            $pdf->Cell(0, 6, 'Téléphone: ' . $facture['client']['telephone'], 0, 1);
        }
        if (!empty($facture['client']['adresse'])) {
            $pdf->Cell(0, 6, 'Adresse: ' . $facture['client']['adresse'], 0, 1);
        }
        $pdf->Ln(10);
        
        // Détails de la réservation
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Détails de la réservation:', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, 'Réservation N°: RES-' . str_pad($facture['reservation_id'], 5, '0', STR_PAD_LEFT), 0, 1);
        $pdf->Cell(0, 6, 'Véhicule: ' . $facture['vehicule']['marque'] . ' ' . $facture['vehicule']['modele'], 0, 1);
        $pdf->Cell(0, 6, 'Période: ' . date('d/m/Y', strtotime($facture['reservation']['date_debut'])) . ' - ' . date('d/m/Y', strtotime($facture['reservation']['date_fin'])), 0, 1);
        
        // Calculer la durée en jours
        $dateDebut = new DateTime($facture['reservation']['date_debut']);
        $dateFin = new DateTime($facture['reservation']['date_fin']);
        $duree = $dateDebut->diff($dateFin)->days;
        $pdf->Cell(0, 6, 'Durée: ' . $duree . ' jour(s)', 0, 1);
        $pdf->Ln(10);
        
        // Tableau des prestations
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(90, 10, 'Description', 1, 0, 'L');
        $pdf->Cell(30, 10, 'Prix unitaire', 1, 0, 'R');
        $pdf->Cell(30, 10, 'Quantité', 1, 0, 'R');
        $pdf->Cell(40, 10, 'Total', 1, 1, 'R');
        
        $pdf->SetFont('Arial', '', 10);
        $prixUnitaire = $facture['montant_total'] / $duree;
        $prixUnitaireFormatted = number_format($prixUnitaire, 0, ',', ' ') . ' FCFA';
        $prixTotalFormatted = number_format($facture['montant_total'], 0, ',', ' ') . ' FCFA';
        
        $pdf->Cell(90, 10, 'Location de véhicule - ' . $facture['vehicule']['marque'] . ' ' . $facture['vehicule']['modele'], 1, 0, 'L');
        $pdf->Cell(30, 10, $prixUnitaireFormatted, 1, 0, 'R');
        $pdf->Cell(30, 10, $duree . ' jour(s)', 1, 0, 'R');
        $pdf->Cell(40, 10, $prixTotalFormatted, 1, 1, 'R');
        
        // Total
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(150, 10, 'Total:', 0, 0, 'R');
        $pdf->Cell(40, 10, $prixTotalFormatted, 0, 1, 'R');
        
        // Notes
        if (!empty($facture['notes'])) {
            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 8, 'Notes:', 0, 1);
            $pdf->SetFont('Arial', '', 10);
            $pdf->MultiCell(0, 6, $facture['notes']);
        }
        
        // Pied de page
        $pdf->Ln(20);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Merci de votre confiance!', 0, 1, 'C');
        $pdf->Cell(0, 5, 'NDAAMAR Location de voitures - SIRET: 123 456 789 00010 - RC: 123456789', 0, 1, 'C');
        
        // Enregistrer le PDF
        $pdf->Output('F', $outputPath);
        
        return true;
    } catch (Exception $e) {
        error_log("Erreur lors de la génération du PDF: " . $e->getMessage());
        return false;
    }
}
// Si aucune action correspondante n'a été traitée, rediriger vers la page de facturation
header('Location: ../Views/facturation.php');
exit();
?>