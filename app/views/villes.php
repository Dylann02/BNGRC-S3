<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNGRC - Villes & Régions</title>
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
            <li><a href="villes" class="active">🏘️ Villes & Régions</a></li>
            <li><a href="besoins">📋 Besoins des sinistrés</a></li>
            <li><a href="dons">🎁 Saisie des dons</a></li>
            <li><a href="dispatch">🚚 Dispatch des dons</a></li>
        </ul>
    </nav>

    <main class="content">
        <header class="top-bar">
            <h1>Gestion des Villes & Régions</h1>
        </header>

        <!-- FORMULAIRE D'AJOUT -->
        <section class="form-section">
            <h2>Ajouter une ville</h2>
            <form action="#" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nomVille">Nom de la ville</label>
                        <input type="text" id="nomVille" name="nomVille" required placeholder="Ex: Mananjary">
                    </div>
                    <div class="form-group">
                        <label for="region">Région</label>
                        <input type="text" id="region" name="region" required placeholder="Ex: Vatovavy-Fitovinany">
                    </div>
                    <div class="form-group">
                        <label for="nbSinistres">Nombre de sinistrés</label>
                        <input type="number" id="nbSinistres" name="nbSinistres" required min="1" placeholder="Ex: 500">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Ajouter la ville</button>
            </form>
        </section>

        <!-- LISTE DES VILLES -->
        <section class="table-section">
            <h2>Liste des villes sinistrées</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ville</th>
                        <th>Région</th>
                        <th>Nb sinistrés</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><strong>Mananjary</strong></td>
                        <td>Vatovavy-Fitovinany</td>
                        <td>1 200</td>
                        <td>
                            <a href="#" class="btn btn-danger btn-small">Supprimer</a>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><strong>Manakara</strong></td>
                        <td>Vatovavy-Fitovinany</td>
                        <td>850</td>
                        <td>
                            <a href="#" class="btn btn-danger btn-small">Supprimer</a>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td><strong>Farafangana</strong></td>
                        <td>Atsimo-Atsinanana</td>
                        <td>600</td>
                        <td>
                            <a href="#" class="btn btn-danger btn-small">Supprimer</a>
                        </td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td><strong>Ikongo</strong></td>
                        <td>Vatovavy-Fitovinany</td>
                        <td>400</td>
                        <td>
                            <a href="#" class="btn btn-danger btn-small">Supprimer</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>