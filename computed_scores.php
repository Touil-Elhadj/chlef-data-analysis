<?php
// ============================================================
// computed_scores.php — Calcul automatique des 16 scores dérivés
// FFQ mis à jour selon le questionnaire validé (3 échelles distinctes)
//
// ÉCHELLE QUOTIDIENNE (index 0-6) :
//   0=Jamais | 1=<1/sem | 2=1/sem | 3=2-4/sem | 4=1/jour | 5=2-3/jour | 6=≥4/jour
//   Aliments: fruits frais, légumes crus, légumes cuits, pain, riz/semoule/pâtes,
//             lait, yaourts, huile olive, autres huiles
//
// ÉCHELLE HEBDOMADAIRE (index 0-6) :
//   0=Jamais | 1=<1/mois | 2=1-3/mois | 3=1/sem | 4=2-4/sem | 5=5-6/sem | 6=1/jour
//   Aliments: fruits secs, légumineuses, couscous, pommes de terre, fromages,
//             viande rouge, volaille, poisson, œufs, boissons sucrées,
//             gâteaux/biscuits, chocolat/confiseries, noix/oléagineux, beurre/margarine
//
// RÉFÉRENCE: Questionnaire validé Chlef 2026 — Adapté HBSC 2022 + IPAQ-A + FFQ
// ============================================================

// ─── CONVERSION FFQ → PORTIONS/SEMAINE ───────────────────
// Chaque fonction retourne le nombre de portions par semaine
// pour permettre une comparaison homogène entre les aliments.

/**
 * Échelle QUOTIDIENNE (index 0-6)
 * Jamais | <1/sem | 1/sem | 2-4/sem | 1/jour | 2-3/jour | ≥4/jour
 */
function ffqDaily_toWeekly($idx) {
    // Valeur médiane de chaque classe, convertie en portions/semaine
    $map = [
        0 => 0.0,    // Jamais
        1 => 0.3,    // < 1/semaine (~1/mois)
        2 => 1.0,    // 1/semaine
        3 => 3.0,    // 2-4/semaine (médiane 3)
        4 => 7.0,    // 1/jour = 7/semaine
        5 => 17.5,   // 2-3/jour (médiane 2.5) × 7
        6 => 28.0,   // ≥4/jour (médiane 4) × 7
    ];
    return $map[(int)$idx] ?? 0.0;
}

/**
 * Échelle HEBDOMADAIRE (index 0-6)
 * Jamais | <1/mois | 1-3/mois | 1/sem | 2-4/sem | 5-6/sem | 1/jour
 */
function ffqWeekly_toWeekly($idx) {
    $map = [
        0 => 0.0,    // Jamais
        1 => 0.1,    // < 1/mois (~0.25/semaine)
        2 => 0.5,    // 1-3/mois (médiane 2/mois = 0.5/semaine)
        3 => 1.0,    // 1/semaine
        4 => 3.0,    // 2-4/semaine (médiane 3)
        5 => 5.5,    // 5-6/semaine (médiane 5.5)
        6 => 7.0,    // 1/jour = 7/semaine
    ];
    return $map[(int)$idx] ?? 0.0;
}

/**
 * Conversion vers portions/jour (pour calculs KIDMED)
 */
function toDaily($weekly) {
    return $weekly / 7.0;
}

// ─── TABLE DES ÉCHELLES PAR ALIMENT ──────────────────────
// Basée sur le questionnaire validé (Q14 à Q36)
// 'D' = Échelle quotidienne | 'W' = Échelle hebdomadaire

