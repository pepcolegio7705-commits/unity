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
                        <th>Apellidos</th>
                        <th>Nombres</th>
                        <th>DNI</th>
                        <th>Legajo</th>
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
            <div class="modal-header bg-primary text-white border-bottom-0 rounded-top-4">
                <h5 class="modal-title fw-bold" id="modalDocenteTitle"><i class="fa-solid fa-user-plus me-2"></i> Agregar Nuevo Docente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="id" id="docenteId">
                <input type="hidden" name="action" id="docenteAction" value="guardar">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted fw-bold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="docenteNombre" class="form-control form-control-lg bg-light" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted fw-bold">Apellido <span class="text-danger">*</span></label>
                        <input type="text" name="apellido" id="docenteApellido" class="form-control form-control-lg bg-light" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted fw-bold">DNI <span class="text-danger">*</span></label>
                        <input type="text" name="dni" id="docenteDni" class="form-control form-control-lg bg-light" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted fw-bold">CUIL</label>
                        <input type="text" name="cuil" id="docenteCuil" class="form-control form-control-lg bg-light">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted fw-bold">Legajo</label>
                        <input type="text" name="legajo" id="docenteLegajo" class="form-control form-control-lg bg-light">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted fw-bold">Titulación</label>
                        <input type="text" name="titulacion" id="docenteTitulacion" class="form-control form-control-lg bg-light">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted fw-bold">Teléfono</label>
                        <input type="text" name="telefono" id="docenteTelefono" class="form-control form-control-lg bg-light">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted fw-bold">Email</label>
                        <input type="email" name="email" id="docenteEmail" class="form-control form-control-lg bg-light">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted fw-bold">Estado</label>
                        <select name="activo" id="docenteActivo" class="form-select form-select-lg bg-light">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label text-muted fw-bold">Dirección</label>
                    <input type="text" name="direccion" id="docenteDireccion" class="form-control form-control-lg bg-light">
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">Guardar Docente</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ver Info Docente -->
<div class="modal fade" id="modalInfoDocente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow-lg">
      <div class="modal-header bg-info text-white rounded-top-4">
        <h5 class="modal-title"><i class="fa-solid fa-address-card me-2"></i> Información del Docente</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="text-center mb-4">
            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center border shadow-sm" style="width: 80px; height: 80px;">
                <i class="fa-solid fa-user-tie fa-3x text-secondary"></i>
            </div>
            <h4 class="mt-3 mb-0" id="info_nombre_completo">Nombre</h4>
            <p class="text-muted mb-0" id="info_titulacion">Título</p>
        </div>
        <hr>
        <div class="row text-sm">
            <div class="col-6 mb-3">
                <span class="text-muted d-block small"><i class="fa-solid fa-id-card me-1"></i> DNI</span>
                <strong id="info_dni"></strong>
            </div>
            <div class="col-6 mb-3">
                <span class="text-muted d-block small"><i class="fa-solid fa-hashtag me-1"></i> CUIL</span>
                <strong id="info_cuil"></strong>
            </div>
            <div class="col-6 mb-3">
                <span class="text-muted d-block small"><i class="fa-solid fa-briefcase me-1"></i> Legajo</span>
                <strong id="info_legajo"></strong>
            </div>
            <div class="col-6 mb-3">
                <span class="text-muted d-block small"><i class="fa-solid fa-phone me-1"></i> Teléfono</span>
                <strong id="info_telefono"></strong>
            </div>
            <div class="col-12 mb-3">
                <span class="text-muted d-block small"><i class="fa-solid fa-envelope me-1"></i> E-mail</span>
                <strong id="info_email"></strong>
            </div>
            <div class="col-12 mb-2">
                <span class="text-muted d-block small"><i class="fa-solid fa-map-location-dot me-1"></i> Dirección</span>
                <strong id="info_direccion"></strong>
            </div>
        </div>
      </div>
      <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Asignaciones Docente -->
