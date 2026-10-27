-- ============================================================================
-- BASE DE DATOS: ingles
-- Sistema: LingoFlow Mobile / EnglishSchedule (Gestión de Lecciones de Inglés)
-- Cumple: M01-22 (Modelado tablas), M01-23 (Locks y Concurrencia), M01-24 (SOLID)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `ingles` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `ingles`;

-- Desactivar llaves foráneas para recreación segura si es necesario
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `lecciones`;
DROP TABLE IF EXISTS `horarios_disponibles`;
DROP TABLE IF EXISTS `profesor_cursos`;
DROP TABLE IF EXISTS `profesores`;
DROP TABLE IF EXISTS `niveles`;
DROP TABLE IF EXISTS `cursos`;
DROP TABLE IF EXISTS `estudiantes`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. TABLA: ESTUDIANTES
CREATE TABLE `estudiantes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `uuid` VARCHAR(36) NOT NULL UNIQUE,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `nombre` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NULL,
  `oauth_provider` ENUM('LOCAL', 'GOOGLE', 'APPLE', 'FACEBOOK') DEFAULT 'LOCAL',
  `oauth_id` VARCHAR(100) NULL,
  `nivel_actual` ENUM('A1', 'A2', 'B1', 'B2', 'C1') DEFAULT 'A1',
  `telefono` VARCHAR(30) NULL,
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_estudiante_email` (`email`),
  INDEX `idx_estudiante_username` (`username`),
  INDEX `idx_estudiante_oauth` (`oauth_provider`, `oauth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABLA: CURSOS (Clínica en el sistema médico)
CREATE TABLE `cursos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo` VARCHAR(30) NOT NULL UNIQUE,
  `nombre` VARCHAR(120) NOT NULL,
  `descripcion` TEXT NULL,
  `icono` VARCHAR(50) DEFAULT 'book-open',
  `activo` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABLA: NIVELES (Especialidad en el sistema médico)
CREATE TABLE `niveles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `curso_id` INT NOT NULL,
  `codigo` VARCHAR(10) NOT NULL,
  `nombre` VARCHAR(60) NOT NULL,
  `descripcion` TEXT NULL,
  FOREIGN KEY (`curso_id`) REFERENCES `cursos`(`id`) ON DELETE CASCADE,
  INDEX `idx_nivel_curso` (`curso_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABLA: PROFESORES (Médico en el sistema médico)
CREATE TABLE `profesores` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `uuid` VARCHAR(36) NOT NULL UNIQUE,
  `nombre` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `especialidad` VARCHAR(120) NOT NULL,
  `acento` VARCHAR(50) DEFAULT 'Americano',
  `biografia` TEXT NULL,
  `avatar_url` VARCHAR(255) NULL,
  `rating` DECIMAL(3,2) DEFAULT 4.90,
  `activo` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABLA INTERMEDIA: PROFESOR - CURSOS
CREATE TABLE `profesor_cursos` (
  `profesor_id` INT NOT NULL,
  `curso_id` INT NOT NULL,
  PRIMARY KEY (`profesor_id`, `curso_id`),
  FOREIGN KEY (`profesor_id`) REFERENCES `profesores`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`curso_id`) REFERENCES `cursos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TABLA: HORARIOS DISPONIBLES (Soporta Concurrencia y Locks - M01-23)
CREATE TABLE `horarios_disponibles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `profesor_id` INT NOT NULL,
  `fecha` DATE NOT NULL,
  `hora_inicio` TIME NOT NULL,
  `hora_fin` TIME NOT NULL,
  `estado` ENUM('DISPONIBLE', 'BLOQUEADO_TEMPORAL', 'RESERVADO') DEFAULT 'DISPONIBLE',
  `bloqueado_hasta` DATETIME NULL COMMENT 'Lock optimista por 60s mientras el estudiante confirma',
  `bloqueado_por_estudiante_id` INT NULL,
  `version` INT DEFAULT 1 COMMENT 'Control de concurrencia optimista (Optimistic Concurrency Control)',
  FOREIGN KEY (`profesor_id`) REFERENCES `profesores`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`bloqueado_por_estudiante_id`) REFERENCES `estudiantes`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `uk_profesor_slot` (`profesor_id`, `fecha`, `hora_inicio`),
  INDEX `idx_horario_busqueda` (`fecha`, `estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TABLA: LECCIONES (Cita médica en el sistema base)
CREATE TABLE `lecciones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `uuid` VARCHAR(36) NOT NULL UNIQUE,
  `estudiante_id` INT NOT NULL,
  `profesor_id` INT NOT NULL,
  `curso_id` INT NOT NULL,
  `nivel_id` INT NOT NULL,
  `horario_id` INT NOT NULL,
  `fecha` DATE NOT NULL,
  `hora_inicio` TIME NOT NULL,
  `hora_fin` TIME NOT NULL,
  `estado` ENUM('PROGRAMADA', 'EN_CURSO', 'COMPLETADA', 'CANCELADA') DEFAULT 'PROGRAMADA',
  `enlace_sesion` VARCHAR(255) DEFAULT 'https://meet.google.com/lingo-room-live',
  `creado_en` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes`(`id`),
  FOREIGN KEY (`profesor_id`) REFERENCES `profesores`(`id`),
  FOREIGN KEY (`curso_id`) REFERENCES `cursos`(`id`),
  FOREIGN KEY (`nivel_id`) REFERENCES `niveles`(`id`),
  FOREIGN KEY (`horario_id`) REFERENCES `horarios_disponibles`(`id`),
  INDEX `idx_lecciones_estudiante` (`estudiante_id`, `estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- INSERCIÓN DE DATOS SEMILLA (SEED DATA)
-- ============================================================================

-- Estudiantes (Incluye al usuario 'mzaldana' del portal corporativo)
INSERT INTO `estudiantes` (`id`, `uuid`, `username`, `nombre`, `email`, `password_hash`, `nivel_actual`, `telefono`) VALUES
(1, 'STU-1001', 'arivera', 'Alex Rivera', 'alex.student@english.edu', '$2y$10$wK1bQy9kXmJ0k9vGkL9oeeWn/f7G4eT5L7cQf7B6tVb6K8vJ5kLm.', 'B1', '+502 5555-1234'),
(2, 'STU-1002', 'smorales', 'Sarah Morales', 'sarah.learner@english.edu', '$2y$10$wK1bQy9kXmJ0k9vGkL9oeeWn/f7G4eT5L7cQf7B6tVb6K8vJ5kLm.', 'B2', '+502 4444-5678'),
(3, 'STU-1003', 'mzaldana', 'Manuel Zaldaña', 'mzaldana@english.edu', '$2y$10$wK1bQy9kXmJ0k9vGkL9oeeWn/f7G4eT5L7cQf7B6tVb6K8vJ5kLm.', 'C1', '+502 3333-9012');

-- Cursos (Categorías de aprendizaje)
INSERT INTO `cursos` (`id`, `codigo`, `nombre`, `descripcion`, `icono`) VALUES
(1, 'GENERAL', 'Inglés General Integral', 'Dominio de gramática, vocabulario y soltura en situaciones de la vida real.', 'book-open'),
(2, 'BUSINESS', 'Inglés Corporativo y de Negocios', 'Negociación internacional, presentaciones ejecutivas y comunicación profesional.', 'briefcase'),
(3, 'IELTS_TOEFL', 'Preparación IELTS & TOEFL', 'Estrategias de alto rendimiento para certificaciones internacionales oficiales.', 'award'),
(4, 'CONVERSATION', 'Club de Fluidez y Conversación', 'Práctica intensiva de oratoria, debates y pronunciación nativa.', 'message-circle');

-- Niveles por Curso
INSERT INTO `niveles` (`id`, `curso_id`, `codigo`, `nombre`, `descripcion`) VALUES
(1, 1, 'A1', 'Principiante', 'Frases cotidianas y vocabulario elemental.'),
(2, 1, 'A2', 'Básico', 'Intercambios sencillos y comprensión directa.'),
(3, 1, 'B1', 'Intermedio', 'Fluidez en situaciones laborales y personales.'),
(4, 1, 'B2', 'Intermedio Alto', 'Argumentación técnica y comprensión compleja.'),
(5, 1, 'C1', 'Avanzado', 'Uso flexible, eficaz y natural del idioma.'),
(6, 2, 'B1', 'Business B1', 'Vocabulario comercial y redacción de emails.'),
(7, 2, 'B2', 'Business B2', 'Reuniones estratégicas y pitch corporativo.'),
(8, 2, 'C1', 'Executive C1', 'Negociaciones de alto impacto y contratos.'),
(9, 3, 'B2', 'IELTS 6.5+', 'Preparación académica para bandas 6.0 a 7.0.'),
(10, 3, 'C1', 'IELTS 8.0+', 'Maestría académica para universidades de élite.'),
(11, 4, 'B1', 'Speaking Club B1', 'Superación del miedo escénico y fluidez.'),
(12, 4, 'B2', 'Debate Club B2', 'Conversación espontánea y fluidez auditiva.');

-- Profesores
INSERT INTO `profesores` (`id`, `uuid`, `nombre`, `email`, `especialidad`, `acento`, `biografia`, `rating`) VALUES
(1, 'PROF-2001', 'Prof. David Miller', 'david.miller@english.edu', 'Cambridge Certified & Gramática Avanzada', 'Británico (Oxford / UK)', 'Más de 10 años preparando estudiantes internacionales con metodología activa.', 4.96),
(2, 'PROF-2002', 'Prof. Emma Watson', 'emma.watson@english.edu', 'Business English & Entrevistas Corporativas', 'Británico (Londres / UK)', 'Consultora de comunicación en multinacionales y coach de oratoria ejecutiva.', 4.98),
(3, 'PROF-2003', 'Prof. Michael Brown', 'michael.brown@english.edu', 'Examinador Oficial IELTS / TOEFL', 'Canadiense / Neutro', 'Especialista en bandas altas y corrección analítica de ensayos y speaking.', 4.92),
(4, 'PROF-2004', 'Prof. Sophia Chen', 'sophia.chen@english.edu', 'Pronunciación & Fonética Americana', 'Americano (USA Nativo)', 'Lingüista aplicada enfocada en neutralización de acento y soltura auditiva.', 4.89);

-- Relación Profesor - Cursos
INSERT INTO `profesor_cursos` (`profesor_id`, `curso_id`) VALUES
(1, 1), (1, 4),
(2, 2), (2, 4),
(3, 3), (3, 1),
(4, 4), (4, 1);

-- Horarios Disponibles (Slots generados para hoy y próximos días)
INSERT INTO `horarios_disponibles` (`id`, `profesor_id`, `fecha`, `hora_inicio`, `hora_fin`, `estado`) VALUES
(1, 1, CURDATE(), '08:00:00', '09:00:00', 'RESERVADO'),
(2, 1, CURDATE(), '09:00:00', '10:00:00', 'DISPONIBLE'),
(3, 1, CURDATE(), '10:00:00', '11:00:00', 'DISPONIBLE'),
(4, 1, CURDATE(), '14:00:00', '15:00:00', 'DISPONIBLE'),
(5, 2, CURDATE(), '09:00:00', '10:00:00', 'RESERVADO'),
(6, 2, CURDATE(), '11:00:00', '12:00:00', 'DISPONIBLE'),
(7, 2, CURDATE(), '15:00:00', '16:00:00', 'DISPONIBLE'),
(8, 3, CURDATE(), '10:00:00', '11:00:00', 'DISPONIBLE'),
(9, 3, CURDATE(), '16:00:00', '17:00:00', 'DISPONIBLE'),
(10, 4, CURDATE(), '08:30:00', '09:30:00', 'DISPONIBLE'),
(11, 4, CURDATE(), '17:00:00', '18:00:00', 'DISPONIBLE'),
-- Horarios para Mañana
(12, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '10:00:00', 'DISPONIBLE'),
(13, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:00:00', '12:00:00', 'DISPONIBLE'),
(14, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', '11:00:00', 'DISPONIBLE'),
(15, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', '15:00:00', 'DISPONIBLE');

-- Lecciones de Ejemplo (Próxima lección para Alex y Manuel)
INSERT INTO `lecciones` (`id`, `uuid`, `estudiante_id`, `profesor_id`, `curso_id`, `nivel_id`, `horario_id`, `fecha`, `hora_inicio`, `hora_fin`, `estado`) VALUES
(1, 'LEC-9001', 1, 1, 1, 3, 1, CURDATE(), '08:00:00', '09:00:00', 'PROGRAMADA'),
(2, 'LEC-9002', 3, 2, 2, 8, 5, CURDATE(), '09:00:00', '10:00:00', 'PROGRAMADA');
