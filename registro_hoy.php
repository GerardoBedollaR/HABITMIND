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
  <title>HabitMind — Asistente y progreso</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/habitmind.css">
</head>

<body class="hm-app-page hm-chat-page">

<header class="hm-header-app">
  <?php include 'partials/header_app.php'; ?>
</header>

<main class="hm-app-main">

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

    <a href="progreso.php" class="hm-sidebar-progress-btn">
      Mira tu progreso 📊
    </a>
  </aside>

  <section class="hm-app-content hm-chat-card">
    <div class="hm-chat-header">Asistente HabitMind</div>
    <div class="hm-chat-timestamp">
      Hola, <strong><?= htmlspecialchars($usuario_nombre) ?></strong>.  
      ¿Qué hábito quieres registrar hoy?
    </div>

    <section class="hm-chat-section">
      <div class="hm-chat-window" id="hm-chat-window">

        <div class="hm-chat-log" id="hm-chat-log">
          <div class="hm-chat-msg hm-chat-bot">
            <span class="hm-chat-author">Bot · HabitMind</span>
            <p>
              ¡Hola <?= htmlspecialchars($usuario_nombre) ?>! 👋 Soy tu asistente HabitMind.
              Cuéntame: ¿en qué hábito quieres trabajar hoy?
            </p>
          </div>
        </div>

        <div class="hm-chat-input-area">
          <input
            type="text"
            id="hm-chat-input"
            placeholder="Escribe un mensaje…"
          >
          <span id="hm-chat-send" class="hm-chat-send" aria-label="Enviar mensaje">
            ✈️
          </span>
        </div>

      </div>
    </section>
  </section>
</main>

<?php include 'partials/footer.php'; ?>

<script>
(function() {
  const chatLog   = document.getElementById('hm-chat-log');
  const chatInput = document.getElementById('hm-chat-input');
  const chatSend  = document.getElementById('hm-chat-send');

  const SENDER_ID = "web-" + Math.random().toString(36).slice(2);

  function appendMessage(text, from) {
    const wrapper = document.createElement('div');
    wrapper.classList.add('hm-chat-msg');

    if (from === 'user') {
      wrapper.classList.add('hm-chat-user');
    } else {
      wrapper.classList.add('hm-chat-bot');
    }

    const author = document.createElement('span');
    author.classList.add('hm-chat-author');
    author.textContent = from === 'user' ? 'Tú' : 'Bot · HabitMind';

    const p = document.createElement('p');
    p.textContent = text;

    wrapper.appendChild(author);
    wrapper.appendChild(p);
    chatLog.appendChild(wrapper);
    chatLog.scrollTop = chatLog.scrollHeight;
  }

  async function enviarMensajeARasa(mensaje) {
    const respuesta = await fetch("http://localhost:5005/webhooks/rest/webhook", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        sender: SENDER_ID,
        message: mensaje
      })
    });

    if (!respuesta.ok) {
      throw new Error("Error HTTP: " + respuesta.status);
    }

    return await respuesta.json();
  }

  async function sendToRasa(message) {
    appendMessage(message, 'user');

    try {
      const respuestasBot = await enviarMensajeARasa(message);

      if (Array.isArray(respuestasBot) && respuestasBot.length > 0) {
        respuestasBot.forEach((r) => {
          if (r.text) {
            appendMessage(r.text, 'bot');
          }
        });
      } else {
        appendMessage('No entendí bien tu mensaje. ¿Puedes reformularlo?', 'bot');
      }

    } catch (err) {
      console.error("Error al contactar Rasa:", err);
      appendMessage('Ocurrió un error al contactar al asistente.', 'bot');
    }
  }

  function handleSend() {
    const text = chatInput.value.trim();
    if (!text) return;

    chatInput.value = '';
    sendToRasa(text);
  }

  chatSend.addEventListener('click', handleSend);

  chatInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      handleSend();
    }
  });
})();
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
        return '#fb923c';
      case 'dificil':
        return '#ef4444';
      case 'facil':
      default:
        return '#22c55e';
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

  sidebarAdd.addEventListener('click', function (e) {
    e.preventDefault();
    openModal();
  });

  btnCancel.addEventListener('click', closeModal);
  btnSave.addEventListener('click', handleSave);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.style.display === 'flex') {
      closeModal();
    }
  });

  modal.addEventListener('click', function (e) {
    if (e.target === modal) {
      closeModal();
    }
  });
});
</script>


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