<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title mb-0">Gestión de Cursos</h1>
    <?php if(isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['Administrador', 'Secretario', 'Directivo'])): ?>
        <button class="btn btn-primary shadow-sm rounded-3 px-4" data-bs-toggle="modal" data-bs-target="#modalCurso" onclick="resetFormCurso()">
            <i class="fa-solid fa-plus me-2"></i> Nuevo Curso
        </button>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="tablaCursos" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Curso</th>
                        <th>Turno</th>
                        <th>Orientación</th>
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

<!-- Modal Agregar/Editar Curso -->
<div class="modal fade" id="modalCurso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formCurso" class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold" id="modalCursoTitle">Agregar Nuevo Curso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <div class="row g-3 mt-1">
                    <input type="hidden" name="id" id="cursoId">
                    <input type="hidden" name="action" id="cursoAction" value="guardar">
                    
                    <div class="col-12">
                        <label class="form-label text-muted small fw-semibold">Nombre del Curso *</label>
                        <input type="text" name="nombre" id="cursoNombre" class="form-control bg-light border-0" required placeholder="Ej. 1ro A">
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label text-muted small fw-semibold">Turno *</label>
                        <select name="turno" id="cursoTurno" class="form-select bg-light border-0" required>
                            <option value="">Seleccione un turno</option>
                            <option value="Mañana">Mañana</option>
                            <option value="Tarde">Tarde</option>
                            <option value="Vespertino">Vespertino</option>
                            <option value="Noche">Noche</option>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label text-muted small fw-semibold">Orientación</label>
                        <select name="orientacion_id" id="cursoOrientacion" class="form-select bg-light border-0">
                            <option value="">Sin orientación</option>
                            <!-- Llenado por AJAX -->
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-3 px-4">Guardar Curso</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ver Alumnos -->
<div class="modal fade" id="modalAlumnosCurso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold" id="modalAlumnosTitle">Alumnos Inscriptos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <input type="hidden" id="cursoIdAsignar">
                
                <div class="mb-4">
                    <label class="form-label text-muted small fw-semibold">Asignar Nuevo Alumno</label>
                    <input type="text" id="buscarAlumnoAsignar" class="form-control bg-light border-0" placeholder="Buscar por Nombre o DNI (Mín. 3 caracteres)..." autocomplete="off">
                    <div id="resultadosBusqueda" class="mt-2"></div>
                </div>

                <div class="table-responsive">
                    <table id="tablaAlumnosCurso" class="table table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Alumno</th>
                                <th>DNI</th>
                                <th>Legajo</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Llenado por AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4">
                <button type="button" class="btn btn-danger rounded-3 px-4" onclick="generarPDFCurso()"><i class="bi bi-file-earmark-pdf"></i> Generar Lista (PDF)</button>
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
let tablaCursos;
$(document).ready(function() {
    // Cargar orientaciones para el select
    $.post('controllers/cursos_ajax.php', { action: 'listar_orientaciones' }, function(res) {
        let options = '<option value="">Sin orientación</option>';
        res.forEach(function(o) {
            options += `<option value="${o.id}">${o.nombre}</option>`;
        });
        $('#cursoOrientacion').html(options);
    }, 'json');

    tablaCursos = $('#tablaCursos').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'controllers/cursos_ajax.php',
            type: 'POST',
            data: { action: 'listar' }
        },
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        columns: [
            { data: 'id_hash', visible: false },
            { data: 'nombre', className: 'fw-bold' },
            { data: 'turno' },
            { data: 'orientacion', render: function(data) { return data || '<span class="text-muted small">Sin orientación</span>'; } },
            { 
                data: 'id_hash', 
                className: 'text-end',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group shadow-sm">
                            <button class="btn btn-sm btn-info text-white border-info" title="Ver Alumnos" onclick='verAlumnosCurso("${data}", "${row.nombre} - ${row.turno}")'><i class="fa-solid fa-users"></i></button>
                            <button class="btn btn-sm btn-light border" title="Editar" onclick='editarCurso(${JSON.stringify(row)})'><i class="fa-solid fa-pen text-warning"></i></button>
                            <button class="btn btn-sm btn-light border" title="Eliminar" onclick="eliminarCurso('${data}')"><i class="fa-solid fa-trash text-danger"></i></button>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#formCurso').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'controllers/cursos_ajax.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    $('#modalCurso').modal('hide');
                    tablaCursos.ajax.reload();
                    Swal.fire({icon: 'success', title: '¡Éxito!', text: res.msg, showConfirmButton: false, timer: 1500});
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }
        });
    });
});

function resetFormCurso() {
    $('#formCurso')[0].reset();
    $('#cursoId').val('');
    $('#cursoAction').val('guardar');
    $('#modalCursoTitle').text('Agregar Nuevo Curso');
}

