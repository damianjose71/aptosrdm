<?php

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';

$pdo = getPDO();

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS usuarios (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        rol ENUM('superadmin', 'empleado') NOT NULL DEFAULT 'superadmin',
        estado TINYINT(1) NOT NULL DEFAULT 1,
        creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_usuarios_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS historial_auditoria (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT UNSIGNED NOT NULL,
        accion VARCHAR(50) NOT NULL,
        descripcion TEXT NOT NULL,
        ip_origen VARCHAR(45) NOT NULL,
        fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_historial_usuarios FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS amenidades (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_amenidades_nombre (nombre)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS apartamentos (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        codigo_apartamento VARCHAR(20) NOT NULL UNIQUE,
        titulo VARCHAR(255) NOT NULL,
        ubicacion ENUM('Playa Salguero', 'Bello Horizonte', 'Rodadero Sur') NOT NULL,
        huespedes INT UNSIGNED NOT NULL DEFAULT 1,
        habitaciones INT UNSIGNED NOT NULL DEFAULT 1,
        banos INT UNSIGNED NOT NULL DEFAULT 1,
        camas INT UNSIGNED NOT NULL DEFAULT 1,
        imagen_principal VARCHAR(512) NULL,
        galeria LONGTEXT NULL,
        descripcion TEXT NULL,
        estado TINYINT(1) NOT NULL DEFAULT 1,
        fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        actualizado_por INT NULL,
        actualizado_en DATETIME NULL,
        CONSTRAINT fk_apartamentos_usuarios FOREIGN KEY (actualizado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
        INDEX idx_apartamentos_estado (estado),
        INDEX idx_apartamentos_ubicacion (ubicacion),
        INDEX idx_apartamentos_huespedes (huespedes)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS apartamento_amenidades (
        apartamento_id INT UNSIGNED NOT NULL,
        amenidad_id INT UNSIGNED NOT NULL,
        PRIMARY KEY (apartamento_id, amenidad_id),
        KEY idx_apartamento_amenidad (apartamento_id),
        CONSTRAINT fk_apartamento_amenidades_apartamento FOREIGN KEY (apartamento_id) REFERENCES apartamentos(id) ON DELETE CASCADE,
        CONSTRAINT fk_apartamento_amenidades_amenidad FOREIGN KEY (amenidad_id) REFERENCES amenidades(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

$apartamentosTableExists = (bool) $pdo->query("SHOW TABLES LIKE 'apartamentos'")->fetchColumn();
$apartamentosHasId = false;
if ($apartamentosTableExists) {
    $apartamentosColumns = $pdo->query('SHOW COLUMNS FROM apartamentos')->fetchAll(PDO::FETCH_COLUMN);
    $apartamentosHasId = in_array('id', $apartamentosColumns, true);
}

if (!$apartamentosTableExists) {
    // La tabla se creo arriba cuando no existia.
} elseif (!$apartamentosHasId) {
    $pdo->exec('ALTER TABLE apartamentos RENAME TO apartamentos_legacy');
    $pdo->exec(
        "CREATE TABLE apartamentos (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            codigo_apartamento VARCHAR(20) NOT NULL UNIQUE,
            titulo VARCHAR(255) NOT NULL,
            ubicacion ENUM('Playa Salguero', 'Bello Horizonte', 'Rodadero Sur') NOT NULL,
            huespedes INT UNSIGNED NOT NULL DEFAULT 1,
            habitaciones INT UNSIGNED NOT NULL DEFAULT 1,
            banos INT UNSIGNED NOT NULL DEFAULT 1,
            camas INT UNSIGNED NOT NULL DEFAULT 1,
            imagen_principal VARCHAR(512) NULL,
            galeria LONGTEXT NULL,
            descripcion TEXT NULL,
            estado TINYINT(1) NOT NULL DEFAULT 1,
            fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            actualizado_por INT NULL,
            actualizado_en DATETIME NULL,
            CONSTRAINT fk_apartamentos_usuarios FOREIGN KEY (actualizado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
            INDEX idx_apartamentos_estado (estado),
            INDEX idx_apartamentos_ubicacion (ubicacion),
            INDEX idx_apartamentos_huespedes (huespedes)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "INSERT INTO apartamentos (
            codigo_apartamento, titulo, ubicacion, huespedes, habitaciones, banos, camas,
            imagen_principal, galeria, descripcion, estado, actualizado_por, actualizado_en, fecha_creacion
        ) SELECT
            l.codigo_apartamento, l.titulo, l.torre, COALESCE(l.capacidad_huespedes, 2), COALESCE(l.num_habitaciones, 1), COALESCE(l.num_banos, 1), COALESCE(l.num_habitaciones, 1),
            COALESCE(l.imagen_principal, NULL), COALESCE(l.galeria_imagenes, '[]'), COALESCE(l.descripcion, NULL), COALESCE(l.visible, 1), l.actualizado_por, COALESCE(l.actualizado_en, NOW()), COALESCE(l.actualizado_en, NOW())
        FROM apartamentos_legacy l"
    );
}

$amenidadesSeed = [
    'Aire acondicionado',
    'Balcón privado',
    'Wi-Fi',
    'Cocina equipada',
    'TV inteligente',
    'Acceso a piscina',
    'Parqueadero',
    'Vista al mar',
    'Zona BBQ',
    'Gimnasio',
    'Terraza',
    'Jacuzzi',
    'Restaurante',
    'Bar',
    'Seguridad 24/7',
    'Ascensor',
    'Lavadora',
];

$amenidadStatement = $pdo->prepare('INSERT IGNORE INTO amenidades (nombre) VALUES (?)');
foreach ($amenidadesSeed as $nombre) {
    $amenidadStatement->execute([$nombre]);
}

$usuarioStatement = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE email = ?');
$usuarioStatement->execute(['admin@reservadelmar.com']);
$usuarioExiste = (int) $usuarioStatement->fetchColumn();
if ($usuarioExiste === 0) {
    $hash = password_hash('Admin2026*', PASSWORD_BCRYPT);
    $pdo->prepare(
        'INSERT INTO usuarios (nombre, email, password_hash, rol, estado, creado_en) VALUES (?, ?, ?, ?, 1, NOW())'
    )->execute(['Administrador', 'admin@reservadelmar.com', $hash]);
}

header('Content-Type: application/json');
echo json_encode([
    'ok' => true,
    'message' => 'Instalación y migración del esquema de apartamentos completadas correctamente',
]);
