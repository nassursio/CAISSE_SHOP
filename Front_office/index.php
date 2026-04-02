<?php

require_once 'connexionBDD.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // trim() supprime les espaces au début et à la fin
    $email = trim($_POST['email']);
    $mdp   = trim($_POST['motdepasse']);

    // Vérifier que les champs ne sont pas vides
    if ($email == '' || $mdp == '') {
        $erreur = 'Veuillez remplir tous les champs.';

    } else {

        // On prépare la requête avec :email comme paramètre
        $sql  = 'SELECT * FROM utilisateurs WHERE Email = :email LIMIT 1';
        $stmt = $pdo->prepare($sql);

        // On exécute en remplaçant :email par la valeur saisie
        $stmt->execute([':email' => $email]);

        $user = $stmt->fetch();

        if ($user && password_verify($mdp, $user['Motdepasse'])) {

            // Si connexion réussie vas vers la caisse
            header('Location: caisse.php');
            exit; // Toujours mettre exit après header()

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

    <!-- Carte blanche contenant le formulaire -->
    <div class="connexion-card">

        <h2>Connexion utilisateur</h2>

        <?php if ($erreur != ''): ?>
            <div class="alerte-erreur"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php">

            <label for="email">Identifiant / Email</label>
            <input type="email"
                   id="email"
                   name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <!-- htmlspecialchars() protège contre les injections HTML -->
            <!-- ?? '' : si $_POST['email'] n'existe pas, on affiche vide -->

            <label for="motdepasse">Mot de passe</label>
            <div class="pwd-wrap">
                <input type="password"
                       id="motdepasse"
                       name="motdepasse">
                <!-- Bouton pour afficher/masquer le mot de passe -->
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