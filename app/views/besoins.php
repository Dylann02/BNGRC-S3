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
            <form action="/traitementForm" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="villeSelect">Ville</label>
                        <select id="villeSelect" name="ville" required>
                            <option value="">-- Sélectionner une ville --</option>
                            <?php foreach ($ville as $v) { ?>
                                <option value="<?= $v['id_ville'];?>"><?= $v['nom_ville'];?></option>
                            <?php  }?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="typeBesoin">Type de besoin</label>
                        <select id="typeBesoin" name="typeBesoin" required>
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($typeBesoin as $t) {?>
                                    <option value="<?= $t['id_type_besoin'];?>"><?= $t['nom_type_besoin'];?></option>
                            <?php }?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="designation">Désignation</label>
                        <input type="text" id="designation" name="nom_besoin" required placeholder="Ex: Riz, Tôle, Aide financière">
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
                    <?php 
                    $count = 0;
                    foreach($data as $d) { ?>
                    <tr>
                        <td><?= $d['nom_ville'];?></td>
                        <td><span class="badge badge-nature"><?= $d['nom_type_besoin'];?></span></td>
                        <td><?= $d['nom_besoin'];?></td>
                        <td><?= $d['prix_unitaire'];?></td>
                        <td><?= $d['quantite'];?></td>
                        <td><strong><?=$d['prix_unitaire'] *  $d['quantite'];?></strong></td>
                        <td><a href="supprimerBesoin/<?= $d['id_besoin'];?>" class="btn btn-danger btn-small">Supprimer</a></td>
                    </tr>
                    <?php 
                    $count += $d['prix_unitaire'] *  $d['quantite'];
                    }?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="6"><strong>TOTAL</strong></td>
                        <td><strong><?= $count?>Ar</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </section>
    </main>
</body>
</html>