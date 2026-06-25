<?php
require_once 'config.php';
require_once 'lang.php';
$session = checkSession();
?>
<!DOCTYPE html>
<html lang="<?=currentLang()?>" dir="<?=langDir()?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= SITE_NAME ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<!-- NAVBAR -->
<?php include 'navbar.php'; ?>

<!-- STATS BAR -->
<div class="stats-bar" id="statsBar">
  <div class="stat-card"><div class="stat-num" id="sTotal">—</div><div class="stat-lbl"><?=__('idx_total_records')?></div></div>
  <div class="stat-card"><div class="stat-num" id="sToday">—</div><div class="stat-lbl"><?=__('idx_today_records')?></div></div>
  <div class="stat-card"><div class="stat-num" id="sObes">—</div><div class="stat-lbl"><?=__('idx_obesity_rate')?></div></div>
  <div class="stat-card"><div class="stat-num" id="sBMI">—</div><div class="stat-lbl"><?=__('idx_avg_bmi')?></div></div>
</div>

<div class="container">
<div id="alertBox"></div>

<!-- PROGRESS STEPS -->
<div class="progress-steps">
  <?php $steps = [__('idx_step1'),__('idx_step2'),__('idx_step3'),__('idx_step4'),__('idx_step5')]; ?>
  <?php foreach ($steps as $i => $label): ?>
  <div class="step <?= $i===0?'active':'' ?>" id="step-indicator-<?= $i+1 ?>" onclick="gotoStep(<?= $i+1 ?>)">
    <div class="step-circle"><?= $i+1 ?></div>
    <div class="step-label"><?= $label ?></div>
  </div>
  <?php if ($i < 4): ?><div class="step-line"></div><?php endif; ?>
  <?php endforeach; ?>
</div>

<form id="mainForm">

