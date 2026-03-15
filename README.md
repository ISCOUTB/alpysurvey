# ALPY Survey

ALPY Survey es un módulo de actividad para Moodle diseñado para recopilar retroalimentación de estudiantes sobre los recursos de aprendizaje proporcionados por la plataforma ALPY. Este módulo permite a los estudiantes responder una encuesta estructurada que evalúa su experiencia con los recursos de aprendizaje, incluyendo simulaciones, proyectos, tutoriales en video, presentaciones y lecturas.

## Características

- **Encuesta estructurada**: Incluye 16 preguntas Likert y una pregunta de ranking para evaluar la experiencia del estudiante.
- **Dimensiones de evaluación**:
  - D1: Tipo y orden de recursos
  - D2: Interacción de contenidos y actividades
  - D3: Familiaridad con el uso de ALPY en SAVIO
- **Cálculo automático de puntajes**: Genera puntajes promedio para cada dimensión y un puntaje total.
- **Restricciones de tiempo**: Permite configurar fechas de apertura y cierre de la encuesta.
- **Calificación**: Asigna una calificación máxima configurable.
- **Reportes para docentes**: Vista de respuestas individuales, exportación a CSV, eliminación de respuestas.
- **Un intento por estudiante**: Cada estudiante puede completar la encuesta solo una vez.

## Requisitos

- Moodle 4.1 o superior
- PHP compatible con Moodle

## Instalación

1. Descarga el código del módulo.
2. Descomprímelo en el directorio `mod/alpysurvey` de tu instalación de Moodle.
3. Accede al panel de administración de Moodle y instala el plugin desde "Notificaciones" o "Plugins".
4. El módulo se instalará automáticamente creando las tablas necesarias en la base de datos.

## Uso

### Para estudiantes
- Accede a la actividad ALPY Survey en el curso.
- Completa la encuesta respondiendo las preguntas Likert (escala de 1 a 5) y el ranking de recursos.
- Envía tus respuestas. Una vez enviadas, podrás ver tus puntajes calculados.

### Para docentes
- Crea una instancia de la actividad ALPY Survey en tu curso.
- Configura el nombre, descripción, fechas de disponibilidad y calificación máxima.
- Una vez que los estudiantes hayan respondido, accede a la actividad para ver los reportes.
- Exporta los datos a CSV para análisis adicional.
- Elimina respuestas individuales si es necesario (el estudiante podrá volver a responder).

## Estructura del código

- `lib.php`: Funciones principales del módulo.
- `locallib.php`: Funciones locales y formularios.
- `mod_form.php`: Formulario de configuración de la actividad.
- `view.php`: Vista principal para estudiantes.
- `submit.php`: Procesamiento de envío de respuestas.
- `report.php`: Generación de reportes para docentes.
- `delete_response.php`: Eliminación de respuestas.
- `db/`: Esquemas de base de datos e instalación.
- `lang/`: Archivos de idioma (inglés y español).
- `classes/`: Clases de eventos y privacidad.
- `backup/`: Funcionalidad de respaldo y restauración.

## Licencia

Este plugin está licenciado bajo GNU GPL v3 o posterior.

## Contribuciones

Copyright 2026. Desarrollado para la plataforma ALPY y SAVIO.

## Versión

Versión 1.0.0-beta (Moodle 4.1+)</content>
<parameter name="filePath">c:\Users\yuran\proyectos\alpysurvey\README.md