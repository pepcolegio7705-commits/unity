<style>
    .materia-card {
        border: none;
        border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
        background: #1e293b;
        color: #f8fafc;
        overflow: hidden;
    }
    .materia-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.3);
    }
    .materia-header {
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        padding: 1.5rem;
        color: white;
    }
    .materia-body {
        padding: 1.5rem;
    }
    .btn-aula {
        background: #eab308;
        color: #000;
        font-weight: 600;
        border-radius: 50px;
        padding: 0.5rem 1.5rem;
        border: none;
    }
    .btn-aula:hover {
        background: #ca8a04;
    }
</style>

<div class="container-fluid px-md-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h2 class="page-title mb-1"><i class="fa-solid fa-book-bookmark text-primary me-2"></i> Mis Materias</h2>
            <p class="text-muted mb-0">Cursos y asignaturas habilitadas para el ciclo lectivo <strong id="cicloActual" class="text-warning">...</strong></p>
        </div>
        <div class="mt-3 mt-md-0">
            <span class="badge bg-secondary p-2 fs-6"><i class="fa-solid fa-chalkboard-user me-2"></i> <span id="nombreDocente">...</span></span>
        </div>
    </div>

    <div id="loadingMaterias" class="text-center py-5 text-muted">
        <i class="fa-solid fa-spinner fa-spin fa-2x mb-3"></i>
        <p>Cargando tus asignaciones...</p>
    </div>

    <div id="materiasContainer" class="row g-4" style="display: none;">
        <!-- Materias dynamically loaded here -->
    </div>
</div>

<script>
$(document).ready(function() {
    cargarMisMaterias();

    function cargarMisMaterias() {
        $.post('controllers/mis_materias_ajax.php', { action: 'listar' }, function(res) {
            $('#loadingMaterias').hide();
            $('#materiasContainer').show().empty();
            
            if (res.status === 'success') {
                $('#cicloActual').text(res.ciclo);
                $('#nombreDocente').text(res.docente);

                if (res.data.length === 0) {
                    $('#materiasContainer').html(`
                        <div class="col-12 text-center py-5">
                            <div class="text-muted mb-3"><i class="fa-solid fa-folder-open fa-3x"></i></div>
                            <h5>No tienes materias asignadas</h5>
                            <p class="text-muted">No se han encontrado asignaciones para ti en el ciclo lectivo actual.</p>
                        </div>
                    `);
                    return;
                }

                res.data.forEach(m => {
                    // Por ahora redirigimos al aula docente (que construiremos pronto) pasando los IDs
                    const aulaUrl = `?page=aula_docente&curso_id=${m.curso_id}&materia_id=${m.materia_id}`;
                    
                    let card = `
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="materia-card shadow-sm h-100 d-flex flex-column">
                            <div class="materia-header">
                                <h4 class="mb-1 fw-bold text-truncate" title="${m.materia_nombre}">${m.materia_nombre}</h4>
                                <span class="badge bg-white text-primary bg-opacity-25 rounded-pill border border-light border-opacity-50">
                                    <i class="fa-solid fa-graduation-cap me-1"></i> ${m.orientacion}
                                </span>
                            </div>
                            <div class="materia-body flex-grow-1 d-flex flex-column justify-content-between">
                                <div class="mb-4">
                                    <h5 class="text-warning mb-1"><i class="fa-solid fa-school me-2"></i> ${m.curso_nombre}</h5>
                                    <small class="text-muted">Turno: ${m.turno}</small>
                                </div>
                                <div class="text-end mt-auto">
                                    <a href="${aulaUrl}" class="btn btn-aula w-100 shadow-sm">
                                        <i class="fa-solid fa-door-open me-2"></i> Ingresar a Aula
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;
                    $('#materiasContainer').append(card);
                });
            } else {
                Swal.fire('Error', res.msg, 'error');
            }
        }, 'json').fail(function() {
            $('#loadingMaterias').hide();
            Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
        });
    }
});
</script>
