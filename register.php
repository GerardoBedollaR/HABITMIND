<?php
// register.php
session_start();
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$nombre = trim($_POST['nombre'] ?? $_POST['nombre_completo'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$pass   = $_POST['password'] ?? '';
$pass2  = $_POST['password2'] ?? '';

$errores = [];

if ($nombre === '' || $correo === '' || $pass === '' || $pass2 === '') {
    $errores[] = 'Todos los campos obligatorios deben estar llenos.';
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El correo electrónico no tiene un formato válido.';
}

if ($pass !== $pass2) {
    $errores[] = 'Las contraseñas no coinciden.';
}

if (strlen($pass) < 6) {
    $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
}

if (!$errores) {
    $sql = "SELECT id_usuario FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $errores[] = 'Error interno al preparar la consulta.';
    } else {
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errores[] = 'Ya existe una cuenta registrada con ese correo.';
        }

        $stmt->close();
    }
}

if ($errores) {
    $_SESSION['register_errors'] = $errores;
    header('Location: login.php#registro');
    exit;
}

$password_hash = password_hash($pass, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre, correo, password_hash)
        VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Error al preparar el INSERT: ' . $conn->error);
}

$stmt->bind_param("sss", $nombre, $correo, $password_hash);

if ($stmt->execute()) {
    $_SESSION['register_success'] = 'Tu cuenta ha sido creada correctamente. Ahora puedes iniciar sesión.';
    header('Location: login.php#registro');
    exit;
}

$_SESSION['register_errors'] = ['No se pudo registrar el usuario: ' . $stmt->error];
header('Location: login.php#registro');
exit;