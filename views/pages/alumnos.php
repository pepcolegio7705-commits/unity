<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title mb-0">Gestión de Alumnos</h1>
    <?php if(isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['Administrador', 'Secretario', 'Directivo'])): ?>
        <button class="btn btn-primary shadow-sm rounded-3 px-4" data-bs-toggle="modal" data-bs-target="#modalAgregarAlumno">
            <i class="fa-solid fa-plus me-2"></i> Nuevo Alumno
        </button>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="tablaAlumnos" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>DNI</th>
                        <th>Legajo</th>
                        <th>Curso</th>
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

<!-- Modal Agregar Alumno -->
<div class="modal fade" id="modalAgregarAlumno" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form id="formAgregarAlumno" class="modal-content border-0 shadow-lg rounded-4" enctype="multipart/form-data">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold">Agregar Nuevo Alumno</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <input type="hidden" name="action" value="guardar">
                <input type="hidden" name="csrf_token" value="<?php echo generar_token_csrf(); ?>">
                
                <div class="row g-4 mt-1">
                    <!-- Datos Personales -->
                    <div class="col-12">
                        <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">Datos Personales</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-semibold">Nombre Completo *</label>
                                <input type="text" name="alumno" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-semibold">DNI *</label>
                                <input type="text" name="dni" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-semibold">Fecha Nacimiento</label>
                                <input type="date" name="fechan" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-muted small fw-semibold">Nacionalidad</label>
                                <input type="text" name="nacionalidad" class="form-control" value="Argentina">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-semibold">Lugar de Nacimiento</label>
                                <input type="text" name="lugar" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-semibold">DNI del Tutor</label>
                                <input type="text" name="dni_tutor" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-semibold">Fotografía</label>
                                <input type="file" name="foto" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <!-- Datos de Contacto -->
                    <div class="col-12">
                        <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">Datos de Contacto</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Teléfono</label>
                                <input type="text" name="telefono" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Datos Académicos -->
                    <div class="col-12">
                        <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">Datos Académicos</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-semibold">Legajo</label>
                                <input type="text" name="legajo" id="input_legajo" class="form-control">
                                <small id="info_ultimo_legajo" class="text-info"></small>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-muted small fw-semibold">Libro</label>
                                <input type="text" name="libro" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-muted small fw-semibold">Folio</label>
                                <input type="text" name="folio" class="form-control">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label text-muted small fw-semibold">Escuela de Procedencia</label>
                                <input type="text" name="escp" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-semibold">Fecha Alta</label>
                                <input type="date" name="fecha_alta" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-semibold">Estado</label>
                                <select name="estatus" class="form-select">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Observaciones</label>
                                <textarea name="obs" class="form-control" rows="1"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-3 px-4">Guardar Alumno</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Alumno -->
<div class="modal fade" id="modalEditarAlumno" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form id="formEditarAlumno" class="modal-content border-0 shadow-lg rounded-4" enctype="multipart/form-data">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold">Editar Alumno</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="action" value="editar">
                <input type="hidden" name="csrf_token" value="<?php echo generar_token_csrf(); ?>">

                <div class="row g-4 mt-1">
                    <!-- Datos Personales -->
                    <div class="col-12">
                        <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">Datos Personales</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-semibold">Nombre Completo *</label>
                                <input type="text" name="alumno" id="edit_alumno" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-semibold">DNI *</label>
                                <input type="text" name="dni" id="edit_dni" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-semibold">Fecha Nacimiento</label>
                                <input type="date" name="fechan" id="edit_fechan" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-muted small fw-semibold">Nacionalidad</label>
                                <input type="text" name="nacionalidad" id="edit_nacionalidad" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-semibold">Lugar de Nacimiento</label>
                                <input type="text" name="lugar" id="edit_lugar" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-semibold">DNI del Tutor</label>
                                <input type="text" name="dni_tutor" id="edit_dni_tutor" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small fw-semibold">Fotografía (Dejar vacío para no cambiar)</label>
                                <input type="file" name="foto" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <!-- Datos de Contacto -->
                    <div class="col-12">
                        <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">Datos de Contacto</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Teléfono</label>
                                <input type="text" name="telefono" id="edit_telefono" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Datos Académicos -->
                    <div class="col-12">
                        <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">Datos Académicos</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-semibold">Legajo</label>
                                <input type="text" name="legajo" id="edit_legajo" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-muted small fw-semibold">Libro</label>
                                <input type="text" name="libro" id="edit_libro" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-muted small fw-semibold">Folio</label>
                                <input type="text" name="folio" id="edit_folio" class="form-control">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label text-muted small fw-semibold">Escuela de Procedencia</label>
                                <input type="text" name="escp" id="edit_escp" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-semibold">Fecha Alta</label>
                                <input type="date" name="fecha_alta" id="edit_fecha_alta" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted small fw-semibold">Estado</label>
                                <select name="estatus" id="edit_estatus" class="form-select">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-semibold">Observaciones</label>
                                <textarea name="obs" id="edit_obs" class="form-control" rows="1"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning rounded-3 px-4">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ver Alumno -->
