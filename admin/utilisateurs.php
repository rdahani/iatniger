<?php
/** Gestion des utilisateurs administrateurs et de leurs droits d'accès (cases à cocher). */

declare(strict_types=1);

require_once __DIR__ . '/_helpers.php';
require_permission('utilisateurs');

$pdo = db();
admin_ensure_permissions_column($pdo);

$action = $_GET['action'] ?? 'liste';
$notice = '';
$erreur = '';
$roles = admin_roles_meta();
$modules = admin_menu_modules();
$all_keys = admin_all_perm_keys();

/** Normalise les permissions POST (cases cochées). */
function utilisateurs_perms_from_post(): array
{
    $raw = $_POST['perms'] ?? [];
    if (!is_array($raw)) {
        return ['dashboard'];
    }
    $allowed = admin_all_perm_keys();
    $perms = [];
    foreach ($raw as $p) {
        $p = (string) $p;
        if ($p === '*' || in_array($p, $allowed, true)) {
            $perms[] = $p;
        }
    }
    $perms = array_values(array_unique($perms));
    if (in_array('*', $perms, true)) {
        return ['*'];
    }
    if ($perms === []) {
        $perms = ['dashboard'];
    } elseif (!in_array('dashboard', $perms, true)) {
        array_unshift($perms, 'dashboard');
    }
    return $perms;
}

if ($pdo !== null && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf'] ?? null)) {
    $op = $_POST['op'] ?? '';

    if ($op === 'enregistrer') {
        $id = (int) ($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $nom = trim($_POST['nom'] ?? '');
        $role = trim($_POST['role'] ?? 'personnalise');
        $password = (string) ($_POST['password'] ?? '');
        $password2 = (string) ($_POST['password2'] ?? '');
        $perms = utilisateurs_perms_from_post();
        $perms_json = json_encode($perms, JSON_UNESCAPED_UNICODE);

        if ($username === '' || $nom === '') {
            $erreur = 'Identifiant et nom sont obligatoires.';
            $action = $id > 0 ? 'editer' : 'nouvelle';
        } elseif (!array_key_exists($role, $roles)) {
            $erreur = 'Rôle invalide.';
            $action = $id > 0 ? 'editer' : 'nouvelle';
        } elseif ($id === 0 && $password === '') {
            $erreur = 'Le mot de passe est obligatoire pour un nouvel utilisateur.';
            $action = 'nouvelle';
        } elseif ($password !== '' && strlen($password) < 8) {
            $erreur = 'Le mot de passe doit contenir au moins 8 caractères.';
            $action = $id > 0 ? 'editer' : 'nouvelle';
        } elseif ($password !== '' && $password !== $password2) {
            $erreur = 'La confirmation du mot de passe ne correspond pas.';
            $action = $id > 0 ? 'editer' : 'nouvelle';
        } else {
            try {
                if ($id > 0) {
                    if ($password !== '') {
                        $st = $pdo->prepare('UPDATE users SET username=?, nom=?, role=?, permissions=?, password_hash=? WHERE id=?');
                        $st->execute([$username, $nom, $role, $perms_json, password_hash($password, PASSWORD_DEFAULT), $id]);
                    } else {
                        $st = $pdo->prepare('UPDATE users SET username=?, nom=?, role=?, permissions=? WHERE id=?');
                        $st->execute([$username, $nom, $role, $perms_json, $id]);
                    }
                    if ($id === (int) $_SESSION['admin_id']) {
                        admin_load_session_user([
                            'id' => $id,
                            'nom' => $nom,
                            'role' => $role,
                            'permissions' => $perms_json,
                        ]);
                    }
                    $notice = 'Utilisateur et droits mis à jour.';
                } else {
                    $st = $pdo->prepare('INSERT INTO users (username, password_hash, nom, role, permissions) VALUES (?,?,?,?,?)');
                    $st->execute([$username, password_hash($password, PASSWORD_DEFAULT), $nom, $role, $perms_json]);
                    $notice = 'Utilisateur créé.';
                }
                $action = 'liste';
            } catch (PDOException $e) {
                $erreur = str_contains($e->getMessage(), 'username') || str_contains($e->getMessage(), 'Duplicate')
                    ? 'Cet identifiant est déjà utilisé.'
                    : 'Erreur : ' . $e->getMessage();
                $action = $id > 0 ? 'editer' : 'nouvelle';
            }
        }
    } elseif ($op === 'supprimer') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === (int) $_SESSION['admin_id']) {
            $erreur = 'Vous ne pouvez pas supprimer votre propre compte.';
        } else {
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
            $notice = 'Utilisateur supprimé.';
        }
    }
}

