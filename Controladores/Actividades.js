const listaActividades = document.getElementById("listaActividades");
const formActividad = document.getElementById("formActividad");
let esAdministrador = false;
let accionesConfiguradas = false;
let actividades = [];
let idEditando = null;

async function solicitarApi(metodo, datos) {
    const opciones = {
        method: metodo,
        headers: { "Content-Type": "application/json" },
    };

    if (datos) {
        opciones.body = JSON.stringify(datos);
    }

    const respuesta = await fetch("../Modelos/gestionarActividades.php", opciones);
    const resultado = await respuesta.json();

    if (respuesta.status === 401) {
        window.location.href = "Login.html";
    }
    if (!respuesta.ok || !resultado.success) {
        throw new Error(resultado.mensaje || resultado.message || "Error en la solicitud");
    }

    return resultado;
}

if (formActividad) {
    formActividad.addEventListener("submit", async (e) => {
        e.preventDefault();

        const datos = {
            nombreActividad: document.getElementById("nombreActividad").value.trim(),
            FechaEvento: document.getElementById("fechaEvento").value,
            municipio: document.getElementById("municipio").value.trim(),
            TotalRecaudo: document.getElementById("totalRecaudado").value,
        };

        try {
            if (idEditando) {
                datos.idActividad = idEditando;
                await solicitarApi("PUT", datos);
                idEditando = null;
                formActividad.reset();
                await cargarActividades();
                alert("Actividad actualizada correctamente");
            } else {
                await solicitarApi("POST", datos);
                formActividad.reset();
                await cargarActividades();
                alert("Actividad guardada correctamente");
            }
        } catch (error) {
            alert("Error al guardar actividad: " + error.message);
        }
    });
}

function abrirEdicion(actividad) {
    idEditando = actividad.idactividad ?? actividad.idActividad;
    document.getElementById("nombreActividad").value = actividad.nombreActividad ?? "";
    document.getElementById("fechaEvento").value = actividad.FechaEvento ?? actividad.fechaEvento ?? "";
    document.getElementById("municipio").value = actividad.municipio ?? "";
    document.getElementById("totalRecaudado").value = actividad.TotalRecaudo ?? actividad.totalRecaudado ?? "";
    window.scrollTo({ top: 0, behavior: "smooth" });
}

async function cargarActividades() {
    try {
        const resultado = await solicitarApi("GET");

        esAdministrador = resultado.rol === "admin";
        configurarAcciones();
        actividades = resultado.actividades;
        mostrarActividades();
    } catch (error) {
        alert("Error al cargar las Actividades: " + error.message);
    }
}

function mostrarActividades() {
    if (!listaActividades) return;
    listaActividades.replaceChildren();

    if (actividades.length === 0) {
        const fila = document.createElement("tr");
        const celda = document.createElement("td");
        celda.colSpan = esAdministrador ? 5 : 4;
        celda.className = "text-center py-4 text-muted";
        celda.textContent = "No hay actividades disponibles.";
        fila.append(celda);
        listaActividades.append(fila);
        return;
    }

    actividades.forEach((actividad) => {
        const fila = document.createElement("tr");
        fila.append(
            crearCelda(actividad.nombreActividad),
            crearCelda(actividad.FechaEvento ?? actividad.fechaEvento),
            crearCelda(actividad.municipio),
            crearCelda(
                (actividad.TotalRecaudo ?? actividad.totalRecaudado) === null
                    ? "Pendiente"
                    : Number(actividad.TotalRecaudo ?? actividad.totalRecaudado).toLocaleString("es-CO", {
                          style: "currency",
                          currency: "COP",
                      })
            )
        );

        if (esAdministrador) {
            const acciones = document.createElement("td");
            acciones.className = "text-nowrap";

            const botonEditar = document.createElement("button");
            botonEditar.type = "button";
            botonEditar.className = "btn btn-sm btn-outline-primary me-2";
            botonEditar.innerHTML = '<i class="bi bi-pencil"></i>';
            botonEditar.addEventListener("click", () => abrirEdicion(actividad));

            const botonEliminar = document.createElement("button");
            botonEliminar.type = "button";
            botonEliminar.className = "btn btn-sm btn-outline-danger";
            botonEliminar.innerHTML = '<i class="bi bi-trash"></i>';
            botonEliminar.addEventListener("click", () => eliminarActividad(actividad));

            acciones.append(botonEditar, botonEliminar);
            fila.append(acciones);
        }

        listaActividades.append(fila);
    });
}

function crearCelda(texto) {
    const celda = document.createElement("td");
    celda.textContent = texto;
    return celda;
}

async function eliminarActividad(actividad) {
    const confirmar = window.confirm(`¿Desea eliminar la actividad "${actividad.nombreActividad}"?`);

    if (!confirmar) {
        return;
    }

    try {
        await solicitarApi("DELETE", { idActividad: actividad.idactividad ?? actividad.idActividad });
        await cargarActividades();
        alert("Actividad eliminada correctamente");
    } catch (error) {
        alert(error.message);
    }
}

function configurarAcciones() {
    if (accionesConfiguradas) {
        return;
    }
    const botonNueva = document.getElementById("nuevaActividad");
    const encabezadoAcciones = document.getElementById("encabezadoAcciones");

    if (esAdministrador) {
        if (botonNueva) botonNueva.classList.remove("d-none");
        if (encabezadoAcciones) encabezadoAcciones.classList.remove("d-none");
        accionesConfiguradas = true;
    }
}

cargarActividades();