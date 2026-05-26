<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title mb-0">Gestión de Docentes</h1>
    <?php if(isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['Administrador', 'Secretario', 'Directivo'])): ?>
        <button class="btn btn-primary shadow-sm rounded-3 px-4" data-bs-toggle="modal" data-bs-target="#modalDocente" onclick="resetFormDocente()">
            <i class="fa-solid fa-plus me-2"></i> Nuevo Docente
        </button>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="tablaDocentes" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>DNI</th>
                        <th>Titulación</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
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

<!-- Modal Agregar/Editar Docente -->
<div class="modal fade" id="modalDocente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="formDocente" class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold" id="modalDocenteTitle">Agregar Nuevo Docente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <div class="row g-3 mt-1">
                    <input type="hidden" name="id" id="docenteId">
                    <input type="hidden" name="action" id="docenteAction" value="guardar">
                    
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold">Nombre Completo *</label>
                        <input type="text" name="nombre" id="docenteNombre" class="form-control bg-light border-0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold">DNI *</label>
                        <input type="text" name="dni" id="docenteDni" class="form-control bg-light border-0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold">Titulación</label>
                        <input type="text" name="titulacion" id="docenteTitulacion" class="form-control bg-light border-0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold">Email</label>
                        <input type="email" name="email" id="docenteEmail" class="form-control bg-light border-0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold">Teléfono</label>
                        <input type="text" name="telefono" id="docenteTelefono" class="form-control bg-light border-0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold">Estado</label>
                        <select name="activo" id="docenteActivo" class="form-select bg-light border-0">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-3 px-4">Guardar Docente</button>
            </div>
        </form>
    </div>
</div>

<script>
let tablaDocentes;
$(document).ready(function() {
    tablaDocentes = $('#tablaDocentes').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'controllers/docentes_ajax.php',
            type: 'POST',
            data: { action: 'listar' }
        },
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        columns: [
            { data: 'id_hash', visible: false },
            { data: 'nombre', className: 'fw-semibold' },
            { data: 'dni' },
            { data: 'titulacion', render: function(data) { return data || '<span class="text-muted small">-</span>'; } },
            { data: 'email', render: function(data) { return data || '<span class="text-muted small">-</span>'; } },
            { data: 'telefono', render: function(data) { return data || '<span class="text-muted small">-</span>'; } },
            { data: 'activo', render: function(data) { 
                return data == 1 
                    ? `<span class="badge bg-success bg-opacity-10 text-success">Activo</span>` 
                    : `<span class="badge bg-secondary bg-opacity-10 text-secondary">Inactivo</span>`; 
            } },
            { 
                data: 'id_hash', 
                className: 'text-end',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group shadow-sm">
                            <button class="btn btn-sm btn-light border" title="Editar" onclick='editarDocente(${JSON.stringify(row)})'><i class="fa-solid fa-pen text-warning"></i></button>
                            <button class="btn btn-sm btn-light border" title="Eliminar" onclick="eliminarDocente('${data}')"><i class="fa-solid fa-trash text-danger"></i></button>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#formDocente').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'controllers/docentes_ajax.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    $('#modalDocente').modal('hide');
                    tablaDocentes.ajax.reload();
                    Swal.fire({icon: 'success', title: '¡Éxito!', text: res.msg, showConfirmButton: false, timer: 1500});
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }
        });
    });
});

function resetFormDocente() {
    $('#formDocente')[0].reset();
    $('#docenteId').val('');
    $('#docenteAction').val('guardar');
    $('#modalDocenteTitle').text('Agregar Nuevo Docente');
}

function editarDocente(row) {
    resetFormDocente();
    $('#docenteId').val(row.id_hash);
    $('#docenteNombre').val(row.nombre);
    $('#docenteDni').val(row.dni);
    $('#docenteTitulacion').val(row.titulacion);
    $('#docenteEmail').val(row.email);
    $('#docenteTelefono').val(row.telefono);
    $('#docenteActivo').val(row.activo);
    $('#docenteAction').val('editar');
    $('#modalDocenteTitle').text('Editar Docente');
    $('#modalDocente').modal('show');
}

function eliminarDocente(id_hash) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "No podrás revertir esta acción.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('controllers/docentes_ajax.php', { action: 'eliminar', id: id_hash }, function(res) {
                if(res.status === 'success') {
                    tablaDocentes.ajax.reload();
                    Swal.fire({icon: 'success', title: 'Eliminado', text: res.msg, showConfirmButton: false, timer: 1500});
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }, 'json');
        }
    });
}
</script>
