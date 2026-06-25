<?php
/**
 * ════════════════════════════════════════════════════════════════════
 *  personal-survey/report.php  —  v2 étendu
 *  Calcule scores classiques + nouveaux indicateurs (WHtR, NOVA UPF,
 *  périnatal, génétique, SCOFF, PHQ-2, comportements alimentaires,
 *  apnée du sommeil, médicaments) et affiche un rapport personnalisé.
 * ════════════════════════════════════════════════════════════════════
 */

require_once __DIR__ . '/../lang.php';
require_once __DIR__ . '/lang_ps.php';
require_once __DIR__ . '/iotf.php';
require_once __DIR__ . '/../computed_scores.php';
require_once __DIR__ . '/advice.php';
require_once __DIR__ . '/extra_scores.php';
require_once __DIR__ . '/extra_advice.php';

$lang   = currentLang();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /personal-survey/');
    exit;
}
$data = $_POST;

// ─── Validation minimale ────────────────────────────────────────────
$age    = isset($data['age'])    ? (float)$data['age']    : 0;
$sex    = $data['sex']           ?? '';
$height = isset($data['height']) ? (float)$data['height'] : 0;
$weight = isset($data['weight']) ? (float)$data['weight'] : 0;

if (!$age || !$sex || !$height || !$weight) $errors[] = __('ps_err_required');
if ($age && ($age < 8 || $age > 80))        $errors[] = __('ps_err_age');

$bmi = 0;
if ($height > 0 && $weight > 0) {
    $bmi = $weight / pow($height / 100, 2);
    if ($bmi < 10 || $bmi > 60) $errors[] = __('ps_err_bmi');
}

if ($errors) { ?><!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= langDir() ?>">
<head>
<meta charset="UTF-8"><title>Erreur</title>
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/personal-survey/assets/style.css">
</head>
<body>
<div class="ps-container" style="padding-top:40px">
  <div class="ps-alert ps-alert-danger">
    <strong>⚠</strong>
    <ul style="margin:8px 0 0 22px">
      <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
  </div>
  <p style="text-align:center;margin-top:24px">
    <a href="/personal-survey/" class="ps-btn ps-btn-primary">← <?= __('ps_back_to_main') ?></a>
  </p>
</div>
</body></html>
<?php exit; }

// ─── Calculs ────────────────────────────────────────────────────────
$iotf = psGetIOTFClass($bmi, $age, $sex);

// Compatibilité avec computed_scores.php (qui attend tout le schéma DB).
// On remplit les champs absents avec des valeurs neutres.
$defaults = [
    'wake_exhausted'=>'Non','insomnia'=>'Non','nightmares'=>'Non',
    'sleep_duration'=>'7-8h','screen_before_sleep'=>'<30min',
    'bedtime'=>'','waketime'=>'',
    'sports_club'=>'Non','transport'=>'','walk_to_school'=>'<10min',
    'active_days_week'=>0,
    'skip_meal_tutoring'=>'Non','breakfast_freq'=>'Tous les jours',
    'snacks_outside'=>'Jamais','snacking_freq'=>'Jamais',
    'academic_stress'=>'Moyen','meals_per_day'=>3,
    'meal_replacement'=>'Non','emotional_eating'=>'Non',
    'sugary_freq'=>'Jamais','energy_freq'=>'Jamais',
    'water_intake'=>'1-1.5L',
    // V2 introduit screen_phone/tv/games/computer avec buckets « lt10..gt6 »
    // computed_scores attend « <1h, 1-2h, 2-3h, 3-4h, >4h ». On mappe.
    'screen_phone'=>'<1h','screen_tv'=>'<1h',
    'screen_games'=>'<1h','screen_computer'=>'<1h',
    'meals_with_family'=>'Souvent','parent_obese'=>'Non',
    'mother_education'=>'Secondaire','family_support'=>'Moyen',
    'parents_employment'=>'','peer_pressure_fastfood'=>'Rarement',
    'body_perception'=>'Normal','tried_lose_weight'=>'Non',
    'want_weight_change'=>'Non',
];
foreach ($defaults as $k => $v) {
    if (!isset($data[$k]) || $data[$k] === '') $data[$k] = $v;
}

