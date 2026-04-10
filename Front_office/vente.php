<?php
require 'connexion.php';
require 'header.php';

// Filtre de date
$filtre = $_GET['filtre'] ?? 'aujourd_hui';
$dd     = $_GET['dd']     ?? date('Y-m-d');
$df     = $_GET['df']     ?? date('Y-m-d');

if ($filtre == 'aujourd_hui') { $dd = date('Y-m-d'); $df = date('Y-m-d'); }
if ($filtre == '7_jours')     { $dd = date('Y-m-d', strtotime('-7 days')); $df = date('Y-m-d'); }

// Récupérer les ventes
$req = $pdo->prepare('SELECT v.*, u.Prenom, u.Nom, MIN(p.stock) AS stock_min
                      FROM vente v
                      JOIN utilisateurs u   ON u.Id = v.id_utilisateur
                      JOIN produit_vendu pv ON pv.id_vente = v.Id
                      JOIN produit p        ON p.Id = pv.id_produit
                      WHERE DATE(v.Date) BETWEEN :d1 AND :d2
                      GROUP BY v.Id, v.Date, v.Montant, v.total_produit, u.Prenom, u.Nom
                      ORDER BY v.Date DESC');
$req->execute([':d1'=>$dd, ':d2'=>$df]);
$ventes = $req->fetchAll();

// Total période
$total = 0;
foreach ($ventes as $v) $total += $v['Montant'];

// Détail d'une vente
$detail = null; $lignes = [];
if (isset($_GET['detail'])) {
    $req = $pdo->prepare('SELECT v.*, u.Prenom FROM vente v JOIN utilisateurs u ON u.Id=v.id_utilisateur WHERE v.Id=:id');
    $req->execute([':id'=>$_GET['detail']]);
    $detail = $req->fetch();
    $req = $pdo->prepare('SELECT pv.*, p.Nom_produit FROM produit_vendu pv JOIN produit p ON p.Id=pv.id_produit WHERE pv.id_vente=:id');
    $req->execute([':id'=>$_GET['detail']]);
    $lignes = $req->fetchAll();
}
?>

<div class="contenu">

    <!-- Total période — grande carte blanche -->
    <div class="total-periode-carte">
        <p class="total-periode-label">Total période</p>
        <p class="total-periode-valeur"><?= number_format($total, 2, ',', ' ') ?> €</p>
    </div>

    <!-- Barre de filtres -->
    <div class="filtres-barre">
        <div class="filtre-dropdown"> Aujourd'hui ▾</div>

        <a href="vente.php?filtre=aujourd_hui&dd=<?=$dd?>&df=<?=$df?>"
           class="<?= $filtre=='aujourd_hui'?'btn-filtre-actif':'btn-filtre-inactif'?>">
            Aujourd'hui
        </a>
        <a href="vente.php?filtre=7_jours&dd=<?=$dd?>&df=<?=$df?>"
           class="<?= $filtre=='7_jours'?'btn-filtre-actif':'btn-filtre-inactif'?>">
            7 jours
        </a>
        <a href="vente.php?filtre=perso&dd=<?=$dd?>&df=<?=$df?>"
           class="<?= $filtre=='perso'?'btn-filtre-actif':'btn-filtre-inactif'?>">
            Personnalisé
        </a>

        <!-- Dates personnalisées -->
        <div class="filtre-date-input">
            <input type="date" value="<?= $dd ?>" id="date-debut"> 
        </div>
        <span style="color:#6B7280;font-size:.85rem">au</span>
        <div class="filtre-date-input">
            <input type="date" value="<?= $df ?>" id="date-fin"> 
        </div>

        <button class="btn-filtrer"
                onclick="window.location.href='vente.php?filtre=perso&dd='+document.getElementById('date-debut').value+'&df='+document.getElementById('date-fin').value">
             Filtrer
        </button>
    </div>

    <!-- Tableau historique des ventes -->
    <div class="historique-carte">

        <div class="historique-entete">
            <div>
                <p class="historique-titre">Historique des ventes</p>
            </div>
            <div class="historique-btns">
                <a href="#" class="btn-export-bleu">📄 Export PDF</a>
            </div>
        </div>
        <p class="historique-sous">Statut du stock mis à jour après chaque vente</p>

        <table class="ventes-table">
            <thead>
                <tr>
                    <th>DATE / HEURE</th>
                    <th>MONTANT</th>
                    <th>UTILISATEUR</th>
                    <th>ARTICLES</th>
                    <th>STATUT STOCK</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($ventes)): ?>
                <tr><td colspan="6" style="text-align:center;padding:2rem;color:#9CA3AF">Aucune vente.</td></tr>
            <?php else: ?>
                <?php foreach ($ventes as $v):
                    $sm = (int)$v['stock_min'];
                    if ($sm == 0)      { $badge = 'badge-rouge-plein'; $txt = 'Rupture proche'; }
                    elseif ($sm <= 5)  { $badge = 'badge-orange-plein'; $txt = 'Stock faible'; }
                    else               { $badge = 'badge-vert-plein'; $txt = 'Stock suffisant'; }
                ?>
                <tr>
                    <td><?= date('d/m/Y - H:i', strtotime($v['Date'])) ?></td>
                    <td><strong><?= number_format($v['Montant'], 2, ',', ' ') ?> €</strong></td>
                    <td>
                        <span class="user-avatar"><?= strtoupper(substr($v['Prenom'],0,1)) ?></span>
                        <?= $v['Prenom'] ?>
                    </td>
                    <td><span class="badge-articles"><?= $v['total_produit'] ?> articles</span></td>
                    <td><span class="badge-statut <?= $badge ?>"><?= $txt ?></span></td>
                    <td>
                        <a href="vente.php?detail=<?= $v['Id'] ?>&filtre=<?=$filtre?>&dd=<?=$dd?>&df=<?=$df?>"
                           class="lien-detail">Détail</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Détail d'une vente -->
    <?php if ($detail): ?>
    <div class="historique-carte" style="margin-top:1rem">
        <div class="historique-entete">
            <p class="historique-titre">Détail — vente du <?= date('d/m/Y H:i', strtotime($detail['Date'])) ?> (<?= $detail['Prenom'] ?>)</p>
            <a href="vente.php?filtre=<?=$filtre?>&dd=<?=$dd?>&df=<?=$df?>" class="btn-filtre-inactif">Fermer</a>
        </div>
        <table class="ventes-table">
            <thead><tr><th>PRODUIT</th><th>QUANTITÉ</th><th>TOTAL</th></tr></thead>
            <tbody>
            <?php foreach ($lignes as $l): ?>
            <tr>
                <td><?= $l['Nom_produit'] ?></td>
                <td><?= $l['quantite'] ?></td>
                <td><strong><?= number_format($l['prix_total'],2,',',' ') ?> €</strong></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div style="text-align:right;padding:.8rem 1rem;font-weight:700">
            Total : <?= number_format($detail['Montant'],2,',',' ') ?> €
        </div>
    </div>
    <?php endif; ?>

</div>

</body>
</html>