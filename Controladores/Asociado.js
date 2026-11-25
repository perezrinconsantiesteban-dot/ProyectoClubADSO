const listaAsociados = document.getElementById("listaAsociados");
const URL_CONSULTA_ASOCIADOS = "../Consultas/obtenerAsociados.php";

async function cargarAsociados() {
  if (!listaAsociados) return;

  try {
    const respuesta = await fetch(URL_CONSULTA_ASOCIADOS);
    const resultado = await respuesta.json();

    if (respuesta.status === 401) {
      window.location.href = "Login.html";
      return;
    }

    if (!respuesta.ok || !resultado.success) {
      throw new Error(resultado.mensaje || resultado.message || "Error al cargar asociados");
    }

    listaAsociados.replaceChildren();
    const asociados = resultado.asociados || [];

    if (asociados.length === 0) {
      const fila = document.createElement("tr");
      const celda = document.createElement("td");
      celda.colSpan = 4;
      celda.className = "text-center text-muted py-4";
      celda.textContent = "No hay asociados registrados.";
      fila.append(celda);
      listaAsociados.append(fila);
      return;
    }

    asociados.forEach((asociado) => {
      const fila = document.createElement("tr");
      const nombreCompleto =
        asociado.nombreCompleto ??
        asociado.nombre ??
        `${asociado.nombres ?? ""} ${asociado.apellidos ?? ""}`.trim();

      [
        asociado.documento,
        nombreCompleto,
        asociado.telefono,
        asociado.correo ?? asociado.email,
      ].forEach((valor) => {
        const td = document.createElement("td");
        td.textContent = valor ?? "";
        fila.append(td);
      });

      listaAsociados.append(fila);
    });
  } catch (error) {
    alert("Error al cargar asociados: " + error.message);
  }
}

cargarAsociados();