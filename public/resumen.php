<?php $pageTitle = 'Resumen'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>

<link rel="stylesheet" href="<?= $basePath ?>assets/css/resumen.css" />
<!-- HERO -->
<div class="resumen-hero">
  <h1>Guía de Remuneraciones Chile - Conceptos Importantes</h1>
  <p>Referencia interactiva con los conceptos clave del sistema de remuneraciones según la normativa chilena vigente.</p>
</div>

<!-- STATS STRIP -->
<div class="stats-strip">
  <div class="stat-card">
    <div class="stat-icon blue">⏱️</div>
    <div class="stat-info">
      <div class="label">Jornada máxima</div>
      <div class="value">44 hrs</div>
      <div class="sub">Semanales ordinarias</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green">🏖️</div>
    <div class="stat-info">
      <div class="label">Vacaciones legales</div>
      <div class="value">15 días</div>
      <div class="sub">Hábiles al año</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple">🏦</div>
    <div class="stat-info">
      <div class="label">Cotización AFP desde el</div>
      <div class="value">10,56%</div>
      <div class="sub">+ comisión de la AFP</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange">🏥</div>
    <div class="stat-info">
      <div class="label">Cotización salud</div>
      <div class="value">7%</div>
      <div class="sub">Mínimo obligatorio</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue">🛡️</div>
    <div class="stat-info">
      <div class="label">Seguro cesantía</div>
      <div class="value">0.6%</div>
      <div class="sub">Aporte del trabajador</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange">💼</div>
    <div class="stat-info">
      <div class="label">Horas extra recargo</div>
      <div class="value">50%</div>
      <div class="sub">Sobre valor hora normal</div>
    </div>
  </div>
</div>

<!-- TIP -->
<div class="tip-box">
  ⚠️ <span><strong>Es Importante:</strong> Mantente actualizado con los topes imponibles y tablas de impuesto único que publica el SII y la Superintendencia de Pensiones cada año.</span>
</div>

