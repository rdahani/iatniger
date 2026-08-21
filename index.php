<?php
/** Page d'accueil — landing premium de l'IAT Niger. */

require_once __DIR__ . '/config/config.php';

$page_title = 'IAT Niger — Institut Africain de Technologie | Un pôle d\'excellence à Niamey';
$page_desc = "BTS, Licences, Masters et Doctorat accrédités CAMES. Depuis 1999, l'IAT Niger a formé plus de 30 000 diplômés à Niamey. Inscriptions ouvertes.";
$page_slug = '';
$active = 'accueil';
$dernieres_actus = actualites(3);

/* ----- Contenu éditable (CMS avec fallback sur les valeurs par défaut) ----- */
$hero_titre = "Construisez votre avenir dans un pôle d'excellence";
$hero_texte = '';
cms_apply_page('accueil', $page_title, $page_desc, $hero_titre, $hero_texte);

$hero_kicker = cms_texte('accueil_hero_kicker', 'Accrédité CAMES · ANAQ-SUP · Depuis 1999');
$hero_h1 = cms_texte('accueil_hero_h1', $hero_titre);
$hero_lead = cms_texte('accueil_hero_lead', "L'Institut Africain de Technologie forme les cadres et techniciens qui transforment le Niger et l'Afrique : 28 filières du Bac Pro au Doctorat, des laboratoires modernes et 25 ans d'expérience.");
$hero_trust = cms_texte_extra('accueil_hero_trust')['items'] ?? ['16 diplômes accrédités CAMES', 'Système LMD', 'Laboratoires équipés'];
$hero_h1_accent = cms_texte('accueil_hero_h1_accent', "un pôle d'excellence");
$hero_btn1 = cms_texte('accueil_hero_btn1', "Je m'inscris maintenant");
$hero_btn2 = cms_texte('accueil_hero_btn2', 'Découvrir les formations');

$stats_accueil = cms_stats('accueil') ?: [
    ['titre' => "années d'expérience", 'extra' => ['valeur' => 25, 'suffixe' => '+']],
    ['titre' => 'diplômés formés', 'extra' => ['valeur' => 30000, 'suffixe' => '+']],
    ['titre' => 'filières de formation', 'extra' => ['valeur' => 28, 'suffixe' => '']],
    ['titre' => 'enseignants-chercheurs de rang A', 'extra' => ['valeur' => 36, 'suffixe' => '']],
];

$cartes_pourquoi = cms_cartes('accueil-pourquoi') ?: [
    ['titre' => 'Diplômes accrédités', 'contenu' => "16 diplômes accrédités au CAMES et reconnus ANAQ-SUP, alignés sur le système LMD (Licence-Master-Doctorat) : votre diplôme a de la valeur partout en Afrique.", 'extra' => ['icone' => 'award']],
    ['titre' => 'Laboratoires modernes', 'contenu' => "Des laboratoires de Génie Électrique et Génie Civil inaugurés en 2026, pour apprendre en pratiquant sur des équipements professionnels.", 'extra' => ['icone' => 'flask']],
    ['titre' => 'Corps enseignant de haut niveau', 'contenu' => "36 enseignants-chercheurs de rang A issus des universités de Niamey, Dakar, Douala, Kara, Montpellier et de toute la sous-région.", 'extra' => ['icone' => 'users']],
    ['titre' => 'Insertion professionnelle', 'contenu' => "Des formations professionnalisantes construites avec les entreprises, des stages et un réseau d'alumni présent dans les ministères et grandes sociétés.", 'extra' => ['icone' => 'briefcase']],
    ['titre' => 'Ouverture internationale', 'contenu' => "Partenariat avec l'ESSEC de l'Université de Douala pour le Master de Recherche et le Doctorat, mobilité des enseignants et des étudiants.", 'extra' => ['icone' => 'globe']],
    ['titre' => 'Vie étudiante riche', 'contenu' => "BDE actif, clubs engagés, sport, culture, voyages d'études et actions citoyennes : un campus où l'on apprend aussi à devenir un leader.", 'extra' => ['icone' => 'heart']],
];

$pourquoi_kicker = cms_texte('accueil_pourquoi_kicker', "Pourquoi choisir l'IAT ?");
$pourquoi_titre = cms_texte('accueil_pourquoi_titre', "Une formation d'excellence, reconnue en Afrique et au-delà");
$pourquoi_lead = cms_texte('accueil_pourquoi_lead', "Quatre valeurs guident l'institut depuis sa création : l'excellence, la qualité, la transparence et l'ouverture au monde.");

