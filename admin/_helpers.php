<?php
/**
 * Helpers partagés pour les écrans admin CMS.
 */

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

/** Affiche alertes notice/erreur. */
function admin_flash(string $notice, string $erreur): void
{
    if ($notice !== '') {
        echo '<div class="alert alert-success">' . icon('check-circle', 18) . '<div>' . e($notice) . '</div></div>';
    }
    if ($erreur !== '') {
        echo '<div class="alert alert-danger">' . icon('x', 18) . '<div>' . e($erreur) . '</div></div>';
    }
}

/** Vérifie que le CMS est installé. */
function admin_require_cms(): ?PDO
{
    $pdo = db();
    if ($pdo === null) {
        return null;
    }
    if (!cms_ready()) {
        return null;
    }
    return $pdo;
}

/** Génère un slug. */
function admin_slugify(string $titre): string
{
    $s = @iconv('UTF-8', 'ASCII//TRANSLIT', mb_strtolower($titre));
    if ($s === false) {
        $s = mb_strtolower($titre);
    }
    $s = preg_replace('/[^a-z0-9]+/', '-', (string) $s);
    return trim((string) $s, '-') ?: 'item';
}

/**
 * Compresse et redimensionne une image pour le web.
 * - Largeur/hauteur max : 1920 px
 * - JPEG / PNG opaque → JPEG qualité 82
 * - PNG transparent / WebP → WebP qualité 80
 * Retourne le chemin absolu final (peut changer d'extension), ou null si échec.
 */
function admin_optimize_image(string $pathAbsolu): ?string
{
    if (!is_file($pathAbsolu) || !extension_loaded('gd')) {
        return null;
    }

    $info = @getimagesize($pathAbsolu);
    if ($info === false) {
        return null;
    }

    $mime = $info['mime'] ?? '';
    $src = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($pathAbsolu),
        'image/png' => @imagecreatefrompng($pathAbsolu),
        'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($pathAbsolu) : false,
        default => false,
    };
    if ($src === false) {
        return null;
    }

    $w = imagesx($src);
    $h = imagesy($src);
    if ($w < 1 || $h < 1) {
        imagedestroy($src);
        return null;
    }

    $max = 1920;
    $nw = $w;
    $nh = $h;
    if ($w > $max || $h > $max) {
        if ($w >= $h) {
            $nw = $max;
            $nh = (int) round($h * ($max / $w));
        } else {
            $nh = $max;
            $nw = (int) round($w * ($max / $h));
        }
    }

    $dst = imagecreatetruecolor($nw, $nh);
    if ($dst === false) {
        imagedestroy($src);
        return null;
    }

    $hasAlpha = false;
    if ($mime === 'image/png' || $mime === 'image/webp') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $nw, $nh, $transparent);
        imagealphablending($dst, true);

        /* Détecte une transparence réelle (échantillonnage rapide). */
        if ($mime === 'image/png') {
            for ($y = 0; $y < $h && !$hasAlpha; $y += max(1, (int) ($h / 40))) {
                for ($x = 0; $x < $w && !$hasAlpha; $x += max(1, (int) ($w / 40))) {
                    $rgba = imagecolorat($src, $x, $y);
                    if ((($rgba & 0x7F000000) >> 24) > 0) {
                        $hasAlpha = true;
                    }
                }
            }
        } else {
            $hasAlpha = true; /* WebP : on conserve le format pour éviter les surprises. */
        }
    } else {
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefilledrectangle($dst, 0, 0, $nw, $nh, $white);
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    imagedestroy($src);

    $dir = dirname($pathAbsolu);
    $base = pathinfo($pathAbsolu, PATHINFO_FILENAME);
    $useWebp = $hasAlpha && function_exists('imagewebp');
    $outExt = $useWebp ? 'webp' : 'jpg';
    $outPath = $dir . '/' . $base . '.' . $outExt;

    $ok = false;
    if ($useWebp) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $ok = imagewebp($dst, $outPath, 80);
    } else {
        if ($hasAlpha) {
            /* Fallback PNG si WebP indisponible. */
            $outExt = 'png';
            $outPath = $dir . '/' . $base . '.png';
            imagesavealpha($dst, true);
            $ok = imagepng($dst, $outPath, 6);
        } else {
            /* Fond blanc pour les PNG opaques convertis en JPEG. */
            if ($mime === 'image/png' || $mime === 'image/webp') {
                $flat = imagecreatetruecolor($nw, $nh);
                $white = imagecolorallocate($flat, 255, 255, 255);
                imagefilledrectangle($flat, 0, 0, $nw, $nh, $white);
                imagecopy($flat, $dst, 0, 0, 0, 0, $nw, $nh);
                imagedestroy($dst);
                $dst = $flat;
            }
            $ok = imagejpeg($dst, $outPath, 82);
        }
    }
    imagedestroy($dst);

    if (!$ok || !is_file($outPath)) {
        return null;
    }

    /* Supprime l'original si l'extension a changé. */
    if (realpath($pathAbsolu) !== realpath($outPath)) {
        @unlink($pathAbsolu);
    }

    return $outPath;
}

