<?php

require_once 'connexion.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['utilisateur'])) { header('Location: index.php'); exit; }

// (int) empêche les caractères malveillants
$id = (int) ($_GET['id'] ?? 0);

if ($id == 0) { header('Location: produit.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM produit WHERE Id = :id');
$stmt->execute([':id' => $id]);
$produit = $stmt->fetch();

if (!$produit) { header('Location: produit.php'); exit; }

$stk = (int) $produit['stock'];
if ($stk == 0)     $badgeClass = 'badge-none';
elseif ($stk <= 5) $badgeClass = 'badge-warn';
else               $badgeClass = 'badge-ok';

require 'header.php';
?>

<div class="page-content">

    <a href="produit.php" class="btn-action btn-edit" style="display:inline-flex;margin-bottom:1rem">
        ← Retour liste
    </a>

    <div class="detail-layout">


        <div class="card detail-img-card">

            <div class="detail-img-box">
                <img src="uploads/<?= $produit['image'] ?>.jpg"
                     alt="<?= htmlspecialchars($produit['Nom_produit']) ?>"
                     onerror="this.style.display='none'">
                <span>Pas d'image</span>
            </div>

            <!-- Représentation visuelle du code-barres -->
            <div class="codebarre-zone">
                <div class="codebarre-barres">
                    <?php
                    // On génère 30 barres de largeur aléatoire
                    // pour simuler l'apparence d'un code-barres
                    for ($i = 0; $i < 30; $i++) {
                        $w = rand(1, 3);
                        echo '<div style="width:' . $w . 'px;background:#111;height:50px"></div>';
                    }
                    ?>
                </div>
                <p class="codebarre-texte"><?= htmlspecialchars($produit['code_barre']) ?></p>
                <p class="codebarre-label">CODE-BARRES GÉNÉRÉ</p>
            </div>

            <button class="btn-modif-photo">&#128247; modifier la photo</button>

        </div>

        <!-- ================================================
             COLONNE DROITE : Informations du produit
             ================================================ -->
        <div class="card detail-info-card">

            <h2>Édition Produit</h2>

            <table class="detail-table">
                <tr>
                    <td class="detail-label">Référence / Code-barres</td>
                    <td><?= htmlspecialchars($produit['code_barre']) ?></td>
                </tr>
                <tr>
                    <td class="detail-label">Nom produit</td>
                    <td><?= htmlspecialchars($produit['Nom_produit']) ?></td>
                </tr>
                <tr>
                    <td class="detail-label">Description</td>
                    <!-- nl2br() convertit les \n en <br> pour afficher les retours à la ligne -->
                    <td><?= nl2br(htmlspecialchars($produit['description'])) ?></td>
                </tr>
                <tr>
                    <td class="detail-label">Prix TTC (€)</td>
                    <td><strong><?= number_format((float)$produit['prix'], 2, ',', ' ') ?> €</strong></td>
                </tr>
                <tr>
                    <td class="detail-label">Stock actuel</td>
                    <td><span class="badge <?= $badgeClass ?>"><?= $stk ?> unités</span></td>
                </tr>
            </table>

            <div class="detail-actions">
                <a href="produit.php?edit=<?= $produit['Id'] ?>"
                   class="btn-save" style="display:inline-block;text-decoration:none;text-align:center;width:auto;padding:.5rem 1.2rem">
                    Modifier
                </a>
                <a href="produit.php?action=supprimer&id=<?= $produit['Id'] ?>"
                   onclick="return confirm('Supprimer ce produit ?')"
                   style="display:inline-block;text-decoration:none;text-align:center;
                          padding:.48rem 1.2rem;background:#fff;color:#B71C1C;
                          border:1px solid #FFCDD2;border-radius:7px;font-size:.88rem;font-weight:500">
                    Supprimer
                </a>
            </div>

        </div>

    </div>
</div>

</body>
</html>
