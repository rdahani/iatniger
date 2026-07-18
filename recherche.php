<?php
/** Recherche interne : formations (catalogue PHP) + actualités (BDD/fallback) + pages. */

require_once __DIR__ . '/config/config.php';

$q = trim($_GET['q'] ?? '');
$page_title = ($q !== '' ? 'Recherche : ' . $q : 'Recherche') . ' | IAT Niger';
$page_desc = "Recherchez une formation, une actualité ou une page sur le site de l'IAT Niger.";
$page_slug = 'recherche';
$active = '';
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => url()],
    ['label' => 'Recherche', 'url' => url('recherche')],
];
$hero_titre = 'Recherche';
$hero_texte = "Formations, actualités, admission : trouvez l'information en quelques secondes.";

/** Normalise une chaîne pour une recherche insensible aux accents/casse. */
function rech_normaliser(string $s): string
{
    $s = mb_strtolower($s);
    $t = @iconv('UTF-8', 'ASCII//TRANSLIT', $s);
    return $t !== false ? $t : $s;
}

$resultats = ['formations' => [], 'actualites' => [], 'pages' => []];
if ($q !== '') {
    $nq = rech_normaliser($q);

    foreach (FORMATIONS as $f) {
        $hay = rech_normaliser($f['titre'] . ' ' . $f['resume'] . ' ' . $f['objectif'] . ' ' . implode(' ', $f['debouches']));
        if (str_contains($hay, $nq)) {
            $resultats['formations'][] = $f;
        }
    }
    foreach (actualites() as $a) {
        $hay = rech_normaliser($a['titre'] . ' ' . $a['extrait'] . ' ' . $a['contenu']);
        if (str_contains($hay, $nq)) {
            $resultats['actualites'][] = $a;
        }
    }
    $pages_statiques = [
        ['titre' => 'À propos — histoire, mission, valeurs', 'url' => url('a-propos'), 'mots' => 'a propos historique mission vision valeurs excellence qualite transparence ouverture direction enseignants cames 1999'],
        ['titre' => "Admission & préinscription", 'url' => url('admission'), 'mots' => 'admission inscription preinscription conditions dossier frais candidature bac bepc'],
        ['titre' => 'Vie étudiante — BDE, clubs, alumni', 'url' => url('vie-etudiante'), 'mots' => 'vie etudiante bde clubs ppf alumni anciens sport culture voyages'],
        ['titre' => 'CSP Algoza — maternelle, primaire, collège, lycée', 'url' => url('csp-algoza'), 'mots' => 'csp algoza primaire maternelle college lycee ecole enfants scolarite cantine anglais'],
        ['titre' => 'WEB TV — vidéos', 'url' => url('web-tv'), 'mots' => 'web tv videos reportages rentree solennelle inauguration'],
        ['titre' => 'Galerie photos', 'url' => url('galerie'), 'mots' => 'galerie photos images campus laboratoires'],
        ['titre' => 'Partenaires', 'url' => url('partenaires'), 'mots' => 'partenaires anpe emig essec douala ist hcr cipmen opagen labari'],
        ['titre' => 'Téléchargements — dépliant, modalités de paiement, logos', 'url' => url('telechargements'), 'mots' => 'telechargements documents depliant brochure modalites paiement frais tarifs logo pdf telecharger'],
        ['titre' => 'FAQ — questions fréquentes', 'url' => url('faq'), 'mots' => 'faq questions frequentes reponses frais reconnaissance diplomes'],
        ['titre' => 'Contact', 'url' => url('contact'), 'mots' => 'contact adresse telephone email niamey gadafawa yantala'],
    ];
    foreach ($pages_statiques as $p) {
        if (str_contains(rech_normaliser($p['titre'] . ' ' . $p['mots']), $nq)) {
            $resultats['pages'][] = $p;
        }
    }
}
$total = count($resultats['formations']) + count($resultats['actualites']) + count($resultats['pages']);

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-hero.php';
?>

<section class="section">
  <div class="container" style="max-width: 860px;">
    <form method="get" action="<?= url('recherche') ?>" role="search" class="card" style="display: flex; gap: 0.8rem; padding: 1rem 1.2rem; margin-bottom: 2.5rem;">
      <label class="visually-hidden" for="q">Votre recherche</label>
      <input type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Ex. : génie civil, master, inscription…" autofocus
             style="flex: 1; border: 0; background: transparent; font-size: 1.05rem; outline: none;">
      <button class="btn btn-primary" type="submit"><?= icon('search', 18) ?> Rechercher</button>
    </form>

    <?php if ($q === '') : ?>
      <p class="lead">Saisissez un mot-clé pour rechercher parmi les 28 formations, les actualités et les pages du site.</p>
    <?php elseif ($total === 0) : ?>
      <div class="alert alert-danger"><?= icon('search', 20) ?><div>Aucun résultat pour « <strong><?= e($q) ?></strong> ». Essayez un autre mot-clé, ou <a href="<?= url('contact') ?>">contactez-nous directement</a>.</div></div>
    <?php else : ?>
      <p class="caption" style="margin-bottom: 2rem;"><?= $total ?> résultat<?= $total > 1 ? 's' : '' ?> pour « <strong><?= e($q) ?></strong> »</p>

      <?php if ($resultats['formations']) : ?>
      <h2 class="h3" style="margin-bottom: 1rem;"><?= icon('graduation-cap', 20) ?> Formations</h2>
      <div style="display: grid; gap: 0.9rem; margin-bottom: 2.5rem;">
        <?php foreach ($resultats['formations'] as $f) : ?>
        <a class="card" style="display: block;" href="<?= url('formation/' . $f['slug']) ?>">
          <div class="badges" style="margin-bottom: 0.4rem;">
            <span class="badge badge-primary"><?= e((niveaux_catalogue()[$f['niveau']]['titre'] ?? $f['niveau'])) ?></span>
            <?php if (!empty($f['badge'])) : ?><span class="badge badge-accent"><?= e($f['badge']) ?></span><?php endif; ?>
          </div>
          <h3 style="font-size: 1.05rem;"><?= e($f['titre']) ?></h3>
          <p><?= e($f['resume'] ?? '') ?></p>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if ($resultats['actualites']) : ?>
      <h2 class="h3" style="margin-bottom: 1rem;"><?= icon('newspaper', 20) ?> Actualités</h2>
      <div style="display: grid; gap: 0.9rem; margin-bottom: 2.5rem;">
        <?php foreach ($resultats['actualites'] as $a) : ?>
        <a class="card" style="display: block;" href="<?= url('actualites/' . $a['slug']) ?>">
          <p class="caption"><?= e(date_fr($a['date_publication'])) ?> · <?= e($a['categorie']) ?></p>
          <h3 style="font-size: 1.05rem;"><?= e($a['titre']) ?></h3>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if ($resultats['pages']) : ?>
      <h2 class="h3" style="margin-bottom: 1rem;"><?= icon('file-text', 20) ?> Pages</h2>
      <div style="display: grid; gap: 0.9rem;">
        <?php foreach ($resultats['pages'] as $p) : ?>
        <a class="card" style="display: block;" href="<?= e($p['url']) ?>">
          <h3 style="font-size: 1.05rem;"><?= e($p['titre']) ?></h3>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
