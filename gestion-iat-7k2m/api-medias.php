<?php
/** API JSON : liste des fichiers de la médiathèque pour le sélecteur. */

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

$accept = $_GET['accept'] ?? 'image'; // image | all
$q = mb_strtolower(trim((string) ($_GET['q'] ?? '')));

$images_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$all_ext = array_merge($images_ext, ['pdf', 'doc', 'docx']);
$allowed = $accept === 'all' ? $all_ext : $images_ext;

function api_medias_lister(string $abs, string $prefix, int $maxDepth = 4): array
{
    $out = [];
    if (!is_dir($abs)) {
        return $out;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($abs, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    $it->setMaxDepth($maxDepth);
    foreach ($it as $f) {
        if (!$f->isFile()) {
            continue;
        }
        $rel = $prefix . '/' . ltrim(str_replace('\\', '/', substr($f->getPathname(), strlen($abs))), '/');
        $out[] = [
            'path' => $rel,
            'name' => $f->getFilename(),
            'ext' => strtolower(pathinfo($f->getFilename(), PATHINFO_EXTENSION)),
            'size' => $f->getSize(),
            'mtime' => $f->getMTime(),
        ];
    }
    return $out;
}

$root = dirname(__DIR__) . '/assets';
$files = array_merge(
    api_medias_lister($root . '/img', 'img'),
    api_medias_lister($root . '/docs', 'docs'),
    api_medias_lister($root . '/uploads', 'uploads')
);

$files = array_values(array_filter($files, static function (array $f) use ($allowed, $q): bool {
    if (!in_array($f['ext'], $allowed, true)) {
        return false;
    }
    if ($q !== '' && !str_contains(mb_strtolower($f['path']), $q)) {
        return false;
    }
    return true;
}));

usort($files, static fn ($a, $b) => $b['mtime'] <=> $a['mtime']);

echo json_encode([
    'ok' => true,
    'count' => count($files),
    'files' => array_slice($files, 0, 400),
], JSON_UNESCAPED_UNICODE);
