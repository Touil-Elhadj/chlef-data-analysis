<?php
require_once '../config.php';
require_once '../lang.php';
$session = checkSession();
header('Content-Type: application/json; charset=utf-8');
if (isGuest()) jsonResponse(['success'=>false,'message'=>__('guest_cannot_save')]);
if ($_SERVER['REQUEST_METHOD']!=='POST') jsonResponse(['success'=>false,'message'=>'Méthode non autorisée']);

$data = json_decode(file_get_contents('php://input'),true);
if (!$data) jsonResponse(['success'=>false,'message'=>'JSON invalide']);

// Validation champs obligatoires
foreach(['questionnaire_num','age','sex','height','weight'] as $f)
    if (empty($data[$f])) jsonResponse(['success'=>false,'message'=>"Champ requis: $f"]);

// ── Validation des valeurs catégorielles ────────────────────
$allowed = [
    'sex'              => ['Garçon','Fille'],
    'grade'            => ['1AS','2AS','3AS'],
    'delivery_type'    => ['Naturel','Césarienne',''],
    'school_avg'       => ['<10','10-12','12-16','>16',''],
    'body_perception'  => ['Beaucoup trop maigre','Un peu trop maigre','Normal','Un peu trop gros',''],
    'tried_lose_weight'=> ['Oui','Non',''],
    'lose_method'      => ['Régime','Sport','Médicaments','Autre',''],
    'weight_variation' => ['Perte notable','Légère perte','Stable','Prise de poids',''],
    'want_weight_change'=> ['Perdre','Maintenir','Prendre',''],
    'smoker'           => ['Jamais fumé','Oui','Ex-fumeur',''],
    'traditional_remedies'=> ['Non','Oui, remèdes traditionnels','Oui, médicaments',''],
    'specialist_consult'=> ['Non','Oui, gastro-entérologue','Oui, nutritionniste',''],
    'coffee_freq'      => ['Tous les jours','4-6/sem','1-3/sem','Jamais',''],
    'sugary_freq'      => ['Tous les jours','4-6/sem','1-3/sem','Jamais',''],
    'energy_freq'      => ['Tous les jours','4-6/sem','1-3/sem','Jamais',''],
    'snacking_freq'    => ['Très souvent','Souvent','Parfois','Rarement','Jamais',''],
    'breakfast_freq'   => ['Oui','4-6/sem','1-3/sem','Jamais','Rarement',''],
    'skip_meal_tutoring'=> ['Oui, souvent','Oui, parfois','Rarement','Jamais',''],
    'sleep_duration'   => ['<6h','6-7h','8h','9h+',''],
    'screen_before_sleep'=> ['<30min','30-60min','>1h',''],
    'wake_exhausted'   => ['Oui, souvent','Oui, parfois','Jamais',''],
    'insomnia'         => ['Oui, souvent','Oui, parfois','Jamais',''],
    'nightmares'       => ['Oui, souvent','Oui, parfois','Jamais',''],
    'sports_club'      => ['Oui','Non',''],
    'transport'        => ['Marche','Vélo','Voiture','Bus',''],
    'walk_to_school'   => ['<10min','10-30min','>30min',''],
    'screen_phone'     => ['<1h','1-2h','2-4h','>4h',''],
    'screen_tv'        => ['<1h','1-2h','2-4h','>4h',''],
    'screen_games'     => ['<1h','1-2h','2-4h','>4h',''],
    'screen_computer'  => ['<1h','1-2h','2-4h','>4h',''],
    'sports_facilities'=> ['Oui','Non',''],
    'nutrition_education'=> ['Jamais','1-2 fois','Régulièrement',''],
    'meals_with_family'=> ['Toujours','Souvent','Rarement','Jamais',''],
    'parents_employment'=> ['Les deux travaillent','Un seul','Aucun',''],
    'mother_education' => ['Sans niveau','Primaire','Moyen','Secondaire','Universitaire',''],
    'academic_stress'  => ['Aucun','Faible','Modéré','Élevé',''],
    'general_health'   => ['Excellente','Bonne','Moyenne','Mauvaise',''],
    'family_support'   => ['Fort','Moyen','Faible','Aucun',''],
    'peer_pressure_fastfood'=> ['Jamais','Parfois','Souvent',''],
    'parent_obese'     => ['Oui','Non',''],
    'follows_nutrition_social'=> ['Oui','Non',''],
    'school_canteen'   => ['Jamais','Parfois','Toujours',''],
    'canteen_fv'       => ['Jamais','Parfois','Toujours',''],
    'canteen_quality'  => ['Mauvaise','Moyenne','Bonne',''],
    'reads_labels'     => ['Jamais','Parfois','Toujours',''],
    'diet_balanced'    => ['Oui','Plutôt oui','Plutôt non','Non',''],
];

