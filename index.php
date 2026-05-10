<?php
// index.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>HabitMind — Landing</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/habitmind.css">
</head>

<main>


<body class="hm-landing-page">

  <!-- HEADER PÚBLICO HABITMIND -->
  <header class="hm-header-public">
    <!-- Logo + texto -->
  <div class="hm-header-app-left">
    <img src="assets/img/logo.png" alt="HabitMind Logo" class="hm-logo-img">
    <div class="hm-logo-text">HABITMIND</div>
  </div>


    <!-- Menú de navegación -->
    <nav class="hm-nav-public">
      <a href="index.php" class="hm-nav-link">Inicio</a>
      <a href="#contacto" class="hm-nav-link">Contacto</a>
      <a href="#beneficios" class="hm-nav-link">Beneficios</a>
    </nav>

    <!-- Botones a la derecha -->
    <div class="hm-nav-right">
      <a href="login.php" class="hm-btn-cta">COMENZAR</a>
    </div>
  </header>

  <section class="hm-hero-mockup">
    <div class="hm-hero-text">

        <h1 class="hm-hero-title">
            Construye tu <span>mejor versión,</span><br>
            un hábito a la vez.
        </h1>

        <p class="hm-hero-subtitle">
            Crea rutinas, mide tu progreso y<br>
            conversa con tu asistente HabitMind.
        </p>


    </div>

    <div class="hm-hero-illustration">
        <img src="assets/img/undraw_activity-tracker_3o6r.svg" alt="Ilustración HabitMind">
    </div>
</section>


  <!-- BENEFICIOS -->
  <section id="beneficios" class="hm-benefits">
    <h2 class="hm-section-title">Beneficios de HabitMind</h2>

    <div class="hm-benefits-grid">
      <article class="hm-card">
        <div class="hm-card-icon">🎧</div>
        <h3 class="hm-card-title">Crea hábitos saludables</h3>
        <p class="hm-card-text">
          Establece rutinas diarias a tu ritmo, con metas claras y seguimiento sencillo.
        </p>
      </article>

      <article class="hm-card">
        <div class="hm-card-icon">⏰</div>
        <h3 class="hm-card-title">Recordatorios inteligentes</h3>
        <p class="hm-card-text">
          Recibe notificaciones amigables para no olvidar tus hábitos más importantes.
        </p>
      </article>

      <article class="hm-card">
        <div class="hm-card-icon">📊</div>
        <h3 class="hm-card-title">Sigue tu progreso</h3>
        <p class="hm-card-text">
          Visualiza tu avance con estadísticas, rachas y logros semanales.
        </p>
      </article>
    </div>
  </section>

  <!-- TESTIMONIOS / HISTORIAS DE ÉXITO -->
  <section class="hm-testimonials">
    <h2 class="hm-section-title">Historias de éxito con HABITMIND</h2>

    <div class="hm-testimonials-grid">
      <article class="hm-testimonial-card">
        <div class="hm-testimonial-header">
          <div class="hm-avatar">M</div>
          <div>
            <div class="hm-testimonial-name">María González</div>
          </div>
        </div>
        <p class="hm-testimonial-text">
          HabitMind me ayudó a mantener una rutina de ejercicio constante y a organizar mejor mis días. 💪
        </p>
      </article>

      <article class="hm-testimonial-card">
        <div class="hm-testimonial-header">
          <div class="hm-avatar">L</div>
          <div>
            <div class="hm-testimonial-name">Luis Romero</div>
          </div>
        </div>
        <p class="hm-testimonial-text">
          Los recordatorios me mantienen enfocado. Ahora cumplo con mis hábitos de lectura y meditación. 📚
        </p>
      </article>

      <article class="hm-testimonial-card">
        <div class="hm-testimonial-header">
          <div class="hm-avatar">A</div>
          <div>
            <div class="hm-testimonial-name">Ana Pérez</div>
          </div>
        </div>
        <p class="hm-testimonial-text">
          Gracias a HabitMind siento que avanzo paso a paso, sin presión, pero con resultados reales. ✨
        </p>
      </article>
    </div>

    <div class="hm-landing-cta">
      <div style="margin-bottom:6px;">
        <strong>+1,200</strong> usuarios activos · <strong>+5,000</strong> hábitos creados · <strong>98%</strong> satisfacción
      </div>
      <div style="margin-bottom:12px;">
        ¿Listo para comenzar tu cambio?
      </div>
      <a href="login.php" class="hm-btn">Comenzar ahora</a>
    </div>
  </section>

</main>

<?php @include 'partials/footer.php'; ?>

</body>
</html>
