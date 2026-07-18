<?php
/**
 * Fiche filière individuelle.
 * URL propre : /formation/{slug} → formation.php?slug=…
 */

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$formation = $slug !== '' ? formation_par_slug($slug) : null;

if ($formation === null) {
    http_response_code(404);
    $page_title = 'Formation introuvable | IAT Niger';
    $page_desc = "Cette filière n'existe pas ou a été retirée du catalogue.";
    $page_slug = '404';
    $active = 'formations';
    require __DIR__ . '/includes/header.php';
    ?>
    <section class="section" style="min-height: 50vh; display: flex; align-items: center;">
      <div class="container text-center">
        <h1 class="h2">Formation introuvable</h1>
        <p class="lead" style="max-width: 520px; margin: 1rem auto 2rem;">Cette filière n'est pas (ou plus) dans le catalogue. Consultez l'ensemble des programmes.</p>
        <a class="btn btn-primary btn-lg" href="<?= url('formations') ?>">Voir les formations</a>
      </div>
    </section>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$niveaux = niveaux_catalogue();
$niveau_key = (string) $formation['niveau'];
$niveau = $niveaux[$niveau_key] ?? null;
$niveau_titre = $niveau['titre'] ?? $niveau_key;
$domaine_label = ($formation['domaine'] ?? '') === 'industriel' ? 'Industriel' : 'Tertiaire';

$page_title = $formation['titre'] . ' — ' . $niveau_titre . ' | IAT Niger';
$page_desc = ($formation['resume'] ?? $formation['objectif'] ?? '') . ' Formation ' . $niveau_titre . ' à l\'IAT Niger, Niamey.';
$page_slug = 'formation/' . $formation['slug'];
$active = 'formations';
$hero_titre = $formation['titre'];
$hero_texte = $formation['resume'] ?? '';
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => url()],
    ['label' => 'Formations', 'url' => url('formations')],
    ['label' => $niveau_titre, 'url' => url('formations/' . $niveau_key)],
    ['label' => $formation['titre'], 'url' => url($page_slug)],
];

/* Formations voisines du même niveau */
$voisines = array_values(array_filter(
    formations_par_niveau($niveau_key),
    static fn ($f) => ($f['slug'] ?? '') !== $formation['slug']
));
$voisines = array_slice($voisines, 0, 3);

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-hero.php';
?>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Course",
  "name": <?= json_encode($formation['titre'], JSON_UNESCAPED_UNICODE) ?>,
  "description": <?= json_encode($formation['objectif'] ?? $formation['resume'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
  "provider": {
    "@type": "CollegeOrUniversity",
    "name": <?= json_encode(SITE_FULL_NAME, JSON_UNESCAPED_UNICODE) ?>,
    "url": <?= json_encode(SITE_URL, JSON_UNESCAPED_UNICODE) ?>
  },
  "url": <?= json_encode(url($page_slug), JSON_UNESCAPED_UNICODE) ?>
}
</script>

<section class="section">
  <div class="container">
    <div class="grid-2" style="align-items: start; gap: clamp(2rem, 4vw, 3.5rem);">
      <div class="reveal">
        <div class="badges" style="margin-bottom: 1.2rem;">
          <span class="badge badge-primary"><?= e($niveau_titre) ?></span>
          <span class="badge badge-primary"><?= e($domaine_label) ?></span>
          <?php if (!empty($formation['badge'])) : ?>
          <span class="badge badge-accent"><?= e($formation['badge']) ?></span>
          <?php endif; ?>
        </div>

        <div class="card" style="margin-bottom: 1.5rem;">
          <span class="card-icon"><?= icon($formation['icone'] ?? 'book-open', 24) ?></span>
          <h2 class="h3" style="margin-bottom: 0.8rem;">Objectif de la formation</h2>
          <p><?= e($formation['objectif'] ?? $formation['resume'] ?? '') ?></p>
        </div>

        <?php if (!empty($formation['debouches']) && is_array($formation['debouches'])) : ?>
        <div class="card reveal">
          <span class="card-icon"><?= icon('briefcase', 24) ?></span>
          <h2 class="h3" style="margin-bottom: 0.9rem;">Débouchés</h2>
          <ul class="pill-list">
            <?php foreach ($formation['debouches'] as $d) : ?>
            <li><?= e($d) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
      </div>

      <aside class="reveal reveal-delay-1">
        <?php if ($niveau) : ?>
        <div class="card" style="margin-bottom: 1.2rem;">
          <h2 class="h3" style="margin-bottom: 1rem;">Infos pratiques</h2>
          <ul class="check-list">
            <li><?= icon('user-plus', 18) ?><span><strong>Recrutement</strong><br><?= e($niveau['recrutement']) ?></span></li>
            <li><?= icon('clock', 18) ?><span><strong>Durée</strong><br><?= e($niveau['duree']) ?></span></li>
            <li><?= icon('file-text', 18) ?><span><strong>Dossier</strong><br><?= e($niveau['dossier']) ?></span></li>
          </ul>
        </div>
        <?php endif; ?>

        <div class="card" style="background: linear-gradient(135deg, var(--primary-deep), var(--primary)); color: #fff; border: 0;">
          <h2 class="h3" style="color: #fff; margin-bottom: 0.7rem;">Intéressé·e par cette filière ?</h2>
          <p style="color: rgb(255 255 255 / 0.85); margin-bottom: 1.3rem;">Préinscription gratuite et sans engagement — notre scolarité vous rappelle.</p>
          <a class="btn btn-accent btn-lg" href="<?= url('admission#preinscription') ?>">Je me préinscris <?= icon('arrow-right', 18) ?></a>
          <p style="margin-top: 1rem;">
            <a href="<?= url('formations/' . $niveau_key) ?>" style="color: #fff; text-decoration: underline; font-size: 0.92rem;">← Retour au <?= e($niveau_titre) ?></a>
          </p>
        </div>
      </aside>
    </div>
  </div>
</section>

<?php if ($voisines) : ?>
<section class="section section-alt">
  <div class="container">
    <div class="section-head reveal">
      <span class="kicker"><?= icon('graduation-cap', 15) ?> Dans le même niveau</span>
      <h2>Autres filières <?= e(mb_strtolower($niveau_titre)) ?></h2>
    </div>
    <div class="grid-3">
      <?php foreach ($voisines as $i => $v) : ?>
      <a class="card formation-card reveal <?= $i === 1 ? 'reveal-delay-1' : ($i === 2 ? 'reveal-delay-2' : '') ?>" href="<?= url('formation/' . $v['slug']) ?>">
        <span class="card-icon"><?= icon($v['icone'] ?? 'book-open', 24) ?></span>
        <?php if (!empty($v['badge'])) : ?>
        <div class="badges"><span class="badge badge-accent"><?= e($v['badge']) ?></span></div>
        <?php endif; ?>
        <h3><?= e($v['titre']) ?></h3>
        <p><?= e($v['resume'] ?? '') ?></p>
        <span class="card-link">Voir la filière <?= icon('arrow-right', 16) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
