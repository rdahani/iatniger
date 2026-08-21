<?php
/**
 * CRUD générique pour le contenu CMS (cms_items), filtré par ?type= et
 * éventuellement ?groupe=. Couvre la FAQ, les partenaires, la galerie, la
 * WEB TV, les documents, les témoignages, et tous les blocs de sections
 * flexibles (carte, texte, timeline, stat, alumni, tarif, hero_slide...).
 */

declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';

$type_tmp = $_GET['type'] ?? ($_POST['type'] ?? 'faq');
$groupe_tmp = $_GET['groupe'] ?? null;
/* Permission : section agrégée ou type dédié, sinon « contenu ». */
$perm_map = [
    'faq' => 'faq', 'partenaire' => 'partenaires', 'temoignage' => 'temoignages',
    'galerie' => 'galerie', 'video' => 'web-tv', 'document' => 'documents',
];
if ($type_tmp === 'section' && $groupe_tmp) {
    $perm_cle = match ($groupe_tmp) {
        'a-propos' => 'a-propos',
        'vie-etudiante' => 'vie-etudiante',
        'csp-algoza' => 'csp',
        'admission' => 'admission',
        default => 'contenu',
    };
} else {
    $perm_cle = $perm_map[$type_tmp] ?? 'contenu';
}
require_permission(admin_can($perm_cle) ? $perm_cle : 'contenu');

