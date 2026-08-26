<?php
/** Garde d'authentification, droits d'accès et helpers de l'espace admin. */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/icons.php';

/** Modules du menu (mêmes clés que les permissions), groupés comme la sidebar. */
function admin_menu_modules(): array
{
    return [
        'Pilotage' => [
            'dashboard' => 'Tableau de bord',
            'parametres' => 'Paramètres du site',
            'pages' => 'Pages & SEO',
            'utilisateurs' => 'Utilisateurs & droits',
        ],
        'Contenus' => [
            'accueil' => 'Accueil',
            'actualites' => 'Actualités',
            'formations' => 'Formations',
            'faq' => 'FAQ',
            'partenaires' => 'Partenaires',
            'temoignages' => 'Témoignages',
            'galerie' => 'Galerie',
            'web-tv' => 'WEB TV',
            'documents' => 'Documents',
            'a-propos' => 'À propos',
            'vie-etudiante' => 'Vie étudiante',
            'csp' => 'CSP Algoza',
            'admission' => 'Admission',
            'contenu' => 'Contenus CMS (générique)',
        ],
        'Demandes' => [
            'messages' => 'Messages',
            'preinscriptions' => 'Préinscriptions',
            'newsletter' => 'Newsletter',
        ],
        'Médias' => [
            'medias' => 'Médiathèque',
        ],
    ];
}

/** Liste plate de toutes les clés de modules. */
function admin_all_perm_keys(): array
{
    $keys = [];
    foreach (admin_menu_modules() as $items) {
        foreach ($items as $k => $_label) {
            $keys[] = $k;
        }
    }
    return $keys;
}

/** Rôles prédéfinis (modèles de cases à cocher). */
function admin_roles_meta(): array
{
    return [
        'admin' => [
            'label' => 'Administrateur',
            'desc' => 'Accès complet à toutes les sections.',
            'perms' => ['*'],
        ],
        'communication' => [
            'label' => 'Communication',
            'desc' => 'Actualités, pages, médias, FAQ, galerie, WEB TV, partenaires…',
            'perms' => [
                'dashboard', 'accueil', 'actualites', 'formations', 'contenu',
                'faq', 'partenaires', 'temoignages', 'galerie', 'web-tv', 'documents',
                'a-propos', 'vie-etudiante', 'csp', 'admission', 'pages',
                'messages', 'medias', 'newsletter',
            ],
        ],
        'scolarite' => [
            'label' => 'Scolarité',
            'desc' => 'Préinscriptions, messages, formations et newsletter.',
            'perms' => [
                'dashboard', 'formations', 'admission', 'preinscriptions',
                'messages', 'newsletter',
            ],
        ],
        'personnalise' => [
            'label' => 'Personnalisé',
            'desc' => 'Droits définis uniquement par les cases cochées ci-dessous.',
            'perms' => ['dashboard'],
        ],
    ];
}

/** Résout la liste de permissions effective à partir du rôle + JSON personnalisé. */
function admin_resolve_perms(?string $role, $permissions_json): array
{
    $role = $role ?: 'communication';
    $custom = null;
    if (is_array($permissions_json)) {
        $custom = $permissions_json;
    } elseif (is_string($permissions_json) && $permissions_json !== '') {
        $decoded = json_decode($permissions_json, true);
        if (is_array($decoded)) {
            $custom = $decoded;
        }
    }

    if (is_array($custom) && $custom !== []) {
        $custom = array_values(array_unique(array_map('strval', $custom)));
        if (in_array('*', $custom, true)) {
            return ['*'];
        }
        return $custom;
    }

    $meta = admin_roles_meta()[$role] ?? admin_roles_meta()['communication'];
    return $meta['perms'];
}

/** Assure la colonne permissions sur users. */
function admin_ensure_permissions_column(?PDO $pdo = null): void
{
    $pdo = $pdo ?? db();
    if ($pdo === null) {
        return;
    }
    static $done = false;
    if ($done) {
        return;
    }
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'permissions'")->fetch();
        if (!$cols) {
            $pdo->exec('ALTER TABLE users ADD COLUMN permissions JSON NULL AFTER role');
        }
        /* Élargir l'ENUM role pour accepter « personnalise ». */
        $pdo->exec("ALTER TABLE users MODIFY role ENUM('admin','communication','scolarite','personnalise') NOT NULL DEFAULT 'admin'");
    } catch (PDOException $e) {
        /* ignore */
    }
    $done = true;
}

