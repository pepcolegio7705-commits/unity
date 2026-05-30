<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title mb-0">Gestión de Materias</h1>
    <?php if(isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['Administrador', 'Secretario', 'Directivo'])): ?>
        <button class="btn btn-primary shadow-sm rounded-3 px-4" data-bs-toggle="modal" data-bs-target="#modalMateria" onclick="resetFormMateria()">
            <i class="fa-solid fa-plus me-2"></i> Nueva Materia
        </button>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="tablaMaterias" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Asignatura</th>
                        <th>Año de Estudio</th>
                        <th>Orientación</th>
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

<!-- Modal Agregar/Editar Materia -->
<div class="modal fade" id="modalMateria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formMateria" class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold" id="modalMateriaTitle">Agregar Nueva Materia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4">
                <div class="row g-3 mt-1">
                    <input type="hidden" name="id" id="materiaId">
                    <input type="hidden" name="action" id="materiaAction" value="guardar">
                    
                    <div class="col-12">
                        <label class="form-label text-muted small fw-semibold">Nombre de la Asignatura *</label>
                        <input type="text" name="nombre" id="materiaNombre" class="form-control bg-light border-0" required placeholder="Ej. Matemáticas">
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label text-muted small fw-semibold">Año de Estudio *</label>
                        <select name="anio_estudio" id="materiaAnio" class="form-select bg-light border-0" required>
                            <option value="">Seleccione un año</option>
                            <option value="1">1° Año</option>
                            <option value="2">2° Año</option>
                            <option value="3">3° Año</option>
                            <option value="4">4° Año</option>
                            <option value="5">5° Año</option>
                            <option value="6">6° Año</option>
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label text-muted small fw-semibold">Orientación</label>
                        <select name="orientacion" id="materiaOrientacion" class="form-select bg-light border-0">
                            <!-- Llenado por AJAX -->
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 px-4 pb-4">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary rounded-3 px-4">Guardar Materia</button>
            </div>
        </form>
    </div>
</div>

<script>
let tablaMaterias;
$(document).ready(function() {
    // Cargar orientaciones
    $.post('controllers/materias_ajax.php', { action: 'listar_orientaciones' }, function(res) {
        let options = '';
        res.forEach(function(o) {
            options += `<option value="${o.id}">${o.nombre}</option>`;
        });
        $('#materiaOrientacion').html(options);
    }, 'json');

    tablaMaterias = $('#tablaMaterias').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'controllers/materias_ajax.php',
            type: 'POST',
            data: { action: 'listar' }
        },
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        columns: [
            { data: 'id_hash', visible: false },
            { data: 'nombre', className: 'fw-bold' },
            { data: 'anio_estudio', render: function(data) { return data + '° Año'; } },
            { data: 'orientacion', render: function(data) { return data || '<span class="text-muted small">COMÚN</span>'; } },
            { 
                data: 'activo', 
                render: function(data) { 
                    return data == 1 
                        ? '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3">Activa</span>' 
                        : '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3">Obsoleta</span>'; 
                } 
            },
            { 
                data: null, 
                className: 'text-end',
                orderable: false,
                render: function(data, type, row) {
                    let btnActivar = '';
                    if (row.activo == 1) {
                        btnActivar = `<button class="btn btn-sm btn-light border" title="Desactivar" onclick="eliminarMateria('${row.id_hash}')"><i class="fa-solid fa-trash text-danger"></i></button>`;
                    } else {
                        btnActivar = `<button class="btn btn-sm btn-light border" title="Restaurar" onclick="activarMateria('${row.id_hash}')"><i class="fa-solid fa-rotate-left text-success"></i></button>`;
                    }
                    return `
                        <div class="btn-group shadow-sm">
                            <button class="btn btn-sm btn-light border" title="Editar" onclick='editarMateria(${JSON.stringify(row)})'><i class="fa-solid fa-pen text-warning"></i></button>
                            ${btnActivar}
                        </div>
                    `;
                }
            }
        ]
    });

    $('#formMateria').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'controllers/materias_ajax.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    $('#modalMateria').modal('hide');
                    tablaMaterias.ajax.reload();
                    Swal.fire({icon: 'success', title: '¡Éxito!', text: res.msg, showConfirmButton: false, timer: 1500});
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }
        });
    });
});

function resetFormMateria() {
    $('#formMateria')[0].reset();
    $('#materiaId').val('');
    $('#materiaAction').val('guardar');
    $('#modalMateriaTitle').text('Agregar Nueva Materia');
}

function editarMateria(row) {
    resetFormMateria();
    $('#materiaId').val(row.id_hash);
    $('#materiaNombre').val(row.nombre);
    $('#materiaAnio').val(row.anio_estudio);
    $('#materiaOrientacion').val(row.orientacion_id);
    $('#materiaAction').val('editar');
    $('#modalMateriaTitle').text('Editar Espacio Curricular');
    $('#modalMateria').modal('show');
}

function eliminarMateria(id_hash) {
    Swal.fire({
        title: '¿Desactivar Espacio Curricular?',
        text: "La materia pasará a estado obsoleto pero seguirá existiendo para el historial.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('controllers/materias_ajax.php', { action: 'eliminar', id: id_hash }, function(res) {
                if(res.status === 'success') {
                    tablaMaterias.ajax.reload();
                    Swal.fire({icon: 'success', title: 'Desactivada', text: res.msg, showConfirmButton: false, timer: 1500});
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }, 'json');
        }
    });
}

function activarMateria(id_hash) {
    $.post('controllers/materias_ajax.php', { action: 'activar', id: id_hash }, function(res) {
        if(res.status === 'success') {
            tablaMaterias.ajax.reload();
            Swal.fire({icon: 'success', title: 'Restaurada', text: res.msg, showConfirmButton: false, timer: 1500});
        } else {
            Swal.fire('Error', res.msg, 'error');
        }
    }, 'json');
}
</script>
