<?php
declare(strict_types=1);

/**
 * Reserva del Mar - Panel de Administración
 * Configuracion de la conexion PDO a la base de datos (Hostinger - MySQL)
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'u936479215_adminrdm');
define('DB_USER', 'u936479215_admin_rdm');
define('DB_PASS', 'Nies3347*');
define('DB_CHARSET', 'utf8mb4');

function getPDO(): PDO
{
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    return new PDO($dsn, DB_USER, DB_PASS, $options);
}

function registrarAuditoria(PDO $pdo, int $usuarioId, string $accion, string $descripcion): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO historial_auditoria (usuario_id, accion, descripcion, ip_origen, fecha_hora) VALUES (?, ?, ?, ?, NOW())'
    );
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt->execute([$usuarioId, $accion, $descripcion, $ip]);
}