$csp_titre = cms_texte('accueil_csp_titre', "CSP Algoza : l'excellence dès le plus jeune âge");
$csp_texte = cms_texte('accueil_csp_texte', "Le Complexe Scolaire Privé Algoza accueille vos enfants de la maternelle au baccalauréat : anglais renforcé, un ordinateur par élève, cantine et classes de 25 élèves maximum.");
$csp_liste = cms_texte_extra('accueil_csp_liste')['items'] ?? [
    'Maternelle & primaire — anglais dès le CI, 25 ordinateurs',
    'Collège & lycée — séries A, C et D, 4 h d\'anglais par semaine',
    'Cantine quotidienne et jardin potager pédagogique',
];
$csp_kicker = cms_texte('accueil_csp_kicker', 'Groupe IAT');
$csp_image = cms_texte('accueil_csp_image', 'campus/immeuble-iat.jpg');
$csp_btn1 = cms_texte('accueil_csp_btn1', 'Découvrir le CSP Algoza');
$csp_btn2 = cms_texte('accueil_csp_btn2', 'Nous contacter');
$csp_badge_titre = cms_texte('accueil_csp_badge_titre', 'Maternelle → Bac');
$csp_badge_sous = cms_texte('accueil_csp_badge_sous', 'Excellence dès le plus jeune âge');

$programmes_kicker = cms_texte('accueil_programmes_kicker', 'Nos programmes');
$programmes_titre = cms_texte('accueil_programmes_titre', 'Du BEPC au Doctorat, un parcours complet');
$programmes_lead = cms_texte('accueil_programmes_lead', '28 filières tertiaires et industrielles, organisées en quatre niveaux pour accompagner chaque étape de votre parcours.');

$actus_kicker = cms_texte('accueil_actus_kicker', 'Actualités');
$actus_titre = cms_texte('accueil_actus_titre', "La vie de l'institut");
$actus_btn = cms_texte('accueil_actus_btn', 'Toutes les actualités');

$temoignages_kicker = cms_texte('accueil_temoignages_kicker', 'Ils nous font confiance');
$temoignages_titre = cms_texte('accueil_temoignages_titre', 'La parole à nos anciens');

$galerie_kicker = cms_texte('accueil_galerie_kicker', 'Galerie');
$galerie_titre = cms_texte('accueil_galerie_titre', 'Le campus en images');
$galerie_btn = cms_texte('accueil_galerie_btn', 'Voir toute la galerie');

$partenaires_kicker = cms_texte('accueil_partenaires_kicker', 'Partenaires');
$partenaires_titre = cms_texte('accueil_partenaires_titre', 'Un réseau institutionnel et académique solide');

$fondateur_photo = cms_texte('accueil_fondateur_photo', 'fondateur-hamadou-hamidou.jpg');
$fondateur_nom = cms_texte('accueil_fondateur_nom', 'M. Hamadou Hamidou');
$fondateur_fonction = cms_texte('accueil_fondateur_fonction', "Fondateur de l'IAT Niger");
$fondateur_titre = cms_texte('accueil_fondateur_titre', "Une ambition : révéler le potentiel de la jeunesse africaine");
$fondateur_texte_1 = cms_texte('accueil_fondateur_texte_1', "Depuis 1999, l'Institut Africain de Technologie poursuit une seule ambition : offrir à la jeunesse nigérienne et africaine une formation à la hauteur de son potentiel. Nos quatre valeurs — l'excellence, la qualité, la transparence et l'ouverture au monde — se lisent dans nos accréditations CAMES, dans nos laboratoires et dans les carrières de nos 30 000 diplômés.");
$fondateur_texte_2 = cms_texte('accueil_fondateur_texte_2', "Choisir l'IAT, c'est rejoindre une institution qui investit continuellement dans ses infrastructures, son corps enseignant et ses partenariats internationaux, pour que chaque étudiant reparte avec bien plus qu'un diplôme : un métier, une méthode et un réseau.");
$fondateur_kicker = cms_texte('accueil_fondateur_kicker', 'Le mot du Fondateur');
$fondateur_points = cms_texte_extra('accueil_fondateur_points')['items'] ?? ['30 000+ diplômés', '16 diplômes CAMES', 'Depuis 1999'];
$fondateur_btn = cms_texte('accueil_fondateur_btn', "Découvrir l'institut");

