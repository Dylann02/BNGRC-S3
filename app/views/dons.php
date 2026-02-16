<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNGRC - Saisie des dons</title>
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
            <li><a href="dons" class="active">🎁 Saisie des dons</a></li>
            <li><a href="dispatch">🚚 Dispatch des dons</a></li>
        </ul>
    </nav>

    <main class="content">
        <header class="top-bar">
            <h1>Saisie des Dons</h1>
        </header>

        <!-- FORMULAIRE -->
        <section class="form-section">
            <h2>Enregistrer un don</h2>
            <form action="#" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="donateur">Nom du donateur</label>
                        <input type="text" id="donateur" name="donateur" required placeholder="Ex: ONG Espoir">
                    </div>
                    <div class="form-group">
                        <label for="dateDon">Date du don</label>
                        <input type="date" id="dateDon" name="dateDon" required value="2026-02-16">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="typeDon">Type de don</label>
                        <select id="typeDon" name="typeDon" required>
                            <option value="">-- Sélectionner --</option>
                            <option value="nature">En nature</option>
                            <option value="materiaux">En matériaux</option>
                            <option value="argent">En argent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="designationDon">Désignation</label>
                        <input type="text" id="designationDon" name="designationDon" required placeholder="Ex: Riz, Tôle, Argent">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="prixUnitaireDon">Prix unitaire (Ar)</label>
                        <input type="number" id="prixUnitaireDon" name="prixUnitaireDon" required min="0" step="100" placeholder="Ex: 2500">
                    </div>
                    <div class="form-group">
                        <label for="quantiteDon">Quantité</label>
                        <input type="number" id="quantiteDon" name="quantiteDon" required min="1" placeholder="Ex: 50">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer le don</button>
            </form>
        </section>

        <!-- LISTE DES DONS -->
        <section class="table-section">
            <h2>Liste des dons reçus</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Donateur</th>
                        <th>Type</th>
                        <th>Désignation</th>
                        <th>Prix unitaire (Ar)</th>
                        <th>Quantité</th>
                        <th>Total (Ar)</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>201</td>
                        <td>12/02/2026</td>
                        <td>ONG Espoir</td>
                        <td><span class="badge badge-nature">Nature</span></td>
                        <td>Riz (kg)</td>
                        <td>2 500</td>
                        <td>3 000</td>
                        <td><strong>7 500 000 Ar</strong></td>
                        <td><span class="badge badge-dispatched">Dispatché</span></td>
                    </tr>
                    <tr>
                        <td>202</td>
                        <td>13/02/2026</td>
                        <td>Croix Rouge</td>
                        <td><span class="badge badge-materiaux">Matériaux</span></td>
                        <td>Tôle</td>
                        <td>45 000</td>
                        <td>100</td>
                        <td><strong>4 500 000 Ar</strong></td>
                        <td><span class="badge badge-dispatched">Dispatché</span></td>
                    </tr>
                    <tr>
                        <td>203</td>
                        <td>14/02/2026</td>
                        <td>Entreprise ABC</td>
                        <td><span class="badge badge-argent">Argent</span></td>
                        <td>Aide financière</td>
                        <td>1</td>
                        <td>2 000 000</td>
                        <td><strong>2 000 000 Ar</strong></td>
                        <td><span class="badge badge-dispatched">Dispatché</span></td>
                    </tr>
                    <tr>
                        <td>204</td>
                        <td>14/02/2026</td>
                        <td>Particulier</td>
                        <td><span class="badge badge-nature">Nature</span></td>
                        <td>Huile (litre)</td>
                        <td>8 000</td>
                        <td>200</td>
                        <td><strong>1 600 000 Ar</strong></td>
                        <td><span class="badge badge-pending">En attente</span></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="7"><strong>TOTAL DES DONS</strong></td>
                        <td><strong>15 600 000 Ar</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </section>
    </main>
</body>
</html>