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

$user_nom = $user['nom'] ?? $_SESSION['user_nom'] ?? 'Utilisateur';
$user_role = $user['role'] ?? $_SESSION['user_role'] ?? 'Gestionnaire';

// Conversion du rôle en libellé lisible
$role_labels = [
    'responsable_engagement' => 'Responsable Engagement',
    'conseiller_client' => 'Conseiller Client',
    'chef_agence' => 'Chef d\'Agence'
];
$display_role = $role_labels[$user_role] ?? $user_role;

// Recuperation des statistiques (Filtre par conseiller si nécessaire)
$where_cond = ($user_role == 'conseiller_client') ? " WHERE id_conseiller = $user_id" : "";
$where_cond_status = ($user_role == 'conseiller_client') ? " AND id_conseiller = $user_id" : "";

$stmt = $pdo->query("SELECT COUNT(*) as total FROM demande_credit" . $where_cond);
$total_demandes = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as attente FROM demande_credit WHERE statut = 'en cours'" . $where_cond_status);
$en_attente = $stmt->fetch()['attente'];

$stmt = $pdo->query("SELECT COUNT(*) as validees FROM demande_credit WHERE statut = 'validee'" . $where_cond_status);
$validees = $stmt->fetch()['validees'];

$stmt = $pdo->query("SELECT COUNT(*) as rejetees FROM demande_credit WHERE statut = 'rejetee'" . $where_cond_status);
$rejetees = $stmt->fetch()['rejetees'];

// Recent requests (Filtre par conseiller si nécessaire)
$recent_query = "
    SELECT d.id_demande, d.montant, d.statut, d.date_demande, c.nom, c.prenom 
    FROM demande_credit d 
    JOIN client c ON d.id_client = c.id_client 
    " . $where_cond . "
    ORDER BY d.id_demande DESC 
    LIMIT 5
";
$recent_stmt = $pdo->query($recent_query);
$recent_demandes = $recent_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - CAMED.SA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/Dashboad.css">
</head>
<body>

