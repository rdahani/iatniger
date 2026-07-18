<?php
/** À propos : historique, mission, vision, valeurs, direction, enseignants. */

require_once __DIR__ . '/config/config.php';

$page_title = 'À propos — Histoire, mission et vision | IAT Niger';
$page_desc = "Créé en 1999, l'Institut Africain de Technologie a formé plus de 30 000 diplômés. Découvrez son histoire, sa mission, ses valeurs et son corps enseignant de rang A.";
$page_slug = 'a-propos';
$active = 'institut';
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => url()],
    ['label' => 'À propos', 'url' => url('a-propos')],
];
$hero_titre = "Un pôle d'excellence au service de l'Afrique depuis 1999";
$hero_texte = "De quelques salles louées au rond-point Gadafawa à un institut accrédité CAMES fort de 30 000 diplômés : l'histoire d'une ambition africaine.";
cms_apply_page('a-propos', $page_title, $page_desc, $hero_titre, $hero_texte);

/* ----- Contenu éditable (CMS avec fallback sur les valeurs par défaut) ----- */
$mission_titre = cms_texte('a-propos_mission_titre', "Former les talents qui développent l'Afrique");
$mission_texte = cms_texte('a-propos_mission_texte', "« Donner une formation de haut niveau adaptée au contexte africain et du monde contemporain afin de mettre à la disposition du marché » les compétences dont les entreprises ont besoin, et mobiliser les ressources intellectuelles pour le développement économique du continent.");

$valeurs = cms_cartes('a-propos-valeurs') ?: [
    ['titre' => 'Excellence', 'contenu' => 'Des programmes exigeants, des enseignants de rang A'],
    ['titre' => 'Qualité', 'contenu' => '16 diplômes accrédités CAMES, normes LMD'],
    ['titre' => 'Transparence', 'contenu' => 'Une gouvernance claire, un conseil scientifique actif'],
    ['titre' => 'Ouverture au monde', 'contenu' => 'Partenariats académiques internationaux'],
];

$stats_a_propos = cms_stats('a-propos') ?: [
    ['titre' => 'diplômés du niveau supérieur', 'extra' => ['valeur' => 20000, 'suffixe' => '']],
    ['titre' => 'diplômés du niveau moyen', 'extra' => ['valeur' => 8000, 'suffixe' => '']],
    ['titre' => 'certifications & attestations', 'extra' => ['valeur' => 2000, 'suffixe' => '']],
    ['titre' => 'diplômes accrédités CAMES', 'extra' => ['valeur' => 16, 'suffixe' => '']],
];

$timeline = cms_timeline('a-propos') ?: [
    ['titre' => '1999', 'sous_titre' => "Naissance de l'institut", 'contenu' => "Création par Arrêté N° 0143/MEN/DEPRI/DETFP du 26 juillet 1999, puis ouverture officielle par Arrêté N° 0233 du 17 novembre 1999, dans un immeuble en location au rond-point Gadafawa, derrière la station TOTAL à Niamey."],
    ['titre' => '2002', 'sous_titre' => 'Un campus en propre', 'contenu' => "Après seulement trois années d'activité, l'institut acquiert sur fonds propres un immeuble à trois niveaux — signe d'une gestion rigoureuse et d'une croissance saine."],
    ['titre' => '2014', 'sous_titre' => 'Extension du campus', 'contenu' => "Réception en octobre 2014 d'un deuxième immeuble construit entre 2012 et 2014 : 10 salles de cours et 5 bureaux, dont celui du président du conseil scientifique."],
    ['titre' => '2020', 'sous_titre' => 'Reconnaissance internationale', 'contenu' => "L'institut reçoit l'Arch of Europe Award, qui distingue son engagement pour la qualité."],
    ['titre' => '2022', 'sous_titre' => 'Cap sur la recherche', 'contenu' => "Signature de la convention de partenariat avec l'ESSEC de l'Université de Douala (Cameroun) : localisation du Master de Recherche et du Doctorat à Niamey."],
    ['titre' => '2026', 'sous_titre' => "L'ère des laboratoires", 'contenu' => "Inauguration de deux laboratoires modernes (Génie Électrique et Génie Civil) et prix Alkalami d'Or de l'excellence académique. L'IAT rejoint aussi le Hub de Peering Fédéré de Niger-REN."],
];

$direction_texte = cms_texte('a-propos_direction', "Depuis 1999, l'Institut Africain de Technologie poursuit une seule ambition : offrir à la jeunesse nigérienne et africaine une formation à la hauteur de son potentiel. Nos quatre valeurs — l'excellence, la qualité, la transparence et l'ouverture au monde — ne sont pas des slogans : elles se lisent dans nos accréditations CAMES, dans nos laboratoires, dans les carrières de nos 30 000 diplômés.\n\nChoisir l'IAT, c'est rejoindre une institution qui investit continuellement dans ses infrastructures, son corps enseignant et ses partenariats internationaux, pour que chaque étudiant reparte avec bien plus qu'un diplôme : un métier, une méthode et un réseau.");
$direction_paragraphes = preg_split('/\n\s*\n/', trim($direction_texte));

