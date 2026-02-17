<?php 
$page = "home";
$total = 0;
$nombre_dons= sizeof($dons);
$nombre_besoin = sizeof($besoin);
$nombre_ville = sizeof($ville);
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
                    <span class="stat-number"><?= $nombre_ville?></span>
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
    </main>
</body>
</html>