/** Configuration des types connus : libellés et champs de formulaire. */
function contenu_type_config(string $type): array
{
    $types = [
        'faq' => [
            'label' => 'FAQ', 'sidebar' => 'faq', 'nouveau' => 'une question',
            'champs' => [
                'titre' => ['label' => 'Question', 'type' => 'text', 'requis' => true],
                'contenu' => ['label' => 'Réponse', 'type' => 'textarea', 'requis' => true],
            ],
        ],
        'partenaire' => [
            'label' => 'Partenaires', 'sidebar' => 'partenaires', 'nouveau' => 'un partenaire',
            'champs' => [
                'titre' => ['label' => 'Nom du partenaire', 'type' => 'text', 'requis' => true],
                'sous_titre' => ['label' => 'Type (Institutionnel, Académique, Entrepreneuriat…)', 'type' => 'text'],
                'contenu' => ['label' => 'Description', 'type' => 'textarea'],
                'image' => ['label' => 'Logo / image', 'type' => 'media', 'base' => 'img'],
            ],
            'extra_fields' => ['fichier' => 'Nom de fichier (sans extension)'],
        ],
        'galerie' => [
            'label' => 'Galerie', 'sidebar' => 'galerie', 'nouveau' => 'une photo',
            'champs' => [
                'titre' => ['label' => 'Légende', 'type' => 'text', 'requis' => true],
                'image' => ['label' => 'Photo', 'type' => 'media', 'base' => 'img', 'requis' => true],
                'groupe' => ['label' => 'Catégorie', 'type' => 'select', 'options' => [
                    'campus' => 'Campus', 'evenements' => 'Événements', 'vie-etudiante' => 'Vie étudiante',
                ]],
            ],
        ],
        'video' => [
            'label' => 'WEB TV', 'sidebar' => 'web-tv', 'nouveau' => 'une vidéo',
            'champs' => [
                'titre' => ['label' => 'Titre', 'type' => 'text', 'requis' => true],
                'image' => ['label' => 'Vignette', 'type' => 'media', 'base' => 'img'],
                'url' => ['label' => 'URL externe (optionnel)', 'type' => 'text'],
            ],
            'extra_fields' => ['vues' => 'Nombre de vues', 'path' => 'Identifiant du lecteur WEB TV (path)'],
        ],
        'document' => [
            'label' => 'Documents', 'sidebar' => 'documents', 'nouveau' => 'un document',
            'champs' => [
                'titre' => ['label' => 'Titre', 'type' => 'text', 'requis' => true],
                'sous_titre' => ['label' => 'Type (PDF, Image, PNG…)', 'type' => 'text'],
                'contenu' => ['label' => 'Description', 'type' => 'textarea'],
                'url' => ['label' => 'Fichier', 'type' => 'media', 'base' => 'assets', 'accept' => 'all', 'requis' => true],
            ],
            'extra_fields' => ['nom_dl' => 'Nom du fichier au téléchargement', 'icone' => ['label' => 'Icône', 'type' => 'icon'], 'badge' => 'Badge (optionnel)'],
        ],
        'temoignage' => [
            'label' => 'Témoignages', 'sidebar' => 'temoignages', 'nouveau' => 'un témoignage',
            'champs' => [
                'titre' => ['label' => 'Auteur', 'type' => 'text', 'requis' => true],
                'sous_titre' => ['label' => 'Fonction', 'type' => 'text'],
                'contenu' => ['label' => 'Citation', 'type' => 'textarea', 'requis' => true],
            ],
            'extra_fields' => ['initiales' => 'Initiales (avatar)'],
        ],
        'carte' => [
            'label' => 'Cartes', 'sidebar' => '', 'nouveau' => 'une carte',
            'champs' => [
                'titre' => ['label' => 'Titre', 'type' => 'text', 'requis' => true],
                'contenu' => ['label' => 'Texte', 'type' => 'textarea'],
                'groupe' => ['label' => 'Groupe', 'type' => 'text'],
            ],
            'extra_fields' => ['icone' => ['label' => 'Icône', 'type' => 'icon']],
        ],
        'texte' => [
            'label' => 'Textes', 'sidebar' => '', 'nouveau' => 'un texte',
            'champs' => [
                'cle' => ['label' => 'Clé unique (ex. accueil_hero_lead)', 'type' => 'text', 'requis' => true],
                'titre' => ['label' => 'Titre (optionnel)', 'type' => 'text'],
                'contenu' => ['label' => 'Contenu', 'type' => 'textarea'],
            ],
        ],
        'timeline' => [
            'label' => 'Chronologie', 'sidebar' => '', 'nouveau' => 'une étape',
            'champs' => [
                'titre' => ['label' => 'Année', 'type' => 'text', 'requis' => true],
                'sous_titre' => ['label' => "Titre de l'étape", 'type' => 'text'],
                'contenu' => ['label' => 'Description', 'type' => 'textarea'],
                'groupe' => ['label' => 'Groupe', 'type' => 'text'],
            ],
        ],
        'stat' => [
            'label' => 'Statistiques', 'sidebar' => '', 'nouveau' => 'une statistique',
            'champs' => [
                'titre' => ['label' => 'Libellé (ex. diplômés formés)', 'type' => 'text', 'requis' => true],
                'groupe' => ['label' => 'Groupe', 'type' => 'text'],
            ],
            'extra_fields' => ['valeur' => 'Valeur (nombre)', 'suffixe' => 'Suffixe (ex. +)'],
        ],
        'alumni' => [
            'label' => 'Alumni', 'sidebar' => 'vie-etudiante', 'nouveau' => 'un membre du bureau',
            'champs' => [
                'titre' => ['label' => 'Nom', 'type' => 'text', 'requis' => true],
                'sous_titre' => ['label' => 'Fonction', 'type' => 'text'],
                'contenu' => ['label' => 'Description', 'type' => 'textarea'],
                'groupe' => ['label' => 'Groupe', 'type' => 'text'],
            ],
            'extra_fields' => ['initiales' => 'Initiales (avatar)'],
        ],
        'tarif' => [
            'label' => 'Tarifs', 'sidebar' => 'csp', 'nouveau' => 'un tarif',
            'champs' => [
                'titre' => ['label' => 'Titre (ex. Collège)', 'type' => 'text', 'requis' => true],
                'sous_titre' => ['label' => 'Sous-titre', 'type' => 'text'],
                'groupe' => ['label' => 'Groupe', 'type' => 'text'],
            ],
            'extra_fields' => ['inscription' => 'Inscription (F CFA)', 'fournitures' => 'Fournitures (F CFA)', 'tenues' => 'Tenues (F CFA)', 'scolarite' => 'Scolarité (F CFA)', 'total' => 'Total annuel (F CFA)'],
        ],
        'hero_slide' => [
            'label' => "Diaporama d'accueil", 'sidebar' => '', 'nouveau' => 'une image',
            'champs' => [
                'titre' => ['label' => 'Texte alternatif (alt)', 'type' => 'text', 'requis' => true],
                'image' => ['label' => 'Image', 'type' => 'media', 'base' => 'img', 'requis' => true],
                'groupe' => ['label' => 'Groupe', 'type' => 'text'],
            ],
        ],
    ];

    return $types[$type] ?? [
        'label' => ucfirst($type), 'sidebar' => '', 'nouveau' => 'un élément',
        'champs' => [
            'titre' => ['label' => 'Titre', 'type' => 'text'],
            'sous_titre' => ['label' => 'Sous-titre', 'type' => 'text'],
            'contenu' => ['label' => 'Contenu', 'type' => 'textarea'],
            'image' => ['label' => 'Image', 'type' => 'text'],
            'url' => ['label' => 'URL', 'type' => 'text'],
            'groupe' => ['label' => 'Groupe', 'type' => 'text'],
            'cle' => ['label' => 'Clé unique (optionnel)', 'type' => 'text'],
        ],
    ];
}