$FFQ_SCALES = [
    // Q14 — Fruits frais
    'ffq_fruits_frais'    => 'D',
    // Q15 — Fruits secs
    'ffq_fruits_secs'     => 'W',
    // Q16 — Légumes crus
    'ffq_legumes_crus'    => 'D',
    // Q17 — Légumes cuits
    'ffq_legumes_cuits'   => 'D',
    // Q18 — Légumineuses
    'ffq_legumineuses'    => 'W',
    // Q19 — Pain & viennoiseries
    'ffq_pain'            => 'D',
    // Q20 — Riz, semoule, pâtes
    'ffq_riz_semoule'     => 'D',
    // Q21 — Couscous
    'ffq_couscous'        => 'W',
    // Q22 — Pommes de terre
    'ffq_pommes_terre'    => 'W',
    // Q23 — Lait
    'ffq_lait'            => 'D',
    // Q24 — Yaourts, fromages blancs
    'ffq_yaourt'          => 'D',
    // Q25 — Fromages
    'ffq_fromage'         => 'W',
    // Q26 — Viande rouge
    'ffq_viande_rouge'    => 'W',
    // Q27 — Volaille
    'ffq_volaille'        => 'W',
    // Q28 — Poisson & fruits de mer
    'ffq_poisson'         => 'W',
    // Q29 — Œufs
    'ffq_oeufs'           => 'W',
    // Q30 — Boissons sucrées
    'ffq_boissons_sucrees'=> 'W',
    // Q31 — Gâteaux, biscuits
    'ffq_gateaux'         => 'W',
    // Q32 — Chocolat, confiseries
    'ffq_chocolat'        => 'W',
    // Q33 — Noix, oléagineux
    'ffq_noix'            => 'W',
    // Q34 — Beurre, margarine
    'ffq_beurre'          => 'W',
    // Q35 — Huile d'olive
    'ffq_huile_olive'     => 'D',
    // Q36 — Autres huiles végétales
    'ffq_autres_huiles'   => 'D',
];

/**
 * Convertit un index FFQ en portions/semaine selon l'échelle de l'aliment
 */
function ffqToWeekly($idx, $field) {
    global $FFQ_SCALES;
    $scale = $FFQ_SCALES[$field] ?? 'W';
    if ($scale === 'D') return ffqDaily_toWeekly($idx);
    return ffqWeekly_toWeekly($idx);
}

/**
 * Raccourci: index FFQ → portions/jour
 */
function ffqToDaily($idx, $field) {
    return ffqToWeekly($idx, $field) / 7.0;
}

// ════════════════════════════════════════════════════════════
// SCORE 1 — QUALITÉ DU SOMMEIL (0-13)
// ════════════════════════════════════════════════════════════
function computeSleepScore($d) {
    $score = 0;

    if ($d['wake_exhausted'] === 'Oui, souvent')   $score += 3;
    elseif ($d['wake_exhausted'] === 'Oui, parfois') $score += 1;

    if ($d['insomnia'] === 'Oui, souvent')   $score += 3;
    elseif ($d['insomnia'] === 'Oui, parfois') $score += 1;

    if ($d['nightmares'] === 'Oui, souvent')   $score += 2;
    elseif ($d['nightmares'] === 'Oui, parfois') $score += 1;

    if ($d['sleep_duration'] === '<6h')    $score += 3;
    elseif ($d['sleep_duration'] === '6-7h') $score += 1;

    if ($d['screen_before_sleep'] === '>1h')        $score += 2;
    elseif ($d['screen_before_sleep'] === '30-60min') $score += 1;

    $classe = $score <= 3 ? 'Bon' : ($score <= 7 ? 'Perturbé' : 'Mauvais');
    return ['score_sommeil' => min($score, 13), 'classe_sommeil' => $classe];
}

// ════════════════════════════════════════════════════════════
// SCORE 2 — ACTIVITÉ PHYSIQUE (0-12)
// ════════════════════════════════════════════════════════════
function computeActivityScore($d) {
    $score = 0;
    $days  = (int)($d['active_days_week'] ?? 0);
    $score += min($days, 7);

    if ($d['sports_club'] === 'Oui') $score += 2;
    if (in_array($d['transport'], ['Marche','Vélo'])) $score += 1;

    if ($d['walk_to_school'] === '>30min')    $score += 2;
    elseif ($d['walk_to_school'] === '10-30min') $score += 1;

    $classe = $score <= 2 ? 'Inactif'
            : ($score <= 5 ? 'Peu actif'
            : ($score <= 9 ? 'Actif' : 'Très actif'));
    return ['score_activite' => min($score, 12), 'classe_activite' => $classe];
}

