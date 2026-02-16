-- Vue unique pour afficher le cumul des dons par besoin et type, avec prix total
CREATE OR REPLACE VIEW vue_don_besoins AS
SELECT 
	b.nom_besoin, 
	t.nom_type_besoin, 
	SUM(d.quantite) AS quantite_totale,
	SUM(d.quantite * b.prix_unitaire) AS prix_total,
    MAX(d.date_don) AS date_don
FROM don d
JOIN besoin b ON d.id_besoin = b.id_besoin
JOIN type_besoin t ON b.id_type_besoin = t.id_type_besoin
GROUP BY b.nom_besoin, t.nom_type_besoin;
