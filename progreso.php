<?php
session_start();
require_once 'api/db.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';

// Hábitos del usuario
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

$total_habitos = count($habitos);

// Completados hoy
$hoy = date('Y-m-d');

$sql = "SELECT COUNT(*) AS total
        FROM registros_habito rh
        INNER JOIN habitos h ON h.id_habito = rh.id_habito
        WHERE h.id_usuario = ?
        AND rh.fecha = ?
        AND rh.estado = 'completado'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id_usuario, $hoy);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

$completados_hoy = (int)$res['total'];

$nivel_progreso = $total_habitos > 0
    ? round(($completados_hoy / $total_habitos) * 100)
    : 0;

// Racha simple: si hoy completó algo, 1 día
$racha_actual = $completados_hoy > 0 ? 1 : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>HabitMind — Progreso</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/habitmind.css">
</head>

<body class="hm-app-page">

<header class="hm-header-app">
  <?php include 'partials/header_app.php'; ?>
</header>

<main class="hm-app-main">

    <!-- Podemos reutilizar el layout: sidebar + contenido principal -->
    <aside class="hm-sidebar">
        <div class="hm-sidebar-header">Mis hábitos</div>

        <div class="hm-sidebar-search">
          <input type="text" class="hm-input" placeholder="🔍 Buscar hábito">
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


        <!-- BOTÓN PARA VOLVER AL CHATBOT (ahora pegado al fondo del aside) -->
        <a href="registro_hoy.php"
           style="
             margin-top:auto;          /* empuja el botón hacia abajo */
             display:block;            /* ocupa el ancho del aside */
             text-align:center;        /* centra el texto */
             border-radius:999px;
             padding:8px 18px;
             font-size:13px;
             text-decoration:none;
             border:none;
             background:#2563eb;
             color:#ffffff;
             box-shadow:0 10px 24px rgba(37,99,235,0.45);
           ">
           Continuar con el chatbot 🤖
        </a>
    </aside>

    <!-- Contenido principal: tarjeta de progreso -->
    <section class="hm-app-content" style="padding: 24px 26px 28px;">

        <!-- Encabezado -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
            <div>
                <h1 style="margin:0; font-size:20px;">Tu progreso</h1>
                <p style="margin:4px 0 0; font-size:13px; color:#6b7280;">
                    Hola, <strong><?= htmlspecialchars($usuario_nombre) ?></strong>.  
                    Aquí puedes revisar cómo vas con tus hábitos.
                </p>
            </div>
        </div>

        <!-- Tarjeta general de progreso (demo por ahora) -->
        <div style="
            background:#ffffff;
            border-radius:18px;
            padding:16px 18px 20px;
            box-shadow:0 10px 25px rgba(15,23,42,0.08);
            margin-bottom:18px;
            font-size:13px;
        ">
            <h3 style="margin-top:0; font-size:15px;">Resumen rápido</h3>
            <p style="margin-bottom:10px;">
                Esta es una vista demo. Más adelante aquí mostraremos:
            </p>
            <ul style="margin:0 0 0 18px; padding:0;">
                <li>Hábitos activos y completados.</li>
                <li>Racha actual de días cumplidos.</li>
                <li>Porcentaje de progreso semanal.</li>
            </ul>
        </div>

        <!-- Grid de tarjetas (estadísticas simuladas) -->
        <div class="hm-dashboard-grid">

          <div class="hm-dashboard-card">
            <span>📋</span>
            <p>Hábitos activos</p>
            <strong><?= $total_habitos ?></strong>
            <small>Número de hábitos que estás siguiendo.</small>
          </div>

          <div class="hm-dashboard-card">
            <span>🔥</span>
            <p>Racha actual</p>
            <strong><?= $racha_actual ?> día(s)</strong>
            <small>Días seguidos cumpliendo al menos un hábito.</small>
          </div>

          <div class="hm-dashboard-card">
            <span>📈</span>
            <p>Nivel de progreso</p>
            <strong><?= $nivel_progreso ?>%</strong>
            <small>Porcentaje de hábitos completados hoy.</small>
          </div>

        </div>

    </section>

</main>

<?php include 'partials/footer.php'; ?>

<!-- Modal: Agregar nuevo hábito -->
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
          <span class="hm-habit-dot hm-dot-easy"></span>
          Fácil
        </label>
        <label>
          <input type="radio" name="hm-habit-diff" value="media">
          <span class="hm-habit-dot hm-dot-medium"></span>
          Intermedia
        </label>
        <label>
          <input type="radio" name="hm-habit-diff" value="alta">
          <span class="hm-habit-dot hm-dot-hard"></span>
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
  const modal = document.getElementById('hm-habit-modal');
  const inputName = document.getElementById('hm-habit-name');
  const btnOpen = document.getElementById('hm-open-add-habit');
  const btnCancel = document.getElementById('hm-habit-cancel');
  const btnSave = document.getElementById('hm-habit-save');

  if (!modal || !inputName || !btnOpen || !btnCancel || !btnSave) return;

  function abrirModal() {
    modal.style.display = 'flex';
    inputName.value = '';
    inputName.focus();
  }

  function cerrarModal() {
    modal.style.display = 'none';
  }

  async function guardarHabito() {
    const nombre = inputName.value.trim();
    const dificultad = document.querySelector('input[name="hm-habit-diff"]:checked')?.value || 'baja';

    if (!nombre) {
      alert('Escribe el nombre del hábito.');
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
      cerrarModal();
      location.reload();
    } else {
      alert(data.error || 'No se pudo guardar el hábito.');
    }
  }

  btnOpen.addEventListener('click', abrirModal);
  btnCancel.addEventListener('click', cerrarModal);
  btnSave.addEventListener('click', guardarHabito);

  modal.addEventListener('click', function(e) {
    if (e.target === modal) cerrarModal();
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
