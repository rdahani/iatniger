<?php
/**
 * API actualités — lecture seule, publique, consommée par l'application mobile IAT Niger.
 *
 * GET api/actualites.php              liste des articles publiés (les plus récents d'abord)
 *     ?page=1&par_page=10             pagination (par_page : 1 à 30)
 * GET api/actualites.php?slug=...     un article complet
 *
 * Ne renvoie que ce que le site affiche déjà publiquement (articles publiés, datés
 * d'aujourd'hui au plus tard). Aucune clé : aucune donnée privée n'est exposée.
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
// Contenu public et peu changeant : cache court côté client et CDN.
header('Cache-Control: public, max-age=300');

require_once dirname(__DIR__) . '/config/config.php';

function actu_json(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    actu_json(405, ['ok' => false, 'error' => 'Méthode non autorisée.']);
}

$pdo = db();
if ($pdo === null) {
    actu_json(503, ['ok' => false, 'error' => 'Actualités momentanément indisponibles.']);
}

/** Article tel que l'application l'attend. L'image est une URL absolue. */
function actu_format(array $a, bool $complet): array
{
    $image = trim((string) ($a['image'] ?? ''));
    // Seuls des chemins simples sous assets/img/ sont acceptés (pas de « .. », pas d'URL externe).
    if ($image !== '' && !preg_match('#^[A-Za-z0-9_\-./]+$#', $image) || str_contains($image, '..')) {
        $image = '';
    }
    $out = [
        'id'        => (int) $a['id'],
        'slug'      => (string) $a['slug'],
        'titre'     => trim((string) $a['titre']),
        'categorie' => trim((string) $a['categorie']),
        'extrait'   => trim((string) $a['extrait']),
        'image'     => $image !== '' ? rtrim(SITE_URL, '/') . '/assets/img/' . ltrim($image, '/') : null,
        'date'      => (string) $a['date_publication'],
        'url'       => url('actualites/' . $a['slug']),
    ];
    if ($complet) {
        // Le contenu est du texte ; toute balise éventuelle est retirée par sécurité.
        $out['contenu'] = trim(html_entity_decode(strip_tags((string) $a['contenu']), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
    return $out;
}

$visible = 'publie = 1 AND date_publication <= CURDATE()';

try {
    $slug = (string) ($_GET['slug'] ?? '');
    if ($slug !== '') {
        if (!preg_match('/^[a-z0-9-]{1,180}$/', $slug)) {
            actu_json(404, ['ok' => false, 'error' => 'Article introuvable.']);
        }
        $st = $pdo->prepare("SELECT id, slug, titre, categorie, extrait, contenu, image, date_publication
                             FROM actualites WHERE slug = ? AND $visible LIMIT 1");
        $st->execute([$slug]);
        $a = $st->fetch(PDO::FETCH_ASSOC);
        if (!$a) {
            actu_json(404, ['ok' => false, 'error' => 'Article introuvable.']);
        }
        actu_json(200, ['ok' => true, 'data' => actu_format($a, true)]);
    }

    $parPage = max(1, min(30, (int) ($_GET['par_page'] ?? 10)));
    $page    = max(1, min(1000, (int) ($_GET['page'] ?? 1)));
    $total   = (int) $pdo->query("SELECT COUNT(*) FROM actualites WHERE $visible")->fetchColumn();

    $st = $pdo->prepare("SELECT id, slug, titre, categorie, extrait, image, date_publication
                         FROM actualites WHERE $visible
                         ORDER BY date_publication DESC, id DESC
                         LIMIT :lim OFFSET :off");
    $st->bindValue(':lim', $parPage, PDO::PARAM_INT);
    $st->bindValue(':off', ($page - 1) * $parPage, PDO::PARAM_INT);
    $st->execute();

    actu_json(200, ['ok' => true, 'data' => [
        'page'       => $page,
        'par_page'   => $parPage,
        'total'      => $total,
        'actualites' => array_map(static fn(array $a): array => actu_format($a, false), $st->fetchAll(PDO::FETCH_ASSOC)),
    ]]);
} catch (Throwable $e) {
    error_log('api/actualites: ' . $e->getMessage());
    actu_json(500, ['ok' => false, 'error' => 'Actualités momentanément indisponibles.']);
}
