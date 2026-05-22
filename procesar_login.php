<?php
session_start();
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$correo = trim($_POST['email'] ?? $_POST['correo'] ?? '');
$pass   = $_POST['password'] ?? '';

if ($correo === '' || $pass === '') {
    $_SESSION['login_error'] = 'Ingresa correo y contraseña.';
    header('Location: login.php');
    exit;
}

$sql = "SELECT id_usuario, nombre, correo, password_hash
        FROM usuarios
        WHERE correo = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Error al preparar login: ' . $conn->error);
}

$stmt->bind_param("s", $correo);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $_SESSION['login_error'] = 'Correo o contraseña incorrectos.';
    header('Location: login.php');
    exit;
}

$usuario = $resultado->fetch_assoc();

if (!password_verify($pass, $usuario['password_hash'])) {
    $_SESSION['login_error'] = 'Correo o contraseña incorrectos.';
    header('Location: login.php');
    exit;
}

$_SESSION['id_usuario'] = $usuario['id_usuario'];
$_SESSION['usuario_nombre'] = $usuario['nombre'];
$_SESSION['usuario_email'] = $usuario['correo'];

header('Location: dashboard.php');
exit;