<!-- ═══════════════════════════════════════════ STEP 1 ═══ -->
<div class="form-step active" id="formStep1">

  <div class="card">
    <div class="card-title"><?=__('idx_s1_title')?></div>
    <div class="form-grid cols3">
      <div class="form-group">
        <label><?=__('idx_form_num')?> <span class="req">*</span></label>
        <input type="number" id="q_num" name="questionnaire_num" min="1" required placeholder="001">
      </div>
      <div class="form-group">
        <label><?=__('idx_school')?> <span class="req">*</span></label>
        <select name="school" id="q_school" required onchange="toggleSchoolOther(this)">
          <option value=""><?=__('idx_choose_school')?></option>
          <optgroup label="دائرة الشلف">
            <option>ثانوية ابن باديس — الشلف</option>
            <option>ثانوية الشهيد خميستي — الشلف</option>
            <option>ثانوية عبد الحميد بن باديس — الشلف</option>
            <option>ثانوية أول نوفمبر — الشلف</option>
          </optgroup>
          <optgroup label="دائرة وادي الفضة">
            <option>ثانوية الأمير عبد القادر — وادي الفضة</option>
          </optgroup>
          <optgroup label="دائرة تنس">
            <option>ثانوية تنس</option>
          </optgroup>
          <optgroup label="دوائر أخرى">
            <option>ثانوية أم الدروع</option>
            <option>ثانوية بني حواء</option>
            <option>ثانوية الهرازة</option>
            <option>ثانوية أبو بكر الصديق — سيدي عكاشة</option>
          </optgroup>
          <optgroup label="أخرى">
            <option value="__other__"><?=__('idx_other_school')?></option>
          </optgroup>
        </select>
        <input type="text" id="school_other" name="school_other"
               placeholder="<?=__('idx_type_school')?>" style="display:none;margin-top:6px">
      </div>
      <div class="form-group">
        <label><?=__('idx_commune')?></label>
        <input type="text" name="commune" id="q_commune"
               placeholder="الشلف / تنس / وادي الفضة...">
      </div>
      <div class="form-group">
        <label><?=__('idx_age')?> <span class="req">*</span></label>
        <input type="number" id="q_age" name="age" min="13" max="20" required placeholder="15-18">
      </div>
      <div class="form-group">
        <label><?=__('idx_grade')?> <span class="req">*</span></label>
        <select name="grade" id="q_grade" required>
          <option value=""><?=__('idx_choose')?></option>
          <option>1AS</option><option>2AS</option><option>3AS</option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_sex')?> <span class="req">*</span></label>
        <div class="radio-group">
          <label class="radio-opt"><input type="radio" name="sex" value="Garçon" required> <?=__('idx_male')?></label>
          <label class="radio-opt"><input type="radio" name="sex" value="Fille"> <?=__('idx_female')?></label>
        </div>
      </div>
      <div class="form-group">
        <label><?=__('idx_height')?> <span class="req">*</span></label>
        <input type="number" id="q_height" name="height" min="130" max="210" step="0.1" required placeholder="165.0">
      </div>
      <div class="form-group">
        <label><?=__('idx_weight')?> <span class="req">*</span></label>
        <input type="number" id="q_weight" name="weight" min="25" max="150" step="0.1" required placeholder="60.0">
      </div>
      <div class="form-group">
        <label><?=__('idx_bmi_auto')?></label>
        <input type="text" id="q_bmi" name="bmi" readonly class="computed">
      </div>
      <div class="form-group">
        <label><?=__('idx_iotf_auto')?></label>
        <input type="text" id="q_iotf" name="iotf_class" readonly class="computed">
      </div>
      <div class="form-group">
        <label><?=__('idx_birth_weight')?></label>
        <input type="number" name="birth_weight" min="0.5" max="7" step="0.01" placeholder="3.20">
      </div>
      <div class="form-group">
        <label><?=__('idx_delivery')?></label>
        <div class="radio-group">
          <label class="radio-opt"><input type="radio" name="delivery_type" value="Naturel"> <?=__('idx_natural')?></label>
          <label class="radio-opt"><input type="radio" name="delivery_type" value="Césarienne"> <?=__('idx_caesarean')?></label>
        </div>
      </div>
      <div class="form-group full">
        <label><?=__('idx_school_avg')?>  <small style="color:#888;font-weight:400">(س.6)</small></label>
        <div class="radio-group">
          <label class="radio-opt"><input type="radio" name="school_avg" value="<10"> أقل من 10</label>
          <label class="radio-opt"><input type="radio" name="school_avg" value="10-12"> من 10 إلى 12</label>
          <label class="radio-opt"><input type="radio" name="school_avg" value="12-16"> من 12 إلى 16</label>
          <label class="radio-opt"><input type="radio" name="school_avg" value=">16"> أكثر من 16</label>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-title"><?=__('idx_s2_title')?></div>
    <div class="form-grid">
      <div class="form-group full">
        <label><?=__('idx_body_percept')?></label>
        <div class="radio-group">
          <label class="radio-opt"><input type="radio" name="body_perception" value="Beaucoup trop maigre"> نحيف جداً</label>
          <label class="radio-opt"><input type="radio" name="body_perception" value="Un peu trop maigre"> نحيف نوعاً ما</label>
          <label class="radio-opt"><input type="radio" name="body_perception" value="Normal"> طبيعي</label>
          <label class="radio-opt"><input type="radio" name="body_perception" value="Un peu trop gros"> بدين نوعاً ما</label>
        </div>
      </div>
      <div class="form-group">
        <label><?=__('idx_tried_lose')?></label>
        <div class="radio-group">
          <label class="radio-opt"><input type="radio" name="tried_lose_weight" value="Oui"> <?=__('idx_yes')?></label>
          <label class="radio-opt"><input type="radio" name="tried_lose_weight" value="Non"> <?=__('idx_no')?></label>
        </div>
      </div>
      <div class="form-group">
        <label><?=__('idx_lose_method')?></label>
        <select name="lose_method">
          <option value="">—</option>
          <option value="Régime"><?=__('idx_diet')?></option>
          <option value="Sport"><?=__('idx_sport')?></option>
          <option value="Médicaments"><?=__('idx_medication')?></option>
          <option value="Autre"><?=__('idx_other')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_weight_change')?></label>
        <select name="weight_variation">
          <option value="">—</option>
          <option value="Perte notable"><?=__('idx_notable_loss')?></option>
          <option value="Légère perte"><?=__('idx_slight_loss')?></option>
          <option value="Stable"><?=__('idx_stable')?></option>
          <option value="Prise de poids"><?=__('idx_weight_gain')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_last_weight')?></label>
        <input type="number" name="weight_last" min="25" max="150" step="0.1" placeholder="55.0">
      </div>
      <div class="form-group">
        <label><?=__('idx_want_change')?></label>
        <select name="want_weight_change">
          <option value="">—</option>
          <option value="Perdre"><?=__('idx_want_lose')?></option>
          <option value="Maintenir"><?=__('idx_want_maintain')?></option>
          <option value="Prendre"><?=__('idx_want_gain')?></option>
          <option value="Non"><?=__('idx_no')?></option>
        </select>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-title"><?=__('idx_s3_title')?></div>
    <div class="form-grid">
      <div class="form-group">
        <label><?=__('idx_smoker')?></label>
        <select name="smoker">
          <option value="">—</option>
          <option value="Jamais fumé"><?=__('idx_never_smoked')?></option>
          <option value="Oui"><?=__('idx_yes')?></option>
          <option value="Ex-fumeur"><?=__('idx_ex_smoker')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_cig_day')?></label>
        <input type="number" name="cig_per_day" min="0" max="60" placeholder="0">
      </div>
      <div class="form-group">
        <label><?=__('idx_trad_remedies')?></label>
        <select name="traditional_remedies">
          <option value="">—</option>
          <option value="Non"><?=__('idx_no')?></option>
          <option value="Oui, remèdes traditionnels"><?=__('idx_trad_yes')?></option>
          <option value="Oui, médicaments"><?=__('idx_med_yes')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_specialist')?></label>
        <select name="specialist_consult">
          <option value="">—</option>
          <option value="Non"><?=__('idx_no')?></option>
          <option value="Oui, gastro-entérologue"><?=__('idx_gastro')?></option>
          <option value="Oui, nutritionniste"><?=__('idx_nutritionist')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_diagnosis')?></label>
        <select name="medical_diagnosis">
          <option value="">—</option>
          <option value="Aucun diagnostic connu"><?=__('idx_no_stress')?></option>
          <option value="Côlon irritable"><?=__('idx_ibs')?></option>
          <option value="Maladie coeliaque"><?=__('idx_celiac')?></option>
          <option value="Maladie de Crohn"><?=__('idx_crohn')?></option>
          <option value="RGO"><?=__('idx_gerd')?></option>
          <option value="Je ne sais pas"><?=__('idx_dont_know')?></option>
        </select>
      </div>
    </div>
  </div>

  <div class="step-actions">
    <button type="button" class="btn btn-primary" onclick="gotoStep(2)"><?=__('idx_next')?></button>
  </div>
