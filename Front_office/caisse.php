<?php

require_once 'connexion.php';


//if (empty($_SESSION['utilisateur'])) { header('Location: index.php'); exit; }

if (!isset($_SESSION['ticket'])) {
    $_SESSION['ticket'] = [];
}

$msg            = '';
$produit_trouve = null;


$recherche = trim($_POST['recherche'] ?? $_GET['q'] ?? '');

if ($recherche != '') {
    $sql  = 'SELECT * FROM produit WHERE code_barre = :q OR Nom_produit LIKE :like LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':q' => $recherche, ':like' => '%' . $recherche . '%']);
    $produit_trouve = $stmt->fetch();
}


//  AJOUTER UN PRODUIT AU TICKET
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'ajouter') {

    if ($produit_trouve) {
        $id  = $produit_trouve['Id'];
        $qte = (int) ($_POST['quantite'] ?? 1);
        if ($qte < 1) $qte = 1;

        if (isset($_SESSION['ticket'][$id])) {
            // Produit déjà dans le ticket → on augmente la quantité
            $_SESSION['ticket'][$id]['quantite'] = $_SESSION['ticket'][$id]['quantite'] + $qte;
        } else {
            // Nouveau produit → on l'ajoute au ticket
            $_SESSION['ticket'][$id] = [
                'Id'          => $id,
                'Nom_produit' => $produit_trouve['Nom_produit'],
                'prix'        => (float) $produit_trouve['prix'],
                'quantite'    => $qte,
                'stock'       => (int)   $produit_trouve['stock'],
                'code_barre'  => $produit_trouve['code_barre'],
            ];
        }
        $recherche      = '';
        $produit_trouve = null;
    }
}

//  MODIFIER LA QUANTITÉ D'UNE LIGNE DU TICKET
//  Si quantité = 0, on supprime la ligne

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'qte') {
    $id  = (int) $_POST['id'];
    $qte = (int) $_POST['quantite'];
    if ($qte <= 0) {
        unset($_SESSION['ticket'][$id]);
    } else {
        $_SESSION['ticket'][$id]['quantite'] = $qte;
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'suppr_ligne' && isset($_GET['id'])) {
    unset($_SESSION['ticket'][(int) $_GET['id']]);
}

if (isset($_GET['action']) && $_GET['action'] == 'vider') {
    $_SESSION['ticket'] = [];
}


//  VALIDER LA VENTE

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'valider') {

    if (empty($_SESSION['ticket'])) {
        $msg = 'Le ticket est vide, impossible de valider.';
    } else {

        $ticket        = $_SESSION['ticket'];
        $montant       = 0;
        $total_produit = 0;

        // Calculer le montant total et le nombre d'articles
        foreach ($ticket as $ligne) {
            $montant       = $montant       + ($ligne['prix'] * $ligne['quantite']);
            $total_produit = $total_produit + $ligne['quantite'];
        }

        $id_utilisateur = $_SESSION['utilisateur']['Id'];

        $sql  = 'INSERT INTO vente (Montant, Date, id_utilisateur, total_produit)
                 VALUES (:montant, NOW(), :uid, :total)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':montant' => $montant, ':uid' => $id_utilisateur, ':total' => $total_produit]);

        // lastInsertId() récupère l'Id auto-incrémenté de la vente créée
        $id_vente = (int) $pdo->lastInsertId();

        foreach ($ticket as $ligne) {

            $sql  = 'INSERT INTO produit_vendu (prix_total, quantite, id_vente, id_produit)
                     VALUES (:pt, :qte, :iv, :ip)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':pt'  => $ligne['prix'] * $ligne['quantite'],
                ':qte' => $ligne['quantite'],
                ':iv'  => $id_vente,
                ':ip'  => $ligne['Id']
            ]);

            // stock = stock - quantité vendue
            $sql  = 'UPDATE produit SET stock = stock - :qte WHERE Id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':qte' => $ligne['quantite'], ':id' => $ligne['Id']]);
        }

        // Vider le ticket après validation réussie
        $_SESSION['ticket'] = [];

        // Rediriger avec le montant pour afficher le message de confirmation
        header('Location: caisse.php?vente_ok=1&montant=' . number_format($montant, 2, '.', ''));
        exit;
    }
}

// Calculer le sous-total du ticket actuel
$ticket    = $_SESSION['ticket'] ?? [];
$soustotal = 0;
foreach ($ticket as $ligne) {
    $soustotal = $soustotal + ($ligne['prix'] * $ligne['quantite']);
}

require 'header.php';
?>

