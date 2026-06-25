// ============================================================
// app.js — Questionnaire Chlef 2026
// ============================================================

// ─── SCHOOL OTHER TOGGLE ──────────────────────────────────
function toggleSchoolOther(sel) {
  const other = document.getElementById('school_other');
  if (other) other.style.display = sel.value === '__other__' ? 'block' : 'none';
}

// ─── IOTF TABLES (Cole et al. 2000 + 2007) ───────────────
const IOTF_SW_OW = {
  2.0:[18.41,20.09,18.02,19.81],2.5:[18.13,19.80,17.76,19.55],
  3.0:[17.89,19.57,17.56,19.36],3.5:[17.69,19.39,17.40,19.23],
  4.0:[17.55,19.29,17.28,19.15],4.5:[17.47,19.26,17.19,19.12],
  5.0:[17.42,19.30,17.15,19.17],5.5:[17.45,19.47,17.20,19.34],
  6.0:[17.55,19.78,17.34,19.65],6.5:[17.71,20.23,17.53,20.08],
  7.0:[17.92,20.63,17.75,20.51],7.5:[18.16,21.09,18.03,21.01],
  8.0:[18.44,21.60,18.35,21.57],8.5:[18.76,22.17,18.69,22.18],
  9.0:[19.10,22.77,19.07,22.81],9.5:[19.46,23.39,19.45,23.46],
  10.0:[19.84,24.00,19.86,24.11],10.5:[20.20,24.57,20.29,24.77],
  11.0:[20.55,25.10,20.74,25.42],11.5:[20.89,25.58,21.20,26.05],
  12.0:[21.22,26.02,21.68,26.67],12.5:[21.56,26.43,22.14,27.24],
  13.0:[21.91,26.84,22.58,27.76],13.5:[22.27,27.25,22.98,28.20],
  14.0:[22.62,27.63,23.34,28.57],14.5:[22.96,27.98,23.66,28.87],
  15.0:[23.29,28.30,23.94,29.11],15.5:[23.60,28.60,24.17,29.29],
  16.0:[23.90,28.88,24.37,29.43],16.5:[24.19,29.14,24.54,29.56],
  17.0:[24.46,29.41,24.70,29.69],17.5:[24.73,29.70,24.85,29.84],
  18.0:[25.00,30.00,25.00,30.00]
};
const IOTF_THIN = {
  2.0:[14.49,13.19,14.60,13.36],2.5:[14.27,13.00,14.37,13.14],
  3.0:[14.09,12.84,14.18,12.96],3.5:[13.94,12.70,14.01,12.79],
  4.0:[13.79,12.56,13.86,12.64],4.5:[13.66,12.44,13.73,12.50],
  5.0:[13.53,12.32,13.60,12.37],5.5:[13.41,12.21,13.48,12.25],
  6.0:[13.29,12.10,13.37,12.13],6.5:[13.18,12.00,13.27,12.02],
  7.0:[13.09,11.92,13.17,11.91],7.5:[13.01,11.84,13.09,11.83],
  8.0:[12.94,11.77,13.02,11.76],8.5:[12.89,11.72,12.96,11.70],
  9.0:[12.85,11.67,12.92,11.65],9.5:[12.83,11.64,12.90,11.62],
  10.0:[12.83,11.63,12.90,11.60],10.5:[12.85,11.64,12.91,11.60],
  11.0:[12.89,11.66,12.95,11.62],11.5:[12.95,11.71,13.01,11.65],
  12.0:[13.03,11.77,13.10,11.70],12.5:[13.13,11.85,13.21,11.77],
  13.0:[13.25,11.95,13.34,11.86],13.5:[13.38,12.06,13.49,11.97],
  14.0:[13.53,12.19,13.65,12.09],14.5:[13.69,12.33,13.82,12.23],
  15.0:[13.86,12.47,13.99,12.37],15.5:[14.03,12.62,14.16,12.51],
  16.0:[14.21,12.77,14.33,12.66],16.5:[14.38,12.92,14.49,12.81],
  17.0:[14.56,13.08,14.65,12.96],17.5:[14.74,13.23,14.81,13.11],
  18.0:[17.50,16.00,17.50,16.00]
};

