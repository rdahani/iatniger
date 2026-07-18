<?php
/**
 * Import one-shot des photos WhatsApp récentes → assets/img/recentes/
 * + insertion galerie CMS + mise à jour du diaporama hero.
 *
 * Usage : php database/import-photos-recentes.php
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';

$srcDir = 'C:\\Users\\LENOVO\\.cursor\\projects\\c-xampp-htdocs-iatniger\\assets';
$destDir = dirname(__DIR__) . '/assets/img/recentes';

if (!is_dir($srcDir)) {
    fwrite(STDERR, "Dossier source introuvable: $srcDir\n");
    exit(1);
}
if (!is_dir($destDir) && !mkdir($destDir, 0755, true)) {
    fwrite(STDERR, "Impossible de créer $destDir\n");
    exit(1);
}

$files = glob($srcDir . DIRECTORY_SEPARATOR . '*WhatsApp_Image_*.png');
if (!$files) {
    $files = glob($srcDir . DIRECTORY_SEPARATOR . '*.png') ?: [];
}
natsort($files);
$files = array_values($files);

if (!$files) {
    fwrite(STDERR, "Aucune image PNG trouvée.\n");
    exit(1);
}

/** Légendes / catégories pour les photos les plus parlantes (index 0-based). */
$meta = [
    0 => ['Direction et administration de l\'IAT', 'campus'],
    10 => ['Travaux pratiques — automate PLC en laboratoire', 'campus'],
    11 => ['Distinction officielle — trophée Alkalami / excellence', 'evenements'],
    12 => ['Allocution lors d\'une cérémonie institutionnelle', 'evenements'],
    13 => ['Étudiante en génie civil — formation pratique', 'vie-etudiante'],
    14 => ['Remise de distinctions — partenaires et lauréats', 'evenements'],
    15 => ['Projet architectural — étudiants en génie civil', 'vie-etudiante'],
    16 => ['Journée culturelle — patrimoine nigérien', 'vie-etudiante'],
    17 => ['Topographie et levé — travaux pratiques', 'campus'],
    18 => ['Démonstration de levé topographique', 'campus'],
    19 => ['Plantation d\'arbre — visite CAEPE', 'evenements'],
    20 => ['Portrait culturel — journée du patrimoine', 'vie-etudiante'],
    21 => ['Tenue traditionnelle — événements campus', 'vie-etudiante'],
    22 => ['Délégation et cadre de l\'institut', 'evenements'],
    23 => ['Portrait étudiante — communauté IAT', 'vie-etudiante'],
    24 => ['Cérémonie de plantation — engagement citoyen', 'evenements'],
    25 => ['Étudiantes en journée culturelle', 'vie-etudiante'],
];

$defaults = [
    'campus' => 'Campus et laboratoires de l\'IAT Niger',
    'evenements' => 'Événement et vie institutionnelle — IAT Niger',
    'vie-etudiante' => 'Vie étudiante à l\'IAT Niger',
];

$imported = [];
$i = 0;
foreach ($files as $src) {
    $i++;
    $name = sprintf('photo-%02d.jpg', $i);
    $dest = $destDir . DIRECTORY_SEPARATOR . $name;
    $rel = 'recentes/' . $name;

    $img = @imagecreatefrompng($src);
    if ($img === false) {
        // Fallback : copie brute renommée en .png si GD échoue
        $namePng = sprintf('photo-%02d.png', $i);
        $destPng = $destDir . DIRECTORY_SEPARATOR . $namePng;
        if (!copy($src, $destPng)) {
            echo "Échec copie: $src\n";
            continue;
        }
        $rel = 'recentes/' . $namePng;
    } else {
        $w = imagesx($img);
        $h = imagesy($img);
        // Limiter le grand côté à 1600 px
        $max = 1600;
        if ($w > $max || $h > $max) {
            $ratio = min($max / $w, $max / $h);
            $nw = (int) round($w * $ratio);
            $nh = (int) round($h * $ratio);
            $resized = imagecreatetruecolor($nw, $nh);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($img);
            $img = $resized;
        }
        imagejpeg($img, $dest, 82);
        imagedestroy($img);
    }

    $idx = $i - 1;
    $cat = $meta[$idx][1] ?? (($i % 3 === 0) ? 'evenements' : (($i % 3 === 1) ? 'campus' : 'vie-etudiante'));
    $legende = $meta[$idx][0] ?? ($defaults[$cat] . ' — photo ' . $i);

    $imported[] = [
        'src' => $rel,
        'legende' => $legende,
        'cat' => $cat,
        'ordre' => $i,
    ];
    echo "OK $rel\n";
}

echo "\n" . count($imported) . " fichier(s) importé(s).\n";

$pdo = db();
if ($pdo === null) {
    echo "BDD indisponible — fichiers OK, insertion CMS reportée.\n";
    exit(0);
}

/* ---------- Galerie : ajouter les nouvelles (sans doublon image) ---------- */
$exist = $pdo->query("SELECT image FROM cms_items WHERE type = 'galerie'")->fetchAll(PDO::FETCH_COLUMN);
$existMap = array_flip($exist);

$st = $pdo->prepare(
    'INSERT INTO cms_items (type, cle, groupe, titre, sous_titre, contenu, extra, image, url, ordre, publie)
     VALUES (\'galerie\', NULL, ?, ?, NULL, NULL, NULL, ?, NULL, ?, 1)'
);

$added = 0;
foreach ($imported as $p) {
    if (isset($existMap[$p['src']])) {
        continue;
    }
    $st->execute([$p['cat'], $p['legende'], $p['src'], $p['ordre']]);
    $added++;
}
echo "$added photo(s) ajoutée(s) à la galerie CMS.\n";

/* ---------- Hero : diaporama avec les plus fortes images paysage / impact ---------- */
$heroPicks = [
    ['src' => 'recentes/photo-15.jpg', 'alt' => 'Étudiants en génie civil — plans et équipements'],
    ['src' => 'recentes/photo-10.jpg', 'alt' => 'Laboratoire automate PLC — formation pratique'],
    ['src' => 'recentes/photo-14.jpg', 'alt' => 'Cérémonie de distinctions et partenaires'],
    ['src' => 'recentes/photo-13.jpg', 'alt' => 'Étudiante en formation technique — génie civil'],
    ['src' => 'recentes/photo-25.jpg', 'alt' => 'Journée culturelle des étudiants de l\'IAT'],
    ['src' => 'campus/immeuble-iat.jpg', 'alt' => "Le campus de l'Institut Africain de Technologie à Niamey"],
];

// Vérifier que les fichiers hero existent (sinon fallback)
$heroPicks = array_values(array_filter($heroPicks, static function (array $h): bool {
    return is_file(dirname(__DIR__) . '/assets/img/' . $h['src']);
}));

$pdo->exec("DELETE FROM cms_items WHERE type = 'hero_slide' AND groupe = 'accueil'");
$stH = $pdo->prepare(
    'INSERT INTO cms_items (type, cle, groupe, titre, sous_titre, contenu, extra, image, url, ordre, publie)
     VALUES (\'hero_slide\', NULL, \'accueil\', ?, NULL, NULL, NULL, ?, NULL, ?, 1)'
);
foreach ($heroPicks as $k => $h) {
    $stH->execute([$h['alt'], $h['src'], $k + 1]);
}
echo count($heroPicks) . " slide(s) hero mises à jour.\n";
echo "Terminé.\n";
