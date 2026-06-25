<?php
/**
 * ════════════════════════════════════════════════════════════════════
 *  personal-survey/index.php  —  v3 (115+ questions, 6 étapes)
 *  Ajouts vs v2 :
 *   • SES (5 Q) — éducation parents, métier, foyer, sécurité alimentaire
 *   • Puberté (4 Q) — ménarche, mue voix, croissance, Tanner auto
 *   • FFQ algérien (11 items) — thé, harira, bourek, zlabia...
 *   • Tabac/Shisha/Vape (3 Q)
 *   • Social jetlag (2 Q) — coucher/réveil week-end
 *   • Sédentarité hors écran (2 Q) — étude, transport
 *   • Antibiotiques en bas âge (2 Q)
 * ════════════════════════════════════════════════════════════════════ */

require_once __DIR__ . '/../lang.php';
require_once __DIR__ . '/lang_ps.php';

$lng = currentLang();
?>
<!DOCTYPE html>
<html lang="<?= $lng ?>" dir="<?= langDir() ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= __('ps_site_name') ?> — <?= __('ps_tagline') ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/personal-survey/assets/style.css">
</head>
<body>

<nav class="ps-navbar">
  <div class="ps-nav-brand">
    <div class="ps-nav-titles">
      <span class="ps-nav-title"><?= __('ps_site_name') ?></span>
      <span class="ps-nav-subtitle"><?= __('ps_tagline') ?></span>
    </div>
  </div>
  <div class="ps-nav-actions">
    <div class="lang-switcher">
      <a href="?lang=ar" class="lang-btn <?= $lng==='ar'?'active':'' ?>">ع</a>
      <a href="?lang=fr" class="lang-btn <?= $lng==='fr'?'active':'' ?>">Fr</a>
      <a href="?lang=en" class="lang-btn <?= $lng==='en'?'active':'' ?>">En</a>
    </div>
    <a href="/login.php" class="ps-nav-link"><?= __('ps_back_to_main') ?></a>
  </div>
</nav>

<div class="ps-container">

<section class="ps-hero">
  <h1 class="ps-hero-title"><?= __('ps_welcome_title') ?></h1>
  <p class="ps-hero-intro"><?= __('ps_welcome_intro') ?></p>
  <div class="ps-privacy-card">
    <div class="ps-privacy-head">🔒 <?= __('ps_privacy_title') ?></div>
    <div class="ps-privacy-body"><?= __('ps_privacy_text') ?></div>
    <span class="ps-time-needed">⏱ 22-28 min</span>
  </div>
</section>

<?php
$steps = [['psx_step1','👤'],['psx_step2','👶'],['psx_step3','🍽'],['psx_step4','🧠'],['psx_step5','🏃'],['psx_step6','💚']];
?>
<div class="ps-steps">
  <?php foreach ($steps as $i => $st): ?>
    <div class="ps-step <?= $i===0?'active':'' ?>" id="ps-step-ind-<?= $i+1 ?>" onclick="psGotoStep(<?= $i+1 ?>)">
      <div class="ps-step-circle"><?= $st[1] ?></div>
      <div class="ps-step-label"><?= __($st[0]) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<form id="psForm" method="POST" action="/personal-survey/report.php" autocomplete="off">

