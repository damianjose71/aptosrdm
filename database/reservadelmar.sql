-- ============================================================
-- Reserva del Mar - Base de datos del panel administrativo
-- ============================================================
-- IMPORTANTE:
-- Este script elimina y vuelve a crear las tablas del panel.
-- Úsalo en una base de datos vacía o haz un backup antes.
--
-- Usuario inicial: adminrdm
-- La contraseña inicial es la que ya tenías definida.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS apartamento_amenidades;
DROP TABLE IF EXISTS historial_auditoria;
DROP TABLE IF EXISTS apartamentos;
DROP TABLE IF EXISTS amenidades;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    usuario VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('superadmin', 'empleado') NOT NULL DEFAULT 'superadmin',
    estado TINYINT(1) NOT NULL DEFAULT 1,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuarios_usuario (usuario),
    UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE amenidades (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_amenidades_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE apartamentos (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    codigo_apartamento VARCHAR(50) NOT NULL,
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
    actualizado_por INT UNSIGNED NULL,
    actualizado_en DATETIME NULL,
    UNIQUE KEY uq_apartamentos_codigo (codigo_apartamento),
    KEY idx_apartamentos_estado (estado),
    KEY idx_apartamentos_ubicacion (ubicacion),
    KEY idx_apartamentos_huespedes (huespedes),
    CONSTRAINT fk_apartamentos_usuarios
        FOREIGN KEY (actualizado_por) REFERENCES usuarios(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE apartamento_amenidades (
    apartamento_id INT UNSIGNED NOT NULL,
    amenidad_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (apartamento_id, amenidad_id),
    KEY idx_apartamento_amenidad_amenidad (amenidad_id),
    CONSTRAINT fk_apartamento_amenidades_apartamento
        FOREIGN KEY (apartamento_id) REFERENCES apartamentos(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_apartamento_amenidades_amenidad
        FOREIGN KEY (amenidad_id) REFERENCES amenidades(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE historial_auditoria (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    accion VARCHAR(50) NOT NULL,
    descripcion TEXT NOT NULL,
    ip_origen VARCHAR(45) NOT NULL,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_historial_usuario (usuario_id),
    KEY idx_historial_fecha (fecha_hora),
    CONSTRAINT fk_historial_usuarios
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuario administrador inicial.
-- El hash corresponde a la contraseña que ya tenías definida.
INSERT INTO usuarios (nombre, usuario, email, password_hash, rol, estado)
VALUES (
    'Administrador',
    'adminrdm',
    'admin@reservadelmar.com',
    '$2y$10$O4DNudjzD2iUy3BR2SeR9OKUpczJNHT283brhYdGCypJh/XlMDAiC',
    'superadmin',
    1
);

-- Amenidades iniciales del panel.
INSERT INTO amenidades (nombre) VALUES
    ('Aire acondicionado'),
    ('Balcón privado'),
    ('Wi-Fi'),
    ('Cocina equipada'),
    ('TV inteligente'),
    ('Acceso a piscina'),
    ('Parqueadero'),
    ('Vista al mar'),
    ('Zona BBQ'),
    ('Gimnasio'),
    ('Terraza'),
    ('Jacuzzi'),
    ('Restaurante'),
    ('Bar'),
    ('Seguridad 24/7'),
    ('Ascensor'),
    ('Lavadora');

SET FOREIGN_KEY_CHECKS = 1;