function editarCurso(row) {
    resetFormCurso();
    $('#cursoId').val(row.id_hash);
    $('#cursoNombre').val(row.nombre);
    $('#cursoTurno').val(row.turno);
    $('#cursoOrientacion').val(row.orientacion_id);
    $('#cursoAction').val('editar');
    $('#modalCursoTitle').text('Editar Curso');
    $('#modalCurso').modal('show');
}

function eliminarCurso(id_hash) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Eliminar este curso podría afectar las materias y alumnos asociados.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('controllers/cursos_ajax.php', { action: 'eliminar', id: id_hash }, function(res) {
                if(res.status === 'success') {
                    tablaCursos.ajax.reload();
                    Swal.fire({icon: 'success', title: 'Eliminado', text: res.msg, showConfirmButton: false, timer: 1500});
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }, 'json');
        }
    });
}

// Funciones para Gestión de Alumnos por Curso
let tablaAlumnosCurso;
function verAlumnosCurso(id_hash, nombreCurso) {
    $('#modalAlumnosTitle').text('Alumnos Inscriptos - ' + nombreCurso);
    $('#cursoIdAsignar').val(id_hash);
    $('#modalAlumnosCurso').modal('show');

    if ($.fn.DataTable.isDataTable('#tablaAlumnosCurso')) {
        $('#tablaAlumnosCurso').DataTable().destroy();
    }

    tablaAlumnosCurso = $('#tablaAlumnosCurso').DataTable({
        ajax: {
            url: 'controllers/cursos_ajax.php',
            type: 'POST',
            data: function(d) {
                d.action = 'listar_alumnos_curso';
                d.curso_id = id_hash;
            }
        },
        columns: [
            { data: 'nombre_alumno', className: 'fw-bold' },
            { data: 'dni' },
            { data: 'legajo' },
            { 
                data: 'asignacion_id_hash',
                className: 'text-end',
                orderable: false,
                render: function(data) {
                    return `<button class="btn btn-sm btn-outline-danger" title="Desasignar Alumno" onclick="desasignarAlumno('${data}')"><i class="fa-solid fa-user-minus"></i></button>`;
                }
            }
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        paging: false,
        info: false,
        scrollY: '300px'
    });

    // Limpiar input
    $('#buscarAlumnoAsignar').val('');
    $('#resultadosBusqueda').empty();
}

function buscarAlumnosParaAsignar() {
    let q = $('#buscarAlumnoAsignar').val();
    let curso_id = $('#cursoIdAsignar').val();

    if (q.length < 3) {
        $('#resultadosBusqueda').empty();
        return;
    }

    $.post('controllers/cursos_ajax.php', { action: 'buscar_alumnos_para_asignar', q: q, curso_id: curso_id }, function(res) {
        let html = '';
        if (res.results && res.results.length > 0) {
            html = '<div class="list-group">';
            res.results.forEach(function(r) {
                html += `<button type="button" class="list-group-item list-group-item-action bg-dark text-white border-secondary" onclick="asignarAlumno('${r.id}', '${curso_id}')"><i class="fa-solid fa-plus-circle text-success me-2"></i> ${r.text}</button>`;
            });
            html += '</div>';
        } else {
            html = '<div class="text-muted small mt-2">No se encontraron alumnos disponibles con esa búsqueda.</div>';
        }
        $('#resultadosBusqueda').html(html);
    }, 'json');
}

// Bind de busqueda al presionar tecla
$('#buscarAlumnoAsignar').on('keyup', function() {
    clearTimeout($.data(this, 'timer'));
    var wait = setTimeout(buscarAlumnosParaAsignar, 500);
    $(this).data('timer', wait);
});

function asignarAlumno(alumno_id, curso_id) {
    $.post('controllers/cursos_ajax.php', { action: 'asignar_alumno', alumno_id: alumno_id, curso_id: curso_id }, function(res) {
        if(res.status === 'success') {
            Swal.fire({icon: 'success', title: 'Asignado', text: res.msg, showConfirmButton: false, timer: 1500});
            $('#buscarAlumnoAsignar').val('');
            $('#resultadosBusqueda').empty();
            tablaAlumnosCurso.ajax.reload();
        } else {
            Swal.fire('Error', res.msg, 'error');
        }
    }, 'json');
}

function desasignarAlumno(asignacion_id) {
    Swal.fire({
        title: '¿Desasignar alumno?',
        text: "El alumno será removido del curso actual.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, desasignar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('controllers/cursos_ajax.php', { action: 'desasignar_alumno', asignacion_id: asignacion_id }, function(res) {
                if(res.status === 'success') {
                    tablaAlumnosCurso.ajax.reload();
                    Swal.fire({icon: 'success', title: 'Removido', text: res.msg, showConfirmButton: false, timer: 1500});
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }, 'json');
        }
    });
}

function generarPDFCurso() {
    let curso_id = $('#cursoIdAsignar').val();
    if(curso_id) {
        window.open('backend/reporte_curso_pdf.php?curso_id=' + encodeURIComponent(curso_id), '_blank');
    }
}
</script>
