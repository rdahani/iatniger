<?php
/** WEB TV : vidéothèque de l'institut (liens vers le lecteur historique). */

require_once __DIR__ . '/config/config.php';

$page_title = 'WEB TV — Reportages et événements en vidéo | IAT Niger';
$page_desc = "La chaîne vidéo de l'IAT Niger : rentrées solennelles, inaugurations de laboratoires, salons, partenariats et reportages sur la vie du campus.";
$page_slug = 'web-tv';
$active = 'actualites';
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => url()],
    ['label' => 'WEB TV', 'url' => url('web-tv')],
];
$hero_titre = 'WEB TV';
$hero_texte = "Rentrées solennelles, inaugurations, salons et reportages : l'institut en images.";
cms_apply_page('web-tv', $page_title, $page_desc, $hero_titre, $hero_texte);

/* Vidéothèque migrée du site historique (lecteur hébergé sur iatniger.org). */
$videos_defaut = [
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
$videos = cms_videos() ?: $videos_defaut;

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-hero.php';
?>

<section class="section">
  <div class="container">
    <div class="grid-3">
      <?php foreach ($videos as $i => $v) : ?>
      <article class="card news-card video-card reveal <?= $i % 3 === 1 ? 'reveal-delay-1' : ($i % 3 === 2 ? 'reveal-delay-2' : '') ?>">
        <div class="news-img">
          <img src="<?= asset('img/' . $v['image']) ?>" alt="" loading="lazy" width="640" height="360">
          <a class="play-badge" href="https://www.iatniger.org/index.php/web-tv/video/<?= e($v['path']) ?>" target="_blank" rel="noopener" aria-label="Regarder : <?= e($v['titre']) ?>">
            <span><?= icon('play', 26) ?></span>
          </a>
        </div>
        <div class="news-body">
          <h3 style="font-size: 1.02rem;"><?= e($v['titre']) ?></h3>
          <p class="caption"><?= icon('eye', 14) ?> <?= number_format($v['vues'], 0, ',', ' ') ?> vues</p>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <p class="caption text-center" style="margin-top: 2.5rem;">Les vidéos s'ouvrent dans le lecteur de la WEB TV IAT. Suivez-nous aussi sur <a href="<?= e(SITE_FACEBOOK) ?>" target="_blank" rel="noopener">Facebook</a>.</p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
