<?php
session_start();

$lang = $_SESSION['lang'] ?? 'es';
if (isset($_GET['lang']) && in_array($_GET['lang'], ['es', 'en'], true)) {
    $lang = $_GET['lang'];
}
$_SESSION['lang'] = $lang;

$langFiles = [
    'es' => __DIR__ . '/es.php',
    'en' => __DIR__ . '/en.php',
];

$strings = [];
if (file_exists($langFiles[$lang])) {
    $strings = require $langFiles[$lang];
}

function __($key) {
    global $strings;
    return $strings[$key] ?? $key;
}
