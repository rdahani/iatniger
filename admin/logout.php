<?php
/** Déconnexion de l'espace admin. */

require_once __DIR__ . '/../config/config.php';

$_SESSION = [];
session_destroy();
header('Location: ' . url('admin/index.php'));
exit;