$rejected = [];
foreach ($allowed as $field => $vals) {
    $v = $data[$field] ?? '';
    if ($v !== '' && !in_array($v, $vals, true)) {
        $rejected[] = "$field='$v'";
        $data[$field] = ''; // neutraliser la valeur invalide
    }
}
if ($rejected) {
    $warnings[] = "Valeurs corrigées: " . implode(', ', $rejected);
    logError('Invalid values', ['fields' => $rejected, 'ip' => getClientIP()]);
}

// ── Validation numérique stricte ────────────────────────────
$numRanges = [
    'questionnaire_num' => [1, 9999],
    'age'               => [13, 20],
    'height'            => [130, 220],
    'weight'            => [25, 180],
    'birth_weight'      => [0.5, 7],
    'cig_per_day'       => [0, 60],
    'fv_portions'       => [0, 15],
    'active_days_week'  => [0, 7],
    'meals_per_day'     => [1, 6],
    'family_overweight' => [0, 10],
];
foreach ($numRanges as $field => [$min, $max]) {
    if (isset($data[$field]) && $data[$field] !== '') {
        $v = floatval($data[$field]);
        if ($v < $min || $v > $max) {
            $warnings[] = "$field=$v hors plage [$min-$max]";
            if (in_array($field, ['questionnaire_num','age','height','weight'])) {
                jsonResponse(['success'=>false,'message'=>"Valeur hors plage: $field=$v (attendu: $min-$max)"]);
            }
        }
    }
}

// ── Validation FFQ (0-6) ────────────────────────────────────
$ffqFields = ['ffq_fruits_frais','ffq_fruits_secs','ffq_legumes_crus','ffq_legumes_cuits','ffq_legumineuses','ffq_pain','ffq_riz_semoule','ffq_couscous','ffq_pommes_terre','ffq_lait','ffq_yaourt','ffq_fromage','ffq_viande_rouge','ffq_volaille','ffq_poisson','ffq_oeufs','ffq_boissons_sucrees','ffq_gateaux','ffq_chocolat','ffq_noix','ffq_beurre','ffq_huile_olive','ffq_autres_huiles'];
foreach ($ffqFields as $f) {
    if (isset($data[$f]) && $data[$f] !== '') {
        $v = intval($data[$f]);
        if ($v < 0 || $v > 6) {
            $warnings[] = "$f=$v (attendu 0-6)";
            $data[$f] = '';
        }
    }
}

// ── Détection de remplissage suspect ────────────────────────
$ffqVals = [];
foreach ($ffqFields as $f) { if (isset($data[$f]) && $data[$f] !== '') $ffqVals[] = intval($data[$f]); }
if (count($ffqVals) >= 20) {
    $allSame = count(array_unique($ffqVals)) === 1;
    if ($allSame) $warnings[] = "⚠ FFQ suspect: toutes les réponses identiques ($ffqVals[0])";
}

