<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

session_start();
$id_usuario = $_SESSION['id_usuario'] ?? 0;
$hoy = date('Y-m-d');

$totalHabitos = 0;
$completadosHoy = 0;
$porcentajeHoy = 0;

// Total hábitos activos
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM habitos
    WHERE id_usuario = ? AND activo = 1
");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$totalHabitos = (int) $stmt->get_result()->fetch_assoc()['total'];

// Hábitos completados hoy
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM registros_habito r
    INNER JOIN habitos h ON h.id_habito = r.id_habito
    WHERE h.id_usuario = ?
      AND r.fecha = ?
      AND r.estado = 'completado'
");
$stmt->bind_param("is", $id_usuario, $hoy);
$stmt->execute();
$completadosHoy = (int) $stmt->get_result()->fetch_assoc()['total'];

if ($totalHabitos > 0) {
    $porcentajeHoy = round(($completadosHoy / $totalHabitos) * 100);
}

// Registros últimos 7 días
$stmt = $conn->prepare("
    SELECT 
        DATE(r.fecha) AS fecha,
        COUNT(*) AS completados
    FROM registros_habito r
    INNER JOIN habitos h ON h.id_habito = r.id_habito
    WHERE h.id_usuario = ?
      AND r.estado = 'completado'
      AND r.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY r.fecha
    ORDER BY r.fecha ASC
");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

$semana = [];
while ($row = $res->fetch_assoc()) {
    $semana[] = $row;
}

echo json_encode([
    'ok' => true,
    'total_habitos' => $totalHabitos,
    'completados_hoy' => $completadosHoy,
    'porcentaje_hoy' => $porcentajeHoy,
    'racha_actual' => $completadosHoy > 0 ? 1 : 0,
    'semana' => $semana
]);