// ════════════════════════════════════════════════════════════
// SCORE 3 — HEURES DE SOMMEIL RÉELLES
// ════════════════════════════════════════════════════════════
function computeRealSleepHours($d) {
    $bed  = $d['bedtime']  ?? '';
    $wake = $d['waketime'] ?? '';
    if (!$bed || !$wake) return ['sleep_hours_real' => null];
    try {
        $b = new DateTime($bed);
        $w = new DateTime($wake);
        if ($w <= $b) $w->modify('+1 day');
        $diff  = $b->diff($w);
        $hours = round($diff->h + $diff->i / 60, 2);
        return ['sleep_hours_real' => $hours];
    } catch (Exception $e) {
        return ['sleep_hours_real' => null];
    }
}

// ════════════════════════════════════════════════════════════
// SCORE 4 — KIDMED (Score Méditerranéen) (-3 à +12)
// Adapté aux 3 échelles FFQ du questionnaire validé
// ════════════════════════════════════════════════════════════
function computeKIDMED($d) {
    $score = 0;

    // ── Points POSITIFS ───────────────────────────────────

    // Fruits frais ≥ 1/jour (échelle quotidienne)
    if (ffqToDaily($d['ffq_fruits_frais'] ?? 0, 'ffq_fruits_frais') >= 1.0)
        $score += 1;

    // Légumes crus OU cuits ≥ 1/jour (échelles quotidiennes)
    $legTotal = ffqToDaily($d['ffq_legumes_crus']  ?? 0, 'ffq_legumes_crus')
              + ffqToDaily($d['ffq_legumes_cuits'] ?? 0, 'ffq_legumes_cuits');
    if ($legTotal >= 1.0) $score += 1;

    // Légumineuses ≥ 2/semaine (échelle hebdomadaire)
    if (ffqToWeekly($d['ffq_legumineuses'] ?? 0, 'ffq_legumineuses') >= 2.0)
        $score += 1;

    // Poisson ≥ 2/semaine (échelle hebdomadaire)
    if (ffqToWeekly($d['ffq_poisson'] ?? 0, 'ffq_poisson') >= 2.0)
        $score += 1;

    // Viande rouge < 2/semaine (échelle hebdomadaire) → POSITIF
    if (ffqToWeekly($d['ffq_viande_rouge'] ?? 0, 'ffq_viande_rouge') < 2.0)
        $score += 1;

    // Lait OU yaourt ≥ 1/jour (échelles quotidiennes)
    $laitTotal = ffqToDaily($d['ffq_lait']   ?? 0, 'ffq_lait')
               + ffqToDaily($d['ffq_yaourt'] ?? 0, 'ffq_yaourt');
    if ($laitTotal >= 1.0) $score += 1;

    // Céréales (pain OU riz/semoule OU couscous) ≥ 1/jour
    // Pain et riz: échelle quotidienne | Couscous: échelle hebdomadaire
    $cerealTotal = ffqToDaily($d['ffq_pain']       ?? 0, 'ffq_pain')
                 + ffqToDaily($d['ffq_riz_semoule'] ?? 0, 'ffq_riz_semoule')
                 + ffqToWeekly($d['ffq_couscous']  ?? 0, 'ffq_couscous') / 7;
    if ($cerealTotal >= 1.0) $score += 1;

    // Huile d'olive ≥ 1/jour (échelle quotidienne)
    if (ffqToDaily($d['ffq_huile_olive'] ?? 0, 'ffq_huile_olive') >= 1.0)
        $score += 1;

    // Noix/oléagineux ≥ 2/semaine (échelle hebdomadaire)
    if (ffqToWeekly($d['ffq_noix'] ?? 0, 'ffq_noix') >= 2.0)
        $score += 1;

    // ── Points NÉGATIFS ───────────────────────────────────

    // Boissons sucrées ≥ 1/jour (échelle hebdomadaire → indice 6 = 7/sem)
    if (ffqToWeekly($d['ffq_boissons_sucrees'] ?? 0, 'ffq_boissons_sucrees') >= 7.0)
        $score -= 1;

    // Gâteaux/biscuits ≥ 1/jour
    if (ffqToWeekly($d['ffq_gateaux'] ?? 0, 'ffq_gateaux') >= 7.0)
        $score -= 1;

    // Chocolat/confiseries ≥ 1/jour
    if (ffqToWeekly($d['ffq_chocolat'] ?? 0, 'ffq_chocolat') >= 7.0)
        $score -= 1;

    $classe = $score >= 8  ? 'Optimal'
            : ($score >= 4 ? 'Amélioration nécessaire'
            :                'Mauvais');

    return ['score_kidmed' => $score, 'classe_kidmed' => $classe];
}