/** Page publique correspondant au contenu édité (pour le bouton « Voir la page »). */
function contenu_page_publique(string $type, ?string $groupe): ?string
{
    $map_types = [
        'faq' => 'faq', 'partenaire' => 'partenaires', 'galerie' => 'galerie',
        'video' => 'web-tv', 'document' => 'telechargements', 'temoignage' => '',
    ];
    if (array_key_exists($type, $map_types)) {
        return $map_types[$type];
    }
    $g = (string) $groupe;
    if ($g === '') {
        return null;
    }
    if (str_starts_with($g, 'a-propos')) {
        return 'a-propos';
    }
    if (str_starts_with($g, 'vie-etudiante') || str_starts_with($g, 'vie-')) {
        return 'vie-etudiante';
    }
    if (str_starts_with($g, 'csp')) {
        return 'csp-algoza';
    }
    if (str_starts_with($g, 'admission')) {
        return 'admission';
    }
    if (str_starts_with($g, 'accueil')) {
        return '';
    }
    return null;
}

/** Détermine la clé de la barre latérale à mettre en surbrillance. */
function contenu_sidebar_actif(string $type, ?string $groupe, array $config): string
{
    if ($config['sidebar'] !== '') {
        return $config['sidebar'];
    }
    $g = (string) $groupe;
    if (str_starts_with($g, 'a-propos')) {
        return 'a-propos';
    }
    if (str_starts_with($g, 'vie-') || str_starts_with($g, 'admission-etapes') === false && str_starts_with($g, 'vie')) {
        return 'vie-etudiante';
    }
    if (str_starts_with($g, 'csp')) {
        return 'csp';
    }
    if (str_starts_with($g, 'admission')) {
        return 'admission';
    }
    return 'faq';
}

$TYPES_CONNUS = ['faq', 'partenaire', 'galerie', 'video', 'document', 'temoignage'];

$type = trim((string) ($_GET['type'] ?? 'faq'));
if ($type === '') {
    $type = 'faq';
}
$groupe = isset($_GET['groupe']) && $_GET['groupe'] !== '' ? (string) $_GET['groupe'] : null;
$config = contenu_type_config($type);
$sidebar_actif = contenu_sidebar_actif($type, $groupe, $config);

$pdo = admin_require_cms();
$notice = '';
$erreur = '';
$action = $_GET['action'] ?? 'liste';

/** Construit l'URL de retour à la liste avec les filtres courants. */
function contenu_url_liste(string $type, ?string $groupe): string
{
    $q = 'type=' . rawurlencode($type);
    if ($groupe !== null) {
        $q .= '&groupe=' . rawurlencode($groupe);
    }
    return url('admin/contenu.php?' . $q);
}

