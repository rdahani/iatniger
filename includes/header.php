<?php
/**
 * En-tête commun : SEO, polices, navigation sticky avec mega menu.
 * Variables attendues (définies par chaque page avant l'include) :
 *   $page_title, $page_desc, $page_slug (canonical), $active (clé nav),
 *   $breadcrumbs (facultatif) : [['label' => ..., 'url' => ...], ...]
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/icons.php';

$page_title = $page_title ?? SITE_FULL_NAME . ' — ' . SITE_TAGLINE;
$page_desc = $page_desc ?? "L'Institut Africain de Technologie (IAT Niger) forme depuis plus de 25 ans les cadres de demain : BTS, Licences, Masters et Doctorat accrédités CAMES à Niamey, Niger.";
$page_slug = $page_slug ?? '';
$active = $active ?? '';
$canonical = url($page_slug);
$og_image = asset('img/banner-iat.jpg');

$site_full_name = setting('site_full_name', SITE_FULL_NAME);
$site_tagline = setting('site_tagline', SITE_TAGLINE);
$site_email = setting('site_email', SITE_EMAIL);
$site_phone_1 = setting('site_phone_1', SITE_PHONE_1);
$site_address = setting('site_address', SITE_ADDRESS);
$site_facebook = setting('site_facebook', SITE_FACEBOOK);
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title) ?></title>
<meta name="description" content="<?= e($page_desc) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta name="robots" content="index, follow">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($site_full_name) ?>">
<meta property="og:title" content="<?= e($page_title) ?>">
<meta property="og:description" content="<?= e($page_desc) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e($og_image) ?>">
<meta property="og:locale" content="fr_FR">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($page_title) ?>">
<meta name="twitter:description" content="<?= e($page_desc) ?>">
<meta name="twitter:image" content="<?= e($og_image) ?>">

<link rel="icon" type="image/png" href="<?= asset('img/logoiat.png') ?>">

<!-- Polices -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<meta name="color-scheme" content="light">
<meta name="theme-color" content="#f7f8fc">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">

<!-- Thème : clair par défaut — une migration force le clair sur les anciens visiteurs mobile -->
<script>
(function () {
  try {
    var t;
    if (!localStorage.getItem('iat-theme-migrated-v3')) {
      /* Remet tout le monde en clair une fois (corrige le sombre hérité du téléphone) */
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
    var metaCs = document.querySelector('meta[name="color-scheme"]');
    var metaTc = document.querySelector('meta[name="theme-color"]');
    if (metaCs) metaCs.setAttribute('content', t === 'dark' ? 'dark' : 'light');
    if (metaTc) metaTc.setAttribute('content', t === 'dark' ? '#0b1020' : '#f7f8fc');
  } catch (e) {
    document.documentElement.setAttribute('data-theme', 'light');
  }
})();
</script>

<!-- JSON-LD : Organisation -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "CollegeOrUniversity",
  "name": "<?= e($site_full_name) ?>",
  "alternateName": "IAT Niger",
  "url": "<?= e(SITE_URL) ?>",
  "logo": "<?= e(asset('img/logoiat.png')) ?>",
  "slogan": "<?= e($site_tagline) ?>",
  "email": "<?= e($site_email) ?>",
  "telephone": "<?= e(preg_replace('/[^0-9+]/', '', $site_phone_1)) ?>",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Rond-Point Gadafawa, Yantala, Commune 1",
    "postOfficeBoxNumber": "412",
    "addressLocality": "Niamey",
    "addressCountry": "NE"
  },
  "sameAs": ["<?= e($site_facebook) ?>"]
}
</script>
<?php if (!empty($breadcrumbs)) : ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    <?php foreach ($breadcrumbs as $i => $bc) : ?>
    {"@type": "ListItem", "position": <?= $i + 1 ?>, "name": "<?= e($bc['label']) ?>", "item": "<?= e($bc['url']) ?>"}<?= $i < count($breadcrumbs) - 1 ? ',' : '' ?>
    <?php endforeach; ?>
  ]
}
</script>
<?php endif; ?>
</head>
<body>
<a class="skip-link" href="#contenu">Aller au contenu principal</a>

