-- ============================================================
-- BASE DE DONNÉES : GETECH EXPRESS
-- Auteur : Étudiant Licence SIL - Session TD 2026
-- Description : Script SQL de création de la BDD
-- ============================================================

-- Création et sélection de la base de données
CREATE DATABASE IF NOT EXISTS getech_express CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE getech_express;

-- ============================================================
-- TABLE : livreur
-- Stocke les informations des livreurs de GETECH EXPRESS
-- ============================================================
CREATE TABLE IF NOT EXISTS livreur (
    id          INT          NOT NULL AUTO_INCREMENT,
    nomcomplet  VARCHAR(150) NOT NULL,
    telephone   VARCHAR(20)  NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : commande
-- Stocke les commandes clients avec leur statut et livreur affecté
-- ============================================================
CREATE TABLE IF NOT EXISTS commande (
    id            INT         NOT NULL AUTO_INCREMENT,
    description   TEXT        NOT NULL,
    client        VARCHAR(150) NOT NULL,
    telclient     VARCHAR(20) NOT NULL,
    adresseclient VARCHAR(255) NOT NULL,
    statut        ENUM('en préparation', 'en cours de livraison', 'livré') NOT NULL DEFAULT 'en préparation',
    livreur_id    INT         DEFAULT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_commande_livreur
        FOREIGN KEY (livreur_id) REFERENCES livreur(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DONNÉES DE TEST (optionnelles)
-- ============================================================
INSERT INTO livreur (nomcomplet, telephone) VALUES
('Kofi Mensah', '97000001'),
('Amavi Dossou', '97000002'),
('Sènan Agossou', '97000003');

INSERT INTO commande (description, client, telclient, adresseclient, statut, livreur_id) VALUES
('Colis alimentaire 5kg', 'Jean Dupont', '96111111', 'Quartier Cadjehoun, Cotonou', 'en préparation', 1),
('Téléphone portable Samsung', 'Marie Cissé', '97222222', 'Carrefour SÈME, Porto-Novo', 'en cours de livraison', 2),
('Documents administratifs', 'Paul Amoussou', '98333333', 'Akpakpa Centre, Cotonou', 'livré', 3);
