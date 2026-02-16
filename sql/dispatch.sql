-- Table dispatch : stocke les attributions de dons aux villes
CREATE TABLE IF NOT EXISTS dispatch (
    id_dispatch INT AUTO_INCREMENT PRIMARY KEY,
    id_don INT NOT NULL,
    id_ville_besoin INT NOT NULL,
    quantite_attribuee INT NOT NULL,
    date_dispatch DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_don) REFERENCES don(id),
    FOREIGN KEY (id_ville_besoin) REFERENCES ville_besoin(id_ville_besoin)
);

-- Vue pour l'historique des dispatches avec détails
CREATE OR REPLACE VIEW v_dispatch_historique AS
SELECT 
    d.id AS id_don,
    d.date_don,
    d.donateur,
    b.nom_besoin,
    t.nom_type_besoin,
    dp.quantite_attribuee,
    dp.quantite_attribuee * b.prix_unitaire AS valeur,
    v.nom_ville,
    dp.date_dispatch,
    dp.id_dispatch
FROM dispatch dp
JOIN don d ON dp.id_don = d.id
JOIN ville_besoin vb ON dp.id_ville_besoin = vb.id_ville_besoin
JOIN besoin b ON d.id_besoin = b.id_besoin
JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
JOIN ville v ON vb.id_ville = v.id_ville
ORDER BY d.date_don ASC;

-- Vue résumé par ville
CREATE OR REPLACE VIEW v_dispatch_resume_ville AS
SELECT 
    v.id_ville,
    v.nom_ville,
    COUNT(dp.id_dispatch) AS nb_attributions,
    COALESCE(SUM(dp.quantite_attribuee * b.prix_unitaire), 0) AS total_recu,
    COALESCE(SUM(vb.quantite * b.prix_unitaire), 0) AS total_besoins
FROM ville v
LEFT JOIN ville_besoin vb ON v.id_ville = vb.id_ville
LEFT JOIN besoin b ON vb.id_besoin = b.id_besoin
LEFT JOIN dispatch dp ON dp.id_ville_besoin = vb.id_ville_besoin
GROUP BY v.id_ville, v.nom_ville;