<div class="modal fade" id="modalVerAlumno" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold">Detalles del Alumno</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4" id="contenidoVerAlumno">
                <!-- Se llena por AJAX -->
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Helper para escapar HTML en JS
function escapeHTML(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>'"]/g, function(tag) {
        const charsToReplace = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
        };
        return charsToReplace[tag] || tag;
    });
}

let tablaAlumnos;
$(document).ready(function() {
    tablaAlumnos = $('#tablaAlumnos').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'controllers/alumnos_ajax.php',
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
            { data: 'legajo' },
            { data: 'curso', render: function(data) { return data ? `<span class="badge bg-primary bg-opacity-10 text-primary">${data}</span>` : '<span class="text-muted small">Sin asignar</span>'; } },
            { data: 'estado', render: function(data) { return `<span class="badge ${data === 'Activo' ? 'bg-success' : 'bg-secondary'} bg-opacity-10 text-${data === 'Activo' ? 'success' : 'secondary'}">${data}</span>`; } },
            { 
                data: 'id_hash', 
                className: 'text-end',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group shadow-sm">
                            <button class="btn btn-sm btn-light border" title="Ver" onclick="verAlumno('${data}')"><i class="fa-solid fa-eye text-primary"></i></button>
                            <button class="btn btn-sm btn-light border" title="Editar" onclick="editarAlumno('${data}')"><i class="fa-solid fa-pen text-warning"></i></button>
                            <button class="btn btn-sm btn-light border" title="Eliminar" onclick="eliminarAlumno('${data}')"><i class="fa-solid fa-trash text-danger"></i></button>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#formAgregarAlumno').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        $.ajax({
            url: 'controllers/alumnos_ajax.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    $('#modalAgregarAlumno').modal('hide');
                    $('#formAgregarAlumno')[0].reset();
                    tablaAlumnos.ajax.reload();
                    Swal.fire({icon: 'success', title: '¡Éxito!', text: res.msg, showConfirmButton: false, timer: 1500});
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }
        });
    });

    // Auto-generar legajo al abrir el modal de agregar
    $('#modalAgregarAlumno').on('show.bs.modal', function () {
        $.post('controllers/alumnos_ajax.php', { action: 'obtener_ultimo_legajo' }, function(res) {
            if(res.status === 'success') {
                $('#input_legajo').val(res.siguiente);
                $('#info_ultimo_legajo').text('Último legajo ingresado: ' + res.ultimo);
            }
        }, 'json');
    });

    $('#formEditarAlumno').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        $.ajax({
            url: 'controllers/alumnos_ajax.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    $('#modalEditarAlumno').modal('hide');
                    tablaAlumnos.ajax.reload(null, false);
                    Swal.fire({icon: 'success', title: '¡Actualizado!', text: res.msg, showConfirmButton: false, timer: 1500});
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }
        });
    });
});

function eliminarAlumno(id_hash) {
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
            let csrf_token = $('#formAgregarAlumno').find('input[name="csrf_token"]').val(); // Usamos el token del formulario principal
            $.post('controllers/alumnos_ajax.php', { action: 'eliminar', id: id_hash, csrf_token: csrf_token }, function(res) {
                if(res.status === 'success') {
                    tablaAlumnos.ajax.reload();
                    Swal.fire({icon: 'success', title: 'Eliminado', text: res.msg, showConfirmButton: false, timer: 1500});
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }, 'json');
        }
    });
}

