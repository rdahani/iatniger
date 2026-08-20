<?php
/** Consultation des messages du formulaire de contact. */

require_once __DIR__ . '/auth.php';
require_permission('messages');

$pdo = db();
$notice = '';

if ($pdo !== null && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $op = $_POST['op'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    if ($op === 'lu') {
        $pdo->prepare('UPDATE messages SET lu = 1 WHERE id = ?')->execute([$id]);
        $notice = 'Message marqué comme lu.';
    } elseif ($op === 'supprimer') {
        $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
        $notice = 'Message supprimé.';
    }
}

$liste = $pdo !== null ? $pdo->query('SELECT * FROM messages ORDER BY recu_le DESC')->fetchAll() : [];

admin_head('Messages');
?>
<div class="admin-layout">
  <?php admin_sidebar('messages'); ?>
  <main class="admin-main">
    <div class="admin-header"><h1 class="h2">Messages de contact</h1></div>
    <?php if ($notice !== '') : ?><div class="alert alert-success"><?= icon('check-circle', 18) ?><div><?= e($notice) ?></div></div><?php endif; ?>
    <?php if ($pdo === null) : ?>
      <div class="alert alert-danger"><?= icon('x', 18) ?><div>Base de données indisponible.</div></div>
    <?php elseif (!$liste) : ?>
      <div class="admin-card"><p>Aucun message pour le moment.</p></div>
    <?php else : ?>
      <div style="display: grid; gap: 1rem;">
        <?php foreach ($liste as $m) : ?>
        <div class="admin-card" style="<?= (int) $m['lu'] === 0 ? 'border-left: 4px solid var(--accent);' : '' ?>">
          <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 0.8rem; margin-bottom: 0.8rem;">
            <div>
              <strong><?= e($m['nom']) ?></strong>
              · <a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a>
              <?php if (!empty($m['telephone'])) : ?> · <a href="tel:<?= e($m['telephone']) ?>"><?= e($m['telephone']) ?></a><?php endif; ?>
            </div>
            <span class="caption"><?= e($m['recu_le']) ?> <?= (int) $m['lu'] === 0 ? '· <strong style="color: var(--accent-strong);">Non lu</strong>' : '' ?></span>
          </div>
          <p class="badge badge-primary" style="margin-bottom: 0.6rem;"><?= e($m['sujet']) ?></p>
          <p style="white-space: pre-line; color: var(--text-2);"><?= e($m['message']) ?></p>
          <div style="display: flex; gap: 0.6rem; margin-top: 1rem;">
            <?php if ((int) $m['lu'] === 0) : ?>
            <form method="post" action="">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="op" value="lu"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
              <button class="btn btn-outline" type="submit"><?= icon('check', 15) ?> Marquer lu</button>
            </form>
            <?php endif; ?>
            <a class="btn btn-primary" href="mailto:<?= e($m['email']) ?>?subject=Re:%20<?= rawurlencode($m['sujet']) ?>"><?= icon('send', 15) ?> Répondre</a>
            <form method="post" action="" onsubmit="return confirm('Supprimer ce message ?');">
              <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
              <input type="hidden" name="op" value="supprimer"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
              <button class="btn btn-outline" type="submit" style="color: var(--danger); border-color: var(--danger);"><?= icon('trash', 15) ?> Supprimer</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>
</body>
</html>