// ── IOTF Cole 2000+2007 ───────────────────────────────────
function getIOTFClass($bmi,$age,$sex){
    $sw=[2.0=>[18.41,20.09,18.02,19.81],2.5=>[18.13,19.80,17.76,19.55],3.0=>[17.89,19.57,17.56,19.36],3.5=>[17.69,19.39,17.40,19.23],4.0=>[17.55,19.29,17.28,19.15],4.5=>[17.47,19.26,17.19,19.12],5.0=>[17.42,19.30,17.15,19.17],5.5=>[17.45,19.47,17.20,19.34],6.0=>[17.55,19.78,17.34,19.65],6.5=>[17.71,20.23,17.53,20.08],7.0=>[17.92,20.63,17.75,20.51],7.5=>[18.16,21.09,18.03,21.01],8.0=>[18.44,21.60,18.35,21.57],8.5=>[18.76,22.17,18.69,22.18],9.0=>[19.10,22.77,19.07,22.81],9.5=>[19.46,23.39,19.45,23.46],10.0=>[19.84,24.00,19.86,24.11],10.5=>[20.20,24.57,20.29,24.77],11.0=>[20.55,25.10,20.74,25.42],11.5=>[20.89,25.58,21.20,26.05],12.0=>[21.22,26.02,21.68,26.67],12.5=>[21.56,26.43,22.14,27.24],13.0=>[21.91,26.84,22.58,27.76],13.5=>[22.27,27.25,22.98,28.20],14.0=>[22.62,27.63,23.34,28.57],14.5=>[22.96,27.98,23.66,28.87],15.0=>[23.29,28.30,23.94,29.11],15.5=>[23.60,28.60,24.17,29.29],16.0=>[23.90,28.88,24.37,29.43],16.5=>[24.19,29.14,24.54,29.56],17.0=>[24.46,29.41,24.70,29.69],17.5=>[24.73,29.70,24.85,29.84],18.0=>[25.00,30.00,25.00,30.00]];
    $th=[2.0=>[14.49,13.19,14.60,13.36],2.5=>[14.27,13.00,14.37,13.14],3.0=>[14.09,12.84,14.18,12.96],3.5=>[13.94,12.70,14.01,12.79],4.0=>[13.79,12.56,13.86,12.64],4.5=>[13.66,12.44,13.73,12.50],5.0=>[13.53,12.32,13.60,12.37],5.5=>[13.41,12.21,13.48,12.25],6.0=>[13.29,12.10,13.37,12.13],6.5=>[13.18,12.00,13.27,12.02],7.0=>[13.09,11.92,13.17,11.91],7.5=>[13.01,11.84,13.09,11.83],8.0=>[12.94,11.77,13.02,11.76],8.5=>[12.89,11.72,12.96,11.70],9.0=>[12.85,11.67,12.92,11.65],9.5=>[12.83,11.64,12.90,11.62],10.0=>[12.83,11.63,12.90,11.60],10.5=>[12.85,11.64,12.91,11.60],11.0=>[12.89,11.66,12.95,11.62],11.5=>[12.95,11.71,13.01,11.65],12.0=>[13.03,11.77,13.10,11.70],12.5=>[13.13,11.85,13.21,11.77],13.0=>[13.25,11.95,13.34,11.86],13.5=>[13.38,12.06,13.49,11.97],14.0=>[13.53,12.19,13.65,12.09],14.5=>[13.69,12.33,13.82,12.23],15.0=>[13.86,12.47,13.99,12.37],15.5=>[14.03,12.62,14.16,12.51],16.0=>[14.21,12.77,14.33,12.66],16.5=>[14.38,12.92,14.49,12.81],17.0=>[14.56,13.08,14.65,12.96],17.5=>[14.74,13.23,14.81,13.11],18.0=>[17.50,16.00,17.50,16.00]];
    $a=max(2.0,min(18.0,round($age*2)/2));
    $b=(float)$bmi; $m=($sex==='Garçon');
    if(!isset($sw[$a])){ if($b>=30)return 'Obésité'; if($b>=25)return 'Surpoids'; if($b>=18.5)return 'Normal'; return 'Minceur'; }
    $S=$sw[$a][$m?0:2]; $O=$sw[$a][$m?1:3]; $T2=$th[$a][$m?0:2]; $T3=$th[$a][$m?1:3];
    if($b>=$O)return 'Obésité'; if($b>=$S)return 'Surpoids'; if($b>=$T2)return 'Normal'; if($b>=$T3)return 'Minceur grade 2'; return 'Minceur grade 3';
}

