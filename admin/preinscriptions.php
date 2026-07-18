<?php
/** Suivi des préinscriptions en ligne. */

require_once __DIR__ . '/auth.php';
require_permission('preinscriptions');

$pdo = db();
$notice = '';

if ($pdo !== null && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $op = $_POST['op'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    if ($op === 'traite') {
        $pdo->prepare('UPDATE preinscriptions SET traite = 1 WHERE id = ?')->execute([$id]);
        $notice = 'Préinscription marquée comme traitée.';
    } elseif ($op === 'supprimer') {
        $pdo->prepare('DELETE FROM preinscriptions WHERE id = ?')->execute([$id]);
        $notice = 'Préinscription supprimée.';
    }
}

$liste = $pdo !== null ? $pdo->query('SELECT * FROM preinscriptions ORDER BY traite ASC, recu_le DESC')->fetchAll() : [];

admin_head('Préinscriptions');
?>
<div class="admin-layout">
  <?php admin_sidebar('preinscriptions'); ?>
  <main class="admin-main">
    <div class="admin-header"><h1 class="h2">Préinscriptions</h1></div>
    <?php if ($notice !== '') : ?><div class="alert alert-success"><?= icon('check-circle', 18) ?><div><?= e($notice) ?></div></div><?php endif; ?>
    <?php if ($pdo === null) : ?>
      <div class="alert alert-danger"><?= icon('x', 18) ?><div>Base de données indisponible.</div></div>
    <?php elseif (!$liste) : ?>
      <div class="admin-card"><p>Aucune préinscription pour le moment.</p></div>
    <?php else : ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th scope="col">Candidat</th><th scope="col">Contact</th><th scope="col">Formation demandée</th><th scope="col">Reçu le</th><th scope="col">Statut</th><th scope="col">Actions</th></tr></thead>
          <tbody>
            <?php foreach ($liste as $p) : ?>
            <tr>
              <td><strong><?= e($p['prenom'] . ' ' . $p['nom']) ?></strong><?php if (!empty($p['dernier_diplome'])) : ?><br><span class="caption"><?= e($p['dernier_diplome']) ?></span><?php endif; ?></td>
              <td><a href="tel:<?= e($p['telephone']) ?>"><?= e($p['telephone']) ?></a><br><a href="mailto:<?= e($p['email']) ?>" class="caption"><?= e($p['email']) ?></a></td>
              <td><?= e($p['formation']) ?><br><span class="caption"><?= e(NIVEAUX[$p['niveau']]['titre'] ?? $p['niveau']) ?></span></td>
              <td><?= e($p['recu_le']) ?></td>
              <td><?= (int) $p['traite'] === 1 ? '<span class="badge badge-success">Traitée</span>' : '<span class="badge badge-accent">À traiter</span>' ?></td>
              <td>
                <div style="display: flex; gap: 0.4rem;">
                  <?php if ((int) $p['traite'] === 0) : ?>
                  <form method="post" action="">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="op" value="traite"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                    <button class="icon-btn" type="submit" aria-label="Marquer traitée" title="Marquer traitée"><?= icon('check', 17) ?></button>
                  </form>
                  <?php endif; ?>
                  <form method="post" action="" onsubmit="return confirm('Supprimer cette préinscription ?');">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="op" value="supprimer"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
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
    <?php if ($liste) : ?>
    <p class="caption" style="margin-top: 1rem;">Les messages laissés par les candidats sont visibles en survolant la ligne — pensez à rappeler chaque candidat sous 48 h.</p>
    <?php endif; ?>
  </main>
</div>
</body>
</html>
