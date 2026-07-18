<?php
/** Galerie photo masonry avec lightbox. */

require_once __DIR__ . '/config/config.php';

$page_title = 'Galerie — Le campus en images | IAT Niger';
$page_desc = "Le campus de l'IAT Niger en images : bâtiments, laboratoires, vie étudiante, événements et distinctions.";
$page_slug = 'galerie';
$active = 'institut';
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => url()],
    ['label' => 'Galerie', 'url' => url('galerie')],
];
$hero_titre = 'Le campus en images';
$hero_texte = "Bâtiments, laboratoires, événements et vie étudiante : découvrez l'IAT en photos.";
cms_apply_page('galerie', $page_title, $page_desc, $hero_titre, $hero_texte);

$photos_defaut = [
    ['src' => 'recentes/photo-15.jpg', 'legende' => 'Projet architectural — étudiants en génie civil', 'cat' => 'vie-etudiante'],
    ['src' => 'recentes/photo-13.jpg', 'legende' => 'Étudiante en génie civil — formation pratique', 'cat' => 'vie-etudiante'],
    ['src' => 'recentes/photo-10.jpg', 'legende' => 'Travaux pratiques — automate PLC en laboratoire', 'cat' => 'campus'],
    ['src' => 'recentes/photo-17.jpg', 'legende' => 'Topographie et levé — travaux pratiques', 'cat' => 'campus'],
    ['src' => 'recentes/photo-14.jpg', 'legende' => 'Remise de distinctions — partenaires et lauréats', 'cat' => 'evenements'],
    ['src' => 'recentes/photo-25.jpg', 'legende' => 'Étudiantes en journée culturelle', 'cat' => 'vie-etudiante'],
    ['src' => 'recentes/photo-16.jpg', 'legende' => 'Journée culturelle — patrimoine nigérien', 'cat' => 'vie-etudiante'],
    ['src' => 'recentes/photo-19.jpg', 'legende' => 'Plantation d\'arbre — visite CAEPE', 'cat' => 'evenements'],
    ['src' => 'recentes/photo-11.jpg', 'legende' => 'Distinction officielle — trophée d\'excellence', 'cat' => 'evenements'],
    ['src' => 'campus/immeuble-iat.jpg', 'legende' => "L'immeuble principal du campus, rond-point Gadafawa", 'cat' => 'campus'],
    ['src' => 'actualites/laboratoires-lancement.jpg', 'legende' => 'Lancement officiel des deux nouveaux laboratoires', 'cat' => 'evenements'],
    ['src' => 'actualites/labo-genie-electrique.jpg', 'legende' => 'Le laboratoire de Génie Électrique', 'cat' => 'campus'],
];
$photos = cms_galerie() ?: $photos_defaut;

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-hero.php';
?>

<section class="section">
  <div class="container">
    <div class="tabs-nav" role="tablist" aria-label="Filtrer la galerie">
      <button role="tab" aria-selected="true" aria-controls="pan-tous" id="tab-tous">Tout</button>
      <button role="tab" aria-selected="false" aria-controls="pan-campus" id="tab-campus" tabindex="-1">Campus</button>
      <button role="tab" aria-selected="false" aria-controls="pan-evenements" id="tab-evenements" tabindex="-1">Événements</button>
      <button role="tab" aria-selected="false" aria-controls="pan-vie" id="tab-vie" tabindex="-1">Vie étudiante</button>
    </div>

    <?php
    /** Rend une grille masonry pour un sous-ensemble de photos. */
    function galerie_grille(array $photos): void
    {
        echo '<div class="masonry">';
        foreach ($photos as $p) {
            echo '<figure><img src="' . asset('img/' . $p['src']) . '" alt="' . e($p['legende']) . '" loading="lazy" width="600" height="400"><figcaption>' . e($p['legende']) . '</figcaption></figure>';
        }
        echo '</div>';
    }
    ?>
    <div class="tab-panel" id="pan-tous" role="tabpanel" aria-labelledby="tab-tous"><?php galerie_grille($photos); ?></div>
    <div class="tab-panel" id="pan-campus" role="tabpanel" aria-labelledby="tab-campus" hidden><?php galerie_grille(array_filter($photos, fn ($p) => $p['cat'] === 'campus')); ?></div>
    <div class="tab-panel" id="pan-evenements" role="tabpanel" aria-labelledby="tab-evenements" hidden><?php galerie_grille(array_filter($photos, fn ($p) => $p['cat'] === 'evenements')); ?></div>
    <div class="tab-panel" id="pan-vie" role="tabpanel" aria-labelledby="tab-vie" hidden><?php galerie_grille(array_filter($photos, fn ($p) => $p['cat'] === 'vie-etudiante')); ?></div>
  </div>
</section>

<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Photo agrandie">
  <button class="lightbox-close icon-btn" type="button" aria-label="Fermer"><?= icon('x', 28) ?></button>
  <img src="" alt="">
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
