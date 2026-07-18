<?php
/** Connexion à l'espace d'administration. */

require_once __DIR__ . '/auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . url('admin/dashboard.php'));
    exit;
}

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* Anti force brute simple : temporisation progressive par session. */
    $_SESSION['login_essais'] = ($_SESSION['login_essais'] ?? 0) + 1;
    if ($_SESSION['login_essais'] > 5) {
        sleep(2);
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $pdo = db();

    if (!csrf_check($_POST['csrf'] ?? null)) {
        $erreur = 'Session expirée, merci de réessayer.';
    } elseif ($pdo === null) {
        $erreur = "Base de données indisponible. Vérifiez que MySQL est démarré et que la base « iatniger » est importée.";
    } else {
        $st = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $st->execute([$username]);
        $user = $st->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            admin_ensure_permissions_column($pdo);
            /* Recharger au cas où la colonne vient d'être ajoutée */
            $st2 = $pdo->prepare('SELECT * FROM users WHERE id = ?');
            $st2->execute([(int) $user['id']]);
            $user = $st2->fetch() ?: $user;
            admin_load_session_user($user);
            $_SESSION['login_essais'] = 0;
            header('Location: ' . url('admin/dashboard.php'));
            exit;
        }
        $erreur = 'Identifiants incorrects.';
    }
}

admin_head('Connexion');
?>
<div class="login-wrap">
  <div class="login-card">
    <div class="brand"><img src="<?= asset('img/logoiat.png') ?>" alt="Logo IAT Niger" width="150" height="57"></div>
    <h1 class="h3 text-center" style="margin-bottom: 1.6rem;">Espace d'administration</h1>
    <?php if ($erreur !== '') : ?>
      <div class="alert alert-danger"><?= icon('x', 18) ?><div><?= e($erreur) ?></div></div>
    <?php endif; ?>
    <form method="post" action="">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div class="form-grid" style="grid-template-columns: 1fr;">
        <div class="form-field">
          <label for="username">Identifiant</label>
          <input type="text" id="username" name="username" required autocomplete="username" autofocus>
        </div>
        <div class="form-field">
          <label for="password">Mot de passe</label>
          <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
      </div>
      <button class="btn btn-primary btn-lg" type="submit" style="width: 100%; margin-top: 1.4rem;"><?= icon('lock', 18) ?> Se connecter</button>
    </form>
    <p class="caption text-center" style="margin-top: 1.4rem;"><a href="<?= url() ?>">← Retour au site</a></p>
  </div>
</div>
</body>
</html>
