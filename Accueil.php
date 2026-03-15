<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CAMED.SA - Gestion Digitale des Crédits</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Specific Landing Page Styles Overriding/Extending style.css */
        body {
            display: block;
            padding: 0;
            overflow-x: hidden;
        }
        body::before {
            background: rgba(17, 24, 39, 0.8);
        }
        
        .navbar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar .logo-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: white;
            font-size: 24px;
            font-weight: 700;
        }

        .navbar .logo-brand img {
            height: 50px;
            border-radius: 8px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
        }

        .nav-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .nav-links a:hover {
            color: white;
        }

        .hero {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            z-index: 1;
            padding: 20px;
        }

        .hero-inner {
            max-width: 900px;
        }

        .hero h1 {
            font-size: clamp(32px, 5vw, 64px);
            color: white;
            line-height: 1.1;
            margin-bottom: 24px;
        }

        .hero h1 span {
            color: var(--primary-light);
        }

        .hero p {
            font-size: clamp(16px, 2vw, 20px);
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 40px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-btns {
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .btn-main {
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-filled {
            background: var(--primary-color);
            color: white;
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
        }

        .btn-main:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .features-section {
            background: white;
            padding: 100px 20px;
            position: relative;
            z-index: 10;
            text-align: center;
        }

        .features-section h2 {
            font-size: 32px;
            margin-bottom: 60px;
            color: var(--text-main);
        }

        .features-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .feature-card {
            padding: 40px;
            border-radius: 24px;
            background: #f9fafb;
            transition: transform 0.3s;
            border: 1px solid #f3f4f6;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            background: white;
            box-shadow: var(--shadow);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 24px;
            color: white;
        }

        .icon-green { background: #10b981; }
        .icon-blue { background: #3b82f6; }
        .icon-orange { background: #f59e0b; }

        .feature-card h3 {
            margin-bottom: 16px;
            font-size: 20px;
        }

        .feature-card p {
            color: var(--text-muted);
            line-height: 1.6;
        }

        footer {
            background: #111827;
            color: white;
            padding: 40px 20px;
            text-align: center;
            font-size: 14px;
            position: relative;
            z-index: 10;
        }

        @media (max-width: 768px) {
            .navbar { padding: 20px; }
            .nav-links { display: none; }
            .hero-btns { flex-direction: column; width: 100%; max-width: 300px; margin: 0 auto; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="Accueil.php" class="logo-brand">
        <img src="LOGO CAMED.jpeg - Copie.jpg" alt="CAMED.SA">
        <span>CAMED.SA</span>
    </a>
    <div class="nav-links">
        <a href="#features">Fonctionnalités</a>
        <a href="index.php">Connexion</a>
        <a href="Inscription.php">Inscription</a>
    </div>
    <a href="index.php" class="btn-main btn-filled" style="padding: 10px 20px; font-size: 14px;">Espace Client</a>
</nav>

<section class="hero">
    <div class="hero-inner">
        <h1>Gérez vos crédits avec une solution <span>digitale sécurisée</span></h1>
        <p>Simplifiez vos processus de demande, évaluation et validation. Une plateforme intuitive conçue pour la performance bancaire d'aujourd'hui.</p>
        <div class="hero-btns">
            <a href="index.php" class="btn-main btn-filled">
                <i class="fas fa-sign-in-alt"></i> Se Connecter
            </a>
            <a href="#features" class="btn-main btn-outline">Découvrir la plateforme</a>
        </div>
    </div>
</section>

<section id="features" class="features-section">
    <h2>Pourquoi choisir CAMED Digitale ?</h2>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon icon-green">
                <i class="fas fa-users-cog"></i>
            </div>
            <h3>Gestion Centralisée</h3>
            <p>Bénéficiez d'une vue d'ensemble sur tous vos clients et leurs dossiers de crédit en un clic.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon icon-blue">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h3>Analyse & Score</h3>
            <p>Évaluez les risques de manière structurée grâce à nos outils d'analyse intégrés.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon icon-orange">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3>Validation Sécurisée</h3>
            <p>Un circuit de signature digitale garantissant la traçabilité et la sécurité de chaque décision.</p>
        </div>
    </div>
</section>

<footer>
    <p>© 2026 CAMED S.A. • Excellence • Sécurité • Innovation</p>
    <p style="margin-top: 10px; opacity: 0.5;">Tous droits réservés</p>
</footer>

</body>
</html>

</body>
</html>
