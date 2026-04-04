<?php

require_once 'connexion.php';


// Vérifier que l'utilisateur est connecté
// Si pas de session, on redirige vers la page de connexion
//if (empty($_SESSION['utilisateur'])) {
//    header('Location: index.php'); exit;
//}

$msg     = '';
$msgType = 'succes';


//  AJOUT D'UN PRODUIT

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'ajouter') {

    $nom  = trim($_POST['Nom_produit']);
    $prix = (float) $_POST['prix'];     
    $desc = trim($_POST['description']);
    $stk  = (int)   $_POST['stock'];    
    $cb   = trim($_POST['code_barre']);

    if ($nom == '' || $prix <= 0 || $cb == '') {
        $msg = 'Nom, prix et code-barres sont obligatoires.';
        $msgType = 'erreur';
    } else {
        // INSERT : ajouter une nouvelle ligne dans la table produit
        $sql = 'INSERT INTO produit (Nom_produit, prix, description, stock, code_barre, image)
                VALUES (:nom, :prix, :desc, :stk, :cb, 0)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':nom'=>$nom, ':prix'=>$prix, ':desc'=>$desc, ':stk'=>$stk, ':cb'=>$cb]);
        $msg = 'Produit ajouté avec succès.';
    }
}


//  MODIFICATION D'UN PRODUIT

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'modifier') {

    $id   = (int)   $_POST['id'];
    $nom  = trim($_POST['Nom_produit']);
    $prix = (float) $_POST['prix'];
    $desc = trim($_POST['description']);
    $stk  = (int)   $_POST['stock'];
    $cb   = trim($_POST['code_barre']);

    // UPDATE : modifier uniquement le produit qui a cet Id
    $sql = 'UPDATE produit
            SET Nom_produit = :nom,
                prix        = :prix,
                description = :desc,
                stock       = :stk,
                code_barre  = :cb
            WHERE Id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':nom'=>$nom, ':prix'=>$prix, ':desc'=>$desc, ':stk'=>$stk, ':cb'=>$cb, ':id'=>$id]);
    $msg = 'Produit modifié avec succès.';
}


//  SUPPRESSION D'UN PRODUIT

if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $pdo->prepare('DELETE FROM produit WHERE Id = :id')->execute([':id' => $id]);
    $msg = 'Produit supprimé.';
    $msgType = 'erreur';
}


//  LISTE DES PRODUITS 

$search = trim($_GET['q'] ?? '');

if ($search != '') {
    // LIKE '%...%' : cherche le texte n'importe où dans le champ
    $sql  = 'SELECT * FROM produit WHERE Nom_produit LIKE :q OR code_barre LIKE :q ORDER BY Id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':q' => '%' . $search . '%']);
} else {
    $stmt = $pdo->query('SELECT * FROM produit ORDER BY Id DESC');
}
$produits = $stmt->fetchAll();


//  CHARGEMENT DU PRODUIT À MODIFIER


$edit = null; // null = mode ajout

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM produit WHERE Id = :id');
    $stmt->execute([':id' => (int) $_GET['edit']]);
    $edit = $stmt->fetch();
}

require 'header.php';
?>

<div class="page-content">

    <?php if ($msg != ''): ?>
        <div class="alerte-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    
    <div class="produit-layout">

        <!-- ================================================
             LISTE DES PRODUITS
             ================================================ -->
        <div class="card">

            <div class="card-header">
                <h2>Liste des produits</h2>
                <form method="GET" action="produit.php" class="search-form">
                    <input type="text" name="q"
                           value="<?= htmlspecialchars($search) ?>"
                           placeholder="Rechercher un produit…">
                    <button type="submit" class="btn-search">Rechercher</button>
                    <?php if ($search != ''): ?>
                        <button type="button" class="btn-reset"
                                onclick="window.location.href='produit.php'">Réinitialiser</button>
                    <?php endif; ?>
                </form>
            </div>

            <table class="table">
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
                <?php if (empty($produits)): ?>
                    <tr><td colspan="5" class="table-vide">Aucun produit trouvé.</td></tr>
                <?php else: ?>
                    <?php foreach ($produits as $p):
                        // Choisir la couleur du badge selon le niveau de stock
                        $stk = (int) $p['stock'];
                        if ($stk == 0)      $badgeClass = 'badge-none'; 
                        elseif ($stk <= 5)  $badgeClass = 'badge-warn'; 
                        else                $badgeClass = 'badge-ok';   
                    ?>
                    <tr>
                        <td class="code-barre"><?= htmlspecialchars($p['code_barre']) ?></td>
                        <td class="nom-produit"><?= htmlspecialchars($p['Nom_produit']) ?></td>
                        <td><?= number_format((float)$p['prix'], 2, ',', ' ') ?> €</td>
                        <td><span class="badge <?= $badgeClass ?>"><?= $stk ?></span></td>
                        <td class="actions-cell">
                            
                            <a href="produit.php?edit=<?= $p['Id'] ?>" class="btn-action btn-edit">Modifier</a>
                            <a href="Detail_produit.php?id=<?= $p['Id'] ?>" class="btn-action btn-detail">Détail</a>
                            <a href="produit.php?action=supprimer&id=<?= $p['Id'] ?>"
                               onclick="return confirm('Supprimer ce produit ?')"
                               class="btn-action btn-del">Supprimer</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>

            <div class="table-footer">
                Affichage de <?= count($produits) ?> produit(s)
            </div>

        </div>

        <!-- ================================================
             FORMULAIRE AJOUT / MODIFICATION
             ================================================ -->
        <div class="card form-card">

            <h3><?= $edit ? 'Édition Produit' : 'Ajouter un produit' ?></h3>

            <form method="POST" action="produit.php">

                <input type="hidden" name="action" value="<?= $edit ? 'modifier' : 'ajouter' ?>">

                <?php if ($edit): ?>
                    <input type="hidden" name="id" value="<?= $edit['Id'] ?>">
                <?php endif; ?>

                <label>Référence / Code-barres</label>
                <input type="text" name="code_barre"
                       value="<?= htmlspecialchars($edit['code_barre'] ?? '') ?>" required>

                <label>Nom produit</label>
                <input type="text" name="Nom_produit"
                       value="<?= htmlspecialchars($edit['Nom_produit'] ?? '') ?>" required>

                <label>Description</label>
                <textarea name="description" rows="3"><?= htmlspecialchars($edit['description'] ?? '') ?></textarea>

                <div class="form-row">
                    <div>
                        <label>Prix TTC (€)</label>
                        <input type="number" name="prix" min="0" step="0.01"
                               value="<?= htmlspecialchars($edit['prix'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label>Stock actuel</label>
                        <input type="number" name="stock" min="0"
                               value="<?= htmlspecialchars($edit['stock'] ?? 0) ?>">
                    </div>
                </div>

                <button type="submit" class="btn-save">Enregistrer</button>

                <?php if ($edit): ?>
                    <button type="button" class="btn-cancel"
                            onclick="window.location.href='produit.php'">Annuler</button>
                <?php endif; ?>

            </form>

        </div>

    </div>
</div>

</body>
</html>
