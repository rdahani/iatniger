<?php
/**
 * Actualités de secours (fallback) — utilisées si MySQL est indisponible.
 * La table `actualites` de la base est la source principale (gérée via l'admin).
 */

const ACTUALITES_FALLBACK = [
    ['slug' => 'projet-hub-peering-federe', 'titre' => "Projet Hub de Peering Fédéré : l'IAT renforce ses infrastructures numériques",
        'date_publication' => '2026-07-03', 'image' => 'actualites/hub-peering.jpg', 'categorie' => 'Infrastructure',
        'extrait' => "L'IAT a participé à la cérémonie de remise de serveurs virtuels et d'équipements réseau organisée par Niger-REN pour accélérer la transformation numérique de l'enseignement supérieur.",
        'contenu' => "Dans le cadre de l'initiative de Niger-REN (Réseau national pour l'éducation et la recherche), l'Institut Africain de Technologie a pris part à une cérémonie de remise de serveurs virtuels et d'équipements réseau. Ce projet de Hub de Peering Fédéré vise à accélérer la transformation numérique des établissements d'enseignement supérieur du Niger.\n\nGrâce à ces nouveaux équipements, l'IAT renforce ses infrastructures numériques au bénéfice direct de ses étudiants et enseignants : meilleure connectivité, hébergement de services pédagogiques et ouverture sur les réseaux académiques régionaux."],
    ['slug' => 'sensibilisation-violences-genre', 'titre' => "L'IAT aux côtés du Club PPF pour sensibiliser à la lutte contre les violences basées sur le genre",
        'date_publication' => '2026-06-30', 'image' => 'actualites/sensibilisation-vbg.jpg', 'categorie' => 'Vie étudiante',
        'extrait' => "L'administration de l'IAT a accompagné le Club PPF pour une journée de sensibilisation consacrée à la lutte contre les violences basées sur le genre.",
        'contenu' => "L'administration de l'Institut Africain de Technologie a accompagné le Club PPF (Participation Politique des Femmes) dans l'organisation d'une journée de sensibilisation consacrée à la lutte contre les violences basées sur le genre.\n\nCette initiative s'inscrit dans l'engagement citoyen de l'institut et dans sa volonté de former des diplômés responsables, acteurs du changement social dans leurs communautés."],
    ['slug' => 'alkalami-dor-2026', 'titre' => "Alkalami d'Or 2026 : l'IAT Niger récompensé pour son excellence académique",
        'date_publication' => '2026-06-28', 'image' => 'actualites/alkalami-dor.jpg', 'categorie' => 'Distinction',
        'extrait' => "L'IAT Niger a été récompensé lors de la deuxième édition du gala « Alkalami d'Or 2026 » pour son excellence académique et ses contributions au secteur éducatif.",
        'contenu' => "L'Institut Africain de Technologie a été distingué lors de la deuxième édition du gala « Alkalami d'Or 2026 », qui récompense les acteurs majeurs du secteur éducatif nigérien.\n\nCette distinction vient saluer l'excellence académique de l'institut, la qualité de ses formations accréditées CAMES et sa contribution constante à la formation des cadres du Niger depuis plus de 25 ans. Elle s'ajoute à l'Arch of Europe Award reçu en 2020."],
    ['slug' => 'girls-in-ict-2026', 'titre' => "Girls in ICT 2026 : immersion des étudiantes de l'IAT dans l'univers des télécommunications",
        'date_publication' => '2026-05-20', 'image' => 'actualites/girls-in-ict.jpg', 'categorie' => 'Partenariat',
        'extrait' => "Une délégation d'étudiantes de l'IAT a effectué une visite pédagogique chez ATC Niger pour découvrir l'univers des télécommunications.",
        'contenu' => "À l'occasion de la journée internationale « Girls in ICT », une délégation d'étudiantes de l'Institut Africain de Technologie a effectué une visite pédagogique chez ATC Niger.\n\nAu programme : découverte des infrastructures de télécommunications, échanges avec des professionnelles du secteur et encouragement des vocations féminines dans les métiers du numérique — un axe fort de la politique d'ouverture de l'IAT."],
    ['slug' => 'journee-femme-nigerienne-don-sang', 'titre' => "Journée Nationale de la Femme Nigérienne : opération de don de sang au CNTS",
        'date_publication' => '2026-05-20', 'image' => 'actualites/don-de-sang.jpg', 'categorie' => 'Vie étudiante',
        'extrait' => "La CONGAFEN a organisé une opération de don de sang au CNTS en présence de la Ministre de la Population, avec la participation de l'IAT.",
        'contenu' => "À l'occasion de la Journée Nationale de la Femme Nigérienne, la CONGAFEN a organisé une opération de don de sang au Centre National de Transfusion Sanguine (CNTS), en présence de la Ministre de la Population.\n\nLa communauté de l'IAT s'est mobilisée pour cette action solidaire, fidèle à ses valeurs d'engagement citoyen et de service à la communauté."],
    ['slug' => 'appui-club-ppf', 'titre' => "Appui au Club PPF de l'IAT : un pas de plus pour l'engagement citoyen",
        'date_publication' => '2026-02-26', 'image' => 'actualites/club-ppf-appui.jpg', 'categorie' => 'Vie étudiante',
        'extrait' => "Le club PPF a reçu du matériel de sonorisation et des équipements pour renforcer ses activités de sensibilisation sur la participation politique des femmes.",
        'contenu' => "Le Club PPF de l'IAT a reçu du matériel de sonorisation et des équipements destinés à renforcer ses activités de sensibilisation, dans le cadre du projet sur la participation politique des femmes.\n\nCet appui matériel permettra au club d'amplifier ses actions sur le campus et au-delà, et confirme la place de la vie associative dans le projet pédagogique de l'institut."],
    ['slug' => 'lancement-deux-laboratoires', 'titre' => "Lancement officiel de deux nouveaux laboratoires à l'IAT",
        'date_publication' => '2026-02-02', 'image' => 'actualites/laboratoires-lancement.jpg', 'categorie' => 'Infrastructure',
        'extrait' => "L'IAT a lancé officiellement deux laboratoires modernes dédiés aux filières Génie Électrique et Génie Civil, un renforcement majeur de la formation technique.",
        'contenu' => "L'Institut Africain de Technologie a procédé au lancement officiel de deux laboratoires modernes dédiés aux filières Génie Électrique et Génie Civil.\n\nCes infrastructures de pointe marquent un renforcement majeur de la formation technique et pratique offerte aux étudiants : travaux dirigés sur équipements professionnels, projets appliqués et meilleure employabilité des diplômés."],
    ['slug' => 'labo-genie-electrique-operationnel', 'titre' => "IAT Niger : le laboratoire de Génie Électrique désormais pleinement opérationnel",
        'date_publication' => '2026-01-25', 'image' => 'actualites/labo-genie-electrique.jpg', 'categorie' => 'Infrastructure',
        'extrait' => "Le laboratoire de Génie Électrique de l'IAT Niger est devenu complètement opérationnel, renforçant les capacités de formation technique de l'institution.",
        'contenu' => "Le laboratoire de Génie Électrique de l'IAT Niger est désormais pleinement opérationnel.\n\nLes étudiants des filières industrielles y réalisent leurs travaux pratiques sur des équipements professionnels, consolidant l'approche « apprendre en faisant » qui fait la réputation de l'institut."],
    ['slug' => 'forage-eau-firdaous-charity', 'titre' => "L'ONG Firdaous Charity dote l'IAT d'un forage d'eau potable",
        'date_publication' => '2025-12-30', 'image' => 'actualites/forage-eau.jpg', 'categorie' => 'Partenariat',
        'extrait' => "L'ONG Firdaous Charity a offert un forage d'eau potable à l'IAT Niger, une initiative saluée pour son impact durable sur la communauté éducative.",
        'contenu' => "L'ONG Firdaous Charity a offert un forage d'eau potable à l'Institut Africain de Technologie.\n\nCette initiative, saluée par l'ensemble de la communauté éducative, améliore durablement les conditions d'études sur le campus et illustre la qualité des partenariats noués par l'institut."],
];

