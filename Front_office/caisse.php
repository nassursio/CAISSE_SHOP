<?php
require 'connexion.php';
require 'header.php';

if (!isset($_SESSION['ticket'])) $_SESSION['ticket'] = [];

$message = ''; $produit_trouve = null;
$recherche = $_POST['recherche'] ?? '';

// Rechercher un produit
if ($recherche != '') {
    $req = $pdo->prepare('SELECT * FROM produit WHERE code_barre=:q OR Nom_produit LIKE :l LIMIT 1');
    $req->execute([':q'=>$recherche, ':l'=>'%'.$recherche.'%']);
    $produit_trouve = $req->fetch();
}

// Ajouter au ticket
if (isset($_POST['ajouter']) && $produit_trouve) {
    $id  = $produit_trouve['Id'];
    $qte = $_POST['quantite'] ?? 1;
    if (isset($_SESSION['ticket'][$id]))
        $_SESSION['ticket'][$id]['quantite'] += $qte;
    else
        $_SESSION['ticket'][$id] = ['Id'=>$id,'Nom_produit'=>$produit_trouve['Nom_produit'],'prix'=>$produit_trouve['prix'],'quantite'=>$qte,'stock'=>$produit_trouve['stock'],'code_barre'=>$produit_trouve['code_barre']];
    $recherche = ''; $produit_trouve = null;
}

// Modifier quantité
if (isset($_POST['maj_qte'])) {
    $id  = $_POST['id']; $qte = $_POST['quantite'];
    if ($qte <= 0) unset($_SESSION['ticket'][$id]);
    else $_SESSION['ticket'][$id]['quantite'] = $qte;
}

if (isset($_GET['supprimer'])) unset($_SESSION['ticket'][$_GET['supprimer']]);

if (isset($_GET['vider'])) $_SESSION['ticket'] = [];

// Valider la vente
if (isset($_POST['valider'])) {
    if (empty($_SESSION['ticket'])) {
        $message = 'Le ticket est vide !';
    } else {
        $montant = 0; $total_articles = 0;
        foreach ($_SESSION['ticket'] as $l) {
            $montant        += $l['prix'] * $l['quantite'];
            $total_articles += $l['quantite'];
        }
        $req = $pdo->prepare('INSERT INTO vente (Montant, Date, id_utilisateur, total_produit) VALUES (:m, NOW(), :u, :t)');
        $req->execute([':m'=>$montant, ':u'=>$_SESSION['user']['Id'], ':t'=>$total_articles]);
        $id_vente = $pdo->lastInsertId();
        foreach ($_SESSION['ticket'] as $l) {
            $req = $pdo->prepare('INSERT INTO produit_vendu (prix_total, quantite, id_vente, id_produit) VALUES (:pt,:q,:v,:p)');
            $req->execute([':pt'=>$l['prix']*$l['quantite'],':q'=>$l['quantite'],':v'=>$id_vente,':p'=>$l['Id']]);
            $req = $pdo->prepare('UPDATE produit SET stock=stock-:q WHERE Id=:id');
            $req->execute([':q'=>$l['quantite'],':id'=>$l['Id']]);
        }
        $_SESSION['ticket'] = [];
        $message = 'Vente enregistrée ! Total : ' . number_format($montant, 2, ',', ' ') . ' €';
    }
}

// Total ticket
$total = 0;
foreach ($_SESSION['ticket'] as $l) $total += $l['prix'] * $l['quantite'];
?>

