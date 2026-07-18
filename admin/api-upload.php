<?php
/** API : téléversement AJAX pour le sélecteur de médias. */

declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'] ?? null)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Session invalide.']);
    exit;
}

$base = ($_POST['base'] ?? 'img') === 'assets' ? 'assets' : 'img';
$chemin = admin_upload('fichier', 'uploads');
if ($chemin === null) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Téléversement impossible (format ou taille).']);
    exit;
}

/* Valeur à stocker dans le champ selon la base attendue. */
$value = $chemin; // uploads/xxx.jpg (relatif à assets/)
if ($base === 'img') {
    /* Pour les champs image du site (assets/img/...), on garde uploads/… car asset('img/') ne marcherait pas.
       On stocke plutôt le chemin utilisable via asset() depuis img OR on copie sous img/uploads.
       Convention site : actualites utilisent chemins sous img/. On place donc dans img/uploads. */
    $src = dirname(__DIR__) . '/assets/' . $chemin;
    $destDir = dirname(__DIR__) . '/assets/img/uploads';
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    $basename = basename($chemin);
    $dest = $destDir . '/' . $basename;
    if (@rename($src, $dest) || @copy($src, $dest)) {
        if (is_file($src) && realpath($src) !== realpath($dest)) {
            @unlink($src);
        }
        $value = 'uploads/' . $basename; // relatif à assets/img/
        $url = asset('img/' . $value);
    } else {
        $value = $chemin;
        $url = asset($chemin);
    }
} else {
    $url = asset($chemin);
}

echo json_encode([
    'ok' => true,
    'path' => $value,
    'url' => $url,
    'assets_path' => $chemin,
], JSON_UNESCAPED_UNICODE);
