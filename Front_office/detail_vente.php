<?php
require 'connexion.php';
require 'header.php';

$id_vente = $_GET['id'] ?? 0;

// Charger la vente + le nom de l'utilisateur
$req = $pdo->prepare('SELECT v.*, u.Prenom, u.Nom
                      FROM vente v
                      JOIN utilisateurs u ON u.Id = v.id_utilisateur
                      WHERE v.Id = :id');
$req->execute([':id' => $id_vente]);
$vente = $req->fetch();


if (!$vente) {
    header('Location: vente.php');
    exit;
}

// Charger les produits de cette vente
$req = $pdo->prepare('SELECT pv.*, p.Nom_produit, p.code_barre, p.prix
                      FROM produit_vendu pv
                      JOIN produit p ON p.Id = pv.id_produit
                      WHERE pv.id_vente = :id');
$req->execute([':id' => $id_vente]);
$lignes = $req->fetchAll();
?>

<div class="contenu">

    <a href="vente.php" class="retour-lien">← Retour historique</a>

    <!-- En-tête de la vente -->
    <div class="historique-carte" style="margin-bottom:1rem">
        <div class="historique-entete">
            <div>
                <p class="historique-titre">
                    Détail vente #<?= $vente['Id'] ?>
                </p>
                <p class="historique-sous">
                    Le <?= date('d/m/Y à H:i', strtotime($vente['Date'])) ?>
                    — Caissier : <?= $vente['Prenom'] ?> <?= $vente['Nom'] ?>
                </p>
            </div>
            <div class="historique-btns">
                <button onclick="window.print()" class="btn-export-bleu">🖨 Imprimer</button>
            </div>
        </div>
    </div>

    <!-- Tableau des produits vendus -->
    <div class="historique-carte">

        <table class="ventes-table">
            <thead>
                <tr>
                    <th>CODE-BARRES</th>
                    <th>PRODUIT</th>
                    <th>PRIX UNITAIRE</th>
                    <th>QUANTITÉ</th>
                    <th>TOTAL LIGNE</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($lignes as $l) : ?>
                <tr>
                    <td style="font-family:monospace;font-size:.8rem;color:#9CA3AF">
                        <?= $l['code_barre'] ?>
                    </td>
                    <td style="font-weight:500"><?= $l['Nom_produit'] ?></td>
                    <td><?= number_format($l['prix'], 2, ',', ' ') ?> €</td>
                    <td><?= $l['quantite'] ?></td>
                    <td style="font-weight:700">
                        <?= number_format($l['prix_total'], 2, ',', ' ') ?> €
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Total de la vente -->
        <div style="display:flex;justify-content:space-between;align-items:center;padding:1rem 1.2rem;border-top:1px solid #F3F4F6">
            <span style="font-size:.85rem;color:#6B7280">
                <?= $vente['total_produit'] ?> article(s) vendu(s)
            </span>
            <span style="font-size:1.3rem;font-weight:800;color:#1565C0">
                Total : <?= number_format($vente['Montant'], 2, ',', ' ') ?> €
            </span>
        </div>

    </div>

</div>

</body>
</html>