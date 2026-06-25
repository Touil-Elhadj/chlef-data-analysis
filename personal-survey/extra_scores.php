<?php
/**
 * ════════════════════════════════════════════════════════════════════
 *  personal-survey/extra_scores.php
 *  Calculs supplémentaires basés sur la littérature scientifique 2024 :
 *   • WHtR (waist-to-height ratio) — Jordan 2025, Lin 2024
 *   • UPF score (NOVA classification) — UPF screener Brazil 2018+
 *   • Perinatal risk (Weng 2012 meta-analysis)
 *   • Genetic/family risk (Eghbali 2024, FTO meta-analysis)
 *   • SCOFF (Morgan 1999) — eating disorder screen
 *   • PHQ-2 + GAD — Kroenke 2003
 *   • Eating behaviors score (eating speed, screen-eating, late eating)
 *   • Sleep apnea risk (STOP-Bang inspired, simplified)
 *   • Medication-induced weight gain risk
 *  Toutes les fonctions sont pures (sans état) et retournent un tableau.
 * ════════════════════════════════════════════════════════════════════
 */

// ─── 1. WHtR — waist-to-height ratio ────────────────────────────────
// Référence : WHtR ≥ 0.5 = adiposité abdominale (enfants ≥6 ans & adultes).
// Cutoffs additionnels : <0.4 = low, 0.4-0.5 = healthy, 0.5-0.6 = increased,
// ≥0.6 = high risk.
function psxWHtR($waist_cm, $height_cm) {
    if (!$waist_cm || !$height_cm || $waist_cm < 30 || $height_cm < 50) {
        return ['whtr_value' => null, 'whtr_class' => null];
    }
    $ratio = $waist_cm / $height_cm;
    if     ($ratio < 0.40) $c = 'Faible';
    elseif ($ratio < 0.50) $c = 'Sain';
    elseif ($ratio < 0.60) $c = 'Augmenté';
    else                   $c = 'Élevé';
    return [
        'whtr_value' => round($ratio, 3),
        'whtr_class' => $c,
    ];
}

// ─── 2. UPF score — NOVA screener style ─────────────────────────────
// Compte le nombre d'UPF consommés ≥3 fois/semaine + bonus pour
// fréquence quotidienne. Score 0-30. Classes basées sur Costa 2018.
function psxUPF($d) {
    $upf_fields = [
        'upf_soda','upf_energy','upf_juices','upf_cookies','upf_candy',
        'upf_icecream','upf_pizza','upf_ready','upf_chips','upf_meat',
        'upf_cereals','upf_yogurt','upf_sauces','upf_white_bread','upf_fastfood',
    ];
    $score = 0;
    $daily = 0;
    foreach ($upf_fields as $f) {
        $v = $d[$f] ?? 'Jamais';
        if     ($v === 'Tous les jours' || $v === '2-3/jour' || $v === 'Plus d\'1/jour') { $score += 2; $daily++; }
        elseif ($v === '4-6/sem')                                                          { $score += 1; }
        elseif ($v === '1-3/sem')                                                          { $score += 0.5; }
    }
    $score = (int) round($score);
    if     ($score <= 4)  $c = 'Faible';
    elseif ($score <= 10) $c = 'Modérée';
    elseif ($score <= 18) $c = 'Élevée';
    else                  $c = 'Très élevée';
    return [
        'upf_score' => $score,
        'upf_daily_items' => $daily,
        'upf_class' => $c,
    ];
}

