<?php
require 'connexion.php';
require 'header.php';

// Supprimer un produit
if (isset($_GET['supprimer'])) {
    $pdo->prepare('DELETE FROM produit WHERE Id = :id')->execute([':id' => $_GET['supprimer']]);
}

// Récupérer tous les produits
$req      = $pdo->query('SELECT * FROM produit ORDER BY Id DESC');
$produits = $req->fetchAll();
?>

<div class="contenu">

    <!-- Titre + recherche + bouton ajouter -->
    <div class="page-titre-barre">
        <h1 class="page-titre">Liste des produits</h1>
        <div style="display:flex; gap:.6rem; align-items:center;">
            <div class="search-produit-input">
                <span>🔍</span>
                <input type="text" placeholder="Rechercher un produit..."
                       oninput="filtrerProduits(this.value)">
            </div>
            <a href="ajout_produit.php" class="btn-ajouter-produit">+ Ajouter produit</a>
        </div>
    </div>

    <!-- Tableau -->
    <div class="produit-tableau">
        <table id="table-produits">
            <thead>
                <tr>
                    <th>CODE</th>
                    <th>NOM</th>
                    <th>PRIX TTC</th>
                    <th>STOCK</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($produits as $p):
                $stk = $p['stock'];
                if ($stk == 0)      $badge = 'badge-rouge';
                elseif ($stk <= 15) $badge = 'badge-orange';
                else                $badge = 'badge-vert';
            ?>
            <tr>
                <td class="prod-code"><?= $p['code_barre'] ?></td>
                <td><span class="prod-nom"><?= $p['Nom_produit'] ?></span></td>
                <td><?= number_format($p['prix'], 2, ',', ' ') ?> €</td>
                <td><span class="badge-stock <?= $badge ?>"><?= $stk ?></span></td>
                <td>
                    <a href="Detail_produit.php?id=<?= $p['Id'] ?>" class="action-edit">✏️</a>
                    <a href="produit.php?supprimer=<?= $p['Id'] ?>"
                       onclick="return confirm('Supprimer ce produit ?')"
                       class="action-del">🗑</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    </div>

</div>

<script>
function filtrerProduits(q) {
    document.querySelectorAll('#table-produits tbody tr').forEach(function(tr) {
        tr.style.display = tr.textContent.toLowerCase().includes(q.toLowerCase()) ? '' : 'none';
    });
}
</script>

</body>
</html>