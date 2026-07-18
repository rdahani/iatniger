<?php
/** Médiathèque : parcourt assets/img, assets/docs et assets/uploads, et permet de téléverser de nouveaux fichiers. */

declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';
require_permission('medias');

$notice = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $chemin = admin_upload('fichier', 'uploads');
    if ($chemin !== null) {
        $notice = 'Fichier téléversé : ' . $chemin;
    } else {
        $erreur = "Le fichier n'a pas pu être téléversé (formats acceptés : jpg, jpeg, png, gif, webp, pdf, doc, docx).";
    }
}

/** Liste récursivement (profondeur limitée) les fichiers d'un dossier, chemins relatifs à assets/. */
function medias_lister(string $dossier_absolu, string $prefixe_relatif, int $profondeur_max = 3): array
{
    $fichiers = [];
    if (!is_dir($dossier_absolu)) {
        return $fichiers;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dossier_absolu, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    $it->setMaxDepth($profondeur_max);
    foreach ($it as $fichier) {
        if ($fichier->isDir()) {
            continue;
        }
        $relatif = $prefixe_relatif . '/' . ltrim(str_replace('\\', '/', substr($fichier->getPathname(), strlen($dossier_absolu))), '/');
        $fichiers[] = [
            'chemin' => $relatif,
            'nom' => $fichier->getFilename(),
            'taille' => $fichier->getSize(),
            'ext' => strtolower(pathinfo($fichier->getFilename(), PATHINFO_EXTENSION)),
        ];
    }
    usort($fichiers, static fn ($a, $b) => strcmp($a['chemin'], $b['chemin']));
    return $fichiers;
}

/** Formate une taille de fichier lisible. */
function medias_taille(int $octets): string
{
    if ($octets >= 1048576) {
        return round($octets / 1048576, 1) . ' Mo';
    }
    return round($octets / 1024) . ' Ko';
}

$assets_dir = dirname(__DIR__) . '/assets';
$dossiers = [
    'Images (assets/img)' => medias_lister($assets_dir . '/img', 'img'),
    'Documents (assets/docs)' => medias_lister($assets_dir . '/docs', 'docs'),
    'Fichiers téléversés (assets/uploads)' => medias_lister($assets_dir . '/uploads', 'uploads'),
];
$images_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

admin_head('Médiathèque');
?>
<div class="admin-layout">
  <?php admin_sidebar('medias'); ?>
  <main class="admin-main">
    <div class="admin-header"><h1 class="h2">Médiathèque</h1></div>

    <?php admin_flash($notice, $erreur); ?>

    <div class="admin-card" style="margin-bottom: 1.6rem;">
      <h2 class="h3" style="margin-bottom: 1rem;">Téléverser un fichier</h2>
      <form method="post" action="<?= url('admin/medias.php') ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
          <input type="file" name="fichier" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx" required>
          <button class="btn btn-primary" type="submit"><?= icon('plus', 16) ?> Téléverser</button>
        </div>
        <p class="caption" style="margin-top: 0.8rem;">Le fichier sera enregistré dans <code>assets/uploads/</code>. Copiez ensuite le chemin affiché dans le champ « image » ou « URL / fichier » de vos contenus.</p>
      </form>
    </div>

    <?php foreach ($dossiers as $titre => $fichiers) : ?>
    <div class="admin-card" style="margin-bottom: 1.6rem;">
      <h2 class="h3" style="margin-bottom: 1rem;"><?= e($titre) ?> — <?= count($fichiers) ?> fichier<?= count($fichiers) > 1 ? 's' : '' ?></h2>
      <?php if (!$fichiers) : ?>
      <p class="caption">Aucun fichier.</p>
      <?php else : ?>
      <div class="grid-4">
        <?php foreach ($fichiers as $f) : ?>
        <div class="card" style="padding: 1rem;">
          <?php if (in_array($f['ext'], $images_ext, true)) : ?>
          <img src="<?= asset($f['chemin']) ?>" alt="<?= e($f['nom']) ?>" loading="lazy" style="width: 100%; height: 90px; object-fit: cover; border-radius: var(--radius-md); margin-bottom: 0.6rem;">
          <?php else : ?>
          <div style="display: flex; align-items: center; justify-content: center; height: 90px; margin-bottom: 0.6rem; color: var(--text-2);"><?= icon('file-text', 36) ?></div>
          <?php endif; ?>
          <p style="font-size: 0.85rem; font-weight: 600; word-break: break-all; margin-bottom: 0.3rem;"><?= e($f['nom']) ?></p>
          <p class="caption" style="margin-bottom: 0.6rem;"><?= medias_taille((int) $f['taille']) ?></p>
          <input type="text" readonly value="<?= e($f['chemin']) ?>" onclick="this.select();" style="font-size: 0.75rem; padding: 0.4rem 0.5rem; width: 100%; background: var(--bg-alt, #f3f4f6); border: 1px solid var(--border, #e5e7eb); border-radius: var(--radius-sm, 6px);">
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </main>
</div>
</body>
</html>