// ════════════════════════════════════════════════════════════
// SCORE 5 — RATIO ALIMENTS PROTECTEURS / À RISQUE
// ════════════════════════════════════════════════════════════
function computeDietRatio($d) {
    // Aliments protecteurs (score 0-10)
    $p = 0;
    $p += min(ffqToDaily($d['ffq_fruits_frais']  ?? 0, 'ffq_fruits_frais'),  2);
    $p += min(ffqToDaily($d['ffq_legumes_crus']  ?? 0, 'ffq_legumes_crus'),  2);
    $p += min(ffqToDaily($d['ffq_legumes_cuits'] ?? 0, 'ffq_legumes_cuits'), 2);
    $p += min(ffqToWeekly($d['ffq_legumineuses'] ?? 0, 'ffq_legumineuses') / 7, 1);
    $p += min(ffqToWeekly($d['ffq_poisson']      ?? 0, 'ffq_poisson')      / 7, 1);
    $protective = round(min($p, 10));

    // Aliments à risque (score 0-8)
    $r = 0;
    $r += min(ffqToWeekly($d['ffq_boissons_sucrees'] ?? 0, 'ffq_boissons_sucrees') / 7, 2);
    $r += min(ffqToWeekly($d['ffq_gateaux']          ?? 0, 'ffq_gateaux')          / 7, 2);
    $r += min(ffqToWeekly($d['ffq_chocolat']         ?? 0, 'ffq_chocolat')         / 7, 2);
    $r += min(ffqToWeekly($d['ffq_beurre']           ?? 0, 'ffq_beurre')           / 7, 1);
    $risk = round(min($r, 8));

    $ratio = $risk > 0 ? round($protective / $risk, 2)
           : ($protective > 0 ? 9.99 : 1.00);

    return [
        'score_protective' => $protective,
        'score_risk'       => $risk,
        'ratio_diet'       => $ratio,
    ];
}

