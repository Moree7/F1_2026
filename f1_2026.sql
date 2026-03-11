-- Base de datos: f1_2026

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `f1_2026`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
USE `f1_2026`;


-- Tabla: usuarios

CREATE TABLE `usuarios` (
  `id_usuario`   INT(11)      NOT NULL AUTO_INCREMENT,
  `nombre`       VARCHAR(50)  NOT NULL,
  `email`        VARCHAR(100) NOT NULL,
  `password`     VARCHAR(255) NOT NULL,
  `rol`          ENUM('admin','usuario') NOT NULL DEFAULT 'usuario',
  `fecha_alta`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acceso` DATETIME    DEFAULT NULL,
  `activo`       TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Usuario admin por defecto (contraseña: password)
INSERT INTO `usuarios` (`nombre`, `email`, `password`, `rol`) VALUES
('Administrador', 'admin@f1.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Fernando Alonso', 'alonso@f1.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario');


-- Tabla: escuderias

CREATE TABLE `escuderias` (
  `id_escuderia`  INT(11)     NOT NULL AUTO_INCREMENT,
  `nombre`        VARCHAR(80) NOT NULL,
  `nacionalidad`  VARCHAR(50) NOT NULL,
  `motor`         VARCHAR(50) NOT NULL,
  `director`      VARCHAR(80) NOT NULL,
  `temporadas_f1` INT(3)      NOT NULL DEFAULT 0,
  `activa`        ENUM('Si','No') NOT NULL DEFAULT 'Si',
  PRIMARY KEY (`id_escuderia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `escuderias` (`id_escuderia`,`nombre`,`nacionalidad`,`motor`,`director`,`temporadas_f1`,`activa`) VALUES
(1,  'Red Bull Racing',   'Austria',      'RBPT/Ford',   'Laurent Mekies',     30, 'Si'),
(2,  'Ferrari',           'Italia',       'Ferrari',     'Frédéric Vasseur',   75, 'Si'),
(3,  'Mercedes',          'Alemania',     'Mercedes',    'Toto Wolff',         30, 'Si'),
(4,  'McLaren',           'Reino Unido',  'Mercedes',    'Andrea Stella',      58, 'Si'),
(5,  'Aston Martin',      'Reino Unido',  'Mercedes',    'Adrian Newey',        4, 'Si'),
(6,  'Alpine',            'Francia',      'Mercedes',    'Flavio Briatore',     4, 'Si'),
(7,  'Williams',          'Reino Unido',  'Mercedes',    'James Vowles',       47, 'Si'),
(8,  'Racing Bulls',      'Italia',       'RBPT/Ford',   'Alan Permane',        4, 'Si'),
(9,  'Haas',              'EEUU',         'Ferrari',     'Ayao Komatsu',        9, 'Si'),
(10, 'Audi',              'Alemania',     'Audi',        'Mattia Binotto',      1, 'Si'),
(11, 'Cadillac',          'EEUU',         'Ferrari',     'Graeme Lowdon',       1, 'Si');


-- Tabla: pilotos

CREATE TABLE `pilotos` (
  `id_piloto`    INT(11)      NOT NULL AUTO_INCREMENT,
  `nombre`       VARCHAR(80)  NOT NULL,
  `codigo`       CHAR(3)      NOT NULL,
  `numero`       INT(3)       NOT NULL,
  `nacionalidad` VARCHAR(50)  NOT NULL,
  `fecha_nac`    DATE         NOT NULL,
  `id_escuderia` INT(11)      NOT NULL,
  `campeonatos`  INT(2)       NOT NULL DEFAULT 0,
  `victorias`    INT(3)       NOT NULL DEFAULT 0,
  `poles`        INT(3)       NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_piloto`),
  FOREIGN KEY (`id_escuderia`) REFERENCES `escuderias`(`id_escuderia`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pilotos` (`id_piloto`,`nombre`,`codigo`,`numero`,`nacionalidad`,`fecha_nac`,`id_escuderia`,`campeonatos`,`victorias`,`poles`) VALUES
(1,  'Max Verstappen',    'VER', 33, 'Holandés',    '1997-09-30', 1,  4, 62, 40),
(2,  'Isack Hadjar',      'HAD', 6,  'Francés',     '2004-09-28', 1,  0, 0,  0),
(3,  'Lewis Hamilton',    'HAM', 44, 'Británico',   '1985-01-07', 2,  7, 103,104),
(4,  'Charles Leclerc',   'LEC', 16, 'Monegasco',   '1997-10-16', 2,  0, 8,  26),
(5,  'George Russell',    'RUS', 63, 'Británico',   '1998-02-15', 3,  0, 3,  8),
(6,  'Kimi Antonelli',    'ANT', 12, 'Italiano',    '2006-08-25', 3,  0, 0,  0),
(7,  'Lando Norris',      'NOR', 4,  'Británico',   '1999-11-13', 4,  1, 6,  13),
(8,  'Oscar Piastri',     'PIA', 81, 'Australiano', '2001-04-06', 4,  0, 3,  2),
(9,  'Fernando Alonso',   'ALO', 14, 'Español',     '1981-07-29', 5,  2, 32, 22),
(10, 'Lance Stroll',      'STR', 18, 'Canadiense',  '1998-10-29', 5,  0, 0,  0),
(11, 'Pierre Gasly',      'GAS', 10, 'Francés',     '1996-02-07', 6,  0, 1,  0),
(12, 'Franco Colapinto',  'COL', 43, 'Argentino',   '2003-05-27', 6,  0, 0,  0),
(13, 'Alexander Albon',   'ALB', 23, 'Tailandés',   '1996-03-23', 7,  0, 0,  0),
(14, 'Carlos Sainz',      'SAI', 55, 'Español',     '1994-09-01', 7,  0, 4,  5),
(15, 'Liam Lawson',       'LAW', 30, 'Neozelandés', '2002-02-11', 8,  0, 0,  0),
(16, 'Arvid Lindblad',    'LIN', 41, 'Sueco',       '2005-06-16', 8,  0, 0,  0),
(17, 'Oliver Bearman',    'BEA', 87, 'Británico',   '2005-05-08', 9,  0, 0,  0),
(18, 'Esteban Ocon',      'OCO', 31, 'Francés',     '1996-09-17', 9,  0, 1,  0),
(19, 'Nico Hülkenberg',   'HUL', 27, 'Alemán',      '1987-08-19', 10, 0, 0,  0),
(20, 'Gabriel Bortoleto', 'BOR', 5,  'Brasileño',   '2004-10-14', 10, 0, 0,  0),
(21, 'Valtteri Bottas',   'BOT', 77, 'Finlandés',   '1989-08-28', 11, 0, 10, 20),
(22, 'Sergio Pérez',      'PER', 11, 'Mexicano',    '1990-01-26', 11, 0, 13, 3);

-- Tabla: circuitos

CREATE TABLE `circuitos` (
  `id_circuito`   INT(11)       NOT NULL AUTO_INCREMENT,
  `nombre`        VARCHAR(100)  NOT NULL,
  `pais`          VARCHAR(50)   NOT NULL,
  `ciudad`        VARCHAR(50)   NOT NULL,
  `longitud_km`   DECIMAL(5,3)  NOT NULL,
  `num_curvas`    INT(3)        NOT NULL,
  `tipo_circuito` ENUM('Autódromo','Callejero','Híbrido','Rutero','Aeródromo') NOT NULL DEFAULT 'Autódromo',
  `lat`           DECIMAL(10,6) NOT NULL COMMENT 'Latitud para API clima',
  `lon`           DECIMAL(10,6) NOT NULL COMMENT 'Longitud para API clima',
  `record_vuelta` VARCHAR(20)   DEFAULT NULL,
  PRIMARY KEY (`id_circuito`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `circuitos` (`id_circuito`,`nombre`,`pais`,`ciudad`,`longitud_km`,`num_curvas`,`tipo_circuito`,`lat`,`lon`,`record_vuelta`) VALUES
(1,  'Bahrain International Circuit',        'Baréin',         'Sakhir',          5.412, 15, 'Autódromo',  26.032500,  50.510833, '1:31.447'),
(2,  'Jeddah Corniche Circuit',              'Arabia Saudí',   'Yeda',            6.174, 27, 'Híbrido',    21.631944,  39.104167, '1:30.734'),
(3,  'Albert Park Circuit',                  'Australia',      'Melbourne',       5.278, 16, 'Híbrido',   -37.849722, 144.968333, '1:20.235'),
(4,  'Suzuka International Racing Course',   'Japón',          'Suzuka',          5.807, 18, 'Autódromo',  34.843056, 136.541667, '1:30.983'),
(5,  'Shanghai International Circuit',       'China',          'Shanghái',        5.451, 16, 'Autódromo',  31.339444, 121.220556, '1:32.238'),
(6,  'Miami International Autodrome',        'EEUU',           'Miami',           5.412, 19, 'Híbrido',    25.958056, -80.238889, '1:29.708'),
(7,  'Autodromo Enzo e Dino Ferrari',        'Italia',         'Imola',           4.909, 19, 'Autódromo',  44.343889,  11.713333, '1:15.484'),
(8,  'Circuit de Monaco',                    'Mónaco',         'Montecarlo',      3.337, 19, 'Callejero',  43.737222,   7.420278, '1:12.909'),
(9,  'Circuit de Barcelona-Catalunya',       'España',         'Barcelona',       4.657, 14, 'Autódromo',  41.570000,   2.260000, '1:16.330'),
(10, 'Circuit Gilles Villeneuve',            'Canadá',         'Montreal',        4.361, 14, 'Híbrido',    45.504444, -73.526111, '1:13.078'),
(11, 'Red Bull Ring',                        'Austria',        'Spielberg',       4.318, 10, 'Autódromo',  47.220278,  14.764722, '1:05.619'),
(12, 'Silverstone Circuit',                  'Reino Unido',    'Silverstone',     5.891, 18, 'Autódromo',  52.073889,  -1.016944, '1:27.097'),
(13, 'Hungaroring',                          'Hungría',        'Budapest',        4.381, 14, 'Autódromo',  47.582778,  19.250278, '1:16.627'),
(14, 'Circuit de Spa-Francorchamps',         'Bélgica',        'Spa',             7.004, 20, 'Híbrido',    50.437222,   5.971389, '1:46.286'),
(15, 'Circuit Park Zandvoort',               'Países Bajos',   'Zandvoort',       4.259, 14, 'Autódromo',  52.388056,   4.540833, '1:11.097'),
(16, 'Autodromo Nazionale Monza',            'Italia',         'Monza',           5.793, 11, 'Autódromo',  45.618611,   9.281944, '1:21.046'),
(17, 'Baku City Circuit',                    'Azerbaiyán',     'Bakú',            6.003, 20, 'Callejero',  40.372778,  49.853333, '1:43.009'),
(18, 'Marina Bay Street Circuit',            'Singapur',       'Singapur',        4.940, 19, 'Callejero',   1.291389, 103.863056, '1:35.867'),
(19, 'Circuit of the Americas',              'EEUU',           'Austin',          5.513, 20, 'Autódromo',  30.132778, -97.641389, '1:36.169'),
(20, 'Autodromo Hermanos Rodriguez',         'México',         'Ciudad de México', 4.304,17, 'Autódromo',  19.404167, -99.090556, '1:17.774'),
(21, 'Autodromo José Carlos Pace',           'Brasil',         'São Paulo',       4.309, 15, 'Autódromo', -23.701389, -46.697500, '1:10.540'),
(22, 'Las Vegas Strip Circuit',              'EEUU',           'Las Vegas',       6.201, 17, 'Callejero',  36.107778,-115.173889, '1:35.119'),
(23, 'Losail International Circuit',         'Catar',          'Losail',          5.380, 16, 'Autódromo',  25.490278,  51.454444, '1:24.319'),
(24, 'Yas Marina Circuit',                   'EAU',            'Abu Dabi',        5.281, 16, 'Autódromo',  24.467222,  54.601944, '1:26.103'),
(25, 'Circuito de Madrid (Madring)',         'España',         'Madrid',          5.470, 22, 'Callejero',  40.454000,  -3.688000, NULL);


-- Tabla: carreras

CREATE TABLE `carreras` (
  `id_carrera`    INT(11)  NOT NULL AUTO_INCREMENT,
  `nombre_gp`     VARCHAR(100) NOT NULL,
  `id_circuito`   INT(11)  NOT NULL,
  `fecha`         DATE     NOT NULL,
  `num_vueltas`   INT(3)   NOT NULL,
  `estado`        ENUM('Programada','En curso','Finalizada','Cancelada') NOT NULL DEFAULT 'Programada',
  `condicion_pista` ENUM('Seco','Mojado','Mixto','Desconocido') NOT NULL DEFAULT 'Desconocido',
  `vuelta_rapida` VARCHAR(20) DEFAULT NULL,
  `id_piloto_vr`  INT(11)  DEFAULT NULL,
  PRIMARY KEY (`id_carrera`),
  FOREIGN KEY (`id_circuito`)  REFERENCES `circuitos`(`id_circuito`)  ON DELETE CASCADE,
  FOREIGN KEY (`id_piloto_vr`) REFERENCES `pilotos`(`id_piloto`)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `carreras` (`nombre_gp`,`id_circuito`,`fecha`,`num_vueltas`,`estado`,`condicion_pista`,`vuelta_rapida`,`id_piloto_vr`) VALUES
('Gran Premio de Australia',        3,  '2026-03-08', 58, 'Finalizada',  'Seco', '1:17.456', 5),
('Gran Premio de China',            5,  '2026-03-22', 56, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Japón',            4,  '2026-03-29', 53, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Baréin',           1,  '2026-04-12', 57, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Arabia Saudí',     2,  '2026-04-19', 50, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Miami',            6,  '2026-05-03', 57, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Canadá',          10,  '2026-05-25', 70, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Mónaco',           8,  '2026-06-07', 78, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de España',           9,  '2026-06-14', 66, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Austria',         11,  '2026-06-28', 71, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Gran Bretaña',    12,  '2026-07-05', 52, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Bélgica',         14,  '2026-07-19', 44, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Hungría',         13,  '2026-07-26', 70, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Países Bajos',    15,  '2026-08-23', 72, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Italia',          16,  '2026-09-06', 53, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de España (Madrid)', 25,  '2026-09-13', 57, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Azerbaiyán',      17,  '2026-09-27', 51, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Singapur',        18,  '2026-10-11', 62, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Estados Unidos',  19,  '2026-10-25', 56, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Ciudad de México',20,  '2026-11-01', 71, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de São Paulo',       21,  '2026-11-08', 71, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Las Vegas',       22,  '2026-11-21', 50, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Catar',           23,  '2026-11-29', 57, 'Programada',  'Desconocido', NULL, NULL),
('Gran Premio de Abu Dabi',        24,  '2026-12-06', 55, 'Programada',  'Desconocido', NULL, NULL);


-- Tabla: resultados

CREATE TABLE `resultados` (
  `id_resultado`  INT(11) NOT NULL AUTO_INCREMENT,
  `id_carrera`    INT(11) NOT NULL,
  `id_piloto`     INT(11) NOT NULL,
  `posicion`      INT(2)  NOT NULL,
  `puntos`        DECIMAL(4,1) NOT NULL DEFAULT 0,
  `vueltas`       INT(3)  NOT NULL DEFAULT 0,
  `tiempo_total`  VARCHAR(20) DEFAULT NULL,
  `estado_carrera` ENUM('Terminó','Abandono','No clasificado','Descalificado') NOT NULL DEFAULT 'Terminó',
  `pit_stops`     INT(2)  NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_resultado`),
  UNIQUE KEY `carrera_piloto` (`id_carrera`,`id_piloto`),
  FOREIGN KEY (`id_carrera`) REFERENCES `carreras`(`id_carrera`) ON DELETE CASCADE,
  FOREIGN KEY (`id_piloto`)  REFERENCES `pilotos`(`id_piloto`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Resultados Carrera Australia 2026
INSERT INTO `resultados` (`id_carrera`,`id_piloto`,`posicion`,`puntos`,`vueltas`,`tiempo_total`,`estado_carrera`,`pit_stops`) VALUES
(1,  5,  1, 25, 58, '1:28:41.312', 'Terminó', 1),
(1,  6,  2, 18, 58, '+2.974', 'Terminó', 1),
(1,  4,  3, 15, 58, '+15.519', 'Terminó', 1),
(1,  3,  4, 12, 58, '+16.144', 'Terminó', 1),
(1,  7,  5, 10, 58, '+51.741', 'Terminó', 2),
(1,  1,  6,  8, 58, '+54.617', 'Terminó', 2),
(1, 17,  7,  6, 57, '+1 vuelta', 'Terminó', 1),
(1, 16,  8,  4, 57, '+1 vuelta', 'Terminó', 1),
(1, 20,  9,  2, 57, '+1 vuelta', 'Terminó', 2),
(1, 11, 10,  1, 57, '+1 vuelta', 'Terminó', 1),
(1, 18, 11,  0, 57, '+1 vuelta', 'Terminó', 1),
(1, 13, 12,  0, 57, '+1 vuelta', 'Terminó', 2),
(1, 15, 13,  0, 57, '+1 vuelta', 'Terminó', 2),
(1, 12, 14,  0, 56, '+2 vueltas', 'Terminó', 2),
(1, 14, 15,  0, 56, '+2 vueltas', 'Terminó', 3),
(1, 22, 16,  0, 56, '+2 vueltas', 'Terminó', 2),
(1, 10, 17,  0, 43, '+15 vueltas', 'Terminó', 4),
(1,  9, 18,  0,  0, 'Abandono', 'Abandono', 3),
(1, 21, 19,  0,  0, 'Abandono', 'Abandono', 1),
(1,  2, 20,  0,  0, 'Abandono', 'Abandono', 0),
(1,  8, 21,  0,  0, 'DNS', 'No clasificado', 0),
(1, 19, 22,  0,  0, 'DNS', 'No clasificado', 0);

-- Tabla: neumaticos

CREATE TABLE `neumaticos` (
  `id_neumatico`   INT(11)      NOT NULL AUTO_INCREMENT,
  `compuesto`      VARCHAR(30)  NOT NULL,
  `codigo`         CHAR(1)      NOT NULL,
  `banda_rodadura` ENUM('Liso','Canales') NOT NULL DEFAULT 'Liso',
  `condicion`      ENUM('Seco','Mojado') NOT NULL DEFAULT 'Seco',
  `adherencia`     ENUM('Baja','Media','Alta','N/A') NOT NULL DEFAULT 'Media',
  `durabilidad`    ENUM('Baja','Media','Alta','N/A') NOT NULL DEFAULT 'Media',
  PRIMARY KEY (`id_neumatico`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `neumaticos` (`compuesto`,`codigo`,`banda_rodadura`,`condicion`,`adherencia`,`durabilidad`) VALUES
('Duro',        'H', 'Liso',    'Seco',   'Baja',  'Alta'),
('Medio',       'M', 'Liso',    'Seco',   'Media', 'Media'),
('Blando',      'S', 'Liso',    'Seco',   'Alta',  'Baja'),
('Intermedio',  'I', 'Canales', 'Mojado', 'N/A',   'N/A'),
('De lluvia',   'W', 'Canales', 'Mojado', 'N/A',   'N/A')
;

COMMIT;
