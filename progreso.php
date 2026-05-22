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
    <title>HabitMind — Progreso</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/habitmind.css">
</head>

<body class="hm-app-page">

<header class="hm-header-app">

  <!-- Logo + texto -->
  <div class="hm-header-app-left">
    <img src="assets/img/logo.png" alt="HabitMind Logo" class="hm-logo-img">
    <div class="hm-logo-text">HABITMIND</div>
  </div>

  <!-- Configuración + Salir -->
  <div class="hm-header-app-right">

     <!-- BOTÓN DE REGRESO AL DASHBOARD -->
    <a href="dashboard.php" class="hm-btn-back" style="
        padding: 8px 16px;
        background: #e5efff;
        border-radius: 25px;
        color: #2563eb;
        text-decoration: none;
        margin-right: 12px;
        font-weight: 500;
    ">
      ← Volver al panel
    </a>

    <button type="button" class="hm-config-pill">
      Configuración ⚙️
    </button>

    <form action="logout.php" method="post" style="margin:0;">
      <button type="submit" class="hm-btn-logout">
        Salir
      </button>
    </form>
  </div>
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
        <div style="
            display:grid;
            grid-template-columns:repeat(3,minmax(0,1fr));
            gap:14px;
            font-size:13px;
        ">
            <div style="
                background:#ffffff;
                border-radius:16px;
                padding:14px 16px;
                box-shadow:0 8px 20px rgba(15,23,42,0.06);
            ">
                <div style="font-size:12px; color:#6b7280;">Hábitos activos</div>
                <div style="font-size:22px; font-weight:600; margin-top:4px;">5</div>
                <div style="font-size:11px; color:#6b7280; margin-top:4px;">
                    Número de hábitos que estás siguiendo.
                </div>
            </div>

            <div style="
                background:#ffffff;
                border-radius:16px;
                padding:14px 16px;
                box-shadow:0 8px 20px rgba(15,23,42,0.06);
            ">
                <div style="font-size:12px; color:#6b7280;">Racha actual</div>
                <div style="font-size:22px; font-weight:600; margin-top:4px;">7 días 🔥</div>
                <div style="font-size:11px; color:#6b7280; margin-top:4px;">
                    Días seguidos cumpliendo al menos un hábito.
                </div>
            </div>

            <div style="
                background:#ffffff;
                border-radius:16px;
                padding:14px 16px;
                box-shadow:0 8px 20px rgba(15,23,42,0.06);
            ">
                <div style="font-size:12px; color:#6b7280;">Nivel de progreso</div>
                <div style="font-size:22px; font-weight:600; margin-top:4px;">82%</div>
                <div style="font-size:11px; color:#6b7280; margin-top:4px;">
                    Porcentaje aproximado de cumplimiento semanal.
                </div>
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
          <input type="radio" name="hm-habit-diff" value="facil" checked>
          <span class="hm-habit-dot hm-dot-easy"></span>
          Fácil
        </label>
        <label>
          <input type="radio" name="hm-habit-diff" value="media">
          <span class="hm-habit-dot hm-dot-medium"></span>
          Intermedia
        </label>
        <label>
          <input type="radio" name="hm-habit-diff" value="dificil">
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
  const sidebarAdd = document.querySelector('.hm-sidebar-add');
  const sidebarList = document.querySelector('.hm-sidebar-list');

  const modal      = document.getElementById('hm-habit-modal');
  const inputName  = document.getElementById('hm-habit-name');
  const btnCancel  = document.getElementById('hm-habit-cancel');
  const btnSave    = document.getElementById('hm-habit-save');

  if (!sidebarAdd || !sidebarList || !modal) {
    // Si esta página no tiene sidebar o modal, salimos silenciosamente
    return;
  }

  function openModal() {
    modal.style.display = 'flex';
    inputName.value = '';
    inputName.focus();
  }

  function closeModal() {
    modal.style.display = 'none';
  }

  function getSelectedDifficulty() {
    const checked = document.querySelector('input[name="hm-habit-diff"]:checked');
    return checked ? checked.value : 'facil';
  }

  function colorForDifficulty(diff) {
    switch (diff) {
      case 'media':
        return '#fb923c'; // naranja
      case 'dificil':
        return '#ef4444'; // rojo
      case 'facil':
      default:
        return '#22c55e'; // verde
    }
  }

  function addHabitToSidebar(name, difficulty) {
    const color = colorForDifficulty(difficulty);

    const item = document.createElement('div');
    item.className = 'hm-habit-item';

    const dot = document.createElement('span');
    dot.className = 'hm-habit-dot';
    dot.style.background = color;

    item.appendChild(dot);
    item.appendChild(document.createTextNode(' ' + name));

    sidebarList.appendChild(item);
  }

  function handleSave() {
    const name = inputName.value.trim();
    if (!name) {
      inputName.focus();
      return;
    }

    const diff = getSelectedDifficulty();
    addHabitToSidebar(name, diff);
    closeModal();
  }

  // Eventos
  sidebarAdd.addEventListener('click', function (e) {
    e.preventDefault();
    openModal();
  });

  btnCancel.addEventListener('click', function () {
    closeModal();
  });

  btnSave.addEventListener('click', function () {
    handleSave();
  });

  // Cerrar con ESC
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.style.display === 'flex') {
      closeModal();
    }
  });

  // Cerrar si clicas fuera de la tarjeta
  modal.addEventListener('click', function (e) {
    if (e.target === modal) {
      closeModal();
    }
  });
});
</script>



</body>
</html>
