<?php
/** CSP Algoza : maternelle, primaire, collège et lycée du Groupe IAT. */

require_once __DIR__ . '/config/config.php';

$page_title = 'CSP Algoza — Maternelle, Primaire, Collège & Lycée | Groupe IAT';
$page_desc = "Le Complexe Scolaire Privé Algoza : anglais renforcé dès le CI, un ordinateur par élève, classes de 25, cantine et jardin pédagogique. Séries A, C et D au lycée.";
$page_slug = 'csp-algoza';
$active = 'vie';
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => url()],
    ['label' => 'CSP Algoza', 'url' => url('csp-algoza')],
];
$hero_titre = "CSP Algoza : l'excellence de la maternelle au bac";
$hero_texte = "Préparer nos élèves à devenir des citoyens responsables en offrant un enseignement visant l'excellence académique.";
cms_apply_page('csp-algoza', $page_title, $page_desc, $hero_titre, $hero_texte);

/* ----- Contenu éditable (CMS avec fallback sur les valeurs par défaut) ----- */
$csp_intro = cms_texte('csp-algoza_intro', "Le curriculum national nigérien, enrichi de cours solides en anglais et en informatique, dans une approche pédagogique multiculturelle où chaque enfant peut s'épanouir.");
$csp_reductions = cms_texte('csp-algoza_reductions', "Des réductions sont accordées aux familles ayant plus de trois enfants inscrits au CSP Algoza.");

$atouts = cms_cartes('csp-algoza-atouts') ?: [
    ['titre' => 'Anglais renforcé', 'contenu' => "Dès le Cours d'Initiation au primaire, et 4 heures par semaine en petits groupes au collège-lycée.", 'extra' => ['icone' => 'globe']],
    ['titre' => '1 élève, 1 ordinateur', 'contenu' => '25 ordinateurs par salle : initiation dès la maternelle (jeux éducatifs, Windows, Word) et cours pratiques hebdomadaires.', 'extra' => ['icone' => 'monitor']],
    ['titre' => '25 élèves par classe', 'contenu' => "Des effectifs limités pour un suivi individualisé par des enseignants qualifiés, avec préparation d'exposés à la bibliothèque.", 'extra' => ['icone' => 'users']],
    ['titre' => 'Cantine & jardin', 'contenu' => 'Cantine quotidienne (petit déjeuner et déjeuner, plats africains et nigériens variés) et jardin potager pédagogique en 6ème-5ème.', 'extra' => ['icone' => 'utensils']],
];

$tarifs_csp = cms_tarifs('csp-algoza');
$tarif_mat = $tarif_col = $tarif_lyc = null;
foreach ($tarifs_csp as $t) {
    if ($tarif_mat === null && stripos($t['titre'], 'Maternelle') !== false) {
        $tarif_mat = $t;
    } elseif ($tarif_col === null && stripos($t['titre'], 'Collège') !== false) {
        $tarif_col = $t;
    } elseif ($tarif_lyc === null && stripos($t['titre'], 'Lycée') !== false) {
        $tarif_lyc = $t;
    }
}
$tarif_mat = $tarif_mat ?: ['extra' => ['inscription' => 30000, 'scolarite' => 200000, 'total' => 230000]];
$tarif_col = $tarif_col ?: ['extra' => ['inscription' => 30000, 'fournitures' => 40000, 'tenues' => 20000, 'scolarite' => 250000, 'total' => 340000]];
$tarif_lyc = $tarif_lyc ?: ['extra' => ['inscription' => 30000, 'fournitures' => 40000, 'tenues' => 20000, 'scolarite' => 300000, 'total' => 390000]];

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-hero.php';
?>

<!-- Atouts -->
<section class="section">
  <div class="container">
    <div class="section-head centered reveal">
      <span class="kicker"><?= icon('sparkles', 15) ?> Nos atouts</span>
      <h2>Un enseignement enrichi, des classes à taille humaine</h2>
      <p class="lead"><?= e($csp_intro) ?></p>
    </div>
    <div class="grid-4">
      <?php foreach ($atouts as $i => $a) : ?>
      <article class="card reveal<?= $i > 0 ? ' reveal-delay-' . min($i, 3) : '' ?>">
        <span class="card-icon"><?= icon($a['extra']['icone'] ?? 'sparkles', 24) ?></span>
        <h3><?= e($a['titre']) ?></h3>
        <p><?= e($a['contenu']) ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Niveaux -->
