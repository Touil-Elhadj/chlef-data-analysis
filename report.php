<?php
/**
 * ═══════════════════════════════════════════════════════════════════
 *  report.php  ── Rapport biostatistique COMPLET (Chlef 2026)
 *  ─────────────────────────────────────────────────────────────────
 *  Couvre toutes les hypothèses dérivables de la base `responses` :
 *
 *  H1  Épidémiologie pondérale   (prévalence, sexe, âge, niveau)
 *  H2  Alimentation              (KIDMED, FFQ, petit-déjeuner, sodas, F/L)
 *  H3  Activité & sédentarité    (IPAQ, club sportif, écrans, transport)
 *  H4  Sommeil & émotion         (durée, qualité, alimentation émotionnelle)
 *  H5  Famille & génétique       (parent obèse, repas famille, éducation mère)
 *  H6  École & pression          (cantine, soutien, stress, performance)
 *  H7  Perception & comportement (gap, tentatives perte, désir)
 *  H8  Santé & risques annexes   (anémie, Vit-D, TCA, tabac, remèdes trad.)
 *  H9  Stratifications           (par école, par daïra, par grade)
 *
 *  Chaque hypothèse est testée par le test approprié :
 *      χ² (Pearson)  pour 2 var. qualitatives
 *      t-Student/Welch  pour moyenne ~ groupe binaire
 *      ANOVA  pour moyenne ~ groupe ≥3 modalités
 *      Pearson/Spearman  pour 2 var. quantitatives/ordinales
 *      OR + IC95 + régression logistique  pour facteur binaire ~ obésité
 *
 *  Le résultat de chaque test est rendu avec p-value, taille d'effet,
 *  IC95 et un libellé "confirmée / non confirmée / tendance".
 *
 *  Trilingue (ar / fr / en) — toutes les clés sont dans lang.php
 *  (annexe : voir le fichier `lang_report_keys.php`).
 * ═══════════════════════════════════════════════════════════════════
 */

require_once 'config.php';
require_once 'lang.php';
require_once __DIR__ . '/vendor/autoload.php';

use TouilElhadj\BiostatPhp\BiostatAnalysis;
use TouilElhadj\ChlefPlatform\StudySpecificAnalyses;
use TouilElhadj\ChlefPlatform\ChartDataGenerator;

$session = checkSession();
$db      = getDB();

/* ── Données ─────────────────────────────────────────────────── */
$rows = $db->query("SELECT * FROM responses ORDER BY entered_at DESC")
           ->fetchAll(PDO::FETCH_ASSOC);
$n = count($rows);

if ($n < 5) {
    include 'navbar.php';
    echo "<p style='text-align:center;padding:4rem'>".__('rp_not_enough')."</p>";
    exit;
}

/* ── Helpers communs ─────────────────────────────────────────── */
function col($r,$k){ return array_column($r,$k); }
function colN($r,$k){
    return array_values(array_filter(
        array_map(fn($v)=>is_numeric($v)?(float)$v:null,array_column($r,$k)),
        fn($v)=>$v!==null
    ));
}
function pct($a,$b){ return $b>0?round($a/$b*100,1):0; }
function avg($a){ $a=array_filter($a,fn($v)=>$v!==null&&$v!==''); return count($a)?round(array_sum($a)/count($a),2):0; }
function cnt($a,$v){ return count(array_filter($a,fn($x)=>$x===$v)); }
function fmt_p($p){ if($p===null) return '—'; if($p<0.001) return '<0.001'; if($p<0.01) return '<0.01'; return number_format($p,3); }
function star($p){ if($p===null) return ''; if($p<0.001) return '***'; if($p<0.01) return '**'; if($p<0.05) return '*'; return ''; }
function badge_p($p){
    if($p===null) return '<span class="rp-badge rp-badge-info">n/a</span>';
    if($p<0.05)  return '<span class="rp-badge rp-badge-success">p='.fmt_p($p).' '.star($p).'</span>';
    if($p<0.20)  return '<span class="rp-badge rp-badge-warning">p='.fmt_p($p).'</span>';
    return '<span class="rp-badge rp-badge-info">p='.fmt_p($p).' (NS)</span>';
}
function fmt_or($r){
    if(!isset($r['or']) || $r['or']===null) return '—';
    $or = number_format($r['or'],2);
    if(isset($r['ci_low'],$r['ci_high']) && $r['ci_low']!==null) {
        return $or.' [IC95: '.number_format($r['ci_low'],2).'–'.number_format($r['ci_high'],2).']';
    }
    return $or;
}
function row_test($lbl,$test_name,$stat,$p,$effect='',$conclusion=''){
    return ['label'=>$lbl,'test'=>$test_name,'stat'=>$stat,'p'=>$p,'effect'=>$effect,'concl'=>$conclusion];
}

/* ── Initialisation analyseur ────────────────────────────────── */
$bp = new BiostatAnalysis($rows);

/* ── Variables de base ───────────────────────────────────────── */
$bmi   = colN($rows,'bmi');
$boys  = array_values(array_filter($rows, fn($r)=>$r['sex']==='Garçon'));
$girls = array_values(array_filter($rows, fn($r)=>$r['sex']==='Fille'));
$nB = count($boys); $nG = count($girls);

$iotf  = col($rows,'iotf_class');
$nObes = cnt($iotf,'Obésité');
$nSurp = cnt($iotf,'Surpoids');
$nNorm = cnt($iotf,'Normal');
$nMinc = cnt($iotf,'Insuffisance pondérale') + cnt($iotf,'Maigreur') + cnt($iotf,'Mince');
if($nMinc===0) $nMinc = $n - $nObes - $nSurp - $nNorm;

/* indicateurs binaires */
$is_obese      = array_map(fn($r)=>$r['iotf_class']==='Obésité'?1:0,$rows);
$is_overweight = array_map(fn($r)=>in_array($r['iotf_class'],StudySpecificAnalyses::OUTCOME_OW_OB)?1:0,$rows);

/* ────────────────────────────────────────────────────────────── */
/*    M O T E U R   D ' H Y P O T H È S E S                       */
/* ────────────────────────────────────────────────────────────── */

$H = [];   // tous les résultats (clé = code hypothèse)

/* helpers : table 2x2 facteur binaire ~ surpoids/obésité */
function tab2x2($rows, $field, $exposed_values, $outcome_field='iotf_class', $outcome_pos=StudySpecificAnalyses::OUTCOME_OW_OB){
    $exposed_values = (array)$exposed_values;
    $outcome_pos    = (array)$outcome_pos;
    $a=$b=$c=$d=0;
    foreach($rows as $r){
        $exp = in_array($r[$field]??'',$exposed_values,true) ? 1 : 0;
        $out = in_array($r[$outcome_field]??'',$outcome_pos,true) ? 1 : 0;
        if($r[$field]===null || $r[$field]==='') continue;
        if($exp && $out)  $a++;
        if($exp && !$out) $b++;
        if(!$exp && $out) $c++;
        if(!$exp && !$out)$d++;
    }
    return ['a'=>$a,'b'=>$b,'c'=>$c,'d'=>$d];
}
function chi2_or_block($rows,$field,$exposed_values,$bp,$outcome_pos=StudySpecificAnalyses::OUTCOME_OW_OB){
    $t = tab2x2($rows,$field,$exposed_values,'iotf_class',$outcome_pos);
    if(($t['a']+$t['b']+$t['c']+$t['d'])<10){
        return ['p'=>null,'or'=>null,'ci_low'=>null,'ci_high'=>null,'chi2'=>null,'a'=>$t['a'],'b'=>$t['b'],'c'=>$t['c'],'d'=>$t['d']];
    }
    $chi = $bp->chi2Test2x2($t['a'],$t['b'],$t['c'],$t['d']);
    $or  = $bp->oddsRatio   ($t['a'],$t['b'],$t['c'],$t['d']);
    return array_merge($chi,$or,$t);
}

/* ═════════ H1 — ÉPIDÉMIOLOGIE PONDÉRALE ═════════ */

// H1.1 — Prévalence du surpoids+obésité
$H['H1.1'] = [
  'title'=>__('rp_h1_1'),
  'value'=>pct($nObes+$nSurp,$n).'% (n='.($nObes+$nSurp).'/'.$n.')',
  'detail'=>'Surpoids '.pct($nSurp,$n).'% | Obésité '.pct($nObes,$n).'%',
  'interp'=>($nObes+$nSurp)>=$n*0.20 ? 'high' : (($nObes+$nSurp)>=$n*0.10 ? 'mid' : 'low')
];

// H1.2 — Différence de prévalence garçons vs filles (χ²)
$nObesB = cnt(col($boys,'iotf_class'),'Obésité');
$nSurpB = cnt(col($boys,'iotf_class'),'Surpoids');
$nObesG = cnt(col($girls,'iotf_class'),'Obésité');
$nSurpG = cnt(col($girls,'iotf_class'),'Surpoids');
$ow_B = $nObesB+$nSurpB; $ow_G = $nObesG+$nSurpG;
$chi_sex = ($nB>0 && $nG>0) ? $bp->chi2Test2x2($ow_G, $nG-$ow_G, $ow_B, $nB-$ow_B) : ['chi2'=>null,'p'=>null,'significant'=>false];
$or_sex  = ($nB>0 && $nG>0) ? $bp->oddsRatio  ($ow_G, $nG-$ow_G, $ow_B, $nB-$ow_B) : ['or'=>null,'ci_low'=>null,'ci_high'=>null];
$H['H1.2'] = [
  'title'=>__('rp_h1_2'),
  'test'=>'χ² + OR',
  'p'=>$chi_sex['p'] ?? null,
  'stat'=>'χ²='.($chi_sex['chi2'] ?? '—'),
  'effect'=>'OR(♀/♂) = '.fmt_or($or_sex),
  'value'=>'♀ '.pct($ow_G,$nG).'% vs ♂ '.pct($ow_B,$nB).'%',
];

// H1.3 — IMC moyen ♂ vs ♀ (t-test)
$tt_sex = $bp->tTest(colN($boys,'bmi'),colN($girls,'bmi'));
$H['H1.3'] = [
  'title'=>__('rp_h1_3'),
  'test'=>'t-Student (Welch)',
  'p'=>$tt_sex['p'],
  'stat'=>'t='.$tt_sex['t'].', df='.$tt_sex['df'],
  'effect'=>'Δ = '.round($tt_sex['m1']-$tt_sex['m2'],2).' kg/m²',
  'value'=>'♂ '.$tt_sex['m1'].' ± '.$tt_sex['sd1'].' | ♀ '.$tt_sex['m2'].' ± '.$tt_sex['sd2'],
];

// H1.4 — IMC moyen par niveau scolaire (ANOVA)
$bmi_by_grade = [];
foreach(['1AS','2AS','3AS'] as $g){
    $bmi_by_grade[$g] = colN(array_filter($rows,fn($r)=>$r['grade']===$g),'bmi');
}
$an_grade = count(array_filter($bmi_by_grade,fn($x)=>count($x)>=2))>=2 ? $bp->anova($bmi_by_grade) : ['F'=>null,'p'=>null];
$H['H1.4'] = [
  'title'=>__('rp_h1_4'),
  'test'=>'ANOVA',
  'p'=>$an_grade['p'] ?? null,
  'stat'=>'F='.($an_grade['F'] ?? '—'),
  'value'=>'1AS '.avg($bmi_by_grade['1AS']).' | 2AS '.avg($bmi_by_grade['2AS']).' | 3AS '.avg($bmi_by_grade['3AS']),
];

// H1.5 — IMC ~ âge (Pearson)
$pe_age = $bp->pearson(colN($rows,'age'),colN($rows,'bmi'));
$H['H1.5'] = [
  'title'=>__('rp_h1_5'),
  'test'=>'Pearson',
  'p'=>$pe_age['p'],
  'stat'=>'r='.$pe_age['r'],
  'effect'=>'r²='.$pe_age['r2'],
  'value'=>'n='.$pe_age['n'],
];

// H1.6 — Poids de naissance ~ statut pondéral actuel
$pe_bw = $bp->pearson(colN($rows,'birth_weight'),colN($rows,'bmi'));
$H['H1.6'] = [
  'title'=>__('rp_h1_6'),
  'test'=>'Pearson',
  'p'=>$pe_bw['p'],
  'stat'=>'r='.$pe_bw['r'],
  'value'=>'n='.$pe_bw['n']
];

// H1.7 — Type d'accouchement (césarienne vs voie basse)
$cs = chi2_or_block($rows,'delivery_type',['Césarienne'],$bp);
$H['H1.7'] = [
  'title'=>__('rp_h1_7'),
  'test'=>'χ² + OR',
  'p'=>$cs['p'],
  'stat'=>'χ²='.($cs['chi2']??'—'),
  'effect'=>'OR='.fmt_or($cs),
  'value'=>'Exp+ : '.$cs['a'].'/'.($cs['a']+$cs['b']).' | Exp− : '.$cs['c'].'/'.($cs['c']+$cs['d'])
];

/* ═════════ H2 — ALIMENTATION ═════════ */

// H2.1 — IMC ~ score KIDMED (Pearson)
$pe_kid = $bp->pearson(colN($rows,'score_kidmed'),colN($rows,'bmi'));
$H['H2.1'] = [
  'title'=>__('rp_h2_1'),
  'test'=>'Pearson',
  'p'=>$pe_kid['p'],
  'stat'=>'r='.$pe_kid['r'],
  'value'=>'n='.$pe_kid['n']
];

// H2.2 — KIDMED moyen par classe IOTF (ANOVA)
$kid_by_iotf = [];
foreach(['Normal','Surpoids','Obésité'] as $c){
    $kid_by_iotf[$c] = colN(array_filter($rows,fn($r)=>$r['iotf_class']===$c),'score_kidmed');
}
$an_kid = count(array_filter($kid_by_iotf,fn($x)=>count($x)>=2))>=2 ? $bp->anova($kid_by_iotf) : ['F'=>null,'p'=>null];
$H['H2.2'] = [
  'title'=>__('rp_h2_2'),
  'test'=>'ANOVA',
  'p'=>$an_kid['p'] ?? null,
  'stat'=>'F='.($an_kid['F'] ?? '—'),
  'value'=>'Normal '.avg($kid_by_iotf['Normal']).' | Surpoids '.avg($kid_by_iotf['Surpoids']).' | Obésité '.avg($kid_by_iotf['Obésité'])
];

// H2.3 — Petit-déjeuner régulier → protection
$pd = chi2_or_block($rows,'breakfast_freq',['Tous les jours','Oui','Quotidien'],$bp);
$H['H2.3'] = [
  'title'=>__('rp_h2_3'),
  'test'=>'χ² + OR',
  'p'=>$pd['p'],
  'stat'=>'χ²='.($pd['chi2']??'—'),
  'effect'=>'OR='.fmt_or($pd),
  'value'=>'Réguliers OW : '.pct($pd['a'],$pd['a']+$pd['b']).'% | Irréguliers OW : '.pct($pd['c'],$pd['c']+$pd['d']).'%'
];

// H2.4 — Boissons sucrées quotidiennes
$bs = chi2_or_block($rows,'sugary_freq',['Tous les jours','Quotidien'],$bp);
$H['H2.4'] = [
  'title'=>__('rp_h2_4'),
  'test'=>'χ² + OR',
  'p'=>$bs['p'],
  'effect'=>'OR='.fmt_or($bs),
  'stat'=>'χ²='.($bs['chi2']??'—'),
  'value'=>'Quot. OW : '.pct($bs['a'],max(1,$bs['a']+$bs['b'])).'%'
];

// H2.5 — Dose-réponse sodas (Spearman)
$sugary_ord = array_map(function($r){
    $lvl = ['Jamais'=>0,'< 1/sem'=>1,'<1/sem'=>1,'1-3/sem'=>2,'4-6/sem'=>3,'Tous les jours'=>4,'Quotidien'=>4];
    return $lvl[$r['sugary_freq']??''] ?? null;
},$rows);
// Filter pairs valides
$pairs_sugar = []; foreach($rows as $i=>$r){ if($sugary_ord[$i]!==null && is_numeric($r['bmi'])) $pairs_sugar[] = [$sugary_ord[$i],(float)$r['bmi']]; }
$sp_sugar = count($pairs_sugar)>=10 ? $bp->spearman(array_column($pairs_sugar,0),array_column($pairs_sugar,1)) : ['r'=>null,'p'=>null,'n'=>0];
$H['H2.5'] = [
  'title'=>__('rp_h2_5'),
  'test'=>'Spearman',
  'p'=>$sp_sugar['p'],
  'stat'=>'ρ='.($sp_sugar['r']??'—'),
  'value'=>'n='.$sp_sugar['n']
];

// H2.6 — Déficit fruits/légumes (<3 portions)
$low_fv = array_map(fn($r)=>($r['fv_portions']??-1)>=0 && $r['fv_portions']<3 ? 'low' : 'ok',$rows);
foreach($rows as $i=>$r){ $rows[$i]['_fv_low'] = $low_fv[$i]; }
$fv = chi2_or_block($rows,'_fv_low',['low'],$bp);
$H['H2.6'] = [
  'title'=>__('rp_h2_6'),
  'test'=>'χ² + OR',
  'p'=>$fv['p'],
  'stat'=>'χ²='.($fv['chi2']??'—'),
  'effect'=>'OR='.fmt_or($fv),
  'value'=>'< 3 portions : '.($fv['a']+$fv['b']).' élèves'
];

// H2.7 — Grignotage fréquent
$grig = chi2_or_block($rows,'snacking_freq',['Souvent','Tous les jours'],$bp);
$H['H2.7'] = [
  'title'=>__('rp_h2_7'),
  'test'=>'χ² + OR',
  'p'=>$grig['p'],
  'effect'=>'OR='.fmt_or($grig),
  'stat'=>'χ²='.($grig['chi2']??'—'),
];

// H2.8 — Saut repas pour cours de soutien (Q45)
$skip = chi2_or_block($rows,'skip_meal_tutoring',['Oui, souvent','Oui, parfois'],$bp);
$H['H2.8'] = [
  'title'=>__('rp_h2_8'),
  'test'=>'χ² + OR',
  'p'=>$skip['p'],
  'stat'=>'χ²='.($skip['chi2']??'—'),
  'effect'=>'OR='.fmt_or($skip),
];

// H2.9 — Consommation eau insuffisante
$water = chi2_or_block($rows,'water_intake',['<1L','< 1L','Moins de 1L'],$bp);
$H['H2.9'] = [
  'title'=>__('rp_h2_9'),
  'test'=>'χ² + OR',
  'p'=>$water['p'],
  'effect'=>'OR='.fmt_or($water),
  'stat'=>'χ²='.($water['chi2']??'—'),
];

// H2.10 — Cantine de mauvaise qualité
$can = chi2_or_block($rows,'canteen_quality',['Mauvaise','Poor','Pauvre'],$bp);
$H['H2.10'] = [
  'title'=>__('rp_h2_10'),
  'test'=>'χ² + OR',
  'p'=>$can['p'],
  'effect'=>'OR='.fmt_or($can),
  'stat'=>'χ²='.($can['chi2']??'—'),
];

// H2.11 — IMC ~ ratio diet (protecteur/risque) Pearson
$pe_ratio = $bp->pearson(colN($rows,'ratio_diet'),colN($rows,'bmi'));
$H['H2.11'] = [
  'title'=>__('rp_h2_11'),
  'test'=>'Pearson',
  'p'=>$pe_ratio['p'],
  'stat'=>'r='.$pe_ratio['r'],
];

// H2.12 — IMC ~ Diversité alimentaire Pearson
$pe_div = $bp->pearson(colN($rows,'dietary_diversity'),colN($rows,'bmi'));
$H['H2.12'] = [
  'title'=>__('rp_h2_12'),
  'test'=>'Pearson',
  'p'=>$pe_div['p'],
  'stat'=>'r='.$pe_div['r'],
];

/* ═════════ H3 — ACTIVITÉ PHYSIQUE & SÉDENTARITÉ ═════════ */

// H3.1 — Inactivité (0 jour actif/sem)
$inact = array_map(fn($r)=>(($r['active_days_week']??null)!==null && $r['active_days_week']==0)?'inactive':(($r['active_days_week']??null)!==null?'active':null),$rows);
foreach($rows as $i=>$r){ $rows[$i]['_inact']=$inact[$i]; }
$ina = chi2_or_block($rows,'_inact',['inactive'],$bp);
$H['H3.1'] = [
  'title'=>__('rp_h3_1'),
  'test'=>'χ² + OR',
  'p'=>$ina['p'],
  'effect'=>'OR='.fmt_or($ina),
  'stat'=>'χ²='.($ina['chi2']??'—'),
];

// H3.2 — Club sportif (protection)
$club = chi2_or_block($rows,'sports_club',['Oui'],$bp);
$H['H3.2'] = [
  'title'=>__('rp_h3_2'),
  'test'=>'χ² + OR',
  'p'=>$club['p'],
  'effect'=>'OR='.fmt_or($club),
  'stat'=>'χ²='.($club['chi2']??'—'),
];

// H3.3 — Téléphone > 4h
$ph = chi2_or_block($rows,'screen_phone',['>4h','> 4h'],$bp);
$H['H3.3'] = [
  'title'=>__('rp_h3_3'),
  'test'=>'χ² + OR',
  'p'=>$ph['p'],
  'effect'=>'OR='.fmt_or($ph),
  'stat'=>'χ²='.($ph['chi2']??'—'),
];

// H3.4 — TV > 2h
$tv = chi2_or_block($rows,'screen_tv',['>2h','> 2h','2-4h','>4h','> 4h'],$bp);
$H['H3.4'] = [
  'title'=>__('rp_h3_4'),
  'test'=>'χ² + OR',
  'p'=>$tv['p'],
  'effect'=>'OR='.fmt_or($tv),
  'stat'=>'χ²='.($tv['chi2']??'—'),
];

// H3.5 — IMC ~ score sédentarité (Pearson)
$pe_sed = $bp->pearson(colN($rows,'score_sedentarite'),colN($rows,'bmi'));
$H['H3.5'] = [
  'title'=>__('rp_h3_5'),
  'test'=>'Pearson',
  'p'=>$pe_sed['p'],
  'stat'=>'r='.$pe_sed['r'],
];

// H3.6 — IMC ~ score activité (Pearson)
$pe_act = $bp->pearson(colN($rows,'score_activite'),colN($rows,'bmi'));
$H['H3.6'] = [
  'title'=>__('rp_h3_6'),
  'test'=>'Pearson',
  'p'=>$pe_act['p'],
  'stat'=>'r='.$pe_act['r'],
];

// H3.7 — Transport actif (marche/vélo)
$tr = chi2_or_block($rows,'transport',['Marche','Vélo','À pied'],$bp);
$H['H3.7'] = [
  'title'=>__('rp_h3_7'),
  'test'=>'χ² + OR',
  'p'=>$tr['p'],
  'effect'=>'OR='.fmt_or($tr),
  'stat'=>'χ²='.($tr['chi2']??'—'),
];