$hero_card1_titre = cms_texte('accueil_hero_card1_titre', '30 000+');
$hero_card1_sous = cms_texte('accueil_hero_card1_sous', 'diplômés depuis 1999');
$hero_card2_titre = cms_texte('accueil_hero_card2_titre', "Alkalami d'Or 2026");
$hero_card2_sous = cms_texte('accueil_hero_card2_sous', "Prix de l'excellence académique");

$niveaux_accueil = niveaux_catalogue();
$icones_niveaux = ['niveau-moyen' => 'book-open', 'licence' => 'graduation-cap', 'master' => 'award', 'doctorat' => 'flask'];

$temoignages_accueil = cms_temoignages() ?: [
    ['citation' => "La jeunesse est l'espoir de chaque communauté en quête de développement et de progrès. J'invite tous les étudiants à s'investir pleinement pour bénéficier de cette formation d'excellence.", 'auteur' => 'Secrétaire Général du BDE', 'fonction' => "Bureau Des Étudiants de l'IAT", 'initiales' => 'SG'],
    ['citation' => "De l'IAT à la Présidence de la République : la formation reçue m'a ouvert les portes des plus hautes institutions du pays.", 'auteur' => 'Abdoulaye Souleymane', 'fonction' => "Président de l'Amicale des Anciens · Protocole, Présidence", 'initiales' => 'AS'],
    ['citation' => "Les compétences acquises en logistique à l'IAT sont celles que j'utilise chaque jour à la Nigelec. Une formation ancrée dans la réalité des entreprises.", 'auteur' => 'Moumouni Absi', 'fonction' => 'Achats & logistique, Nigelec · Vice-président des Anciens', 'initiales' => 'MA'],
    ['citation' => "L'IAT m'a donné bien plus qu'un diplôme : une méthode, un réseau et l'envie de servir. Je coordonne aujourd'hui une ONG nationale.", 'auteur' => 'Salif Moussa Douké', 'fonction' => 'Coordonnateur national, ONG OADES-Niger', 'initiales' => 'SM'],
];

$partenaires_strip = cms_partenaires() ?: [
    ['fichier' => 'anpe', 'nom' => 'ANPE'], ['fichier' => 'emig', 'nom' => 'EMIG'], ['fichier' => 'essecd', 'nom' => 'ESSEC Douala'], ['fichier' => 'ist', 'nom' => 'IST'],
    ['fichier' => 'hcr', 'nom' => 'HCR'], ['fichier' => 'labari', 'nom' => 'Labari'], ['fichier' => 'opagen', 'nom' => 'OPAGEN'], ['fichier' => 'cipmen', 'nom' => 'CIPMEN'],
];

require __DIR__ . '/includes/header.php';
?>

<!-- ============ HERO ============ -->
<section class="hero">
  <div class="container hero-inner">
    <div>
      <span class="kicker"><?= icon('award', 15) ?> <?= e($hero_kicker) ?></span>
      <h1><?php
        $accent_pos = mb_stripos($hero_h1, $hero_h1_accent);
        if ($accent_pos !== false) {
            echo e(mb_substr($hero_h1, 0, $accent_pos))
                . '<span class="grad">' . e(mb_substr($hero_h1, $accent_pos, mb_strlen($hero_h1_accent))) . '</span>'
                . e(mb_substr($hero_h1, $accent_pos + mb_strlen($hero_h1_accent)));
        } else {
            echo e($hero_h1);
        }
      ?></h1>
      <p class="lead"><?= e($hero_lead) ?></p>
      <div class="hero-actions">
        <a class="btn btn-primary btn-lg" href="<?= url('admission#preinscription') ?>"><?= e($hero_btn1) ?> <?= icon('arrow-right', 18) ?></a>
        <a class="btn btn-outline btn-lg" href="<?= url('formations') ?>"><?= e($hero_btn2) ?></a>
      </div>
      <ul class="hero-trust">
        <?php foreach ($hero_trust as $t) : ?>
        <li><?= icon('check-circle', 18) ?> <?= e($t) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="hero-visual reveal">
      <?php
      $hero_slides = cms_hero_slides('accueil') ?: [
          ['src' => 'recentes/photo-48.jpg', 'alt' => 'Travaux pratiques — génie civil et topographie'],
          ['src' => 'recentes/photo-20.jpg', 'alt' => 'Étudiants de l\'IAT — fiers de leur institut'],
          ['src' => 'recentes/photo-26.jpg', 'alt' => 'Vie de campus et moments institutionnels'],
          ['src' => 'recentes/photo-17.jpg', 'alt' => 'Formation pratique sur le terrain'],
          ['src' => 'campus/immeuble-iat.jpg', 'alt' => "Le campus de l'Institut Africain de Technologie à Niamey"],
      ];
      ?>
      <div class="hero-slider" data-hero-slider aria-label="Le campus et la vie de l'institut en images">
        <?php foreach ($hero_slides as $i => $s) : ?>
        <img class="hero-img <?= $i === 0 ? 'is-active' : '' ?>" src="<?= asset('img/' . $s['src']) ?>" alt="<?= e($s['alt']) ?>"
             width="800" height="600" <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
        <?php endforeach; ?>
        <div class="hero-slider-dots" role="tablist" aria-label="Choisir une image">
          <?php foreach ($hero_slides as $i => $s) : ?>
          <button type="button" class="<?= $i === 0 ? 'is-active' : '' ?>" data-slide="<?= $i ?>" aria-label="Image <?= $i + 1 ?> sur <?= count($hero_slides) ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="hero-card hero-card-1">
        <?= icon('graduation-cap', 26) ?>
        <div><strong><?= e($hero_card1_titre) ?></strong><small><?= e($hero_card1_sous) ?></small></div>
      </div>
      <div class="hero-card hero-card-2">
        <?= icon('award', 26) ?>
        <div><strong><?= e($hero_card2_titre) ?></strong><small><?= e($hero_card2_sous) ?></small></div>
      </div>
    </div>
  </div>