// ── Calcul IMC et IOTF ──────────────────────────────────────
$h=floatval($data['height']); $w=floatval($data['weight']); $a=floatval($data['age']); $sex=$data['sex']??'Garçon';
$bmi=round($w/(($h/100)**2),1);
if ($bmi<10||$bmi>60) jsonResponse(['success'=>false,'message'=>"IMC impossible: $bmi — vérifiez taille/poids"]);

$iotf=getIOTFClass($bmi,$a,$sex);

// ── Scores dérivés ────────────────────────────────────────
require_once '../computed_scores.php';
$computed = computeAllScores($data,$iotf);

$db=getDB();

// Vérifier doublon
$chk=$db->prepare('SELECT id FROM responses WHERE questionnaire_num=?');
$chk->execute([intval($data['questionnaire_num'])]);
if ($chk->fetch()) jsonResponse(['success'=>false,'message'=>'Numéro de questionnaire déjà enregistré']);

// ── Vérifier le plafond de l'échantillon (TARGET_N) ─────────
// Le numéro de questionnaire peut être quelconque, mais le nombre
// total d'enregistrements ne doit jamais dépasser TARGET_N (900).
$currentTotal = (int)$db->query('SELECT COUNT(*) FROM responses')->fetchColumn();
if ($currentTotal >= TARGET_N) {
    jsonResponse(['success'=>false,'message'=>'Échantillon complet : '.TARGET_N.' enregistrements atteints. Aucune nouvelle saisie possible.']);
}

// Vérifier assignation si définie
$assign=$db->prepare("SELECT id FROM assignments WHERE user_id=? AND ? BETWEEN range_start AND range_end");
$assign->execute([$session['user_id'],intval($data['questionnaire_num'])]);
$assignOk = $assign->fetch() || !$db->query("SELECT COUNT(*) FROM assignments")->fetchColumn();

$ffq_cols=['ffq_fruits_frais','ffq_fruits_secs','ffq_legumes_crus','ffq_legumes_cuits','ffq_legumineuses','ffq_pain','ffq_riz_semoule','ffq_couscous','ffq_pommes_terre','ffq_lait','ffq_yaourt','ffq_fromage','ffq_viande_rouge','ffq_volaille','ffq_poisson','ffq_oeufs','ffq_boissons_sucrees','ffq_gateaux','ffq_chocolat','ffq_noix','ffq_beurre','ffq_huile_olive','ffq_autres_huiles'];
$computed_cols=['score_sommeil','classe_sommeil','score_activite','classe_activite','sleep_hours_real','score_kidmed','classe_kidmed','score_protective','score_risk','ratio_diet','dietary_diversity','classe_diversity','score_pression_scolaire','classe_pression','score_sedentarite','classe_sedentarite','score_famille','classe_famille','obesity_risk_score','obesity_risk_class','perception_gap','screen_hours_total','screen_sum','screen_max','anemia_risk','vitd_risk','eating_disorder_risk','global_nutrition_score','global_nutrition_class'];

// Résoudre le nom d'école (si "autre" choisi)
$schoolName = $data['school'] ?? null;
if ($schoolName === '__other__' && !empty($data['school_other'])) {
    $schoolName = sanitize($data['school_other']);
}