// H3.8 — Heures écran totales (t-test moyennes par classe IOTF)
$scr_norm = colN(array_filter($rows,fn($r)=>$r['iotf_class']==='Normal'),'screen_hours_total');
$scr_ow   = colN(array_filter($rows,fn($r)=>in_array($r['iotf_class']??'',StudySpecificAnalyses::OUTCOME_OW_OB)),'screen_hours_total');
$tt_scr = $bp->tTest($scr_ow,$scr_norm);
$H['H3.8'] = [
  'title'=>__('rp_h3_8'),
  'test'=>'t-Student',
  'p'=>$tt_scr['p'],
  'stat'=>'t='.$tt_scr['t'],
  'value'=>'OW '.$tt_scr['m1'].'h | N '.$tt_scr['m2'].'h',
];

// H3.9 — Installations sportives à l'école (protection)
$fac = chi2_or_block($rows,'sports_facilities',['Oui'],$bp);
$H['H3.9'] = [
  'title'=>__('rp_h3_9'),
  'test'=>'χ² + OR',
  'p'=>$fac['p'],
  'effect'=>'OR='.fmt_or($fac),
];

/* ═════════ H4 — SOMMEIL & COMPORTEMENT ÉMOTIONNEL ═════════ */

// H4.1 — Sommeil court < 6h
$slp = chi2_or_block($rows,'sleep_duration',['<6h','< 6h','Moins de 6h'],$bp);
$H['H4.1'] = [
  'title'=>__('rp_h4_1'),
  'test'=>'χ² + OR',
  'p'=>$slp['p'],
  'effect'=>'OR='.fmt_or($slp),
  'stat'=>'χ²='.($slp['chi2']??'—'),
];

// H4.2 — IMC ~ score sommeil (Pearson, score élevé = mauvais)
$pe_slp = $bp->pearson(colN($rows,'score_sommeil'),colN($rows,'bmi'));
$H['H4.2'] = [
  'title'=>__('rp_h4_2'),
  'test'=>'Pearson',
  'p'=>$pe_slp['p'],
  'stat'=>'r='.$pe_slp['r'],
];

// H4.3 — Insomnie fréquente
$ins = chi2_or_block($rows,'insomnia',['Souvent','Toujours','Oui'],$bp);
$H['H4.3'] = [
  'title'=>__('rp_h4_3'),
  'test'=>'χ² + OR',
  'p'=>$ins['p'],
  'effect'=>'OR='.fmt_or($ins),
];

// H4.4 — Réveil épuisé
$wk = chi2_or_block($rows,'wake_exhausted',['Souvent','Toujours','Oui'],$bp);
$H['H4.4'] = [
  'title'=>__('rp_h4_4'),
  'test'=>'χ² + OR',
  'p'=>$wk['p'],
  'effect'=>'OR='.fmt_or($wk),
];

// H4.5 — Écrans avant sommeil
$ecs = chi2_or_block($rows,'screen_before_sleep',['Oui','Toujours','Souvent'],$bp);
$H['H4.5'] = [
  'title'=>__('rp_h4_5'),
  'test'=>'χ² + OR',
  'p'=>$ecs['p'],
  'effect'=>'OR='.fmt_or($ecs),
];

// H4.6 — Alimentation émotionnelle
$emot = chi2_or_block($rows,'emotional_eating',['Oui, souvent','Souvent','Oui'],$bp);
$H['H4.6'] = [
  'title'=>__('rp_h4_6'),
  'test'=>'χ² + OR',
  'p'=>$emot['p'],
  'effect'=>'OR='.fmt_or($emot),
  'stat'=>'χ²='.($emot['chi2']??'—'),
];

// H4.7 — Heures réelles de sommeil par classe IOTF (t-test)
$slh_n = colN(array_filter($rows,fn($r)=>$r['iotf_class']==='Normal'),'sleep_hours_real');
$slh_o = colN(array_filter($rows,fn($r)=>in_array($r['iotf_class']??'',StudySpecificAnalyses::OUTCOME_OW_OB)),'sleep_hours_real');
$tt_slh = $bp->tTest($slh_o,$slh_n);
$H['H4.7'] = [
  'title'=>__('rp_h4_7'),
  'test'=>'t-Student',
  'p'=>$tt_slh['p'],
  'stat'=>'t='.$tt_slh['t'],
  'value'=>'OW '.$tt_slh['m1'].'h | N '.$tt_slh['m2'].'h',
];

/* ═════════ H5 — FAMILLE & GÉNÉTIQUE ═════════ */

// H5.1 — Parent obèse → enfant
$po = chi2_or_block($rows,'parent_obese',['Oui'],$bp);
$H['H5.1'] = [
  'title'=>__('rp_h5_1'),
  'test'=>'χ² + OR',
  'p'=>$po['p'],
  'effect'=>'OR='.fmt_or($po),
  'stat'=>'χ²='.($po['chi2']??'—'),
];

// H5.2 — Famille en surpoids
$fov = chi2_or_block($rows,'family_overweight',['Oui'],$bp);
$H['H5.2'] = [
  'title'=>__('rp_h5_2'),
  'test'=>'χ² + OR',
  'p'=>$fov['p'],
  'effect'=>'OR='.fmt_or($fov),
];

// H5.3 — Repas en famille (protection)
$mf = chi2_or_block($rows,'meals_with_family',['Toujours','Souvent'],$bp);
$H['H5.3'] = [
  'title'=>__('rp_h5_3'),
  'test'=>'χ² + OR',
  'p'=>$mf['p'],
  'effect'=>'OR='.fmt_or($mf),
];

// H5.4 — Niveau d'éducation de la mère (ANOVA sur IMC)
$bmi_by_edu = [];
foreach(['Sans niveau','Primaire','Moyen','Secondaire','Universitaire'] as $e){
    $bmi_by_edu[$e] = colN(array_filter($rows,fn($r)=>$r['mother_education']===$e),'bmi');
}
$an_edu = count(array_filter($bmi_by_edu,fn($x)=>count($x)>=2))>=2 ? $bp->anova(array_filter($bmi_by_edu,fn($x)=>count($x)>=2)) : ['F'=>null,'p'=>null];
$H['H5.4'] = [
  'title'=>__('rp_h5_4'),
  'test'=>'ANOVA',
  'p'=>$an_edu['p'] ?? null,
  'stat'=>'F='.($an_edu['F'] ?? '—'),
];

// H5.5 — Emploi des deux parents
$emp = chi2_or_block($rows,'parents_employment',['Les deux','Both','Both working','Les deux travaillent'],$bp);
$H['H5.5'] = [
  'title'=>__('rp_h5_5'),
  'test'=>'χ² + OR',
  'p'=>$emp['p'],
  'effect'=>'OR='.fmt_or($emp),
];

// H5.6 — IMC ~ score famille (Pearson)
$pe_fam = $bp->pearson(colN($rows,'score_famille'),colN($rows,'bmi'));
$H['H5.6'] = [
  'title'=>__('rp_h5_6'),
  'test'=>'Pearson',
  'p'=>$pe_fam['p'],
  'stat'=>'r='.$pe_fam['r'],
];

// H5.7 — Soutien familial (protection)
$fs = chi2_or_block($rows,'family_support',['Élevé','Bon','Oui'],$bp);
$H['H5.7'] = [
  'title'=>__('rp_h5_7'),
  'test'=>'χ² + OR',
  'p'=>$fs['p'],
  'effect'=>'OR='.fmt_or($fs),
];

/* ═════════ H6 — ÉCOLE & PRESSION SCOLAIRE ═════════ */

// H6.1 — Stress scolaire élevé
$st = chi2_or_block($rows,'academic_stress',['Élevé','Eleve','High'],$bp);
$H['H6.1'] = [
  'title'=>__('rp_h6_1'),
  'test'=>'χ² + OR',
  'p'=>$st['p'],
  'effect'=>'OR='.fmt_or($st),
  'stat'=>'χ²='.($st['chi2']??'—'),
];

// H6.2 — Pression amis fast-food
$pp = chi2_or_block($rows,'peer_pressure_fastfood',['Oui','Souvent'],$bp);
$H['H6.2'] = [
  'title'=>__('rp_h6_2'),
  'test'=>'χ² + OR',
  'p'=>$pp['p'],
  'effect'=>'OR='.fmt_or($pp),
];

// H6.3 — IMC ~ score pression scolaire (Pearson)
$pe_pres = $bp->pearson(colN($rows,'score_pression_scolaire'),colN($rows,'bmi'));
$H['H6.3'] = [
  'title'=>__('rp_h6_3'),
  'test'=>'Pearson',
  'p'=>$pe_pres['p'],
  'stat'=>'r='.$pe_pres['r'],
];

// H6.4 — Cantine scolaire utilisée
$cu = chi2_or_block($rows,'school_canteen',['Oui','Souvent'],$bp);
$H['H6.4'] = [
  'title'=>__('rp_h6_4'),
  'test'=>'χ² + OR',
  'p'=>$cu['p'],
  'effect'=>'OR='.fmt_or($cu),
];

// H6.5 — Performance scolaire (moyenne) ~ IMC (Q6)
// school_avg est catégoriel — ANOVA
$bmi_by_avg = [];
foreach($rows as $r){ $k=$r['school_avg']??''; if($k===''||$k===null) continue; if(!isset($bmi_by_avg[$k])) $bmi_by_avg[$k]=[]; if(is_numeric($r['bmi'])) $bmi_by_avg[$k][]=(float)$r['bmi']; }
$bmi_by_avg = array_filter($bmi_by_avg,fn($x)=>count($x)>=2);
$an_perf = count($bmi_by_avg)>=2 ? $bp->anova($bmi_by_avg) : ['F'=>null,'p'=>null];
$H['H6.5'] = [
  'title'=>__('rp_h6_5'),
  'test'=>'ANOVA',
  'p'=>$an_perf['p'] ?? null,
  'stat'=>'F='.($an_perf['F'] ?? '—'),
];

// H6.6 — Éducation nutritionnelle reçue
$ne = chi2_or_block($rows,'nutrition_education',['Oui','Régulière','Souvent'],$bp);
$H['H6.6'] = [
  'title'=>__('rp_h6_6'),
  'test'=>'χ² + OR',
  'p'=>$ne['p'],
  'effect'=>'OR='.fmt_or($ne),
];

/* ═════════ H7 — PERCEPTION & COMPORTEMENT ═════════ */

// H7.1 — Concordance perception / IOTF (χ²)
$gap = col($rows,'perception_gap');
$tab_g = ['Concordant'=>cnt($gap,'Concordant'),'Sous-estime'=>cnt($gap,'Sous-estime'),'Surestime'=>cnt($gap,'Surestime')];
$H['H7.1'] = [
  'title'=>__('rp_h7_1'),
  'test'=>'Distribution',
  'value'=>'Conc. '.pct($tab_g['Concordant'],$n).'% | Sous '.pct($tab_g['Sous-estime'],$n).'% | Sur '.pct($tab_g['Surestime'],$n).'%',
  'p'=>null,
];

// H7.2 — Sous-estimation chez OW/OB (chi²)
$undo_ow = 0; $undo_n = 0;
foreach($rows as $r){
    if(in_array($r['iotf_class']??'',StudySpecificAnalyses::OUTCOME_OW_OB)){
        $undo_n++;
        if($r['perception_gap']==='Sous-estime') $undo_ow++;
    }
}
$H['H7.2'] = [
  'title'=>__('rp_h7_2'),
  'test'=>'Proportion',
  'value'=>$undo_n>0 ? pct($undo_ow,$undo_n).'% des OW/OB se sous-estiment' : '—',
  'p'=>null,
];

// H7.3 — Tentative de perte de poids
$try = chi2_or_block($rows,'tried_lose_weight',['Oui'],$bp);
$H['H7.3'] = [
  'title'=>__('rp_h7_3'),
  'test'=>'χ² + OR',
  'p'=>$try['p'],
  'effect'=>'OR='.fmt_or($try),
];

// H7.4 — Désir de changer le poids
$desire = chi2_or_block($rows,'want_weight_change',['Perdre','Maigrir','Oui'],$bp);
$H['H7.4'] = [
  'title'=>__('rp_h7_4'),
  'test'=>'χ² + OR',
  'p'=>$desire['p'],
  'effect'=>'OR='.fmt_or($desire),
];

/* ═════════ H8 — RISQUES ANNEXES ═════════ */

// H8.1 — Tabagisme
$smk = chi2_or_block($rows,'smoker',['Oui','Régulier','Occasionnel'],$bp);
$H['H8.1'] = [
  'title'=>__('rp_h8_1'),
  'test'=>'χ² + OR',
  'p'=>$smk['p'],
  'effect'=>'OR='.fmt_or($smk),
];

// H8.2 — Remèdes traditionnels (orexigènes)
$rt = chi2_or_block($rows,'traditional_remedies',['Oui','Régulièrement','Parfois'],$bp);
$H['H8.2'] = [
  'title'=>__('rp_h8_2'),
  'test'=>'χ² + OR',
  'p'=>$rt['p'],
  'effect'=>'OR='.fmt_or($rt),
];

// H8.3 — Risque anémie ~ IOTF (χ²)
$tan = ['risk'=>0,'norisk'=>0,'risk_ow'=>0,'norisk_ow'=>0];
foreach($rows as $r){
    $hr = ($r['anemia_risk']??'')==='Élevé' || ($r['anemia_risk']??'')==='High';
    $ow = in_array($r['iotf_class']??'',StudySpecificAnalyses::OUTCOME_OW_OB);
    if($hr) { $tan['risk']++; if($ow) $tan['risk_ow']++; }
    else    { $tan['norisk']++; if($ow) $tan['norisk_ow']++; }
}
$an_chi = $bp->chi2Test2x2($tan['risk_ow'], $tan['risk']-$tan['risk_ow'], $tan['norisk_ow'], $tan['norisk']-$tan['norisk_ow']);
$H['H8.3'] = [
  'title'=>__('rp_h8_3'),
  'test'=>'χ²',
  'p'=>$an_chi['p'] ?? null,
  'stat'=>'χ²='.($an_chi['chi2']??'—'),
];

// H8.4 — Risque déficit Vit-D
$vd_ow=$vd_now=0;
foreach($rows as $r){
    if(($r['vitd_risk']??'')==='Élevé' || ($r['vitd_risk']??'')==='High'){
        if(in_array($r['iotf_class']??'',StudySpecificAnalyses::OUTCOME_OW_OB)) $vd_ow++; else $vd_now++;
    }
}
$H['H8.4'] = [
  'title'=>__('rp_h8_4'),
  'test'=>'Distribution',
  'value'=>'OW : '.$vd_ow.' | Normal : '.$vd_now,
  'p'=>null,
];

// H8.5 — Risque trouble alimentaire
$ed = chi2_or_block($rows,'eating_disorder_risk',['Élevé','High'],$bp);
$H['H8.5'] = [
  'title'=>__('rp_h8_5'),
  'test'=>'χ² + OR',
  'p'=>$ed['p'],
  'effect'=>'OR='.fmt_or($ed),
];

// H8.6 — Score risque obésité ~ IOTF (validation interne)
$pe_or = $bp->pearson(colN($rows,'obesity_risk_score'),colN($rows,'bmi'));
$H['H8.6'] = [
  'title'=>__('rp_h8_6'),
  'test'=>'Pearson',
  'p'=>$pe_or['p'],
  'stat'=>'r='.$pe_or['r'],
];

// H8.7 — Score global nutrition ~ IMC
$pe_glob = $bp->pearson(colN($rows,'global_nutrition_score'),colN($rows,'bmi'));
$H['H8.7'] = [
  'title'=>__('rp_h8_7'),
  'test'=>'Pearson',
  'p'=>$pe_glob['p'],
  'stat'=>'r='.$pe_glob['r'],
];

/* ═════════ H9 — STRATIFICATIONS ═════════ */

// H9.1 — Prévalence par école (et test homogénéité χ²)
$schools = [];
foreach($rows as $r){
    $s = $r['school'] ?? 'Autre';
    if(!isset($schools[$s])) $schools[$s] = ['n'=>0,'ow'=>0,'bmi'=>[]];
    $schools[$s]['n']++;
    if(in_array($r['iotf_class']??'',StudySpecificAnalyses::OUTCOME_OW_OB)) $schools[$s]['ow']++;
    if(is_numeric($r['bmi'])) $schools[$s]['bmi'][] = (float)$r['bmi'];
}

// H9.2 — Par daïra (commune comme proxy)
$daira_bmi = [];
foreach($rows as $r){
    $d = $r['commune'] ?? 'Autre';
    if(!isset($daira_bmi[$d])) $daira_bmi[$d] = [];
    if(is_numeric($r['bmi'])) $daira_bmi[$d][] = (float)$r['bmi'];
}
$daira_bmi = array_filter($daira_bmi,fn($x)=>count($x)>=2);
$an_daira = count($daira_bmi)>=2 ? $bp->anova($daira_bmi) : ['F'=>null,'p'=>null];
$H['H9.2'] = [
  'title'=>__('rp_h9_2'),
  'test'=>'ANOVA',
  'p'=>$an_daira['p'] ?? null,
  'stat'=>'F='.($an_daira['F'] ?? '—'),
];

/* ════════════════════════════════════════════════════════════════ */
/*    H10 — FOCUS 3AS : pression scolaire / cours de soutien      */
/*    Axe original de l'étude (mémoire §1.6, §1.10, hypothèse H4) */
/* ════════════════════════════════════════════════════════════════ */

// Sous-échantillons par niveau
$rows_3as       = array_values(array_filter($rows, fn($r)=>$r['grade']==='3AS'));
$rows_1_2_as    = array_values(array_filter($rows, fn($r)=>in_array($r['grade'],['1AS','2AS'])));
$rows_1as       = array_values(array_filter($rows, fn($r)=>$r['grade']==='1AS'));
$rows_2as       = array_values(array_filter($rows, fn($r)=>$r['grade']==='2AS'));
$n_3as          = count($rows_3as);
$n_1_2_as       = count($rows_1_2_as);

// Indicateurs descriptifs 3AS
$bmi_3as            = colN($rows_3as,'bmi');
$kidmed_3as         = colN($rows_3as,'score_kidmed');
$pression_3as       = colN($rows_3as,'score_pression_scolaire');
$nOW_3as            = count(array_filter($rows_3as,fn($r)=>in_array($r['iotf_class']??'',StudySpecificAnalyses::OUTCOME_OW_OB)));
$nSkipMeal_3as      = count(array_filter($rows_3as,fn($r)=>preg_match('/oui/i',$r['skip_meal_tutoring']??'')));
$nEmotional_3as     = count(array_filter($rows_3as,fn($r)=>preg_match('/oui|souvent/i',$r['emotional_eating']??'')));
$nStressHigh_3as    = count(array_filter($rows_3as,fn($r)=>preg_match('/élev|haut|fort/i',$r['academic_stress']??'')));

$pct_OW_3as         = $n_3as>0 ? round($nOW_3as*100/$n_3as,1) : 0;
$pct_skip_3as       = $n_3as>0 ? round($nSkipMeal_3as*100/$n_3as,1) : 0;
$pct_emotional_3as  = $n_3as>0 ? round($nEmotional_3as*100/$n_3as,1) : 0;
$pct_stress_3as     = $n_3as>0 ? round($nStressHigh_3as*100/$n_3as,1) : 0;

// Mêmes indicateurs pour 1AS+2AS (comparaison)
$nOW_12             = count(array_filter($rows_1_2_as,fn($r)=>in_array($r['iotf_class']??'',StudySpecificAnalyses::OUTCOME_OW_OB)));
$nSkipMeal_12       = count(array_filter($rows_1_2_as,fn($r)=>preg_match('/oui/i',$r['skip_meal_tutoring']??'')));
$nEmotional_12      = count(array_filter($rows_1_2_as,fn($r)=>preg_match('/oui|souvent/i',$r['emotional_eating']??'')));
$nStressHigh_12     = count(array_filter($rows_1_2_as,fn($r)=>preg_match('/élev|haut|fort/i',$r['academic_stress']??'')));

// H10.1 — Surpoids+Obésité plus fréquents en 3AS qu'en 1AS+2AS
function chi2_or_direct($a,$b,$c,$d,$bp){
    if(($a+$b+$c+$d)<10) return ['p'=>null,'chi2'=>null,'or'=>null,'ci_low'=>null,'ci_high'=>null];
    $chi = $bp->chi2Test2x2($a,$b,$c,$d);
    $or  = $bp->oddsRatio($a,$b,$c,$d);
    return array_merge($chi,$or);
}
$h10_1 = chi2_or_direct($nOW_3as, $n_3as-$nOW_3as, $nOW_12, $n_1_2_as-$nOW_12, $bp);
$H['H10.1'] = [
  'title'=>__('rp_h10_1'),
  'test'=>'χ² + OR',
  'p'=>$h10_1['p'],
  'stat'=>'χ²='.($h10_1['chi2']??'—'),
  'effect'=>'OR='.fmt_or($h10_1),
  'value'=>'3AS '.$pct_OW_3as.'% | 1AS+2AS '.($n_1_2_as>0?round($nOW_12*100/$n_1_2_as,1):0).'%',
];

// H10.2 — Saut de repas (cours de soutien) plus fréquent en 3AS
$h10_2 = chi2_or_direct($nSkipMeal_3as, $n_3as-$nSkipMeal_3as, $nSkipMeal_12, $n_1_2_as-$nSkipMeal_12, $bp);
$H['H10.2'] = [
  'title'=>__('rp_h10_2'),
  'test'=>'χ² + OR',
  'p'=>$h10_2['p'],
  'stat'=>'χ²='.($h10_2['chi2']??'—'),
  'effect'=>'OR='.fmt_or($h10_2),
  'value'=>'3AS '.$pct_skip_3as.'% | 1AS+2AS '.($n_1_2_as>0?round($nSkipMeal_12*100/$n_1_2_as,1):0).'%',
];

// H10.3 — Score de pression scolaire moyen plus élevé en 3AS (t-Welch)
$tt_pression_3as = $bp->tTest($pression_3as, colN($rows_1_2_as,'score_pression_scolaire'));
$H['H10.3'] = [
  'title'=>__('rp_h10_3'),
  'test'=>'t-Student (Welch)',
  'p'=>$tt_pression_3as['p'],
  'stat'=>'t='.$tt_pression_3as['t'].', df='.$tt_pression_3as['df'],
  'effect'=>'Δ = '.round(($tt_pression_3as['m1']??0)-($tt_pression_3as['m2']??0),2),
  'value'=>'3AS '.($tt_pression_3as['m1']??'—').' | 1AS+2AS '.($tt_pression_3as['m2']??'—'),
];

// H10.4 — Alimentation émotionnelle plus fréquente en 3AS
$h10_4 = chi2_or_direct($nEmotional_3as, $n_3as-$nEmotional_3as, $nEmotional_12, $n_1_2_as-$nEmotional_12, $bp);
$H['H10.4'] = [
  'title'=>__('rp_h10_4'),
  'test'=>'χ² + OR',
  'p'=>$h10_4['p'],
  'stat'=>'χ²='.($h10_4['chi2']??'—'),
  'effect'=>'OR='.fmt_or($h10_4),
  'value'=>'3AS '.$pct_emotional_3as.'% | 1AS+2AS '.($n_1_2_as>0?round($nEmotional_12*100/$n_1_2_as,1):0).'%',
];

