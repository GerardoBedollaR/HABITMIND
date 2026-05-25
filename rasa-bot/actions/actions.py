from typing import Any, Text, Dict, List, Optional
import os
import re
import unicodedata

import mysql.connector
from mysql.connector import Error

from rasa_sdk import Action, Tracker
from rasa_sdk.executor import CollectingDispatcher
from rasa_sdk.events import SlotSet


# ============================================================
# CONFIGURACIÓN DE BASE DE DATOS
# ============================================================
# Por defecto se usan los datos que ya tenías:
# host: 127.0.0.1
# user: root
# password: root
# database: habitmind
# port: 3306
#
# Si después quieres cambiarlos sin tocar el código, puedes usar:
# HABITMIND_DB_HOST
# HABITMIND_DB_USER
# HABITMIND_DB_PASSWORD
# HABITMIND_DB_NAME
# HABITMIND_DB_PORT
# HABITMIND_USER_ID
# ============================================================


def conectar_db():
    return mysql.connector.connect(
        host=os.getenv("HABITMIND_DB_HOST", "127.0.0.1"),
        user=os.getenv("HABITMIND_DB_USER", "root"),
        password=os.getenv("HABITMIND_DB_PASSWORD", "root"),
        database=os.getenv("HABITMIND_DB_NAME", "habitmind"),
        port=int(os.getenv("HABITMIND_DB_PORT", "3306")),
    )


def obtener_id_usuario() -> int:
    """
    ID temporal del usuario.
    Por ahora se usa 1 porque normalmente en pruebas locales
    el chatbot no tiene todavía la sesión real del usuario PHP.

    Más adelante este valor puede venir desde la sesión del sistema web.
    """
    try:
        return int(os.getenv("HABITMIND_USER_ID", "1"))
    except ValueError:
        return 1


def quitar_acentos(texto: str) -> str:
    """
    Convierte texto con acentos a texto sin acentos.
    Ejemplo: hábito -> habito
    """
    texto_normalizado = unicodedata.normalize("NFD", texto)
    texto_sin_acentos = "".join(
        caracter for caracter in texto_normalizado
        if unicodedata.category(caracter) != "Mn"
    )
    return texto_sin_acentos


def limpiar_nombre_habito(texto: str) -> str:
    """
    Limpia el nombre del hábito para evitar guardar frases sucias como:
    - "quiero agregar tomar agua"
    - "crea el hábito de leer todos los días"
    - "agrega ejercicio por favor"

    Devuelve solamente el nombre más probable del hábito.
    """

    if not texto:
        return ""

    texto_original = texto.strip()
    texto_minusculas = texto_original.lower().strip()
    texto_sin_acentos = quitar_acentos(texto_minusculas)

    patrones = [
        r"quiero crear el habito de (.+)",
        r"quiero crear un habito de (.+)",
        r"quiero crear un habito llamado (.+)",
        r"quiero crear el habito llamado (.+)",
        r"quiero registrar el habito de (.+)",
        r"quiero registrar un habito de (.+)",
        r"quiero registrar (.+)",
        r"registrar el habito de (.+)",
        r"registrar un habito de (.+)",
        r"registrar (.+)",
        r"crear el habito de (.+)",
        r"crear un habito de (.+)",
        r"crear habito de (.+)",
        r"crea el habito de (.+)",
        r"crea un habito de (.+)",
        r"crea (.+)",
        r"agrega el habito de (.+)",
        r"agrega un habito de (.+)",
        r"agrega (.+)",
        r"agregar el habito de (.+)",
        r"agregar un habito de (.+)",
        r"agregar (.+)",
        r"anade el habito de (.+)",
        r"anade un habito de (.+)",
        r"anade (.+)",
        r"añade el hábito de (.+)",
        r"añade un hábito de (.+)",
        r"añade (.+)",
        r"nuevo habito de (.+)",
        r"nuevo habito llamado (.+)",
        r"habito nuevo de (.+)",
    ]

    nombre_habito = ""

    for patron in patrones:
        match = re.search(patron, texto_sin_acentos)
        if match:
            nombre_habito = match.group(1).strip()
            break

    if not nombre_habito:
        nombre_habito = texto_sin_acentos.strip()

    # Eliminar frases finales comunes que no forman parte del nombre.
    frases_finales = [
        " por favor",
        " porfa",
        " gracias",
        " para hoy",
        " hoy",
        " diario",
        " todos los dias",
        " todos los días",
        " cada dia",
        " cada día",
    ]

    for frase in frases_finales:
        if nombre_habito.endswith(frase):
            nombre_habito = nombre_habito[: -len(frase)].strip()

    # Eliminar signos innecesarios.
    nombre_habito = re.sub(r"[¿?¡!.,;:]+", "", nombre_habito)
    nombre_habito = re.sub(r"\s+", " ", nombre_habito).strip()

    # Capitalizar de forma más natural.
    nombre_habito = nombre_habito.capitalize()

    return nombre_habito


