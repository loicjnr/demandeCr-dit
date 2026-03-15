<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'actions/db.php';

$id_client = $_GET['id'] ?? 0;

// Fetch client info
$stmt = $pdo->prepare("SELECT * FROM client WHERE id_client = ?");
$stmt->execute([$id_client]);
$client = $stmt->fetch();

if (!$client) {
    die("Client non trouvé.");
}

// Fetch credit history
$stmt_history = $pdo->prepare("SELECT * FROM demande_credit WHERE id_client = ? ORDER BY date_demande DESC");
$stmt_history->execute([$id_client]);
$history = $stmt_history->fetchAll();

// Fetch user info for header
$user_id = $_SESSION['user_id'];
$stmt_user = $pdo->prepare("SELECT nom, role FROM utilisateur WHERE id_utilisateur = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();
$user_role = $user['role'] ?? 'Gestionnaire';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail Client - CAMED.SA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/Dashboad.css">
    <style>
        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 24px;
            border: 1px solid var(--border);
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-init {
            width: 80px;
            height: 80px;
            background: #f0fdf4;
            color: var(--primary);
            font-size: 32px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 24px;
            margin: 0 auto 20px;
        }
        .history-card {
            background: white;
            border-radius: 24px;
            border: 1px solid var(--border);
            padding: 25px;
        }
        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--bg-main);
        }
        .history-item:last-child { border-bottom: none; }
        .badge {
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-pending { background: #fffbeb; color: #92400e; }
        .badge-approved { background: #ecfdf5; color: #064e3b; }
        .badge-rejected { background: #fef2f2; color: #991b1b; }
    </style>
</head>
<body>

<div class="app">
    <header class="topbar">
        <div class="logo">
            <img src="LOGO CAMED.jpeg - Copie.jpg" alt="Logo" />
        </div>
        <div class="top-icons">
            <a href="clients.php" title="Retour" style="color: var(--text-muted); text-decoration: none;"><i class="fa-solid fa-arrow-left"></i></a>
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
            <div class="avatar" style="background-image: url('my.jpeg');"></div>
        </div>
    </header>

    <div class="profile-card">
        <div class="profile-init"><?= strtoupper(substr($client['nom'], 0, 1)) ?></div>
        <h2 style="font-size: 24px; font-weight: 700;"><?= htmlspecialchars($client['prenom'] . ' ' . $client['nom']) ?></h2>
        <p style="color: var(--text-muted); font-size: 14px; margin-top: 5px;"><?= htmlspecialchars($client['profession']) ?></p>
        
        <div style="display: flex; justify-content: center; gap: 20px; margin-top: 25px;">
            <div style="text-align: left;">
                <p style="font-size: 12px; color: var(--text-muted);">Téléphone</p>
                <p style="font-weight: 600;"><?= htmlspecialchars($client['telephone']) ?></p>
            </div>
            <div style="text-align: left;">
                <p style="font-size: 12px; color: var(--text-muted);">E-mail</p>
                <p style="font-weight: 600;"><?= htmlspecialchars($client['email'] ?: 'Non renseigné') ?></p>
            </div>
        </div>
    </div>

    <div class="history-card">
        <h3 style="margin-bottom: 20px; font-size: 18px;">Historique des Dossiers</h3>
        <?php if (empty($history)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 20px;">Aucun dossier trouvé pour ce client.</p>
        <?php else: ?>
            <?php foreach ($history as $h): ?>
                <?php
                    $badgeClass = 'badge-pending';
                    if ($h['statut'] == 'validee') $badgeClass = 'badge-approved';
                    if ($h['statut'] == 'rejetee') $badgeClass = 'badge-rejected';
                ?>
                <div class="history-item">
                    <div>
                        <p style="font-size: 12px; color: var(--primary); font-weight: 700;">#CR-<?= date('Y', strtotime($h['date_demande'])) ?>-<?= sprintf('%03d', $h['id_demande']) ?></p>
                        <p style="font-weight: 600; font-size: 15px;"><?= number_format($h['montant'], 0, ',', ' ') ?> FCFA</p>
                        <p style="font-size: 11px; color: var(--text-muted);"><?= date('d M Y', strtotime($h['date_demande'])) ?></p>
                    </div>
                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($h['statut']) ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <a href="NouvelleDemande.php?client_id=<?= $id_client ?>" style="display: block; text-align: center; background: var(--primary); color: white; padding: 20px; border-radius: 20px; text-decoration: none; font-weight: 700; margin-top: 25px;">
        <i class="fa-solid fa-plus-circle"></i> Créer une demande pour ce client
    </a>
</div>

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
