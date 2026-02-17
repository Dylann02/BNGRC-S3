<?php
/** @var array $argent */
/** @var array $besoins */
/** @var array $historique */
/** @var float $frais_pourcent */
/** @var string|null $error */
/** @var string|null $success */
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achats via dons en argent</title>
    <link rel="stylesheet" href="/assets/style.css">
    <?php $page = "achats"?>
</head>
<body>
<?php include("header.php"); ?>

    <main class="content">
        <header class="top-bar">
            <h1>💰 Achats via Dons en Argent</h1>
        </header>

        <!-- MESSAGES -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- SOLDE ARGENT -->
        <section class="dispatch-controls">
            <div class="info-box">
                <p>💰 Utilisez les <strong>dons en argent</strong> pour acheter les besoins des villes sinistrées. Chaque achat <strong>déduit l'argent</strong> des dons en argent existants.</p>
            </div>
            <div class="stats-row" style="display:flex; gap:20px; margin-bottom:15px; flex-wrap:wrap;">
                <div class="stat-card">
                    <span>Argent disponible</span>
                    <strong style="color: green; font-size:1.3em;"><?= number_format($argent['argent_disponible'] ?? 0, 0, ',', ' ') ?> Ar</strong>
                </div>
                <div class="stat-card">
                    <span>Total dépensé</span>
                    <strong style="color: #f44336;"><?= number_format($argent['total_depense'] ?? 0, 0, ',', ' ') ?> Ar</strong>
                </div>
            </div>
            <a href="/achats/reset" class="btn btn-danger" onclick="return confirm('Réinitialiser tous les achats et restaurer l\'argent ?')">🔄 Réinitialiser les achats</a>
        </section>

        <!-- MAJORATION DES PRIX -->
        <section class="dispatch-controls" style="margin-top:15px;">
            <form action="/achats/frais" method="post" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <label style="font-weight:bold;">📈 Majoration des prix d'achat :</label>
                <input type="number" name="frais_pourcent" value="<?= $frais_pourcent ?>" min="0" max="100" step="0.1" style="width:80px;" required>
                <span>%</span>
                <button type="submit" class="btn btn-primary btn-sm">✅ Appliquer</button>
                <span style="color:#888; font-size:0.9em;">(Prix unit. × <?= 1 + $frais_pourcent / 100 ?>)</span>
            </form>
        </section>

        <!-- BESOINS ACHETABLES -->
        <section class="table-section">
            <h2>Besoins achetables</h2>
            <table>
                <thead>
                    <tr>
                        <th>Ville</th>
                        <th>Besoin</th>
                        <th>Type</th>
                        <th>Prix unit.</th>
                        <th>Prix majoré (+<?= $frais_pourcent ?>%)</th>
                        <th>Qté demandée</th>
                        <th>Déjà dispatché</th>
                        <th>Déjà acheté</th>
                        <th>Qté restante</th>
                        <th>Coût restant</th>
                        <th>Acheter</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($besoins)): ?>
                        <?php foreach ($besoins as $b): ?>
                            <?php 
                                $couvert = $b['quantite_restante'] <= 0;
                                $prixMajore = $b['prix_unitaire'] * (1 + $frais_pourcent / 100);
                                $coutMajore = max(0, $b['quantite_restante']) * $prixMajore;
                            ?>
                            <tr style="<?= $couvert ? 'opacity:0.5; background:#e8f5e9;' : '' ?>">
                                <td><strong><?= htmlspecialchars($b['nom_ville']) ?></strong></td>
                                <td><?= htmlspecialchars($b['nom_besoin']) ?></td>
                                <td><span class="badge badge-<?= strtolower($b['nom_type_besoin']) ?>"><?= htmlspecialchars($b['nom_type_besoin']) ?></span></td>
                                <td><?= number_format($b['prix_unitaire'], 2, ',', ' ') ?> Ar</td>
                                <td><strong style="color:#e65100;"><?= number_format($prixMajore, 2, ',', ' ') ?> Ar</strong></td>
                                <td><?= $b['quantite_demandee'] ?></td>
                                <td><?= $b['quantite_dispatchee'] ?></td>
                                <td><?= $b['quantite_achetee'] ?></td>
                                <td><strong><?= max(0, $b['quantite_restante']) ?></strong></td>
                                <td><strong><?= number_format($coutMajore, 0, ',', ' ') ?> Ar</strong></td>
                                <td>
                                    <?php if ($couvert): ?>
                                        <span style="color:green; font-weight:bold;">✅ Couvert</span>
                                    <?php else: ?>
                                        <form action="/achats/acheter" method="post" style="display:flex; gap:5px; align-items:center;">
                                            <input type="hidden" name="id_ville_besoin" value="<?= $b['id_ville_besoin'] ?>">
                                            <input type="number" name="quantite" min="1" max="<?= $b['quantite_restante'] ?>" value="<?= $b['quantite_restante'] ?>" style="width:70px;" required>
                                            <button type="submit" class="btn btn-primary btn-sm">🛒 Acheter</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" style="text-align:center;">Aucun besoin enregistré.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- HISTORIQUE DES ACHATS -->
        <section class="table-section">
            <h2>Historique des achats</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ville</th>
                        <th>Besoin</th>
                        <th>Type</th>
                        <th>Quantité</th>
                        <th>Prix unit.</th>
                        <th>Montant total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($historique)): ?>
                        <?php $totalAchats = 0; ?>
                        <?php foreach ($historique as $h): ?>
                            <tr>
                                <td><?= $h['id_achat'] ?></td>
                                <td><strong><?= htmlspecialchars($h['nom_ville']) ?></strong></td>
                                <td><?= htmlspecialchars($h['nom_besoin']) ?></td>
                                <td><span class="badge badge-<?= strtolower($h['nom_type_besoin']) ?>"><?= htmlspecialchars($h['nom_type_besoin']) ?></span></td>
                                <td><?= $h['quantite'] ?></td>
                                <td><?= number_format($h['prix_unitaire'], 2, ',', ' ') ?> Ar</td>
                                <td><strong><?= number_format($h['montant_total'], 0, ',', ' ') ?> Ar</strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($h['date_achat'])) ?></td>
                            </tr>
                            <?php $totalAchats += $h['montant_total']; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center;">Aucun achat effectué.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($historique)): ?>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="6"><strong>TOTAL ACHATS</strong></td>
                        <td><strong><?= number_format($totalAchats, 0, ',', ' ') ?> Ar</strong></td>
                        <td><strong><?= count($historique) ?> achat(s)</strong></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </section>
    </main>
</body>
</html>
