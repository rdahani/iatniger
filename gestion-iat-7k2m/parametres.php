<?php
/** Paramètres du site : réglages CMS (site_settings) + mot de passe de l'admin connecté. */

declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';
require_permission('parametres');

$pdo = admin_require_cms();
$notice = '';
$erreur = '';

$groupes_labels = [
    'identite' => 'Identité du site',
    'contact' => 'Coordonnées',
    'footer' => 'Pied de page',
    'general' => 'Général',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $op = $_POST['op'] ?? '';

    if ($op === 'enregistrer_parametres' && $pdo !== null) {
        $valeurs = $_POST['settings'] ?? [];
        try {
            $st = $pdo->prepare('UPDATE site_settings SET valeur = ? WHERE cle = ?');
            $n = 0;
            foreach ($valeurs as $cle => $valeur) {
                $st->execute([trim((string) $valeur), (string) $cle]);
                $n++;
            }
            $notice = $n . ' paramètre(s) mis à jour.';
        } catch (PDOException $e) {
            $erreur = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    } elseif ($op === 'changer_mdp') {
        $actuel = $_POST['mdp_actuel'] ?? '';
        $nouveau = $_POST['mdp_nouveau'] ?? '';
        $confirmation = $_POST['mdp_confirmation'] ?? '';
        $db = db();
        if ($db === null) {
            $erreur = 'Base de données indisponible.';
        } else {
            $st = $db->prepare('SELECT * FROM users WHERE id = ?');
            $st->execute([(int) ($_SESSION['admin_id'] ?? 0)]);
            $user = $st->fetch();
            if (!$user || !password_verify($actuel, $user['password_hash'])) {
                $erreur = 'Mot de passe actuel incorrect.';
            } elseif (mb_strlen($nouveau) < 8) {
                $erreur = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
            } elseif ($nouveau !== $confirmation) {
                $erreur = 'La confirmation ne correspond pas au nouveau mot de passe.';
            } else {
                $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                    ->execute([password_hash($nouveau, PASSWORD_DEFAULT), (int) $user['id']]);
                $notice = 'Mot de passe mis à jour avec succès.';
            }
        }
    }
}

$settings_par_groupe = [];
if ($pdo !== null) {
    $rows = $pdo->query('SELECT * FROM site_settings ORDER BY groupe, label')->fetchAll();
    foreach ($rows as $row) {
        $settings_par_groupe[$row['groupe'] ?: 'general'][] = $row;
    }
}

admin_head('Paramètres du site');
?>
<div class="admin-layout">
  <?php admin_sidebar('parametres'); ?>
  <main class="admin-main">
    <div class="admin-header"><h1 class="h2">Paramètres du site</h1></div>

    <?php admin_flash($notice, $erreur); ?>

    <?php if ($pdo === null) : ?>
      <div class="alert alert-danger">
        <?= icon('alert-triangle', 18) ?>
        <div>Le CMS n'est pas encore installé (ou la base de données est indisponible). <a href="<?= url('admin/install-cms.php') ?>">Installer le CMS</a> pour pouvoir modifier les paramètres du site.</div>
      </div>
    <?php else : ?>

      <?php if (!$settings_par_groupe) : ?>
      <div class="admin-card"><p>Aucun paramètre enregistré pour le moment. <a href="<?= url('admin/install-cms.php') ?>">Lancez l'installation du CMS</a>.</p></div>
      <?php else : ?>
      <form method="post" action="<?= url('admin/parametres.php') ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="op" value="enregistrer_parametres">

        <?php foreach ($settings_par_groupe as $groupe => $items) : ?>
        <div class="admin-card" style="margin-bottom: 1.6rem;">
          <h2 class="h3" style="margin-bottom: 1.2rem;"><?= e($groupes_labels[$groupe] ?? ucfirst($groupe)) ?></h2>
          <div class="form-grid">
            <?php foreach ($items as $it) :
                $long = mb_strlen((string) $it['valeur']) > 120 || str_contains((string) $it['cle'], 'texte') || str_contains((string) $it['cle'], 'mention'); ?>
            <div class="form-field <?= $long ? 'full' : '' ?>">
              <label for="set-<?= e($it['cle']) ?>"><?= e($it['label'] !== '' ? $it['label'] : $it['cle']) ?></label>
              <?php if ($long) : ?>
              <textarea id="set-<?= e($it['cle']) ?>" name="settings[<?= e($it['cle']) ?>]" style="min-height: 90px;"><?= e($it['valeur']) ?></textarea>
              <?php else : ?>
              <input type="text" id="set-<?= e($it['cle']) ?>" name="settings[<?= e($it['cle']) ?>]" value="<?= e($it['valeur']) ?>">
              <?php endif; ?>
              <span class="caption">Clé : <code><?= e($it['cle']) ?></code></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>

        <button class="btn btn-primary btn-lg" type="submit"><?= icon('check', 18) ?> Enregistrer les paramètres</button>
      </form>
      <?php endif; ?>
    <?php endif; ?>

    <div class="admin-card" style="margin-top: 2rem; max-width: 560px;">
      <h2 class="h3" style="margin-bottom: 1rem;">Changer mon mot de passe</h2>
      <form method="post" action="<?= url('admin/parametres.php') ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="op" value="changer_mdp">
        <div class="form-grid" style="grid-template-columns: 1fr;">
          <div class="form-field">
            <label for="mdp-actuel">Mot de passe actuel</label>
            <input type="password" id="mdp-actuel" name="mdp_actuel" required autocomplete="current-password">
          </div>
          <div class="form-field">
            <label for="mdp-nouveau">Nouveau mot de passe</label>
            <input type="password" id="mdp-nouveau" name="mdp_nouveau" required minlength="8" autocomplete="new-password">
          </div>
          <div class="form-field">
            <label for="mdp-confirmation">Confirmer le nouveau mot de passe</label>
            <input type="password" id="mdp-confirmation" name="mdp_confirmation" required minlength="8" autocomplete="new-password">
          </div>
        </div>
        <button class="btn btn-outline btn-lg" type="submit" style="margin-top: 1.2rem;"><?= icon('lock', 18) ?> Changer le mot de passe</button>
      </form>
    </div>
  </main>
</div>
</body>
</html>