$edition = null;
$perms_cochees = ['dashboard'];
if (($action === 'editer' || $action === 'nouvelle') && $pdo !== null) {
    if ($action === 'editer') {
        $st = $pdo->prepare('SELECT id, username, nom, role, permissions FROM users WHERE id = ?');
        $st->execute([(int) ($_GET['id'] ?? $_POST['id'] ?? 0)]);
        $edition = $st->fetch() ?: null;
        if ($edition === null) {
            $action = 'liste';
        } else {
            $perms_cochees = admin_resolve_perms($edition['role'], $edition['permissions'] ?? null);
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['perms'])) {
        $perms_cochees = utilisateurs_perms_from_post();
    }
}

$liste = [];
if ($pdo !== null && $action === 'liste') {
    $liste = $pdo->query('SELECT id, username, nom, role, permissions, cree_le FROM users ORDER BY nom ASC')->fetchAll();
}

/* Données JS pour préremplir selon le rôle */
$presets_js = [];
foreach ($roles as $rk => $rm) {
    $presets_js[$rk] = $rm['perms'];
}

admin_head('Utilisateurs & droits');
?>
<div class="admin-layout">
  <?php admin_sidebar('utilisateurs'); ?>
  <main class="admin-main">
    <?php admin_flash($notice, $erreur); ?>

    <?php if ($pdo === null) : ?>
      <div class="alert alert-danger"><?= icon('x', 18) ?><div>Base de données indisponible.</div></div>

    <?php elseif ($action === 'nouvelle' || $action === 'editer') : ?>
      <div class="admin-header">
        <h1 class="h2"><?= $edition ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' ?></h1>
        <a class="btn btn-outline" href="<?= url('admin/utilisateurs.php') ?>">← Retour à la liste</a>
      </div>
      <div class="admin-card">
        <form method="post" action="<?= url('admin/utilisateurs.php') ?>" id="form-user-droits">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="op" value="enregistrer">
          <input type="hidden" name="id" value="<?= (int) ($edition['id'] ?? 0) ?>">
          <div class="form-grid">
            <div class="form-field">
              <label for="u-username">Identifiant *</label>
              <input type="text" id="u-username" name="username" required autocomplete="off"
                     value="<?= e($edition['username'] ?? $_POST['username'] ?? '') ?>">
            </div>
            <div class="form-field">
              <label for="u-nom">Nom affiché *</label>
              <input type="text" id="u-nom" name="nom" required
                     value="<?= e($edition['nom'] ?? $_POST['nom'] ?? '') ?>">
            </div>
            <div class="form-field full">
              <label for="u-role">Modèle de rôle</label>
              <select id="u-role" name="role" required>
                <?php
                $role_sel = $edition['role'] ?? $_POST['role'] ?? 'communication';
                foreach ($roles as $rk => $rm) : ?>
                <option value="<?= e($rk) ?>" <?= $role_sel === $rk ? 'selected' : '' ?>><?= e($rm['label']) ?> — <?= e($rm['desc']) ?></option>
                <?php endforeach; ?>
              </select>
              <p class="caption" style="margin-top:0.45rem;">Choisir un modèle (Administrateur, Communication, Scolarité…) coche automatiquement les cases. Vous pouvez ensuite ajuster.</p>
            </div>
            <div class="form-field">
              <label for="u-pass">Mot de passe <?= $edition ? '(laisser vide pour ne pas changer)' : '*' ?></label>
              <input type="password" id="u-pass" name="password" autocomplete="new-password" <?= $edition ? '' : 'required' ?> minlength="8">
            </div>
            <div class="form-field">
              <label for="u-pass2">Confirmer le mot de passe</label>
              <input type="password" id="u-pass2" name="password2" autocomplete="new-password" <?= $edition ? '' : 'required' ?> minlength="8">
            </div>
          </div>

          <div class="admin-perms-panel" id="admin-perms-panel">
            <div class="admin-perms-head">
              <h2 class="h3">Accès aux sections du menu</h2>
              <div class="admin-perms-actions">
                <button type="button" class="btn btn-outline" id="perms-tout"><?= icon('check', 14) ?> Tout cocher</button>
                <button type="button" class="btn btn-outline" id="perms-rien"><?= icon('x', 14) ?> Tout décocher</button>
              </div>
            </div>
            <label class="admin-perm-full">
              <input type="checkbox" name="perms[]" value="*" id="perm-star" <?= in_array('*', $perms_cochees, true) ? 'checked' : '' ?>>
              <span><strong>Accès complet</strong> — toutes les sections (équivalent Administrateur)</span>
            </label>
            <?php
            $has_star = in_array('*', $perms_cochees, true);
            foreach ($modules as $groupe => $items) : ?>
            <fieldset class="admin-perms-group">
              <legend><?= e($groupe) ?></legend>
              <div class="admin-perms-grid">
                <?php foreach ($items as $key => $label) :
                    $checked = $has_star || in_array($key, $perms_cochees, true);
                    ?>
                <label class="admin-perm-item">
                  <input type="checkbox" name="perms[]" value="<?= e($key) ?>" class="perm-module" data-perm="<?= e($key) ?>"
                         <?= $checked ? 'checked' : '' ?> <?= $has_star ? 'disabled' : '' ?>>
                  <span><?= e($label) ?></span>
                </label>
                <?php endforeach; ?>
              </div>
            </fieldset>
            <?php endforeach; ?>
          </div>

          <button class="btn btn-primary btn-lg" type="submit" style="margin-top: 1.4rem;">Enregistrer</button>
        </form>
      </div>

      <script>
      (function () {
        var presets = <?= json_encode($presets_js, JSON_UNESCAPED_UNICODE) ?>;
        var roleSelect = document.getElementById('u-role');
        var star = document.getElementById('perm-star');
        var modules = Array.prototype.slice.call(document.querySelectorAll('.perm-module'));
        var applyFromRole = true;

        function setModulesEnabled(enabled) {
          modules.forEach(function (cb) {
            cb.disabled = !enabled;
            if (!enabled && star.checked) cb.checked = true;
          });
        }

        function applyPreset(role) {
          var list = presets[role] || ['dashboard'];
          var full = list.indexOf('*') !== -1;
          star.checked = full;
          modules.forEach(function (cb) {
            cb.checked = full || list.indexOf(cb.getAttribute('data-perm')) !== -1;
          });
          setModulesEnabled(!full);
          if (role !== 'personnalise') {
            /* déjà appliqué */
          }
        }

        roleSelect.addEventListener('change', function () {
          applyFromRole = true;
          applyPreset(roleSelect.value);
        });

        star.addEventListener('change', function () {
          setModulesEnabled(!star.checked);
          if (star.checked) {
            modules.forEach(function (cb) { cb.checked = true; });
            if (roleSelect.value !== 'admin') roleSelect.value = 'admin';
          } else {
            roleSelect.value = 'personnalise';
          }
        });

        modules.forEach(function (cb) {
          cb.addEventListener('change', function () {
            if (star.checked) return;
            roleSelect.value = 'personnalise';
          });
        });

        document.getElementById('perms-tout').addEventListener('click', function () {
          star.checked = true;
          setModulesEnabled(false);
          modules.forEach(function (cb) { cb.checked = true; });
          roleSelect.value = 'admin';
        });
        document.getElementById('perms-rien').addEventListener('click', function () {
          star.checked = false;
          setModulesEnabled(true);
          modules.forEach(function (cb) {
            cb.checked = cb.getAttribute('data-perm') === 'dashboard';
          });
          roleSelect.value = 'personnalise';
        });

        /* Avant envoi : réactiver les cases désactivées pour qu'elles soient postées */
        document.getElementById('form-user-droits').addEventListener('submit', function () {
          modules.forEach(function (cb) { cb.disabled = false; });
        });
      })();
      </script>

    <?php else : ?>
      <div class="admin-header">
        <h1 class="h2">Utilisateurs &amp; droits</h1>
        <a class="btn btn-primary" href="<?= url('admin/utilisateurs.php?action=nouvelle') ?>"><?= icon('plus', 16) ?> Nouvel utilisateur</a>
      </div>

      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th scope="col">Nom</th>
              <th scope="col">Identifiant</th>
              <th scope="col">Rôle</th>
              <th scope="col">Sections</th>
              <th scope="col">Créé le</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$liste) : ?>
            <tr><td colspan="6">Aucun utilisateur.</td></tr>
            <?php endif; ?>
            <?php foreach ($liste as $u) :
                $up = admin_resolve_perms($u['role'], $u['permissions'] ?? null);
                $nb = in_array('*', $up, true) ? 'Toutes' : (count($up) . ' section' . (count($up) > 1 ? 's' : ''));
                ?>
            <tr>
              <td><strong><?= e($u['nom']) ?></strong><?= (int) $u['id'] === (int) $_SESSION['admin_id'] ? ' <span class="badge badge-primary">Vous</span>' : '' ?></td>
              <td><?= e($u['username']) ?></td>
              <td><span class="badge badge-accent"><?= e($roles[$u['role']]['label'] ?? $u['role']) ?></span></td>
              <td class="caption"><?= e($nb) ?></td>
              <td><?= e(date_fr(substr((string) $u['cree_le'], 0, 10))) ?></td>
              <td style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                <a class="icon-btn" href="<?= url('admin/utilisateurs.php?action=editer&id=' . (int) $u['id']) ?>" title="Modifier"><?= icon('edit', 17) ?></a>
                <?php if ((int) $u['id'] !== (int) $_SESSION['admin_id']) : ?>
                <form method="post" action="<?= url('admin/utilisateurs.php') ?>" onsubmit="return confirm('Supprimer cet utilisateur ?');" style="display:inline;">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="op" value="supprimer">
                  <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                  <button class="icon-btn" type="submit" title="Supprimer"><?= icon('trash', 17) ?></button>
                </form>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>
</div>
<?php admin_foot(); ?>