if ($pdo !== null && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $op = $_POST['op'] ?? '';
    $post_type = (string) ($_POST['type'] ?? $type);
    $post_groupe = isset($_POST['filtre_groupe']) && $_POST['filtre_groupe'] !== '' ? (string) $_POST['filtre_groupe'] : null;
    $cfg = contenu_type_config($post_type);

    if ($op === 'enregistrer') {
        $id = (int) ($_POST['id'] ?? 0);
        $data = ['type' => $post_type];
        $data['titre'] = isset($_POST['titre']) ? trim((string) $_POST['titre']) : null;
        $data['sous_titre'] = isset($_POST['sous_titre']) ? trim((string) $_POST['sous_titre']) : null;
        $data['contenu'] = isset($_POST['contenu']) ? trim((string) $_POST['contenu']) : null;
        $data['image'] = isset($_POST['image']) ? trim((string) $_POST['image']) : null;
        $data['url'] = isset($_POST['url']) ? trim((string) $_POST['url']) : null;
        $data['groupe'] = isset($_POST['groupe']) && trim((string) $_POST['groupe']) !== '' ? trim((string) $_POST['groupe']) : null;
        $data['cle'] = isset($_POST['cle']) && trim((string) $_POST['cle']) !== '' ? trim((string) $_POST['cle']) : null;
        $data['ordre'] = (int) ($_POST['ordre'] ?? 0);
        $data['publie'] = isset($_POST['publie']) ? 1 : 0;

        /* Nouvel élément : place-le automatiquement en fin de liste plutôt que de forcer ordre=0. */
        if ($id === 0 && $data['ordre'] === 0) {
            $whereSql = 'type = ?';
            $params = [$data['type']];
            if ($data['groupe'] !== null) {
                $whereSql .= ' AND groupe = ?';
                $params[] = $data['groupe'];
            } else {
                $whereSql .= ' AND groupe IS NULL';
            }
            $stMax = $pdo->prepare("SELECT COALESCE(MAX(ordre), -1) FROM cms_items WHERE $whereSql");
            $stMax->execute($params);
            $data['ordre'] = ((int) $stMax->fetchColumn()) + 1;
        }

        /* Champ image via upload de fichier (facultatif, prioritaire sur le champ texte). */
        $televerse = admin_upload('image_fichier', 'uploads');
        if ($televerse !== null) {
            $data['image'] = $televerse;
        }

        if (!empty($cfg['extra_fields'])) {
            $extra = [];
            foreach (array_keys($cfg['extra_fields']) as $ek) {
                $v = trim((string) ($_POST['extra'][$ek] ?? ''));
                if ($v !== '') {
                    $extra[$ek] = ctype_digit($v) ? (int) $v : $v;
                }
            }
            $data['extra'] = $extra ? cms_extra_encode($extra) : null;
        } else {
            $raw = trim((string) ($_POST['extra_json'] ?? ''));
            if ($raw === '') {
                $data['extra'] = null;
            } else {
                $decoded = json_decode($raw, true);
                $data['extra'] = json_last_error() === JSON_ERROR_NONE ? cms_extra_encode((array) $decoded) : $raw;
            }
        }

        /* Validation des champs requis. */
        $manquant = '';
        foreach ($cfg['champs'] as $key => $def) {
            if (!empty($def['requis']) && trim((string) ($data[$key] ?? '')) === '') {
                $manquant = $def['label'];
                break;
            }
        }

        if ($manquant !== '') {
            $erreur = 'Le champ « ' . $manquant . ' » est obligatoire.';
            $action = $id > 0 ? 'editer' : 'nouvelle';
        } else {
            try {
                if ($id > 0) {
                    $st = $pdo->prepare('UPDATE cms_items SET type=?, groupe=?, cle=?, titre=?, sous_titre=?, contenu=?, extra=?, image=?, url=?, ordre=?, publie=? WHERE id=?');
                    $st->execute([$data['type'], $data['groupe'], $data['cle'], $data['titre'], $data['sous_titre'], $data['contenu'], $data['extra'], $data['image'], $data['url'], $data['ordre'], $data['publie'], $id]);
                    $notice = 'Élément mis à jour.';
                } else {
                    $st = $pdo->prepare('INSERT INTO cms_items (type, groupe, cle, titre, sous_titre, contenu, extra, image, url, ordre, publie) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
                    $st->execute([$data['type'], $data['groupe'], $data['cle'], $data['titre'], $data['sous_titre'], $data['contenu'], $data['extra'], $data['image'], $data['url'], $data['ordre'], $data['publie']]);
                    $notice = 'Élément créé.';
                }
                $action = 'liste';
            } catch (PDOException $e) {
                $erreur = str_contains($e->getMessage(), 'uq_type_cle')
                    ? 'Cette clé est déjà utilisée pour ce type de contenu.'
                    : "Erreur d'enregistrement : " . $e->getMessage();
                $action = $id > 0 ? 'editer' : 'nouvelle';
            }
        }
    } elseif ($op === 'supprimer') {
        $pdo->prepare('DELETE FROM cms_items WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
        $notice = 'Élément supprimé.';
    } elseif ($op === 'basculer') {
        $pdo->prepare('UPDATE cms_items SET publie = 1 - publie WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
        $notice = 'Statut de publication modifié.';
    } elseif ($op === 'monter' || $op === 'descendre') {
        $id = (int) ($_POST['id'] ?? 0);
        $st = $pdo->prepare('SELECT * FROM cms_items WHERE id = ?');
        $st->execute([$id]);
        $courant = $st->fetch();
        if ($courant) {
            $whereSql = 'type = ?';
            $params = [$courant['type']];
            if ($courant['groupe'] !== null) {
                $whereSql .= ' AND groupe = ?';
                $params[] = $courant['groupe'];
            } else {
                $whereSql .= ' AND groupe IS NULL';
            }
            if ($op === 'monter') {
                $whereSql .= ' AND (ordre < ? OR (ordre = ? AND id < ?)) ORDER BY ordre DESC, id DESC';
            } else {
                $whereSql .= ' AND (ordre > ? OR (ordre = ? AND id > ?)) ORDER BY ordre ASC, id ASC';
            }
            $params[] = (int) $courant['ordre'];
            $params[] = (int) $courant['ordre'];
            $params[] = $id;
            $st2 = $pdo->prepare("SELECT * FROM cms_items WHERE $whereSql LIMIT 1");
            $st2->execute($params);
            $voisin = $st2->fetch();
            if ($voisin) {
                $pdo->prepare('UPDATE cms_items SET ordre = ? WHERE id = ?')->execute([(int) $voisin['ordre'], (int) $courant['id']]);
                $pdo->prepare('UPDATE cms_items SET ordre = ? WHERE id = ?')->execute([(int) $courant['ordre'], (int) $voisin['id']]);
                $notice = 'Ordre mis à jour.';
            }
        }
    }
    /* Après un traitement en POST, on repart avec les filtres d'origine. */
    $type = $post_type;
    $groupe = $post_groupe;
    $config = contenu_type_config($type);
    $sidebar_actif = contenu_sidebar_actif($type, $groupe, $config);
}

$edition = null;
if (($action === 'editer') && $pdo !== null) {
    $st = $pdo->prepare('SELECT * FROM cms_items WHERE id = ?');
    $st->execute([(int) ($_GET['id'] ?? $_POST['id'] ?? 0)]);
    $edition = $st->fetch() ?: null;
    if ($edition !== null) {
        $edition['extra'] = $edition['extra'] !== null && $edition['extra'] !== ''
            ? (json_decode((string) $edition['extra'], true) ?: [])
            : [];
    } else {
        $action = 'liste';
    }
}

/**
 * "section" est un alias virtuel utilisé par les liens historiques de la barre
 * latérale (type=section&groupe=a-propos, vie-etudiante, csp-algoza, admission) :
 * il agrège tous les cms_items dont le groupe (ou la clé) commence par ce préfixe,
 * quel que soit leur type réel (carte, texte, timeline, stat, alumni, tarif...).
 */
$est_section_agregee = $type === 'section' && $groupe !== null;

$liste = [];
if ($pdo !== null && $action === 'liste') {
    if ($est_section_agregee) {
        $sql = 'SELECT * FROM cms_items WHERE groupe = ? OR groupe LIKE ? OR cle LIKE ? ORDER BY type ASC, ordre ASC, id ASC';
        $params = [$groupe, $groupe . '-%', $groupe . '_%'];
    } else {
        $sql = 'SELECT * FROM cms_items WHERE type = ?';
        $params = [$type];
        if ($groupe !== null) {
            $sql .= ' AND groupe = ?';
            $params[] = $groupe;
        }
        $sql .= ' ORDER BY ordre ASC, id ASC';
    }
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $liste = $st->fetchAll();
}

/* Groupes distincts existants pour ce type (aide à la navigation). */
$groupes_existants = [];
if ($pdo !== null) {
    $st = $pdo->prepare('SELECT DISTINCT groupe FROM cms_items WHERE type = ? AND groupe IS NOT NULL ORDER BY groupe');
    $st->execute([$type]);
    $groupes_existants = array_column($st->fetchAll(), 'groupe');
}

admin_head($config['label']);
?>
<div class="admin-layout">
  <?php admin_sidebar($sidebar_actif); ?>
  <main class="admin-main">

    <?php admin_flash($notice, $erreur); ?>

    <?php if ($pdo === null) : ?>
      <div class="admin-header"><h1 class="h2">Contenu</h1></div>
      <div class="alert alert-danger"><?= icon('alert-triangle', 18) ?><div>Le CMS n'est pas encore installé. <a href="<?= url('admin/install-cms.php') ?>">Installer le CMS</a> pour gérer ce contenu.</div></div>

    <?php elseif ($action === 'nouvelle' || $action === 'editer') :
        $val = static fn (string $k, $defaut = '') => $edition[$k] ?? ($_POST[$k] ?? $defaut); ?>
      <div class="admin-header">
        <h1 class="h2"><?= $edition ? 'Modifier ' . e($config['nouveau']) : 'Ajouter ' . e($config['nouveau']) ?> — <?= e($config['label']) ?></h1>
        <a class="btn btn-outline" href="<?= contenu_url_liste($type, $groupe) ?>">← Retour à la liste</a>
      </div>
      <div class="admin-card">
        <form method="post" action="<?= url('admin/contenu.php') ?>" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="op" value="enregistrer">
          <input type="hidden" name="type" value="<?= e($type) ?>">
          <input type="hidden" name="filtre_groupe" value="<?= e((string) $groupe) ?>">
          <input type="hidden" name="id" value="<?= (int) ($edition['id'] ?? 0) ?>">
          <div class="form-grid">
            <?php foreach ($config['champs'] as $key => $def) :
                if ($key === 'groupe' && ($def['type'] ?? '') !== 'select') {
                    continue;
                }
                $ftype = $def['type'] ?? 'text';
                if ($ftype === 'media') {
                    admin_media_field($key, (string) $val($key), [
                        'id' => 'ci-' . $key,
                        'label' => $def['label'] . (!empty($def['requis']) ? ' *' : ''),
                        'base' => $def['base'] ?? 'img',
                        'accept' => $def['accept'] ?? 'image',
                        'required' => !empty($def['requis']),
                        'full' => true,
                    ]);
                    continue;
                }
                if ($ftype === 'icon') {
                    admin_icon_field($key, (string) $val($key, 'book-open'), [
                        'id' => 'ci-' . $key,
                        'label' => $def['label'],
                    ]);
                    continue;
                }
                $full = $ftype === 'textarea'; ?>
            <div class="form-field <?= $full ? 'full' : '' ?>">
              <label for="ci-<?= e($key) ?>"><?= e($def['label']) ?><?= !empty($def['requis']) ? ' *' : '' ?></label>
              <?php if ($ftype === 'textarea') : ?>
              <textarea id="ci-<?= e($key) ?>" name="<?= e($key) ?>" style="min-height: 120px;" <?= !empty($def['requis']) ? 'required' : '' ?>><?= e((string) $val($key)) ?></textarea>
              <?php elseif ($ftype === 'select') : ?>
              <select id="ci-<?= e($key) ?>" name="<?= e($key) ?>">
                <option value="">— Choisir —</option>
                <?php foreach ($def['options'] as $ov => $ol) : ?>
                <option value="<?= e($ov) ?>" <?= (string) $val($key) === $ov ? 'selected' : '' ?>><?= e($ol) ?></option>
                <?php endforeach; ?>
              </select>
              <?php else : ?>
              <input type="text" id="ci-<?= e($key) ?>" name="<?= e($key) ?>" value="<?= e((string) $val($key)) ?>" <?= !empty($def['requis']) ? 'required' : '' ?>>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <?php if (!array_key_exists('groupe', $config['champs']) || ($config['champs']['groupe']['type'] ?? '') !== 'select') : ?>
            <div class="form-field">
              <label for="ci-groupe-libre">Groupe (regroupement libre, optionnel)</label>
              <input type="text" id="ci-groupe-libre" name="groupe" value="<?= e((string) $val('groupe', (string) $groupe)) ?>" list="ci-groupes-existants">
              <datalist id="ci-groupes-existants">
                <?php foreach ($groupes_existants as $g) : ?><option value="<?= e($g) ?>"><?php endforeach; ?>
              </datalist>
            </div>
            <?php endif; ?>
            <?php if (!array_key_exists('cle', $config['champs'])) : ?>
            <div class="form-field">
              <label for="ci-cle-libre">Clé unique (optionnel)</label>
              <input type="text" id="ci-cle-libre" name="cle" value="<?= e((string) $val('cle')) ?>">
            </div>
            <?php endif; ?>
            <?php if (!array_key_exists('image', $config['champs'])) : ?>
            <?php admin_media_field('image', (string) $val('image'), [
                'id' => 'ci-image-libre',
                'label' => 'Image (optionnel)',
                'base' => 'img',
                'accept' => 'image',
                'full' => true,
            ]); ?>
            <?php endif; ?>
            <?php if (!array_key_exists('url', $config['champs'])) : ?>
            <div class="form-field">
              <label for="ci-url-libre">URL externe (optionnel)</label>
              <input type="text" id="ci-url-libre" name="url" value="<?= e((string) $val('url')) ?>">
            </div>
            <?php endif; ?>
            <div class="form-field">
              <label for="ci-ordre">Ordre d'affichage</label>
              <input type="number" id="ci-ordre" name="ordre" value="<?= (int) $val('ordre', 0) ?>">
            </div>

            <?php if (!empty($config['extra_fields'])) : ?>
            <?php foreach ($config['extra_fields'] as $ek => $el) :
                $extra_val = (string) ($edition['extra'][$ek] ?? ($_POST['extra'][$ek] ?? ''));
                if (is_array($el) && ($el['type'] ?? '') === 'icon') {
                    admin_icon_field('extra[' . $ek . ']', $extra_val !== '' ? $extra_val : 'award', [
                        'id' => 'ci-extra-' . $ek,
                        'label' => $el['label'] ?? $ek,
                    ]);
                    continue;
                }
                $elabel = is_array($el) ? ($el['label'] ?? $ek) : $el;
                ?>
            <div class="form-field">
              <label for="ci-extra-<?= e($ek) ?>"><?= e($elabel) ?></label>
              <input type="text" id="ci-extra-<?= e($ek) ?>" name="extra[<?= e($ek) ?>]" value="<?= e($extra_val) ?>">
            </div>
            <?php endforeach; ?>
            <?php else : ?>
            <div class="form-field full">
              <label for="ci-extra-json">Données complémentaires (JSON libre, optionnel)</label>
              <textarea id="ci-extra-json" name="extra_json" style="min-height: 90px; font-family: monospace;" placeholder="{&quot;cle&quot;: &quot;valeur&quot;}"><?= e($edition ? cms_extra_encode($edition['extra'] ?? []) : ($_POST['extra_json'] ?? '')) ?></textarea>
            </div>
            <?php endif; ?>

            <div class="form-field full">
              <label style="display: flex; align-items: center; gap: 0.6rem; font-weight: 500;">
                <input type="checkbox" name="publie" style="width: auto;" <?= (int) $val('publie', 1) === 1 ? 'checked' : '' ?>> Publier immédiatement
              </label>
            </div>
          </div>
          <button class="btn btn-primary btn-lg" type="submit" style="margin-top: 1.4rem;">Enregistrer</button>
        </form>
      </div>

    <?php else : ?>
      <div class="admin-header">
        <h1 class="h2"><?= e($config['label']) ?><?= $groupe !== null ? ' — ' . e($groupe) : '' ?></h1>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
          <?php $page_publique = contenu_page_publique($type, $groupe);
          if ($page_publique !== null) {
              echo admin_voir_page($page_publique);
          } ?>
          <?php
          $textes_page_map = [
              'faq' => ['faq.php', 'Textes de la page FAQ'],
              'partenaire' => ['partenaires.php', 'Textes de la page Partenaires'],
              'video' => ['web-tv.php', 'Textes de la page WEB TV'],
              'document' => ['telechargements.php', 'Textes de la page Téléchargements'],
          ];
          if (isset($textes_page_map[$type])) :
              [$tf, $tl] = $textes_page_map[$type]; ?>
          <a class="btn btn-outline" href="<?= url('admin/' . $tf) ?>"><?= icon('edit', 16) ?> <?= e($tl) ?></a>
          <?php endif; ?>
          <?php if (!$est_section_agregee) : ?>
          <a class="btn btn-primary" href="<?= url('admin/contenu.php?type=' . rawurlencode($type) . ($groupe !== null ? '&groupe=' . rawurlencode($groupe) : '') . '&action=nouvelle') ?>"><?= icon('plus', 16) ?> Ajouter <?= e($config['nouveau']) ?></a>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($est_section_agregee) : ?>
      <div class="alert alert-success" style="margin-bottom: 1.4rem;">
        <?= icon('check-circle', 18) ?>
        <div>Cette vue regroupe tous les blocs de contenu liés à « <?= e($groupe) ?> » (cartes, textes, statistiques, chronologie…), quel que soit leur type. Pour ajouter un nouvel élément, choisissez son type ci-dessous :
          <?php foreach (['carte', 'texte', 'timeline', 'stat', 'alumni', 'tarif'] as $t) : $c = contenu_type_config($t); ?>
          <a class="btn btn-outline" style="margin: 0.3rem 0.3rem 0 0;" href="<?= url('admin/contenu.php?type=' . rawurlencode($t) . '&groupe=' . rawurlencode($groupe) . '&action=nouvelle') ?>"><?= icon('plus', 14) ?> <?= e($c['label']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php else : ?>
      <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.4rem;">
        <?php foreach ($TYPES_CONNUS as $t) : $c = contenu_type_config($t); ?>
        <a class="btn <?= $t === $type ? 'btn-primary' : 'btn-outline' ?>" href="<?= url('admin/contenu.php?type=' . rawurlencode($t)) ?>"><?= e($c['label']) ?></a>
        <?php endforeach; ?>
      </div>

      <?php if ($groupes_existants) : ?>
      <p class="caption" style="margin-bottom: 1rem;">
        Groupes pour ce type :
        <a href="<?= url('admin/contenu.php?type=' . rawurlencode($type)) ?>"><?= $groupe === null ? '<strong>Tous</strong>' : 'Tous' ?></a>
        <?php foreach ($groupes_existants as $g) : ?>
        · <a href="<?= url('admin/contenu.php?type=' . rawurlencode($type) . '&groupe=' . rawurlencode($g)) ?>"><?= $g === $groupe ? '<strong>' . e($g) . '</strong>' : e($g) ?></a>
        <?php endforeach; ?>
      </p>
      <?php endif; ?>
      <?php endif; ?>

      <div class="table-wrap">
        <table class="table">
          <thead><tr><?php if ($est_section_agregee) : ?><th scope="col">Type</th><?php endif; ?><th scope="col">Contenu</th><th scope="col">Groupe</th><th scope="col">Ordre</th><th scope="col">Statut</th><th scope="col">Actions</th></tr></thead>
          <tbody>
            <?php if (!$liste) : ?>
            <tr><td colspan="6">Aucun élément. <?php if (!$est_section_agregee) : ?><a href="<?= url('admin/contenu.php?type=' . rawurlencode($type) . ($groupe !== null ? '&groupe=' . rawurlencode($groupe) : '') . '&action=nouvelle') ?>">Ajoutez-en un</a>.<?php endif; ?></td></tr>
            <?php endif; ?>
            <?php foreach ($liste as $i => $it) :
                $it_type = $est_section_agregee ? (string) $it['type'] : $type;
                $it_groupe = $est_section_agregee ? $it['groupe'] : $groupe; ?>
            <tr>
              <?php if ($est_section_agregee) : ?><td><span class="badge badge-primary"><?= e($it_type) ?></span></td><?php endif; ?>
              <td>
                <strong><?= e(mb_strimwidth((string) ($it['titre'] ?: $it['cle'] ?: '(sans titre)'), 0, 60, '…')) ?></strong>
                <?php if (!empty($it['sous_titre'])) : ?><br><span class="caption"><?= e($it['sous_titre']) ?></span><?php endif; ?>
              </td>
              <td><?= e((string) ($it['groupe'] ?? ($it['cle'] ?? '—'))) ?></td>
              <td><?= (int) $it['ordre'] ?></td>
              <td><?= (int) $it['publie'] === 1 ? '<span class="badge badge-success">Publié</span>' : '<span class="badge badge-accent">Brouillon</span>' ?></td>
              <td>
                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                  <?php if (!$est_section_agregee) : ?>
                  <form method="post" action="<?= url('admin/contenu.php') ?>" style="display: inline;">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="op" value="monter">
                    <input type="hidden" name="type" value="<?= e($type) ?>">
                    <input type="hidden" name="filtre_groupe" value="<?= e((string) $groupe) ?>">
                    <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
                    <button class="icon-btn" type="submit" aria-label="Monter" title="Monter" <?= $i === 0 ? 'disabled' : '' ?>>↑</button>
                  </form>
                  <form method="post" action="<?= url('admin/contenu.php') ?>" style="display: inline;">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="op" value="descendre">
                    <input type="hidden" name="type" value="<?= e($type) ?>">
                    <input type="hidden" name="filtre_groupe" value="<?= e((string) $groupe) ?>">
                    <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
                    <button class="icon-btn" type="submit" aria-label="Descendre" title="Descendre" <?= $i === count($liste) - 1 ? 'disabled' : '' ?>>↓</button>
                  </form>
                  <?php endif; ?>
                  <a class="icon-btn" href="<?= url('admin/contenu.php?type=' . rawurlencode($it_type) . ($it_groupe !== null ? '&groupe=' . rawurlencode((string) $it_groupe) : '') . '&action=editer&id=' . (int) $it['id']) ?>" aria-label="Modifier" title="Modifier"><?= icon('edit', 17) ?></a>
                  <form method="post" action="<?= url('admin/contenu.php') ?>" style="display: inline;">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="op" value="basculer">
                    <input type="hidden" name="type" value="<?= e($type) ?>">
                    <input type="hidden" name="filtre_groupe" value="<?= e((string) $groupe) ?>">
                    <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
                    <button class="icon-btn" type="submit" aria-label="Publier / dépublier" title="Publier / dépublier"><?= icon((int) $it['publie'] === 1 ? 'eye' : 'check', 17) ?></button>
                  </form>
                  <form method="post" action="<?= url('admin/contenu.php') ?>" style="display: inline;" onsubmit="return confirm('Supprimer définitivement cet élément ?');">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="op" value="supprimer">
                    <input type="hidden" name="type" value="<?= e($type) ?>">
                    <input type="hidden" name="filtre_groupe" value="<?= e((string) $groupe) ?>">
                    <input type="hidden" name="id" value="<?= (int) $it['id'] ?>">
                    <button class="icon-btn" type="submit" aria-label="Supprimer" title="Supprimer" style="color: var(--danger);"><?= icon('trash', 17) ?></button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>
</div>
</body>
</html>
