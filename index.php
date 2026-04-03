<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

requireLogin();

$perPage = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$offset = ($page - 1) * $perPage;

$where = $search ? 'WHERE make LIKE :q OR model LIKE :q OR color LIKE :q' : '';
$params = $search ? [':q' => "%$search%"] : [];

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM cars $where");
$stmtCount->execute($params);
$total = (int) $stmtCount->fetchColumn();

$stmtList = $pdo->prepare("SELECT * FROM cars $where ORDER BY id DESC LIMIT :limit OFFSET :offset");
if ($search) {
    $stmtList->bindValue(':q', "%$search%", PDO::PARAM_STR);
}
$stmtList->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmtList->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtList->execute();
$cars = $stmtList->fetchAll();

$totalPages = max(1, (int) ceil($total / $perPage));

$availableMakes = $pdo->query("SELECT DISTINCT make FROM cars ORDER BY make")->fetchAll(PDO::FETCH_COLUMN);
$availableYears = $pdo->query("SELECT DISTINCT year FROM cars ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);
$availableColors = $pdo->query("SELECT DISTINCT color FROM cars ORDER BY color")->fetchAll(PDO::FETCH_COLUMN);

$avgPrice = $pdo->query('SELECT AVG(price) FROM cars')->fetchColumn();
$totalCars = $pdo->query('SELECT COUNT(*) FROM cars')->fetchColumn();
$newest = $pdo->query('SELECT MAX(year) FROM cars')->fetchColumn();

$csrf_token = generateCSRFToken();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig = new \Twig\Environment($loader, ['cache' => false]);

// Add custom filters
$twig->addFilter(new \Twig\TwigFilter('urlencode', function ($string) {
    return urlencode($string);
}));

echo $twig->render('dashboard.html.twig', [
    'page' => 'dashboard',
    'username' => escapeOutput($_SESSION['username']),
    'totalCars' => $totalCars,
    'avgPrice' => $avgPrice,
    'newest' => $newest,
    'cars' => $cars,
    'availableMakes' => $availableMakes,
    'availableYears' => $availableYears,
    'availableColors' => $availableColors,
    'search' => $search,
    'currentPage' => $page,
    'perPage' => $perPage,
    'offset' => $offset,
    'total' => $total,
    'totalPages' => $totalPages,
    'flash' => $flash,
    'csrf_token' => $csrf_token,
]);