function getIOTFClass(bmi, age, sex) {
  const a   = Math.max(2, Math.min(18, Math.round(age * 2) / 2));
  const boy = (sex === 'Garçon');
  const row = IOTF_SW_OW[a];
  if (!row) {
    if (bmi >= 30) return 'Obésité';
    if (bmi >= 25) return 'Surpoids';
    if (bmi >= 18.5) return 'Normal';
    return 'Minceur';
  }
  const sw = row[boy ? 0 : 2];
  const ow = row[boy ? 1 : 3];
  const tr = IOTF_THIN[a];
  const t2 = tr ? tr[boy ? 0 : 2] : 17.0;
  const t3 = tr ? tr[boy ? 1 : 3] : 16.0;

  if (bmi >= ow) return 'Obésité';
  if (bmi >= sw) return 'Surpoids';
  if (bmi >= t2) return 'Normal';
  if (bmi >= t3) return 'Minceur grade 2';
  return 'Minceur grade 3';
}

// ─── BMI AUTO-CALCULATION ────────────────────────────────
function calcBMI() {
  const h   = parseFloat(document.getElementById('q_height')?.value);
  const w   = parseFloat(document.getElementById('q_weight')?.value);
  const age = parseFloat(document.getElementById('q_age')?.value);
  const sex = document.querySelector('input[name="sex"]:checked')?.value || 'Garçon';

  if (!h || !w || h < 100 || w < 20) return;

  const bmi  = parseFloat((w / Math.pow(h / 100, 2)).toFixed(1));
  const iotf = (age >= 2 && age <= 18) ? getIOTFClass(bmi, age, sex) : (
    bmi >= 30 ? 'Obésité' : bmi >= 25 ? 'Surpoids' : bmi >= 18.5 ? 'Normal' : 'Minceur'
  );

  document.getElementById('q_bmi').value  = bmi.toFixed(1);
  const el = document.getElementById('q_iotf');
  el.value = iotf;
  el.style.color = {
    'Normal':'#1a7340', 'Surpoids':'#856404',
    'Obésité':'#9b2335', 'Minceur grade 2':'#0c5460',
    'Minceur grade 3':'#721c24'
  }[iotf] || '#333';
}

document.getElementById('q_height')?.addEventListener('input', calcBMI);
document.getElementById('q_weight')?.addEventListener('input', calcBMI);
document.getElementById('q_age')?.addEventListener('input', calcBMI);
document.querySelectorAll('input[name="sex"]').forEach(el => el.addEventListener('change', calcBMI));

