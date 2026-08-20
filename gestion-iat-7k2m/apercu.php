<?php
/**
 * Aperçu guidé du site : prévisualisation de chaque page publique dans un
 * cadre, avec la liste des zones modifiables et le lien direct vers l'écran
 * admin correspondant. Pensé pour les utilisateurs non techniques.
 */

declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';
require_login();

/**
 * Cartographie : page publique → zones éditables.
 * Chaque zone : [libellé, chemin admin, permission].
 */
$pages_apercu = [
    '' => ['label' => "Page d'accueil", 'zones' => [
        ['Hero : titre, texte, points de confiance, cartes flottantes', 'admin/accueil.php', 'accueil'],
        ['Diaporama d\'images du hero', 'admin/accueil.php', 'accueil'],
        ['Bandeau de statistiques (chiffres animés)', 'admin/accueil.php', 'accueil'],
        ['Cartes « Pourquoi choisir l\'IAT ? »', 'admin/accueil.php', 'accueil'],
        ['Mot du fondateur (photo, nom, textes)', 'admin/accueil.php', 'accueil'],
        ['Programmes : les 4 cartes de niveaux', 'admin/formations.php', 'formations'],
        ['Bloc CSP Algoza', 'admin/accueil.php', 'accueil'],
        ['Actualités récentes', 'admin/actualites.php', 'actualites'],
        ['Témoignages', 'admin/contenu.php?type=temoignage', 'temoignages'],
        ['Bandeau des partenaires', 'admin/contenu.php?type=partenaire', 'partenaires'],
        ['Titre &amp; description SEO', 'admin/pages.php', 'pages'],
    ]],
    'a-propos' => ['label' => 'À propos', 'zones' => [
        ['Mission, valeurs, chronologie, direction, enseignants', 'admin/contenu.php?type=section&groupe=a-propos', 'a-propos'],
        ['Titre &amp; description SEO', 'admin/pages.php', 'pages'],
    ]],
    'formations' => ['label' => 'Formations', 'zones' => [
        ['Niveaux (titres, sous-titres, descriptions)', 'admin/formations.php', 'formations'],
        ['Filières (28 formations)', 'admin/formations.php', 'formations'],
        ['Mega-menu Formations (bandeau promo)', 'admin/navigation.php', 'parametres'],
        ['Titre &amp; description SEO', 'admin/pages.php', 'pages'],
    ]],
    'admission' => ['label' => 'Admission', 'zones' => [
        ['Étapes d\'admission', 'admin/contenu.php?type=section&groupe=admission', 'admission'],
        ['Conditions par niveau', 'admin/formations.php', 'formations'],
        ['Préinscriptions reçues', 'admin/preinscriptions.php', 'preinscriptions'],
        ['Titre &amp; description SEO', 'admin/pages.php', 'pages'],
    ]],
    'vie-etudiante' => ['label' => 'Vie étudiante', 'zones' => [
        ['Cartes BDE, Club PPF, alumni', 'admin/contenu.php?type=section&groupe=vie-etudiante', 'vie-etudiante'],
        ['Titre &amp; description SEO', 'admin/pages.php', 'pages'],
    ]],
    'csp-algoza' => ['label' => 'CSP Algoza', 'zones' => [
        ['Intro, atouts, niveaux, tarifs, réductions', 'admin/contenu.php?type=section&groupe=csp-algoza', 'csp'],
        ['Titre &amp; description SEO', 'admin/pages.php', 'pages'],
    ]],
    'actualites' => ['label' => 'Actualités', 'zones' => [
        ['Articles (ajout, édition, publication)', 'admin/actualites.php', 'actualites'],
        ['Titre &amp; description SEO', 'admin/pages.php', 'pages'],
    ]],
    'galerie' => ['label' => 'Galerie', 'zones' => [
        ['Photos et légendes', 'admin/contenu.php?type=galerie', 'galerie'],
        ['Titre &amp; description SEO', 'admin/pages.php', 'pages'],
    ]],
    'partenaires' => ['label' => 'Partenaires', 'zones' => [
        ['Logos, noms et descriptions', 'admin/contenu.php?type=partenaire', 'partenaires'],
        ['Titre &amp; description SEO', 'admin/pages.php', 'pages'],
    ]],
    'web-tv' => ['label' => 'WEB TV', 'zones' => [
        ['Vidéos (titres, vignettes, liens)', 'admin/contenu.php?type=video', 'web-tv'],
        ['Titre &amp; description SEO', 'admin/pages.php', 'pages'],
    ]],
    'telechargements' => ['label' => 'Téléchargements', 'zones' => [
        ['Documents à télécharger', 'admin/contenu.php?type=document', 'documents'],
        ['Titre &amp; description SEO', 'admin/pages.php', 'pages'],
    ]],
    'faq' => ['label' => 'FAQ', 'zones' => [
        ['Questions / réponses', 'admin/contenu.php?type=faq', 'faq'],
        ['Titre &amp; description SEO', 'admin/pages.php', 'pages'],
    ]],
    'contact' => ['label' => 'Contact', 'zones' => [
        ['Coordonnées (adresse, téléphones, e-mail)', 'admin/parametres.php', 'parametres'],
        ['Messages reçus', 'admin/messages.php', 'messages'],
        ['Titre &amp; description SEO', 'admin/pages.php', 'pages'],
    ]],
];