<!-- ════════════ STEP 1 — Profile + SES + puberté ════════════ -->
<div class="ps-form-step active" id="psStep1">
  <div class="ps-card">
    <h2 class="ps-card-title">👤 <?= __('psx_step1') ?></h2>
    <div class="ps-grid cols3">
      <div class="ps-fg">
        <label><?= __('ps_age') ?> <span class="ps-req">*</span></label>
        <input type="number" name="age" min="8" max="80" required>
      </div>
      <div class="ps-fg">
        <label><?= __('ps_sex') ?> <span class="ps-req">*</span></label>
        <select name="sex" required>
          <option value=""><?= __('ps_choose') ?></option>
          <option value="Garçon"><?= __('ps_male') ?></option>
          <option value="Fille"><?= __('ps_female') ?></option>
        </select>
      </div>
      <div class="ps-fg">
        <label><?= __('psx_residence') ?></label>
        <select name="residence">
          <option value=""><?= __('ps_choose') ?></option>
          <option value="urban"><?= __('psx_urban') ?></option>
          <option value="periurban"><?= __('psx_periurban') ?></option>
          <option value="rural"><?= __('psx_rural') ?></option>
        </select>
      </div>
    </div>
    <div class="ps-grid cols3">
      <div class="ps-fg">
        <label><?= __('ps_height') ?> (cm) <span class="ps-req">*</span></label>
        <input type="number" name="height" min="100" max="220" step="0.1" required>
      </div>
      <div class="ps-fg">
        <label><?= __('ps_weight') ?> (kg) <span class="ps-req">*</span></label>
        <input type="number" name="weight" min="20" max="200" step="0.1" required>
      </div>
      <div class="ps-fg">
        <label><?= __('ps_bmi_preview') ?></label>
        <input type="text" id="ps_bmi" class="ps-computed" readonly>
      </div>
    </div>
    <div class="ps-grid">
      <div class="ps-fg">
        <label><?= __('psx_waist') ?></label>
        <input type="number" name="waist_cm" min="30" max="200" step="0.1">
        <small class="ps-card-note"><?= __('psx_waist_help') ?></small>
      </div>
      <div class="ps-fg">
        <label><?= __('ps_birth_weight') ?></label>
        <select name="birth_weight">
          <option value=""><?= __('psx_dont_know') ?></option>
          <option value="<2.5kg">&lt; 2.5 kg</option>
          <option value="2.5-3kg">2.5 – 3 kg</option>
          <option value="3-3.5kg">3 – 3.5 kg</option>
          <option value="3.5-4kg">3.5 – 4 kg</option>
          <option value=">4kg">&gt; 4 kg</option>
        </select>
      </div>
    </div>
  </div>

  <!-- SES -->
  <div class="ps-card">
    <h2 class="ps-card-title">🏠 <?= __('psv_ses_title') ?></h2>
    <p class="ps-card-note"><?= __('psv_ses_note') ?></p>
    <div class="ps-grid">
      <div class="ps-fg">
        <label><?= __('psv_mother_edu') ?></label>
        <select name="psv_mother_edu">
          <option value=""><?= __('psx_dont_know') ?></option>
          <option value="none"><?= __('psv_edu_none') ?></option>
          <option value="primary"><?= __('psv_edu_primary') ?></option>
          <option value="middle"><?= __('psv_edu_middle') ?></option>
          <option value="secondary"><?= __('psv_edu_secondary') ?></option>
          <option value="university"><?= __('psv_edu_university') ?></option>
        </select>
      </div>
      <div class="ps-fg">
        <label><?= __('psv_father_edu') ?></label>
        <select name="psv_father_edu">
          <option value=""><?= __('psx_dont_know') ?></option>
          <option value="none"><?= __('psv_edu_none') ?></option>
          <option value="primary"><?= __('psv_edu_primary') ?></option>
          <option value="middle"><?= __('psv_edu_middle') ?></option>
          <option value="secondary"><?= __('psv_edu_secondary') ?></option>
          <option value="university"><?= __('psv_edu_university') ?></option>
        </select>
      </div>
      <div class="ps-fg">
        <label><?= __('psv_father_work') ?></label>
        <select name="psv_father_work">
          <option value=""><?= __('psx_dont_know') ?></option>
          <option value="unemp"><?= __('psv_work_unemp') ?></option>
          <option value="informal"><?= __('psv_work_informal') ?></option>
          <option value="manual"><?= __('psv_work_manual') ?></option>
          <option value="employee"><?= __('psv_work_employee') ?></option>
          <option value="prof"><?= __('psv_work_prof') ?></option>
        </select>
      </div>
      <div class="ps-fg">
        <label><?= __('psv_mother_work') ?></label>
        <select name="psv_mother_work">
          <option value=""><?= __('psx_dont_know') ?></option>
          <option value="housewife"><?= __('psv_work_housewife') ?></option>
          <option value="informal"><?= __('psv_work_informal') ?></option>
          <option value="manual"><?= __('psv_work_manual') ?></option>
          <option value="employee"><?= __('psv_work_employee') ?></option>
          <option value="prof"><?= __('psv_work_prof') ?></option>
        </select>
      </div>
      <div class="ps-fg">
        <label><?= __('psv_siblings_n') ?></label>
        <input type="number" name="psv_siblings_n" min="0" max="15">
      </div>
    </div>

    <div class="ps-fg">
      <label><?= __('psv_household') ?></label>
      <div class="ps-checkbox-group">
        <?php
        $hh = [
          'own'=>'psv_house_own','car'=>'psv_house_car','internet'=>'psv_house_internet',
          'room'=>'psv_house_room','heater'=>'psv_house_heater',
        ];
        foreach ($hh as $v => $key): ?>
          <label class="ps-checkbox-opt">
            <input type="checkbox" name="psv_household[]" value="<?= $v ?>">
            <span><?= __($key) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="ps-fg">
      <label><?= __('psv_food_security') ?></label>
      <div class="ps-radio-group">
        <?php foreach (['never','rare','some','often'] as $v): ?>
          <label class="ps-radio-opt">
            <input type="radio" name="psv_food_security" value="<?= $v ?>"<?= $v==='never'?' checked':'' ?>>
            <span><?= __('psv_food_' . $v) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Puberty -->
  <div class="ps-card">
    <h2 class="ps-card-title">🌱 <?= __('psv_puberty_title') ?></h2>
    <p class="ps-card-note"><?= __('psv_puberty_note') ?></p>
    <div class="ps-grid">
      <div class="ps-fg">
        <label><?= __('psv_menarche') ?></label>
        <select name="psv_menarche">
          <option value=""><?= __('psx_dont_know') ?></option>
          <option value="not_yet"><?= __('psv_men_not_yet') ?></option>
          <option value="<10"><?= __('psv_men_lt10') ?></option>
          <option value="10_11"><?= __('psv_men_10_11') ?></option>
          <option value="12_13"><?= __('psv_men_12_13') ?></option>
          <option value="14_15"><?= __('psv_men_14_15') ?></option>
          <option value=">15"><?= __('psv_men_gt15') ?></option>
        </select>
      </div>
      <div class="ps-fg">
        <label><?= __('psv_voice_change') ?></label>
        <select name="psv_voice_change">
          <option value=""><?= __('psx_dont_know') ?></option>
          <option value="not_yet"><?= __('psv_voi_not_yet') ?></option>
          <option value="<12"><?= __('psv_voi_lt12') ?></option>
          <option value="12_13"><?= __('psv_voi_12_13') ?></option>
          <option value="14_15"><?= __('psv_voi_14_15') ?></option>
          <option value=">15"><?= __('psv_voi_gt15') ?></option>
        </select>
      </div>
      <div class="ps-fg">
        <label><?= __('psv_growth_spurt') ?></label>
        <select name="psv_growth_spurt">
          <option value=""><?= __('psx_dont_know') ?></option>
          <option value="8_10"><?= __('psv_spurt_8_10') ?></option>
          <option value="11_13"><?= __('psv_spurt_11_13') ?></option>
          <option value="14_16"><?= __('psv_spurt_14_16') ?></option>
          <option value="late"><?= __('psv_spurt_late') ?></option>
          <option value="adult"><?= __('psv_spurt_adult') ?></option>
        </select>
      </div>
      <div class="ps-fg">
        <label><?= __('psv_tanner_self') ?></label>
        <input type="number" name="psv_tanner_self" min="1" max="5">
      </div>
    </div>
  </div>

  <div class="ps-actions">
    <span></span>
    <button type="button" class="ps-btn ps-btn-primary" onclick="psGotoStep(2)"><?= __('ps_next') ?> →</button>
  </div>
