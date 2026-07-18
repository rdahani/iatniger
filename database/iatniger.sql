-- ============================================================
-- IAT Niger — Schéma et données initiales
-- Import : phpMyAdmin (http://localhost/phpmyadmin) ou
--   mysql -u root < database/iatniger.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS iatniger CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE iatniger;

-- ---------- Utilisateurs (administration) ----------
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  nom VARCHAR(120) NOT NULL,
  role ENUM('admin','communication','scolarite','personnalise') NOT NULL DEFAULT 'admin',
  permissions JSON DEFAULT NULL,
  cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Mot de passe par défaut : IatNiger#2026  (À CHANGER dès la première connexion)
INSERT INTO users (username, password_hash, nom, role) VALUES
('admin', '$2y$10$P8Si07f3oFJ8R6Xn//lgsOaH.2ogrCXkvkzK.WadoKpdxIytQINhO', 'Administrateur IAT', 'admin')
ON DUPLICATE KEY UPDATE username = username;

-- ---------- Actualités ----------
CREATE TABLE IF NOT EXISTS actualites (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(180) NOT NULL UNIQUE,
  titre VARCHAR(255) NOT NULL,
  categorie VARCHAR(60) NOT NULL DEFAULT 'Actualité',
  extrait TEXT NOT NULL,
  contenu MEDIUMTEXT NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  date_publication DATE NOT NULL,
  publie TINYINT(1) NOT NULL DEFAULT 1,
  cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_date (date_publication),
  INDEX idx_publie (publie)
) ENGINE=InnoDB;

INSERT INTO actualites (slug, titre, categorie, extrait, contenu, image, date_publication) VALUES
('projet-hub-peering-federe', 'Projet Hub de Peering Fédéré : l''IAT renforce ses infrastructures numériques', 'Infrastructure',
 'L''IAT a participé à la cérémonie de remise de serveurs virtuels et d''équipements réseau organisée par Niger-REN pour accélérer la transformation numérique de l''enseignement supérieur.',
 'Dans le cadre de l''initiative de Niger-REN (Réseau national pour l''éducation et la recherche), l''Institut Africain de Technologie a pris part à une cérémonie de remise de serveurs virtuels et d''équipements réseau. Ce projet de Hub de Peering Fédéré vise à accélérer la transformation numérique des établissements d''enseignement supérieur du Niger.\n\nGrâce à ces nouveaux équipements, l''IAT renforce ses infrastructures numériques au bénéfice direct de ses étudiants et enseignants : meilleure connectivité, hébergement de services pédagogiques et ouverture sur les réseaux académiques régionaux.',
 'actualites/hub-peering.jpg', '2026-07-03'),
('sensibilisation-violences-genre', 'L''IAT aux côtés du Club PPF pour sensibiliser à la lutte contre les violences basées sur le genre', 'Vie étudiante',
 'L''administration de l''IAT a accompagné le Club PPF pour une journée de sensibilisation consacrée à la lutte contre les violences basées sur le genre.',
 'L''administration de l''Institut Africain de Technologie a accompagné le Club PPF (Participation Politique des Femmes) dans l''organisation d''une journée de sensibilisation consacrée à la lutte contre les violences basées sur le genre.\n\nCette initiative s''inscrit dans l''engagement citoyen de l''institut et dans sa volonté de former des diplômés responsables, acteurs du changement social dans leurs communautés.',
 'actualites/sensibilisation-vbg.jpg', '2026-06-30'),
('alkalami-dor-2026', 'Alkalami d''Or 2026 : l''IAT Niger récompensé pour son excellence académique', 'Distinction',
 'L''IAT Niger a été récompensé lors de la deuxième édition du gala « Alkalami d''Or 2026 » pour son excellence académique et ses contributions au secteur éducatif.',
 'L''Institut Africain de Technologie a été distingué lors de la deuxième édition du gala « Alkalami d''Or 2026 », qui récompense les acteurs majeurs du secteur éducatif nigérien.\n\nCette distinction vient saluer l''excellence académique de l''institut, la qualité de ses formations accréditées CAMES et sa contribution constante à la formation des cadres du Niger depuis plus de 25 ans. Elle s''ajoute à l''Arch of Europe Award reçu en 2020.',
 'actualites/alkalami-dor.jpg', '2026-06-28'),
('girls-in-ict-2026', 'Girls in ICT 2026 : immersion des étudiantes de l''IAT dans l''univers des télécommunications', 'Partenariat',
 'Une délégation d''étudiantes de l''IAT a effectué une visite pédagogique chez ATC Niger pour découvrir l''univers des télécommunications.',
 'À l''occasion de la journée internationale « Girls in ICT », une délégation d''étudiantes de l''Institut Africain de Technologie a effectué une visite pédagogique chez ATC Niger.\n\nAu programme : découverte des infrastructures de télécommunications, échanges avec des professionnelles du secteur et encouragement des vocations féminines dans les métiers du numérique — un axe fort de la politique d''ouverture de l''IAT.',
 'actualites/girls-in-ict.jpg', '2026-05-20'),
('journee-femme-nigerienne-don-sang', 'Journée Nationale de la Femme Nigérienne : opération de don de sang au CNTS', 'Vie étudiante',
 'La CONGAFEN a organisé une opération de don de sang au CNTS en présence de la Ministre de la Population, avec la participation de l''IAT.',
 'À l''occasion de la Journée Nationale de la Femme Nigérienne, la CONGAFEN a organisé une opération de don de sang au Centre National de Transfusion Sanguine (CNTS), en présence de la Ministre de la Population.\n\nLa communauté de l''IAT s''est mobilisée pour cette action solidaire, fidèle à ses valeurs d''engagement citoyen et de service à la communauté.',
 'actualites/don-de-sang.jpg', '2026-05-20'),
('appui-club-ppf', 'Appui au Club PPF de l''IAT : un pas de plus pour l''engagement citoyen', 'Vie étudiante',
 'Le club PPF a reçu du matériel de sonorisation et des équipements pour renforcer ses activités de sensibilisation sur la participation politique des femmes.',
 'Le Club PPF de l''IAT a reçu du matériel de sonorisation et des équipements destinés à renforcer ses activités de sensibilisation, dans le cadre du projet sur la participation politique des femmes.\n\nCet appui matériel permettra au club d''amplifier ses actions sur le campus et au-delà, et confirme la place de la vie associative dans le projet pédagogique de l''institut.',
 'actualites/club-ppf-appui.jpg', '2026-02-26'),
('lancement-deux-laboratoires', 'Lancement officiel de deux nouveaux laboratoires à l''IAT', 'Infrastructure',
 'L''IAT a lancé officiellement deux laboratoires modernes dédiés aux filières Génie Électrique et Génie Civil, un renforcement majeur de la formation technique.',
 'L''Institut Africain de Technologie a procédé au lancement officiel de deux laboratoires modernes dédiés aux filières Génie Électrique et Génie Civil.\n\nCes infrastructures de pointe marquent un renforcement majeur de la formation technique et pratique offerte aux étudiants : travaux dirigés sur équipements professionnels, projets appliqués et meilleure employabilité des diplômés.',
 'actualites/laboratoires-lancement.jpg', '2026-02-02'),
('labo-genie-electrique-operationnel', 'IAT Niger : le laboratoire de Génie Électrique désormais pleinement opérationnel', 'Infrastructure',
 'Le laboratoire de Génie Électrique de l''IAT Niger est devenu complètement opérationnel, renforçant les capacités de formation technique de l''institution.',
 'Le laboratoire de Génie Électrique de l''IAT Niger est désormais pleinement opérationnel.\n\nLes étudiants des filières industrielles y réalisent leurs travaux pratiques sur des équipements professionnels, consolidant l''approche « apprendre en faisant » qui fait la réputation de l''institut.',
 'actualites/labo-genie-electrique.jpg', '2026-01-25'),
('forage-eau-firdaous-charity', 'L''ONG Firdaous Charity dote l''IAT d''un forage d''eau potable', 'Partenariat',
 'L''ONG Firdaous Charity a offert un forage d''eau potable à l''IAT Niger, une initiative saluée pour son impact durable sur la communauté éducative.',
 'L''ONG Firdaous Charity a offert un forage d''eau potable à l''Institut Africain de Technologie.\n\nCette initiative, saluée par l''ensemble de la communauté éducative, améliore durablement les conditions d''études sur le campus et illustre la qualité des partenariats noués par l''institut.',
 'actualites/forage-eau.jpg', '2025-12-30')
ON DUPLICATE KEY UPDATE slug = slug;

-- ---------- Messages de contact ----------
CREATE TABLE IF NOT EXISTS messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL,
  telephone VARCHAR(40) DEFAULT NULL,
  sujet VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  lu TINYINT(1) NOT NULL DEFAULT 0,
  recu_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- Préinscriptions ----------
CREATE TABLE IF NOT EXISTS preinscriptions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(120) NOT NULL,
  prenom VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL,
  telephone VARCHAR(40) NOT NULL,
  niveau VARCHAR(40) NOT NULL,
  formation VARCHAR(160) NOT NULL,
  dernier_diplome VARCHAR(160) DEFAULT NULL,
  message TEXT,
  traite TINYINT(1) NOT NULL DEFAULT 0,
  recu_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- Newsletter ----------
CREATE TABLE IF NOT EXISTS newsletter (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(160) NOT NULL UNIQUE,
  inscrit_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
