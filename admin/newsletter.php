<?php
/** Gestion des abonnés à la newsletter : liste, suppression, export CSV. */

declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';
require_permission('newsletter');

$pdo = db();
$notice = '';
$erreur = '';

/* ----- Export CSV ----- */
if ($pdo !== null && ($_GET['export'] ?? '') === 'csv') {
    $abonnes = $pdo->query('SELECT * FROM newsletter ORDER BY inscrit_le DESC')->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="newsletter-iatniger-' . date('Y-m-d') . '.csv"');
    echo "\xEF\xBB\xBF"; // BOM UTF-8 pour Excel
    $out = fopen('php://output', 'w');
    fputcsv($out, ['E-mail', 'Inscrit le'], ';');
    foreach ($abonnes as $a) {
        fputcsv($out, [$a['email'], $a['inscrit_le']], ';');
    }
    fclose($out);
    exit;
}

if ($pdo !== null && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $op = $_POST['op'] ?? '';
    if ($op === 'supprimer') {
        $pdo->prepare('DELETE FROM newsletter WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
        $notice = 'Abonné supprimé.';
    }
}

$liste = $pdo !== null ? $pdo->query('SELECT * FROM newsletter ORDER BY inscrit_le DESC')->fetchAll() : [];

admin_head('Newsletter');
?>
<div class="admin-layout">
  <?php admin_sidebar('newsletter'); ?>
  <main class="admin-main">
    <div class="admin-header">
      <h1 class="h2">Abonnés à la newsletter</h1>
      <?php if ($liste) : ?>
      <a class="btn btn-outline" href="<?= url('admin/newsletter.php?export=csv') ?>"><?= icon('download', 16) ?> Exporter en CSV</a>
      <?php endif; ?>
    </div>

    <?php admin_flash($notice, $erreur); ?>

    <?php if ($pdo === null) : ?>
      <div class="alert alert-danger"><?= icon('x', 18) ?><div>Base de données indisponible.</div></div>
    <?php elseif (!$liste) : ?>
      <div class="admin-card"><p>Aucun abonné pour le moment.</p></div>
    <?php else : ?>
      <p class="caption" style="margin-bottom: 1rem;"><?= count($liste) ?> abonné<?= count($liste) > 1 ? 's' : '' ?>.</p>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th scope="col">E-mail</th><th scope="col">Inscrit le</th><th scope="col">Actions</th></tr></thead>
          <tbody>
            <?php foreach ($liste as $a) : ?>
            <tr>
              <td><a href="mailto:<?= e($a['email']) ?>"><?= e($a['email']) ?></a></td>
              <td><?= e((string) $a['inscrit_le']) ?></td>
              <td>
                <form method="post" action="<?= url('admin/newsletter.php') ?>" onsubmit="return confirm('Désinscrire définitivement cet e-mail ?');">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="op" value="supprimer">
                  <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                  <button class="icon-btn" type="submit" aria-label="Supprimer" title="Supprimer" style="color: var(--danger);"><?= icon('trash', 17) ?></button>
                </form>
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
