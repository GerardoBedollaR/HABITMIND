<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once '../db.php';


if (!isset($_SESSION['id_usuario'])) {

    echo json_encode([
        'ok' => false,
        'error' => 'Sesión no iniciada'
    ]);

    exit;
}


$id_usuario = (int) $_SESSION['id_usuario'];

$nombre = trim($_POST['nombre'] ?? '');
$dificultad = trim($_POST['dificultad'] ?? 'baja');


if ($nombre === '') {

    echo json_encode([
        'ok' => false,
        'error' => 'Nombre vacío'
    ]);

    exit;
}


$colores = [
    'baja' => '#22c55e',
    'media' => '#fb923c',
    'alta' => '#ef4444'
];

$color = $colores[$dificultad] ?? '#22c55e';

$icono = '✅';


$sql = "
INSERT INTO habitos
(
    id_usuario,
    nombre,
    dificultad,
    color,
    icono,
    activo
)
VALUES
(
    ?, ?, ?, ?, ?, 1
)
";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    echo json_encode([
        'ok' => false,
        'error' => $conn->error
    ]);

    exit;
}


$stmt->bind_param(
    "issss",
    $id_usuario,
    $nombre,
    $dificultad,
    $color,
    $icono
);


if ($stmt->execute()) {

    echo json_encode([
        'ok' => true,
        'mensaje' => 'Hábito creado'
    ]);

} else {

    echo json_encode([
        'ok' => false,
        'error' => $stmt->error
    ]);
}