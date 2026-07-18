<?php
/** Gestion des actualités : liste, création, édition, publication, suppression. */

require_once __DIR__ . '/_helpers.php';
require_permission('actualites');

$pdo = db();
$action = $_GET['action'] ?? 'liste';
$notice = '';
$erreur = '';

/** Génère un slug URL propre à partir d'un titre. */
function slugifier(string $titre): string
{
    $s = @iconv('UTF-8', 'ASCII//TRANSLIT', mb_strtolower($titre));
    if ($s === false) {
        $s = mb_strtolower($titre);
    }
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-') ?: 'article';
}

/* ----- Traitements POST ----- */
if ($pdo !== null && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $op = $_POST['op'] ?? '';

    if ($op === 'enregistrer') {
        $id = (int) ($_POST['id'] ?? 0);
        $titre = trim($_POST['titre'] ?? '');
        $categorie = trim($_POST['categorie'] ?? 'Actualité');
        $extrait = trim($_POST['extrait'] ?? '');
        $contenu = trim($_POST['contenu'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $date_pub = $_POST['date_publication'] ?? date('Y-m-d');
        $publie = isset($_POST['publie']) ? 1 : 0;

        if ($titre === '' || $extrait === '' || $contenu === '') {
            $erreur = 'Titre, extrait et contenu sont obligatoires.';
            $action = $id > 0 ? 'editer' : 'nouvelle';
        } else {
            try {
                if ($id > 0) {
                    $st = $pdo->prepare('UPDATE actualites SET titre=?, categorie=?, extrait=?, contenu=?, image=?, date_publication=?, publie=? WHERE id=?');
                    $st->execute([$titre, $categorie, $extrait, $contenu, $image, $date_pub, $publie, $id]);
                    $notice = 'Actualité mise à jour.';
                } else {
                    $slug = slugifier($titre);
                    /* Unicité du slug */
                    $st = $pdo->prepare('SELECT COUNT(*) FROM actualites WHERE slug = ?');
                    $st->execute([$slug]);
                    if ((int) $st->fetchColumn() > 0) {
                        $slug .= '-' . time();
                    }
                    $st = $pdo->prepare('INSERT INTO actualites (slug, titre, categorie, extrait, contenu, image, date_publication, publie) VALUES (?,?,?,?,?,?,?,?)');
                    $st->execute([$slug, $titre, $categorie, $extrait, $contenu, $image, $date_pub, $publie]);
                    $notice = 'Actualité créée.';
                }
                $action = 'liste';
            } catch (PDOException $e) {
                $erreur = "Erreur d'enregistrement : " . $e->getMessage();
            }
        }
    } elseif ($op === 'supprimer') {
        $st = $pdo->prepare('DELETE FROM actualites WHERE id = ?');
        $st->execute([(int) ($_POST['id'] ?? 0)]);
        $notice = 'Actualité supprimée.';
    } elseif ($op === 'basculer') {
        $st = $pdo->prepare('UPDATE actualites SET publie = 1 - publie WHERE id = ?');
        $st->execute([(int) ($_POST['id'] ?? 0)]);
        $notice = 'Statut de publication modifié.';
    }
}

/* ----- Données pour l'affichage ----- */
$edition = null;
if ($action === 'editer' && $pdo !== null) {
    $st = $pdo->prepare('SELECT * FROM actualites WHERE id = ?');
    $st->execute([(int) ($_GET['id'] ?? $_POST['id'] ?? 0)]);
    $edition = $st->fetch() ?: null;
    if ($edition === null) {
        $action = 'liste';
    }
}
$liste = [];
if ($pdo !== null && $action === 'liste') {
    $liste = $pdo->query('SELECT * FROM actualites ORDER BY date_publication DESC')->fetchAll();
}

admin_head('Actualités');
?>
<div class="admin-layout">
  <?php admin_sidebar('actualites'); ?>
  <main class="admin-main">

    <?php if ($notice !== '') : ?><div class="alert alert-success"><?= icon('check-circle', 18) ?><div><?= e($notice) ?></div></div><?php endif; ?>
    <?php if ($erreur !== '') : ?><div class="alert alert-danger"><?= icon('x', 18) ?><div><?= e($erreur) ?></div></div><?php endif; ?>

    <?php if ($pdo === null) : ?>
      <div class="alert alert-danger"><?= icon('x', 18) ?><div>Base de données indisponible.</div></div>

    <?php elseif ($action === 'nouvelle' || $action === 'editer') : ?>
      <div class="admin-header">
        <h1 class="h2"><?= $edition ? 'Modifier l\'actualité' : 'Nouvelle actualité' ?></h1>
        <a class="btn btn-outline" href="<?= url('admin/actualites.php') ?>">← Retour à la liste</a>
      </div>
      <div class="admin-card">
        <form method="post" action="<?= url('admin/actualites.php') ?>">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="op" value="enregistrer">
          <input type="hidden" name="id" value="<?= (int) ($edition['id'] ?? 0) ?>">
          <div class="form-grid">
            <div class="form-field full">
              <label for="ac-titre">Titre *</label>
              <input type="text" id="ac-titre" name="titre" required value="<?= e($edition['titre'] ?? $_POST['titre'] ?? '') ?>">
            </div>
            <div class="form-field">
              <label for="ac-cat">Catégorie</label>
              <select id="ac-cat" name="categorie">
                <?php foreach (['Actualité', 'Infrastructure', 'Vie étudiante', 'Distinction', 'Partenariat', 'Événement'] as $c) : ?>
                <option value="<?= e($c) ?>" <?= ($edition['categorie'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-field">
              <label for="ac-date">Date de publication</label>
              <input type="date" id="ac-date" name="date_publication" value="<?= e($edition['date_publication'] ?? date('Y-m-d')) ?>">
            </div>
            <?php admin_media_field('image', (string) ($edition['image'] ?? $_POST['image'] ?? ''), [
                'id' => 'ac-image',
                'label' => 'Image de l\'actualité',
                'base' => 'img',
                'accept' => 'image',
                'full' => true,
            ]); ?>
            <div class="form-field full">
              <label for="ac-extrait">Extrait (résumé affiché dans les listes) *</label>
              <textarea id="ac-extrait" name="extrait" required style="min-height: 90px;"><?= e($edition['extrait'] ?? $_POST['extrait'] ?? '') ?></textarea>
            </div>
            <div class="form-field full">
              <label for="ac-contenu">Contenu de l'article * (séparez les paragraphes par une ligne vide)</label>
              <textarea id="ac-contenu" name="contenu" required style="min-height: 240px;"><?= e($edition['contenu'] ?? $_POST['contenu'] ?? '') ?></textarea>
            </div>
            <div class="form-field full">
              <label style="display: flex; align-items: center; gap: 0.6rem; font-weight: 500;">
                <input type="checkbox" name="publie" style="width: auto;" <?= (int) ($edition['publie'] ?? 1) === 1 ? 'checked' : '' ?>> Publier immédiatement
              </label>
            </div>
          </div>
          <button class="btn btn-primary btn-lg" type="submit" style="margin-top: 1.4rem;">Enregistrer</button>
        </form>
      </div>

    <?php else : ?>
      <div class="admin-header">
        <h1 class="h2">Actualités</h1>
        <a class="btn btn-primary" href="<?= url('admin/actualites.php?action=nouvelle') ?>"><?= icon('plus', 16) ?> Nouvelle actualité</a>
      </div>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th scope="col">Titre</th><th scope="col">Catégorie</th><th scope="col">Date</th><th scope="col">Statut</th><th scope="col">Actions</th></tr></thead>
          <tbody>
            <?php if (!$liste) : ?>
            <tr><td colspan="5">Aucune actualité. <a href="<?= url('admin/actualites.php?action=nouvelle') ?>">Créez la première</a>.</td></tr>
            <?php endif; ?>
            <?php foreach ($liste as $a) : ?>
            <tr>
              <td><strong><?= e(mb_strimwidth($a['titre'], 0, 60, '…')) ?></strong></td>
              <td><span class="badge badge-primary"><?= e($a['categorie']) ?></span></td>
              <td><?= e(date_fr($a['date_publication'])) ?></td>
              <td><?= (int) $a['publie'] === 1 ? '<span class="badge badge-success">Publié</span>' : '<span class="badge badge-accent">Brouillon</span>' ?></td>
              <td>
                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                  <a class="icon-btn" href="<?= url('admin/actualites.php?action=editer&id=' . (int) $a['id']) ?>" aria-label="Modifier" title="Modifier"><?= icon('edit', 17) ?></a>
                  <form method="post" action="<?= url('admin/actualites.php') ?>" style="display: inline;">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="op" value="basculer">
                    <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                    <button class="icon-btn" type="submit" aria-label="Publier / dépublier" title="Publier / dépublier"><?= icon((int) $a['publie'] === 1 ? 'eye' : 'check', 17) ?></button>
                  </form>
                  <form method="post" action="<?= url('admin/actualites.php') ?>" style="display: inline;" onsubmit="return confirm('Supprimer définitivement cette actualité ?');">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="op" value="supprimer">
                    <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
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
  </main>
</div>
</body>
</html>
