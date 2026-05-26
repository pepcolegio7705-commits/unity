-- Base de datos
CREATE DATABASE IF NOT EXISTS unity_gestion;
USE unity_gestion;

-- 🔁 Elimina si existen
DROP TABLE IF EXISTS calificaciones, inscripciones_examen, examenes,
                    docentes_materias, alumnos, materias,
                    cursos, tutores, usuarios;

-- 🧍 Usuarios
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  dni VARCHAR(15) UNIQUE NOT NULL,
  correo VARCHAR(100) UNIQUE NOT NULL,
  clave VARCHAR(255) NOT NULL,
  rol ENUM('Administrador', 'Directivo', 'Secretario', 'Preceptor', 'Docente', 'Alumno') NOT NULL,
  estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 👨‍👩 Tutores (padre / madre / otro)
CREATE TABLE tutores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  dni VARCHAR(15) UNIQUE,
  correo VARCHAR(100),
  telefono VARCHAR(30),
  direccion TEXT,
  tipo ENUM('Padre', 'Madre', 'Otro') NOT NULL,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 🎓 Cursos
CREATE TABLE cursos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  turno ENUM('Mañana', 'Tarde', 'Vespertino') NOT NULL,
  creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 📚 Materias
CREATE TABLE materias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  curso_id INT NOT NULL,
  FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
);

-- 👨‍🎓 Alumnos
CREATE TABLE alumnos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL UNIQUE,
  curso_id INT NOT NULL,
  legajo VARCHAR(20) UNIQUE,
  libro VARCHAR(10),
  folio VARCHAR(10),
  nacionalidad VARCHAR(50),
  fecha_nacimiento DATE,
  direccion TEXT,
  telefono VARCHAR(30),
  tutor_padre_id INT,
  tutor_madre_id INT,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (curso_id) REFERENCES cursos(id),
  FOREIGN KEY (tutor_padre_id) REFERENCES tutores(id),
  FOREIGN KEY (tutor_madre_id) REFERENCES tutores(id)
);

-- ✏️ Docente por materia (N:N)
CREATE TABLE docentes_materias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  materia_id INT NOT NULL,
  UNIQUE (usuario_id, materia_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE CASCADE
);

-- 📝 Exámenes
CREATE TABLE examenes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  materia_id INT NOT NULL,
  fecha DATE NOT NULL,
  tipo ENUM('Parcial', 'Final', 'Recuperatorio') NOT NULL,
  FOREIGN KEY (materia_id) REFERENCES materias(id)
);

-- 🧾 Inscripciones a exámenes
CREATE TABLE inscripciones_examen (
  id INT AUTO_INCREMENT PRIMARY KEY,
  examen_id INT NOT NULL,
  alumno_id INT NOT NULL,
  fecha_inscripcion DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (examen_id, alumno_id),
  FOREIGN KEY (examen_id) REFERENCES examenes(id),
  FOREIGN KEY (alumno_id) REFERENCES alumnos(id)
);

-- 🧮 Calificaciones
CREATE TABLE calificaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  alumno_id INT NOT NULL,
  materia_id INT NOT NULL,
  examen_id INT,
  nota DECIMAL(4,2),
  observacion TEXT,
  fecha_carga DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (alumno_id) REFERENCES alumnos(id),
  FOREIGN KEY (materia_id) REFERENCES materias(id),
  FOREIGN KEY (examen_id) REFERENCES examenes(id)
);