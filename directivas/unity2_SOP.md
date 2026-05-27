# Directiva Base: Unity2 (SOP)

Esta directiva sirve como plantilla base y **Fuente de la Verdad** para el proyecto `unity2`.

## Objetivo del Proyecto
**Gestión Académica.** El software debe ser robusto, seguro, y asegurar la integridad de los datos (ej. legajos únicos por institución).

## Reglas Globales Inquebrantables
- **Mentalidad Full-Stack:** Todo código debe considerar la arquitectura completa.
- **Diseño Premium:** Las interfaces gráficas SIEMPRE deben ser profesionales.
- **Control de Versiones (GitHub):** Todo cambio finalizado debe respaldarse.

## El Bucle Central
1. **Consultar/Crear:** Leer esta directiva ANTES de codificar.
2. **Ejecutar:** Programar el código basándome *estrictamente* en esta lógica.
3. **Observar y Aprender:** Actualizar la sección de "Restricciones" si ocurre algún fallo.

## Restricciones / Casos Borde (Memoria Viva)
> *Nota: Todo aprendizaje nuevo tras un error se documenta aquí.*
- **Unicidad de Legajos:** Los legajos de los alumnos DEBEN ser únicos y autogenerados secuencialmente desde el mayor existente para evitar colisiones.
- **Vulnerabilidades CSRF:** Todo formulario AJAX que modifique datos (POST) debe validar un token de seguridad.
- **Vulnerabilidades XSS (DOM):** Al insertar datos JSON en el DOM mediante JavaScript (`.html()`), los datos DEBEN estar escapados (sanitizados), o usar textContent (`.text()`).
- **Seguridad de Respaldos:** La carpeta `respaldos/` contiene información extremadamente sensible (.sql). DEBE contar obligatoriamente con un archivo `.htaccess` con `Require all denied` para prevenir descargas directas no autorizadas.
