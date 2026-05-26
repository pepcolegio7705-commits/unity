<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="page-title mb-0">Dashboard</h1>
    <div class="text-muted">
        <i class="fa-regular fa-calendar me-2"></i> <?php echo date('d M Y'); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card p-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">Total Alumnos</h6>
                    <h3 class="mb-0 fw-bold">Gestión</h3>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
            </div>
            <a href="?page=alumnos" class="text-white text-decoration-none mt-3 fw-medium" style="font-size: 0.9rem;">
                Ir a Alumnos <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header border-0 bg-transparent">
                <h5 class="mb-0">Bienvenido a Sintek-Unity</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">El sistema de gestión de contenidos educativos ha sido actualizado a su nueva versión. Navegue a través del menú lateral para acceder a los distintos módulos.</p>
                <div class="alert alert-primary bg-primary bg-opacity-10 border-0 text-primary mt-4">
                    <i class="fa-solid fa-circle-info me-2"></i> Actualmente migrando el módulo de <strong>Alumnos</strong> a la nueva arquitectura.
                </div>
            </div>
        </div>
    </div>
</div>
