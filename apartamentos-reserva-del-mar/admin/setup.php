<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';

$pdo = getPDO();

// ---------------------------------------------------------------
// Tabla: usuarios
// ---------------------------------------------------------------
$pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('superadmin', 'empleado') NOT NULL DEFAULT 'superadmin',
    estado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// ---------------------------------------------------------------
// Tabla: apartamentos
// ---------------------------------------------------------------
$pdo->exec("CREATE TABLE IF NOT EXISTS apartamentos (
    codigo_apartamento VARCHAR(20) NOT NULL PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    torre ENUM('Playa Salguero', 'Rodadero Sur', 'Bello Horizonte') NOT NULL,
    capacidad_huespedes INT NOT NULL DEFAULT 2,
    num_habitaciones INT NOT NULL DEFAULT 1,
    num_banos INT NOT NULL DEFAULT 1,
    imagen_principal VARCHAR(512) NULL,
    galeria_imagenes TEXT NULL,
    descripcion TEXT NULL,
    visible TINYINT(1) NOT NULL DEFAULT 1,
    actualizado_por INT NULL,
    actualizado_en DATETIME NULL,
    CONSTRAINT fk_apartamentos_usuarios FOREIGN KEY (actualizado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// ---------------------------------------------------------------
// Tabla: historial_auditoria
// ---------------------------------------------------------------
$pdo->exec("CREATE TABLE IF NOT EXISTS historial_auditoria (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    accion VARCHAR(50) NOT NULL,
    descripcion TEXT NOT NULL,
    ip_origen VARCHAR(45) NOT NULL,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historial_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// ---------------------------------------------------------------
// Usuario administrador por defecto
// ---------------------------------------------------------------
$hash = password_hash('Admin2026*', PASSWORD_BCRYPT);
$stmt = $pdo->prepare('INSERT IGNORE INTO usuarios (nombre, email, password_hash, rol, estado, creado_en) VALUES (?, ?, ?, ?, 1, NOW())');
$stmt->execute(['Administrador', 'admin@reservadelmar.com', $hash]);

header('Content-Type: application/json');
echo json_encode(['ok' => true, 'message' => 'Instalacion completada correctamente']);
