<?php
/** @var array $argent */
/** @var array $besoins */
/** @var array $historique */
/** @var array $villes */
/** @var int|null $filtre_ville */
/** @var string|null $error */
/** @var string|null $success */
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNGRC - Achats via dons en argent</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <nav class="sidebar">
        <div class="sidebar-header">
            <h2>🏛️ BNGRC</h2>
            <p>Suivi des dons</p>
        </div>
        <ul class="nav-links">
            <li><a href="home">📊 Tableau de bord</a></li>
            <li><a href="villes">🏘️ Villes & Régions</a></li>
            <li><a href="besoins">📋 Besoins des sinistrés</a></li>
            <li><a href="dons">🎁 Saisie des dons</a></li>
            <li><a href="dispatch">🚚 Dispatch des dons</a></li>
            <li><a href="achats" class="active">💰 Achats (argent)</a></li>
            <li><a href="simulation">🔬 Simulation</a></li>
            <li><a href="recap">📊 Récapitulation</a></li>
        </ul>
    </nav>

    <main class="content">
        <header class="top-bar">
            <h1>Achats via Dons en Argent</h1>
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
                <p>💰 Achetez des besoins <strong>Nature</strong> et <strong>Matériaux</strong> avec les dons en argent. Frais d'achat : <strong><?= $argent['frais_pourcent'] ?>%</strong>
                (ex: achat de 100 Ar → coût réel <?= 100 + 100 * $argent['frais_pourcent'] / 100 ?> Ar).</p>
            </div>
            <div class="stats-row" style="display:flex; gap:20px; margin-bottom:15px; flex-wrap:wrap;">
                <div class="stat-card">
                    <span>Total dons en argent</span>
                    <strong><?= number_format($argent['total_argent_dons'] ?? 0, 0, ',', ' ') ?> Ar</strong>
                </div>
                <div class="stat-card">
                    <span>Déjà dépensé (+ frais)</span>
                    <strong><?= number_format($argent['total_depense'] ?? 0, 0, ',', ' ') ?> Ar</strong>
                </div>
                <div class="stat-card">
                    <span>Argent disponible</span>
                    <strong style="color: green;"><?= number_format($argent['argent_disponible'] ?? 0, 0, ',', ' ') ?> Ar</strong>
                </div>
            </div>
            <a href="/achats/reset" class="btn btn-danger">🔄 Réinitialiser les achats</a>
        </section>

        <!-- FILTRE PAR VILLE -->
        <section class="form-section" style="margin-top:15px;">
            <form action="/achats" method="get" style="display:flex; gap:10px; align-items:center;">
                <label for="ville">Filtrer par ville :</label>
                <select name="ville" id="ville">
                    <option value="">-- Toutes les villes --</option>
                    <?php foreach ($villes as $v): ?>
                        <option value="<?= $v['id_ville'] ?>" <?= ($filtre_ville == $v['id_ville']) ? 'selected' : '' ?>><?= htmlspecialchars($v['nom_ville']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
            </form>
        </section>

        <!-- BESOINS RESTANTS -->
        <section class="table-section">
            <h2>Besoins restants achetables</h2>
            <table>
                <thead>
                    <tr>
                        <th>Ville</th>
                        <th>Besoin</th>
                        <th>Type</th>
                        <th>Prix unit.</th>
                        <th>Qté demandée</th>
                        <th>Qté satisfaite</th>
                        <th>Qté manquante</th>
                        <th>Coût base</th>
                        <th>Coût + frais (<?= $argent['frais_pourcent'] ?>%)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($besoins)): ?>
                        <?php foreach ($besoins as $b): ?>
                            <?php $coutAvecFrais = $b['cout_achat'] * (1 + $argent['frais_pourcent'] / 100); ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($b['nom_ville']) ?></strong></td>
                                <td><?= htmlspecialchars($b['nom_besoin']) ?></td>
                                <td><span class="badge badge-<?= strtolower($b['nom_type_besoin']) ?>"><?= htmlspecialchars($b['nom_type_besoin']) ?></span></td>
                                <td><?= number_format($b['prix_unitaire'], 2, ',', ' ') ?> Ar</td>
                                <td><?= $b['quantite_demandee'] ?></td>
                                <td><?= $b['quantite_satisfaite'] ?></td>
                                <td><strong><?= $b['quantite_manquante'] ?></strong></td>
                                <td><?= number_format($b['cout_achat'], 0, ',', ' ') ?> Ar</td>
                                <td><strong><?= number_format($coutAvecFrais, 0, ',', ' ') ?> Ar</strong></td>
                                <td>
                                    <form action="/achats/acheter" method="post" style="display:flex; gap:5px; align-items:center;">
                                        <input type="hidden" name="id_ville_besoin" value="<?= $b['id_ville_besoin'] ?>">
                                        <input type="number" name="quantite" min="1" max="<?= $b['quantite_manquante'] ?>" value="<?= $b['quantite_manquante'] ?>" style="width:70px;" required>
                                        <button type="submit" class="btn btn-primary btn-sm">🛒 Acheter</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align:center;">Tous les besoins sont satisfaits ou aucun besoin enregistré.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- HISTORIQUE DES ACHATS VALIDES -->
        <section class="table-section">
            <h2>Historique des achats validés</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ville</th>
                        <th>Besoin</th>
                        <th>Type</th>
                        <th>Quantité</th>
                        <th>Prix unit.</th>
                        <th>Montant base</th>
                        <th>Frais (%)</th>
                        <th>Frais (Ar)</th>
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
                                <td><?= number_format($h['montant_base'], 0, ',', ' ') ?> Ar</td>
                                <td><?= $h['frais_pourcent'] ?>%</td>
                                <td><?= number_format($h['montant_frais'], 0, ',', ' ') ?> Ar</td>
                                <td><strong><?= number_format($h['montant_total'], 0, ',', ' ') ?> Ar</strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($h['date_achat'])) ?></td>
                            </tr>
                            <?php $totalAchats += $h['montant_total']; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" style="text-align:center;">Aucun achat effectué.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($historique)): ?>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="9"><strong>TOTAL ACHATS</strong></td>
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