<div class="page-content">

    <?php if (isset($_GET['vente_ok'])): ?>
        <div class="alerte-succes">
            Vente enregistrée avec succès !
            Total : <strong><?= htmlspecialchars($_GET['montant']) ?> €</strong>
        </div>
    <?php endif; ?>

    <?php if ($msg != ''): ?>
        <div class="alerte-erreur"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="caisse-layout">


        <div class="caisse-gauche">

            <!-- Barre de recherche / scan code-barres -->
            <form method="POST" action="caisse.php" class="search-bar">
                <input type="text"
                       name="recherche"
                       id="scan-input"
                       autofocus
                       value="<?= htmlspecialchars($recherche) ?>"
                       placeholder="Rechercher un produit (nom, référence…)">
                <button type="submit" class="btn-search">Rechercher</button>
                <div class="scan-badge">
                    &#9646;&#9646;&#9646; SCAN CODE-BARRES
                    <small>Présentez le produit devant le lecteur</small>
                </div>
            </form>

            <!-- Carte du produit trouvé -->
            <?php if ($produit_trouve): ?>
            <div class="produit-card">

                <div class="produit-img-box">
                    <img src="uploads/<?= $produit_trouve['image'] ?>.jpg"
                         alt="<?= htmlspecialchars($produit_trouve['Nom_produit']) ?>"
                         onerror="this.style.display='none'">
                    <span>Pas d'image</span>
                </div>

                <div class="produit-infos">
                    <p class="produit-ref">Réf. <?= htmlspecialchars($produit_trouve['code_barre']) ?></p>
                    <p class="produit-nom"><?= htmlspecialchars($produit_trouve['Nom_produit']) ?></p>
                    <p class="produit-prix"><?= number_format((float)$produit_trouve['prix'], 2, ',', ' ') ?> €</p>

                    <!-- Affichage du stock restant avec couleur -->
                    <div class="stock-bar">
                        Stock en magasin
                        <?php if ($produit_trouve['stock'] > 0): ?>
                            <span class="stock-ok">&#9679; <?= $produit_trouve['stock'] ?> unités</span>
                        <?php else: ?>
                            <span class="stock-none">Rupture de stock</span>
                        <?php endif; ?>
                    </div>


                    <form method="POST" action="caisse.php" class="qte-form">
                        <input type="hidden" name="action" value="ajouter">

                        <input type="hidden" name="recherche"
                               value="<?= htmlspecialchars($produit_trouve['code_barre']) ?>">
                        <label>Modifier la quantité</label>
                        <div class="qte-selector">

                            <button type="button" class="btn-qte"
                                    onclick="var i=document.getElementById('qte');
                                             i.value=Math.max(1,+i.value-1)">−</button>
                            <input type="number" id="qte" name="quantite" value="1" min="1">

                            <button type="button" class="btn-qte"
                                    onclick="document.getElementById('qte').value=
                                             +document.getElementById('qte').value+1">+</button>
                            <button type="submit" class="btn-ajouter">Ajouter au ticket</button>
                        </div>
                    </form>
                </div>

            </div>
            <?php endif; ?>

        </div>


        <div class="ticket-card">

            <div class="ticket-header">Ticket en cours</div>

            <table class="ticket-table">
                <thead>
                    <tr>
                        <th>Désignation</th>
                        <th>Qté</th>
                        <th>PU</th>
                        <th>Stock restant</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($ticket)): ?>
                    <tr><td colspan="5" class="ticket-vide">Ticket vide</td></tr>
                <?php else: ?>
                    <?php foreach ($ticket as $ligne): ?>
                    <tr>
                        <td>
                            <span class="ticket-nom"><?= htmlspecialchars($ligne['Nom_produit']) ?></span>
                            <span class="ticket-ref">Réf. <?= htmlspecialchars($ligne['code_barre']) ?></span>
                        </td>
                        <td>
                            <!-- Mini formulaire pour modifier la quantité de cette ligne -->
                            <form method="POST" action="caisse.php">
                                <input type="hidden" name="action" value="qte">
                                <input type="hidden" name="id" value="<?= $ligne['Id'] ?>">
                                <div class="qte-selector-mini">

                                    <button type="button" class="btn-qte-sm"
                                            onclick="var i=this.nextElementSibling;
                                                     i.value=Math.max(0,+i.value-1);
                                                     this.form.submit()">−</button>

                                    <input type="number" name="quantite"
                                           value="<?= $ligne['quantite'] ?>" min="0"
                                           onchange="this.form.submit()">

                                    <button type="button" class="btn-qte-sm"
                                            onclick="var i=this.previousElementSibling;
                                                     i.value=+i.value+1;
                                                     this.form.submit()">+</button>
                                </div>
                            </form>
                        </td>
                        <td><?= number_format($ligne['prix'], 2, ',', ' ') ?> €</td>
                        <td class="stock-cell"><?= $ligne['stock'] ?></td>
                        <td class="total-cell">
                            <?= number_format($ligne['prix'] * $ligne['quantite'], 2, ',', ' ') ?> €
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>

            <!-- Sous-total -->
            <div class="ticket-soustotal">
                <span>Sous-total</span>
                <span><?= number_format($soustotal, 2, ',', ' ') ?> €</span>
            </div>

            <div class="ticket-total">
                <span>Total TTC</span>
                <span class="total-montant"><?= number_format($soustotal, 2, ',', ' ') ?> €</span>
            </div>

            <div class="ticket-btns">
                <?php
                // array_key_last() retourne la clé du dernier élément du tableau
                $derniere_cle = !empty($ticket) ? array_key_last($ticket) : 0;
                ?>
                <a href="caisse.php?action=suppr_ligne&id=<?= $derniere_cle ?>"
                   class="btn-suppr-ligne">Suppr. ligne</a>
                <a href="caisse.php?action=vider"
                   onclick="return confirm('Vider le ticket ?')"
                   class="btn-vider">Vider</a>
            </div>

            <form method="POST" action="caisse.php">
                <input type="hidden" name="action" value="valider">
                <button type="submit" class="btn-valider">&#10003; VALIDER</button>
            </form>

            <form method="POST" action="caisse.php">
                <input type="hidden" name="action" value="valider">
                <button type="submit" class="btn-ticket-pdf">&#128438; Générer ticket PDF</button>
            </form>

            <p class="ticket-note">&#9432; Ticket PDF généré après validation</p>

        </div>

    </div>
</div>

</body>
</html>