<div class="modal fade" id="modalAsignarDocente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold">Asignaciones: <span id="nombreDocenteAsignacion" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <div class="row g-4">
                    <!-- Formulario de Asignación -->
                    <div class="col-md-4 border-end">
                        <h6 class="fw-bold mb-3">Nueva Asignación</h6>
                        <form id="formAsignacion">
                            <input type="hidden" name="action" value="guardar_asignacion">
                            <input type="hidden" name="docente_id" id="asignacionDocenteId">
                            
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-semibold">Ciclo Lectivo</label>
                                <select name="ciclo_lectivo_id" id="asignacionCiclo" class="form-select bg-light border-0" required>
                                    <!-- Options by AJAX -->
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-semibold">Curso</label>
                                <select name="curso_id" id="asignacionCurso" class="form-select bg-light border-0" required>
                                    <!-- Options by AJAX -->
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-semibold">Espacio Curricular / Materia</label>
                                <select name="espacio_curricular_id" id="asignacionMateria" class="form-select bg-light border-0" required>
                                    <option value="">Seleccione un curso primero</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-3">Asignar Materia</button>
                        </form>
                    </div>
                    
                    <!-- Tabla de Asignaciones Actuales -->
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Materias Asignadas</h6>
                            <div class="w-50">
                                <select id="filtroCicloAsignaciones" class="form-select form-select-sm bg-light border-0">
                                    <!-- Opciones de ciclo -->
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-hover align-middle w-100" id="tablaAsignaciones">
                                <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                                    <tr>
                                        <th>Ciclo</th>
                                        <th>Curso</th>
                                        <th>Materia</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Ajax -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
            { data: 'apellido', className: 'fw-bold text-uppercase', render: function(data) { return data || ''; } },
            { data: 'nombre', className: 'fw-semibold', render: function(data) { return data || ''; } },
            { data: 'dni' },
            { data: 'legajo', render: function(data) { return data || '<span class="text-muted small">-</span>'; } },
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
                    let nombreCompleto = row.apellido ? (row.nombre + ' ' + row.apellido) : row.nombre;
                    // Escapamos los strings para el evento onclick
                    let rowJson = JSON.stringify(row).replace(/'/g, "&#39;");
                    return `
                        <div class="btn-group shadow-sm">
                            <button class="btn btn-sm btn-light border" title="Asignaciones" onclick="gestionarAsignaciones('${data}', '${nombreCompleto}')"><i class="fa-solid fa-book-open text-primary"></i></button>
                            <button class="btn btn-sm btn-light border" title="Ver Info" onclick='verInfoDocente(${rowJson})'><i class="fa-solid fa-eye text-info"></i></button>
                            <button class="btn btn-sm btn-light border" title="Editar" onclick='editarDocente(${rowJson})'><i class="fa-solid fa-pen text-warning"></i></button>
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

    // Cargar listas iniciales para el modal de asignación
    cargarListasAsignaciones();

    // Cuando cambie el curso en el modal, buscar sus materias
    $('#asignacionCurso').change(function() {
        let curso_id = $(this).val();
        if (curso_id) {
            $.post('controllers/cursos_ajax.php', { action: 'obtener_materias', curso_id: curso_id }, function(materias) {
                let sel = $('#asignacionMateria');
                sel.empty().append('<option value="">Seleccione materia...</option>');
                materias.forEach(m => {
                    sel.append(`<option value="${m.id}">${m.nombre}</option>`);
                });
            }, 'json');
        } else {
            $('#asignacionMateria').empty().append('<option value="">Seleccione un curso primero</option>');
        }
    });

    // Guardar Asignación
    $('#formAsignacion').submit(function(e) {
        e.preventDefault();
        $.post('controllers/docentes_ajax.php', $(this).serialize(), function(res) {
            if(res.status === 'success') {
                cargarAsignacionesDocente($('#asignacionDocenteId').val(), $('#filtroCicloAsignaciones').val());
                Swal.fire({icon: 'success', title: 'Asignado', text: res.msg, timer: 1500, showConfirmButton: false});
            } else {
                Swal.fire('Error', res.msg, 'error');
            }
        }, 'json');
    });

    // Al cambiar el ciclo de la tabla, recargar asignaciones
    $('#filtroCicloAsignaciones').change(function() {
        cargarAsignacionesDocente($('#asignacionDocenteId').val(), $(this).val());
    });
});

function resetFormDocente() {
    $('#formDocente')[0].reset();
    $('#docenteId').val('');
    $('#docenteAction').val('guardar');
    $('#modalDocenteTitle').html('<i class="fa-solid fa-user-plus me-2"></i> Agregar Nuevo Docente');
}

function editarDocente(row) {
    resetFormDocente();
    $('#docenteId').val(row.id_hash);
    $('#docenteNombre').val(row.nombre);
    $('#docenteApellido').val(row.apellido);
    $('#docenteDni').val(row.dni);
    $('#docenteCuil').val(row.cuil);
    $('#docenteLegajo').val(row.legajo);
    $('#docenteTitulacion').val(row.titulacion);
    $('#docenteEmail').val(row.email);
    $('#docenteTelefono').val(row.telefono);
    $('#docenteDireccion').val(row.direccion);
    $('#docenteActivo').val(row.activo);
    $('#docenteAction').val('editar');
    $('#modalDocenteTitle').html('<i class="fa-solid fa-pen me-2"></i> Editar Docente');
    $('#modalDocente').modal('show');
}

function verInfoDocente(row) {
    let nom = row.nombre || '';
    let ape = row.apellido || '';
    let full = ape ? (nom + ' ' + ape) : nom;
    
    $('#info_nombre_completo').text(full);
    $('#info_titulacion').text(row.titulacion || 'Sin título registrado');
    $('#info_dni').text(row.dni);
    $('#info_cuil').text(row.cuil || '-');
    $('#info_legajo').text(row.legajo || '-');
    $('#info_telefono').text(row.telefono || '-');
    $('#info_email').text(row.email || '-');
    $('#info_direccion').text(row.direccion || '-');
    $('#modalInfoDocente').modal('show');
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

// Lógica de Asignaciones
function cargarListasAsignaciones() {
    // Cargar Ciclos
    $.post('controllers/asistencias_ajax.php', { action: 'obtener_ciclos' }, function(ciclos) {
        $('#asignacionCiclo').empty();
        $('#filtroCicloAsignaciones').empty();
        if(ciclos && ciclos.length > 0) {
            ciclos.forEach(c => {
                $('#asignacionCiclo').append(`<option value="${c.id}">${c.nombre}</option>`);
                $('#filtroCicloAsignaciones').append(`<option value="${c.id}">${c.nombre}</option>`);
            });
            // Seleccionar el ciclo actual (suele ser el primero o el activo, asumo el primero por ahora)
        }
    }, 'json');

    // Cargar Cursos
    $.post('controllers/asistencias_ajax.php', { action: 'obtener_cursos' }, function(cursos) {
        $('#asignacionCurso').empty().append('<option value="">Seleccione curso...</option>');
        if(cursos) {
            cursos.forEach(c => {
                $('#asignacionCurso').append(`<option value="${c.id}">${c.nombre}</option>`);
            });
        }
    }, 'json');
}

function gestionarAsignaciones(id_hash, nombreDocente) {
    $('#asignacionDocenteId').val(id_hash);
    $('#nombreDocenteAsignacion').text(nombreDocente);
    
    // Cargar la tabla de asignaciones actuales
    let ciclo_id = $('#filtroCicloAsignaciones').val();
    cargarAsignacionesDocente(id_hash, ciclo_id);
    
    $('#modalAsignarDocente').modal('show');
}

function cargarAsignacionesDocente(docente_hash, ciclo_id) {
    if (!docente_hash) return;
    $.post('controllers/docentes_ajax.php', { action: 'listar_asignaciones', docente_id: docente_hash, ciclo_id: ciclo_id }, function(res) {
        let tbody = $('#tablaAsignaciones tbody');
        tbody.empty();
        if (res.status === 'success' && res.data.length > 0) {
            res.data.forEach(a => {
                tbody.append(`
                    <tr>
                        <td><span class="badge bg-secondary">${a.ciclo}</span></td>
                        <td>${a.curso}</td>
                        <td class="fw-semibold">${a.materia}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarAsignacion(${a.id}, '${docente_hash}', '${ciclo_id}')"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                `);
            });
        } else {
            tbody.append('<tr><td colspan="4" class="text-center text-muted">No tiene asignaciones en este ciclo.</td></tr>');
        }
    }, 'json');
}

function eliminarAsignacion(asignacion_id, docente_hash, ciclo_id) {
    if (confirm("¿Estás seguro de eliminar esta asignación?")) {
        $.post('controllers/docentes_ajax.php', { action: 'eliminar_asignacion', id: asignacion_id }, function(res) {
            if (res.status === 'success') {
                cargarAsignacionesDocente(docente_hash, ciclo_id);
            } else {
                alert("Error: " + res.msg);
            }
        }, 'json');
    }
}
</script>
