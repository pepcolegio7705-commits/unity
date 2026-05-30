<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 text-white fw-bold mb-0"><i class="bi bi-file-earmark-text text-warning"></i> Certificados Analíticos</h2>
        <button id="btnImprimirAnalitico" class="btn btn-danger" style="display:none;" onclick="imprimirAnalitico()">
            <i class="bi bi-file-earmark-pdf"></i> Imprimir Fórm. 4
        </button>
    </div>

    <!-- Buscador de Alumno -->
    <div class="card bg-dark border-secondary shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-5">
                    <label class="form-label text-muted small fw-semibold">Buscar Alumno (DNI)</label>
                    <div class="input-group">
                        <input type="text" id="inputBuscarDni" class="form-control bg-dark text-white border-secondary" placeholder="Ej: 12345678">
                        <button class="btn btn-primary" type="button" id="btnBuscarDni">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </div>
                <div class="col-md-7 d-flex align-items-end pb-1">
                    <div id="infoAlumnoEncontrado" class="text-success fw-bold fs-5" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="panelAnalitico" style="display:none;">
        
        <!-- Cabecera del Analítico -->
        <div class="card bg-dark border-secondary shadow-sm mb-4">
            <div class="card-header bg-secondary bg-opacity-25 border-secondary text-white fw-bold">
                Datos de Cabecera del Analítico
            </div>
            <div class="card-body p-4">
                <form id="formCabecera">
                    <input type="hidden" name="action" value="guardar_cabecera">
                    <input type="hidden" name="alumno_id" id="cabecera_alumno_id">
                    <input type="hidden" name="csrf_token" value="<?php echo generar_token_csrf(); ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">Archivo Nº</label>
                            <input type="text" name="archivo_no" id="cab_archivo_no" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Escuela de procedencia</label>
                            <input type="text" name="escuela_procedencia" id="cab_escuela_procedencia" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-semibold">Fecha de Emisión</label>
                            <input type="date" name="fecha_emision" id="cab_fecha_emision" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small fw-semibold">Observaciones Generales</label>
                            <input type="text" name="observaciones_generales" id="cab_observaciones_generales" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save"></i> Guardar Cabecera</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Historial por Año -->
        <div class="card bg-dark border-secondary shadow-sm mb-4">
            <div class="card-header bg-secondary bg-opacity-25 border-secondary text-white fw-bold">
                Historial Académico
            </div>
            <div class="card-body p-3">
                
                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                    <?php for($i=1; $i<=6; $i++): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $i==1?'active':'' ?>" id="pills-anio-<?= $i ?>-tab" data-bs-toggle="pill" data-bs-target="#pills-anio-<?= $i ?>" type="button" role="tab" aria-controls="pills-anio-<?= $i ?>" aria-selected="<?= $i==1?'true':'false' ?>">
                            <?= $i ?>º Año
                        </button>
                    </li>
                    <?php endfor; ?>
                </ul>
                
                <div class="tab-content" id="pills-tabContent">
                    <?php for($i=1; $i<=6; $i++): ?>
                    <div class="tab-pane fade <?= $i==1?'show active':'' ?>" id="pills-anio-<?= $i ?>" role="tabpanel" aria-labelledby="pills-anio-<?= $i ?>-tab">
                        
                        <!-- Formulario para agregar/editar materia -->
                        <form class="formGuardarNota row g-2 mb-3 align-items-end" data-anio="<?= $i ?>">
                            <input type="hidden" name="action" value="guardar_nota">
                            <input type="hidden" name="alumno_id" class="nota_alumno_id">
                            <input type="hidden" name="anio_estudio" value="<?= $i ?>">
                            <input type="hidden" name="nota_id_hash" class="nota_id_hash">
                            <input type="hidden" name="csrf_token" value="<?php echo generar_token_csrf(); ?>">
                            
                            <div class="col-md-3">
                                <label class="small text-muted mb-1">Asignatura *</label>
                                <div class="asignatura-container">
                                    <select class="form-select form-select-sm bg-dark text-white border-secondary select-asignatura" data-anio="<?= $i ?>">
                                        <option value="">Cargando materias...</option>
                                    </select>
                                    <input type="text" name="asignatura" class="form-control form-control-sm bg-dark text-white border-secondary input-asignatura mt-1 d-none" placeholder="Escribir materia..." disabled>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <label class="small text-muted mb-1">C. Num.</label>
                                <input type="text" name="calificacion_num" class="form-control form-control-sm bg-dark text-white border-secondary">
                            </div>
                            <div class="col-md-2">
                                <label class="small text-muted mb-1">C. Letras</label>
                                <input type="text" name="calificacion_letras" class="form-control form-control-sm bg-dark text-white border-secondary">
                            </div>
                            <div class="col-md-2">
                                <label class="small text-muted mb-1">Condición/Estab.</label>
                                <input type="text" name="condicion_establecimiento" class="form-control form-control-sm bg-dark text-white border-secondary">
                            </div>
                            <div class="col-md-1">
                                <label class="small text-muted mb-1">Acta/Fecha</label>
                                <input type="text" name="fecha" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Mes/Año">
                            </div>
                            <div class="col-md-2 d-flex gap-1">
                                <div class="w-50">
                                    <label class="small text-muted mb-1">Repite N.</label>
                                    <input type="text" name="repite_nota" class="form-control form-control-sm bg-dark text-white border-secondary">
                                </div>
                                <div class="w-50">
                                    <label class="small text-muted mb-1">Def.</label>
                                    <input type="text" name="calificacion_definitiva" class="form-control form-control-sm bg-dark text-white border-secondary">
                                </div>
                            </div>
                            <div class="col-md-1 text-end">
                                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-plus-lg"></i> Agregar</button>
                            </div>
                        </form>
                        
                        <!-- Tabla de materias del año -->
                        <div class="table-responsive">
                            <table class="table table-dark table-hover table-sm border-secondary table-notas" data-anio="<?= $i ?>">
                                <thead class="table-secondary text-dark">
                                    <tr>
                                        <th>Asignatura</th>
                                        <th>Calif.</th>
                                        <th>Letras</th>
                                        <th>Establecimiento</th>
                                        <th>Fecha</th>
                                        <th>Repite</th>
                                        <th>Def.</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dynamic content -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Formulario de Observaciones de ese Año -->
                        <form class="formObservacionAnio d-flex gap-2 mt-3" data-anio="<?= $i ?>">
                            <input type="hidden" name="action" value="guardar_observacion">
                            <input type="hidden" name="alumno_id" class="obs_alumno_id">
                            <input type="hidden" name="anio_estudio" value="<?= $i ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generar_token_csrf(); ?>">
                            
                            <span class="text-muted small fw-bold mt-2">Observaciones del Año:</span>
                            <input type="text" name="observacion" class="form-control form-control-sm bg-dark text-white border-secondary input-observacion" placeholder="Ej: Curso completo">
                            <button type="submit" class="btn btn-sm btn-outline-success">Guardar Obs.</button>
                        </form>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Helper para escapar HTML