// ─── 3. Perinatal risk score ────────────────────────────────────────
// Weng 2012 meta-analysis (PMC3512440) : maternal pre-pregnancy
// overweight, high birth weight, rapid early gain, maternal smoking,
// short/no breastfeeding, early solid intro.
function psxPerinatal($d) {
    $score = 0;
    $factors = [];

    // Naissance — poids
    $bw = $d['birth_weight'] ?? '';
    if ($bw === '>4kg' || $bw === '> 4 kg' || $bw === 'Macrosomie') { $score += 2; $factors[] = 'macrosomie'; }
    elseif ($bw === '<2.5kg' || $bw === '< 2.5 kg')                 { $score += 1; $factors[] = 'low_birth_weight'; }

    // Mode d'accouchement
    if (($d['delivery_type'] ?? '') === 'Césarienne') { $score += 1; $factors[] = 'cesarean'; }

    // Allaitement — Yan 2014 (PMC4301835) : OR=0.78 pour breastfeeding
    $bf = $d['psx_breastfed_duration'] ?? '';
    if ($bf === 'jamais' || $bf === '<1mo') { $score += 2; $factors[] = 'no_bf'; }
    elseif ($bf === '1-3mo')                { $score += 1; $factors[] = 'short_bf'; }
    // 3-6mo neutre, ≥6mo protecteur (pas de pénalité)

    $bft = $d['psx_breastfed_type'] ?? '';
    if ($bft === 'formula')      { $score += 1; $factors[] = 'formula_only'; }

    // Introduction solides — Pearce 2013
    if (($d['psx_solid_intro'] ?? '') === '<4mo') { $score += 1; $factors[] = 'early_solids'; }

    // Tabagisme maternel — 47% augmentation (Weng 2012)
    if (($d['psx_mother_smoked'] ?? '') === 'Oui') { $score += 2; $factors[] = 'maternal_smoking'; }

    // Diabète gestationnel
    if (($d['psx_gest_diabetes'] ?? '') === 'Oui') { $score += 1; $factors[] = 'gest_diabetes'; }

    // Surpoids maternel pré/pendant grossesse
    if (($d['psx_mother_overweight_preg'] ?? '') === 'Oui') { $score += 2; $factors[] = 'maternal_obesity'; }

    // Prise de poids rapide infantile
    if (($d['psx_rapid_weight_gain'] ?? '') === 'Oui') { $score += 2; $factors[] = 'rapid_gain'; }

    if     ($score <= 2) $c = 'Faible';
    elseif ($score <= 5) $c = 'Modéré';
    elseif ($score <= 8) $c = 'Élevé';
    else                 $c = 'Très élevé';

    return [
        'perinatal_score'   => $score,
        'perinatal_class'   => $c,
        'perinatal_factors' => $factors,
    ];
}

// ─── 4. Genetic / family risk ───────────────────────────────────────
// FTO + family history (Eghbali 2024). Note : non testable génétiquement
// ici, on utilise l'héritabilité observée (parents, fratrie, diabète).
function psxGenetic($d) {
    $score = 0;
    $fb = $d['psx_father_bmi'] ?? '';
    $mb = $d['psx_mother_bmi'] ?? '';
    foreach ([$fb, $mb] as $bmi) {
        if     ($bmi === 'obese') $score += 2;
        elseif ($bmi === 'over')  $score += 1;
    }
    if (($d['psx_siblings_obese'] ?? '') === 'Oui') $score += 1;
    if (($d['psx_family_t2d']     ?? '') === 'Oui') $score += 1;
    if (($d['psx_family_htn']     ?? '') === 'Oui') $score += 1;
    if (($d['psx_family_chol']    ?? '') === 'Oui') $score += 1;
    if (($d['psx_family_thyroid'] ?? '') === 'Oui') $score += 1;

    if     ($score <= 1) $c = 'Faible';
    elseif ($score <= 4) $c = 'Modéré';
    elseif ($score <= 7) $c = 'Élevé';
    else                 $c = 'Très élevé';

    return [
        'genetic_score' => $score,
        'genetic_class' => $c,
    ];
}

// ─── 5. SCOFF — eating disorder screen ──────────────────────────────
// Morgan 1999 (BMJ). 5 questions Oui/Non, ≥2 oui = positif (Se ~85%, Sp ~80%).
function psxSCOFF($d) {
    $count = 0;
    $items = [];
    foreach (['scoff_sick','scoff_control','scoff_onestone','scoff_fat','scoff_food'] as $q) {
        if (($d[$q] ?? '') === 'Oui') { $count++; $items[] = $q; }
    }
    $positive = $count >= 2;
    return [
        'scoff_count'    => $count,
        'scoff_positive' => $positive,
        'scoff_items'    => $items,
        'scoff_class'    => $positive ? 'À risque' : 'Non à risque',
    ];
}

