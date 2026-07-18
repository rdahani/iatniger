<?php
/**
 * Couche CMS — lecture/écriture du contenu éditable depuis l'admin.
 * Fallback automatique sur les constantes / valeurs par défaut si BDD absente.
 */

declare(strict_types=1);

/** Indique si les tables CMS sont disponibles. */
function cms_ready(): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    $pdo = db();
    if ($pdo === null) {
        return $ok = false;
    }
    try {
        $pdo->query('SELECT 1 FROM site_settings LIMIT 1');
        return $ok = true;
    } catch (PDOException $e) {
        return $ok = false;
    }
}

/** Lit un paramètre site (avec cache requête). */
function setting(string $cle, ?string $defaut = null): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        if (cms_ready()) {
            try {
                foreach (db()->query('SELECT cle, valeur FROM site_settings') as $row) {
                    $cache[$row['cle']] = (string) $row['valeur'];
                }
            } catch (PDOException $e) {
            }
        }
    }
    if (array_key_exists($cle, $cache)) {
        return $cache[$cle];
    }
    $map = [
        'site_name' => defined('SITE_NAME') ? SITE_NAME : 'IAT Niger',
        'site_full_name' => defined('SITE_FULL_NAME') ? SITE_FULL_NAME : 'Institut Africain de Technologie',
        'site_tagline' => defined('SITE_TAGLINE') ? SITE_TAGLINE : "Un pôle d'excellence",
        'site_email' => defined('SITE_EMAIL') ? SITE_EMAIL : 'info@iatniger.org',
        'site_phone_1' => defined('SITE_PHONE_1') ? SITE_PHONE_1 : '',
        'site_phone_2' => defined('SITE_PHONE_2') ? SITE_PHONE_2 : '',
        'site_whatsapp' => defined('SITE_WHATSAPP') ? SITE_WHATSAPP : '',
        'site_address' => defined('SITE_ADDRESS') ? SITE_ADDRESS : '',
        'site_facebook' => defined('SITE_FACEBOOK') ? SITE_FACEBOOK : '',
        'footer_cta_titre' => 'Prêt à rejoindre l\'IAT ?',
        'footer_cta_texte' => 'Préinscriptions ouvertes — gratuites et sans engagement.',
        'footer_mention' => 'Agréé par l\'État du Niger · Accréditations CAMES / ANAQ-SUP',
    ];
    return $defaut ?? ($map[$cle] ?? '');
}

