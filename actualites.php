<?php
/** Liste des actualités avec pagination. */

require_once __DIR__ . '/config/config.php';

$page_title = 'Actualités — La vie de l\'institut | IAT Niger';
$page_desc = "Toute l'actualité de l'IAT Niger : distinctions, nouveaux laboratoires, partenariats, vie étudiante et engagement citoyen.";
$page_slug = 'actualites';
$active = 'actualites';
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => url()],
    ['label' => 'Actualités', 'url' => url('actualites')],
];
$hero_titre = 'Actualités';
$hero_texte = "Distinctions, infrastructures, partenariats, vie étudiante : suivez la vie de l'institut.";

$toutes = actualites();
$par_page = 6;
$total_pages = max(1, (int) ceil(count($toutes) / $par_page));
$page_courante = max(1, min($total_pages, (int) ($_GET['page'] ?? 1)));
$items = array_slice($toutes, ($page_courante - 1) * $par_page, $par_page);

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-hero.php';
?>

<section class="section">
  <div class="container">
    <div class="grid-3">
      <?php foreach ($items as $i => $actu) : ?>
      <article class="card news-card reveal <?= $i % 3 === 1 ? 'reveal-delay-1' : ($i % 3 === 2 ? 'reveal-delay-2' : '') ?>">
        <div class="news-img">
          <img src="<?= asset('img/' . $actu['image']) ?>" alt="<?= e($actu['titre']) ?>" loading="lazy" width="640" height="360">
        </div>
        <div class="news-body">
          <div class="news-meta">
            <span class="badge badge-primary"><?= e($actu['categorie']) ?></span>
            <span><?= icon('calendar', 14) ?> <?= e(date_fr($actu['date_publication'])) ?></span>
          </div>
          <h3><a href="<?= url('actualites/' . $actu['slug']) ?>"><?= e($actu['titre']) ?></a></h3>
          <p><?= e(mb_strimwidth($actu['extrait'], 0, 150, '…')) ?></p>
          <a class="card-link" href="<?= url('actualites/' . $actu['slug']) ?>">Lire l'article <?= icon('arrow-right', 16) ?></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <?php if ($total_pages > 1) : ?>
    <nav class="pagination" aria-label="Pagination des actualités">
      <?php for ($p = 1; $p <= $total_pages; $p++) : ?>
        <?php if ($p === $page_courante) : ?>
          <span class="current" aria-current="page"><?= $p ?></span>
        <?php else : ?>
          <a href="<?= url('actualites?page=' . $p) ?>"><?= $p ?></a>
        <?php endif; ?>
      <?php endfor; ?>
    </nav>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