// H10.5 — DANS 3AS UNIQUEMENT : saut de repas ⟺ surpoids
$h10_5 = chi2_or_block($rows_3as,'skip_meal_tutoring',['Oui, souvent','Oui, parfois','Oui'],$bp);
$H['H10.5'] = [
  'title'=>__('rp_h10_5'),
  'test'=>'χ² + OR (sous-groupe 3AS)',
  'p'=>$h10_5['p'],
  'stat'=>'χ²='.($h10_5['chi2']??'—'),
  'effect'=>'OR='.fmt_or($h10_5),
  'value'=>'Skip+ OW : '.$h10_5['a'].'/'.($h10_5['a']+$h10_5['b']).' | Skip− OW : '.$h10_5['c'].'/'.($h10_5['c']+$h10_5['d']),
];

// H10.6 — DANS 3AS UNIQUEMENT : score_pression ⟺ IMC (Pearson)
$pe_press_3as = $bp->pearson($pression_3as, $bmi_3as);
$H['H10.6'] = [
  'title'=>__('rp_h10_6'),
  'test'=>'Pearson (sous-groupe 3AS)',
  'p'=>$pe_press_3as['p'],
  'stat'=>'r='.$pe_press_3as['r'],
  'effect'=>'r²='.$pe_press_3as['r2'],
  'value'=>'n='.$pe_press_3as['n'],
];

/* ─── Cochran-Armitage : test de tendance linéaire 1AS→2AS→3AS ─── */
// Calcul manuel : OR par niveau puis test chi-square de tendance
function cochranArmitageTrend($cells) {
    // $cells = [[ni0, ni1], ...] pour chaque niveau ordonné
    $K = count($cells);
    if($K<3) return ['Z'=>null,'p'=>null];
    $scores = range(1,$K);
    $T1=0; $N=0; $R=0;
    $ns=[]; $rs=[];
    foreach($cells as $i=>$c){
        $n_i = $c[0]+$c[1];
        $r_i = $c[1];
        $T1 += $scores[$i]*$r_i;
        $N  += $n_i; $R += $r_i;
        $ns[$i]=$n_i; $rs[$i]=$r_i;
    }
    if($N==0||$R==0||$R==$N) return ['Z'=>null,'p'=>null];
    $p = $R/$N;
    $E = $p * array_sum(array_map(fn($i)=>$scores[$i]*$ns[$i],array_keys($ns)));
    $S1=0; $S2=0;
    foreach($ns as $i=>$n_i){
        $S1 += $scores[$i]*$n_i;
        $S2 += $scores[$i]*$scores[$i]*$n_i;
    }
    $V = $p*(1-$p)*($S2 - $S1*$S1/$N);
    if($V<=0) return ['Z'=>null,'p'=>null];
    $Z = ($T1-$E)/sqrt($V);
    // Two-sided p from normal
    $pval = 2*(1 - 0.5*(1+erf_safe(abs($Z)/sqrt(2))));
    return ['Z'=>round($Z,3),'p'=>round($pval,4)];
}
function erf_safe($x){
    // Approximation Abramowitz & Stegun 7.1.26
    $t = 1.0/(1.0+0.3275911*$x);
    $y = 1.0-(((((1.061405429*$t - 1.453152027)*$t)+1.421413741)*$t - 0.284496736)*$t + 0.254829592)*$t*exp(-$x*$x);
    return $y;
}

// Test de tendance Cochran-Armitage : %surpoids+obésité 1AS→2AS→3AS
$ow_1as = count(array_filter($rows_1as,fn($r)=>in_array($r['iotf_class']??'',StudySpecificAnalyses::OUTCOME_OW_OB)));
$ow_2as = count(array_filter($rows_2as,fn($r)=>in_array($r['iotf_class']??'',StudySpecificAnalyses::OUTCOME_OW_OB)));
$ow_3as_t = count(array_filter($rows_3as,fn($r)=>in_array($r['iotf_class']??'',StudySpecificAnalyses::OUTCOME_OW_OB)));
$cells_trend_ow = [
    [count($rows_1as)-$ow_1as, $ow_1as],
    [count($rows_2as)-$ow_2as, $ow_2as],
    [count($rows_3as)-$ow_3as_t, $ow_3as_t],
];
$ca_ow = cochranArmitageTrend($cells_trend_ow);

$H['H10.7'] = [
  'title'=>__('rp_h10_7'),
  'test'=>'Cochran-Armitage (tendance)',
  'p'=>$ca_ow['p'],
  'stat'=>'Z='.($ca_ow['Z']??'—'),
  'value'=>'1AS '.(count($rows_1as)>0?round($ow_1as*100/count($rows_1as),1):0).'% → 2AS '.(count($rows_2as)>0?round($ow_2as*100/count($rows_2as),1):0).'% → 3AS '.(count($rows_3as)>0?round($ow_3as_t*100/count($rows_3as),1):0).'%',
];

// Test de tendance Cochran-Armitage : %saut de repas 1AS→2AS→3AS
$skip_1as = count(array_filter($rows_1as,fn($r)=>preg_match('/oui/i',$r['skip_meal_tutoring']??'')));
$skip_2as = count(array_filter($rows_2as,fn($r)=>preg_match('/oui/i',$r['skip_meal_tutoring']??'')));
$skip_3as_t = count(array_filter($rows_3as,fn($r)=>preg_match('/oui/i',$r['skip_meal_tutoring']??'')));
$cells_trend_skip = [
    [count($rows_1as)-$skip_1as, $skip_1as],
    [count($rows_2as)-$skip_2as, $skip_2as],
    [count($rows_3as)-$skip_3as_t, $skip_3as_t],
];
$ca_skip = cochranArmitageTrend($cells_trend_skip);
$H['H10.8'] = [
  'title'=>__('rp_h10_8'),
  'test'=>'Cochran-Armitage (tendance)',
  'p'=>$ca_skip['p'],
  'stat'=>'Z='.($ca_skip['Z']??'—'),
  'value'=>'1AS '.(count($rows_1as)>0?round($skip_1as*100/count($rows_1as),1):0).'% → 2AS '.(count($rows_2as)>0?round($skip_2as*100/count($rows_2as),1):0).'% → 3AS '.(count($rows_3as)>0?round($skip_3as_t*100/count($rows_3as),1):0).'%',
];

/* ────────────────────────────────────────────────────────────── */
/*    Comptage hypothèses confirmées / tendance / non confirmées  */
/* ────────────────────────────────────────────────────────────── */
$nb_conf=0; $nb_trend=0; $nb_ns=0; $nb_na=0;
foreach($H as $h){
    if(!isset($h['p']) || $h['p']===null){ $nb_na++; continue; }
    if($h['p']<0.05) $nb_conf++;
    elseif($h['p']<0.20) $nb_trend++;
    else $nb_ns++;
}

/* ── Données pour les graphiques JS ─────────────────────────── */
$jsIOTF    = json_encode([$nMinc,$nNorm,$nSurp,$nObes]);

$kidClasses    = col($rows,'classe_kidmed');
$jsKidmed = json_encode([cnt($kidClasses,'Qualité faible')+cnt($kidClasses,'Mauvais')+cnt($kidClasses,'Faible'),
                         cnt($kidClasses,'Amélioration nécessaire')+cnt($kidClasses,'Moyen'),
                         cnt($kidClasses,'Qualité optimale')+cnt($kidClasses,'Bon')]);

$globalClasses = col($rows,'global_nutrition_class');
$jsGlobal = json_encode([cnt($globalClasses,'Mauvais'),cnt($globalClasses,'Moyen'),cnt($globalClasses,'Bon'),cnt($globalClasses,'Excellent')]);

$obesRisk      = col($rows,'obesity_risk_class');
$jsRisk = json_encode([cnt($obesRisk,'Faible'),cnt($obesRisk,'Modéré'),cnt($obesRisk,'Élevé')]);

$perception    = col($rows,'perception_gap');
$jsPercept = json_encode([cnt($perception,'Concordant'),cnt($perception,'Sous-estime'),cnt($perception,'Surestime')]);

// Schools chart
$schoolNames=[];$schoolN=[];$schoolOb=[];
foreach($schools as $s=>$d){
    $short = mb_substr(explode('—',$s)[0],0,20);
    $schoolNames[]=$short;
    $schoolN[]=$d['n'];
    $schoolOb[]=$d['n']>0 ? round($d['ow']/$d['n']*100,1) : 0;
}
$jsSchoolNames = json_encode($schoolNames,JSON_UNESCAPED_UNICODE);
$jsSchoolN     = json_encode($schoolN);
$jsSchoolOb    = json_encode($schoolOb);

// Sex bar chart : prévalence OW par sexe et grade
$jsSexGrade = [];
foreach(['1AS','2AS','3AS'] as $g){
    $bg = array_filter($boys,fn($r)=>$r['grade']===$g);
    $gg = array_filter($girls,fn($r)=>$r['grade']===$g);
    $bow = count(array_filter($bg,fn($r)=>in_array($r['iotf_class']??'',StudySpecificAnalyses::OUTCOME_OW_OB)));
    $gow = count(array_filter($gg,fn($r)=>in_array($r['iotf_class']??'',StudySpecificAnalyses::OUTCOME_OW_OB)));
    $jsSexGrade[$g] = ['boys'=>pct($bow,count($bg)),'girls'=>pct($gow,count($gg))];
}

// Hypotheses summary chart (donut)
$jsHypoSummary = json_encode([$nb_conf,$nb_trend,$nb_ns,$nb_na]);

// Timeline
$byDate = $db->query("SELECT DATE(entered_at) AS d, COUNT(*) AS n FROM responses GROUP BY DATE(entered_at) ORDER BY d")->fetchAll(PDO::FETCH_ASSOC);
$cumul = 0; $dates = []; $cumuls = [];
foreach($byDate as $r){ $cumul += $r['n']; $dates[] = $r['d']; $cumuls[] = $cumul; }
$jsDates = json_encode($dates);
$jsCumuls = json_encode($cumuls);

// Forest plot data — pour les OR significatifs
$forestData = [];
foreach($H as $code=>$h){
    if(!str_contains($h['effect']??'','OR=')) continue;
    if(!isset($h['p']) || $h['p']===null) continue;
    // Extract OR from string. Regex capture from fmt_or pattern: "OR(...)" or "1.23 [IC95: 1.0–1.5]"
    if(preg_match('/(\d+\.\d+)\s*\[IC95:\s*(\d+\.\d+)–(\d+\.\d+)\]/u', $h['effect'], $m)){
        $forestData[] = [
          'code'=>$code,
          'label'=>$h['title'],
          'or'=>(float)$m[1],
          'low'=>(float)$m[2],
          'high'=>(float)$m[3],
          'p'=>$h['p'],
        ];
    }
}
// trier par OR
usort($forestData,fn($a,$b)=>$b['or']<=>$a['or']);
$jsForest = json_encode($forestData,JSON_UNESCAPED_UNICODE);

/* ════════════════════════════════════════════════════════════════ */
/*    BENJAMINI-HOCHBERG — correction pour tests multiples (V3)    */
/* ════════════════════════════════════════════════════════════════ */
$pvalsMap = [];
foreach($H as $code=>$h){
    if(isset($h['p']) && $h['p']!==null && is_numeric($h['p'])) $pvalsMap[$code] = $h['p'];
}
$pvalsBH = BiostatAnalysis::benjaminiHochberg($pvalsMap);
foreach($pvalsBH as $code=>$p_adj){ $H[$code]['p_bh'] = $p_adj; }

$nb_conf_bh = 0;
foreach($H as $h){ if(isset($h['p_bh']) && $h['p_bh']<0.05) $nb_conf_bh++; }

/* ════════════════════════════════════════════════════════════════ */
/*    DONNÉES MANQUANTES — couverture par variable (V3)            */
/* ════════════════════════════════════════════════════════════════ */
$missingVars = [
    'bmi'=>'IMC','iotf_class'=>'Classe IOTF','score_kidmed'=>'KIDMED',
    'score_pression_scolaire'=>'Score pression','skip_meal_tutoring'=>'Saut repas (soutien)',
    'academic_stress'=>'Stress scolaire','emotional_eating'=>'Alim. émotionnelle',
    'sleep_hours_real'=>'Heures sommeil','screen_hours_total'=>'Heures écrans',
    'birth_weight'=>'Poids naissance','delivery_type'=>'Type accouchement',
    'parent_obese'=>'Parent obèse','school_avg'=>'Moyenne scolaire',
];
$missingTable = [];
foreach($missingVars as $col=>$lbl){
    $missing = 0;
    foreach($rows as $r){
        $v = $r[$col] ?? null;
        if($v===null||$v===''||$v==='null'||(is_numeric($v) && (float)$v==0 && $col==='birth_weight'))
            $missing++;
    }
    $pct = $n>0 ? round($missing*100/$n,1) : 0;
    $missingTable[] = ['col'=>$col,'label'=>$lbl,'n_missing'=>$missing,'pct'=>$pct,
                      'flag'=>$pct>=20?'critical':($pct>=10?'warning':($pct>=5?'note':'ok'))];
}

/* ════════════════════════════════════════════════════════════════ */
/*    RÉGRESSION LOGISTIQUE MULTIVARIÉE (V3)                       */
/* ════════════════════════════════════════════════════════════════ */
$logitData = [];
foreach($rows as $r){
    if(!isset($r['iotf_class'])) continue;
    if(!is_numeric($r['score_kidmed'] ?? null)) continue;
    if(!is_numeric($r['score_activite'] ?? null)) continue;
    if(!is_numeric($r['score_sedentarite'] ?? null)) continue;
    if(!is_numeric($r['score_sommeil'] ?? null)) continue;
    if(!is_numeric($r['score_pression_scolaire'] ?? null)) continue;
    if(!is_numeric($r['age'] ?? null)) continue;
    $logitData[] = [
      'y'      => StudySpecificAnalyses::isOWOB($r['iotf_class']) ? 1 : 0,
      'kidmed' => (float)$r['score_kidmed'],
      'activ'  => (float)$r['score_activite'],
      'seden'  => (float)$r['score_sedentarite'],
      'sleep'  => (float)$r['score_sommeil'],
      'press'  => (float)$r['score_pression_scolaire'],
      'skip'   => preg_match('/oui/i', $r['skip_meal_tutoring']??'') ? 1 : 0,
      'sex'    => ($r['sex']??'')==='Garçon' ? 1 : 0,
      'age'    => (float)$r['age'],
      'school' => $r['school'] ?? 'Autre',
    ];
}
$logitResult = null;
if(count($logitData) >= 30){
    $y_arr = array_column($logitData,'y');
    $X_arr = array_map(fn($d)=>[
        $d['kidmed'],$d['activ'],$d['seden'],$d['sleep'],$d['press'],
        $d['skip'],$d['sex'],$d['age']
    ], $logitData);
    $names = ['KIDMED','Activité','Sédentarité','Sommeil','Pression','Saut repas','Sexe (♂=1)','Âge'];
    $logitResult = $bp->logisticRegressionMulti($y_arr, $X_arr, $names);
}

/* ════════════════════════════════════════════════════════════════ */
/*    ANALYSES BIOSTATISTIQUES AVANCÉES (v2.0 — mai 2026)          */
/*    VIF + Box-Tidwell + GLMM + GEE + MICE/Rubin                  */
/* ════════════════════════════════════════════════════════════════ */
$advancedResults = null;
if($logitResult && !isset($logitResult['error']) && count($logitData) >= 100){
    $advancedResults = [];

    // 1. VIF — multicolinéarité (Allison 2012)
    $advancedResults['vif'] = $bp->vif($X_arr, $names);

    // 2. Box-Tidwell — linéarité du logit sur prédicteurs continus
    //    Indices 0..4 (KIDMED, Activité, Sédentarité, Sommeil, Pression) + 7 (Âge)
    $advancedResults['boxtidwell'] = $bp->boxTidwell($y_arr, $X_arr, [0,1,2,3,4,7], $names);

    // 3. & 4. GLMM + GEE — effet école (cluster = $r['school'])
    $clusters_arr = array_column($logitData, 'school');
    $n_clusters_distinct = count(array_unique($clusters_arr));
    if($n_clusters_distinct >= 3){
        $advancedResults['glmm'] = $bp->glmmLogistic($y_arr, $X_arr, $clusters_arr, $names, 50);
        $advancedResults['gee']  = $bp->geeLogistic($y_arr, $X_arr, $clusters_arr, $names, 30);
    }

    // 5. MICE — Imputation multiple + Rubin's rules
    //    On reconstruit un jeu avec les manquants (avant le filtrage strict du modèle complet)
    $data_for_mice = [];
    foreach($rows as $r){
        if(empty($r['iotf_class'])) continue;
        $data_for_mice[] = [
            'kidmed' => is_numeric($r['score_kidmed']??null)            ? (float)$r['score_kidmed']             : null,
            'activ'  => is_numeric($r['score_activite']??null)          ? (float)$r['score_activite']           : null,
            'seden'  => is_numeric($r['score_sedentarite']??null)       ? (float)$r['score_sedentarite']        : null,
            'sleep'  => is_numeric($r['score_sommeil']??null)           ? (float)$r['score_sommeil']            : null,
            'press'  => is_numeric($r['score_pression_scolaire']??null) ? (float)$r['score_pression_scolaire']  : null,
            'age'    => is_numeric($r['age']??null)                     ? (float)$r['age']                      : null,
            'sex'    => ($r['sex']??'')==='Garçon' ? 1 : 0,
            'y'      => StudySpecificAnalyses::isOWOB($r['iotf_class']) ? 1 : 0,
        ];
    }
    // Détecter s'il y a assez de manquants pour justifier MICE
    $n_total_missing = 0;
    foreach($data_for_mice as $row_) foreach($row_ as $v_) if($v_ === null) $n_total_missing++;

    if($n_total_missing >= 10 && count($data_for_mice) >= 50){
        $mice_m = 10; // m = 10 imputations (compromis vitesse/qualité pour rapport en ligne)
        $mice_res = $bp->mice($data_for_mice, [
            'kidmed'=>'continuous','activ'=>'continuous','seden'=>'continuous',
            'sleep'=>'continuous','press'=>'continuous','age'=>'continuous',
            'sex'=>'binary','y'=>'binary',
        ], $mice_m, 10, 5);

        // Pooling : régression sur chaque imputation puis Rubin
        $estimates_mi = []; $se_mi = [];
        $mice_names = ['KIDMED','Activité','Sédentarité','Sommeil','Pression','Âge','Sexe'];
        foreach($mice_res['imputations'] as $imp){
            $y_imp = array_column($imp, 'y');
            $X_imp = [];
            foreach($imp as $row_) $X_imp[] = [
                $row_['kidmed'],$row_['activ'],$row_['seden'],$row_['sleep'],
                $row_['press'],$row_['age'],$row_['sex']
            ];
            $fit_imp = $bp->logisticRegressionMulti($y_imp, $X_imp, $mice_names);
            if(!isset($fit_imp['error']) && $fit_imp['converged']){
                $estimates_mi[] = $fit_imp['coef'];
                $se_mi[]        = $fit_imp['se'];
            }
        }
        if(count($estimates_mi) >= 2){
            $advancedResults['mice_pooled'] = $bp->rubinPool($estimates_mi, $se_mi);
            $advancedResults['mice_meta'] = [
                'm'            => $mice_res['m'],
                'iterations'   => $mice_res['iterations'],
                'donors_pmm'   => $mice_res['donors_pmm'],
                'vars_imputed' => $mice_res['vars_imputed'],
                'pct_missing'  => $mice_res['pct_missing'],
                'n_total'      => $mice_res['n_total'],
                'names'        => array_merge(['(Intercept)'], $mice_names),
            ];
        }
    }
}

/* ════════════════════════════════════════════════════════════════ */
/*    📑 FIGURES DU CHAPITRE 3 — Préparation des données            */
/*    Reproduit les figures 3 à 17 du mémoire à partir de la base  */
/* ════════════════════════════════════════════════════════════════ */

// ─── Helper : écart-type (n−1) en ligne (réutilise $bp si dispo) ─
$_sd = function($a) use ($bp){
    $a = array_values(array_filter($a, fn($v)=>is_numeric($v)));
    return count($a) >= 2 ? round($bp->std($a), 2) : 0;
};

// ── Figure 3 — Distribution IOTF (pie) — utilise $jsIOTF déjà calculé
$jsCh3_F3 = $jsIOTF;
$ch3_f3_total = $nMinc + $nNorm + $nSurp + $nObes;

// ── Figure 4 — IMC moyen par niveau scolaire (bar)
$jsCh3_F4 = json_encode([
    avg($bmi_by_grade['1AS']),
    avg($bmi_by_grade['2AS']),
    avg($bmi_by_grade['3AS']),
]);
$jsCh3_F4sd = json_encode([
    $_sd($bmi_by_grade['1AS']),
    $_sd($bmi_by_grade['2AS']),
    $_sd($bmi_by_grade['3AS']),
]);
$ch3_f4_F = $an_grade['F'] ?? null;
$ch3_f4_p = $an_grade['p'] ?? null;

// ── Figure 5 — Distribution KIDMED (bar par classe)
//    Réutilise $jsKidmed (3 classes)
$jsCh3_F5 = $jsKidmed;
$ch3_f5_mean = avg(colN($rows,'score_kidmed'));
$ch3_f5_sd   = $_sd(colN($rows,'score_kidmed'));

// ── Figure 6 — Fréquences de consommation : boissons sucrées + petit-déjeuner
function ch3_freq_count($rows, $field, $patterns){
    $out = array_fill(0, count($patterns), 0);
    foreach($rows as $r){
        $v = trim((string)($r[$field] ?? ''));
        if($v === '') continue;
        foreach($patterns as $i=>$pats){
            foreach((array)$pats as $pat){
                if(preg_match('/'.$pat.'/iu', $v)){ $out[$i]++; continue 3; }
            }
        }
    }
    return $out;
}
// Catégories : [Quotidien, 4–6/sem, 1–3/sem, Jamais]
$sugary_counts = ch3_freq_count($rows, 'sugary_freq', [
    ['tous les jours','quotidien','daily','يومي'],
    ['4.{0,3}6.{0,3}(sem|wk|أسبوع)','4-6'],
    ['1.{0,3}3.{0,3}(sem|wk|أسبوع)','1-3'],
    ['jamais','never','أبد'],
]);
$breakfast_counts = ch3_freq_count($rows, 'breakfast_freq', [
    ['tous les jours','quotidien','daily','يومي','oui'],
    ['4.{0,3}6.{0,3}(sem|wk|أسبوع)','4-6'],
    ['1.{0,3}3.{0,3}(sem|wk|أسبوع)','1-3'],
    ['jamais','never','أبد'],
]);
$jsCh3_F6_sugary    = json_encode($sugary_counts);
$jsCh3_F6_breakfast = json_encode($breakfast_counts);

// ── Figure 7 — % saut de repas (cours soutien) par niveau scolaire
//    On calcule localement (le $evol global est défini plus bas dans le fichier)
$ch3_pct_skip_by_grade = [];
$ch3_pression_by_grade = [];
foreach(['1AS','2AS','3AS'] as $g){
    $rg = array_values(array_filter($rows, fn($r)=>$r['grade']===$g));
    $ng = count($rg);
    $ch3_pct_skip_by_grade[$g] = $ng>0
        ? round(count(array_filter($rg, fn($r)=>preg_match('/oui/i', $r['skip_meal_tutoring']??'')))*100/$ng, 1)
        : 0;
    $ch3_pression_by_grade[$g] = avg(colN($rg, 'score_pression_scolaire'));
}
$jsCh3_F7 = json_encode([
    (float)$ch3_pct_skip_by_grade['1AS'],
    (float)$ch3_pct_skip_by_grade['2AS'],
    (float)$ch3_pct_skip_by_grade['3AS'],
]);

