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

// Fetch demandes (Filtre par conseiller pour les conseillers, vue globale pour les décideurs)
$where_clause = ($user_role == 'conseiller_client') ? " WHERE d.id_conseiller = $user_id" : "";

$stmt = $pdo->query("
    SELECT d.id_demande, d.montant, d.statut, d.date_demande, c.nom, c.prenom 
    FROM demande_credit d 
    JOIN client c ON d.id_client = c.id_client 
    $where_clause
    ORDER BY d.id_demande DESC
");
$demandes = $stmt->fetchAll();
$total_demandes = count($demandes);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Crédits - CAMED.SA</title>
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
        <h2>Gestion des Crédits</h2>
        <p>Vous avez <?= $total_demandes ?> demandes enregistrées au total chez CAMED.SA.</p>
    </div>

    <div class="chart-card" style="padding: 10px; margin-bottom: 30px;">
        <div class="notif-header" style="margin-bottom: 0; padding: 10px 15px;">
            <div style="display: flex; align-items: center; gap: 10px; background: #f9fafb; padding: 10px 15px; border-radius: 12px; flex: 1;">
                <i class="fa-solid fa-magnifying-glass" style="color: var(--text-muted);"></i>
                <input type="text" id="creditSearch" placeholder="Rechercher une référence ou un client..." style="border: none; background: transparent; outline: none; width: 100%; font-size: 14px;">
            </div>
            <div class="filter-group" style="margin-left: 10px; display: flex; gap: 5px;">
                <button class="filter-btn active" data-filter="all" style="background: var(--primary); color: white; border: none; padding: 8px 15px; border-radius: 8px; font-size: 12px; cursor: pointer;">Tous</button>
                <button class="filter-btn" data-filter="en cours" style="background: white; border: 1px solid var(--border); padding: 8px 15px; border-radius: 8px; font-size: 12px; cursor: pointer;">En cours</button>
                <button class="filter-btn" data-filter="validee" style="background: white; border: 1px solid var(--border); padding: 8px 15px; border-radius: 8px; font-size: 12px; cursor: pointer;">Validés</button>
            </div>
        </div>
    </div>

    <div class="notifications" id="creditList">
        <?php if (empty($demandes)): ?>
            <p style="text-align: center; color: var(--text-muted); padding: 40px;">Aucune demande trouvée.</p>
        <?php else: ?>
            <?php foreach ($demandes as $d): ?>
                <?php
                    $typeClass = 'warning';
                    $iconClass = 'fa-clock';
                    if ($d['statut'] == 'validee') { $typeClass = 'success'; $iconClass = 'fa-check-circle'; }
                    if ($d['statut'] == 'rejetee') { $typeClass = 'danger'; $iconClass = 'fa-circle-xmark'; }
                    
                    $ref = "#CR-" . date('Y', strtotime($d['date_demande'])) . "-" . sprintf('%03d', $d['id_demande']);
                    $searchData = strtolower($ref . ' ' . $d['nom'] . ' ' . $d['prenom']);
                ?>
                <div class="notif <?= $typeClass ?> credit-card" data-search="<?= htmlspecialchars($searchData) ?>" data-status="<?= htmlspecialchars($d['statut']) ?>">
                    <i class="fa-solid <?= $iconClass ?>"></i>
                    <div style="flex: 1;">
                        <h4 style="font-size: 11px; color: var(--primary); text-transform: uppercase; margin-bottom: 2px;"><?= $ref ?></h4>
                        <h4><?= htmlspecialchars($d['prenom'] . ' ' . $d['nom']) ?></h4>
                        <p>Dossier : <strong style="text-transform: capitalize;"><?= htmlspecialchars($d['statut']) ?></strong> • <?= number_format($d['montant'], 0, ',', ' ') ?> FCFA</p>
                        <small><i class="fa-regular fa-calendar" style="margin-right: 4px;"></i> <?= date('d M Y', strtotime($d['date_demande'])) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
const searchInput = document.getElementById('creditSearch');
const filterBtns = document.querySelectorAll('.filter-btn');
const cards = document.querySelectorAll('.credit-card');

function filterCredits() {
    const term = searchInput.value.toLowerCase();
    const activeFilter = document.querySelector('.filter-btn.active').getAttribute('data-filter');
    
    cards.forEach(card => {
        const searchText = card.getAttribute('data-search');
        const status = card.getAttribute('data-status');
        
        const matchesSearch = searchText.includes(term);
        const matchesFilter = (activeFilter === 'all' || status === activeFilter);
        
        if (matchesSearch && matchesFilter) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

searchInput.addEventListener('input', filterCredits);

filterBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        filterBtns.forEach(b => {
            b.classList.remove('active');
            b.style.background = 'white';
            b.style.color = 'var(--text-muted)';
            b.style.border = '1px solid var(--border)';
        });
        this.classList.add('active');
        this.style.background = 'var(--primary)';
        this.style.color = 'white';
        this.style.border = 'none';
        filterCredits();
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
    <a href="clients.php" class="nav-item">
        <i class="fa-solid fa-users"></i>
        <span>Clients</span>
    </a>
    <a href="demande_de_credit.php" class="nav-item active">
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
