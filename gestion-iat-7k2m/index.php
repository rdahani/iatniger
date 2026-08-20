<?php
/** Connexion à l'espace d'administration. */

require_once __DIR__ . '/auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . url('admin/dashboard.php'));
    exit;
}

/** Tables de sécurité : tentatives échouées et journal des connexions. */
function login_securite_tables(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        ip VARCHAR(45) NOT NULL,
        username VARCHAR(120) NOT NULL,
        essai_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ip (ip, essai_le),
        INDEX idx_user (username, essai_le)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_journal (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        username VARCHAR(120) NOT NULL,
        ip VARCHAR(45) NOT NULL,
        connecte_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_date (connecte_le)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $pdo = db();

    if (!csrf_check($_POST['csrf'] ?? null)) {
        $erreur = 'Session expirée, merci de réessayer.';
    } elseif ($pdo === null) {
        $erreur = "Base de données indisponible. Vérifiez que MySQL est démarré et que la base « iatniger » est importée.";
    } else {
        try {
            login_securite_tables($pdo);

            /* Purge des tentatives anciennes (> 24 h). */
            $pdo->exec("DELETE FROM login_attempts WHERE essai_le < (NOW() - INTERVAL 1 DAY)");

            /* Verrouillage : 5 échecs en 15 minutes pour cette IP ou cet identifiant. */
            $st = $pdo->prepare('SELECT COUNT(*) FROM login_attempts
                WHERE (ip = ? OR username = ?) AND essai_le > (NOW() - INTERVAL 15 MINUTE)');
            $st->execute([$ip, $username]);
            $echecs = (int) $st->fetchColumn();

            if ($echecs >= 5) {
                $erreur = 'Trop de tentatives échouées. Réessayez dans 15 minutes.';
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

                    /* Réinitialise le compteur et journalise la connexion. */
                    $pdo->prepare('DELETE FROM login_attempts WHERE ip = ? OR username = ?')->execute([$ip, $username]);
                    $pdo->prepare('INSERT INTO login_journal (user_id, username, ip) VALUES (?,?,?)')
                        ->execute([(int) $user['id'], (string) $user['username'], $ip]);

                    header('Location: ' . url('admin/dashboard.php'));
                    exit;
                }
                /* Échec : enregistre la tentative et ralentit la réponse. */
                $pdo->prepare('INSERT INTO login_attempts (ip, username) VALUES (?,?)')->execute([$ip, $username]);
                sleep(1);
                $erreur = 'Identifiants incorrects.';
            }
        } catch (PDOException $e) {
            $erreur = 'Erreur technique pendant la connexion. Réessayez.';
        }
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
