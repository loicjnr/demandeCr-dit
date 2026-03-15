<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'actions/db.php';

// Fetch user info
$user_id = $_SESSION['user_id'];
$stmt_user = $pdo->prepare("SELECT nom, role FROM utilisateur WHERE id_utilisateur = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();

$user_role = $user['role'] ?? $_SESSION['user_role'] ?? 'Gestionnaire';
$role_labels = [
    'responsable_engagement' => 'Responsable Engagement',
    'conseiller_client' => 'Conseiller Client',
    'chef_agence' => 'Chef d\'Agence'
];
$display_role = $role_labels[$user_role] ?? $user_role;

// Fetch clients (Filtrage par conseiller pour les conseillers, vue globale pour les décideurs)
if ($user_role == 'conseiller_client') {
    $stmt = $pdo->prepare("
        SELECT DISTINCT c.* 
        FROM client c
        JOIN demande_credit d ON c.id_client = d.id_client
        WHERE d.id_conseiller = ?
        ORDER BY c.nom ASC
    ");
    $stmt->execute([$user_id]);
} else {
    $stmt = $pdo->query("SELECT * FROM client ORDER BY nom ASC");
}
$clients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Répertoire Clients - CAMED.SA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/Dashboad.css">
    <style>
        .client-card-premium {
            background: white;
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 16px;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s;
        }
        .client-card-premium:hover {
            transform: scale(1.01);
            box-shadow: var(--shadow-md);
        }
        .client-details {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .client-init {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: rgba(16, 185, 129, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 20px;
        }
    </style>
</head>
<body>

<div class="app">
    <header class="topbar">
        <div class="logo">
            <img src="LOGO CAMED.jpeg - Copie.jpg" alt="Logo CAMED.SA" />
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
        <h2>Répertoire Clients</h2>
        <p>Gérez la base de données de vos emprunteurs CAMED.SA.</p>
    </div>

    <div class="chart-card" style="padding: 10px; margin-bottom: 30px;">
        <div class="notif-header" style="margin-bottom: 0; padding: 10px 15px;">
            <div style="display: flex; align-items: center; gap: 10px; background: #f9fafb; padding: 10px 15px; border-radius: 12px; flex: 1;">
                <i class="fa-solid fa-magnifying-glass" style="color: var(--text-muted);"></i>
                <input type="text" id="clientSearch" placeholder="Rechercher un client..." style="border: none; background: transparent; outline: none; width: 100%; font-size: 14px;">
            </div>
            <a href="AjouterClient.php" style="text-decoration: none; margin-left: 10px; background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-user-plus"></i> Nouveau
            </a>
        </div>
    </div>

    <div class="client-list" id="clientList">
        <?php foreach ($clients as $c): ?>
        <div class="client-card-premium" data-name="<?= htmlspecialchars(strtolower($c['nom'] . ' ' . $c['prenom'])) ?>">
            <div class="client-details">
                <div class="client-init"><?= strtoupper(substr($c['nom'], 0, 1)) ?></div>
                <div class="client-info">
                    <h4 style="font-size: 16px; margin-bottom: 4px;"><?= htmlspecialchars($c['nom'] . ' ' . $c['prenom']) ?></h4>
                    <p style="font-size: 13px; color: var(--text-muted);">
                        <i class="fa-solid fa-phone" style="font-size: 10px;"></i> <?= htmlspecialchars($c['telephone']) ?> • 
                        <i class="fa-solid fa-briefcase" style="font-size: 10px;"></i> <?= htmlspecialchars($c['profession']) ?>
                    </p>
                </div>
            </div>
            <a href="DetailClient.php?id=<?= $c['id_client'] ?>" style="color: var(--primary); font-size: 18px;"><i class="fa-solid fa-circle-chevron-right"></i></a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.getElementById('clientSearch').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.client-card-premium');
    
    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        if (name.includes(term)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
});

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

<nav class="bottom-nav">
    <a href="tableau_de_bord.php" class="nav-item">
        <i class="fa-solid fa-table-columns"></i>
        <span>Tableau</span>
    </a>
    <a href="clients.php" class="nav-item active">
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
    <a href="parametres.php" class="nav-item">
        <i class="fa-solid fa-sliders"></i>
        <span>Paramètres</span>
    </a>
</nav>

</body>
</html>