// Mapper les nouveaux buckets de screen time vers l'échelle attendue
// par computed_scores.php (pour éviter un score sédentarité aberrant).
$bucketMap = [
    'lt10'=>'<1h','10_30'=>'<1h','30_60'=>'<1h',
    '1_2'=>'1-2h','2_4'=>'2-3h','4_6'=>'3-4h','gt6'=>'>4h',
];
foreach (['screen_phone','screen_tv','screen_games','screen_computer'] as $f) {
    if (isset($bucketMap[$data[$f]])) $data[$f] = $bucketMap[$data[$f]];
}

$scores = computeAllScores($data, $iotf);
$extra  = psxComputeAllExtra($data, $iotf, $bmi);
$v3     = psvComputeAllV3($data, $iotf, $bmi, $age, $sex);

// ─── Helpers ────────────────────────────────────────────────────────
function ps_class_meta($classe) {
    $map = [
        'Obésité'=>['ps_cl_obese','danger'],
        'Surpoids'=>['ps_cl_overweight','warning'],
        'Normal'=>['ps_cl_normal','success'],
        'Minceur'=>['ps_cl_thin','warning'],
        'Minceur grade 2'=>['ps_cl_thin2','danger'],
        'Minceur grade 3'=>['ps_cl_thin3','danger'],
        'Optimal'=>['ps_cl_optimal','success'],
        'Amélioration nécessaire'=>['ps_cl_improve','warning'],
        'Bon'=>['ps_cl_good','success'],
        'Perturbé'=>['ps_cl_disturbed','warning'],
        'Mauvais'=>['ps_cl_bad','danger'],
        'Inactif'=>['ps_cl_inactive','danger'],
        'Peu actif'=>['ps_cl_little','warning'],
        'Actif'=>['ps_cl_active','success'],
        'Très actif'=>['ps_cl_veryactive','success'],
        'Modérément sédentaire'=>['ps_cl_modsedent','warning'],
        'Sédentaire'=>['ps_cl_sedentary','danger'],
        'Faible'=>['ps_cl_low','success'],
        'Moyen'=>['ps_cl_avg','warning'],
        'Modérée'=>['ps_cl_mod','warning'],
        'Élevée'=>['ps_cl_high','danger'],
        'Modéré'=>['ps_cl_mod','warning'],
        'Élevé'=>['ps_cl_high','danger'],
        'Très élevée'=>['ps_cl_high','danger'],
        'Très élevé'=>['ps_cl_high','danger'],
        'Favorable'=>['ps_cl_favorable','success'],
        'Neutre'=>['ps_cl_neutral','info'],
        'Défavorable'=>['ps_cl_unfavorable','danger'],
        'Excellent'=>['ps_cl_excellent','success'],
        'Concordant'=>['ps_cl_concord','success'],
        'Sous-estime'=>['ps_cl_underest','warning'],
        'Surestime'=>['ps_cl_overest','warning'],
        // V2 extras
        'Sain'=>['ps_cl_good','success'],
        'Augmenté'=>['ps_cl_mod','warning'],
        'À améliorer'=>['ps_cl_improve','warning'],
        'Préoccupant'=>['ps_cl_high','warning'],
        'À risque élevé'=>['ps_cl_high','danger'],
        'À risque'=>['ps_cl_high','danger'],
        'Non à risque'=>['ps_cl_good','success'],
        'À surveiller'=>['ps_cl_mod','warning'],
        'Critique'=>['ps_cl_high','danger'],
        // V3 extras
        'High SES'=>['ps_cl_favorable','success'],
        'Mid SES'=>['ps_cl_neutral','info'],
        'Low SES'=>['ps_cl_unfavorable','warning'],
        'Very low SES'=>['ps_cl_unfavorable','danger'],
        'Précoce'=>['ps_cl_high','warning'],
        'Dans la norme'=>['ps_cl_good','success'],
        'Tardif'=>['ps_cl_mod','info'],
        'Équilibré'=>['ps_cl_good','success'],
        'Non-fumeur'=>['ps_cl_good','success'],
        'Exposition légère'=>['ps_cl_mod','warning'],
        'Risque modéré'=>['ps_cl_mod','warning'],
        'Risque élevé'=>['ps_cl_high','danger'],
        'Non évalué'=>['ps_cl_neutral','info'],
    ];
    return $map[$classe] ?? [$classe, 'info'];
}
function ps_class_label($c) { $m=ps_class_meta($c); return is_string($m[0])?(__($m[0])?:$m[0]):$m[0]; }
function ps_class_level($c) { $m=ps_class_meta($c); return $m[1]; }
function ps_bmi_pct($bmi)   { return max(0, min(100, ($bmi - 15) / 25 * 100)); }

