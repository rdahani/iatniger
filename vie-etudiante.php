<?php
/** Vie étudiante : BDE, clubs, activités, alumni. */

require_once __DIR__ . '/config/config.php';

$page_title = 'Vie étudiante — BDE, clubs et alumni | IAT Niger';
$page_desc = "BDE actif, clubs engagés, sport, culture, voyages d'études et un réseau d'anciens présent dans les ministères et grandes entreprises du Niger.";
$page_slug = 'vie-etudiante';
$active = 'vie';
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => url()],
    ['label' => 'Vie étudiante', 'url' => url('vie-etudiante')],
];
$hero_titre = 'Un campus qui forme aussi des leaders';
$hero_texte = "Sport, culture, engagement citoyen, voyages d'études : à l'IAT, la vie étudiante fait partie de la formation.";
cms_apply_page('vie-etudiante', $page_title, $page_desc, $hero_titre, $hero_texte);

/* ----- Contenu éditable (CMS avec fallback sur les valeurs par défaut) ----- */
$bde_kicker = cms_texte('vie-etudiante_bde_kicker', 'Bureau Des Étudiants');
$bde_titre = cms_texte('vie-etudiante_bde_titre', 'Le BDE, moteur de la vie du campus');
$bde_lead = cms_texte('vie-etudiante_bde_lead', "Le BDE s'engage à « défendre les intérêts matériels et moraux de ses camarades dans le respect des textes de l'établissement », en cohérence avec la vision de l'IAT : promouvoir les futurs leaders.");

$cartes_bde = cms_cartes('vie-etudiante-bde') ?: [
    ['titre' => 'Culture & sport', 'contenu' => 'Représentations culturelles (culture peulh…), matchs de football amicaux, fêtes de fin d\'année et animations tout au long de l\'année.', 'extra' => ['icone' => 'star']],
    ['titre' => 'Découverte & ouverture', 'contenu' => "Visites d'entreprises, excursions et voyages d'études — comme le voyage au Bénin (2018-2019) — et participation au Salon des Grandes Écoles.", 'extra' => ['icone' => 'globe']],
    ['titre' => 'Engagement citoyen', 'contenu' => 'Journées communautaires : don de sang, plantation d\'arbres, sensibilisation contre les violences basées sur le genre avec le Club PPF.', 'extra' => ['icone' => 'heart']],
];

$club_kicker = cms_texte('vie-etudiante_club_kicker', 'Clubs étudiants');
$club_titre = cms_texte('vie-etudiante_club_titre', 'Le Club PPF : des étudiantes actrices du changement');
$club_ppf_texte = cms_texte('vie-etudiante_club_ppf', "Le Club PPF (Participation Politique des Femmes) mène des campagnes de sensibilisation sur le campus et au-delà. Il a récemment reçu du matériel de sonorisation pour amplifier ses actions, et co-organise des journées de sensibilisation contre les violences basées sur le genre.");
$club_image = cms_texte('vie-etudiante_club_image', 'actualites/club-ppf-appui.jpg');
$club_btn = cms_texte('vie-etudiante_club_btn', 'Suivre leurs actions');

$alumni_kicker = cms_texte('vie-etudiante_alumni_kicker', 'Alumni');
$alumni_titre = cms_texte('vie-etudiante_alumni_titre', "L'Amicale des Anciens : un réseau qui ouvre des portes");
$alumni_intro = cms_texte('vie-etudiante_alumni_intro', "Les anciens de l'IAT occupent des postes dans les ministères (Finances, Transport, DGI), le secteur privé (consulting, télécoms), le secteur public et l'entrepreneuriat.");
$alumni_caption = cms_texte('vie-etudiante_alumni_caption', "Le bureau compte également des secrétaires chargés de la communication et de l'organisation, des commissaires aux comptes et un trésorier adjoint.");

$alumni = cms_alumni('vie-etudiante') ?: [
    ['titre' => 'Abdoulaye Souleymane', 'sous_titre' => "Président de l'Amicale", 'contenu' => "Protocole, Assistant du Ministre d'État à la Présidence de la République.", 'extra' => ['initiales' => 'AS']],
    ['titre' => 'Moumouni Absi', 'sous_titre' => 'Vice-président', 'contenu' => 'Département achats et logistique à la Nigelec.', 'extra' => ['initiales' => 'MA']],
    ['titre' => 'Salif Moussa Douké', 'sous_titre' => 'Secrétaire Général', 'contenu' => "Coordonnateur national de l'ONG OADES-Niger.", 'extra' => ['initiales' => 'SM']],
    ['titre' => 'Maria Saley', 'sous_titre' => 'Trésorière Générale', 'contenu' => "Membre du bureau de l'Amicale des Anciens Élèves et Étudiants de l'IAT.", 'extra' => ['initiales' => 'MS']],
];

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-hero.php';
?>

<!-- BDE -->
<section class="section" id="bde">
  <div class="container">
    <div class="section-head reveal">
      <span class="kicker"><?= icon('heart', 15) ?> <?= e($bde_kicker) ?></span>
      <h2><?= e($bde_titre) ?></h2>
      <p class="lead"><?= e($bde_lead) ?></p>
    </div>
    <div class="grid-3">
      <?php foreach ($cartes_bde as $i => $c) : ?>
      <article class="card reveal<?= $i > 0 ? ' reveal-delay-' . min($i, 3) : '' ?>">
        <span class="card-icon"><?= icon($c['extra']['icone'] ?? 'star', 24) ?></span>
        <h3><?= e($c['titre']) ?></h3>
        <p><?= e($c['contenu']) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Club PPF -->
<section class="section section-alt">
  <div class="container grid-2">
    <div class="reveal">
      <img src="<?= asset('img/' . ltrim($club_image, '/')) ?>" alt="Les membres du Club PPF de l'IAT recevant du matériel" loading="lazy" width="700" height="480" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
    </div>
    <div class="reveal reveal-delay-1">
      <span class="kicker"><?= icon('megaphone', 15) ?> <?= e($club_kicker) ?></span>
      <h2><?= e($club_titre) ?></h2>
      <p class="lead" style="margin-top: 1rem;"><?= e($club_ppf_texte) ?></p>
      <div class="hero-actions">
        <a class="btn btn-outline" href="<?= url('actualites') ?>"><?= e($club_btn) ?> <?= icon('arrow-right', 16) ?></a>
      </div>
    </div>
  </div>
</section>

<!-- Alumni -->
<section class="section" id="alumni">
  <div class="container">
    <div class="section-head reveal">
      <span class="kicker"><?= icon('briefcase', 15) ?> <?= e($alumni_kicker) ?></span>
      <h2><?= e($alumni_titre) ?></h2>
      <p class="lead"><?= e($alumni_intro) ?></p>
    </div>
    <div class="grid-4">
      <?php foreach ($alumni as $i => $a) : ?>
      <article class="card reveal<?= $i > 0 ? ' reveal-delay-' . min($i, 3) : '' ?>">
        <span class="testimonial-author" style="margin-bottom: 0.8rem;"><span class="avatar" aria-hidden="true"><?= e($a['extra']['initiales'] ?? '') ?></span></span>
        <h3 style="font-size: 1.05rem;"><?= e($a['titre']) ?></h3>
        <p class="caption"><?= e($a['sous_titre']) ?></p>
        <p><?= e($a['contenu']) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
    <p class="caption reveal" style="margin-top: 1.5rem;"><?= e($alumni_caption) ?></p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