</div>

<!-- ═══════════════════════════════════════════ STEP 2 ═══ -->
<div class="form-step" id="formStep2">
  <div class="card">
    <div class="card-title"><?=__('idx_s4_title')?></div>
    <p class="card-note">
      <?=__('idx_ffq_note')?>
      <strong><?=__('idx_daily_scale')?></strong> — <strong><?=__('idx_weekly_scale')?></strong>
    </p>

    <?php
    // ─── سلم يومي (0-6) ───────────────────────────────────
    // 0=أبداً | 1=أقل من 1/أسبوع | 2=1/أسبوع | 3=2-4/أسبوع | 4=1/يوم | 5=2-3/يوم | 6=≥4/يوم
    $daily_foods = [
      __('ffq_grp_fruits_d') => [
        ['ffq_fruits_frais',__('ffq_fruits_frais')],
      ],
      __('ffq_grp_veg_d') => [
        ['ffq_legumes_crus', __('ffq_legumes_crus')],
        ['ffq_legumes_cuits',__('ffq_legumes_cuits')],
      ],
      __('ffq_grp_cereal_d') => [
        ['ffq_pain',       __('ffq_pain')],
        ['ffq_riz_semoule',__('ffq_riz_semoule')],
      ],
      __('ffq_grp_dairy_d') => [
        ['ffq_lait',  __('ffq_lait')],
        ['ffq_yaourt',__('ffq_yaourt')],
      ],
      __('ffq_grp_fats_d') => [
        ['ffq_huile_olive',  __('ffq_huile_olive')],
        ['ffq_autres_huiles',__('ffq_autres_huiles')],
      ],
    ];

    // ─── سلم أسبوعي (0-6) ─────────────────────────────────
    // 0=أبداً | 1=أقل من 1/شهر | 2=1-3/شهر | 3=1/أسبوع | 4=2-4/أسبوع | 5=5-6/أسبوع | 6=1/يوم
    $weekly_foods = [
      __('ffq_grp_fruits_w') => [
        ['ffq_fruits_secs',__('ffq_fruits_secs')],
      ],
      __('ffq_grp_veg_w') => [
        ['ffq_legumineuses',__('ffq_legumineuses')],
      ],
      __('ffq_grp_cereal_w') => [
        ['ffq_couscous',    __('ffq_couscous')],
        ['ffq_pommes_terre',__('ffq_pommes_terre')],
      ],
      __('ffq_grp_dairy_w') => [
        ['ffq_fromage',__('ffq_fromage')],
      ],
      __('ffq_grp_meat_w') => [
        ['ffq_viande_rouge',__('ffq_viande_rouge')],
        ['ffq_volaille',    __('ffq_volaille')],
        ['ffq_poisson',     __('ffq_poisson')],
        ['ffq_oeufs',       __('ffq_oeufs')],
      ],
      __('ffq_grp_sugary_w') => [
        ['ffq_boissons_sucrees',__('ffq_boissons_sucrees')],
        ['ffq_gateaux',        __('ffq_gateaux')],
        ['ffq_chocolat',       __('ffq_chocolat')],
        ['ffq_noix',           __('ffq_noix')],
      ],
      __('ffq_grp_fats_w') => [
        ['ffq_beurre',__('ffq_beurre')],
      ],
    ];
    ?>

    <?php /* ═══ جدول السلم اليومي ═══ */ ?>
    <div style="margin-bottom:1rem">
      <div style="background:#1F4E79;color:#fff;padding:8px 14px;border-radius:var(--radius) var(--radius) 0 0;font-size:13px;font-weight:600">
        ⬛ <?=__('idx_daily_header')?>
      </div>
      <div class="table-scroll">
      <table class="ffq-table">
        <thead>
          <tr>
            <th class="ffq-food-col"><?=__('idx_food_col')?></th>
            <th><?=__('idx_never')?><br><small>0</small></th>
            <th><?=__('idx_lt1wk')?><br><small>1</small></th>
            <th><?=__('idx_1wk')?><br><small>2</small></th>
            <th><?=__('idx_2_4wk')?><br><small>3</small></th>
            <th><?=__('idx_1day')?><br><small>4</small></th>
            <th><?=__('idx_2_3day')?><br><small>5</small></th>
            <th><?=__('idx_4plus_day')?><br><small>6</small></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($daily_foods as $group => $items): ?>
          <tr class="ffq-group-row"><td colspan="8"><?= $group ?></td></tr>
          <?php foreach ($items as [$name, $label]): ?>
          <tr>
            <td class="ffq-food"><?= $label ?></td>
            <?php for ($i=0; $i<=6; $i++): ?>
            <td><input type="radio" name="<?= $name ?>" value="<?= $i ?>"></td>
            <?php endfor; ?>
          </tr>
          <?php endforeach; ?>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>

    <?php /* ═══ جدول السلم الأسبوعي ═══ */ ?>
    <div style="margin-bottom:1rem">
      <div style="background:#185FA5;color:#fff;padding:8px 14px;border-radius:var(--radius) var(--radius) 0 0;font-size:13px;font-weight:600">
        🟦 <?=__('idx_weekly_header')?>
      </div>
      <div class="table-scroll">
      <table class="ffq-table">
        <thead>
          <tr>
            <th class="ffq-food-col"><?=__('idx_food_col')?></th>
            <th><?=__('idx_never')?><br><small>0</small></th>
            <th><?=__('idx_lt1mo')?><br><small>1</small></th>
            <th><?=__('idx_1_3mo')?><br><small>2</small></th>
            <th><?=__('idx_1wk')?><br><small>3</small></th>
            <th><?=__('idx_2_4wk')?><br><small>4</small></th>
            <th><?=__('idx_5_6wk')?><br><small>5</small></th>
            <th><?=__('idx_1day')?><br><small>6</small></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($weekly_foods as $group => $items): ?>
          <tr class="ffq-group-row"><td colspan="8"><?= $group ?></td></tr>
          <?php foreach ($items as [$name, $label]): ?>
          <tr>
            <td class="ffq-food"><?= $label ?></td>
            <?php for ($i=0; $i<=6; $i++): ?>
            <td><input type="radio" name="<?= $name ?>" value="<?= $i ?>"></td>
            <?php endfor; ?>
          </tr>
          <?php endforeach; ?>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
  <div class="step-actions">
    <button type="button" class="btn" onclick="gotoStep(1)"><?=__('idx_prev')?></button>
    <button type="button" class="btn btn-primary" onclick="gotoStep(3)"><?=__('idx_next')?></button>
  </div>
