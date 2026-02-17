-- Table achat : achats de besoins via les dons en argent
CREATE TABLE IF NOT EXISTS achat (
    id_achat INT AUTO_INCREMENT PRIMARY KEY,
    id_ville_besoin INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    montant_total DECIMAL(12,2) NOT NULL,
    date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_ville_besoin) REFERENCES ville_besoin(id_ville_besoin)
);
