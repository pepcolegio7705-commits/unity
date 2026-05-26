<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 text-white fw-bold mb-0"><i class="bi bi-clipboard-check text-warning"></i> Gestión de Asistencias</h2>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs border-0 mb-4" id="asistenciasTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active bg-dark text-white border-secondary border-bottom-0" id="tomar-tab" data-bs-toggle="tab" data-bs-target="#tomar" type="button" role="tab" aria-controls="tomar" aria-selected="true">
                <i class="bi bi-pencil-square"></i> Tomar Asistencia
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link bg-dark text-white border-secondary border-bottom-0" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial" type="button" role="tab" aria-controls="historial" aria-selected="false">
                <i class="bi bi-clock-history"></i> Historial
            </button>
        </li>
    </ul>

    <div class="tab-content" id="asistenciasTabsContent">
        <!-- Tab: Tomar Asistencia -->
        <div class="tab-pane fade show active" id="tomar" role="tabpanel" aria-labelledby="tomar-tab">
            <div class="card bg-dark border-secondary shadow-sm mb-4">
                <div class="card-body p-4">
                    <form id="formSeleccionAsistencia" class="row g-3 mb-4">
                        <div class="col-md-5">
                            <label class="form-label text-muted small fw-semibold">Curso</label>
                            <select name="curso_id" id="selectCursoTomar" class="form-select bg-dark text-white border-secondary" required>
                                <option value="">Seleccionar Curso...</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-muted small fw-semibold">Fecha</label>
                            <input type="date" name="fecha" id="inputFechaTomar" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Cargar Alumnos</button>
                        </div>
                    </form>

                    <form id="formTomarAsistencia" style="display:none;">
                        <input type="hidden" name="action" value="guardar">
                        <input type="hidden" name="curso_id" id="hiddenCursoTomar">
                        <input type="hidden" name="fecha" id="hiddenFechaTomar">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle border-secondary">
                                <thead class="table-secondary text-dark">
                                    <tr>
                                        <th>Alumno</th>
                                        <th>DNI</th>
                                        <th style="width: 250px;">Estado</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyAlumnosTomar">
                                    <!-- Se carga por AJAX -->
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-success px-4"><i class="bi bi-save"></i> Guardar Asistencia</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tab: Historial -->
        <div class="tab-pane fade" id="historial" role="tabpanel" aria-labelledby="historial-tab">
            <div class="card bg-dark border-secondary shadow-sm mb-4">
                <div class="card-body p-4">
                    <!-- Filtros -->
                    <form id="formFiltroAsistencias" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">Curso</label>
                            <select name="curso_id" id="filtroCurso" class="form-select bg-dark text-white border-secondary">
                                <option value="">Todos los cursos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">Fecha</label>
                            <input type="date" name="fecha" id="filtroFecha" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">Estado</label>
                            <select name="tipo_id" id="filtroTipo" class="form-select bg-dark text-white border-secondary">
                                <option value="">Todos los estados</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">Alumno (DNI o Nombre)</label>
                            <input type="text" name="alumno" id="filtroAlumno" class="form-control bg-dark text-white border-secondary" placeholder="Buscar...">
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table id="tablaHistorialAsistencias" class="table table-dark table-hover align-middle border-secondary w-100">
                            <thead class="table-secondary text-dark">
                                <tr>
                                    <th>Alumno</th>
                                    <th>DNI</th>
                                    <th>Curso</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Observaciones</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Asistencia -->
<div class="modal fade" id="modalEditarAsistencia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEditarAsistencia" class="modal-content bg-dark text-white border-secondary shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold">Editar Asistencia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <input type="hidden" name="action" value="editar">
                <input type="hidden" name="editarAsistenciaID" id="editarAsistenciaID">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-semibold">Estado</label>
                    <select name="editarTipoAsistencia" id="editarTipoAsistencia" class="form-select bg-dark text-white border-secondary" required>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-semibold">Observaciones</label>
                    <textarea name="editarObservaciones" id="editarObservaciones" rows="3" class="form-control bg-dark text-white border-secondary"></textarea>
                </div>
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4">
                <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning rounded-3 px-4 text-dark fw-bold">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
let tablaHistorial;
let tiposAsistencia = [];

