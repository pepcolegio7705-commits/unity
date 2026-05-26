-- Respaldo generado el 2025-07-01_22-35-54
SET FOREIGN_KEY_CHECKS=0;

-- Estructura para `roles`
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Datos para `roles`
INSERT INTO `roles` VALUES ('1','Administrador');
INSERT INTO `roles` VALUES ('2','Docente');
INSERT INTO `roles` VALUES ('3','Alumno');

