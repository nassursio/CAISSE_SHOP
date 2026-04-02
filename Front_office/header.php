<?php
$page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaisseShop</title>

    <!-- Feuille de style globale -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">

    <a class="navbar-brand" href="caisse.php">
        <span class="brand-caisse">Caisse</span><span class="brand-shop">Shop</span>
    </a>

    <div class="navbar-sep"></div>

    <!-- La classe "active" est ajoutée si on est sur la page correspondante -->
    <div class="nav-links">

        <a href="caisse.php"
           class="nav-link <?= $page == 'caisse.php' ? 'active' : '' ?>">
            Caisses
        </a>

        <a href="produit.php"
           class="nav-link <?= $page == 'produit.php' ? 'active' : '' ?>">
            Produits
        </a>

        <a href="vente.php"
           class="nav-link <?= $page == 'vente.php' ? 'active' : '' ?>">
            Ventes
        </a>

    </div>

    <!-- Partie droite : nom utilisateur et déconnexion -->
    <div class="nav-right">
        Utilisateur : <strong>Ali</strong>
        <a href="index.php" class="nav-deconnexion">Déconnexion</a>
    </div>

</nav>
