-- ============================================================
-- IAT Niger — Extension CMS (contenu entièrement éditable)
-- Import après iatniger.sql, ou via admin/install-cms.php
-- ============================================================
USE iatniger;

CREATE TABLE IF NOT EXISTS site_settings (
  cle VARCHAR(80) NOT NULL PRIMARY KEY,
  valeur TEXT NOT NULL,
  label VARCHAR(160) NOT NULL DEFAULT '',
  groupe VARCHAR(60) NOT NULL DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cms_pages (
  slug VARCHAR(80) NOT NULL PRIMARY KEY,
  titre_seo VARCHAR(255) DEFAULT NULL,
  meta_desc TEXT,
  hero_titre VARCHAR(255) DEFAULT NULL,
  hero_texte TEXT,
  contenu MEDIUMTEXT,
  maj_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cms_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(40) NOT NULL,
  groupe VARCHAR(60) DEFAULT NULL,
  cle VARCHAR(80) DEFAULT NULL,
  titre VARCHAR(255) DEFAULT NULL,
  sous_titre VARCHAR(255) DEFAULT NULL,
  contenu MEDIUMTEXT,
  extra JSON DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  url VARCHAR(500) DEFAULT NULL,
  ordre INT NOT NULL DEFAULT 0,
  publie TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE KEY uq_type_cle (type, cle),
  INDEX idx_type_groupe (type, groupe, ordre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cms_niveaux (
  slug VARCHAR(60) NOT NULL PRIMARY KEY,
  titre VARCHAR(160) NOT NULL,
  sous_titre VARCHAR(255) DEFAULT NULL,
  recrutement TEXT,
  duree VARCHAR(160) DEFAULT NULL,
  dossier TEXT,
  description TEXT,
  ordre INT NOT NULL DEFAULT 0,
  publie TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cms_formations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(120) NOT NULL UNIQUE,
  niveau VARCHAR(60) NOT NULL,
  domaine VARCHAR(40) NOT NULL DEFAULT 'tertiaire',
  titre VARCHAR(200) NOT NULL,
  icone VARCHAR(60) DEFAULT 'book-open',
  resume TEXT,
  objectif TEXT,
  debouches JSON,
  badge VARCHAR(40) DEFAULT NULL,
  ordre INT NOT NULL DEFAULT 0,
  publie TINYINT(1) NOT NULL DEFAULT 1,
  INDEX idx_niveau (niveau, ordre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
