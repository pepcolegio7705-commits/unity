<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title mb-0">Respaldos del Sistema</h1>
    <?php if(isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['Administrador', 'Directivo'])): ?>
        <button class="btn btn-primary shadow-sm rounded-3 px-4" id="btnCrearRespaldo">
            <i class="fa-solid fa-cloud-arrow-up me-2"></i> Generar Respaldo
        </button>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="table-responsive">
            <input type="hidden" id="csrf_token" value="<?php echo generar_token_csrf(); ?>">
            <table id="tablaRespaldos" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>Archivo</th>
                        <th>Fecha de Creación</th>
                        <th>Tamaño</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Llenado por AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let tablaRespaldos;
$(document).ready(function() {
    const csrf_token = $('#csrf_token').val();

    tablaRespaldos = $('#tablaRespaldos').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'controllers/respaldos_ajax.php',
            type: 'POST',
            data: { action: 'listar' }
        },
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        columns: [
            { data: 'nombre', className: 'fw-semibold text-primary' },
            { data: 'fecha' },
            { data: 'tamano' },
            { 
                data: 'id_hash', 
                className: 'text-end',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group shadow-sm">
                            <a href="controllers/respaldos_ajax.php?action=descargar&id=${data}" class="btn btn-sm btn-light border" title="Descargar"><i class="fa-solid fa-download text-success"></i></a>
                            <button class="btn btn-sm btn-light border" title="Restaurar" onclick="restaurarRespaldo('${data}')"><i class="fa-solid fa-clock-rotate-left text-warning"></i></button>
                            <button class="btn btn-sm btn-light border" title="Eliminar" onclick="eliminarRespaldo('${data}')"><i class="fa-solid fa-trash text-danger"></i></button>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#btnCrearRespaldo').on('click', function() {
        Swal.fire({
            title: 'Generando respaldo...',
            text: 'Por favor espera, esto puede tardar unos segundos.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.post('controllers/respaldos_ajax.php', { action: 'crear', csrf_token: csrf_token }, function(res) {
            if(res.status === 'success') {
                tablaRespaldos.ajax.reload();
                Swal.fire({icon: 'success', title: '¡Éxito!', text: res.msg});
            } else {
                Swal.fire('Error', res.msg, 'error');
            }
        }, 'json').fail(function() {
            Swal.fire('Error', 'Ocurrió un error en la conexión.', 'error');
        });
    });
});

function restaurarRespaldo(id_hash) {
    const csrf_token = $('#csrf_token').val();
    Swal.fire({
        title: '¿Restaurar Base de Datos?',
        text: "¡ATENCIÓN! Esto sobrescribirá todos los datos actuales con los del respaldo. Esta acción NO se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, restaurar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Restaurando...',
                text: 'Aplicando el respaldo, por favor no cierres la ventana.',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.post('controllers/respaldos_ajax.php', { action: 'restaurar', id: id_hash, csrf_token: csrf_token }, function(res) {
                if(res.status === 'success') {
                    Swal.fire({icon: 'success', title: 'Restaurado', text: res.msg}).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }, 'json');
        }
    });
}

function eliminarRespaldo(id_hash) {
    const csrf_token = $('#csrf_token').val();
    Swal.fire({
        title: '¿Eliminar archivo?',
        text: "No podrás recuperar este respaldo.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('controllers/respaldos_ajax.php', { action: 'eliminar', id: id_hash, csrf_token: csrf_token }, function(res) {
                if(res.status === 'success') {
                    tablaRespaldos.ajax.reload();
                    Swal.fire({icon: 'success', title: 'Eliminado', text: res.msg, timer: 1500, showConfirmButton: false});
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }, 'json');
        }
    });
}
</script>
