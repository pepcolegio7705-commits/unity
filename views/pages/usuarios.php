<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Gestión de Usuarios</h1>
    <button class="btn btn-primary shadow-sm" onclick="abrirModalNuevo()">
        <i class="fas fa-plus fa-sm text-white-50"></i> Nuevo Usuario
    </button>
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4 border-0">
    <div class="card-header py-3 bg-white d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-users me-2"></i>Lista de Usuarios</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover w-100" id="tablaUsuarios" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>DNI</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Llenado por DataTables -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Formulario Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalUsuarioTitle">Agregar Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formUsuario">
                <div class="modal-body bg-light">
                    <input type="hidden" id="usuarioId" name="id">
                    <input type="hidden" id="usuarioAction" name="action" value="guardar">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bg-white" id="usuarioNombre" name="nombre" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">DNI <span class="text-danger">*</span></label>
                            <input type="number" class="form-control bg-white" id="usuarioDni" name="dni" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Rol <span class="text-danger">*</span></label>
                            <select class="form-select bg-white" id="usuarioRol" name="rol_id" required>
                                <option value="">Seleccione...</option>
                                <!-- Cargado vía AJAX -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" class="form-control bg-white" id="usuarioCorreo" name="correo" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Contraseña <span id="reqClave" class="text-danger">*</span></label>
                        <input type="password" class="form-control bg-white" id="usuarioClave" name="clave" minlength="6">
                        <div class="form-text" id="helpClave">Dejar en blanco para mantener la clave actual al editar.</div>
                    </div>
                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let tablaUsuarios;

$(document).ready(function() {
    // Cargar Roles
    cargarRoles();

    // Inicializar DataTables
    tablaUsuarios = $('#tablaUsuarios').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "controllers/usuarios_ajax.php",
            "type": "POST",
            "data": function (d) {
                d.action = 'listar';
            }
        },
        "columns": [
            { "data": "nombre", "className": "fw-bold" },
            { "data": "dni" },
            { "data": "correo" },
            { "data": "rol" },
            { "data": "fecha_creacion" },
            { 
                "data": null,
                "orderable": false,
                "searchable": false,
                "render": function(data, type, row) {
                    return `
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-primary" onclick='editarUsuario(${JSON.stringify(row).replace(/'/g, "&#39;")})' title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarUsuario('${row.id_hash}')" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        "responsive": true,
        "order": [[0, 'asc']]
    });

    // Envío del formulario
    $('#formUsuario').on('submit', function(e) {
        e.preventDefault();
        
        let formData = $(this).serialize();
        
        $.ajax({
            url: 'controllers/usuarios_ajax.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    $('#modalUsuario').modal('hide');
                    if (res.logout_required) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Sesión Cerrada',
                            text: 'Has modificado tus propios datos. Por seguridad, debes iniciar sesión nuevamente.',
                            confirmButtonText: 'Entendido',
                            allowOutsideClick: false
                        }).then(() => {
                            window.location.href = 'logout.php';
                        });
                    } else {
                        tablaUsuarios.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: res.msg,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Ocurrió un error en el servidor.', 'error');
            }
        });
    });
});

function cargarRoles() {
    $.post('controllers/usuarios_ajax.php', {action: 'obtener_roles'}, function(res) {
        if(res.status === 'success') {
            let html = '<option value="">Seleccione...</option>';
            res.data.forEach(r => {
                html += `<option value="${r.id}">${r.nombre}</option>`;
            });
            $('#usuarioRol').html(html);
        }
    }, 'json');
}

function resetFormUsuario() {
    $('#formUsuario')[0].reset();
    $('#usuarioId').val('');
    $('#usuarioAction').val('guardar');
    $('#modalUsuarioTitle').text('Agregar Nuevo Usuario');
    $('#usuarioClave').prop('required', true);
    $('#reqClave').show();
    $('#helpClave').hide();
}

function abrirModalNuevo() {
    resetFormUsuario();
    $('#modalUsuario').modal('show');
}

function editarUsuario(row) {
    resetFormUsuario();
    $('#usuarioAction').val('editar');
    $('#modalUsuarioTitle').text('Editar Usuario');
    
    $('#usuarioId').val(row.id_hash);
    $('#usuarioNombre').val(row.nombre);
    $('#usuarioDni').val(row.dni);
    $('#usuarioCorreo').val(row.correo);
    $('#usuarioRol').val(row.rol_id);
    
    // Contraseña no es obligatoria al editar
    $('#usuarioClave').prop('required', false);
    $('#reqClave').hide();
    $('#helpClave').show();
    
    $('#modalUsuario').modal('show');
}

function eliminarUsuario(id_hash) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "No podrás revertir esta acción.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        cancelButtonColor: '#858796',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('controllers/usuarios_ajax.php', { action: 'eliminar', id: id_hash }, function(res) {
                if (res.status === 'success') {
                    tablaUsuarios.ajax.reload(null, false);
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: res.msg,
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }, 'json');
        }
    });
}
</script>
