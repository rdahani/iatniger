<?php
/** Inscription newsletter (POST depuis le footer) puis retour à la page d'origine. */

require_once __DIR__ . '/config/config.php';

$retour = $_SERVER['HTTP_REFERER'] ?? url();
/* Sécurité : ne rediriger que vers le site lui-même. */
if (strpos($retour, SITE_URL) !== 0) {
    $retour = url();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $honeypot = trim($_POST['site_web'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

    if ($honeypot === '' && csrf_check($_POST['csrf'] ?? null) && $email) {
        $pdo = db();
        if ($pdo !== null) {
            try {
                $st = $pdo->prepare('INSERT IGNORE INTO newsletter (email) VALUES (?)');
                $st->execute([$email]);
            } catch (PDOException $e) {
                // Silencieux : l'inscription newsletter ne doit jamais bloquer la navigation.
            }
        }
    }
}

header('Location: ' . $retour . (str_contains($retour, '?') ? '&' : '?') . 'newsletter=merci');
exit;