function escapeHTML(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[&<>'"]/g, function(tag) {
        const charsToReplace = { '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' };
        return charsToReplace[tag] || tag;
    });
}

let currentAlumnoIdHash = '';
let csrfTokenGlobal = '<?php echo generar_token_csrf(); ?>';

$(document).ready(function() {
    // Buscar alumno por DNI
    $('#btnBuscarDni').on('click', function(e) {
        e.preventDefault();
        let dni = $('#inputBuscarDni').val().trim();
        if(dni === '') {
            Swal.fire('Atención', 'Ingrese un DNI para buscar.', 'warning');
            return;
        }
        
        let btn = $(this);
        btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Buscando...');
        
        $.post('controllers/analiticos_ajax.php', { action: 'buscar_por_dni', dni: dni }, function(res) {
            btn.prop('disabled', false).html('<i class="bi bi-search"></i> Buscar');
            
            if(res.status === 'success') {
                let a = res.data;
                currentAlumnoIdHash = a.id_hash;
                $('#cabecera_alumno_id').val(a.id_hash);
                $('.nota_alumno_id').val(a.id_hash);
                $('.obs_alumno_id').val(a.id_hash);
                
                $('#infoAlumnoEncontrado').html(`<i class="bi bi-person-check-fill"></i> ${escapeHTML(a.alumno)} | Legajo: ${escapeHTML(a.legajo) || 'Sin asignar'}`).fadeIn();
                window.tempLegajo = a.legajo; // guardamos en una var global para la cabecera
                window.tempEscp = a.escp; // guardamos escuela de procedencia
                
                cargarCabecera();
                cargarObservaciones();
                cargarNotas();
                
                $('#panelAnalitico').fadeIn();
                $('#btnImprimirAnalitico').fadeIn();
            } else {
                $('#panelAnalitico').hide();
                $('#btnImprimirAnalitico').hide();
                $('#infoAlumnoEncontrado').hide();
                currentAlumnoIdHash = '';
                Swal.fire('No encontrado', res.msg, 'info');
            }
        }, 'json');
    });

    // Buscar con Enter
    $('#inputBuscarDni').on('keypress', function(e) {
        if(e.which == 13) {
            $('#btnBuscarDni').click();
        }
    });

    // Cargar listas de materias
    cargarSelectMaterias();

    // Manejar cambio en select de asignatura
    $('.select-asignatura').on('change', function() {
        let val = $(this).val();
        let inputManual = $(this).siblings('.input-asignatura');
        
        if (val === 'otra') {
            // Mostrar input manual
            inputManual.removeClass('d-none').prop('disabled', false).prop('required', true);
            $(this).removeAttr('name'); // Que no se envie el select
        } else {
            // Ocultar input manual y usar select
            inputManual.addClass('d-none').prop('disabled', true).prop('required', false).val('');
            $(this).attr('name', 'asignatura'); // El select envia el valor
        }
    });

    // Guardar Cabecera
    $('#formCabecera').on('submit', function(e) {
        e.preventDefault();
        $.post('controllers/analiticos_ajax.php', $(this).serialize(), function(res) {
            if(res.status === 'success') {
                Swal.fire({icon: 'success', title: 'Guardado', text: res.msg, timer: 1000, showConfirmButton: false});
            } else {
                Swal.fire('Error', res.msg, 'error');
            }
        }, 'json');
    });

    // Guardar Nota (Asignatura)
    $('.formGuardarNota').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        // Validar que el campo no este vacio si es manual
        let asig = form.find('select.select-asignatura').val() === 'otra' ? form.find('.input-asignatura').val() : form.find('select.select-asignatura').val();
        if(!asig) {
            Swal.fire('Atención', 'Debes elegir o escribir una asignatura.', 'warning');
            return;
        }

        $.post('controllers/analiticos_ajax.php', form.serialize(), function(res) {
            if(res.status === 'success') {
                cargarNotas();
                form[0].reset();
                form.find('.nota_id_hash').val(''); // Limpiar ID si era edicion
                form.find('.nota_alumno_id').val(currentAlumnoIdHash);
                form.find('.btn-primary').html('<i class="bi bi-plus-lg"></i> Agregar');
                // Restaurar select
                form.find('.select-asignatura').val('').change();
            } else {
                Swal.fire('Error', res.msg, 'error');
            }
        }, 'json');
    });

    // Guardar Observación
    $('.formObservacionAnio').on('submit', function(e) {
        e.preventDefault();
        $.post('controllers/analiticos_ajax.php', $(this).serialize(), function(res) {
            if(res.status === 'success') {
                Swal.fire({icon: 'success', title: 'Guardado', text: res.msg, timer: 1000, showConfirmButton: false});
            } else {
                Swal.fire('Error', res.msg, 'error');
            }
        }, 'json');
    });
});

