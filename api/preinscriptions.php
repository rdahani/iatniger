<?php
/**
 * API préinscriptions web — consommée par SoftIAT (HTTPS, clé partagée).
 *
 * GET  ?action=list|get|counts&id=
 * POST action=traite&id=  (+ clé)
 * Header : X-Preinscriptions-Key
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once dirname(__DIR__) . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$key = (string) (
    $_SERVER['HTTP_X_PREINSCRIPTIONS_KEY']
    ?? $_GET['key']
    ?? $_POST['key']
    ?? ''
);
$expected = defined('PREINSCRIPTIONS_API_KEY') ? (string) PREINSCRIPTIONS_API_KEY : '';

if ($expected === '' || !hash_equals($expected, $key)) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Clé API invalide ou absente.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = db();
if ($pdo === null) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'Base de données indisponible.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$action = (string) ($_POST['action'] ?? $_GET['action'] ?? 'list');

try {
    if ($action === 'list') {
        $order = (($_GET['order'] ?? '') === 'date') ? 'recu_le DESC' : 'traite ASC, recu_le DESC';
        $rows = $pdo->query("SELECT * FROM preinscriptions ORDER BY $order")->fetchAll();
        echo json_encode(['ok' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'get') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ID invalide.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $st = $pdo->prepare('SELECT * FROM preinscriptions WHERE id = ?');
        $st->execute([$id]);
        $row = $st->fetch();
        if (!$row) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Préinscription introuvable.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        echo json_encode(['ok' => true, 'data' => $row], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'counts') {
        $total = (int) $pdo->query('SELECT COUNT(*) FROM preinscriptions')->fetchColumn();
        $pending = (int) $pdo->query('SELECT COUNT(*) FROM preinscriptions WHERE traite = 0')->fetchColumn();
        echo json_encode(['ok' => true, 'data' => ['total' => $total, 'pending' => $pending]], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'traite' && $method === 'POST') {
        $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'ID invalide.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $st = $pdo->prepare('UPDATE preinscriptions SET traite = 1 WHERE id = ?');
        $st->execute([$id]);
        echo json_encode(['ok' => true, 'updated' => $st->rowCount() > 0], JSON_UNESCAPED_UNICODE);
        exit;
    }

    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Action inconnue.'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Erreur serveur.'], JSON_UNESCAPED_UNICODE);
}
