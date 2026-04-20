<?php
require 'connexion.php';
require 'header.php';

$id      = (int)($_GET['id'] ?? 0);
$message = '';

// Enregistrer les modifications
if (isset($_POST['modifier'])) {
    $req = $pdo->prepare('UPDATE produit SET code_barre=:c, Nom_produit=:n, description=:d, prix=:p, stock=:s WHERE Id=:id');
    $req->execute([
        ':c'  => $_POST['code_barre'],
        ':n'  => $_POST['nom'],
        ':d'  => $_POST['description'],
        ':p'  => $_POST['prix'],
        ':s'  => $_POST['stock'],
        ':id' => $_POST['id'],
    ]);
    $message = 'Produit modifié avec succès !';
}

// Récupérer le produit
$req = $pdo->prepare('SELECT * FROM produit WHERE Id = :id');
$req->execute([':id' => $id]);
$p = $req->fetch();

if (!$p) { echo "Produit introuvable."; exit; }
?>

<div class="contenu">

    <a href="produit.php" class="retour-lien">← Retour liste</a>

    <?php if ($message): ?>
        <div class="msg-succes"><?= $message ?></div>
    <?php endif; ?>

    <div class="detail-carte">

        <!-- Colonne gauche : image + code-barres -->
        <div class="detail-gauche">

            <div class="detail-image-zone">
                <span style="color:#9CA3AF;font-size:.85rem">Pas d'image</span>
            </div>

            <div class="codebarre-section">
                <p class="codebarre-label">CODE-BARRES GÉNÉRÉ</p>
                <div class="codebarre-zone">
                    <div class="codebarre-barres">
                        <?php
                        // Barres visuelles basées sur le code
                        foreach (str_split($p['code_barre']) as $i => $c) {
                            $w = ($i % 3 == 0) ? 3 : 1;
                            echo "<div style='width:{$w}px;height:48px;background:#1A1A2E;'></div>";
                        }
                        ?>
                    </div>
                    <div class="codebarre-numero"><?= $p['code_barre'] ?></div>
                </div>
            </div>

            <button class="btn-modifier-photo">📷 modifier la photo</button>

        </div>

        <!-- Colonne droite : formulaire -->
        <div class="detail-droite">
            <h2>Édition Produit</h2>

            <form method="POST">
                <input type="hidden" name="id" value="<?= $p['Id'] ?>">

                <label>Référence / Code-barres</label>
                <input type="text" name="code_barre" value="<?= htmlspecialchars($p['code_barre']) ?>">

                <label>Nom produit</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($p['Nom_produit']) ?>">

                <label>Description</label>
                <textarea name="description"><?= htmlspecialchars($p['description']) ?></textarea>

                <div class="detail-prix-stock">
                    <div>
                        <label>Prix TTC (€)</label>
                        <input type="number" step="0.01" name="prix" value="<?= $p['prix'] ?>">
                    </div>
                    <div>
                        <label>Stock actuel</label>
                        <input type="number" name="stock" value="<?= $p['stock'] ?>">
                    </div>
                </div>

                <button type="button" class="btn-generer-cb">|||| Générer code-barres</button>

                <button type="submit" name="modifier" class="btn-enregistrer">💾 Enregistrer</button>
            </form>
        </div>

    </div>

</div>

</body>
</html>