// ════════════════════════════════════════════════════════════
// SCORE 6 — DIVERSITÉ ALIMENTAIRE (0-7 groupes)
// ════════════════════════════════════════════════════════════
function computeDietaryDiversity($d) {
    // Un groupe est "consommé" si au moins 1x/semaine
    $threshold_weekly = 1.0; // portions/semaine minimum

    $groups = [
        // Groupe 1: Fruits
        max(ffqToWeekly($d['ffq_fruits_frais'] ?? 0, 'ffq_fruits_frais'),
            ffqToWeekly($d['ffq_fruits_secs']  ?? 0, 'ffq_fruits_secs')),
        // Groupe 2: Légumes + légumineuses
        max(ffqToWeekly($d['ffq_legumes_crus']  ?? 0, 'ffq_legumes_crus'),
            ffqToWeekly($d['ffq_legumes_cuits'] ?? 0, 'ffq_legumes_cuits'),
            ffqToWeekly($d['ffq_legumineuses']  ?? 0, 'ffq_legumineuses')),
        // Groupe 3: Céréales et féculents
        max(ffqToWeekly($d['ffq_pain']         ?? 0, 'ffq_pain'),
            ffqToWeekly($d['ffq_riz_semoule']  ?? 0, 'ffq_riz_semoule'),
            ffqToWeekly($d['ffq_couscous']     ?? 0, 'ffq_couscous'),
            ffqToWeekly($d['ffq_pommes_terre'] ?? 0, 'ffq_pommes_terre')),
        // Groupe 4: Produits laitiers
        max(ffqToWeekly($d['ffq_lait']    ?? 0, 'ffq_lait'),
            ffqToWeekly($d['ffq_yaourt']  ?? 0, 'ffq_yaourt'),
            ffqToWeekly($d['ffq_fromage'] ?? 0, 'ffq_fromage')),
        // Groupe 5: Viandes, poisson, œufs
        max(ffqToWeekly($d['ffq_viande_rouge'] ?? 0, 'ffq_viande_rouge'),
            ffqToWeekly($d['ffq_volaille']     ?? 0, 'ffq_volaille'),
            ffqToWeekly($d['ffq_poisson']      ?? 0, 'ffq_poisson'),
            ffqToWeekly($d['ffq_oeufs']        ?? 0, 'ffq_oeufs')),
        // Groupe 6: Produits sucrés et snacks
        max(ffqToWeekly($d['ffq_boissons_sucrees'] ?? 0, 'ffq_boissons_sucrees'),
            ffqToWeekly($d['ffq_gateaux']          ?? 0, 'ffq_gateaux'),
            ffqToWeekly($d['ffq_chocolat']         ?? 0, 'ffq_chocolat'),
            ffqToWeekly($d['ffq_noix']             ?? 0, 'ffq_noix')),
        // Groupe 7: Matières grasses
        max(ffqToWeekly($d['ffq_beurre']        ?? 0, 'ffq_beurre'),
            ffqToWeekly($d['ffq_huile_olive']   ?? 0, 'ffq_huile_olive'),
            ffqToWeekly($d['ffq_autres_huiles'] ?? 0, 'ffq_autres_huiles')),
    ];

    $diversite = count(array_filter($groups, fn($g) => $g >= $threshold_weekly));
    $classe = $diversite <= 3 ? 'Faible' : ($diversite <= 5 ? 'Moyen' : 'Bon');
    return ['dietary_diversity' => $diversite, 'classe_diversity' => $classe];
}

// ════════════════════════════════════════════════════════════
// SCORE 7 — PRESSION SCOLAIRE-ALIMENTATION (0-9)
// Variable originale spécifique à cette étude
// ════════════════════════════════════════════════════════════
function computeSchoolPressureScore($d) {
    $score = 0;

    if ($d['skip_meal_tutoring'] === 'Oui, souvent')  $score += 3;
    elseif ($d['skip_meal_tutoring'] === 'Oui, parfois') $score += 2;
    elseif ($d['skip_meal_tutoring'] === 'Rarement')     $score += 1;

    if ($d['breakfast_freq'] === 'Jamais')     $score += 2;
    elseif ($d['breakfast_freq'] === '1-3/sem') $score += 1;

    if ($d['snacks_outside'] === '3+/sem')     $score += 2;
    elseif ($d['snacks_outside'] === '1-2/sem') $score += 1;

    if ($d['academic_stress'] === 'Élevé')     $score += 1;
    if (in_array($d['meals_per_day'], [1, 2, '1', '2'])) $score += 1;

    $classe = $score <= 2 ? 'Faible' : ($score <= 5 ? 'Modérée' : 'Élevée');
    return ['score_pression_scolaire' => min($score, 9), 'classe_pression' => $classe];
}

