-- ============================================================
--  SAGD – Sistema de Almacenamiento y Gestión de Datos
--  Iglesia Cristiana Emanuel
--  Base de datos MySQL
-- ============================================================

CREATE DATABASE IF NOT EXISTS sagd_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sagd_db;

-- ── USUARIOS DEL SISTEMA (administradores) ──────────────────
CREATE TABLE IF NOT EXISTS usuarios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    correo      VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,          -- bcrypt hash
    rol         ENUM('admin','editor') DEFAULT 'editor',
    activo      TINYINT(1) DEFAULT 1,
    creado_en   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── MIEMBROS Y ASISTENTES ───────────────────────────────────
CREATE TABLE IF NOT EXISTS miembros (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    apellido        VARCHAR(100) NOT NULL,
    tipo_doc        ENUM('CC','TI','CE','PP') DEFAULT 'CC',
    documento       VARCHAR(30)  NOT NULL UNIQUE,
    telefono        VARCHAR(20),
    direccion       VARCHAR(200),
    correo          VARCHAR(150),
    tipo            ENUM('miembro','asistente') DEFAULT 'asistente',
    fecha_ingreso   DATE,
    activo          TINYINT(1) DEFAULT 1,
    creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── ANUNCIOS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS anuncios (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titulo      VARCHAR(200) NOT NULL,
    contenido   TEXT NOT NULL,
    fecha_evento DATE,
    activo      TINYINT(1) DEFAULT 1,
    creado_por  INT,
    creado_en   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── CRONOGRAMA ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS cronograma (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    dia_semana  ENUM('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo') NOT NULL,
    actividad   VARCHAR(200) NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin    TIME,
    lugar       VARCHAR(150),
    activo      TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- ── ACTIVIDADES ESPECIALES ───────────────────────────────────
CREATE TABLE IF NOT EXISTS actividades (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    titulo      VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha_inicio DATE NOT NULL,
    fecha_fin    DATE,
    lugar        VARCHAR(150),
    activo       TINYINT(1) DEFAULT 1,
    creado_por   INT,
    creado_en    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── DATOS DE PRUEBA ─────────────────────────────────────────

-- Admin por defecto: admin@iglesia.com / Admin123*
INSERT INTO usuarios (nombre, correo, password, rol) VALUES
('Administrador', 'admin@iglesia.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

INSERT INTO cronograma (dia_semana, actividad, hora_inicio, hora_fin, lugar) VALUES
('Domingo',    'Culto dominical',   '10:00:00', '12:00:00', 'Templo principal'),
('Lunes',      'Grupo de jóvenes',  '18:00:00', '20:00:00', 'Salón de jóvenes'),
('Miércoles',  'Estudio bíblico',   '19:00:00', '21:00:00', 'Salón principal'),
('Viernes',    'Culto de oración',  '19:00:00', '21:00:00', 'Templo principal'),
('Sábado',     'Escuela dominical', '15:00:00', '17:00:00', 'Aulas de enseñanza');

INSERT INTO anuncios (titulo, contenido, fecha_evento, creado_por) VALUES
('Culto dominical especial', 'Este domingo celebramos un culto especial de adoración. ¡Te esperamos con tu familia!', '2025-03-30', 1),
('Vigilia de oración mensual', 'Los invitamos a la vigilia de oración del primer viernes de cada mes, de 9:00 PM a medianoche.', '2025-04-04', 1),
('Nueva serie de estudios bíblicos', 'Inicia nueva serie: "Fundamentos de la Fe". Todos los miércoles a las 7:00 PM.', '2025-04-02', 1);

INSERT INTO actividades (titulo, descripcion, fecha_inicio, fecha_fin, lugar, creado_por) VALUES
('Campamento Juvenil 2025', 'Campamento anual para jóvenes de 14 a 25 años. Tres días de comunión, adoración y formación.', '2025-04-15', '2025-04-17', 'La Gabriela', 1),
('Semana Santa – Programa especial', 'Cultos especiales de Semana Santa con predicaciones, alabanza y reflexión.', '2025-04-13', '2025-04-18', 'Templo principal', 1),
('Bazar benéfico', 'Bazar para recolectar fondos y apoyar familias de la comunidad.', '2025-04-26', '2025-04-26', 'Salón comunal', 1);
