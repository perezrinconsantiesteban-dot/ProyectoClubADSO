
const URL_MIS_DATOS = "../Consultas/obtenerMisDatos.php";
const URL_ACTUALIZAR = "../Consultas/actualizarMisDatos.php";

let datosActuales = {};

// Consultar los datos
async function cargarMisDatos() {

    const mensaje = document.getElementById("mensaje");
    const contenedor = document.getElementById("datosAsociado");

    try {

        const respuesta = await fetch(URL_MIS_DATOS);
        const resultado = await respuesta.json();

        if (respuesta.status === 401) {
            window.location.href = "Login.html";
            return;
        }

        if (!respuesta.ok || !resultado.success) {
            throw new Error(resultado.mensaje || "Error al consultar datos");
        }

        datosActuales = resultado.asociado;

        mostrarDatos(datosActuales);

        mensaje.classList.add("d-none");
        contenedor.classList.remove("d-none");

    } catch (error) {

        mensaje.textContent = error.message;

    }
}

// Mostrar los datos en la página
function mostrarDatos(asociado) {

    const campos = [
        "documento",
        "nombres",
        "apellidos",
        "fechaNacimiento",
        "genero",
        "ciudad",
        "direccionResidencial",
        "telefono",
        "email"
    ];

    campos.forEach(campo => {

        const elemento = document.getElementById(campo);

        if (elemento) {
            elemento.textContent = asociado[campo] ?? "";
        }

    });

}

// Abrir modal para editar
document.getElementById("btnEditar").addEventListener("click", function () {

    document.getElementById("editNombres").value =
        datosActuales.nombres ?? "";

    document.getElementById("editApellidos").value =
        datosActuales.apellidos ?? "";

    document.getElementById("editFechaNacimiento").value =
        datosActuales.fechaNacimiento ?? "";

    document.getElementById("editGenero").value =
        datosActuales.genero ?? "";

    document.getElementById("editCiudad").value =
        datosActuales.ciudad ?? "";

    document.getElementById("editDireccion").value =
        datosActuales.direccionResidencial ?? "";

    document.getElementById("editTelefono").value =
        datosActuales.telefono ?? "";

    document.getElementById("editEmail").value =
        datosActuales.email ?? "";

    const modal = new bootstrap.Modal(
        document.getElementById("modalEditarDatos")
    );

    modal.show();

});

// Guardar cambios
document.getElementById("formEditarDatos").addEventListener("submit",
    async function (event) {

        event.preventDefault();

        const datos = {

            nombres: document.getElementById("editNombres").value,
            apellidos: document.getElementById("editApellidos").value,
            fechaNacimiento: document.getElementById("editFechaNacimiento").value,
            genero: document.getElementById("editGenero").value,
            ciudad: document.getElementById("editCiudad").value,
            direccionResidencial: document.getElementById("editDireccion").value,
            telefono: document.getElementById("editTelefono").value,
            email: document.getElementById("editEmail").value

        };

        try {

            const respuesta = await fetch(URL_ACTUALIZAR, {

                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify(datos)

            });

            const resultado = await respuesta.json();

            if (respuesta.status === 401) {
                window.location.href = "Login.html";
                return;
            }

            if (!respuesta.ok || !resultado.success) {
                throw new Error(resultado.mensaje || "Error al guardar los datos");
            }

            alert(resultado.mensaje);

            datosActuales = datos;

            mostrarDatos(datosActuales);

            const modalElemento = document.getElementById("modalEditarDatos");

            const modal = bootstrap.Modal.getInstance(modalElemento);

            if (modal) {
                modal.hide();
            }

        } catch (error) {

            alert(error.message);

        }

    }
);

// Ejecutar consulta al cargar la página
cargarMisDatos();
