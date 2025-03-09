<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailSender {
    /**
     * Envoie un email de facture au client
     * 
     * @param array $facture La facture complète avec ses relations
     * @param string $pdfPath Chemin vers le fichier PDF de la facture (optionnel)
     * @return bool Succès ou échec de l'envoi
     */
    public static function sendInvoiceEmail($facture, $pdfPath = null) {
        // Vérifier si PHPMailer est disponible
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            // Si PHPMailer n'est pas disponible, utiliser la méthode mail() native
            return self::sendInvoiceEmailNative($facture, $pdfPath);
        }
        
        try {
            // Récupérer les informations nécessaires
            $clientEmail = $facture['client']['email'];
            $clientNom = $facture['client']['nom'];
            $clientPrenom = $facture['client']['prenom'];
            $factureNum = 'FACT-' . str_pad($facture['id'], 5, '0', STR_PAD_LEFT);
            $montant = number_format($facture['montant_total'], 0, ',', ' ') . ' FCFA';
            $dateEmission = date('d/m/Y', strtotime($facture['date_emission']));
            
            // Créer une nouvelle instance de PHPMailer
            $mail = new PHPMailer(true);
            
            // Configurer pour utiliser SMTP si nécessaire
            // $mail->isSMTP();
            // $mail->Host = 'smtp.example.com';
            // $mail->SMTPAuth = true;
            // $mail->Username = 'user@example.com';
            // $mail->Password = 'password';
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            // $mail->Port = 587;
            
            // Pour le développement local, utiliser le mode sans SMTP
            $mail->isMail();
            
            // Configuration de l'expéditeur et du destinataire
            $mail->setFrom('contact@ndaamar.com', 'NDAAMAR Location');
            $mail->addAddress($clientEmail, $clientPrenom . ' ' . $clientNom);
            $mail->addReplyTo('contact@ndaamar.com', 'NDAAMAR Location');
            
            // Configuration de l'email
            $mail->isHTML(true);
            $mail->Subject = "Facture $factureNum - Paiement confirmé";
            
            // Corps de l'email en HTML
            $mail->Body = "
            <html>
            <head>
                <title>Confirmation de paiement</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #0d47a1; color: white; padding: 15px; text-align: center; }
                    .content { padding: 20px; border: 1px solid #ddd; }
                    .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                    .invoice-details { background-color: #f8f9fa; padding: 15px; margin: 15px 0; }
                    .button { display: inline-block; background-color: #0d47a1; color: white; padding: 10px 20px; 
                            text-decoration: none; border-radius: 4px; margin-top: 15px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>NDAAMAR Location de voitures</h2>
                    </div>
                    <div class='content'>
                        <p>Bonjour $clientPrenom $clientNom,</p>
                        
                        <p>Nous vous confirmons la bonne réception de votre paiement pour la facture <strong>$factureNum</strong>.</p>
                        
                        <div class='invoice-details'>
                            <p><strong>Numéro de facture :</strong> $factureNum</p>
                            <p><strong>Date d'émission :</strong> $dateEmission</p>
                            <p><strong>Montant :</strong> $montant</p>
                            <p><strong>Statut :</strong> <span style='color: green;'>Payée</span></p>
                        </div>
                        
                        <p>Vous trouverez ci-joint votre facture acquittée. Vous pouvez également consulter votre facture en vous connectant à votre espace client.</p>
                        
                        <p>Nous vous remercions pour votre confiance et restons à votre disposition pour tout renseignement complémentaire.</p>
                        
                        <p>Cordialement,<br>
                        L'équipe NDAAMAR Location</p>
                    </div>
                    <div class='footer'>
                        <p>NDAAMAR Location de voitures - 123 Avenue de la République, Dakar, Sénégal</p>
                        <p>Tél : (+221) 33 123 45 67 - Email : contact@ndaamar.com</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            // Version texte brut pour les clients qui ne prennent pas en charge HTML
            $mail->AltBody = "Bonjour $clientPrenom $clientNom,\n\n" .
                            "Nous vous confirmons la bonne réception de votre paiement pour la facture $factureNum.\n\n" .
                            "Numéro de facture : $factureNum\n" .
                            "Date d'émission : $dateEmission\n" .
                            "Montant : $montant\n" .
                            "Statut : Payée\n\n" .
                            "Vous trouverez ci-joint votre facture acquittée.\n\n" .
                            "Nous vous remercions pour votre confiance.\n\n" .
                            "Cordialement,\n" .
                            "L'équipe NDAAMAR Location";
            
            // Ajouter la pièce jointe si elle existe
            if ($pdfPath && file_exists($pdfPath)) {
                $mail->addAttachment($pdfPath, 'Facture_' . $factureNum . '.pdf');
            }
            
            // Envoyer l'email
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Erreur lors de l'envoi de l'email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Méthode de secours utilisant la fonction mail() native de PHP
     */
    private static function sendInvoiceEmailNative($facture, $pdfPath = null) {
        // Cette méthode utilise la fonction mail() comme dans votre code original
        // Je vous conseille d'utiliser plutôt PHPMailer (méthode ci-dessus)
        
        // Récupérer les informations nécessaires
        $clientEmail = $facture['client']['email'];
        $clientNom = $facture['client']['nom'];
        $clientPrenom = $facture['client']['prenom'];
        $factureNum = 'FACT-' . str_pad($facture['id'], 5, '0', STR_PAD_LEFT);
        $montant = number_format($facture['montant_total'], 0, ',', ' ') . ' FCFA';
        $dateEmission = date('d/m/Y', strtotime($facture['date_emission']));
        
        // En-têtes de l'email
        $headers = "From: NDAAMAR Location <contact@ndaamar.com>\r\n";
        $headers .= "Reply-To: contact@ndaamar.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        // Sujet de l'email
        $subject = "Facture $factureNum - Paiement confirmé";
        
        // Corps de l'email en HTML (même contenu que dans la méthode PHPMailer)
        $message = "
        <html>
        <head>
            <title>Confirmation de paiement</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #0d47a1; color: white; padding: 15px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                .invoice-details { background-color: #f8f9fa; padding: 15px; margin: 15px 0; }
                .button { display: inline-block; background-color: #0d47a1; color: white; padding: 10px 20px; 
                          text-decoration: none; border-radius: 4px; margin-top: 15px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>NDAAMAR Location de voitures</h2>
                </div>
                <div class='content'>
                    <p>Bonjour $clientPrenom $clientNom,</p>
                    
                    <p>Nous vous confirmons la bonne réception de votre paiement pour la facture <strong>$factureNum</strong>.</p>
                    
                    <div class='invoice-details'>
                        <p><strong>Numéro de facture :</strong> $factureNum</p>
                        <p><strong>Date d'émission :</strong> $dateEmission</p>
                        <p><strong>Montant :</strong> $montant</p>
                        <p><strong>Statut :</strong> <span style='color: green;'>Payée</span></p>
                    </div>
                    
                    <p>Merci pour votre paiement. Votre facture est maintenant acquittée.</p>
                    
                    <p>Cordialement,<br>
                    L'équipe NDAAMAR Location</p>
                </div>
                <div class='footer'>
                    <p>NDAAMAR Location de voitures - 123 Avenue de la République, Dakar, Sénégal</p>
                    <p>Tél : (+221) 33 123 45 67 - Email : contact@ndaamar.com</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Pour le débogage - sauvegarder l'email dans un fichier
        $debugFile = __DIR__ . '/../temp/email_debug_' . time() . '.html';
        file_put_contents($debugFile, $message);
        
        // Envoyer l'email (sans pièce jointe pour cette version simplifiée)
        return mail($clientEmail, $subject, $message, $headers);
    }
}
?>