<?php
/**
 * IAT Niger — Configuration globale
 * Environnement cible : XAMPP (Apache + PHP + MySQL)
 */

declare(strict_types=1);

session_start();

/* ---------- Site ---------- */
define('SITE_NAME', 'IAT Niger');
define('SITE_FULL_NAME', 'Institut Africain de Technologie');
define('SITE_TAGLINE', 'Un pôle d\'excellence');

/*
 * Surcharge locale / production (gitignorée) :
 * créer config/config.local.php pour DB_*, SITE_BASE, etc.
 */
$__local = __DIR__ . '/config.local.php';
if (is_file($__local)) {
    require $__local;
}

/*
 * Chemin de base de l'app :
 * - XAMPP local : /iatniger
 * - Production à la racine du domaine/sous-domaine : '' (vide)
 */
if (!defined('SITE_BASE')) {
    $__script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $__base = '/iatniger';
    if ($__script !== '' && !str_contains($__script, '/iatniger')) {
        $__base = '';
    }
    define('SITE_BASE', $__base);
}
$__scheme = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
$__host = $_SERVER['HTTP_HOST'] ?? 'localhost';
if (!defined('SITE_URL')) {
    define('SITE_URL', $__scheme . '://' . $__host . SITE_BASE);
}
define('SITE_EMAIL', 'info@iatniger.org');
define('SITE_PHONE_1', '(+227) 20 75 29 40');
define('SITE_PHONE_2', '(+227) 96 97 07 92');
/** Numéro WhatsApp au format international sans + ni espaces (ex. 22791787675). */
define('SITE_WHATSAPP', '22791787675');
define('SITE_ADDRESS', 'BP 412, Rond-Point Gadafawa, Yantala – Commune 1, Niamey, Niger');
define('SITE_FACEBOOK', 'https://www.facebook.com/IATNIGERGROUPE');

/* ---------- Base de données ---------- */
if (!defined('DB_HOST')) {
    define('DB_HOST', '127.0.0.1');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'iatniger');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}

/*
 * URL de l'application SoftIAT (gestion scolaire).
 * Surcharge dans config.local.php / config.production.php.
 */
if (!defined('SOFTIAT_BASE_URL')) {
    define('SOFTIAT_BASE_URL', 'http://localhost/softiat');
}

/** Lien vers la fiche préinscription web dans SoftIAT. */
function softiat_preinscription_url(int $id): string
{
    if ($id <= 0 || !defined('SOFTIAT_BASE_URL') || SOFTIAT_BASE_URL === '') {
        return '';
    }
    return rtrim(SOFTIAT_BASE_URL, '/') . '/modules/preinscriptions_web/view.php?id=' . $id;
}

/**
 * Connexion PDO partagée.
 * Retourne null si la base est indisponible : les pages publiques
 * restent fonctionnelles grâce aux contenus de secours.
 */
function db(): ?PDO
{
    static $pdo = null;
    static $failed = false;

    if ($pdo !== null || $failed) {
        return $pdo;
    }
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $e) {
        $failed = true;
        $pdo = null;
    }
    return $pdo;
}

/* ---------- Aides globales ---------- */

/** Échappement HTML systématique. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/*
 * Chemin réel du dossier d'administration (non devinable).
 * Le code continue d'écrire url('admin/...') : la traduction se fait ici.
 * Surcharge possible dans config.local.php.
 */
if (!defined('ADMIN_SLUG')) {
    define('ADMIN_SLUG', 'gestion-iat-7k2m');
}

/** Préfixe chemin site (/ ou /iatniger). */
function path_base(): string
{
    $base = rtrim(SITE_BASE, '/');
    return $base !== '' ? $base : '';
}

/** URL absolue interne (canonical, Open Graph, redirections). */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    if ($path === 'admin' || str_starts_with($path, 'admin/')) {
        $path = ADMIN_SLUG . substr($path, 5);
    }
    return rtrim(SITE_URL, '/') . ($path !== '' ? '/' . $path : '/');
}

/**
 * URL d'un asset — chemin relatif à la racine du site (même domaine que la page).
 * Évite les assets cross-domain quand config.local.php est présent en local.
 */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $u = path_base() . '/assets/' . $path;
    if (preg_match('/\.(css|js)$/', $path)) {
        $abs = dirname(__DIR__) . '/assets/' . $path;
        if (is_file($abs)) {
            $u .= '?v=' . filemtime($abs);
        }
    }
    return $u;
}

/** Date française : 03 juillet 2026. */
function date_fr(string $date): string
{
    $months = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
        'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $ts = strtotime($date);
    if ($ts === false) {
        return $date;
    }
    return sprintf('%02d %s %d', (int) date('j', $ts), $months[(int) date('n', $ts)], (int) date('Y', $ts));
}

/** Jeton CSRF pour les formulaires. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(?string $token): bool
{
    return is_string($token) && hash_equals($_SESSION['csrf'] ?? '', $token);
}

require_once __DIR__ . '/../constants/formations.php';
require_once __DIR__ . '/../constants/actualites.php';
require_once __DIR__ . '/../includes/cms.php';
require_once __DIR__ . '/../includes/cms-page-textes.php';
