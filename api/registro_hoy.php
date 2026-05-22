<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

$id_habito = intval($_POST['id_habito'] ?? 0);

if ($id_habito <= 0) {
    echo json_encode([
        'ok' => false,
        'error' => 'id_habito inválido'
    ]);
    exit;
}

$fecha = date('Y-m-d');

$stmt = $conn->prepare("
    INSERT INTO registros_habito (id_habito, fecha, estado)
    VALUES (?, ?, 'completado')
    ON DUPLICATE KEY UPDATE
        estado = 'completado',
        fecha_registro = CURRENT_TIMESTAMP
");

$stmt->bind_param("is", $id_habito, $fecha);

if ($stmt->execute()) {
    echo json_encode([
        'ok' => true,
        'mensaje' => 'Hábito registrado correctamente ✅'
    ]);
} else {
    echo json_encode([
        'ok' => false,
        'error' => 'No se pudo registrar el hábito'
    ]);
}