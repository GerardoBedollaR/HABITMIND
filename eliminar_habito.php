<?php
session_start();

/*
|--------------------------------------------------------------------------
| CONEXIÓN A LA BASE DE DATOS
|--------------------------------------------------------------------------
| Ajusta estos datos si en tu proyecto son diferentes
*/
$host = "127.0.0.1";
$usuario = "root";
$contrasena = "root";
$bd = "habitmind";
$puerto = 3306;

$conexion = new mysqli($host, $usuario, $contrasena, $bd, $puerto);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

/*
|--------------------------------------------------------------------------
| VALIDAR DATOS
|--------------------------------------------------------------------------
*/
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id_habito"])) {
    $id_habito = (int) $_POST["id_habito"];

    // Si manejas sesiones de usuario reales, usa esto:
    // $id_usuario = $_SESSION['id_usuario'] ?? 0;

    // Si por ahora todo está en pruebas, puedes dejar 1:
    $id_usuario = $_SESSION['id_usuario'] ?? 1;

    /*
    |--------------------------------------------------------------------------
    | ELIMINAR SOLO EL HÁBITO DEL USUARIO ACTUAL
    |--------------------------------------------------------------------------
    */
    $stmt = $conexion->prepare("DELETE FROM habitos WHERE id_habito = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $id_habito, $id_usuario);

    if ($stmt->execute()) {
        $stmt->close();
        $conexion->close();

        // Regresa a la página anterior
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        echo "Error al eliminar el hábito.";
    }

    $stmt->close();
}

$conexion->close();
?>