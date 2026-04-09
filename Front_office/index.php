<?php
require 'connexion.php';
session_start();
if (isset($_SESSION['user'])) { header('Location: caisse.php'); exit; }

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $req = $pdo->prepare('SELECT * FROM utilisateurs WHERE Email = :e LIMIT 1');
    $req->execute([':e' => $_POST['email']]);
    $user = $req->fetch();
    if ($user && $user['Motdepasse'] == $_POST['motdepasse']) {
        $_SESSION['user'] = $user;
        header('Location: caisse.php'); exit;
    }
    $erreur = 'Email ou mot de passe incorrect.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion — CaisseShop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="page-connexion">

    <!-- Logo centré en haut -->
    <div class="connexion-logo">
        <img src="../image/logo-removebg-preview.png" alt="CaisseShop">
    </div>

    <!-- Carte de connexion -->
    <div class="connexion-carte">
        <h2>Connexion utilisateur</h2>

        <?php if ($erreur): ?>
            <div class="msg-erreur"><?= $erreur ?></div>
        <?php endif; ?>

        <form method="POST">
            <!-- Champ Email -->
            <div class="champ">
                <input type="email" name="email" placeholder="Identifiant / Email">
            </div>

            <!-- Champ Mot de passe avec bouton oeil -->
            <div class="champ">
                <input type="password" id="mdp" name="motdepasse" placeholder="Mot de passe">
                <button type="button" class="oeil"
                        onclick="var i=document.getElementById('mdp');i.type=i.type=='password'?'text':'password'">
                    👁
                </button>
            </div>

            <button type="submit" class="btn-connecter">Se connecter</button>
        </form>

        <a href="#" class="lien-oubli">Mot de passe oublié ?</a>
    </div>

</body>
</html>