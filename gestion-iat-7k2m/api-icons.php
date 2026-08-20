<?php
/** API JSON : catalogue d'icônes pour le sélecteur. */

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

$q = mb_strtolower(trim((string) ($_GET['q'] ?? '')));
$icons = [];
foreach (icons_catalogue() as $name) {
    if ($q !== '' && !str_contains($name, $q)) {
        continue;
    }
    $icons[] = [
        'name' => $name,
        'svg' => icon($name, 24),
    ];
}

echo json_encode(['ok' => true, 'count' => count($icons), 'icons' => $icons], JSON_UNESCAPED_UNICODE);
