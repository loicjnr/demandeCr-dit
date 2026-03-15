<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Connexion - CAMED.SA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-split">
        <div class="login-image">
            <!-- Background set via CSS: argent.jpeg -->
        </div>
        
        <section class="login-card">
            <div class="logo-container">
                <img src="LOGO CAMED.jpeg - Copie.jpg" alt="Logo CAMED.SA">
            </div>

            <h1>Bienvenue</h1>
            <p class="subtitle">Connectez-vous à votre espace gestionnaire</p>

            <?php
            session_start();
            if (isset($_SESSION['error_login'])) {
                echo '<div class="error-msg"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($_SESSION['error_login']) . '</div>';
                unset($_SESSION['error_login']);
            }
            ?>

            <form action="login.php" method="POST">
                <div class="input-box">
                    <label for="login">Identifiant</label>
                    <input type="text" id="login" name="login" placeholder="Ex: tcheumeni" required>
                    <i class="fas fa-user"></i> 
                </div>

                <div class="input-box">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <i class="fas fa-lock"></i>
                </div>
                
                <button type="submit" name="submit_login" class="login-btn">Se connecter</button>
            </form>

            <div class="register-link">
                <p>
                    <a href="retrouver_mot_de_passe.php">Mot de passe oublié ?</a>
                    <span>•</span>
                    <a href="Inscription.php">Créer un compte</a>
                </p>
            </div>
        </section>
    </div>
</body>
</html>
