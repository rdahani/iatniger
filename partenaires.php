<?php
/** Partenaires institutionnels, académiques et entreprises. */

require_once __DIR__ . '/config/config.php';

$page_title = 'Partenaires — Un réseau au service de nos étudiants | IAT Niger';
$page_desc = "ANPE, EMIG, ESSEC Douala, IST, HCR, CIPMEN… Découvrez les partenaires institutionnels, académiques et privés de l'IAT Niger.";
$page_slug = 'partenaires';
$active = 'institut';
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => url()],
    ['label' => 'Partenaires', 'url' => url('partenaires')],
];
$hero_titre = 'Nos partenaires';
$hero_texte = "Institutions publiques, universités et entreprises : un réseau qui renforce la formation et l'insertion de nos diplômés.";
cms_apply_page('partenaires', $page_title, $page_desc, $hero_titre, $hero_texte);

$partenaires_defaut = [
    ['fichier' => 'essecd', 'nom' => 'ESSEC — Université de Douala', 'type' => 'Académique',
        'desc' => "Partenaire du Master de Recherche et du Doctorat : formation, mobilité, projets de recherche et événements scientifiques."],
    ['fichier' => 'anpe', 'nom' => 'ANPE', 'type' => 'Institutionnel',
        'desc' => "L'Agence Nationale pour la Promotion de l'Emploi accompagne l'insertion professionnelle des diplômés."],
    ['fichier' => 'emig', 'nom' => 'EMIG', 'type' => 'Académique',
        'desc' => "L'École des Mines, de l'Industrie et de la Géologie, partenaire académique des filières industrielles."],
    ['fichier' => 'ist', 'nom' => 'IST', 'type' => 'Académique',
        'desc' => "L'Institut des Sciences et Technologies, partenaire d'échanges pédagogiques."],
    ['fichier' => 'hcr', 'nom' => 'HCR', 'type' => 'Institutionnel',
        'desc' => "Le Haut-Commissariat des Nations Unies pour les Réfugiés, partenaire de programmes de formation."],
    ['fichier' => 'cipmen', 'nom' => 'CIPMEN', 'type' => 'Entrepreneuriat',
        'desc' => "Le Centre Incubateur des PME au Niger soutient les projets entrepreneuriaux des étudiants."],
    ['fichier' => 'opagen', 'nom' => 'OPAGEN', 'type' => 'Professionnel',
        'desc' => "Partenaire professionnel pour les stages et l'insertion des diplômés."],
    ['fichier' => 'labari', 'nom' => 'Labari', 'type' => 'Médias',
        'desc' => "Partenaire média qui relaie les activités et événements de l'institut."],
];
$partenaires = cms_partenaires() ?: $partenaires_defaut;

$cta_titre = cms_texte('partenaires_cta_titre', "Devenir partenaire de l'IAT");
$cta_lead = cms_texte('partenaires_cta_lead', 'Entreprise, institution ou université : construisons ensemble des programmes qui servent la jeunesse africaine.');
$cta_btn = cms_texte('partenaires_cta_btn', 'Proposer un partenariat');

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-hero.php';
?>

<section class="section">
  <div class="container">
    <div class="grid-4">
      <?php foreach ($partenaires as $i => $p) : ?>
      <article class="card reveal <?= $i % 4 === 1 ? 'reveal-delay-1' : ($i % 4 === 2 ? 'reveal-delay-2' : ($i % 4 === 3 ? 'reveal-delay-3' : '')) ?>">
        <div class="partner-logo" style="filter: none; opacity: 1; margin-bottom: 1.1rem;">
          <img src="<?= asset('img/partenaires/' . $p['fichier'] . '.jpg') ?>" alt="Logo <?= e($p['nom']) ?>" loading="lazy" width="120" height="58">
        </div>
        <div class="badges" style="margin-bottom: 0.6rem;"><span class="badge badge-primary"><?= e($p['type']) ?></span></div>
        <h3 style="font-size: 1.05rem;"><?= e($p['nom']) ?></h3>
        <p><?= e($p['desc']) ?></p>
      </article>
      <?php endforeach; ?>
    </div>

    <div class="card reveal" style="margin-top: 3rem; text-align: center; padding: clamp(2rem, 5vw, 3rem);">
      <h2 class="h3"><?= e($cta_titre) ?></h2>
      <p class="lead" style="margin: 0.8rem auto 1.5rem; max-width: 560px;"><?= e($cta_lead) ?></p>
      <a class="btn btn-primary btn-lg" href="<?= url('contact') ?>"><?= e($cta_btn) ?> <?= icon('arrow-right', 18) ?></a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