$page = (string) ($_GET['page'] ?? '');
if (!array_key_exists($page, $pages_apercu)) {
    $page = '';
}
$courante = $pages_apercu[$page];

admin_head('Aperçu du site');
?>
<div class="admin-layout">
  <?php admin_sidebar('apercu'); ?>
  <main class="admin-main">
    <div class="admin-header">
      <h1 class="h2">Aperçu du site</h1>
      <a class="btn btn-outline" href="<?= url($page) ?>" target="_blank" rel="noopener"><?= icon('external-link', 16) ?> Ouvrir dans un onglet</a>
    </div>

    <div class="admin-card" style="margin-bottom: 1.2rem;">
      <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
        <?php foreach ($pages_apercu as $slug => $p) : ?>
        <a class="btn <?= $slug === $page ? 'btn-primary' : 'btn-outline' ?>" href="<?= url('admin/apercu.php' . ($slug !== '' ? '?page=' . rawurlencode($slug) : '')) ?>"><?= $p['label'] ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: minmax(260px, 340px) 1fr; gap: 1.2rem; align-items: start;">
      <div class="admin-card">
        <h2 class="h3" style="margin-bottom: 0.4rem;">Que peut-on modifier ici ?</h2>
        <p class="caption" style="margin-bottom: 1rem;">Page « <?= $courante['label'] ?> » : cliquez sur une zone pour ouvrir l'écran d'édition correspondant.</p>
        <div style="display: grid; gap: 0.5rem;">
          <?php foreach ($courante['zones'] as [$zone_label, $zone_href, $zone_perm]) :
              if (!admin_can($zone_perm)) {
                  continue;
              } ?>
          <a class="btn btn-outline" style="justify-content: flex-start; text-align: left; white-space: normal;" href="<?= url($zone_href) ?>">
            <?= icon('edit', 15) ?> <span><?= $zone_label ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="admin-card" style="padding: 0.6rem;">
        <iframe src="<?= url($page) ?>" title="Aperçu : <?= e($courante['label']) ?>"
                style="width: 100%; height: min(78vh, 900px); border: 0; border-radius: var(--radius-md, 10px); background: #fff;"
                loading="lazy"></iframe>
      </div>
    </div>

    <p class="caption" style="margin-top: 1rem;">Astuce : après une modification dans l'admin, revenez ici et rechargez la page (F5) pour voir le résultat.</p>
  </main>
</div>
</body>
</html>
