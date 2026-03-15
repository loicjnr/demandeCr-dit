<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'actions/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: demande_de_credit.php");
    exit();
}

$id_demande = (int)$_GET['id'];

// Handle Decision POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $nouveau_statut = ($_POST['action'] == 'valider') ? 'validee' : 'rejetee';
    
    if ($_SESSION['user_role'] == 'responsable_engagement' || $_SESSION['user_role'] == 'chef_agence' || $_SESSION['user_role'] == 'administrateur') {
        $stmt_update = $pdo->prepare("UPDATE demande_credit SET statut = ?, id_responsable = ? WHERE id_demande = ?");
        $stmt_update->execute([$nouveau_statut, $_SESSION['user_id'], $id_demande]);
        $msg = "La demande a été " . ($nouveau_statut == 'validee' ? 'validée' : 'rejetée') . " avec succès.";
    } else {
        $error = "Vous n'avez pas les droits pour prendre une décision.";
    }
}

// Fetch demand details
$stmt = $pdo->prepare("
    SELECT d.*, c.nom, c.prenom, c.profession, c.telephone 
    FROM demande_credit d 
    JOIN client c ON d.id_client = c.id_client 
    WHERE d.id_demande = ?
");
$stmt->execute([$id_demande]);
$demande = $stmt->fetch();

if (!$demande) {
    header("Location: demande_de_credit.php");
    exit();
}

// Calcul du Score de Crédit (Simulation simple basées sur le taux d'endettement)
$salaire = (float)($demande['salaire_net'] ?? 0);
$charges = (float)($demande['charges_mensuelles'] ?? 0);
$score = 0;
$score_label = "Critique";

if ($salaire > 0) {
    $reste_a_vivre = $salaire - $charges;
    $taux_endettement = ($charges / $salaire) * 100;
    
    // Algorithme de scoring basique (max 1000)
    $score = 1000 - ($taux_endettement * 10);
    if ($reste_a_vivre < 50000) $score -= 200;
    
    $score = max(0, min(1000, round($score)));
    
    if ($score > 700) $score_label = "Excellent";
    elseif ($score > 400) $score_label = "Acceptable";
    else $score_label = "Risqué";
} else {
    $score = 0;
    $score_label = "Inconnu";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Décision Crédit #<?= $id_demande ?> - CAMED.SA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/Decision.css">
    <style>
        .tab-content { display: none; padding-top: 20px; }
        .tab-content.active { display: block; }
        .doc-item { display: flex; align-items: center; gap: 12px; padding: 12px; background: #f9fafb; border-radius: 12px; margin-bottom: 8px; font-size: 13px; }
        .doc-item i { font-size: 18px; color: var(--primary); }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="top-bar">
            <a href="demande_de_credit.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i></a>
            <div class="header-title-section">
                <h2><?= str_replace('_', ' ', strtoupper($_SESSION['user_role'])) ?></h2>
                <span>Module de Décision • CAMED.SA</span>
            </div>
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
        </div>
    </div>
    
    <?php if (isset($msg)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="client-hero">
        <div class="client-avatar" style="background-image: url('my.jpeg');"></div>
        <div class="client-main-info">
            <h3><?= htmlspecialchars($demande['prenom'] . ' ' . $demande['nom']) ?></h3>
            <div class="badge-row">
                <span class="badge badge-id">CLI-<?= sprintf('%04d', $demande['id_client']) ?></span>
                <span class="badge badge-status" style="background: <?= $demande['statut'] == 'validee' ? '#f0fdf4' : ($demande['statut'] == 'rejetee' ? '#fef2f2' : '#fff7ed') ?>; color: <?= $demande['statut'] == 'validee' ? '#10b981' : ($demande['statut'] == 'rejetee' ? '#ef4444' : '#f59e0b') ?>;">
                    <?= strtoupper($demande['statut']) ?>
                </span>
            </div>
        </div>
    </div>

    <div class="tabs">
        <div class="tab active" onclick="showTab(0)">Profil Client</div>
        <div class="tab" onclick="showTab(1)">Détails Prêt</div>
        <div class="tab" onclick="showTab(2)">Documents</div>
    </div>

    <!-- TAB 0: Profil Client -->
    <div id="tab0" class="tab-content active">
        <div class="content-section">
            <div class="section-title">
                Revenus & Évaluation
                <span>Profession: <?= htmlspecialchars($demande['profession']) ?></span>
            </div>
            
            <div class="grid-row">
                <div class="info-card">
                    <p>Salaire Net Mensuel</p>
                    <h4><?= number_format($salaire, 0, ',', ' ') ?> FCFA</h4>
                </div>
                <div class="info-card">
                    <p>Charges Mensuelles</p>
                    <h4 style="color: var(--error);"><?= number_format($charges, 0, ',', ' ') ?> FCFA</h4>
                </div>
            </div>

            <div class="score-card">
                <div class="score-display">
                    <p>SCORE DE CRÉDIT ESTIMÉ</p>
                    <h2 style="color: <?= ($score > 600) ? 'var(--primary)' : (($score > 350) ? 'var(--warning)' : 'var(--error)') ?>;">
                        <?= $score ?> <span>/ 1000</span>
                    </h2>
                </div>
                <div class="score-status">
                    <i class="fa-solid fa-shield-check"></i>
                    Évaluation : <strong><?= $score_label ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 1: Détails Prêt -->
    <div id="tab1" class="tab-content">
        <div class="content-section">
            <div class="section-title">Analyse du Financement</div>
            <div class="detail-list">
                <div class="detail-row">
                    <div class="detail-icon"><i class="fa-solid fa-sack-dollar"></i></div>
                    <div class="detail-text">
                        <p>Capital sollicité</p>
                        <h4><?= number_format($demande['montant'], 0, ',', ' ') ?> FCFA</h4>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-icon"><i class="fa-solid fa-percent"></i></div>
                    <div class="detail-text">
                        <p>Taux d'endettement</p>
                        <h4><?= ($salaire > 0) ? round(($charges / $salaire) * 100, 1) : 0 ?> %</h4>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-icon"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="detail-text">
                        <p>Date dépôt dossier</p>
                        <h4><?= date('d M Y', strtotime($demande['date_demande'])) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: Documents -->
    <div id="tab2" class="tab-content">
        <div class="content-section">
            <div class="section-title">Pièces Justificatives</div>
            <div class="doc-item"><i class="fa-solid fa-file-pdf"></i> Pièce d'Identité (Validée)</div>
            <div class="doc-item"><i class="fa-solid fa-file-pdf"></i> 3 Derniers Bulletins de Salaire</div>
            <div class="doc-item"><i class="fa-solid fa-file-pdf"></i> Attestation de Travail</div>
            <div class="doc-item"><i class="fa-solid fa-file-image"></i> Justificatif de Domicile</div>
        </div>
    </div>
    
    <?php if ($demande['statut'] == 'en cours'): ?>
        <?php if ($_SESSION['user_role'] != 'conseiller_client'): ?>
        <div class="decision-area">
            <form action="" method="POST">
                <p style="font-size: 13px; font-weight: 600; margin-bottom: 10px; color: var(--text-muted);">Observations & Justification</p>
                <textarea name="motif" placeholder="Justifiez ici votre décision (ex: Taux d'endettement trop élevé)..."></textarea>
                <div class="decision-btns">
                    <button type="submit" name="action" value="valider" class="btn-action btn-validate">
                        <i class="fa-solid fa-check-circle"></i> Valider le Dossier
                    </button>
                    <button type="submit" name="action" value="rejeter" class="btn-action btn-reject">
                        <i class="fa-solid fa-times-circle"></i> Rejeter la Demande
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="decision-area" style="text-align: center; background: #f8fafc; border: 1px dashed var(--border);">
            <p style="color: var(--text-muted); font-size: 14px;">
                <i class="fa-solid fa-lock"></i> En attente de décision par un Responsable.
            </p>
        </div>
        <?php endif; ?>
    <?php else: ?>
    <div class="decision-area" style="text-align: center;">
        <h3 style="color: <?= $demande['statut'] == 'validee' ? 'var(--primary)' : 'var(--error)' ?>;">
            <i class="fa-solid <?= $demande['statut'] == 'validee' ? 'fa-check-double' : 'fa-ban' ?>"></i>
            Cette demande a déjà été <?= strtoupper($demande['statut']) ?>.
        </h3>
    </div>
    <?php endif; ?>
</div>

<script>
function showTab(index) {
    document.querySelectorAll('.tab').forEach((t, i) => {
        t.classList.toggle('active', i === index);
    });
    document.querySelectorAll('.tab-content').forEach((c, i) => {
        c.classList.toggle('active', i === index);
    });
}

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

<footer style="text-align: center; margin-top: 40px; color: var(--text-muted); font-size: 13px;">
    System Décisionnel CAMED.SA © 2026
</footer>

</body>
</html>
