@"
# HabitMind

HabitMind es una aplicación web para el seguimiento de hábitos personales con integración de un asistente conversacional desarrollado con Rasa.

## Tecnologías utilizadas

- PHP
- MySQL
- JavaScript
- HTML/CSS
- Rasa Open Source
- Python 3.10
- XAMPP

## Estructura principal

- /api: endpoints PHP para hábitos, registros y estadísticas.
- /assets: recursos visuales del sistema.
- /js: scripts del frontend.
- /partials: componentes reutilizables de la interfaz.
- /rasa-bot: proyecto del asistente conversacional en Rasa.

## Ejecución local

1. Activar Apache y MySQL en XAMPP.
2. Abrir la página:

http://localhost/HABITMIND/

3. Levantar Rasa:

```powershell
cd C:\xampp\htdocs\HABITMIND
.\.venv-rasa\Scripts\activate
cd rasa-bot
rasa run --enable-api --cors "*" --port 5005