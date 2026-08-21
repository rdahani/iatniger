<?php
/**
 * Formations : page hub (sans paramètre) ou page niveau (?niveau=licence…).
 * URLs propres : /formations et /formations/{niveau} via .htaccess
 */

require_once __DIR__ . '/config/config.php';

$niveaux = niveaux_catalogue();
$niveau_key = $_GET['niveau'] ?? '';
$niveau = $niveaux[$niveau_key] ?? null;

$hub_card_link = cms_texte('formations_hub_card_link', 'Voir les filières');
$overview_kicker = cms_texte('formations_overview_kicker', "Vue d'ensemble");
$overview_titre = cms_texte('formations_overview_titre', "Les 28 filières de l'IAT");
$overview_btn = cms_texte('formations_overview_btn', "Conditions d'admission");
$tertiaire_kicker = cms_texte('formations_tertiaire_kicker', 'Filières tertiaires');
$tertiaire_titre = cms_texte('formations_tertiaire_titre', 'Gestion, commerce & services');
$industriel_kicker = cms_texte('formations_industriel_kicker', 'Filières industrielles');
$industriel_titre = cms_texte('formations_industriel_titre', 'Ingénierie, technologies & industrie');
$debouches_label = cms_texte('formations_debouches_label', 'Débouchés');
$fiche_btn = cms_texte('formations_fiche_btn', 'Fiche complète');
$cta_preinscription = cms_texte('formations_cta_preinscription', 'Déposer ma préinscription');
$doctorat_essec_titre = cms_texte('formations_doctorat_essec_titre', 'Le partenariat ESSEC — Université de Douala');
$doctorat_essec_texte = cms_texte('formations_doctorat_essec_texte', "Le partenariat vise à « accompagner les activités de formation et de recherche de l'IAT dans le domaine des Sciences économiques et de Gestion ».");
$doctorat_essec_items = cms_texte_extra('formations_doctorat_essec_items')['items'] ?? [
    'Formation académique : Licence, Master et Doctorat',
    'Formation professionnelle continue',
    'Mobilité des enseignants et des étudiants',
    'Projets de recherche et événements scientifiques',
];
$doctorat_ecole_titre = cms_texte('formations_doctorat_ecole_titre', "L'École Doctorale ESSEC");
$doctorat_ecole_texte = cms_texte('formations_doctorat_ecole_texte', "Après le Master de Recherche, poursuivez en thèse dans l'un des quatre domaines de l'École Doctorale :");
$doctorat_ecole_items = cms_texte_extra('formations_doctorat_ecole_items')['items'] ?? [
    'Business Economics',
    'Management des organisations',
    'Science Juridique',
    "Science de l'ingénieur",
];
$doctorat_contacts = cms_texte('formations_doctorat_contacts', "Points de contact à l'IAT : Cherifa Hamadou Hamidou · Ousseini Adamou Magagi · Bara Boubacar");

if ($niveau !== null) {
    /* ----- Page d'un niveau ----- */
    $page_title = $niveau['titre'] . ' — Formations | IAT Niger';
    $page_desc = $niveau['description'] . ' Recrutement : ' . $niveau['recrutement'] . '.';
    $page_slug = 'formations/' . $niveau_key;
    $liste = formations_par_niveau($niveau_key);
    $hero_titre = $niveau['titre'];
    $hero_texte = $niveau['description'];
    $breadcrumbs = [
        ['label' => 'Accueil', 'url' => url()],
        ['label' => 'Formations', 'url' => url('formations')],
        ['label' => $niveau['titre'], 'url' => url($page_slug)],
    ];
} else {
    /* ----- Page hub ----- */
    $page_title = 'Nos Formations — BTS, Licences, Masters, Doctorat | IAT Niger';
    $page_desc = "28 filières tertiaires et industrielles du Bac Pro au Doctorat, accréditées CAMES : gestion, banque, informatique, génie civil, télécoms, pétrole…";
    $page_slug = 'formations';
    $hero_titre = 'Nos formations';
    $hero_texte = "Du BEPC au Doctorat : 28 filières professionnalisantes, tertiaires et industrielles, dans le système LMD.";
    $breadcrumbs = [
        ['label' => 'Accueil', 'url' => url()],
        ['label' => 'Formations', 'url' => url('formations')],
    ];
    cms_apply_page('formations', $page_title, $page_desc, $hero_titre, $hero_texte);
}
$active = 'formations';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-hero.php';

