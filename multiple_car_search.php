<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

requireLogin();

$make = trim($_GET['make'] ?? '');
$model = trim($_GET['model'] ?? '');
$year = trim($_GET['year'] ?? '');
$color = trim($_GET['color'] ?? '');
$min_price = trim($_GET['min_price'] ?? '');
$max_price = trim($_GET['max_price'] ?? '');

$conditions = [];
$params = [];

if ($make !== '') {
    $conditions[] = "make LIKE ?";
    $params[] = "%" . $make . "%";
}

if ($model !== '') {
    $conditions[] = "model LIKE ?";
    $params[] = "%" . $model . "%";
}

if ($year !== '' && is_numeric($year)) {
    $conditions[] = "CAST(year AS CHAR) LIKE ?";
    $params[] = "%" . $year . "%";
}

if ($color !== '') {
    $conditions[] = "color LIKE ?";
    $params[] = "%" . $color . "%";
}

if ($min_price !== '' && is_numeric($min_price)) {
    $conditions[] = "price >= ?";
    $params[] = $min_price;
}

if ($max_price !== '' && is_numeric($max_price)) {
    $conditions[] = "price <= ?";
    $params[] = $max_price;
}

$sql = "SELECT * FROM cars";

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig = new \Twig\Environment($loader, ['cache' => false]);

echo $twig->render('multiple_car_search.html.twig', [
    'cars' => $cars,
    'make' => $make,
    'model' => $model,
    'year' => $year,
    'color' => $color,
    'min_price' => $min_price,
    'max_price' => $max_price
]);