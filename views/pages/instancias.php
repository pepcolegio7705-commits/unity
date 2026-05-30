<div class="container-fluid px-md-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h2 class="page-title mb-1"><i class="fa-solid fa-calendar-check text-primary me-2"></i> Instancias de Calificación</h2>
            <p class="text-muted mb-0">Habilita o deshabilita la carga de notas para los docentes por cada periodo de evaluación.</p>
        </div>
        <div class="mt-3 mt-md-0">
            <button class="btn btn-primary shadow-sm" onclick="abrirModalInstancia()"><i class="fa-solid fa-plus me-2"></i> Nueva Instancia</button>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center border-bottom border-light border-opacity-10 py-3">
            <h5 class="mb-0 fw-bold"><i class="fa-solid fa-list-check me-2 text-warning"></i> Periodos del Ciclo Actual (<span id="txtCicloActual">...</span>)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaInstancias" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th width="30%">Nombre de Instancia</th>
                            <th width="15%">Tipo</th>
                            <th width="15%">Escala</th>
                            <th width="15%">Estado de Carga</th>
                            <th width="15%">Notas / Observaciones</th>
                            <th width="10%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Load via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Instancia -->
<div class="modal fade" id="modalInstancia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom border-light border-opacity-10">
                <h5 class="modal-title fw-bold" id="modalInstanciaTitle"><i class="fa-solid fa-calendar-plus text-primary me-2"></i> Nueva Instancia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formInstancia">
                <div class="modal-body">
                    <input type="hidden" name="action" id="actionInstancia" value="guardar">
                    <input type="hidden" name="id" id="instanciaId">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Nombre del Periodo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nombre" id="instanciaNombre" placeholder="Ej: Primer Trimestre" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Tipo</label>
                        <select class="form-select" name="tipo" id="instanciaTipo">
                            <option value="Trimestre">Trimestre</option>
                            <option value="Cuatrimestre">Cuatrimestre</option>
                            <option value="Semestre">Semestre</option>
                            <option value="Anual">Anual</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Escala de Notas</label>
                        <select class="form-select" name="escala_notas" id="instanciaEscala">
                            <option value="Numerica">Numérica (1 al 10)</option>
                            <option value="Cualitativa">Cualitativa (S, MB, B, R)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Notas u Observaciones (Opcional)</label>
                        <textarea class="form-control" name="notas" id="instanciaNotas" rows="2" placeholder="Ej: Cierre de notas el 30 de Mayo"></textarea>
                    </div>
                    
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" role="switch" id="instanciaActiva" name="activa" value="1" checked>
                        <label class="form-check-label text-muted" for="instanciaActiva">
                            <strong class="text-white">Habilitar carga de notas inmediatamente</strong>
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-top border-light border-opacity-10">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-save me-2"></i> Guardar Instancia</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let dtInstancias;

