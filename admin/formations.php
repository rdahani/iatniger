<?php
/** Gestion du catalogue de formations : niveaux (cms_niveaux) et filières (cms_formations). */

declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';
require_permission('formations');

$pdo = admin_require_cms();
$notice = '';
$erreur = '';
$onglet = $_GET['onglet'] ?? 'formations';
$action = $_GET['action'] ?? 'liste';

if ($pdo !== null && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $op = $_POST['op'] ?? '';

    if ($op === 'enregistrer_niveau') {
        $slug = trim($_POST['slug'] ?? '');
        if ($slug === '') {
            $erreur = 'Slug du niveau manquant.';
        } else {
            try {
                $st = $pdo->prepare('INSERT INTO cms_niveaux (slug, titre, sous_titre, recrutement, duree, dossier, description, ordre, publie) VALUES (?,?,?,?,?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE titre=VALUES(titre), sous_titre=VALUES(sous_titre), recrutement=VALUES(recrutement), duree=VALUES(duree), dossier=VALUES(dossier), description=VALUES(description), ordre=VALUES(ordre), publie=VALUES(publie)');
                $st->execute([
                    $slug,
                    trim($_POST['titre'] ?? ''),
                    trim($_POST['sous_titre'] ?? ''),
                    trim($_POST['recrutement'] ?? ''),
                    trim($_POST['duree'] ?? ''),
                    trim($_POST['dossier'] ?? ''),
                    trim($_POST['description'] ?? ''),
                    (int) ($_POST['ordre'] ?? 0),
                    isset($_POST['publie']) ? 1 : 0,
                ]);
                $notice = 'Niveau « ' . trim($_POST['titre'] ?? $slug) . ' » mis à jour.';
                $action = 'liste';
                $onglet = 'niveaux';
            } catch (PDOException $e) {
                $erreur = "Erreur d'enregistrement : " . $e->getMessage();
            }
        }
    } elseif ($op === 'enregistrer_formation') {
        $id = (int) ($_POST['id'] ?? 0);
        $titre = trim($_POST['titre'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        if ($slug === '' && $titre !== '') {
            $slug = admin_slugify($titre);
        }
        $niveau = trim($_POST['niveau'] ?? '');
        $debouches = array_values(array_filter(array_map('trim', explode("\n", (string) ($_POST['debouches'] ?? '')))));

        if ($titre === '' || $slug === '' || $niveau === '') {
            $erreur = 'Titre, slug et niveau sont obligatoires.';
            $action = $id > 0 ? 'editer_formation' : 'nouvelle_formation';
            $onglet = 'formations';
        } else {
            try {
                $data = [
                    $slug, $niveau,
                    trim($_POST['domaine'] ?? 'tertiaire'),
                    $titre,
                    trim($_POST['icone'] ?? 'book-open') ?: 'book-open',
                    trim($_POST['resume'] ?? ''),
                    trim($_POST['objectif'] ?? ''),
                    json_encode($debouches, JSON_UNESCAPED_UNICODE),
                    trim($_POST['badge'] ?? '') ?: null,
                    (int) ($_POST['ordre'] ?? 0),
                    isset($_POST['publie']) ? 1 : 0,
                ];
                if ($id > 0) {
                    $st = $pdo->prepare('UPDATE cms_formations SET slug=?, niveau=?, domaine=?, titre=?, icone=?, resume=?, objectif=?, debouches=?, badge=?, ordre=?, publie=? WHERE id=?');
                    $st->execute(array_merge($data, [$id]));
                    $notice = 'Formation mise à jour.';
                } else {
                    $st = $pdo->prepare('INSERT INTO cms_formations (slug, niveau, domaine, titre, icone, resume, objectif, debouches, badge, ordre, publie) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
                    $st->execute($data);
                    $notice = 'Formation créée.';
                }
                $action = 'liste';
                $onglet = 'formations';
            } catch (PDOException $e) {
                $erreur = str_contains($e->getMessage(), 'slug')
                    ? 'Ce slug est déjà utilisé par une autre formation.'
                    : "Erreur d'enregistrement : " . $e->getMessage();
                $action = $id > 0 ? 'editer_formation' : 'nouvelle_formation';
                $onglet = 'formations';
            }
        }
    } elseif ($op === 'supprimer_formation') {
        $pdo->prepare('DELETE FROM cms_formations WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
        $notice = 'Formation supprimée.';
        $onglet = 'formations';
    } elseif ($op === 'basculer_formation') {
        $pdo->prepare('UPDATE cms_formations SET publie = 1 - publie WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
        $notice = 'Statut de publication modifié.';
        $onglet = 'formations';
    }
}

$niveaux = [];
$formations = [];
$edition_niveau = null;
$edition_formation = null;

if ($pdo !== null) {
    $niveaux = $pdo->query('SELECT * FROM cms_niveaux ORDER BY ordre ASC')->fetchAll();

    if ($onglet === 'niveaux' && $action === 'editer') {
        $st = $pdo->prepare('SELECT * FROM cms_niveaux WHERE slug = ?');
        $st->execute([(string) ($_GET['slug'] ?? '')]);
        $edition_niveau = $st->fetch() ?: null;
    }

    if ($onglet === 'formations') {
        $niveau_filtre = $_GET['niveau'] ?? '';
        $sql = 'SELECT * FROM cms_formations';
        $params = [];
        if ($niveau_filtre !== '') {
            $sql .= ' WHERE niveau = ?';
            $params[] = $niveau_filtre;
        }
        $sql .= ' ORDER BY niveau ASC, ordre ASC, id ASC';
        $st = $pdo->prepare($sql);
        $st->execute($params);
        $formations = $st->fetchAll();

        if ($action === 'editer_formation') {
            $st = $pdo->prepare('SELECT * FROM cms_formations WHERE id = ?');
            $st->execute([(int) ($_GET['id'] ?? 0)]);
            $edition_formation = $st->fetch() ?: null;
            if ($edition_formation !== null) {
                $edition_formation['debouches'] = json_decode((string) $edition_formation['debouches'], true) ?: [];
            }
        }
    }
}

admin_head('Formations');
?>
<div class="admin-layout">
  <?php admin_sidebar('formations'); ?>
  <main class="admin-main">
    <div class="admin-header"><h1 class="h2">Formations</h1></div>

    <?php admin_flash($notice, $erreur); ?>

    <?php if ($pdo === null) : ?>
      <div class="alert alert-danger"><?= icon('alert-triangle', 18) ?><div>Le CMS n'est pas encore installé. <a href="<?= url('admin/install-cms.php') ?>">Installer le CMS</a> pour gérer les formations.</div></div>

    <?php else : ?>

    <div class="tabs-nav" role="tablist" style="margin-bottom: 1.6rem;">
      <a href="<?= url('admin/formations.php?onglet=formations') ?>" role="tab" aria-selected="<?= $onglet === 'formations' ? 'true' : 'false' ?>" style="text-decoration:none;">Filières</a>
      <a href="<?= url('admin/formations.php?onglet=niveaux') ?>" role="tab" aria-selected="<?= $onglet === 'niveaux' ? 'true' : 'false' ?>" style="text-decoration:none;">Niveaux</a>
    </div>

    <?php if ($onglet === 'niveaux') : ?>

      <?php if ($action === 'editer' && $edition_niveau) : ?>
      <div class="admin-header">
        <h2 class="h3">Modifier le niveau : <?= e($edition_niveau['titre']) ?></h2>
        <a class="btn btn-outline" href="<?= url('admin/formations.php?onglet=niveaux') ?>">← Retour à la liste</a>
      </div>
      <div class="admin-card">
        <form method="post" action="<?= url('admin/formations.php') ?>">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="op" value="enregistrer_niveau">
          <input type="hidden" name="slug" value="<?= e($edition_niveau['slug']) ?>">
          <div class="form-grid">
            <div class="form-field"><label for="nv-titre">Titre</label><input type="text" id="nv-titre" name="titre" required value="<?= e($edition_niveau['titre']) ?>"></div>
            <div class="form-field"><label for="nv-sous-titre">Sous-titre</label><input type="text" id="nv-sous-titre" name="sous_titre" value="<?= e((string) $edition_niveau['sous_titre']) ?>"></div>
            <div class="form-field"><label for="nv-recrutement">Recrutement</label><input type="text" id="nv-recrutement" name="recrutement" value="<?= e((string) $edition_niveau['recrutement']) ?>"></div>
            <div class="form-field"><label for="nv-duree">Durée</label><input type="text" id="nv-duree" name="duree" value="<?= e((string) $edition_niveau['duree']) ?>"></div>
            <div class="form-field full"><label for="nv-dossier">Dossier à fournir</label><textarea id="nv-dossier" name="dossier" style="min-height: 80px;"><?= e((string) $edition_niveau['dossier']) ?></textarea></div>
            <div class="form-field full"><label for="nv-description">Description</label><textarea id="nv-description" name="description" style="min-height: 100px;"><?= e((string) $edition_niveau['description']) ?></textarea></div>
            <div class="form-field"><label for="nv-ordre">Ordre</label><input type="number" id="nv-ordre" name="ordre" value="<?= (int) $edition_niveau['ordre'] ?>"></div>
            <div class="form-field full">
              <label style="display: flex; align-items: center; gap: 0.6rem; font-weight: 500;">
                <input type="checkbox" name="publie" style="width: auto;" <?= (int) $edition_niveau['publie'] === 1 ? 'checked' : '' ?>> Publié
              </label>
            </div>
          </div>
          <button class="btn btn-primary btn-lg" type="submit" style="margin-top: 1.4rem;">Enregistrer</button>
        </form>
      </div>
      <?php else : ?>
      <p class="caption" style="margin-bottom: 1.2rem;">Les niveaux (Niveau Moyen, Licence, Master, Doctorat…) structurent le catalogue de formations. Créez-en de nouveaux depuis l'installation CMS, puis modifiez-les ici.</p>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th scope="col">Titre</th><th scope="col">Recrutement</th><th scope="col">Durée</th><th scope="col">Statut</th><th scope="col">Actions</th></tr></thead>
          <tbody>
            <?php if (!$niveaux) : ?>
            <tr><td colspan="5">Aucun niveau enregistré.</td></tr>
            <?php endif; ?>
            <?php foreach ($niveaux as $n) : ?>
            <tr>
              <td><strong><?= e($n['titre']) ?></strong><br><span class="caption"><?= e((string) $n['sous_titre']) ?></span></td>
              <td><?= e(mb_strimwidth((string) $n['recrutement'], 0, 50, '…')) ?></td>
              <td><?= e((string) $n['duree']) ?></td>
              <td><?= (int) $n['publie'] === 1 ? '<span class="badge badge-success">Publié</span>' : '<span class="badge badge-accent">Masqué</span>' ?></td>
              <td><a class="icon-btn" href="<?= url('admin/formations.php?onglet=niveaux&action=editer&slug=' . rawurlencode($n['slug'])) ?>" aria-label="Modifier" title="Modifier"><?= icon('edit', 17) ?></a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

    <?php else : ?>

      <?php if ($action === 'nouvelle_formation' || ($action === 'editer_formation' && $edition_formation)) :
          $f = $edition_formation ?? [];
          $val = static fn (string $k, $defaut = '') => $f[$k] ?? ($_POST[$k] ?? $defaut); ?>
      <div class="admin-header">
        <h2 class="h3"><?= $edition_formation ? 'Modifier la formation' : 'Nouvelle formation' ?></h2>
        <a class="btn btn-outline" href="<?= url('admin/formations.php?onglet=formations') ?>">← Retour à la liste</a>
      </div>
      <div class="admin-card">
        <form method="post" action="<?= url('admin/formations.php') ?>">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="op" value="enregistrer_formation">
          <input type="hidden" name="id" value="<?= (int) ($f['id'] ?? 0) ?>">
          <div class="form-grid">
            <div class="form-field full"><label for="fm-titre">Titre *</label><input type="text" id="fm-titre" name="titre" required value="<?= e((string) $val('titre')) ?>"></div>
            <div class="form-field"><label for="fm-slug">Slug (laisser vide pour générer automatiquement)</label><input type="text" id="fm-slug" name="slug" value="<?= e((string) $val('slug')) ?>" placeholder="genie-civil"></div>
            <div class="form-field">
              <label for="fm-niveau">Niveau *</label>
              <select id="fm-niveau" name="niveau" required>
                <?php foreach ($niveaux as $n) : ?>
                <option value="<?= e($n['slug']) ?>" <?= (string) $val('niveau') === $n['slug'] ? 'selected' : '' ?>><?= e($n['titre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-field">
              <label for="fm-domaine">Domaine</label>
              <select id="fm-domaine" name="domaine">
                <option value="tertiaire" <?= (string) $val('domaine', 'tertiaire') === 'tertiaire' ? 'selected' : '' ?>>Tertiaire</option>
                <option value="industriel" <?= (string) $val('domaine') === 'industriel' ? 'selected' : '' ?>>Industriel</option>
              </select>
            </div>
            <?php admin_icon_field('icone', (string) $val('icone', 'book-open'), [
                'id' => 'fm-icone',
                'label' => 'Icône',
            ]); ?>
            <div class="form-field"><label for="fm-badge">Badge (optionnel, ex. CAMES)</label><input type="text" id="fm-badge" name="badge" value="<?= e((string) $val('badge')) ?>"></div>
            <div class="form-field full"><label for="fm-resume">Résumé (une phrase)</label><textarea id="fm-resume" name="resume" style="min-height: 70px;"><?= e((string) $val('resume')) ?></textarea></div>
            <div class="form-field full"><label for="fm-objectif">Objectif de la formation</label><textarea id="fm-objectif" name="objectif" style="min-height: 110px;"><?= e((string) $val('objectif')) ?></textarea></div>
            <div class="form-field full">
              <label for="fm-debouches">Débouchés (un par ligne)</label>
              <textarea id="fm-debouches" name="debouches" style="min-height: 110px;"><?= e($edition_formation !== null ? implode("\n", $edition_formation['debouches']) : (string) ($_POST['debouches'] ?? '')) ?></textarea>
            </div>
            <div class="form-field"><label for="fm-ordre">Ordre</label><input type="number" id="fm-ordre" name="ordre" value="<?= (int) $val('ordre', 0) ?>"></div>
            <div class="form-field full">
              <label style="display: flex; align-items: center; gap: 0.6rem; font-weight: 500;">
                <input type="checkbox" name="publie" style="width: auto;" <?= (int) $val('publie', 1) === 1 ? 'checked' : '' ?>> Publiée
              </label>
            </div>
          </div>
          <button class="btn btn-primary btn-lg" type="submit" style="margin-top: 1.4rem;">Enregistrer</button>
        </form>
      </div>
      <?php else : ?>

      <div class="admin-header" style="margin-top: 0;">
        <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
          <a class="btn <?= empty($_GET['niveau']) ? 'btn-primary' : 'btn-outline' ?>" href="<?= url('admin/formations.php?onglet=formations') ?>">Tous les niveaux</a>
          <?php foreach ($niveaux as $n) : ?>
          <a class="btn <?= ($_GET['niveau'] ?? '') === $n['slug'] ? 'btn-primary' : 'btn-outline' ?>" href="<?= url('admin/formations.php?onglet=formations&niveau=' . rawurlencode($n['slug'])) ?>"><?= e($n['titre']) ?></a>
          <?php endforeach; ?>
        </div>
        <a class="btn btn-primary" href="<?= url('admin/formations.php?onglet=formations&action=nouvelle_formation') ?>"><?= icon('plus', 16) ?> Nouvelle formation</a>
      </div>

      <div class="table-wrap">
        <table class="table">
          <thead><tr><th scope="col">Filière</th><th scope="col">Niveau</th><th scope="col">Domaine</th><th scope="col">Statut</th><th scope="col">Actions</th></tr></thead>
          <tbody>
            <?php if (!$formations) : ?>
            <tr><td colspan="5">Aucune formation. <a href="<?= url('admin/formations.php?onglet=formations&action=nouvelle_formation') ?>">Ajoutez-en une</a>.</td></tr>
            <?php endif; ?>
            <?php foreach ($formations as $f) : ?>
            <tr>
              <td><strong><?= e($f['titre']) ?></strong><?= $f['badge'] ? ' <span class="badge badge-accent">' . e($f['badge']) . '</span>' : '' ?><br><span class="caption"><?= e($f['slug']) ?></span></td>
              <td><?= e((string) ($niveaux[array_search($f['niveau'], array_column($niveaux, 'slug'), true)]['titre'] ?? $f['niveau'])) ?></td>
              <td><span class="badge badge-primary"><?= e(ucfirst($f['domaine'])) ?></span></td>
              <td><?= (int) $f['publie'] === 1 ? '<span class="badge badge-success">Publiée</span>' : '<span class="badge badge-accent">Masquée</span>' ?></td>
              <td>
                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                  <a class="icon-btn" href="<?= url('admin/formations.php?onglet=formations&action=editer_formation&id=' . (int) $f['id']) ?>" aria-label="Modifier" title="Modifier"><?= icon('edit', 17) ?></a>
                  <form method="post" action="<?= url('admin/formations.php') ?>" style="display: inline;">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="op" value="basculer_formation">
                    <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                    <button class="icon-btn" type="submit" aria-label="Publier / masquer" title="Publier / masquer"><?= icon((int) $f['publie'] === 1 ? 'eye' : 'check', 17) ?></button>
                  </form>
                  <form method="post" action="<?= url('admin/formations.php') ?>" style="display: inline;" onsubmit="return confirm('Supprimer définitivement cette formation ?');">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="op" value="supprimer_formation">
                    <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                    <button class="icon-btn" type="submit" aria-label="Supprimer" title="Supprimer" style="color: var(--danger);"><?= icon('trash', 17) ?></button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    <?php endif; ?>
    <?php endif; ?>
  </main>
</div>
</body>
</html>
