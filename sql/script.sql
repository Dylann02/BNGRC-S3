create database BNGRC;
USE BNGRC;

CREATE TABLE region (
    id_region INT AUTO_INCREMENT PRIMARY KEY,
    nom_region VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE ville (
    id_ville INT AUTO_INCREMENT PRIMARY KEY,
    nom_ville VARCHAR(100) NOT NULL,
    id_region INT NOT NULL,
    FOREIGN KEY (id_region) REFERENCES region(id_region)
);

CREATE TABLE type_besoin (
    id_type_besoin INT AUTO_INCREMENT PRIMARY KEY,
    nom_type_besoin VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE besoin (
    id_besoin INT AUTO_INCREMENT PRIMARY KEY,
    nom_besoin VARCHAR(100) NOT NULL,
    id_type_besoin INT NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_type_besoin) REFERENCES type_besoin(id_type_besoin)
);

CREATE TABLE ville_besoin (
    id_ville_besoin INT AUTO_INCREMENT PRIMARY KEY,
    id_ville INT NOT NULL,
    id_besoin INT NOT NULL,
    quantite INT NOT NULL,
    date_saisie DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_ville) REFERENCES ville(id_ville),
    FOREIGN KEY (id_besoin) REFERENCES besoin(id_besoin)
);

CREATE OR REPLACE TABLE don (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donateur VARCHAR(200),
    id_besoin INT NOT NULL,
    quantite INT NOT NULL,
    date_don DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_besoin) REFERENCES besoin(id_besoin)
);

CREATE OR REPLACE view v_affiche_besoin as 
SELECT 
besoin.id_besoin as id_besoin,
ville.nom_ville as nom_ville,
besoin.nom_besoin as nom_besoin,
type_besoin.nom_type_besoin as nom_type_besoin,
ville_besoin.quantite as quantite,
besoin.prix_unitaire as prix_unitaire
FROM ville_besoin JOIN besoin 
ON ville_besoin.id_besoin =besoin.id_besoin
JOIN ville ON ville_besoin.id_ville = ville.id_ville
JOIN type_besoin ON type_besoin.id_type_besoin = besoin.id_type_besoin;

