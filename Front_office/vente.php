<?php
require 'connexion.php';
require 'header.php';

// Récupérer toutes les ventes avec le prénom de l'utilisateur
$req    = $pdo->query('SELECT v.*, u.Prenom, u.Nom
                       FROM vente v
                       JOIN utilisateurs u ON u.Id = v.id_utilisateur
                       ORDER BY v.Date DESC');
$ventes = $req->fetchAll();

// Calculer le total général
$total_general = 0;
foreach ($ventes as $v) {
    $total_general = $total_general + $v['Montant'];
}

// Voir le détail d'une vente si on clique sur "Détail"
$detail        = null;
$detail_lignes = [];

if (isset($_GET['detail'])) {

    $req = $pdo->prepare('SELECT v.*, u.Prenom FROM vente v
                          JOIN utilisateurs u ON u.Id = v.id_utilisateur
                          WHERE v.Id = :id');
    $req->execute([':id' => $_GET['detail']]);
    $detail = $req->fetch();

    $req = $pdo->prepare('SELECT pv.*, p.Nom_produit FROM produit_vendu pv
                          JOIN produit p ON p.Id = pv.id_produit
                          WHERE pv.id_vente = :id');
    $req->execute([':id' => $_GET['detail']]);
    $detail_lignes = $req->fetchAll();
}
?>

<div class="contenu">

    <p>Total toutes les ventes : <strong><?= $total_general ?> €</strong></p>

    <!-- Tableau des ventes -->
    <div class="carte">
        <h2>Historique des ventes</h2>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Montant</th>
                    <th>Caissier</th>
                    <th>Nb articles</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($ventes as $v) : ?>
                <tr>
                    <td><?= $v['Date'] ?></td>
                    <td><?= $v['Montant'] ?> €</td>
                    <td><?= $v['Prenom'] ?> <?= $v['Nom'] ?></td>
                    <td><?= $v['total_produit'] ?></td>
                    <td><a href="vente.php?detail=<?= $v['Id'] ?>">Détail</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Détail d'une vente -->
    <?php if ($detail) : ?>
    <div class="carte" style="margin-top:1rem">
        <h2>Détail vente du <?= $detail['Date'] ?> — <?= $detail['Prenom'] ?></h2>

        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($detail_lignes as $l) : ?>
                <tr>
                    <td><?= $l['Nom_produit'] ?></td>
                    <td><?= $l['quantite'] ?></td>
                    <td><?= $l['prix_total'] ?> €</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <p><strong>Total : <?= $detail['Montant'] ?> €</strong></p>
        <a href="vente.php">← Retour</a>
    </div>
    <?php endif; ?>

</div>

</body>
</html>