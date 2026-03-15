<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'actions/db.php';

// Fetch user info
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$user_role = $user['role'] ?? $_SESSION['user_role'] ?? 'Gestionnaire';
$role_labels = [
    'responsable_engagement' => 'Responsable Engagement',
    'conseiller_client' => 'Conseiller Client',
    'chef_agence' => 'Chef d\'Agence'
];
$display_role = $role_labels[$user_role] ?? $user_role;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres Profil - CAMED.SA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/Dashboad.css">
    <style>
        .profile-header-large {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 24px;
            border: 1px solid var(--border);
            margin-bottom: 32px;
        }
        .avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 30px;
            margin: 0 auto 20px;
            background: url('my.jpeg') center/cover;
            border: 4px solid var(--bg-main);
            box-shadow: var(--shadow-md);
        }
        .settings-block {
            background: white;
            border-radius: 24px;
            padding: 24px;
            border: 1px solid var(--border);
            margin-bottom: 24px;
        }
        .setting-row {
            display: flex;
            justify-content: space-between;
            padding: 16px 0;
            border-bottom: 1px solid var(--bg-main);
        }
        .setting-row:last-child { border-bottom: none; }
        .setting-label { color: var(--text-muted); font-size: 14px; }
        .setting-value { font-weight: 600; font-size: 14px; }
    </style>
</head>
<body>

<div class="app">
    <header class="topbar">
        <div class="logo">
            <img src="LOGO CAMED.jpeg - Copie.jpg" alt="Logo" />
        </div>
        <div class="top-icons">
            <a href="tableau_de_bord.php" title="Retour" style="color: var(--text-muted); text-decoration: none;"><i class="fa-solid fa-arrow-left"></i></a>
            <i class="fa-solid fa-bell" id="bellIcon" style="cursor: pointer; position: relative;">
                <span style="position: absolute; top: -5px; right: -5px; background: var(--error); color: white; border-radius: 50%; width: 15px; height: 15px; font-size: 10px; display: flex; align-items: center; justify-content: center;">3</span>
            </i>
            <div id="notifDropdown" class="chart-card" style="display: none; position: absolute; top: 60px; right: 20px; width: 300px; z-index: 1000; box-shadow: var(--shadow-xl); padding: 15px; text-align: left;">
                <h4 style="margin-bottom: 15px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">Alertes Système</h4>
                <div style="font-size: 13px; display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; gap: 10px; align-items: flex-start;">
                        <i class="fa-solid fa-circle-exclamation" style="color: var(--warning);"></i>
                        <p>Dossier #124 nécessite une pièce jointe supplémentaire.</p>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: flex-start;">
                        <i class="fa-solid fa-circle-check" style="color: var(--success);"></i>
                        <p>Dossier #119 validé avec succès par le Responsable.</p>
                    </div>
                </div>
            </div>
            <div class="avatar" style="background-image: url('my.jpeg');" title="<?= htmlspecialchars($display_role) ?>"></div>
        </div>
    </header>

    <div class="greeting">
        <h2>Paramètres du Compte</h2>
        <p>Gérez vos informations personnelles et vos préférences CAMED.SA.</p>
    </div>

    <div class="profile-header-large">
        <div class="avatar-large"></div>
        <h2 style="font-size: 24px; font-weight: 700; color: var(--text-main);"><?= htmlspecialchars($user['nom'] ?? 'Utilisateur') ?></h2>
        <p style="color: var(--primary); font-weight: 700; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; margin-top: 8px;">
            <i class="fa-solid fa-shield-halved"></i> <?= $display_role ?>
        </p>
    </div>

    <div class="settings-block">
        <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 20px;">Informations Fondamentales</h3>
        <div class="setting-row">
            <span class="setting-label">Identifiant Unique</span>
            <span class="setting-value">@<?= htmlspecialchars($user['login']) ?></span>
        </div>
        <div class="setting-row">
            <span class="setting-label">E-mail Professionnel</span>
            <span class="setting-value"><?= htmlspecialchars($user['email']) ?></span>
        </div>
        <div class="setting-row">
            <span class="setting-label">Date d'inscription</span>
            <span class="setting-value">Membre Premium</span>
        </div>
    </div>

    <div class="settings-block">
        <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 20px;">Sécurité & Version</h3>
        <div class="setting-row">
            <span class="setting-label">Version de l'application</span>
            <span class="setting-value">v3.0.4 - Premium Edition</span>
        </div>
        <div class="setting-row">
            <span class="setting-label">Dernière vérification</span>
            <span class="setting-value"><?= date('d/m/Y H:i') ?></span>
        </div>
    </div>

    <a href="actions/logout.php" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 10px; background: #fff1f2; color: #e11d48; padding: 18px; border-radius: 20px; font-weight: 700; border: 1px solid #fda4af;">
        <i class="fa-solid fa-right-from-bracket"></i> Déconnexion du système
    </a>
</div>

<nav class="bottom-nav">
    <a href="tableau_de_bord.php" class="nav-item">
        <i class="fa-solid fa-table-columns"></i>
        <span>Tableau</span>
    </a>
    <a href="clients.php" class="nav-item">
        <i class="fa-solid fa-users"></i>
        <span>Clients</span>
    </a>
    <a href="demande_de_credit.php" class="nav-item">
        <i class="fa-solid fa-credit-card"></i>
        <span>Crédits</span>
    </a>
    <?php if ($user_role != 'conseiller_client'): ?>
    <a href="rapports.php" class="nav-item">
        <i class="fa-solid fa-file-contract"></i>
        <span>Rapports</span>
    </a>
    <?php endif; ?>
    <a href="parametres.php" class="nav-item active">
        <i class="fa-solid fa-sliders"></i>
        <span>Paramètres</span>
    </a>
</nav>

<script>
// Toggle Notifications
document.getElementById('bellIcon').addEventListener('click', function(e) {
    e.stopPropagation();
    const dropdown = document.getElementById('notifDropdown');
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
});

document.addEventListener('click', function() {
    if(document.getElementById('notifDropdown')) document.getElementById('notifDropdown').style.display = 'none';
});

if(document.getElementById('notifDropdown')) {
    document.getElementById('notifDropdown').addEventListener('click', function(e) {
        e.stopPropagation();
    });
}
</script>

</body>
</html>
