<?php
require_once 'config.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid CSRF token'];
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare('DELETE FROM cars WHERE id = :id');
    $stmt->execute([':id' => $id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Car deleted successfully.'];
    } else {
        $_SESSION['flash'] = ['type' => 'warning', 'msg' => 'Car not found or already deleted.'];
    }
} else {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Invalid request.'];
}

header('Location: index.php');
exit;