</section>

<!-- ============ STATISTIQUES ============ -->
<section class="stats-band" aria-label="L'IAT en chiffres">
  <div class="container stats-grid">
    <?php foreach ($stats_accueil as $i => $s) : ?>
    <div class="stat reveal<?= $i > 0 ? ' reveal-delay-' . min($i, 3) : '' ?>">
      <div class="stat-value"><span data-count="<?= (int) ($s['extra']['valeur'] ?? 0) ?>">0</span><?php if (!empty($s['extra']['suffixe'])) : ?><span class="suffix"><?= e($s['extra']['suffixe']) ?></span><?php endif; ?></div>
      <div class="stat-label"><?= e($s['titre']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ============ POURQUOI CHOISIR L'IAT ============ -->
<section class="section" id="pourquoi">
  <div class="container">
    <div class="section-head centered reveal">
      <span class="kicker"><?= icon('sparkles', 15) ?> <?= e($pourquoi_kicker) ?></span>
      <h2><?= e($pourquoi_titre) ?></h2>
      <p class="lead"><?= e($pourquoi_lead) ?></p>
    </div>
    <div class="grid-3">
      <?php foreach ($cartes_pourquoi as $i => $c) : ?>
      <article class="card reveal<?= $i % 3 > 0 ? ' reveal-delay-' . ($i % 3) : '' ?>">
        <span class="card-icon"><?= icon($c['extra']['icone'] ?? 'star', 24) ?></span>
        <h3><?= e($c['titre']) ?></h3>
        <p><?= e($c['contenu']) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ MOT DU FONDATEUR ============ -->
<section class="section" id="fondateur" style="padding-top: 0;">
  <div class="container">
    <div class="fondateur-panel reveal">
      <div class="fondateur-photo">
        <img src="<?= asset('img/' . ltrim($fondateur_photo, '/')) ?>" alt="<?= e($fondateur_nom . ', ' . $fondateur_fonction) ?>" loading="lazy" width="500" height="713">
        <div class="fondateur-plaque">
          <strong><?= e($fondateur_nom) ?></strong>
          <small><?= e($fondateur_fonction) ?></small>
        </div>
      </div>
      <div class="fondateur-content">
        <span class="fondateur-kicker"><?= icon('quote', 16) ?> <?= e($fondateur_kicker) ?></span>
        <h2><?= e($fondateur_titre) ?></h2>
        <p class="fondateur-citation"><?= e($fondateur_texte_1) ?></p>
        <p class="fondateur-texte"><?= e($fondateur_texte_2) ?></p>
        <div class="fondateur-pied">
          <ul class="fondateur-points">
            <?php foreach ($fondateur_points as $pt) : ?>
            <li><?= icon('check-circle', 17) ?> <?= e($pt) ?></li>
            <?php endforeach; ?>
          </ul>
          <a class="btn btn-accent" href="<?= url('a-propos') ?>"><?= e($fondateur_btn) ?> <?= icon('arrow-right', 16) ?></a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ PROGRAMMES ============ -->
<section class="section section-alt" id="programmes">
  <div class="container">
    <div class="section-head reveal">
      <span class="kicker"><?= icon('graduation-cap', 15) ?> <?= e($programmes_kicker) ?></span>
      <h2><?= e($programmes_titre) ?></h2>
      <p class="lead"><?= e($programmes_lead) ?></p>
    </div>
    <div class="grid-4">
      <?php
      $i_niv = 0;
      foreach ($niveaux_accueil as $slug => $niv) :
          $nb = count(formations_par_niveau($slug));
          $icone = $icones_niveaux[$slug] ?? 'graduation-cap';
          $delay = $i_niv > 0 ? ' reveal-delay-' . min($i_niv, 3) : '';
          $lien_label = $nb > 0 ? ($nb . ' filière' . ($nb > 1 ? 's' : '')) : 'Découvrir';
          ?>
      <a class="card formation-card reveal<?= $delay ?>" href="<?= url('formations/' . $slug) ?>">
        <span class="card-icon"><?= icon($icone, 24) ?></span>
        <?php if (!empty($niv['sous_titre'])) : ?>
        <div class="badges"><span class="badge badge-primary"><?= e($niv['sous_titre']) ?></span></div>
        <?php endif; ?>
        <h3><?= e($niv['titre']) ?></h3>
        <p><?= e($niv['description'] ?? '') ?></p>
        <span class="card-link"><?= e($lien_label) ?> <?= icon('arrow-right', 16) ?></span>
      </a>
      <?php
          $i_niv++;
      endforeach;
      ?>
    </div>
  </div>
</section>

<!-- ============ CSP ALGOZA ============ -->
<section class="section section-csp" aria-labelledby="csp-titre">
  <div class="container section-csp-inner">
    <div class="section-csp-copy reveal">
      <span class="kicker"><?= icon('school', 15) ?> <?= e($csp_kicker) ?></span>
      <h2 id="csp-titre"><?= e($csp_titre) ?></h2>
      <p class="lead"><?= e($csp_texte) ?></p>
      <ul class="check-list">
        <?php foreach ($csp_liste as $item) : ?>
        <li><?= icon('check', 18) ?><span><?= e($item) ?></span></li>
        <?php endforeach; ?>
      </ul>
      <div class="hero-actions">
        <a class="btn btn-accent btn-lg" href="<?= url('csp-algoza') ?>"><?= e($csp_btn1) ?> <?= icon('arrow-right', 18) ?></a>
        <a class="btn btn-ghost-light btn-lg" href="<?= url('contact') ?>"><?= e($csp_btn2) ?></a>
      </div>
    </div>
    <div class="section-csp-visual reveal reveal-delay-1">
      <figure class="section-csp-frame">
        <img src="<?= asset('img/' . ltrim($csp_image, '/')) ?>" alt="Campus du Groupe IAT — Institut Africain de Technologie et CSP Algoza à Niamey" loading="lazy" width="700" height="500">
      </figure>
      <div class="section-csp-badge" aria-hidden="true">
        <?= icon('award', 20) ?>
        <div><strong><?= e($csp_badge_titre) ?></strong><small><?= e($csp_badge_sous) ?></small></div>
      </div>
    </div>
  </div>
</section>

<!-- ============ ACTUALITÉS ============ -->
<section class="section section-alt" id="actualites">
  <div class="container">
    <div class="section-head reveal" style="display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:1rem; max-width:none;">
      <div>
        <span class="kicker"><?= icon('newspaper', 15) ?> <?= e($actus_kicker) ?></span>
        <h2><?= e($actus_titre) ?></h2>
      </div>
      <a class="btn btn-outline" href="<?= url('actualites') ?>"><?= e($actus_btn) ?> <?= icon('arrow-right', 16) ?></a>
    </div>
    <div class="grid-3">
      <?php foreach ($dernieres_actus as $i => $actu) : ?>
      <article class="card news-card reveal <?= $i === 1 ? 'reveal-delay-1' : ($i === 2 ? 'reveal-delay-2' : '') ?>">
        <div class="news-img">
          <img src="<?= asset('img/' . $actu['image']) ?>" alt="<?= e($actu['titre']) ?>" loading="lazy" width="640" height="360">
        </div>
        <div class="news-body">
          <div class="news-meta">
            <span class="badge badge-primary"><?= e($actu['categorie']) ?></span>
            <span><?= icon('calendar', 14) ?> <?= e(date_fr($actu['date_publication'])) ?></span>
          </div>
          <h3><a href="<?= url('actualites/' . $actu['slug']) ?>"><?= e($actu['titre']) ?></a></h3>
          <p><?= e(mb_strimwidth($actu['extrait'], 0, 140, '…')) ?></p>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ TÉMOIGNAGES ============ -->
<section class="section temoignages-section" id="temoignages">
  <div class="container" data-carousel>
    <div class="section-head reveal">
      <span class="kicker"><?= icon('quote', 15) ?> <?= e($temoignages_kicker) ?></span>
      <h2><?= e($temoignages_titre) ?></h2>
    </div>
    <div class="testimonial-track" tabindex="0" aria-label="Témoignages d'anciens étudiants — utilisez les flèches pour naviguer">
      <?php foreach ($temoignages_accueil as $t) : ?>
      <article class="card testimonial">
        <span class="quote-icon"><?= icon('quote', 28) ?></span>
        <blockquote>« <?= e($t['citation']) ?> »</blockquote>
        <div class="testimonial-author">
          <span class="avatar" aria-hidden="true"><?= e($t['initiales']) ?></span>
          <div><strong><?= e($t['auteur']) ?></strong><small><?= e($t['fonction']) ?></small></div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <div class="carousel-controls">
      <button class="icon-btn" type="button" data-prev aria-label="Témoignage précédent"><?= icon('chevron-right', 20, 'flip-x') ?></button>
      <button class="icon-btn" type="button" data-next aria-label="Témoignage suivant"><?= icon('chevron-right', 20) ?></button>
    </div>
  </div>
</section>

<!-- ============ GALERIE ============ -->
<section class="section" id="galerie">
  <div class="container">
    <div class="section-head reveal" style="display:flex; flex-wrap:wrap; align-items:flex-end; justify-content:space-between; gap:1rem; max-width:none;">
      <div>
        <span class="kicker"><?= icon('image', 15) ?> <?= e($galerie_kicker) ?></span>
        <h2><?= e($galerie_titre) ?></h2>
      </div>
      <a class="btn btn-outline" href="<?= url('galerie') ?>"><?= e($galerie_btn) ?> <?= icon('arrow-right', 16) ?></a>
    </div>
    <?php
    $galerie_accueil_prefer = cms_texte_extra('accueil_galerie_photos')['items'] ?? [
        'recentes/photo-48.jpg', 'recentes/photo-20.jpg', 'recentes/photo-26.jpg',
        'recentes/photo-17.jpg', 'recentes/photo-21.jpg', 'recentes/photo-49.jpg',
        'recentes/photo-12.jpg', 'campus/immeuble-iat.jpg',
    ];
    $galerie_accueil_prefer = array_values(array_filter(array_map('trim', $galerie_accueil_prefer), static fn ($src) => $src !== ''));
    $galerie_cms = cms_galerie();
    $by_src = [];
    foreach ($galerie_cms as $p) {
        if (!empty($p['src'])) {
            $by_src[$p['src']] = $p;
        }
    }
    $galerie_accueil = [];
    foreach ($galerie_accueil_prefer as $src) {
        if (isset($by_src[$src])) {
            $galerie_accueil[] = $by_src[$src];
        } elseif (is_file(__DIR__ . '/assets/img/' . $src)) {
            $galerie_accueil[] = ['src' => $src, 'legende' => 'IAT Niger'];
        }
    }
    ?>
    <div class="home-gallery reveal">
      <?php foreach ($galerie_accueil as $p) : ?>
      <figure>
        <img src="<?= asset('img/' . $p['src']) ?>" alt="<?= e($p['legende']) ?>" loading="lazy" width="600" height="450">
        <figcaption><?= e($p['legende']) ?></figcaption>
      </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Photo agrandie">
  <button class="lightbox-close icon-btn" type="button" aria-label="Fermer"><?= icon('x', 28) ?></button>
  <img src="" alt="">
</div>

<!-- ============ PARTENAIRES ============ -->
<section class="section section-alt" id="partenaires">
  <div class="container">
    <div class="section-head centered reveal">
      <span class="kicker"><?= icon('handshake', 15) ?> <?= e($partenaires_kicker) ?></span>
      <h2><?= e($partenaires_titre) ?></h2>
    </div>
    <div class="partner-strip reveal">
      <?php foreach ($partenaires_strip as $p) : ?>
      <div class="partner-logo">
        <img src="<?= asset('img/partenaires/' . $p['fichier'] . '.jpg') ?>" alt="Logo <?= e($p['nom']) ?>" loading="lazy" width="120" height="58">
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<style>.flip-x{transform:scaleX(-1);}</style>

<?php require __DIR__ . '/includes/footer.php'; ?>
