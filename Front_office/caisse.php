<?php
require 'connexion.php';
require 'header.php';

// On initialise le ticket s'il n'existe pas encore
if (!isset($_SESSION['ticket'])) {
    $_SESSION['ticket'] = [];
}

$message        = '';
$dernier_produit = null; // Le dernier article scanné/ajouté (affiché à droite)

// --- Ajouter un produit via scan ou recherche ---
if (isset($_POST['ajouter_id'])) {
    $id = (int)$_POST['ajouter_id'];

    $req = $pdo->prepare('SELECT * FROM produit WHERE Id = :id');
    $req->execute([':id' => $id]);
    $p = $req->fetch();

    if ($p) {
        if (isset($_SESSION['ticket'][$id])) {
            $_SESSION['ticket'][$id]['quantite'] += 1;
        } else {
            $_SESSION['ticket'][$id] = [
                'Id'          => $p['Id'],
                'Nom_produit' => $p['Nom_produit'],
                'prix'        => $p['prix'],
                'quantite'    => 1,
                'stock'       => $p['stock'],
                'code_barre'  => $p['code_barre'],
            ];
        }
        $dernier_produit = $p;
        $_SESSION['dernier_produit'] = $p; // on garde en session pour réafficher
    }
}

// Modifier la quantité d'une ligne du ticket
if (isset($_POST['maj_qte'])) {
    $id  = (int)$_POST['id'];
    $qte = (int)$_POST['quantite'];
    if ($qte == 0) {
        unset($_SESSION['ticket'][$id]);
    } else {
        $_SESSION['ticket'][$id]['quantite'] = $qte;
    }
}

// Supprimer une ligne du ticket
if (isset($_GET['supprimer'])) {
    unset($_SESSION['ticket'][(int)$_GET['supprimer']]);
}

// Vider tout le ticket
if (isset($_GET['vider'])) {
    $_SESSION['ticket']          = [];
    $_SESSION['dernier_produit'] = null;
}

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
        $req->execute([':m' => $montant, ':u' => $_SESSION['user']['Id'], ':t' => $total_articles]);
        $id_vente = $pdo->lastInsertId();

        foreach ($_SESSION['ticket'] as $l) {
            $req = $pdo->prepare('INSERT INTO produit_vendu (prix_total, quantite, id_vente, id_produit) VALUES (:pt, :q, :v, :p)');
            $req->execute([':pt' => $l['prix'] * $l['quantite'], ':q' => $l['quantite'], ':v' => $id_vente, ':p' => $l['Id']]);

            $req = $pdo->prepare('UPDATE produit SET stock = stock - :q WHERE Id = :id');
            $req->execute([':q' => $l['quantite'], ':id' => $l['Id']]);
        }

        $_SESSION['ticket']          = [];
        $_SESSION['dernier_produit'] = null;
        $message = 'Vente enregistrée ! Total : ' . number_format($montant, 2, ',', ' ') . ' €';
    }
}

// Récupérer le dernier produit scanné depuis la session
if (!$dernier_produit && !empty($_SESSION['dernier_produit'])) {
    $dernier_produit = $_SESSION['dernier_produit'];
}

// Calcul du total du ticket
$total = 0;
foreach ($_SESSION['ticket'] as $l) {
    $total += $l['prix'] * $l['quantite'];
}

// Nombre total d'articles dans le ticket
$nb_articles = 0;
foreach ($_SESSION['ticket'] as $l) {
    $nb_articles += $l['quantite'];
}

// Sous-total HT (on suppose TVA 20%)
$total_ht = $total / 1.20;
$tva      = $total - $total_ht;
?>

