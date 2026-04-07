-- ============================================================
--  Base de données : alertroute
--  Projet         : AlertRoute - Signalement de problèmes urbains
--  Compatible avec: formulaire.php, login.php, search.php,
--                   all_signalements.php, alertlocal.php, report_details.php
-- ============================================================

DROP DATABASE IF EXISTS alertroute;
CREATE DATABASE alertroute
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE alertroute;

-- ============================================================
-- TABLE : ville
-- Utilisée dans : formulaire.php → SELECT id, nom FROM ville
-- ============================================================
CREATE TABLE ville (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom  VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO ville (nom) VALUES
  ('Nouakchott'),
  ('Nouadhibou'),
  ('Rosso'),
  ('Kaédi'),
  ('Atar'),
  ('Kiffa'),
  ('Zouerate'),
  ('Sélibaby');

-- ============================================================
-- TABLE : quartier
-- Utilisée dans : formulaire.php → SELECT id, nom FROM quartier WHERE nom=? AND ville_id=?
--                 search.php    → JOIN Quartier q ON s.quartier_id = q.id
-- ============================================================
CREATE TABLE quartier (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom       VARCHAR(100) NOT NULL,
  ville_id  INT UNSIGNED NOT NULL,
  latitude  DECIMAL(10,7),
  longitude DECIMAL(10,7),
  FOREIGN KEY (ville_id) REFERENCES ville(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO quartier (nom, ville_id, latitude, longitude) VALUES
  -- Nouakchott (id=1)
  ('Tevragh-Zeina',   1, 18.1000, -16.0167),
  ('Ksar',            1, 18.1000, -15.9833),
  ('Sebkha',          1, 18.0667, -16.0000),
  ('Hay Salam',       1, 18.0712, -15.9580),
  ('Hay Saken',       1, 18.1100, -15.9750),
  ('Toujounine',      1, 18.0833, -15.9333),
  ('Dar Naim',        1, 18.1167, -15.9333),
  ('Arafat',          1, 18.0500, -15.9667),
  ('El Mina',         1, 18.0833, -15.9833),
  ('Riyad',           1, 18.0667, -15.9500),
  ('Teyarett',        1, 18.1333, -15.9500),
  -- Nouadhibou (id=2)
  ('Baghdad',         2, 20.9310, -17.0340),
  ('Numerot',         2, 20.9250, -17.0410),
  ('Cansado',         2, 20.8833, -17.0500),
  -- Rosso (id=3)
  ('Centre Rosso',    3, 16.5128, -15.8049),
  -- Kaédi (id=4)
  ('Centre Kaédi',    4, 16.1500, -13.5000);

-- ============================================================
-- TABLE : typeprobleme
-- Utilisée dans : formulaire.php → SELECT nom FROM typeprobleme
--                 search.php    → JOIN TypeProbleme t ON s.type_probleme_id = t.id
-- ============================================================
CREATE TABLE typeprobleme (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom  VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO typeprobleme (nom) VALUES
  ('Nid-de-poule'),
  ('Route inondée'),
  ('Trottoir endommagé'),
  ('Chaussée dégradée'),
  ('Lampadaire défectueux'),
  ('Éclairage public insuffisant'),
  ('Feu de circulation en panne'),
  ('Fuite d\'eau'),
  ('Canalisation bouchée'),
  ('Dépôt sauvage'),
  ('Déchets encombrants'),
  ('Graffiti'),
  ('Poubelle débordante'),
  ('Banc cassé'),
  ('Panneau de signalisation manquant'),
  ('Arbre tombé'),
  ('Branche dangereuse'),
  ('Végétation envahissante obstruant le passage'),
  ('Mobilier urbain cassé dans le parc'),
  ('Signalisation routière effacée'),
  ('Autre');

-- ============================================================
-- TABLE : photo
-- Utilisée dans : formulaire.php → INSERT INTO photo (nom, url)
-- ============================================================
CREATE TABLE photo (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom  VARCHAR(255),
  url  VARCHAR(500)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Photos de démonstration (liées aux images dans /images/)
INSERT INTO photo (nom, url) VALUES
  ('Nid-de-poule profond sur la route principale.',          'images/Nid-de-poule profond sur la route principale..jpg'),
  ('Graffiti sur le mur de l école.',                        'images/Graffiti sur le mur de l école..jpg'),
  ('Lampadaire ne fonctionne plus depuis une semaine.',      'images/Lampadaire ne fonctionne plus depuis une semaine..jpg'),
  ('Déchets encombrants abandonnés sur le trottoir.',        'images/Déchets encombrants abandonnés sur le trottoir..jpg'),
  ('Fuite d eau sur la chaussée.',                           'images/Fuite d eau sur la chaussée..jpg'),
  ('Trottoir endommagé causant des accidents.',              'images/Trottoir endommagé causant des accidents..jpg'),
  ('Panneau de signalisation manquant.',                     'images/Panneau de signalisation manquant..jpg'),
  ('Poubelle débordante.',                                   'images/Déchets encombrants abandonnés sur le trottoir..jpg'),
  ('Route inondée après la pluie.',                          'images/Route inondée après la pluie..jpg'),
  ('Arbre tombé bloquant la route.',                         'images/Arbre tombé bloquant la route..jpg'),
  ('Feu de circulation en panne au carrefour.',              'images/Feu de circulation en panne au carrefour..jpg'),
  ('Mobilier urbain cassé dans le parc.',                    'images/Mobilier urbain cassé dans le parc..jpg'),
  ('Végétation envahissante obstruant le passage.',          'images/Végétation envahissante obstruant le passage..jpg'),
  ('Dépôt sauvage de déchets près du marché.',              'images/Dépôt sauvage de déchets près du marché..jpg'),
  ('Éclairage public insuffisant dans la rue.',              'images/Éclairage public insuffisant dans la rue..jpg');

-- ============================================================
-- TABLE : signalement
-- Utilisée dans : formulaire.php → INSERT INTO signalement(...)
--                 all_signalements.php → SELECT/UPDATE/DELETE
--                 alertlocal.php → SELECT * FROM signalement
--                 search.php → SELECT avec JOIN
--                 update_danger_levels.php → UPDATE niveau_de_gravite
-- ============================================================
CREATE TABLE signalement (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  description       TEXT NOT NULL,
  date_signalement  DATE NOT NULL,
  type_probleme_id  INT UNSIGNED,
  photo_id          INT UNSIGNED DEFAULT NULL,
  photo_id2         INT UNSIGNED DEFAULT NULL,
  photo_id3         INT UNSIGNED DEFAULT NULL,
  quartier_id       INT UNSIGNED,
  etat              ENUM('nouveau','en_cours','resolu','rejete') NOT NULL DEFAULT 'nouveau',
  niveau_de_gravite ENUM('faible','moyen','eleve') NOT NULL DEFAULT 'faible',
  latitude          DECIMAL(10,7),
  longitude         DECIMAL(10,7),
  utilisateur_id    INT UNSIGNED DEFAULT NULL,
  image_path        VARCHAR(500) DEFAULT NULL,
  FOREIGN KEY (type_probleme_id) REFERENCES typeprobleme(id) ON DELETE SET NULL,
  FOREIGN KEY (quartier_id)      REFERENCES quartier(id)      ON DELETE SET NULL,
  FOREIGN KEY (photo_id)         REFERENCES photo(id)          ON DELETE SET NULL,
  FOREIGN KEY (photo_id2)        REFERENCES photo(id)          ON DELETE SET NULL,
  FOREIGN KEY (photo_id3)        REFERENCES photo(id)          ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO signalement (description, date_signalement, type_probleme_id, photo_id, quartier_id, etat, niveau_de_gravite, latitude, longitude) VALUES
  ('Nid-de-poule profond sur la route principale.',          '2025-05-01',  1,  1,  1,  'resolu',   'eleve',  18.1174, -15.9810),
  ('Graffiti sur le mur de l\'école primaire.',              '2025-05-02',  12, 2,  2,  'en_cours', 'faible', 18.0932, -15.9736),
  ('Lampadaire ne fonctionne plus depuis une semaine.',      '2025-05-03',  5,  3,  3,  'nouveau',  'moyen',  18.0801, -15.9654),
  ('Déchets encombrants abandonnés devant la mosquée.',      '2025-05-04',  11, 4,  4,  'nouveau',  'moyen',  18.0712, -15.9580),
  ('Fuite d\'eau importante sur la chaussée.',               '2025-05-05',  8,  5,  1,  'en_cours', 'eleve',  18.1200, -15.9820),
  ('Trottoir complètement défoncé, dangereux pour piétons.', '2025-05-06',  3,  6,  6,  'nouveau',  'eleve',  18.0600, -15.9500),
  ('Panneau STOP manquant au carrefour dangereux.',          '2025-05-08',  15, 7,  1,  'resolu',   'eleve',  18.1180, -15.9805),
  ('Poubelle publique débordante depuis 3 jours.',           '2025-05-09',  13, 8,  8,  'en_cours', 'moyen',  18.0450, -15.9350),
  ('Fuite d\'eau sur la chaussée principale.',               '2025-05-10',  8,  5,  12, 'nouveau',  'eleve',  20.9310, -17.0340),
  ('Éclairage absent toute la nuit dans la rue.',            '2025-05-11',  6,  15, 9,  'nouveau',  'moyen',  18.1050, -16.0100),
  ('Gros arbre tombé qui bloque complètement la route.',     '2025-05-12',  16, 10, 5,  'resolu',   'eleve',  18.1100, -15.9750),
  ('Dépôt sauvage d\'ordures derrière le stade.',           '2025-05-13',  10, 14, 3,  'nouveau',  'moyen',  18.0810, -15.9640),
  ('Route inondée après les pluies, véhicules bloqués.',     '2025-05-14',  2,  9,  4,  'en_cours', 'eleve',  18.0712, -15.9580),
  ('Feu de circulation en panne au carrefour principal.',    '2025-05-15',  7,  11, 1,  'en_cours', 'eleve',  18.1174, -15.9810),
  ('Chaussée très dégradée avec fissures profondes.',        '2025-05-16',  4,  NULL,2, 'nouveau',  'moyen',  18.0940, -15.9730),
  ('Banc public cassé, risque de blessure.',                 '2025-05-17',  14, 12, 10, 'nouveau',  'faible', 18.0900, -15.9900),
  ('Plusieurs nids-de-poule consécutifs sur 200m.',          '2025-05-18',  1,  1,  6,  'en_cours', 'eleve',  18.0610, -15.9510),
  ('Tags et graffitis sur le mur de la mosquée.',            '2025-05-19',  12, 2,  7,  'nouveau',  'faible', 18.1310, -15.9590),
  ('Arbre tombé bloquant la route secondaire.',              '2025-07-16',  16, 10, 5,  'resolu',   'eleve',  18.1174, -15.9810),
  ('Déchets encombrants abandonnés sur le trottoir.',        '2025-07-16',  11, 4,  1,  'nouveau',  'moyen',  18.1180, -15.9800),
  ('Dépôt sauvage d\'ordures devant un commerce.',          '2025-07-16',  10, 14, 1,  'nouveau',  'moyen',  18.1170, -15.9820),
  ('Éclairage public insuffisant dans toute la rue.',        '2025-07-16',  6,  15, 1,  'en_cours', 'moyen',  18.1160, -15.9815),
  ('Feu de circulation en panne au carrefour.',              '2025-07-16',  7,  11, 1,  'en_cours', 'eleve',  18.1174, -15.9810),
  ('Fuite d\'eau sur la chaussée, eau gaspillée.',          '2025-07-16',  8,  5,  1,  'nouveau',  'eleve',  18.1185, -15.9808),
  ('Graffiti sur le mur de l\'école publique.',             '2025-07-16',  12, 2,  1,  'nouveau',  'faible', 18.1165, -15.9825),
  ('Signalisation routière effacée sur avenue principale.',  '2025-07-17',  20, NULL,2,  'nouveau',  'moyen',  18.0945, -15.9720),
  ('Végétation envahissante qui obstrue le trottoir.',       '2025-07-17',  18, 13, 7,  'nouveau',  'faible', 18.1305, -15.9595);

-- ============================================================
-- TABLE : connexion
-- Utilisée dans : login.php → SELECT * FROM connexion WHERE email=?
-- Colonnes : id, email, mot_de_passe, role
-- ⚠️  Ton code compare les mots de passe en clair (pas de hash)
-- ============================================================
CREATE TABLE connexion (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email        VARCHAR(150) NOT NULL UNIQUE,
  mot_de_passe VARCHAR(255) NOT NULL,
  role         ENUM('admin','moderateur','citoyen') NOT NULL DEFAULT 'citoyen'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO connexion (email, mot_de_passe, role) VALUES
  ('admin@alertroute.mr',  'Admin2025!',  'admin'),
  ('modo@alertroute.mr',   'Modo2025!',   'moderateur'),
  ('test@alertroute.mr',   'test1234',    'citoyen');

-- ============================================================
-- TABLE : subscriptions
-- Utilisée dans : alertlocal.php → INSERT INTO subscriptions(...)
-- ============================================================
CREATE TABLE subscriptions (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email             VARCHAR(150) NOT NULL,
  subscription_type VARCHAR(50),
  radius            DECIMAL(8,4) DEFAULT 3.4,
  latitude          DECIMAL(10,7) DEFAULT 18.0858,
  longitude         DECIMAL(10,7) DEFAULT -15.9785,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- VUE : vue_signalements_complets
-- Pratique pour récupérer toutes les infos en une seule requête
-- ============================================================
CREATE OR REPLACE VIEW vue_signalements_complets AS
SELECT
  s.id,
  s.description,
  s.date_signalement,
  s.etat,
  s.niveau_de_gravite,
  s.latitude,
  s.longitude,
  s.image_path,
  t.nom           AS type_probleme,
  q.nom           AS quartier,
  v.nom           AS ville,
  p.url           AS photo_url,
  p2.url          AS photo2_url,
  p3.url          AS photo3_url
FROM signalement s
LEFT JOIN typeprobleme t  ON t.id = s.type_probleme_id
LEFT JOIN quartier     q  ON q.id = s.quartier_id
LEFT JOIN ville        v  ON v.id = q.ville_id
LEFT JOIN photo        p  ON p.id = s.photo_id
LEFT JOIN photo        p2 ON p2.id = s.photo_id2
LEFT JOIN photo        p3 ON p3.id = s.photo_id3;

-- ============================================================
-- RÉSUMÉ DES COMPTES DE CONNEXION
-- ============================================================
-- Admin   : admin@alertroute.mr  / Admin2025!
-- Modérat.: modo@alertroute.mr   / Modo2025!
-- Test    : test@alertroute.mr   / test1234
-- ============================================================
         
