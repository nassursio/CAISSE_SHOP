<?php
require 'connexion.php';
require 'header.php';

$id = [];

if (isset($_POST['ajouter'])) {

    $req = $pdo->prepare("
        INSERT INTO produit
    Id => auto-increment
    Nom_produit = :nom
    prix = :prix
    description = :description
    stock = :stock
    code_barre = :code_barre
    image = :image
    ");
    

    $req->execute([
        ':nom' => $_POST['nom'],
        ':prix' => $_POST['prix'],
        ':description' => $_POST['description'],
        ':stock' => $_POST['stock'],
        ':code_barre' => $_POST['code_barre'],
        ':image' => 0
    ]);

    echo "<p style='color:green'>Produit ajouté avec succès</p>";
}


?>
<div class="ticket-gauche">
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


                <label>Référence / Code-barres</label>
                <input type="text" name="code_barre">

                <label>Nom produit</label>
                <input type="text" name="nom">

                <label>Description</label>
                <textarea name="description"></textarea>

                <div style="display:flex; gap:1rem">
                    <div>
                        <label>Prix (€)</label>
                        <input type="number" step="0.01" name="prix">
                    </div>

                    <div>
                        <label>Stock</label>
                        <input type="number" name="stock">
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
</div>
</body>
</html>