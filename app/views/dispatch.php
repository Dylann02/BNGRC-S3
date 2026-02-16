<?php
/** @var array $historique */
/** @var array $resume */
/** @var array $total */
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNGRC - Dispatch des dons</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<?php include("header.php");?>

    <main class="content">
        <header class="top-bar">
            <h1>Dispatch des Dons</h1>
        </header>

        <!-- CONTROLES -->
        <section class="dispatch-controls">
            <div class="info-box">
                <p>⚙️ Le dispatch attribue automatiquement les dons aux villes par <strong>ordre de date de saisie</strong> du don, en respectant la correspondance des types et désignations de besoins.</p>
            </div>
            <a href="/dispatch/lancer" class="btn btn-success btn-large">🚀 Lancer le dispatch automatique</a>
            <a href="/dispatch/reset" class="btn btn-danger">🔄 Réinitialiser le dispatch</a>
        </section>

        <!-- HISTORIQUE DES ATTRIBUTIONS -->
        <section class="table-section">
            <h2>Historique des attributions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Don ID</th>
                        <th>Date don</th>
                        <th>Donateur</th>
                        <th>Désignation</th>
                        <th>Qté attribuée</th>
                        <th>Valeur (Ar)</th>
                        <th>→ Ville</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($historique)): ?>
                        <?php foreach ($historique as $h): ?>
                            <tr>
                                <td><?= htmlspecialchars($h['id_don']) ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($h['date_don']))) ?></td>
                                <td><?= htmlspecialchars($h['donateur'] ?? 'Anonyme') ?></td>
                                <td><?= htmlspecialchars($h['nom_besoin']) ?> <span class="badge badge-<?= strtolower($h['nom_type_besoin']) ?>"><?= htmlspecialchars($h['nom_type_besoin']) ?></span></td>
                                <td><?= number_format($h['quantite_attribuee'], 0, ',', ' ') ?></td>
                                <td><strong><?= number_format($h['valeur'], 0, ',', ' ') ?> Ar</strong></td>
                                <td><strong><?= htmlspecialchars($h['nom_ville']) ?></strong></td>
                                <td>✅ Attribué</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center;">Aucune attribution pour le moment. Cliquez sur "Lancer le dispatch automatique".</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="5"><strong>TOTAL DISPATCHÉ</strong></td>
                        <td><strong><?= number_format($total['total_valeur'] ?? 0, 0, ',', ' ') ?> Ar</strong></td>
                        <td colspan="2"><strong><?= $total['nb_attributions'] ?? 0 ?> attribution(s)</strong></td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <!-- RESUME PAR VILLE -->
        <section class="table-section">
            <h2>Résumé des attributions par ville</h2>
            <table>
                <thead>
                    <tr>
                        <th>Ville</th>
                        <th>Nb attributions</th>
                        <th>Total reçu (Ar)</th>
                        <th>Total besoins (Ar)</th>
                        <th>Couverture</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($resume)): ?>
                        <?php foreach ($resume as $r): ?>
                            <?php 
                                $totalBesoins = $r['total_besoins'] ?? 0;
                                $totalRecu = $r['total_recu'] ?? 0;
                                $couverture = $totalBesoins > 0 ? round(($totalRecu / $totalBesoins) * 100) : 0;
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['nom_ville']) ?></strong></td>
                                <td><?= $r['nb_attributions'] ?></td>
                                <td><?= number_format($totalRecu, 0, ',', ' ') ?> Ar</td>
                                <td><?= number_format($totalBesoins, 0, ',', ' ') ?> Ar</td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= $couverture ?>%;"><?= $couverture ?>%</div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">Aucune ville enregistrée.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>