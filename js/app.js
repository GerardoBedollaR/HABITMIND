// js/app.js
const $ = (sel) => document.querySelector(sel);
const habitListEl = $("#habitList");
const habitSelectEl = $("#habitSelect");
const btnCheck = $("#btnCheck");

let chart;
const api = (p) => `./api/${p}`;

const dificultadLabel = (d) => ({
  baja: "Baja",
  media: "Media",
  alta: "Alta",
}[d] || d);

const privacidadLabel = (p) => ({
  privada: "Privada",
  solo_amigos: "Solo amigos",
  publica: "Pública",
}[p] || p);

async function loadHabitos(){
  const res = await fetch(api("habitos_list.php"));
  const habitos = await res.json();

  // Tarjetas
  habitListEl.innerHTML = "";
  habitos.forEach(h => {
    const el = document.createElement("div");
    el.className = "habit-item";
    el.innerHTML = `
      <div>
        <div style="font-weight:600">${h.nombre}</div>
        <div style="font-size:13px;margin-top:4px">${h.descripcion || ""}</div>
        <div class="meta" style="margin-top:6px">
          <span class="badge">Dificultad: ${dificultadLabel(h.dificultad)}</span>
          <span class="badge">Privacidad: ${privacidadLabel(h.privacidad)}</span>
        </div>
      </div>
      <div>
        <button class="btn secondary" data-id="${h.id_habito}">Ver progreso</button>
      </div>
    `;
    el.querySelector("button").addEventListener("click", () => selectHabit(h.id_habito));
    habitListEl.appendChild(el);
  });

  // Selector para gráfico
  habitSelectEl.innerHTML = "";
  habitos.forEach(h => {
    const opt = document.createElement("option");
    opt.value = h.id_habito;
    opt.textContent = h.nombre;
    habitSelectEl.appendChild(opt);
  });

  if(habitos[0]){
    await selectHabit(habitos[0].id_habito);
  }else{
    renderChart([],[]);
  }
}

async function selectHabit(idHabito){
  habitSelectEl.value = idHabito;
  const res = await fetch(api(`estadisticas_habito.php?id_habito=${idHabito}`));
  const rows = await res.json();

  const labels = rows.map(r => r.dia);
  const data = rows.map(r => Number(r.checks || 0));
  renderChart(labels, data);
}

function renderChart(labels, data){
  const ctx = document.getElementById("chart").getContext("2d");
  if(chart) chart.destroy();
  chart = new Chart(ctx, {
    type: "bar",
    data: {
      labels,
      datasets: [{
        label: "Días completados",
        data,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: true }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { precision: 0 }
        }
      }
    }
  });
}

btnCheck.addEventListener("click", async () => {
  const idHabito = parseInt(habitSelectEl.value, 10);
  if(!idHabito){
    alert("Primero selecciona un hábito.");
    return;
  }

  const res = await fetch(api("registro_hoy.php"), {
    method: "POST",
    headers: { "Content-Type":"application/json" },
    body: JSON.stringify({ id_habito: idHabito })
  });
  const data = await res.json();
  if(data.ok){
    alert("Registro de hoy guardado ✅");
    await selectHabit(idHabito);
  }else{
    alert("Error: " + (data.error || "no se pudo registrar"));
  }
});

loadHabitos();
