/* ════════════════════════════════════════════════════════════════════
 *  personal-survey/assets/app.js
 *  Logique client : navigation entre étapes, calcul IMC en direct,
 *  validation simple, reset, animation de soumission.
 *  Aucune dépendance externe.
 * ════════════════════════════════════════════════════════════════════ */

(function () {
  'use strict';

  // ─── État ──────────────────────────────────────────────────────
  let currentStep = 1;
  const totalSteps = 6;

  // ─── Navigation entre étapes ───────────────────────────────────
  function psGotoStep(n) {
    if (n < 1 || n > totalSteps) return;
    // Valider avant d'avancer (jamais bloquant en arrière)
    if (n > currentStep && !validateStep(currentStep)) return;

    document.querySelectorAll('.ps-form-step').forEach(el => el.classList.remove('active'));
    const target = document.getElementById('psStep' + n);
    if (target) target.classList.add('active');

    document.querySelectorAll('.ps-step').forEach((el, idx) => {
      el.classList.remove('active', 'done');
      const step = idx + 1;
      if (step < n) el.classList.add('done');
      if (step === n) el.classList.add('active');
    });

    currentStep = n;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
  window.psGotoStep = psGotoStep;

  // ─── Validation par étape ──────────────────────────────────────
  function validateStep(step) {
    const errors = [];
    if (step === 1) {
      const age    = parseFloat(getVal('age'));
      const sex    = getVal('sex');
      const height = parseFloat(getVal('height'));
      const weight = parseFloat(getVal('weight'));
      if (!age || !sex || !height || !weight) {
        errors.push(__('required'));
      } else {
        if (age < 8 || age > 80)        errors.push(__('age'));
        if (height < 100 || height > 220) errors.push(__('height'));
        if (weight < 20 || weight > 200)  errors.push(__('weight'));
      }
    }
    if (errors.length) {
      showAlert(errors.join('\n'));
      return false;
    }
    clearAlert();
    return true;
  }

  // ─── Messages d'erreur localisés ───────────────────────────────
  const lang = (document.documentElement.lang || 'fr').slice(0, 2);
  const msgs = {
    ar: { required: 'يرجى ملء الحقول الإلزامية: العمر، الجنس، الطول، الوزن.',
          age: 'العمر يجب أن يكون بين 8 و 80 سنة.',
          height: 'الطول يجب أن يكون بين 100 و 220 سم.',
          weight: 'الوزن يجب أن يكون بين 20 و 200 كغ.',
          confirm_reset: 'هل تريد فعلاً مسح كل الإجابات والبدء من جديد؟',
          submitting: 'جاري إعداد التقرير…' },
    fr: { required: 'Veuillez remplir : âge, sexe, taille, poids.',
          age: "L'âge doit être entre 8 et 80 ans.",
          height: 'La taille doit être entre 100 et 220 cm.',
          weight: 'Le poids doit être entre 20 et 200 kg.',
          confirm_reset: 'Voulez-vous vraiment effacer toutes vos réponses ?',
          submitting: 'Génération du rapport…' },
    en: { required: 'Please fill: age, sex, height, weight.',
          age: 'Age must be between 8 and 80 years.',
          height: 'Height must be between 100 and 220 cm.',
          weight: 'Weight must be between 20 and 200 kg.',
          confirm_reset: 'Really clear all your answers and start over?',
          submitting: 'Generating report…' }
  };
  function __(k) { return (msgs[lang] || msgs.fr)[k] || k; }

  // ─── Helpers DOM ───────────────────────────────────────────────
  function getVal(name) {
    const el = document.querySelector('[name="' + name + '"]:checked')
            || document.querySelector('[name="' + name + '"]');
    return el ? (el.value || '').trim() : '';
  }

  function showAlert(text) {
    let alert = document.getElementById('psAlertTop');
    if (!alert) {
      alert = document.createElement('div');
      alert.id = 'psAlertTop';
      alert.className = 'ps-alert ps-alert-danger';
      const form = document.getElementById('psForm');
      if (form) form.insertBefore(alert, form.firstChild);
    }
    alert.textContent = '⚠ ' + text;
    alert.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
  function clearAlert() {
    const alert = document.getElementById('psAlertTop');
    if (alert) alert.style.display = 'none';
  }

  // ─── Calcul IMC en direct ──────────────────────────────────────
  function updateBMI() {
    const h = parseFloat(getVal('height'));
    const w = parseFloat(getVal('weight'));
    const out = document.getElementById('ps_bmi');
    if (!out) return;
    if (h >= 80 && w >= 10) {
      const bmi = w / Math.pow(h / 100, 2);
      out.value = bmi.toFixed(1);
    } else {
      out.value = '';
    }
  }
  document.addEventListener('input', function (e) {
    if (e.target && (e.target.name === 'height' || e.target.name === 'weight')) {
      updateBMI();
    }
  });

  // ─── Reset complet ─────────────────────────────────────────────
  function psResetForm() {
    if (!confirm(__('confirm_reset'))) return;
    const form = document.getElementById('psForm');
    if (!form) return;
    form.reset();
    updateBMI();
    psGotoStep(1);
    clearAlert();
  }
  window.psResetForm = psResetForm;

  // ─── Animation de soumission ───────────────────────────────────
  function attachSubmit() {
    const form = document.getElementById('psForm');
    if (!form) return;
    form.addEventListener('submit', function (e) {
      if (!validateStep(1)) {
        e.preventDefault();
        psGotoStep(1);
        return;
      }
      const btn = form.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.dataset.orig = btn.textContent;
        btn.textContent = __('submitting');
      }
    });
  }

  // ─── Init ──────────────────────────────────────────────────────
  // Note : les .ps-step ont déjà un onclick inline dans index.php,
  // donc pas besoin d'attacher de listener supplémentaire.
  document.addEventListener('DOMContentLoaded', function () {
    updateBMI();
    attachSubmit();
    // Ne pas appeler psGotoStep(1) : laisser le HTML PHP gérer
    // l'état initial (évite le clignotement au chargement).
  });

})();
