-- Respaldo generado el 2025-07-03_19-34-54
SET FOREIGN_KEY_CHECKS=0;

-- Estructura para `tipo_tutor`
DROP TABLE IF EXISTS `tipo_tutor`;
CREATE TABLE `tipo_tutor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Datos para `tipo_tutor`
INSERT INTO `tipo_tutor` VALUES ('1','Tutor');
INSERT INTO `tipo_tutor` VALUES ('2','Tutora');
INSERT INTO `tipo_tutor` VALUES ('3','Encargado/a Legal');

