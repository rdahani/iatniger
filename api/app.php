<?php
/**
 * API « contenu de l'application mobile » — lecture seule, publique.
 *
 * GET api/app.php
 *
 * Renvoie ce que l'école gère déjà dans l'administration du site : coordonnées
 * (Paramètres), chiffres clés (Accueil), niveaux et filières (Formations).
 * L'application mobile IAT Niger le relit à chaque ouverture : modifier ces
 * informations dans l'administration suffit, sans republier l'application.
 *
 * Aucune clé : tout ce qui est renvoyé est déjà affiché publiquement sur le site.
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=300');

require_once dirname(__DIR__) . '/config/config.php';

function app_json(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    app_json(405, ['ok' => false, 'error' => 'Méthode non autorisée.']);
}

$pdo = db();
if ($pdo === null) {
    app_json(503, ['ok' => false, 'error' => 'Contenu momentanément indisponible.']);
}

try {
    // ── Paramètres du site (identité, contacts) ──
    $s = [];
    foreach ($pdo->query('SELECT cle, valeur FROM site_settings') as $row) {
        $s[(string) $row['cle']] = trim((string) $row['valeur']);
    }
    $telephones = array_values(array_filter([$s['site_phone_1'] ?? '', $s['site_phone_2'] ?? '']));

    // ── Chiffres clés de la page d'accueil ──
    $chiffres = [];
    $st = $pdo->query("SELECT titre, extra FROM cms_items
                       WHERE type = 'stat' AND groupe = 'accueil' AND publie = 1 ORDER BY ordre, id");
    foreach ($st as $row) {
        $x = json_decode((string) $row['extra'], true) ?: [];
        $valeur = (int) ($x['valeur'] ?? 0);
        if ($valeur <= 0) { continue; }
        $chiffres[] = [
            'valeur'  => number_format($valeur, 0, ',', ' ') . (string) ($x['suffixe'] ?? ''),
            'libelle' => trim((string) $row['titre']),
        ];
    }

    // ── Niveaux et filières publiés ──
    $filieres = [];
    $st = $pdo->query("SELECT niveau, domaine, titre, resume, debouches, badge FROM cms_formations
                       WHERE publie = 1 ORDER BY ordre, id");
    foreach ($st as $f) {
        $deb = json_decode((string) $f['debouches'], true);
        $filieres[(string) $f['niveau']][] = [
            'nom'       => trim((string) $f['titre']),
            'domaine'   => (string) $f['domaine'] === 'industriel' ? 'industriel' : 'tertiaire',
            'resume'    => trim((string) $f['resume']),
            'debouches' => is_array($deb) ? array_values(array_map('strval', $deb)) : [],
            'cames'     => stripos((string) $f['badge'], 'CAMES') !== false,
        ];
    }
    $niveaux = [];
    $st = $pdo->query('SELECT slug, titre, sous_titre, recrutement, duree, dossier, description
                       FROM cms_niveaux WHERE publie = 1 ORDER BY ordre, slug');
    foreach ($st as $n) {
        $niveaux[] = [
            'id'          => (string) $n['slug'],
            'titre'       => trim((string) $n['titre']),
            'sous_titre'  => trim((string) $n['sous_titre']),
            'resume'      => trim((string) $n['description']),
            'recrutement' => trim((string) $n['recrutement']),
            'duree'       => trim((string) $n['duree']),
            'dossier'     => trim((string) $n['dossier']),
            'filieres'    => $filieres[(string) $n['slug']] ?? [],
        ];
    }

    $data = [
        'identite' => [
            'nom'    => $s['site_full_name'] ?? '',
            'sigle'  => $s['site_name'] ?? '',
            'slogan' => $s['site_tagline'] ?? '',
        ],
        'contacts' => [
            'adresse'    => $s['site_address'] ?? '',
            'telephones' => $telephones,
            'email'      => $s['site_email'] ?? '',
            'whatsapp'   => preg_replace('/\D+/', '', $s['site_whatsapp'] ?? ''),
            'facebook'   => $s['site_facebook'] ?? '',
        ],
        'chiffres' => $chiffres,
        'niveaux'  => $niveaux,
    ];
    app_json(200, ['ok' => true, 'data' => $data]);
} catch (Throwable $e) {
    error_log('api/app: ' . $e->getMessage());
    app_json(500, ['ok' => false, 'error' => 'Contenu momentanément indisponible.']);
}
