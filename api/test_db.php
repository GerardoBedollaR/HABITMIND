<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

echo json_encode([
    'ok' => true,
    'mensaje' => 'Conexión MySQL funcionando correctamente'
]);