// ════════════════════════════════════════════════════════════
// SCORE 8 — SÉDENTARITÉ (0-15)
// ════════════════════════════════════════════════════════════
function computeSedentarityScore($d) {
    $score = 0;
    $screenMap = ['<1h'=>0, '1-2h'=>1, '2-4h'=>2, '>4h'=>3];

    $score += $screenMap[$d['screen_phone']    ?? ''] ?? 0;
    $score += $screenMap[$d['screen_tv']       ?? ''] ?? 0;
    $score += $screenMap[$d['screen_games']    ?? ''] ?? 0;
    $score += $screenMap[$d['screen_computer'] ?? ''] ?? 0;

    if (in_array($d['transport'], ['Voiture','Bus'])) $score += 1;
    if (($d['active_days_week'] ?? 1) == 0)  $score += 3;
    elseif (($d['active_days_week'] ?? 1) <= 2) $score += 1;

    $classe = $score <= 3 ? 'Actif'
            : ($score <= 7 ? 'Modérément sédentaire' : 'Sédentaire');
    return ['score_sedentarite' => min($score, 15), 'classe_sedentarite' => $classe];
}

// ════════════════════════════════════════════════════════════
// SCORE 9 — ENVIRONNEMENT FAMILIAL (-3 à +8)
// ════════════════════════════════════════════════════════════
function computeFamilyScore($d) {
    $score = 0;

    if ($d['meals_with_family'] === 'Toujours') $score += 2;
    elseif ($d['meals_with_family'] === 'Souvent') $score += 1;

    if ($d['mother_education'] === 'Universitaire') $score += 2;
    elseif ($d['mother_education'] === 'Secondaire') $score += 1;

    if ($d['family_support'] === 'Fort')   $score += 2;
    elseif ($d['family_support'] === 'Moyen') $score += 1;

    if ($d['parents_employment'] === 'Les deux travaillent') $score += 1;

    if ($d['peer_pressure_fastfood'] === 'Souvent')  $score -= 2;
    elseif ($d['peer_pressure_fastfood'] === 'Parfois') $score -= 1;

    if ($d['parent_obese'] === 'Oui') $score -= 1;

    $classe = $score <= 1 ? 'Défavorable' : ($score <= 4 ? 'Neutre' : 'Favorable');
    return ['score_famille' => $score, 'classe_famille' => $classe];
}

// ════════════════════════════════════════════════════════════
// SCORE 10 — RISQUE D'OBÉSITÉ COMPOSITE (0-13)
// ════════════════════════════════════════════════════════════
function computeObesityRisk($d, $iotf) {
    $score = 0;

    if ($d['parent_obese'] === 'Oui')                       $score += 3;
    if ($d['delivery_type'] === 'Césarienne')               $score += 1;

    $bw = floatval($d['birth_weight'] ?? 3.2);
    if ($bw < 2.5 || $bw > 4.0)                            $score += 1;

    if ($d['sleep_duration'] === '<6h')                     $score += 2;
    elseif ($d['sleep_duration'] === '6-7h')                $score += 1;

    if (($d['active_days_week'] ?? 1) == 0)                 $score += 2;
    elseif (($d['active_days_week'] ?? 1) <= 2)             $score += 1;

    // Boissons sucrées (échelle hebdomadaire) : ≥1/jour = indice 6 = 7/sem
    $bsWeekly = ffqToWeekly($d['ffq_boissons_sucrees'] ?? 0, 'ffq_boissons_sucrees');
    if ($bsWeekly >= 7.0) $score += 2;        // ≥1/jour
    elseif ($bsWeekly >= 4.0) $score += 1;   // ≥2-4/sem

    if ($d['skip_meal_tutoring'] === 'Oui, souvent')        $score += 1;
    if ($d['breakfast_freq'] === 'Jamais')                  $score += 1;

    $classe = $score <= 3 ? 'Faible' : ($score <= 7 ? 'Modéré' : 'Élevé');
    return ['obesity_risk_score' => min($score, 13), 'obesity_risk_class' => $classe];
}