function admin_role(): string
{
    $role = (string) ($_SESSION['admin_role'] ?? 'admin');
    return array_key_exists($role, admin_roles_meta()) ? $role : 'admin';
}

/** Permissions de la session courante. */
function admin_session_perms(): array
{
    if (!empty($_SESSION['admin_perms']) && is_array($_SESSION['admin_perms'])) {
        return $_SESSION['admin_perms'];
    }
    return admin_resolve_perms(admin_role(), null);
}

function admin_can(string $perm): bool
{
    $perms = admin_session_perms();
    return in_array('*', $perms, true) || in_array($perm, $perms, true);
}

/** Charge le profil droits dans la session (après login ou mise à jour). */
function admin_load_session_user(array $user): void
{
    $_SESSION['admin_id'] = (int) $user['id'];
    $_SESSION['admin_nom'] = $user['nom'];
    $_SESSION['admin_role'] = $user['role'] ?? 'admin';
    $_SESSION['admin_perms'] = admin_resolve_perms(
        $user['role'] ?? 'admin',
        $user['permissions'] ?? null
    );
}

/** Redirige vers la connexion si non authentifié. */
function require_login(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . url('admin/index.php'));
        exit;
    }
}

/** Exige une permission ; sinon affiche une page d'accès refusé. */
function require_permission(string $perm): void
{
    require_login();
    if (!admin_can($perm)) {
        admin_head('Accès refusé');
        echo '<div class="admin-layout">';
        admin_sidebar('dashboard');
        echo '<main class="admin-main"><div class="alert alert-danger">'
            . icon('lock', 18)
            . '<div>Vous n\'avez pas les droits nécessaires pour accéder à cette section. '
            . 'Votre rôle : <strong>' . e(admin_roles_meta()[admin_role()]['label'] ?? admin_role()) . '</strong>.</div></div>'
            . '<p style="margin-top:1rem;"><a class="btn btn-outline" href="' . e(url('admin/dashboard.php')) . '">Retour au tableau de bord</a></p>'
            . '</main></div></body></html>';
        exit;
    }
}

/** En-tête HTML commun de l'admin. */
function admin_head(string $titre): void
{
    ?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= e($titre) ?> — Administration IAT Niger</title>
<link rel="icon" type="image/png" href="<?= asset('img/logoiat.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<script>
(function () {
  try {
    var t;
    if (!localStorage.getItem('iat-theme-migrated-v3')) {
      t = 'light';
      localStorage.setItem('iat-theme-pref', 'light');
      localStorage.removeItem('iat-theme');
      localStorage.setItem('iat-theme-migrated-v3', '1');
    } else {
      t = localStorage.getItem('iat-theme-pref');
      if (t !== 'dark' && t !== 'light') t = 'light';
    }
    document.documentElement.setAttribute('data-theme', t);
    document.documentElement.style.colorScheme = t === 'dark' ? 'dark' : 'light';
  } catch (e) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
})();
window.IAT_ADMIN = {
  assetBase: <?= json_encode(path_base() . '/assets/') ?>,
  mediaApi: <?= json_encode(url('admin/api-medias.php')) ?>,
  iconsApi: <?= json_encode(url('admin/api-icons.php')) ?>,
  uploadApi: <?= json_encode(url('admin/api-upload.php')) ?>,
  csrf: <?= json_encode(csrf_token()) ?>
};
</script>
<script src="<?= asset('js/admin-pickers.js') ?>" defer></script>
</head>
<body>
    <?php
}

/** Pied de page admin. */
function admin_foot(): void
{
    echo '</body></html>';
}

