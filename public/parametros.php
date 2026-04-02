<?php $pageTitle = 'Parámetros y Causales'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>

<link rel="stylesheet" href="/remuneraciones/public/assets/css/resumen.css" />

<div class="resumen-hero">
  <h1>Término de Contrato</h1>
  <p>Análisis técnico de las causales de término de la relación laboral según los artículos 159, 160 y 161 del Código del Trabajo de Chile.</p>
</div>

<div class="stats-strip">
  <div class="stat-card">
    <div class="stat-icon blue">⚖️</div>
    <div class="stat-info">
      <div class="label">Art. 159</div>
      <div class="value">Objetivas</div>
      <div class="sub">Términos naturales</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red">🚫</div>
    <div class="stat-info">
      <div class="label">Art. 160</div>
      <div class="value">Disciplinarias</div>
      <div class="sub">Imputables al trabajador</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange">🏢</div>
    <div class="stat-info">
      <div class="label">Art. 161</div>
      <div class="value">Económicas</div>
      [cite_start]<div class="sub">Necesidades de la empresa</div>
    </div>
  </div>
</div>

<div class="tip-box">
  ⚠️ <span><strong>Nota de Cumplimiento:</strong> La correcta aplicación de estos artículos es fundamental para evitar conflictos legales y despidos injustificados.</span>
</div>

<div class="accordion">

  <div class="acc-item open">
    <div class="acc-header" onclick="toggle(this)">
      <div class="acc-num">159</div>
      <div class="acc-title">Causales de término sin responsabilidad del empleador</div>
      <svg class="acc-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
    </div>
    <div class="acc-body">
      Establece causales objetivas que no generan derecho a indemnización por años de servicio.
      <ul>
        <li><strong>Causales:</strong> Mutuo acuerdo, renuncia, muerte del trabajador, vencimiento de plazo o conclusión del servicio.</li>
        <li><strong>Práctica:</strong> La renuncia debe ser ratificada ante ministro de fe y el mutuo acuerdo requiere consentimiento real para evitar simulaciones.</li>
      </ul>
      <div class="pill-list">
        <span class="pill">Sin Indemnización</span>
        <span class="pill green">Contratos a plazo fijo [cite: 8]</span>
        <span class="pill purple">Salida voluntaria</span>
      </div>
    </div>
  </div>

  <div class="acc-item">
    <div class="acc-header" onclick="toggle(this)">
      <div class="acc-num">160</div>
      <div class="acc-title">Despido por causales imputables al trabajador</div>
      <svg class="acc-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
    </div>
    <div class="acc-body">
        Regula el despido disciplinario por conductas graves del trabajador.
      <ul>
        <li><strong>Causales:</strong> Falta de probidad, acoso, abandono del trabajo o incumplimiento grave de obligaciones.</li>
        <li><strong>Evidencia:</strong> El empleador debe probar la conducta. [cite_start]Los tribunales exigen proporcionalidad y pruebas concretas.</li>
      </ul>
      <div class="pill-list">
        <span class="pill red">Falta de Probidad</span>
        <span class="pill">Sin Indemnización</span>
        <span class="pill orange">Riesgo de litigio</span>
      </div>
    </div>
  </div>

  <div class="acc-item">
    <div class="acc-header" onclick="toggle(this)">
      <div class="acc-num">161</div>
      <div class="acc-title">Necesidades de la empresa</div>
      <svg class="acc-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
    </div>
    <div class="acc-body">
      Permite el término por razones económicas, técnicas o estructurales de la organización.
      <ul>
        <li><strong>Ejemplos:</strong> Reestructuración, baja en productividad o cambios del mercado.</li>
        <li><strong>Indemnización:</strong> A diferencia del Art. 160, aquí **sí corresponde** el pago de indemnización por años de servicio.</li>
      </ul>
      <div class="pill-list">
        [cite_start]<span class="pill green">Con Indemnización</span>
        [cite_start]<span class="pill purple">Reestructuración</span>
        [cite_start]<span class="pill">Justificación comprobable</span>
      </div>
    </div>
  </div>

</div>

<div class="info-grid">
  <div class="info-card">
    <h3>Interpretación Práctica</h3>
    <ul>
      <li>El Art. 159 se usa para términos "naturales" de la relación.</li>
      <li>El Art. 160 es estrictamente disciplinario y requiere respaldo sólido.</li>
      <li>El Art. 161 requiere que la necesidad sea real, seria y no arbitraria.</li>
    </ul>
  </div>
  <div class="info-card">
    <h3>Riesgos Jurídicos</h3>
    <ul>
      <li>Invocar el Art. 160 sin pruebas deriva en despido injustificado.</li>
      <li>Los tribunales revisan si la empresa realmente tenía la necesidad del Art. 161.</li>
      <li>El mutuo acuerdo no debe usarse para eludir indemnizaciones legales.</li>
    </ul>
  </div>
</div>

<div class="actions" style="margin-bottom:32px;">
  <a class="btn btn-ghost" href="/remuneraciones/public/index.php">← Volver al Inicio</a>
</div>

<script>
function toggle(header) {
  const item = header.parentElement;
  const wasOpen = item.classList.contains('open');
  document.querySelectorAll('.acc-item').forEach(i => i.classList.remove('open'));
  if (!wasOpen) item.classList.add('open');
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
