<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

requireLogin();

$perPage = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Count all cars
$stmtCount = $pdo->query("SELECT COUNT(*) FROM cars");
$total = (int) $stmtCount->fetchColumn();

// Get normal car list only
$stmtList = $pdo->prepare("SELECT * FROM cars ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmtList->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmtList->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtList->execute();
$cars = $stmtList->fetchAll();

$totalPages = max(1, (int) ceil($total / $perPage));

// Dashboard statistics
$avgPrice = $pdo->query('SELECT AVG(price) FROM cars')->fetchColumn();
$totalCars = $pdo->query('SELECT COUNT(*) FROM cars')->fetchColumn();
$newest = $pdo->query('SELECT MAX(year) FROM cars')->fetchColumn();

$csrf_token = generateCSRFToken();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig = new \Twig\Environment($loader, ['cache' => false]);

echo $twig->render('dashboard.html.twig', [
    'page' => 'dashboard',
    'username' => escapeOutput($_SESSION['username']),
    'totalCars' => $totalCars,
    'avgPrice' => $avgPrice,
    'newest' => $newest,
    'cars' => $cars,
    'currentPage' => $page,
    'perPage' => $perPage,
    'offset' => $offset,
    'total' => $total,
    'totalPages' => $totalPages,
    'flash' => $flash,
    'csrf_token' => $csrf_token,
]);
