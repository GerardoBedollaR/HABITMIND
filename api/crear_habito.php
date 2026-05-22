<?php
header('Content-Type: application/json; charset=utf-8');

require_once 'db.php';

$nombre = trim($_POST['nombre'] ?? '');

if ($nombre === '') {
    echo json_encode([
        'ok' => false,
        'error' => 'Nombre inválido'
    ]);
    exit;
}

session_start();
$id_usuario = $_SESSION['id_usuario'] ?? 0;

$stmt = $conn->prepare("
    INSERT INTO habitos
    (
        id_usuario,
        nombre
    )
    VALUES (?, ?)
");

$stmt->bind_param(
    "is",
    $id_usuario,
    $nombre
);

if ($stmt->execute()) {

    echo json_encode([
        'ok' => true,
        'mensaje' => 'Hábito creado correctamente'
    ]);

} else {

    echo json_encode([
        'ok' => false,
        'error' => 'Error al crear hábito'
    ]);
}