-- ===================================================================
-- Vue : v_achat_besoin_via_argent
-- Objectif : Montre les besoins non satisfaits de chaque ville 
--            qui pourraient être achetés grâce aux dons en argent.
-- ===================================================================

-- 1) Vue intermédiaire : montant total d'argent disponible (dons de type "Argent" non encore dispatché)
CREATE OR REPLACE VIEW v_argent_disponible AS
SELECT 
    COALESCE(SUM(d.quantite * b.prix_unitaire), 0) AS total_argent_dons,
    COALESCE(SUM(d.quantite * b.prix_unitaire), 0) 
        - COALESCE((SELECT SUM(dp.quantite_attribuee * b2.prix_unitaire) 
                    FROM dispatch dp 
                    JOIN don d2 ON dp.id_don = d2.id 
                    JOIN besoin b2 ON d2.id_besoin = b2.id_besoin
                    JOIN type_besoin t2 ON b2.id_type_besoin = t2.id_type_besoin
                    WHERE t2.nom_type_besoin = 'Argent'), 0) AS argent_disponible
FROM don d
JOIN besoin b ON d.id_besoin = b.id_besoin
JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
WHERE t.nom_type_besoin = 'Argent';

-- 2) Vue principale : besoins non satisfaits achetables avec l'argent disponible
CREATE OR REPLACE VIEW v_achat_besoin_via_argent AS
SELECT 
    v.nom_ville,
    b.nom_besoin,
    t.nom_type_besoin,
    b.prix_unitaire,
    vb.quantite AS quantite_demandee,
    -- Quantité déjà reçue via dons en nature / dispatch
    COALESCE(dons_nature.quantite_recue, 0) AS quantite_deja_recue,
    -- Quantité encore manquante
    vb.quantite - COALESCE(dons_nature.quantite_recue, 0) AS quantite_manquante,
    -- Coût pour combler le manque
    (vb.quantite - COALESCE(dons_nature.quantite_recue, 0)) * b.prix_unitaire AS cout_achat,
    -- Quantité achetable avec l'argent disponible (plafonné au manque)
    LEAST(
        vb.quantite - COALESCE(dons_nature.quantite_recue, 0),
        FLOOR((SELECT argent_disponible FROM v_argent_disponible) / b.prix_unitaire)
    ) AS quantite_achetable,
    -- Montant nécessaire pour cet achat
    LEAST(
        vb.quantite - COALESCE(dons_nature.quantite_recue, 0),
        FLOOR((SELECT argent_disponible FROM v_argent_disponible) / b.prix_unitaire)
    ) * b.prix_unitaire AS montant_achat
FROM ville_besoin vb
JOIN ville v ON vb.id_ville = v.id_ville
JOIN besoin b ON vb.id_besoin = b.id_besoin
JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
-- On exclut les besoins de type "Argent" (on ne rachète pas de l'argent)
LEFT JOIN (
    SELECT 
        vb2.id_ville_besoin,
        COALESCE(SUM(dp.quantite_attribuee), 0) AS quantite_recue
    FROM ville_besoin vb2
    LEFT JOIN dispatch dp ON dp.id_ville_besoin = vb2.id_ville_besoin
    GROUP BY vb2.id_ville_besoin
) dons_nature ON dons_nature.id_ville_besoin = vb.id_ville_besoin
WHERE t.nom_type_besoin != 'Argent'
  AND (vb.quantite - COALESCE(dons_nature.quantite_recue, 0)) > 0
ORDER BY cout_achat DESC;