<div class="app">
    <header class="topbar">
        <div class="logo">
            <img src="LOGO CAMED.jpeg - Copie.jpg" alt="Logo CAMED.SA" />
        </div>
        <div class="top-icons">
            <i class="fa-solid fa-bell" id="bellIcon" style="cursor: pointer; position: relative;">
                <span style="position: absolute; top: -5px; right: -5px; background: var(--error); color: white; border-radius: 50%; width: 15px; height: 15px; font-size: 10px; display: flex; align-items: center; justify-content: center;">3</span>
            </i>
            <div id="notifDropdown" class="chart-card" style="display: none; position: absolute; top: 60px; right: 20px; width: 300px; z-index: 1000; box-shadow: var(--shadow-xl); padding: 15px;">
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
            <a href="actions/logout.php" title="Déconnexion" style="color: var(--error);"><i class="fa-solid fa-power-off"></i></a>
            <div class="avatar" style="background-image: url('my.jpeg');" title="<?= htmlspecialchars($display_role) ?>"></div>
        </div>
    </header>

    <div class="greeting">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
            <h2 style="margin-bottom: 0;">Bonjour, <?= htmlspecialchars($user_nom) ?> </h2>
            <span style="background: var(--primary); color: white; padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                <?= htmlspecialchars($display_role) ?>
            </span>
        </div>
        <?php if ($user_role == 'conseiller_client'): ?>
            <p>Espace de saisie et suivi de vos dossiers clients au sein de CAMED.SA.</p>
        <?php else: ?>
            <p>Console de supervision et de décision pour l'ensemble de l'agence CAMED.SA.</p>
        <?php endif; ?>
    </div>

    <!-- Quick Buttons for core pages -->
    <div style="display: flex; gap: 15px; margin-bottom: 40px; overflow-x: auto; padding-bottom: 10px;">
        <?php if ($user_role == 'conseiller_client' || $user_role == 'administrateur'): ?>
        <a href="clients.php" style="text-decoration: none; background: white; padding: 15px 25px; border-radius: 15px; border: 1px solid var(--border); color: var(--text-main); font-weight: 600; display: flex; align-items: center; gap: 10px; min-width: 180px;">
            <i class="fa-solid fa-users" style="color: var(--primary);"></i> Gérer Clients
        </a>
        <a href="NouvelleDemande.php" style="text-decoration: none; background: white; padding: 15px 25px; border-radius: 15px; border: 1px solid var(--border); color: var(--text-main); font-weight: 600; display: flex; align-items: center; gap: 10px; min-width: 180px;">
            <i class="fa-solid fa-plus-circle" style="color: var(--primary);"></i> Nouveau Crédit
        </a>
        <?php endif; ?>

        <?php if ($user_role != 'conseiller_client'): ?>
        <a href="rapports.php" style="text-decoration: none; background: white; padding: 15px 25px; border-radius: 15px; border: 1px solid var(--border); color: var(--text-main); font-weight: 600; display: flex; align-items: center; gap: 10px; min-width: 180px;">
            <i class="fa-solid fa-chart-line" style="color: var(--primary);"></i> Voir Rapports
        </a>
        <?php endif; ?>
    </div>

    <div class="stats">
        <div class="card">
            <div class="card-icon blue"><i class="fa-solid fa-folder-open"></i></div>
            <h4><?= ($user_role == 'conseiller_client') ? 'Mes Demandes' : 'Total Agence' ?></h4>
            <h2><?= $total_demandes ?></h2>
        </div>

        <div class="card">
            <div class="card-icon orange"><i class="fa-solid fa-clock"></i></div>
            <h4><?= ($user_role == 'conseiller_client') ? 'Mes En-cours' : 'À Valider' ?></h4>
            <h2><?= $en_attente ?></h2>
        </div>

        <div class="card">
            <div class="card-icon green"><i class="fa-solid fa-circle-check"></i></div>
            <h4><?= ($user_role == 'conseiller_client') ? 'Mes Validées' : 'Validations' ?></h4>
            <h2><?= $validees ?></h2>
        </div>

        <div class="card">
            <div class="card-icon red"><i class="fa-solid fa-circle-xmark"></i></div>
            <h4><?= ($user_role == 'conseiller_client') ? 'Mes Rejets' : 'Rejets Agence' ?></h4>
            <h2><?= $rejetees ?></h2>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            <h3><?= ($user_role == 'conseiller_client') ? 'Mon Activité' : 'Performance Agence' ?></h3>
            <span>Évolution des crédits • 30 derniers jours</span>
        </div>
        <div style="height: 200px; width: 100%; margin-top: 20px; display: flex; align-items: flex-end; gap: 5px;">
            <!-- Simple SVG Line Chart -->
            <svg viewBox="0 0 400 150" style="width: 100%; height: 100%;" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="grad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:var(--primary);stop-opacity:0.2" />
                        <stop offset="100%" style="stop-color:var(--primary);stop-opacity:0" />
                    </linearGradient>
                </defs>
                <path d="M0,120 Q50,80 100,100 T200,50 T300,80 T400,30 L400,150 L0,150 Z" fill="url(#grad)" />
                <path d="M0,120 Q50,80 100,100 T200,50 T300,80 T400,30" fill="none" stroke="var(--primary)" stroke-width="3" />
                <circle cx="100" cy="100" r="4" fill="var(--primary)" />
                <circle cx="200" cy="50" r="4" fill="var(--primary)" />
                <circle cx="300" cy="80" r="4" fill="var(--primary)" />
                <circle cx="400" cy="30" r="4" fill="var(--primary)" />
            </svg>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 11px; color: var(--text-muted); font-weight: 600;">
            <span>S-4</span> <span>S-3</span> <span>S-2</span> <span>S-1</span> <span>AUJOURD'HUI</span>
        </div>
    </div>
    
    <div class="notifications">
        <div class="notif-header">
            <h3><?= ($user_role == 'conseiller_client') ? 'Mes Derniers Dossiers' : 'Dernières Activités Agence' ?></h3>
            <a href="demande_de_credit.php">Tout voir <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i></a>
        </div>

        <?php if (empty($recent_demandes)): ?>
            <p style="text-align: center; color: var(--text-muted); padding: 20px;">Aucune demande récente.</p>
        <?php else: ?>
            <?php foreach ($recent_demandes as $demande): ?>
                <?php 
                    $typeClass = 'warning';
                    $iconClass = 'fa-file-invoice-dollar';
                    if ($demande['statut'] == 'validee') {
                        $typeClass = 'success';
                        $iconClass = 'fa-check-circle';
                    } elseif ($demande['statut'] == 'rejetee') {
                        $typeClass = 'danger';
                        $iconClass = 'fa-exclamation-triangle';
                    }
                ?>
                <div class="notif <?= $typeClass ?>">
                    <i class="fa-solid <?= $iconClass ?>"></i>
                    <div style="flex: 1;">
                        <h4>Demande #<?= $demande['id_demande'] ?> • <?= htmlspecialchars($demande['nom'] . ' ' . $demande['prenom']) ?></h4>
                        <p>Statut : <strong style="text-transform: capitalize;"><?= htmlspecialchars($demande['statut']) ?></strong> • Montant: <?= number_format($demande['montant'], 0, ',', ' ') ?> FCFA</p>
                        <small><i class="fa-regular fa-calendar" style="margin-right: 4px;"></i> <?= date('d M Y', strtotime($demande['date_demande'])) ?></small>
                    </div>
                    <a href="Decision.php?id=<?= $demande['id_demande'] ?>" style="align-self: center; color: var(--primary);"><i class="fa-solid fa-angle-right"></i></a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<nav class="bottom-nav">
    <a href="tableau_de_bord.php" class="nav-item active">
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
    <a href="parametres.php" class="nav-item">
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
    document.getElementById('notifDropdown').style.display = 'none';
});

document.getElementById('notifDropdown').addEventListener('click', function(e) {
    e.stopPropagation();
});
</script>

</body>
</html>
