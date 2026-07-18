<?php
/** Page 404 élégante avec suggestions. */

require_once __DIR__ . '/config/config.php';
http_response_code(404);

$page_title = 'Page introuvable (404) | IAT Niger';
$page_desc = "La page demandée n'existe pas ou a été déplacée.";
$page_slug = '404';

require __DIR__ . '/includes/header.php';
?>

<section class="section" style="min-height: 55vh; display: flex; align-items: center;">
  <div class="container text-center">
    <p class="display" style="background: linear-gradient(100deg, var(--primary), var(--accent)); -webkit-background-clip: text; background-clip: text; color: transparent; font-family: var(--font-display); font-weight: 800;">404</p>
    <h1 class="h2" style="margin: 1rem 0;">Cette page a pris un congé académique</h1>
    <p class="lead" style="max-width: 520px; margin: 0 auto 2rem;">La page que vous cherchez n'existe pas ou a été déplacée lors de la refonte du site.</p>
    <div class="hero-actions" style="justify-content: center;">
      <a class="btn btn-primary btn-lg" href="<?= url() ?>">Retour à l'accueil</a>
      <a class="btn btn-outline btn-lg" href="<?= url('recherche') ?>"><?= icon('search', 18) ?> Rechercher</a>
      <a class="btn btn-outline btn-lg" href="<?= url('formations') ?>">Voir les formations</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
