-- Script de création de la base de données CAMED
-- Utilisation avec XAMPP / MySQL

CREATE DATABASE IF NOT EXISTS camed_db;
USE camed_db;

-- 1. Table administrateur
CREATE TABLE IF NOT EXISTS administrateur (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    fonction VARCHAR(100) NOT NULL
);

-- 2. Table utilisateur (générique pour conseiller, chef d'agence, responsable)
CREATE TABLE IF NOT EXISTS utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Ajouté pour l'authentification
    role ENUM('conseiller_client', 'chef_agence', 'responsable_engagement') NOT NULL,
    id_admin INT,
    FOREIGN KEY (id_admin) REFERENCES administrateur(id_admin) ON DELETE SET NULL
);

-- 3. Table client
CREATE TABLE IF NOT EXISTS client (
    id_client INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    profession VARCHAR(100)
);

-- 4. Table regle_credit
CREATE TABLE IF NOT EXISTS regle_credit (
    id_regle INT AUTO_INCREMENT PRIMARY KEY,
    type_credit VARCHAR(100) NOT NULL,
    duree_min INT NOT NULL, -- En mois
    conditions TEXT NOT NULL
);

-- 5. Table demande_credit
CREATE TABLE IF NOT EXISTS demande_credit (
    id_demande INT AUTO_INCREMENT PRIMARY KEY,
    montant DECIMAL(15, 2) NOT NULL,
    statut ENUM('en cours', 'validee', 'rejetee') DEFAULT 'en cours',
    date_demande DATE DEFAULT CURRENT_DATE,
    id_client INT NOT NULL,
    id_conseiller INT,
    id_chef_agence INT,
    id_responsable INT,
    id_responsable INT,
    salaire_net DECIMAL(15, 2) DEFAULT 0,
    charges_mensuelles DECIMAL(15, 2) DEFAULT 0,
    FOREIGN KEY (id_client) REFERENCES client(id_client) ON DELETE CASCADE,
    FOREIGN KEY (id_conseiller) REFERENCES utilisateur(id_utilisateur) ON DELETE SET NULL,
    FOREIGN KEY (id_chef_agence) REFERENCES utilisateur(id_utilisateur) ON DELETE SET NULL,
    FOREIGN KEY (id_responsable) REFERENCES utilisateur(id_utilisateur) ON DELETE SET NULL
);

-- Données de test / Administrateur par défaut
INSERT INTO administrateur (nom, email, fonction) VALUES ('Admin Principal', 'admin@camed.sa', 'Superviseur IT') ON DUPLICATE KEY UPDATE nom=nom;

-- Utilisateur de test (mot de passe: 'password123' hashé)
-- Hash généré par password_hash('password123', PASSWORD_BCRYPT)
INSERT INTO utilisateur (nom, email, login, password, role) VALUES 
('Tcheumeni', 'tcheumeni@camed.sa', 'tcheumeni', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'responsable_engagement'),
('Conseiller 1', 'conseiller@camed.sa', 'conseiller', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'conseiller_client'),
('Chef Agence', 'chef@camed.sa', 'chef', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'chef_agence')
ON DUPLICATE KEY UPDATE nom=nom;

-- Client de test
INSERT INTO client (nom, prenom, telephone, email, profession) VALUES 
('Traore', 'Moussa', '655443322', 'moussa@gmail.com', 'Commerçant'),
('Kone', 'Sarah', '699887766', 'sarah@gmail.com', 'Infirmière')
ON DUPLICATE KEY UPDATE nom=nom;

-- Demandes de test
INSERT INTO demande_credit (montant, statut, date_demande, id_client, id_conseiller) VALUES 
(500000.00, 'en cours', '2026-01-20', 1, 2),
(2000000.00, 'validee', '2026-01-25', 2, 2)
ON DUPLICATE KEY UPDATE montant=montant;