// ════════════════════════════════════════════════════════════
// SCORE 11 — DISCORDANCE PERCEPTION / RÉALITÉ IOTF
// ════════════════════════════════════════════════════════════
function computePerceptionGap($d, $iotf) {
    $realMap = [
        'Minceur grade 3' => -2, 'Minceur grade 2' => -1, 'Minceur' => -1,
        'Normal' => 0, 'Surpoids' => 1, 'Obésité' => 2
    ];
    $percMap = [
        'Beaucoup trop maigre' => -2, 'Un peu trop maigre' => -1,
        'Normal' => 0, 'Un peu trop gros' => 1
    ];
    $real      = $realMap[$iotf] ?? 0;
    $perceived = $percMap[$d['body_perception'] ?? ''] ?? 0;
    $gap       = $perceived - $real;

    if ($gap === 0)    $result = 'Concordant';
    elseif ($gap < 0)  $result = 'Sous-estime son poids';
    else               $result = 'Surestime son poids';

    return ['perception_gap' => $result];
}

// ════════════════════════════════════════════════════════════
// SCORE 12 — HEURES D'ÉCRAN (TROIS INDICATEURS)
// ────────────────────────────────────────────────────────────
// Conformément aux recommandations méthodologiques HBSC 2018
// (Ng et al.) sur la prise en compte du multi-screen behavior,
// la plateforme calcule trois indicateurs complémentaires :
//   - screen_hours_total : somme cumulée des 4 supports (LEGACY,
//     conservé pour comparabilité avec la littérature antérieure)
//   - screen_sum         : alias de screen_hours_total avec
//     précision en virgule (h/jour, arrondi 0,5)
//   - screen_max         : temps d'écran maximum sur un support
//     unique (INDICATEUR PRINCIPAL recommandé), insensible au
//     biais de double-comptage du multi-screen behavior
// ════════════════════════════════════════════════════════════
function computeScreenHours($d) {
    $midMap = ['<1h'=>0.5, '1-2h'=>1.5, '2-4h'=>3.0, '>4h'=>5.0];
    $vals = [];
    foreach (['screen_phone','screen_tv','screen_games','screen_computer'] as $f)
        $vals[] = $midMap[$d[$f] ?? ''] ?? 0;
    $sum = array_sum($vals);
    $max = !empty($vals) ? max($vals) : 0;
    return [
        'screen_hours_total' => (int)round($sum),  // LEGACY (compat)
        'screen_sum'         => round($sum, 1),    // cumulé en h/j
        'screen_max'         => round($max, 1),    // PRINCIPAL — max par support
    ];
}

// ════════════════════════════════════════════════════════════
// SCORE 13 — PROXY RISQUE ANÉMIE
// ════════════════════════════════════════════════════════════
function computeAnemiaRisk($d) {
    $score = 0;
    // Viande rouge < 1/sem (échelle hebdomadaire)
    if (ffqToWeekly($d['ffq_viande_rouge'] ?? 0, 'ffq_viande_rouge') < 1.0) $score++;
    // Poisson < 1/sem
    if (ffqToWeekly($d['ffq_poisson']      ?? 0, 'ffq_poisson')      < 1.0) $score++;
    // Légumineuses < 1/sem
    if (ffqToWeekly($d['ffq_legumineuses'] ?? 0, 'ffq_legumineuses') < 1.0) $score++;
    // Sexe féminin = facteur aggravant
    if (($d['sex'] ?? '') === 'Fille') $score++;

    $classe = $score <= 1 ? 'Faible' : ($score <= 2 ? 'Modéré' : 'Élevé');
    return ['anemia_risk' => $classe];
}

// ════════════════════════════════════════════════════════════
// SCORE 14 — PROXY RISQUE DÉFICIT VITAMINE D
// ════════════════════════════════════════════════════════════
function computeVitDRisk($d) {
    $score = 0;
    if (($d['active_days_week'] ?? 0) == 0)  $score += 2;
    elseif (($d['active_days_week'] ?? 0) <= 2) $score += 1;

    // Lait < 1/sem (échelle quotidienne)
    if (ffqToWeekly($d['ffq_lait']   ?? 0, 'ffq_lait')   < 1.0) $score++;
    // Poisson < 1/sem
    if (ffqToWeekly($d['ffq_poisson']?? 0, 'ffq_poisson') < 1.0) $score++;
    // Œufs < 1/sem
    if (ffqToWeekly($d['ffq_oeufs']  ?? 0, 'ffq_oeufs')  < 1.0) $score++;

    $classe = $score <= 1 ? 'Faible' : ($score <= 3 ? 'Modéré' : 'Élevé');
    return ['vitd_risk' => $classe];
}