// ─── Conseils ───────────────────────────────────────────────────────
$advices = [];

// 1. Conseils classiques
$advices[] = adviceBMI($iotf, $bmi, $age, $lang);
$advices[] = adviceKIDMED($scores['classe_kidmed'], $scores['score_kidmed'], $lang);
$advices[] = adviceSleep($scores['classe_sommeil'], $scores['score_sommeil'], $lang);
$advices[] = adviceActivity($scores['classe_activite'], $scores['score_activite'], $lang);
$advices[] = adviceSedentary($scores['classe_sedentarite'], $scores['screen_hours_total'], $lang);
$advices[] = adviceObRisk($scores['obesity_risk_class'], $scores['obesity_risk_score'], $iotf, $lang);
$advices[] = advicePerception($scores['perception_gap'], $iotf, $lang);
$advices[] = adviceAnemia($scores['anemia_risk'], $lang);
$advices[] = adviceVitD($scores['vitd_risk'], $lang);
$advices[] = adviceEatDis($scores['eating_disorder_risk'], $lang);
$advices[] = adviceFamily($scores['classe_famille'], $lang);
$advices[] = adviceSchoolPressure($scores['classe_pression'], $lang);

// 2. Conseils v2 (nouveaux)
$advices[] = adviceWHtR($extra['whtr_value'], $extra['whtr_class'], $lang);
$advices[] = adviceUPF($extra['upf_score'], $extra['upf_class'], $extra['upf_daily_items'], $lang);
$advices[] = advicePerinatal($extra['perinatal_score'], $extra['perinatal_class'], $extra['perinatal_factors'], $lang);
$advices[] = adviceGenetic($extra['genetic_score'], $extra['genetic_class'], $lang);
$advices[] = adviceSCOFF($extra['scoff_positive'], $extra['scoff_count'], $extra['scoff_items'], $lang);
$advices[] = adviceMental($extra['phq2_score'], $extra['gad1_score'], $extra['mental_class'], $lang);
$advices[] = adviceEatingBehavior($extra['eb_score'], $extra['eb_class'], $extra['eb_issues'], $lang);
$advices[] = adviceApnea($extra['apnea_class'], $lang);
$advices[] = adviceMedRisk($extra['med_risk_class'], $lang);

// 3. Conseils v3 (SES, puberté, FFQ DZ, tabac, jetlag, sitting, antibiotiques)
$advices[] = adviceSES($v3['ses_class'], $v3['food_insecure'], $lang);
$advices[] = advicePuberty($v3['puberty_timing'], $v3['puberty_class'], $v3['puberty_reason'], $lang);
$advices[] = adviceDZDiet($v3['dz_diet_score'], $v3['dz_diet_class'], $v3['dz_diet_flags'], $lang);
$advices[] = adviceSmoking($v3['smoking_score'], $v3['smoking_class'], $v3['smoking_passive'], $lang);
$advices[] = adviceJetlag($v3['jetlag_hours'], $v3['jetlag_class'], $lang);
$advices[] = adviceSitting($v3['sitting_hours'], $v3['sitting_class'], $lang);
$advices[] = adviceAntibiotic($v3['abx_score'], $v3['abx_class'], $v3['abx_unknown'], $lang);

$advices = array_values(array_filter($advices));
$rank = ['danger'=>0,'warning'=>1,'info'=>2,'success'=>3];
usort($advices, fn($a,$b) => ($rank[$a['level']]??9) <=> ($rank[$b['level']]??9));