if ($niveau === null) : ?>

<!-- ============ HUB DES FORMATIONS ============ -->
<section class="section">
  <div class="container">
    <div class="grid-2" style="align-items: stretch; gap: 1.5rem;">
      <?php foreach ($niveaux as $key => $n) : ?>
      <a class="card formation-card reveal" href="<?= url('formations/' . $key) ?>">
        <span class="card-icon"><?= icon(['niveau-moyen' => 'book-open', 'licence' => 'graduation-cap', 'master' => 'award', 'doctorat' => 'flask'][$key], 24) ?></span>
        <div class="badges">
          <span class="badge badge-primary"><?= e($n['sous_titre']) ?></span>
        </div>
        <h3><?= e($n['titre']) ?></h3>
        <p><?= e($n['description']) ?></p>
        <span class="card-link"><?= e($hub_card_link) ?> <?= icon('arrow-right', 16) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Toutes les filières en un coup d'œil -->
<section class="section section-alt">
  <div class="container">
    <div class="section-head reveal">
      <span class="kicker"><?= icon('search', 15) ?> <?= e($overview_kicker) ?></span>
      <h2><?= e($overview_titre) ?></h2>
    </div>
    <?php foreach ($niveaux as $key => $n) : $liste_n = formations_par_niveau($key);
        if (!$liste_n) { continue; } ?>
    <h3 style="margin: 2rem 0 1rem;"><?= e($n['titre']) ?></h3>
    <ul class="pill-list reveal">
      <?php foreach ($liste_n as $f) : ?>
      <li><a href="<?= url('formation/' . $f['slug']) ?>"><?= e($f['titre']) ?><?= !empty($f['badge']) ? ' · <strong style="color:var(--accent-strong);">' . e($f['badge']) . '</strong>' : '' ?></a></li>
      <?php endforeach; ?>
    </ul>
    <?php endforeach; ?>
    <div style="margin-top: 2.5rem;">
      <a class="btn btn-primary btn-lg" href="<?= url('admission') ?>"><?= e($overview_btn) ?> <?= icon('arrow-right', 18) ?></a>
    </div>
  </div>
</section>

<?php else : ?>

