<?php
// procesar_login.php
session_start();
require __DIR__ . '/db.php';

// 1. Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// 2. Tomar datos del formulario
$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

// 3. Validación básica
if ($email === '' || $pass === '') {
    $_SESSION['login_error'] = 'Por favor ingresa tu correo y contraseña.';
    header('Location: login.php');
    exit;
}

// 4. Buscar usuario por correo
$sql  = "SELECT id_usuario, nombre_completo, correo, password_hash
         FROM usuarios
         WHERE correo = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $_SESSION['login_error'] = 'Error interno en el servidor (prepare).';
    header('Location: login.php');
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // No existe ese correo
    $_SESSION['login_error'] = 'Correo o contraseña incorrectos.';
    header('Location: login.php');
    exit;
}

$usuario = $result->fetch_assoc();
$stmt->close();

// 5. Verificar la contraseña
if (!password_verify($pass, $usuario['password_hash'])) {
    $_SESSION['login_error'] = 'Correo o contraseña incorrectos.';
    header('Location: login.php');
    exit;
}

// 6. Login correcto: guardar datos en sesión
$_SESSION['id_usuario']      = $usuario['id_usuario'];
$_SESSION['usuario_nombre']  = $usuario['nombre_completo'];
$_SESSION['usuario_email']   = $usuario['correo'];

// 7. Redirigir al dashboard (ajusta el archivo si usas otro)
header('Location: dashboard.php');
exit;
