
<nav class="sidebar">
        <div class="sidebar-header">
            <h2>🏛️ BNGRC</h2>
            <p>Suivi des dons</p>
        </div>
        <ul class="nav-links">
            <li><a href="home" id="home">📊 Tableau de bord</a></li>
            <li><a href="villes" id="villes">🏘️ Villes & Régions</a></li>
            <li><a href="besoins" id="besoins">📋 Besoins des sinistrés</a></li>
            <li><a href="dons" id="dons">🎁 Saisie des dons</a></li>
            <li><a href="dispatch" id="dispatch">🚚 Dispatch des dons</a></li>
            <li><a href="achats" id="achats">💰 Achats (argent)</a></li>
            <li><a href="recap" id="recap">📊 Récapitulation</a></li>
        </ul>
    </nav>

    <script>
        const elements = document.querySelectorAll('.active');
        elements.forEach(el => el.classList.remove('active'));
        const ville = "<?= $page ?>";
        console.log(ville);
        var selected = document.getElementById(ville);
        if (selected) {
            selected.classList.add('active');
        }
    </script>