// ─── 6. Mental health screen (PHQ-2 + GAD-1) ────────────────────────
// Kroenke 2003 : PHQ-2 score ≥3 = positif pour dépression majeure.
// Mapping : almost_every=3, morehalf=2, some=1, none=0.
function psxMental($d) {
    $map = [
        'almost_every' => 3, 'morehalf' => 2, 'some' => 1, 'none' => 0,
    ];
    $down     = $map[$d['phq_down']     ?? 'none'] ?? 0;
    $interest = $map[$d['phq_interest'] ?? 'none'] ?? 0;
    $worry    = $map[$d['gad_worry']    ?? 'none'] ?? 0;
    $phq2 = $down + $interest;
    $total = $phq2 + $worry;

    if     ($total <= 1)  $c = 'Bon';
    elseif ($total <= 3)  $c = 'À surveiller';
    elseif ($total <= 6)  $c = 'Préoccupant';
    else                  $c = 'Critique';

    return [
        'phq2_score'   => $phq2,
        'gad1_score'   => $worry,
        'mental_total' => $total,
        'mental_class' => $c,
    ];
}

// ─── 7. Eating behavior score ───────────────────────────────────────
// Eating speed (Ohkuma 2015, very fast eating doubles obesity risk),
// screen-eating, late eating, second servings, large portions.
function psxEatingBehavior($d) {
    $score = 0;
    $issues = [];

    // Vitesse de manger (Ohkuma meta-analysis)
    $sp = $d['eating_speed'] ?? '';
    if     ($sp === 'very_fast') { $score += 3; $issues[] = 'very_fast'; }
    elseif ($sp === 'fast')      { $score += 2; $issues[] = 'fast'; }

    // Portion size
    $ps = $d['portion_size'] ?? '';
    if     ($ps === 'much_bigger') { $score += 2; $issues[] = 'huge_portions'; }
    elseif ($ps === 'bigger')      { $score += 1; $issues[] = 'large_portions'; }

    // Second servings
    if (in_array($d['second_serving'] ?? '', ['Toujours','Souvent'])) {
        $score += 1; $issues[] = 'seconds';
    }

    // Eating in front of screen
    $es = $d['eat_screen'] ?? '';
    if     ($es === 'always') { $score += 2; $issues[] = 'screen_always'; }
    elseif ($es === 'often')  { $score += 1; $issues[] = 'screen_often'; }

    // Late eating
    $le = $d['late_eating'] ?? '';
    if     ($le === 'daily') { $score += 2; $issues[] = 'late_daily'; }
    elseif ($le === 'freq')  { $score += 1; $issues[] = 'late_freq'; }

    // Outside meals
    $om = $d['outside_meals'] ?? '0';
    if     (in_array($om, ['gt7','4_6'])) { $score += 2; $issues[] = 'fastfood_freq'; }
    elseif ($om === '2_3')                { $score += 1; $issues[] = 'fastfood_some'; }

    // Bread excess
    if (($d['bread_per_day'] ?? '') === 'gt5') { $score += 2; $issues[] = 'bread_excess'; }
    elseif (($d['bread_per_day'] ?? '') === '3_4') { $score += 1; }

    // Sugar in drinks
    $sg = $d['sugar_drinks'] ?? '';
    if     ($sg === 'gt6') { $score += 2; $issues[] = 'sugar_excess'; }
    elseif ($sg === '4_6') { $score += 1; $issues[] = 'sugar_high'; }

    // Cooking method
    if (($d['cooking_method'] ?? '') === 'fried') { $score += 2; $issues[] = 'frying_dominant'; }

    if     ($score <= 3)  $c = 'Sain';
    elseif ($score <= 7)  $c = 'À améliorer';
    elseif ($score <= 12) $c = 'Préoccupant';
    else                  $c = 'À risque élevé';

    return [
        'eb_score'  => $score,
        'eb_class'  => $c,
        'eb_issues' => $issues,
    ];
}

// ─── 8. Sleep apnea risk (simplified STOP-Bang) ─────────────────────
// Score : ronflement + somnolence + IMC élevé + sexe masculin = risque OSA.
function psxApnea($d, $iotf, $sex) {
    $score = 0;
    if (($d['snoring'] ?? '') === 'Oui')          $score += 2;
    if (($d['daytime_sleepy'] ?? '') === 'Oui')   $score += 1;
    if (in_array($iotf, ['Obésité','Surpoids']))  $score += 2;
    if ($sex === 'Garçon')                        $score += 1;
    // Sleep quality
    $sq = (int)($d['sleep_quality'] ?? 7);
    if ($sq <= 4) $score += 1;

    if     ($score <= 1) $c = 'Faible';
    elseif ($score <= 3) $c = 'Modéré';
    else                 $c = 'Élevé';

    return [
        'apnea_score' => $score,
        'apnea_class' => $c,
    ];
}