function editarAlumno(id_hash) {
    $.post('controllers/alumnos_ajax.php', { action: 'obtener', id: id_hash }, function(res) {
        if(res.status === 'success') {
            $('#edit_id').val(res.data.id_hash);
            $('#edit_alumno').val(res.data.alumno);
            $('#edit_dni').val(res.data.dni);
            $('#edit_fechan').val(res.data.fechan);
            $('#edit_nacionalidad').val(res.data.nacionalidad);
            $('#edit_lugar').val(res.data.lugar);
            $('#edit_dni_tutor').val(res.data.dni_tutor);
            $('#edit_legajo').val(res.data.legajo);
            $('#edit_libro').val(res.data.libro);
            $('#edit_folio').val(res.data.folio);
            $('#edit_email').val(res.data.email);
            $('#edit_telefono').val(res.data.telefono);
            $('#edit_escp').val(res.data.escp);
            $('#edit_fecha_alta').val(res.data.fecha_alta);
            $('#edit_estatus').val(res.data.estatus);
            $('#edit_obs').val(res.data.obs);
            $('#modalEditarAlumno').modal('show');
        } else {
            Swal.fire('Error', res.msg, 'error');
        }
    }, 'json');
}

function verAlumno(id_hash) {
    $.post('controllers/alumnos_ajax.php', { action: 'obtener', id: id_hash }, function(res) {
        if(res.status === 'success') {
            const data = res.data;
            let fotoHtml = data.foto ? `<img src="uploads/fotos_alumnos/${escapeHTML(data.foto)}" alt="Foto Alumno" class="img-thumbnail" style="max-height:120px;">` : `<div class="bg-light d-flex align-items-center justify-content-center border rounded" style="width: 100px; height: 120px;"><i class="fa-solid fa-user text-muted fs-1"></i></div>`;
            let estadoHtml = data.estatus == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';

            let html = `
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        ${fotoHtml}
                        <div class="mt-2">${estadoHtml}</div>
                    </div>
                    <div class="col-md-9">
                        <h5 class="fw-bold mb-1">${escapeHTML(data.alumno)}</h5>
                        <p class="text-muted small mb-3"><i class="fa-solid fa-id-card"></i> DNI: ${escapeHTML(data.dni)} | <i class="fa-solid fa-calendar"></i> Alta: ${escapeHTML(data.fecha_alta)}</p>
                        
                        <div class="row g-3">
                            <div class="col-md-6"><strong class="small text-primary">Fecha Nacimiento:</strong><br> ${escapeHTML(data.fechan) || '-'}</div>
                            <div class="col-md-6"><strong class="small text-primary">Nacionalidad y Lugar:</strong><br> ${escapeHTML(data.nacionalidad) || '-'} / ${escapeHTML(data.lugar) || '-'}</div>
                            
                            <div class="col-md-6"><strong class="small text-primary">DNI Tutor:</strong><br> ${escapeHTML(data.dni_tutor) || '-'}</div>
                            <div class="col-md-6"><strong class="small text-primary">Email:</strong><br> ${escapeHTML(data.email) || '-'}</div>
                            
                            <div class="col-md-6"><strong class="small text-primary">Teléfono:</strong><br> ${escapeHTML(data.telefono) || '-'}</div>
                            <div class="col-md-6"><strong class="small text-primary">Escuela Procedencia:</strong><br> ${escapeHTML(data.escp) || '-'}</div>

                            <div class="col-md-4"><strong class="small text-primary">Legajo:</strong><br> ${escapeHTML(data.legajo) || '-'}</div>
                            <div class="col-md-4"><strong class="small text-primary">Libro:</strong><br> ${escapeHTML(data.libro) || '-'}</div>
                            <div class="col-md-4"><strong class="small text-primary">Folio:</strong><br> ${escapeHTML(data.folio) || '-'}</div>
                            
                            <div class="col-12"><strong class="small text-primary">Observaciones:</strong><br> ${escapeHTML(data.obs) || '-'}</div>
                        </div>
                    </div>
                </div>
            `;
            $('#contenidoVerAlumno').html(html);
            $('#modalVerAlumno').modal('show');
        } else {
            Swal.fire('Error', res.msg, 'error');
        }
    }, 'json');
}
</script>