/**
 * Récupère les actualités : base MySQL si disponible, sinon fallback.
 */
function actualites(int $limit = 0): array
{
    $pdo = db();
    if ($pdo !== null) {
        try {
            $sql = 'SELECT * FROM actualites WHERE publie = 1 ORDER BY date_publication DESC';
            if ($limit > 0) {
                $sql .= ' LIMIT ' . $limit;
            }
            $rows = $pdo->query($sql)->fetchAll();
            if ($rows) {
                return $rows;
            }
        } catch (PDOException $e) {
            // Table absente : on retombe sur le fallback.
        }
    }
    $items = ACTUALITES_FALLBACK;
    return $limit > 0 ? array_slice($items, 0, $limit) : $items;
}

/** Récupère une actualité par slug (BDD puis fallback). */
function actualite_par_slug(string $slug): ?array
{
    $pdo = db();
    if ($pdo !== null) {
        try {
            $st = $pdo->prepare('SELECT * FROM actualites WHERE slug = ? AND publie = 1');
            $st->execute([$slug]);
            $row = $st->fetch();
            if ($row) {
                return $row;
            }
        } catch (PDOException $e) {
        }
    }
    foreach (ACTUALITES_FALLBACK as $a) {
        if ($a['slug'] === $slug) {
            return $a;
        }
    }
    return null;
}
