create database BNGRC;
use BNGRC;

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

CREATE TABLE don (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_besoin INT NOT NULL,
    quantite INT NOT NULL,
    date_don DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_besoin) REFERENCES besoin(id_besoin)
);



