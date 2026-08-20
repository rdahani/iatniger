<?php
/** Éditeur dédié à la page d'accueil : hero, statistiques, cartes « pourquoi » et diaporama. */

declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';
require_permission('accueil');

$pdo = admin_require_cms();
$notice = '';
$erreur = '';

/** Textes de l'accueil (hero, fondateur, CSP, cartes flottantes), identifiés par leur clé unique. */
$textes_config = [
    'accueil_hero_kicker' => ['label' => "Accroche courte au-dessus du titre (kicker)", 'liste' => false],
    'accueil_hero_h1' => ['label' => 'Titre principal (h1)', 'liste' => false],
    'accueil_hero_lead' => ['label' => 'Texte d\'introduction sous le titre', 'liste' => false],
    'accueil_hero_trust' => ['label' => 'Points de confiance (un par ligne)', 'liste' => true],
    'accueil_hero_card1_titre' => ['label' => 'Carte flottante 1 — titre', 'liste' => false],
    'accueil_hero_card1_sous' => ['label' => 'Carte flottante 1 — sous-titre', 'liste' => false],
    'accueil_hero_card2_titre' => ['label' => 'Carte flottante 2 — titre', 'liste' => false],
    'accueil_hero_card2_sous' => ['label' => 'Carte flottante 2 — sous-titre', 'liste' => false],
    'accueil_fondateur_photo' => ['label' => 'Photo du fondateur', 'liste' => false, 'media' => true],
    'accueil_fondateur_nom' => ['label' => 'Nom du fondateur', 'liste' => false],
    'accueil_fondateur_fonction' => ['label' => 'Fonction / titre sous le nom', 'liste' => false],
    'accueil_fondateur_titre' => ['label' => 'Titre du bloc « Mot du fondateur »', 'liste' => false],
    'accueil_fondateur_texte_1' => ['label' => 'Citation / premier paragraphe', 'liste' => false],
    'accueil_fondateur_texte_2' => ['label' => 'Second paragraphe', 'liste' => false],
    'accueil_fondateur_points' => ['label' => 'Points clés sous le texte (un par ligne)', 'liste' => true],
    'accueil_csp_titre' => ['label' => 'Titre du bloc CSP Algoza', 'liste' => false],
    'accueil_csp_texte' => ['label' => 'Texte du bloc CSP Algoza', 'liste' => false],
    'accueil_csp_liste' => ['label' => 'Liste à puces du bloc CSP Algoza (une par ligne)', 'liste' => true],
];

/** Valeurs affichées dans le formulaire si la clé n'existe pas encore en base. */
$textes_defauts = [
    'accueil_hero_kicker' => 'Accrédité CAMES · ANAQ-SUP · Depuis 1999',
    'accueil_hero_h1' => "Construisez votre avenir dans un pôle d'excellence",
    'accueil_hero_lead' => "L'Institut Africain de Technologie forme les cadres et techniciens qui transforment le Niger et l'Afrique : 28 filières du Bac Pro au Doctorat, des laboratoires modernes et 25 ans d'expérience.",
    'accueil_hero_trust' => "16 diplômes accrédités CAMES\nSystème LMD\nLaboratoires équipés",
    'accueil_hero_card1_titre' => '30 000+',
    'accueil_hero_card1_sous' => 'diplômés depuis 1999',
    'accueil_hero_card2_titre' => "Alkalami d'Or 2026",
    'accueil_hero_card2_sous' => "Prix de l'excellence académique",
    'accueil_fondateur_photo' => 'fondateur-hamadou-hamidou.jpg',
    'accueil_fondateur_nom' => 'M. Hamadou Hamidou',
    'accueil_fondateur_fonction' => "Fondateur de l'IAT Niger",
    'accueil_fondateur_titre' => "Une ambition : révéler le potentiel de la jeunesse africaine",
    'accueil_fondateur_texte_1' => "Depuis 1999, l'Institut Africain de Technologie poursuit une seule ambition : offrir à la jeunesse nigérienne et africaine une formation à la hauteur de son potentiel. Nos quatre valeurs — l'excellence, la qualité, la transparence et l'ouverture au monde — se lisent dans nos accréditations CAMES, dans nos laboratoires et dans les carrières de nos 30 000 diplômés.",
    'accueil_fondateur_texte_2' => "Choisir l'IAT, c'est rejoindre une institution qui investit continuellement dans ses infrastructures, son corps enseignant et ses partenariats internationaux, pour que chaque étudiant reparte avec bien plus qu'un diplôme : un métier, une méthode et un réseau.",
    'accueil_fondateur_points' => "30 000+ diplômés\n16 diplômes CAMES\nDepuis 1999",
    'accueil_csp_titre' => "CSP Algoza : l'excellence dès le plus jeune âge",
    'accueil_csp_texte' => "Le Complexe Scolaire Privé Algoza accueille vos enfants de la maternelle au baccalauréat : anglais renforcé, un ordinateur par élève, cantine et classes de 25 élèves maximum.",
    'accueil_csp_liste' => "Maternelle & primaire — anglais dès le CI, 25 ordinateurs\nCollège & lycée — séries A, C et D, 4 h d'anglais par semaine\nCantine quotidienne et jardin potager pédagogique",
];