</div>

<!-- ════════════ STEP 2 — Périnatal + famille + antibiotiques ════════════ -->
<div class="ps-form-step" id="psStep2">
  <div class="ps-card">
    <h2 class="ps-card-title">👶 <?= __('psx_perinatal_title') ?></h2>
    <p class="ps-card-note"><?= __('psx_perinatal_note') ?></p>
    <div class="ps-grid">
      <?php
      $perinatal = [
        ['ps_delivery_type', 'delivery_type', ['Voie basse'=>'ps_natural','Césarienne'=>'ps_caesarean']],
        ['psx_breastfed_type', 'psx_breastfed_type', ['exclusive'=>'psx_bf_exclusive','mixed'=>'psx_bf_mixed','formula'=>'psx_bf_formula']],
        ['psx_breastfed_duration', 'psx_breastfed_duration', ['jamais'=>'psx_bf_never','<1mo'=>'psx_bf_lt1','1-3mo'=>'psx_bf_1_3','3-6mo'=>'psx_bf_3_6','6-12mo'=>'psx_bf_6_12','>12mo'=>'psx_bf_gt12']],
        ['psx_solid_intro', 'psx_solid_intro', ['<4mo'=>'psx_solid_lt4','4-6mo'=>'psx_solid_4_6','>6mo'=>'psx_solid_gt6']],
      ];
      foreach ($perinatal as $p): ?>
        <div class="ps-fg">
          <label><?= __($p[0]) ?></label>
          <select name="<?= $p[1] ?>">
            <option value=""><?= __('psx_dont_know') ?></option>
            <?php foreach ($p[2] as $v => $k): ?>
              <option value="<?= $v ?>"><?= __($k) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endforeach; ?>

      <?php foreach (['psx_mother_smoked','psx_gest_diabetes','psx_mother_overweight_preg','psx_rapid_weight_gain'] as $f): ?>
        <div class="ps-fg">
          <label><?= __($f) ?></label>
          <select name="<?= $f ?>">
            <option value=""><?= __('psx_dont_know') ?></option>
            <option value="Oui"><?= __('ps_yes') ?></option>
            <option value="Non"><?= __('ps_no') ?></option>
          </select>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Antibiotiques -->
  <div class="ps-card">
    <h2 class="ps-card-title">💊 <?= __('psv_abx_title') ?></h2>
    <p class="ps-card-note"><?= __('psv_abx_note') ?></p>
    <div class="ps-grid">
      <div class="ps-fg">
        <label><?= __('psv_abx_under2') ?></label>
        <select name="psv_abx_under2">
          <option value="dontknow"><?= __('psx_dont_know') ?></option>
          <option value="none"><?= __('psv_abx_none') ?></option>
          <option value="few"><?= __('psv_abx_few') ?></option>
          <option value="many"><?= __('psv_abx_many') ?></option>
        </select>
      </div>
      <div class="ps-fg">
        <label><?= __('psv_abx_recent') ?></label>
        <select name="psv_abx_recent">
          <option value=""><?= __('ps_choose') ?></option>
          <option value="0_1"><?= __('psv_abx_0_1') ?></option>
          <option value="2_3"><?= __('psv_abx_2_3') ?></option>
          <option value="gt3"><?= __('psv_abx_gt3') ?></option>
        </select>
      </div>
    </div>
  </div>

  <!-- Family history -->
  <div class="ps-card">
    <h2 class="ps-card-title">👨‍👩‍👧 <?= __('psx_family_history_title') ?></h2>
    <div class="ps-grid">
      <?php
      $bmi_opts = ['under'=>'psx_bmi_under','normal'=>'psx_bmi_normal','over'=>'psx_bmi_over','obese'=>'psx_bmi_obese'];
      foreach (['psx_father_bmi','psx_mother_bmi'] as $f): ?>
        <div class="ps-fg">
          <label><?= __($f) ?></label>
          <select name="<?= $f ?>">
            <option value=""><?= __('psx_dont_know') ?></option>
            <?php foreach ($bmi_opts as $v => $k): ?><option value="<?= $v ?>"><?= __($k) ?></option><?php endforeach; ?>
          </select>
        </div>
      <?php endforeach;
      foreach (['psx_siblings_obese','psx_family_t2d','psx_family_htn','psx_family_chol','psx_family_thyroid'] as $f): ?>
        <div class="ps-fg">
          <label><?= __($f) ?></label>
          <select name="<?= $f ?>">
            <option value=""><?= __('psx_dont_know') ?></option>
            <option value="Oui"><?= __('ps_yes') ?></option>
            <option value="Non"><?= __('ps_no') ?></option>
          </select>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="ps-actions">
    <button type="button" class="ps-btn" onclick="psGotoStep(1)">← <?= __('ps_prev') ?></button>
    <button type="button" class="ps-btn ps-btn-primary" onclick="psGotoStep(3)"><?= __('ps_next') ?> →</button>
  </div>