// ════════════════════════════════════════════════════════════
// SCORE 15 — PROXY RISQUE TROUBLE ALIMENTAIRE
// ════════════════════════════════════════════════════════════
function computeEatingDisorderRisk($d, $iotf) {
    $score = 0;
    if ($d['tried_lose_weight'] === 'Oui') $score += 2;

    if (($d['perception_gap'] ?? '') !== 'Concordant'
     && !empty($d['perception_gap'])) $score += 1;

    if (!empty($d['emotional_eating'])
     && $d['emotional_eating'] !== 'Non, je ne mange pas en réponse aux émotions')
        $score += 1;

    if ($d['wake_exhausted'] === 'Oui, souvent') $score += 1;

    if ($d['want_weight_change'] === 'Oui, perdre du poids'
     && in_array($iotf, ['Normal','Minceur grade 2','Minceur grade 3','Minceur']))
        $score += 2;

    $classe = $score <= 1 ? 'Faible' : ($score <= 3 ? 'Modéré' : 'Élevé');
    return ['eating_disorder_risk' => $classe];
}

// ════════════════════════════════════════════════════════════
// SCORE 16 — SCORE GLOBAL NUTRITION (0-100)
// ════════════════════════════════════════════════════════════
function computeGlobalScore($scores) {
    $s = 0;

    // Sommeil (max 20 pts) — inversé
    $s += (1 - min($scores['score_sommeil'] / 13, 1)) * 20;

    // Activité physique (max 20 pts)
    $s += min($scores['score_activite'] / 12, 1) * 20;

    // KIDMED (max 25 pts) — normalisé entre -3 et 9
    $kidmed_norm = ($scores['score_kidmed'] + 3) / 12;
    $s += min(max($kidmed_norm, 0), 1) * 25;

    // Sédentarité (max 15 pts) — inversé
    $s += (1 - min($scores['score_sedentarite'] / 15, 1)) * 15;

    // Environnement familial (max 10 pts) — normalisé entre -3 et 8
    $fam_norm = ($scores['score_famille'] + 3) / 11;
    $s += min(max($fam_norm, 0), 1) * 10;

    // Pression scolaire (max 10 pts) — inversé
    $s += (1 - min($scores['score_pression_scolaire'] / 9, 1)) * 10;

    $global = (int)round(min($s, 100));
    $classe = $global >= 75 ? 'Excellent'
            : ($global >= 55 ? 'Bon'
            : ($global >= 35 ? 'Moyen' : 'Mauvais'));

    return ['global_nutrition_score' => $global, 'global_nutrition_class' => $classe];
}

// ════════════════════════════════════════════════════════════
// FONCTION PRINCIPALE
// Appel: $scores = computeAllScores($data, $iotf);
// ════════════════════════════════════════════════════════════
function computeAllScores($d, $iotf) {
    $all = [];
    $all += computeSleepScore($d);
    $all += computeActivityScore($d);
    $all += computeRealSleepHours($d);
    $all += computeKIDMED($d);
    $all += computeDietRatio($d);
    $all += computeDietaryDiversity($d);
    $all += computeSchoolPressureScore($d);
    $all += computeSedentarityScore($d);
    $all += computeFamilyScore($d);
    $all += computeObesityRisk($d, $iotf);
    $all += computePerceptionGap($d, $iotf);
    $all += computeScreenHours($d);
    $all += computeAnemiaRisk($d);
    $all += computeVitDRisk($d);

    // eating_disorder_risk a besoin de perception_gap
    $d['perception_gap'] = $all['perception_gap'];
    $all += computeEatingDisorderRisk($d, $iotf);

    // score global (dernier — dépend de tous les autres)
    $all += computeGlobalScore($all);

    return $all;
}
