<?php
/** @var array $argent */
/** @var array $besoins */
/** @var array $simulations */
/** @var array $villes */
/** @var int|null $filtre_ville */
/** @var string|null $error */
/** @var string|null $success */
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNGRC - Simulation d'achats</title>
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
            <li><a href="achats">💰 Achats (argent)</a></li>
            <li><a href="simulation" class="active">🔬 Simulation</a></li>
            <li><a href="recap">📊 Récapitulation</a></li>
        </ul>
    </nav>

    <main class="content">
        <header class="top-bar">
            <h1>🔬 Simulation d'achats</h1>
        </header>

        <!-- MESSAGES -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- INFO + SOLDE -->
        <section class="dispatch-controls">
            <div class="info-box">
                <p>🔬 La simulation permet de <strong>tester des achats</strong> sans les valider. Simulez autant d'achats que nécessaire, puis validez-les tous en une fois ou annulez.</p>
            </div>
            <div class="stats-row" style="display:flex; gap:20px; margin-bottom:15px; flex-wrap:wrap;">
                <div class="stat-card">
                    <span>Argent disponible</span>
                    <strong style="color: green;"><?= number_format($argent['argent_disponible'] ?? 0, 0, ',', ' ') ?> Ar</strong>
                </div>
                <div class="stat-card">
                    <span>Frais d'achat</span>
                    <strong><?= $argent['frais_pourcent'] ?>%</strong>
                </div>
                <div class="stat-card">
                    <span>Simulations en attente</span>
                    <strong style="color: orange;"><?= count($simulations ?? []) ?></strong>
                </div>
            </div>
        </section>

        <!-- FILTRE PAR VILLE -->
        <section class="form-section" style="margin-top:15px;">
            <form action="/simulation" method="get" style="display:flex; gap:10px; align-items:center;">
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

        <!-- BESOINS DISPONIBLES POUR SIMULATION -->
        <section class="table-section">
            <h2>Besoins restants — Simuler un achat</h2>
            <table>
                <thead>
                    <tr>
                        <th>Ville</th>
                        <th>Besoin</th>
                        <th>Type</th>
                        <th>Prix unit.</th>
                        <th>Qté manquante</th>
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
                                <td><strong><?= $b['quantite_manquante'] ?></strong></td>
                                <td><strong><?= number_format($coutAvecFrais, 0, ',', ' ') ?> Ar</strong></td>
                                <td>
                                    <form action="/simulation/simuler" method="post" style="display:flex; gap:5px; align-items:center;">
                                        <input type="hidden" name="id_ville_besoin" value="<?= $b['id_ville_besoin'] ?>">
                                        <input type="number" name="quantite" min="1" max="<?= $b['quantite_manquante'] ?>" value="<?= $b['quantite_manquante'] ?>" style="width:70px;" required>
                                        <button type="submit" class="btn btn-warning btn-sm">🔬 Simuler</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center;">Aucun besoin restant à simuler.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- SIMULATIONS EN ATTENTE -->
        <section class="table-section">
            <h2>Simulations en attente de validation</h2>
            <?php if (!empty($simulations)): ?>
                <div style="margin-bottom:15px; display:flex; gap:10px;">
                    <a href="/simulation/valider" class="btn btn-primary">✅ Valider toutes les simulations</a>
                    <a href="/simulation/annuler" class="btn btn-danger">❌ Annuler toutes les simulations</a>
                </div>
            <?php endif; ?>
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
                        <th>Date simulation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($simulations)): ?>
                        <?php $totalSimu = 0; ?>
                        <?php foreach ($simulations as $s): ?>
                            <tr style="background-color: #fff8e1;">
                                <td><?= $s['id_achat'] ?></td>
                                <td><strong><?= htmlspecialchars($s['nom_ville']) ?></strong></td>
                                <td><?= htmlspecialchars($s['nom_besoin']) ?></td>
                                <td><span class="badge badge-<?= strtolower($s['nom_type_besoin']) ?>"><?= htmlspecialchars($s['nom_type_besoin']) ?></span></td>
                                <td><?= $s['quantite'] ?></td>
                                <td><?= number_format($s['prix_unitaire'], 2, ',', ' ') ?> Ar</td>
                                <td><?= number_format($s['montant_base'], 0, ',', ' ') ?> Ar</td>
                                <td><?= $s['frais_pourcent'] ?>%</td>
                                <td><?= number_format($s['montant_frais'], 0, ',', ' ') ?> Ar</td>
                                <td><strong><?= number_format($s['montant_total'], 0, ',', ' ') ?> Ar</strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($s['date_achat'])) ?></td>
                            </tr>
                            <?php $totalSimu += $s['montant_total']; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" style="text-align:center;">Aucune simulation en cours.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($simulations)): ?>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="9"><strong>TOTAL SIMULATIONS</strong></td>
                        <td><strong><?= number_format($totalSimu, 0, ',', ' ') ?> Ar</strong></td>
                        <td><strong><?= count($simulations) ?> simulation(s)</strong></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </section>
    </main>
</body>
</html>
