<?php

$page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaisseShop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">

    <a class="navbar-brand" href="caisse.php">
        <span class="brand-caisse">Caisse</span><span class="brand-shop">Shop</span>
    </a>

    <div class="navbar-sep"></div>


    <div class="nav-links">
        <a href="caisse.php"
           class="nav-link <?= $page == 'caisse.php' ? 'active' : '' ?>">
           Caisses
        </a>
        <a href="produit.php"
           class="nav-link <?= ($page == 'produit.php' || $page == 'Detail_produit.php') ? 'active' : '' ?>">
           Produits
        </a>
        <a href="vente.php"
           class="nav-link <?= $page == 'vente.php' ? 'active' : '' ?>">
           Ventes
        </a>
    </div>

    <div class="nav-right">
        <?php if (!empty($_SESSION['utilisateur'])): ?>
            Utilisateur : <strong><?= htmlspecialchars($_SESSION['utilisateur']['Prenom']) ?></strong>
            <a href="logout.php" class="nav-deconnexion">Déconnexion</a>
        <?php endif; ?>
    </div>

</nav>