</div>

<!-- ════════════ STEP 3 — UPF + FFQ + Algerian dishes ════════════ -->
<?php
$upf_items = [
  'upf_soda'=>'psx_upf_soda','upf_energy'=>'psx_upf_energy','upf_juices'=>'psx_upf_juices',
  'upf_cookies'=>'psx_upf_cookies','upf_candy'=>'psx_upf_candy','upf_icecream'=>'psx_upf_icecream',
  'upf_pizza'=>'psx_upf_pizza','upf_ready'=>'psx_upf_ready','upf_chips'=>'psx_upf_chips',
  'upf_meat'=>'psx_upf_meat','upf_cereals'=>'psx_upf_cereals','upf_yogurt'=>'psx_upf_yogurt',
  'upf_sauces'=>'psx_upf_sauces','upf_white_bread'=>'psx_upf_white_bread','upf_fastfood'=>'psx_upf_fastfood',
];
$upf_freq_vals = ['Jamais','1-3/sem','4-6/sem','Tous les jours','2-3/jour'];
$upf_freq_lbl  = $lng==='ar'?['أبداً','1-3/أس','4-6/أس','يومياً','2-3/يوم']:($lng==='fr'?['Jamais','1-3/sem','4-6/sem','Tous les j.','2-3/j']:['Never','1-3/wk','4-6/wk','Daily','2-3/day']);

$ffq_groups = [
  'fruits_frais'=>'ffq_fruits_frais','legumes_cuits'=>'ffq_legumes_cuits','legumes_crus'=>'ffq_legumes_crus',
  'legumineuses'=>'ffq_legumineuses','poisson'=>'ffq_poisson','lait'=>'ffq_lait','oeufs'=>'ffq_oeufs',
  'viande_rouge'=>'ffq_viande_rouge','huile_olive'=>'ffq_huile_olive','couscous'=>'ffq_couscous',
];
$weekly_vals = ['Jamais','<1/mois','1-3/mois','1-2/sem','3-4/sem','5-6/sem','Tous les jours'];
$weekly_lbl  = $lng==='ar'?['أبداً','<1/شهر','1-3/شهر','1-2/أس','3-4/أس','5-6/أس','يومياً']:($lng==='fr'?['Jamais','<1/mois','1-3/mois','1-2/sem','3-4/sem','5-6/sem','Tous les j.']:['Never','<1/mo','1-3/mo','1-2/wk','3-4/wk','5-6/wk','Daily']);

$dz_items = [
  'psv_dz_harira'=>'psv_dz_harira','psv_dz_bourek'=>'psv_dz_bourek','psv_dz_mhajeb'=>'psv_dz_mhajeb',
  'psv_dz_zlabia'=>'psv_dz_zlabia','psv_dz_garantita'=>'psv_dz_garantita','psv_dz_lham_dlou'=>'psv_dz_lham_dlou',
  'psv_dz_leben'=>'psv_dz_leben','psv_dz_dates'=>'psv_dz_dates','psv_dz_olives'=>'psv_dz_olives',
  'psv_dz_kefta_fry'=>'psv_dz_kefta_fry','psv_dz_smoothies'=>'psv_dz_smoothies',
];
?>
<div class="ps-form-step" id="psStep3">
  <div class="ps-card">
    <h2 class="ps-card-title">🍽 <?= __('psx_upf_title') ?></h2>
    <p class="ps-card-note"><?= __('psx_upf_note') ?></p>
    <div class="ps-table-scroll">
    <table class="ps-ffq-table">
      <thead><tr><th class="ps-ffq-food-col"><?= __('ps_food_col') ?></th>
        <?php foreach ($upf_freq_lbl as $l): ?><th><?= $l ?></th><?php endforeach; ?>
      </tr></thead>
      <tbody>
        <?php foreach ($upf_items as $name => $key): ?>
          <tr><td class="ps-ffq-food-col ps-ffq-food"><?= __($key) ?></td>
            <?php foreach ($upf_freq_vals as $i => $f): ?>
              <td><input type="radio" name="<?= $name ?>" value="<?= $f ?>"<?= $i===0?' checked':'' ?>></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

  <div class="ps-card">
    <h2 class="ps-card-title">🥗 <?= __('ps_s2_title') ?></h2>
    <p class="ps-card-note"><?= __('ps_ffq_note') ?></p>
    <div class="ps-table-scroll">
    <table class="ps-ffq-table">
      <thead><tr><th class="ps-ffq-food-col"><?= __('ps_food_col') ?></th>
        <?php foreach ($weekly_lbl as $l): ?><th><?= $l ?></th><?php endforeach; ?>
      </tr></thead>
      <tbody>
        <?php foreach ($ffq_groups as $name => $key): ?>
          <tr><td class="ps-ffq-food-col ps-ffq-food"><?= __($key) ?></td>
            <?php foreach ($weekly_vals as $i => $f): ?>
              <td><input type="radio" name="<?= $name ?>" value="<?= $f ?>"<?= $i===0?' checked':'' ?>></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

  <!-- Algerian dishes -->
  <div class="ps-card">
    <h2 class="ps-card-title">🇩🇿 <?= __('psv_alg_title') ?></h2>
    <p class="ps-card-note"><?= __('psv_alg_note') ?></p>

    <div class="ps-fg">
      <label><?= __('psv_dz_mint_tea') ?></label>
      <div class="ps-radio-group">
        <?php foreach (['0','1_2','3_4','5_6','gt6'] as $v): ?>
          <label class="ps-radio-opt">
            <input type="radio" name="psv_dz_mint_tea" value="<?= $v ?>"<?= $v==='0'?' checked':'' ?>>
            <span><?= __('psv_tea_' . $v) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="ps-table-scroll">
    <table class="ps-ffq-table">
      <thead><tr><th class="ps-ffq-food-col"><?= __('ps_food_col') ?></th>
        <?php foreach ($weekly_lbl as $l): ?><th><?= $l ?></th><?php endforeach; ?>
      </tr></thead>
      <tbody>
        <?php foreach ($dz_items as $name => $key): ?>
          <tr><td class="ps-ffq-food-col ps-ffq-food"><?= __($key) ?></td>
            <?php foreach ($weekly_vals as $i => $f): ?>
              <td><input type="radio" name="<?= $name ?>" value="<?= $f ?>"<?= $i===0?' checked':'' ?>></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

  <div class="ps-actions">
    <button type="button" class="ps-btn" onclick="psGotoStep(2)">← <?= __('ps_prev') ?></button>
    <button type="button" class="ps-btn ps-btn-primary" onclick="psGotoStep(4)"><?= __('ps_next') ?> →</button>
  </div>
