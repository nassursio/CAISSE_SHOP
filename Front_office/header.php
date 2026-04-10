<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: index.php'); exit; }
$page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CaisseShop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">

    <!-- Logo -->
    <a href="caisse.php" class="navbar-logo">
        <img src="../image/logo-removebg-preview.png" alt="CaisseShop">
    </a>

    <div class="navbar-sep"></div>

    <!-- Liens navigation -->
    <div class="navbar-liens">
        <a href="caisse.php"  class="navbar-lien <?= $page=='caisse.php'  ?'actif':''?>">
            <span class="icone">🖥</span> Caisses
        </a>
        <a href="produit.php" class="navbar-lien <?= $page=='produit.php'||$page=='Detail_produit.php' ?'actif':''?>">
            <span class="icone">📦</span> Produits
        </a>
        <a href="vente.php"   class="navbar-lien <?= $page=='vente.php'   ?'actif':''?>">
            <span class="icone">📊</span> Ventes
        </a>
    </div>

    <!-- Utilisateur + Déconnexion à droite -->
    <div class="navbar-droite">
        <span>👤 Utilisateur : <strong><?= $_SESSION['user']['Prenom'] ?></strong></span>
        <a href="logout.php" class="navbar-deconnexion">↪ Déconnexion</a>
    </div>

</nav>