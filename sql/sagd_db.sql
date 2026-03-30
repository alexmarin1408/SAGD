-- Base de datos SAGD
-- Sistema de Almacenamiento y Gestion de Datos
-- Iglesia Cristiana Emanuel - La Gabriela, Medellin
-- Autor: Alexander De Jesus Marin Munoz

CREATE DATABASE IF NOT EXISTS sagd_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sagd_db;

CREATE TABLE IF NOT EXISTS usuarios (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100) NOT NULL,
    correo     VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    rol        ENUM('admin') DEFAULT 'admin',
    activo     TINYINT(1) DEFAULT 1,
    creado_en  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS miembros (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100) NOT NULL,
    apellido   VARCHAR(100) NOT NULL,
    tipo_doc   ENUM('CC','TI','CE','PP') DEFAULT 'CC',
    documento  VARCHAR(30) NOT NULL UNIQUE,
    telefono   VARCHAR(20),
    tipo       ENUM('miembro','asistente') DEFAULT 'asistente',
    activo     TINYINT(1) DEFAULT 1,
    creado_en  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS anuncios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titulo      VARCHAR(200) NOT NULL,
    contenido   TEXT NOT NULL,
    fecha_evento DATE,
    activo      TINYINT(1) DEFAULT 1,
    creado_en   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cronograma (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    dia_semana  VARCHAR(20) NOT NULL,
    actividad   VARCHAR(200) NOT NULL,
    hora_inicio TIME,
    hora_fin    TIME,
    lugar       VARCHAR(200),
    activo      TINYINT(1) DEFAULT 1,
    creado_en   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS actividades (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    titulo       VARCHAR(200) NOT NULL,
    descripcion  TEXT,
    fecha_inicio DATE,
    fecha_fin    DATE,
    lugar        VARCHAR(200),
    activo       TINYINT(1) DEFAULT 1,
    creado_en    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Datos iniciales
INSERT INTO usuarios (nombre, correo, password, rol) VALUES
('Administrador', 'alexmarin1408@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Contrasena: password

INSERT INTO miembros (nombre, apellido, tipo_doc, documento, telefono, tipo) VALUES
('Juan', 'Garcia', 'CC', '1001234567', '3001234567', 'miembro'),
('Maria', 'Lopez', 'CC', '1007654321', '3017654321', 'asistente'),
('Carlos', 'Martinez', 'CC', '1009876543', '3009876543', 'miembro'),
('Ana', 'Rodriguez', 'CC', '1005432109', '3005432109', 'asistente'),
('Pedro', 'Sanchez', 'CC', '1003210987', '3003210987', 'miembro');

INSERT INTO anuncios (titulo, contenido, fecha_evento) VALUES
('Culto dominical', 'Les recordamos que el culto dominical es a las 10am en el salon principal.', '2026-04-06'),
('Reunion de jovenes', 'Este sabado reunion de jovenes a las 4pm. No faltes!', '2026-04-05'),
('Estudio biblico', 'El miercoles tenemos estudio biblico a las 7pm.', '2026-04-08');

INSERT INTO cronograma (dia_semana, actividad, hora_inicio, hora_fin, lugar) VALUES
('Domingo', 'Culto Principal', '10:00:00', '12:00:00', 'Salon Principal'),
('Miercoles', 'Estudio Biblico', '19:00:00', '21:00:00', 'Salon Principal'),
('Sabado', 'Reunion de Jovenes', '16:00:00', '18:00:00', 'Salon Jovenes'),
('Viernes', 'Celulas', '19:00:00', '21:00:00', 'Hogares');

INSERT INTO actividades (titulo, descripcion, fecha_inicio, fecha_fin, lugar) VALUES
('Campamento de Jovenes', 'Campamento anual de jovenes en las afueras de la ciudad.', '2026-04-18', '2026-04-20', 'Finca La Esperanza');