<!-- ACCORDION -->
<div class="accordion">

  <!-- 1 -->
  <div class="acc-item open">
    <div class="acc-header" onclick="toggle(this)">
      <div class="acc-num">1</div>
      <div class="acc-title">Marco Legal e Instituciones Clave</div>
      <svg class="acc-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
    </div>
    <div class="acc-body">
      El sistema de remuneraciones está regulado principalmente por el <strong>Código del Trabajo</strong>, que establece condiciones mínimas de empleo, jornadas, descansos, sueldos y derechos laborales.
      <div class="pill-list">
        <span class="pill">Dirección del Trabajo</span>
        <span class="pill purple">SII — Impuestos</span>
        <span class="pill green">AFP — Pensiones</span>
        <span class="pill orange">FONASA / ISAPRE</span>
        <span class="pill red">AFC — Cesantía</span>
      </div>
    </div>
  </div>

  <!-- 2 -->
  <div class="acc-item">
    <div class="acc-header" onclick="toggle(this)">
      <div class="acc-num">2</div>
      <div class="acc-title">Tipos de Remuneración</div>
      <svg class="acc-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
    </div>
    <div class="acc-body">
      La remuneración es toda compensación en dinero o especie que recibe el trabajador por sus servicios. Se divide en:
      <div class="pill-list">
        <span class="pill green">Sueldo base — pago fijo</span>
        <span class="pill">Gratificación — participación en utilidades</span>
        <span class="pill orange">Horas extras — recargo 50%</span>
        <span class="pill purple">Bonos — incentivos variables</span>
        <span class="pill">Comisiones — % sobre ventas</span>
      </div>
    </div>
  </div>

  <!-- 3 -->
  <div class="acc-item">
    <div class="acc-header" onclick="toggle(this)">
      <div class="acc-num">3</div>
      <div class="acc-title">Haberes Imponibles vs No Imponibles</div>
      <svg class="acc-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
    </div>
    <div class="acc-body">
      <strong>Imponibles:</strong> afectos a cotizaciones previsionales e impuesto único. Ejemplo: sueldo base, horas extras, bonos de producción.<br><br>
      <strong>No imponibles:</strong> no se descuentan cotizaciones. Ejemplo: colación, movilización, viáticos, asignación de herramientas.
      <div class="pill-list">
        <span class="pill green"> Imponible → con descuentos legales</span>
        <span class="pill orange"> No imponible → sin descuentos</span>
      </div>
    </div>
  </div>

  <!-- 4 -->
  <div class="acc-item">
    <div class="acc-header" onclick="toggle(this)">
      <div class="acc-num">4</div>
      <div class="acc-title">Cotizaciones Previsionales Obligatorias</div>
      <svg class="acc-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
    </div>
    <div class="acc-body">
      Se descuentan del sueldo imponible del trabajador:
      <table class="cot-table">
        <thead><tr><th>Concepto</th><th>Porcentaje</th><th>Institución</th></tr></thead>
        <tbody>
          <tr><td>AFP (pensión) desde el </td><td class="pct">10,46%</td><td>+ comisión según AFP</td></tr>
          <tr><td>Salud</td><td class="pct">7%</td><td>FONASA o ISAPRE</td></tr>
          <tr><td>Seguro de cesantía</td><td class="pct">0.6%</td><td>AFC Chile</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 5 -->
  <div class="acc-item">
    <div class="acc-header" onclick="toggle(this)">
      <div class="acc-num">5</div>
      <div class="acc-title">Impuesto Único al Trabajo</div>
      <svg class="acc-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
    </div>
    <div class="acc-body">
      Es un impuesto <strong>progresivo</strong> que se calcula sobre la renta imponible una vez descontadas las cotizaciones previsionales. A mayor ingreso, mayor tasa. Las tablas son publicadas mensualmente por el <strong>SII</strong>.
      <div class="pill-list">
        <span class="pill purple">Progresivo por tramos de renta</span>
        <span class="pill">Se retiene mensualmente</span>
        <span class="pill green"> El empleador lo entera al SII</span>
      </div>
    </div>
  </div>

  <!-- 6 -->
  <div class="acc-item">
    <div class="acc-header" onclick="toggle(this)">
      <div class="acc-num">6</div>
      <div class="acc-title">Contratos de Trabajo</div>
      <svg class="acc-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
    </div>
    <div class="acc-body">
      Todo contrato debe incluir identificación de las partes, jornada de trabajo y remuneración pactada.
      <div class="pill-list">
        <span class="pill green">Indefinido</span>
        <span class="pill orange">A plazo fijo</span>
        <span class="pill purple">Por obra o faena</span>
      </div>
    </div>
  </div>

  <!-- 7 -->
  <div class="acc-item">
    <div class="acc-header" onclick="toggle(this)">
      <div class="acc-num">7</div>
      <div class="acc-title">Finiquitos y Término de Contrato</div>
      <svg class="acc-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
    </div>
    <div class="acc-body">
      El finiquito pone término a la relación laboral e incluye todos los pagos pendientes al trabajador.
      <div class="pill-list">
        <span class="pill">Indemnización por años de servicio</span>
        <span class="pill orange">Vacaciones proporcionales</span>
        <span class="pill green">Remuneraciones pendientes</span>
        <span class="pill purple">Gratificación proporcional</span>
      </div>
    </div>
  </div>

  <!-- 8 -->
  <div class="acc-item">
    <div class="acc-header" onclick="toggle(this)">
      <div class="acc-num">8</div>
      <div class="acc-title">Errores Comunes a Evitar</div>
      <svg class="acc-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
    </div>
    <div class="acc-body">
      Los errores más frecuentes en la gestión de remuneraciones son:
      <div class="pill-list">
        <span class="pill red">❌ Mal cálculo de horas extras</span>
        <span class="pill red">❌ No actualizar topes imponibles</span>
        <span class="pill red">❌ Fallas en cotizaciones previsionales</span>
        <span class="pill red">❌ No aplicar tabla de impuesto vigente</span>
        <span class="pill red">❌ Gratificación incorrecta</span>
      </div>
    </div>
  </div>

</div>

<!-- BOTTOM INFO CARDS -->
<div class="info-grid">
  <div class="info-card">
    <h3>Buenas Prácticas</h3>
    <ul>
      <li>Mantenerse actualizado con la normativa</li>
      <li>Validar cálculos antes de pagar</li>
      <li>Automatizar procesos repetitivos</li>
      <li>Proteger la confidencialidad de los datos</li>
      <li>Documentar todos los cambios</li>
    </ul>
  </div>
  <div class="info-card">
    <h3>Herramientas Recomendadas</h3>
    <ul>
      <li>Sistemas ERP especializados</li>
      <li>Excel avanzado con macros</li>
      <li>Software de liquidaciones en línea</li>
      <li>Acceso directo al portal del SII</li>
      <li>Previred para cotizaciones</li>
    </ul>
  </div>
</div>

<div class="actions" style="margin-bottom:32px;">
 <a class="btn btn-ghost" href="<?= $basePath ?>index.php">← Volver al Inicio</a>
</div>

<script>
function toggle(header) {
  const item = header.parentElement;
  const wasOpen = item.classList.contains('open');
  // Cierra todos
  document.querySelectorAll('.acc-item').forEach(i => i.classList.remove('open'));
  // Abre el clickeado si estaba cerrado
  if (!wasOpen) item.classList.add('open');
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

