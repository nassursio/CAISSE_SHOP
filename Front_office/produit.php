<?php
require 'connexion.php';
require 'header.php';

$message = '';

// ─── AJOUTER un produit ───────────────────────────────────
if (isset($_POST['ajouter'])) {

    $req = $pdo->prepare('INSERT INTO produit (Nom_produit, prix, description, stock, code_barre, image)
                          VALUES (:nom, :prix, :desc, :stock, :cb, 0)');

    $req->execute([
        ':nom'   => $_POST['nom'],
        ':prix'  => $_POST['prix'],
        ':desc'  => $_POST['description'],
        ':stock' => $_POST['stock'],
        ':cb'    => $_POST['code_barre'],
    ]);

    $message = 'Produit ajouté !';
}

// ─── MODIFIER un produit ──────────────────────────────────
if (isset($_POST['modifier'])) {

    $req = $pdo->prepare('UPDATE produit
                          SET Nom_produit=:nom, prix=:prix, description=:desc,
                              stock=:stock, code_barre=:cb
                          WHERE Id=:id');

    $req->execute([
        ':nom'   => $_POST['nom'],
        ':prix'  => $_POST['prix'],
        ':desc'  => $_POST['description'],
        ':stock' => $_POST['stock'],
        ':cb'    => $_POST['code_barre'],
        ':id'    => $_POST['id'],
    ]);

    $message = 'Produit modifié !';
}

// ─── SUPPRIMER un produit ─────────────────────────────────
if (isset($_GET['supprimer'])) {
    $req = $pdo->prepare('DELETE FROM produit WHERE Id = :id');
    $req->execute([':id' => $_GET['supprimer']]);
    $message = 'Produit supprimé.';
}

// ─── RÉCUPÉRER tous les produits ──────────────────────────
$req      = $pdo->query('SELECT * FROM produit ORDER BY Id DESC');
$produits = $req->fetchAll();

// ─── Charger un produit pour le modifier ──────────────────
$produit_a_modifier = null;
if (isset($_GET['modifier'])) {
    $req = $pdo->prepare('SELECT * FROM produit WHERE Id = :id');
    $req->execute([':id' => $_GET['modifier']]);
    $produit_a_modifier = $req->fetch();
}
?>

<div class="contenu">

    <?php if ($message != '') : ?>
        <p class="succes"><?= $message ?></p>
    <?php endif; ?>

    <div class="deux-colonnes">

        <!-- Liste des produits -->
        <div class="carte">
            <h2>Liste des produits</h2>

            <table>
                <thead>
                    <tr>
                        <th>Code-barres</th>
                        <th>Nom</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($produits as $p) : ?>
                    <tr>
                        <td><?= $p['code_barre'] ?></td>
                        <td><?= $p['Nom_produit'] ?></td>
                        <td><?= $p['prix'] ?> €</td>
                        <td><?= $p['stock'] ?></td>
                        <td>
                            <a href="Detail_produit.php?id=<?= $p['Id'] ?>">Détail</a>
                            <a href="produit.php?modifier=<?= $p['Id'] ?>">Modifier</a>
                            <a href="produit.php?supprimer=<?= $p['Id'] ?>"
                               onclick="return confirm('Supprimer ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
<button type="button" onclick="window.location.href='ajout_produit.php'">Ajouter un produit</button>

    </div>
</div>

</body>
</html>