$(document).ready(function() {
    // Inicializar DataTable
    dtInstancias = $('#tablaInstancias').DataTable({
        "ajax": {
            "url": "controllers/instancias_ajax.php",
            "type": "POST",
            "data": function(d) {
                d.action = 'listar';
            },
            "dataSrc": function (json) {
                if(json.status === 'success') {
                    $('#txtCicloActual').text(json.ciclo_nombre);
                    return json.data;
                } else {
                    Swal.fire('Error', json.msg || 'Error al cargar datos', 'error');
                    return [];
                }
            }
        },
        "columns": [
            { "data": "nombre", "render": function(data) { return `<span class="fw-bold">${data}</span>`; } },
            { "data": "tipo", "render": function(data) { return `<span class="badge bg-secondary">${data}</span>`; } },
            { "data": "escala_notas", "render": function(data) { return `<span class="badge ${data === 'Numerica' ? 'bg-primary' : 'bg-info'}">${data || 'Numerica'}</span>`; } },
            { "data": "activa", "render": function(data, type, row) {
                let isChecked = data == 1 ? 'checked' : '';
                return `
                    <div class="form-check form-switch">
                        <input class="form-check-input switch-estado" type="checkbox" role="switch" 
                            data-id="${row.id_hash}" ${isChecked} onchange="toggleEstado(this)">
                        <label class="form-check-label small ms-1 ${data == 1 ? 'text-success' : 'text-danger'}">
                            ${data == 1 ? 'Habilitada' : 'Bloqueada'}
                        </label>
                    </div>
                `;
            }},
            { "data": "notas", "render": function(data) { return `<small class="text-muted">${data || '-'}</small>`; } },
            { "data": "id_hash", "className": "text-center", "orderable": false, "render": function(data, type, row) {
                return `
                    <button class="btn btn-sm btn-outline-info rounded-circle me-1" title="Editar" 
                        onclick='editarInstancia(${JSON.stringify(row)})'><i class="fa-solid fa-pen"></i></button>
                    <button class="btn btn-sm btn-outline-danger rounded-circle" title="Eliminar" 
                        onclick="eliminarInstancia('${data}')"><i class="fa-solid fa-trash"></i></button>
                `;
            }}
        ],
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
        "ordering": false,
        "pageLength": 25,
        "dom": '<"d-flex justify-content-between align-items-center mb-3"f>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
    });

    // Guardar formulario
    $('#formInstancia').submit(function(e) {
        e.preventDefault();
        $.post('controllers/instancias_ajax.php', $(this).serialize(), function(res) {
            if (res.status === 'success') {
                $('#modalInstancia').modal('hide');
                dtInstancias.ajax.reload(null, false);
                Swal.fire({icon: 'success', title: 'Éxito', text: res.msg, timer: 1500, showConfirmButton: false});
            } else {
                Swal.fire('Error', res.msg, 'error');
            }
        }, 'json');
    });
});

function abrirModalInstancia() {
    $('#formInstancia')[0].reset();
    $('#actionInstancia').val('guardar');
    $('#instanciaId').val('');
    $('#modalInstanciaTitle').html('<i class="fa-solid fa-calendar-plus text-primary me-2"></i> Nueva Instancia');
    $('#instanciaActiva').prop('checked', true);
    $('#modalInstancia').modal('show');
}

function editarInstancia(data) {
    $('#formInstancia')[0].reset();
    $('#actionInstancia').val('editar');
    $('#instanciaId').val(data.id_hash);
    $('#instanciaNombre').val(data.nombre);
    $('#instanciaTipo').val(data.tipo);
    $('#instanciaEscala').val(data.escala_notas || 'Numerica');
    $('#instanciaNotas').val(data.notas);
    $('#instanciaActiva').prop('checked', data.activa == 1);
    
    $('#modalInstanciaTitle').html('<i class="fa-solid fa-pen text-info me-2"></i> Editar Instancia');
    $('#modalInstancia').modal('show');
}

function eliminarInstancia(id) {
    Swal.fire({
        title: '¿Eliminar Instancia?',
        text: 'Se eliminarán también las calificaciones asociadas a este periodo de forma irreversible.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#3b82f6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('controllers/instancias_ajax.php', { action: 'eliminar', id: id }, function(res) {
                if(res.status === 'success') {
                    dtInstancias.ajax.reload(null, false);
                    Swal.fire({icon: 'success', title: 'Eliminado', text: res.msg, timer: 1500, showConfirmButton: false});
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }, 'json');
        }
    });
}

function toggleEstado(checkbox) {
    const id = $(checkbox).data('id');
    const estado = $(checkbox).is(':checked') ? 1 : 0;
    
    $.post('controllers/instancias_ajax.php', { action: 'toggle_estado', id: id, activa: estado }, function(res) {
        if(res.status === 'success') {
            dtInstancias.ajax.reload(null, false); // Reload to update label colors
            // Optional mini toast
        } else {
            $(checkbox).prop('checked', !estado); // Revert UI
            Swal.fire('Error', res.msg, 'error');
        }
    }, 'json');
}
</script>
