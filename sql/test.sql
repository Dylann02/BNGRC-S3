INSERT INTO region (nom_region) VALUES
('Analamanga'),
('Atsinanana'),
('Boeny'),
('Vakinankaratra'),
('Sava');

INSERT INTO ville (nom_ville, id_region) VALUES
('Antananarivo', 1),
('Toamasina', 2),
('Mahajanga', 3),
('Antsirabe', 4),
('Sambava', 5);

INSERT INTO type_besoin (nom_type_besoin) VALUES
('Nature'),
('Materiaux'),
('Argent'),
('Sante'),
('Logement');

INSERT INTO besoin (nom_besoin, id_type_besoin, prix_unitaire) VALUES
('Eau potable', 1, 0.50),
('Nourriture', 1, 2.00),
('Tentes', 2, 100.00),
('Vêtements', 2, 50.00),
('Dons en argent', 3, 1.00),
('Soins médicaux', 4, 20.00),
('Abri temporaire', 5, 150.00);


INSERT INTO ville_besoin (id_ville, id_besoin, quantite, date_saisie) VALUES
(1, 1, 1000, '2026-01-10 08:00:00'),
(1, 2, 500,  '2026-01-10 09:30:00'),
(2, 3, 50,   '2026-01-11 10:00:00'),
(2, 4, 200,  '2026-01-11 14:15:00'),
(3, 5, 300,  '2026-01-12 07:45:00'),
(4, 6, 150,  '2026-01-13 11:00:00'),
(5, 7, 20,   '2026-01-14 16:30:00');


INSERT INTO don (id_besoin, quantite) VALUES
(1, 500),
(2, 200),
(3, 20),
(4, 100),
(5, 150),
(6, 80),
(7, 10);