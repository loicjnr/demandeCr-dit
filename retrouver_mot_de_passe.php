<?php
session_start();
require_once 'actions/db.php';

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    if (!empty($email)) {
        // Vérifier si l'email existe
        $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Générer un token unique
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Sauvegarder en base
            $update = $pdo->prepare("UPDATE utilisateur SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $update->execute([$token, $expires, $email]);
            
            // Simulation d'envoi d'email
            // Dans une vraie config XAMPP, on utiliserait mail() avec sendmail configuré
            $message = "Un lien de réinitialisation a été généré (Simulation). <br> <small>Note technique : Pour XAMPP, la fonction mail() nécessite Sendmail configuré avec un compte SMTP (Gmail/Mailtrap).</small>";
        } else {
            // Pour la sécurité, on affiche le même message même si l'email n'existe pas
            $message = "Si cet email est enregistré, vous recevrez un lien de réinitialisation sous peu.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Récupération - CAMED.SA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-split">
        <div class="login-image"></div>
        
        <section class="login-card">
            <div class="logo-container">
                <img src="LOGO CAMED.jpeg - Copie.jpg" alt="Logo CAMED.SA">
            </div>

            <h1>Récupération</h1>
            <p class="subtitle">Entrez votre email pour réinitialiser votre accès</p>

            <?php if ($message): ?>
                <div style="background: #f0fdf4; color: #166534; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; border: 1px solid #bbf7d0;">
                    <i class="fas fa-check-circle"></i> <?= $message ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="input-box">
                    <label for="recovery_email">Adresse Email</label>
                    <input type="email" id="recovery_email" name="email" placeholder="votre@email.com" required>
                    <i class="fas fa-envelope"></i> 
                </div>
                
                <button type="submit" class="login-btn">Envoyer les instructions</button>
            </form>

            <div class="register-link" style="margin-top: 20px;">
                <p>
                    <a href="index.php"><i class="fas fa-arrow-left"></i> Retour à la connexion</a>
                </p>
            </div>
        </section>
    </div>
</body>
</html>