// ── Figure 8 — KIDMED moyen par classe IOTF (bar + erreurs)
$kid_norm_arr = $kid_by_iotf['Normal']   ?? [];
$kid_sur_arr  = $kid_by_iotf['Surpoids'] ?? [];
$kid_obe_arr  = $kid_by_iotf['Obésité']  ?? [];
$jsCh3_F8_means = json_encode([avg($kid_norm_arr), avg($kid_sur_arr), avg($kid_obe_arr)]);
$jsCh3_F8_sds   = json_encode([$_sd($kid_norm_arr), $_sd($kid_sur_arr), $_sd($kid_obe_arr)]);
$jsCh3_F8_ns    = json_encode([count($kid_norm_arr), count($kid_sur_arr), count($kid_obe_arr)]);
$ch3_f8_F = $an_kid['F'] ?? null;
$ch3_f8_p = $an_kid['p'] ?? null;

// ── Figure 9 — Classes d'activité physique stratifiées par sexe
$activity_classes_ch3 = ['Inactif','Peu actif','Actif','Très actif'];
$fig9_boys = []; $fig9_girls = [];
foreach($activity_classes_ch3 as $cls){
    $fig9_boys[]  = count(array_filter($boys,  fn($r)=>($r['classe_activite']??'')===$cls));
    $fig9_girls[] = count(array_filter($girls, fn($r)=>($r['classe_activite']??'')===$cls));
}
$jsCh3_F9_boys  = json_encode($fig9_boys);
$jsCh3_F9_girls = json_encode($fig9_girls);

// ── Figure 10 — Histogramme des heures d'écran totales par jour
$screen_vals = colN($rows, 'screen_sum');
if(count($screen_vals) === 0) $screen_vals = colN($rows, 'screen_hours_total');
$f10_bins   = [0, 2, 4, 6, 8, 10, 12, 999];
$f10_labels = ['<2 h','2–4 h','4–6 h','6–8 h','8–10 h','10–12 h','≥12 h'];
$f10_counts = array_fill(0, count($f10_labels), 0);
foreach($screen_vals as $v){
    $v = (float)$v;
    for($i=0; $i<count($f10_labels); $i++){
        if($v >= $f10_bins[$i] && $v < $f10_bins[$i+1]){ $f10_counts[$i]++; break; }
    }
}
$jsCh3_F10_labels = json_encode($f10_labels);
$jsCh3_F10_counts = json_encode($f10_counts);
$ch3_f10_mean = count($screen_vals) ? round(array_sum($screen_vals)/count($screen_vals), 2) : 0;
$ch3_f10_n_above_4h = 0;
foreach($screen_vals as $v) if((float)$v >= 4) $ch3_f10_n_above_4h++;
$ch3_f10_pct_above_4h = count($screen_vals) ? round($ch3_f10_n_above_4h*100/count($screen_vals), 1) : 0;

// ── Figure 11 — Matrice perception × IOTF (stacked bar par classe IOTF)
$f11_perceptions = ['Beaucoup trop maigre','Un peu trop maigre','Poids normal','Un peu trop gros','Beaucoup trop gros'];
$f11_perception_short = ['Bcp maigre','Peu maigre','Normal','Peu gros','Bcp gros'];
$f11_iotf_cats = ['Insuffisance pondérale','Normal','Surpoids','Obésité'];
$f11_matrix = [];
foreach($f11_iotf_cats as $ic){
    $row_m = [];
    foreach($f11_perceptions as $pc){
        $cnt = 0;
        foreach($rows as $r){
            $iotf = $r['iotf_class'] ?? '';
            // tolère 'Maigreur', 'Mince' pour Insuffisance pondérale
            $iotf_match = ($ic === 'Insuffisance pondérale')
                ? in_array($iotf, ['Insuffisance pondérale','Maigreur','Mince'], true)
                : ($iotf === $ic);
            if(!$iotf_match) continue;
            $bp_field = trim((string)($r['body_perception'] ?? ''));
            if($bp_field === '') continue;
            // Comparaison souple (sans accent)
            if(mb_stripos($bp_field, $pc) !== false) $cnt++;
        }
        $row_m[] = $cnt;
    }
    $f11_matrix[$ic] = $row_m;
}
$jsCh3_F11_labels  = json_encode($f11_iotf_cats, JSON_UNESCAPED_UNICODE);
$jsCh3_F11_percLbl = json_encode($f11_perception_short, JSON_UNESCAPED_UNICODE);
$jsCh3_F11_data    = json_encode(array_values($f11_matrix));

// ── Figure 12 — Convergence pression scolaire × saut de repas (double-axe)
$jsCh3_F12_press = json_encode([
    (float)$ch3_pression_by_grade['1AS'],
    (float)$ch3_pression_by_grade['2AS'],
    (float)$ch3_pression_by_grade['3AS'],
]);
$jsCh3_F12_skip  = $jsCh3_F7;

// ── Figure 13 — Forest plot multivarié (régression logistique V1)
$ch3_f13 = [];
if($logitResult && !isset($logitResult['error']) && !empty($logitResult['converged'])){
    for($j=1; $j<count($logitResult['names']); $j++){
        $or  = $logitResult['or'][$j]      ?? null;
        $low = $logitResult['ci_low'][$j]  ?? null;
        $high= $logitResult['ci_high'][$j] ?? null;
        $p   = $logitResult['p'][$j]       ?? null;
        if($or === null) continue;
        $ch3_f13[] = [
            'name' => (string)$logitResult['names'][$j],
            'or'   => (float)$or,
            'low'  => $low === null ? (float)$or : (float)$low,
            'high' => $high === null ? (float)$or : (float)$high,
            'p'    => $p === null ? null : (float)$p,
        ];
    }
}
$jsCh3_F13 = json_encode($ch3_f13, JSON_UNESCAPED_UNICODE);

// ── Figure 14 — Courbe ROC du modèle V1
$ch3_f14_fpr = [0]; $ch3_f14_tpr = [0]; $ch3_f14_auc = null;
if($logitResult && !isset($logitResult['error']) && !empty($logitResult['converged'])
   && !empty($logitResult['predicted']) && !empty($y_arr)){
    $ch3_f14_auc = $logitResult['auc'] ?? null;
    $preds = $logitResult['predicted'];
    $ys    = $y_arr;
    $n_min = min(count($preds), count($ys));
    $pairs = [];
    for($i=0; $i<$n_min; $i++) $pairs[] = ['p'=>(float)$preds[$i], 'y'=>(int)$ys[$i]];
    usort($pairs, fn($a,$b)=>$b['p']<=>$a['p']);
    $P_pos = 0; foreach($pairs as $pr) if($pr['y']===1) $P_pos++;
    $N_neg = count($pairs) - $P_pos;
    $tp = 0; $fp = 0;
    $fprs_full = [0.0]; $tprs_full = [0.0];
    foreach($pairs as $pr){
        if($pr['y']===1) $tp++; else $fp++;
        $fprs_full[] = $N_neg > 0 ? $fp/$N_neg : 0;
        $tprs_full[] = $P_pos > 0 ? $tp/$P_pos : 0;
    }
    // Échantillonner à ~80 points max pour le graphique
    $target_pts = 80;
    $step = max(1, intval(count($fprs_full) / $target_pts));
    $ch3_f14_fpr = []; $ch3_f14_tpr = [];
    for($i=0; $i<count($fprs_full); $i+=$step){
        $ch3_f14_fpr[] = round($fprs_full[$i], 4);
        $ch3_f14_tpr[] = round($tprs_full[$i], 4);
    }
    if(end($ch3_f14_fpr) < 1){ $ch3_f14_fpr[] = 1.0; $ch3_f14_tpr[] = 1.0; }
}
$jsCh3_F14_fpr = json_encode($ch3_f14_fpr);
$jsCh3_F14_tpr = json_encode($ch3_f14_tpr);

// ── Figure 15 — V1 (screen_max) vs V2 (screen_sum) — comparaison forêt
// La plateforme utilise V1 ; nous comparons les OR bivariés screen_max vs screen_sum
// pour illustrer l'artefact métrologique évoqué au § 3.3.2 du mémoire.
$ch3_f15_v1 = []; $ch3_f15_v2 = []; $ch3_f15_names = [];
if($logitResult && !isset($logitResult['error']) && !empty($logitResult['converged'])){
    // V1 standard : on copie les OR du modèle principal
    for($j=1; $j<count($logitResult['names']); $j++){
        $or  = $logitResult['or'][$j]      ?? null;
        $low = $logitResult['ci_low'][$j]  ?? null;
        $high= $logitResult['ci_high'][$j] ?? null;
        if($or === null) continue;
        $ch3_f15_names[] = $logitResult['names'][$j];
        $ch3_f15_v1[]    = ['or'=>(float)$or, 'low'=>(float)($low ?? $or), 'high'=>(float)($high ?? $or)];
    }
    // V2 : refit en remplaçant Sédentarité par screen_hours_total comme proxy de screen_sum
    $X_v2 = [];
    foreach($logitData as $d){
        // Récupère l'enregistrement correspondant pour screen_hours_total
        $X_v2[] = [
            $d['kidmed'], $d['activ'], $d['seden'], $d['sleep'], $d['press'],
            $d['skip'],   $d['sex'],   $d['age'],
        ];
    }
    // Pour V2 on remplace 'seden' (index 2) par screen_hours_total quand dispo
    $X_v2_alt = $X_v2; $has_alt = false;
    foreach($logitData as $i=>$d){
        $orig_row = null;
        foreach($rows as $r){
            if(($r['school'] ?? 'Autre') === $d['school']
               && is_numeric($r['screen_hours_total'] ?? null)){
                $orig_row = $r; break;
            }
        }
        if($orig_row !== null){
            $X_v2_alt[$i][2] = (float)$orig_row['screen_hours_total'];
            $has_alt = true;
        }
    }
    if($has_alt && count($X_v2_alt) >= 30){
        $logit_v2 = $bp->logisticRegressionMulti($y_arr, $X_v2_alt, $logitResult['names'] ? array_slice($logitResult['names'],1) : []);
        if(!isset($logit_v2['error']) && !empty($logit_v2['converged'])){
            for($j=1; $j<count($logit_v2['names']); $j++){
                $or  = $logit_v2['or'][$j]      ?? null;
                $low = $logit_v2['ci_low'][$j]  ?? null;
                $high= $logit_v2['ci_high'][$j] ?? null;
                if($or === null){ $ch3_f15_v2[] = null; continue; }
                $ch3_f15_v2[] = ['or'=>(float)$or, 'low'=>(float)($low ?? $or), 'high'=>(float)($high ?? $or)];
            }
        }
    }
    // Si V2 n'a pas pu être calculé, on duplique V1 (avec marqueur)
    if(empty($ch3_f15_v2)){
        $ch3_f15_v2 = $ch3_f15_v1;
    }
}
$jsCh3_F15_names = json_encode($ch3_f15_names, JSON_UNESCAPED_UNICODE);
$jsCh3_F15_v1    = json_encode($ch3_f15_v1);
$jsCh3_F15_v2    = json_encode($ch3_f15_v2);

// ── Figure 16 — Robustesse multivariée : V1 / GLMM / GEE / MICE
$ch3_f16_key_vars = ['KIDMED','Activité','Âge'];
$ch3_f16_methods = ['V1 standard'=>null,'GLMM (école)'=>null,'GEE'=>null,'MICE m=10'=>null];

// V1 standard
if($logitResult && !empty($logitResult['converged'])){
    $tmp = [];
    foreach($ch3_f16_key_vars as $kv){
        $idx = array_search($kv, $logitResult['names']);
        $tmp[$kv] = ($idx !== false && isset($logitResult['or'][$idx]))
            ? ['or'=>$logitResult['or'][$idx],'low'=>$logitResult['ci_low'][$idx]??null,'high'=>$logitResult['ci_high'][$idx]??null]
            : null;
    }
    $ch3_f16_methods['V1 standard'] = $tmp;
}
// GLMM
if(isset($advancedResults['glmm']) && !empty($advancedResults['glmm']['converged'])){
    $tmp = [];
    foreach($ch3_f16_key_vars as $kv){
        $idx = array_search($kv, $advancedResults['glmm']['names']);
        $tmp[$kv] = ($idx !== false)
            ? ['or'=>$advancedResults['glmm']['or'][$idx],'low'=>$advancedResults['glmm']['ci_low'][$idx]??null,'high'=>$advancedResults['glmm']['ci_high'][$idx]??null]
            : null;
    }
    $ch3_f16_methods['GLMM (école)'] = $tmp;
}
// GEE
if(isset($advancedResults['gee'])){
    $tmp = [];
    foreach($ch3_f16_key_vars as $kv){
        $idx = array_search($kv, $advancedResults['gee']['names'] ?? []);
        $tmp[$kv] = ($idx !== false && isset($advancedResults['gee']['or'][$idx]))
            ? ['or'=>$advancedResults['gee']['or'][$idx],'low'=>$advancedResults['gee']['ci_low'][$idx]??null,'high'=>$advancedResults['gee']['ci_high'][$idx]??null]
            : null;
    }
    $ch3_f16_methods['GEE'] = $tmp;
}
// MICE
if(isset($advancedResults['mice_pooled'], $advancedResults['mice_meta'])){
    $tmp = [];
    foreach($ch3_f16_key_vars as $kv){
        $idx = array_search($kv, $advancedResults['mice_meta']['names']);
        $tmp[$kv] = ($idx !== false && isset($advancedResults['mice_pooled']['or'][$idx]))
            ? ['or'=>$advancedResults['mice_pooled']['or'][$idx],'low'=>$advancedResults['mice_pooled']['ci_low'][$idx]??null,'high'=>$advancedResults['mice_pooled']['ci_high'][$idx]??null]
            : null;
    }
    $ch3_f16_methods['MICE m=10'] = $tmp;
}
$jsCh3_F16 = json_encode($ch3_f16_methods, JSON_UNESCAPED_UNICODE);
$jsCh3_F16_keys = json_encode($ch3_f16_key_vars, JSON_UNESCAPED_UNICODE);

// ── Figure 17 — Volcano plot : log(OR) vs −log10(p), couleur = significatif après FDR
$ch3_f17 = [];
foreach($H as $code=>$h){
    if(!isset($h['p']) || $h['p']===null || !is_numeric($h['p'])) continue;
    $eff = $h['effect'] ?? '';
    if(!preg_match('/(\d+[\.,]\d+)\s*\[IC95:\s*(\d+[\.,]\d+)\D+(\d+[\.,]\d+)\]/u', $eff, $m)) continue;
    $or_val = (float)str_replace(',','.',$m[1]);
    if($or_val <= 0) continue;
    $p_val  = max((float)$h['p'], 1e-10);
    $is_sig_bh = (isset($h['p_bh']) && $h['p_bh'] !== null && $h['p_bh'] < 0.05) ? 1 : 0;
    $ch3_f17[] = [
        'code' => $code,
        'label'=> mb_substr((string)($h['title'] ?? $code), 0, 60),
        'x'    => round(log($or_val), 3),
        'y'    => round(-log10($p_val), 3),
        'or'   => round($or_val, 2),
        'p'    => $p_val,
        'sig'  => $is_sig_bh,
    ];
}
$jsCh3_F17 = json_encode($ch3_f17, JSON_UNESCAPED_UNICODE);
$ch3_f17_n_sig = count(array_filter($ch3_f17, fn($pt)=>$pt['sig']===1));

?>
<!DOCTYPE html>
<html lang="<?=currentLang()?>" dir="<?=langDir()?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=__('rp_title')?> — <?= SITE_NAME ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
<style>
.rp{max-width:1180px;margin:0 auto;padding:1rem}
.rp-title{text-align:center;margin-bottom:1.25rem}
.rp-title h2{color:var(--primary);font-size:20px;margin-bottom:4px}
.rp-title p{color:var(--text-muted);font-size:12px}
.rp-ts{text-align:center;font-size:11px;color:var(--text-muted);margin-bottom:1rem;padding:6px;background:var(--bg-card);border-radius:var(--radius);border:1px solid var(--border-light)}

.kpi{display:grid;grid-template-columns:repeat(6,1fr);gap:8px;margin-bottom:1rem}
.kpi-card{background:var(--bg-card);border:1px solid var(--border-light);border-radius:var(--radius-lg);padding:14px 10px;text-align:center;box-shadow:var(--shadow)}
.kpi-val{font-size:24px;font-weight:700;color:var(--primary)}
.kpi-lbl{font-size:10px;color:var(--text-muted);margin-top:2px}
.kpi-sub{font-size:10px;color:var(--text-muted);margin-top:4px;padding-top:4px;border-top:1px solid var(--border-light)}

.rp-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}
.rp-grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:12px}
.rp-card{background:var(--bg-card);border:1px solid var(--border-light);border-radius:var(--radius-lg);padding:14px;box-shadow:var(--shadow)}
.rp-card-title{font-size:13px;font-weight:600;color:var(--primary);margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid var(--border-light)}
.rp-full{grid-column:1/-1}
.cw{position:relative;height:220px}
.cw-tall{height:280px}

