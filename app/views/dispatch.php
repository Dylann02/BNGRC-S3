<?php
$don

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNGRC - Dispatch des dons</title>
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
            <li><a href="dispatch" class="active">🚚 Dispatch des dons</a></li>
        </ul>
    </nav>

    <main class="content">
        <header class="top-bar">
            <h1>Dispatch des Dons</h1>
        </header>

        <!-- CONTROLES -->
        <section class="dispatch-controls">
            <div class="info-box">
                <p>⚙️ Le dispatch attribue automatiquement les dons aux villes par <strong>ordre de date de saisie</strong> du don, en respectant la correspondance des types et désignations de besoins.</p>
            </div>
            <a href="#" class="btn btn-success btn-large">🚀 Lancer le dispatch automatique</a>
            <a href="#" class="btn btn-danger">🔄 Réinitialiser le dispatch</a>
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
                    <!-- Don 201 : Riz → Mananjary (prioritaire, besoin saisi en premier) -->
                    <tr>
                        <td>201</td>
                        <td>12/02/2026</td>
                        <td>ONG Espoir</td>
                        <td>Riz (kg) <span class="badge badge-nature">Nature</span></td>
                        <td>3 000</td>
                        <td><strong>7 500 000 Ar</strong></td>
                        <td><strong>Mananjary</strong></td>
                        <td>✅ Attribué</td>
                    </tr>
                    <!-- Don 202 : Tôle → Mananjary -->
                    <tr>
                        <td>202</td>
                        <td>13/02/2026</td>
                        <td>Croix Rouge</td>
                        <td>Tôle <span class="badge badge-materiaux">Matériaux</span></td>
                        <td>100</td>
                        <td><strong>4 500 000 Ar</strong></td>
                        <td><strong>Mananjary</strong></td>
                        <td>✅ Attribué</td>
                    </tr>
                    <!-- Don 203 : Argent → Farafangana -->
                    <tr>
                        <td>203</td>
                        <td>14/02/2026</td>
                        <td>Entreprise ABC</td>
                        <td>Aide financière <span class="badge badge-argent">Argent</span></td>
                        <td>2 000 000</td>
                        <td><strong>2 000 000 Ar</strong></td>
                        <td><strong>Farafangana</strong></td>
                        <td>✅ Attribué</td>
                    </tr>
                    <!-- Don 204 : Huile → Mananjary (besoin 102) -->
                    <tr>
                        <td>204</td>
                        <td>14/02/2026</td>
                        <td>Particulier</td>
                        <td>Huile (litre) <span class="badge badge-nature">Nature</span></td>
                        <td>200</td>
                        <td><strong>1 600 000 Ar</strong></td>
                        <td><strong>Mananjary</strong></td>
                        <td>✅ Attribué</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="5"><strong>TOTAL DISPATCHÉ</strong></td>
                        <td><strong>15 600 000 Ar</strong></td>
                        <td colspan="2"><strong>4 attributions</strong></td>
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
                    <tr>
                        <td><strong>Mananjary</strong></td>
                        <td>3</td>
                        <td>13 600 000 Ar</td>
                        <td>30 000 000 Ar</td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 45%;">45%</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Manakara</strong></td>
                        <td>0</td>
                        <td>0 Ar</td>
                        <td>9 900 000 Ar</td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 0%;">0%</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Farafangana</strong></td>
                        <td>1</td>
                        <td>2 000 000 Ar</td>
                        <td>10 000 000 Ar</td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 20%;">20%</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Ikongo</strong></td>
                        <td>0</td>
                        <td>0 Ar</td>
                        <td>8 350 000 Ar</td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 0%;">0%</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>