</div>

<!-- ════════════ STEP 4 — Eating behaviors (inchangé v2) ════════════ -->
<div class="ps-form-step" id="psStep4">
  <div class="ps-card">
    <h2 class="ps-card-title">🧠 <?= __('psx_step4') ?></h2>
    <div class="ps-grid">
      <?php
      $eb_selects = [
        ['psx_eating_speed','eating_speed',['very_slow'=>'psx_speed_very_slow','slow'=>'psx_speed_slow','normal'=>'psx_speed_normal','fast'=>'psx_speed_fast','very_fast'=>'psx_speed_very_fast']],
        ['psx_portion_size','portion_size',['smaller'=>'psx_portion_smaller','same'=>'psx_portion_same','bigger'=>'psx_portion_bigger','much_bigger'=>'psx_portion_much_bigger']],
        ['psx_second_serving','second_serving',['Toujours'=>'ps_always','Souvent'=>'ps_often','Parfois'=>'ps_sometimes','Rarement'=>'ps_rarely','Jamais'=>'ps_never']],
        ['psx_eat_screen','eat_screen',['always'=>'psx_screen_always','often'=>'psx_screen_often','sometimes'=>'psx_screen_sometimes','rarely'=>'psx_screen_rarely','never'=>'psx_screen_never']],
        ['psx_late_eating','late_eating',['daily'=>'psx_late_daily','freq'=>'psx_late_freq','sometimes'=>'psx_late_sometimes','rare'=>'psx_late_rare']],
        ['psx_outside_meals','outside_meals',['0'=>'0','1'=>'1','2_3'=>'2 – 3','4_6'=>'4 – 6','gt7'=>'7 +']],
        ['psx_bread_per_day','bread_per_day',['lt1'=>'psx_bread_lt1','1_2'=>'1 – 2','3_4'=>'3 – 4','gt5'=>'5 +']],
        ['psx_sugar_drinks','sugar_drinks',['0'=>'0','1_3'=>'1 – 3','4_6'=>'4 – 6','gt6'=>'6 +']],
        ['psx_cooking_method','cooking_method',['fried'=>'psx_cook_fried','grilled'=>'psx_cook_grilled','baked'=>'psx_cook_baked','steamed'=>'psx_cook_steamed','slow'=>'psx_cook_slow']],
        ['ps_breakfast','breakfast_freq',['Tous les jours'=>'ps_everyday','4-6/sem'=>'4 – 6 / sem','1-3/sem'=>'1 – 3 / sem','Jamais'=>'ps_never']],
        ['ps_water_intake','water_intake',['<0.5L'=>'< 0.5 L','0.5-1L'=>'0.5 – 1 L','1-1.5L'=>'1 – 1.5 L','1.5-2L'=>'1.5 – 2 L','>2L'=>'> 2 L']],
        ['psx_ramadan','ramadan',['no'=>'psx_ramadan_no','partial'=>'psx_ramadan_partial','yes'=>'psx_ramadan_yes']],
        ['psx_ramadan_change','ramadan_change',['unknown'=>'psx_ram_unknown','lost'=>'psx_ram_lost','stable'=>'psx_ram_stable','gained'=>'psx_ram_gained']],
      ];
      foreach ($eb_selects as $s): ?>
        <div class="ps-fg">
          <label><?= __($s[0]) ?></label>
          <select name="<?= $s[1] ?>">
            <option value=""><?= __('ps_choose') ?></option>
            <?php foreach ($s[2] as $v => $k): ?>
              <option value="<?= $v ?>"><?= strpos($k,'_')!==false&&!is_numeric(substr($k,0,1))?__($k):$k ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="ps-actions">
    <button type="button" class="ps-btn" onclick="psGotoStep(3)">← <?= __('ps_prev') ?></button>
    <button type="button" class="ps-btn ps-btn-primary" onclick="psGotoStep(5)"><?= __('ps_next') ?> →</button>
  </div>
