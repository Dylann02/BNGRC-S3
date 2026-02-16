<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNGRC - Besoins des sinistrés</title>
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
            <li><a href="besoins" class="active">📋 Besoins des sinistrés</a></li>
            <li><a href="dons">🎁 Saisie des dons</a></li>
            <li><a href="dispatch">🚚 Dispatch des dons</a></li>
        </ul>
    </nav>

    <main class="content">
        <header class="top-bar">
            <h1>Saisie des Besoins par Ville</h1>
        </header>

        <!-- FORMULAIRE -->
        <section class="form-section">
            <h2>Ajouter un besoin</h2>
            <form action="#" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="villeSelect">Ville</label>
                        <select id="villeSelect" name="villeId" required>
                            <option value="">-- Sélectionner une ville --</option>
                            <option value="1">Mananjary (Vatovavy-Fitovinany)</option>
                            <option value="2">Manakara (Vatovavy-Fitovinany)</option>
                            <option value="3">Farafangana (Atsimo-Atsinanana)</option>
                            <option value="4">Ikongo (Vatovavy-Fitovinany)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="typeBesoin">Type de besoin</label>
                        <select id="typeBesoin" name="typeBesoin" required>
                            <option value="">-- Sélectionner --</option>
                            <option value="nature">En nature (riz, huile...)</option>
                            <option value="materiaux">En matériaux (tôle, clou...)</option>
                            <option value="argent">En argent</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="designation">Désignation</label>
                        <input type="text" id="designation" name="designation" required placeholder="Ex: Riz, Tôle, Aide financière">
                    </div>
                    <div class="form-group">
                        <label for="prixUnitaire">Prix unitaire (Ar)</label>
                        <input type="number" id="prixUnitaire" name="prixUnitaire" required min="0" step="100" placeholder="Ex: 2500">
                    </div>
                    <div class="form-group">
                        <label for="quantite">Quantité</label>
                        <input type="number" id="quantite" name="quantite" required min="1" placeholder="Ex: 100">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Ajouter le besoin</button>
            </form>
        </section>

        <!-- LISTE DES BESOINS -->
        <section class="table-section">
            <h2>Liste des besoins</h2>
            <div class="filter-bar">
                <label for="filterVille">Filtrer par ville :</label>
                <select id="filterVille" name="filterVille">
                    <option value="">Toutes les villes</option>
                    <option value="1">Mananjary</option>
                    <option value="2">Manakara</option>
                    <option value="3">Farafangana</option>
                    <option value="4">Ikongo</option>
                </select>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ville</th>
                        <th>Type</th>
                        <th>Désignation</th>
                        <th>Prix unitaire (Ar)</th>
                        <th>Quantité</th>
                        <th>Total (Ar)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>101</td>
                        <td>Mananjary</td>
                        <td><span class="badge badge-nature">Nature</span></td>
                        <td>Riz (kg)</td>
                        <td>2 500</td>
                        <td>5 000</td>
                        <td><strong>12 500 000 Ar</strong></td>
                        <td><a href="#" class="btn btn-danger btn-small">Supprimer</a></td>
                    </tr>
                    <tr>
                        <td>102</td>
                        <td>Mananjary</td>
                        <td><span class="badge badge-nature">Nature</span></td>
                        <td>Huile (litre)</td>
                        <td>8 000</td>
                        <td>500</td>
                        <td><strong>4 000 000 Ar</strong></td>
                        <td><a href="#" class="btn btn-danger btn-small">Supprimer</a></td>
                    </tr>
                    <tr>
                        <td>103</td>
                        <td>Mananjary</td>
                        <td><span class="badge badge-materiaux">Matériaux</span></td>
                        <td>Tôle</td>
                        <td>45 000</td>
                        <td>300</td>
                        <td><strong>13 500 000 Ar</strong></td>
                        <td><a href="#" class="btn btn-danger btn-small">Supprimer</a></td>
                    </tr>
                    <tr>
                        <td>104</td>
                        <td>Manakara</td>
                        <td><span class="badge badge-nature">Nature</span></td>
                        <td>Riz (kg)</td>
                        <td>2 500</td>
                        <td>3 000</td>
                        <td><strong>7 500 000 Ar</strong></td>
                        <td><a href="#" class="btn btn-danger btn-small">Supprimer</a></td>
                    </tr>
                    <tr>
                        <td>105</td>
                        <td>Manakara</td>
                        <td><span class="badge badge-materiaux">Matériaux</span></td>
                        <td>Clou (kg)</td>
                        <td>12 000</td>
                        <td>200</td>
                        <td><strong>2 400 000 Ar</strong></td>
                        <td><a href="#" class="btn btn-danger btn-small">Supprimer</a></td>
                    </tr>
                    <tr>
                        <td>106</td>
                        <td>Farafangana</td>
                        <td><span class="badge badge-nature">Nature</span></td>
                        <td>Riz (kg)</td>
                        <td>2 500</td>
                        <td>2 000</td>
                        <td><strong>5 000 000 Ar</strong></td>
                        <td><a href="#" class="btn btn-danger btn-small">Supprimer</a></td>
                    </tr>
                    <tr>
                        <td>107</td>
                        <td>Farafangana</td>
                        <td><span class="badge badge-argent">Argent</span></td>
                        <td>Aide financière</td>
                        <td>1</td>
                        <td>5 000 000</td>
                        <td><strong>5 000 000 Ar</strong></td>
                        <td><a href="#" class="btn btn-danger btn-small">Supprimer</a></td>
                    </tr>
                    <tr>
                        <td>108</td>
                        <td>Ikongo</td>
                        <td><span class="badge badge-materiaux">Matériaux</span></td>
                        <td>Tôle</td>
                        <td>45 000</td>
                        <td>150</td>
                        <td><strong>6 750 000 Ar</strong></td>
                        <td><a href="#" class="btn btn-danger btn-small">Supprimer</a></td>
                    </tr>
                    <tr>
                        <td>109</td>
                        <td>Ikongo</td>
                        <td><span class="badge badge-nature">Nature</span></td>
                        <td>Huile (litre)</td>
                        <td>8 000</td>
                        <td>200</td>
                        <td><strong>1 600 000 Ar</strong></td>
                        <td><a href="#" class="btn btn-danger btn-small">Supprimer</a></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="6"><strong>TOTAL</strong></td>
                        <td><strong>58 250 000 Ar</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </section>
    </main>
</body>
</html>