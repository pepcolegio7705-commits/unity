<div class="container-fluid px-md-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h2 class="page-title mb-1"><i class="fa-solid fa-chart-bar text-primary me-2"></i> Reportes Consolidados</h2>
            <p class="text-muted mb-0">Genera informes de rendimiento de los estudiantes y descárgalos en formato Excel.</p>
        </div>
    </div>

    <!-- Navegación por pestañas -->
    <ul class="nav nav-tabs border-bottom border-light border-opacity-10 mb-4" id="reportesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="materia-tab" data-bs-toggle="tab" data-bs-target="#materia-pane" type="button" role="tab" aria-controls="materia-pane" aria-selected="true"><i class="fa-solid fa-book-open text-info me-2"></i> Reporte por Materia</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="sabana-tab" data-bs-toggle="tab" data-bs-target="#sabana-pane" type="button" role="tab" aria-controls="sabana-pane" aria-selected="false"><i class="fa-solid fa-table-cells text-warning me-2"></i> Boletín Sabana por Curso</button>
        </li>
    </ul>

    <div class="tab-content" id="reportesTabsContent">
        <!-- Pestaña: Reporte por Materia -->
        <div class="tab-pane fade show active" id="materia-pane" role="tabpanel" tabindex="0">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent py-3 border-bottom border-light border-opacity-10">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-filter text-primary me-2"></i> Filtros del Reporte</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">Ciclo Lectivo</label>
                            <select class="form-select" id="filtroCicloMat"></select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">Curso</label>
                            <select class="form-select" id="filtroCursoMat"></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">Materia</label>
                            <select class="form-select" id="filtroMateriaMat"></select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100 shadow-sm" onclick="generarReporteMateria()"><i class="fa-solid fa-bolt me-2"></i> Generar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 d-none" id="cardResultadosMat">
                <div class="card-header bg-transparent py-3 border-bottom border-light border-opacity-10 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-info"><i class="fa-solid fa-list text-info me-2"></i> Resultados</h5>
                    <button class="btn btn-danger btn-sm" onclick="imprimirPDFMat()"><i class="fa-solid fa-file-pdf me-2"></i>Imprimir PDF</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaReporteMat" class="table table-hover table-bordered align-middle w-100 text-center">
                            <thead>
                                <tr id="headReporteMat"></tr>
                            </thead>
                            <tbody id="bodyReporteMat"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestaña: Reporte Sabana -->
        <div class="tab-pane fade" id="sabana-pane" role="tabpanel" tabindex="0">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent py-3 border-bottom border-light border-opacity-10">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-filter text-primary me-2"></i> Filtros del Boletín Sabana</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">Ciclo Lectivo</label>
                            <select class="form-select" id="filtroCicloSab"></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">Instancia de Calificación</label>
                            <select class="form-select" id="filtroInstanciaSab"></select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">Curso</label>
                            <select class="form-select" id="filtroCursoSab"></select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100 shadow-sm" onclick="generarReporteSabana()"><i class="fa-solid fa-bolt me-2"></i> Generar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 d-none" id="cardResultadosSab">
                <div class="card-header bg-transparent py-3 border-bottom border-light border-opacity-10 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-warning"><i class="fa-solid fa-table text-warning me-2"></i> Resultados</h5>
                    <button class="btn btn-danger btn-sm" onclick="imprimirPDFSab()"><i class="fa-solid fa-file-pdf me-2"></i>Imprimir PDF</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaReporteSab" class="table table-hover table-bordered align-middle w-100 text-center nowrap">
                            <thead>
                                <tr id="headReporteSab"></tr>
                            </thead>
                            <tbody id="bodyReporteSab"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTables Buttons for Export -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<style>
