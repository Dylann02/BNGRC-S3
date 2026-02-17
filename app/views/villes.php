<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNGRC - Villes & Régions</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<?php include("header.php");?>

    <main class="content">
        <header class="top-bar">
            <h1>Gestion des Villes & Régions</h1>
        </header>

        <section class="form-section">
            <h2>Ajouter une ville</h2>
            <form method="POST" action="/villes">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nomVille">Nom de la ville</label>
                        <input type="text" id="nomVille" name="nomVille" placeholder="Ex: Mananjary" required>
                    </div>
                    <div class="form-group">
                        <label for="region">Région</label>
                        <select id="region" name="region" required>
                            <option value="">-- Choisir une région --</option>
                            <?php if(isset($regions) && !empty($regions)): ?>
                                <?php foreach($regions as $region): ?>
                                    <option value="<?php echo htmlspecialchars(isset($region['id_region']) ? $region['id_region'] : ''); ?>">
                                        <?php echo htmlspecialchars(isset($region['nom_region']) ? $region['nom_region'] : ''); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Ajouter la ville</button>
            </form>
        </section>

        <section class="table-section">
            <h2>Liste des villes sinistrées</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ville</th>
                        <th>Région</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(isset($villes) && !empty($villes)): ?>
                        <?php foreach($villes as $ville): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(isset($ville['id_ville']) ? $ville['id_ville'] : ''); ?></td>
                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars(isset($ville['nom_ville']) ? $ville['nom_ville'] : ''); ?>
                                    </strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars(isset($ville['nom_region']) ? $ville['nom_region'] : 'Non définie'); ?>
                                </td>
                                <td>
                                    <?php if(isset($ville['id_ville'])): ?>
                                        <a href="/delete-ville/<?php echo $ville['id_ville']; ?>" 
                                           class="btn btn-danger btn-small" 
                                           onclick="return confirm('Supprimer cette ville ?')">Supprimer</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center;">Aucune ville enregistrée</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>