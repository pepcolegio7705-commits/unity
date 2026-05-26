document.getElementById('formPerfil')?.addEventListener('submit', async function (e) {
  e.preventDefault();
  const form = e.target;
  const data = new FormData(form);
  const mensaje = document.getElementById('mensajePerfil');

  try {
    const respuesta = await fetch('actualizar_perfil.php', {
      method: 'POST',
      body: data
    });
    const resultado = await respuesta.json();

    if (resultado.exito) {
      mensaje.className = 'alert alert-success';
      mensaje.textContent = resultado.exito;

      setTimeout(() => {
        window.location.href = 'login.php'; // Reautenticación
      }, 1200);
    } else {
      mensaje.className = 'alert alert-danger';
      mensaje.textContent = resultado.error || 'Error inesperado.';
    }

    mensaje.classList.remove('d-none');
  } catch (error) {
    mensaje.className = 'alert alert-danger';
    mensaje.textContent = 'Error de conexión.';
    mensaje.classList.remove('d-none');
  }
});