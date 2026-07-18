<?php
/**
 * Installation et alimentation du contenu CMS.
 *
 * cms_install_and_seed() crée les tables (si besoin) et insère le contenu
 * de démarrage à partir des pages publiques actuelles / constantes.
 *
 * Comportement d'idempotence :
 *  - $force = false (par défaut) : n'ajoute que ce qui manque, ne touche jamais
 *    au contenu déjà présent (donc sans danger si l'admin a déjà modifié le CMS).
 *  - $force = true : réinitialise le contenu de démarrage (écrase les valeurs
 *    des clés connues et régénère les blocs d'éléments sans clé unique).
 *
 * Utilisé par admin/install-cms.php (HTTP) et database/seed-cms.php (CLI).
 */

declare(strict_types=1);

/** Crée les tables CMS si elles n'existent pas encore. */
function cms_seed_create_tables(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        cle VARCHAR(80) NOT NULL PRIMARY KEY,
        valeur TEXT NOT NULL,
        label VARCHAR(160) NOT NULL DEFAULT '',
        groupe VARCHAR(60) NOT NULL DEFAULT 'general'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS cms_pages (
        slug VARCHAR(80) NOT NULL PRIMARY KEY,
        titre_seo VARCHAR(255) DEFAULT NULL,
        meta_desc TEXT,
        hero_titre VARCHAR(255) DEFAULT NULL,
        hero_texte TEXT,
        contenu MEDIUMTEXT,
        maj_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS cms_items (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS cms_niveaux (
        slug VARCHAR(60) NOT NULL PRIMARY KEY,
        titre VARCHAR(160) NOT NULL,
        sous_titre VARCHAR(255) DEFAULT NULL,
        recrutement TEXT,
        duree VARCHAR(160) DEFAULT NULL,
        dossier TEXT,
        description TEXT,
        ordre INT NOT NULL DEFAULT 0,
        publie TINYINT(1) NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS cms_formations (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

/** Upsert générique sur une table à clé primaire simple (1 colonne). */
function cms_seed_upsert_row(PDO $pdo, string $table, string $keyCol, string $keyVal, array $data, bool $force): void
{
    $cols = array_keys($data);
    $allCols = array_merge([$keyCol], $cols);
    $allVals = array_merge([$keyVal], array_values($data));
    $placeholders = implode(',', array_fill(0, count($allCols), '?'));
    $colsSql = implode(',', $allCols);
    if ($force) {
        $updates = implode(',', array_map(static fn ($c) => "$c = VALUES($c)", $cols));
        $sql = "INSERT INTO $table ($colsSql) VALUES ($placeholders) ON DUPLICATE KEY UPDATE $updates";
    } else {
        $sql = "INSERT IGNORE INTO $table ($colsSql) VALUES ($placeholders)";
    }
    $pdo->prepare($sql)->execute($allVals);
}

/** Upsert d'un cms_items identifié par (type, cle) — pour les contenus uniques (textes, réglages de section...). */
function cms_seed_item_keyed(PDO $pdo, string $type, string $cle, array $data, bool $force): void
{
    if (isset($data['extra']) && is_array($data['extra'])) {
        $data['extra'] = cms_extra_encode($data['extra']);
    }
    $cols = ['type', 'cle', 'groupe', 'titre', 'sous_titre', 'contenu', 'extra', 'image', 'url', 'ordre', 'publie'];
    $base = ['type' => $type, 'cle' => $cle, 'groupe' => null, 'titre' => null, 'sous_titre' => null,
        'contenu' => null, 'extra' => null, 'image' => null, 'url' => null, 'ordre' => 0, 'publie' => 1];
    $row = array_merge($base, $data);
    $vals = [];
    foreach ($cols as $c) {
        $vals[] = $row[$c];
    }
    $placeholders = implode(',', array_fill(0, count($cols), '?'));
    $colsSql = implode(',', $cols);
    if ($force) {
        $updatable = array_diff($cols, ['type', 'cle']);
        $updates = implode(',', array_map(static fn ($c) => "$c = VALUES($c)", $updatable));
        $sql = "INSERT INTO cms_items ($colsSql) VALUES ($placeholders) ON DUPLICATE KEY UPDATE $updates";
    } else {
        $sql = "INSERT IGNORE INTO cms_items ($colsSql) VALUES ($placeholders)";
    }
    $pdo->prepare($sql)->execute($vals);
}

/**
 * Sème un bloc de cms_items sans clé unique (faq, partenaire, galerie...).
 * Si des lignes existent déjà pour ce type (et ce groupe, s'il est précisé) :
 * ignoré sauf si $force (dans ce cas, le bloc est vidé puis reconstruit).
 * $groupe = null signifie "vérifier/vider au niveau du type entier", utile
 * quand les lignes du bloc portent chacune leur propre groupe (ex. galerie).
 * Retourne le nombre de lignes insérées.
 */
function cms_seed_item_block(PDO $pdo, string $type, ?string $groupe, array $rows, bool $force): int
{
    $whereSql = 'type = ?';
    $params = [$type];
    if ($groupe !== null) {
        $whereSql .= ' AND groupe = ?';
        $params[] = $groupe;
    }
    $st = $pdo->prepare("SELECT COUNT(*) FROM cms_items WHERE $whereSql");
    $st->execute($params);
    $count = (int) $st->fetchColumn();

    if ($count > 0 && !$force) {
        return 0;
    }
    if ($count > 0 && $force) {
        $pdo->prepare("DELETE FROM cms_items WHERE $whereSql")->execute($params);
    }

    $cols = ['type', 'groupe', 'cle', 'titre', 'sous_titre', 'contenu', 'extra', 'image', 'url', 'ordre', 'publie'];
    $ins = $pdo->prepare('INSERT INTO cms_items (' . implode(',', $cols) . ') VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')');
    $ordre = 0;
    $n = 0;
    foreach ($rows as $r) {
        $base = ['type' => $type, 'groupe' => $groupe, 'cle' => null, 'titre' => null, 'sous_titre' => null,
            'contenu' => null, 'extra' => null, 'image' => null, 'url' => null, 'ordre' => $ordre, 'publie' => 1];
        $row = array_merge($base, $r);
        if (isset($row['extra']) && is_array($row['extra'])) {
            $row['extra'] = cms_extra_encode($row['extra']);
        }
        $vals = [];
        foreach ($cols as $c) {
            $vals[] = $row[$c];
        }
        $ins->execute($vals);
        $ordre++;
        $n++;
    }
    return $n;
}

/** Paramètres du site (à partir des constantes SITE_* + textes de pied de page). */
function cms_seed_settings(PDO $pdo, bool $force): int
{
    $settings = [
        ['site_name', defined('SITE_NAME') ? SITE_NAME : 'IAT Niger', 'Nom court du site', 'identite'],
        ['site_full_name', defined('SITE_FULL_NAME') ? SITE_FULL_NAME : 'Institut Africain de Technologie', 'Nom complet', 'identite'],
        ['site_tagline', defined('SITE_TAGLINE') ? SITE_TAGLINE : "Un pôle d'excellence", 'Slogan', 'identite'],
        ['site_email', defined('SITE_EMAIL') ? SITE_EMAIL : 'info@iatniger.org', 'E-mail de contact', 'contact'],
        ['site_phone_1', defined('SITE_PHONE_1') ? SITE_PHONE_1 : '', 'Téléphone principal', 'contact'],
        ['site_phone_2', defined('SITE_PHONE_2') ? SITE_PHONE_2 : '', 'Téléphone secondaire', 'contact'],
        ['site_whatsapp', defined('SITE_WHATSAPP') ? SITE_WHATSAPP : '', 'WhatsApp (indicatif + numéro, sans + ni espaces)', 'contact'],
        ['site_address', defined('SITE_ADDRESS') ? SITE_ADDRESS : '', 'Adresse postale', 'contact'],
        ['site_facebook', defined('SITE_FACEBOOK') ? SITE_FACEBOOK : '', 'Lien Facebook', 'contact'],
        ['footer_cta_titre', "Prêt·e à rejoindre un pôle d'excellence ?", 'Titre du bandeau CTA (pied de page)', 'footer'],
        ['footer_cta_texte', 'Inscriptions ouvertes — BTS, Licences, Masters et Doctorat. Rejoignez plus de 30 000 diplômés.', 'Texte du bandeau CTA (pied de page)', 'footer'],
        ['footer_about', "L'Institut Africain de Technologie forme depuis 1999 les cadres et techniciens qui construisent le Niger et l'Afrique de demain.", 'Texte « à propos » du pied de page', 'footer'],
        ['footer_mention', "Agréé par arrêtés N° 0143 & 0233/MEN/DEPRI/DETFP (1999) · Diplômes accrédités CAMES / ANAQ-SUP", 'Mention légale du pied de page', 'footer'],
    ];
    $n = 0;
    foreach ($settings as [$cle, $valeur, $label, $groupe]) {
        cms_seed_upsert_row($pdo, 'site_settings', 'cle', $cle, ['valeur' => $valeur, 'label' => $label, 'groupe' => $groupe], $force);
        $n++;
    }
    return $n;
}

/** Pages CMS (SEO + bandeau d'en-tête) pour toutes les pages publiques. */
function cms_seed_pages(PDO $pdo, bool $force): int
{
    $pages = [
        'accueil' => [
            'IAT Niger — Institut Africain de Technologie | Un pôle d\'excellence à Niamey',
            "BTS, Licences, Masters et Doctorat accrédités CAMES. Depuis 1999, l'IAT Niger a formé plus de 30 000 diplômés à Niamey. Inscriptions ouvertes.",
            "Construisez votre avenir dans un pôle d'excellence",
            "L'Institut Africain de Technologie forme les cadres et techniciens qui transforment le Niger et l'Afrique : 28 filières du Bac Pro au Doctorat, des laboratoires modernes et 25 ans d'expérience.",
        ],
        'a-propos' => [
            'À propos — Histoire, mission et vision | IAT Niger',
            "Créé en 1999, l'Institut Africain de Technologie a formé plus de 30 000 diplômés. Découvrez son histoire, sa mission, ses valeurs et son corps enseignant de rang A.",
            "Un pôle d'excellence au service de l'Afrique depuis 1999",
            "De quelques salles louées au rond-point Gadafawa à un institut accrédité CAMES fort de 30 000 diplômés : l'histoire d'une ambition africaine.",
        ],
        'partenaires' => [
            'Partenaires — Un réseau au service de nos étudiants | IAT Niger',
            "ANPE, EMIG, ESSEC Douala, IST, HCR, CIPMEN… Découvrez les partenaires institutionnels, académiques et privés de l'IAT Niger.",
            'Nos partenaires',
            "Institutions publiques, universités et entreprises : un réseau qui renforce la formation et l'insertion de nos diplômés.",
        ],
        'galerie' => [
            'Galerie — Le campus en images | IAT Niger',
            "Le campus de l'IAT Niger en images : bâtiments, laboratoires, vie étudiante, événements et distinctions.",
            'Le campus en images',
            "Bâtiments, laboratoires, événements et vie étudiante : découvrez l'IAT en photos.",
        ],
        'telechargements' => [
            'Téléchargements — Documents officiels | IAT Niger',
            "Téléchargez les documents officiels de l'IAT Niger : dépliant 2026-2027 avec modalités de paiement, brochure des formations, logos officiels.",
            'Documents à télécharger',
            "Dépliant officiel, modalités de paiement, brochure des formations et logos : tous les documents de l'institut en un clic.",
        ],
        'formations' => [
            'Nos Formations — BTS, Licences, Masters, Doctorat | IAT Niger',
            "28 filières tertiaires et industrielles du Bac Pro au Doctorat, accréditées CAMES : gestion, banque, informatique, génie civil, télécoms, pétrole…",
            'Nos formations',
            "Du Niveau Moyen au Doctorat : 28 filières pour construire votre avenir professionnel.",
        ],
        'admission' => [
            'Admission & Inscription — Comment rejoindre l\'IAT | IAT Niger',
            "Conditions d'accès, dossier de candidature et préinscription en ligne à l'IAT Niger : Niveau Moyen, BTS/Licence, Master, Doctorat. Inscriptions ouvertes.",
            "Rejoignez l'IAT en 3 étapes simples",
            "Choisissez votre formation, préparez votre dossier, déposez votre préinscription en ligne : notre équipe scolarité vous rappelle.",
        ],
        'vie-etudiante' => [
            'Vie étudiante — BDE, clubs et alumni | IAT Niger',
            "BDE actif, clubs engagés, sport, culture, voyages d'études et un réseau d'anciens présent dans les ministères et grandes entreprises du Niger.",
            'Un campus qui forme aussi des leaders',
            "Sport, culture, engagement citoyen, voyages d'études : à l'IAT, la vie étudiante fait partie de la formation.",
        ],
        'csp-algoza' => [
            'CSP Algoza — Maternelle, Primaire, Collège & Lycée | Groupe IAT',
            "Le Complexe Scolaire Privé Algoza : anglais renforcé dès le CI, un ordinateur par élève, classes de 25, cantine et jardin pédagogique. Séries A, C et D au lycée.",
            "CSP Algoza : l'excellence de la maternelle au bac",
            'Préparer nos élèves à devenir des citoyens responsables en offrant un enseignement visant l\'excellence académique.',
        ],
        'actualites' => [
            "Actualités — La vie de l'institut | IAT Niger",
            "Toute l'actualité de l'IAT Niger : distinctions, nouveaux laboratoires, partenariats, vie étudiante et engagement citoyen.",
            'Actualités',
            "Distinctions, infrastructures, partenariats, vie étudiante : suivez la vie de l'institut.",
        ],
        'web-tv' => [
            'WEB TV — Reportages et événements en vidéo | IAT Niger',
            "La chaîne vidéo de l'IAT Niger : rentrées solennelles, inaugurations de laboratoires, salons, partenariats et reportages sur la vie du campus.",
            'WEB TV',
            "Rentrées solennelles, inaugurations, salons et reportages : l'institut en images.",
        ],
        'faq' => [
            'FAQ — Questions fréquentes | IAT Niger',
            "Admission, frais, accréditations, débouchés : toutes les réponses aux questions les plus fréquentes sur l'IAT Niger.",
            'Questions fréquentes',
            "Tout ce qu'il faut savoir avant de rejoindre l'IAT.",
        ],
        'contact' => [
            'Contact — Nous écrire ou nous rendre visite | IAT Niger',
            "Contactez l'IAT Niger : rond-point Gadafawa, Yantala, Niamey. Tél. (+227) 20 75 29 40 / 96 97 07 92 — info@iatniger.org.",
            'Parlons de votre avenir',
            "Une question sur une filière, une inscription, un partenariat ? Notre équipe vous répond rapidement.",
        ],
    ];
    $n = 0;
    foreach ($pages as $slug => [$titre_seo, $meta_desc, $hero_titre, $hero_texte]) {
        cms_seed_upsert_row($pdo, 'cms_pages', 'slug', $slug, [
            'titre_seo' => $titre_seo,
            'meta_desc' => $meta_desc,
            'hero_titre' => $hero_titre,
            'hero_texte' => $hero_texte,
        ], $force);
        $n++;
    }
    return $n;
}

/** Niveaux de formation depuis la constante NIVEAUX. */
function cms_seed_niveaux(PDO $pdo, bool $force): int
{
    if (!defined('NIVEAUX')) {
        return 0;
    }
    $n = 0;
    $ordre = 0;
    foreach (NIVEAUX as $slug => $niv) {
        cms_seed_upsert_row($pdo, 'cms_niveaux', 'slug', $slug, [
            'titre' => $niv['titre'],
            'sous_titre' => $niv['sous_titre'] ?? null,
            'recrutement' => $niv['recrutement'] ?? null,
            'duree' => $niv['duree'] ?? null,
            'dossier' => $niv['dossier'] ?? null,
            'description' => $niv['description'] ?? null,
            'ordre' => $ordre,
            'publie' => 1,
        ], $force);
        $ordre++;
        $n++;
    }
    return $n;
}

/** Catalogue des formations depuis la constante FORMATIONS. */
function cms_seed_formations(PDO $pdo, bool $force): int
{
    if (!defined('FORMATIONS')) {
        return 0;
    }
    $n = 0;
    $ordre = 0;
    foreach (FORMATIONS as $f) {
        cms_seed_upsert_row($pdo, 'cms_formations', 'slug', $f['slug'], [
            'niveau' => $f['niveau'],
            'domaine' => $f['domaine'] ?? 'tertiaire',
            'titre' => $f['titre'],
            'icone' => $f['icone'] ?? 'book-open',
            'resume' => $f['resume'] ?? null,
            'objectif' => $f['objectif'] ?? null,
            'debouches' => json_encode($f['debouches'] ?? [], JSON_UNESCAPED_UNICODE),
            'badge' => $f['badge'] ?? null,
            'ordre' => $ordre,
            'publie' => 1,
        ], $force);
        $ordre++;
        $n++;
    }
    return $n;
}

/** Contenu éditorial : FAQ, partenaires, galerie, vidéos, documents, témoignages, sections de pages. */
function cms_seed_items(PDO $pdo, bool $force): int
{
    $n = 0;

    /* ---------- FAQ ---------- */
    $n += cms_seed_item_block($pdo, 'faq', null, [
        ['titre' => "Quelles sont les conditions pour s'inscrire en Licence ?",
            'contenu' => "Le BAC toutes séries (ou un diplôme équivalent) donne accès à la 1ère année. Les titulaires d'un BTS, DUT ou d'une L2 avec 120 crédits validés peuvent intégrer directement la Licence 3. Le dossier comprend un extrait d'acte de naissance, un certificat de nationalité et le dernier bulletin ou diplôme."],
        ['titre' => "Puis-je m'inscrire sans le BAC ?",
            'contenu' => "Oui. Le Niveau Moyen (Bac Professionnel et Technique) recrute dès le BEPC ou niveau 3ème, pour une formation en 3 ans dans 6 filières tertiaires et industrielles."],
        ['titre' => "Les diplômes de l'IAT sont-ils reconnus ?",
            'contenu' => "Oui. L'IAT est agréé par l'État du Niger depuis 1999 (arrêtés N° 0143 et 0233/MEN/DEPRI/DETFP) et 16 de ses diplômes sont accrédités au CAMES, dans le système LMD. Les formations sont également reconnues par l'ANAQ-SUP."],
        ['titre' => 'Comment se déroule la préinscription en ligne ?',
            'contenu' => "Remplissez le formulaire de préinscription sur la page Admission : c'est gratuit et sans engagement. Notre service scolarité vous rappelle sous 48 h ouvrées pour finaliser votre dossier."],
        ['titre' => 'Quels sont les frais de scolarité ?',
            'contenu' => "Les frais varient selon le niveau et la filière. Pour le CSP Algoza : 230 000 F CFA/an au primaire, 340 000 F CFA au collège et 390 000 F CFA au lycée. Pour l'institut supérieur, contactez la scolarité au (+227) 20 75 29 40 pour un devis précis. Le Master de Recherche (Université de Douala) : 300 000 F CFA de frais d'inscription."],
        ['titre' => "L'IAT propose-t-il un Doctorat ?",
            'contenu' => "Oui, via le partenariat avec l'ESSEC de l'Université de Douala (Cameroun) : après le Master de Recherche, vous pouvez poursuivre en thèse dans l'École Doctorale (Business Economics, Management des organisations, Science Juridique, Science de l'ingénieur)."],
        ['titre' => 'Y a-t-il des travaux pratiques ?',
            'contenu' => "Oui. Deux laboratoires modernes (Génie Électrique et Génie Civil) ont été inaugurés en 2026, et les filières informatiques travaillent sur des environnements professionnels (GNU/Linux, réseaux, bases de données)."],
        ['titre' => 'Où se trouve le campus ?',
            'contenu' => "Au rond-point Gadafawa, quartier Yantala, Commune 1, Niamey (BP 412). Téléphone : (+227) 20 75 29 40 / 96 97 07 92 — info@iatniger.org."],
    ], $force);

    /* ---------- Partenaires ---------- */
    $partenaires = [
        ['fichier' => 'essecd', 'nom' => 'ESSEC — Université de Douala', 'type' => 'Académique',
            'desc' => "Partenaire du Master de Recherche et du Doctorat : formation, mobilité, projets de recherche et événements scientifiques."],
        ['fichier' => 'anpe', 'nom' => 'ANPE', 'type' => 'Institutionnel',
            'desc' => "L'Agence Nationale pour la Promotion de l'Emploi accompagne l'insertion professionnelle des diplômés."],
        ['fichier' => 'emig', 'nom' => 'EMIG', 'type' => 'Académique',
            'desc' => "L'École des Mines, de l'Industrie et de la Géologie, partenaire académique des filières industrielles."],
        ['fichier' => 'ist', 'nom' => 'IST', 'type' => 'Académique',
            'desc' => "L'Institut des Sciences et Technologies, partenaire d'échanges pédagogiques."],
        ['fichier' => 'hcr', 'nom' => 'HCR', 'type' => 'Institutionnel',
            'desc' => "Le Haut-Commissariat des Nations Unies pour les Réfugiés, partenaire de programmes de formation."],
        ['fichier' => 'cipmen', 'nom' => 'CIPMEN', 'type' => 'Entrepreneuriat',
            'desc' => "Le Centre Incubateur des PME au Niger soutient les projets entrepreneuriaux des étudiants."],
        ['fichier' => 'opagen', 'nom' => 'OPAGEN', 'type' => 'Professionnel',
            'desc' => "Partenaire professionnel pour les stages et l'insertion des diplômés."],
        ['fichier' => 'labari', 'nom' => 'Labari', 'type' => 'Médias',
            'desc' => "Partenaire média qui relaie les activités et événements de l'institut."],
    ];
    $rows = array_map(static fn ($p) => [
        'titre' => $p['nom'],
        'sous_titre' => $p['type'],
        'contenu' => $p['desc'],
        'image' => 'partenaires/' . $p['fichier'] . '.jpg',
        'extra' => ['fichier' => $p['fichier']],
    ], $partenaires);
    $n += cms_seed_item_block($pdo, 'partenaire', null, $rows, $force);

    /* ---------- Galerie ---------- */
    $photos = [
        ['src' => 'recentes/photo-15.jpg', 'legende' => 'Projet architectural — étudiants en génie civil', 'cat' => 'vie-etudiante'],
        ['src' => 'recentes/photo-13.jpg', 'legende' => 'Étudiante en génie civil — formation pratique', 'cat' => 'vie-etudiante'],
        ['src' => 'recentes/photo-10.jpg', 'legende' => 'Travaux pratiques — automate PLC en laboratoire', 'cat' => 'campus'],
        ['src' => 'recentes/photo-17.jpg', 'legende' => 'Topographie et levé — travaux pratiques', 'cat' => 'campus'],
        ['src' => 'recentes/photo-14.jpg', 'legende' => 'Remise de distinctions — partenaires et lauréats', 'cat' => 'evenements'],
        ['src' => 'recentes/photo-25.jpg', 'legende' => 'Étudiantes en journée culturelle', 'cat' => 'vie-etudiante'],
        ['src' => 'recentes/photo-16.jpg', 'legende' => 'Journée culturelle — patrimoine nigérien', 'cat' => 'vie-etudiante'],
        ['src' => 'recentes/photo-19.jpg', 'legende' => 'Plantation d\'arbre — visite CAEPE', 'cat' => 'evenements'],
        ['src' => 'recentes/photo-11.jpg', 'legende' => 'Distinction officielle — trophée d\'excellence', 'cat' => 'evenements'],
        ['src' => 'campus/immeuble-iat.jpg', 'legende' => "L'immeuble principal du campus, rond-point Gadafawa", 'cat' => 'campus'],
        ['src' => 'actualites/laboratoires-lancement.jpg', 'legende' => 'Lancement officiel des deux nouveaux laboratoires', 'cat' => 'evenements'],
        ['src' => 'actualites/labo-genie-electrique.jpg', 'legende' => 'Le laboratoire de Génie Électrique', 'cat' => 'campus'],
        ['src' => 'actualites/alkalami-dor.jpg', 'legende' => "Gala Alkalami d'Or 2026 : l'IAT récompensé", 'cat' => 'evenements'],
        ['src' => 'actualites/girls-in-ict.jpg', 'legende' => 'Girls in ICT 2026 chez ATC Niger', 'cat' => 'vie-etudiante'],
        ['src' => 'banner-iat.jpg', 'legende' => 'Le Groupe IAT : 19 diplômes accrédités CAMES / ANAQ-SUP', 'cat' => 'evenements'],
    ];
    $rows = array_map(static fn ($p) => [
        'titre' => $p['legende'],
        'image' => $p['src'],
        'groupe' => $p['cat'],
    ], $photos);
    $n += cms_seed_item_block($pdo, 'galerie', null, $rows, $force);

    /* ---------- Vidéos WEB TV ---------- */
    $videos = [
        ['titre' => 'Visite du ministre au stand de IAT Niger — SeNum24', 'vues' => 7691, 'path' => 'visite-du-ministre-au-stand-de-iatniger-senum24', 'image' => 'actualites/hub-peering.jpg'],
        ['titre' => "Rentrée Solennelle de l'IAT Niger", 'vues' => 5558, 'path' => 'rentree-solennelle-de-liat-niger', 'image' => 'campus/immeuble-iat.jpg'],
        ['titre' => "Salon de l'Orientation Académique et Professionnelle", 'vues' => 5444, 'path' => 'salon-de-lorientation-academique-et-professionnelle', 'image' => 'actualites/girls-in-ict.jpg'],
        ['titre' => 'Du social avec le CSP ALGOZA en ce premier jour de congé Ramadan', 'vues' => 2700, 'path' => 'du-social-avec-le-csp-algoza-en-ce-premier-jour-de-conge-ramadan', 'image' => 'actualites/forage-eau.jpg'],
        ['titre' => 'IAT Niger — SPOT', 'vues' => 2435, 'path' => 'iat-niger-spot', 'image' => 'banner-iat.jpg'],
        ['titre' => 'IAT — Conseil Scientifique', 'vues' => 2308, 'path' => 'iat-conseil-scientifique', 'image' => 'actualites/alkalami-dor.jpg'],
        ['titre' => "1ère réunion d'information enseignants-administration (amphithéâtre Abdou Moumouni Dioffo)", 'vues' => 1975, 'path' => 'le-dimanche-02-octobre-2022-sest-tenue-dans-lamphitheatre-abdou-moumouni-dioffo-de-liat-la-1ere-reunion-dinformation-regroupant-les-enseignants-de-linstitut-et-ladministration', 'image' => 'actualites/laboratoires-lancement.jpg'],
        ['titre' => 'Ouverture du festival FIFIDO', 'vues' => 1506, 'path' => 'ouverture-du-festival-fifido', 'image' => 'actualites/don-de-sang.jpg'],
        ['titre' => "Convention de partenariat de localisation du Master de Recherche et du Doctorat avec l'ESSEC (Université de Douala)", 'vues' => 1478, 'path' => 'convention-de-partenariat-de-localisation-du-master-de-recherche-et-du-doctorat-avec-lessec-universite-de-douala-cameroun', 'image' => 'partenaires/essecd.jpg'],
        ['titre' => "Convention de partenariat entre IAT Niger et l'ESSEC, Université de Douala", 'vues' => 1246, 'path' => 'convention-de-partenariat-entre-iatniger-et-lessec-universite-de-douala-cameroun', 'image' => 'partenaires/essecd.jpg'],
        ['titre' => "L'IAT inaugure deux nouveaux laboratoires de pointe", 'vues' => 1226, 'path' => 'linstitut-africain-de-technologie-inaugure-deux-nouveaux-laboratoires-de-pointe', 'image' => 'actualites/labo-genie-electrique.jpg'],
        ['titre' => "Délibération des dossiers d'accréditation à la DGQEA", 'vues' => 1190, 'path' => 'delibereration-des-dossiers-daccreditation-a-la-direction-generale-de-la-qualite-des-equivalenceset-accreditations-dgqea', 'image' => 'actualites/alkalami-dor.jpg'],
        ['titre' => 'Publireportage IAT Niger', 'vues' => 769, 'path' => 'publireportage-iat-niger', 'image' => 'depliant-iat.jpg'],
        ['titre' => 'IAT Info 20/01/2022', 'vues' => 619, 'path' => 'iat-info-20-01-2022', 'image' => 'logo-iat.jpg'],
        ['titre' => "Visite marquante de l'Ambassadrice des États-Unis au Salon de l'Orientation", 'vues' => 436, 'path' => 'salon-de-lorientation-academique-et-professionnelle-une-visite-marquante-de-lambassadrice-des-etats-unis', 'image' => 'actualites/girls-in-ict.jpg'],
    ];
    $rows = array_map(static fn ($v) => [
        'titre' => $v['titre'],
        'image' => $v['image'],
        'extra' => ['vues' => $v['vues'], 'path' => $v['path']],
    ], $videos);
    $n += cms_seed_item_block($pdo, 'video', null, $rows, $force);

    /* ---------- Documents téléchargeables ---------- */
    $documents = [
        ['fichier' => 'docs/depliant-iat-2026-2027.pdf', 'nom_dl' => 'Depliant-IAT-Niger-2026-2027.pdf',
            'titre' => 'Dépliant officiel 2026-2027', 'type' => 'PDF', 'icone' => 'file-text',
            'desc' => "Le document de référence : toutes les filières, les conditions d'accès et les modalités de paiement pour l'année académique 2026-2027.",
            'badge' => 'Modalités de paiement incluses'],
        ['fichier' => 'img/brochure-2025-2026.jpg', 'nom_dl' => 'Brochure-IAT-Niger-2025-2026.jpg',
            'titre' => 'Brochure 2025-2026', 'type' => 'Image', 'icone' => 'book-open',
            'desc' => "La brochure complète des formations de l'institut : Niveau Moyen, Licences, Masters et contacts utiles.", 'badge' => null],
        ['fichier' => 'img/depliant-iat.jpg', 'nom_dl' => 'Depliant-IAT-Niger.jpg',
            'titre' => 'Dépliant (version image)', 'type' => 'Image', 'icone' => 'image',
            'desc' => "Le dépliant de présentation du Groupe IAT au format image, facile à partager sur WhatsApp et les réseaux sociaux.", 'badge' => null],
        ['fichier' => 'docs/logo-iat-hd.png', 'nom_dl' => 'Logo-IAT-Niger-HD.png',
            'titre' => 'Logo officiel (haute définition)', 'type' => 'PNG', 'icone' => 'award',
            'desc' => "Le logo officiel de l'Institut Africain de Technologie en haute résolution, pour vos documents et supports partenaires.", 'badge' => null],
        ['fichier' => 'img/logoiat.png', 'nom_dl' => 'Logo-IAT-Niger-horizontal.png',
            'titre' => 'Logo horizontal avec slogan', 'type' => 'PNG', 'icone' => 'award',
            'desc' => "La version horizontale du logo avec le slogan « Un pôle d'excellence », idéale pour les en-têtes de documents.", 'badge' => null],
        ['fichier' => 'img/banner-iat.jpg', 'nom_dl' => 'Banniere-IAT-Niger.jpg',
            'titre' => 'Bannière officielle', 'type' => 'Image', 'icone' => 'image',
            'desc' => "La bannière du Groupe IAT : 19 diplômes Licences et Masters accrédités par le CAMES / ANAQ-SUP.", 'badge' => null],
    ];
    $rows = array_map(static fn ($d) => [
        'titre' => $d['titre'],
        'sous_titre' => $d['type'],
        'contenu' => $d['desc'],
        'url' => $d['fichier'],
        'extra' => array_filter(['nom_dl' => $d['nom_dl'], 'icone' => $d['icone'], 'badge' => $d['badge']], static fn ($v) => $v !== null),
    ], $documents);
    $n += cms_seed_item_block($pdo, 'document', null, $rows, $force);

    /* ---------- Témoignages ---------- */
    $n += cms_seed_item_block($pdo, 'temoignage', null, [
        ['titre' => 'Secrétaire Général du BDE', 'sous_titre' => "Bureau Des Étudiants de l'IAT",
            'contenu' => "La jeunesse est l'espoir de chaque communauté en quête de développement et de progrès. J'invite tous les étudiants à s'investir pleinement pour bénéficier de cette formation d'excellence.",
            'extra' => ['initiales' => 'SG']],
        ['titre' => 'Abdoulaye Souleymane', 'sous_titre' => 'Président de l\'Amicale des Anciens · Protocole, Présidence',
            'contenu' => "De l'IAT à la Présidence de la République : la formation reçue m'a ouvert les portes des plus hautes institutions du pays.",
            'extra' => ['initiales' => 'AS']],
        ['titre' => 'Moumouni Absi', 'sous_titre' => 'Achats & logistique, Nigelec · Vice-président des Anciens',
            'contenu' => "Les compétences acquises en logistique à l'IAT sont celles que j'utilise chaque jour à la Nigelec. Une formation ancrée dans la réalité des entreprises.",
            'extra' => ['initiales' => 'MA']],
        ['titre' => 'Salif Moussa Douké', 'sous_titre' => 'Coordonnateur national, ONG OADES-Niger',
            'contenu' => "L'IAT m'a donné bien plus qu'un diplôme : une méthode, un réseau et l'envie de servir. Je coordonne aujourd'hui une ONG nationale.",
            'extra' => ['initiales' => 'SM']],
    ], $force);

    /* ---------- Accueil : textes ---------- */
    cms_seed_item_keyed($pdo, 'texte', 'accueil_hero_kicker', ['contenu' => 'Accrédité CAMES · ANAQ-SUP · Depuis 1999'], $force);
    cms_seed_item_keyed($pdo, 'texte', 'accueil_hero_h1', ['contenu' => "Construisez votre avenir dans un pôle d'excellence"], $force);
    cms_seed_item_keyed($pdo, 'texte', 'accueil_hero_lead', ['contenu' => "L'Institut Africain de Technologie forme les cadres et techniciens qui transforment le Niger et l'Afrique : 28 filières du Bac Pro au Doctorat, des laboratoires modernes et 25 ans d'expérience."], $force);
    cms_seed_item_keyed($pdo, 'texte', 'accueil_hero_trust', ['extra' => ['items' => ['16 diplômes accrédités CAMES', 'Système LMD', 'Laboratoires équipés']]], $force);
    cms_seed_item_keyed($pdo, 'texte', 'accueil_csp_titre', ['contenu' => "CSP Algoza : l'excellence dès le plus jeune âge"], $force);
    cms_seed_item_keyed($pdo, 'texte', 'accueil_csp_texte', ['contenu' => "Le Complexe Scolaire Privé Algoza accueille vos enfants de la maternelle au baccalauréat : anglais renforcé, un ordinateur par élève, cantine et classes de 25 élèves maximum."], $force);
    cms_seed_item_keyed($pdo, 'texte', 'accueil_csp_liste', ['extra' => ['items' => [
        'Maternelle & primaire — anglais dès le CI, 25 ordinateurs',
        'Collège & lycée — séries A, C et D, 4 h d\'anglais par semaine',
        'Cantine quotidienne et jardin potager pédagogique',
    ]]], $force);

    /* ---------- Accueil : pourquoi choisir l'IAT ---------- */
    $n += cms_seed_item_block($pdo, 'carte', 'accueil-pourquoi', [
        ['titre' => 'Diplômes accrédités', 'contenu' => "16 diplômes accrédités au CAMES et reconnus ANAQ-SUP, alignés sur le système LMD (Licence-Master-Doctorat) : votre diplôme a de la valeur partout en Afrique.", 'extra' => ['icone' => 'award']],
        ['titre' => 'Laboratoires modernes', 'contenu' => "Des laboratoires de Génie Électrique et Génie Civil inaugurés en 2026, pour apprendre en pratiquant sur des équipements professionnels.", 'extra' => ['icone' => 'flask']],
        ['titre' => 'Corps enseignant de haut niveau', 'contenu' => "36 enseignants-chercheurs de rang A issus des universités de Niamey, Dakar, Douala, Kara, Montpellier et de toute la sous-région.", 'extra' => ['icone' => 'users']],
        ['titre' => 'Insertion professionnelle', 'contenu' => "Des formations professionnalisantes construites avec les entreprises, des stages et un réseau d'alumni présent dans les ministères et grandes sociétés.", 'extra' => ['icone' => 'briefcase']],
        ['titre' => 'Ouverture internationale', 'contenu' => "Partenariat avec l'ESSEC de l'Université de Douala pour le Master de Recherche et le Doctorat, mobilité des enseignants et des étudiants.", 'extra' => ['icone' => 'globe']],
        ['titre' => 'Vie étudiante riche', 'contenu' => "BDE actif, clubs engagés, sport, culture, voyages d'études et actions citoyennes : un campus où l'on apprend aussi à devenir un leader.", 'extra' => ['icone' => 'heart']],
    ], $force);

    /* ---------- Accueil : statistiques ---------- */
    $n += cms_seed_item_block($pdo, 'stat', 'accueil', [
        ['titre' => "années d'expérience", 'extra' => ['valeur' => 25, 'suffixe' => '+']],
        ['titre' => 'diplômés formés', 'extra' => ['valeur' => 30000, 'suffixe' => '+']],
        ['titre' => 'filières de formation', 'extra' => ['valeur' => 28, 'suffixe' => '']],
        ['titre' => 'enseignants-chercheurs de rang A', 'extra' => ['valeur' => 36, 'suffixe' => '']],
    ], $force);

    /* ---------- Accueil : diaporama du hero ---------- */
    $n += cms_seed_item_block($pdo, 'hero_slide', 'accueil', [
        ['image' => 'recentes/photo-48.jpg', 'titre' => 'Travaux pratiques — génie civil et topographie'],
        ['image' => 'recentes/photo-20.jpg', 'titre' => 'Étudiants de l\'IAT — fiers de leur institut'],
        ['image' => 'recentes/photo-26.jpg', 'titre' => 'Vie de campus et moments institutionnels'],
        ['image' => 'recentes/photo-17.jpg', 'titre' => 'Formation pratique sur le terrain'],
        ['image' => 'campus/immeuble-iat.jpg', 'titre' => "Le campus de l'Institut Africain de Technologie à Niamey"],
    ], $force);

    /* ---------- À propos : historique ---------- */
    $n += cms_seed_item_block($pdo, 'timeline', 'a-propos', [
        ['titre' => '1999', 'sous_titre' => "Naissance de l'institut", 'contenu' => "Création par Arrêté N° 0143/MEN/DEPRI/DETFP du 26 juillet 1999, puis ouverture officielle par Arrêté N° 0233 du 17 novembre 1999, dans un immeuble en location au rond-point Gadafawa, derrière la station TOTAL à Niamey."],
        ['titre' => '2002', 'sous_titre' => 'Un campus en propre', 'contenu' => "Après seulement trois années d'activité, l'institut acquiert sur fonds propres un immeuble à trois niveaux — signe d'une gestion rigoureuse et d'une croissance saine."],
        ['titre' => '2014', 'sous_titre' => 'Extension du campus', 'contenu' => "Réception en octobre 2014 d'un deuxième immeuble construit entre 2012 et 2014 : 10 salles de cours et 5 bureaux, dont celui du président du conseil scientifique."],
        ['titre' => '2020', 'sous_titre' => 'Reconnaissance internationale', 'contenu' => "L'institut reçoit l'Arch of Europe Award, qui distingue son engagement pour la qualité."],
        ['titre' => '2022', 'sous_titre' => 'Cap sur la recherche', 'contenu' => "Signature de la convention de partenariat avec l'ESSEC de l'Université de Douala (Cameroun) : localisation du Master de Recherche et du Doctorat à Niamey."],
        ['titre' => '2026', 'sous_titre' => "L'ère des laboratoires", 'contenu' => "Inauguration de deux laboratoires modernes (Génie Électrique et Génie Civil) et prix Alkalami d'Or de l'excellence académique. L'IAT rejoint aussi le Hub de Peering Fédéré de Niger-REN."],
    ], $force);

    /* ---------- À propos : valeurs, textes, stats, enseignants ---------- */
    $n += cms_seed_item_block($pdo, 'carte', 'a-propos-valeurs', [
        ['titre' => 'Excellence', 'contenu' => 'Des programmes exigeants, des enseignants de rang A', 'extra' => ['icone' => 'check']],
        ['titre' => 'Qualité', 'contenu' => '16 diplômes accrédités CAMES, normes LMD', 'extra' => ['icone' => 'check']],
        ['titre' => 'Transparence', 'contenu' => 'Une gouvernance claire, un conseil scientifique actif', 'extra' => ['icone' => 'check']],
        ['titre' => 'Ouverture au monde', 'contenu' => 'Partenariats académiques internationaux', 'extra' => ['icone' => 'check']],
    ], $force);

    cms_seed_item_keyed($pdo, 'texte', 'a-propos_mission_titre', ['contenu' => "Former les talents qui développent l'Afrique"], $force);
    cms_seed_item_keyed($pdo, 'texte', 'a-propos_mission_texte', ['contenu' => "« Donner une formation de haut niveau adaptée au contexte africain et du monde contemporain afin de mettre à la disposition du marché » les compétences dont les entreprises ont besoin, et mobiliser les ressources intellectuelles pour le développement économique du continent."], $force);
    cms_seed_item_keyed($pdo, 'texte', 'a-propos_direction', ['contenu' => "Depuis 1999, l'Institut Africain de Technologie poursuit une seule ambition : offrir à la jeunesse nigérienne et africaine une formation à la hauteur de son potentiel. Nos quatre valeurs — l'excellence, la qualité, la transparence et l'ouverture au monde — ne sont pas des slogans : elles se lisent dans nos accréditations CAMES, dans nos laboratoires, dans les carrières de nos 30 000 diplômés.\n\nChoisir l'IAT, c'est rejoindre une institution qui investit continuellement dans ses infrastructures, son corps enseignant et ses partenariats internationaux, pour que chaque étudiant reparte avec bien plus qu'un diplôme : un métier, une méthode et un réseau."], $force);
    cms_seed_item_keyed($pdo, 'texte', 'a-propos_enseignants_intro', ['contenu' => 'Les Masters professionnels et le Master de Recherche sont animés par des enseignants-chercheurs de rang A — professeurs titulaires du CAMES, professeurs agrégés et maîtres de conférences.'], $force);

    $n += cms_seed_item_block($pdo, 'carte', 'a-propos-enseignants', [
        ['titre' => 'Grades académiques', 'contenu' => 'Professeurs titulaires du CAMES, professeurs agrégés, maîtres de conférences et agrégés en Science de Gestion.', 'extra' => ['icone' => 'award']],
        ['titre' => 'Disciplines couvertes', 'contenu' => 'Science de Gestion, Réseaux Informatiques, Sociologie et Psycho-Sociologie — au service de filières tertiaires et industrielles.', 'extra' => ['icone' => 'book-open']],
        ['titre' => "Universités d'attache", 'contenu' => 'Abdou Moumouni (Niger), Cheikh Anta Diop (Sénégal), Douala (Cameroun), Kara (Togo), Paul-Valéry Montpellier (France), et des universités du Bénin, du Burkina Faso et de Côte d\'Ivoire.', 'extra' => ['icone' => 'globe']],
    ], $force);

    $n += cms_seed_item_block($pdo, 'stat', 'a-propos', [
        ['titre' => 'diplômés du niveau supérieur', 'extra' => ['valeur' => 20000, 'suffixe' => '']],
        ['titre' => 'diplômés du niveau moyen', 'extra' => ['valeur' => 8000, 'suffixe' => '']],
        ['titre' => 'certifications & attestations', 'extra' => ['valeur' => 2000, 'suffixe' => '']],
        ['titre' => 'diplômes accrédités CAMES', 'extra' => ['valeur' => 16, 'suffixe' => '']],
    ], $force);

    /* ---------- Vie étudiante ---------- */
    $n += cms_seed_item_block($pdo, 'carte', 'vie-etudiante-bde', [
        ['titre' => 'Culture & sport', 'contenu' => 'Représentations culturelles (culture peulh…), matchs de football amicaux, fêtes de fin d\'année et animations tout au long de l\'année.', 'extra' => ['icone' => 'star']],
        ['titre' => 'Découverte & ouverture', 'contenu' => "Visites d'entreprises, excursions et voyages d'études — comme le voyage au Bénin (2018-2019) — et participation au Salon des Grandes Écoles.", 'extra' => ['icone' => 'globe']],
        ['titre' => 'Engagement citoyen', 'contenu' => 'Journées communautaires : don de sang, plantation d\'arbres, sensibilisation contre les violences basées sur le genre avec le Club PPF.', 'extra' => ['icone' => 'heart']],
    ], $force);

    cms_seed_item_keyed($pdo, 'texte', 'vie-etudiante_club_ppf', ['contenu' => "Le Club PPF (Participation Politique des Femmes) mène des campagnes de sensibilisation sur le campus et au-delà. Il a récemment reçu du matériel de sonorisation pour amplifier ses actions, et co-organise des journées de sensibilisation contre les violences basées sur le genre."], $force);
    cms_seed_item_keyed($pdo, 'texte', 'vie-etudiante_alumni_intro', ['contenu' => "Les anciens de l'IAT occupent des postes dans les ministères (Finances, Transport, DGI), le secteur privé (consulting, télécoms), le secteur public et l'entrepreneuriat."], $force);

    $n += cms_seed_item_block($pdo, 'alumni', 'vie-etudiante', [
        ['titre' => 'Abdoulaye Souleymane', 'sous_titre' => "Président de l'Amicale", 'contenu' => "Protocole, Assistant du Ministre d'État à la Présidence de la République.", 'extra' => ['initiales' => 'AS']],
        ['titre' => 'Moumouni Absi', 'sous_titre' => 'Vice-président', 'contenu' => 'Département achats et logistique à la Nigelec.', 'extra' => ['initiales' => 'MA']],
        ['titre' => 'Salif Moussa Douké', 'sous_titre' => 'Secrétaire Général', 'contenu' => "Coordonnateur national de l'ONG OADES-Niger.", 'extra' => ['initiales' => 'SM']],
        ['titre' => 'Maria Saley', 'sous_titre' => 'Trésorière Générale', 'contenu' => "Membre du bureau de l'Amicale des Anciens Élèves et Étudiants de l'IAT.", 'extra' => ['initiales' => 'MS']],
    ], $force);

    /* ---------- CSP Algoza ---------- */
    $n += cms_seed_item_block($pdo, 'carte', 'csp-algoza-atouts', [
        ['titre' => 'Anglais renforcé', 'contenu' => "Dès le Cours d'Initiation au primaire, et 4 heures par semaine en petits groupes au collège-lycée.", 'extra' => ['icone' => 'globe']],
        ['titre' => '1 élève, 1 ordinateur', 'contenu' => '25 ordinateurs par salle : initiation dès la maternelle (jeux éducatifs, Windows, Word) et cours pratiques hebdomadaires.', 'extra' => ['icone' => 'monitor']],
        ['titre' => '25 élèves par classe', 'contenu' => "Des effectifs limités pour un suivi individualisé par des enseignants qualifiés, avec préparation d'exposés à la bibliothèque.", 'extra' => ['icone' => 'users']],
        ['titre' => 'Cantine & jardin', 'contenu' => 'Cantine quotidienne (petit déjeuner et déjeuner, plats africains et nigériens variés) et jardin potager pédagogique en 6ème-5ème.', 'extra' => ['icone' => 'utensils']],
    ], $force);

    $n += cms_seed_item_block($pdo, 'tarif', 'csp-algoza', [
        ['titre' => 'Maternelle & Primaire', 'sous_titre' => 'Petite Section à CM2', 'extra' => ['inscription' => 30000, 'scolarite' => 200000, 'total' => 230000]],
        ['titre' => 'Collège', 'sous_titre' => '6ème à 3ème', 'extra' => ['inscription' => 30000, 'fournitures' => 40000, 'tenues' => 20000, 'scolarite' => 250000, 'total' => 340000]],
        ['titre' => 'Lycée', 'sous_titre' => 'Séries A, C, D', 'extra' => ['inscription' => 30000, 'fournitures' => 40000, 'tenues' => 20000, 'scolarite' => 300000, 'total' => 390000]],
    ], $force);

    cms_seed_item_keyed($pdo, 'texte', 'csp-algoza_intro', ['contenu' => 'Le curriculum national nigérien, enrichi de cours solides en anglais et en informatique, dans une approche pédagogique multiculturelle où chaque enfant peut s\'épanouir.'], $force);
    cms_seed_item_keyed($pdo, 'texte', 'csp-algoza_reductions', ['contenu' => 'Des réductions sont accordées aux familles ayant plus de trois enfants inscrits au CSP Algoza.'], $force);

    /* ---------- Admission ---------- */
    $n += cms_seed_item_block($pdo, 'carte', 'admission-etapes', [
        ['titre' => 'Choisissez votre formation', 'contenu' => "28 filières du Niveau Moyen au Doctorat. Parcourez le catalogue des formations et identifiez celle qui correspond à votre projet."],
        ['titre' => 'Préparez votre dossier', 'contenu' => "Extrait d'acte de naissance, certificat de nationalité et dernier bulletin ou diplôme. Des pièces complémentaires sont demandées pour le Master de Recherche."],
        ['titre' => 'Déposez votre candidature', 'contenu' => "Préinscrivez-vous en ligne ou rendez-vous directement au campus (rond-point Gadafawa, Yantala). Notre équipe vous recontacte rapidement."],
    ], $force);

    return $n;
}

/** Point d'entrée unique : crée les tables puis sème tout le contenu. Retourne des messages lisibles. */
function cms_install_and_seed(PDO $pdo, bool $force = false): array
{
    $messages = [];

    cms_seed_create_tables($pdo);
    $messages[] = 'Tables CMS créées (ou déjà présentes).';

    $n = cms_seed_settings($pdo, $force);
    $messages[] = $n . ' paramètre(s) de site enregistré(s).';

    $n = cms_seed_pages($pdo, $force);
    $messages[] = $n . ' page(s) CMS (SEO / bandeau) enregistrée(s).';

    $n = cms_seed_niveaux($pdo, $force);
    $messages[] = $n . ' niveau(x) de formation enregistré(s).';

    $n = cms_seed_formations($pdo, $force);
    $messages[] = $n . ' formation(s) enregistrée(s).';

    $n = cms_seed_items($pdo, $force);
    $messages[] = $n . " élément(s) de contenu ajouté(s) (FAQ, partenaires, galerie, vidéos, documents, témoignages, sections de pages…).";

    $messages[] = $force
        ? 'Réinitialisation complète effectuée : le contenu de démarrage a été restauré.'
        : "Installation terminée : le contenu déjà personnalisé n'a pas été modifié.";

    return $messages;
}
