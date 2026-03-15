<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
// Restriction RBAC
if ($_SESSION['user_role'] == 'conseiller_client') {
    header("Location: tableau_de_bord.php");
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

// Stats queries
$total_stmt = $pdo->query("SELECT COUNT(*) as count, SUM(montant) as total FROM demande_credit");
$total_data = $total_stmt->fetch();

$validee_stmt = $pdo->query("SELECT COUNT(*) as count FROM demande_credit WHERE statut = 'validee'");
$validee_count = $validee_stmt->fetch()['count'];

$rejetee_stmt = $pdo->query("SELECT COUNT(*) as count FROM demande_credit WHERE statut = 'rejetee'");
$rejetee_count = $rejetee_stmt->fetch()['count'];

$en_cours_stmt = $pdo->query("SELECT COUNT(*) as count FROM demande_credit WHERE statut = 'en cours'");
$en_cours_count = $en_cours_stmt->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapports & Statistiques - CAMED.SA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/Dashboad.css">
    <style>
        .progress-bar {
            height: 12px;
            background: #f3f4f6;
            border-radius: 99px;
            margin-top: 12px;
            overflow: hidden;
        }
        .progress-fill { height: 100%; border-radius: 99px; }
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
        <h2>Rapports d'Activité</h2>
        <p>Analyse globale des performances de crédit CAMED.SA.</p>
    </div>

    <div class="stats">
        <div class="card">
            <div class="card-icon blue"><i class="fa-solid fa-sack-dollar"></i></div>
            <h4>Volume Total Sollicité</h4>
            <h2 style="font-size: 20px;"><?= number_format($total_data['total'], 0, ',', ' ') ?> FCFA</h2>
        </div>
        <div class="card">
            <div class="card-icon green"><i class="fa-solid fa-check-double"></i></div>
            <h4>Taux d'Approbation</h4>
            <h2><?= $total_data['count'] > 0 ? round(($validee_count / $total_data['count']) * 100) : 0 ?>%</h2>
        </div>
        <div class="card">
            <div class="card-icon orange"><i class="fa-solid fa-hourglass-half"></i></div>
            <h4>Demandes en Attente</h4>
            <h2><?= $en_cours_count ?></h2>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            <h3>Répartition par Statut</h3>
            <span>Performance en temps réel</span>
        </div>
        
        <div style="margin-top: 24px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span style="font-size: 14px; font-weight: 500;">Dossiers Validés (<?= $validee_count ?>)</span>
                <span style="font-weight: 700; color: #10b981;"><?= $total_data['count'] > 0 ? round(($validee_count / $total_data['count']) * 100) : 0 ?>%</span>
            </div>
            <div class="progress-bar" style="margin-bottom: 24px;">
                <div class="progress-fill" style="width: <?= $total_data['count'] > 0 ? ($validee_count / $total_data['count']) * 100 : 0 ?>%; background: #10b981;"></div>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span style="font-size: 14px; font-weight: 500;">Dossiers Rejetés (<?= $rejetee_count ?>)</span>
                <span style="font-weight: 700; color: #ef4444;"><?= $total_data['count'] > 0 ? round(($rejetee_count / $total_data['count']) * 100) : 0 ?>%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $total_data['count'] > 0 ? ($rejetee_count / $total_data['count']) * 100 : 0 ?>%; background: #ef4444;"></div>
            </div>
        </div>
    </div>
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
    <a href="rapports.php" class="nav-item active">
        <i class="fa-solid fa-file-contract"></i>
        <span>Rapports</span>
    </a>
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
