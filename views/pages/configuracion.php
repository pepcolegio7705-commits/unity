<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Configuración Institucional</h1>
</div>

<ul class="nav nav-tabs mt-3" id="configTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="datos-tab" data-bs-toggle="tab" data-bs-target="#datos" type="button" role="tab"><i class="fas fa-building me-1"></i> Datos Generales</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="logo-tab" data-bs-toggle="tab" data-bs-target="#logo" type="button" role="tab"><i class="fas fa-image me-1"></i> Logo Institucional</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="orientaciones-tab" data-bs-toggle="tab" data-bs-target="#orientaciones" type="button" role="tab"><i class="fas fa-graduation-cap me-1"></i> Orientaciones Académicas</button>
    </li>
</ul>

<div class="tab-content border border-top-0 p-4 bg-white shadow-sm mb-4" id="configTabsContent">
    
    <!-- DATOS GENERALES -->
    <div class="tab-pane fade show active" id="datos" role="tabpanel">
        <form id="formInstitucion">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Nombre del Establecimiento</label>
                    <input type="text" name="nombre" class="form-control" style="text-transform: uppercase" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Director/a</label>
                    <input type="text" name="director" class="form-control" style="text-transform: uppercase">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Dirección</label>
                    <input type="text" name="direccion" class="form-control" style="text-transform: uppercase">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Localidad</label>
                    <input type="text" name="localidad" class="form-control" style="text-transform: uppercase">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Provincia</label>
                    <input type="text" name="provincia" class="form-control" style="text-transform: uppercase">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Teléfono</label>
                    <input type="text" name="telefono" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Correo Electrónico</label>
                    <input type="email" name="email" class="form-control">
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-2"><i class="fas fa-save me-1"></i> Guardar Cambios</button>
        </form>
    </div>

    <!-- LOGO INSTITUCIONAL -->
    <div class="tab-pane fade" id="logo" role="tabpanel">
        <form id="formLogo" enctype="multipart/form-data">
            <div class="row align-items-center">
                <div class="col-md-4 text-center mb-3">
                    <p class="fw-bold mb-2">Logo Actual</p>
                    <img src="assets/img/logo.png?v=<?= time() ?>" id="imgLogoActual" alt="Logo Institucional" class="img-thumbnail shadow-sm mb-3" style="max-height:150px; width:auto; border-radius: 8px;">
                    <div>
                        <button type="button" id="btnEliminarLogo" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash me-1"></i> Eliminar Logo
                        </button>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h6 class="fw-bold">Subir Nuevo Logo</h6>
                            <p class="text-muted small">Se recomienda usar una imagen en formato PNG con fondo transparente. Tamaño máximo: 2 MB.</p>
                            <input type="file" name="logo" class="form-control mb-3" accept="image/png, image/jpeg, image/jpg, image/webp" required>
                            <button type="submit" class="btn btn-success"><i class="fas fa-upload me-1"></i> Subir y Actualizar</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ORIENTACIONES ACADÉMICAS -->
    <div class="tab-pane fade" id="orientaciones" role="tabpanel">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-white">
                        <h6 class="m-0 font-weight-bold text-primary">Nueva Orientación</h6>
                    </div>
                    <div class="card-body">
                        <form id="formOrientacion">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Nombre</label>
                                <input type="text" name="nombre" class="form-control" style="text-transform: uppercase" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="3" style="text-transform: uppercase"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i> Agregar</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header py-3 bg-white">
                        <h6 class="m-0 font-weight-bold text-primary">Orientaciones Existentes</h6>
                    </div>
                    <div class="card-body">
                        <div id="listaOrientaciones" class="row">
                            <!-- Llenado vía AJAX -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
</div>