</div>

<!-- ═══════════════════════════════════════════ STEP 3 ═══ -->
<div class="form-step" id="formStep3">
  <div class="card">
    <div class="card-title"><?=__('idx_s5_title')?></div>
    <div class="form-grid">
      <div class="form-group">
        <label><?=__('idx_coffee_freq')?></label>
        <select name="coffee_freq">
          <option value="">—</option>
          <option value="Tous les jours"><?=__('idx_everyday')?></option>
          <option value="4-6/sem">4-6/أسبوع</option>
          <option value="1-3/sem">1-3/أسبوع</option>
          <option value="Jamais"><?=__('idx_never')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_coffee_cups')?></label>
        <select name="coffee_cups">
          <option value="">—</option>
          <option value="1 tasse"><?=__('idx_1cup')?></option>
          <option value="2 tasses"><?=__('idx_2cups')?></option>
          <option value="3+"><?=__('idx_3plus_cups')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_sugary_freq')?></label>
        <select name="sugary_freq">
          <option value="">—</option>
          <option value="Tous les jours"><?=__('idx_everyday')?></option>
          <option value="4-6/sem">4-6/أسبوع</option>
          <option value="1-3/sem">1-3/أسبوع</option>
          <option value="Jamais"><?=__('idx_never')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_energy_freq')?></label>
        <select name="energy_freq">
          <option value="">—</option>
          <option value="Tous les jours"><?=__('idx_everyday')?></option>
          <option value="4-6/sem">4-6/أسبوع</option>
          <option value="1-3/sem">1-3/أسبوع</option>
          <option value="Jamais"><?=__('idx_never')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_water_intake')?></label>
        <select name="water_intake">
          <option value="">—</option>
          <option value="<1L"><?=__('idx_lt1l')?></option>
          <option value="1-1.5L"><?=__('idx_1_1_5l')?></option>
          <option value="1.75-2L"><?=__('idx_1_75_2l')?></option>
          <option value="2.25-2.5L"><?=__('idx_2_25_2_5l')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_snacking_freq')?></label>
        <select name="snacking_freq">
          <option value="">—</option>
          <option value="Très souvent"><?=__('idx_very_often')?></option>
          <option value="Souvent"><?=__('idx_often')?></option>
          <option value="Parfois"><?=__('idx_sometimes')?></option>
          <option value="Rarement"><?=__('idx_rarely')?></option>
          <option value="Jamais"><?=__('idx_never')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_meals_per_day')?></label>
        <div class="radio-group">
          <label class="radio-opt"><input type="radio" name="meals_per_day" value="1"> 1</label>
          <label class="radio-opt"><input type="radio" name="meals_per_day" value="2"> 2</label>
          <label class="radio-opt"><input type="radio" name="meals_per_day" value="3"> 3</label>
          <label class="radio-opt"><input type="radio" name="meals_per_day" value="4"> 4+</label>
        </div>
      </div>
      <div class="form-group">
        <label><?=__('idx_breakfast')?></label>
        <select name="breakfast_freq">
          <option value="">—</option>
          <option value="Oui"><?=__('idx_everyday')?></option>
          <option value="4-6/sem">4-6/أسبوع</option>
          <option value="1-3/sem">1-3/أسبوع</option>
          <option value="Jamais"><?=__('idx_never')?></option>
        </select>
      </div>
      <div class="form-group full">
        <label><?=__('idx_skip_tutoring')?> <span class="highlight"><?=__('idx_important_var')?></span></label>
        <div class="radio-group">
          <label class="radio-opt"><input type="radio" name="skip_meal_tutoring" value="Oui, souvent"> <?=__('idx_yes_often')?></label>
          <label class="radio-opt"><input type="radio" name="skip_meal_tutoring" value="Oui, parfois"> <?=__('idx_yes_sometimes')?></label>
          <label class="radio-opt"><input type="radio" name="skip_meal_tutoring" value="Rarement"> <?=__('idx_rarely')?></label>
          <label class="radio-opt"><input type="radio" name="skip_meal_tutoring" value="Jamais"> <?=__('idx_never')?></label>
        </div>
      </div>
      <div class="form-group">
        <label><?=__('idx_meal_replace')?></label>
        <select name="meal_replacement">
          <option value="">—</option>
          <option value="Snacks/chips"><?=__('idx_chips')?></option>
          <option value="Sandwich"><?=__('idx_sandwich')?></option>
          <option value="Fruits"><?=__('idx_fruits')?></option>
          <option value="Rien"><?=__('idx_nothing')?></option>
          <option value="Autre"><?=__('idx_other')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_fv_portions')?></label>
        <input type="number" name="fv_portions" min="0" max="15" placeholder="0-10">
      </div>
      <div class="form-group">
        <label><?=__('idx_canteen')?></label>
        <select name="school_canteen">
          <option value="">—</option>
          <option value="Jamais"><?=__('idx_never')?></option>
          <option value="Parfois"><?=__('idx_sometimes')?></option>
          <option value="Toujours"><?=__('idx_always')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_canteen_fv')?></label>
        <select name="canteen_fv">
          <option value="">—</option>
          <option value="Jamais"><?=__('idx_never')?></option>
          <option value="Parfois"><?=__('idx_sometimes')?></option>
          <option value="Toujours"><?=__('idx_always')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_canteen_quality')?></label>
        <select name="canteen_quality">
          <option value="">—</option>
          <option value="Mauvaise"><?=__('idx_bad')?></option>
          <option value="Moyenne"><?=__('idx_average')?></option>
          <option value="Bonne"><?=__('idx_good')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_snacks_outside')?></label>
        <select name="snacks_outside">
          <option value="">—</option>
          <option value="Jamais"><?=__('idx_never')?></option>
          <option value="1-2/sem">1-2/أسبوع</option>
          <option value="3+/sem">3+/<?=__('idx_1wk')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_food_influence')?></label>
        <select name="food_influence">
          <option value="">—</option>
          <option value="Parents"><?=__('idx_parents')?></option>
          <option value="Amis"><?=__('idx_friends')?></option>
          <option value="Publicité"><?=__('idx_advertising')?></option>
          <option value="Moi-même"><?=__('idx_himself')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_reads_labels')?></label>
        <select name="reads_labels">
          <option value="">—</option>
          <option value="Jamais"><?=__('idx_never')?></option>
          <option value="Parfois"><?=__('idx_sometimes')?></option>
          <option value="Toujours"><?=__('idx_always')?></option>
        </select>
      </div>
    </div>
  </div>
  <div class="step-actions">
    <button type="button" class="btn" onclick="gotoStep(2)"><?=__('idx_prev')?></button>
    <button type="button" class="btn btn-primary" onclick="gotoStep(4)"><?=__('idx_next')?></button>
  </div>
