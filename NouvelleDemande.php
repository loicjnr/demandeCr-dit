<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'actions/db.php';

$success = "";
$error = "";

$client_id = $_GET['client_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $montant = $_POST['montant'] ?? 0;
    $id_client = $_POST['id_client'] ?? 0;
    $salaire_net = $_POST['salaire_net'] ?? 0;
    $charges_mensuelles = $_POST['charges_mensuelles'] ?? 0;
    $user_id = $_SESSION['user_id'];

    if ($montant > 0 && $id_client > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO demande_credit (montant, id_client, id_conseiller, date_demande, salaire_net, charges_mensuelles) VALUES (?, ?, ?, CURRENT_DATE, ?, ?)");
            $stmt->execute([$montant, $id_client, $user_id, $salaire_net, $charges_mensuelles]);
            $success = "La demande de crédit a été enregistrée avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    } else {
        $error = "Veuillez saisir un montant valide et sélectionner un client.";
    }
}

// Fetch clients for the dropdown
$clients = $pdo->query("SELECT id_client, nom, prenom FROM client ORDER BY nom ASC")->fetchAll();

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
    <title>Nouvelle Demande - CAMED.SA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/Dashboad.css">
    <style>
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 24px;
            border: 1px solid var(--border);
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-main);
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border-radius: 12px;
            border: 1px solid var(--border);
            font-size: 14px;
            outline: none;
            transition: border 0.3s;
            background: white;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: var(--primary);
        }
        .btn-submit {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            font-size: 16px;
        }
        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>

<div class="app">
    <header class="topbar">
        <div class="logo">
            <img src="LOGO CAMED.jpeg - Copie.jpg" alt="Logo" />
        </div>
        <div class="top-icons">
            <a href="demande_de_credit.php" title="Retour" style="color: var(--text-muted); text-decoration: none;"><i class="fa-solid fa-arrow-left"></i></a>
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

    <div class="greeting">
        <h2>Nouvelle Demande</h2>
        <p>Saisissez les informations financières pour le nouveau dossier de crédit.</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <div class="form-group">
                <label>Nom du Client *</label>
                <select name="id_client" required>
                    <option value="">Sélectionner un client...</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= $c['id_client'] ?>" <?= ($c['id_client'] == $client_id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nom'] . ' ' . $c['prenom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Montant du Crédit sollicité (FCFA) *</label>
                <input type="number" name="montant" required placeholder="Ex: 1000000" min="1">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Salaire Net Mensuel (FCFA) *</label>
                    <input type="number" name="salaire_net" required placeholder="Ex: 500000" min="0">
                </div>
                <div class="form-group">
                    <label>Charges Mensuelles (FCFA) *</label>
                    <input type="number" name="charges_mensuelles" required placeholder="Ex: 150000" min="0">
                </div>
            </div>
            <div class="form-group">
                <label>Type de Crédit</label>
                <select name="type">
                    <option value="Equipement">Crédit Équipement</option>
                    <option value="Consommation">Crédit Consommation</option>
                    <option value="Immobilier">Crédit Immobilier</option>
                </select>
            </div>
            <button type="submit" class="btn-submit">Enregistrer la Demande</button>
        </form>
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
