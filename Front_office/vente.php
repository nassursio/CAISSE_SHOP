<?php
require 'connexion.php';
require 'header.php';

// Filtre de date
$filtre = $_GET['filtre'] ?? '7_jours';

if ($filtre == '7_jours') {
    $dd = date('Y-m-d', strtotime('-7 days'));
    $df = date('Y-m-d');
}
if ($filtre == 'perso') {
    $dd = $_GET['dd'] ?? date('Y-m-d');
    $df = $_GET['df'] ?? date('Y-m-d');
}

// Récupérer les ventes
$req = $pdo->prepare('
    SELECT v.*, u.Prenom, u.Nom, MIN(p.stock) AS stock_min
    FROM vente v
    JOIN utilisateurs u   ON u.Id = v.id_utilisateur
    JOIN produit_vendu pv ON pv.id_vente = v.Id
    JOIN produit p        ON p.Id = pv.id_produit
    WHERE DATE(v.Date) BETWEEN :d1 AND :d2
    GROUP BY v.Id
    ORDER BY v.Date DESC
');
$req->execute([':d1' => $dd, ':d2' => $df]);
$ventes = $req->fetchAll();

// Total de la période
$total = 0;
foreach ($ventes as $v) $total += $v['Montant'];
?>

<div class="contenu">

    <!-- Total période -->
    <div class="total-periode-carte">
        <p class="total-periode-label">Total période</p>
        <p class="total-periode-valeur"><?= number_format($total, 2, ',', ' ') ?> €</p>
    </div>

    <!-- Filtres -->
    <div class="filtres-barre">
        <a href="vente.php?filtre=7_jours"     class="<?= $filtre=='7_jours'     ? 'btn-filtre-actif' : 'btn-filtre-inactif' ?>">7 jours</a>

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

    <!-- Tableau -->
    <div class="historique-carte">
        <div class="historique-entete">
            <div>
                <p class="historique-titre">Historique des ventes</p>
                <p class="historique-sous">Statut du stock mis à jour après chaque vente</p>
            </div>
            <div class="historique-btns">
                <a href="#" class="btn-export-bleu">📄 Export PDF</a>
            </div>
        </div>

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
                    if ($sm == 0)      { $badge = 'badge-rouge-plein';  $txt = 'Rupture proche'; }
                    elseif ($sm <= 10) { $badge = 'badge-orange-plein'; $txt = 'Stock faible'; }
                    else               { $badge = 'badge-vert-plein';   $txt = 'Stock suffisant'; }
                ?>
                <tr>
                    <td><?= date('d/m/Y - H:i', strtotime($v['Date'])) ?></td>
                    <td><strong><?= number_format($v['Montant'], 2, ',', ' ') ?> €</strong></td>
                    <td>
                        <span class="user-avatar"><?= strtoupper(substr($v['Prenom'], 0, 1)) ?></span>
                        <?= $v['Prenom'] ?>
                    </td>
                    <td><span class="badge-articles"><?= $v['total_produit'] ?> articles</span></td>
                    <td><span class="badge-statut <?= $badge ?>"><?= $txt ?></span></td>
                    <td><a href="detail_vente.php?id=<?= $v['Id'] ?>" class="lien-detail">Détail</a></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>