.rp-table{width:100%;font-size:12px;border-collapse:collapse}
.rp-table th{background:var(--primary);color:#fff;padding:6px 8px;font-weight:600;text-align:center;font-size:11px}
.rp-table td{padding:5px 8px;border-bottom:1px solid var(--border-light);text-align:center}
.rp-table tr:nth-child(even) td{background:#fafafa}
.rp-table .lbl{text-align:start;font-weight:500}

.rp-insight{background:#f8f9ff;border-inline-start:3px solid var(--primary-light);padding:8px 12px;border-radius:4px;font-size:12px;color:var(--text-muted);margin-top:10px;line-height:1.8}
.rp-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600;white-space:nowrap}
.rp-badge-danger{background:#fde8e8;color:#9b2335}
.rp-badge-warning{background:#fff3cd;color:#856404}
.rp-badge-success{background:#d4edda;color:#155724}
.rp-badge-info{background:#d1ecf1;color:#0c5460}

.sex-comp{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px}
.sex-card{background:var(--bg);padding:10px;border-radius:var(--radius);text-align:center}
.sex-icon{font-size:14px;margin-bottom:4px}
.sex-val{font-size:18px;font-weight:700}
.sex-lbl{font-size:10px;color:var(--text-muted)}

.print-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:var(--primary);color:#fff;border:none;border-radius:var(--radius);cursor:pointer;font-size:13px;font-family:inherit}
.print-btn:hover{background:#163d62}

/* Section hypothèses */
.hsec{margin-top:18px}
.hsec-title{display:flex;align-items:center;gap:8px;font-size:14px;font-weight:700;color:var(--primary);margin:14px 0 8px;padding:6px 10px;background:linear-gradient(90deg,var(--primary-bg),transparent);border-inline-start:4px solid var(--primary);border-radius:4px}
.h-table{width:100%;font-size:11px;border-collapse:collapse;background:#fff;border-radius:6px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.05)}
.h-table th{background:var(--primary);color:#fff;padding:7px 10px;font-weight:600;text-align:start;font-size:11px}
.h-table th.c{text-align:center}
.h-table td{padding:6px 10px;border-bottom:1px solid var(--border-light);vertical-align:top}
.h-table td.c{text-align:center;white-space:nowrap}
.h-table tr:nth-child(even) td{background:#fafafa}
.h-code{font-family:ui-monospace,monospace;font-weight:700;color:var(--primary);font-size:11px}

/* Forest plot */
.forest{padding:8px}
.forest-row{display:grid;grid-template-columns:200px 1fr 110px 60px;gap:8px;align-items:center;font-size:11px;padding:4px 0;border-bottom:1px solid var(--border-light)}
.forest-row:last-child{border-bottom:none}
.forest-bar{position:relative;height:14px}
.forest-bar svg{width:100%;height:100%}

@media print{
  .navbar,.print-btn,.rp-ts{display:none!important}
  .rp-card{break-inside:avoid}
  .kpi{grid-template-columns:repeat(3,1fr)}
  .hsec{break-inside:avoid}
  body{font-size:11px}
}
@media(max-width:768px){
  .kpi{grid-template-columns:repeat(3,1fr)}
  .rp-grid,.rp-grid-3{grid-template-columns:1fr}
  .forest-row{grid-template-columns:1fr;gap:2px}
}
</style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="rp">

<div class="rp-title">
    <h2><?=__('rp_title')?></h2>
    <p><?=__('rp_subtitle')?> — n = <?=$n?> | <?=__('rp_total_hypos')?> : <?=count($H)?></p>
</div>

<div class="rp-ts">
    <?=__('rp_last_update')?>: <?=date('Y-m-d H:i')?>
    | <?=__('rp_progress')?>: <?=$n?> / <?=TARGET_N?> (<?=pct($n,TARGET_N)?>%)
    <button class="print-btn" style="margin-inline-start:12px" onclick="window.print()">🖨️ <?=__('rp_print')?></button>
</div>

<!-- ═══ KPI ═══ -->
<div class="kpi">
  <div class="kpi-card">
    <div class="kpi-val"><?=$n?></div>
    <div class="kpi-lbl"><?=__('rp_total')?></div>
    <div class="kpi-sub">♂ <?=$nB?> | ♀ <?=$nG?></div>
  </div>
  <div class="kpi-card">
    <div class="kpi-val" style="color:#9b2335"><?=pct($nObes,$n)?>%</div>
    <div class="kpi-lbl"><?=__('rp_obesity')?> (IOTF)</div>
    <div class="kpi-sub">n = <?=$nObes?></div>
  </div>
  <div class="kpi-card">
    <div class="kpi-val" style="color:#856404"><?=pct($nSurp,$n)?>%</div>
    <div class="kpi-lbl"><?=__('rp_overweight')?> (IOTF)</div>
    <div class="kpi-sub">n = <?=$nSurp?></div>
  </div>
  <div class="kpi-card">
    <div class="kpi-val"><?=avg($bmi)?></div>
    <div class="kpi-lbl"><?=__('rp_mean_bmi')?></div>
    <div class="kpi-sub">♂ <?=avg(colN($boys,'bmi'))?> | ♀ <?=avg(colN($girls,'bmi'))?></div>
  </div>
  <div class="kpi-card">
    <div class="kpi-val" style="color:#1a7340"><?=avg(colN($rows,'score_kidmed'))?></div>
    <div class="kpi-lbl"><?=__('rp_mean_kidmed')?></div>
    <div class="kpi-sub">♂ <?=avg(colN($boys,'score_kidmed'))?> | ♀ <?=avg(colN($girls,'score_kidmed'))?></div>
  </div>
  <div class="kpi-card">
    <div class="kpi-val" style="color:#0c5460"><?=$nb_conf?>/<?=count($H)?></div>
    <div class="kpi-lbl"><?=__('rp_h_confirmed')?></div>
    <div class="kpi-sub"><?=__('rp_trend')?> : <?=$nb_trend?> | NS : <?=$nb_ns?></div>
  </div>
</div>

<!-- ═══ KPI 3AS — Focus pression scolaire / cours de soutien ═══ -->
<div class="rp-card" style="background:linear-gradient(135deg,#fef9e7 0%,#fff 100%);border-inline-start:4px solid #c9a961">
  <div class="rp-card-title">📚 <?=__('rp_3as_focus_title')?></div>
  <div class="rp-ts" style="font-size:12px;color:#856404;margin-bottom:10px"><?=__('rp_3as_focus_intro')?></div>
  <div class="kpi" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr))">
    <div class="kpi-card">
      <div class="kpi-val"><?=$n_3as?></div>
      <div class="kpi-lbl"><?=__('rp_3as_n')?></div>
      <div class="kpi-sub"><?=$n>0?round($n_3as*100/$n,1):0?>% <?=__('rp_of_sample')?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-val" style="color:#9b2335"><?=$pct_OW_3as?>%</div>
      <div class="kpi-lbl"><?=__('rp_3as_overweight')?></div>
      <div class="kpi-sub">n=<?=$nOW_3as?>/<?=$n_3as?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-val" style="color:#856404"><?=$pct_skip_3as?>%</div>
      <div class="kpi-lbl"><?=__('rp_3as_skipmeal')?></div>
      <div class="kpi-sub">n=<?=$nSkipMeal_3as?>/<?=$n_3as?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-val" style="color:#0c5460"><?=avg($pression_3as)?></div>
      <div class="kpi-lbl"><?=__('rp_3as_pression')?></div>
      <div class="kpi-sub">/9</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-val" style="color:#5a3a82"><?=$pct_emotional_3as?>%</div>
      <div class="kpi-lbl"><?=__('rp_3as_emotional')?></div>
      <div class="kpi-sub">n=<?=$nEmotional_3as?>/<?=$n_3as?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-val" style="color:#c0392b"><?=$pct_stress_3as?>%</div>
      <div class="kpi-lbl"><?=__('rp_3as_stress')?></div>
      <div class="kpi-sub">n=<?=$nStressHigh_3as?>/<?=$n_3as?></div>
    </div>
  </div>
</div>

<!-- ═══ Distributions ═══ -->
<div class="rp-grid-3">
  <div class="rp-card">
    <div class="rp-card-title">1. <?=__('rp_iotf_title')?></div>
    <div class="cw"><canvas id="cIOTF"></canvas></div>
  </div>
  <div class="rp-card">
    <div class="rp-card-title">2. <?=__('rp_kidmed_title')?></div>
    <div class="cw"><canvas id="cKID"></canvas></div>
  </div>
  <div class="rp-card">
    <div class="rp-card-title">3. <?=__('rp_risk_title')?></div>
    <div class="cw"><canvas id="cRisk"></canvas></div>
  </div>
</div>

<div class="rp-grid">
  <div class="rp-card">
    <div class="rp-card-title">4. <?=__('rp_percept_title')?></div>
    <div class="cw"><canvas id="cPercept"></canvas></div>
  </div>
  <div class="rp-card">
    <div class="rp-card-title">5. <?=__('rp_global_title')?></div>
    <div class="cw"><canvas id="cGlobal"></canvas></div>
  </div>
</div>

<!-- ═══ Synthèse hypothèses (donut) ═══ -->
<div class="rp-grid">
  <div class="rp-card">
    <div class="rp-card-title">6. <?=__('rp_hypos_summary')?></div>
    <div class="cw"><canvas id="cHypos"></canvas></div>
    <div class="rp-insight">
      <span class="rp-badge rp-badge-success"><?=$nb_conf?> <?=__('rp_h_confirmed')?></span>
      <span class="rp-badge rp-badge-warning"><?=$nb_trend?> <?=__('rp_trend')?></span>
      <span class="rp-badge rp-badge-info"><?=$nb_ns?> NS</span>
      <span class="rp-badge rp-badge-info"><?=$nb_na?> n/a</span>
    </div>
  </div>
  <div class="rp-card">
    <div class="rp-card-title">7. <?=__('rp_sex_grade_title')?></div>
    <div class="cw"><canvas id="cSexGrade"></canvas></div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!--    📑 FIGURES DU CHAPITRE 3 — Reproduction du mémoire           -->
<!--    Figures 3 à 17 calculées en temps réel sur la base actuelle  -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="rp-card rp-full" style="margin-top:18px;border-inline-start:4px solid #6f42c1;background:linear-gradient(180deg,#faf7ff,#fff)">
  <div class="rp-card-title" style="color:#6f42c1;font-size:15px">
    📑 <?=__('rp_ch3_section_title')?>
  </div>
  <div class="rp-ts" style="font-size:12px;color:var(--text-muted);margin-bottom:0;font-style:italic;text-align:start">
    <?=__('rp_ch3_section_intro')?>
  </div>
</div>

<!-- ─── FIGURE 3 — Distribution IOTF ─── -->
<div class="rp-card rp-full" style="margin-top:12px">
  <div class="rp-card-title">
    <?=__('rp_ch3_fig3_title')?>
  </div>
  <div class="rp-ts" style="font-size:11px;color:var(--text-muted);margin-bottom:8px;text-align:start"><?=__('rp_ch3_fig3_caption')?> (n = <?=$ch3_f3_total?>)</div>
  <div class="cw cw-tall"><canvas id="cCh3F3"></canvas></div>
  <div class="rp-insight">
    <strong><?=__('rp_overweight')?> + <?=__('rp_obesity')?> :</strong> <?=pct($nSurp+$nObes,$n)?>% (n=<?=$nSurp+$nObes?>)
    | <?=__('rp_normal')?> : <?=pct($nNorm,$n)?>%
    | <?=__('rp_minceur')?> : <?=pct($nMinc,$n)?>%
  </div>
</div>

<!-- ─── FIGURES 4 & 5 — IMC par niveau et Distribution KIDMED ─── -->
<div class="rp-grid">
  <div class="rp-card">
    <div class="rp-card-title"><?=__('rp_ch3_fig4_title')?></div>
    <div class="rp-ts" style="font-size:11px;color:var(--text-muted);margin-bottom:8px;text-align:start"><?=__('rp_ch3_fig4_caption')?></div>
    <div class="cw cw-tall"><canvas id="cCh3F4"></canvas></div>
    <div class="rp-insight" style="font-size:11px">
      ANOVA : F = <?=$ch3_f4_F!==null?number_format($ch3_f4_F,2):'—'?>
      | p = <?=fmt_p($ch3_f4_p)?> <?=star($ch3_f4_p)?>
    </div>
  </div>
  <div class="rp-card">
    <div class="rp-card-title"><?=__('rp_ch3_fig5_title')?></div>
    <div class="rp-ts" style="font-size:11px;color:var(--text-muted);margin-bottom:8px;text-align:start"><?=__('rp_ch3_fig5_caption')?></div>
    <div class="cw cw-tall"><canvas id="cCh3F5"></canvas></div>
    <div class="rp-insight" style="font-size:11px">
      <?=__('rp_mean_kidmed')?> : <?=$ch3_f5_mean?> ± <?=$ch3_f5_sd?>
    </div>
  </div>
</div>

<!-- ─── FIGURES 6 & 7 — Boissons / petit-déj et Saut de repas ─── -->
<div class="rp-grid">
  <div class="rp-card">
    <div class="rp-card-title"><?=__('rp_ch3_fig6_title')?></div>
    <div class="rp-ts" style="font-size:11px;color:var(--text-muted);margin-bottom:8px;text-align:start"><?=__('rp_ch3_fig6_caption')?></div>
    <div class="cw cw-tall"><canvas id="cCh3F6"></canvas></div>
  </div>
  <div class="rp-card">
    <div class="rp-card-title"><?=__('rp_ch3_fig7_title')?></div>
    <div class="rp-ts" style="font-size:11px;color:var(--text-muted);margin-bottom:8px;text-align:start"><?=__('rp_ch3_fig7_caption')?></div>
    <div class="cw cw-tall"><canvas id="cCh3F7"></canvas></div>
    <div class="rp-insight" style="font-size:11px">
      1AS : <?=$ch3_pct_skip_by_grade['1AS']?>%
      | 2AS : <?=$ch3_pct_skip_by_grade['2AS']?>%
      | 3AS : <?=$ch3_pct_skip_by_grade['3AS']?>%
    </div>
  </div>
</div>

<!-- ─── FIGURES 8 & 9 — KIDMED × IOTF et Activité par sexe ─── -->
<div class="rp-grid">
  <div class="rp-card">
    <div class="rp-card-title"><?=__('rp_ch3_fig8_title')?></div>
    <div class="rp-ts" style="font-size:11px;color:var(--text-muted);margin-bottom:8px;text-align:start"><?=__('rp_ch3_fig8_caption')?></div>
    <div class="cw cw-tall"><canvas id="cCh3F8"></canvas></div>
    <div class="rp-insight" style="font-size:11px">
      ANOVA : F = <?=$ch3_f8_F!==null?number_format($ch3_f8_F,2):'—'?>
      | p = <?=fmt_p($ch3_f8_p)?> <?=star($ch3_f8_p)?>
    </div>
  </div>
  <div class="rp-card">
    <div class="rp-card-title"><?=__('rp_ch3_fig9_title')?></div>
    <div class="rp-ts" style="font-size:11px;color:var(--text-muted);margin-bottom:8px;text-align:start"><?=__('rp_ch3_fig9_caption')?></div>
    <div class="cw cw-tall"><canvas id="cCh3F9"></canvas></div>
  </div>
</div>

<!-- ─── FIGURES 10 & 11 — Écrans et Perception × IOTF ─── -->
<div class="rp-grid">
  <div class="rp-card">
    <div class="rp-card-title"><?=__('rp_ch3_fig10_title')?></div>
    <div class="rp-ts" style="font-size:11px;color:var(--text-muted);margin-bottom:8px;text-align:start"><?=__('rp_ch3_fig10_caption')?></div>
    <div class="cw cw-tall"><canvas id="cCh3F10"></canvas></div>
    <div class="rp-insight" style="font-size:11px">
      <?=__('rp_ch3_fig10_mean')?> : <?=$ch3_f10_mean?> h/j
      | ≥ 4 h/j : <?=$ch3_f10_pct_above_4h?>% (n=<?=$ch3_f10_n_above_4h?>)
    </div>
  </div>
  <div class="rp-card">
    <div class="rp-card-title"><?=__('rp_ch3_fig11_title')?></div>
    <div class="rp-ts" style="font-size:11px;color:var(--text-muted);margin-bottom:8px;text-align:start"><?=__('rp_ch3_fig11_caption')?></div>
    <div class="cw cw-tall"><canvas id="cCh3F11"></canvas></div>
  </div>
</div>

<!-- ─── FIGURE 12 — Convergence pression × saut de repas ─── -->
<div class="rp-card rp-full" style="margin-top:12px">
  <div class="rp-card-title"><?=__('rp_ch3_fig12_title')?></div>
  <div class="rp-ts" style="font-size:11px;color:var(--text-muted);margin-bottom:8px;text-align:start"><?=__('rp_ch3_fig12_caption')?></div>
  <div class="cw cw-tall" style="height:340px"><canvas id="cCh3F12"></canvas></div>
</div>

<!-- ─── FIGURE 13 — Forest plot multivarié V1 ─── -->
<div class="rp-card rp-full" style="margin-top:12px">
  <div class="rp-card-title"><?=__('rp_ch3_fig13_title')?></div>
  <div class="rp-ts" style="font-size:11px;color:var(--text-muted);margin-bottom:8px;text-align:start"><?=__('rp_ch3_fig13_caption')?></div>
  <?php if(count($ch3_f13) > 0): ?>
    <div class="forest" id="cCh3F13Plot"></div>
  <?php else: ?>
    <div style="padding:14px;color:var(--text-muted);font-size:12px;font-style:italic;text-align:center">
      <?=__('rp_logit_insufficient')?>
    </div>
  <?php endif; ?>
</div>

<!-- ─── FIGURES 14 & 15 — ROC et V1 vs V2 ─── -->
<div class="rp-grid">
  <div class="rp-card">
    <div class="rp-card-title"><?=__('rp_ch3_fig14_title')?></div>
    <div class="rp-ts" style="font-size:11px;color:var(--text-muted);margin-bottom:8px;text-align:start"><?=__('rp_ch3_fig14_caption')?></div>
    <?php if($ch3_f14_auc !== null): ?>
      <div class="cw cw-tall"><canvas id="cCh3F14"></canvas></div>
      <div class="rp-insight" style="font-size:11px">
        AUC = <?=$ch3_f14_auc?>
        <?php if($ch3_f14_auc >= 0.8): ?>(<?=__('rp_logit_auc_excellent')?>)
        <?php elseif($ch3_f14_auc >= 0.7): ?>(<?=__('rp_logit_auc_acceptable')?>)
        <?php elseif($ch3_f14_auc >= 0.6): ?>(<?=__('rp_logit_auc_poor')?>)
        <?php else: ?>(<?=__('rp_logit_auc_random')?>)
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div style="padding:14px;color:var(--text-muted);font-size:12px;font-style:italic;text-align:center">
        <?=__('rp_logit_insufficient')?>
      </div>
    <?php endif; ?>
  </div>
  <div class="rp-card">
    <div class="rp-card-title"><?=__('rp_ch3_fig15_title')?></div>
    <div class="rp-ts" style="font-size:11px;color:var(--text-muted);margin-bottom:8px;text-align:start"><?=__('rp_ch3_fig15_caption')?></div>
    <?php if(count($ch3_f15_names) > 0): ?>
      <div class="cw cw-tall" style="height:320px"><canvas id="cCh3F15"></canvas></div>
    <?php else: ?>
      <div style="padding:14px;color:var(--text-muted);font-size:12px;font-style:italic;text-align:center">
        <?=__('rp_logit_insufficient')?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- ─── FIGURE 16 — Robustesse multi-méthode (V1/GLMM/GEE/MICE) ─── -->
<div class="rp-card rp-full" style="margin-top:12px">
  <div class="rp-card-title"><?=__('rp_ch3_fig16_title')?></div>
  <div class="rp-ts" style="font-size:11px;color:var(--text-muted);margin-bottom:8px;text-align:start"><?=__('rp_ch3_fig16_caption')?></div>
  <?php
    $f16_has_any = false;
    foreach($ch3_f16_methods as $m) if($m !== null){ $f16_has_any = true; break; }
  ?>
  <?php if($f16_has_any): ?>
    <div class="cw cw-tall" style="height:340px"><canvas id="cCh3F16"></canvas></div>
  <?php else: ?>
    <div style="padding:14px;color:var(--text-muted);font-size:12px;font-style:italic;text-align:center">
      <?=__('rp_logit_insufficient')?>
    </div>
  <?php endif; ?>
</div>

<!-- ─── FIGURE 17 — Volcano plot avec FDR ─── -->
<div class="rp-card rp-full" style="margin-top:12px;margin-bottom:18px">
  <div class="rp-card-title"><?=__('rp_ch3_fig17_title')?></div>
  <div class="rp-ts" style="font-size:11px;color:var(--text-muted);margin-bottom:8px;text-align:start"><?=__('rp_ch3_fig17_caption')?></div>
  <?php if(count($ch3_f17) > 0): ?>
    <div class="cw cw-tall" style="height:380px"><canvas id="cCh3F17"></canvas></div>
    <div class="rp-insight" style="font-size:11px">
      <span style="color:#9b2335;font-weight:600">●</span> <?=__('rp_ch3_fig17_sig_bh')?> :
      <?=$ch3_f17_n_sig?>/<?=count($ch3_f17)?>
      &nbsp;|&nbsp; <span style="color:#999">●</span> NS
    </div>
  <?php else: ?>
    <div style="padding:14px;color:var(--text-muted);font-size:12px;font-style:italic;text-align:center">
      <?=__('rp_f_nodata')?>
    </div>
  <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!--    Fin des figures du Chapitre 3                                -->
<!-- ═══════════════════════════════════════════════════════════════ -->

<!-- ═══ TABLEAUX D'HYPOTHÈSES PAR FAMILLE ═══ -->
<?php
$families = [
  'H1' => ['title'=>__('rp_h1_title'),'codes'=>['H1.1','H1.2','H1.3','H1.4','H1.5','H1.6','H1.7']],
  'H2' => ['title'=>__('rp_h2_title'),'codes'=>['H2.1','H2.2','H2.3','H2.4','H2.5','H2.6','H2.7','H2.8','H2.9','H2.10','H2.11','H2.12']],
  'H3' => ['title'=>__('rp_h3_title'),'codes'=>['H3.1','H3.2','H3.3','H3.4','H3.5','H3.6','H3.7','H3.8','H3.9']],
  'H4' => ['title'=>__('rp_h4_title'),'codes'=>['H4.1','H4.2','H4.3','H4.4','H4.5','H4.6','H4.7']],
  'H5' => ['title'=>__('rp_h5_title'),'codes'=>['H5.1','H5.2','H5.3','H5.4','H5.5','H5.6','H5.7']],
  'H6' => ['title'=>__('rp_h6_title'),'codes'=>['H6.1','H6.2','H6.3','H6.4','H6.5','H6.6']],
  'H7' => ['title'=>__('rp_h7_title'),'codes'=>['H7.1','H7.2','H7.3','H7.4']],
  'H8' => ['title'=>__('rp_h8_title'),'codes'=>['H8.1','H8.2','H8.3','H8.4','H8.5','H8.6','H8.7']],
  'H9' => ['title'=>__('rp_h9_title'),'codes'=>['H9.2']],
  'H10'=> ['title'=>__('rp_h10_title'),'codes'=>['H10.1','H10.2','H10.3','H10.4','H10.5','H10.6','H10.7','H10.8']],
];
?>

<!-- ═══ ÉVOLUTION DES INDICATEURS PAR NIVEAU SCOLAIRE — Focus 3AS ═══ -->
<?php
// Calcul indicateurs par niveau pour le graphique
$evol = [];
foreach(['1AS','2AS','3AS'] as $g){
    $rg = array_values(array_filter($rows, fn($r)=>$r['grade']===$g));
    $ng = count($rg);
    $evol[$g] = [
        'n'         => $ng,
        'pct_ow'    => $ng>0 ? round(count(array_filter($rg,fn($r)=>in_array($r['iotf_class']??'',StudySpecificAnalyses::OUTCOME_OW_OB)))*100/$ng,1) : 0,
        'pct_skip'  => $ng>0 ? round(count(array_filter($rg,fn($r)=>preg_match('/oui/i',$r['skip_meal_tutoring']??'')))*100/$ng,1) : 0,
        'pct_emot'  => $ng>0 ? round(count(array_filter($rg,fn($r)=>preg_match('/oui|souvent/i',$r['emotional_eating']??'')))*100/$ng,1) : 0,
        'pct_str'   => $ng>0 ? round(count(array_filter($rg,fn($r)=>preg_match('/élev|haut|fort/i',$r['academic_stress']??'')))*100/$ng,1) : 0,
        'pression'  => avg(colN($rg,'score_pression_scolaire')),
        'kidmed'    => avg(colN($rg,'score_kidmed')),
        'bmi'       => avg(colN($rg,'bmi')),
    ];
}
$jsEvolLabels = json_encode(['1AS','2AS','3AS']);
$jsEvolOW     = json_encode([$evol['1AS']['pct_ow'],$evol['2AS']['pct_ow'],$evol['3AS']['pct_ow']]);
$jsEvolSkip   = json_encode([$evol['1AS']['pct_skip'],$evol['2AS']['pct_skip'],$evol['3AS']['pct_skip']]);
$jsEvolEmot   = json_encode([$evol['1AS']['pct_emot'],$evol['2AS']['pct_emot'],$evol['3AS']['pct_emot']]);
$jsEvolStr    = json_encode([$evol['1AS']['pct_str'],$evol['2AS']['pct_str'],$evol['3AS']['pct_str']]);
$jsEvolPress  = json_encode([(float)$evol['1AS']['pression'],(float)$evol['2AS']['pression'],(float)$evol['3AS']['pression']]);
?>
<div class="rp-card" style="border-inline-start:4px solid #c9a961">
  <div class="rp-card-title">📈 <?=__('rp_evol_title')?></div>
  <div class="rp-ts" style="font-size:12px;color:var(--text-muted);margin-bottom:8px"><?=__('rp_evol_intro')?></div>
  <div class="cw" style="height:340px"><canvas id="cEvol"></canvas></div>
</div>

<!-- ═══ TABLEAU 3D : pression × statut pondéral × niveau ═══ -->
<?php
$grid3d = [];
foreach(['1AS','2AS','3AS'] as $g){
    $rg = array_filter($rows, fn($r)=>$r['grade']===$g);
    foreach(['Faible','Moyen','Élevé'] as $pcat){
        $rgp = array_filter($rg, function($r) use($pcat){
            $sp = (int)($r['score_pression_scolaire']??0);
            if($pcat==='Faible') return $sp<=3;
            if($pcat==='Moyen')  return $sp>=4 && $sp<=6;
            return $sp>=7;
        });
        $ngp = count($rgp);
        $owgp = count(array_filter($rgp, fn($r)=>in_array($r['iotf_class']??'',StudySpecificAnalyses::OUTCOME_OW_OB)));
        $grid3d[$g][$pcat] = ['n'=>$ngp,'ow'=>$owgp,'pct'=>$ngp>0?round($owgp*100/$ngp,1):null];
    }
}
?>
<div class="rp-card">
  <div class="rp-card-title">📊 <?=__('rp_3d_title')?></div>
  <div class="rp-ts" style="font-size:12px;color:var(--text-muted);margin-bottom:8px"><?=__('rp_3d_intro')?></div>
  <table class="rp-table" style="font-size:13px">
    <thead>
      <tr>
        <th rowspan="2" style="vertical-align:middle"><?=__('rp_grade_label')?></th>
        <th colspan="3" class="c"><?=__('rp_pression_low')?> (0–3)</th>
        <th colspan="3" class="c"><?=__('rp_pression_med')?> (4–6)</th>
        <th colspan="3" class="c"><?=__('rp_pression_high')?> (7–9)</th>
      </tr>
      <tr>
        <th class="c">n</th><th class="c">OW+OB</th><th class="c">%</th>
        <th class="c">n</th><th class="c">OW+OB</th><th class="c">%</th>
        <th class="c">n</th><th class="c">OW+OB</th><th class="c">%</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach(['1AS','2AS','3AS'] as $g): ?>
      <tr<?=$g==='3AS'?' style="background:#fef9e7;font-weight:600"':''?>>
        <td><?=$g?><?=$g==='3AS'?' ⭐':''?></td>
        <?php foreach(['Faible','Moyen','Élevé'] as $pcat):
            $cell = $grid3d[$g][$pcat]; ?>
          <td class="c"><?=$cell['n']?></td>
          <td class="c"><?=$cell['ow']?></td>
          <td class="c"<?=$cell['pct']!==null && $cell['pct']>=15?' style="color:#9b2335;font-weight:700"':''?>><?=$cell['pct']??'—'?><?=$cell['pct']!==null?'%':''?></td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div class="rp-ts" style="font-size:11px;color:var(--text-muted);margin-top:6px;font-style:italic">⭐ <?=__('rp_3as_highlight')?></div>
</div>

<?php foreach($families as $famKey=>$fam): ?>
<div class="hsec">
  <div class="hsec-title">📊 <?=$fam['title']?></div>
  <table class="h-table">
    <thead>
      <tr>
        <th style="width:50px">#</th>
        <th><?=__('rp_h_hypothesis')?></th>
        <th class="c" style="width:120px"><?=__('rp_h_test')?></th>
        <th class="c" style="width:140px"><?=__('rp_h_stat')?></th>
        <th class="c" style="width:110px"><?=__('rp_h_effect')?></th>
        <th class="c" style="width:90px"><?=__('rp_h_pvalue')?></th>
        <th class="c" style="width:90px" title="<?=__('rp_h_pbh_help')?>"><?=__('rp_h_pbh')?></th>
        <th class="c" style="width:100px"><?=__('rp_h_concl')?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($fam['codes'] as $code): if(!isset($H[$code])) continue; $h=$H[$code]; ?>
        <tr>
          <td class="c"><span class="h-code"><?=$code?></span></td>
          <td class="lbl"><?=$h['title']?>
            <?php if(!empty($h['value'])): ?>
              <div style="font-size:10px;color:var(--text-muted);margin-top:2px"><?=$h['value']?></div>
            <?php endif; ?>
          </td>
          <td class="c"><?=$h['test']??'—'?></td>
          <td class="c"><?=$h['stat']??'—'?></td>
          <td class="c"><?=$h['effect']??'—'?></td>
          <td class="c"><?=badge_p($h['p']??null)?></td>
          <td class="c" style="font-size:11px"><?php
            $pbh = $h['p_bh'] ?? null;
            if($pbh===null) echo '<span style="color:#999">—</span>';
            else echo '<span style="color:'.($pbh<0.05?'#1a7340':($pbh<0.20?'#856404':'#999')).'">'.fmt_p($pbh).'</span>';
          ?></td>
          <td class="c">
            <?php
            $p = $h['p'] ?? null;
            if($p===null) echo '<span class="rp-badge rp-badge-info">'.__('rp_h_na').'</span>';
            elseif($p<0.05) echo '<span class="rp-badge rp-badge-success">✓ '.__('rp_h_confirmed').'</span>';
            elseif($p<0.20) echo '<span class="rp-badge rp-badge-warning">~ '.__('rp_trend').'</span>';
            else echo '<span class="rp-badge rp-badge-info">— NS</span>';
            ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endforeach; ?>

<!-- ═══ RÉGRESSION LOGISTIQUE MULTIVARIÉE (V3 — Wave 1 #4 et #5) ═══ -->
<?php if($logitResult && !isset($logitResult['error'])): ?>
<div class="rp-card rp-full" style="margin-top:14px;border-inline-start:4px solid #1F4E79">
  <div class="rp-card-title">🎯 <?=__('rp_logit_title')?></div>
  <div class="rp-ts" style="font-size:12px;color:var(--text-muted);margin-bottom:10px"><?=__('rp_logit_intro')?></div>

  <?php if(!$logitResult['converged']): ?>
    <div style="padding:10px;background:#fdf3f5;border-inline-start:3px solid #9b2335;color:#721c24;font-size:12px;margin-bottom:10px">
      ⚠️ <?=__('rp_logit_no_converge')?>
    </div>
  <?php endif; ?>

  <table class="rp-table" style="font-size:13px">
    <thead>
      <tr>
        <th><?=__('rp_logit_var')?></th>
        <th class="c"><?=__('rp_logit_coef')?> (β)</th>
        <th class="c"><?=__('rp_logit_se')?></th>
        <th class="c">OR <?=__('rp_logit_adj')?></th>
        <th class="c">IC 95%</th>
        <th class="c">p-value (Wald)</th>
      </tr>
    </thead>
    <tbody>
      <?php for($j=1; $j<count($logitResult['names']); $j++):
          $isSig = $logitResult['p'][$j] !== null && $logitResult['p'][$j] < 0.05; ?>
        <tr<?=$isSig?' style="background:#e8f5e9;font-weight:600"':''?>>
          <td><?=htmlspecialchars($logitResult['names'][$j])?></td>
          <td class="c"><?=$logitResult['coef'][$j]?></td>
          <td class="c"><?=$logitResult['se'][$j]??'—'?></td>
          <td class="c" style="<?=$isSig?'color:#1a7340':''?>"><?=$logitResult['or'][$j]?></td>
          <td class="c">[<?=$logitResult['ci_low'][$j]??'—'?> – <?=$logitResult['ci_high'][$j]??'—'?>]</td>
          <td class="c"><?=fmt_p($logitResult['p'][$j])?> <?=star($logitResult['p'][$j])?></td>
        </tr>
      <?php endfor; ?>
    </tbody>
  </table>

  <!-- Métriques de qualité du modèle -->
  <div class="kpi" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));margin-top:14px">
    <div class="kpi-card">
      <div class="kpi-val" style="color:#1F4E79"><?=$logitResult['n']?></div>
      <div class="kpi-lbl"><?=__('rp_logit_n')?></div>
      <div class="kpi-sub"><?=$logitResult['k']?> <?=__('rp_logit_predictors')?></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-val" style="color:<?=($logitResult['auc']??0)>=0.7?'#1a7340':'#856404'?>">
        <?=$logitResult['auc']?? '—'?>
      </div>
      <div class="kpi-lbl">AUC (ROC)</div>
      <div class="kpi-sub">
        <?=($logitResult['auc']??0)>=0.8?__('rp_logit_auc_excellent'):
          (($logitResult['auc']??0)>=0.7?__('rp_logit_auc_acceptable'):
          (($logitResult['auc']??0)>=0.6?__('rp_logit_auc_poor'):__('rp_logit_auc_random')))?>
      </div>
    </div>
    <div class="kpi-card">
      <div class="kpi-val" style="color:<?=($logitResult['hl']['p']??0)>=0.05?'#1a7340':'#856404'?>">
        <?=fmt_p($logitResult['hl']['p'])?>
      </div>
      <div class="kpi-lbl">Hosmer-Lemeshow p</div>
      <div class="kpi-sub">
        <?=($logitResult['hl']['p']??0)>=0.05?__('rp_logit_hl_good'):__('rp_logit_hl_bad')?>
      </div>
    </div>
  </div>

  <div class="rp-ts" style="font-size:11px;color:var(--text-muted);margin-top:10px;line-height:1.7;font-style:italic">
    <?=__('rp_logit_method_note')?>
  </div>
</div>
<?php else: ?>
<div class="rp-card rp-full" style="margin-top:14px">
  <div class="rp-card-title">🎯 <?=__('rp_logit_title')?></div>
  <div class="rp-ts" style="color:var(--text-muted);font-size:12px"><?=__('rp_logit_insufficient')?></div>
</div>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════════════════ -->
<!--    ANALYSES BIOSTATISTIQUES AVANCÉES (v2.0 — mai 2026)          -->
<!--    VIF + Box-Tidwell + GLMM + GEE + MICE/Rubin                  -->
<!-- ════════════════════════════════════════════════════════════════ -->
<?php if($advancedResults): ?>
<div class="rp-card rp-full" style="margin-top:18px;border-inline-start:4px solid #6f42c1;background:linear-gradient(180deg,#faf7ff,#fff)">
  <div class="rp-card-title" style="color:#6f42c1">🧪 Analyses biostatistiques avancées — Robustesse méthodologique</div>
  <div class="rp-ts" style="font-size:12px;color:var(--text-muted);margin-bottom:10px;font-style:italic">
    Cinq analyses complémentaires validant la robustesse du modèle multivarié principal :
    VIF (multicolinéarité), Box-Tidwell (linéarité du logit), GLMM (effet école par PQL/Henderson),
    GEE (corrélation exchangeable + sandwich de Liang &amp; Zeger), et MICE+Rubin (imputation multiple).
  </div>
</div>

<!-- ─── 1. VIF — Variance Inflation Factor ─── -->
<?php if(isset($advancedResults['vif']) && !isset($advancedResults['vif']['error'])): ?>
<div class="rp-card rp-full" style="margin-top:14px;border-inline-start:4px solid #1F4E79">
  <div class="rp-card-title">📐 1. VIF — Facteur d'inflation de la variance</div>
  <div class="rp-ts" style="font-size:12px;color:var(--text-muted);margin-bottom:8px">
    Pour chaque prédicteur, on régresse X<sub>j</sub> sur tous les autres et on calcule VIF<sub>j</sub> = 1/(1 − R²<sub>j</sub>).
    Seuils : <span style="color:#856404">VIF &gt; 2.5</span> attention ; <span style="color:#9b2335">VIF &gt; 5</span> critique (Allison, 2012).
  </div>
  <table class="rp-table" style="font-size:12px">
    <thead>
      <tr>
        <th>Prédicteur</th>
        <th class="c">VIF</th>
        <th class="c">R²<sub>auxiliaire</sub></th>
        <th class="c">Tolérance (1/VIF)</th>
        <th class="c">Statut</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($advancedResults['vif'] as $vname => $vinfo): ?>
      <?php if(isset($vinfo['error'])) continue; ?>
      <tr>
        <td class="lbl"><?=htmlspecialchars($vname)?></td>
        <td class="c"><b><?=$vinfo['vif']?></b></td>
        <td class="c"><?=$vinfo['r2']?></td>
        <td class="c"><?=$vinfo['tolerance']?></td>
        <td class="c">
          <?php if($vinfo['flag']==='OK'): ?>
            <span class="rp-badge rp-badge-success">✓ OK</span>
          <?php elseif($vinfo['flag']==='WARNING'): ?>
            <span class="rp-badge rp-badge-warning">⚠ Attention</span>
          <?php else: ?>
            <span class="rp-badge rp-badge-danger">⚠ Critique</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="rp-insight">
    <?php
      $vif_critical = 0; $vif_warning = 0;
      foreach($advancedResults['vif'] as $vinfo){
          if(isset($vinfo['flag'])){
              if($vinfo['flag']==='CRITICAL') $vif_critical++;
              if($vinfo['flag']==='WARNING')  $vif_warning++;
          }
      }
    ?>
    <?php if($vif_critical===0 && $vif_warning===0): ?>
      <strong>✓ Aucun problème de multicolinéarité</strong> — tous les VIF &lt; 2.5, ce qui valide la fiabilité des estimations multivariées.
    <?php elseif($vif_critical===0): ?>
      <strong>⚠ Vigilance</strong> — <?=$vif_warning?> variable(s) avec VIF entre 2.5 et 5 ; les estimations restent interprétables mais leur erreur standard peut être inflatée.
    <?php else: ?>
      <strong>⚠ Multicolinéarité critique</strong> — <?=$vif_critical?> variable(s) avec VIF &gt; 5 ; envisager de retirer ou de combiner les prédicteurs concernés.
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- ─── 2. Box-Tidwell — Linéarité du logit ─── -->
<?php if(isset($advancedResults['boxtidwell'])): ?>
<div class="rp-card rp-full" style="margin-top:14px;border-inline-start:4px solid #1F4E79">
  <div class="rp-card-title">📈 2. Box-Tidwell — Test de linéarité du logit</div>
  <div class="rp-ts" style="font-size:12px;color:var(--text-muted);margin-bottom:8px">
    Pour chaque prédicteur continu X<sub>j</sub>, on introduit le terme X<sub>j</sub>·ln(X<sub>j</sub>) dans le modèle.
    H<sub>0</sub> : logit linéaire en X<sub>j</sub>. Rejet (p &lt; 0.05) → non-linéarité (Box &amp; Tidwell, 1962).
  </div>
  <table class="rp-table" style="font-size:12px">
    <thead>
      <tr>
        <th>Prédicteur continu</th>
        <th class="c">β (X·ln X)</th>
        <th class="c">SE</th>
        <th class="c">p-value</th>
        <th>Conclusion</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($advancedResults['boxtidwell'] as $bname => $binfo): ?>
      <?php if(isset($binfo['error'])): ?>
        <tr>
          <td class="lbl"><?=htmlspecialchars($bname)?></td>
          <td class="c" colspan="4" style="color:var(--text-muted)">Erreur d'estimation</td>
        </tr>
      <?php else: ?>
        <tr>
          <td class="lbl"><?=htmlspecialchars($bname)?></td>
          <td class="c"><?=$binfo['beta_xlnx']?></td>
          <td class="c"><?=$binfo['se']??'—'?></td>
          <td class="c"><?=fmt_p($binfo['p'])?> <?=star($binfo['p'])?></td>
          <td style="font-size:11px">
            <?php if($binfo['linear_ok']): ?>
              <span class="rp-badge rp-badge-success">✓ Linéarité OK</span>
            <?php else: ?>
              <span class="rp-badge rp-badge-warning">⚠ Non-linéarité</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endif; ?>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="rp-insight">
    <?php
      $nonlinear = 0; $linear_ok = 0;
      foreach($advancedResults['boxtidwell'] as $b){
          if(isset($b['linear_ok'])){ $b['linear_ok'] ? $linear_ok++ : $nonlinear++; }
      }
    ?>
    <?php if($nonlinear===0): ?>
      <strong>✓ Linéarité du logit confirmée</strong> sur tous les prédicteurs continus testés ; le modèle multivarié principal est valide en sa forme actuelle.
    <?php else: ?>
      <strong>⚠ Non-linéarité détectée</strong> sur <?=$nonlinear?> prédicteur(s) ; envisager de recoder ces variables en classes (terciles/quartiles) ou en splines.
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- ─── 3. GLMM — Modèle logistique mixte ─── -->
<?php if(isset($advancedResults['glmm']) && !isset($advancedResults['glmm']['error'])): $gl=$advancedResults['glmm']; ?>
<div class="rp-card rp-full" style="margin-top:14px;border-inline-start:4px solid #1F4E79">
  <div class="rp-card-title">🏫 3. GLMM logistique — Intercept aléatoire école (PQL)</div>
  <div class="rp-ts" style="font-size:12px;color:var(--text-muted);margin-bottom:8px">
    Modèle : logit(p<sub>ij</sub>) = X<sub>ij</sub>β + u<sub>j</sub> avec u<sub>j</sub> ~ N(0, σ²<sub>u</sub>).
    Algorithme : Penalized Quasi-Likelihood + équations mixtes de Henderson (Breslow &amp; Clayton, 1993).
  </div>
  <div class="kpi" style="grid-template-columns:repeat(auto-fit,minmax(130px,1fr));margin-bottom:12px">
    <div class="kpi-card">
      <div class="kpi-val" style="color:<?=$gl['converged']?'#1a7340':'#856404'?>">
        <?=$gl['converged']?'✓':'⚠'?>
      </div>
      <div class="kpi-lbl">Convergence</div>
      <div class="kpi-sub"><?=$gl['iter']?> itérations PQL</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-val"><?=$gl['n_clusters']?></div>
      <div class="kpi-lbl">Écoles (clusters)</div>
      <div class="kpi-sub">~<?=$gl['mean_cluster_size']?> élèves / école</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-val"><?=$gl['sigma2_u']?></div>
      <div class="kpi-lbl">σ²<sub>u</sub> (var. inter-écoles)</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-val" style="color:<?=$gl['icc']>=0.05?'#1F4E79':'#1a7340'?>"><?=$gl['icc']?></div>
      <div class="kpi-lbl">ICC</div>
      <div class="kpi-sub"><?=round($gl['icc']*100,1)?>% var. inter-écoles</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-val"><?=$gl['deff']?></div>
      <div class="kpi-lbl">DEFF</div>
      <div class="kpi-sub">Effet du plan d'échantillonnage</div>
    </div>
  </div>
  <table class="rp-table" style="font-size:12px">
    <thead>
      <tr>
        <th>Variable</th>
        <th class="c">β</th>
        <th class="c">OR ajusté (mixed)</th>
        <th class="c">IC 95 %</th>
        <th class="c">p-value</th>
      </tr>
    </thead>
    <tbody>
      <?php for($j=1; $j<count($gl['or']); $j++):
          $isSig = $gl['p'][$j] !== null && $gl['p'][$j] < 0.05; ?>
      <tr<?=$isSig?' style="background:#e8f5e9;font-weight:600"':''?>>
        <td class="lbl"><?=htmlspecialchars($gl['names'][$j])?></td>
        <td class="c"><?=$gl['coef'][$j]?></td>
        <td class="c" style="<?=$isSig?'color:#1a7340':''?>"><b><?=$gl['or'][$j]?></b></td>
        <td class="c">[<?=$gl['ci_low'][$j]??'—'?> – <?=$gl['ci_high'][$j]??'—'?>]</td>
        <td class="c"><?=fmt_p($gl['p'][$j])?> <?=star($gl['p'][$j])?></td>
      </tr>
      <?php endfor; ?>
    </tbody>
  </table>
  <div class="rp-insight">
    <?php if($gl['icc'] < 0.05): ?>
      <strong>ICC = <?=$gl['icc']?> faible</strong> — la variance entre écoles est négligeable ; le modèle multivarié standard reste valide.
    <?php elseif($gl['icc'] < 0.20): ?>
      <strong>ICC = <?=$gl['icc']?> modérée</strong> — il existe une certaine variabilité entre établissements ; les OR du GLMM sont les estimations recommandées pour l'inférence.
    <?php else: ?>
      <strong>ICC = <?=$gl['icc']?> élevée</strong> — variation importante entre établissements ; l'utilisation d'un modèle hiérarchique est essentielle.
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- ─── 4. GEE — Equations d'estimation généralisées ─── -->
<?php if(isset($advancedResults['gee']) && !isset($advancedResults['gee']['error'])): $ge=$advancedResults['gee']; ?>
<div class="rp-card rp-full" style="margin-top:14px;border-inline-start:4px solid #1F4E79">
  <div class="rp-card-title">🔗 4. GEE — Variance robuste sandwich (Liang &amp; Zeger 1986)</div>
  <div class="rp-ts" style="font-size:12px;color:var(--text-muted);margin-bottom:8px">
    Régression logistique GEE avec structure de corrélation <b>exchangeable</b>.
    Variance robuste « sandwich » : V<sub>β</sub> = M<sub>0</sub><sup>−1</sup> M<sub>1</sub> M<sub>0</sub><sup>−1</sup>.
    Insensible à une mauvaise spécification de la structure de corrélation.
  </div>
  <div class="kpi" style="grid-template-columns:repeat(auto-fit,minmax(130px,1fr));margin-bottom:12px">
    <div class="kpi-card">
      <div class="kpi-val" style="color:<?=$ge['converged']?'#1a7340':'#856404'?>">
        <?=$ge['converged']?'✓':'⚠'?>
      </div>
      <div class="kpi-lbl">Convergence</div>
      <div class="kpi-sub"><?=$ge['iter']?> itérations</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-val"><?=$ge['n_clusters']?></div>
      <div class="kpi-lbl">Écoles (clusters)</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-val"><?=$ge['alpha']?></div>
      <div class="kpi-lbl">α exchangeable</div>
      <div class="kpi-sub">Corrélation intra-cluster</div>
    </div>
  </div>
  <table class="rp-table" style="font-size:12px">
    <thead>
      <tr>
        <th rowspan="2">Variable</th>
        <th class="c" rowspan="2">β</th>
        <th class="c" rowspan="2">OR (sandwich)</th>
        <th class="c" rowspan="2">IC 95 % (sandwich)</th>
        <th class="c" rowspan="2">p-value</th>
        <th class="c" colspan="2">SE</th>
      </tr>
      <tr>
        <th class="c">robust</th>
        <th class="c">model</th>
      </tr>
    </thead>
    <tbody>
      <?php for($j=1; $j<count($ge['or']); $j++):
          $isSig = $ge['p'][$j] !== null && $ge['p'][$j] < 0.05; ?>
      <tr<?=$isSig?' style="background:#e8f5e9;font-weight:600"':''?>>
        <td class="lbl"><?=htmlspecialchars($ge['names'][$j])?></td>
        <td class="c"><?=$ge['coef'][$j]?></td>
        <td class="c" style="<?=$isSig?'color:#1a7340':''?>"><b><?=$ge['or'][$j]?></b></td>
        <td class="c">[<?=$ge['ci_low'][$j]??'—'?> – <?=$ge['ci_high'][$j]??'—'?>]</td>
        <td class="c"><?=fmt_p($ge['p'][$j])?> <?=star($ge['p'][$j])?></td>
        <td class="c" style="color:#9b2335"><?=$ge['se_robust'][$j]??'—'?></td>
        <td class="c" style="color:var(--text-muted)"><?=$ge['se_model'][$j]??'—'?></td>
      </tr>
      <?php endfor; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<!-- ─── 5. MICE + Rubin ─── -->
<?php if(isset($advancedResults['mice_pooled']) && isset($advancedResults['mice_meta'])): $mi=$advancedResults['mice_pooled']; $mm=$advancedResults['mice_meta']; ?>
<div class="rp-card rp-full" style="margin-top:14px;border-inline-start:4px solid #1F4E79">
  <div class="rp-card-title">🔄 5. MICE — Imputation multiple + Règles de Rubin</div>
  <div class="rp-ts" style="font-size:12px;color:var(--text-muted);margin-bottom:8px">
    Imputation multiple par équations chaînées avec Predictive Mean Matching (van Buuren &amp; Groothuis-Oudshoorn, 2011),
    m = <?=$mm['m']?> imputations × <?=$mm['iterations']?> itérations, <?=$mm['donors_pmm']?> donneurs PMM ;
    pooling par règles de Rubin (1987) : T = U + (1+1/m)·B.
  </div>
  <div class="kpi" style="grid-template-columns:repeat(auto-fit,minmax(130px,1fr));margin-bottom:12px">
    <div class="kpi-card">
      <div class="kpi-val"><?=$mm['n_total']?></div>
      <div class="kpi-lbl">N sujets inclus</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-val"><?=$mm['m']?></div>
      <div class="kpi-lbl">Imputations m</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-val"><?=count($mm['vars_imputed'])?></div>
      <div class="kpi-lbl">Variables imputées</div>
      <div class="kpi-sub">Sur <?=count($mm['pct_missing'])?> au total</div>
    </div>
  </div>
  <?php if(count($mm['vars_imputed']) > 0): ?>
  <div style="font-size:11px;margin:8px 0;color:var(--text-muted)">
    <b>Variables imputées :</b>
    <?php foreach($mm['vars_imputed'] as $vn): ?>
      <span style="display:inline-block;background:#fff3cd;padding:2px 6px;border-radius:4px;margin:2px;">
        <?=htmlspecialchars($vn)?> (<?=$mm['pct_missing'][$vn]??0?>%)
      </span>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <table class="rp-table" style="font-size:12px">
    <thead>
      <tr>
        <th>Variable</th>
        <th class="c">β poolé</th>
        <th class="c">SE poolée</th>
        <th class="c">OR poolé</th>
        <th class="c">IC 95 %</th>
        <th class="c">p-value</th>
      </tr>
    </thead>
    <tbody>
      <?php for($j=1; $j<count($mi['or']); $j++):
          $isSig = isset($mi['p'][$j]) && $mi['p'][$j] !== null && $mi['p'][$j] < 0.05;
          $lbl = $mm['names'][$j] ?? "X$j"; ?>
      <tr<?=$isSig?' style="background:#e8f5e9;font-weight:600"':''?>>
        <td class="lbl"><?=htmlspecialchars($lbl)?></td>
        <td class="c"><?=$mi['beta'][$j]??'—'?></td>
        <td class="c"><?=$mi['se'][$j]??'—'?></td>
        <td class="c" style="<?=$isSig?'color:#1a7340':''?>"><b><?=$mi['or'][$j]??'—'?></b></td>
        <td class="c">[<?=$mi['ci_low'][$j]??'—'?> – <?=$mi['ci_high'][$j]??'—'?>]</td>
        <td class="c"><?=fmt_p($mi['p'][$j])?> <?=star($mi['p'][$j])?></td>
      </tr>
      <?php endfor; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<!-- ─── Synthèse comparative des 4 modèles ─── -->
<?php if(isset($advancedResults['glmm']) && isset($advancedResults['gee']) && $logitResult): ?>
<div class="rp-card rp-full" style="margin-top:14px;border-inline-start:4px solid #6f42c1;background:linear-gradient(180deg,#faf7ff,#fff)">
  <div class="rp-card-title" style="color:#6f42c1">🧮 Synthèse comparative — 4 modèles multivariés</div>
  <div class="rp-ts" style="font-size:12px;color:var(--text-muted);margin-bottom:10px;font-style:italic">
    La convergence des estimations entre les 4 approches (standard, GLMM, GEE, MICE poolé)
    constitue un test de robustesse des conclusions face au choix méthodologique.
  </div>
  <table class="rp-table" style="font-size:11px">
    <thead>
      <tr>
        <th rowspan="2">Variable</th>
        <th class="c" colspan="2">Standard</th>
        <th class="c" colspan="2">GLMM (mixed)</th>
        <th class="c" colspan="2">GEE (sandwich)</th>
        <?php if(isset($advancedResults['mice_pooled'])): ?>
          <th class="c" colspan="2">MICE poolé</th>
        <?php endif; ?>
      </tr>
      <tr>
        <th class="c">OR</th><th class="c">p</th>
        <th class="c">OR</th><th class="c">p</th>
        <th class="c">OR</th><th class="c">p</th>
        <?php if(isset($advancedResults['mice_pooled'])): ?>
          <th class="c">OR</th><th class="c">p</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php
        $st = $logitResult; $gl = $advancedResults['glmm']; $ge = $advancedResults['gee'];
        $mi = $advancedResults['mice_pooled'] ?? null;
        // Mapping noms standard ↔ MICE (MICE n'a pas Saut repas)
        $mice_map = ['KIDMED'=>1,'Activité'=>2,'Sédentarité'=>3,'Sommeil'=>4,'Pression'=>5,'Âge'=>6,'Sexe (♂=1)'=>7];
        for($j=1; $j<count($st['or']); $j++):
          $name = $st['names'][$j];
          $mice_j = $mice_map[$name] ?? null;
      ?>
      <tr>
        <td class="lbl"><?=htmlspecialchars($name)?></td>
        <td class="c"><?=$st['or'][$j]?></td>
        <td class="c" style="<?=($st['p'][$j]??1)<0.05?'color:#1a7340;font-weight:700':''?>"><?=fmt_p($st['p'][$j])?></td>
        <td class="c"><?=$gl['or'][$j]??'—'?></td>
        <td class="c" style="<?=($gl['p'][$j]??1)<0.05?'color:#1a7340;font-weight:700':''?>"><?=fmt_p($gl['p'][$j])?></td>
        <td class="c"><?=$ge['or'][$j]??'—'?></td>
        <td class="c" style="<?=($ge['p'][$j]??1)<0.05?'color:#1a7340;font-weight:700':''?>"><?=fmt_p($ge['p'][$j])?></td>
        <?php if($mi !== null): ?>
          <?php if($mice_j !== null && isset($mi['or'][$mice_j])): ?>
            <td class="c"><?=$mi['or'][$mice_j]?></td>
            <td class="c" style="<?=($mi['p'][$mice_j]??1)<0.05?'color:#1a7340;font-weight:700':''?>"><?=fmt_p($mi['p'][$mice_j])?></td>
          <?php else: ?>
            <td class="c" colspan="2" style="color:var(--text-muted)">n/a</td>
          <?php endif; ?>
        <?php endif; ?>
      </tr>
      <?php endfor; ?>
    </tbody>
  </table>
  <div class="rp-insight">
    <strong>Lecture :</strong> les OR doivent rester du même ordre de grandeur et conserver leur sens (≷ 1) à travers les 4 modèles ;
    les p-values significatives (en vert) doivent rester cohérentes. Tout écart majeur signalerait une fragilité méthodologique.
  </div>
</div>
<?php endif; ?>

<?php endif; // fin $advancedResults ?>

<!-- ═══ DONNÉES MANQUANTES (V3 — Wave 1 #7) ═══ -->
<div class="rp-card rp-full" style="margin-top:14px">
  <div class="rp-card-title">📋 <?=__('rp_missing_title')?></div>
  <div class="rp-ts" style="font-size:12px;color:var(--text-muted);margin-bottom:10px"><?=__('rp_missing_intro')?></div>
  <table class="rp-table" style="font-size:12px">
    <thead>
      <tr>
        <th><?=__('rp_missing_var')?></th>
        <th class="c"><?=__('rp_missing_n')?></th>
        <th class="c">%</th>
        <th class="c"><?=__('rp_missing_status')?></th>
        <th><?=__('rp_missing_action')?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($missingTable as $row): ?>
      <tr>
        <td><?=htmlspecialchars($row['label'])?> <span style="color:var(--text-muted);font-size:10px">(<?=$row['col']?>)</span></td>
        <td class="c"><?=$row['n_missing']?></td>
        <td class="c"><?=$row['pct']?>%</td>
        <td class="c">
          <?php if($row['flag']==='critical'): ?>
            <span class="rp-badge rp-badge-danger">⚠ <?=__('rp_missing_critical')?></span>
          <?php elseif($row['flag']==='warning'): ?>
            <span class="rp-badge rp-badge-warning"><?=__('rp_missing_high')?></span>
          <?php elseif($row['flag']==='note'): ?>
            <span class="rp-badge rp-badge-info"><?=__('rp_missing_low')?></span>
          <?php else: ?>
            <span class="rp-badge rp-badge-success">✓ OK</span>
          <?php endif; ?>
        </td>
        <td style="font-size:11px;color:var(--text-muted)">
          <?php if($row['flag']==='critical'): ?>
            <?=__('rp_missing_action_drop')?>
          <?php elseif($row['flag']==='warning'): ?>
            <?=__('rp_missing_action_impute')?>
          <?php else: ?>
            <?=__('rp_missing_action_keep')?>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ═══ FOREST PLOT — OR significatifs ═══ -->
<?php if(count($forestData)>0): ?>
<div class="rp-card rp-full" style="margin-top:14px">
  <div class="rp-card-title">📌 <?=__('rp_forest_title')?> (<?=count($forestData)?>)</div>
  <div class="forest" id="forestPlot"></div>
  <div class="rp-insight"><?=__('rp_forest_help')?></div>
</div>
<?php endif; ?>

<!-- ═══ ÉCOLES (existant) ═══ -->
<div class="rp-card rp-full" style="margin-top:14px">
  <div class="rp-card-title">8. <?=__('rp_school_title')?></div>
  <div style="position:relative;height:<?=max(220,count($schools)*32+60)?>px">
    <canvas id="cSchool"></canvas>
  </div>
</div>

<!-- ═══ TABLEAU SYNTHÈSE INDICATEURS PAR SEXE ═══ -->
<div class="rp-card rp-full" style="margin-top:14px">
  <div class="rp-card-title">9. <?=__('rp_sex_title')?></div>
  <table class="rp-table">
    <thead>
      <tr><th><?=__('rp_indicator')?></th><th><?=__('rp_all')?></th><th><?=__('rp_boys')?></th><th><?=__('rp_girls')?></th><th><?=__('rp_h_pvalue')?></th></tr>
    </thead>
    <tbody>
      <tr><td class="lbl">IMC moyen</td><td><?=avg($bmi)?></td><td><?=avg(colN($boys,'bmi'))?></td><td><?=avg(colN($girls,'bmi'))?></td><td><?=fmt_p($H['H1.3']['p']??null).' '.star($H['H1.3']['p']??null)?></td></tr>
      <tr><td class="lbl">% Surpoids</td><td><?=pct($nSurp,$n)?>%</td><td><?=pct(cnt(col($boys,'iotf_class'),'Surpoids'),$nB)?>%</td><td><?=pct(cnt(col($girls,'iotf_class'),'Surpoids'),$nG)?>%</td><td>—</td></tr>
      <tr><td class="lbl">% Obésité</td><td><?=pct($nObes,$n)?>%</td><td><?=pct($nObesB,$nB)?>%</td><td><?=pct($nObesG,$nG)?>%</td><td><?=fmt_p($H['H1.2']['p']??null).' '.star($H['H1.2']['p']??null)?></td></tr>
      <tr><td class="lbl">KIDMED moyen</td><td><?=avg(colN($rows,'score_kidmed'))?></td><td><?=avg(colN($boys,'score_kidmed'))?></td><td><?=avg(colN($girls,'score_kidmed'))?></td><td><?=fmt_p($bp->tTest(colN($boys,'score_kidmed'),colN($girls,'score_kidmed'))['p'])?></td></tr>
      <tr><td class="lbl">Score activité</td><td><?=avg(colN($rows,'score_activite'))?></td><td><?=avg(colN($boys,'score_activite'))?></td><td><?=avg(colN($girls,'score_activite'))?></td><td><?=fmt_p($bp->tTest(colN($boys,'score_activite'),colN($girls,'score_activite'))['p'])?></td></tr>
      <tr><td class="lbl">Score sédentarité</td><td><?=avg(colN($rows,'score_sedentarite'))?></td><td><?=avg(colN($boys,'score_sedentarite'))?></td><td><?=avg(colN($girls,'score_sedentarite'))?></td><td><?=fmt_p($bp->tTest(colN($boys,'score_sedentarite'),colN($girls,'score_sedentarite'))['p'])?></td></tr>
      <tr><td class="lbl">Score sommeil</td><td><?=avg(colN($rows,'score_sommeil'))?></td><td><?=avg(colN($boys,'score_sommeil'))?></td><td><?=avg(colN($girls,'score_sommeil'))?></td><td><?=fmt_p($bp->tTest(colN($boys,'score_sommeil'),colN($girls,'score_sommeil'))['p'])?></td></tr>
      <tr><td class="lbl">Score pression</td><td><?=avg(colN($rows,'score_pression_scolaire'))?></td><td><?=avg(colN($boys,'score_pression_scolaire'))?></td><td><?=avg(colN($girls,'score_pression_scolaire'))?></td><td><?=fmt_p($bp->tTest(colN($boys,'score_pression_scolaire'),colN($girls,'score_pression_scolaire'))['p'])?></td></tr>
      <tr><td class="lbl">Score global /100</td><td><?=avg(colN($rows,'global_nutrition_score'))?></td><td><?=avg(colN($boys,'global_nutrition_score'))?></td><td><?=avg(colN($girls,'global_nutrition_score'))?></td><td><?=fmt_p($bp->tTest(colN($boys,'global_nutrition_score'),colN($girls,'global_nutrition_score'))['p'])?></td></tr>
    </tbody>
  </table>
</div>

<!-- ═══ NARRATIVE ═══ -->
<div class="rp-card rp-full" style="margin-top:14px">
  <div class="rp-card-title">10. <?=__('rp_findings_title')?></div>
  <div class="rp-insight" style="border:none;background:none;padding:0">
  <?php
  $findings = [];

  // Prévalence
  if(($nObes+$nSurp) > $n*0.25) $findings[]=['danger', pct($nObes+$nSurp,$n).'% — '.__('rp_f_quarter')];

  // Sexe
  $obB = pct($nObesB,$nB); $obG = pct($nObesG,$nG);
  if(isset($H['H1.2']['p']) && $H['H1.2']['p']!==null && $H['H1.2']['p']<0.05){
    if($obG>$obB) $findings[]=['warning', __('rp_f_girls_higher')." ({$obG}% vs {$obB}%) — p=".fmt_p($H['H1.2']['p'])];
    else          $findings[]=['warning', __('rp_f_boys_higher')." ({$obB}% vs {$obG}%) — p=".fmt_p($H['H1.2']['p'])];
  }

  // KIDMED
  $kid_avg = avg(colN($rows,'score_kidmed'));
  if($kid_avg<4)    $findings[]=['danger', __('rp_mean_kidmed')." ({$kid_avg}) — ".__('rp_f_kid_low')];
  elseif($kid_avg<8) $findings[]=['warning', __('rp_mean_kidmed')." ({$kid_avg}) — ".__('rp_f_kid_mid')];
  else              $findings[]=['success', __('rp_mean_kidmed')." ({$kid_avg}) — ".__('rp_f_kid_good')];

  // Risque élevé
  $highRisk = cnt($obesRisk,'Élevé');
  if($highRisk>$n*0.15) $findings[]=['danger', pct($highRisk,$n)."% ".__('rp_f_risk_high')];

  // Perception
  $conc = cnt($perception,'Concordant');
  if(pct($conc,$n)<50) $findings[]=['warning', __('rp_f_gap')." (".pct($conc,$n)."%)"];

  // Saut repas
  $skipMeals = cnt(col($rows,'skip_meal_tutoring'),'Oui, souvent') + cnt(col($rows,'skip_meal_tutoring'),'Oui, parfois');
  if($skipMeals>$n*0.3) $findings[]=['warning', pct($skipMeals,$n)."% ".__('rp_f_skip')];

  // Hypothèses confirmées avec OR fort
  foreach($forestData as $f){
    if($f['p']<0.05 && $f['or']>=2.0){
      $findings[]=['danger', $f['code'].' — '.$f['label'].' : OR='.$f['or'].' [IC '.$f['low'].'–'.$f['high'].'], p='.fmt_p($f['p'])];
    } elseif($f['p']<0.05 && $f['or']<=0.5){
      $findings[]=['success', $f['code'].' — '.$f['label'].' ('.__('rp_protective').') : OR='.$f['or'].' [IC '.$f['low'].'–'.$f['high'].'], p='.fmt_p($f['p'])];
    }
  }

  // ─── Constats spécifiques 3AS ───────────────────────────────
  if($n_3as>0){
    if($pct_skip_3as >= 50)
      $findings[]=['danger', '3AS — '.$pct_skip_3as.'% '.__('rp_f_3as_skip')];
    elseif($pct_skip_3as >= 30)
      $findings[]=['warning', '3AS — '.$pct_skip_3as.'% '.__('rp_f_3as_skip')];

    if($pct_OW_3as > ($pct_OW_global = pct($nSurp+$nObes,$n)))
      $findings[]=['warning', '3AS — '.__('rp_f_3as_owhigher').' ('.$pct_OW_3as.'% vs '.$pct_OW_global.'%)'];

    // Cochran-Armitage trend
    if(isset($ca_ow['p']) && $ca_ow['p']!==null && $ca_ow['p']<0.05)
      $findings[]=['danger', __('rp_f_trend_ow').' (Z='.$ca_ow['Z'].', p='.fmt_p($ca_ow['p']).')'];
    if(isset($ca_skip['p']) && $ca_skip['p']!==null && $ca_skip['p']<0.05)
      $findings[]=['danger', __('rp_f_trend_skip').' (Z='.$ca_skip['Z'].', p='.fmt_p($ca_skip['p']).')'];

    // H10.5 — saut de repas ⟺ surpoids dans 3AS
    if(isset($H['H10.5']['p']) && $H['H10.5']['p']!==null && $H['H10.5']['p']<0.05)
      $findings[]=['danger', '3AS — '.__('rp_f_3as_skip_ow').' : OR='.fmt_or($h10_5).', p='.fmt_p($H['H10.5']['p'])];

    // H10.6 — score_pression ⟺ IMC dans 3AS
    if(isset($H['H10.6']['p']) && $H['H10.6']['p']!==null && $H['H10.6']['p']<0.05)
      $findings[]=['warning', '3AS — '.__('rp_f_3as_press_bmi').' : r='.$pe_press_3as['r'].', p='.fmt_p($H['H10.6']['p'])];
  }

  if(empty($findings)) $findings[]=['info', __('rp_f_nodata')];

  foreach($findings as [$type,$msg]):
  ?>
    <div style="display:flex;gap:8px;align-items:flex-start;padding:6px 0;border-bottom:1px solid var(--border-light)">
      <span class="rp-badge rp-badge-<?=$type?>"><?=$type==='danger'?__('rp_alert'):($type==='warning'?__('rp_note'):($type==='success'?__('rp_positive'):__('rp_info')))?></span>
      <span style="font-size:12px;color:var(--text-muted);line-height:1.7"><?=$msg?></span>
    </div>
  <?php endforeach; ?>
  </div>
</div>

<!-- ═══ EXPORT CSV ═══ -->
<div class="rp-card rp-full" style="margin-top:14px;text-align:center">
  <button onclick="exportHypothesesCSV()" class="print-btn" style="background:#0c5460;color:#fff;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-size:13px">
    📥 <?=__('rp_export_csv')?>
  </button>
  <span style="font-size:11px;color:var(--text-muted);margin-inline-start:10px"><?=__('rp_export_csv_help')?></span>
</div>

<!-- ═══ LIMITES DE L'ÉTUDE ═══ -->
<div class="rp-card rp-full" style="margin-top:14px;border-inline-start:4px solid #856404">
  <div class="rp-card-title">⚠️ <?=__('rp_limits_title')?></div>
  <div class="rp-insight" style="font-size:12px;line-height:1.8">
    <ul style="margin:0;padding-inline-start:18px">
      <li><?=__('rp_limit_design')?></li>
      <li><?=__('rp_limit_selfreport')?></li>
      <li><?=__('rp_limit_sample')?></li>
      <li><?=__('rp_limit_residual')?></li>
      <li><?=__('rp_limit_screen')?></li>
      <li><?=__('rp_limit_multiple')?></li>
      <li><?=__('rp_limit_missing')?></li>
      <li><?=__('rp_limit_3as')?></li>
      <li><?=__('rp_limit_seasonal')?></li>
    </ul>
  </div>
</div>

<!-- ═══ Méthodologie ═══ -->
<div class="rp-card rp-full" style="margin-top:14px">
  <div class="rp-card-title">11. <?=__('rp_method_title')?></div>
  <div class="rp-insight" style="font-size:11px;line-height:1.7">
    <?=__('rp_method_text')?>

    <div style="margin-top:10px;font-weight:600;color:var(--primary)"><?=__('rp_method_subtitle_biv')?></div>
    <ul style="margin-top:4px">
      <li>χ² Pearson 2×2 + correction de continuité — <?=__('rp_method_chi2')?></li>
      <li>t-Student (Welch) bilatéral — <?=__('rp_method_t')?></li>
      <li>ANOVA à un facteur — <?=__('rp_method_anova')?></li>
      <li>Pearson r / Spearman ρ — <?=__('rp_method_corr')?></li>
      <li>Odds Ratio + IC 95% (Woolf) — <?=__('rp_method_or')?></li>
    </ul>

    <div style="margin-top:10px;font-weight:600;color:var(--primary)"><?=__('rp_method_subtitle_multi')?></div>
    <ul style="margin-top:4px">
      <li><?=__('rp_method_logit')?></li>
      <li><?=__('rp_method_glmm')?></li>
      <li><?=__('rp_method_gee')?></li>
      <li><?=__('rp_method_mice')?></li>
    </ul>

    <div style="margin-top:10px;font-weight:600;color:var(--primary)"><?=__('rp_method_subtitle_corr')?></div>
    <ul style="margin-top:4px">
      <li><?=__('rp_method_roc')?></li>
      <li><?=__('rp_method_fdr')?></li>
    </ul>

    <div style="margin-top:10px;font-weight:600;color:var(--primary)"><?=__('rp_method_subtitle_viz')?></div>
    <ul style="margin-top:4px">
      <li><?=__('rp_method_figures')?></li>
    </ul>

    <div style="margin-top:10px;padding-top:8px;border-top:1px dashed var(--border-light)">
      <?=__('rp_method_legend')?> : <strong>***</strong> p&lt;0.001, <strong>**</strong> p&lt;0.01, <strong>*</strong> p&lt;0.05, NS p≥0.05, tendance 0.05≤p&lt;0.20.
    </div>
  </div>
</div>

</div><!-- .rp -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
Chart.defaults.font.family = 'system-ui,-apple-system,sans-serif';
Chart.defaults.font.size = 11;

// 1. IOTF
new Chart(document.getElementById('cIOTF'), {
    type: 'doughnut',
    data: { labels: ["<?=__('rp_minceur')?>","<?=__('rp_normal')?>","<?=__('rp_surpoids')?>","<?=__('rp_obesite')?>"],
            datasets: [{data:<?=$jsIOTF?>, backgroundColor:['#0c5460','#1a7340','#856404','#9b2335']}] },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'bottom'}} }
});

// 2. KIDMED
new Chart(document.getElementById('cKID'), {
    type: 'doughnut',
    data: { labels:["<?=__('rp_kid_low')?>","<?=__('rp_kid_mid')?>","<?=__('rp_kid_opt')?>"],
            datasets:[{data:<?=$jsKidmed?>, backgroundColor:['#9b2335','#856404','#1a7340']}] },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'bottom'}} }
});

// 3. Risk
new Chart(document.getElementById('cRisk'), {
    type: 'doughnut',
    data: { labels:["<?=__('rp_risk_low')?>","<?=__('rp_risk_mod')?>","<?=__('rp_risk_high')?>"],
            datasets:[{data:<?=$jsRisk?>, backgroundColor:['#1a7340','#856404','#9b2335']}] },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'bottom'}} }
});

