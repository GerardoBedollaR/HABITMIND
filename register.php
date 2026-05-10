<?php
// register.php
session_start();
require __DIR__ . '/db.php';

// Solo aceptar peticiones POST (vienen del formulario)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si alguien entra directo a register.php por URL, lo regresamos al login
    header('Location: login.php');
    exit;
}

// 1. Tomar y limpiar datos del formulario
$nombre  = trim($_POST['nombre_completo'] ?? '');
$correo  = trim($_POST['correo'] ?? '');
$edad    = trim($_POST['edad'] ?? '');
$genero  = $_POST['genero'] ?? 'O';
$pass    = $_POST['password'] ?? '';
$pass2   = $_POST['password2'] ?? '';

$errores = [];

// 2. Validaciones básicas
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

// Edad opcional, pero si viene, debe ser numérica y positiva
if ($edad !== '' && !ctype_digit($edad)) {
    $errores[] = 'La edad debe ser un número entero positivo.';
}

// 3. Validar que el correo no exista ya
if (!$errores) {
    $sql = "SELECT id_usuario FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errores[] = 'Ya existe una cuenta registrada con ese correo.';
        }

        $stmt->close();
    } else {
        $errores[] = 'Error interno al preparar la consulta (SELECT).';
    }
}

// 4. Si hay errores, regresamos al login con los errores en sesión
if ($errores) {
    $_SESSION['register_errors'] = $errores;
    header('Location: login.php#registro');
    exit;
}


// 5. Insertar el usuario en la BD
$hash = password_hash($pass, PASSWORD_DEFAULT);
$edad_int = ($edad === '') ? null : (int)$edad;

$sql = "INSERT INTO usuarios (nombre_completo, correo, edad, genero, password_hash)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Error al preparar el INSERT: ' . $conn->error);
}

// 's' = string, 'i' = int
$stmt->bind_param(
    "ssiss",
    $nombre,
    $correo,
    $edad_int,
    $genero,
    $hash
);

if ($stmt->execute()) {
    // Guardamos mensaje de éxito y regresamos al login, sección registro
    $_SESSION['register_success'] = 'Tu cuenta ha sido creada correctamente. Ahora puedes iniciar sesión.';
    header('Location: login.php#registro');
    exit;
} else {
    echo "<h2>Error</h2>";
    echo "<p>No se pudo registrar el usuario: " . htmlspecialchars($stmt->error) . "</p>";
    echo '<p><a href="login.php">Volver</a></p>';
}


$stmt->close();
$conn->close();
