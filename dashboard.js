$(document).ready(function () {
  $.getJSON("dashboard_datos.php", function (res) {
    const $cards = $("#dashboardCards").empty();

    // Total de alumnos
    $cards.append(`
      <div class="col-md-3">
        <div class="card border-success shadow-sm">
          <div class="card-body text-center">
            <h5 class="card-title">👥 Alumnos activos</h5>
            <p class="fs-4 text-success">${res.total_alumnos}</p>
          </div>
        </div>
      </div>
    `);

    // Total asistencias
    $cards.append(`
      <div class="col-md-3">
        <div class="card border-primary shadow-sm">
          <div class="card-body text-center">
            <h5 class="card-title">📋 Asistencias registradas</h5>
            <p class="fs-4 text-primary">${res.total_asistencias}</p>
          </div>
        </div>
      </div>
    `);

    // Porcentaje asistencia promedio
    $cards.append(`
      <div class="col-md-3">
        <div class="card border-info shadow-sm">
          <div class="card-body text-center">
            <h5 class="card-title">📊 % Asistencia promedio</h5>
            <p class="fs-4 text-info">${res.porcentaje_asistencia}%</p>
          </div>
        </div>
      </div>
    `);

    // Placeholder calificaciones
    $cards.append(`
      <div class="col-md-3">
        <div class="card border-secondary shadow-sm">
          <div class="card-body text-center">
            <h5 class="card-title">📝 Calificaciones</h5>
            <p class="fs-6 text-muted">Módulo en desarrollo</p>
          </div>
        </div>
      </div>
    `);
  });
});