let materiasHistorico = [];

function cargarSelectMaterias() {
    for(let anio = 1; anio <= 6; anio++) {
        $.post('controllers/analiticos_ajax.php', { action: 'obtener_materias', anio: anio }, function(res) {
            if(res.status === 'success') {
                let select = $(`.select-asignatura[data-anio="${anio}"]`);
                select.empty();
                select.append('<option value="">Seleccionar Materia...</option>');
                
                let agrupado = res.data;
                for (let orientacion in agrupado) {
                    if (orientacion === 'COMUN') {
                        agrupado[orientacion].forEach(m => {
                            select.append(`<option value="${m}">${m}</option>`);
                        });
                    } else {
                        let optgroup = $(`<optgroup label="--- Orientación en ${orientacion} ---"></optgroup>`);
                        agrupado[orientacion].forEach(m => {
                            optgroup.append(`<option value="${m}">${m}</option>`);
                        });
                        select.append(optgroup);
                    }
                }
                select.append('<option value="otra" class="text-warning fw-bold">+ Otra materia (escribir manual)</option>');
                select.change(); // inicializar estado de input manual
            }
        }, 'json');
    }
}

function cargarCabecera() {
    $.post('controllers/analiticos_ajax.php', { action: 'obtener_cabecera', alumno_id: currentAlumnoIdHash }, function(res) {
        if(res.status === 'success') {
            let d = res.data;
            if(d) {
                $('#cab_archivo_no').val(d.archivo_no);
                $('#cab_escuela_procedencia').val(d.escuela_procedencia);
                $('#cab_fecha_emision').val(d.fecha_emision);
                $('#cab_observaciones_generales').val(d.observaciones_generales);
            } else {
                $('#formCabecera')[0].reset();
                $('#cabecera_alumno_id').val(currentAlumnoIdHash); // Restaurar ID oculto
                if(window.tempLegajo) {
                    $('#cab_archivo_no').val(window.tempLegajo); // Rellenar auto el archivo_no con legajo
                }
                if(window.tempEscp) {
                    $('#cab_escuela_procedencia').val(window.tempEscp); // Rellenar auto la escuela
                }
            }
        }
    }, 'json');
}

