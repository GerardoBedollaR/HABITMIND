<?php
header('Content-Type: application/json; charset=utf-8');

$habitos = [
  [
    "id_habito" => 1,
    "nombre" => "Hacer ejercicio",
    "descripcion" => "Realizar actividad física diaria.",
    "dificultad" => "media",
    "privacidad" => "privada"
  ],
  [
    "id_habito" => 2,
    "nombre" => "Beber agua",
    "descripcion" => "Tomar suficiente agua durante el día.",
    "dificultad" => "baja",
    "privacidad" => "privada"
  ],
  [
    "id_habito" => 3,
    "nombre" => "Leer 20 min",
    "descripcion" => "Leer al menos 20 minutos diarios.",
    "dificultad" => "baja",
    "privacidad" => "privada"
  ],
  [
    "id_habito" => 4,
    "nombre" => "Meditar",
    "descripcion" => "Practicar respiración o meditación breve.",
    "dificultad" => "media",
    "privacidad" => "privada"
  ],
  [
    "id_habito" => 5,
    "nombre" => "Comer saludable",
    "descripcion" => "Elegir comidas más saludables.",
    "dificultad" => "media",
    "privacidad" => "privada"
  ]
];

echo json_encode($habitos);