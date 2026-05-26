 document.getElementById("formularioAlumno").addEventListener("submit", function (e) {
      e.preventDefault(); // Evita envío automático

      Swal.fire({
          title: '¿Deseas guardar los cambios?',
          text: 'Esta acción actualizará los datos del alumno.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Sí, guardar',
          cancelButtonText: 'Cancelar'
      }).then((result) => {
          if (result.isConfirmed) {
              const formData = new FormData(this);

              fetch('../actualizarAlumno.php', {
                  method: 'POST',
                  body: formData
              })
              .then(response => {
                return response.text().then(text => {
                    console.log('Respuesta RAW:', text); // te muestra la respuesta real, aunque no sea JSON
                    try {
                    return JSON.parse(text);
                    } catch (err) {
                    console.error('Fallo al convertir a JSON:', err);
                    throw err;
                    }
                });
                })
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      Swal.fire({
                          title: '¡Actualizado!',
                          text: data.message || 'Los datos fueron modificados correctamente.',
                          icon: 'success',
                          confirmButtonText: 'Aceptar'
                      }).then(() => {
                          window.location.href = 'alumnos.php';
                      });
                  } else {
                      Swal.fire({
                          title: 'Error',
                          text: data.message || 'No se pudo actualizar.',
                          icon: 'error',
                          confirmButtonText: 'Aceptar'
                      });
                  }
              })
              .catch(error => {
                  console.error('Error en la solicitud:', error);
                  Swal.fire({
                      title: 'Error',
                      text: 'Hubo un problema al procesar la solicitud. Verifica tu conexión o intenta nuevamente.',
                      icon: 'error',
                      confirmButtonText: 'Aceptar'
                  });
              });
          }
      });
  });