<script>
$(document).ready(function() {
    cargarDatosInstitucion();
    cargarOrientaciones();

    // Guardar Datos
    $('#formInstitucion').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'controllers/configuracion_ajax.php',
            type: 'POST',
            data: $(this).serialize() + '&action=guardar',
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    Swal.fire({icon: 'success', title: 'Guardado', text: res.msg, showConfirmButton: false, timer: 1500});
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }
        });
    });

    // Subir Logo
    $('#formLogo').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        formData.append('action', 'subir_logo');
        
        $.ajax({
            url: 'controllers/configuracion_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    Swal.fire({icon: 'success', title: 'Logo actualizado', text: res.msg, showConfirmButton: false, timer: 1500});
                    $('#imgLogoActual').attr('src', 'assets/img/logo.png?v=' + new Date().getTime());
                    $('#formLogo')[0].reset();
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }
        });
    });

    // Eliminar Logo
    $('#btnEliminarLogo').on('click', function() {
        Swal.fire({
            title: '¿Eliminar logo?',
            text: 'Esta acción borrará el logo institucional.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('controllers/configuracion_ajax.php', { action: 'eliminar_logo' }, function(res) {
                    if (res.status === 'success') {
                        $('#imgLogoActual').attr('src', '');
                        Swal.fire({icon: 'success', title: 'Eliminado', text: res.msg, showConfirmButton: false, timer: 1500});
                    } else {
                        Swal.fire('Error', res.msg, 'error');
                    }
                }, 'json');
            }
        });
    });

    // Nueva Orientación
    $('#formOrientacion').on('submit', function(e) {
        e.preventDefault();
        $.post('controllers/configuracion_ajax.php', $(this).serialize() + '&action=guardar_orientacion', function(res) {
            if (res.status === 'success') {
                Swal.fire({icon: 'success', title: 'Agregada', text: res.msg, showConfirmButton: false, timer: 1500});
                $('#formOrientacion')[0].reset();
                cargarOrientaciones();
            } else {
                Swal.fire('Error', res.msg, 'error');
            }
        }, 'json');
    });



async function cargarDatosInstitucion() {
    $.post('controllers/configuracion_ajax.php', { action: 'consultar' }, function(res) {
        if (res.status === 'success' && res.data) {
            let data = res.data;
            $('#formInstitucion input[name="nombre"]').val(data.nombre);
            $('#formInstitucion input[name="director"]').val(data.director);
            $('#formInstitucion input[name="direccion"]').val(data.direccion);
            $('#formInstitucion input[name="localidad"]').val(data.localidad);
            $('#formInstitucion input[name="provincia"]').val(data.provincia);
            $('#formInstitucion input[name="telefono"]').val(data.telefono);
            $('#formInstitucion input[name="email"]').val(data.email);
        }
    }, 'json');
}

async function cargarOrientaciones() {
    $.post('controllers/configuracion_ajax.php', { action: 'listar_orientaciones' }, function(res) {
        if (res.status === 'success') {
            let html = '';
            res.data.forEach(o => {
                html += `
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-left-primary shadow-sm">
                            <div class="card-body">
                                <h6 class="fw-bold mb-1">${o.nombre}</h6>
                                <p class="small text-muted mb-3">${o.descripcion || 'Sin descripción'}</p>
                                <button class="btn btn-sm btn-outline-danger" onclick="eliminarOrientacion('${o.id_hash}')">
                                    <i class="fas fa-trash me-1"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#listaOrientaciones').html(html || '<div class="col-12"><p class="text-muted">No hay orientaciones registradas.</p></div>');
        }
    }, 'json');
}

function eliminarOrientacion(id_hash) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "No podrás revertir esta acción.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        cancelButtonColor: '#858796',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('controllers/configuracion_ajax.php', { action: 'eliminar_orientacion', id: id_hash }, function(res) {
                if (res.status === 'success') {
                    Swal.fire({icon: 'success', title: 'Eliminado', text: res.msg, showConfirmButton: false, timer: 1500});
                    cargarOrientaciones();
                } else {
                    Swal.fire('Error', res.msg, 'error');
                }
            }, 'json');
        }
    });
}
</script>
