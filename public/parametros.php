<?php $pageTitle = 'Término de Contrato'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>

<link rel="stylesheet" href="<?= $basePath ?>/assets/css/resumen.css" /> 

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
      <div class="sub">Necesidades de la empresa</div>
    </div>
  </div>
</div>

<div class="tip-box">
  ⚠️ <span><strong>Nota de Cumplimiento:</strong> La correcta aplicación de estos artículos es fundamental para evitar conflictos legales y despidos injustificados. Los tribunales analizan estrictamente cada causal invocada.</span>
</div>

<div class="accordion">

  <!-- ART 159 -->
  <div class="acc-item open">
    <div class="acc-header" onclick="toggle(this)">
      <div class="acc-num">159</div>
      <div class="acc-title">Causales de término sin responsabilidad del empleador</div>
      <svg class="acc-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
    </div>
    <div class="acc-body">
      Establece causales <strong>objetivas</strong> de término del contrato de trabajo que <strong>no generan derecho a indemnización</strong> por años de servicio. La relación laboral finaliza de manera natural o voluntaria.
      <br><br>
      <strong>Principales causales:</strong>
      <ul style="margin-top:8px; line-height:2;">
        <li><strong>Mutuo acuerdo:</strong> Requiere consentimiento real de ambas partes. No puede usarse para simular y eludir indemnizaciones.</li>
        <li><strong>Renuncia del trabajador:</strong> Debe ser libre y voluntaria, usualmente ratificada ante ministro de fe.</li>
        <li><strong>Muerte del trabajador:</strong> Término automático de la relación laboral.</li>
        <li><strong>Vencimiento del plazo convenido:</strong> Aplica en contratos a plazo fijo.</li>
        <li><strong>Conclusión del trabajo o servicio:</strong> Frecuente en contratos por obra o faena.</li>
      </ul>
      <br>
      <strong>Uso real:</strong> Es frecuente en contratos a plazo fijo, trabajos por obra y acuerdos negociados de salida. El mutuo acuerdo no debe utilizarse para eludir indemnizaciones legales.
      <div class="pill-list">
        <span class="pill">Sin Indemnización</span>
        <span class="pill green">Contratos a plazo fijo</span>
        <span class="pill purple">Salida voluntaria</span>
        <span class="pill orange">Ratificación ante ministro de fe</span>
      </div>
    </div>
  </div>

  <!-- ART 160 -->
  <div class="acc-item">
    <div class="acc-header" onclick="toggle(this)">
      <div class="acc-num">160</div>
      <div class="acc-title">Despido por causales imputables al trabajador</div>
      <svg class="acc-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
    </div>
    <div class="acc-body">
      Regula el <strong>despido disciplinario</strong> donde el trabajador incurre en conductas graves. El empleador <strong>debe probar</strong> la causal invocada con evidencia concreta.
      <br><br>
      <strong>Ejemplos de causales:</strong>
      <ul style="margin-top:8px; line-height:2;">
        <li><strong>Falta de probidad:</strong> Conductas deshonestas o contrarias a la ética laboral.</li>
        <li><strong>Conductas de acoso:</strong> Acoso laboral o sexual debidamente acreditado.</li>
        <li><strong>Abandono del trabajo:</strong> Ausencia injustificada o negativa a trabajar.</li>
        <li><strong>Incumplimiento grave de obligaciones:</strong> Infracciones serias al contrato.</li>
      </ul>
      <br>
      <strong>Interpretación práctica:</strong> La jurisprudencia exige <strong>proporcionalidad, gravedad y evidencia concreta</strong>. Un error común es invocar esta causal sin respaldo suficiente, lo que puede derivar en despido injustificado y obligar al empleador a pagar indemnizaciones.
      <br><br>
      <strong>Riesgo:</strong> Es altamente litigado. Los tribunales analizan estrictamente la conducta del trabajador y si la sanción es proporcional a la falta.
      <div class="pill-list">
        <span class="pill red">Falta de Probidad</span>
        <span class="pill red">Acoso Laboral / Sexual</span>
        <span class="pill">Sin Indemnización</span>
        <span class="pill orange">Alto riesgo de litigio</span>
        <span class="pill purple">Requiere prueba concreta</span>
      </div>
    </div>
  </div>

  <!-- ART 161 -->
  <div class="acc-item">
    <div class="acc-header" onclick="toggle(this)">
      <div class="acc-num">161</div>
      <div class="acc-title">Necesidades de la empresa</div>
      <svg class="acc-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
    </div>
    <div class="acc-body">
      Permite al empleador poner término al contrato por <strong>razones económicas, técnicas o estructurales</strong> de la organización. A diferencia del Art. 160, aquí <strong>sí corresponde indemnización</strong> por años de servicio.
      <br><br>
      <strong>Ejemplos de causales:</strong>
      <ul style="margin-top:8px; line-height:2;">
        <li><strong>Reestructuración:</strong> Reorganización interna de la empresa.</li>
        <li><strong>Baja en productividad:</strong> Reducción comprobable de la actividad económica.</li>
        <li><strong>Cambios del mercado:</strong> Situaciones externas que afectan la operación.</li>
      </ul>
      <br>
      <strong>Interpretación práctica:</strong> Debe existir una justificación <strong>real, seria y comprobable</strong>. No puede utilizarse de forma arbitraria o como pretexto para despidos sin causa.
      <br><br>
      <strong>Uso real:</strong> Es una de las causales más utilizadas, pero también más cuestionadas. Los tribunales revisan si la empresa efectivamente tenía la necesidad invocada y si esta era proporcional a la medida adoptada.
      <div class="pill-list">
        <span class="pill green">Con Indemnización</span>
        <span class="pill purple">Reestructuración</span>
        <span class="pill">Justificación comprobable</span>
        <span class="pill orange">Revisión judicial</span>
      </div>
    </div>
  </div>

</div>

<!-- CONCLUSIÓN -->
<div class="tip-box" style="margin-bottom:24px;">
  <span><strong>Conclusión:</strong> El Art. 159 se refiere a términos naturales de la relación, el Art. 160 a conductas graves del trabajador y el Art. 161 a decisiones empresariales justificadas. Cada uno tiene requisitos y consecuencias distintas.</span>
</div>

<div class="info-grid">
  <div class="info-card">
    <h3>Interpretación Práctica</h3>
    <ul>
      <li>El Art. 159 se usa para términos naturales de la relación laboral.</li>
      <li>El Art. 160 es estrictamente disciplinario y requiere respaldo sólido.</li>
      <li>El Art. 161 requiere que la necesidad sea real, seria y no arbitraria.</li>
      <li>El mutuo acuerdo no debe usarse para eludir indemnizaciones legales.</li>
    </ul>
  </div>
  <div class="info-card">
    <h3>⚠️ Riesgos Jurídicos</h3>
    <ul>
      <li>Invocar el Art. 160 sin pruebas deriva en despido injustificado.</li>
      <li>Los tribunales revisan si la empresa realmente tenía la necesidad del Art. 161.</li>
      <li>La renuncia debe ser libre, voluntaria y ratificada ante ministro de fe.</li>
      <li>La proporcionalidad de la sanción es clave en el Art. 160.</li>
    </ul>
  </div>
</div>

<div class="actions" style="margin-bottom:32px;">
  <a class="btn ghost btn-volver" href="<?= htmlspecialchars($basePath) ?>index.php">← Volver al Inicio</a>
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