<?php
// api/registro_hoy.php
// API DEMO: registra un hábito para la fecha de hoy (sin BD)

header('Content-Type: application/json; charset=utf-8');

// Solo permitimos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'ok'    => false,
        'error' => 'Solo se permite el método POST'
    ]);
    exit;
}

// Leer id_habito
$id = $_POST['id_habito'] ?? '';

if ($id === '' || !ctype_digit($id)) {
    echo json_encode([
        'ok'    => false,
        'error' => 'id_habito inválido'
    ]);
    exit;
}

// Aquí en la versión final iría la inserción en la base de datos.
// Por ahora solo simulamos una respuesta exitosa.
echo json_encode([
    'ok'      => true,
    'mensaje' => 'Hábito registrado para hoy (demo).',
    'id_habito' => (int)$id,
    'fecha'   => date('Y-m-d')
]);
