<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 text-white fw-bold mb-0"><i class="bi bi-journal-bookmark-fill text-warning"></i> Gestión de Calificaciones</h2>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs border-0 mb-4" id="calificacionesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active bg-dark text-white border-secondary border-bottom-0" id="cargar-tab" data-bs-toggle="tab" data-bs-target="#cargar" type="button" role="tab" aria-controls="cargar" aria-selected="true">
                <i class="bi bi-pencil-square"></i> Cargar Notas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link bg-dark text-white border-secondary border-bottom-0" id="boletin-tab" data-bs-toggle="tab" data-bs-target="#boletin" type="button" role="tab" aria-controls="boletin" aria-selected="false">
                <i class="bi bi-table"></i> Boletín (Matriz)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="calificacionesTabsContent">
        <!-- Tab: Cargar Notas -->
        <div class="tab-pane fade show active" id="cargar" role="tabpanel" aria-labelledby="cargar-tab">
            <div class="card bg-dark border-secondary shadow-sm mb-4">
                <div class="card-body p-4">
                    <form id="formSeleccionCargar" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">Curso</label>
                            <select name="curso_id" id="selectCursoCargar" class="form-select bg-dark text-white border-secondary" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">Materia</label>
                            <select name="materia_id" id="selectMateriaCargar" class="form-select bg-dark text-white border-secondary" required>
                                <option value="">Seleccione curso primero</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-semibold">Instancia</label>
                            <select name="instancia_id" id="selectInstanciaCargar" class="form-select bg-dark text-white border-secondary" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-search"></i> Buscar Alumnos</button>
                        </div>
                    </form>

                    <form id="formNotasAlumnos" style="display:none;">
                        <input type="hidden" name="action" value="guardar_notas">
                        <input type="hidden" name="curso_id" id="hiddenCursoCargar">
                        <input type="hidden" name="materia_id" id="hiddenMateriaCargar">
                        <input type="hidden" name="instancia_id" id="hiddenInstanciaCargar">
                        
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle border-secondary w-100">
                                <thead class="table-secondary text-dark">
                                    <tr>
                                        <th>Alumno</th>
                                        <th style="width: 150px;">Nota</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyAlumnosCargar">
                                    <!-- Dynamic content -->
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-success px-4"><i class="bi bi-save"></i> Guardar Notas</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tab: Boletín -->
        <div class="tab-pane fade" id="boletin" role="tabpanel" aria-labelledby="boletin-tab">
            <div class="card bg-dark border-secondary shadow-sm mb-4">
                <div class="card-body p-4">
                    <form id="formSeleccionBoletin" class="row g-3 mb-4">
                        <div class="col-md-5">
                            <label class="form-label text-muted small fw-semibold">Curso</label>
                            <select name="curso_id" id="selectCursoBoletin" class="form-select bg-dark text-white border-secondary" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-muted small fw-semibold">Instancia</label>
                            <select name="instancia_id" id="selectInstanciaBoletin" class="form-select bg-dark text-white border-secondary" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-table"></i> Ver Boletín</button>
                        </div>
                    </form>

                    <div id="tablaCalificacionesContainer" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 id="tituloBoletin" class="text-info fw-bold mb-0"></h5>
                            <button type="button" class="btn btn-danger btn-sm" onclick="generarPDFBoletin()"><i class="bi bi-file-earmark-pdf"></i> Imprimir Boletín (PDF)</button>
                        </div>
                        <div class="table-responsive">
                            <table id="tablaBoletin" class="table table-dark table-bordered table-hover border-secondary text-center w-100">
                                <thead class="table-secondary text-dark" id="theadBoletin">
                                </thead>
                                <tbody id="tbodyBoletin">
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
$(document).ready(function() {
    cargarListas();

    // Cascading dropdown Cursos -> Materias in Cargar
    $('#selectCursoCargar').on('change', function() {
        let curso_id = $(this).val();
        let selectMateria = $('#selectMateriaCargar');
        selectMateria.empty().append('<option value="">Cargando...</option>');
        
        if(!curso_id) {
            selectMateria.empty().append('<option value="">Seleccione curso primero</option>');
            return;
        }

        $.post('controllers/calificaciones_ajax.php', { action: 'obtener_materias', curso_id: curso_id }, function(materias) {
            selectMateria.empty();
            if(materias.length === 0) {
                selectMateria.append('<option value="">Sin materias asignadas</option>');
            } else {
                selectMateria.append('<option value="">Seleccione...</option>');
                materias.forEach(m => {
                    selectMateria.append(`<option value="${m.id}">${m.nombre}</option>`);
                });
            }
        }, 'json');
    });

    // Formulario de Selección para Cargar Notas
    $('#formSeleccionCargar').on('submit', function(e) {
        e.preventDefault();
        let curso_id = $('#selectCursoCargar').val();
        let materia_id = $('#selectMateriaCargar').val();
        let instancia_id = $('#selectInstanciaCargar').val();

        if(!curso_id || !materia_id || !instancia_id) return;

        $('#hiddenCursoCargar').val(curso_id);
        $('#hiddenMateriaCargar').val(materia_id);
        $('#hiddenInstanciaCargar').val(instancia_id);

        $.post('controllers/calificaciones_ajax.php', { action: 'obtener_alumnos_para_calificar', curso_id: curso_id, materia_id: materia_id, instancia_id: instancia_id }, function(alumnos) {
            let tbody = $('#tbodyAlumnosCargar');
            tbody.empty();

            if(alumnos.length === 0) {
                tbody.append(`<tr><td colspan="3" class="text-center text-muted">No hay alumnos asignados a este curso.</td></tr>`);
                $('#formNotasAlumnos').hide();
                return;
            }

            alumnos.forEach(a => {
                let nota = a.nota !== null ? a.nota : '';
                let obs = a.observaciones !== null ? a.observaciones : '';
                tbody.append(`
                    <tr>
                        <td class="fw-bold">
                            ${a.alumno}
                            <input type="hidden" name="alumno_id[]" value="${a.alumno_id}">
                        </td>
                        <td>
                            <input type="text" name="nota[]" class="form-control form-control-sm bg-dark text-white border-secondary text-center fw-bold" value="${nota}" placeholder="-">
                        </td>
                        <td>
                            <input type="text" name="observaciones[]" class="form-control form-control-sm bg-dark text-white border-secondary" value="${obs}">
                        </td>
                    </tr>
                `);
            });

            $('#formNotasAlumnos').fadeIn();
        }, 'json');
    });

    // Guardar Notas
    $('#formNotasAlumnos').on('submit', function(e) {
        e.preventDefault();
        $.post('controllers/calificaciones_ajax.php', $(this).serialize(), function(res) {
            if(res.status === 'success') {
                Swal.fire({icon: 'success', title: '¡Guardado!', text: res.msg, showConfirmButton: false, timer: 1500});
                $('#formNotasAlumnos').hide();
                $('#formSeleccionCargar')[0].reset();
                $('#selectMateriaCargar').empty().append('<option value="">Seleccione curso primero</option>');
            } else {
                Swal.fire('Error', res.msg, 'error');
            }
        }, 'json');
    });

    // Formulario de Selección Boletin
    $('#formSeleccionBoletin').on('submit', function(e) {
        e.preventDefault();
        let curso_id = $('#selectCursoBoletin').val();
        let instancia_id = $('#selectInstanciaBoletin').val();
        
        if(!curso_id || !instancia_id) return;

        $('#tablaCalificacionesContainer').hide();

        $.post('controllers/calificaciones_ajax.php', { action: 'listar_matriz', curso_id: curso_id, instancia_id: instancia_id }, function(res) {
            if(res.status === 'error') {
                Swal.fire('Atención', res.msg, 'warning');
                return;
            }

            let curso = res.curso;
            let materias = res.materias;
            let alumnos = res.alumnos;

            $('#tituloBoletin').html(`<i class="bi bi-mortarboard"></i> Curso: ${curso.nombre} - Turno: ${curso.turno}`);

            let thead = '<tr><th class="text-start">Alumno</th>';
            materias.forEach(m => {
                thead += `<th>${m.nombre}</th>`;
            });
            thead += '</tr>';
            $('#theadBoletin').html(thead);

            let tbody = '';
            alumnos.forEach(a => {
                tbody += `<tr><td class="text-start fw-bold">${a.alumno}</td>`;
                materias.forEach(m => {
                    let nota = a.notas[m.id];
                    let badgeClass = 'text-muted';
                    if(nota !== '-') {
                        let notaNum = parseFloat(nota);
                        if(!isNaN(notaNum)) {
                            if(notaNum >= 7) badgeClass = 'text-success fw-bold';
                            else if(notaNum >= 4) badgeClass = 'text-warning fw-bold';
                            else badgeClass = 'text-danger fw-bold';
                        } else {
                            badgeClass = 'text-info fw-bold';
                        }
                    }
                    tbody += `<td class="${badgeClass}">${nota}</td>`;
                });
                tbody += `</tr>`;
            });
            $('#tbodyBoletin').html(tbody);

            $('#tablaCalificacionesContainer').fadeIn();

            // Destruir instancia anterior si existe
            if ($.fn.DataTable.isDataTable('#tablaBoletin')) {
                $('#tablaBoletin').DataTable().destroy();
            }

            // Inicializar DataTables sin paginación ni busqueda compleja, pero con botones
            $('#tablaBoletin').DataTable({
                paging: false,
                searching: true,
                info: false,
                ordering: true,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i> Imprimir',
                        className: 'btn btn-secondary btn-sm',
                        orientation: 'landscape'
                    }
                ],
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });

        }, 'json');
    });

});