<div class="contenu">

    <?php if ($message): ?>
        <div class="msg-succes"><?= $message ?></div>
    <?php endif; ?>

    <!-- Barre de recherche / scan -->
    <form method="POST" class="barre-recherche">
        <span class="icone-loupe"></span>
        <input type="text" name="recherche" value="<?= htmlspecialchars($recherche) ?>"
               placeholder="Rechercher un produit (nom, référence...)" autofocus>
        <button type="submit" class="btn-rechercher">Rechercher</button>
        <div class="scan-zone">
            <span>|||</span>
            <div>
                <strong>SCAN CODE-BARRES</strong>
                <small>Présentez le produit devant le lecteur</small>
            </div>
        </div>
    </form>

    <div class="caisse-layout">

        <!-- Zone gauche : produit trouvé -->
        <div>
            <?php if ($produit_trouve): ?>
            <div class="produit-carte">
                <div style="text-align:center">
                    <div class="produit-image" style="width:260px;height:260px;border:1px solid #E5E7EB;border-radius:10px;background:#F9FAFB;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:.8rem;color:#9CA3AF">
                        Pas d'image
                    </div>
                    <p class="produit-ref">Ref: <?= $produit_trouve['code_barre'] ?></p>
                    <p class="produit-nom"><?= $produit_trouve['Nom_produit'] ?></p>
                    <p class="produit-prix"><?= number_format($produit_trouve['prix'], 2, ',', ' ') ?> €</p>
                </div>

                <!-- Stock -->
                <div class="stock-barre">
                    <span>Stock en magasin</span>
                    <?php if ($produit_trouve['stock'] > 0): ?>
                        <span class="stock-ok">● <?= $produit_trouve['stock'] ?> unités</span>
                    <?php else: ?>
                        <span class="stock-zero">Rupture de stock</span>
                    <?php endif; ?>
                </div>

                <!-- Quantité + bouton ajouter -->
                <form method="POST">
                    <input type="hidden" name="recherche" value="<?= $produit_trouve['code_barre'] ?>">
                    <p class="qte-label">Modifier la quantité</p>
                    <div class="qte-ctrl">
                        <button type="button" onclick="var i=document.getElementById('qte');i.value=Math.max(1,+i.value-1)">−</button>
                        <div class="qte-val"><input type="number" id="qte" name="quantite" value="1" min="1" style="border:none;outline:none;text-align:center;width:100%;font-size:1rem;font-weight:600"></div>
                        <button type="button" onclick="document.getElementById('qte').value=+document.getElementById('qte').value+1">+</button>
                    </div>
                    <button type="submit" name="ajouter"
                            style="width:100%;margin-top:.8rem;padding:.65rem;background:#1565C0;color:white;border:none;border-radius:8px;font-size:.9rem;font-weight:600;cursor:pointer;font-family:inherit">
                        Ajouter au ticket
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <!-- Zone droite : ticket -->
        <div class="ticket-carte">
            <div class="ticket-titre">Ticket en cours</div>

            <table class="ticket-table">
                <thead>
                    <tr>
                        <th>Désignation</th>
                        <th>Prix</th>
                        <th>Stock restant</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($_SESSION['ticket'])): ?>
                    <tr><td colspan="4" style="text-align:center;padding:2rem;color:#9CA3AF">Ticket vide</td></tr>
                <?php else: ?>
                    <?php foreach ($_SESSION['ticket'] as $l): ?>
                    <tr>
                        <td>
                            <span class="ticket-prod-nom"><?= $l['Nom_produit'] ?></span>
                            <span class="ticket-prod-ref">Ref: <?= $l['code_barre'] ?></span>
                        </td>
                        <td><?= number_format($l['prix'], 2, ',', ' ') ?> €</td>
                        <td style="text-align:center"><?= $l['stock'] ?></td>
                        <td class="ticket-total-cel"><?= number_format($l['prix']*$l['quantite'], 2, ',', ' ') ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>

            <!-- Sous-total -->
            <div class="ticket-soustotal">
                <span>Sous-total</span>
                <span><?= number_format($total, 2, ',', ' ') ?> €</span>
            </div>

            <!-- Total TTC -->
            <div class="ticket-total-zone">
                <span class="ticket-total-label">Total TTC</span>
                <span class="ticket-total-montant"><?= number_format($total, 2, ',', ' ') ?> €</span>
            </div>

            <!-- Boutons Suppr / Vider / Valider -->
            <div class="ticket-btns-haut">
                <?php $cle = !empty($_SESSION['ticket']) ? array_key_last($_SESSION['ticket']) : 0; ?>
                <a href="caisse.php?supprimer=<?= $cle ?>" class="btn-suppr"> Supprimer</a>
                <a href="caisse.php?vider=1" onclick="return confirm('Vider ?')" class="btn-vider">✕ Vider</a>
                <form method="POST" style="display:contents">
                    <button type="submit" name="valider" class="btn-valider">✓ VALIDER</button>
                </form>
            </div>

            <!-- Générer ticket PDF -->
            <form method="POST">
                <button type="submit" name="valider" class="btn-pdf"> Générer ticket PDF</button>
            </form>

            <p class="ticket-note"> Ticket PDF généré après validation</p>
        </div>

    </div>
</div>

</body>
</html>