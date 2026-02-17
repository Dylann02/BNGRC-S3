<?php $page = "dons"?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BNGRC - Saisie des dons</title>
    <link rel="stylesheet" href="/assets/style.css">
    <script defer nonce="<?= $nonce ?>" src="/assets/dons.js"></script>
</head>
<body>
<?php include("header.php");?>
    <main class="content">
        <header class="top-bar">
            <h1>Saisie des Dons</h1>
        </header>

        <!-- FORMULAIRE -->
        <section class="form-section">
            <h2>Enregistrer un don</h2>
            <form action="/dons/create" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="donateur">Nom du donateur</label>
                        <input type="text" id="donateur" name="donateur" required placeholder="Ex: ONG Espoir">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="typeBesoin">Type de besoin</label>
                        <select id="typeBesoin" name="type_besoin" required>
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($type_besoin as $type): ?>
                                <option value="<?= $type['id_type_besoin'] ?>"><?= htmlspecialchars($type['nom_type_besoin']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="besoinDon">Besoin</label>
                        <select id="besoinDon" name="id_besoin" required>
                            <option value="">-- Sélectionner un type d'abord --</option>
                            <?php foreach ($besoins as $besoin): ?>
                                <option value="<?= $besoin['id_besoin'] ?>" data-type="<?= $besoin['id_type_besoin'] ?>">
                                    <?= htmlspecialchars($besoin['nom_besoin']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
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
                        <th>Donateur</th>
                        <th>Besoins</th>
                        <th>Type</th>
                        <th>Quantité</th>
                        <th>Prix</th>
                        <th>Date du don</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($dons)): ?>
                        <?php $total=0?>
                        <?php foreach ($dons as $don): ?>
                            <tr>
                                <td><?= htmlspecialchars($don['donateur'] ?? '') ?></td>
                                <td><?= htmlspecialchars($don['nom_besoin']) ?></td>
                                <td><?= htmlspecialchars($don['nom_type_besoin']) ?></td>
                                <td><?= htmlspecialchars($don['quantite_totale']) ?></td>
                                <td><?= htmlspecialchars($don['prix_total']) ?></td>
                                <?php $total=$total+$don['prix_total'] ?>
                                <td><?= htmlspecialchars($don['date_don'] ?? '') ?></td>
                            </tr>

                        <?php endforeach; ?>
                        <tr>
                            <td>Total</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><?= $total  ?></td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="6">Aucun don enregistré.</td></tr>
                    <?php endif; ?>
                </tbody>
                <!-- Optionnel : total général ici si besoin -->
            </table>
        </section>
        <?php include("footer.php"); ?>
    </main>
</body>
</html>