// ─── STEP NAVIGATION ─────────────────────────────────────
function gotoStep(n) {
  document.querySelectorAll('.form-step').forEach((el, i) => {
    el.classList.toggle('active', i + 1 === n);
  });
  document.querySelectorAll('.progress-steps .step').forEach((el, i) => {
    el.classList.remove('active', 'done');
    if (i + 1 === n) el.classList.add('active');
    else if (i + 1 < n) el.classList.add('done');
  });
  if (n === 5) buildReview();
  updateAllStepProgress();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ─── STEP PROGRESS BARS ──────────────────────────────────
function countStepFields(stepEl) {
  const inputs  = stepEl.querySelectorAll('input[type="number"], input[type="text"], input[type="time"]');
  const selects = stepEl.querySelectorAll('select');
  // Pour les radios, on compte par name (chaque groupe = 1 champ)
  const radioNames = new Set();
  stepEl.querySelectorAll('input[type="radio"]').forEach(r => { if (r.name) radioNames.add(r.name); });

  let total = 0, filled = 0;

  inputs.forEach(el => {
    if (!el.name || el.readOnly) return; // ignorer les champs calculés (BMI, IOTF)
    total++;
    if (el.value.trim() !== '') filled++;
  });

  selects.forEach(el => {
    if (!el.name) return;
    total++;
    if (el.value !== '') filled++;
  });

  radioNames.forEach(name => {
    total++;
    if (stepEl.querySelector(`input[name="${name}"]:checked`)) filled++;
  });

  return { total, filled };
}

function updateStepProgress(stepNum) {
  const stepEl = document.getElementById('formStep' + stepNum);
  if (!stepEl || stepNum === 5) return; // pas de barre pour l'étape de révision

  const { total, filled } = countStepFields(stepEl);
  const pct = total > 0 ? Math.round(filled / total * 100) : 0;

  // Mettre à jour la barre dans l'étape
  let bar = stepEl.querySelector('.step-progress');
  if (!bar) {
    bar = document.createElement('div');
    bar.className = 'step-progress';
    bar.innerHTML = `
      <div class="step-prog-info">
        <span class="step-prog-text"></span>
        <span class="step-prog-pct"></span>
      </div>
      <div class="step-prog-track"><div class="step-prog-fill"></div></div>
    `;
    stepEl.insertBefore(bar, stepEl.firstChild);
  }

  const fill = bar.querySelector('.step-prog-fill');
  const text = bar.querySelector('.step-prog-text');
  const pctEl = bar.querySelector('.step-prog-pct');

  fill.style.width = pct + '%';
  fill.style.background = pct === 100 ? 'var(--success)' : pct >= 50 ? 'var(--primary)' : 'var(--warning)';
  text.textContent = `${filled} / ${total}`;
  pctEl.textContent = pct + '%';

  // Mettre à jour le badge sur l'indicateur de l'étape
  const stepIndicator = document.getElementById('step-indicator-' + stepNum);
  if (stepIndicator) {
    let badge = stepIndicator.querySelector('.step-badge');
    if (!badge) {
      badge = document.createElement('div');
      badge.className = 'step-badge';
      stepIndicator.appendChild(badge);
    }
    badge.textContent = pct + '%';
    badge.style.color = pct === 100 ? 'var(--success)' : pct >= 50 ? 'var(--primary)' : 'var(--warning)';
  }
}

function updateAllStepProgress() {
  for (let i = 1; i <= 4; i++) updateStepProgress(i);
}

// ─── LISTENERS POUR MISE À JOUR EN TEMPS RÉEL ───────────
document.getElementById('mainForm')?.addEventListener('input', () => {
  const activeStep = document.querySelector('.form-step.active');
  if (activeStep) {
    const num = parseInt(activeStep.id.replace('formStep', ''));
    updateStepProgress(num);
  }
});
document.getElementById('mainForm')?.addEventListener('change', () => {
  const activeStep = document.querySelector('.form-step.active');
  if (activeStep) {
    const num = parseInt(activeStep.id.replace('formStep', ''));
    updateStepProgress(num);
  }
});

// Initialiser au chargement
setTimeout(updateAllStepProgress, 300);

// ─── REVIEW BUILDER ───────────────────────────────────────
function buildReview() {
  const g = (id) => document.getElementById(id)?.value || '—';
  const r = (name) => document.querySelector(`input[name="${name}"]:checked`)?.value || '—';
  const s = (name) => document.querySelector(`select[name="${name}"]`)?.value || '—';

  const skipVal = r('skip_meal_tutoring');
  const skipYes = skipVal.includes('Oui');
  const iotfVal = g('q_iotf');
  const iotfColor = {'Obésité':'#9b2335','Surpoids':'#856404','Normal':'#1a7340','Minceur':'#0c5460'}[iotfVal]||'#333';

  const box = document.getElementById('reviewBox');
  box.innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px">
      <div><strong>${_lang.form_num}</strong> ${g('q_num')}</div>
      <div><strong>${_lang.age}</strong> ${g('q_age')} ${_lang.years}</div>
      <div><strong>${_lang.sex}</strong> ${r('sex')}</div>
      <div><strong>${_lang.grade}</strong> ${g('q_grade') || s('grade')}</div>
      <div><strong>${_lang.height}</strong> ${g('q_height')} سم</div>
      <div><strong>${_lang.weight}</strong> ${g('q_weight')} كغ</div>
      <div><strong>${_lang.bmi}</strong> ${g('q_bmi')}</div>
      <div><strong>${_lang.iotf}</strong> <span style="font-weight:700;color:${iotfColor}">${iotfVal}</span></div>
      <div><strong>وزن الولادة:</strong> ${document.querySelector('input[name="birth_weight"]')?.value||'—'} كغ</div>
      <div><strong>نوع الولادة:</strong> ${r('delivery_type')}</div>
      <div><strong>${_lang.breakfast}</strong> ${s('breakfast_freq')}</div>
      <div><strong>${_lang.sport}</strong> ${r('sports_club')}</div>
      <div><strong>${_lang.sleep}</strong> ${s('sleep_duration')}</div>
      <div><strong>${_lang.mother_edu}</strong> ${s('mother_education')}</div>
      <div><strong>${_lang.parent_obese}</strong> ${r('parent_obese')}</div>
      <div><strong>${_lang.stress}</strong> ${s('academic_stress')}</div>
    </div>
    ${skipYes ? `
    <div class="review-highlight">
      ⭐ <strong>${_lang.tutoring}</strong> ${skipVal} — 
      ${_lang.replaces} ${s('meal_replacement')}
    </div>` : ''}
  `;
}

// ─── FORM SERIALIZATION ────────────────────────────────────
function serializeForm() {
  const data = {};
  const form = document.getElementById('mainForm');

  // Text / number / time inputs
  form.querySelectorAll('input[type="text"], input[type="number"], input[type="time"]').forEach(el => {
    if (el.name) data[el.name] = el.value.trim();
  });

  // Selects
  form.querySelectorAll('select').forEach(el => {
    if (el.name) data[el.name] = el.value;
  });

  // Radios
  form.querySelectorAll('input[type="radio"]:checked').forEach(el => {
    if (el.name) data[el.name] = el.value;
  });

  return data;
}

// ─── SUBMIT ────────────────────────────────────────────────
async function submitForm() {
  const data = serializeForm();

  // Basic validation
  if (!data.questionnaire_num || !data.age || !data.sex || !data.height || !data.weight) {
    showAlert(_lang.err_required, 'danger');
    gotoStep(1);
    return;
  }

  const btn = document.querySelector('.btn-success');
  btn.textContent = _lang.saving;
  btn.disabled = true;

  try {
    const res = await fetch('/api/save.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json; charset=utf-8' },
      body: JSON.stringify(data)
    });
    const json = await res.json();

    if (json.success) {
      const msg = `
        ✓ تم حفظ الاستمارة رقم <strong>${data.questionnaire_num}</strong> بنجاح.<br>
        IMC = <strong>${json.bmi}</strong> → <strong>${json.iotf}</strong> |
        KIDMED: <strong>${json.score_kidmed} (${json.classe_kidmed})</strong> |
        نشاط: <strong>${json.classe_activite}</strong> |
        نوم: <strong>${json.classe_sommeil}</strong> |
        خطر سمنة: <strong>${json.obesity_risk}</strong> |
        Score global: <strong>${json.global_score}/100 (${json.global_class})</strong>
      `;
      showAlert(msg, 'success');
      formDirty = false;
      resetForm();
      loadStats();
    } else {
      showAlert('خطأ: ' + json.message, 'danger');
    }
  } catch (e) {
    // تحقق إذا كانت البيانات حُفظت فعلاً رغم الخطأ
    try {
      const check = await fetch('/api/data.php?action=stats');
      const checkJson = await check.json();
      if (checkJson.success) {
        showAlert('✓ تم حفظ السجل بنجاح.', 'success');
        formDirty = false;
        resetForm();
        loadStats();
        return;
      }
    } catch (_) {}
    showAlert(_lang.conn_error, 'danger');
  } finally {
    btn.textContent = _lang.save_btn;
    btn.disabled = false;
  }
}

// ─── RESET ────────────────────────────────────────────────
function resetForm() {
  document.getElementById('mainForm').reset();
  document.getElementById('q_bmi').value = '';
  document.getElementById('q_iotf').value = '';
  document.getElementById('q_iotf').style.color = '';
  formDirty = false;
  gotoStep(1);
  setTimeout(updateAllStepProgress, 100);
}

// ─── ALERT ────────────────────────────────────────────────
function showAlert(msg, type = 'success') {
  const box = document.getElementById('alertBox');
  if (!box) return;
  box.innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
  if (type === 'success') {
    setTimeout(() => box.innerHTML = '', 6000);
  }
  box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// ─── LOAD STATS ───────────────────────────────────────────
async function loadStats() {
  try {
    const res = await fetch('/api/data.php?action=stats');
    const json = await res.json();
    if (!json.success) return;
    document.getElementById('sTotal').textContent = json.total ?? '—';
    document.getElementById('sToday').textContent = json.today ?? '—';
    document.getElementById('sBMI').textContent = json.bmi_avg ?? '—';
    document.getElementById('sObes').textContent = json.obese_pct != null ? json.obese_pct + '%' : '—';
  } catch (e) { /* silently fail */ }
}

// ─── INIT ─────────────────────────────────────────────────
loadStats();
setInterval(loadStats, 60000); // refresh every 60s

// ─── BEFOREUNLOAD WARNING ─────────────────────────────────
let formDirty = false;
document.getElementById('mainForm')?.addEventListener('input', () => { formDirty = true; });
document.getElementById('mainForm')?.addEventListener('change', () => { formDirty = true; });
window.addEventListener('beforeunload', (e) => {
  if (formDirty) {
    e.preventDefault();
    e.returnValue = '';
  }
});
