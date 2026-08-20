<?php
/** Installation / réinitialisation du contenu CMS (tables + contenu de démarrage). */

declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';
require_permission('parametres');

require_once __DIR__ . '/../includes/cms-seed.php';

$pdo = db();
$notice = '';
$erreur = '';
$resultats = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    if ($pdo === null) {
        $erreur = "Base de données indisponible. Démarrez MySQL dans XAMPP et importez d'abord database/iatniger.sql.";
    } else {
        $force = isset($_POST['force']);
        try {
            $resultats = cms_install_and_seed($pdo, $force);
            $notice = $force ? 'Réinitialisation du contenu CMS effectuée avec succès.' : 'Installation du CMS effectuée avec succès.';
        } catch (PDOException $e) {
            $erreur = "Erreur lors de l'installation : " . $e->getMessage();
        }
    }
}

$deja_installe = cms_ready();

admin_head('Installation CMS');
?>
<div class="admin-layout">
  <?php admin_sidebar('dashboard'); ?>
  <main class="admin-main">
    <div class="admin-header">
      <h1 class="h2">Installation du CMS</h1>
    </div>

    <?php admin_flash($notice, $erreur); ?>

    <?php if ($pdo === null) : ?>
      <div class="alert alert-danger"><?= icon('x', 18) ?><div>Base de données indisponible. Vérifiez que MySQL est démarré et que la base « iatniger » est importée.</div></div>
    <?php endif; ?>

    <?php if ($resultats) : ?>
    <div class="admin-card" style="margin-bottom: 1.6rem;">
      <h2 class="h3" style="margin-bottom: 1rem;">Résultat de l'opération</h2>
      <ul class="check-list">
        <?php foreach ($resultats as $r) : ?>
        <li><?= icon('check-circle', 18) ?><span><?= e($r) ?></span></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <div class="admin-card">
      <h2 class="h3" style="margin-bottom: 0.8rem;">
        <?= $deja_installe ? 'Le CMS est installé' : "Le CMS n'est pas encore installé" ?>
      </h2>
      <?php if ($deja_installe) : ?>
      <div class="alert alert-success" style="margin-bottom: 1.4rem;"><?= icon('check-circle', 18) ?><div>Les tables CMS existent et sont prêtes à l'emploi. Vous pouvez relancer l'installation à tout moment pour ajouter du contenu manquant, ou forcer une réinitialisation complète.</div></div>
      <?php else : ?>
      <div class="alert alert-danger" style="margin-bottom: 1.4rem;"><?= icon('alert-triangle', 18) ?><div>Les tables <code>site_settings</code>, <code>cms_pages</code>, <code>cms_items</code>, <code>cms_niveaux</code> et <code>cms_formations</code> n'existent pas encore. Cliquez sur le bouton ci-dessous pour les créer et les remplir avec le contenu actuel du site.</div></div>
      <?php endif; ?>

      <p style="color: var(--text-2); margin-bottom: 1.4rem;">
        Cette opération crée (si nécessaire) les tables du CMS et importe le contenu de démarrage à partir des pages publiques actuelles :
        réglages du site, pages &amp; SEO, FAQ, partenaires, galerie, vidéos, documents, témoignages, formations, niveaux et sections de pages
        (accueil, à propos, vie étudiante, CSP Algoza, admission…).
      </p>

      <form method="post" action="<?= url('admin/install-cms.php') ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <label style="display: flex; align-items: flex-start; gap: 0.6rem; font-weight: 500; margin-bottom: 1.4rem; max-width: 640px;">
          <input type="checkbox" name="force" value="1" style="width: auto; margin-top: 0.2rem;">
          <span>Forcer la réinitialisation — écrase les paramètres, pages, formations et sections déjà seedées pour restaurer le contenu de démarrage. Le contenu ajouté manuellement (nouvelles FAQ, actualités…) n'est pas supprimé.</span>
        </label>
        <button class="btn btn-primary btn-lg" type="submit">
          <?= icon('settings', 18) ?> <?= $deja_installe ? 'Installer / réinitialiser le contenu CMS' : 'Installer le contenu CMS' ?>
        </button>
      </form>
    </div>

    <div class="admin-card" style="margin-top: 1.6rem;">
      <h2 class="h3" style="margin-bottom: 0.8rem;">Alternative en ligne de commande</h2>
      <p style="color: var(--text-2);">Vous pouvez aussi exécuter l'installation depuis un terminal, sans passer par le navigateur :</p>
      <pre style="background: var(--bg-alt, #f3f4f6); padding: 1rem; border-radius: var(--radius-md); overflow-x: auto; margin-top: 0.8rem;"><code>C:\xampp\php\php.exe database\seed-cms.php</code></pre>
      <p class="caption">Ajoutez <code>--force</code> pour réinitialiser le contenu de démarrage : <code>php database\seed-cms.php --force</code></p>
    </div>
  </main>
</div>
</body>
</html>
