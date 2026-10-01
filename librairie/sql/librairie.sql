-- ============================================================
--  Nemesis : base de données de la librairie
--  Cours 582-31VB-MA, Programmation Web avancée
--
--  Ce script recrée complètement la base de données :
--  tables, clés primaires, clés étrangères et données de départ.
--
--  Sur N0C, la base est créée dans cPanel (avec un préfixe).
--  Dans ce cas, retirer les lignes DROP DATABASE,
--  CREATE DATABASE et USE et importer le reste dans phpMyAdmin.
-- ============================================================

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS librairie;
CREATE DATABASE librairie CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE librairie;

-- ------------------------------------------------------------
--  Table auteur
-- ------------------------------------------------------------
CREATE TABLE auteur (
    id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    prenom          VARCHAR(80)   NOT NULL,
    nom             VARCHAR(80)   NOT NULL,
    annee_naissance SMALLINT      NULL,
    biographie      TEXT          NULL,
    PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Table editeur
-- ------------------------------------------------------------
CREATE TABLE editeur (
    id       INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nom      VARCHAR(100)  NOT NULL,
    ville    VARCHAR(80)   NULL,
    site_web VARCHAR(255)  NULL,
    PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Table categorie
-- ------------------------------------------------------------
CREATE TABLE categorie (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nom         VARCHAR(60)   NOT NULL,
    description VARCHAR(255)  NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categorie_nom (nom)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Table livre
--  Relation un-à-plusieurs : un auteur écrit plusieurs livres.
--  Relation un-à-plusieurs : un éditeur publie plusieurs livres.
-- ------------------------------------------------------------
CREATE TABLE livre (
    id                 INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    titre              VARCHAR(150)   NOT NULL,
    auteur_id          INT UNSIGNED   NOT NULL,
    editeur_id         INT UNSIGNED   NULL,
    annee_publication  SMALLINT       NULL,
    prix               DECIMAL(6,2)   NOT NULL DEFAULT 0.00,
    quantite_stock     INT UNSIGNED   NOT NULL DEFAULT 0,
    resume             TEXT           NULL,
    couleur_couverture CHAR(7)        NOT NULL DEFAULT '#5C0F1A',
    PRIMARY KEY (id),
    CONSTRAINT fk_livre_auteur
        FOREIGN KEY (auteur_id) REFERENCES auteur (id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_livre_editeur
        FOREIGN KEY (editeur_id) REFERENCES editeur (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Table livre_categorie (table de liaison)
--  Relation plusieurs-à-plusieurs : un livre appartient à
--  plusieurs catégories et une catégorie contient plusieurs livres.
-- ------------------------------------------------------------
CREATE TABLE livre_categorie (
    livre_id     INT UNSIGNED NOT NULL,
    categorie_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (livre_id, categorie_id),
    CONSTRAINT fk_lc_livre
        FOREIGN KEY (livre_id) REFERENCES livre (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_lc_categorie
        FOREIGN KEY (categorie_id) REFERENCES categorie (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ============================================================
--  Données de départ
-- ============================================================

INSERT INTO auteur (id, prenom, nom, annee_naissance, biographie) VALUES
(1, 'Mary',    'Shelley',    1797, 'Romancière anglaise qui a imaginé Frankenstein très jeune, pendant un été pluvieux en Suisse.'),
(2, 'Bram',    'Stoker',     1847, 'Écrivain irlandais, créateur du comte Dracula.'),
(3, 'Edgar Allan', 'Poe',    1809, 'Poète et nouvelliste américain, maître du récit fantastique et macabre.'),
(4, 'Charles', 'Baudelaire', 1821, 'Poète français et traducteur de Poe.'),
(5, 'Anne',    'Rice',       1941, 'Romancière américaine, autrice des Chroniques des vampires.'),
(6, 'Patrick', 'Senécal',    1967, 'Romancier québécois, figure incontournable de l''horreur d''ici.'),
(7, 'Guy',     'de Maupassant', 1850, 'Écrivain français, auteur de nombreuses nouvelles, dont plusieurs fantastiques.');

INSERT INTO editeur (id, nom, ville, site_web) VALUES
(1, 'Alire',              'Lévis', NULL),
(2, 'Folio',              'Paris', NULL),
(3, 'Le Livre de Poche',  'Paris', NULL),
(4, 'Pocket',             'Paris', NULL);

INSERT INTO categorie (id, nom, description) VALUES
(1, 'Horreur',     'Récits conçus pour faire peur.'),
(2, 'Fantastique', 'Quand le surnaturel s''invite dans le quotidien.'),
(3, 'Gothique',    'Châteaux, ruines, secrets et atmosphères sombres.'),
(4, 'Vampires',    'Créatures de la nuit, immortelles et assoiffées.'),
(5, 'Poésie',      'Recueils de poèmes.'),
(6, 'Classique',   'Œuvres qui ont traversé les siècles.');

INSERT INTO livre (id, titre, auteur_id, editeur_id, annee_publication, prix, quantite_stock, resume, couleur_couverture) VALUES
(1, 'Frankenstein',               1, 2, 1818, 12.95, 5, 'Un jeune savant donne vie à une créature, puis l''abandonne aussitôt.', '#2E3B2A'),
(2, 'Dracula',                    2, 3, 1897, 13.95, 2, 'En visite en Transylvanie, un clerc de notaire découvre la vraie nature de son hôte.', '#5C0F1A'),
(3, 'Histoires extraordinaires',  3, 2, 1856, 11.95, 6, 'Nouvelles d''Edgar Allan Poe traduites par Charles Baudelaire.', '#1E1A2B'),
(4, 'Les Fleurs du mal',          4, 3, 1857,  8.95, 7, 'Le recueil de poèmes de Baudelaire, entre spleen et idéal.', '#3B1230'),
(5, 'Entretien avec un vampire',  5, 4, 1976, 14.95, 1, 'Un vampire raconte sa longue existence à un jeune journaliste.', '#4A1942'),
(6, 'Aliss',                      6, 1, 2000, 19.95, 4, 'Une adolescente quitte sa banlieue pour un quartier de Montréal où tout semble permis.', '#7A1E2B'),
(7, 'Les Sept Jours du talion',   6, 1, 2002, 18.95, 3, 'Un chirurgien enlève le meurtrier de sa fille et annonce qu''il le torturera pendant sept jours.', '#3A2A1E'),
(8, 'Le Horla',                   7, 2, 1887,  5.95, 0, 'Dans son journal, un homme se convainc peu à peu qu''un être invisible le hante.', '#C9B89A');

INSERT INTO livre_categorie (livre_id, categorie_id) VALUES
(1, 2), (1, 3), (1, 6),
(2, 1), (2, 3), (2, 4), (2, 6),
(3, 1), (3, 2), (3, 6),
(4, 5), (4, 6),
(5, 2), (5, 4),
(6, 1), (6, 2),
(7, 1),
(8, 2), (8, 6);
