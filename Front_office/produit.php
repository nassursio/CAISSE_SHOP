<?php
require 'connexion.php';
require 'header.php';

$message = '';

// Ajouter un produit
if (isset($_POST['ajouter'])) {
    $req = $pdo->prepare('INSERT INTO produit (Nom_produit,prix,description,stock,code_barre,image) VALUES (:n,:p,:d,:s,:c,0)');
    $req->execute([':n'=>$_POST['nom'],':p'=>$_POST['prix'],':d'=>$_POST['description'],':s'=>$_POST['stock'],':c'=>$_POST['code_barre']]);
    $message = 'Produit ajouté !';
}

// Supprimer un produit
if (isset($_GET['supprimer'])) {
    $pdo->prepare('DELETE FROM produit WHERE Id=:id')->execute([':id'=>$_GET['supprimer']]);
    $message = 'Produit supprimé.';
}

// Récupérer les produits
$req      = $pdo->query('SELECT * FROM produit ORDER BY Id DESC');
$produits = $req->fetchAll();
?>

<div class="contenu">

    <?php if ($message): ?>
        <div class="msg-succes"><?= $message ?></div>
    <?php endif; ?>

    <!-- Titre + barre de recherche + bouton ajouter -->
    <div class="page-titre-barre">
        <h1 class="page-titre">Liste des produits</h1>
        <div class="search-produit">
            <div class="search-produit-input">
                <span></span>
                <input type="text" id="search" placeholder="Rechercher un produit..."
                       oninput="filtrerProduits(this.value)">
            </div>
            <a href="Detail_produit.php?nouveau=1" class="btn-ajouter-produit">+ Ajouter produit</a>
        </div>
    </div>

    <!-- Tableau des produits -->
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
                <td>
                    <span class="prod-nom"><?= $p['Nom_produit'] ?></span>
                </td>
                <td><?= number_format($p['prix'], 2, ',', ' ') ?> €</td>
                <td>
                    <span class="badge-stock <?= $badge ?>"><?= $stk ?></span>
                </td>
                <td>
                    <a href="Detail_produit.php?id=<?= $p['Id'] ?>" class="action-edit" title="Modifier">✏️</a>
                    <a href="produit.php?supprimer=<?= $p['Id'] ?>"
                       onclick="return confirm('Supprimer ce produit ?')"
                       class="action-del" title="Supprimer">🗑</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pied de tableau -->
        <div class="pagination">
            <span>Affichage de 1 à <?= count($produits) ?> sur <?= count($produits) ?> produits</span>
            <div style="display:flex;gap:.5rem">
                <a href="#" class="btn-export-csv">⬇ Exporter CSV</a>
            </div>
        </div>
    </div>

</div>

<script>
// Filtrer les produits en temps réel
function filtrerProduits(q) {
    const lignes = document.querySelectorAll('#table-produits tbody tr');
    lignes.forEach(function(tr) {
        const texte = tr.textContent.toLowerCase();
        tr.style.display = texte.includes(q.toLowerCase()) ? '' : 'none';
    });
}
</script>

</body>
</html>