/** Upload fichier vers assets/uploads/ (images/docs). Retourne chemin relatif ou null. */
function admin_upload(string $field, string $sous_dossier = 'uploads'): ?string
{
    if (empty($_FILES[$field]['tmp_name']) || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
        return null;
    }
    $err = (int) ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($err !== UPLOAD_ERR_OK) {
        return null;
    }
    $name = (string) $_FILES[$field]['name'];
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx'];
    if (!in_array($ext, $allowed, true)) {
        return null;
    }
    $dir = dirname(__DIR__) . '/assets/' . trim($sous_dossier, '/');
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $safe = preg_replace('/[^a-zA-Z0-9._-]+/', '-', pathinfo($name, PATHINFO_FILENAME));
    $filename = $safe . '-' . time() . '.' . $ext;
    $dest = $dir . '/' . $filename;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) {
        return null;
    }

    /* Images : compression + redimensionnement automatiques (sauf GIF animés). */
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        $optimized = admin_optimize_image($dest);
        if ($optimized !== null) {
            return trim($sous_dossier, '/') . '/' . basename($optimized);
        }
    }

    return trim($sous_dossier, '/') . '/' . $filename;
}

/**
 * Champ média avec aperçu + bouton parcourir (bibliothèque) + upload.
 * $name : nom de l'input (ex. image)
 * $value : chemin relatif (souvent sous assets/img/ ou assets/)
 * $opts : base (img|assets), accept, label, id
 */
function admin_media_field(string $name, string $value = '', array $opts = []): void
{
    $id = $opts['id'] ?? ('mf-' . preg_replace('/[^a-z0-9_-]/i', '-', $name));
    $label = $opts['label'] ?? 'Image / fichier';
    $base = $opts['base'] ?? 'img'; // img = chemins relatifs à assets/img ; assets = relatifs à assets/
    $accept = $opts['accept'] ?? 'image';
    $full = !empty($opts['full']);
    $required = !empty($opts['required']);

    $preview = '';
    if ($value !== '') {
        if ($base === 'img' && !str_starts_with($value, 'http') && !str_starts_with($value, 'img/')) {
            $preview = asset('img/' . ltrim($value, '/'));
        } elseif (str_starts_with($value, 'http')) {
            $preview = $value;
        } else {
            $preview = asset(ltrim($value, '/'));
        }
    }
    $is_image = $preview !== '' && preg_match('/\.(jpe?g|png|gif|webp)(\?|$)/i', $preview);
    ?>
<div class="form-field <?= $full ? 'full' : '' ?> admin-picker-field" data-picker="media" data-base="<?= e($base) ?>" data-accept="<?= e($accept) ?>">
  <label for="<?= e($id) ?>"><?= e($label) ?><?= $required ? ' *' : '' ?></label>
  <div class="admin-media-row">
    <div class="admin-media-preview" data-preview aria-hidden="<?= $is_image ? 'false' : 'true' ?>">
      <?php if ($is_image) : ?>
      <img src="<?= e($preview) ?>" alt="" width="96" height="72">
      <?php else : ?>
      <span class="admin-media-placeholder"><?= icon('image', 28) ?></span>
      <?php endif; ?>
    </div>
    <div class="admin-media-controls">
      <input type="text" id="<?= e($id) ?>" name="<?= e($name) ?>" value="<?= e($value) ?>"
             data-media-input placeholder="<?= $base === 'img' ? 'ex. actualites/photo.jpg' : 'ex. docs/fichier.pdf' ?>"
             <?= $required ? 'required' : '' ?>>
      <div class="admin-media-actions">
        <button type="button" class="btn btn-outline" data-media-browse><?= icon('folder', 16) ?> Parcourir</button>
        <label class="btn btn-outline admin-upload-btn">
          <?= icon('download', 16) ?> Téléverser
          <input type="file" data-media-upload accept="<?= $accept === 'image' ? '.jpg,.jpeg,.png,.gif,.webp' : '.jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx' ?>" hidden>
        </label>
        <button type="button" class="btn btn-outline" data-media-clear title="Effacer"><?= icon('x', 16) ?></button>
      </div>
    </div>
  </div>
</div>
    <?php
}

/**
 * Champ icône avec aperçu + bibliothèque.
 */
function admin_icon_field(string $name, string $value = '', array $opts = []): void
{
    $id = $opts['id'] ?? ('if-' . preg_replace('/[^a-z0-9_-]/i', '-', $name));
    $label = $opts['label'] ?? 'Icône';
    $full = !empty($opts['full']);
    $value = $value !== '' ? $value : 'book-open';
    ?>
<div class="form-field <?= $full ? 'full' : '' ?> admin-picker-field" data-picker="icon">
  <label for="<?= e($id) ?>"><?= e($label) ?></label>
  <div class="admin-icon-row">
    <div class="admin-icon-preview" data-icon-preview><?= icon($value, 28) ?></div>
    <div class="admin-icon-controls">
      <input type="text" id="<?= e($id) ?>" name="<?= e($name) ?>" value="<?= e($value) ?>" data-icon-input placeholder="book-open">
      <button type="button" class="btn btn-outline" data-icon-browse><?= icon('library', 16) ?> Bibliothèque</button>
    </div>
  </div>
</div>
    <?php
}

/** Bouton « Voir la page » : ouvre la page publique correspondante dans un onglet. */
function admin_voir_page(string $chemin, string $label = 'Voir la page'): string
{
    return '<a class="btn btn-outline" href="' . e(url($chemin)) . '" target="_blank" rel="noopener">' . icon('eye', 16) . ' ' . e($label) . '</a>';
}

/** En-tête de liste CRUD standard. */
function admin_list_header(string $titre, string $new_url, string $new_label = 'Ajouter'): void
{
    echo '<div class="admin-header"><h1 class="h2">' . e($titre) . '</h1>';
    echo '<a class="btn btn-primary" href="' . e($new_url) . '">' . icon('plus', 16) . ' ' . e($new_label) . '</a></div>';
}
