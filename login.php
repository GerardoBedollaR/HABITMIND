<?php
// login.php
session_start();

// Mensaje de error de login 
$login_error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

// NUEVO: mensaje de éxito de registro
$register_success = $_SESSION['register_success'] ?? '';
unset($_SESSION['register_success']);

// Errores del registro 
$register_errors = $_SESSION['register_errors'] ?? [];
unset($_SESSION['register_errors']);

?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>HabitMind — Inicia sesión</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/habitmind.css">
</head>
<body class="hm-landing-page">

 <!-- HEADER PÚBLICO HABITMIND -->
  <header class="hm-header-public">
    <!-- Logo + texto -->
  <div class="hm-header-app-left">
    <img src="assets/img/logo.png" alt="HabitMind Logo" class="hm-logo-img">
    <div class="hm-logo-text">HABITMIND</div>
  </div>

    <nav class="hm-nav-public">
      <a href="index.php" class="hm-nav-link">Inicio</a>
      <a href="index.php#contacto" class="hm-nav-link">Contacto</a>
      <a href="index.php#beneficios" class="hm-nav-link">Beneficios</a>
    </nav>

    <div class="hm-nav-right">
      <a href="login.php" class="hm-btn-small-outline">Iniciar sesión</a>
    </div>
  </header>

  <!-- ====================== LOGIN LAYOUT ======================= -->
  <main class="hm-login-layout">

    <!-- Columna izquierda: ilustración -->
    <section class="hm-login-left">
      <img src="assets/img/undraw_fingerprint-login_19qv.svg" alt="HabitMind Logo" class="hm-login-illustration-img">
    </section>


    <!-- Columna derecha: login + registro -->
    <section class="hm-login-right">

      <!-- círculo grande decorativo -->
      <div class="hm-login-bg-circle"></div>

      <!-- Tarjeta pequeña: INICIA SESIÓN -->
      <div class="hm-login-card-small">
        <h2 class="hm-login-card-title">Inicia sesión en HabitMind</h2>

        <?php if (!empty($login_error)) : ?>
          <div class="hm-alert hm-alert-error">
            <?= htmlspecialchars($login_error) ?>
          </div>
        <?php endif; ?>

        <form action="procesar_login.php" method="post">
          <div class="hm-form-group">
            <label for="email_login">Correo electrónico</label>
            <input
              type="email"
              id="email_login"
              name="email"
              class="hm-input"
              required
            >
          </div>

          <div class="hm-form-group">
            <label for="password_login">Contraseña</label>
            <input
              type="password"
              id="password_login"
              name="password"
              class="hm-input"
              required
            >
          </div>

          <button type="submit" class="hm-btn-full hm-btn-full-primary">
            Iniciar sesión
          </button>

          <p class="hm-login-small-note">
            ¿No tienes cuenta? <a href="#registro">Regístrate aquí ✨</a>
          </p>
        </form>
      </div>

      <!-- Panel grande: CREA TU CUENTA -->
      <section class="hm-register-panel" id="registro">
        <h2 class="hm-register-title">Crea tu cuenta en HabitMind</h2>

        <!-- Mensaje de éxito del registro -->
        <?php if (!empty($register_success)) : ?>
          <div class="hm-alert hm-alert-success">
            <?= htmlspecialchars($register_success) ?>
          </div>
        <?php endif; ?>


        <!-- Por ahora solo demo, puedes cambiar el action después -->
        <form action="register.php" method="post">

          <div class="hm-form-group">
            <label for="reg_nombre">Nombre completo</label>
            <input
              type="text"
              id="reg_nombre"
              name="nombre_completo"
              class="hm-input"
              required
            >
          </div>

          <div class="hm-form-group">
            <label for="reg_email">Correo electrónico</label>
            <input
              type="email"
              id="reg_email"
              name="correo"
              class="hm-input"
              required
            >
          </div>

          <div class="hm-register-row">
            <div class="hm-form-group">
              <label for="reg_edad">Edad</label>
              <input
                type="number"
                id="reg_edad"
                name="edad"
                class="hm-input"
                min="10"
                max="99"
              >
            </div>

            <div class="hm-form-group">
              <label>Género</label>
              <div class="hm-gender-options">
                <label><input type="radio" name="genero" value="M"> M</label>
                <label><input type="radio" name="genero" value="F"> F</label>
                <label><input type="radio" name="genero" value="O" checked> Otro</label>
              </div>
            </div>
          </div>

          <div class="hm-form-group">
            <label for="reg_pass">Contraseña</label>
            <input
              type="password"
              id="reg_pass"
              name="password"
              class="hm-input"
              required
            >
          </div>

          <div class="hm-form-group">
            <label for="reg_pass2">Confirma tu contraseña</label>
            <input
              type="password"
              id="reg_pass2"
              name="password2"
              class="hm-input"
              required
            >
          </div>

          <button type="submit" class="hm-btn-full hm-btn-full-primary">
            Crear cuenta
          </button>

        </form>


      </section>

    </section>

  </main>

  <?php include 'partials/footer.php'; ?>

  <?php if (!empty($register_errors)) : ?>
    <div class="hm-modal-overlay is-visible" id="regErrorModal">
      <div class="hm-modal">
        <h3 class="hm-modal-title">Errores en el registro</h3>
        <p class="hm-modal-subtitle">
          Revisa la información e inténtalo de nuevo.
        </p>
        <ul class="hm-modal-list">
          <?php foreach ($register_errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
        <button type="button" class="hm-modal-btn" id="closeRegError">
          Entendido
        </button>
      </div>
    </div>
  <?php endif; ?>

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('regErrorModal');
    var btn   = document.getElementById('closeRegError');

    if (modal && btn) {
      btn.addEventListener('click', function () {
        modal.classList.remove('is-visible');
      });

      // Cerrar al hacer clic fuera de la tarjeta
      modal.addEventListener('click', function (e) {
        if (e.target === modal) {
          modal.classList.remove('is-visible');
        }
      });
    }
  });
  </script>


</body>
</html>