</div>

<!-- ════════════ STEP 5 — Activity + screens + sleep + jetlag + sitting ════════════ -->
<?php
$st_buckets = ['lt10','10_30','30_60','1_2','2_4','4_6','gt6'];
$st_labels  = ['psx_st_lt10','psx_st_10_30','psx_st_30_60','psx_st_1_2','psx_st_2_4','psx_st_4_6','psx_st_gt6'];
$screens = ['screen_phone'=>'psx_screen_phone_social','screen_tv'=>'psx_screen_tv_x','screen_games'=>'psx_screen_games_x','screen_computer'=>'psx_screen_pc_x'];
?>
<div class="ps-form-step" id="psStep5">
  <div class="ps-card">
    <h2 class="ps-card-title">📺 <?= __('ps_s4_title') ?></h2>
    <p class="ps-card-note"><?= __('psx_screen_granular_help') ?></p>
    <div class="ps-table-scroll">
    <table class="ps-ffq-table">
      <thead><tr><th class="ps-ffq-food-col"><?= __('ps_food_col') ?></th>
        <?php foreach ($st_labels as $lk): ?><th><?= __($lk) ?></th><?php endforeach; ?>
      </tr></thead>
      <tbody>
        <?php foreach ($screens as $name => $key): ?>
          <tr><td class="ps-ffq-food-col ps-ffq-food"><?= __($key) ?></td>
            <?php foreach ($st_buckets as $i => $b): ?>
              <td><input type="radio" name="<?= $name ?>" value="<?= $b ?>"<?= $i===0?' checked':'' ?>></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>

    <div class="ps-grid" style="margin-top:14px">
      <div class="ps-fg">
        <label><?= __('psx_screen_in_bed') ?></label>
        <select name="screen_before_sleep">
          <option value=""><?= __('ps_choose') ?></option>
          <option value="<30min"><?= __('ps_none') ?> / &lt; 30 min</option>
          <option value="30-60min">30 – 60 min</option>
          <option value=">1h">&gt; 1 h</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Non-screen sitting -->
  <div class="ps-card">
    <h2 class="ps-card-title">🪑 <?= __('psv_sitting_title') ?></h2>
    <p class="ps-card-note"><?= __('psv_sitting_note') ?></p>
    <div class="ps-grid">
      <div class="ps-fg">
        <label><?= __('psv_study_hours') ?></label>
        <select name="psv_study_hours">
          <option value=""><?= __('ps_choose') ?></option>
          <?php foreach (['lt2','2_4','4_6','6_8','gt8'] as $v): ?>
            <option value="<?= $v ?>"><?= __('psv_sit_' . $v) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="ps-fg">
        <label><?= __('psv_transport_min') ?></label>
        <select name="psv_transport_min">
          <option value=""><?= __('ps_choose') ?></option>
          <?php foreach (['lt15','15_30','30_60','1_2h','gt2h'] as $v): ?>
            <option value="<?= $v ?>"><?= __('psv_t_' . $v) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>

  <div class="ps-card">
    <h2 class="ps-card-title">🏃 <?= __('ps_ind_activity') ?></h2>
    <div class="ps-grid">
      <div class="ps-fg">
        <label><?= __('psx_mvpa') ?></label>
        <select name="mvpa">
          <option value=""><?= __('ps_choose') ?></option>
          <?php foreach (['none','lt30','30_60','1_2','gt2'] as $v): ?>
            <option value="<?= $v ?>"><?= __('psx_mvpa_' . $v) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="ps-fg">
        <label><?= __('ps_active_days') ?></label>
        <select name="active_days_week">
          <option value="">-</option>
          <?php for ($i=0;$i<=7;$i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
        </select>
      </div>
      <div class="ps-fg">
        <label><?= __('ps_sports_club') ?></label>
        <select name="sports_club">
          <option value=""><?= __('ps_choose') ?></option>
          <option value="Oui"><?= __('ps_yes') ?></option>
          <option value="Non"><?= __('ps_no') ?></option>
        </select>
      </div>
      <div class="ps-fg">
        <label><?= __('ps_transport') ?></label>
        <select name="transport">
          <option value=""><?= __('ps_choose') ?></option>
          <option value="Marche"><?= __('ps_walking') ?></option>
          <option value="Vélo"><?= __('ps_bicycle') ?></option>
          <option value="Bus"><?= __('ps_bus') ?></option>
          <option value="Voiture"><?= __('ps_car') ?></option>
        </select>
      </div>
      <div class="ps-fg">
        <label><?= __('psx_can_walk30') ?></label>
        <select name="can_walk30">
          <option value=""><?= __('ps_choose') ?></option>
          <option value="Oui"><?= __('ps_yes') ?></option>
          <option value="Non"><?= __('ps_no') ?></option>
        </select>
      </div>
      <div class="ps-fg">
        <label><?= __('psx_fitness_self') ?></label>
        <input type="number" name="fitness_self" min="1" max="10">
      </div>
    </div>
  </div>

  <div class="ps-card">
    <h2 class="ps-card-title">😴 <?= __('ps_ind_sleep') ?></h2>
    <div class="ps-grid">
      <div class="ps-fg"><label><?= __('ps_bedtime') ?></label><input type="time" name="bedtime" value="23:00"></div>
      <div class="ps-fg"><label><?= __('ps_waketime') ?></label><input type="time" name="waketime" value="07:00"></div>
      <!-- NEW: weekend bedtime/wake (social jetlag) -->
      <div class="ps-fg"><label><?= __('psv_bedtime_we') ?></label><input type="time" name="psv_bedtime_we" value="00:30"></div>
      <div class="ps-fg"><label><?= __('psv_waketime_we') ?></label><input type="time" name="psv_waketime_we" value="09:00"></div>
      <div class="ps-fg">
        <label><?= __('ps_sleep_duration') ?></label>
        <select name="sleep_duration">
          <option value=""><?= __('ps_choose') ?></option>
          <option value="<6h">&lt; 6 h</option>
          <option value="6-7h">6 – 7 h</option>
          <option value="7-8h">7 – 8 h</option>
          <option value=">8h">&gt; 8 h</option>
        </select>
      </div>
      <div class="ps-fg"><label><?= __('psx_sleep_quality') ?></label><input type="number" name="sleep_quality" min="1" max="10"></div>
      <?php foreach (['psx_snoring','psx_daytime_sleepy'] as $f): ?>
        <div class="ps-fg">
          <label><?= __($f) ?></label>
          <select name="<?= str_replace('psx_','',$f) ?>">
            <option value=""><?= __('psx_dont_know') ?></option>
            <option value="Oui"><?= __('ps_yes') ?></option>
            <option value="Non"><?= __('ps_no') ?></option>
          </select>
        </div>
      <?php endforeach; ?>
      <?php foreach (['ps_insomnia','ps_wake_exhausted'] as $f): ?>
        <div class="ps-fg">
          <label><?= __($f) ?></label>
          <select name="<?= str_replace('ps_','',$f) ?>">
            <option value=""><?= __('ps_choose') ?></option>
            <option value="Non"><?= __('ps_no') ?></option>
            <option value="Oui, parfois"><?= __('ps_sometimes') ?></option>
            <option value="Oui, souvent"><?= __('ps_often') ?></option>
          </select>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="ps-actions">
    <button type="button" class="ps-btn" onclick="psGotoStep(4)">← <?= __('ps_prev') ?></button>
    <button type="button" class="ps-btn ps-btn-primary" onclick="psGotoStep(6)"><?= __('ps_next') ?> →</button>
  </div>
</div>

<!-- ════════════ STEP 6 — Psychosocial + SCOFF + médical + tabac ════════════ -->
<div class="ps-form-step" id="psStep6">
  <div class="ps-card">
    <h2 class="ps-card-title">💚 <?= __('psx_psycho_title') ?></h2>
    <p class="ps-card-note"><?= __('psx_psycho_note') ?></p>
    <?php
    $phq_opts = ['none'=>'psx_phq_none','some'=>'psx_phq_some','morehalf'=>'psx_phq_morehalf','almost_every'=>'psx_phq_almost_every'];
    foreach (['phq_down'=>'psx_phq_down','phq_interest'=>'psx_phq_interest','gad_worry'=>'psx_gad_worry'] as $name => $key): ?>
      <div class="ps-fg">
        <label><?= __($key) ?></label>
        <div class="ps-radio-group">
          <?php foreach ($phq_opts as $v => $l): ?>
            <label class="ps-radio-opt">
              <input type="radio" name="<?= $name ?>" value="<?= $v ?>"<?= $v==='none'?' checked':'' ?>>
              <span><?= __($l) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="ps-grid">
      <?php
      $psyc = [
        ['psx_body_satisfaction','body_satisfaction',['very_sat'=>'psx_bs_very_sat','sat'=>'psx_bs_sat','neutral'=>'psx_bs_neutral','unsat'=>'psx_bs_unsat','very_unsat'=>'psx_bs_very_unsat']],
      ];
      foreach ($psyc as $p): ?>
        <div class="ps-fg">
          <label><?= __($p[0]) ?></label>
          <select name="<?= $p[1] ?>">
            <option value=""><?= __('ps_choose') ?></option>
            <?php foreach ($p[2] as $v => $k): ?><option value="<?= $v ?>"><?= __($k) ?></option><?php endforeach; ?>
          </select>
        </div>
      <?php endforeach;
      foreach (['psx_weight_teasing'=>'weight_teasing','psx_social_compare'=>'social_compare','psx_lonely'=>'lonely'] as $k => $n): ?>
        <div class="ps-fg">
          <label><?= __($k) ?></label>
          <select name="<?= $n ?>">
            <option value=""><?= __('ps_choose') ?></option>
            <option value="never"><?= __('psx_wt_never') ?></option>
            <option value="some"><?= __('psx_wt_some') ?></option>
            <option value="often"><?= __('psx_wt_often') ?></option>
          </select>
        </div>
      <?php endforeach; ?>
      <div class="ps-fg">
        <label><?= __('ps_academic_stress') ?></label>
        <select name="academic_stress">
          <option value=""><?= __('ps_choose') ?></option>
          <option value="Faible"><?= __('ps_low') ?></option>
          <option value="Moyen"><?= __('ps_moderate') ?></option>
          <option value="Élevé"><?= __('ps_high') ?></option>
        </select>
      </div>
    </div>
  </div>

  <!-- Smoking -->
  <div class="ps-card">
    <h2 class="ps-card-title">🚬 <?= __('psv_smoking_title') ?></h2>
    <p class="ps-card-note"><?= __('psv_smoking_note') ?></p>
    <div class="ps-fg">
      <label><?= __('psv_smoke_status') ?></label>
      <div class="ps-radio-group">
        <?php foreach (['none','cig','shisha','vape','multi'] as $v): ?>
          <label class="ps-radio-opt">
            <input type="radio" name="psv_smoke_status" value="<?= $v ?>"<?= $v==='none'?' checked':'' ?>>
            <span><?= __('psv_smoke_' . $v) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="ps-fg">
      <label><?= __('psv_smoke_freq') ?></label>
      <div class="ps-radio-group">
        <?php foreach (['never','tried','occasion','weekly','daily'] as $v): ?>
          <label class="ps-radio-opt">
            <input type="radio" name="psv_smoke_freq" value="<?= $v ?>"<?= $v==='never'?' checked':'' ?>>
            <span><?= __('psv_sf_' . $v) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="ps-fg">
      <label><?= __('psv_smoke_around') ?></label>
      <div class="ps-radio-group">
        <label class="ps-radio-opt"><input type="radio" name="psv_smoke_around" value="Oui"><span><?= __('ps_yes') ?></span></label>
        <label class="ps-radio-opt"><input type="radio" name="psv_smoke_around" value="Non" checked><span><?= __('ps_no') ?></span></label>
      </div>
    </div>
  </div>

  <!-- SCOFF -->
  <div class="ps-card">
    <h2 class="ps-card-title">🍴 <?= __('psx_scoff_title') ?></h2>
    <p class="ps-card-note"><?= __('psx_scoff_note') ?></p>
    <?php foreach (['scoff_sick'=>'psx_scoff_sick','scoff_control'=>'psx_scoff_control','scoff_onestone'=>'psx_scoff_onestone','scoff_fat'=>'psx_scoff_fat','scoff_food'=>'psx_scoff_food'] as $name => $key): ?>
      <div class="ps-fg">
        <label><?= __($key) ?></label>
        <div class="ps-radio-group">
          <label class="ps-radio-opt"><input type="radio" name="<?= $name ?>" value="Oui"><span><?= __('ps_yes') ?></span></label>
          <label class="ps-radio-opt"><input type="radio" name="<?= $name ?>" value="Non" checked><span><?= __('ps_no') ?></span></label>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Médicaments -->
  <div class="ps-card">
    <h2 class="ps-card-title">💊 <?= __('psx_meds_title') ?></h2>
    <p class="ps-card-note"><?= __('psx_meds_taken') ?></p>
    <?php foreach (['med_cortico'=>'psx_med_cortico','med_antidep'=>'psx_med_antidep','med_antipsy'=>'psx_med_antipsy','med_contracep'=>'psx_med_contracep','med_other'=>'psx_med_other'] as $name => $key): ?>
      <div class="ps-fg">
        <label><?= __($key) ?></label>
        <div class="ps-radio-group">
          <label class="ps-radio-opt"><input type="radio" name="<?= $name ?>" value="Oui"><span><?= __('ps_yes') ?></span></label>
          <label class="ps-radio-opt"><input type="radio" name="<?= $name ?>" value="Non" checked><span><?= __('ps_no') ?></span></label>
        </div>
      </div>
    <?php endforeach; ?>
    <div class="ps-grid">
      <div class="ps-fg">
        <label><?= __('psx_conditions') ?></label>
        <select name="condition">
          <option value="none"><?= __('psx_cond_none') ?></option>
          <option value="thyroid"><?= __('psx_cond_thyroid') ?></option>
          <option value="asthma"><?= __('psx_cond_asthma') ?></option>
          <option value="diabetes"><?= __('psx_cond_diab') ?></option>
          <option value="pcos"><?= __('psx_cond_pcos') ?></option>
          <option value="depression"><?= __('psx_cond_depression') ?></option>
        </select>
      </div>
      <div class="ps-fg">
        <label><?= __('psx_weight_change6m') ?></label>
        <select name="weight_change6m">
          <option value=""><?= __('ps_choose') ?></option>
          <?php foreach (['stable'=>'psx_wc_stable','gain'=>'psx_wc_gain','gain_lot'=>'psx_wc_gain_lot','lost'=>'psx_wc_lost','lost_lot'=>'psx_wc_lost_lot'] as $v => $k): ?>
            <option value="<?= $v ?>"><?= __($k) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>

  <div class="ps-disclaimer-final"><?= __('ps_disclaimer') ?></div>

  <div class="ps-actions">
    <button type="button" class="ps-btn" onclick="psGotoStep(5)">← <?= __('ps_prev') ?></button>
    <button type="button" class="ps-btn ps-btn-warn" onclick="psResetForm()"><?= __('ps_reset') ?></button>
    <button type="submit" class="ps-btn ps-btn-success"><?= __('ps_submit') ?> ✓</button>
  </div>
</div>

</form>
</div>

<script src="/personal-survey/assets/app.js"></script>
</body>
</html>