.th-header { background-color: #1e293b !important; color: #f8fafc !important; font-size: 0.8rem; }
.th-alumno { text-align: left !important; min-width: 250px; }
.nota-cell { font-weight: 600; font-size: 0.95rem; }
.dt-buttons .btn { margin-bottom: 15px; border-radius: 20px; padding: 0.375rem 1rem; }
</style>

<script>
let dtMat = null;
let dtSab = null;

$(document).ready(function() {
    cargarCiclos();

    // Eventos cascada Reporte Materia
    $('#filtroCicloMat').change(function() { cargarCursosMat(); });
    $('#filtroCursoMat').change(function() { cargarMateriasMat(); });

    // Eventos cascada Reporte Sabana
    $('#filtroCicloSab').change(function() { 
        cargarCursosSab(); 
        cargarInstanciasSab(); 
    });
});

function cargarCiclos() {
    $.post('controllers/reportes_ajax.php', { action: 'obtener_ciclos' }, function(res) {
        if(res.status === 'success' && res.data.length > 0) {
            let html = '';
            res.data.forEach(c => html += `<option value="${c.id}">${c.nombre}</option>`);
            $('#filtroCicloMat, #filtroCicloSab').html(html);
            
            // Trigger cascada
            cargarCursosMat();
            cargarCursosSab();
            cargarInstanciasSab();
        } else {
            $('#filtroCicloMat, #filtroCicloSab').html('<option value="">Sin ciclos configurados</option>');
        }
    }, 'json');
}

// --- LOGICA REPORTE MATERIA ---
function cargarCursosMat() {
    const ciclo = $('#filtroCicloMat').val();
    if(!ciclo) return;
    $.post('controllers/reportes_ajax.php', { action: 'obtener_cursos', ciclo_id: ciclo }, function(res) {
        let html = '';
        if(res.status === 'success' && res.data.length > 0) {
            res.data.forEach(c => html += `<option value="${c.id}">${c.nombre}</option>`);
            $('#filtroCursoMat').html(html);
            cargarMateriasMat();
        } else {
            $('#filtroCursoMat').html('<option value="">Sin cursos</option>');
            $('#filtroMateriaMat').html('<option value="">-</option>');
        }
    }, 'json');
}

function cargarMateriasMat() {
    const curso = $('#filtroCursoMat').val();
    if(!curso) return;
    $.post('controllers/reportes_ajax.php', { action: 'obtener_materias', curso_id: curso }, function(res) {
        let html = '';
        if(res.status === 'success' && res.data.length > 0) {
            res.data.forEach(m => html += `<option value="${m.id}">${m.asignatura}</option>`);
        } else {
            html = '<option value="">Sin materias asignadas</option>';
        }
        $('#filtroMateriaMat').html(html);
    }, 'json');
}

function generarReporteMateria() {
    const ciclo = $('#filtroCicloMat').val();
    const curso = $('#filtroCursoMat').val();
    const materia = $('#filtroMateriaMat').val();
    
    if(!ciclo || !curso || !materia) {
        Swal.fire('Atención', 'Selecciona todos los filtros para generar el reporte.', 'warning');
        return;
    }

    if (dtMat) { dtMat.destroy(); $('#tablaReporteMat').empty(); }
    $('#tablaReporteMat').html('<thead id="headReporteMat"></thead><tbody id="bodyReporteMat"></tbody>');
    
    $('#cardResultadosMat').removeClass('d-none');
    $('#bodyReporteMat').html('<tr><td class="text-center py-4"><i class="fa-solid fa-spinner fa-spin text-primary fs-3 mb-2"></i><br>Generando reporte...</td></tr>');

    $.post('controllers/reportes_ajax.php', { 
        action: 'generar_reporte_materia', 
        ciclo_id: ciclo, 
        curso_id: curso, 
        materia_id: materia 
    }, function(res) {
        if(res.status === 'success') {
            dibujarTablaMat(res.data);
        } else {
            $('#bodyReporteMat').html(`<tr><td class="text-center text-danger py-4">${res.msg}</td></tr>`);
        }
    }, 'json');
}

function dibujarTablaMat(data) {
    let thead = '<tr><th class="th-header th-alumno">Apellido y Nombre</th>';
    let columnsDef = [{ data: 'alumno', className: 'text-start fw-bold' }];
    
    if (data.instancias.length === 0) {
        thead += '<th class="th-header">Sin instancias</th></tr>';
        columnsDef.push({ data: 'empty', defaultContent: '-' });
    } else {
        data.instancias.forEach(inst => {
            thead += `<th class="th-header">${inst.nombre}</th>`;
            columnsDef.push({ data: `inst_${inst.id}`, defaultContent: '-', className: 'nota-cell' });
        });
        thead += '</tr>';
    }
    
    $('#headReporteMat').html(thead);
    $('#bodyReporteMat').empty(); // DataTables se encarga

    dtMat = $('#tablaReporteMat').DataTable({
        data: data.alumnos,
        columns: columnsDef,
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
        dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-3"Bf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        buttons: [
            { extend: 'excelHtml5', text: '<i class="fa-solid fa-file-excel me-1"></i> Exportar Excel', className: 'btn btn-success btn-sm' }
        ],
        pageLength: 50
    });
}

