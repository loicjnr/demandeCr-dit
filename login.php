<?php
session_start();
require_once 'actions/db.php';

if (isset($_POST['submit_login'])) {
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    if (!empty($login) && !empty($password)) {
        // Recherche de l'utilisateur
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        // Verification du mot de passe
        if ($user && password_verify($password, $user['password'])) {
            // Création des variables de session
            $_SESSION['user_id'] = $user['id_utilisateur'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_role'] = $user['role'];

            // Redirection selon le rôle (par défaut vers tableau de bord)
            header("Location: tableau_de_bord.php");
            exit();
        } else {
            $_SESSION['error_login'] = "Identifiant ou mot de passe incorrect.";
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION['error_login'] = "Veuillez remplir tous les champs.";
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
