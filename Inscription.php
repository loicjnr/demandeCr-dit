<?php
session_start();
require_once 'actions/db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    if (!empty($nom) && !empty($email) && !empty($role) && !empty($login) && !empty($password)) {
        // Verification si l'email ou login existe deja
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = ? OR login = ?");
        $stmt_check->execute([$email, $login]);
        if ($stmt_check->fetchColumn() > 0) {
            $error = "Cet email ou login existe déjà.";
        } else {
            // Hachage du mot de passe
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Insertion
            $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, email, login, password, role) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$nom, $email, $login, $hashed_password, $role])) {
                $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            } else {
                $error = "Une erreur est survenue lors de l'inscription.";
            }
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - CAMED.SA</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .success-msg {
            color: var(--primary-dark);
            background: #ecfdf5;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 24px;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid #d1fae5;
        }
    </style>
</head>
<body>
    <div class="login-split">
        <div class="login-image" style="background-image: url('argent.jpeg');">
            <!-- Background side panel -->
        </div>

        <section class="login-card">
            <div class="logo-container">
                <img src="LOGO CAMED.jpeg - Copie.jpg" alt="Logo CAMED.SA">
            </div>

            <h1>Créer un compte</h1>
            <p class="subtitle">Rejoignez l'équipe de gestion CAMED.SA</p>

            <?php if ($error): ?>
                <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-msg"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form action="Inscription.php" method="POST">
                <div class="input-box">
                    <label for="nom">Nom complet</label>
                    <input type="text" id="nom" name="nom" placeholder="Ex: Jean Dupont" required>
                    <i class="fas fa-user-tag"></i>
                </div>

                <div class="input-box">
                    <label for="email">Adresse Email</label>
                    <input type="email" id="email" name="email" placeholder="Ex: contact@exemple.com" required>
                    <i class="fas fa-envelope"></i>
                </div>

                <div class="input-box">
                    <label for="role">Rôle au sein de l'agence</label>
                    <div style="position: relative;">
                        <select name="role" id="role" required style="width: 100%; padding: 14px 16px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; outline: none; font-size: 15px; color: var(--text-main); appearance: none;">
                            <option value="" disabled selected>Sélectionnez votre poste</option>
                            <option value="conseiller_client">Conseiller Client</option>
                            <option value="chef_agence">Chef d'Agence</option>
                            <option value="responsable_engagement">Responsable d'Engagement</option>
                        </select>
                        <i class="fas fa-chevron-down" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-muted); font-size: 12px;"></i>
                    </div>
                </div>

                <div class="input-box">
                    <label for="login">Identifiant de connexion</label>
                    <input type="text" id="login" name="login" placeholder="Ex: jdupont" required>
                    <i class="fas fa-user"></i>
                </div>

                <div class="input-box">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <i class="fas fa-lock"></i>
                </div>

                <button type="submit" name="register" class="login-btn">Finaliser l'inscription</button>
            </form>

            <div class="register-link">
                <p>Déjà un compte ? <a href="index.php">Se connecter</a></p>
            </div>
        </section>
    </div>
</body>
</html>
