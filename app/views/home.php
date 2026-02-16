<?php 
$total = 0;
$nombre_dons= sizeof($dons);
$nombre_besoin = sizeof($besoin);
foreach($dons as $don){
    $total += $don['prix_total'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNGRC - Tableau de Bord</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<?php include("header.php");?>

    <main class="content">
        <header class="top-bar">
            <h1>Tableau de Bord</h1>

            <div class="stats-summary">
                <div class="stat-card stat-villes">
                    <span class="stat-number">4</span>
                    <span class="stat-label">Villes sinistrées</span>
                </div>
                <div class="stat-card stat-besoins">
                    <span class="stat-number"><?=$nombre_besoin?></span>
                    <span class="stat-label">Besoins enregistrés</span>
                </div>
                <div class="stat-card stat-dons">
                    <span class="stat-number"><?= $nombre_dons?></span>
                    <span class="stat-label">Dons reçus</span>
                </div>
                <div class="stat-card stat-montant">
                    <span class="stat-number"><?= $total ?></span>
                    <span class="stat-label">Montant total des dons</span>
                </div>
            </div>
        </header>

        <!-- TABLEAU RECAPITULATIF PAR VILLE -->
        <section class="dashboard-table">
            <h2>Récapitulatif par ville</h2>
            <table>
                <thead>
                    <tr>
                        <th>Ville</th>
                        <th>Région</th>
                        <th>Besoins (Nature)</th>
                        <th>Besoins (Matériaux)</th>
                        <th>Besoins (Argent)</th>
                        <th>Total Besoins (Ar)</th>
                        <th>Dons attribués (Ar)</th>
                        <th>Reste à couvrir (Ar)</th>
                        <th>Couverture</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- MANANJARY -->
                    <tr>
                        <td><strong>Mananjary</strong></td>
                        <td>Vatovavy-Fitovinany</td>
                        <td>Riz: 5 000 kg, Huile: 500 L</td>
                        <td>Tôle: 300</td>
                        <td>-</td>
                        <td>30 000 000 Ar</td>
                        <td>7 500 000 Ar</td>
                        <td>22 500 000 Ar</td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 25%;">25%</div>
                            </div>
                        </td>
                    </tr>
                    <!-- MANAKARA -->
                    <tr>
                        <td><strong>Manakara</strong></td>
                        <td>Vatovavy-Fitovinany</td>
                        <td>Riz: 3 000 kg</td>
                        <td>Clou: 200 kg</td>
                        <td>-</td>
                        <td>9 900 000 Ar</td>
                        <td>0 Ar</td>
                        <td>9 900 000 Ar</td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 0%;">0%</div>
                            </div>
                        </td>
                    </tr>
                    <!-- FARAFANGANA -->
                    <tr>
                        <td><strong>Farafangana</strong></td>
                        <td>Atsimo-Atsinanana</td>
                        <td>Riz: 2 000 kg</td>
                        <td>-</td>
                        <td>5 000 000 Ar</td>
                        <td>10 000 000 Ar</td>
                        <td>2 000 000 Ar</td>
                        <td>8 000 000 Ar</td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 20%;">20%</div>
                            </div>
                        </td>
                    </tr>
                    <!-- IKONGO -->
                    <tr>
                        <td><strong>Ikongo</strong></td>
                        <td>Vatovavy-Fitovinany</td>
                        <td>Huile: 200 L</td>
                        <td>Tôle: 150</td>
                        <td>-</td>
                        <td>8 350 000 Ar</td>
                        <td>3 600 000 Ar</td>
                        <td>4 750 000 Ar</td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 43%;">43%</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="5"><strong>TOTAL GÉNÉRAL</strong></td>
                        <td><strong>58 250 000 Ar</strong></td>
                        <td><strong>13 100 000 Ar</strong></td>
                        <td><strong>45 150 000 Ar</strong></td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: 22%;">22%</div>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </section>
    </main>
</body>
</html>