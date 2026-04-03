<?php
// ==========================================
// FILE: test_navigation.php
// Place this in your root folder to test BASE_URL
// ==========================================
require_once 'config/constants.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Navigation Test</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        .success { color: green; font-weight: bold; }
        .info { background: #f0f0f0; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Navigation Configuration Test</h1>
    
    <div class="info">
        <strong>BASE_URL:</strong> <?= BASE_URL ?><br>
        <strong>Document Root:</strong> <?= $_SERVER['DOCUMENT_ROOT'] ?><br>
        <strong>Script Name:</strong> <?= $_SERVER['SCRIPT_NAME'] ?>
    </div>

    <h2>Testing Navigation Links:</h2>
    <ul>
        <li><a href="<?= BASE_URL ?>index.php">Login Page</a> <?= file_exists($_SERVER['DOCUMENT_ROOT'] . parse_url(BASE_URL, PHP_URL_PATH) . 'index.php') ? '<span class="success">✓</span>' : '✗' ?></li>
        <li><a href="<?= BASE_URL ?>user/home.php">Home Page</a> <?= file_exists($_SERVER['DOCUMENT_ROOT'] . parse_url(BASE_URL, PHP_URL_PATH) . 'user/home.php') ? '<span class="success">✓</span>' : '✗' ?></li>
        <li><a href="<?= BASE_URL ?>user/fir/file_fir.php">File FIR</a> <?= file_exists($_SERVER['DOCUMENT_ROOT'] . parse_url(BASE_URL, PHP_URL_PATH) . 'user/fir/file_fir.php') ? '<span class="success">✓</span>' : '✗' ?></li>
        <li><a href="<?= BASE_URL ?>user/pages/about.php">About Page</a> <?= file_exists($_SERVER['DOCUMENT_ROOT'] . parse_url(BASE_URL, PHP_URL_PATH) . 'user/pages/about.php') ? '<span class="success">✓</span>' : '✗' ?></li>
    </ul>

    <h2>Manual Testing Instructions:</h2>
    <ol>
        <li>Click on "Login Page" and then login</li>
        <li>Navigate to different pages using the navbar</li>
        <li>Click "Home" from any page - it should work without errors</li>
        <li>Check browser console for any 404 errors</li>
    </ol>

    <p class="success">✓ If all links work, navigation issue is resolved!</p>
</body>
</html>