function imprimirPDFMat() {
    const ciclo = $('#filtroCicloMat').val();
    const curso = $('#filtroCursoMat').val();
    const materia = $('#filtroMateriaMat').val();
    if(ciclo && curso && materia) {
        window.open(`controllers/imprimir_reporte_materia.php?ciclo_id=${ciclo}&curso_id=${curso}&materia_id=${materia}`, '_blank');
    }
}

// --- LOGICA REPORTE SABANA ---
function cargarCursosSab() {
    const ciclo = $('#filtroCicloSab').val();
    if(!ciclo) return;
    $.post('controllers/reportes_ajax.php', { action: 'obtener_cursos', ciclo_id: ciclo }, function(res) {
        let html = '';
        if(res.status === 'success' && res.data.length > 0) {
            res.data.forEach(c => html += `<option value="${c.id}">${c.nombre}</option>`);
        } else {
            html = '<option value="">Sin cursos</option>';
        }
        $('#filtroCursoSab').html(html);
    }, 'json');
}

function cargarInstanciasSab() {
    const ciclo = $('#filtroCicloSab').val();
    if(!ciclo) return;
    $.post('controllers/reportes_ajax.php', { action: 'obtener_instancias', ciclo_id: ciclo }, function(res) {
        let html = '';
        if(res.status === 'success' && res.data.length > 0) {
            res.data.forEach(i => html += `<option value="${i.id}">${i.nombre}</option>`);
        } else {
            html = '<option value="">Sin instancias configuradas</option>';
        }
        $('#filtroInstanciaSab').html(html);
    }, 'json');
}

function generarReporteSabana() {
    const ciclo = $('#filtroCicloSab').val();
    const curso = $('#filtroCursoSab').val();
    const instancia = $('#filtroInstanciaSab').val();
    
    if(!ciclo || !curso || !instancia) {
        Swal.fire('Atención', 'Selecciona todos los filtros para generar el reporte.', 'warning');
        return;
    }

    if (dtSab) { dtSab.destroy(); $('#tablaReporteSab').empty(); }
    $('#tablaReporteSab').html('<thead id="headReporteSab"></thead><tbody id="bodyReporteSab"></tbody>');
    
    $('#cardResultadosSab').removeClass('d-none');
    $('#bodyReporteSab').html('<tr><td class="text-center py-4"><i class="fa-solid fa-spinner fa-spin text-warning fs-3 mb-2"></i><br>Consolidando notas...</td></tr>');

    $.post('controllers/reportes_ajax.php', { 
        action: 'generar_reporte_sabana', 
        ciclo_id: ciclo, 
        curso_id: curso, 
        instancia_id: instancia 
    }, function(res) {
        if(res.status === 'success') {
            dibujarTablaSab(res.data);
        } else {
            $('#bodyReporteSab').html(`<tr><td class="text-center text-danger py-4">${res.msg}</td></tr>`);
        }
    }, 'json');
}

function dibujarTablaSab(data) {
    let thead = '<tr><th class="th-header th-alumno">Apellido y Nombre</th>';
    let columnsDef = [{ data: 'alumno', className: 'text-start fw-bold' }];
    
    if (data.materias.length === 0) {
        thead += '<th class="th-header">Sin materias asignadas</th></tr>';
        columnsDef.push({ data: 'empty', defaultContent: '-' });
    } else {
        data.materias.forEach(mat => {
            // Acortar nombre de materia si es muy largo
            let abr = mat.asignatura.length > 15 ? mat.asignatura.substring(0, 15) + '...' : mat.asignatura;
            thead += `<th class="th-header" title="${mat.asignatura}">${abr}</th>`;
            columnsDef.push({ data: `mat_${mat.id}`, defaultContent: '-', className: 'nota-cell' });
        });
        thead += '</tr>';
    }
    
    $('#headReporteSab').html(thead);
    $('#bodyReporteSab').empty(); 

    dtSab = $('#tablaReporteSab').DataTable({
        data: data.alumnos,
        columns: columnsDef,
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
        dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-3"Bf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        buttons: [
            { extend: 'excelHtml5', text: '<i class="fa-solid fa-file-excel me-1"></i> Exportar Excel', className: 'btn btn-success btn-sm' }
        ],
        pageLength: 50,
        scrollX: true // Importante para sábana porque pueden ser 15 materias
    });
}

function imprimirPDFSab() {
    const ciclo = $('#filtroCicloSab').val();
    const curso = $('#filtroCursoSab').val();
    const instancia = $('#filtroInstanciaSab').val();
    if(ciclo && curso && instancia) {
        window.open(`controllers/imprimir_reporte_sabana.php?ciclo_id=${ciclo}&curso_id=${curso}&instancia_id=${instancia}`, '_blank');
    }
}
</script>