function generarPDFBoletin() {
    let curso_id = $('#selectCursoBoletin').val();
    let instancia_id = $('#selectInstanciaBoletin').val();
    if(curso_id && instancia_id) {
        window.open('backend/reporte_boletin_pdf.php?curso_id=' + encodeURIComponent(curso_id) + '&instancia_id=' + encodeURIComponent(instancia_id), '_blank');
    }
}

function cargarListas() {
    $.post('controllers/calificaciones_ajax.php', { action: 'obtener_cursos' }, function(cursos) {
        cursos.forEach(c => {
            let opt = `<option value="${c.id}">${c.nombre}</option>`;
            $('#selectCursoCargar').append(opt);
            $('#selectCursoBoletin').append(opt);
        });
    }, 'json');

    $.post('controllers/calificaciones_ajax.php', { action: 'obtener_instancias' }, function(instancias) {
        if(instancias.length === 0) {
            let opt = '<option value="">No hay instancias</option>';
            $('#selectInstanciaCargar').append(opt);
            $('#selectInstanciaBoletin').append(opt);
        } else {
            instancias.forEach(i => {
                let opt = `<option value="${i.id}">${i.nombre} (${i.tipo}) - ${i.ciclo_nombre}</option>`;
                if (i.activa == 1) {
                    $('#selectInstanciaCargar').append(opt);
                }
                $('#selectInstanciaBoletin').append(opt);
            });
        }
    }, 'json');
}
</script>