// ─── Indicateurs (cartes) ───────────────────────────────────────────
$indicators = [
    ['key'=>'ps_ind_bmi',       'value'=>number_format($bmi,1), 'unit'=>'kg/m²', 'classe'=>$iotf],
    ['key'=>'psx_ind_whtr',     'value'=>$extra['whtr_value']!==null?number_format($extra['whtr_value'],2):'—', 'unit'=>'', 'classe'=>$extra['whtr_class']??'Inconnu'],
    ['key'=>'ps_ind_kidmed',    'value'=>$scores['score_kidmed'], 'unit'=>'/ 12', 'classe'=>$scores['classe_kidmed']],
    ['key'=>'psx_ind_upf',      'value'=>$extra['upf_score'], 'unit'=>'/ 30', 'classe'=>$extra['upf_class']],
    ['key'=>'ps_ind_sleep',     'value'=>$scores['sleep_hours_real']!==null?number_format($scores['sleep_hours_real'],1).' h':'—', 'unit'=>'', 'classe'=>$scores['classe_sommeil']],
    ['key'=>'ps_ind_activity',  'value'=>$scores['score_activite'], 'unit'=>'/ 12', 'classe'=>$scores['classe_activite']],
    ['key'=>'ps_ind_sedent',    'value'=>$scores['screen_hours_total'].' h', 'unit'=>'', 'classe'=>$scores['classe_sedentarite']],
    ['key'=>'psx_ind_eating_behav','value'=>$extra['eb_score'], 'unit'=>'/ 20', 'classe'=>$extra['eb_class']],
    ['key'=>'psx_ind_perinatal','value'=>$extra['perinatal_score'], 'unit'=>'/ 15', 'classe'=>$extra['perinatal_class']],
    ['key'=>'psx_ind_genetic',  'value'=>$extra['genetic_score'], 'unit'=>'/ 9', 'classe'=>$extra['genetic_class']],
    ['key'=>'psx_ind_scoff',    'value'=>$extra['scoff_count'], 'unit'=>'/ 5', 'classe'=>$extra['scoff_class']],
    ['key'=>'psx_ind_mental',   'value'=>$extra['mental_total'], 'unit'=>'/ 9', 'classe'=>$extra['mental_class']],
    ['key'=>'psx_ind_apnea',    'value'=>'', 'unit'=>'', 'classe'=>$extra['apnea_class']],
    ['key'=>'psx_ind_meds',     'value'=>'', 'unit'=>'', 'classe'=>$extra['med_risk_class']],
    ['key'=>'ps_ind_diversity', 'value'=>$scores['dietary_diversity'], 'unit'=>'/ 7', 'classe'=>$scores['classe_diversity']],
    ['key'=>'ps_ind_obrisk',    'value'=>$scores['obesity_risk_score'], 'unit'=>'/ 13', 'classe'=>$scores['obesity_risk_class']],
    ['key'=>'ps_ind_perception','value'=>'', 'unit'=>'', 'classe'=>$scores['perception_gap']],
    ['key'=>'ps_ind_anemia',    'value'=>'', 'unit'=>'', 'classe'=>$scores['anemia_risk']],
    ['key'=>'ps_ind_vitd',      'value'=>'', 'unit'=>'', 'classe'=>$scores['vitd_risk']],
    ['key'=>'ps_ind_eatdis',    'value'=>'', 'unit'=>'', 'classe'=>$scores['eating_disorder_risk']],
    ['key'=>'ps_ind_family',    'value'=>'', 'unit'=>'', 'classe'=>$scores['classe_famille']],
    ['key'=>'ps_ind_pressure',  'value'=>$scores['score_pression_scolaire'], 'unit'=>'/ 9', 'classe'=>$scores['classe_pression']],
    // V3 indicators
    ['key'=>'psv_ind_ses',      'value'=>$v3['ses_score'],      'unit'=>'/ 15', 'classe'=>$v3['ses_class']],
    ['key'=>'psv_ind_puberty',  'value'=>'',                    'unit'=>'',     'classe'=>$v3['puberty_class']],
    ['key'=>'psv_ind_dzdiet',   'value'=>$v3['dz_diet_score'],  'unit'=>'/ 20', 'classe'=>$v3['dz_diet_class']],
    ['key'=>'psv_ind_smoking',  'value'=>$v3['smoking_score'],  'unit'=>'',     'classe'=>$v3['smoking_class']],
    ['key'=>'psv_ind_jetlag',   'value'=>$v3['jetlag_hours']!==null?$v3['jetlag_hours'].' h':'—', 'unit'=>'', 'classe'=>$v3['jetlag_class']],
    ['key'=>'psv_ind_sitting',  'value'=>$v3['sitting_hours'].' h', 'unit'=>'', 'classe'=>$v3['sitting_class']],
    ['key'=>'psv_ind_abx',      'value'=>$v3['abx_score'],      'unit'=>'/ 4',  'classe'=>$v3['abx_class']],
];

