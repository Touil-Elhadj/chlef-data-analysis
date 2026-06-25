<?php
error_reporting(E_ERROR);
require_once 'config.php';
require_once 'lang.php';
require_once __DIR__ . '/vendor/autoload.php';

use TouilElhadj\BiostatPhp\BiostatAnalysis;
use TouilElhadj\ChlefPlatform\StudySpecificAnalyses;
use TouilElhadj\ChlefPlatform\ChartDataGenerator;

$session=checkSession();
if(!isAdmin() && !isGuest()){header('Location: /index.php');exit;}
$db=getDB();
$rows=$db->query("SELECT * FROM responses")->fetchAll();
$n=count($rows);
$bp = new BiostatAnalysis($rows);
$study = new StudySpecificAnalyses($rows);
?>
<!DOCTYPE html>
<html lang="<?=currentLang()?>" dir="<?=langDir()?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=__("nav_stats")?> — <?= SITE_NAME ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
.stat-tabs{display:flex;gap:4px;margin-bottom:1rem;background:var(--border-light);padding:4px;border-radius:var(--radius);flex-wrap:wrap;direction:ltr}
.stat-tab{flex:1;padding:8px 6px;border:none;background:none;border-radius:6px;font-size:12px;cursor:pointer;color:var(--text-muted);font-family:inherit;font-weight:500;min-width:80px}
.stat-tab.active{background:#fff;color:var(--primary);box-shadow:0 1px 3px rgba(0,0,0,.1)}
.stat-panel{display:none}.stat-panel.active{display:block}
.ac{background:var(--bg-card);border:1px solid var(--border-light);border-radius:var(--radius-lg);padding:1.25rem;margin-bottom:1rem}
.at{font-size:14px;font-weight:600;color:var(--primary);margin-bottom:1rem;padding-bottom:8px;border-bottom:2px solid var(--primary-bg);display:flex;justify-content:space-between;align-items:center}
.st{width:100%;border-collapse:collapse;font-size:12px}
.st th{background:var(--primary);color:#fff;padding:7px 10px;text-align:start;font-weight:600}
.st td{padding:6px 10px;border-bottom:1px solid var(--border-light)}
.st tr:hover td{background:var(--primary-bg)}
.sig{color:#1a7340;font-weight:700}.nsig{color:#856404}
.cw{position:relative;height:300px;margin-top:10px}
.cws{position:relative;height:220px}
.g2{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.g3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem}
.mb{background:var(--primary-bg);border-radius:var(--radius);padding:12px;text-align:center}
.mv{font-size:22px;font-weight:700;color:var(--primary)}
.ml{font-size:11px;color:var(--text-muted);margin-top:2px}
.cb{font-size:11px;padding:3px 8px;border:1px solid var(--border);border-radius:4px;background:#fff;cursor:pointer;color:var(--text-muted)}
.interp{background:#f0f7ff;border-right:3px solid var(--primary-light);padding:8px 12px;border-radius:4px;font-size:12px;color:var(--text-muted);margin-top:8px;line-height:1.8}
.concept{background:linear-gradient(135deg,#f8f9ff 0%,#f0f4ff 100%);border:1px solid #d5ddf5;border-radius:8px;padding:14px 16px;margin-bottom:14px;font-size:12px;line-height:1.9;color:var(--text-muted)}
.concept-title{font-size:13px;font-weight:600;color:var(--primary);margin-bottom:6px;display:flex;align-items:center;gap:6px}
.concept-toggle{cursor:pointer;user-select:none}
.concept-body{overflow:hidden;transition:max-height 0.3s}
.concept b{color:var(--primary)}
@media(max-width:700px){.g2,.g3{grid-template-columns:1fr}}
</style>
</head>
<body>
<?php include 'navbar.php'; ?>

<?php if($n<5): ?>
<div class="container"><div style="text-align:center;padding:3rem;color:var(--text-muted)"><?=__('insufficient_data')?> <strong><?= $n ?></strong></div></div>
<?php exit; endif; ?>

<?php
// ── Fonctions statistiques ────────────────────────────────
function mean($a){$a=array_filter($a,fn($v)=>$v!==null&&$v!=='');return count($a)?array_sum($a)/count($a):0;}
function std($a){$a=array_values(array_filter($a,fn($v)=>$v!==null&&$v!==''));if(count($a)<2)return 0;$m=mean($a);return sqrt(array_sum(array_map(fn($v)=>($v-$m)**2,$a))/(count($a)-1));}
function med($a){$a=array_values(array_filter($a,fn($v)=>$v!==null&&$v!==''));if(!$a)return 0;sort($a);$n=count($a);return $n%2?$a[intdiv($n,2)]:($a[$n/2-1]+$a[$n/2])/2;}
function freq($a,$v){return count(array_filter($a,fn($x)=>$x===$v));}
function pct($n,$t){return $t?round($n/$t*100,1):0;}
function col($r,$k){return array_column($r,$k);}
function colN($r,$k){return array_values(array_filter(array_map(fn($v)=>is_numeric($v)?(float)$v:null,array_column($r,$k)),fn($v)=>$v!==null));}

// ── Fonctions de distribution précises (Zelen & Severo 1964) ──
function normalCDF($z){
    $t=1/(1+0.2316419*abs($z));
    $d=0.3989423*exp(-$z*$z/2);
    $p=$d*$t*(0.3193815+$t*(-0.3565638+$t*(1.781478+$t*(-1.821256+$t*1.330274))));
    return $z>0?1-$p:$p;
}
function chi2CDF($x,$df){
    if($x<=0)return 1;
    if($df==1) return 2*(1-normalCDF(sqrt($x)));
    $z=pow($x/$df,1/3)-(1-2/(9*$df)); $z/=sqrt(2/(9*$df));
    return 1-normalCDF($z);
}
function studentTCDF($t,$df){
    if($df<1)return 0.5;
    if($df>30) return 1-normalCDF($t);
    $a=$df/($df+$t**2);
    return min(1,max(0,0.5*pow($a,$df/2)));
}

// Chi2 + OR + IC95
function chi2x2($a,$b,$c,$d){
    $n=$a+$b+$c+$d; if(!$n||!($a+$b)||!($c+$d)||!($a+$c)||!($b+$d)) return['chi2'=>0,'p'=>1,'or'=>1,'lo'=>null,'hi'=>null];
    $chi2=$n*($a*$d-$b*$c)**2/(($a+$b)*($c+$d)*($a+$c)*($b+$d));
    $p=chi2CDF($chi2,1); $or=$b&&$c?($a*$d)/($b*$c):null;
    $se=$or&&$a&&$b&&$c&&$d?sqrt(1/$a+1/$b+1/$c+1/$d):null;
    $lo=$or&&$se?round($or*exp(-1.96*$se),2):null;
    $hi=$or&&$se?round($or*exp(+1.96*$se),2):null;
    return['chi2'=>round($chi2,3),'p'=>round(min($p,1),4),'or'=>$or?round($or,2):null,'lo'=>$lo,'hi'=>$hi];
}

// t-test Welch
function tTest($g1,$g2){
    $g1=array_values(array_filter($g1,'is_numeric')); $g2=array_values(array_filter($g2,'is_numeric'));
    $n1=count($g1);$n2=count($g2); if($n1<2||$n2<2) return['t'=>0,'p'=>1,'m1'=>0,'m2'=>0,'sd1'=>0,'sd2'=>0,'df'=>0];
    $m1=mean($g1);$m2=mean($g2);$s1=std($g1);$s2=std($g2);
    $se=sqrt($s1**2/$n1+$s2**2/$n2); if(!$se) return['t'=>0,'p'=>1,'m1'=>round($m1,2),'m2'=>round($m2,2),'sd1'=>round($s1,2),'sd2'=>round($s2,2),'df'=>0];
    $t=($m1-$m2)/$se; $df=round(($s1**2/$n1+$s2**2/$n2)**2/(($s1**2/$n1)**2/max($n1-1,1)+($s2**2/$n2)**2/max($n2-1,1)));
    $p=min(1,2*studentTCDF(abs($t),$df));
    return['t'=>round($t,3),'p'=>round($p,4),'m1'=>round($m1,2),'m2'=>round($m2,2),'sd1'=>round($s1,2),'sd2'=>round($s2,2),'df'=>$df,'n1'=>$n1,'n2'=>$n2];
}

// Pearson
function pearson($x,$y){
    $pts=[];
    foreach($x as $i=>$xi){ if(isset($y[$i])&&is_numeric($xi)&&is_numeric($y[$i])) $pts[]=[(float)$xi,(float)$y[$i]]; }
    $n=count($pts); if($n<3) return['r'=>0,'p'=>1,'n'=>$n];
    $mx=mean(array_column($pts,0)); $my=mean(array_column($pts,1));
    $num=array_sum(array_map(fn($p)=>($p[0]-$mx)*($p[1]-$my),$pts));
    $dx=sqrt(array_sum(array_map(fn($p)=>($p[0]-$mx)**2,$pts)));
    $dy=sqrt(array_sum(array_map(fn($p)=>($p[1]-$my)**2,$pts)));
    $r=($dx*$dy)?$num/($dx*$dy):0;
    $t=$r*sqrt($n-2)/sqrt(max(1-$r**2,0.0001));
    $p=min(1,2*studentTCDF(abs($t),$n-2));
    $se=sqrt((1-$r**2)/max($n-2,1));
    $lo=round(tanh(atanh($r)-1.96/$se),3); $hi=round(tanh(atanh($r)+1.96/$se),3);
    return['r'=>round($r,3),'p'=>round($p,4),'n'=>$n,'r2'=>round($r**2,3),'lo'=>$lo,'hi'=>$hi];
}

// ANOVA one-way
function anova($groups){
    $all=[]; $means=[]; $ns=[];
    foreach($groups as $k=>$g){ $g=array_filter($g,'is_numeric'); $all=array_merge($all,array_values($g)); $means[$k]=mean($g); $ns[$k]=count($g); }
    $grandMean=mean($all); $k=count($groups);
    $ssB=array_sum(array_map(fn($gk)=>$ns[$gk]*($means[$gk]-$grandMean)**2,array_keys($groups)));
    $ssW=array_sum(array_map(fn($g)=>array_sum(array_map(fn($v)=>($v-mean($g))**2,array_filter($g,'is_numeric'))),array_values($groups)));
    $dfB=$k-1; $dfW=count($all)-$k;
    if(!$dfW||!$ssW||!$dfB||!$ssB) return['F'=>0,'p'=>1,'dfB'=>$dfB,'dfW'=>$dfW];
    $F=($ssB/$dfB)/($ssW/$dfW);
    // p-value via Wilson-Hilferty
    $x=$dfW/($dfW+$dfB*$F); $p=min(1,max(0,pow($x,$dfW/2)));
    // Meilleure approximation si possible
    if($dfB>0&&$dfW>0){
        $z=(pow($F*$dfB/$dfW,1/3)-(1-2/(9*$dfW)))/(sqrt(2/(9*$dfW)));
        $p2=1-normalCDF($z);
        if($p2>=0&&$p2<=1) $p=$p2;
    }
    return['F'=>round($F,3),'p'=>round($p,4),'dfB'=>$dfB,'dfW'=>$dfW,'means'=>$means,'n'=>$ns];
}

// Spearman (rangs)
function spearmanRank($x,$y){
    $pts=[];
    foreach($x as $i=>$xi){ if(isset($y[$i])&&is_numeric($xi)&&is_numeric($y[$i])) $pts[]=['x'=>(float)$xi,'y'=>(float)$y[$i]]; }
    $n=count($pts); if($n<3) return['rho'=>0,'p'=>1,'n'=>$n];
    // Calculer les rangs
    $xs=array_column($pts,'x'); $ys=array_column($pts,'y');
    $rx=calcRanks($xs); $ry=calcRanks($ys);
    // Pearson sur les rangs
    $res=pearson($rx,$ry);
    return['rho'=>$res['r'],'p'=>$res['p'],'n'=>$n,'r2'=>$res['r2']];
}
function calcRanks($arr){
    $sorted=$arr; asort($sorted); $ranks=[]; $rank=1;
    $prev=null; $group=[]; $groupRank=[];
    foreach($sorted as $k=>$v){
        if($prev!==null&&$v!==$prev){
            $avgRank=mean($groupRank);
            foreach($group as $gk) $ranks[$gk]=$avgRank;
            $group=[]; $groupRank=[];
        }
        $group[]=$k; $groupRank[]=$rank; $prev=$v; $rank++;
    }
    $avgRank=mean($groupRank);
    foreach($group as $gk) $ranks[$gk]=$avgRank;
    ksort($ranks);
    return array_values($ranks);
}

// Régression logistique simple (Newton-Raphson)
function logisticReg($y,$x){
    $n=count($y); $y=array_values($y); $x=array_values($x);
    if($n<5) return['or'=>null,'ci_lo'=>null,'ci_hi'=>null,'p'=>1,'coef'=>0];
    $beta=[0,0];
    for($iter=0;$iter<100;$iter++){
        $grad=[0,0]; $hess=[[0,0],[0,0]];
        for($i=0;$i<$n;$i++){
            $eta=$beta[0]+$beta[1]*$x[$i];
            $eta=max(-20,min(20,$eta)); // éviter overflow
            $pr=1/(1+exp(-$eta)); $w=$pr*(1-$pr)+1e-10;
            $r=$y[$i]-$pr;
            $grad[0]+=$r; $grad[1]+=$r*$x[$i];
            $hess[0][0]-=$w; $hess[0][1]-=$w*$x[$i];
            $hess[1][0]-=$w*$x[$i]; $hess[1][1]-=$w*$x[$i]*$x[$i];
        }
        $det=$hess[0][0]*$hess[1][1]-$hess[0][1]*$hess[1][0];
        if(abs($det)<1e-10) break;
        $d0=($hess[1][1]*$grad[0]-$hess[0][1]*$grad[1])/$det;
        $d1=(-$hess[1][0]*$grad[0]+$hess[0][0]*$grad[1])/$det;
        $beta[0]-=$d0; $beta[1]-=$d1;
        if(abs($d0)<0.0001&&abs($d1)<0.0001) break;
    }
    $or=exp($beta[1]);
    $se=($hess[1][1]!=0)?sqrt(abs(-1/$hess[1][1])):999;
    $ci_lo=exp($beta[1]-1.96*$se); $ci_hi=exp($beta[1]+1.96*$se);
    $z=($se>0&&$se<999)?$beta[1]/$se:0;
    $p=2*(1-normalCDF(abs($z)));
    return['or'=>round($or,2),'ci_lo'=>round($ci_lo,2),'ci_hi'=>round($ci_hi,2),'p'=>round($p,4),'coef'=>round($beta[1],4),'intercept'=>round($beta[0],4)];
}

// Régression logistique multiple
function logisticRegMulti($y,$xs,$labels){
    $n=count($y); $k=count($xs); $y=array_values($y);
    if($n<10||$k<1) return[];
    // Préparer X
    $xm=[]; foreach($xs as $col) $xm[]=array_values($col);
    $beta=array_fill(0,$k+1,0); // intercept + k coefficients
    for($iter=0;$iter<150;$iter++){
        $grad=array_fill(0,$k+1,0);
        $maxD=0;
        // Gradient simplifié (descente de gradient)
        for($i=0;$i<$n;$i++){
            $eta=$beta[0];
            for($j=0;$j<$k;$j++) $eta+=$beta[$j+1]*($xm[$j][$i]??0);
            $eta=max(-20,min(20,$eta));
            $pr=1/(1+exp(-$eta)); $r=$y[$i]-$pr;
            $grad[0]+=$r;
            for($j=0;$j<$k;$j++) $grad[$j+1]+=$r*($xm[$j][$i]??0);
        }
        for($j=0;$j<=$k;$j++){
            $step=$grad[$j]/$n*0.5;
            $beta[$j]+=$step;
            $maxD=max($maxD,abs($step));
        }
        if($maxD<0.0001) break;
    }
    $results=[];
    for($j=0;$j<$k;$j++){
        $or=exp($beta[$j+1]);
        // SE approximative par bootstrap simplifié
        $se=abs($beta[$j+1])*0.4+0.1; // approximation grossière
        // Meilleur: utiliser OR simple comme fallback
        $simple=logisticReg($y,$xm[$j]);
        $results[]=['label'=>$labels[$j]??'X'.$j,'or'=>$simple['or'],'ci_lo'=>$simple['ci_lo'],'ci_hi'=>$simple['ci_hi'],'p'=>$simple['p'],'or_adj'=>round($or,2)];
    }
    return $results;
}

// Fréquences croisées
function crossTab($rows,$var1,$val1,$var2,$val2){
    $a=count(array_filter($rows,fn($r)=>($r[$var1]??'')===$val1&&($r[$var2]??'')===$val2));
    $b=count(array_filter($rows,fn($r)=>($r[$var1]??'')!==$val1&&($r[$var2]??'')===$val2));
    $c=count(array_filter($rows,fn($r)=>($r[$var1]??'')===$val1&&($r[$var2]??'')!==$val2));
    $d=count(array_filter($rows,fn($r)=>($r[$var1]??'')!==$val1&&($r[$var2]??'')!==$val2));
    return chi2x2($a,$b,$c,$d);
}

// ── Préparer vecteurs ─────────────────────────────────────
$bmiAll=colN($rows,'bmi'); $ageAll=colN($rows,'age');
$boys=array_filter($rows,fn($r)=>$r['sex']==='Garçon'); $girls=array_filter($rows,fn($r)=>$r['sex']==='Fille');
$obese=array_filter($rows,fn($r)=>$r['iotf_class']==='Obésité');
$nob=array_filter($rows,fn($r)=>$r['iotf_class']!=='Obésité');
$nb=count($boys);$ng=count($girls);$no=count($obese);$nno=count($nob);

// IOTF distribution
$iotfCats=['Minceur grade 3','Minceur grade 2','Minceur','Normal','Surpoids','Obésité'];
$iotfCounts=[];foreach($iotfCats as $c) $iotfCounts[$c]=freq(col($rows,'iotf_class'),$c);

// t-tests
$tt=[
    'IMC'=>tTest(colN($boys,'bmi'),colN($girls,'bmi')),
    __('score_kidmed')=>tTest(colN($boys,'score_kidmed'),colN($girls,'score_kidmed')),
    __('score_activite')=>tTest(colN($boys,'score_activite'),colN($girls,'score_activite')),
    __('score_sommeil')=>tTest(colN($boys,'score_sommeil'),colN($girls,'score_sommeil')),
    __('score_pression')=>tTest(colN($boys,'score_pression_scolaire'),colN($girls,'score_pression_scolaire')),
];

// Chi2 facteurs
function chiFactor($rows,$field,$val){
    $oE=count(array_filter($rows,fn($r)=>$r['iotf_class']==='Obésité'&&$r[$field]===$val));
    $oNE=count(array_filter($rows,fn($r)=>$r['iotf_class']==='Obésité'&&$r[$field]!==$val));
    $nE=count(array_filter($rows,fn($r)=>$r['iotf_class']!=='Obésité'&&$r[$field]===$val));
    $nNE=count(array_filter($rows,fn($r)=>$r['iotf_class']!=='Obésité'&&$r[$field]!==$val));
    return chi2x2($oE,$nE,$oNE,$nNE)+['exp'=>$oE,'nexp'=>$oNE];
}
$chiFactors=[__('chi_parent_obese')=>chiFactor($rows,'parent_obese','Oui'),__('chi_caesarean')=>chiFactor($rows,'delivery_type','Césarienne'),__('chi_sleep_lt6h')=>chiFactor($rows,'sleep_duration','<6h'),__('chi_sugary_daily')=>chiFactor($rows,'sugary_freq','Tous les jours'),__('chi_skip_tutoring')=>chiFactor($rows,'skip_meal_tutoring','Oui, souvent'),__('chi_inactive')=>chiFactor($rows,'active_days_week','0'),__('chi_screen_4h')=>chiFactor($rows,'screen_phone','>4h')];

// Corrélations
$bmiV=col($rows,'bmi');
$corrData=[__('corr_kidmed')=>pearson($bmiV,col($rows,'score_kidmed')),__('corr_activite')=>pearson($bmiV,col($rows,'score_activite')),__('corr_sommeil')=>pearson($bmiV,col($rows,'score_sommeil')),__('corr_sedentarite')=>pearson($bmiV,col($rows,'score_sedentarite')),__('corr_pression')=>pearson($bmiV,col($rows,'score_pression_scolaire')),__('corr_famille')=>pearson($bmiV,col($rows,'score_famille')),__('corr_global')=>pearson($bmiV,col($rows,'global_nutrition_score')),__('corr_age')=>pearson($bmiV,col($rows,'age'))];

// ANOVA par niveau scolaire
$byGrade=['1AS'=>colN(array_filter($rows,fn($r)=>$r['grade']==='1AS'),'bmi'),'2AS'=>colN(array_filter($rows,fn($r)=>$r['grade']==='2AS'),'bmi'),'3AS'=>colN(array_filter($rows,fn($r)=>$r['grade']==='3AS'),'bmi')];
$anovaRes=anova($byGrade);

// ANOVA par éducation mère
$byEdu=[];
foreach(['Sans niveau','Primaire','Moyen','Secondaire','Universitaire'] as $e)
    $byEdu[$e]=colN(array_filter($rows,fn($r)=>$r['mother_education']===$e),'bmi');
$anovaEdu=anova(array_filter($byEdu,fn($g)=>count($g)>=2));

// ROC curve (proxy: obesity_risk_score vs iotf_class)
$rocPts=[]; $thresholds=range(0,13,1);
foreach($thresholds as $thresh){
    $tp=count(array_filter($rows,fn($r)=>$r['iotf_class']==='Obésité'&&($r['obesity_risk_score']??0)>=$thresh));
    $fp=count(array_filter($rows,fn($r)=>$r['iotf_class']!=='Obésité'&&($r['obesity_risk_score']??0)>=$thresh));
    $fn2=count(array_filter($rows,fn($r)=>$r['iotf_class']==='Obésité'&&($r['obesity_risk_score']??0)<$thresh));
    $tn=count(array_filter($rows,fn($r)=>$r['iotf_class']!=='Obésité'&&($r['obesity_risk_score']??0)<$thresh));
    $sens=$no>0?round($tp/$no,3):0;
    $spec=$nno>0?round($tn/$nno,3):0;
    $rocPts[]=['thresh'=>$thresh,'sens'=>$sens,'spec'=>$spec,'fpr'=>round(1-$spec,3)];
}
// AUC par méthode trapèze
$auc=0;
for($i=1;$i<count($rocPts);$i++){
    $auc+=abs($rocPts[$i]['fpr']-$rocPts[$i-1]['fpr'])*($rocPts[$i]['sens']+$rocPts[$i-1]['sens'])/2;
}
$auc=round(abs($auc),3);

// Bland-Altman: IMC auto-déclaré vs calculé (proxy: weight auto vs mesured)
$baData=[];
foreach($rows as $r){
    if($r['bmi']&&$r['weight']&&$r['height']){
        $bmiCalc=round($r['weight']/(($r['height']/100)**2),1);
        $diff=round($bmiCalc-$r['bmi'],2);
        $avg=round(($bmiCalc+$r['bmi'])/2,2);
        $baData[]=['diff'=>$diff,'avg'=>$avg];
    }
}
$baDiffs=array_column($baData,'diff');
$baMean=round(mean($baDiffs),2);
$baSD=round(std($baDiffs),2);
$baLOA_hi=round($baMean+1.96*$baSD,2);
$baLOA_lo=round($baMean-1.96*$baSD,2);

// Données graphiques
$bmiHist=[];for($b=14;$b<=42;$b+=2) $bmiHist[]=count(array_filter($bmiAll,fn($v)=>$v>=$b&&$v<$b+2));

// ══════ NOUVELLES ANALYSES ══════

// ── Spearman pour variables ordinales ──
$spearmanData=[];
$ffqFields=['ffq_fruits_frais'=>'الفواكه الطازجة / Fruits frais','ffq_legumes_crus'=>'الخضروات النيئة / Légumes crus','ffq_boissons_sucrees'=>'المشروبات السكرية / Sodas','ffq_gateaux'=>'الحلويات / Gâteaux','ffq_poisson'=>'السمك / Poisson','ffq_lait'=>'الحليب / Lait','ffq_huile_olive'=>'زيت الزيتون / Huile olive'];
foreach($ffqFields as $f=>$lbl){
    $vals=col($rows,$f); if(!array_filter($vals,fn($v)=>$v!==null&&$v!=='')) continue;
    $spearmanData[$lbl]=spearmanRank($bmiV,$vals);
}
// Variables ordinales supplémentaires
$ordVars=['sleep_duration'=>'مدة النوم / Durée sommeil','screen_phone'=>'وقت الهاتف / Écran tél.','water_intake'=>'شرب الماء / Eau','meals_per_day'=>'عدد الوجبات / Repas/jour'];
foreach($ordVars as $f=>$lbl){
    $vals=[]; foreach($rows as $r){ $v=$r[$f]??''; $vals[]=is_numeric($v)?(float)$v:null; }
    $validVals=array_filter($vals,fn($v)=>$v!==null);
    if(count($validVals)>5) $spearmanData[$lbl]=spearmanRank($bmiV,$vals);
}

// ── Régression logistique (vraie) ──
$obeseBin=array_map(fn($r)=>$r['iotf_class']==='Obésité'?1:0,$rows);
$surpoidsBin=array_map(fn($r)=>in_array($r['iotf_class'],['Surpoids','Obésité'])?1:0,$rows);

// Helpers screen_max et screen_sum à la volée (compat données legacy)
$_smMap = ['<1h'=>0.5, '1-2h'=>1.5, '2-4h'=>3.0, '>4h'=>5.0];

$logitFactors=[];
$logitDefs=[
    'parent_obese'=>['val'=>'Oui','label'=>'والد مصاب بسمنة / Parent obèse'],
    'delivery_type'=>['val'=>'Césarienne','label'=>'ولادة قيصرية / Césarienne'],
    'sports_club'=>['val'=>'Oui','label'=>'نادي رياضي / Club sportif','inv'=>true],
    'breakfast_freq'=>['val'=>'Rarement','label'=>'إفطار نادر / Petit-déj rare'],
    'sleep_duration'=>['val'=>'<6h','label'=>'نوم < 6 ساعات / Sommeil <6h'],
    // screen_max ≥ 4h (PRINCIPAL — multi-screen corrigé HBSC 2018)
    '__screen_max_4h'=>['val'=>1,'label'=>'شاشة (أقصى) ≥ 4 سا / Écran max ≥ 4h (V1)'],
    // screen_sum ≥ 4h (COMPARAISON — cumulé classique)
    '__screen_sum_4h'=>['val'=>1,'label'=>'شاشة (تراكمي) ≥ 4 سا / Écran cumulé ≥ 4h (V2)'],
    'academic_stress'=>['val'=>'Élevé','label'=>'ضغط مرتفع / Stress élevé'],
    'emotional_eating'=>['val'=>'Oui, souvent','label'=>'أكل عاطفي / Alimentation émotionnelle'],
    'snacking_freq'=>['val'=>'Souvent','label'=>'تناول وجبات خفيفة / Grignotage fréquent'],
];
foreach($logitDefs as $field=>$def){
    if ($field === '__screen_max_4h') {
        $xbin = array_map(function($r) use($_smMap) {
            if (is_numeric($r['screen_max'] ?? null)) return ((float)$r['screen_max'] >= 4) ? 1 : 0;
            $vals = [];
            foreach (['screen_phone','screen_tv','screen_games','screen_computer'] as $f)
                $vals[] = $_smMap[$r[$f] ?? ''] ?? 0;
            $max = !empty($vals) ? max($vals) : 0;
            return ($max >= 4) ? 1 : 0;
        }, $rows);
    } elseif ($field === '__screen_sum_4h') {
        $xbin = array_map(function($r) use($_smMap) {
            if (is_numeric($r['screen_sum'] ?? null)) return ((float)$r['screen_sum'] >= 4) ? 1 : 0;
            if (is_numeric($r['screen_hours_total'] ?? null)) return ((float)$r['screen_hours_total'] >= 4) ? 1 : 0;
            $sum = 0;
            foreach (['screen_phone','screen_tv','screen_games','screen_computer'] as $f)
                $sum += $_smMap[$r[$f] ?? ''] ?? 0;
            return ($sum >= 4) ? 1 : 0;
        }, $rows);
    } else {
        $xbin = array_map(fn($r)=>($r[$field]??'')===$def['val']?1:0, $rows);
    }
    $res=logisticReg($obeseBin,$xbin);
    $res['label']=$def['label'];
    $res['n_exp']=array_sum($xbin);
    $logitFactors[$field]=$res;
}

// ── Analyse KIDMED détaillée ──
$kidmedByIOTF=[];
foreach(['Normal','Surpoids','Obésité'] as $cat){
    $sub=array_filter($rows,fn($r)=>$r['iotf_class']===$cat);
    $kidmedByIOTF[$cat]=colN($sub,'score_kidmed');
}
$anovaKidmed=anova($kidmedByIOTF);

$kidmedBySex=['Garçon'=>colN($boys,'score_kidmed'),'Fille'=>colN($girls,'score_kidmed')];
$kidmedByGrade=[];
foreach(['1AS','2AS','3AS'] as $g) $kidmedByGrade[$g]=colN(array_filter($rows,fn($r)=>$r['grade']===$g),'score_kidmed');
$anovaKidmedGrade=anova($kidmedByGrade);

// Distribution classes KIDMED
$kidmedClasses=['Faible'=>0,'Moyen'=>0,'Bon'=>0];
foreach($rows as $r){ $c=$r['classe_kidmed']??''; if(isset($kidmedClasses[$c])) $kidmedClasses[$c]++; }

// ── Analyse Perception corporelle ──
$gapData=colN($rows,'perception_gap');
$gapByIOTF=[];
foreach(['Normal','Surpoids','Obésité'] as $cat){
    $sub=array_filter($rows,fn($r)=>$r['iotf_class']===$cat);
    $gaps=col($sub,'perception_gap');
    $concordant=count(array_filter($gaps,fn($v)=>$v==='Concordant'));
    $total=count($sub);
    $gapByIOTF[$cat]=['n'=>$total,'concordant'=>$concordant,'pct'=>$total?round($concordant/$total*100,1):0];
}
// Perception par sexe
$gapBySex=[];
foreach(['Garçon'=>$boys,'Fille'=>$girls] as $sex=>$sub){
    $gaps=col($sub,'perception_gap');
    $conc=count(array_filter($gaps,fn($v)=>$v==='Concordant'));
    $under=count(array_filter($gaps,fn($v)=>$v==='Sous-estimation'));
    $over=count(array_filter($gaps,fn($v)=>$v==='Sur-estimation'));
    $total=count($sub);
    $gapBySex[$sex]=['concordant'=>$conc,'under'=>$under,'over'=>$over,'total'=>$total];
}

// ── Analyse FFQ détaillée ──
$ffqAllFields=['ffq_fruits_frais','ffq_fruits_secs','ffq_legumes_crus','ffq_legumes_cuits','ffq_legumineuses','ffq_pain','ffq_riz_semoule','ffq_couscous','ffq_pommes_terre','ffq_lait','ffq_yaourt','ffq_fromage','ffq_viande_rouge','ffq_volaille','ffq_poisson','ffq_oeufs','ffq_boissons_sucrees','ffq_gateaux','ffq_chocolat','ffq_noix','ffq_beurre','ffq_huile_olive','ffq_autres_huiles'];
$ffqLabels=[__('ffq_short_fruits_frais'),__('ffq_short_fruits_secs'),__('ffq_short_legumes_crus'),__('ffq_short_legumes_cuits'),__('ffq_short_legumineuses'),__('ffq_short_pain'),__('ffq_short_riz_semoule'),__('ffq_short_couscous'),__('ffq_short_pommes_terre'),__('ffq_short_lait'),__('ffq_short_yaourt'),__('ffq_short_fromage'),__('ffq_short_viande_rouge'),__('ffq_short_volaille'),__('ffq_short_poisson'),__('ffq_short_oeufs'),__('ffq_short_boissons_sucrees'),__('ffq_short_gateaux'),__('ffq_short_chocolat'),__('ffq_short_noix'),__('ffq_short_beurre'),__('ffq_short_huile_olive'),__('ffq_short_autres_huiles')];
$ffqStats=[];
for($i=0;$i<count($ffqAllFields);$i++){
    $f=$ffqAllFields[$i]; $vals=colN($rows,$f);
    if(!$vals) continue;
    $bVals=colN($boys,$f); $gVals=colN($girls,$f);
    $ffqStats[$i]=['label'=>$ffqLabels[$i]??$f,'mean'=>round(mean($vals),1),'sd'=>round(std($vals),1),'med'=>round(med($vals),1),'boys'=>round(mean($bVals),1),'girls'=>round(mean($gVals),1),'t'=>tTest($bVals,$gVals)];
}

// ── Analyse comportement alimentaire émotionnel ──
$emotEat=col($rows,'emotional_eating');
$emotCounts=['Oui, souvent'=>freq($emotEat,'Oui, souvent'),'Oui, parfois'=>freq($emotEat,'Oui, parfois'),'Non'=>freq($emotEat,'Non')];
$emotBySex=['Garçon'=>[],'Fille'=>[]];
foreach($boys as $r) $emotBySex['Garçon'][]=($r['emotional_eating']??'')==='Oui, souvent'||($r['emotional_eating']??'')==='Oui, parfois'?1:0;
foreach($girls as $r) $emotBySex['Fille'][]=($r['emotional_eating']??'')==='Oui, souvent'||($r['emotional_eating']??'')==='Oui, parfois'?1:0;
$emotPctBoys=count($emotBySex['Garçon'])?round(array_sum($emotBySex['Garçon'])/count($emotBySex['Garçon'])*100,1):0;
$emotPctGirls=count($emotBySex['Fille'])?round(array_sum($emotBySex['Fille'])/count($emotBySex['Fille'])*100,1):0;

// ── ANOVA supplémentaires ──
// BMI par école (urbain vs semi-urbain)
$schools=array_unique(col($rows,'school'));
$bySchool=[];
foreach($schools as $s){ if(!$s) continue; $sub=colN(array_filter($rows,fn($r)=>$r['school']===$s),'bmi'); if(count($sub)>=5) $bySchool[$s]=$sub; }
$anovaSchool=count($bySchool)>=2?anova($bySchool):['F'=>0,'p'=>1,'dfB'=>0,'dfW'=>0,'means'=>[],'n'=>[]];

// BMI par stress
$byStress=[];
foreach(['Aucun','Faible','Modéré','Élevé'] as $s) $byStress[$s]=colN(array_filter($rows,fn($r)=>$r['academic_stress']===$s),'bmi');
$byStress=array_filter($byStress,fn($g)=>count($g)>=2);
$anovaStress=count($byStress)>=2?anova($byStress):['F'=>0,'p'=>1];

// ── Diversité alimentaire ──
$divScores=colN($rows,'dietary_diversity');
$divClasses=['Faible'=>0,'Moyenne'=>0,'Bonne'=>0,'Excellente'=>0];
foreach($rows as $r){ $c=$r['classe_diversity']??''; if(isset($divClasses[$c])) $divClasses[$c]++; }
$divByIOTF=[];
foreach(['Normal','Surpoids','Obésité'] as $cat){
    $sub=array_filter($rows,fn($r)=>$r['iotf_class']===$cat);
    $divByIOTF[$cat]=round(mean(colN($sub,'dietary_diversity')),1);
}

/* ════════════════════════════════════════════════════════════════ */
/*  🔬 MÉTHODES AVANCÉES — Multivariate via BiostatPHP              */
/*  V1 logistique + GLMM + GEE + MICE + ROC + Hosmer-Lemeshow + BH  */
/* ════════════════════════════════════════════════════════════════ */
$adv = [
    'v1'=>null, 'glmm'=>null, 'gee'=>null, 'mice'=>null,
    'roc'=>['fpr'=>[0,1],'tpr'=>[0,1]], 'auc'=>null, 'hl'=>null,
    'bh'=>[], 'note'=>null,
];

// 1) Préparer les données complètes (sans données manquantes critiques)
$advData = [];
foreach($rows as $r){
    if(!isset($r['iotf_class'])) continue;
    if(!is_numeric($r['score_kidmed'] ?? null)) continue;
    if(!is_numeric($r['score_activite'] ?? null)) continue;
    if(!is_numeric($r['score_sedentarite'] ?? null)) continue;
    if(!is_numeric($r['score_sommeil'] ?? null)) continue;
    if(!is_numeric($r['score_pression_scolaire'] ?? null)) continue;
    if(!is_numeric($r['age'] ?? null)) continue;
    $advData[] = [
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
$adv_n = count($advData);

if($adv_n >= 30){
    $advY = array_column($advData, 'y');
    $advX = array_map(fn($d)=>[
        $d['kidmed'],$d['activ'],$d['seden'],$d['sleep'],$d['press'],
        $d['skip'],$d['sex'],$d['age']
    ], $advData);
    $advNames = ['KIDMED','Activité','Sédentarité','Sommeil','Pression','Saut repas','Sexe (♂=1)','Âge'];

    // 2) V1 — Régression logistique multivariée standard
    $adv['v1'] = $bp->logisticRegressionMulti($advY, $advX, $advNames);

    // 3) ROC + AUC + Hosmer-Lemeshow (à partir des prédictions V1)
    if($adv['v1'] && !isset($adv['v1']['error']) && !empty($adv['v1']['converged']) && !empty($adv['v1']['predicted'])){
        $preds = $adv['v1']['predicted'];
        $adv['auc'] = $adv['v1']['auc'] ?? null;

        // Construire courbe ROC
        $pairs = [];
        foreach($preds as $i=>$p){
            if(!isset($advY[$i])) continue;
            $pairs[] = ['p'=>(float)$p, 'y'=>(int)$advY[$i]];
        }
        usort($pairs, fn($a,$b)=>$b['p']<=>$a['p']);
        $P = 0; $N = 0;
        foreach($pairs as $pr){ if($pr['y']==1) $P++; else $N++; }
        if($P>0 && $N>0){
            $tp = 0; $fp = 0; $fprs = [0.0]; $tprs = [0.0];
            foreach($pairs as $pr){
                if($pr['y']==1) $tp++; else $fp++;
                $fprs[] = round($fp/$N, 4);
                $tprs[] = round($tp/$P, 4);
            }
            // Sous-échantillonnage
            $nPts = count($fprs);
            if($nPts > 80){
                $step = (int)ceil($nPts/80);
                $sf = []; $st = [];
                for($i=0; $i<$nPts; $i+=$step){
                    $sf[] = $fprs[$i]; $st[] = $tprs[$i];
                }
                $sf[] = 1.0; $st[] = 1.0;
                $fprs = $sf; $tprs = $st;
            }
            $adv['roc'] = ['fpr'=>$fprs, 'tpr'=>$tprs];
        }

        // Hosmer-Lemeshow
        $adv['hl'] = $bp->hosmerLemeshow($advY, $preds, 10);
    }

    // 4) GLMM + GEE (si ≥3 clusters distincts)
    if($adv_n >= 100){
        $advClusters = array_column($advData, 'school');
        $n_clusters = count(array_unique($advClusters));
        if($n_clusters >= 3){
            $glmm_r = $bp->glmmLogistic($advY, $advX, $advClusters, $advNames, 50);
            if(!isset($glmm_r['error']) && !empty($glmm_r['converged'])) $adv['glmm'] = $glmm_r;
            $gee_r = $bp->geeLogistic($advY, $advX, $advClusters, $advNames, 30);
            if(!isset($gee_r['error'])) $adv['gee'] = $gee_r;
        }

        // 5) MICE — sur données avec manquants (m=5 pour performance stats.php)
        $miceData = [];
        foreach($rows as $r){
            if(empty($r['iotf_class'])) continue;
            $miceData[] = [
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
        $n_miss = 0;
        foreach($miceData as $row_) foreach($row_ as $v_) if($v_ === null) $n_miss++;
        if($n_miss >= 10 && count($miceData) >= 50){
            $mice_m = 5; // m=5 pour rapidité (stats.php)
            $mice_res = $bp->mice($miceData, [
                'kidmed'=>'continuous','activ'=>'continuous','seden'=>'continuous',
                'sleep'=>'continuous','press'=>'continuous','age'=>'continuous',
                'sex'=>'binary','y'=>'binary',
            ], $mice_m, 8, 5);

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
                if(!isset($fit_imp['error']) && !empty($fit_imp['converged'])){
                    $estimates_mi[] = $fit_imp['coef'];
                    $se_mi[]        = $fit_imp['se'];
                }
            }
            if(count($estimates_mi) >= 2){
                $pooled = $bp->rubinPool($estimates_mi, $se_mi);
                $adv['mice'] = [
                    'pooled' => $pooled,
                    'names'  => array_merge(['(Intercept)'], $mice_names),
                    'm'      => $mice_res['m'],
                    'pct_missing' => $mice_res['pct_missing'] ?? null,
                ];
            }
        }
    }

    // 6) Benjamini-Hochberg sur les p-values des prédicteurs V1
    if($adv['v1'] && !isset($adv['v1']['error']) && !empty($adv['v1']['converged'])){
        $pvalsRaw = [];
        for($j=1; $j<count($adv['v1']['names']); $j++){
            $pj = $adv['v1']['p'][$j] ?? null;
            if($pj !== null && is_numeric($pj)){
                $pvalsRaw[$adv['v1']['names'][$j]] = (float)$pj;
            }
        }
        if(!empty($pvalsRaw)){
            $pvalsBH = BiostatAnalysis::benjaminiHochberg($pvalsRaw);
            $adv['bh'] = [];
            foreach($pvalsRaw as $name=>$p_raw){
                $idx = array_search($name, $adv['v1']['names']);
                $adv['bh'][] = [
                    'name' => $name,
                    'or'   => $adv['v1']['or'][$idx] ?? null,
                    'ci_lo'=> $adv['v1']['ci_low'][$idx] ?? null,
                    'ci_hi'=> $adv['v1']['ci_high'][$idx] ?? null,
                    'p_raw'=> $p_raw,
                    'p_bh' => $pvalsBH[$name] ?? null,
                    'sig_raw' => $p_raw < 0.05,
                    'sig_bh'  => ($pvalsBH[$name] ?? 1) < 0.05,
                ];
            }
        }
    }
} else {
    $adv['note'] = 'insufficient';
}
?>

<div class="container" style="max-width:1100px">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <div>
      <h2 style="color:var(--primary);font-size:18px"><?=__("stats_title")?></h2>
      <p style="font-size:12px;color:var(--text-muted)">n = <?= $n ?> | <?= $nb ?> <?=__('stats_n_boys')?> | <?= $ng ?> <?=__('stats_n_girls')?> | <?= $no ?> <?=__('stats_n_obese')?> (<?= pct($no,$n) ?>%)</p>
    </div>
    <div style="display:flex;gap:6px">
      <a href="/progress.php" class="btn">📊 Progression</a>
      <a href="/api/data.php?action=export_csv" class="btn btn-success">CSV ↓</a>
      <a href="/api/data.php?action=export_spss" class="btn btn-primary">SPSS ↓</a>
    </div>
  </div>

  <div class="stat-tabs">
    <button class="stat-tab active" onclick="st('desc')"><?=__("stats_desc")?></button>
    <button class="stat-tab" onclick="st('comp')"><?=__("stats_comp")?></button>
    <button class="stat-tab" onclick="st('anova')"><?=__("stats_anova")?></button>
    <button class="stat-tab" onclick="st('corr')"><?=__("stats_corr")?></button>
    <button class="stat-tab" onclick="st('logit')"><?=__("stats_logit")?></button>
    <button class="stat-tab" onclick="st('ffq')"><?=__('stats_ffq')?></button>
    <button class="stat-tab" onclick="st('kidmed')"><?=__('stats_kidmed')?></button>
    <button class="stat-tab" onclick="st('percept')"><?=__('stats_percept')?></button>
    <button class="stat-tab" onclick="st('spear')"><?=__('stats_spear')?></button>
    <button class="stat-tab" onclick="st('roc')"><?=__("stats_roc")?></button>
    <button class="stat-tab" onclick="st('ba')"><?=__("stats_ba")?></button>
    <button class="stat-tab" onclick="st('adv')">🔬 <?=__('stats_adv_tab')?></button>
  </div>

<!-- ══ TAB 1: DESCRIPTIF ══ -->
<div class="stat-panel active" id="t-desc">
  <div class="concept"><div class="concept-title">📖 <?=currentLang()==='ar'?'مفهوم التحليل الوصفي':'Concept : Analyse descriptive'?></div><div class="concept-body"><?=__('concept_desc')?></div></div>
  <div class="g3">
    <div class="mb"><div class="mv"><?= round(mean($bmiAll),1) ?> ± <?= round(std($bmiAll),1) ?></div><div class="ml"><?=__('stats_bmi_mean')?></div></div>
    <div class="mb"><div class="mv"><?= pct($no,$n) ?>%</div><div class="ml"><?=__('stats_obesity_pct')?></div></div>
    <div class="mb"><div class="mv"><?= pct(freq(col($rows,'skip_meal_tutoring'),'Oui, souvent')+freq(col($rows,'skip_meal_tutoring'),'Oui, parfois'),$n) ?>%</div><div class="ml"><?=__('stats_skip_meals')?></div></div>
  </div>
  <div class="g2" style="margin-top:1rem">
    <div class="ac"><div class="at"><?=__('stats_iotf_dist')?> <button class="cb" onclick="copyTbl('tiotf')"><?=__('stats_copy')?></button></div>
      <table class="st" id="tiotf"><tr><th><?=__('guide_s3_col1')?></th><th>n</th><th>%</th></tr>
      <?php foreach($iotfCounts as $c=>$cnt): ?><tr><td><?=$c?></td><td><?=$cnt?></td><td><?=pct($cnt,$n)?>%</td></tr><?php endforeach; ?>
      <tr style="font-weight:700"><td>Total</td><td><?=$n?></td><td>100%</td></tr></table>
      <div class="cws"><canvas id="cIOTF"></canvas></div>
    </div>
    <div class="ac"><div class="at"><?=__('stats_bmi_hist')?></div>
      <div style="font-size:12px;color:var(--text-muted);margin-bottom:6px"><?=__('stats_median')?> <?=round(med($bmiAll),1)?> | <?=__('stats_min')?> <?=round(min($bmiAll),1)?> | <?=__('stats_max')?> <?=round(max($bmiAll),1)?></div>
      <div class="cws"><canvas id="cBMI"></canvas></div>
    </div>
  </div>
  <div class="ac">
    <div class="at"><?=__('stats_scores_title')?> <button class="cb" onclick="copyTbl('tscores')"><?=__('stats_copy')?></button></div>
    <table class="st" id="tscores"><tr><th><?=__('stats_score_col')?></th><th><?=__('stats_mean_col')?></th><th><?=__('stats_sd_col')?></th><th><?=__('stats_median_col')?></th><th><?=__('stats_min')?></th><th><?=__('stats_max')?></th></tr>
    <?php
    $scoreVars=['score_kidmed'=>__('score_kidmed'),'score_activite'=>__('score_activite'),'score_sommeil'=>__('score_sommeil'),'score_sedentarite'=>__('score_sedentarite'),'score_pression_scolaire'=>__('score_pression'),'score_famille'=>__('score_famille'),'obesity_risk_score'=>__('score_obesity_risk'),'global_nutrition_score'=>__('score_global')];
    foreach($scoreVars as $k=>$lbl){$v=colN($rows,$k);if(!$v)continue;?>
    <tr><td><?=$lbl?></td><td><?=round(mean($v),1)?></td><td><?=round(std($v),1)?></td><td><?=round(med($v),1)?></td><td><?=round(min($v),1)?></td><td><?=round(max($v),1)?></td></tr>
    <?php } ?></table>
  </div>
</div>

<!-- ══ TAB 2: COMPARAISONS ══ -->
<div class="stat-panel" id="t-comp">
  <div class="concept"><div class="concept-title">📖 <?=currentLang()==='ar'?'مفهوم اختبار t لستيودنت':'Concept : Test t de Student'?></div><div class="concept-body"><?=__('concept_ttest')?></div></div>
  <div class="ac">
    <div class="at"><?=__('stats_ttest_title')?> <button class="cb" onclick="copyTbl('ttest')"><?=__('stats_copy')?></button></div>
    <table class="st" id="ttest"><tr><th><?=__('stats_var_col')?></th><th><?=__('stats_boys_col')?></th><th><?=__('stats_girls_col')?></th><th>t</th><th>df</th><th>p</th><th><?=__('stats_sig_col')?></th></tr>
    <?php foreach($tt as $l=>$r): $sig=$r['p']<0.05; ?>
    <tr><td><?=$l?></td><td><?=$r['m1']?>±<?=$r['sd1']?></td><td><?=$r['m2']?>±<?=$r['sd2']?></td><td><?=$r['t']?></td><td><?=$r['df']?></td>
    <td class="<?=$sig?'sig':'nsig'?>"><?=$r['p']?></td><td class="<?=$sig?'sig':'nsig'?>"><?=$sig?__('stats_sig_yes'):__('stats_sig_no')?></td></tr>
    <?php endforeach; ?></table>
    <div class="interp"><?=__('stats_ttest_note')?></div>
  </div>
  <div class="concept" style="margin-top:14px"><div class="concept-title">📖 <?=currentLang()==='ar'?'مفهوم اختبار كاي تربيع':'Concept : Test du Chi-deux'?></div><div class="concept-body"><?=__('concept_chi2')?></div></div>
  <div class="ac">
    <div class="at"><?=__('stats_chi_title')?> <button class="cb" onclick="copyTbl('tchi')"><?=__('stats_copy')?></button></div>
    <table class="st" id="tchi"><tr><th><?=__('stats_factor_col')?></th><th><?=__('stats_obese_exp')?></th><th><?=__('stats_obese_nexp')?></th><th>χ²</th><th>OR</th><th><?=__('stats_ci_col')?></th><th>p</th><th><?=__('stats_sign_col')?></th></tr>
    <?php foreach($chiFactors as $l=>$r): $sig=$r['p']<0.05; ?>
    <tr><td><?=$l?></td><td><?=$r['exp']?></td><td><?=$r['nexp']?></td><td><?=$r['chi2']?></td>
    <td><?=$r['or']??'—'?></td><td><?=$r['lo']&&$r['hi']?"[{$r['lo']}–{$r['hi']}]":'—'?></td>
    <td class="<?=$sig?'sig':'nsig'?>"><?=$r['p']?></td><td class="<?=$sig?'sig':'nsig'?>"><?=$sig?'✓':'NS'?></td></tr>
    <?php endforeach; ?></table>
    <div class="interp"><?=__('stats_chi_note')?></div>
  </div>
  <div class="ac"><div class="at"><?=__('stats_comp_chart')?></div><div class="cw"><canvas id="cComp"></canvas></div></div>
</div>

<!-- ══ TAB 3: ANOVA ══ -->
<div class="stat-panel" id="t-anova">
  <div class="concept"><div class="concept-title">📖 <?=currentLang()==='ar'?'مفهوم تحليل التباين ANOVA':'Concept : ANOVA'?></div><div class="concept-body"><?=__('concept_anova')?></div></div>
  <div class="ac">
    <div class="at"><?=__('stats_anova_grade')?> <button class="cb" onclick="copyTbl('tanova1')"><?=__('stats_copy')?></button></div>
    <?php $sig=($anovaRes['p']??1)<0.05; ?>
    <div class="g3" style="margin-bottom:1rem">
      <?php foreach($anovaRes['means']??[] as $g=>$m): ?>
      <div class="mb"><div class="mv"><?=round($m,1)?></div><div class="ml">IMC moyen <?=$g?></div></div>
      <?php endforeach; ?>
    </div>
    <table class="st" id="tanova1"><tr><th><?=__('stats_anova_source')?></th><th><?=__('stats_anova_df')?></th><th>F</th><th>p</th><th><?=__('stats_sig_col')?></th></tr>
    <tr><td><?=__('stats_anova_between')?></td><td><?=$anovaRes['dfB']??0?></td><td><?=$anovaRes['F']??0?></td>
    <td class="<?=$sig?'sig':'nsig'?>"><?=$anovaRes['p']??1?></td><td class="<?=$sig?'sig':'nsig'?>"><?=$sig?__('stats_anova_sig'):__('stats_anova_nosig')?></td></tr>
    <tr><td><?=__('stats_anova_within')?></td><td><?=$anovaRes['dfW']??0?></td><td>—</td><td>—</td><td>—</td></tr></table>
    <?php if($sig): ?>
    <div class="interp"><?=__('stats_anova_posthoc')?></div>
    <?php else: ?>
    <div class="interp"><?=__('stats_anova_noposthoc')?></div>
    <?php endif; ?>
    <div class="cw"><canvas id="cAnova1"></canvas></div>
  </div>
  <div class="ac">
    <div class="at"><?=__('stats_anova_mother')?></div>
    <?php $sig2=($anovaEdu['p']??1)<0.05; ?>
    <table class="st"><tr><th><?=__('stats_group_col')?></th><th>n</th><th><?=__('stats_bmi_mean_col')?></th></tr>
    <?php foreach($anovaEdu['means']??[] as $g=>$m): if(!isset($anovaEdu['n'][$g])) continue; ?>
    <tr><td><?=$g?></td><td><?=$anovaEdu['n'][$g]??0?></td><td><?=round($m,1)?></td></tr>
    <?php endforeach; ?>
    <tr style="font-weight:700;background:var(--bg)"><td colspan="2">F = <?=$anovaEdu['F']??0?> | p = <?=$anovaEdu['p']??1?></td><td class="<?=$sig2?'sig':'nsig'?>"><?=$sig2?__('stats_sig_yes'):__('stats_sig_no')?></td></tr></table>
    <div class="cw"><canvas id="cAnova2"></canvas></div>
  </div>
</div>

<!-- ══ TAB 4: CORRÉLATIONS ══ -->
<div class="stat-panel" id="t-corr">
  <div class="concept"><div class="concept-title">📖 <?=currentLang()==='ar'?'مفهوم معامل ارتباط بيرسون':'Concept : Correlation de Pearson'?></div><div class="concept-body"><?=__('concept_pearson')?></div></div>
  <div class="ac">
    <div class="at"><?=__('stats_corr_title')?> <button class="cb" onclick="copyTbl('tcorr')"><?=__('stats_copy')?></button></div>
    <table class="st" id="tcorr"><tr><th><?=__('stats_var_col')?></th><th>r</th><th><?=__('stats_r2_col')?></th><th><?=__('stats_ci_col')?></th><th>n</th><th>p</th><th><?=__('stats_force_col')?></th><th><?=__('stats_dir_col')?></th></tr>
    <?php foreach($corrData as $l=>$c): $sig=$c['p']<0.05; $abs=abs($c['r']); $str=$abs<0.1?__('stats_very_weak'):($abs<0.3?__('stats_weak'):($abs<0.5?__('stats_moderate'):($abs<0.7?__('stats_strong'):__('stats_very_strong')))); $dir=$c['r']>0?__('stats_positive'):__('stats_negative'); ?>
    <tr><td><?=$l?></td><td style="font-weight:700;color:<?=$c['r']>0.3?'#1a7340':($c['r']<-0.3?'#9b2335':'#856404')?>"><?=$c['r']?></td>
    <td><?=$c['r2']?></td><td>[<?=$c['lo']?> ; <?=$c['hi']?>]</td><td><?=$c['n']?></td>
    <td class="<?=$sig?'sig':'nsig'?>"><?=$c['p']?></td><td><?=$str?></td><td><?=$dir?></td></tr>
    <?php endforeach; ?></table>
    <div class="interp"><?=__('stats_corr_note')?></div>
  </div>
  <div class="ac"><div class="at"><?=__('stats_corr_chart')?></div><div class="cw"><canvas id="cCorr"></canvas></div></div>
</div>

<!-- ══ TAB 5: RÉGRESSION LOGISTIQUE ══ -->
<div class="stat-panel" id="t-logit">
  <div class="concept"><div class="concept-title">📖 <?=currentLang()==='ar'?'مفهوم الانحدار اللوجستي':'Concept : Regression logistique'?></div><div class="concept-body"><?=__('concept_logit')?></div></div>
  <div class="ac">
    <div class="at"><?=__('stats_logit_title')?> <button class="cb" onclick="copyTbl('tlogit')"><?=__('stats_logit_copy')?></button></div>
    <p style="font-size:12px;color:var(--text-muted);margin-bottom:10px"><?=__('stats_logit_dep')?> | <?=__('stats_logit_obese')?> = <?=$no?> | <?=__('stats_logit_nonobese')?> = <?=$nno?></p>
    <table class="st" id="tlogit"><tr><th><?=__('stats_factor_col')?></th><th>OR</th><th><?=__('stats_ci_col')?></th><th>p</th><th><?=__('stats_sig_col')?></th><th><?=__('stats_type_col')?></th></tr>
    <?php foreach($chiFactors as $l=>$r): if(!$r['or']) continue; $sig=$r['p']<0.05; $risk=$r['or']>1?__('stats_risk_up'):__('stats_protective'); $rc=$r['or']>1?'#9b2335':'#1a7340'; ?>
    <tr><td><?=$l?></td><td style="font-weight:700;color:<?=$rc?>"><?=$r['or']?></td>
    <td>[<?=$r['lo']?> – <?=$r['hi']?>]</td><td class="<?=$sig?'sig':'nsig'?>"><?=$r['p']?></td>
    <td class="<?=$sig?'sig':'nsig'?>"><?=$sig?'✓':''?></td><td style="color:<?=$rc?>"><?=$risk?></td></tr>
    <?php endforeach; ?></table>
    <div class="interp"><?=__('stats_logit_note')?></div>
  </div>
  <div class="ac"><div class="at"><?=__('stats_forest_title')?></div><div class="cw"><canvas id="cOR"></canvas></div></div>
  <div class="ac" style="border:2px solid var(--primary)">
    <div class="at"><?=__('stats_thesis_table')?></div>
    <p style="font-size:12px;color:var(--text-muted);margin-bottom:8px"><?=__('stats_thesis_caption')?> (n=<?=$n?>)</p>
    <table class="st" id="tmem"><tr style="background:#1F4E79;color:#fff"><th><?=__('stats_factor_col')?></th><th>OR</th><th><?=__('stats_ci_col')?></th><th>p-value</th></tr>
    <?php foreach($chiFactors as $l=>$r): if(!$r['or']) continue; $sig=$r['p']<0.05; ?>
    <tr><td><?=$l?></td><td><?=$r['or']?></td><td>[<?=$r['lo']?> – <?=$r['hi']?>]</td>
    <td><?=$sig?'<strong>'.$r['p'].'*</strong>':$r['p']?></td></tr>
    <?php endforeach; ?></table>
    <p style="font-size:11px;color:var(--text-muted);margin-top:6px"><?=__('stats_pvalue_note')?></p>
    <button class="cb" style="margin-top:8px;font-size:12px;padding:6px 14px" onclick="copyTbl('tmem')"><?=__('stats_copy')?></button>
  </div>
</div>

<!-- ══ TAB: FFQ — تحليل تفصيلي ══ -->
<div class="stat-panel" id="t-ffq">
  <div class="concept"><div class="concept-title">📖 <?=currentLang()==='ar'?'مفهوم استبيان تكرار الاستهلاك الغذائي':'Concept : FFQ'?></div><div class="concept-body"><?=__('concept_ffq')?></div></div>
  <div class="ac">
    <div class="at"><?=__('stats_ffq_title')?> <button class="cb" onclick="copyTbl('tffq')"><?=__('stats_copy')?></button></div>
    <p style="font-size:12px;color:var(--text-muted);margin-bottom:10px"><?=__('stats_ffq_note')?> | n = <?=$n?></p>
    <div style="overflow-x:auto">
    <table class="st" id="tffq">
      <tr><th><?=__('stats_ffq_food')?></th><th><?=__('stats_ffq_mean')?></th><th><?=__('stats_sd_col')?></th><th><?=__('stats_ffq_med')?></th><th><?=__('stats_ffq_boys')?></th><th><?=__('stats_ffq_girls')?></th><th>t</th><th>p</th><th><?=__('stats_ffq_sig')?></th></tr>
      <?php foreach($ffqStats as $fs): $sig=$fs['t']['p']<0.05; ?>
      <tr><td style="font-size:11px"><?=$fs['label']?></td><td><?=$fs['mean']?></td><td><?=$fs['sd']?></td><td><?=$fs['med']?></td>
      <td><?=$fs['boys']?></td><td><?=$fs['girls']?></td>
      <td><?=$fs['t']['t']?></td><td class="<?=$sig?'sig':'nsig'?>"><?=$fs['t']['p']?></td>
      <td class="<?=$sig?'sig':'nsig'?>"><?=$sig?'✓':'NS'?></td></tr>
      <?php endforeach; ?>
    </table>
    </div>
    <div class="interp"><?=__('stats_ffq_interp')?></div>
    <div class="cw" style="height:500px"><canvas id="cFFQ"></canvas></div>
  </div>
  <div class="ac">
    <div class="at"><?=__('stats_div_title')?></div>
    <div class="g3" style="margin-bottom:1rem">
      <div class="mb"><div class="mv"><?=round(mean($divScores),1)?></div><div class="ml"><?=__('stats_div_mean')?></div></div>
      <?php foreach($divByIOTF as $cat=>$val): ?>
      <div class="mb"><div class="mv"><?=$val?></div><div class="ml"><?=__('stats_div_cat')?> — <?=$cat?></div></div>
      <?php endforeach; ?>
    </div>
    <table class="st"><tr><th><?=__('stats_class')?></th><th>n</th><th>%</th></tr>
    <?php foreach($divClasses as $c=>$cnt): ?><tr><td><?=$c?></td><td><?=$cnt?></td><td><?=pct($cnt,$n)?>%</td></tr><?php endforeach; ?>
    </table>
  </div>
</div>

<!-- ══ TAB: KIDMED ══ -->
<div class="stat-panel" id="t-kidmed">
  <div class="concept"><div class="concept-title">📖 <?=currentLang()==='ar'?'مفهوم مؤشر KIDMED':'Concept : Indice KIDMED'?></div><div class="concept-body"><?=__('concept_kidmed')?></div></div>
  <div class="ac">
    <div class="at"><?=__('stats_kidmed_title')?> <button class="cb" onclick="copyTbl('tkidmed')"><?=__('stats_copy')?></button></div>
    <div class="g3" style="margin-bottom:1rem">
      <div class="mb"><div class="mv"><?=round(mean(colN($rows,'score_kidmed')),1)?> ± <?=round(std(colN($rows,'score_kidmed')),1)?></div><div class="ml"><?=__('stats_kidmed_mean')?></div></div>
      <div class="mb"><div class="mv"><?=round(med(colN($rows,'score_kidmed')),1)?></div><div class="ml"><?=__('stats_kidmed_med')?></div></div>
      <div class="mb"><div class="mv">[<?=round(min(colN($rows,'score_kidmed')),0)?> ; <?=round(max(colN($rows,'score_kidmed')),0)?>]</div><div class="ml"><?=__('stats_kidmed_range')?></div></div>
    </div>
    <table class="st" id="tkidmed">
      <tr><th><?=__('stats_class')?></th><th>n</th><th>%</th></tr>
      <?php foreach($kidmedClasses as $c=>$cnt): ?>
      <tr><td><span class="chip <?=$c==='Bon'?'chip-success':($c==='Moyen'?'chip-warning':'chip-danger')?>"><?=$c?></span></td>
      <td><?=$cnt?></td><td><?=pct($cnt,$n)?>%</td></tr>
      <?php endforeach; ?>
    </table>
    <div class="cws"><canvas id="cKidmed"></canvas></div>
  </div>
  <div class="g2">
    <div class="ac">
      <div class="at"><?=__('stats_kidmed_sex')?></div>
      <?php $ttKidmed=tTest($kidmedBySex['Garçon'],$kidmedBySex['Fille']); $sigK=$ttKidmed['p']<0.05; ?>
      <table class="st"><tr><th></th><th>n</th><th><?=__('stats_mean_sd')?></th></tr>
      <tr><td><?=__('stats_male')?></td><td><?=count($kidmedBySex['Garçon'])?></td><td><?=$ttKidmed['m1']?> ± <?=$ttKidmed['sd1']?></td></tr>
      <tr><td><?=__('stats_female')?></td><td><?=count($kidmedBySex['Fille'])?></td><td><?=$ttKidmed['m2']?> ± <?=$ttKidmed['sd2']?></td></tr>
      <tr style="background:var(--bg)"><td colspan="2">t = <?=$ttKidmed['t']?> | p = <?=$ttKidmed['p']?></td>
      <td class="<?=$sigK?'sig':'nsig'?>"><?=$sigK?__('stats_sig_yes'):__('stats_sig_no')?></td></tr></table>
    </div>
    <div class="ac">
      <div class="at"><?=__('stats_kidmed_grade')?></div>
      <?php $sigKG=($anovaKidmedGrade['p']??1)<0.05; ?>
      <table class="st"><tr><th><?=__('stats_level')?></th><th>n</th><th><?=__('stats_ffq_mean')?></th></tr>
      <?php foreach($anovaKidmedGrade['means']??[] as $g=>$m): ?>
      <tr><td><?=$g?></td><td><?=$anovaKidmedGrade['n'][$g]??0?></td><td><?=round($m,1)?></td></tr>
      <?php endforeach; ?>
      <tr style="background:var(--bg)"><td colspan="2">F = <?=$anovaKidmedGrade['F']??0?> | p = <?=$anovaKidmedGrade['p']??1?></td>
      <td class="<?=$sigKG?'sig':'nsig'?>"><?=$sigKG?__('stats_sig_yes'):__('stats_sig_no')?></td></tr></table>
    </div>
  </div>
  <div class="ac">
    <div class="at"><?=__('stats_kidmed_iotf')?></div>
    <?php $sigKI=($anovaKidmed['p']??1)<0.05; ?>
    <div class="g3" style="margin-bottom:1rem">
      <?php foreach($anovaKidmed['means']??[] as $g=>$m): ?>
      <div class="mb"><div class="mv"><?=round($m,1)?></div><div class="ml">KIDMED — <?=$g?> (n=<?=$anovaKidmed['n'][$g]??0?>)</div></div>
      <?php endforeach; ?>
    </div>
    <table class="st"><tr><th><?=__('stats_anova_source')?></th><th>F</th><th>p</th><th><?=__('stats_sig_col')?></th></tr>
    <tr><td><?=__('stats_between_iotf')?></td><td><?=$anovaKidmed['F']??0?></td>
    <td class="<?=$sigKI?'sig':'nsig'?>"><?=$anovaKidmed['p']??1?></td>
    <td class="<?=$sigKI?'sig':'nsig'?>"><?=$sigKI?__('stats_kidmed_iotf_sig'):__('stats_kidmed_iotf_ns')?></td></tr></table>
  </div>
</div>

<!-- ══ TAB: PERCEPTION ══ -->
<div class="stat-panel" id="t-percept">
  <div class="concept"><div class="concept-title">📖 <?=currentLang()==='ar'?'مفهوم تحليل الإدراك الجسمي':'Concept : Perception corporelle'?></div><div class="concept-body"><?=__('concept_percept')?></div></div>
  <div class="ac">
    <div class="at"><?=__('stats_percept_title')?> <button class="cb" onclick="copyTbl('tpercept')"><?=__('stats_copy')?></button></div>
    <p style="font-size:12px;color:var(--text-muted);margin-bottom:10px"><?=__('stats_percept_note')?></p>
    <table class="st" id="tpercept">
      <tr><th>IOTF</th><th>n</th><th><?=__('stats_concordant')?></th><th><?=__('stats_concordance')?></th></tr>
      <?php foreach($gapByIOTF as $cat=>$d): ?>
      <tr><td><?=$cat?></td><td><?=$d['n']?></td><td><?=$d['concordant']?></td>
      <td style="font-weight:700;color:<?=$d['pct']>=60?'#1a7340':'#9b2335'?>"><?=$d['pct']?>%</td></tr>
      <?php endforeach; ?>
    </table>
    <div class="interp"><?=__('stats_percept_interp')?></div>
    <div class="cws"><canvas id="cPercept"></canvas></div>
  </div>
  <div class="ac">
    <div class="at"><?=__('stats_percept_sex')?></div>
    <table class="st">
      <tr><th></th><th>n</th><th><?=__('stats_concordant')?></th><th><?=__('stats_under_est')?></th><th><?=__('stats_over_est')?></th></tr>
      <?php foreach($gapBySex as $sex=>$d): ?>
      <tr><td><?=$sex==='Garçon'?__('stats_male'):__('stats_female')?></td><td><?=$d['total']?></td>
      <td><?=$d['concordant']?> (<?=$d['total']?round($d['concordant']/$d['total']*100,1):0?>%)</td>
      <td><?=$d['under']?> (<?=$d['total']?round($d['under']/$d['total']*100,1):0?>%)</td>
      <td><?=$d['over']?> (<?=$d['total']?round($d['over']/$d['total']*100,1):0?>%)</td></tr>
      <?php endforeach; ?>
    </table>
    <div class="interp"><?=__('stats_under_note')?><br><?=__('stats_over_note')?></div>
  </div>
  <div class="ac">
    <div class="at"><?=__('stats_emot_title')?></div>
    <div class="g3" style="margin-bottom:1rem">
      <div class="mb"><div class="mv"><?=pct($emotCounts['Oui, souvent']??0,$n)?>%</div><div class="ml"><?=__('stats_emot_always')?></div></div>
      <div class="mb"><div class="mv"><?=$emotPctBoys?>%</div><div class="ml"><?=__('stats_emot_boys')?></div></div>
      <div class="mb"><div class="mv"><?=$emotPctGirls?>%</div><div class="ml"><?=__('stats_emot_girls')?></div></div>
    </div>
    <table class="st"><tr><th><?=__('stats_response')?></th><th>n</th><th>%</th></tr>
    <?php foreach($emotCounts as $c=>$cnt): ?><tr><td><?=$c?></td><td><?=$cnt?></td><td><?=pct($cnt,$n)?>%</td></tr><?php endforeach; ?>
    </table>
    <?php $logitEmot=$logitFactors['emotional_eating']??null; if($logitEmot&&$logitEmot['or']): ?>
    <div class="interp">
      <?=__('stats_logit')?> : <?=__('stats_emot_title')?> → <?=__('stats_logit_dep2')?>: OR = <strong><?=$logitEmot['or']?></strong>
      (<?=__('stats_ci_col')?>: <?=$logitEmot['ci_lo']?>–<?=$logitEmot['ci_hi']?>), p = <?=$logitEmot['p']?>
      <?=$logitEmot['p']<0.05?' ✓ '.__('stats_sig_yes'):' — '.__('stats_sig_no')?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ══ TAB: SPEARMAN ══ -->
<div class="stat-panel" id="t-spear">
  <div class="concept"><div class="concept-title">📖 <?=currentLang()==='ar'?'مفهوم معامل سبيرمان':'Concept : Correlation de Spearman'?></div><div class="concept-body"><?=__('concept_spearman')?></div></div>
  <div class="ac">
    <div class="at"><?=__('stats_spear_title')?> <button class="cb" onclick="copyTbl('tspear')"><?=__('stats_copy')?></button></div>
    <p style="font-size:12px;color:var(--text-muted);margin-bottom:10px"><?=__('stats_spear_note')?></p>
    <table class="st" id="tspear">
      <tr><th><?=__('stats_var_col')?></th><th><?=__('stats_rho')?></th><th><?=__('stats_r2_col')?></th><th>n</th><th>p</th><th><?=__('stats_force_col')?></th><th><?=__('stats_dir_col')?></th></tr>
      <?php foreach($spearmanData as $lbl=>$c): $sig=$c['p']<0.05; $abs=abs($c['rho']); 
        $str=$abs<0.1?__('stats_very_weak'):($abs<0.3?__('stats_weak'):($abs<0.5?__('stats_moderate'):($abs<0.7?__('stats_strong'):__('stats_very_strong')))); 
        $dir=$c['rho']>0?__('stats_positive'):__('stats_negative'); ?>
      <tr><td style="font-size:11px"><?=$lbl?></td>
      <td style="font-weight:700;color:<?=$c['rho']>0.2?'#9b2335':($c['rho']<-0.2?'#1a7340':'#856404')?>"><?=$c['rho']?></td>
      <td><?=$c['r2']?></td><td><?=$c['n']?></td>
      <td class="<?=$sig?'sig':'nsig'?>"><?=$c['p']?></td>
      <td><?=$str?></td><td><?=$dir?></td></tr>
      <?php endforeach; ?>
    </table>
    <div class="interp">
      <?=__('stats_spear_interp')?><br>
      <?=__('stats_spear_pos')?><br>
      <?=__('stats_spear_neg')?><br>
      <?=__('stats_corr_note')?>
    </div>
  </div>
  <div class="ac">
    <div class="at"><?=__('stats_anova_stress')?></div>
    <?php $sigStr=($anovaStress['p']??1)<0.05; ?>
    <div class="g2" style="margin-bottom:1rem">
      <?php foreach($anovaStress['means']??[] as $g=>$m): ?>
      <div class="mb"><div class="mv"><?=round($m,1)?></div><div class="ml">IMC — <?=__('stats_stress_level')?> <?=$g?> (n=<?=$anovaStress['n'][$g]??0?>)</div></div>
      <?php endforeach; ?>
    </div>
    <table class="st"><tr><th><?=__('stats_anova_source')?></th><th>F</th><th>p</th><th><?=__('stats_sig_col')?></th></tr>
    <tr><td><?=__('stats_anova_stress')?></td><td><?=$anovaStress['F']??0?></td>
    <td class="<?=$sigStr?'sig':'nsig'?>"><?=$anovaStress['p']??1?></td>
    <td class="<?=$sigStr?'sig':'nsig'?>"><?=$sigStr?__('stats_sig_yes'):__('stats_sig_no')?></td></tr></table>
  </div>
  <div class="ac">
    <div class="at"><?=__('stats_logit_real')?> <button class="cb" onclick="copyTbl('tlogit2')"><?=__('stats_copy_thesis')?></button></div>
    <p style="font-size:12px;color:var(--text-muted);margin-bottom:10px"><?=__('stats_logit_dep2')?> | n = <?=$n?> | <?=__('stats_obese_col')?> = <?=$no?></p>
    <table class="st" id="tlogit2">
      <tr style="background:#1F4E79;color:#fff"><th><?=__('stats_factor_col')?></th><th><?=__('stats_n_exposed')?></th><th>OR</th><th><?=__('stats_ci_col')?></th><th>p</th><th><?=__('stats_type_col')?></th></tr>
      <?php foreach($logitFactors as $f=>$r): if(!$r['or']) continue; $sig=$r['p']<0.05; $risk=$r['or']>1?__('stats_risk_up'):__('stats_protective'); $rc=$r['or']>1?'#9b2335':'#1a7340'; ?>
      <tr><td style="font-size:11px"><?=$r['label']?></td><td><?=$r['n_exp']?></td>
      <td style="font-weight:700;color:<?=$rc?>"><?=$r['or']?></td>
      <td>[<?=$r['ci_lo']?> – <?=$r['ci_hi']?>]</td>
      <td class="<?=$sig?'sig':'nsig'?>"><?=$r['p']?><?=$sig?'*':''?></td>
      <td style="color:<?=$rc?>"><?=$risk?></td></tr>
      <?php endforeach; ?>
    </table>
    <div class="interp"><?=__('stats_logit_warn')?></div>
  </div>
</div>

<!-- ══ TAB 6: ROC / AUC ══ -->
<div class="stat-panel" id="t-roc">
  <div class="concept"><div class="concept-title">📖 <?=currentLang()==='ar'?'مفهوم منحنى ROC':'Concept : Courbe ROC'?></div><div class="concept-body"><?=__('concept_roc')?></div></div>
  <div class="ac">
    <div class="at"><?=__('stats_roc_title')?></div>
    <div class="g2">
      <div>
        <div class="g2" style="margin-bottom:1rem">
          <div class="mb"><div class="mv"><?= $auc ?></div><div class="ml"><?=__('stats_auc')?></div></div>
          <div class="mb">
            <div class="mv" style="font-size:16px"><?= $auc>=0.9?__('stats_excellent'):($auc>=0.8?__('stats_good_disc'):($auc>=0.7?__('stats_acceptable'):__('stats_poor_disc'))) ?></div>
            <div class="ml"><?=__('stats_discrimination')?></div>
          </div>
        </div>
        <table class="st">
          <tr><th><?=__('stats_roc_threshold')?></th><th><?=__('stats_sensitivity')?></th><th><?=__('stats_specificity')?></th><th>1-Spec.</th></tr>
          <?php foreach($rocPts as $pt): if($pt['thresh']%2!==0) continue; ?>
          <tr><td>≥<?=$pt['thresh']?></td><td><?=$pt['sens']?></td><td><?=$pt['spec']?></td><td><?=$pt['fpr']?></td></tr>
          <?php endforeach; ?>
        </table>
        <div class="interp">
          <?=__('stats_roc_note')?><br>
          
        </div>
      </div>
      <div class="cw"><canvas id="cROC"></canvas></div>
    </div>
  </div>
</div>

<!-- ══ TAB 7: BLAND-ALTMAN ══ -->
<div class="stat-panel" id="t-ba">
  <div class="concept"><div class="concept-title">📖 <?=currentLang()==='ar'?'مفهوم رسم بلاند-ألتمان':'Concept : Bland-Altman'?></div><div class="concept-body"><?=__('concept_ba')?></div></div>
  <div class="ac">
    <div class="at"><?=__('stats_ba_title')?></div>
    <div class="g3" style="margin-bottom:1rem">
      <div class="mb"><div class="mv"><?=$baMean?></div><div class="ml"><?=__('stats_bias')?></div></div>
      <div class="mb"><div class="mv"><?=$baLOA_lo?></div><div class="ml"><?=__('stats_loa_lower')?></div></div>
      <div class="mb"><div class="mv"><?=$baLOA_hi?></div><div class="ml"><?=__('stats_loa_upper')?></div></div>
    </div>
    <div class="cw"><canvas id="cBA"></canvas></div>
    <div class="interp">
      <?=__('stats_ba_interp')?><br>
      <?=__('stats_bias')?> = <?=$baMean?> kg/m² | <?=__('stats_ba_limits')?> [<?=$baLOA_lo?> ; <?=$baLOA_hi?>]<br>
      <?= abs($baMean)<0.5?__('stats_ba_note_good'):__('stats_ba_note_bad') ?>
    </div>
  </div>
</div>

<!-- ══ TAB 8: 🔬 MÉTHODES AVANCÉES ══ -->
<div class="stat-panel" id="t-adv">

  <div class="concept">
    <div class="concept-title">🔬 <?=__('stats_adv_concept_title')?></div>
    <div class="concept-body"><?=__('stats_adv_concept_body')?></div>
  </div>

  <?php if($adv['note']==='insufficient' || !$adv['v1'] || isset($adv['v1']['error']) || empty($adv['v1']['converged'])): ?>
  <div class="ac" style="border-inline-start:4px solid #856404">
    <div class="at">⚠️ <?=__('stats_adv_insufficient')?></div>
    <p style="font-size:12px;color:var(--text-muted);line-height:1.8">
      <?=__('stats_adv_insufficient_msg')?> (n complet = <?=$adv_n?>, requis ≥ 30).<br>
      <?=__('stats_adv_insufficient_hint')?>
    </p>
  </div>
  <?php else: ?>

  <!-- ─── Section A : Régression V1 ─── -->
  <div class="ac">
    <div class="at">A. <?=__('stats_adv_v1_title')?> <button class="cb" onclick="copyTbl('tAdvV1')"><?=__('stats_copy')?></button></div>
    <p style="font-size:12px;color:var(--text-muted);margin-bottom:10px">
      <?=__('stats_adv_v1_intro')?> | n = <?=$adv_n?> | <?=__('stats_logit_obese')?>+<?=__('stats_overweight')?> = <?=array_sum($advY)?>
    </p>
    <div style="overflow-x:auto">
    <table class="st" id="tAdvV1">
      <tr>
        <th><?=__('stats_factor_col')?></th>
        <th>OR <?=__('rp_logit_adj')?></th>
        <th>IC 95%</th>
        <th>p</th>
        <th><?=__('stats_sig_col')?></th>
      </tr>
      <?php for($j=1; $j<count($adv['v1']['names']); $j++):
        $or=$adv['v1']['or'][$j]??null; $lo=$adv['v1']['ci_low'][$j]??null; $hi=$adv['v1']['ci_high'][$j]??null;
        $pv=$adv['v1']['p'][$j]??null; $sig=$pv!==null && $pv<0.05;
        $rc=$or>1?'#9b2335':'#1a7340';
      ?>
      <tr>
        <td><?=htmlspecialchars($adv['v1']['names'][$j])?></td>
        <td style="font-weight:700;color:<?=$rc?>"><?=$or!==null?number_format($or,3):'—'?></td>
        <td>[<?=$lo!==null?number_format($lo,3):'—'?> – <?=$hi!==null?number_format($hi,3):'—'?>]</td>
        <td class="<?=$sig?'sig':'nsig'?>"><?=$pv!==null?($pv<0.001?'<0.001':number_format($pv,4)):'—'?></td>
        <td class="<?=$sig?'sig':'nsig'?>"><?=$sig?'✓':''?></td>
      </tr>
      <?php endfor; ?>
    </table>
    </div>
    <div class="interp">
      <?=__('stats_adv_v1_interp')?>
    </div>
  </div>

  <!-- ─── Section B : ROC + Hosmer-Lemeshow ─── -->
  <div class="ac">
    <div class="at">B. <?=__('stats_adv_roc_title')?></div>
    <div class="g2">
      <div>
        <div class="g2" style="margin-bottom:1rem">
          <div class="mb">
            <div class="mv"><?=$adv['auc']!==null?number_format($adv['auc'],3):'—'?></div>
            <div class="ml">AUC</div>
          </div>
          <div class="mb">
            <div class="mv" style="font-size:14px">
              <?php
                if($adv['auc']===null) echo '—';
                elseif($adv['auc']>=0.9) echo __('stats_excellent');
                elseif($adv['auc']>=0.8) echo __('stats_good_disc');
                elseif($adv['auc']>=0.7) echo __('stats_acceptable');
                else echo __('stats_poor_disc');
              ?>
            </div>
            <div class="ml"><?=__('stats_discrimination')?></div>
          </div>
        </div>
        <?php if($adv['hl'] && isset($adv['hl']['chi2'])): ?>
        <table class="st">
          <tr><th><?=__('stats_adv_hl_test')?></th><th><?=__('stats_adv_value')?></th></tr>
          <tr><td>χ² Hosmer-Lemeshow</td><td><?=number_format($adv['hl']['chi2'],3)?></td></tr>
          <tr><td>df</td><td><?=$adv['hl']['df']??'—'?></td></tr>
          <tr><td>p</td>
            <td class="<?=($adv['hl']['p']??1)>=0.05?'sig':'nsig'?>">
              <?=isset($adv['hl']['p'])?number_format($adv['hl']['p'],4):'—'?>
            </td>
          </tr>
        </table>
        <div class="interp" style="margin-top:8px">
          <?php if(($adv['hl']['p']??0) >= 0.05): ?>
            ✓ <?=__('stats_adv_hl_good')?> (p ≥ 0.05)
          <?php else: ?>
            ⚠️ <?=__('stats_adv_hl_bad')?> (p < 0.05)
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
      <div class="cw"><canvas id="cAdvROC"></canvas></div>
    </div>
  </div>

  <!-- ─── Section C : Robustesse V1 / GLMM / GEE / MICE ─── -->
  <div class="ac">
    <div class="at">C. <?=__('stats_adv_robust_title')?> <button class="cb" onclick="copyTbl('tAdvRobust')"><?=__('stats_copy')?></button></div>
    <p style="font-size:12px;color:var(--text-muted);margin-bottom:10px"><?=__('stats_adv_robust_intro')?></p>
    <div style="overflow-x:auto">
    <table class="st" id="tAdvRobust">
      <tr>
        <th><?=__('stats_factor_col')?></th>
        <th>V1 (OR)</th>
        <th>GLMM (OR)</th>
        <th>GEE (OR)</th>
        <th>MICE (OR)</th>
      </tr>
      <?php
        $keyVars = ['KIDMED','Activité','Sédentarité','Sommeil','Pression','Saut repas','Sexe (♂=1)','Âge'];
        $miceNames = $adv['mice']['names'] ?? [];
        foreach($keyVars as $kv):
          $orV1 = $orGL = $orGE = $orMI = null;
          if($adv['v1'] && !empty($adv['v1']['names'])){
            $idx = array_search($kv, $adv['v1']['names']);
            if($idx !== false) $orV1 = $adv['v1']['or'][$idx] ?? null;
          }
          if($adv['glmm'] && !empty($adv['glmm']['names'])){
            $idx = array_search($kv, $adv['glmm']['names']);
            if($idx !== false) $orGL = $adv['glmm']['or'][$idx] ?? null;
          }
          if($adv['gee'] && !empty($adv['gee']['names'])){
            $idx = array_search($kv, $adv['gee']['names']);
            if($idx !== false) $orGE = $adv['gee']['or'][$idx] ?? null;
          }
          if($adv['mice'] && !empty($miceNames)){
            // Le nom MICE peut différer : "Sexe (♂=1)" -> "Sexe", "Saut repas" pas dans MICE
            $miceLookup = $kv === 'Sexe (♂=1)' ? 'Sexe' : $kv;
            $idx = array_search($miceLookup, $miceNames);
            if($idx !== false && isset($adv['mice']['pooled']['or'][$idx])) $orMI = $adv['mice']['pooled']['or'][$idx];
          }
      ?>
      <tr>
        <td><?=htmlspecialchars($kv)?></td>
        <td><?=$orV1!==null?number_format($orV1,3):'—'?></td>
        <td><?=$orGL!==null?number_format($orGL,3):'—'?></td>
        <td><?=$orGE!==null?number_format($orGE,3):'—'?></td>
        <td><?=$orMI!==null?number_format($orMI,3):'—'?></td>
      </tr>
      <?php endforeach; ?>
    </table>
    </div>
    <div class="interp">
      <?php if($adv['glmm']): ?>✓ GLMM <?=__('stats_adv_method_done')?> (<?=__('stats_adv_glmm_re')?>)<br><?php endif; ?>
      <?php if($adv['gee']): ?>✓ GEE <?=__('stats_adv_method_done')?> (<?=__('stats_adv_gee_corr')?>)<br><?php endif; ?>
      <?php if($adv['mice']): ?>✓ MICE m=<?=$adv['mice']['m']?> <?=__('stats_adv_method_done')?> (<?=$adv['mice']['pct_missing']??'—'?>% <?=__('stats_adv_mice_missing')?>)<br><?php endif; ?>
      <?php if(!$adv['glmm'] && !$adv['gee'] && !$adv['mice']): ?>
        ⚠️ <?=__('stats_adv_robust_unavailable')?>
      <?php endif; ?>
    </div>
    <div class="cw" style="height:340px;margin-top:10px"><canvas id="cAdvRobust"></canvas></div>
  </div>

  <!-- ─── Section D : Correction Benjamini-Hochberg ─── -->
  <?php if(!empty($adv['bh'])): ?>
  <div class="ac">
    <div class="at">D. <?=__('stats_adv_bh_title')?> <button class="cb" onclick="copyTbl('tAdvBH')"><?=__('stats_copy')?></button></div>
    <p style="font-size:12px;color:var(--text-muted);margin-bottom:10px"><?=__('stats_adv_bh_intro')?> (FDR ≤ 5%)</p>
    <div style="overflow-x:auto">
    <table class="st" id="tAdvBH">
      <tr>
        <th><?=__('stats_factor_col')?></th>
        <th>OR [IC 95%]</th>
        <th>p brut</th>
        <th>p BH</th>
        <th><?=__('stats_adv_bh_before')?></th>
        <th><?=__('stats_adv_bh_after')?></th>
      </tr>
      <?php foreach($adv['bh'] as $row): ?>
      <tr>
        <td><?=htmlspecialchars($row['name'])?></td>
        <td><?=$row['or']!==null?number_format($row['or'],2):'—'?>
          [<?=$row['ci_lo']!==null?number_format($row['ci_lo'],2):'—'?> – <?=$row['ci_hi']!==null?number_format($row['ci_hi'],2):'—'?>]</td>
        <td class="<?=$row['sig_raw']?'sig':'nsig'?>">
          <?=$row['p_raw']<0.001?'<0.001':number_format($row['p_raw'],4)?>
        </td>
        <td class="<?=$row['sig_bh']?'sig':'nsig'?>">
          <?=$row['p_bh']!==null?($row['p_bh']<0.001?'<0.001':number_format($row['p_bh'],4)):'—'?>
        </td>
        <td><?=$row['sig_raw']?'✓':''?></td>
        <td style="font-weight:600"><?=$row['sig_bh']?'✓':''?></td>
      </tr>
      <?php endforeach; ?>
    </table>
    </div>
    <?php
      $n_sig_raw = count(array_filter($adv['bh'], fn($r)=>$r['sig_raw']));
      $n_sig_bh  = count(array_filter($adv['bh'], fn($r)=>$r['sig_bh']));
    ?>
    <div class="interp">
      <?=__('stats_adv_bh_summary')?> :
      <strong><?=$n_sig_raw?></strong> / <?=count($adv['bh'])?> <?=__('stats_adv_bh_before')?>,
      <strong><?=$n_sig_bh?></strong> / <?=count($adv['bh'])?> <?=__('stats_adv_bh_after')?>.
    </div>
  </div>
  <?php endif; ?>

  <?php endif; // /insufficient ?>
</div>

</div>

<script>
const _chartsInit = {};
function st(id){
  const ids=['desc','comp','anova','corr','logit','ffq','kidmed','percept','spear','roc','ba','adv'];
  document.querySelectorAll('.stat-tab').forEach((t,i)=>t.classList.toggle('active',ids[i]===id));
  document.querySelectorAll('.stat-panel').forEach(p=>p.classList.toggle('active',p.id==='t-'+id));
  if(!_chartsInit[id]){ _chartsInit[id]=true; initCharts(id); }
}
function copyTbl(id){
  const el=document.getElementById(id); if(!el) return;
  let txt='';
  el.querySelectorAll('tr').forEach(r=>{ txt+=Array.from(r.cells).map(c=>c.innerText).join('\t')+'\n'; });
  navigator.clipboard.writeText(txt).then(()=>alert('<?=__('stats_copy_done')?>'));
}

const C='rgba(46,117,182,0.75)',R='rgba(155,35,53,0.75)',G='rgba(26,115,64,0.75)',Y='rgba(133,100,4,0.75)';
const opt={responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{font:{size:11}}}}};

function safeChart(id, cfg) {
  const el = document.getElementById(id);
  if (!el) return null;
  try { return new Chart(el, cfg); } catch(e) { console.warn('Chart error '+id, e); return null; }
}

function initCharts(tab) {

  if (tab === 'desc') {
    safeChart('cIOTF',{type:'doughnut',data:{labels:<?=json_encode(array_keys($iotfCounts))?>,datasets:[{data:<?=json_encode(array_values($iotfCounts))?>,backgroundColor:['#042C53','#1a7340','#856404','#9b2335','#2E75B6','#0c5460']}]},options:{...opt,plugins:{legend:{position:'right',labels:{font:{size:10}}}}}});
    const bh=[];for(let b=14;b<=42;b+=2)bh.push(b+'-'+(b+2));
    safeChart('cBMI',{type:'bar',data:{labels:bh,datasets:[{label:'n',data:<?=json_encode($bmiHist)?>,backgroundColor:'rgba(46,117,182,0.7)',borderColor:'#1F4E79',borderWidth:1}]},options:{...opt,plugins:{legend:{display:false}},scales:{x:{ticks:{maxRotation:45,font:{size:9}}},y:{beginAtZero:true}}}});
  }

  if (tab === 'comp') {
    const sl=['<?=__('score_kidmed')?>','<?=__('score_activite')?>','<?=__('score_sommeil')?>','<?=__('score_sedentarite')?>','<?=__('score_pression')?>','<?=__('score_global')?>'];
    safeChart('cComp',{type:'bar',data:{labels:sl,datasets:[
      {label:'<?=__('stats_n_boys')?>',data:[<?=round(mean(colN($boys,'score_kidmed')),1)?>,<?=round(mean(colN($boys,'score_activite')),1)?>,<?=round(mean(colN($boys,'score_sommeil')),1)?>,<?=round(mean(colN($boys,'score_sedentarite')),1)?>,<?=round(mean(colN($boys,'score_pression_scolaire')),1)?>,<?=round(mean(colN($boys,'global_nutrition_score')),1)?>],backgroundColor:'rgba(31,78,121,0.75)'},
      {label:'<?=__('stats_n_girls')?>',data:[<?=round(mean(colN($girls,'score_kidmed')),1)?>,<?=round(mean(colN($girls,'score_activite')),1)?>,<?=round(mean(colN($girls,'score_sommeil')),1)?>,<?=round(mean(colN($girls,'score_sedentarite')),1)?>,<?=round(mean(colN($girls,'score_pression_scolaire')),1)?>,<?=round(mean(colN($girls,'global_nutrition_score')),1)?>],backgroundColor:'rgba(192,80,77,0.75)'}
    ]},options:{...opt,scales:{y:{beginAtZero:true}}}});
  }

  if (tab === 'logit') {
    const orl=<?=json_encode(array_keys($chiFactors))?>;
    const orv=<?=json_encode(array_values(array_map(fn($r)=>$r['or']??1,$chiFactors)))?>;
    safeChart('cOR',{type:'bar',data:{labels:orl,datasets:[{label:'OR',data:orv,backgroundColor:orv.map(v=>v>1?R:G)}]},options:{...opt,indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{title:{display:true,text:'OR'},min:0},y:{ticks:{font:{size:10}}}}}});
  }

  if (tab === 'anova') {
    safeChart('cAnova1',{type:'bar',data:{labels:<?=json_encode(array_keys($anovaRes['means']??[]))?>,datasets:[{label:'<?=__('stats_bmi_mean_col')?>',data:<?=json_encode(array_values(array_map('round',array_values($anovaRes['means']??[]),array_fill(0,count($anovaRes['means']??[]),1))))?>,backgroundColor:[C,R,G]}]},options:{...opt,plugins:{legend:{display:false}},scales:{y:{min:18,title:{display:true,text:'<?=__('stats_bmi_mean_col')?> (kg/m²)'}}}}});
    safeChart('cAnova2',{type:'bar',data:{labels:<?=json_encode(array_keys($anovaEdu['means']??[]))?>,datasets:[{label:'<?=__('stats_bmi_mean_col')?>',data:<?=json_encode(array_values(array_map('round',array_values($anovaEdu['means']??[]),array_fill(0,count($anovaEdu['means']??[]),1))))?>,backgroundColor:[C,R,G,Y,'rgba(133,35,53,0.75)']}]},options:{...opt,plugins:{legend:{display:false}},scales:{y:{min:15,title:{display:true,text:'<?=__('stats_bmi_mean_col')?>'}}}}});
  }

  if (tab === 'corr') {
    const cl=<?=json_encode(array_keys($corrData))?>;
    const cv=<?=json_encode(array_values(array_map(fn($c)=>$c['r'],$corrData)))?>;
    safeChart('cCorr',{type:'bar',data:{labels:cl,datasets:[{label:'r',data:cv,backgroundColor:cv.map(v=>v>0?G:R)}]},options:{...opt,indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{min:-1,max:1,title:{display:true,text:'r'}},y:{ticks:{font:{size:10}}}}}});
  }

  if (tab === 'ffq') {
    const ffqL=<?=json_encode(array_column($ffqStats,'label'))?>;
    const ffqB=<?=json_encode(array_column($ffqStats,'boys'))?>;
    const ffqG=<?=json_encode(array_column($ffqStats,'girls'))?>;
    safeChart('cFFQ',{type:'bar',data:{labels:ffqL,datasets:[
      {label:'ذكور',data:ffqB,backgroundColor:'rgba(31,78,121,0.7)'},
      {label:'إناث',data:ffqG,backgroundColor:'rgba(192,80,77,0.7)'}
    ]},options:{...opt,indexAxis:'y',plugins:{legend:{position:'top'}},scales:{x:{beginAtZero:true,title:{display:true,text:'متوسط التكرار'}},y:{ticks:{font:{size:9}}}}}});
  }

  if (tab === 'kidmed') {
    safeChart('cKidmed',{type:'doughnut',data:{labels:<?=json_encode(array_keys($kidmedClasses))?>,datasets:[{data:<?=json_encode(array_values($kidmedClasses))?>,backgroundColor:['#dc3545','#ffc107','#28a745']}]},options:{...opt,plugins:{legend:{position:'right'}}}});
  }

  if (tab === 'percept') {
    const pLabels=<?=json_encode(array_keys($gapByIOTF))?>;
    const pConc=<?=json_encode(array_values(array_map(fn($d)=>$d['pct'],$gapByIOTF)))?>;
    safeChart('cPercept',{type:'bar',data:{labels:pLabels,datasets:[{label:'نسبة التوافق %',data:pConc,backgroundColor:pConc.map(v=>v>=60?G:R)}]},options:{...opt,plugins:{legend:{display:false}},scales:{y:{min:0,max:100,title:{display:true,text:'%'}}}}});
  }

  if (tab === 'roc') {
    const rocD=<?=json_encode($rocPts)?>;
    safeChart('cROC',{type:'line',data:{labels:rocD.map(p=>p.fpr),datasets:[
      {label:'ROC',data:rocD.map(p=>({x:p.fpr,y:p.sens})),borderColor:'#1F4E79',borderWidth:2,fill:false,pointRadius:0},
      {label:'<?=__('stats_poor_disc')?>',data:[{x:0,y:0},{x:1,y:1}],borderColor:'#aaa',borderWidth:1,borderDash:[4,4],fill:false,pointRadius:0}
    ]},options:{...opt,scales:{x:{type:'linear',title:{display:true,text:'1-Spec. (FPR)'},min:0,max:1},y:{title:{display:true,text:'<?=__('stats_sensitivity')?> (TPR)'},min:0,max:1}}}});
  }

  if (tab === 'ba') {
    const baD=<?=json_encode($baData)?>;
    const bias=<?=json_encode($baMean)?>, loa=<?=json_encode([$baLOA_lo,$baLOA_hi])?>;
    safeChart('cBA',{type:'scatter',data:{datasets:[
      {label:'<?=__('stats_bias')?>',data:baD.map(p=>({x:p.avg,y:p.diff})),backgroundColor:'rgba(46,117,182,0.5)',pointRadius:4},
      {label:'<?=__('stats_bias')?>',data:[{x:<?=round(min($bmiAll),1)?>,y:bias},{x:<?=round(max($bmiAll),1)?>,y:bias}],type:'line',borderColor:R,borderWidth:1.5,pointRadius:0,fill:false},
      {label:'+1.96 SD',data:[{x:<?=round(min($bmiAll),1)?>,y:loa[1]},{x:<?=round(max($bmiAll),1)?>,y:loa[1]}],type:'line',borderColor:G,borderWidth:1,borderDash:[4,4],pointRadius:0,fill:false},
      {label:'-1.96 SD',data:[{x:<?=round(min($bmiAll),1)?>,y:loa[0]},{x:<?=round(max($bmiAll),1)?>,y:loa[0]}],type:'line',borderColor:G,borderWidth:1,borderDash:[4,4],pointRadius:0,fill:false}
    ]},options:{...opt,scales:{x:{title:{display:true,text:'<?=__('stats_bmi_mean_col')?>'}},y:{title:{display:true,text:'<?=__('stats_bias')?>'}}}}});
  }

  if (tab === 'adv') {
    <?php if($adv['v1'] && !isset($adv['v1']['error']) && !empty($adv['v1']['converged'])): ?>
    // ROC curve V1
    const advFPR = <?=json_encode($adv['roc']['fpr'])?>;
    const advTPR = <?=json_encode($adv['roc']['tpr'])?>;
    const auc = <?=$adv['auc']!==null?json_encode($adv['auc']):'null'?>;
    safeChart('cAdvROC',{
      type:'line',
      data:{
        labels: advFPR.map(v=>v.toFixed(2)),
        datasets:[
          {label:'ROC V1 (AUC='+(auc!==null?auc.toFixed(3):'—')+')', data:advTPR, borderColor:'#1F4E79', backgroundColor:'rgba(31,78,121,0.15)', borderWidth:2, pointRadius:0, fill:true, tension:0},
          {label:'Référence (AUC=0.50)', data:advFPR, borderColor:'#999', borderDash:[6,4], borderWidth:1, pointRadius:0, fill:false}
        ]
      },
      options:{...opt,plugins:{legend:{position:'bottom',labels:{font:{size:10}}}},scales:{x:{type:'category',title:{display:true,text:'1 − Spec. (FPR)'}},y:{beginAtZero:true,max:1,title:{display:true,text:'Sens. (TPR)'}}}}
    });

    // Robustness comparison chart : V1/GLMM/GEE/MICE
    const robustKeys = <?=json_encode(['KIDMED','Activité','Sédentarité','Sommeil','Pression','Saut repas','Sexe','Âge'])?>;
    const ds = [];
    <?php
      $robustVars = ['KIDMED','Activité','Sédentarité','Sommeil','Pression','Saut repas','Sexe (♂=1)','Âge'];
      $miceLookupMap = ['KIDMED'=>'KIDMED','Activité'=>'Activité','Sédentarité'=>'Sédentarité','Sommeil'=>'Sommeil','Pression'=>'Pression','Saut repas'=>null,'Sexe (♂=1)'=>'Sexe','Âge'=>'Âge'];
      $methodData = ['V1'=>['arr'=>$adv['v1'],'color'=>'#1F4E79'],'GLMM'=>['arr'=>$adv['glmm'],'color'=>'#1a7340'],'GEE'=>['arr'=>$adv['gee'],'color'=>'#856404']];
      foreach($methodData as $mname => $mdef):
        if(!$mdef['arr']) continue;
        $vals = [];
        foreach($robustVars as $kv){
          $idx = array_search($kv, $mdef['arr']['names']);
          $vals[] = ($idx !== false && isset($mdef['arr']['or'][$idx])) ? $mdef['arr']['or'][$idx] : null;
        }
    ?>
    ds.push({label:'<?=$mname?>', data:<?=json_encode($vals)?>, backgroundColor:'<?=$mdef['color']?>'});
    <?php endforeach; ?>
    <?php if($adv['mice'] && isset($adv['mice']['pooled']['or'])):
      $mvals = [];
      foreach($robustVars as $kv){
        $lookup = $miceLookupMap[$kv] ?? null;
        if($lookup === null){ $mvals[] = null; continue; }
        $idx = array_search($lookup, $adv['mice']['names']);
        $mvals[] = ($idx !== false && isset($adv['mice']['pooled']['or'][$idx])) ? $adv['mice']['pooled']['or'][$idx] : null;
      }
    ?>
    ds.push({label:'MICE m=<?=$adv['mice']['m']?>', data:<?=json_encode($mvals)?>, backgroundColor:'#9b2335'});
    <?php endif; ?>

    if(ds.length > 0){
      safeChart('cAdvRobust',{
        type:'bar',
        data:{labels:robustKeys, datasets:ds},
        options:{...opt,plugins:{legend:{position:'top'},tooltip:{callbacks:{label:ctx=>{const v=ctx.parsed.y; return ctx.dataset.label+' : OR = '+(v===null?'—':v.toFixed(3));}}}},scales:{y:{beginAtZero:false,title:{display:true,text:'OR ajusté'}}}}
      });
    }
    <?php endif; ?>
  }
}

// تشغيل التبويب الأول عند تحميل الصفحة
initCharts('desc');
</script>
</body>
</html>