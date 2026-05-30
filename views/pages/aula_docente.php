<div class="container-fluid px-md-4 mb-5">
    <?php
    $curso_id_hash = $_GET['curso_id'] ?? '';
    $materia_id_hash = $_GET['materia_id'] ?? '';
    ?>
    <input type="hidden" id="hdnCursoId" value="<?php echo htmlspecialchars($curso_id_hash); ?>">
    <input type="hidden" id="hdnMateriaId" value="<?php echo htmlspecialchars($materia_id_hash); ?>">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <a href="?page=mis_materias" class="btn btn-sm btn-outline-secondary rounded-pill mb-2"><i class="fa-solid fa-arrow-left me-1"></i> Volver a Mis Materias</a>
            <h2 class="page-title mb-1"><i class="fa-solid fa-door-open text-primary me-2"></i> Aula Virtual</h2>
            <p class="text-muted mb-0" id="infoAula">Cargando información del aula...</p>
        </div>
    </div>

    <!-- Navegación por pestañas -->
    <ul class="nav nav-tabs border-bottom border-light border-opacity-10 mb-4" id="aulaTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="alumnos-tab" data-bs-toggle="tab" data-bs-target="#alumnos-pane" type="button" role="tab" aria-controls="alumnos-pane" aria-selected="true"><i class="fa-solid fa-users text-info me-2"></i> Lista de Alumnos</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="calificaciones-tab" data-bs-toggle="tab" data-bs-target="#calificaciones-pane" type="button" role="tab" aria-controls="calificaciones-pane" aria-selected="false"><i class="fa-solid fa-star text-warning me-2"></i> Planilla de Calificaciones</button>
        </li>
    </ul>

    <div class="tab-content" id="aulaTabsContent">
        <!-- Pestaña: Lista de Alumnos -->
        <div class="tab-pane fade show active" id="alumnos-pane" role="tabpanel" aria-labelledby="alumnos-tab" tabindex="0">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center border-bottom border-light border-opacity-10 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-list-ol me-2 text-primary"></i> Nómina de Estudiantes</h5>
                    <button class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="imprimirLista()"><i class="fa-solid fa-print me-1"></i> Imprimir Lista</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive" id="printArea">
                        <table id="tablaAlumnos" class="table table-hover align-middle w-100">
                            <thead>
                                <tr>
                                    <th width="5%">N°</th>
                                    <th width="35%">Apellido y Nombre</th>
                                    <th width="15%">DNI</th>
                                    <th width="15%">Legajo</th>
                                    <th width="30%" class="d-print-table-cell d-none">Firma / Notas (Uso interno)</th>
                                </tr>
                            </thead>
                            <tbody id="bodyAlumnos">
                                <!-- Llenado por AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestaña: Calificaciones -->
        <div class="tab-pane fade" id="calificaciones-pane" role="tabpanel" aria-labelledby="calificaciones-tab" tabindex="0">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center border-bottom border-light border-opacity-10 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-table me-2 text-warning"></i> Carga de Notas</h5>
                    <button class="btn btn-sm btn-primary shadow-sm rounded-pill px-3" id="btnGuardarNotas" onclick="guardarNotas()"><i class="fa-solid fa-save me-1"></i> Guardar Cambios</button>
                </div>
                <div class="card-body">
                    <div id="alertNotas" class="alert alert-info d-none"><i class="fa-solid fa-circle-info me-2"></i> Algunas instancias están bloqueadas por administración y solo son de lectura.</div>
                    <div class="table-responsive">
                        <form id="formNotas">
                            <table id="tablaPlanilla" class="table table-bordered table-hover align-middle text-center w-100">
                                <thead>
                                    <tr id="headPlanilla">
                                        <!-- Llenado por AJAX -->
                                    </tr>
                                </thead>
                                <tbody id="bodyPlanilla">
                                    <!-- Llenado por AJAX -->
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos para impresión */
@media print {
    body * { visibility: hidden; }
    #printArea, #printArea * { visibility: visible; }
    #printArea { position: absolute; left: 0; top: 0; width: 100%; color: #000; }
    .table td, .table th { border: 1px solid #000 !important; color: #000 !important; }
    .d-print-table-cell { display: table-cell !important; }
    .page-title { visibility: visible; position: absolute; top: -50px; left:0; color: #000;}
}
.nota-input {
    width: 70px;
    text-align: center;
    background-color: #0f172a !important;
    color: #f8fafc !important;
    border: 1px solid #334155;
    border-radius: 4px;
}
.nota-input:focus {
    border-color: #3b82f6;
    outline: none;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}
.nota-read {
    font-weight: bold;
    color: #94a3b8;
}
.th-instancia {
    background-color: #1e293b !important;
    color: #f8fafc !important;
}
.th-alumnos {
    text-align: left !important;
}
</style>

<script>
let alumnosData = [];
let instanciasData = [];

$(document).ready(function() {
    cargarAula();
});

function cargarAula() {
    const curso = $('#hdnCursoId').val();
    const materia = $('#hdnMateriaId').val();

    if(!curso || !materia) {
        Swal.fire('Error', 'Faltan parámetros del curso o materia.', 'error');
        return;
    }

    $.post('controllers/aula_docente_ajax.php', { action: 'cargar_aula', curso_id: curso, materia_id: materia }, function(res) {
        if(res.status === 'success') {
            $('#infoAula').html(`<strong class="text-warning">${res.info.curso}</strong> &mdash; ${res.info.materia} <span class="badge bg-secondary ms-2">${res.info.ciclo}</span>`);
            
            alumnosData = res.alumnos;
            instanciasData = res.instancias;
            const notasData = res.notas;

            dibujarListaAlumnos(alumnosData);
            dibujarPlanilla(alumnosData, instanciasData, notasData);
        } else {
            Swal.fire('Error', res.msg, 'error');
        }
    }, 'json');
}

function dibujarListaAlumnos(alumnos) {
    let tbody = $('#bodyAlumnos');
    tbody.empty();
    
    if(alumnos.length === 0) {
        tbody.html('<tr><td colspan="5" class="text-center py-4">No hay alumnos registrados en este curso.</td></tr>');
        return;
    }

    alumnos.forEach((a, index) => {
        tbody.append(`
            <tr>
                <td class="text-muted fw-bold">${index + 1}</td>
                <td class="fw-bold">${a.nombre_alumno}</td>
                <td>${a.dni || '-'}</td>
                <td>${a.legajo || '-'}</td>
                <td class="d-print-table-cell d-none"></td>
            </tr>
        `);
    });
}

function dibujarPlanilla(alumnos, instancias, notas) {
    let thead = $('#headPlanilla');
    let tbody = $('#bodyPlanilla');
    
    thead.empty();
    tbody.empty();

    if(alumnos.length === 0) {
        tbody.html('<tr><td class="text-center py-4">No hay alumnos.</td></tr>');
        return;
    }

    // Cabeceras
    let headHtml = `<th width="30%" class="th-alumnos">Alumno</th>`;
    let hasBloqueadas = false;

    if(instancias.length === 0) {
        headHtml += `<th>No hay instancias de evaluación configuradas</th>`;
    } else {
        instancias.forEach(inst => {
            let badge = inst.activa == 1 ? '<span class="text-success small"><i class="fa-solid fa-pen"></i></span>' : '<span class="text-danger small"><i class="fa-solid fa-lock"></i></span>';
            headHtml += `<th class="th-instancia">${inst.nombre} ${badge}</th>`;
            if(inst.activa == 0) hasBloqueadas = true;
        });
    }
    thead.html(headHtml);

    if(hasBloqueadas) $('#alertNotas').removeClass('d-none');

    // Detectar si la materia requiere nota conceptual
    const materiaNombre = $('#infoAula').text().toLowerCase();
    const esConceptual = materiaNombre.includes('integracion de saberes') || materiaNombre.includes('integración de saberes');

    // Filas
    alumnos.forEach((a) => {
        let rowHtml = `<tr><td class="text-start fw-bold">${a.nombre_alumno}</td>`;
        
        if(instancias.length === 0) {
            rowHtml += `<td>-</td>`;
        } else {
            instancias.forEach(inst => {
                // Buscar nota
                let notaEncontrada = notas.find(n => n.alumno_id == a.alumno_id && n.instancia_id == inst.id);
                let valorNota = notaEncontrada ? notaEncontrada.nota : '';

                if (inst.activa == 1) {
                    // Input habilitado
                    let usarCualitativa = esConceptual || (inst.escala_notas === 'Cualitativa');
                    
                    if (usarCualitativa) {
                        rowHtml += `<td>
                            <select class="form-select form-select-sm nota-input mx-auto" name="notas[${a.alumno_id}][${inst.id}]">
                                <option value=""></option>
                                <option value="S" ${valorNota == 'S' ? 'selected' : ''}>S</option>
                                <option value="MB" ${valorNota == 'MB' ? 'selected' : ''}>MB</option>
                                <option value="B" ${valorNota == 'B' ? 'selected' : ''}>B</option>
                                <option value="R" ${valorNota == 'R' ? 'selected' : ''}>R</option>
                            </select>
                        </td>`;
                    } else {
                        rowHtml += `<td>
                            <input type="number" class="form-control form-control-sm nota-input mx-auto" 
                                name="notas[${a.alumno_id}][${inst.id}]" 
                                value="${valorNota}" min="1" max="10" step="0.01">
                        </td>`;
                    }
                } else {
                    // Solo lectura
                    rowHtml += `<td><span class="nota-read">${valorNota || '-'}</span></td>`;
                }
            });
        }
        
        rowHtml += `</tr>`;
        tbody.append(rowHtml);
    });
}

function guardarNotas() {
    const curso = $('#hdnCursoId').val();
    const materia = $('#hdnMateriaId').val();
    
    let form = $('#formNotas').serialize();
    form += `&action=guardar_notas&curso_id=${curso}&materia_id=${materia}`;

    const btn = $('#btnGuardarNotas');
    btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Guardando...');

    $.post('controllers/aula_docente_ajax.php', form, function(res) {
        if(res.status === 'success') {
            Swal.fire({icon: 'success', title: 'Guardado', text: res.msg, timer: 1500, showConfirmButton: false});
        } else {
            Swal.fire('Error', res.msg, 'error');
        }
    }, 'json').always(function() {
        btn.prop('disabled', false).html('<i class="fa-solid fa-save me-1"></i> Guardar Cambios');
    });
}

function imprimirLista() {
    const curso = $('#hdnCursoId').val();
    const materia = $('#hdnMateriaId').val();
    if (!curso || !materia) {
        Swal.fire('Error', 'Faltan parámetros', 'error');
        return;
    }
    window.open(`controllers/imprimir_lista_aula.php?curso_id=${curso}&materia_id=${materia}`, '_blank');
}
</script>