function cargarObservaciones() {
    $('.input-observacion').val(''); // limpiar
    $.post('controllers/analiticos_ajax.php', { action: 'obtener_observaciones', alumno_id: currentAlumnoIdHash }, function(res) {
        if(res.status === 'success') {
            let obs = res.data;
            for (let anio in obs) {
                $(`.formObservacionAnio[data-anio="${anio}"]`).find('.input-observacion').val(obs[anio]);
            }
        }
    }, 'json');
}

function cargarNotas() {
    $.post('controllers/analiticos_ajax.php', { action: 'listar_notas', alumno_id: currentAlumnoIdHash }, function(res) {
        if(res.status === 'success') {
            materiasHistorico = res.data; // Guardar en RAM para edición rápida
            
            // Limpiar tbody
            $('.table-notas tbody').empty();

            materiasHistorico.forEach(m => {
                let tbody = $(`.table-notas[data-anio="${m.anio_estudio}"] tbody`);
                let tr = `
                    <tr>
                        <td class="fw-bold">${m.asignatura}</td>
                        <td>${m.calificacion_num || '-'}</td>
                        <td class="small">${m.calificacion_letras || '-'}</td>
                        <td class="small">${m.condicion_establecimiento || '-'}</td>
                        <td class="small">${m.fecha || '-'} / ${m.acta_num || '-'}</td>
                        <td class="small">${m.repite_nota || '-'} (${m.repite_fecha || '-'})</td>
                        <td class="fw-bold">${m.calificacion_definitiva || '-'}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-warning py-0 px-2" onclick="editarNota('${m.id_hash}')"><i class="bi bi-pencil"></i> Editar</button>
                            <button class="btn btn-sm btn-outline-danger py-0 px-2" onclick="eliminarNota('${m.id_hash}')"><i class="bi bi-trash"></i> Eliminar</button>
                        </td>
                    </tr>
                `;
                tbody.append(tr);
            });
        }
    }, 'json');
}

function editarNota(id_hash) {
    let nota = materiasHistorico.find(m => m.id_hash === id_hash);
    if(!nota) return;
    
    let form = $(`.formGuardarNota[data-anio="${nota.anio_estudio}"]`);
    form.find('.nota_id_hash').val(nota.id_hash);
    
    // Configurar select vs manual
    let select = form.find('.select-asignatura');
    let inputManual = form.find('.input-asignatura');
    
    // Ver si la nota.asignatura está en el select
    if (select.find(`option[value="${nota.asignatura}"]`).length > 0) {
        select.val(nota.asignatura).change();
    } else {
        select.val('otra').change();
        inputManual.val(nota.asignatura);
    }
    
    form.find('input[name="calificacion_num"]').val(nota.calificacion_num);
    form.find('input[name="calificacion_letras"]').val(nota.calificacion_letras);
    form.find('input[name="condicion_establecimiento"]').val(nota.condicion_establecimiento);
    form.find('input[name="acta_num"]').val(nota.acta_num); // Comparten campo en el PDF, o adaptarlo si se desdobló
    form.find('input[name="fecha"]').val(nota.fecha);
    form.find('input[name="repite_nota"]').val(nota.repite_nota);
    form.find('input[name="repite_fecha"]').val(nota.repite_fecha);
    form.find('input[name="calificacion_definitiva"]').val(nota.calificacion_definitiva);
    
    form.find('.btn-primary').html('<i class="bi bi-check-lg"></i> Guardar');
}

function eliminarNota(id_hash) {
    if(confirm('¿Seguro que deseas eliminar esta asignatura del analítico?')) {
        $.post('controllers/analiticos_ajax.php', { action: 'eliminar_nota', id: id_hash, csrf_token: csrfTokenGlobal }, function(res) {
            if(res.status === 'success') {
                cargarNotas();
            } else {
                Swal.fire('Error', res.msg, 'error');
            }
        }, 'json');
    }
}

function imprimirAnalitico() {
    if(currentAlumnoIdHash) {
        window.open('backend/reporte_analitico_pdf.php?alumno_id=' + encodeURIComponent(currentAlumnoIdHash), '_blank');
    }
}
</script>