</div>

<!-- ═══════════════════════════════════════════ STEP 4 ═══ -->
<div class="form-step" id="formStep4">
  <div class="card">
    <div class="card-title"><?=__('idx_s6_title')?></div>
    <div class="form-grid">
      <div class="form-group">
        <label><?=__('idx_sleep_duration')?></label>
        <select name="sleep_duration">
          <option value="">—</option>
          <option value="<6h"><?=__('idx_lt6h')?></option>
          <option value="6-7h"><?=__('idx_6_7h')?></option>
          <option value="8h"><?=__('idx_8h')?></option>
          <option value="9h+"><?=__('idx_9h_plus')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_screen_sleep')?></label>
        <select name="screen_before_sleep">
          <option value="">—</option>
          <option value="<30min"><?=__('idx_lt30min')?></option>
          <option value="30-60min"><?=__('idx_30_60min')?></option>
          <option value=">1h"><?=__('idx_gt1h')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_wake_exhausted')?></label>
        <select name="wake_exhausted">
          <option value="">—</option>
          <option value="Oui, souvent"><?=__('idx_yes_often')?></option>
          <option value="Oui, parfois"><?=__('idx_yes_sometimes')?></option>
          <option value="Jamais"><?=__('idx_never')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_insomnia')?></label>
        <select name="insomnia">
          <option value="">—</option>
          <option value="Oui, souvent"><?=__('idx_yes_often')?></option>
          <option value="Oui, parfois"><?=__('idx_yes_sometimes')?></option>
          <option value="Jamais"><?=__('idx_never')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_nightmares')?></label>
        <select name="nightmares">
          <option value="">—</option>
          <option value="Oui, souvent"><?=__('idx_yes_often')?></option>
          <option value="Oui, parfois"><?=__('idx_yes_sometimes')?></option>
          <option value="Jamais"><?=__('idx_never')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_emot_eating')?></label>
        <select name="emotional_eating">
          <option value="">—</option>
          <option value="Chocolat / sucreries"><?=__('idx_choc_sweets')?></option>
          <option value="Fruits à coque"><?=__('idx_nuts')?></option>
          <option value="Non, je ne mange pas en réponse aux émotions"><?=__('idx_no_emot_eat')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_bedtime')?></label>
        <input type="time" name="bedtime" value="22:00">
      </div>
      <div class="form-group">
        <label><?=__('idx_waketime')?></label>
        <input type="time" name="waketime" value="06:30">
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-title"><?=__('idx_s7_title')?></div>
    <div class="form-grid">
      <div class="form-group">
        <label><?=__('idx_sports_club')?></label>
        <div class="radio-group">
          <label class="radio-opt"><input type="radio" name="sports_club" value="Oui"> <?=__('idx_yes')?></label>
          <label class="radio-opt"><input type="radio" name="sports_club" value="Non"> <?=__('idx_no')?></label>
        </div>
      </div>
      <div class="form-group">
        <label><?=__('idx_sport_type')?></label>
        <input type="text" name="sport_type" placeholder="كرة قدم، سباحة...">
      </div>
      <div class="form-group full">
        <label><?=__('idx_active_days')?></label>
        <div class="radio-group">
          <?php foreach(range(0,7) as $d): ?>
          <label class="radio-opt"><input type="radio" name="active_days_week" value="<?= $d ?>"> <?= $d ?></label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="form-group">
        <label><?=__('idx_transport')?></label>
        <select name="transport">
          <option value="">—</option>
          <option value="Marche"><?=__('idx_walking')?></option>
          <option value="Vélo"><?=__('idx_bicycle')?></option>
          <option value="Voiture"><?=__('idx_car')?></option>
          <option value="Bus"><?=__('idx_bus')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_walk_duration')?></label>
        <select name="walk_to_school">
          <option value="">—</option>
          <option value="<10min"><?=__('idx_lt10min')?></option>
          <option value="10-30min"><?=__('idx_10_30min')?></option>
          <option value=">30min"><?=__('idx_gt30min')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_screen_phone')?></label>
        <select name="screen_phone">
          <option value="">—</option>
          <option value="<1h"><?=__('idx_lt1h')?></option>
          <option value="1-2h"><?=__('idx_1_2h')?></option>
          <option value="2-4h"><?=__('idx_2_4h')?></option>
          <option value=">4h"><?=__('idx_gt4h')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_screen_tv')?></label>
        <select name="screen_tv">
          <option value="">—</option>
          <option value="<1h"><?=__('idx_lt1h')?></option>
          <option value="1-2h"><?=__('idx_1_2h')?></option>
          <option value="2-4h"><?=__('idx_2_4h')?></option>
          <option value=">4h"><?=__('idx_gt4h')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_screen_games')?></label>
        <select name="screen_games">
          <option value="">—</option>
          <option value="<1h"><?=__('idx_lt1h')?></option>
          <option value="1-2h"><?=__('idx_1_2h')?></option>
          <option value="2-4h"><?=__('idx_2_4h')?></option>
          <option value=">4h"><?=__('idx_gt4h')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_screen_pc')?></label>
        <select name="screen_computer">
          <option value="">—</option>
          <option value="<1h"><?=__('idx_lt1h')?></option>
          <option value="1-2h"><?=__('idx_1_2h')?></option>
          <option value="2-4h"><?=__('idx_2_4h')?></option>
          <option value=">4h"><?=__('idx_gt4h')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_sports_facilities')?></label>
        <div class="radio-group">
          <label class="radio-opt"><input type="radio" name="sports_facilities" value="Oui"> <?=__('idx_yes')?></label>
          <label class="radio-opt"><input type="radio" name="sports_facilities" value="Non"> <?=__('idx_no')?></label>
        </div>
      </div>
      <div class="form-group">
        <label><?=__('idx_nutrition_ed')?></label>
        <select name="nutrition_education">
          <option value="">—</option>
          <option value="Jamais"><?=__('idx_never')?></option>
          <option value="1-2 fois"><?=__('idx_1_2_times')?></option>
          <option value="Régulièrement"><?=__('idx_regularly')?></option>
        </select>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-title"><?=__('idx_s8_title')?></div>
    <div class="form-grid">
      <div class="form-group">
        <label><?=__('idx_mother_edu')?></label>
        <select name="mother_education">
          <option value="">—</option>
          <option value="Sans niveau"><?=__('idx_no_level')?></option>
          <option value="Primaire"><?=__('idx_primary')?></option>
          <option value="Moyen"><?=__('idx_moderate')?></option>
          <option value="Secondaire"><?=__('idx_secondary')?></option>
          <option value="Universitaire"><?=__('idx_university')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_parents_work')?></label>
        <select name="parents_employment">
          <option value="">—</option>
          <option value="Les deux travaillent"><?=__('idx_both_work')?></option>
          <option value="Un seul"><?=__('idx_one_works')?></option>
          <option value="Aucun"><?=__('idx_none_works')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_stress_level')?></label>
        <select name="academic_stress">
          <option value="">—</option>
          <option value="Aucun"><?=__('idx_no_stress')?></option>
          <option value="Faible"><?=__('idx_low')?></option>
          <option value="Modéré"><?=__('idx_moderate')?></option>
          <option value="Élevé"><?=__('idx_high')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_parent_obese')?> <span class="highlight">⭐</span></label>
        <div class="radio-group">
          <label class="radio-opt"><input type="radio" name="parent_obese" value="Oui"> <?=__('idx_yes')?></label>
          <label class="radio-opt"><input type="radio" name="parent_obese" value="Non"> <?=__('idx_no')?></label>
        </div>
      </div>
      <div class="form-group">
        <label><?=__('idx_family_meals')?></label>
        <select name="meals_with_family">
          <option value="">—</option>
          <option value="Toujours"><?=__('idx_always')?></option>
          <option value="Souvent"><?=__('idx_often')?></option>
          <option value="Rarement"><?=__('idx_rarely')?></option>
          <option value="Jamais"><?=__('idx_never')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_family_ow')?></label>
        <select name="family_overweight">
          <option value="">—</option>
          <option value="0"><?=__('idx_nobody')?></option>
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3+"><?=__('idx_3_plus')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_follows_social')?></label>
        <div class="radio-group">
          <label class="radio-opt"><input type="radio" name="follows_nutrition_social" value="Oui"> <?=__('idx_yes')?></label>
          <label class="radio-opt"><input type="radio" name="follows_nutrition_social" value="Non"> <?=__('idx_no')?></label>
        </div>
      </div>
      <div class="form-group">
        <label><?=__('idx_general_health')?></label>
        <select name="general_health">
          <option value="">—</option>
          <option value="Excellente"><?=__('idx_excellent')?></option>
          <option value="Bonne"><?=__('idx_good')?></option>
          <option value="Moyenne"><?=__('idx_average')?></option>
          <option value="Mauvaise"><?=__('idx_bad')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_family_support')?></label>
        <select name="family_support">
          <option value="">—</option>
          <option value="Fort"><?=__('idx_strong')?></option>
          <option value="Moyen"><?=__('idx_moderate')?></option>
          <option value="Faible"><?=__('idx_weak')?></option>
          <option value="Aucun"><?=__('idx_no_stress')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_peer_pressure')?></label>
        <select name="peer_pressure_fastfood">
          <option value="">—</option>
          <option value="Jamais"><?=__('idx_never')?></option>
          <option value="Parfois"><?=__('idx_sometimes')?></option>
          <option value="Souvent"><?=__('idx_often')?></option>
        </select>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-title"><?=__('idx_s10_title')?></div>
    <div class="form-grid">
      <div class="form-group">
        <label><?=__('idx_knows_fv')?></label>
        <select name="knows_fv_portions">
          <option value="">—</option>
          <option value="1-2"><?=__('idx_1_2_portions')?></option>
          <option value="3-4"><?=__('idx_3_4_portions')?></option>
          <option value="5+"><?=__('idx_5_plus')?></option>
          <option value="Je ne sais pas"><?=__('idx_dont_know')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_knows_bmi')?></label>
        <select name="knows_bmi">
          <option value="">—</option>
          <option value="Oui, sait le calculer"><?=__('idx_knows_calc')?></option>
          <option value="En a entendu parler"><?=__('idx_heard_of')?></option>
          <option value="Non"><?=__('idx_no')?></option>
        </select>
      </div>
      <div class="form-group">
        <label><?=__('idx_diet_balanced')?></label>
        <select name="diet_balanced">
          <option value="">—</option>
          <option value="Oui"><?=__('idx_yes')?></option>
          <option value="Plutôt oui"><?=__('idx_rather_yes')?></option>
          <option value="Plutôt non"><?=__('idx_rather_no')?></option>
          <option value="Non"><?=__('idx_no')?></option>
        </select>
      </div>
    </div>
  </div>

  <div class="step-actions">
    <button type="button" class="btn" onclick="gotoStep(3)"><?=__('idx_prev')?></button>
    <button type="button" class="btn btn-primary" onclick="gotoStep(5)"><?=__('idx_review')?></button>
  </div>