// ─── 9. Medication-induced weight risk ──────────────────────────────
// Liste basée sur Domecq 2015 (J Clin Endocrinol Metab).
function psxMedRisk($d) {
    $high   = ['med_cortico','med_antipsy'];      // gain notable
    $medium = ['med_antidep','med_contracep'];    // gain modéré
    $any_high = $any_med = false;
    foreach ($high as $m)   if (($d[$m] ?? '') === 'Oui') $any_high = true;
    foreach ($medium as $m) if (($d[$m] ?? '') === 'Oui') $any_med  = true;

    if ($any_high)      $c = 'Élevé';
    elseif ($any_med)   $c = 'Modéré';
    else                $c = 'Faible';

    return ['med_risk_class' => $c];
}

// ─── 10. Méga-fonction d'orchestration ──────────────────────────────
function psxComputeAllExtra($d, $iotf, $bmi) {
    $extra = [];
    $extra += psxWHtR($d['waist_cm'] ?? null, $d['height'] ?? null);
    $extra += psxUPF($d);
    $extra += psxPerinatal($d);
    $extra += psxGenetic($d);
    $extra += psxSCOFF($d);
    $extra += psxMental($d);
    $extra += psxEatingBehavior($d);
    $extra += psxApnea($d, $iotf, $d['sex'] ?? '');
    $extra += psxMedRisk($d);
    return $extra;
}

// ════════════════════════════════════════════════════════════════════
// V3 ADDITIONS — SES, puberté, FFQ DZ, tabac, jetlag, sitting, abx
// ════════════════════════════════════════════════════════════════════

// ─── 11. SES score (0-15, plus haut = plus défavorisé) ──────────────
// Mother education = facteur le plus prédicteur (literature transition countries).
function psvSES($d) {
    $score = 0;
    // Mother education (weight ×1.5 because most predictive)
    $me = $d['psv_mother_edu'] ?? '';
    $score += ['none'=>4,'primary'=>3,'middle'=>2,'secondary'=>1,'university'=>0][$me] ?? 1;
    // Father education
    $fe = $d['psv_father_edu'] ?? '';
    $score += ['none'=>2,'primary'=>1.5,'middle'=>1,'secondary'=>0.5,'university'=>0][$fe] ?? 0.5;
    // Father occupation
    $fw = $d['psv_father_work'] ?? '';
    $score += ['unemp'=>3,'informal'=>2,'manual'=>1,'employee'=>0.5,'prof'=>0,'housewife'=>1][$fw] ?? 0.5;
    // Household amenities (multi-select array)
    $h = $d['psv_household'] ?? [];
    if (!is_array($h)) $h = [];
    $owned = count($h);  // out of 5 max
    if      ($owned <= 1) $score += 3;
    elseif  ($owned <= 2) $score += 2;
    elseif  ($owned <= 3) $score += 1;
    // Food security (strongest direct indicator of vulnerability)
    $fs = $d['psv_food_security'] ?? 'never';
    $score += ['never'=>0,'rare'=>1,'some'=>2,'often'=>3][$fs] ?? 0;

    $score = (int) round($score);
    if     ($score <= 3)  $c = 'High SES';
    elseif ($score <= 7)  $c = 'Mid SES';
    elseif ($score <= 11) $c = 'Low SES';
    else                  $c = 'Very low SES';

    return [
        'ses_score' => $score,
        'ses_class' => $c,
        'food_insecure' => in_array($fs, ['some','often']),
    ];
}

