<?php
/** Pages & SEO : édition des méta-données et du bandeau d'en-tête (cms_pages). */

declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';
require_permission('pages');

$pdo = admin_require_cms();
$notice = '';
$erreur = '';
$action = $_GET['action'] ?? 'liste';

/** Libellés lisibles pour chaque page publique. */
$labels = [
    'accueil' => 'Accueil',
    'a-propos' => 'À propos',
    'partenaires' => 'Partenaires',
    'galerie' => 'Galerie',
    'telechargements' => 'Téléchargements',
    'formations' => 'Formations',
    'admission' => 'Admission',
    'vie-etudiante' => 'Vie étudiante',
    'csp-algoza' => 'CSP Algoza',
    'actualites' => 'Actualités',
    'web-tv' => 'WEB TV',
    'faq' => 'FAQ',
    'contact' => 'Contact',
];

if ($pdo !== null && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $op = $_POST['op'] ?? '';
    if ($op === 'enregistrer') {
        $slug = trim($_POST['slug'] ?? '');
        $titre_seo = trim($_POST['titre_seo'] ?? '');
        $meta_desc = trim($_POST['meta_desc'] ?? '');
        $hero_titre = trim($_POST['hero_titre'] ?? '');
        $hero_texte = trim($_POST['hero_texte'] ?? '');
        if ($slug === '') {
            $erreur = 'Slug de page manquant.';
        } else {
            try {
                $st = $pdo->prepare('INSERT INTO cms_pages (slug, titre_seo, meta_desc, hero_titre, hero_texte) VALUES (?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE titre_seo = VALUES(titre_seo), meta_desc = VALUES(meta_desc), hero_titre = VALUES(hero_titre), hero_texte = VALUES(hero_texte)');
                $st->execute([$slug, $titre_seo, $meta_desc, $hero_titre, $hero_texte]);
                $notice = 'Page « ' . ($labels[$slug] ?? $slug) . ' » mise à jour.';
                $action = 'liste';
            } catch (PDOException $e) {
                $erreur = "Erreur d'enregistrement : " . $e->getMessage();
            }
        }
    }
}

$edition = null;
if ($action === 'editer' && $pdo !== null) {
    $slug = (string) ($_GET['slug'] ?? '');
    $st = $pdo->prepare('SELECT * FROM cms_pages WHERE slug = ?');
    $st->execute([$slug]);
    $edition = $st->fetch() ?: ['slug' => $slug, 'titre_seo' => '', 'meta_desc' => '', 'hero_titre' => '', 'hero_texte' => ''];
}

$liste = [];
if ($pdo !== null && $action === 'liste') {
    $liste = $pdo->query('SELECT * FROM cms_pages')->fetchAll();
    usort($liste, static function ($a, $b) use ($labels) {
        return ($labels[$a['slug']] ?? $a['slug']) <=> ($labels[$b['slug']] ?? $b['slug']);
    });
}

admin_head('Pages & SEO');
?>
<div class="admin-layout">
  <?php admin_sidebar('pages'); ?>
  <main class="admin-main">

    <?php admin_flash($notice, $erreur); ?>

    <?php if ($pdo === null) : ?>
      <div class="admin-header"><h1 class="h2">Pages &amp; SEO</h1></div>
      <div class="alert alert-danger"><?= icon('alert-triangle', 18) ?><div>Le CMS n'est pas encore installé. <a href="<?= url('admin/install-cms.php') ?>">Installer le CMS</a> pour gérer les pages.</div></div>

    <?php elseif ($action === 'editer') : ?>
      <div class="admin-header">
        <h1 class="h2">Modifier : <?= e($labels[$edition['slug']] ?? $edition['slug']) ?></h1>
        <a class="btn btn-outline" href="<?= url('admin/pages.php') ?>">← Retour à la liste</a>
      </div>
      <div class="admin-card">
        <form method="post" action="<?= url('admin/pages.php') ?>">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="op" value="enregistrer">
          <input type="hidden" name="slug" value="<?= e($edition['slug']) ?>">
          <div class="form-grid">
            <div class="form-field full">
              <label for="pg-titre-seo">Titre SEO (balise &lt;title&gt;)</label>
              <input type="text" id="pg-titre-seo" name="titre_seo" value="<?= e($edition['titre_seo'] ?? '') ?>">
            </div>
            <div class="form-field full">
              <label for="pg-meta-desc">Meta description</label>
              <textarea id="pg-meta-desc" name="meta_desc" style="min-height: 80px;"><?= e($edition['meta_desc'] ?? '') ?></textarea>
            </div>
            <div class="form-field full">
              <label for="pg-hero-titre">Titre du bandeau (h1)</label>
              <input type="text" id="pg-hero-titre" name="hero_titre" value="<?= e($edition['hero_titre'] ?? '') ?>">
            </div>
            <div class="form-field full">
              <label for="pg-hero-texte">Texte du bandeau</label>
              <textarea id="pg-hero-texte" name="hero_texte" style="min-height: 90px;"><?= e($edition['hero_texte'] ?? '') ?></textarea>
            </div>
          </div>
          <button class="btn btn-primary btn-lg" type="submit" style="margin-top: 1.4rem;">Enregistrer</button>
        </form>
      </div>

    <?php else : ?>
      <div class="admin-header"><h1 class="h2">Pages &amp; SEO</h1></div>
      <p class="caption" style="margin-bottom: 1.4rem;">Modifiez le titre SEO, la meta description et le bandeau d'en-tête de chaque page publique.</p>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th scope="col">Page</th><th scope="col">Titre SEO</th><th scope="col">Titre du bandeau</th><th scope="col">Actions</th></tr></thead>
          <tbody>
            <?php if (!$liste) : ?>
            <tr><td colspan="4">Aucune page enregistrée. <a href="<?= url('admin/install-cms.php') ?>">Lancez l'installation du CMS</a>.</td></tr>
            <?php endif; ?>
            <?php foreach ($liste as $p) : ?>
            <tr>
              <td><strong><?= e($labels[$p['slug']] ?? $p['slug']) ?></strong><br><span class="caption"><?= e($p['slug']) ?></span></td>
              <td><?= e(mb_strimwidth((string) $p['titre_seo'], 0, 60, '…')) ?></td>
              <td><?= e(mb_strimwidth((string) $p['hero_titre'], 0, 50, '…')) ?></td>
              <td><a class="icon-btn" href="<?= url('admin/pages.php?action=editer&slug=' . rawurlencode($p['slug'])) ?>" aria-label="Modifier" title="Modifier"><?= icon('edit', 17) ?></a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>
</div>
</body>
</html>
