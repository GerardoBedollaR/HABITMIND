from typing import Any, Text, Dict, List
import re

import mysql.connector
from rasa_sdk import Action, Tracker
from rasa_sdk.executor import CollectingDispatcher


def conectar_db():
    return mysql.connector.connect(
        host="127.0.0.1",
        user="root",
        password="root",
        database="habitmind",
        port=3306
    )


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

        texto = mensaje.lower().strip()

        patrones = [
            r"quiero crear el hábito de (.+)",
            r"quiero crear el habito de (.+)",
            r"crear el hábito de (.+)",
            r"crear el habito de (.+)",
            r"crea el hábito de (.+)",
            r"crea el habito de (.+)",
            r"agrega el hábito de (.+)",
            r"agrega el habito de (.+)",
            r"añade el hábito de (.+)",
            r"añade el habito de (.+)",
            r"nuevo hábito de (.+)",
            r"nuevo habito de (.+)",
            r"quiero agregar (.+)",
            r"quiero añadir (.+)",
            r"agrega (.+)",
            r"añade (.+)",
        ]

        nombre_habito = ""

        for patron in patrones:
            match = re.search(patron, texto)
            if match:
                nombre_habito = match.group(1).strip()
                break

        if not nombre_habito:
            nombre_habito = texto

        nombre_habito = nombre_habito.strip()

        if not nombre_habito:
            dispatcher.utter_message(
                text="No pude identificar el nombre del hábito."
            )
            return []

        nombre_habito = nombre_habito.capitalize()

        try:
            conexion = conectar_db()
            cursor = conexion.cursor()

            sql = """
                INSERT INTO habitos (
                    id_usuario,
                    nombre
                )
                VALUES (%s, %s)
            """

            valores = (1, nombre_habito)

            cursor.execute(sql, valores)
            conexion.commit()

            cursor.close()
            conexion.close()

            dispatcher.utter_message(
                text=f"Perfecto ✨ He creado el hábito: '{nombre_habito}'."
            )

        except Exception as e:
            dispatcher.utter_message(
                text=f"Ocurrió un error al guardar el hábito: {str(e)}"
            )

        return []