<header class="navbar" id="navbar">
  <div class="container navbar-inner">
    <a href="<?= url() ?>" class="brand" aria-label="<?= e($site_full_name) ?> — Accueil">
      <img src="<?= asset('img/logoiat.png') ?>" alt="Logo IAT Niger" width="120" height="46">
    </a>

    <nav class="nav-main" id="nav-main" aria-label="Navigation principale">
      <ul class="nav-list">
        <li><a href="<?= url() ?>" <?= $active === 'accueil' ? 'aria-current="page"' : '' ?>>Accueil</a></li>

        <li class="has-dropdown">
          <button type="button" aria-expanded="false" <?= $active === 'institut' ? 'class="is-active"' : '' ?>>
            L'Institut <?= icon('chevron-down', 16) ?>
          </button>
          <div class="dropdown">
            <a href="<?= url('a-propos') ?>"><?= icon('landmark', 18) ?><span><strong>À propos</strong><small>Historique, mission &amp; vision</small></span></a>
            <a href="<?= url('a-propos#direction') ?>"><?= icon('message-circle', 18) ?><span><strong>Mot de la Direction</strong><small>L'engagement de l'IAT</small></span></a>
            <a href="<?= url('a-propos#enseignants') ?>"><?= icon('users', 18) ?><span><strong>Corps enseignant</strong><small>36 enseignants-chercheurs de rang A</small></span></a>
            <a href="<?= url('partenaires') ?>"><?= icon('handshake', 18) ?><span><strong>Partenaires</strong><small>Institutions &amp; entreprises</small></span></a>
            <a href="<?= url('galerie') ?>"><?= icon('image', 18) ?><span><strong>Galerie</strong><small>Le campus en images</small></span></a>
            <a href="<?= url('telechargements') ?>"><?= icon('download', 18) ?><span><strong>Téléchargements</strong><small>Dépliant, modalités de paiement, logos</small></span></a>
            <a class="dropdown-promo" href="<?= url('a-propos') ?>">
              <img src="<?= asset('img/etudiants/laureate-trophee.jpg') ?>" alt="" loading="lazy" width="371" height="372">
              <span><strong>25 ans d'excellence</strong><small>Découvrir notre histoire <?= icon('arrow-right', 13) ?></small></span>
            </a>
          </div>
        </li>

        <li class="has-dropdown has-mega">
          <button type="button" aria-expanded="false" <?= $active === 'formations' ? 'class="is-active"' : '' ?>>
            Formations <?= icon('chevron-down', 16) ?>
          </button>
          <div class="dropdown mega">
            <div class="mega-wrap">
              <div class="mega-grid">
                <?php
                /* Cartes du mega-menu : suivent le catalogue des niveaux (admin → Formations). */
                $mega_icones = ['niveau-moyen' => 'book-open', 'licence' => 'graduation-cap', 'master' => 'award', 'doctorat' => 'flask'];
                foreach (niveaux_catalogue() as $mega_slug => $mega_niv) :
                    ?>
                <a class="mega-card" href="<?= url('formations/' . $mega_slug) ?>">
                  <span class="mega-icon"><?= icon($mega_icones[$mega_slug] ?? 'graduation-cap', 22) ?></span>
                  <strong><?= e($mega_niv['titre']) ?></strong>
                  <?php if (!empty($mega_niv['sous_titre'])) : ?><small><?= e($mega_niv['sous_titre']) ?></small><?php endif; ?>
                </a>
                <?php endforeach; ?>
              </div>
              <a class="mega-promo" href="<?= url('admission#preinscription') ?>">
                <img src="<?= asset('img/' . ltrim(cms_texte('nav_mega_promo_image', 'etudiants/etudiant-laptop.jpg'), '/')) ?>" alt="" loading="lazy" width="371" height="372">
                <span class="mega-promo-body">
                  <span class="badge badge-accent"><?= e(cms_texte('nav_mega_promo_badge', 'Inscriptions ouvertes')) ?></span>
                  <strong><?= e(cms_texte('nav_mega_promo_titre', 'Rejoignez la promotion 2026-2027')) ?></strong>
                  <small><?= e(cms_texte('nav_mega_promo_texte', 'Préinscription gratuite en ligne')) ?> <?= icon('arrow-right', 14) ?></small>
                </span>
              </a>
            </div>
            <div class="mega-footer">
              <a href="<?= url('formations') ?>">Voir toutes les formations <?= icon('arrow-right', 16) ?></a>
              <a href="<?= url('admission') ?>">Conditions d'admission <?= icon('arrow-right', 16) ?></a>
            </div>
          </div>
        </li>

        <li><a href="<?= url('admission') ?>" <?= $active === 'admission' ? 'aria-current="page"' : '' ?>>Admission</a></li>

        <li class="has-dropdown">
          <button type="button" aria-expanded="false" <?= $active === 'vie' ? 'class="is-active"' : '' ?>>
            Vie étudiante <?= icon('chevron-down', 16) ?>
          </button>
          <div class="dropdown">
            <a href="<?= url('vie-etudiante') ?>"><?= icon('heart', 18) ?><span><strong>BDE &amp; clubs</strong><small>Sport, culture, engagement</small></span></a>
            <a href="<?= url('vie-etudiante#alumni') ?>"><?= icon('briefcase', 18) ?><span><strong>Alumni</strong><small>L'amicale des anciens</small></span></a>
            <a href="<?= url('csp-algoza') ?>"><?= icon('school', 18) ?><span><strong>CSP Algoza</strong><small>Primaire, collège &amp; lycée</small></span></a>
            <a class="dropdown-promo" href="<?= url('vie-etudiante') ?>">
              <img src="<?= asset('img/etudiants/diplome-bleu.jpg') ?>" alt="" loading="lazy" width="371" height="372">
              <span><strong>Fier·e·s de nos diplômés</strong><small>La vie du campus <?= icon('arrow-right', 13) ?></small></span>
            </a>
          </div>
        </li>

        <li class="has-dropdown">
          <button type="button" aria-expanded="false" <?= $active === 'actualites' ? 'class="is-active"' : '' ?>>
            Actualités <?= icon('chevron-down', 16) ?>
          </button>
          <div class="dropdown">
            <a href="<?= url('actualites') ?>"><?= icon('newspaper', 18) ?><span><strong>Actualités</strong><small>La vie de l'institut</small></span></a>
            <a href="<?= url('web-tv') ?>"><?= icon('video', 18) ?><span><strong>WEB TV</strong><small>Reportages &amp; événements</small></span></a>
            <a href="<?= url('faq') ?>"><?= icon('help-circle', 18) ?><span><strong>FAQ</strong><small>Questions fréquentes</small></span></a>
          </div>
        </li>

        <li><a href="<?= url('contact') ?>" <?= $active === 'contact' ? 'aria-current="page"' : '' ?>>Contact</a></li>
      </ul>
    </nav>

    <div class="nav-actions">
      <a class="icon-btn" href="<?= url('recherche') ?>" aria-label="Rechercher sur le site"><?= icon('search', 20) ?></a>
      <button class="icon-btn" id="theme-toggle" type="button" aria-label="Basculer le mode sombre">
        <span class="icon-sun"><?= icon('sun', 20) ?></span>
        <span class="icon-moon"><?= icon('moon', 20) ?></span>
      </button>
      <a class="btn btn-accent btn-nav" href="<?= url('admission#preinscription') ?>">S'inscrire</a>
      <button class="icon-btn nav-burger" id="nav-burger" type="button" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="nav-main">
        <span class="icon-menu"><?= icon('menu', 22) ?></span>
        <span class="icon-close"><?= icon('x', 22) ?></span>
      </button>
    </div>
  </div>
</header>

<main id="contenu">
