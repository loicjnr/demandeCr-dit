<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'actions/db.php';

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $email = $_POST['email'] ?? '';
    $profession = $_POST['profession'] ?? '';

    if (!empty($nom) && !empty($prenom) && !empty($telephone)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO client (nom, prenom, telephone, email, profession) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $telephone, $email, $profession]);
            $success = "Le client a été ajouté avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout : " . $e->getMessage();
        }
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}

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
    <title>Ajouter un Client - CAMED.SA</title>
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
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 12px;
            border: 1px solid var(--border);
            font-size: 14px;
            outline: none;
            transition: border 0.3s;
        }
        .form-group input:focus {
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
            transition: opacity 0.3s;
        }
        .btn-submit:hover {
            opacity: 0.9;
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

    <div class="greeting">
        <h2>Nouveau Client</h2>
        <p>Enregistrez un nouveau client dans la base de données CAMED.SA.</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Nom *</label>
                    <input type="text" name="nom" required placeholder="Ex: Traore">
                </div>
                <div class="form-group">
                    <label>Prénom *</label>
                    <input type="text" name="prenom" required placeholder="Ex: Moussa">
                </div>
            </div>
            <div class="form-group">
                <label>Téléphone *</label>
                <input type="text" name="telephone" required placeholder="Ex: 655443322">
            </div>
            <div class="form-group">
                <label>E-mail (Optionnel)</label>
                <input type="email" name="email" placeholder="moussa@example.com">
            </div>
            <div class="form-group">
                <label>Profession</label>
                <input type="text" name="profession" placeholder="Ex: Commerçant">
            </div>
            <button type="submit" class="btn-submit">Enregistrer le Client</button>
        </form>
    </div>
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