def nombre_habito_valido(nombre_habito: str) -> bool:
    """
    Valida que el hábito no esté vacío ni sea una frase demasiado genérica.
    """

    if not nombre_habito:
        return False

    if len(nombre_habito) < 3:
        return False

    if len(nombre_habito) > 100:
        return False

    nombres_invalidos = {
        "Habito",
        "Hábito",
        "Un habito",
        "Un hábito",
        "El habito",
        "El hábito",
        "Nuevo habito",
        "Nuevo hábito",
        "Crear",
        "Agregar",
        "Registrar",
        "Añadir",
    }

    if nombre_habito in nombres_invalidos:
        return False

    return True


def existe_habito(cursor, id_usuario: int, nombre_habito: str) -> bool:
    """
    Verifica si el hábito ya existe para el usuario.
    Esto evita guardar duplicados como:
    - Leer
    - leer
    - LEER
    """

    sql = """
        SELECT id
        FROM habitos
        WHERE id_usuario = %s
          AND LOWER(nombre) = LOWER(%s)
        LIMIT 1
    """

    cursor.execute(sql, (id_usuario, nombre_habito))
    resultado = cursor.fetchone()

    return resultado is not None


# ============================================================
# ACCIÓN PRINCIPAL: CREAR HÁBITO
# ============================================================

class ActionCrearHabito(Action):

    def name(self) -> Text:
        return "action_crear_habito"

    def run(
        self,
        dispatcher: CollectingDispatcher,
        tracker: Tracker,
        domain: Dict[Text, Any]
    ) -> List[Dict[Text, Any]]:

        mensaje = tracker.latest_message.get("text", "")

        print("=== ACTION CREAR HABITO EJECUTADA ===")
        print("MENSAJE RECIBIDO:", mensaje)

        nombre_habito = limpiar_nombre_habito(mensaje)

        print("HÁBITO DETECTADO:", nombre_habito)

        if not nombre_habito_valido(nombre_habito):
            dispatcher.utter_message(
                text=(
                    "No pude identificar correctamente el nombre del hábito. "
                    "Intenta escribirlo así: “quiero crear el hábito de tomar agua”."
                )
            )
            return []

        conexion = None
        cursor = None

        try:
            id_usuario = obtener_id_usuario()

            conexion = conectar_db()
            cursor = conexion.cursor()

            if existe_habito(cursor, id_usuario, nombre_habito):
                dispatcher.utter_message(
                    text=(
                        f"El hábito “{nombre_habito}” ya existe en tu lista. "
                        f"No lo volví a crear para evitar duplicados."
                    )
                )

                return [
                    SlotSet("habit_name", nombre_habito)
                ]

            sql = """
                INSERT INTO habitos (
                    id_usuario,
                    nombre
                )
                VALUES (%s, %s)
            """

            valores = (id_usuario, nombre_habito)

            cursor.execute(sql, valores)
            conexion.commit()

            dispatcher.utter_message(
                text=(
                    f"Perfecto. He creado el hábito: “{nombre_habito}”.\n\n"
                    f"Recuerda registrar tu avance diario en el apartado "
                    f"“Registrar hoy” para que HabitMind pueda medir tu progreso."
                )
            )

            return [
                SlotSet("habit_name", nombre_habito)
            ]

        except Error as e:
            print("ERROR MYSQL:", str(e))

            dispatcher.utter_message(
                text=(
                    "Ocurrió un error al guardar el hábito en la base de datos.\n\n"
                    f"Detalle técnico: {str(e)}"
                )
            )

            return []

        except Exception as e:
            print("ERROR GENERAL:", str(e))

            dispatcher.utter_message(
                text=(
                    "Ocurrió un error inesperado al crear el hábito.\n\n"
                    f"Detalle técnico: {str(e)}"
                )
            )

            return []

        finally:
            if cursor is not None:
                cursor.close()

            if conexion is not None and conexion.is_connected():
                conexion.close()


# ============================================================
# ACCIONES DE COMPATIBILIDAD
# ============================================================
# Estas acciones sirven por si en domain.yml, rules.yml o stories.yml
# ya tienes nombres como action_registrar_habito.
#
# Internamente reutilizan la misma lógica de ActionCrearHabito.
# ============================================================

class ActionRegistrarHabito(ActionCrearHabito):

    def name(self) -> Text:
        return "action_registrar_habito"


class ActionSubmitHabitRegistration(ActionCrearHabito):

    def name(self) -> Text:
        return "action_submit_habit_registration"