/** Meta + hero d'une page. */
function cms_page(string $slug): ?array
{
    if (!cms_ready()) {
        return null;
    }
    try {
        $st = db()->prepare('SELECT * FROM cms_pages WHERE slug = ?');
        $st->execute([$slug]);
        $row = $st->fetch();
        return $row ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/** Applique les meta/hero CMS sur les variables de page si disponibles. */
function cms_apply_page(string $slug, ?string &$title, ?string &$desc, ?string &$hero_titre, ?string &$hero_texte): void
{
    $p = cms_page($slug);
    if ($p === null) {
        return;
    }
    if (!empty($p['titre_seo'])) {
        $title = $p['titre_seo'];
    }
    if (!empty($p['meta_desc'])) {
        $desc = $p['meta_desc'];
    }
    if (!empty($p['hero_titre'])) {
        $hero_titre = $p['hero_titre'];
    }
    if (!empty($p['hero_texte'])) {
        $hero_texte = $p['hero_texte'];
    }
}

/** Liste d'items CMS par type (+ groupe optionnel). */
function cms_items(string $type, ?string $groupe = null, bool $publies_seulement = true): array
{
    if (!cms_ready()) {
        return [];
    }
    try {
        $sql = 'SELECT * FROM cms_items WHERE type = ?';
        $params = [$type];
        if ($groupe !== null) {
            $sql .= ' AND groupe = ?';
            $params[] = $groupe;
        }
        if ($publies_seulement) {
            $sql .= ' AND publie = 1';
        }
        $sql .= ' ORDER BY ordre ASC, id ASC';
        $st = db()->prepare($sql);
        $st->execute($params);
        $rows = $st->fetchAll();
        foreach ($rows as &$r) {
            $r['extra'] = $r['extra'] !== null && $r['extra'] !== ''
                ? (json_decode((string) $r['extra'], true) ?: [])
                : [];
        }
        return $rows;
    } catch (PDOException $e) {
        return [];
    }
}

/** Un item unique par type+cle. */
function cms_item(string $type, string $cle): ?array
{
    if (!cms_ready()) {
        return null;
    }
    try {
        $st = db()->prepare('SELECT * FROM cms_items WHERE type = ? AND cle = ? LIMIT 1');
        $st->execute([$type, $cle]);
        $r = $st->fetch();
        if (!$r) {
            return null;
        }
        $r['extra'] = $r['extra'] !== null && $r['extra'] !== ''
            ? (json_decode((string) $r['extra'], true) ?: [])
            : [];
        return $r;
    } catch (PDOException $e) {
        return null;
    }
}

/** Niveaux de formation depuis BDD ou constante. */
function cms_niveaux(): array
{
    if (cms_ready()) {
        try {
            $rows = db()->query('SELECT * FROM cms_niveaux WHERE publie = 1 ORDER BY ordre ASC')->fetchAll();
            if ($rows) {
                $out = [];
                foreach ($rows as $r) {
                    $out[$r['slug']] = [
                        'titre' => $r['titre'],
                        'sous_titre' => $r['sous_titre'],
                        'recrutement' => $r['recrutement'],
                        'duree' => $r['duree'],
                        'dossier' => $r['dossier'],
                        'description' => $r['description'],
                    ];
                }
                return $out;
            }
        } catch (PDOException $e) {
        }
    }
    return defined('NIVEAUX') ? NIVEAUX : [];
}

/** Formations depuis BDD ou constante. */
function cms_formations(?string $niveau = null): array
{
    if (cms_ready()) {
        try {
            if ($niveau !== null) {
                $st = db()->prepare('SELECT * FROM cms_formations WHERE publie = 1 AND niveau = ? ORDER BY ordre ASC, id ASC');
                $st->execute([$niveau]);
            } else {
                $st = db()->query('SELECT * FROM cms_formations WHERE publie = 1 ORDER BY ordre ASC, id ASC');
            }
            $rows = $st->fetchAll();
            if ($rows) {
                return array_map(static function (array $r): array {
                    $deb = json_decode((string) ($r['debouches'] ?? '[]'), true) ?: [];
                    return [
                        'slug' => $r['slug'],
                        'niveau' => $r['niveau'],
                        'domaine' => $r['domaine'],
                        'titre' => $r['titre'],
                        'icone' => $r['icone'],
                        'resume' => $r['resume'],
                        'objectif' => $r['objectif'],
                        'debouches' => $deb,
                        'badge' => $r['badge'] ?: null,
                    ];
                }, $rows);
            }
        } catch (PDOException $e) {
        }
    }
    if (!defined('FORMATIONS')) {
        return [];
    }
    if ($niveau === null) {
        return FORMATIONS;
    }
    return array_values(array_filter(FORMATIONS, fn ($f) => $f['niveau'] === $niveau));
}

function cms_formation_par_slug(string $slug): ?array
{
    foreach (cms_formations() as $f) {
        if ($f['slug'] === $slug) {
            return $f;
        }
    }
    if (defined('FORMATIONS')) {
        foreach (FORMATIONS as $f) {
            if ($f['slug'] === $slug) {
                return $f;
            }
        }
    }
    return null;
}

/** FAQ avec fallback. */
function cms_faq(): array
{
    $items = cms_items('faq');
    if ($items) {
        return array_map(static fn ($i) => [$i['titre'], $i['contenu']], $items);
    }
    return [];
}

/** Partenaires. */
function cms_partenaires(): array
{
    $items = cms_items('partenaire');
    if (!$items) {
        return [];
    }
    return array_map(static function (array $i): array {
        return [
            'fichier' => $i['extra']['fichier'] ?? pathinfo((string) $i['image'], PATHINFO_FILENAME),
            'nom' => $i['titre'],
            'type' => $i['sous_titre'] ?? '',
            'desc' => $i['contenu'] ?? '',
            'image' => $i['image'],
        ];
    }, $items);
}

/** Galerie. */
function cms_galerie(): array
{
    $items = cms_items('galerie');
    if (!$items) {
        return [];
    }
    return array_map(static function (array $i): array {
        return [
            'src' => $i['image'] ?? '',
            'legende' => $i['titre'] ?? '',
            'cat' => $i['groupe'] ?? 'campus',
        ];
    }, $items);
}

/** Vidéos WEB TV. */
function cms_videos(): array
{
    $items = cms_items('video');
    if (!$items) {
        return [];
    }
    return array_map(static function (array $i): array {
        return [
            'titre' => $i['titre'],
            'vues' => (int) ($i['extra']['vues'] ?? 0),
            'path' => $i['extra']['path'] ?? ($i['url'] ?? ''),
            'image' => $i['image'] ?? '',
            'url' => $i['url'] ?? '',
        ];
    }, $items);
}

/** Documents téléchargeables. */
function cms_documents(): array
{
    $items = cms_items('document');
    if (!$items) {
        return [];
    }
    return array_map(static function (array $i): array {
        $ex = $i['extra'] ?? [];
        return [
            'fichier' => $i['url'] ?? ($ex['fichier'] ?? ''),
            'nom_dl' => $ex['nom_dl'] ?? basename((string) ($i['url'] ?? '')),
            'titre' => $i['titre'],
            'type' => $i['sous_titre'] ?? 'PDF',
            'icone' => $ex['icone'] ?? 'file-text',
            'desc' => $i['contenu'] ?? '',
            'badge' => $ex['badge'] ?? null,
        ];
    }, $items);
}

/** Témoignages. */
function cms_temoignages(): array
{
    $items = cms_items('temoignage');
    if (!$items) {
        return [];
    }
    return array_map(static function (array $i): array {
        return [
            'citation' => $i['contenu'] ?? '',
            'auteur' => $i['titre'] ?? '',
            'fonction' => $i['sous_titre'] ?? '',
            'initiales' => $i['extra']['initiales'] ?? '',
        ];
    }, $items);
}

/** Texte unique par clé (type=texte). */
function cms_texte(string $cle, string $defaut = ''): string
{
    $item = cms_item('texte', $cle);
    if ($item === null) {
        return $defaut;
    }
    $c = trim((string) ($item['contenu'] ?? ''));
    return $c !== '' ? $c : $defaut;
}

/** Extra JSON d'un texte clé. */
function cms_texte_extra(string $cle): array
{
    $item = cms_item('texte', $cle);
    return is_array($item['extra'] ?? null) ? $item['extra'] : [];
}

/** Cartes CMS (type=carte) pour un groupe. */
function cms_cartes(string $groupe): array
{
    return cms_items('carte', $groupe);
}

/** Stats CMS pour un groupe. */
function cms_stats(string $groupe): array
{
    return cms_items('stat', $groupe);
}

/** Timeline. */
function cms_timeline(string $groupe = 'a-propos'): array
{
    return cms_items('timeline', $groupe);
}

/** Alumni. */
function cms_alumni(string $groupe = 'vie-etudiante'): array
{
    return cms_items('alumni', $groupe);
}

/** Tarifs CSP. */
function cms_tarifs(string $groupe = 'csp-algoza'): array
{
    return cms_items('tarif', $groupe);
}

/** Slides hero. */
function cms_hero_slides(string $groupe = 'accueil'): array
{
    $items = cms_items('hero_slide', $groupe);
    if (!$items) {
        return [];
    }
    return array_map(static fn ($i) => [
        'src' => $i['image'] ?? '',
        'alt' => $i['titre'] ?? '',
    ], $items);
}

/** Décode JSON extra en sécurité. */
function cms_extra_encode(array $data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE);
}

/** Formate un montant FCFA. */
function cms_fcfa(int|float|string $n): string
{
    return number_format((float) $n, 0, ',', ' ') . ' F CFA';
}