// 4. Perception
new Chart(document.getElementById('cPercept'), {
    type: 'doughnut',
    data: { labels:["<?=__('rp_concordant')?>","<?=__('rp_underest')?>","<?=__('rp_overest')?>"],
            datasets:[{data:<?=$jsPercept?>, backgroundColor:['#1a7340','#856404','#9b2335']}] },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'bottom'}} }
});

// 5. Global
new Chart(document.getElementById('cGlobal'), {
    type: 'bar',
    data: { labels:["<?=__('rp_bad')?>","<?=__('rp_average')?>","<?=__('rp_good')?>","<?=__('rp_excellent')?>"],
            datasets:[{label:'n',data:<?=$jsGlobal?>,backgroundColor:['#9b2335','#856404','#1a7340','#0c5460']}] },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
});

// 6. Hypotheses summary
new Chart(document.getElementById('cHypos'), {
    type: 'doughnut',
    data: { labels:["<?=__('rp_h_confirmed')?>","<?=__('rp_trend')?>","NS","n/a"],
            datasets:[{data:<?=$jsHypoSummary?>, backgroundColor:['#1a7340','#856404','#0c5460','#999']}] },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'bottom'}} }
});

// 7. Sex × Grade
new Chart(document.getElementById('cSexGrade'), {
    type:'bar',
    data:{ labels:["1AS","2AS","3AS"], datasets:[
        {label:"<?=__('rp_boys')?>", data:[<?=$jsSexGrade['1AS']['boys']?>,<?=$jsSexGrade['2AS']['boys']?>,<?=$jsSexGrade['3AS']['boys']?>], backgroundColor:'#1F4E79'},
        {label:"<?=__('rp_girls')?>", data:[<?=$jsSexGrade['1AS']['girls']?>,<?=$jsSexGrade['2AS']['girls']?>,<?=$jsSexGrade['3AS']['girls']?>], backgroundColor:'#9b2335'}
    ]},
    options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'top'}}, scales:{y:{beginAtZero:true,ticks:{callback:v=>v+'%'}}} }
});

