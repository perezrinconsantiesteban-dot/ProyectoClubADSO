const formulario = document.getElementById('formLogin');

formulario.addEventListener('submit', async function (event) {
    event.preventDefault();

    const boton = formulario.querySelector('button[type="submit"]');
    const mensaje = formulario.querySelector('.mensajeLogin');

    boton.disabled = true;
    if (mensaje) mensaje.textContent = '';

    try {
        const respuesta = await fetch(formulario.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(new FormData(formulario).entries()))
        });

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            if (mensaje) {
                mensaje.textContent = resultado.mensaje || "No fue posible iniciar sesión. Por favor, verifica tus credenciales.";
            }
            return;
        }

        localStorage.setItem("logueado", "true");
        localStorage.setItem("rol", resultado.rol);
        localStorage.setItem("nombre", resultado.nombre);

        window.location.href = 'dashboard.html';

    } catch (error) {
        console.error('Error al iniciar sesión:', error);
        if (mensaje) {
            mensaje.textContent = "Ocurrió un error al intentar iniciar sesión. Por favor, inténtalo de nuevo más tarde.";
        }
    } finally {
        boton.disabled = false;
    }
});
