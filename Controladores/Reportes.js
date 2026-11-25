const URL_REPORTE = "../Consultas/obtenerReportes.php";

const formatoCOP = (valor) =>
  "$ " + Number(valor || 0).toLocaleString("es-CO", { maximumFractionDigits: 0 });

let carteraData = [];

function renderKPIs(kpis = {}) {
  const elPrestado = document.getElementById("kpiTotalPrestado");
  const elPagado = document.getElementById("kpiTotalPagado");
  const elPendiente = document.getElementById("kpiCarteraPendiente");
  const elActividades = document.getElementById("kpiRecaudoActividades");
  const elResumenBalance = document.getElementById("resumenBalanceActividades");
  const elResumenEstado = document.getElementById("resumenEstadoActividades");

  if (elPrestado) elPrestado.textContent = formatoCOP(kpis.totalPrestado);
  if (elPagado) elPagado.textContent = formatoCOP(kpis.totalPagado);
  if (elPendiente) elPendiente.textContent = formatoCOP(kpis.carteraPendiente);
  if (elActividades) elActividades.textContent = formatoCOP(kpis.recaudoActividades);

  if (elResumenBalance) {
    elResumenBalance.textContent = formatoCOP(kpis.recaudoActividades);
  }
  if (elResumenEstado) {
    elResumenEstado.textContent = Number(kpis.recaudoActividades || 0) > 0 ? "Registrado" : "Pendiente";
  }
}

function renderCartera(lista = []) {
  const tbody = document.getElementById("tablaCarteraAsociados");
  if (!tbody) return;
  tbody.replaceChildren();

  if (!lista.length) {
    const tr = document.createElement("tr");
    const td = document.createElement("td");
    td.colSpan = 8;
    td.className = "text-center text-muted py-4";
    td.textContent = "No hay asociados registrados.";
    tr.append(td);
    tbody.append(tr);
    return;
  }

  lista.forEach((item) => {
    const saldo = Number(item.saldoPendiente || 0);
    const creditos = Number(item.creditos || 0);
    const tr = document.createElement("tr");

    [
      item.documento,
      String(item.asociado || "").trim(),
      item.telefono,
      creditos,
      formatoCOP(item.totalPrestado),
      formatoCOP(item.totalPagado),
      formatoCOP(saldo),
    ].forEach((val, idx) => {
      const td = document.createElement("td");
      if (idx === 3) td.className = "text-center";
      if (idx === 5) td.className = "text-success fw-semibold";
      if (idx === 6 && saldo > 0) td.className = "text-danger fw-semibold";
      td.textContent = val ?? "";
      tr.append(td);
    });

    const tdEstado = document.createElement("td");
    const badge = document.createElement("span");
    if (creditos === 0 || saldo <= 0) {
      badge.className = "badge bg-secondary";
      badge.textContent = creditos === 0 ? "Sin Créditos" : "Al Día";
    } else {
      badge.className = "badge bg-danger";
      badge.textContent = `Saldo Pendiente (${formatoCOP(saldo)})`;
    }
    tdEstado.append(badge);
    tr.append(tdEstado);

    tbody.append(tr);
  });
}

function renderActividades(lista = []) {
  const tbody = document.getElementById("tablaBalanceActividades");
  if (!tbody) return;
  tbody.replaceChildren();

  if (!lista.length) {
    const tr = document.createElement("tr");
    const td = document.createElement("td");
    td.colSpan = 8;
    td.className = "text-center text-muted py-4";
    td.textContent = "No hay actividades registradas.";
    tr.append(td);
    tbody.append(tr);
    return;
  }

  lista.forEach((act) => {
    const total = Number(act.totalRecaudado || 0);
    const participantes = Number(act.participantes || 0);
    const mitadClub = Number(act.mitadClub ?? total * 0.5);
    const mitadDistribuida = Number(act.mitadDistribuida ?? total * 0.5);
    const gananciaPorAsociado = Number(
      act.gananciaPorAsociado ?? (participantes > 0 ? mitadDistribuida / participantes : 0)
    );

    const tr = document.createElement("tr");

    [
      act.actividad,
      act.fecha,
      act.municipio,
      formatoCOP(total),
      participantes,
      formatoCOP(mitadClub),
      formatoCOP(mitadDistribuida),
      formatoCOP(gananciaPorAsociado),
    ].forEach((val, idx) => {
      const td = document.createElement("td");
      if (idx === 3) td.className = "fw-semibold";
      if (idx === 4) {
        const badge = document.createElement("span");
        badge.className = "badge bg-primary";
        badge.textContent = val;
        td.className = "text-center";
        td.append(badge);
      } else {
        if (idx === 5) td.className = "text-success fw-semibold";
        td.textContent = val ?? "";
      }
      tr.append(td);
    });

    tbody.append(tr);
  });
}

async function cargarReportes() {
  try {
    const resp = await fetch(URL_REPORTE, { cache: "no-store" });
    if (resp.status === 401) {
      window.location.href = "Login.html";
      return;
    }

    const texto = await resp.text();
    let data;
    try {
      data = JSON.parse(texto);
    } catch {
      throw new Error(`Respuesta inválida del servidor: ${texto.slice(0, 120)}`);
    }

    if (!resp.ok || !data.success) {
      throw new Error(data.mensaje || "Error al cargar reportes");
    }

    carteraData = data.cartera || [];
    renderKPIs(data.kpis || {});
    renderCartera(carteraData);
    renderActividades(data.actividades || []);
  } catch (error) {
    alert("Error al cargar reportes: " + error.message);
  }
}

document.getElementById("btnImprimir")?.addEventListener("click", () => window.print());

document.getElementById("buscarAsociado")?.addEventListener("input", (e) => {
  const termino = e.target.value.trim().toLowerCase();
  const filtrados = carteraData.filter(
    (item) =>
      String(item.documento || "").toLowerCase().includes(termino) ||
      String(item.asociado || "").toLowerCase().includes(termino)
  );
  renderCartera(filtrados);
});

cargarReportes();