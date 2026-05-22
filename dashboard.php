<?php
session_start();
require_once 'api/db.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';

$sql = "SELECT id_habito, nombre, color, icono
        FROM habitos
        WHERE id_usuario = ?
        AND activo = 1
        ORDER BY id_habito DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

$habitos = [];

while ($fila = $resultado->fetch_assoc()) {
    $habitos[] = $fila;
}
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
  <?php if (empty($habitos)): ?>
    <p style="font-size:14px; color:#64748b;">
      Aún no tienes hábitos.
    </p>
  <?php else: ?>
    <?php foreach ($habitos as $habito): ?>
      <div class="hm-habit-item">
        <span
          class="hm-habit-dot"
          style="background: <?= htmlspecialchars($habito['color']) ?>;">
        </span>
        <?= htmlspecialchars($habito['icono']) ?>
        <?= htmlspecialchars($habito['nombre']) ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

    <div class="hm-sidebar-add">+ Agregar hábito</div>
    <div style="margin-top:20px;">

      <input
          type="text"
          id="nuevoHabito"
          placeholder="Nuevo hábito..."
          class="hm-input"
      >

      <button
          id="btnCrearHabito"
          class="hm-btn"
          style="margin-top:10px; width:100%;"
      >
          Crear hábito
      </button>

      <p
          id="msgHabito"
          style="font-size:14px; margin-top:10px;"
      ></p>

  </div>

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
        🔥<br>Racha actual<br>
        <span id="stat-racha" style="font-size:22px;">0</span>
      </div>

      <div class="hm-dashboard-card">
        ✅<br>Hábitos cumplidos hoy<br>
        <span id="stat-completados" style="font-size:22px;">0</span>
      </div>

      <div class="hm-dashboard-card">
        📋<br>Hábitos activos<br>
        <span id="stat-total" style="font-size:22px;">0</span>
      </div>

      <div class="hm-dashboard-card">
        📈<br>Progreso de hoy<br>
        <span id="stat-porcentaje" style="font-size:22px;">0%</span>
      </div>
    </div>

    <div style="margin-top:30px;">
      <h3>Registrar hábito de hoy</h3>

      <select id="hm-select-habito">
        <option value="">Selecciona un hábito</option>

        <?php foreach ($habitos as $habito): ?>
          <option value="<?= $habito['id_habito'] ?>">
            <?= htmlspecialchars($habito['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button id="hm-btn-registrar">
          Registrar hoy ✅
      </button>

      <p id="hm-msg"></p>
  </div>
  </section>
</main>

<?php include 'partials/footer.php'; ?>

  <script>
  document.getElementById('hm-btn-registrar').addEventListener('click', async () => {

      const idHabito = document.getElementById('hm-select-habito').value;
      const mensaje  = document.getElementById('hm-msg');

      if (!idHabito) {
          mensaje.innerText = 'Selecciona un hábito';
          return;
      }

      try {

          const respuesta = await fetch('api/registro_hoy.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/x-www-form-urlencoded'
              },
              body: 'id_habito=' + encodeURIComponent(idHabito)
          });

          const data = await respuesta.json();

          if (data.ok) {
              mensaje.innerText = data.mensaje;
              mensaje.style.color = 'green';
          } else {
              mensaje.innerText = data.error;
              mensaje.style.color = 'red';
          }

      } catch (error) {

          mensaje.innerText = 'Error al conectar con API';
          mensaje.style.color = 'red';

      }

  });
  </script>

  <script>
    document
    .getElementById('btnCrearHabito')
    .addEventListener('click', async () => {

        const nombre = document
            .getElementById('nuevoHabito')
            .value
            .trim();

        const msg = document
            .getElementById('msgHabito');

        if (!nombre) {

            msg.innerHTML = 'Escribe un hábito';
            msg.style.color = 'red';
            return;
        }

        try {

            const resp = await fetch(
                'api/crear_habito.php',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type':
                        'application/x-www-form-urlencoded'
                    },
                    body:
                        'nombre=' +
                        encodeURIComponent(nombre)
                }
            );

            const data = await resp.json();

            if (data.ok) {

                msg.innerHTML =
                    'Hábito creado correctamente ✅';

                msg.style.color = 'green';

                setTimeout(() => {
                    location.reload();
                }, 800);

            } else {

                msg.innerHTML =
                    data.error || 'Error';

                msg.style.color = 'red';
            }

        } catch (err) {

            console.error(err);

            msg.innerHTML =
                'Error de conexión';

            msg.style.color = 'red';
        }
    });
    </script>

    <script>
  async function cargarEstadisticasDashboard() {
    try {
      const resp = await fetch('api/estadisticas_dashboard.php');
      const data = await resp.json();

      if (!data.ok) return;

      document.getElementById('stat-racha').textContent =
        data.racha_actual + ' día(s)';

      document.getElementById('stat-completados').textContent =
        data.completados_hoy;

      document.getElementById('stat-total').textContent =
        data.total_habitos;

      document.getElementById('stat-porcentaje').textContent =
        data.porcentaje_hoy + '%';

    } catch (error) {
      console.error('Error cargando estadísticas:', error);
    }
  }

  cargarEstadisticasDashboard();
  </script>
</body>
</html>