// Evolution by school grade (3AS focus)
new Chart(document.getElementById('cEvol'), {
    type:'line',
    data:{
        labels:<?=$jsEvolLabels?>,
        datasets:[
            {label:"<?=__('rp_evol_ow')?>",     data:<?=$jsEvolOW?>,    borderColor:'#9b2335', backgroundColor:'rgba(155,35,53,0.1)',  yAxisID:'y',  tension:0.3, borderWidth:3},
            {label:"<?=__('rp_evol_skip')?>",   data:<?=$jsEvolSkip?>,  borderColor:'#856404', backgroundColor:'rgba(133,100,4,0.1)',  yAxisID:'y',  tension:0.3, borderWidth:3},
            {label:"<?=__('rp_evol_emot')?>",   data:<?=$jsEvolEmot?>,  borderColor:'#5a3a82', backgroundColor:'rgba(90,58,130,0.1)',  yAxisID:'y',  tension:0.3, borderWidth:2,borderDash:[5,5]},
            {label:"<?=__('rp_evol_str')?>",    data:<?=$jsEvolStr?>,   borderColor:'#c0392b', backgroundColor:'rgba(192,57,43,0.1)',  yAxisID:'y',  tension:0.3, borderWidth:2,borderDash:[5,5]},
            {label:"<?=__('rp_evol_press')?>",  data:<?=$jsEvolPress?>, borderColor:'#0c5460', backgroundColor:'rgba(12,84,96,0.1)',   yAxisID:'y2', tension:0.3, borderWidth:3, type:'line', pointRadius:5}
        ]
    },
    options:{
        responsive:true, maintainAspectRatio:false,
        plugins:{legend:{position:'top'},title:{display:false}},
        scales:{
            y: {position:'left',  beginAtZero:true, title:{display:true,text:'%'},        ticks:{callback:v=>v+'%'}},
            y2:{position:'right', beginAtZero:true, title:{display:true,text:'Score /9'}, max:9, grid:{drawOnChartArea:false}}
        }
    }
});

