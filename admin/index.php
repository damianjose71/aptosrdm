<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['admin_id'])) {
    header('Location: /admin/dashboard.php');
    exit;
}

header('Location: /admin/auth/login.php');
exit;
