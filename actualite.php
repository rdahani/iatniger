<?php
/** Détail d'une actualité : /actualites/{slug} */

require_once __DIR__ . '/config/config.php';

$slug = $_GET['slug'] ?? '';
$actu = $slug !== '' ? actualite_par_slug($slug) : null;

if ($actu === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

$page_title = $actu['titre'] . ' | IAT Niger';
$page_desc = mb_strimwidth($actu['extrait'], 0, 158, '…');
$page_slug = 'actualites/' . $actu['slug'];
$active = 'actualites';
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => url()],
    ['label' => 'Actualités', 'url' => url('actualites')],
    ['label' => mb_strimwidth($actu['titre'], 0, 48, '…'), 'url' => url($page_slug)],
];
$hero_titre = $actu['titre'];

$suggestions = array_values(array_filter(actualites(4), fn ($a) => $a['slug'] !== $actu['slug']));
$suggestions = array_slice($suggestions, 0, 3);

require __DIR__ . '/includes/header.php';
?>
<!-- JSON-LD : Article -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "NewsArticle",
  "headline": "<?= e($actu['titre']) ?>",
  "datePublished": "<?= e($actu['date_publication']) ?>",
  "image": "<?= e(asset('img/' . $actu['image'])) ?>",
  "publisher": { "@type": "Organization", "name": "<?= e(SITE_FULL_NAME) ?>", "logo": { "@type": "ImageObject", "url": "<?= e(asset('img/logoiat.png')) ?>" } },
  "mainEntityOfPage": "<?= e(url($page_slug)) ?>"
}
</script>

<?php require __DIR__ . '/includes/page-hero.php'; ?>

<article class="section">
  <div class="container">
    <div class="article-body">
      <div class="news-meta" style="margin-bottom: 1.6rem; display: flex; gap: 1rem; align-items: center;">
        <span class="badge badge-primary"><?= e($actu['categorie']) ?></span>
        <span class="caption"><?= icon('calendar', 14) ?> Publié le <?= e(date_fr($actu['date_publication'])) ?></span>
      </div>
      <img class="article-hero-img" src="<?= asset('img/' . $actu['image']) ?>" alt="<?= e($actu['titre']) ?>" width="960" height="540">
      <?php foreach (preg_split('/\n\s*\n/', (string) $actu['contenu']) as $para) : ?>
      <p><?= nl2br(e(trim($para))) ?></p>
      <?php endforeach; ?>

      <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 2.5rem;">
        <a class="btn btn-outline" href="<?= url('actualites') ?>"><?= icon('arrow-right', 16, 'flip-x') ?> Toutes les actualités</a>
        <a class="btn btn-primary" href="<?= url('admission#preinscription') ?>">S'inscrire à l'IAT <?= icon('arrow-right', 16) ?></a>
      </div>
    </div>
  </div>
</article>

<?php if ($suggestions) : ?>
<section class="section section-alt">
  <div class="container">
    <div class="section-head reveal"><h2 class="h3">À lire aussi</h2></div>
    <div class="grid-3">
      <?php foreach ($suggestions as $s) : ?>
      <article class="card news-card reveal">
        <div class="news-img"><img src="<?= asset('img/' . $s['image']) ?>" alt="<?= e($s['titre']) ?>" loading="lazy" width="640" height="360"></div>
        <div class="news-body">
          <div class="news-meta"><span class="badge badge-primary"><?= e($s['categorie']) ?></span></div>
          <h3><a href="<?= url('actualites/' . $s['slug']) ?>"><?= e($s['titre']) ?></a></h3>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<style>.flip-x{transform:scaleX(-1);}</style>

<?php require __DIR__ . '/includes/footer.php'; ?>