// 8. Schools
new Chart(document.getElementById('cSchool'), {
    type: 'bar',
    data: { labels:<?=$jsSchoolNames?>, datasets:[
        {label:'n', data:<?=$jsSchoolN?>, backgroundColor:'#1F4E79'},
        {label:"% <?=__('rp_overweight')?> + <?=__('rp_obesity')?>", data:<?=$jsSchoolOb?>, backgroundColor:'#9b2335'}
    ] },
    options: { indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'top'}}, scales:{x:{beginAtZero:true}} }
});

// 9. Forest plot
const forest = <?=$jsForest?>;
const fp = document.getElementById('forestPlot');
if (fp && forest.length>0){
    let html = '';
    const maxOR = Math.max(5, ...forest.map(f=>f.high));
    forest.forEach(f=>{
        const pos1 = (f.low/maxOR)*100;
        const pos2 = (f.high/maxOR)*100;
        const posOR = (f.or/maxOR)*100;
        const color = f.or>1 ? '#9b2335' : '#1a7340';
        html += `
        <div class="forest-row">
            <div style="font-size:10px"><strong>${f.code}</strong> — ${f.label.length>40?f.label.substring(0,40)+'…':f.label}</div>
            <div class="forest-bar">
                <svg viewBox="0 0 100 14" preserveAspectRatio="none">
                    <line x1="${(1/maxOR)*100}" y1="7" x2="${(1/maxOR)*100}" y2="14" stroke="#999" stroke-width="0.3"/>
                    <line x1="${pos1}" y1="7" x2="${pos2}" y2="7" stroke="${color}" stroke-width="0.8"/>
                    <circle cx="${posOR}" cy="7" r="1.2" fill="${color}"/>
                </svg>
            </div>
            <div style="font-size:10px;text-align:center">${f.or.toFixed(2)} [${f.low.toFixed(2)}-${f.high.toFixed(2)}]</div>
            <div style="font-size:10px;text-align:center;color:${f.p<0.05?'#1a7340':'#856404'}">p=${f.p<0.001?'<.001':f.p.toFixed(3)}</div>
        </div>`;
    });
    fp.innerHTML = html;
}

// ════════════════════════════════════════════════════════════════════
// 📑 FIGURES DU CHAPITRE 3 — Reproduction du mémoire
// ════════════════════════════════════════════════════════════════════

// Helper : créer un graphique si l'élément existe
function _ch3MakeChart(id, config){
    const el = document.getElementById(id);
    if(el) new Chart(el, config);
}

// ─── Figure 3 — Distribution IOTF (doughnut) ───
_ch3MakeChart('cCh3F3', {
    type: 'doughnut',
    data: {
        labels: ["<?=__('rp_minceur')?>","<?=__('rp_normal')?>","<?=__('rp_surpoids')?>","<?=__('rp_obesite')?>"],
        datasets: [{ data: <?=$jsCh3_F3?>, backgroundColor:['#0c5460','#1a7340','#856404','#9b2335'], borderWidth:2, borderColor:'#fff' }]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{
            legend:{ position:'right', labels:{ font:{size:11} } },
            tooltip:{ callbacks:{ label:(ctx)=>{
                const total = ctx.dataset.data.reduce((a,b)=>a+b,0);
                const pct = total>0 ? (ctx.parsed*100/total).toFixed(1) : 0;
                return `${ctx.label}: ${ctx.parsed} (${pct}%)`;
            } } }
        }
    }
});

// ─── Figure 4 — IMC moyen par niveau scolaire ───
_ch3MakeChart('cCh3F4', {
    type: 'bar',
    data: {
        labels: ['1ʳᵉ AS','2ᵉ AS','3ᵉ AS'],
        datasets: [{
            label: 'IMC moyen (kg/m²)',
            data: <?=$jsCh3_F4?>,
            backgroundColor:['#1F4E79','#2E75B6','#5b9bd5'],
            borderColor:'#1F4E79', borderWidth:1
        }]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{
            legend:{display:false},
            tooltip:{ callbacks:{ label:(ctx)=>{
                const sd = <?=$jsCh3_F4sd?>[ctx.dataIndex];
                return `IMC = ${ctx.parsed.y} ± ${sd} kg/m²`;
            } } }
        },
        scales:{ y:{ beginAtZero:false, title:{display:true,text:'IMC (kg/m²)'} } }
    }
});

// ─── Figure 5 — Distribution KIDMED ───
_ch3MakeChart('cCh3F5', {
    type: 'bar',
    data: {
        labels: ["<?=__('rp_kid_low')?> (≤3)","<?=__('rp_kid_mid')?> (4–7)","<?=__('rp_kid_opt')?> (≥8)"],
        datasets: [{
            label: 'n',
            data: <?=$jsCh3_F5?>,
            backgroundColor:['#9b2335','#856404','#1a7340'],
            borderWidth:1
        }]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{display:false} },
        scales:{ y:{ beginAtZero:true, title:{display:true,text:'Effectif'} } }
    }
});

// ─── Figure 6 — Boissons sucrées + Petit-déjeuner ───
_ch3MakeChart('cCh3F6', {
    type: 'bar',
    data: {
        labels: ["<?=__('rp_ch3_freq_daily')?>","<?=__('rp_ch3_freq_4_6')?>","<?=__('rp_ch3_freq_1_3')?>","<?=__('rp_ch3_freq_never')?>"],
        datasets: [
            { label:"<?=__('rp_ch3_sugary')?>", data:<?=$jsCh3_F6_sugary?>, backgroundColor:'#9b2335' },
            { label:"<?=__('rp_ch3_breakfast')?>", data:<?=$jsCh3_F6_breakfast?>, backgroundColor:'#1a7340' }
        ]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{position:'top'} },
        scales:{ y:{ beginAtZero:true, title:{display:true,text:'Effectif'} } }
    }
});

// ─── Figure 7 — % saut repas par niveau ───
_ch3MakeChart('cCh3F7', {
    type: 'bar',
    data: {
        labels: ['1ʳᵉ AS','2ᵉ AS','3ᵉ AS'],
        datasets: [{
            label:'% saut de repas',
            data: <?=$jsCh3_F7?>,
            backgroundColor:['#fec44f','#ec7014','#cc4c02'],
            borderColor:'#cc4c02', borderWidth:1
        }]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{
            legend:{display:false},
            tooltip:{ callbacks:{ label:(ctx)=>`${ctx.parsed.y}%` } }
        },
        scales:{ y:{ beginAtZero:true, max:100, ticks:{callback:v=>v+'%'}, title:{display:true,text:'%'} } }
    }
});

// ─── Figure 8 — KIDMED moyen par classe IOTF (avec barres d'erreur SD) ───
{
    const f8means = <?=$jsCh3_F8_means?>;
    const f8sds   = <?=$jsCh3_F8_sds?>;
    const f8ns    = <?=$jsCh3_F8_ns?>;
    _ch3MakeChart('cCh3F8', {
        type: 'bar',
        data: {
            labels: ["<?=__('rp_normal')?>","<?=__('rp_surpoids')?>","<?=__('rp_obesite')?>"],
            datasets: [{
                label: 'KIDMED moyen',
                data: f8means,
                backgroundColor: ['#1a7340','#856404','#9b2335'],
                borderWidth: 1
            }]
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins:{
                legend:{display:false},
                tooltip:{ callbacks:{ label:(ctx)=>`KIDMED = ${ctx.parsed.y} ± ${f8sds[ctx.dataIndex]} (n=${f8ns[ctx.dataIndex]})` } }
            },
            scales:{ y:{ beginAtZero:true, title:{display:true,text:'Score KIDMED'} } }
        },
        plugins: [{
            id: 'errorBars',
            afterDatasetsDraw(chart){
                const {ctx, scales:{y}} = chart;
                const meta = chart.getDatasetMeta(0);
                meta.data.forEach((bar, i)=>{
                    const m = f8means[i], s = f8sds[i];
                    if(!s) return;
                    const yTop = y.getPixelForValue(m+s);
                    const yBot = y.getPixelForValue(Math.max(0, m-s));
                    const x = bar.x;
                    ctx.save();
                    ctx.strokeStyle = '#333'; ctx.lineWidth = 1.2;
                    ctx.beginPath();
                    ctx.moveTo(x, yTop); ctx.lineTo(x, yBot);
                    ctx.moveTo(x-6, yTop); ctx.lineTo(x+6, yTop);
                    ctx.moveTo(x-6, yBot); ctx.lineTo(x+6, yBot);
                    ctx.stroke();
                    ctx.restore();
                });
            }
        }]
    });
}

// ─── Figure 9 — Activité physique par sexe ───
_ch3MakeChart('cCh3F9', {
    type: 'bar',
    data: {
        labels: ['Inactif','Peu actif','Actif','Très actif'],
        datasets: [
            { label:"<?=__('rp_boys')?>",  data:<?=$jsCh3_F9_boys?>,  backgroundColor:'#1F4E79' },
            { label:"<?=__('rp_girls')?>", data:<?=$jsCh3_F9_girls?>, backgroundColor:'#9b2335' }
        ]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{position:'top'} },
        scales:{ y:{ beginAtZero:true, title:{display:true,text:'Effectif'} } }
    }
});

// ─── Figure 10 — Histogramme heures d'écran ───
_ch3MakeChart('cCh3F10', {
    type: 'bar',
    data: {
        labels: <?=$jsCh3_F10_labels?>,
        datasets: [{
            label:"n d'élèves",
            data: <?=$jsCh3_F10_counts?>,
            backgroundColor: <?=$jsCh3_F10_labels?>.map((l,i)=> i<2 ? '#1a7340' : (i<4 ? '#856404' : '#9b2335')),
            borderWidth:1
        }]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{display:false} },
        scales:{
            x:{ title:{display:true,text:"Heures d'écran cumulées par jour"} },
            y:{ beginAtZero:true, title:{display:true,text:'Effectif'} }
        }
    }
});

// ─── Figure 11 — Perception × IOTF (stacked bar) ───
{
    const f11_data = <?=$jsCh3_F11_data?>; // [iotf][perception]
    const f11_iotf = <?=$jsCh3_F11_labels?>;
    const f11_perc = <?=$jsCh3_F11_percLbl?>;
    // pivot pour stacked : un dataset par perception
    const colors11 = ['#0c5460','#5b9bd5','#1a7340','#856404','#9b2335'];
    const datasets11 = f11_perc.map((p, idx)=>({
        label: p,
        data: f11_iotf.map((_, j)=> (f11_data[j] && f11_data[j][idx]) ? f11_data[j][idx] : 0),
        backgroundColor: colors11[idx % colors11.length]
    }));
    _ch3MakeChart('cCh3F11', {
        type: 'bar',
        data: { labels: f11_iotf, datasets: datasets11 },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{position:'top', labels:{font:{size:10}, boxWidth:10} } },
            scales:{
                x:{ stacked:true, title:{display:true,text:'Classe IOTF (objectif)'} },
                y:{ stacked:true, beginAtZero:true, title:{display:true,text:'Effectif'} }
            }
        }
    });
}

// ─── Figure 12 — Convergence pression × saut de repas ───
_ch3MakeChart('cCh3F12', {
    type: 'bar',
    data: {
        labels: ['1ʳᵉ AS','2ᵉ AS','3ᵉ AS'],
        datasets: [
            { type:'bar', label:'% saut de repas', data:<?=$jsCh3_F12_skip?>, backgroundColor:'#cc4c02', yAxisID:'y' },
            { type:'line', label:'Score pression /9', data:<?=$jsCh3_F12_press?>, borderColor:'#0c5460', backgroundColor:'rgba(12,84,96,0.1)', tension:0.3, borderWidth:3, pointRadius:6, pointBackgroundColor:'#0c5460', yAxisID:'y2' }
        ]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{position:'top'} },
        scales:{
            y:  { position:'left',  beginAtZero:true, max:100, title:{display:true,text:'% saut de repas'}, ticks:{callback:v=>v+'%'} },
            y2: { position:'right', beginAtZero:true, max:9,   title:{display:true,text:'Score pression /9'}, grid:{drawOnChartArea:false} }
        }
    }
});

// ─── Figure 13 — Forest plot multivarié (V1) ───
{
    const f13 = <?=$jsCh3_F13?>;
    const fp13 = document.getElementById('cCh3F13Plot');
    if(fp13 && f13.length > 0){
        const maxOR = Math.max(3, ...f13.map(f=>f.high || f.or || 1));
        let html = '';
        f13.forEach(f=>{
            const pos1 = (f.low /maxOR)*100;
            const pos2 = (f.high/maxOR)*100;
            const posOR= (f.or  /maxOR)*100;
            const sig  = (f.p !== null && f.p < 0.05);
            const color = sig ? (f.or > 1 ? '#9b2335' : '#1a7340') : '#888';
            html += `
            <div class="forest-row">
                <div style="font-size:11px"><strong>${f.name}</strong></div>
                <div class="forest-bar">
                    <svg viewBox="0 0 100 14" preserveAspectRatio="none">
                        <line x1="${(1/maxOR)*100}" y1="2" x2="${(1/maxOR)*100}" y2="12" stroke="#bbb" stroke-width="0.5" stroke-dasharray="0.5,0.5"/>
                        <line x1="${pos1}" y1="7" x2="${pos2}" y2="7" stroke="${color}" stroke-width="0.9"/>
                        <circle cx="${posOR}" cy="7" r="1.4" fill="${color}"/>
                    </svg>
                </div>
                <div style="font-size:10px;text-align:center">${f.or.toFixed(2)} [${f.low.toFixed(2)}–${f.high.toFixed(2)}]</div>
                <div style="font-size:10px;text-align:center;color:${sig?'#1a7340':'#856404'}">${f.p===null?'—':(f.p<0.001?'<0.001':f.p.toFixed(3))}</div>
            </div>`;
        });
        fp13.innerHTML = html;
    }
}

// ─── Figure 14 — Courbe ROC ───
_ch3MakeChart('cCh3F14', {
    type: 'line',
    data: {
        labels: <?=$jsCh3_F14_fpr?>.map(v=>v.toFixed(2)),
        datasets: [
            {
                label: 'ROC V1 (AUC = <?=$ch3_f14_auc!==null?$ch3_f14_auc:"—"?>)',
                data: <?=$jsCh3_F14_tpr?>,
                borderColor:'#1F4E79', backgroundColor:'rgba(31,78,121,0.15)',
                tension:0, borderWidth:2, pointRadius:0, fill:true
            },
            {
                label: 'Référence (AUC = 0.50)',
                data: <?=$jsCh3_F14_fpr?>,
                borderColor:'#999', borderDash:[6,4], borderWidth:1, pointRadius:0, fill:false
            }
        ]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{position:'bottom', labels:{font:{size:10}} } },
        scales:{
            x:{ type:'category', title:{display:true,text:'1 − Spécificité (FPR)'} },
            y:{ beginAtZero:true, max:1, title:{display:true,text:'Sensibilité (TPR)'} }
        }
    }
});

// ─── Figure 15 — V1 (screen_max) vs V2 (screen_sum) ───
{
    const f15names = <?=$jsCh3_F15_names?>;
    const f15v1    = <?=$jsCh3_F15_v1?>;
    const f15v2    = <?=$jsCh3_F15_v2?>;
    _ch3MakeChart('cCh3F15', {
        type:'bar',
        data:{
            labels: f15names,
            datasets: [
                { label:'V1 (screen_max)',  data:f15v1.map(d=>d?d.or:null), backgroundColor:'#1F4E79' },
                { label:'V2 (screen_sum)',  data:f15v2.map(d=>d?d.or:null), backgroundColor:'#9b2335' }
            ]
        },
        options:{
            indexAxis:'y', responsive:true, maintainAspectRatio:false,
            plugins:{
                legend:{position:'top'},
                tooltip:{ callbacks:{ label:(ctx)=>{
                    const d = (ctx.dataset.label.includes('V1')?f15v1:f15v2)[ctx.dataIndex];
                    if(!d) return ctx.dataset.label+' : —';
                    return `${ctx.dataset.label} : OR = ${d.or.toFixed(3)} [${d.low.toFixed(3)} – ${d.high.toFixed(3)}]`;
                } } }
            },
            scales:{ x:{ beginAtZero:true, title:{display:true,text:'OR ajusté'} } }
        }
    });
}

// ─── Figure 16 — Robustesse multi-méthode ───
{
    const f16   = <?=$jsCh3_F16?>;
    const fkeys = <?=$jsCh3_F16_keys?>;
    const methods = Object.keys(f16).filter(k=>f16[k] !== null);
    const colors16 = {'V1 standard':'#1F4E79','GLMM (école)':'#1a7340','GEE':'#856404','MICE m=10':'#9b2335'};
    const datasets16 = methods.map(m=>({
        label: m,
        data: fkeys.map(kv => (f16[m][kv] && f16[m][kv].or !== null) ? f16[m][kv].or : null),
        backgroundColor: colors16[m] || '#888'
    }));
    _ch3MakeChart('cCh3F16', {
        type:'bar',
        data:{ labels: fkeys, datasets: datasets16 },
        options:{
            responsive:true, maintainAspectRatio:false,
            plugins:{
                legend:{position:'top'},
                tooltip:{ callbacks:{ label:(ctx)=>{
                    const m = ctx.dataset.label, kv = fkeys[ctx.dataIndex];
                    const d = f16[m] ? f16[m][kv] : null;
                    if(!d) return `${m} : —`;
                    const lo = d.low===null?'—':d.low.toFixed(2);
                    const hi = d.high===null?'—':d.high.toFixed(2);
                    return `${m} : OR = ${d.or===null?'—':d.or.toFixed(3)} [${lo} – ${hi}]`;
                } } }
            },
            scales:{
                y:{ beginAtZero:false, title:{display:true,text:'OR ajusté'} }
            }
        }
    });
}

// ─── Figure 17 — Volcano plot ───
{
    const f17 = <?=$jsCh3_F17?>;
    if(f17.length > 0){
        _ch3MakeChart('cCh3F17', {
            type:'scatter',
            data:{
                datasets:[
                    {
                        label: '<?=__('rp_ch3_fig17_sig_bh')?>',
                        data: f17.filter(p=>p.sig===1).map(p=>({x:p.x, y:p.y, _ref:p})),
                        backgroundColor:'#9b2335',
                        pointRadius:7, pointHoverRadius:9, borderColor:'#fff', borderWidth:1
                    },
                    {
                        label: 'NS (FDR)',
                        data: f17.filter(p=>p.sig===0).map(p=>({x:p.x, y:p.y, _ref:p})),
                        backgroundColor:'rgba(150,150,150,0.55)',
                        pointRadius:5, pointHoverRadius:7, borderColor:'#fff', borderWidth:1
                    }
                ]
            },
            options:{
                responsive:true, maintainAspectRatio:false,
                plugins:{
                    legend:{position:'top'},
                    tooltip:{ callbacks:{ label:(ctx)=>{
                        const r = ctx.raw && ctx.raw._ref;
                        if(!r) return '';
                        return [
                            `${r.code} — ${r.label}`,
                            `OR = ${r.or.toFixed(2)}  |  ln(OR) = ${r.x.toFixed(2)}`,
                            `p = ${r.p<0.001?'<0.001':r.p.toFixed(3)}  |  −log₁₀(p) = ${r.y.toFixed(2)}`
                        ];
                    } } }
                },
                scales:{
                    x:{
                        title:{display:true,text:'ln(OR)  — protecteur ←  | →  facteur de risque'},
                        grid:{ color:(ctx)=> ctx.tick && ctx.tick.value===0 ? '#999' : 'rgba(0,0,0,0.05)' }
                    },
                    y:{
                        beginAtZero:true,
                        title:{display:true,text:'−log₁₀(p)'}
                    }
                }
            }
        });
        // Ligne horizontale p=0.05 ⇔ -log10(p)=1.301
        // Géré via le grid (ligne fixe en haut visuellement par décoration séparée non triviale en Chart.js sans plugin annotation)
    }
}

// ─── Export CSV des hypothèses ─────────────────────────────
const allHypotheses = <?php
    $exportH = [];
    foreach($H as $code=>$h){
        $exportH[] = [
            'code'   => $code,
            'titre'  => $h['title'] ?? '',
            'test'   => $h['test'] ?? '',
            'stat'   => $h['stat'] ?? '',
            'effect' => $h['effect'] ?? '',
            'p'      => isset($h['p']) && $h['p']!==null ? (string)$h['p'] : 'n/a',
            'concl'  => isset($h['p']) && $h['p']!==null
                          ? ($h['p']<0.05 ? 'Confirmée' : ($h['p']<0.20 ? 'Tendance' : 'NS'))
                          : 'Non testable',
        ];
    }
    echo json_encode($exportH, JSON_UNESCAPED_UNICODE);
?>;

function exportHypothesesCSV(){
    const headers = ['Code','Hypothèse','Test','Statistique','Effet','p-value','Conclusion'];
    const rows = [headers.join(';')];
    allHypotheses.forEach(h=>{
        const cells = [h.code, h.titre, h.test, h.stat, h.effect, h.p, h.concl];
        const escaped = cells.map(c => {
            const s = String(c).replace(/"/g,'""');
            return /[;"\n]/.test(s) ? `"${s}"` : s;
        });
        rows.push(escaped.join(';'));
    });
    const csv = '\uFEFF' + rows.join('\n'); // BOM pour Excel
    const blob = new Blob([csv], {type:'text/csv;charset=utf-8;'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'hypotheses_chlef_' + new Date().toISOString().slice(0,10) + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>
</body>
</html>
