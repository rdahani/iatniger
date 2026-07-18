<?php
/**
 * Installation / réinitialisation du contenu CMS depuis la ligne de commande.
 *
 * Usage :
 *   C:\xampp\php\php.exe database\seed-cms.php
 *   C:\xampp\php\php.exe database\seed-cms.php --force
 *
 * --force réinitialise le contenu de démarrage (paramètres, pages, formations,
 * niveaux et blocs de sections) sans toucher au contenu ajouté manuellement
 * (nouvelles actualités, FAQ ou éléments créés depuis l'admin).
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Ce script s'exécute uniquement en ligne de commande.\n");
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/cms-seed.php';

$force = in_array('--force', $argv, true);

$pdo = db();
if ($pdo === null) {
    fwrite(STDERR, "Erreur : impossible de se connecter à la base de données.\n");
    fwrite(STDERR, "Vérifiez que MySQL est démarré (XAMPP) et que la base « " . DB_NAME . " » existe (importez database/iatniger.sql).\n");
    exit(1);
}

echo "=== Installation du CMS IAT Niger ===\n";
echo $force ? "Mode : réinitialisation forcée (le contenu de démarrage sera restauré)\n\n" : "Mode : installation standard (n'ajoute que ce qui manque)\n\n";

try {
    $messages = cms_install_and_seed($pdo, $force);
    foreach ($messages as $m) {
        echo '- ' . $m . "\n";
    }
    echo "\nTerminé avec succès.\n";
    exit(0);
} catch (PDOException $e) {
    fwrite(STDERR, "Erreur pendant l'installation : " . $e->getMessage() . "\n");
    exit(1);
}
