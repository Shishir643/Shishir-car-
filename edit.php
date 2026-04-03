<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

requireLogin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id < 1) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM cars WHERE id = :id');
$stmt->execute([':id' => $id]);
$car = $stmt->fetch();

if (!$car) {
    $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Car not found.'];
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['csrf'] = 'Invalid CSRF token';
    }

    $car['make'] = trim($_POST['make'] ?? '');
    $car['model'] = trim($_POST['model'] ?? '');
    $car['year'] = (int) ($_POST['year'] ?? 0);
    $car['color'] = trim($_POST['color'] ?? '');
    $car['price'] = trim($_POST['price'] ?? '');
    $car['mileage'] = trim($_POST['mileage'] ?? '');

    if ($car['make'] === '') {
        $errors['make'] = 'Make is required.';
    }
    if ($car['model'] === '') {
        $errors['model'] = 'Model is required.';
    }
    if ($car['year'] < 1886 || $car['year'] > (int) date('Y') + 1) {
        $errors['year'] = 'Enter a valid year.';
    }
    if ($car['color'] === '') {
        $errors['color'] = 'Color is required.';
    }
    if (!is_numeric($car['price']) || (float) $car['price'] < 0) {
        $errors['price'] = 'Enter a valid price.';
    }
    if (!is_numeric($car['mileage']) || (int) $car['mileage'] < 0) {
        $errors['mileage'] = 'Enter a valid mileage.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'UPDATE cars
             SET make = :make, model = :model, year = :year,
                 color = :color, price = :price, mileage = :mileage
             WHERE id = :id'
        );
        $stmt->execute([
            ':make' => $car['make'],
            ':model' => $car['model'],
            ':year' => $car['year'],
            ':color' => $car['color'],
            ':price' => (float) $car['price'],
            ':mileage' => (int) $car['mileage'],
            ':id' => $id,
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Car updated successfully!'];
        header('Location: index.php');
        exit;
    }
}

$csrf_token = generateCSRFToken();

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig = new \Twig\Environment($loader, ['cache' => false]);

echo $twig->render('form.html.twig', [
    'page' => 'edit',
    'page_title' => 'Edit Car',
    'is_edit' => true,
    'car' => $car,
    'errors' => $errors,
    'csrf_token' => $csrf_token,
]);