<div class="contenu">

    <?php if ($message): ?>
        <div class="msg-succes"><?= $message ?></div>
    <?php endif; ?>

    <!-- Layout : ticket à gauche, panneau scan à droite -->
    <div style="display:grid; grid-template-columns: 1fr 380px; gap:1.2rem; align-items:start;">

        <!-- ===== PARTIE GAUCHE : ticket ===== -->
        <div class="ticket-carte">

            <!-- En-tête ticket -->
            <div style="display:flex; justify-content:space-between; align-items:center; padding:1rem 1.2rem; border-bottom:1px solid #F3F4F6;">
                <span style="font-size:1rem; font-weight:700;">Ticket en cours</span>
                <?php if ($nb_articles > 0): ?>
                    <span style="background:#F3F4F6; padding:.3rem .8rem; border-radius:999px; font-size:.82rem; color:#374151;">
                        <?= $nb_articles ?> article<?= $nb_articles > 1 ? 's' : '' ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Tableau des lignes du ticket -->
            <table style="width:100%; border-collapse:collapse; font-size:.85rem;">
                <thead>
                    <tr style="border-bottom:1px solid #F3F4F6;">
                        <th style="padding:.6rem 1rem; text-align:left; color:#9CA3AF; font-size:.73rem; font-weight:600; text-transform:uppercase;">Désignation</th>
                        <th style="padding:.6rem .5rem; text-align:center; color:#9CA3AF; font-size:.73rem; font-weight:600; text-transform:uppercase;">Qté</th>
                        <th style="padding:.6rem .5rem; text-align:right; color:#9CA3AF; font-size:.73rem; font-weight:600; text-transform:uppercase;">PU TTC</th>
                        <th style="padding:.6rem .5rem; text-align:right; color:#9CA3AF; font-size:.73rem; font-weight:600; text-transform:uppercase;">Total TTC</th>
                        <th style="padding:.6rem .5rem; text-align:center; color:#9CA3AF; font-size:.73rem; font-weight:600; text-transform:uppercase;">Stock R.</th>
                        <th style="padding:.6rem .5rem;"></th>
                    </tr>
                </thead>
                <tbody>

                <?php if (empty($_SESSION['ticket'])): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:3rem; color:#9CA3AF;">
                            Ticket vide — scannez ou recherchez un produit
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($_SESSION['ticket'] as $l):
                        // Couleur du badge stock
                        if ($l['stock'] <= 0)      { $stk_style = 'background:#FEE2E2;color:#991B1B;'; }
                        elseif ($l['stock'] <= 5)  { $stk_style = 'background:#FEF9C3;color:#854D0E;'; }
                        else                        { $stk_style = 'background:#F3F4F6;color:#374151;'; }
                    ?>
                    <tr style="border-bottom:1px solid #F9FAFB;">

                        <!-- Nom + référence -->
                        <td style="padding:.8rem 1rem;">
                            <div style="font-weight:600; color:#1A1A2E;"><?= $l['Nom_produit'] ?></div>
                            <div style="font-size:.72rem; color:#9CA3AF;">Réf: <?= $l['code_barre'] ?></div>
                        </td>

                        <!-- Contrôle quantité − N + -->
                        <td style="padding:.8rem .5rem; text-align:center; white-space:nowrap;">
                            <form method="POST" style="display:inline-flex; align-items:center; gap:.3rem;">
                                <input type="hidden" name="id" value="<?= $l['Id'] ?>">
                                <input type="hidden" name="maj_qte" value="1">
                                <button type="submit" name="quantite" value="<?= $l['quantite'] - 1 ?>"
                                    style="width:26px;height:26px;border:1px solid #E5E7EB;border-radius:6px;background:white;cursor:pointer;font-size:.9rem;">−</button>
                                <span style="font-weight:600; min-width:20px; text-align:center;"><?= $l['quantite'] ?></span>
                                <button type="submit" name="quantite" value="<?= $l['quantite'] + 1 ?>"
                                    style="width:26px;height:26px;border:1px solid #E5E7EB;border-radius:6px;background:white;cursor:pointer;font-size:.9rem;">+</button>
                            </form>
                        </td>

                        <!-- Prix unitaire -->
                        <td style="padding:.8rem .5rem; text-align:right; color:#374151;">
                            <?= number_format($l['prix'], 2, ',', ' ') ?> €
                        </td>

                        <!-- Total ligne -->
                        <td style="padding:.8rem .5rem; text-align:right; font-weight:700; color:#1A1A2E;">
                            <?= number_format($l['prix'] * $l['quantite'], 2, ',', ' ') ?> €
                        </td>

                        <!-- Badge stock -->
                        <td style="padding:.8rem .5rem; text-align:center;">
                            <span style="<?= $stk_style ?> padding:.2rem .6rem; border-radius:999px; font-size:.75rem; font-weight:600;">
                                <?= $l['stock'] ?> unités
                            </span>
                        </td>

                        <!-- Bouton supprimer -->
                        <td style="padding:.8rem .5rem; text-align:center;">
                            <a href="caisse.php?supprimer=<?= $l['Id'] ?>"
                               style="color:#9CA3AF; text-decoration:none; font-size:1rem; font-weight:600;">×</a>
                        </td>

                    </tr>
                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>
            </table>

            <!-- Pied de ticket : totaux + boutons -->
            <div style="padding:1rem 1.2rem; border-top:1px solid #F3F4F6;">

                <!-- Sous-total HT et TVA -->
                <div style="display:flex; justify-content:space-between; font-size:.85rem; color:#6B7280; margin-bottom:.3rem;">
                    <span>Sous-total HT</span>
                    <span><?= number_format($total_ht, 2, ',', ' ') ?> €</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:.85rem; color:#6B7280; margin-bottom:.8rem;">
                    <span>TVA (20%)</span>
                    <span><?= number_format($tva, 2, ',', ' ') ?> €</span>
                </div>

                <!-- Total TTC -->
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem;">
                    <span style="font-size:1rem; font-weight:700;">Total TTC</span>
                    <span style="font-size:2rem; font-weight:800; color:#1565C0;">
                        <?= number_format($total, 2, ',', ' ') ?> €
                    </span>
                </div>

                <!-- Boutons du bas -->
                <div style="display:flex; gap:.6rem; align-items:center;">

                    <!-- Vider le ticket -->
                    <a href="caisse.php?vider=1"
                       onclick="return confirm('Vider le ticket ?')"
                       style="display:flex;align-items:center;gap:.4rem;padding:.55rem 1rem;border:1px solid #FECACA;border-radius:8px;background:white;color:#DC2626;font-size:.83rem;font-weight:600;text-decoration:none;">
                        🗑 Vider le ticket
                    </a>

                    <!-- Ticket PDF (placeholder) -->
                    <button style="display:flex;align-items:center;gap:.4rem;padding:.55rem 1rem;border:1px solid #E5E7EB;border-radius:8px;background:white;color:#374151;font-size:.83rem;font-weight:600;cursor:pointer;font-family:inherit;">
                        📄 Ticket PDF
                    </button>

                    <!-- Valider -->
                    <form method="POST" style="flex:1;">
                        <button type="submit" name="valider"
                            style="width:100%;padding:.65rem;background:#16A34A;border:none;border-radius:8px;color:white;font-size:.9rem;font-weight:700;cursor:pointer;font-family:inherit;display:flex;align-items:center;justify-content:center;gap:.5rem;">
                            ✓ VALIDER L'ENCAISSEMENT
                        </button>
                    </form>

                </div>
            </div>

        </div><!-- fin ticket-carte -->


        <!-- ===== PARTIE DROITE : scan + dernier produit ===== -->
        <div style="display:flex; flex-direction:column; gap:1rem;">

            <!-- Zone scan code-barres -->
            <form method="POST" action="caisse.php" id="form-scan">
                <div style="border:2px dashed #93C5FD; border-radius:12px; padding:1.5rem; background:white; text-align:center; cursor:pointer;"
                     onclick="document.getElementById('input-scan').focus()">

                    <div style="font-size:2rem; margin-bottom:.5rem;">||||</div>
                    <div style="color:#1565C0; font-weight:700; font-size:1rem; letter-spacing:.05em; margin-bottom:.3rem;">
                        SCAN CODE-BARRES
                    </div>
                    <div style="color:#9CA3AF; font-size:.8rem; margin-bottom:1rem;">
                        Lecteur Code128 actif • Prêt à scanner
                    </div>

                    <!-- Champ de saisie visible pour recherche manuelle -->
                    <input type="text" id="input-scan" name="scan_code"
                           placeholder="Scanner ou taper le code / nom..."
                           style="width:100%; padding:.6rem .9rem; border:1px solid #E5E7EB; border-radius:8px; font-size:.88rem; font-family:inherit; outline:none; text-align:center;"
                           autofocus>

                </div>
                <!-- Ce bouton déclenche la recherche -->
                <button type="submit" style="display:none;">OK</button>
            </form>

            <!-- Traitement du scan -->
            <?php
            if (isset($_POST['scan_code']) && $_POST['scan_code'] != '') {
                $code = trim($_POST['scan_code']);
                $req  = $pdo->prepare('SELECT * FROM produit WHERE code_barre = :c OR Nom_produit LIKE :n LIMIT 1');
                $req->execute([':c' => $code, ':n' => '%' . $code . '%']);
                $trouve = $req->fetch();
                if ($trouve) {
                    // On l'ajoute au ticket
                    $id = $trouve['Id'];
                    if (isset($_SESSION['ticket'][$id])) {
                        $_SESSION['ticket'][$id]['quantite'] += 1;
                    } else {
                        $_SESSION['ticket'][$id] = [
                            'Id'          => $trouve['Id'],
                            'Nom_produit' => $trouve['Nom_produit'],
                            'prix'        => $trouve['prix'],
                            'quantite'    => 1,
                            'stock'       => $trouve['stock'],
                            'code_barre'  => $trouve['code_barre'],
                        ];
                    }
                    $_SESSION['dernier_produit'] = $trouve;
                    $dernier_produit = $trouve;
                    // Recalcul total
                    $total = 0;
                    foreach ($_SESSION['ticket'] as $l) $total += $l['prix'] * $l['quantite'];
                    $total_ht = $total / 1.20;
                    $tva      = $total - $total_ht;
                    echo "<script>window.location.href='caisse.php';</script>";
                }
            }
            ?>

            <!-- Dernier article scanné -->
            <div style="background:white; border:1px solid #E5E7EB; border-radius:12px; padding:1.2rem;">

                <div style="font-size:.72rem; font-weight:600; color:#9CA3AF; letter-spacing:.08em; text-transform:uppercase; margin-bottom:1rem;">
                    Dernier article scanné
                </div>

                <?php if ($dernier_produit): ?>

                    <!-- Image placeholder -->
                    <div style="width:100%; height:160px; background:#F9FAFB; border:1px solid #E5E7EB; border-radius:10px; display:flex; align-items:center; justify-content:center; margin-bottom:1rem; color:#9CA3AF; font-size:.8rem;">
                        Pas d'image
                    </div>

                    <!-- Nom du produit -->
                    <div style="font-size:1.1rem; font-weight:700; color:#1A1A2E; text-align:center; margin-bottom:.4rem;">
                        <?= $dernier_produit['Nom_produit'] ?>
                    </div>

                    <!-- Prix -->
                    <div style="font-size:1.6rem; font-weight:800; color:#1565C0; text-align:center; margin-bottom:.8rem;">
                        <?= number_format($dernier_produit['prix'], 2, ',', ' ') ?> €
                    </div>

                    <!-- Référence + stock -->
                    <div style="display:flex; gap:.5rem; justify-content:center; margin-bottom:1rem;">
                        <span style="background:#F3F4F6; padding:.25rem .7rem; border-radius:6px; font-size:.75rem; color:#374151;">
                            Réf: <?= $dernier_produit['code_barre'] ?>
                        </span>
                        <span style="background:#F3F4F6; padding:.25rem .7rem; border-radius:6px; font-size:.75rem; color:#374151;">
                            Stock: <?= $dernier_produit['stock'] ?>
                        </span>
                    </div>

                    <!-- Contrôle quantité pour ce produit -->
                    <form method="POST" style="display:flex; align-items:center; justify-content:center; gap:.8rem;">
                        <input type="hidden" name="id" value="<?= $dernier_produit['Id'] ?>">
                        <input type="hidden" name="maj_qte" value="1">
                        <?php $qte_actuelle = $_SESSION['ticket'][$dernier_produit['Id']]['quantite'] ?? 1; ?>
                        <button type="submit" name="quantite" value="<?= $qte_actuelle - 1 ?>"
                            style="width:36px;height:36px;border:1px solid #E5E7EB;border-radius:8px;background:white;cursor:pointer;font-size:1.1rem;">−</button>
                        <span style="font-size:1.1rem; font-weight:600; min-width:30px; text-align:center;">
                            <?= $qte_actuelle ?>
                        </span>
                        <button type="submit" name="quantite" value="<?= $qte_actuelle + 1 ?>"
                            style="width:36px;height:36px;border:1px solid #E5E7EB;border-radius:8px;background:white;cursor:pointer;font-size:1.1rem;">+</button>
                    </form>

                <?php else: ?>
                    <div style="text-align:center; padding:2rem; color:#9CA3AF; font-size:.85rem;">
                        Aucun article scanné
                    </div>
                <?php endif; ?>

            </div>

        </div><!-- fin partie droite -->

    </div><!-- fin grid -->

</div><!-- fin contenu -->

</body>
</html>