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
    <?php include 'partials/header_app.php'; ?>
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
        <div class="habit-row">
            <div class="habit-info">
                <span class="habit-color" style="background-color: <?= htmlspecialchars($habito['color']) ?>;"></span>

                <span class="habit-icon">
                    <?= htmlspecialchars($habito['icono']) ?>
                </span>

                <span class="habit-name">
                    <?= htmlspecialchars($habito['nombre']) ?>
                </span>
            </div>

            <form action="eliminar_habito.php" method="POST" class="form-eliminar-habito">
                <input type="hidden" name="id_habito" value="<?= (int)$habito['id_habito'] ?>">
                <button type="submit" class="delete-habit-btn" title="Eliminar hábito">
                    🗑️
                </button>
            </form>
        </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

    <div id="hm-open-add-habit" class="hm-sidebar-add" style="cursor:pointer;">
      + Agregar hábito
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

  <div id="hm-habit-modal" class="hm-modal-backdrop" style="display:none;">
    <div class="hm-modal-card">
      <h2 class="hm-modal-title">Agregar nuevo hábito</h2>

      <label class="hm-modal-label">
        Nombre del hábito
        <input
          id="hm-habit-name"
          type="text"
          class="hm-input"
          placeholder="Ej. Leer 20 min antes de dormir"
        >
      </label>

      <label class="hm-modal-label">
        Dificultad
        <div class="hm-difficulty-row">
          <label>
            <input type="radio" name="hm-habit-diff" value="baja" checked>
            <span class="hm-habit-dot" style="background:#22c55e;"></span>
            Fácil
          </label>

          <label>
            <input type="radio" name="hm-habit-diff" value="media">
            <span class="hm-habit-dot" style="background:#fb923c;"></span>
            Intermedia
          </label>

          <label>
            <input type="radio" name="hm-habit-diff" value="alta">
            <span class="hm-habit-dot" style="background:#ef4444;"></span>
            Difícil
          </label>
        </div>
      </label>

      <div class="hm-modal-actions">
        <button type="button" id="hm-habit-cancel" class="hm-btn-secondary">
          Cancelar
        </button>

        <button type="button" id="hm-habit-save" class="hm-btn-primary">
          Guardar
        </button>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const btnOpen = document.getElementById('hm-open-add-habit');
    const modal = document.getElementById('hm-habit-modal');
    const inputName = document.getElementById('hm-habit-name');
    const btnCancel = document.getElementById('hm-habit-cancel');
    const btnSave = document.getElementById('hm-habit-save');

    function openModal() {
      modal.style.display = 'flex';
      inputName.value = '';
      inputName.focus();
    }

    function closeModal() {
      modal.style.display = 'none';
    }

    async function saveHabit() {
      const nombre = inputName.value.trim();
      const dificultad = document.querySelector('input[name="hm-habit-diff"]:checked').value;

      if (!nombre) {
        inputName.focus();
        return;
      }

      const resp = await fetch('api/crear_habito.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body:
          'nombre=' + encodeURIComponent(nombre) +
          '&dificultad=' + encodeURIComponent(dificultad)
      });

      const data = await resp.json();

      if (data.ok) {
        closeModal();
        location.reload();
      } else {
        alert(data.error || 'No se pudo crear el hábito.');
      }
    }

    btnOpen.addEventListener('click', openModal);
    btnCancel.addEventListener('click', closeModal);
    btnSave.addEventListener('click', saveHabit);

    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        closeModal();
      }
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeModal();
      }
    });
  });
  </script>

<div id="modalEliminarHabito" class="modal-eliminar-overlay" hidden>
    <div class="modal-eliminar-card">
        <div class="modal-eliminar-icon">🗑️</div>

        <h3>Eliminar hábito</h3>

        <p>
            ¿Seguro que quieres eliminar este hábito?
            Esta acción no se puede deshacer.
        </p>

        <div class="modal-eliminar-actions">
            <button type="button" id="btnCancelarEliminar" class="btn-modal-cancelar">
                Cancelar
            </button>

            <button type="button" id="btnConfirmarEliminar" class="btn-modal-eliminar">
                Sí, eliminar
            </button>
        </div>
    </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("modalEliminarHabito");
    const btnCancelar = document.getElementById("btnCancelarEliminar");
    const btnConfirmar = document.getElementById("btnConfirmarEliminar");

    let formularioPendiente = null;

    document.querySelectorAll(".form-eliminar-habito").forEach(function (formulario) {
        formulario.addEventListener("submit", function (event) {
            event.preventDefault();

            formularioPendiente = formulario;
            modal.hidden = false;
        });
    });

    btnCancelar.addEventListener("click", function () {
        formularioPendiente = null;
        modal.hidden = true;
    });

    btnConfirmar.addEventListener("click", function () {
        if (formularioPendiente) {
            formularioPendiente.submit();
        }
    });

    modal.addEventListener("click", function (event) {
        if (event.target === modal) {
            formularioPendiente = null;
            modal.hidden = true;
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && !modal.hidden) {
            formularioPendiente = null;
            modal.hidden = true;
        }
    });
});
</script>

</body>
</html>
