<?php
/**
 * Corrige les « PNG » qui sont en fait des JPEG, met à jour galerie + hero.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';

$dir = dirname(__DIR__) . '/assets/img/recentes';
$files = glob($dir . '/photo-*.png') ?: [];
natsort($files);

$renamed = 0;
foreach ($files as $png) {
    $jpg = preg_replace('/\.png$/i', '.jpg', $png);
    $data = file_get_contents($png);
    if ($data === false) {
        continue;
    }
    // Déjà JPEG : renommer / réécrire proprement
    $img = @imagecreatefromstring($data);
    if ($img !== false) {
        $w = imagesx($img);
        $h = imagesy($img);
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
        imagejpeg($img, $jpg, 82);
        imagedestroy($img);
        unlink($png);
    } else {
        rename($png, $jpg);
    }
    $renamed++;
    echo "→ " . basename($jpg) . "\n";
}
echo "$renamed fichier(s) corrigé(s).\n";

$pdo = db();
if ($pdo === null) {
    exit(0);
}

$pdo->exec("UPDATE cms_items SET image = REPLACE(image, '.png', '.jpg') WHERE type IN ('galerie','hero_slide') AND image LIKE 'recentes/%.png'");

/* Hero avec vraies images JPG */
$heroPicks = [
    ['src' => 'recentes/photo-15.jpg', 'alt' => 'Étudiants en génie civil — plans et équipements'],
    ['src' => 'recentes/photo-10.jpg', 'alt' => 'Laboratoire automate PLC — formation pratique'],
    ['src' => 'recentes/photo-14.jpg', 'alt' => 'Cérémonie de distinctions et partenaires'],
    ['src' => 'recentes/photo-13.jpg', 'alt' => 'Étudiante en formation technique — génie civil'],
    ['src' => 'recentes/photo-25.jpg', 'alt' => 'Journée culturelle des étudiants de l\'IAT'],
    ['src' => 'campus/immeuble-iat.jpg', 'alt' => "Le campus de l'Institut Africain de Technologie à Niamey"],
];
$heroPicks = array_values(array_filter($heroPicks, static fn ($h) => is_file(dirname(__DIR__) . '/assets/img/' . $h['src'])));

$pdo->exec("DELETE FROM cms_items WHERE type = 'hero_slide' AND groupe = 'accueil'");
$stH = $pdo->prepare(
    'INSERT INTO cms_items (type, cle, groupe, titre, sous_titre, contenu, extra, image, url, ordre, publie)
     VALUES (\'hero_slide\', NULL, \'accueil\', ?, NULL, NULL, NULL, ?, NULL, ?, 1)'
);
foreach ($heroPicks as $k => $h) {
    $stH->execute([$h['alt'], $h['src'], $k + 1]);
}
echo count($heroPicks) . " slides hero OK.\n";

/* Améliorer quelques légendes */
$captions = [
    'recentes/photo-01.jpg' => ['Direction et administration de l\'IAT', 'campus'],
    'recentes/photo-10.jpg' => ['Travaux pratiques — automate PLC en laboratoire', 'campus'],
    'recentes/photo-11.jpg' => ['Distinction officielle — trophée d\'excellence', 'evenements'],
    'recentes/photo-12.jpg' => ['Allocution lors d\'une cérémonie institutionnelle', 'evenements'],
    'recentes/photo-13.jpg' => ['Étudiante en génie civil — formation pratique', 'vie-etudiante'],
    'recentes/photo-14.jpg' => ['Remise de distinctions — partenaires et lauréats', 'evenements'],
    'recentes/photo-15.jpg' => ['Projet architectural — étudiants en génie civil', 'vie-etudiante'],
    'recentes/photo-16.jpg' => ['Journée culturelle — patrimoine nigérien', 'vie-etudiante'],
    'recentes/photo-17.jpg' => ['Topographie et levé — travaux pratiques', 'campus'],
    'recentes/photo-18.jpg' => ['Démonstration de levé topographique', 'campus'],
    'recentes/photo-19.jpg' => ['Plantation d\'arbre — visite CAEPE', 'evenements'],
    'recentes/photo-20.jpg' => ['Portrait culturel — journée du patrimoine', 'vie-etudiante'],
    'recentes/photo-21.jpg' => ['Tenue traditionnelle — événements campus', 'vie-etudiante'],
    'recentes/photo-22.jpg' => ['Cadres et délégation de l\'institut', 'evenements'],
    'recentes/photo-23.jpg' => ['Portrait étudiante — communauté IAT', 'vie-etudiante'],
    'recentes/photo-24.jpg' => ['Cérémonie de plantation — engagement citoyen', 'evenements'],
    'recentes/photo-25.jpg' => ['Étudiantes en journée culturelle', 'vie-etudiante'],
];
$stU = $pdo->prepare('UPDATE cms_items SET titre = ?, groupe = ? WHERE type = \'galerie\' AND image = ?');
foreach ($captions as $img => [$titre, $groupe]) {
    $stU->execute([$titre, $groupe, $img]);
}
echo "Légendes mises à jour.\n";