</div>

<!-- ═══════════════════════════════════════════ STEP 5 ═══ -->
<div class="form-step" id="formStep5">
  <div class="card">
    <div class="card-title"><?=__('idx_review_title')?></div>
    <div id="reviewBox" class="review-box"></div>
  </div>
  <div class="step-actions">
    <button type="button" class="btn" onclick="gotoStep(4)"><?=__('idx_prev')?></button>
    <button type="button" class="btn btn-danger" onclick="resetForm()"><?=__('idx_clear_all')?></button>
    <button type="button" class="btn btn-success" onclick="submitForm()">
      <?=__('idx_save')?>
    </button>
  </div>
</div>

</form>
</div><!-- /container -->

<script>
const _lang = {
  form_num: 'رقم الاستمارة:',
  age: 'العمر:',
  years: 'سنة',
  sex: 'الجنس:',
  grade: 'المستوى:',
  height: 'الطول:',
  weight: 'الوزن:',
  bmi: 'IMC:',
  iotf: 'تصنيف IOTF:',
  breakfast: 'وجبة الإفطار:',
  sport: 'نادٍ رياضي:',
  sleep: 'مدة النوم:',
  mother_edu: 'تعليم الأم:',
  parent_obese: 'والد مصاب بسمنة:',
  stress: 'ضغط دراسي:',
  tutoring: 'دروس خصوصية:',
  replaces: 'يعوّض بـ:',
  saving: 'جاري الحفظ...',
  save_btn: 'حفظ السجل ✓',
  err_required: 'يرجى ملء الحقول الإلزامية: رقم الاستمارة، العمر، الجنس، الطول، الوزن.',
  conn_error: 'خطأ في الاتصال بالخادم. تأكد من اتصالك بالإنترنت.'
};
</script>
<script src="/assets/js/app.js"></script>
</body>
</html>