<section class="section section-alt">
  <div class="container">
    <div class="section-head reveal">
      <span class="kicker"><?= icon('school', 15) ?> Les niveaux</span>
      <h2>De la Petite Section à la Terminale</h2>
    </div>
    <div class="grid-2" style="align-items: stretch;">
      <article class="card reveal">
        <span class="card-icon"><?= icon('baby', 24) ?></span>
        <h3>Maternelle &amp; Primaire</h3>
        <p style="margin-bottom: 1rem;">Petite Section (2-3 ans), Moyenne Section (3-4 ans), Grande Section (4-5 ans), puis Cours d'Initiation (5-6 ans) et tout le cycle primaire. L'anglais commence dès le CI.</p>
        <div class="table-wrap">
          <table class="table">
            <caption class="visually-hidden">Frais annuels maternelle et primaire</caption>
            <thead><tr><th scope="col">Inscription</th><th scope="col">Scolarité</th><th scope="col">Total annuel</th></tr></thead>
            <tbody><tr><td><?= e(cms_fcfa($tarif_mat['extra']['inscription'] ?? 0)) ?></td><td><?= e(cms_fcfa($tarif_mat['extra']['scolarite'] ?? 0)) ?></td><td><strong><?= e(cms_fcfa($tarif_mat['extra']['total'] ?? 0)) ?></strong></td></tr></tbody>
          </table>
        </div>
      </article>
      <article class="card reveal reveal-delay-1">
        <span class="card-icon"><?= icon('school', 24) ?></span>
        <h3>Collège &amp; Lycée</h3>
        <p style="margin-bottom: 1rem;">Programme de l'État nigérien enrichi. Au lycée, trois orientations : les séries A, C et D. Uniforme : robe/chemise blanche, jupe/pantalon bleu marine.</p>
        <div class="table-wrap">
          <table class="table">
            <caption class="visually-hidden">Frais annuels collège et lycée</caption>
            <thead><tr><th scope="col">Section</th><th scope="col">Inscription</th><th scope="col">Fournitures</th><th scope="col">Tenues</th><th scope="col">Scolarité</th><th scope="col">Total</th></tr></thead>
            <tbody>
              <tr><td><strong>Collège</strong></td><td><?= e(number_format((float) ($tarif_col['extra']['inscription'] ?? 0), 0, ',', ' ')) ?></td><td><?= e(number_format((float) ($tarif_col['extra']['fournitures'] ?? 0), 0, ',', ' ')) ?></td><td><?= e(number_format((float) ($tarif_col['extra']['tenues'] ?? 0), 0, ',', ' ')) ?></td><td><?= e(number_format((float) ($tarif_col['extra']['scolarite'] ?? 0), 0, ',', ' ')) ?></td><td><strong><?= e(cms_fcfa($tarif_col['extra']['total'] ?? 0)) ?></strong></td></tr>
              <tr><td><strong>Lycée</strong></td><td><?= e(number_format((float) ($tarif_lyc['extra']['inscription'] ?? 0), 0, ',', ' ')) ?></td><td><?= e(number_format((float) ($tarif_lyc['extra']['fournitures'] ?? 0), 0, ',', ' ')) ?></td><td><?= e(number_format((float) ($tarif_lyc['extra']['tenues'] ?? 0), 0, ',', ' ')) ?></td><td><?= e(number_format((float) ($tarif_lyc['extra']['scolarite'] ?? 0), 0, ',', ' ')) ?></td><td><strong><?= e(cms_fcfa($tarif_lyc['extra']['total'] ?? 0)) ?></strong></td></tr>
            </tbody>
          </table>
        </div>
      </article>
    </div>
    <div class="alert alert-success reveal" style="margin-top: 2rem;">
      <?= icon('check-circle', 20) ?>
      <div><strong>Réductions familles nombreuses :</strong> <?= e($csp_reductions) ?></div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section">
  <div class="container text-center">
    <div class="section-head centered reveal">
      <h2>Inscrivez votre enfant au CSP Algoza</h2>
      <p class="lead">Contactez-nous pour connaître les disponibilités et planifier une visite de l'école.</p>
    </div>
    <div class="hero-actions" style="justify-content: center;">
      <a class="btn btn-primary btn-lg" href="<?= url('contact') ?>">Nous contacter <?= icon('arrow-right', 18) ?></a>
      <a class="btn btn-outline btn-lg" href="tel:+22796970792"><?= icon('phone', 18) ?> <?= e(SITE_PHONE_2) ?></a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