// ─── 12. Pubertal timing ────────────────────────────────────────────
// Early puberty (filles <11, garçons <12) = obesity risk factor.
function psvPuberty($d, $sex, $age) {
    $timing = 'normal';
    $reason = null;

    if ($sex === 'Fille') {
        $m = $d['psv_menarche'] ?? '';
        if      (in_array($m, ['<10','10_11'])) { $timing = 'early'; $reason = 'menarche_early'; }
        elseif  ($m === '14_15')                { $timing = 'late';  $reason = 'menarche_late'; }
        elseif  ($m === '>15')                  { $timing = 'late';  $reason = 'menarche_very_late'; }
        elseif  ($m === 'not_yet' && $age >= 14) { $timing = 'late'; $reason = 'no_menarche_late'; }
    } elseif ($sex === 'Garçon') {
        $v = $d['psv_voice_change'] ?? '';
        if      ($v === '<12')   { $timing = 'early'; $reason = 'voice_early'; }
        elseif  ($v === '>15')   { $timing = 'late';  $reason = 'voice_late'; }
        elseif  ($v === 'not_yet' && $age >= 15) { $timing = 'late'; $reason = 'no_voice_late'; }
    }

    // Cross-check with growth spurt
    $sp = $d['psv_growth_spurt'] ?? '';
    if ($sp === '8_10' && $timing === 'normal') { $timing = 'early'; $reason = 'spurt_early'; }

    $tanner = (int)($d['psv_tanner_self'] ?? 3);

    $class_map = [
        'early'  => 'Précoce',
        'normal' => 'Dans la norme',
        'late'   => 'Tardif',
    ];

    return [
        'puberty_timing' => $timing,
        'puberty_class'  => $class_map[$timing],
        'puberty_reason' => $reason,
        'tanner_self'    => $tanner,
    ];
}

// ─── 13. Algerian dietary excess score ──────────────────────────────
// Capture culturally-specific overconsumption (sweet tea, traditional
// pastries, fried street food, fatty couscous, mhajeb).
function psvDZDiet($d) {
    $score = 0;
    $flags = [];

    // Thé sucré : si ≥3 verres/j = facteur sucre majeur
    $tea = $d['psv_dz_mint_tea'] ?? '0';
    if      ($tea === 'gt6') { $score += 3; $flags[] = 'tea_excess'; }
    elseif  ($tea === '5_6') { $score += 2; $flags[] = 'tea_high'; }
    elseif  ($tea === '3_4') { $score += 1; $flags[] = 'tea_some'; }

    // Items réguliers ≥ 2/sem
    $dz_items = [
        'psv_dz_bourek'    => 1.5,
        'psv_dz_mhajeb'    => 1,
        'psv_dz_zlabia'    => 1.5,
        'psv_dz_garantita' => 1,
        'psv_dz_lham_dlou' => 1,
        'psv_dz_kefta_fry' => 1.5,
        'psv_dz_smoothies' => 1,
    ];
    foreach ($dz_items as $f => $w) {
        $v = $d[$f] ?? 'Jamais';
        if (in_array($v, ['Tous les jours','5-6/sem'])) { $score += 2 * $w; $flags[] = $f; }
        elseif (in_array($v, ['3-4/sem']))               { $score += 1 * $w; }
    }

    // Protective items (subtract if frequent)
    $protective = [
        'psv_dz_leben'  => 0.5,
        'psv_dz_dates'  => 0.5,
        'psv_dz_olives' => 0.5,
        'psv_dz_harira' => 0.5,  // soupe avec légumineuses si modéré
    ];
    foreach ($protective as $f => $w) {
        $v = $d[$f] ?? 'Jamais';
        if (in_array($v, ['Tous les jours','5-6/sem','3-4/sem'])) { $score -= 1 * $w; }
    }

    $score = max(0, (int) round($score));

    if     ($score <= 3)  $c = 'Équilibré';
    elseif ($score <= 7)  $c = 'Modéré';
    elseif ($score <= 12) $c = 'Élevé';
    else                  $c = 'Très élevé';

    return [
        'dz_diet_score' => $score,
        'dz_diet_class' => $c,
        'dz_diet_flags' => $flags,
    ];
}

// ─── 14. Smoking score ──────────────────────────────────────────────
function psvSmoking($d) {
    $st = $d['psv_smoke_status'] ?? 'none';
    $fq = $d['psv_smoke_freq']   ?? 'never';
    $passive = ($d['psv_smoke_around'] ?? 'Non') === 'Oui';

    $score = 0;
    if ($st !== 'none') {
        $base = ['cig'=>3,'shisha'=>2,'vape'=>1.5,'multi'=>3.5][$st] ?? 0;
        $mult = ['tried'=>0.3,'occasion'=>0.6,'weekly'=>1.0,'daily'=>1.5][$fq] ?? 0;
        $score = $base * $mult;
    }
    if ($passive) $score += 1;

    $score = round($score, 1);

    if     ($score < 0.5) $c = 'Non-fumeur';
    elseif ($score < 2)   $c = 'Exposition légère';
    elseif ($score < 4)   $c = 'Risque modéré';
    else                  $c = 'Risque élevé';

    return [
        'smoking_score' => $score,
        'smoking_class' => $c,
        'smoking_passive' => $passive,
    ];
}

