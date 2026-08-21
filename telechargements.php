<?php
/** Téléchargements : documents officiels (dépliant, modalités de paiement, brochure, logos). */

require_once __DIR__ . '/config/config.php';

$page_title = 'Téléchargements — Documents officiels | IAT Niger';
$page_desc = "Téléchargez les documents officiels de l'IAT Niger : dépliant 2026-2027 avec modalités de paiement, brochure des formations, logos officiels.";
$page_slug = 'telechargements';
$active = 'institut';
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => url()],
    ['label' => 'Téléchargements', 'url' => url('telechargements')],
];
$hero_titre = 'Documents à télécharger';
$hero_texte = "Dépliant officiel, modalités de paiement, brochure des formations et logos : tous les documents de l'institut en un clic.";
cms_apply_page('telechargements', $page_title, $page_desc, $hero_titre, $hero_texte);

/** Formate une taille de fichier lisible. */
function taille_fichier(string $chemin): string
{
    $abs = __DIR__ . '/assets/' . $chemin;
    if (!is_file($abs)) {
        return '';
    }
    $o = filesize($abs);
    return $o >= 1048576 ? round($o / 1048576, 1) . ' Mo' : round($o / 1024) . ' Ko';
}

$documents_defaut = [
    [
        'fichier' => 'docs/depliant-iat-2026-2027.pdf',
        'nom_dl' => 'Depliant-IAT-Niger-2026-2027.pdf',
        'titre' => 'Dépliant officiel 2026-2027',
        'type' => 'PDF',
        'icone' => 'file-text',
        'desc' => "Le document de référence : toutes les filières, les conditions d'accès et les modalités de paiement pour l'année académique 2026-2027.",
        'badge' => 'Modalités de paiement incluses',
    ],
    [
        'fichier' => 'img/brochure-2025-2026.jpg',
        'nom_dl' => 'Brochure-IAT-Niger-2025-2026.jpg',
        'titre' => 'Brochure 2025-2026',
        'type' => 'Image',
        'icone' => 'book-open',
        'desc' => "La brochure complète des formations de l'institut : Niveau Moyen, Licences, Masters et contacts utiles.",
    ],
    [
        'fichier' => 'img/depliant-iat.jpg',
        'nom_dl' => 'Depliant-IAT-Niger.jpg',
        'titre' => 'Dépliant (version image)',
        'type' => 'Image',
        'icone' => 'image',
        'desc' => "Le dépliant de présentation du Groupe IAT au format image, facile à partager sur WhatsApp et les réseaux sociaux.",
    ],
    [
        'fichier' => 'docs/logo-iat-hd.png',
        'nom_dl' => 'Logo-IAT-Niger-HD.png',
        'titre' => 'Logo officiel (haute définition)',
        'type' => 'PNG',
        'icone' => 'award',
        'desc' => "Le logo officiel de l'Institut Africain de Technologie en haute résolution, pour vos documents et supports partenaires.",
    ],
    [
        'fichier' => 'img/logoiat.png',
        'nom_dl' => 'Logo-IAT-Niger-horizontal.png',
        'titre' => 'Logo horizontal avec slogan',
        'type' => 'PNG',
        'icone' => 'award',
        'desc' => "La version horizontale du logo avec le slogan « Un pôle d'excellence », idéale pour les en-têtes de documents.",
    ],
    [
        'fichier' => 'img/banner-iat.jpg',
        'nom_dl' => 'Banniere-IAT-Niger.jpg',
        'titre' => 'Bannière officielle',
        'type' => 'Image',
        'icone' => 'image',
        'desc' => "La bannière du Groupe IAT : 19 diplômes Licences et Masters accrédités par le CAMES / ANAQ-SUP.",
    ],
];
$documents = cms_documents() ?: $documents_defaut;

$dl_btn = cms_texte('telechargements_dl_btn', 'Télécharger');
$paiement_kicker = cms_texte('telechargements_paiement_kicker', 'Modalités de paiement');
$paiement_titre = cms_texte('telechargements_paiement_titre', 'Comment régler les frais de scolarité ?');
$paiement_texte = cms_texte('telechargements_paiement_texte', 'Les modalités de paiement officielles (montants par filière, échéanciers et facilités) sont détaillées dans le dépliant 2026-2027. Pour toute question ou situation particulière, le service scolarité vous accompagne.');
$paiement_items = cms_texte_extra('telechargements_paiement_items')['items'] ?? [
    'Paiement au service scolarité du campus (rond-point Gadafawa, Yantala)',
    'Échéanciers détaillés par niveau dans le dépliant officiel',
    'Réductions familles nombreuses au CSP Algoza (3 inscrits et plus)',
];
$paiement_btn1 = cms_texte('telechargements_paiement_btn1', 'Dépliant & modalités (PDF)');
$paiement_btn2 = cms_texte('telechargements_paiement_btn2', 'Scolarité');
$paiement_btn3 = cms_texte('telechargements_paiement_btn3', 'Poser une question');

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-hero.php';
?>

<section class="section">
  <div class="container">
    <div class="grid-3">
      <?php foreach ($documents as $i => $d) : $taille = taille_fichier($d['fichier']); ?>
      <article class="card formation-card reveal <?= $i % 3 === 1 ? 'reveal-delay-1' : ($i % 3 === 2 ? 'reveal-delay-2' : '') ?>">
        <span class="card-icon"><?= icon($d['icone'], 24) ?></span>
        <div class="badges">
          <span class="badge badge-primary"><?= e($d['type']) ?><?= $taille !== '' ? ' · ' . e($taille) : '' ?></span>
          <?php if (isset($d['badge'])) : ?><span class="badge badge-accent"><?= e($d['badge']) ?></span><?php endif; ?>
        </div>
        <h3 style="font-size: 1.1rem;"><?= e($d['titre']) ?></h3>
        <p><?= e($d['desc']) ?></p>
        <a class="btn btn-primary" style="margin-top: 1.2rem; align-self: flex-start;"
           href="<?= asset($d['fichier']) ?>" download="<?= e($d['nom_dl']) ?>">
          <?= icon('download', 17) ?> <?= e($dl_btn) ?>
        </a>
      </article>
      <?php endforeach; ?>
    </div>

    <!-- Modalités de paiement : accès rapide -->
    <div class="card reveal" style="margin-top: 3rem; padding: clamp(1.8rem, 4vw, 2.8rem);">
      <div class="grid-2">
        <div>
          <span class="kicker"><?= icon('landmark', 15) ?> <?= e($paiement_kicker) ?></span>
          <h2 class="h3" style="margin-bottom: 0.8rem;"><?= e($paiement_titre) ?></h2>
          <p style="color: var(--text-2); margin-bottom: 1rem;"><?= e($paiement_texte) ?></p>
          <ul class="check-list">
            <?php foreach ($paiement_items as $item) : ?>
            <li><?= icon('check', 18) ?><span><?= e($item) ?></span></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div style="display: flex; flex-direction: column; gap: 0.9rem; justify-content: center;">
          <a class="btn btn-accent btn-lg" href="<?= asset('docs/depliant-iat-2026-2027.pdf') ?>" download="Depliant-IAT-Niger-2026-2027.pdf"><?= icon('download', 18) ?> <?= e($paiement_btn1) ?></a>
          <a class="btn btn-outline btn-lg" href="tel:+22720752940"><?= icon('phone', 18) ?> <?= e($paiement_btn2) ?> : <?= e(SITE_PHONE_1) ?></a>
          <a class="btn btn-outline btn-lg" href="<?= url('contact') ?>"><?= icon('mail', 18) ?> <?= e($paiement_btn3) ?></a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
