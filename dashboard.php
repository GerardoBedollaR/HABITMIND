<?php
// dashboard.php
session_start();

// PROTECCIÓN: si no hay usuario en sesión, regresar al login
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$usuario_email  = $_SESSION['usuario_email']  ?? '';
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'HabitMinder';
?>



<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>HabitMind — Panel de Usuario</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/habitmind.css">
</head>
<body class="hm-app-page">

<!-- HEADER APP HABITMIND -->
  <header class="hm-header-app">
    <div class="hm-header-app-left">
      <img src="assets/img/logo.png" alt="HabitMind Logo" class="hm-logo-img">
      <div class="hm-logo-text">HABITMIND</div>
    </div>

    <!-- Configuración + Salir -->
    <div class="hm-header-app-right">
      <button type="button" class="hm-config-pill">
        Configuración ⚙
      </button>

      <form action="logout.php" method="post" style="margin:0;">
        <button type="submit" class="hm-btn-logout">
          Salir
        </button>
      </form>
    </div>
  </header>

<main class="hm-app-main">
  <!-- Sidebar -->
  <aside class="hm-sidebar">
    <div class="hm-sidebar-header">Mis hábitos</div>

    <div class="hm-sidebar-search">
      <input type="text" placeholder="🔍 Buscar" class="hm-input">
    </div>

    <div class="hm-sidebar-list">
      <div class="hm-habit-item">
        <span class="hm-habit-dot" style="background:#10b981;"></span> Hacer ejercicio
      </div>
      <div class="hm-habit-item">
        <span class="hm-habit-dot" style="background:#38bdf8;"></span> Beber agua
      </div>
      <div class="hm-habit-item">
        <span class="hm-habit-dot" style="background:#facc15;"></span> Leer 20 min
      </div>
      <div class="hm-habit-item">
        <span class="hm-habit-dot" style="background:#f97316;"></span> Meditar
      </div>
      <div class="hm-habit-item">
        <span class="hm-habit-dot" style="background:#22c55e;"></span> Comer saludable
      </div>
    </div>

    <div class="hm-sidebar-add">+ Agregar hábito</div>

    <a href="registro_hoy.php" class="hm-sidebar-progress-btn" style="text-decoration:none; text-align:center;">
      Continuar chat 🤖
    </a>
  </aside>

  <!-- Contenido principal -->
  <section class="hm-app-content">
    <h2 class="hm-dashboard-title">Panel de Usuario</h2>
    <p style="margin-bottom:16px;">
      Hola, <strong><?= htmlspecialchars($usuario_nombre) ?></strong>.  
      Este es un resumen rápido de tus hábitos (demo).
    </p>

    <div class="hm-dashboard-grid">
      <div class="hm-dashboard-card">
        🔥<br>Racha actual<br><span style="font-size:22px;">7 días</span>
      </div>
      <div class="hm-dashboard-card">
        ✅<br>Hábitos cumplidos<br><span style="font-size:22px;">15</span>
      </div>
      <div class="hm-dashboard-card">
        ⏰<br>Recordatorios hoy<br><span style="font-size:22px;">3</span>
      </div>
      <div class="hm-dashboard-card">
        📈<br>Nivel de progreso<br><span style="font-size:22px;">82%</span>
      </div>
    </div>
  </section>
</main>

<?php include 'partials/footer.php'; ?>
</body>
</html>
