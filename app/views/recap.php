<?php
/** @var array $recap */
/** @var array $recap_villes */
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNGRC - Récapitulation</title>
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
            <li><a href="simulation">🔬 Simulation</a></li>
            <li><a href="recap" class="active">📊 Récapitulation</a></li>
        </ul>
    </nav>

    <main class="content">
        <header class="top-bar">
            <h1>📊 Récapitulation générale</h1>
        </header>

        <!-- BOUTON ACTUALISER -->
        <section class="dispatch-controls">
            <button id="btn-actualiser" class="btn btn-primary" onclick="actualiserRecap()">🔄 Actualiser</button>
            <span id="loading" style="display:none; margin-left:10px; color:#888;">Chargement...</span>
        </section>

        <!-- STATS GLOBALES -->
        <section class="dispatch-controls" id="stats-globales">
            <div class="stats-row" style="display:flex; gap:20px; flex-wrap:wrap;">
                <div class="stat-card">
                    <span>Total besoins (valeur)</span>
                    <strong id="stat-total-besoins"><?= number_format($recap['total_besoins_valeur'] ?? 0, 0, ',', ' ') ?> Ar</strong>
                </div>
                <div class="stat-card">
                    <span>Total satisfait (dispatch)</span>
                    <strong id="stat-total-dispatch" style="color: #2196f3;"><?= number_format($recap['total_dispatch_valeur'] ?? 0, 0, ',', ' ') ?> Ar</strong>
                </div>
                <div class="stat-card">
                    <span>Total achats</span>
                    <strong id="stat-total-achats" style="color: #4caf50;"><?= number_format($recap['total_achats_valeur'] ?? 0, 0, ',', ' ') ?> Ar</strong>
                </div>
                <div class="stat-card">
                    <span>Total frais d'achat</span>
                    <strong id="stat-total-frais" style="color: #ff9800;"><?= number_format($recap['total_frais'] ?? 0, 0, ',', ' ') ?> Ar</strong>
                </div>
                <div class="stat-card">
                    <span>Total restant</span>
                    <strong id="stat-total-restant" style="color: #f44336;"><?= number_format($recap['total_restant_valeur'] ?? 0, 0, ',', ' ') ?> Ar</strong>
                </div>
                <div class="stat-card">
                    <span>Couverture globale</span>
                    <strong id="stat-couverture"><?= number_format($recap['couverture_pourcent'] ?? 0, 1) ?>%</strong>
                </div>
            </div>
            <!-- Barre de progression globale -->
            <div style="margin-top:15px;">
                <div style="background:#e0e0e0; border-radius:8px; height:30px; overflow:hidden; position:relative;">
                    <div id="progress-bar" style="height:100%; background: linear-gradient(90deg, #2196f3 <?= ($recap['dispatch_pourcent'] ?? 0) ?>%, #4caf50 <?= ($recap['dispatch_pourcent'] ?? 0) ?>%); width:<?= min($recap['couverture_pourcent'] ?? 0, 100) ?>%; transition: width 0.5s;">
                    </div>
                    <span style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); font-weight:bold; color:#333;" id="progress-text"><?= number_format($recap['couverture_pourcent'] ?? 0, 1) ?>%</span>
                </div>
                <div style="display:flex; gap:20px; margin-top:5px; font-size:0.85em;">
                    <span>🔵 Dispatch : <span id="legend-dispatch"><?= number_format($recap['dispatch_pourcent'] ?? 0, 1) ?></span>%</span>
                    <span>🟢 Achats : <span id="legend-achats"><?= number_format($recap['achats_pourcent'] ?? 0, 1) ?></span>%</span>
                    <span>🔴 Restant : <span id="legend-restant"><?= number_format(100 - ($recap['couverture_pourcent'] ?? 0), 1) ?></span>%</span>
                </div>
            </div>
        </section>

        <!-- ARGENT -->
        <section class="dispatch-controls">
            <h2>💰 Bilan financier</h2>
            <div class="stats-row" style="display:flex; gap:20px; flex-wrap:wrap;">
                <div class="stat-card">
                    <span>Dons en argent reçus</span>
                    <strong id="stat-argent-total"><?= number_format($recap['argent_total_dons'] ?? 0, 0, ',', ' ') ?> Ar</strong>
                </div>
                <div class="stat-card">
                    <span>Dépensé (achats + frais)</span>
                    <strong id="stat-argent-depense" style="color: #f44336;"><?= number_format($recap['argent_depense'] ?? 0, 0, ',', ' ') ?> Ar</strong>
                </div>
                <div class="stat-card">
                    <span>Argent disponible</span>
                    <strong id="stat-argent-disponible" style="color: green;"><?= number_format($recap['argent_disponible'] ?? 0, 0, ',', ' ') ?> Ar</strong>
                </div>
            </div>
        </section>

        <!-- RECAP PAR VILLE -->
        <section class="table-section">
            <h2>Récapitulation par ville</h2>
            <table id="table-recap-villes">
                <thead>
                    <tr>
                        <th>Ville</th>
                        <th>Région</th>
                        <th>Total besoins</th>
                        <th>Satisfait (dispatch)</th>
                        <th>Satisfait (achats)</th>
                        <th>Total satisfait</th>
                        <th>Restant</th>
                        <th>Couverture</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recap_villes)): ?>
                        <?php foreach ($recap_villes as $rv): ?>
                            <?php $couverture = ($rv['total_besoins'] > 0) ? ($rv['total_satisfait'] / $rv['total_besoins'] * 100) : 0; ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($rv['nom_ville']) ?></strong></td>
                                <td><?= htmlspecialchars($rv['nom_region'] ?? '-') ?></td>
                                <td><?= $rv['total_besoins'] ?></td>
                                <td><?= $rv['total_dispatch'] ?></td>
                                <td><?= $rv['total_achats'] ?></td>
                                <td><strong><?= $rv['total_satisfait'] ?></strong></td>
                                <td style="color: <?= $rv['total_restant'] > 0 ? 'red' : 'green' ?>;"><?= $rv['total_restant'] ?></td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div style="background:#e0e0e0; border-radius:4px; height:14px; width:80px; overflow:hidden;">
                                            <div style="height:100%; background:<?= $couverture >= 100 ? '#4caf50' : '#2196f3' ?>; width:<?= min($couverture, 100) ?>%;"></div>
                                        </div>
                                        <span><?= number_format($couverture, 1) ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center;">Aucune donnée.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script nonce="<?= \Flight::get('csp_nonce') ?>">
    function formatNumber(num) {
        return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    function actualiserRecap() {
        const btn = document.getElementById('btn-actualiser');
        const loading = document.getElementById('loading');
        btn.disabled = true;
        loading.style.display = 'inline';

        fetch('/recap/json')
            .then(r => r.json())
            .then(data => {
                const r = data.recap;
                const villes = data.recap_villes;

                // Stats globales
                document.getElementById('stat-total-besoins').textContent = formatNumber(r.total_besoins_valeur) + ' Ar';
                document.getElementById('stat-total-dispatch').textContent = formatNumber(r.total_dispatch_valeur) + ' Ar';
                document.getElementById('stat-total-achats').textContent = formatNumber(r.total_achats_valeur) + ' Ar';
                document.getElementById('stat-total-frais').textContent = formatNumber(r.total_frais) + ' Ar';
                document.getElementById('stat-total-restant').textContent = formatNumber(r.total_restant_valeur) + ' Ar';
                document.getElementById('stat-couverture').textContent = parseFloat(r.couverture_pourcent).toFixed(1) + '%';

                // Barre de progression
                document.getElementById('progress-bar').style.width = Math.min(r.couverture_pourcent, 100) + '%';
                document.getElementById('progress-text').textContent = parseFloat(r.couverture_pourcent).toFixed(1) + '%';
                document.getElementById('legend-dispatch').textContent = parseFloat(r.dispatch_pourcent).toFixed(1);
                document.getElementById('legend-achats').textContent = parseFloat(r.achats_pourcent).toFixed(1);
                document.getElementById('legend-restant').textContent = (100 - parseFloat(r.couverture_pourcent)).toFixed(1);

                // Argent
                document.getElementById('stat-argent-total').textContent = formatNumber(r.argent_total_dons) + ' Ar';
                document.getElementById('stat-argent-depense').textContent = formatNumber(r.argent_depense) + ' Ar';
                document.getElementById('stat-argent-disponible').textContent = formatNumber(r.argent_disponible) + ' Ar';

                // Tableau villes
                const tbody = document.querySelector('#table-recap-villes tbody');
                if (villes && villes.length > 0) {
                    tbody.innerHTML = villes.map(v => {
                        const couv = v.total_besoins > 0 ? (v.total_satisfait / v.total_besoins * 100) : 0;
                        const barColor = couv >= 100 ? '#4caf50' : '#2196f3';
                        const restColor = v.total_restant > 0 ? 'red' : 'green';
                        return `<tr>
                            <td><strong>${v.nom_ville}</strong></td>
                            <td>${v.nom_region || '-'}</td>
                            <td>${v.total_besoins}</td>
                            <td>${v.total_dispatch}</td>
                            <td>${v.total_achats}</td>
                            <td><strong>${v.total_satisfait}</strong></td>
                            <td style="color:${restColor};">${v.total_restant}</td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div style="background:#e0e0e0; border-radius:4px; height:14px; width:80px; overflow:hidden;">
                                        <div style="height:100%; background:${barColor}; width:${Math.min(couv, 100)}%;"></div>
                                    </div>
                                    <span>${couv.toFixed(1)}%</span>
                                </div>
                            </td>
                        </tr>`;
                    }).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">Aucune donnée.</td></tr>';
                }
            })
            .catch(err => {
                console.error('Erreur actualisation:', err);
                alert('Erreur lors de l\'actualisation.');
            })
            .finally(() => {
                btn.disabled = false;
                loading.style.display = 'none';
            });
    }
    </script>
</body>
</html>
