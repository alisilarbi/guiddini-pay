<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f0f2f5;
        }

        .container {
            max-width: 48rem;
            margin: 0 auto;
            background-color: white;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo-circle {
            width: 3rem;
            height: 3rem;
            background-color: #f5f6f7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .checkmark-circle {
            width: 2rem;
            height: 2rem;
            color: #27ae60;
        }

        .company-name {
            font-size: 1.125rem;
            font-weight: 600;
        }

        .header-right {
            width: 5rem;
            height: 3rem;
            position: relative;
        }

        .header-right-inner {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.25rem;
        }

        .header-right-text {
            color: #3b1c84;
            font-weight: bold;
            font-size: 1.25rem;
        }

        .badge {
            position: absolute;
            top: -0.25rem;
            right: -0.25rem;
            background-color: #facc15;
            color: #111827;
            font-size: 0.5rem;
            padding: 0.25rem;
            border-radius: 0.125rem;
        }

        .title {
            text-align: center;
            margin-bottom: 1rem;
        }

        .title h1 {
            font-size: 1.25rem;
            font-weight: bold;
            color: #111827;
        }

        .title-sub {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .title-sub span.fawtara {
            color: #3b1c84;
            font-weight: 600;
        }

        .title p {
            font-size: 0.875rem;
            color: #6b7280;
        }

        hr {
            border: 0;
            border-top: 1px solid #d1d5db;
            margin: 1rem 0;
        }

        .success-icon {
            display: flex;
            justify-content: center;
            margin: 2rem 0;
            position: relative;
        }

        .success-circle {
            width: 5rem;
            height: 5rem;
            background-color: #27ae60;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .success-circle svg {
            width: 2.5rem;
            height: 2.5rem;
        }

        .decorations {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .decor-dot {
            position: absolute;
            background-color: #27ae60;
            border-radius: 50%;
        }

        .decor-dot-1 {
            top: 25%;
            left: 33%;
            width: 0.5rem;
            height: 0.5rem;
        }

        .decor-dot-2 {
            top: 33%;
            right: 33%;
            width: 0.5rem;
            height: 0.5rem;
        }

        .decor-dot-3 {
            bottom: 25%;
            left: 33%;
            width: 0.5rem;
            height: 0.5rem;
        }

        .decor-dot-4 {
            top: 50%;
            right: 25%;
            width: 0.5rem;
            height: 0.5rem;
        }

        .decor-ring-1 {
            position: absolute;
            top: 25%;
            right: 50%;
            width: 0.75rem;
            height: 0.75rem;
            border: 2px solid #27ae60;
            border-radius: 50%;
            border-top-color: transparent;
            border-left-color: transparent;
            transform: rotate(45deg);
        }

        .decor-ring-2 {
            position: absolute;
            bottom: 25%;
            right: 50%;
            width: 0.75rem;
            height: 0.75rem;
            border: 2px solid #27ae60;
            border-radius: 50%;
            border-bottom-color: transparent;
            border-right-color: transparent;
            transform: rotate(45deg);
        }

        .success-message {
            text-align: center;
            margin-bottom: 2rem;
        }

        .success-message h2 {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .success-message p {
            color: #6b7280;
            line-height: 1.5;
        }

        .section {
            margin-bottom: 1.5rem;
        }

        .section h3 {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .info-card {
            background-color: #f5f6f7;
            border-radius: 0.5rem;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-row {
            display: flex;
        }

        .info-label {
            width: 7rem;
            color: #6b7280;
        }

        .payment-details .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .payment-details .grid div {
            padding: 0.75rem;
        }

        .payment-details .grid div:nth-child(odd) {
            background-color: #3b1c84;
            color: white;
            font-weight: 500;
        }

        .payment-details .grid div:nth-child(even) {
            background-color: #f5f6f7;
            text-align: right;
        }

        .support-card {
            background-color: #f5f6f7;
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
        }

        .support-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #27ae60;
            color: white;
            border-radius: 9999px;
            padding: 0.5rem 1.5rem;
        }

        .phone-icon {
            background-color: white;
            border-radius: 50%;
            padding: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .phone-icon svg {
            width: 1.25rem;
            height: 1.25rem;
            color: #27ae60;
        }

        .support-button-text {
            display: flex;
            flex-direction: column;
        }

        .support-button-text .small {
            font-size: 0.75rem;
        }

        .support-button-text .large {
            font-size: 1.25rem;
            font-weight: bold;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
        }

        .footer-left {
            color: #6b7280;
        }

        .footer-left .highlight {
            color: #3b1c84;
            font-weight: 500;
        }

        .footer-right {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            color: #6b7280;
        }

        .footer-right .fawtara {
            color: #3b1c84;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">


            <div class="header-left">
                <div class="logo-circle">
                    <div class="checkmark-circle">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                                stroke="currentColor" stroke-width="2" />
                            <path d="M8 12L11 15L16 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
                <span class="company-name">Sarl Pointili</span>
            </div>
            <div class="header-right">
                <div class="header-right-inner">
                    <div class="header-right-text">SB</div>
                    <div class="badge">الجزائر</div>
                </div>
            </div>


        </div>



        <!-- Title -->
        <div class="title">
            <h1>Reçu de paiement éléctronique</h1>
            <div class="title-sub">
                <span>E-payment by</span>
                <span class="fawtara">Fawtara</span>
            </div>
            <p>Go paperless, Go effortless</p>
        </div>

        <hr>

        <!-- Success Icon -->
        <div class="success-icon">
            <div class="success-circle">
                <!-- Check icon SVG -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div class="decorations">
                <div class="decor-dot decor-dot-1"></div>
                <div class="decor-dot decor-dot-2"></div>
                <div class="decor-dot decor-dot-3"></div>
                <div class="decor-dot decor-dot-4"></div>
                <div class="decor-ring-1"></div>
                <div class="decor-ring-2"></div>
            </div>
        </div>

        <!-- Success Message -->
        <div class="success-message">
            <h2>Paiement Réussi !</h2>
            <p>
                Votre paiement a été traité avec succès. Merci pour
                <br>
                votre paiement rapide.
            </p>
        </div>

        <!-- Account Information -->
        <div class="section">
            <h3>Au Compte de : Sarl Pointili</h3>
            <div class="info-card">
                <div class="info-row">
                    <span class="info-label">Téléphone:</span>
                    <span>0540761845</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span>contact@pointili.com</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span>Bureau N°03, 3 Rue Mouloud Feraoun, Dar El Beïda, Algeria</span>
                </div>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="section payment-details">
            <h3>Détails de paiement</h3>
            <div class="grid">
                <div>Numéro de commande</div>
                <div>510005</div>
                <div>ID de transaction</div>
                <div>hRy5sazi8buNN4AAFAPB</div>
                <div>Numéro d'autorisation</div>
                <div>299050</div>
                <div>Méthode de paiement</div>
                <div>CIB / Edahabia</div>
                <div>Date et heure</div>
                <div>2024-10-09 09:40:58</div>
                <div>Montant Total</div>
                <div>7400.00 DA</div>
            </div>
        </div>

        <!-- Support Information -->
        <div class="section">
            <div class="support-card">
                <p>Si vous rencontrez un problème avec le paiement, Contactez la SATIM</p>
                <div class="support-button">
                    <div class="phone-icon">
                        <!-- Phone icon SVG -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                            </path>
                        </svg>
                    </div>
                    <div class="support-button-text">
                        <div class="small">APPEL GRATUIT</div>
                        <div class="large">3020</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Provider Information -->
        <div class="section">
            <h3>Paiement électronique par :</h3>
            <div class="info-card">
                <div class="info-row">
                    <span class="info-label">Téléphone:</span>
                    <span>0712349502</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Siteweb:</span>
                    <span>www.efawtara.com</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span>contact@efawtara.com</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span>Bureau N1389, 11ème étage, Mohammadia Mall Alger</span>
                </div>
            </div>
        </div>

        <hr>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-left">
                Pour toute question ou problème, veuillez contacter notre équipe de support à
                <span class="highlight">support@efawtara.com</span> ou appeler le
                <span class="highlight">+213 738458983</span>.
            </div>
            <div class="footer-right">
                <span>Powered by</span>
                <span class="fawtara">Guiddini</span>
            </div>
        </div>
    </div>
</body>

</html>
