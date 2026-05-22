<?php
header('Content-Type: application/json; charset=utf-8');

$id = isset($_GET['id_habito']) ? intval($_GET['id_habito']) : 0;

if ($id <= 0) {
  echo json_encode([]);
  exit;
}

$dias = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];

$data = [];
foreach ($dias as $i => $dia) {
  $data[] = [
    "dia" => $dia,
    "checks" => rand(0, 1)
  ];
}

echo json_encode($data);