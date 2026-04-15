<?php
require 'connexion.php';
require 'header.php';

// On récupère les dates depuis l'URL, par défaut c'est aujourd'hui
$filtre = $_GET['filtre'] ?? 'aujourd_hui';
$dd = $_GET['dd'] ?? date('Y-m-d');
$df = $_GET['df'] ?? date('Y-m-d');

if ($filtre == '7_jours')     { $dd = date('Y-m-d', strtotime('-7 days')); $df = date('Y-m-d'); }
// On récupère toutes les ventes entre les deux dates
$req = $pdo->prepare('
    SELECT v.*, u.Prenom, u.Nom
    FROM vente v
    JOIN utilisateurs u ON u.Id = v.id_utilisateur
    WHERE DATE(v.Date) BETWEEN :d1 AND :d2
    ORDER BY v.Date DESC
');
$req->execute([':d1' => $dd, ':d2' => $df]);
$ventes = $req->fetchAll();

// On calcule le total de toutes les ventes affichées
$total = 0;
foreach ($ventes as $v) {
    $total += $v['Montant'];
}
?>

<div class="contenu">

    <!-- Total de la période -->
    <div class="total-periode-carte">
        <p class="total-periode-label">Total période</p>
        <p class="total-periode-valeur"><?= number_format($total, 2, ',', ' ') ?> €</p>
    </div>

    <!-- Filtre par date -->
    <div class="filtres-barre">
        <span style="font-size:.85rem;color:#6B7280">Du</span>
        <div class="filtre-date-input">
            <input type="date" value="<?= $dd ?>" id="date-debut">
        </div>
        <span style="font-size:.85rem;color:#6B7280">au</span>
        <div class="filtre-date-input">
            <input type="date" value="<?= $df ?>" id="date-fin">
        </div>
        <button class="btn-filtrer" onclick="
            window.location.href = 'vente.php?dd=' + document.getElementById('date-debut').value
                                             + '&df=' + document.getElementById('date-fin').value
        ">
            Filtrer
        </button>
    </div>

    <!-- Tableau des ventes -->
    <div class="historique-carte">

        <div class="historique-entete">
            <p class="historique-titre">Historique des ventes</p>
        </div>

        <table class="ventes-table">
            <thead>
                <tr>
                    <th>DATE / HEURE</th>
                    <th>MONTANT</th>
                    <th>CAISSIER</th>
                    <th>ARTICLES</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>

            <?php if (empty($ventes)): ?>
                <tr>
                    <td colspan="5" style="text-align:center;padding:2rem;color:#9CA3AF">
                        Aucune vente sur cette période.
                    </td>
                </tr>

            <?php else: ?>
                <?php foreach ($ventes as $v): ?>
                <tr>
                    <td><?= date('d/m/Y - H:i', strtotime($v['Date'])) ?></td>
                    <td><strong><?= number_format($v['Montant'], 2, ',', ' ') ?> €</strong></td>
                    <td><?= $v['Prenom'] ?> <?= $v['Nom'] ?></td>
                    <td><?= $v['total_produit'] ?> articles</td>
                    <td>
                        <a href="detail_vente.php?id=<?= $v['Id'] ?>" class="action-edit">Détail</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            </tbody>
        </table>

    </div>

</div>

</body>
</html>