<!-- ============ PAGE NIVEAU ============ -->
<section class="section">
  <div class="container">
    <!-- Infos clés du niveau -->
    <div class="grid-3" style="margin-bottom: 3.5rem;">
      <div class="card reveal">
        <span class="card-icon"><?= icon('user-plus', 24) ?></span>
        <h3 style="font-size:1.05rem;">Recrutement</h3>
        <p><?= e($niveau['recrutement']) ?></p>
      </div>
      <div class="card reveal reveal-delay-1">
        <span class="card-icon"><?= icon('clock', 24) ?></span>
        <h3 style="font-size:1.05rem;">Durée</h3>
        <p><?= e($niveau['duree']) ?></p>
      </div>
      <div class="card reveal reveal-delay-2">
        <span class="card-icon"><?= icon('file-text', 24) ?></span>
        <h3 style="font-size:1.05rem;">Dossier de candidature</h3>
        <p><?= e($niveau['dossier']) ?></p>
      </div>
    </div>

    <?php
    $tertiaires = array_filter($liste, fn ($f) => $f['domaine'] === 'tertiaire');
    $industrielles = array_filter($liste, fn ($f) => $f['domaine'] === 'industriel');
    ?>

    <?php if ($liste) : ?>
    <?php if ($tertiaires) : ?>
    <div class="section-head reveal">
      <span class="kicker"><?= icon('briefcase', 15) ?> <?= e($tertiaire_kicker) ?></span>
      <h2 class="h3"><?= e($tertiaire_titre) ?></h2>
    </div>
    <div class="accordion" style="margin-bottom: 3rem;">
      <?php foreach ($tertiaires as $f) : ?>
      <div class="accordion-item reveal">
        <button class="accordion-trigger" type="button" aria-expanded="false">
          <span style="display:flex; align-items:center; gap:0.8rem;">
            <span class="card-icon" style="width:40px;height:40px;margin:0;"><?= icon($f['icone'], 20) ?></span>
            <?= e($f['titre']) ?>
            <?php if (!empty($f['badge'])) : ?><span class="badge badge-accent"><?= e($f['badge']) ?></span><?php endif; ?>
          </span>
          <?= icon('chevron-down', 20) ?>
        </button>
        <div class="accordion-panel"><div>
          <div class="accordion-content">
            <p style="margin-bottom: 1rem;"><?= e($f['objectif']) ?></p>
            <h4 style="margin-bottom: 0.6rem;"><?= e($debouches_label) ?></h4>
            <ul class="pill-list">
              <?php foreach ($f['debouches'] as $d) : ?><li><?= e($d) ?></li><?php endforeach; ?>
            </ul>
            <a class="btn btn-outline" style="margin-top: 1.2rem;" href="<?= url('formation/' . $f['slug']) ?>"><?= e($fiche_btn) ?> <?= icon('arrow-right', 16) ?></a>
          </div>
        </div></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($industrielles) : ?>
    <div class="section-head reveal">
      <span class="kicker"><?= icon('wrench', 15) ?> <?= e($industriel_kicker) ?></span>
      <h2 class="h3"><?= e($industriel_titre) ?></h2>
    </div>
    <div class="accordion">
      <?php foreach ($industrielles as $f) : ?>
      <div class="accordion-item reveal">
        <button class="accordion-trigger" type="button" aria-expanded="false">
          <span style="display:flex; align-items:center; gap:0.8rem;">
            <span class="card-icon" style="width:40px;height:40px;margin:0;"><?= icon($f['icone'], 20) ?></span>
            <?= e($f['titre']) ?>
            <?php if (!empty($f['badge'])) : ?><span class="badge badge-accent"><?= e($f['badge']) ?></span><?php endif; ?>
          </span>
          <?= icon('chevron-down', 20) ?>
        </button>
        <div class="accordion-panel"><div>
          <div class="accordion-content">
            <p style="margin-bottom: 1rem;"><?= e($f['objectif']) ?></p>
            <h4 style="margin-bottom: 0.6rem;"><?= e($debouches_label) ?></h4>
            <ul class="pill-list">
              <?php foreach ($f['debouches'] as $d) : ?><li><?= e($d) ?></li><?php endforeach; ?>
            </ul>
            <a class="btn btn-outline" style="margin-top: 1.2rem;" href="<?= url('formation/' . $f['slug']) ?>"><?= e($fiche_btn) ?> <?= icon('arrow-right', 16) ?></a>
          </div>
        </div></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <?php if ($niveau_key === 'doctorat') : ?>
    <!-- Contenu spécifique Master de Recherche / Doctorat -->
    <div class="grid-2" style="margin-top: 1rem;">
      <div class="card reveal">
        <span class="card-icon"><?= icon('handshake', 24) ?></span>
        <h3><?= e($doctorat_essec_titre) ?></h3>
        <p style="margin-bottom: 1rem;"><?= e($doctorat_essec_texte) ?></p>
        <ul class="check-list">
          <?php foreach ($doctorat_essec_items as $item) : ?>
          <li><?= icon('check', 18) ?><span><?= e($item) ?></span></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="card reveal reveal-delay-1">
        <span class="card-icon"><?= icon('flask', 24) ?></span>
        <h3><?= e($doctorat_ecole_titre) ?></h3>
        <p style="margin-bottom: 1rem;"><?= e($doctorat_ecole_texte) ?></p>
        <ul class="check-list">
          <?php foreach ($doctorat_ecole_items as $item) : ?>
          <li><?= icon('check', 18) ?><span><?= e($item) ?></span></li>
          <?php endforeach; ?>
        </ul>
        <p class="caption" style="margin-top: 1rem;"><?= e($doctorat_contacts) ?></p>
      </div>
    </div>
    <?php endif; ?>

    <div class="text-center" style="margin-top: 3.5rem;">
      <a class="btn btn-accent btn-lg" href="<?= url('admission#preinscription') ?>"><?= e($cta_preinscription) ?> <?= icon('arrow-right', 18) ?></a>
    </div>
  </div>
</section>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
