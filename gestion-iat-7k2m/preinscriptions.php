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
$niveaux = function_exists('niveaux_catalogue') ? niveaux_catalogue() : (defined('NIVEAUX') ? NIVEAUX : []);

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
      <div style="display: grid; gap: 1rem;">
        <?php foreach ($liste as $p) : ?>
        <div class="admin-card" style="<?= (int) $p['traite'] === 0 ? 'border-left: 4px solid var(--accent);' : '' ?>">
          <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 0.8rem; margin-bottom: 0.8rem;">
            <div>
              <strong><?= e($p['prenom'] . ' ' . $p['nom']) ?></strong>
              <?php if ((int) $p['traite'] === 0) : ?>
                <span class="badge badge-accent" style="margin-left: 0.5rem;">À traiter</span>
              <?php else : ?>
                <span class="badge badge-success" style="margin-left: 0.5rem;">Traitée</span>
              <?php endif; ?>
              <p class="caption" style="margin: 0.35rem 0 0;">
                <?= e($p['formation']) ?>
                · <?= e($niveaux[$p['niveau']]['titre'] ?? $p['niveau']) ?>
                <?php if (!empty($p['dernier_diplome'])) : ?> · <?= e($p['dernier_diplome']) ?><?php endif; ?>
              </p>
              <p class="caption" style="margin: 0.2rem 0 0;">
                <a href="tel:<?= e($p['telephone']) ?>"><?= e($p['telephone']) ?></a>
                · <a href="mailto:<?= e($p['email']) ?>"><?= e($p['email']) ?></a>
                · reçu le <?= e($p['recu_le']) ?>
              </p>
            </div>
            <div style="display: flex; gap: 0.4rem; align-items: flex-start; flex-wrap: wrap;">
              <?php
              $softiatUrl = function_exists('softiat_preinscription_url') ? softiat_preinscription_url((int) $p['id']) : '';
              if ($softiatUrl !== '') : ?>
              <a class="btn btn-primary" href="<?= e($softiatUrl) ?>" target="_blank" rel="noopener"><?= icon('external-link', 16) ?> SoftIAT</a>
              <?php endif; ?>
              <?php if ((int) $p['traite'] === 0) : ?>
              <form method="post" action="">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="op" value="traite"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                <button class="btn btn-outline" type="submit"><?= icon('check', 16) ?> Traiter</button>
              </form>
              <?php endif; ?>
              <a class="btn btn-outline" href="mailto:<?= e($p['email']) ?>?subject=<?= rawurlencode('Votre préinscription — IAT Niger') ?>"><?= icon('mail', 16) ?> Répondre</a>
              <form method="post" action="" onsubmit="return confirm('Supprimer cette préinscription ?');">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="op" value="supprimer"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                <button class="icon-btn" type="submit" aria-label="Supprimer" title="Supprimer" style="color: var(--danger);"><?= icon('trash', 17) ?></button>
              </form>
            </div>
          </div>
          <?php if (trim((string) ($p['message'] ?? '')) !== '') : ?>
          <p style="white-space: pre-line; color: var(--text-2); margin: 0; padding-top: 0.6rem; border-top: 1px solid var(--border, #e5e7eb);"><?= e($p['message']) ?></p>
          <?php else : ?>
          <p class="caption" style="margin: 0; padding-top: 0.6rem; border-top: 1px solid var(--border, #e5e7eb);">Aucun message laissé par le candidat.</p>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <p class="caption" style="margin-top: 1rem;">Pensez à rappeler chaque candidat sous 48 h ouvrées.</p>
    <?php endif; ?>
  </main>
</div>
</body>
</html>