$(document).ready(function() {
    // Set today's date
    $('#inputFechaTomar').val(new Date().toISOString().split('T')[0]);

    // Cargar listas (Cursos y Tipos)
    cargarListas();

    // Inicializar DataTables
    tablaHistorial = $('#tablaHistorialAsistencias').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'controllers/asistencias_ajax.php',
            type: 'POST',
            data: function (d) {
                d.action = 'listar';
                d.curso_id = $('#filtroCurso').val();
                d.fecha = $('#filtroFecha').val();
                d.tipo_id = $('#filtroTipo').val();
                d.alumno = $('#filtroAlumno').val();
            }
        },
        columns: [
            { data: 'alumno' },
            { data: 'dni' },
            { data: 'curso' },
            { 
                data: 'fecha',
                render: function(data) {
                    if(!data) return '';
                    let d = new Date(data);
                    // fix timezone offset issue for simple string
                    d.setMinutes(d.getMinutes() + d.getTimezoneOffset());
                    return d.toLocaleDateString('es-ES');
                }
            },
            { 
                data: 'tipo',
                render: function(data) {
                    let badgeClass = 'bg-secondary';
                    if(data.toLowerCase() === 'presente') badgeClass = 'bg-success';
                    else if(data.toLowerCase() === 'ausente') badgeClass = 'bg-danger';
                    else if(data.toLowerCase().includes('tarde')) badgeClass = 'bg-warning text-dark';
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            { data: 'observaciones' },
            {
                data: null,
                orderable: false,
                render: function(data) {
                    return `<button class="btn btn-sm btn-warning text-dark btnEditarAsistencia" 
                            data-id="${data.id_hash}" data-tipo="${data.tipo_id}" data-obs="${data.observaciones || ''}">
                            <i class="bi bi-pencil"></i>
                            </button>`;
                }
            }
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
    });

    // Recargar tabla al cambiar filtros
    $('#formFiltroAsistencias').on('change input', function() {
        tablaHistorial.ajax.reload();
    });

    // Cargar alumnos para tomar asistencia
    $('#formSeleccionAsistencia').on('submit', function(e) {
        e.preventDefault();
        let curso_id = $('#selectCursoTomar').val();
        let fecha = $('#inputFechaTomar').val();
        
        if (!curso_id || !fecha) return;

        $.post('controllers/asistencias_ajax.php', { action: 'obtener_alumnos_curso', curso_id: curso_id }, function(alumnos) {
            let tbody = $('#tbodyAlumnosTomar');
            tbody.empty();
            if(alumnos.length === 0) {
                tbody.append(`<tr><td colspan="4" class="text-center text-muted">No hay alumnos asignados a este curso.</td></tr>`);
                $('#formTomarAsistencia').hide();
                return;
            }

            $('#hiddenCursoTomar').val(curso_id);
            $('#hiddenFechaTomar').val(fecha);

            // Generar options
            let opcionesTipos = '';
            tiposAsistencia.forEach(t => {
                // Seleccionar Presente por defecto (id 1, normalmente)
                let sel = (t.nombre.toLowerCase() === 'presente') ? 'selected' : '';
                opcionesTipos += `<option value="${t.id}" ${sel}>${t.nombre}</option>`;
            });

            alumnos.forEach(a => {
                let tr = `<tr>
                    <td class="fw-bold">${a.nombre_alumno}</td>
                    <td>${a.dni}</td>
                    <td>
                        <select name="tipo_asistencia[${a.alumno_id}]" class="form-select form-select-sm bg-dark text-white border-secondary">
                            ${opcionesTipos}
                        </select>
                    </td>
                    <td>
                        <input type="text" name="observaciones[${a.alumno_id}]" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Opcional">
                    </td>
                </tr>`;
                tbody.append(tr);
            });

            $('#formTomarAsistencia').fadeIn();
        }, 'json');
    });

    // Guardar asistencia
    $('#formTomarAsistencia').on('submit', function(e) {
        e.preventDefault();
        $.post('controllers/asistencias_ajax.php', $(this).serialize(), function(res) {
            if(res.status === 'success') {
                Swal.fire({icon: 'success', title: '¡Guardado!', text: res.msg, showConfirmButton: false, timer: 1500});
                $('#formTomarAsistencia').hide();
                $('#formSeleccionAsistencia')[0].reset();
                $('#inputFechaTomar').val(new Date().toISOString().split('T')[0]); // re-set date
                tablaHistorial.ajax.reload(null, false);
            } else {
                let errText = res.msg;
                if(res.errores && res.errores.length > 0) {
                    errText += '\\n' + res.errores.join('\\n');
                }
                Swal.fire('Atención', errText, 'warning');
            }
        }, 'json');
    });

    // Abrir modal editar
    $('#tablaHistorialAsistencias').on('click', '.btnEditarAsistencia', function() {
        let id = $(this).data('id');
        let tipo = $(this).data('tipo');
        let obs = $(this).data('obs');

        $('#editarAsistenciaID').val(id);
        $('#editarTipoAsistencia').val(tipo);
        $('#editarObservaciones').val(obs);

        $('#modalEditarAsistencia').modal('show');
    });

    // Guardar edición
    $('#formEditarAsistencia').on('submit', function(e) {
        e.preventDefault();
        $.post('controllers/asistencias_ajax.php', $(this).serialize(), function(res) {
            if(res.status === 'success') {
                $('#modalEditarAsistencia').modal('hide');
                tablaHistorial.ajax.reload(null, false);
                Swal.fire({icon: 'success', title: '¡Actualizado!', text: res.msg, showConfirmButton: false, timer: 1500});
            } else {
                Swal.fire('Error', res.msg, 'error');
            }
        }, 'json');
    });
});

function cargarListas() {
    $.post('controllers/asistencias_ajax.php', { action: 'obtener_cursos' }, function(cursos) {
        cursos.forEach(c => {
            $('#selectCursoTomar').append(`<option value="${c.id}">${c.nombre}</option>`);
            $('#filtroCurso').append(`<option value="${c.id}">${c.nombre}</option>`);
        });
    }, 'json');

    $.post('controllers/asistencias_ajax.php', { action: 'obtener_tipos' }, function(tipos) {
        tiposAsistencia = tipos;
        tipos.forEach(t => {
            $('#filtroTipo').append(`<option value="${t.id}">${t.nombre}</option>`);
            $('#editarTipoAsistencia').append(`<option value="${t.id}">${t.nombre}</option>`);
        });
    }, 'json');
}
</script>