$strengths = $weaknesses = [];
foreach ($indicators as $ind) {
    $lvl = ps_class_level($ind['classe']);
    if      ($lvl === 'success') $strengths[]  = __($ind['key']);
    elseif  ($lvl === 'danger')  $weaknesses[] = __($ind['key']);
}

$globalScore = $scores['global_nutrition_score'];
$globalClass = $scores['global_nutrition_class'];
$globalLevel = ps_class_level($globalClass);
$globalLabel = ps_class_label($globalClass);
?><!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= langDir() ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= __('ps_report_title') ?> — <?= __('ps_site_name') ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/personal-survey/assets/style.css">
</head>
<body class="ps-report-body">

<nav class="ps-navbar no-print">
  <div class="ps-nav-brand">
    <div class="ps-nav-titles">
      <span class="ps-nav-title"><?= __('ps_site_name') ?></span>
      <span class="ps-nav-subtitle"><?= __('ps_report_title') ?></span>
    </div>
  </div>
  <div class="ps-nav-actions">
    <a href="/personal-survey/" class="ps-nav-link">← <?= __('ps_restart') ?></a>
  </div>
</nav>

<div class="ps-container ps-report-container">

  <!-- ═══ Header ═══ -->
  <header class="ps-report-header">
    <h1 class="ps-report-h1"><?= __('ps_report_title') ?></h1>
    <p class="ps-report-meta">
      <span><?= __('ps_generated_on') ?> : <strong><?= date('d/m/Y H:i') ?></strong></span>
      &nbsp;·&nbsp;
      <span class="ps-not-saved-badge"><?= __('ps_not_saved') ?></span>
    </p>
    <div class="ps-actions no-print">
      <button onclick="window.print()" class="ps-btn ps-btn-primary"><?= __('ps_print') ?></button>
      <a href="/personal-survey/" class="ps-btn ps-btn-warn"><?= __('ps_restart') ?></a>
    </div>
  </header>

  <!-- ═══ Summary ═══ -->
  <section class="ps-card ps-summary-card">
    <h2 class="ps-card-title"><?= __('ps_summary_title') ?></h2>
    <div class="ps-summary-grid">
      <!-- BMI -->
      <div class="ps-summary-item">
        <div class="ps-gauge-wrap">
          <svg viewBox="0 0 120 120" class="ps-gauge">
            <circle cx="60" cy="60" r="50" class="ps-gauge-bg"></circle>
            <circle cx="60" cy="60" r="50" class="ps-gauge-fg ps-level-<?= ps_class_level($iotf) ?>"
              stroke-dasharray="<?= round(ps_bmi_pct($bmi)*3.14,1) ?> 314"
              transform="rotate(-90 60 60)"></circle>
            <text x="60" y="58" text-anchor="middle" class="ps-gauge-val"><?= number_format($bmi,1) ?></text>
            <text x="60" y="76" text-anchor="middle" class="ps-gauge-unit">kg/m²</text>
          </svg>
        </div>
        <div class="ps-summary-label">IMC</div>
        <div class="ps-summary-class ps-level-<?= ps_class_level($iotf) ?>">
          <?= htmlspecialchars(ps_class_label($iotf)) ?>
        </div>
      </div>

      <!-- WHtR -->
      <?php if ($extra['whtr_value'] !== null): ?>
      <div class="ps-summary-item">
        <div class="ps-gauge-wrap">
          <svg viewBox="0 0 120 120" class="ps-gauge">
            <circle cx="60" cy="60" r="50" class="ps-gauge-bg"></circle>
            <circle cx="60" cy="60" r="50" class="ps-gauge-fg ps-level-<?= ps_class_level($extra['whtr_class']) ?>"
              stroke-dasharray="<?= round(min(1,$extra['whtr_value']/0.7)*314,1) ?> 314"
              transform="rotate(-90 60 60)"></circle>
            <text x="60" y="58" text-anchor="middle" class="ps-gauge-val"><?= number_format($extra['whtr_value'],2) ?></text>
            <text x="60" y="76" text-anchor="middle" class="ps-gauge-unit">WHtR</text>
          </svg>
        </div>
        <div class="ps-summary-label"><?= __('psx_ind_whtr') ?></div>
        <div class="ps-summary-class ps-level-<?= ps_class_level($extra['whtr_class']) ?>">
          <?= htmlspecialchars($extra['whtr_class']) ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Global -->
      <div class="ps-summary-item">
        <div class="ps-gauge-wrap">
          <svg viewBox="0 0 120 120" class="ps-gauge">
            <circle cx="60" cy="60" r="50" class="ps-gauge-bg"></circle>
            <circle cx="60" cy="60" r="50" class="ps-gauge-fg ps-level-<?= $globalLevel ?>"
              stroke-dasharray="<?= round($globalScore*3.14,1) ?> 314"
              transform="rotate(-90 60 60)"></circle>
            <text x="60" y="58" text-anchor="middle" class="ps-gauge-val"><?= $globalScore ?></text>
            <text x="60" y="76" text-anchor="middle" class="ps-gauge-unit">/ 100</text>
          </svg>
        </div>
        <div class="ps-summary-label"><?= __('ps_global_score') ?></div>
        <div class="ps-summary-class ps-level-<?= $globalLevel ?>">
          <?= htmlspecialchars($globalLabel) ?>
        </div>
      </div>

      <!-- Strengths/Weaknesses -->
      <div class="ps-summary-item ps-summary-text">
        <?php if ($strengths): ?>
          <div class="ps-strength-box">
            <h3 class="ps-strength-h">✓ <?= __('ps_strengths') ?></h3>
            <ul class="ps-strength-list">
              <?php foreach (array_slice($strengths,0,8) as $s): ?><li><?= htmlspecialchars($s) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
        <?php if ($weaknesses): ?>
          <div class="ps-weakness-box">
            <h3 class="ps-weakness-h">⚠ <?= __('ps_weaknesses') ?></h3>
            <ul class="ps-weakness-list">
              <?php foreach (array_slice($weaknesses,0,8) as $w): ?><li><?= htmlspecialchars($w) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ═══ Indicators grid ═══ -->
  <section class="ps-card">
    <h2 class="ps-card-title"><?= __('ps_indicators') ?></h2>
    <div class="ps-indicator-grid">
      <?php foreach ($indicators as $ind):
        $lvl   = ps_class_level($ind['classe']);
        $label = ps_class_label($ind['classe']); ?>
        <div class="ps-indicator-card ps-level-<?= $lvl ?>">
          <div class="ps-indicator-head"><?= __($ind['key']) ?></div>
          <?php if ($ind['value'] !== ''): ?>
            <div class="ps-indicator-value">
              <span class="ps-indicator-num"><?= $ind['value'] ?></span>
              <?php if ($ind['unit']): ?><span class="ps-indicator-unit"><?= $ind['unit'] ?></span><?php endif; ?>
            </div>
          <?php endif; ?>
          <div class="ps-indicator-class"><?= htmlspecialchars($label) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ═══ Advice ═══ -->
  <section class="ps-card">
    <h2 class="ps-card-title"><?= __('ps_advice_title') ?></h2>
    <?php if (!$advices): ?>
      <p class="ps-card-note"><?= __('ps_cl_optimal') ?> ✓</p>
    <?php else: ?>
      <div class="ps-advice-list">
        <?php foreach ($advices as $a): ?>
          <article class="ps-advice-card ps-level-<?= $a['level'] ?>">
            <h3 class="ps-advice-title"><?= htmlspecialchars($a['title']) ?></h3>
            <?php if (!empty($a['text'])): ?>
              <p class="ps-advice-text"><?= htmlspecialchars($a['text']) ?></p>
            <?php endif; ?>
            <?php if (!empty($a['bullets']) && is_array($a['bullets'])): ?>
              <ul class="ps-advice-bullets">
                <?php foreach ($a['bullets'] as $b): ?><li><?= htmlspecialchars($b) ?></li><?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- ═══ Méthodologie & références ═══ -->
  <section class="ps-card ps-methodology">
    <h2 class="ps-card-title">📚 <?= __('psx_methodology') ?></h2>
    <div class="ps-method-body">
      <p class="ps-card-note">
        <?php if ($lang==='ar'): ?>
          هذا التقرير مبني على أدوات بحثية معتمدة دولياً. الإشارات أدناه تسمح للمختصّين بالتحقّق من المنهجية.
        <?php elseif ($lang==='fr'): ?>
          Ce rapport est basé sur des outils de recherche validés internationalement. Les références ci-dessous permettent aux spécialistes de vérifier la méthodologie.
        <?php else: ?>
          This report is built on internationally validated research tools. The references below allow specialists to verify the methodology.
        <?php endif; ?>
      </p>
      <ul class="ps-refs">
        <li><strong>IMC pédiatrique (IOTF) :</strong> Cole TJ et al. <em>BMJ</em> 2000;320:1240 ; Cole &amp; Lobstein <em>Pediatr Obes</em> 2012.</li>
        <li><strong>WHtR (rapport taille/hauteur) :</strong> seuil ≥0.5 pour l'obésité abdominale (Ashwell &amp; Hsieh, <em>Eur J Clin Nutr</em> 2003 ; appliqué Jordanie 2025 — <em>Sci Rep</em> 2025).</li>
        <li><strong>KIDMED :</strong> Serra-Majem et al. <em>Public Health Nutr</em> 2004 — adhésion régime méditerranéen pédiatrique.</li>
        <li><strong>NOVA / aliments ultra-transformés :</strong> Monteiro et al. <em>Public Health Nutr</em> 2018 ; UPF-screener Costa Louzada 2018. Liens UPF–obésité enfants/ados : <em>Eur J Nutr</em> 2022 (PMC8942762), CHNS Chine 2025.</li>
        <li><strong>Facteurs périnataux :</strong> Weng SF et al. <em>Arch Dis Child</em> 2012 (méta-analyse 30 cohortes) ; Yan J et al. <em>BMC Public Health</em> 2014 — allaitement AOR=0.78 (n=226 508).</li>
        <li><strong>Génétique :</strong> Eghbali M et al. <em>Endocrinol Diabetes Metab</em> 2024 — variants FTO chez enfants/ados.</li>
        <li><strong>SCOFF :</strong> Morgan JF et al. <em>BMJ</em> 1999 — dépistage TCA (Se 85%, Sp 80%, ≥2 oui = positif).</li>
        <li><strong>PHQ-2 :</strong> Kroenke K et al. <em>Med Care</em> 2003 — dépistage rapide dépression.</li>
        <li><strong>Vitesse de manger :</strong> Ohkuma T et al. <em>Int J Obes</em> 2015 — manger vite double le risque d'obésité.</li>
        <li><strong>Recommandations 24-h Movement :</strong> &lt; 2 h écran récréatif, ≥ 60 min APMV/j, 8-11 h sommeil (selon l'âge).</li>
        <li><strong>Statut socio-économique :</strong> Wang &amp; Lim <em>Curr Obes Rep</em> 2012 ; transition épidémiologique pays MENA — éducation maternelle = facteur le plus prédictif.</li>
        <li><strong>Puberté précoce → obésité :</strong> Pierce &amp; Leon <em>BMC Pediatr</em> 2005 ; Day et al. <em>Sci Rep</em> 2015 (Mendelian randomization).</li>
        <li><strong>Social jetlag :</strong> Roenneberg T et al. <em>Curr Biol</em> 2012 — décalage chrono &gt; 1 h associé à obésité, diabète, dépression.</li>
        <li><strong>Sédentarité indépendante des écrans :</strong> Ekelund U et al. <em>Lancet</em> 2016 — 8 h+ assis/jour = risque mortalité +60% si peu actif.</li>
        <li><strong>Antibiotiques précoces &amp; microbiote :</strong> Trasande L et al. <em>Int J Obes</em> 2013 ; Mueller NT et al. <em>Nature Rev Endocrinol</em> 2015.</li>
        <li><strong>Tabac/chicha :</strong> WHO TobReg 2005 — 1 séance chicha = fumée de 100 cigarettes ; reprise pondérale post-sevrage 5-10 kg moyenne.</li>
        <li><strong>FFQ algérien :</strong> adapté du modèle CRASC Algérie 2021 — intégration sucre du thé, friture traditionnelle, pâtisseries festives.</li>
      </ul>
    </div>
  </section>

  <!-- ═══ Disclaimer ═══ -->
  <section class="ps-disclaimer-final">
    <?= __('ps_disclaimer') ?>
  </section>

  <div class="ps-actions ps-actions-bottom no-print">
    <button onclick="window.print()" class="ps-btn ps-btn-primary"><?= __('ps_print') ?></button>
    <a href="/personal-survey/" class="ps-btn ps-btn-warn"><?= __('ps_restart') ?></a>
  </div>

</div>
</body>
</html>
