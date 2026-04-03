<?php

require_once 'connexion.php';



if (!empty($_SESSION['utilisateur'])) {
    header('Location: caisse.php');
    exit;
}

$erreur = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = trim($_POST['email']      ?? '');
    $mdp   = trim($_POST['motdepasse'] ?? '');

    // Vérifier que les champs sont remplis
    if ($email == '' || $mdp == '') {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {

        // Chercher l'utilisateur par son email
        $sql  = 'SELECT * FROM utilisateurs WHERE Email = :email LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        // password_verify() compare le mot de passe saisi avec
        if ($user && password_verify($mdp, $user['Motdepasse'])) {

            $_SESSION['utilisateur'] = $user;

            header('Location: caisse.php');
            exit;

        } else {
            $erreur = 'Identifiant ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — CaisseShop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="page-connexion">

    <div class="connexion-logo">
        <span class="brand-caisse">Caisse</span><span class="brand-shop">Shop</span>
        <p>Logiciel de Caisse &amp; Gestion</p>
    </div>

    <div class="connexion-card">

        <h2>Connexion utilisateur</h2>

        <?php if ($erreur != ''): ?>
            <div class="alerte-erreur"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <!-- Formulaire de connexion -->
        <form method="POST" action="index.php">

            <label for="email">Identifiant / Email</label>
            <input type="email"
                   id="email"
                   name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <label for="motdepasse">Mot de passe</label>
            <div class="pwd-wrap">
                <input type="password"
                       id="motdepasse"
                       name="motdepasse">
                <!-- Bouton oeil pour afficher/masquer le mot de passe -->
                <button type="button"
                        class="btn-eye"
                        onclick="var i=document.getElementById('motdepasse');
                                 i.type = i.type == 'password' ? 'text' : 'password'">
                    &#128065;
                </button>
            </div>

            <button type="submit" class="btn-connect">Se connecter</button>

        </form>

        <a href="#" class="lien-oubli">Mot de passe oublié ?</a>

    </div>

</body>
</html>
