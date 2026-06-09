<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

requireLogin();

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
$twig = new \Twig\Environment($loader, ['cache' => false]);

echo $twig->render('ajax_car_search.html.twig', [
    'page' => 'ajax_search',
    'username' => escapeOutput($_SESSION['username'] ?? '')
]);