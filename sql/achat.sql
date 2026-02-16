-- Table achat : stocke les achats de besoins effectués via les dons en argent
-- frais_pourcent : le % de frais appliqué au moment de l'achat
-- montant_frais : le montant des frais calculé
-- simulation : 1 = simulation (non validé), 0 = achat validé
CREATE TABLE IF NOT EXISTS achat (
    id_achat INT AUTO_INCREMENT PRIMARY KEY,
    id_ville_besoin INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    frais_pourcent DECIMAL(5,2) NOT NULL DEFAULT 0,
    montant_frais DECIMAL(12,2) NOT NULL DEFAULT 0,
    simulation TINYINT(1) NOT NULL DEFAULT 0,
    date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_ville_besoin) REFERENCES ville_besoin(id_ville_besoin)
);