$cols=['questionnaire_num','entered_by','school','commune','age','sex','grade','height','weight','bmi','iotf_class','birth_weight','delivery_type','school_avg','body_perception','tried_lose_weight','lose_method','weight_variation','weight_last','want_weight_change','smoker','cig_per_day','traditional_remedies','specialist_consult','medical_diagnosis',...$ffq_cols,'coffee_freq','coffee_cups','sugary_freq','snacking_freq','energy_freq','water_intake','meals_per_day','breakfast_freq','skip_meal_tutoring','meal_replacement','fv_portions','school_canteen','snacks_outside','food_influence','reads_labels','canteen_fv','canteen_quality','wake_exhausted','insomnia','nightmares','emotional_eating','bedtime','waketime','sleep_duration','screen_before_sleep','sports_club','sport_type','active_days_week','walk_to_school','transport','sports_facilities','nutrition_education','screen_tv','screen_games','screen_computer','screen_phone','meals_with_family','parents_employment','follows_nutrition_social','mother_education','general_health','academic_stress','family_support','peer_pressure_fastfood','family_overweight','parent_obese','knows_fv_portions','knows_bmi','diet_balanced',...$computed_cols];

$values=[intval($data['questionnaire_num']),$session['user_id'],$schoolName,$data['commune']??null,$a,$sex,$data['grade']??null,$h,$w,$bmi,$iotf,!empty($data['birth_weight'])?floatval($data['birth_weight']):null,$data['delivery_type']??null,$data['school_avg']??null,$data['body_perception']??null,$data['tried_lose_weight']??null,$data['lose_method']??null,$data['weight_variation']??null,$data['weight_last']??null,$data['want_weight_change']??null,$data['smoker']??null,!empty($data['cig_per_day'])?(int)$data['cig_per_day']:0,$data['traditional_remedies']??null,$data['specialist_consult']??null,$data['medical_diagnosis']??null];

foreach($ffq_cols as $c) $values[]=isset($data[$c])&&$data[$c]!==''?(int)$data[$c]:null;

$rest=['coffee_freq','coffee_cups','sugary_freq','snacking_freq','energy_freq','water_intake','meals_per_day','breakfast_freq','skip_meal_tutoring','meal_replacement','fv_portions','school_canteen','snacks_outside','food_influence','reads_labels','canteen_fv','canteen_quality','wake_exhausted','insomnia','nightmares','emotional_eating','bedtime','waketime','sleep_duration','screen_before_sleep','sports_club','sport_type','active_days_week','walk_to_school','transport','sports_facilities','nutrition_education','screen_tv','screen_games','screen_computer','screen_phone','meals_with_family','parents_employment','follows_nutrition_social','mother_education','general_health','academic_stress','family_support','peer_pressure_fastfood','family_overweight','parent_obese','knows_fv_portions','knows_bmi','diet_balanced'];
foreach($rest as $f) $values[]=!empty($data[$f])?$data[$f]:null;
foreach($computed_cols as $c) $values[]=$computed[$c]??null;

$ph=implode(',',array_fill(0,count($cols),'?'));
$cn=implode(',',$cols);

try {
    $db->prepare("INSERT INTO responses ($cn) VALUES ($ph)")->execute($values);
    $newId=$db->lastInsertId();
    $total=(int)$db->query('SELECT COUNT(*) FROM responses')->fetchColumn();
    checkMilestones($total);
    auditLog('INSERT','responses',$newId,"Q#{$data['questionnaire_num']} IMC=$bmi IOTF=$iotf");
    jsonResponse(['success'=>true,'message'=>'Enregistrement sauvegardé','id'=>$newId,'bmi'=>$bmi,'iotf'=>$iotf,'global_score'=>$computed['global_nutrition_score'],'global_class'=>$computed['global_nutrition_class'],'obesity_risk'=>$computed['obesity_risk_class'],'score_kidmed'=>$computed['score_kidmed'],'classe_kidmed'=>$computed['classe_kidmed'],'classe_activite'=>$computed['classe_activite'],'classe_sommeil'=>$computed['classe_sommeil'],'perception_gap'=>$computed['perception_gap'],'warnings'=>$warnings,'total'=>$total,'assign_ok'=>$assignOk]);
} catch(PDOException $e){
    logError('INSERT failed',$e->getMessage());
    $msg = APP_DEBUG ? ('Erreur DB: '.$e->getMessage()) : 'Erreur interne — veuillez réessayer';
    jsonResponse(['success'=>false,'message'=>$msg]);
}