// ─── 15. Social jetlag (calculé à partir des heures coucher/réveil) ─
// Roenneberg 2012 : |midpoint_sleep_weekend - midpoint_sleep_weekday|
// > 1 h = associé à risque cardiométabolique élevé.
function psvJetlag($d) {
    $wd_b = $d['bedtime']      ?? '';
    $wd_w = $d['waketime']     ?? '';
    $we_b = $d['psv_bedtime_we']  ?? '';
    $we_w = $d['psv_waketime_we'] ?? '';

    if (!$wd_b || !$wd_w || !$we_b || !$we_w) {
        return ['jetlag_hours' => null, 'jetlag_class' => 'Non évalué'];
    }

    $t2m = function ($t) {
        if (!preg_match('/^(\d{1,2}):(\d{1,2})/', $t, $m)) return null;
        return (int)$m[1] * 60 + (int)$m[2];
    };

    $bd = $t2m($wd_b); $wd = $t2m($wd_w);
    $be = $t2m($we_b); $we = $t2m($we_w);
    if ($bd===null || $wd===null || $be===null || $we===null) {
        return ['jetlag_hours' => null, 'jetlag_class' => 'Non évalué'];
    }

    // Midpoint of sleep — wrap around midnight
    $mid = function ($bed, $wake) {
        if ($wake < $bed) $wake += 24*60;
        return (($bed + $wake) / 2) % (24 * 60);
    };
    $mid_wd = $mid($bd, $wd);
    $mid_we = $mid($be, $we);
    // Take the shortest difference on the 24h circle
    $diff = abs($mid_we - $mid_wd);
    if ($diff > 12*60) $diff = 24*60 - $diff;
    $diff_h = round($diff / 60, 1);

    if     ($diff_h < 1)  $c = 'Faible';
    elseif ($diff_h < 2)  $c = 'Modéré';
    else                  $c = 'Élevé';

    return [
        'jetlag_hours' => $diff_h,
        'jetlag_class' => $c,
    ];
}

// ─── 16. Non-screen sitting time (study + transport) ────────────────
function psvSitting($d) {
    $study_map = ['lt2'=>1,'2_4'=>3,'4_6'=>5,'6_8'=>7,'gt8'=>9];
    $trans_map = ['lt15'=>0.2,'15_30'=>0.4,'30_60'=>0.8,'1_2h'=>1.5,'gt2h'=>2.5];
    $study  = $study_map[$d['psv_study_hours']    ?? ''] ?? 0;
    $trans  = $trans_map[$d['psv_transport_min']  ?? ''] ?? 0;
    $total  = round($study + $trans, 1);

    if     ($total < 4)  $c = 'Faible';
    elseif ($total < 7)  $c = 'Modéré';
    elseif ($total < 10) $c = 'Élevé';
    else                 $c = 'Très élevé';

    return [
        'sitting_hours' => $total,
        'sitting_class' => $c,
    ];
}

// ─── 17. Early-life antibiotic exposure ─────────────────────────────
function psvAntibiotic($d) {
    $u2 = $d['psv_abx_under2']  ?? 'dontknow';
    $r  = $d['psv_abx_recent']  ?? '0_1';
    $score = 0;
    if      ($u2 === 'many') $score += 2;
    elseif  ($u2 === 'few')  $score += 1;
    if      ($r === 'gt3')   $score += 2;
    elseif  ($r === '2_3')   $score += 1;

    if     ($score === 0) $c = 'Faible';
    elseif ($score <= 2)  $c = 'Modéré';
    else                  $c = 'Élevé';

    return [
        'abx_score' => $score,
        'abx_class' => $c,
        'abx_unknown' => $u2 === 'dontknow',
    ];
}

// ─── 18. Méga-orchestrateur v3 ──────────────────────────────────────
function psvComputeAllV3($d, $iotf, $bmi, $age, $sex) {
    $v3 = [];
    $v3 += psvSES($d);
    $v3 += psvPuberty($d, $sex, $age);
    $v3 += psvDZDiet($d);
    $v3 += psvSmoking($d);
    $v3 += psvJetlag($d);
    $v3 += psvSitting($d);
    $v3 += psvAntibiotic($d);
    return $v3;
}