if ($pdo !== null && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $op = $_POST['op'] ?? '';

    if ($op === 'enregistrer_textes') {
        $n = 0;
        foreach ($textes_config as $cle => $cfg) {
            $contenu = null;
            $extra = null;
            if ($cfg['liste']) {
                $items = array_values(array_filter(array_map('trim', explode("\n", (string) ($_POST['textes'][$cle] ?? '')))));
                $extra = cms_extra_encode(['items' => $items]);
            } else {
                $contenu = trim((string) ($_POST['textes'][$cle] ?? ''));
            }
            $st = $pdo->prepare('INSERT INTO cms_items (type, cle, contenu, extra) VALUES (?,?,?,?)
                ON DUPLICATE KEY UPDATE contenu = VALUES(contenu), extra = VALUES(extra)');
            $st->execute(['texte', $cle, $contenu, $extra]);
            $n++;
        }
        $notice = 'Textes de la page d\'accueil mis à jour (' . $n . ').';
    } elseif ($op === 'enregistrer_stats') {
        $st = $pdo->prepare('UPDATE cms_items SET titre = ?, extra = ? WHERE id = ? AND type = ? AND groupe = ?');
        $n = 0;
        foreach ($_POST['stats'] ?? [] as $id => $vals) {
            $extra = cms_extra_encode(['valeur' => (int) ($vals['valeur'] ?? 0), 'suffixe' => trim((string) ($vals['suffixe'] ?? ''))]);
            $st->execute([trim((string) ($vals['titre'] ?? '')), $extra, (int) $id, 'stat', 'accueil']);
            $n++;
        }
        $notice = 'Statistiques mises à jour (' . $n . ').';
    } elseif ($op === 'enregistrer_cartes') {
        $st = $pdo->prepare('UPDATE cms_items SET titre = ?, contenu = ?, extra = ? WHERE id = ? AND type = ? AND groupe = ?');
        $n = 0;
        foreach ($_POST['cartes'] ?? [] as $id => $vals) {
            $extra = cms_extra_encode(['icone' => trim((string) ($vals['icone'] ?? ''))]);
            $st->execute([trim((string) ($vals['titre'] ?? '')), trim((string) ($vals['contenu'] ?? '')), $extra, (int) $id, 'carte', 'accueil-pourquoi']);
            $n++;
        }
        $notice = 'Cartes « pourquoi choisir l\'IAT » mises à jour (' . $n . ').';
    } elseif ($op === 'enregistrer_slides') {
        $st = $pdo->prepare('UPDATE cms_items SET titre = ?, image = ? WHERE id = ? AND type = ? AND groupe = ?');
        $n = 0;
        foreach ($_POST['slides'] ?? [] as $id => $vals) {
            $st->execute([trim((string) ($vals['titre'] ?? '')), trim((string) ($vals['image'] ?? '')), (int) $id, 'hero_slide', 'accueil']);
            $n++;
        }
        $notice = 'Diaporama mis à jour (' . $n . ').';
    }
}

$textes = [];
$stats = [];
$cartes = [];
$slides = [];
if ($pdo !== null) {
    foreach (array_keys($textes_config) as $cle) {
        $st = $pdo->prepare('SELECT * FROM cms_items WHERE type = ? AND cle = ?');
        $st->execute(['texte', $cle]);
        $row = $st->fetch();
        if ($row) {
            $row['extra'] = $row['extra'] !== null && $row['extra'] !== '' ? (json_decode((string) $row['extra'], true) ?: []) : [];
        }
        $textes[$cle] = $row ?: null;
    }
    $stats = $pdo->query("SELECT * FROM cms_items WHERE type = 'stat' AND groupe = 'accueil' ORDER BY ordre ASC, id ASC")->fetchAll();
    foreach ($stats as &$s) {
        $s['extra'] = $s['extra'] !== null && $s['extra'] !== '' ? (json_decode((string) $s['extra'], true) ?: []) : [];
    }
    unset($s);
    $cartes = $pdo->query("SELECT * FROM cms_items WHERE type = 'carte' AND groupe = 'accueil-pourquoi' ORDER BY ordre ASC, id ASC")->fetchAll();
    foreach ($cartes as &$c) {
        $c['extra'] = $c['extra'] !== null && $c['extra'] !== '' ? (json_decode((string) $c['extra'], true) ?: []) : [];
    }
    unset($c);
    $slides = $pdo->query("SELECT * FROM cms_items WHERE type = 'hero_slide' AND groupe = 'accueil' ORDER BY ordre ASC, id ASC")->fetchAll();
}

admin_head('Accueil');
?>
<div class="admin-layout">
  <?php admin_sidebar('accueil'); ?>
  <main class="admin-main">
    <div class="admin-header"><h1 class="h2">Page d'accueil</h1></div>

    <?php admin_flash($notice, $erreur); ?>

    <?php if ($pdo === null) : ?>
      <div class="alert alert-danger"><?= icon('alert-triangle', 18) ?><div>Le CMS n'est pas encore installé. <a href="<?= url('admin/install-cms.php') ?>">Installer le CMS</a> pour éditer la page d'accueil.</div></div>
    <?php else : ?>

    <div class="admin-card" style="margin-bottom: 1.6rem;">
      <h2 class="h3" style="margin-bottom: 1rem;">Textes du hero, du fondateur &amp; du bloc CSP Algoza</h2>
      <form method="post" action="<?= url('admin/accueil.php') ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="op" value="enregistrer_textes">
        <div class="form-grid">
          <?php foreach ($textes_config as $cle => $cfg) :
              $row = $textes[$cle];
              $defaut = $textes_defauts[$cle] ?? '';
              if (!empty($cfg['media'])) {
                  $photo = (string) ($row['contenu'] ?? $defaut);
                  admin_media_field('textes[' . $cle . ']', $photo, [
                      'id' => 'tx-' . $cle,
                      'label' => $cfg['label'],
                      'base' => 'img',
                      'accept' => 'image',
                      'full' => true,
                  ]);
                  continue;
              }
              if ($cfg['liste']) {
                  $valeur = $row ? implode("\n", $row['extra']['items'] ?? []) : $defaut;
              } else {
                  $valeur = (string) ($row['contenu'] ?? $defaut);
              }
              ?>
          <div class="form-field full">
            <label for="tx-<?= e($cle) ?>"><?= e($cfg['label']) ?></label>
            <textarea id="tx-<?= e($cle) ?>" name="textes[<?= e($cle) ?>]" style="min-height: <?= $cfg['liste'] ? '90' : '70' ?>px;"><?= e($valeur) ?></textarea>
          </div>
          <?php endforeach; ?>
        </div>
        <button class="btn btn-primary btn-lg" type="submit" style="margin-top: 1.2rem;">Enregistrer les textes</button>
      </form>
      <p class="caption" style="margin-top: 1rem;">Les cartes « programmes » (Niveau Moyen, Licences, Masters, Doctorat) se gèrent dans <a href="<?= url('admin/formations.php') ?>">Formations → Niveaux</a>.</p>
    </div>

    <div class="admin-card" style="margin-bottom: 1.6rem;">
      <h2 class="h3" style="margin-bottom: 1rem;">Statistiques (bandeau de chiffres)</h2>
      <?php if (!$stats) : ?>
      <p>Aucune statistique enregistrée. <a href="<?= url('admin/install-cms.php') ?>">Lancez l'installation du CMS</a> ou <a href="<?= url('admin/contenu.php?type=stat&groupe=accueil&action=nouvelle') ?>">ajoutez-en une</a>.</p>
      <?php else : ?>
      <form method="post" action="<?= url('admin/accueil.php') ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="op" value="enregistrer_stats">
        <div class="form-grid">
          <?php foreach ($stats as $s) : ?>
          <div class="form-field"><label>Libellé</label><input type="text" name="stats[<?= (int) $s['id'] ?>][titre]" value="<?= e((string) $s['titre']) ?>"></div>
          <div class="form-field"><label>Valeur</label><input type="number" name="stats[<?= (int) $s['id'] ?>][valeur]" value="<?= (int) ($s['extra']['valeur'] ?? 0) ?>"></div>
          <div class="form-field"><label>Suffixe</label><input type="text" name="stats[<?= (int) $s['id'] ?>][suffixe]" value="<?= e((string) ($s['extra']['suffixe'] ?? '')) ?>" placeholder="+"></div>
          <?php endforeach; ?>
        </div>
        <button class="btn btn-primary btn-lg" type="submit" style="margin-top: 1.2rem;">Enregistrer les statistiques</button>
      </form>
      <?php endif; ?>
      <p class="caption" style="margin-top: 1rem;"><a href="<?= url('admin/contenu.php?type=stat&groupe=accueil') ?>">Ajouter, réordonner ou supprimer des statistiques →</a></p>
    </div>

    <div class="admin-card" style="margin-bottom: 1.6rem;">
      <h2 class="h3" style="margin-bottom: 1rem;">Cartes « Pourquoi choisir l'IAT ? »</h2>
      <?php if (!$cartes) : ?>
      <p>Aucune carte enregistrée. <a href="<?= url('admin/install-cms.php') ?>">Lancez l'installation du CMS</a> ou <a href="<?= url('admin/contenu.php?type=carte&groupe=accueil-pourquoi&action=nouvelle') ?>">ajoutez-en une</a>.</p>
      <?php else : ?>
      <form method="post" action="<?= url('admin/accueil.php') ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="op" value="enregistrer_cartes">
        <div style="display: grid; gap: 1.2rem;">
          <?php foreach ($cartes as $c) : ?>
          <div class="form-grid" style="border-top: 1px solid var(--border, #e5e7eb); padding-top: 1rem;">
            <div class="form-field"><label>Titre</label><input type="text" name="cartes[<?= (int) $c['id'] ?>][titre]" value="<?= e((string) $c['titre']) ?>"></div>
            <?php admin_icon_field('cartes[' . (int) $c['id'] . '][icone]', (string) ($c['extra']['icone'] ?? 'award'), [
                'id' => 'accueil-icone-' . (int) $c['id'],
                'label' => 'Icône',
            ]); ?>
            <div class="form-field full"><label>Texte</label><textarea name="cartes[<?= (int) $c['id'] ?>][contenu]" style="min-height: 70px;"><?= e((string) $c['contenu']) ?></textarea></div>
          </div>
          <?php endforeach; ?>
        </div>
        <button class="btn btn-primary btn-lg" type="submit" style="margin-top: 1.2rem;">Enregistrer les cartes</button>
      </form>
      <?php endif; ?>
      <p class="caption" style="margin-top: 1rem;"><a href="<?= url('admin/contenu.php?type=carte&groupe=accueil-pourquoi') ?>">Ajouter, réordonner ou supprimer des cartes →</a></p>
    </div>

    <div class="admin-card" style="margin-bottom: 1.6rem;">
      <h2 class="h3" style="margin-bottom: 1rem;">Diaporama du hero</h2>
      <?php if (!$slides) : ?>
      <p>Aucune image enregistrée. <a href="<?= url('admin/install-cms.php') ?>">Lancez l'installation du CMS</a> ou <a href="<?= url('admin/contenu.php?type=hero_slide&groupe=accueil&action=nouvelle') ?>">ajoutez-en une</a>.</p>
      <?php else : ?>
      <form method="post" action="<?= url('admin/accueil.php') ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="op" value="enregistrer_slides">
        <div class="form-grid">
          <?php foreach ($slides as $s) : ?>
          <?php admin_media_field('slides[' . (int) $s['id'] . '][image]', (string) $s['image'], [
              'id' => 'accueil-slide-' . (int) $s['id'],
              'label' => 'Image du diaporama',
              'base' => 'img',
              'accept' => 'image',
              'full' => true,
          ]); ?>
          <div class="form-field"><label>Texte alternatif</label><input type="text" name="slides[<?= (int) $s['id'] ?>][titre]" value="<?= e((string) $s['titre']) ?>"></div>
          <?php endforeach; ?>
        </div>
        <button class="btn btn-primary btn-lg" type="submit" style="margin-top: 1.2rem;">Enregistrer le diaporama</button>
      </form>
      <?php endif; ?>
      <p class="caption" style="margin-top: 1rem;"><a href="<?= url('admin/contenu.php?type=hero_slide&groupe=accueil') ?>">Ajouter, réordonner ou supprimer des images →</a></p>
    </div>

    <div class="admin-card">
      <h2 class="h3" style="margin-bottom: 1rem;">Autres blocs affichés sur l'accueil</h2>
      <div style="display: flex; flex-wrap: wrap; gap: 0.8rem;">
        <a class="btn btn-outline" href="<?= url('admin/contenu.php?type=temoignage') ?>"><?= icon('quote', 16) ?> Témoignages</a>
        <a class="btn btn-outline" href="<?= url('admin/contenu.php?type=partenaire') ?>"><?= icon('handshake', 16) ?> Partenaires</a>
        <a class="btn btn-outline" href="<?= url('admin/actualites.php') ?>"><?= icon('newspaper', 16) ?> Actualités récentes</a>
      </div>
    </div>

    <?php endif; ?>
  </main>
</div>
</body>
</html>
