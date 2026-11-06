<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Location: /admin.php?panel=nuevo');
exit;