/** Barre latérale de l'admin (filtrée selon les droits). */
function admin_sidebar(string $actif): void
{
    $groupes = [
        'Pilotage' => [
            'dashboard' => ['dashboard.php', 'layout-dashboard', 'Tableau de bord', 'dashboard'],
            'apercu' => ['apercu.php', 'eye', 'Aperçu du site', 'dashboard'],
            'parametres' => ['parametres.php', 'settings', 'Paramètres du site', 'parametres'],
            'navigation' => ['navigation.php', 'menu', 'Navigation & menu', 'parametres'],
            'pages' => ['pages.php', 'file-text', 'Pages & SEO', 'pages'],
            'utilisateurs' => ['utilisateurs.php', 'users', 'Utilisateurs & droits', 'utilisateurs'],
        ],
        'Contenus' => [
            'accueil' => ['accueil.php', 'home', 'Accueil', 'accueil'],
            'actualites' => ['actualites.php', 'newspaper', 'Actualités', 'actualites'],
            'formations' => ['formations.php', 'graduation-cap', 'Formations', 'formations'],
            'formations-textes' => ['formations-textes.php', 'file-text', 'Textes formations', 'formations'],
            'faq' => ['contenu.php?type=faq', 'help-circle', 'FAQ', 'faq'],
            'partenaires' => ['contenu.php?type=partenaire', 'handshake', 'Partenaires', 'partenaires'],
            'temoignages' => ['contenu.php?type=temoignage', 'quote', 'Témoignages', 'temoignages'],
            'galerie' => ['contenu.php?type=galerie', 'image', 'Galerie', 'galerie'],
            'web-tv' => ['contenu.php?type=video', 'play', 'WEB TV', 'web-tv'],
            'documents' => ['contenu.php?type=document', 'download', 'Documents', 'documents'],
            'a-propos' => ['a-propos.php', 'book-open', 'À propos', 'a-propos'],
            'vie-etudiante' => ['vie-etudiante.php', 'heart', 'Vie étudiante', 'vie-etudiante'],
            'csp' => ['csp-algoza.php', 'school', 'CSP Algoza', 'csp'],
            'admission' => ['admission.php', 'user-plus', 'Admission', 'admission'],
            'contact' => ['contact.php', 'map-pin', 'Contact (textes)', 'parametres'],
        ],
        'Demandes' => [
            'messages' => ['messages.php', 'inbox', 'Messages', 'messages'],
            'preinscriptions' => ['preinscriptions.php', 'user-plus', 'Préinscriptions', 'preinscriptions'],
            'newsletter' => ['newsletter.php', 'mail', 'Newsletter', 'newsletter'],
        ],
        'Médias' => [
            'medias' => ['medias.php', 'folder', 'Médiathèque', 'medias'],
        ],
    ];
    $role_label = admin_roles_meta()[admin_role()]['label'] ?? 'Admin';
    ?>
<aside class="admin-sidebar">
  <span class="brand-admin">IAT Admin</span>
  <span class="admin-nav-label" style="opacity:0.85;text-transform:none;letter-spacing:0;font-size:0.75rem;"><?= e($_SESSION['admin_nom'] ?? '') ?> · <?= e($role_label) ?></span>
  <?php foreach ($groupes as $groupe => $liens) :
      $visibles = array_filter($liens, static fn ($l) => admin_can($l[3]));
      if (!$visibles) {
          continue;
      }
      ?>
  <span class="admin-nav-label"><?= e($groupe) ?></span>
  <?php foreach ($visibles as $cle => [$fichier, $ico, $label]) : ?>
  <a href="<?= url('admin/' . $fichier) ?>" <?= $cle === $actif ? 'class="active" aria-current="page"' : '' ?>><?= icon($ico, 18) ?> <?= e($label) ?></a>
  <?php endforeach; ?>
  <?php endforeach; ?>
  <?php if (!cms_ready() && admin_can('parametres')) : ?>
  <a href="<?= url('admin/install-cms.php') ?>" style="color:#fbbf24;"><?= icon('alert-triangle', 18) ?> Installer le CMS</a>
  <?php endif; ?>
  <span class="spacer"></span>
  <a href="<?= url() ?>" target="_blank"><?= icon('external-link', 18) ?> Voir le site</a>
  <a href="<?= url('admin/logout.php') ?>"><?= icon('log-out', 18) ?> Déconnexion</a>
</aside>
    <?php
}
