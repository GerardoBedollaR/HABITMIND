<header class="hm-header-app">
  <div class="hm-header-app-left">
    <img src="assets/img/logo.png" alt="HabitMind Logo" class="hm-logo-img">
    <div class="hm-logo-text">HABITMIND</div>
  </div>

 <nav class="hm-header-nav">
    <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">Inicio</a>
    <a href="registro_hoy.php" class="<?= basename($_SERVER['PHP_SELF']) === 'registro_hoy.php' ? 'active' : '' ?>">Asistente</a>
    <a href="progreso.php" class="<?= basename($_SERVER['PHP_SELF']) === 'progreso.php' ? 'active' : '' ?>">Progreso</a>
  </nav>

  <div class="hm-header-app-right">
    <form action="logout.php" method="post" style="margin:0;">
      <button type="submit" class="hm-btn-logout">Salir</button>
    </form>
  </div>
</header>