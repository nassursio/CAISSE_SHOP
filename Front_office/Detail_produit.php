<?php
require 'connexion.php';
require 'header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

//  MODIFICATION
if (isset($_POST['modifier'])) {

    $req = $pdo->prepare("
        UPDATE produit SET
        code_barre = :code_barre,
        Nom_produit = :nom,
        description = :description,
        prix = :prix,
        stock = :stock
        WHERE Id = :id
    ");

    $req->execute([
        ':code_barre' => $_POST['code_barre'],
        ':nom' => $_POST['nom'],
        ':description' => $_POST['description'],
        ':prix' => $_POST['prix'],
        ':stock' => $_POST['stock'],
        ':id' => $_POST['id']
    ]);

    echo "<p style='color:green'>Produit modifié avec succès</p>";
}

//  RÉCUP PRODUIT
$req = $pdo->prepare("SELECT * FROM produit WHERE Id = :id");
$req->execute([':id' => $id]);
$p = $req->fetch();

if (!$p) {
    echo "Produit introuvable";
    exit;
}
?>

<div class="contenu">

    <a href="produit.php">← Retour liste</a>

    <div class="carte" style="display:flex; gap:2rem; margin-top:1rem">

        <!-- IMAGE -->
        <div style="flex:1">
            <img src="image_produit.png" style="width:100%; max-width:300px">

            <p style="margin-top:1rem">CODE-BARRES GÉNÉRÉ</p>
            <img src="barcode.png" style="width:150px">

            <br><br>
            <button>📷 modifier la photo</button>
        </div>

        <!-- FORMULAIRE -->
        <div style="flex:2">
            <h2>Édition Produit</h2>

            <form method="POST">

                <input type="hidden" name="id" value="<?= $p['Id'] ?>">

                <label>Référence / Code-barres</label>
                <input type="text" name="code_barre"
                    value="<?= htmlspecialchars($p['code_barre']) ?>">

                <label>Nom produit</label>
                <input type="text" name="nom"
                    value="<?= htmlspecialchars($p['Nom_produit']) ?>">

                <label>Description</label>
                <textarea name="description"><?= htmlspecialchars($p['description']) ?></textarea>

                <div style="display:flex; gap:1rem">
                    <div>
                        <label>Prix (€)</label>
                        <input type="number" step="0.01" name="prix"
                            value="<?= htmlspecialchars($p['prix']) ?>">
                    </div>

                    <div>
                        <label>Stock</label>
                        <input type="number" name="stock"
                            value="<?= htmlspecialchars($p['stock']) ?>">
                    </div>
                </div>

                <br>

                <button type="submit" name="modifier">
                     Enregistrer
                </button>

            </form>
        </div>

    </div>

</div>

</body>
</html>