$enseignants_intro = cms_texte('a-propos_enseignants_intro', 'Les Masters professionnels et le Master de Recherche sont animés par des enseignants-chercheurs de rang A — professeurs titulaires du CAMES, professeurs agrégés et maîtres de conférences.');

$enseignants_cartes = cms_cartes('a-propos-enseignants') ?: [
    ['titre' => 'Grades académiques', 'contenu' => 'Professeurs titulaires du CAMES, professeurs agrégés, maîtres de conférences et agrégés en Science de Gestion.', 'extra' => ['icone' => 'award']],
    ['titre' => 'Disciplines couvertes', 'contenu' => 'Science de Gestion, Réseaux Informatiques, Sociologie et Psycho-Sociologie — au service de filières tertiaires et industrielles.', 'extra' => ['icone' => 'book-open']],
    ['titre' => "Universités d'attache", 'contenu' => 'Abdou Moumouni (Niger), Cheikh Anta Diop (Sénégal), Douala (Cameroun), Kara (Togo), Paul-Valéry Montpellier (France), et des universités du Bénin, du Burkina Faso et de Côte d\'Ivoire.', 'extra' => ['icone' => 'globe']],
];

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-hero.php';
?>

<!-- Mission & Vision -->
<section class="section">
  <div class="container">
    <div class="grid-2">
      <div class="reveal">
        <span class="kicker"><?= icon('target', 15) ?> Notre mission</span>
        <h2><?= e($mission_titre) ?></h2>
        <p class="lead" style="margin-top:1rem;"><?= e($mission_texte) ?></p>
        <ul class="check-list" style="margin-top:1.5rem;">
          <?php foreach ($valeurs as $v) : ?>
          <li><?= icon('check', 18) ?><span><strong><?= e($v['titre']) ?></strong> — <?= e($v['contenu']) ?></span></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="reveal reveal-delay-1">
        <img src="<?= asset('img/campus/immeuble-iat.jpg') ?>" alt="L'immeuble principal de l'IAT à Niamey" loading="lazy" width="720" height="540" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);">
      </div>
    </div>
  </div>
</section>

<!-- Chiffres -->
<section class="stats-band" aria-label="Nos diplômés en chiffres">
  <div class="container stats-grid">
    <?php foreach ($stats_a_propos as $i => $s) : ?>
    <div class="stat reveal<?= $i > 0 ? ' reveal-delay-' . min($i, 3) : '' ?>">
      <div class="stat-value"><span data-count="<?= (int) ($s['extra']['valeur'] ?? 0) ?>">0</span><?php if (!empty($s['extra']['suffixe'])) : ?><span class="suffix"><?= e($s['extra']['suffixe']) ?></span><?php endif; ?></div>
      <div class="stat-label"><?= e($s['titre']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Historique / Timeline -->
<section class="section section-alt" id="historique">
  <div class="container">
    <div class="section-head reveal">
      <span class="kicker"><?= icon('clock', 15) ?> Historique</span>
      <h2>Plus de 25 ans de croissance continue</h2>
    </div>
    <div class="timeline">
      <?php foreach ($timeline as $t) : ?>
      <div class="timeline-item reveal">
        <span class="year"><?= e($t['titre']) ?></span>
        <h3><?= e($t['sous_titre']) ?></h3>
        <p><?= e($t['contenu']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Mot de la direction -->
<section class="section" id="direction">
  <div class="container">
    <div class="card direction-card reveal">
      <img src="<?= asset('img/fondateur-hamadou-hamidou.jpg') ?>" alt="M. Hamadou Hamidou, fondateur de l'IAT Niger" loading="lazy" width="500" height="713" style="border-radius: var(--radius); box-shadow: var(--shadow-md); width: 100%;">
      <div>
        <span class="quote-icon" style="color: var(--accent);"><?= icon('quote', 34) ?></span>
        <h2 style="margin: 1rem 0;">Le mot de la Direction</h2>
        <?php foreach ($direction_paragraphes as $p) : ?>
        <p style="color: var(--text-2); font-size: 1.05rem; margin-bottom: 1.2rem;"><?= e($p) ?></p>
        <?php endforeach; ?>
        <p style="font-family: var(--font-display); font-weight: 700;">M. Hamadou Hamidou — Fondateur de l'IAT Niger</p>
      </div>
    </div>
  </div>
</section>

<!-- Corps enseignant -->
<section class="section section-alt" id="enseignants">
  <div class="container">
    <div class="section-head reveal">
      <span class="kicker"><?= icon('users', 15) ?> Corps enseignant</span>
      <h2>36 enseignants-chercheurs de rang A</h2>
      <p class="lead"><?= e($enseignants_intro) ?></p>
    </div>
    <div class="grid-3">
      <?php foreach ($enseignants_cartes as $i => $c) : ?>
      <article class="card reveal<?= $i > 0 ? ' reveal-delay-' . min($i, 3) : '' ?>">
        <span class="card-icon"><?= icon($c['extra']['icone'] ?? 'award', 24) ?></span>
        <h3><?= e($c['titre']) ?></h3>
        <p><?= e($c['contenu']) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
