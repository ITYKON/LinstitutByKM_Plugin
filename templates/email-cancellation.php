<?php
// Template d'email d'annulation épuré noir et blanc
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annulation de réservation</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f9f9f9 !important;
            margin: 0;
            padding: 0;
            color: #333333 !important;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }
        .header {
            padding: 32px 24px;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
        }
        .content {
            padding: 32px 24px;
        }
        .booking-card {
            background: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 24px;
            margin: 24px 0;
        }
        .service-info h3 {
            margin: 0 0 8px 0;
            font-size: 18px;
            font-weight: 600;
            color: #000000;
        }
        .detail-item {
            display: flex;
            margin: 16px 0;
            align-items: center;
        }
        .detail-item svg {
            margin-right: 12px;
            color: #666666;
        }
        .cta-button {
            display: inline-block;
            background: #000000;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 4px;
            font-weight: 500;
            margin: 16px 0;
        }
        .footer {
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #999999;
            border-top: 1px solid #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0 0 8px 0; font-size: 24px; font-weight: 600;">Réservation annulée</h1>
            <p style="margin: 0; color: #666666;">Confirmation d'annulation de rendez-vous</p>
        </div>
        
        <div class="content">
            <p>Bonjour <strong><?php echo $client; ?></strong>,</p>
            <p>Nous vous confirmons l'annulation de votre réservation :</p>
            
            <div class="booking-card">
                <div class="service-info">
                    <h3><?php echo $service; ?></h3>
                    <p style="margin: 0; color: #666666;">avec <?php echo $employee; ?></p>
                </div>
                
                <div class="detail-item">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span><?php echo $date; ?></span>
                </div>
                
                <div class="detail-item">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12,6 12,12 16,14"></polyline>
                    </svg>
                    <span><?php echo $time; ?></span>
                </div>
                
                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #f0f0f0;">
                    <p style="margin: 0 0 8px 0; font-size: 14px; color: #666666;">Référence</p>
                    <div style="display: inline-block; background: #f0f0f0; padding: 4px 10px; border-radius: 3px; font-family: monospace; font-size: 13px;">
                        #<?php echo $booking_id; ?>
                    </div>
                </div>
            </div>
            
            <div style="background: #f9f9f9; border-left: 3px solid #dddddd; padding: 16px; margin: 24px 0;">
                <p style="margin: 0 0 8px 0; font-size: 14px; font-weight: 500;">Votre annulation a bien été prise en compte.</p>
                <p style="margin: 0; font-size: 14px;">Un email de confirmation vous a été envoyé.</p>
            </div>
            
            <p>Nous espérons vous revoir bientôt.</p>
            
            <div style="text-align: center; margin: 32px 0 16px;">
                <a href="https://www.linstitutbykm.com" class="cta-button">Prendre un nouveau rendez-vous</a>
            </div>
            
            <p>L'équipe <?php echo $company; ?></p>
        </div>
        
        <div class="footer">
            <p style="margin: 0 0 8px 0;">
                <a href="https://www.linstitutbykm.com" style="color: #999999; text-decoration: none;">www.linstitutbykm.com</a>
            </p>
            <p style="margin: 0; font-size: 12px;">
                Cet email a été envoyé automatiquement, merci de ne pas y répondre.
            </p>
        </div>
    </div>
</body>
</html>
