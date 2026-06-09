<?php
require_once 'config.php';
requireLogin();

$keyword = trim($_GET['keyword'] ?? '');

if ($keyword === '') {
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, make, model, year, color, price, mileage
    FROM cars
    WHERE make LIKE ? OR model LIKE ? OR color LIKE ?
    ORDER BY id DESC
");

$searchTerm = "%" . $keyword . "%";
$stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
$cars = $stmt->fetchAll();

if ($cars) {
    echo '<table border="1" cellpadding="8" cellspacing="0">';
    echo '<tr>';
    echo '<th>ID</th>';
    echo '<th>Make</th>';
    echo '<th>Model</th>';
    echo '<th>Year</th>';
    echo '<th>Color</th>';
    echo '<th>Price</th>';
    echo '<th>Mileage</th>';
    echo '</tr>';

    foreach ($cars as $car) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($car['id']) . '</td>';
        echo '<td>' . htmlspecialchars($car['make']) . '</td>';
        echo '<td>' . htmlspecialchars($car['model']) . '</td>';
        echo '<td>' . htmlspecialchars($car['year']) . '</td>';
        echo '<td>' . htmlspecialchars($car['color']) . '</td>';
        echo '<td>' . htmlspecialchars($car['price']) . '</td>';
        echo '<td>' . htmlspecialchars($car['mileage']) . '</td>';
        echo '</tr>';
    }

    echo '</table>';
} else {
    echo '<p>No cars found.</p>';
}