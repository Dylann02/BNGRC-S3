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