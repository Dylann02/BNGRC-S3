<?php
function formatMontant($montant) {
    return number_format($montant, 0, ',', ' ') . ' Ar';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNGRC - Récapitulation des Besoins</title>
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        .recap-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .recap-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .btn-refresh {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s;
        }
        .btn-refresh:hover {
            background-color: #2980b9;
        }
        .btn-refresh:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }
        .btn-refresh .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        .btn-refresh.loading .spinner {
            display: inline-block;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .recap-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .recap-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        .recap-card.total {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .recap-card.satisfait {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .recap-card.restant {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        }
        .recap-card .label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }
        .recap-card .montant {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .progress-container {
            background: #f0f0f0;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 1rem;
        }
        .progress-bar {
            background: #e0e0e0;
            border-radius: 5px;
            height: 30px;
            overflow: hidden;
            margin-top: 1rem;
        }
        .progress-fill {
            background: linear-gradient(90deg, #11998e 0%, #38ef7d 100%);
            height: 100%;
            transition: width 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .last-update {
            text-align: center;
            color: #888;
            font-size: 0.85rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
<?php include("header.php"); ?>

<main class="content">
    <div class="recap-container">
        <div class="recap-header">
            <h1>Récapitulation des Besoins</h1>
            <button class="btn-refresh" id="btnRefresh" onclick="actualiserRecap()">
                <span class="spinner"></span>
                <span>Actualiser</span>
            </button>
        </div>

        <div class="recap-cards">
            <div class="recap-card total">
                <div class="label">Besoins Totaux</div>
                <div class="montant" id="montantTotal"><?= formatMontant($recap['montant_total']) ?></div>
            </div>
            <div class="recap-card satisfait">
                <div class="label">Besoins Satisfaits</div>
                <div class="montant" id="montantSatisfait"><?= formatMontant($recap['montant_satisfait']) ?></div>
            </div>
            <div class="recap-card restant">
                <div class="label">Besoins Restants</div>
                <div class="montant" id="montantRestant"><?= formatMontant($recap['montant_restant']) ?></div>
            </div>
        </div>

        <div class="progress-container">
            <h3>Progression globale</h3>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill" style="width: <?= $recap['pourcentage_satisfait'] ?>%">
                    <span id="progressText"><?= $recap['pourcentage_satisfait'] ?>%</span>
                </div>
            </div>
        </div>

        <div class="last-update" id="lastUpdate">
            Dernière mise à jour : <?= date('d/m/Y H:i:s') ?>
        </div>
    </div>
</main>

<script>
function formatMontant(montant) {
    return new Intl.NumberFormat('fr-FR').format(montant) + ' Ar';
}

function actualiserRecap() {
    const btn = document.getElementById('btnRefresh');
    btn.classList.add('loading');
    btn.disabled = true;

    fetch('/api/recap')
        .then(response => response.json())
        .then(data => {
            document.getElementById('montantTotal').textContent = formatMontant(data.montant_total);
            document.getElementById('montantSatisfait').textContent = formatMontant(data.montant_satisfait);
            document.getElementById('montantRestant').textContent = formatMontant(data.montant_restant);
            
            const progressFill = document.getElementById('progressFill');
            progressFill.style.width = data.pourcentage_satisfait + '%';
            document.getElementById('progressText').textContent = data.pourcentage_satisfait + '%';
            
            const now = new Date();
            document.getElementById('lastUpdate').textContent = 
                'Dernière mise à jour : ' + now.toLocaleDateString('fr-FR') + ' ' + now.toLocaleTimeString('fr-FR');
        })
        .catch(error => {
            console.error('Erreur lors de l\'actualisation:', error);
            alert('Erreur lors de l\'actualisation des données');
        })
        .finally(() => {
            btn.classList.remove('loading');
            btn.disabled = false;
        });
}
</script>
</body>
</html>