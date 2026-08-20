<?php
/** Édition du mega-menu Formations (bandeau promo) de la navigation du site. */

declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';
require_permission('parametres');

$pdo = admin_require_cms();
$notice = '';

$textes_config = [
    'nav_mega_promo_badge' => ['label' => 'Badge du bandeau (ex. Inscriptions ouvertes)', 'media' => false],
    'nav_mega_promo_titre' => ['label' => 'Titre du bandeau', 'media' => false],
    'nav_mega_promo_texte' => ['label' => 'Texte sous le titre', 'media' => false],
    'nav_mega_promo_image' => ['label' => 'Image du bandeau', 'media' => true],
];

$textes_defauts = [
    'nav_mega_promo_badge' => 'Inscriptions ouvertes',
    'nav_mega_promo_titre' => 'Rejoignez la promotion 2026-2027',
    'nav_mega_promo_texte' => 'Préinscription gratuite en ligne',
    'nav_mega_promo_image' => 'etudiants/etudiant-laptop.jpg',
];

if ($pdo !== null && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $st = $pdo->prepare('INSERT INTO cms_items (type, cle, contenu) VALUES (?,?,?)
        ON DUPLICATE KEY UPDATE contenu = VALUES(contenu)');
    $n = 0;
    foreach (array_keys($textes_config) as $cle) {
        $st->execute(['texte', $cle, trim((string) ($_POST['textes'][$cle] ?? ''))]);
        $n++;
    }
    $notice = 'Mega-menu mis à jour (' . $n . ' champs).';
}

$valeurs = [];
if ($pdo !== null) {
    $st = $pdo->prepare('SELECT contenu FROM cms_items WHERE type = ? AND cle = ?');
    foreach ($textes_config as $cle => $cfg) {
        $st->execute(['texte', $cle]);
        $row = $st->fetch();
        $valeurs[$cle] = ($row && $row['contenu'] !== null && $row['contenu'] !== '') ? (string) $row['contenu'] : $textes_defauts[$cle];
    }
}

admin_head('Navigation & menu');
?>
<div class="admin-layout">
  <?php admin_sidebar('navigation'); ?>
  <main class="admin-main">
    <div class="admin-header">
      <h1 class="h2">Navigation &amp; menu Formations</h1>
      <a class="btn btn-outline" href="<?= url('formations') ?>" target="_blank" rel="noopener"><?= icon('eye', 16) ?> Voir la page</a>
    </div>

    <?php admin_flash($notice, ''); ?>

    <?php if ($pdo === null) : ?>
      <div class="alert alert-danger"><?= icon('alert-triangle', 18) ?><div>Le CMS n'est pas encore installé. <a href="<?= url('admin/install-cms.php') ?>">Installer le CMS</a>.</div></div>
    <?php else : ?>

    <div class="alert alert-success" style="margin-bottom: 1.6rem;">
      <?= icon('check-circle', 18) ?>
      <div>Les <strong>4 cartes du menu</strong> (Niveau Moyen, Licences, Masters, Doctorat) reprennent automatiquement le <strong>titre</strong> et le <strong>sous-titre</strong> des niveaux : modifiez-les dans <a href="<?= url('admin/formations.php') ?>">Formations → Niveaux</a>.</div>
    </div>

    <div class="admin-card">
      <h2 class="h3" style="margin-bottom: 1rem;">Bandeau promotionnel (colonne droite du menu)</h2>
      <form method="post" action="<?= url('admin/navigation.php') ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div class="form-grid">
          <?php foreach ($textes_config as $cle => $cfg) :
              if ($cfg['media']) {
                  admin_media_field('textes[' . $cle . ']', $valeurs[$cle], [
                      'id' => 'nav-' . $cle,
                      'label' => $cfg['label'],
                      'base' => 'img',
                      'accept' => 'image',
                      'full' => true,
                  ]);
                  continue;
              }
              ?>
          <div class="form-field full">
            <label for="nav-<?= e($cle) ?>"><?= e($cfg['label']) ?></label>
            <input type="text" id="nav-<?= e($cle) ?>" name="textes[<?= e($cle) ?>]" value="<?= e($valeurs[$cle]) ?>">
          </div>
          <?php endforeach; ?>
        </div>
        <button class="btn btn-primary btn-lg" type="submit" style="margin-top: 1.2rem;">Enregistrer</button>
      </form>
      <p class="caption" style="margin-top: 1rem;">Le bandeau pointe toujours vers la préinscription (Admission). Le reste de la navigation (Accueil, L'Institut, Vie étudiante…) est fixe pour garantir la cohérence du site.</p>
    </div>

    <?php endif; ?>
  </main>
</div>
</body>
</html>
