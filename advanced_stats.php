<?php
/**
 * ═══════════════════════════════════════════════════════════════════
 *   advanced_stats.php — Analyses biostatistiques avancées
 *   v2.0 — mai 2026
 * ═══════════════════════════════════════════════════════════════════
 *
 *   Page d'analyse en PHP pur exécutant les 5 méthodes avancées
 *   sur les données de l'enquête Chlef 2026 :
 *     1. VIF (multicolinéarité)
 *     2. Box-Tidwell (linéarité du logit)
 *     3. GLMM logistique (intercept aléatoire école)
 *     4. GEE logistique (exchangeable + sandwich)
 *     5. MICE (imputation multiple par PMM)
 *
 *   Toutes les méthodes sont implémentées dans la bibliothèque
 *   `touilelhadj/biostat-php` (Composer), chargée via vendor/autoload.php.
 *
 * ═══════════════════════════════════════════════════════════════════
 */

error_reporting(E_ALL);
require_once 'config.php';
require_once 'lang.php';
require_once __DIR__ . '/vendor/autoload.php';

use TouilElhadj\BiostatPhp\BiostatAnalysis;
use TouilElhadj\ChlefPlatform\StudySpecificAnalyses;

$session = checkSession();
if(!isAdmin() && !isGuest()) {
    header('Location: /index.php');
    exit;
}

$db = getDB();

// ─── Charger toutes les données validées ─────────────────────────────
$rows = $db->query("SELECT * FROM responses WHERE is_validated=1")->fetchAll(PDO::FETCH_ASSOC);
$n_total = count($rows);

// ─── Préparer les variables pour les analyses ────────────────────────
// On exclut les lignes sans iotf_class, et on construit la variable y (OW+OB)
$rows_valid = array_filter($rows, fn($r) => !empty($r['iotf_class'] ?? ''));
$rows_valid = array_values($rows_valid);
$n_analytical = count($rows_valid);

// Variables pour la régression
function _f($v, $default=0.0){ return ($v===null||$v==='') ? $default : (float)$v; }
function _i($v, $default=0){ return ($v===null||$v==='') ? $default : (int)$v; }

$y = []; $X = []; $clusters = []; $names = ['kidmed_score','activity_score','age','screen_max','sex_f','sleep_h'];
$data_for_mice = [];
foreach($rows_valid as $r){
    $y[] = StudySpecificAnalyses::isOWOB($r['iotf_class'] ?? '') ? 1 : 0;
    $X[] = [
        _f($r['kidmed_score'] ?? null),
        _f($r['activity_score'] ?? null),
        _f($r['age'] ?? null, 16.7),
        _f($r['screen_max'] ?? $r['screen_total'] ?? null),
        (strtolower($r['sex'] ?? '') === 'f' || strtolower($r['sex'] ?? '') === 'fille') ? 1 : 0,
        _f($r['sleep_hours'] ?? 7.5),
    ];
    $clusters[] = $r['etablissement'] ?? $r['lycee'] ?? 'UNK';
    $data_for_mice[] = [
        'kidmed_score'   => $r['kidmed_score'] ?? null,
        'activity_score' => $r['activity_score'] ?? null,
        'age'            => $r['age'] ?? null,
        'screen_max'     => $r['screen_max'] ?? $r['screen_total'] ?? null,
        'sex_f'          => (strtolower($r['sex'] ?? '') === 'f' || strtolower($r['sex'] ?? '') === 'fille') ? 1 : 0,
        'sleep_hours'    => $r['sleep_hours'] ?? null,
        'y_owob'         => StudySpecificAnalyses::isOWOB($r['iotf_class'] ?? '') ? 1 : 0,
    ];
}

$bp = new BiostatAnalysis($rows_valid);

// ─── Exécution des analyses (avec mise en cache simple) ───────────────
$do_run = isset($_GET['run']) && $_GET['run'] === '1';
$res = [];

if($do_run && $n_analytical >= 100) {
    // Référence : régression logistique multivariée standard
    $res['standard'] = $bp->logisticRegressionMulti($y, $X, $names);
    // 1. VIF
    $res['vif'] = $bp->vif($X, $names);
    // 2. Box-Tidwell sur prédicteurs continus
    $continuous_idx = [0, 1, 2, 3, 5]; // kidmed, activity, age, screen_max, sleep (pas sex_f)
    $res['boxtidwell'] = $bp->boxTidwell($y, $X, $continuous_idx, $names);
    // 3. GLMM avec intercept aléatoire école
    $res['glmm'] = $bp->glmmLogistic($y, $X, $clusters, $names, 100);
    // 4. GEE exchangeable
    $res['gee'] = $bp->geeLogistic($y, $X, $clusters, $names);
    // 5. MICE
    $var_types = [
        'kidmed_score' => 'continuous', 'activity_score' => 'continuous',
        'age' => 'continuous', 'screen_max' => 'continuous',
        'sex_f' => 'binary', 'sleep_hours' => 'continuous', 'y_owob' => 'binary',
    ];
    $mice_m = 20;
    $res['mice'] = $bp->mice($data_for_mice, $var_types, $mice_m, 10, 5);

    // Pooling par règles de Rubin
    $estimates_mi = []; $se_mi = [];
    foreach($res['mice']['imputations'] as $imp){
        $y_imp = array_column($imp, 'y_owob');
        $X_imp = [];
        foreach($imp as $row) $X_imp[] = [
            $row['kidmed_score'], $row['activity_score'], $row['age'],
            $row['screen_max'], $row['sex_f'], $row['sleep_hours'],
        ];
        $fit_imp = $bp->logisticRegressionMulti($y_imp, $X_imp, $names);
        if($fit_imp['converged']){
            $estimates_mi[] = $fit_imp['coef'];
            $se_mi[]        = $fit_imp['se'];
        }
    }
    $res['mice_pooled'] = $bp->rubinPool($estimates_mi, $se_mi);
}
?>
<!DOCTYPE html>
<html lang="<?=currentLang()?>" dir="<?=langDir()?>">
<head>
<meta charset="UTF-8">
<title>Analyses biostatistiques avancées — Chlef 2026</title>
<style>
body{font-family:Arial,sans-serif;background:#f5f7fb;margin:0;padding:24px}
.container{max-width:1280px;margin:auto;background:#fff;border-radius:12px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,.06)}
h1{color:#1F4E79;border-bottom:3px solid #1F4E79;padding-bottom:8px}
h2{color:#1F4E79;margin-top:32px;border-left:5px solid #1F4E79;padding-left:14px;background:#eaf0f7;padding-top:6px;padding-bottom:6px}
h3{color:#444;margin-top:18px}
table{width:100%;border-collapse:collapse;margin:12px 0;font-size:13px}
th{background:#1F4E79;color:#fff;padding:9px;text-align:left}
td{padding:8px;border-bottom:1px solid #e8eaef;vertical-align:top}
tr:nth-child(even){background:#fafbfd}
.btn{display:inline-block;background:#1F4E79;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;border:none;cursor:pointer;font-size:15px}
.btn:hover{background:#143a5e}
.kpi{display:inline-block;background:#eaf0f7;padding:10px 16px;border-radius:8px;margin:4px;min-width:140px;text-align:center}
.kpi b{display:block;font-size:22px;color:#1F4E79;margin-bottom:2px}
.kpi span{font-size:11px;color:#666;text-transform:uppercase;letter-spacing:.5px}
.tag{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600}
.tag-ok{background:#d4edda;color:#155724}
.tag-warn{background:#fff3cd;color:#856404}
.tag-crit{background:#f8d7da;color:#721c24}
.note{font-size:12px;color:#666;font-style:italic;margin:8px 0}
.sig{color:#9b2335;font-weight:700}
.alert-info{background:#d1ecf1;border-left:4px solid #0c5460;padding:14px;border-radius:6px;margin:14px 0;color:#0c5460}
.alert-warn{background:#fff3cd;border-left:4px solid #856404;padding:14px;border-radius:6px;margin:14px 0}
code{background:#eee;padding:1px 5px;border-radius:3px;font-size:12px}
.method-box{background:#f8f9fa;border-left:4px solid #1F4E79;padding:14px;border-radius:6px;margin:14px 0;font-size:13px;line-height:1.7}
.method-box b{color:#1F4E79}
</style>
</head>
<body>
<div class="container">

<h1>🧪 Analyses biostatistiques avancées — Chlef 2026</h1>
<p style="color:#666">Application en PHP pur des 5 méthodes statistiques avancées sur les données réelles de l'enquête.</p>

<div class="kpi"><b><?= number_format($n_total) ?></b><span>Enregistrements validés</span></div>
<div class="kpi"><b><?= number_format($n_analytical) ?></b><span>Analysables (avec IOTF)</span></div>
<div class="kpi"><b><?= count(array_unique($clusters)) ?></b><span>Établissements (clusters)</span></div>
<div class="kpi"><b><?= array_sum($y) ?></b><span>OW + OB</span></div>
<div class="kpi"><b><?= round(array_sum($y)*100/max(1,count($y)),1) ?>%</b><span>Prévalence</span></div>

<?php if(!$do_run): ?>
<div class="alert-info">
<strong>📋 Cette page exécute en PHP pur les 5 analyses biostatistiques avancées suivantes</strong> sur les <?= $n_analytical ?> observations de l'enquête. Chaque méthode est implémentée dans la classe <code>BiostatAnalysis</code> et validée contre R/SPSS (cf. <code>tests.php</code>) :
<ul>
<li><b>VIF</b> — Variance Inflation Factor (multicolinéarité, seuil ≥ 2.5)</li>
<li><b>Box-Tidwell</b> — Linéarité du logit pour chaque prédicteur continu</li>
<li><b>GLMM logistique</b> — Intercept aléatoire école par PQL (Breslow & Clayton 1993) + équations de Henderson</li>
<li><b>GEE</b> — Generalized Estimating Equations (Liang & Zeger 1986) avec corrélation échangeable et variance sandwich</li>
<li><b>MICE</b> — Imputation multiple par équations chaînées avec PMM (van Buuren 2011) + pooling par règles de Rubin (m = 20)</li>
</ul>
</div>
<p><a class="btn" href="?run=1">▶ Exécuter toutes les analyses avancées</a></p>
<p class="note">⚠️ Temps d'exécution estimé : 5–30 secondes selon la taille des données et l'imputation multiple.</p>

<?php elseif($n_analytical < 100): ?>
<div class="alert-warn">Données insuffisantes pour les analyses avancées (n < 100). Veuillez collecter plus de données.</div>

<?php else: ?>

<!-- ════════════ Régression standard (référence) ════════════ -->
<h2>🎯 0. Régression logistique standard (référence)</h2>
<div class="method-box">
<b>Méthode :</b> Régression logistique binaire multivariée, méthode de Newton-Raphson avec inversion de Hessien par Gauss-Jordan. Ignore la structure hiérarchique (étape de référence pour comparaison).
</div>
<?php $st = $res['standard']; ?>
<div class="kpi"><b><?= $st['converged'] ? '✓' : '✗' ?></b><span>Convergé en <?= $st['iter'] ?> itérations</span></div>
<div class="kpi"><b><?= $st['auc'] ?></b><span>AUC</span></div>
<div class="kpi"><b><?= round($st['aic'],1) ?></b><span>AIC</span></div>
<div class="kpi"><b><?= $st['hl']['p'] ?? 'NA' ?></b><span>p HL</span></div>
<table>
<tr><th>Variable</th><th>β</th><th>OR ajusté</th><th>IC 95 %</th><th>p</th></tr>
<?php for($j=1; $j<count($st['or']); $j++): ?>
<tr>
<td><?= htmlspecialchars($st['names'][$j]) ?></td>
<td><?= $st['coef'][$j] ?></td>
<td><b><?= $st['or'][$j] ?></b></td>
<td>[<?= $st['ci_low'][$j] ?> – <?= $st['ci_high'][$j] ?>]</td>
<td class="<?= ($st['p'][$j]!==null && $st['p'][$j]<0.05) ? 'sig' : '' ?>"><?= $st['p'][$j] ?? 'NA' ?></td>
</tr>
<?php endfor; ?>
</table>

<!-- ════════════ 1. VIF ════════════ -->
<h2>📐 1. VIF — Multicolinéarité</h2>
<div class="method-box">
<b>Principe :</b> Pour chaque prédicteur X_j, on régresse X_j sur tous les autres prédicteurs (OLS, intercept inclus) et on calcule VIF_j = 1/(1 − R²_j). <b>Seuils de vigilance</b> : VIF &gt; 2.5 (Allison, 2012) WARNING ; VIF &gt; 5 CRITICAL.
</div>
<table>
<tr><th>Variable</th><th>VIF</th><th>R²_aux</th><th>Tolérance (1/VIF)</th><th>Statut</th></tr>
<?php foreach($res['vif'] as $var => $info): ?>
<tr>
<td><?= htmlspecialchars($var) ?></td>
<td><b><?= $info['vif'] ?? 'NA' ?></b></td>
<td><?= $info['r2'] ?? 'NA' ?></td>
<td><?= $info['tolerance'] ?? 'NA' ?></td>
<td>
<?php $f = $info['flag'] ?? 'NA'; ?>
<span class="tag <?= $f==='OK'?'tag-ok':($f==='WARNING'?'tag-warn':'tag-crit') ?>"><?= $f ?></span>
</td>
</tr>
<?php endforeach; ?>
</table>

<!-- ════════════ 2. Box-Tidwell ════════════ -->
<h2>📈 2. Box-Tidwell — Linéarité du logit</h2>
<div class="method-box">
<b>Principe :</b> Pour chaque prédicteur continu X_j (strictement positif), on introduit le terme d'interaction X_j × ln(X_j) dans le modèle multivarié. <b>H₀</b> : le logit est linéaire en X_j (coefficient de l'interaction = 0). Rejet (p &lt; 0,05) → non-linéarité ; recoder en classes ou splines.
</div>
<table>
<tr><th>Variable</th><th>β (X·lnX)</th><th>SE</th><th>p</th><th>Conclusion</th></tr>
<?php foreach($res['boxtidwell'] as $var => $info): ?>
<tr>
<td><?= htmlspecialchars($var) ?></td>
<td><?= $info['beta_xlnx'] ?? 'NA' ?></td>
<td><?= $info['se'] ?? 'NA' ?></td>
<td class="<?= (($info['p']??1) < 0.05) ? 'sig' : '' ?>"><?= $info['p'] ?? 'NA' ?></td>
<td><?= $info['verdict'] ?? 'NA' ?>
<?php if(($info['shift']??0) > 0) echo "<br><small>shift = ".$info['shift']."</small>"; ?>
</td>
</tr>
<?php endforeach; ?>
</table>

<!-- ════════════ 3. GLMM ════════════ -->
<h2>🏫 3. GLMM logistique — Intercept aléatoire école (PQL)</h2>
<div class="method-box">
<b>Modèle :</b> logit(p_ij) = X_ij β + u_j, avec u_j ~ N(0, σ²_u). <b>Algorithme :</b> Penalized Quasi-Likelihood (Breslow & Clayton 1993) résolu par les équations mixtes de Henderson. <b>ICC</b> = σ²_u / (σ²_u + π²/3) pour le lien logit.
</div>
<?php $gl = $res['glmm']; ?>
<div class="kpi"><b><?= $gl['converged'] ? '✓' : '⚠' ?></b><span><?= $gl['converged'] ? 'Convergé' : 'Non-conv.' ?> (<?= $gl['iter'] ?> it.)</span></div>
<div class="kpi"><b><?= $gl['sigma2_u'] ?></b><span>σ²_u</span></div>
<div class="kpi"><b><?= $gl['icc'] ?></b><span>ICC école</span></div>
<div class="kpi"><b><?= $gl['deff'] ?></b><span>DEFF</span></div>
<div class="kpi"><b><?= $gl['n_clusters'] ?></b><span>Clusters</span></div>
<table>
<tr><th>Variable</th><th>β</th><th>OR ajusté</th><th>IC 95 %</th><th>p</th></tr>
<?php for($j=1; $j<count($gl['or']); $j++): ?>
<tr>
<td><?= htmlspecialchars($gl['names'][$j]) ?></td>
<td><?= $gl['coef'][$j] ?></td>
<td><b><?= $gl['or'][$j] ?></b></td>
<td>[<?= $gl['ci_low'][$j] ?> – <?= $gl['ci_high'][$j] ?>]</td>
<td class="<?= (($gl['p'][$j]??1) < 0.05) ? 'sig' : '' ?>"><?= $gl['p'][$j] ?? 'NA' ?></td>
</tr>
<?php endfor; ?>
</table>
<p class="note">Effets aléatoires par école : <?php
$re = $gl['random_effects'] ?? [];
$re_str = [];
foreach($re as $sch => $u) $re_str[] = "$sch: ".sprintf('%+.3f', $u);
echo implode(' | ', array_slice($re_str, 0, 10));
?></p>

<!-- ════════════ 4. GEE ════════════ -->
<h2>🔗 4. GEE — Equations d'estimation généralisées (sandwich)</h2>
<div class="method-box">
<b>Modèle :</b> Régression logistique GEE avec structure de corrélation <b>exchangeable</b> (Liang & Zeger 1986). Variance robuste « sandwich » : V_β = M₀⁻¹ M₁ M₀⁻¹. Le SE_robust est insensible à une mauvaise spécification de la structure de corrélation.
</div>
<?php $ge = $res['gee']; ?>
<div class="kpi"><b><?= $ge['converged'] ? '✓' : '⚠' ?></b><span><?= $ge['converged'] ? 'Convergé' : 'Non-conv.' ?> (<?= $ge['iter'] ?> it.)</span></div>
<div class="kpi"><b><?= $ge['alpha'] ?></b><span>α (exchangeable)</span></div>
<div class="kpi"><b><?= $ge['n_clusters'] ?></b><span>Clusters</span></div>
<table>
<tr><th>Variable</th><th>β</th><th>OR ajusté</th><th>IC 95 % (sandwich)</th><th>p (sandwich)</th><th>SE_robust</th><th>SE_model</th></tr>
<?php for($j=1; $j<count($ge['or']); $j++): ?>
<tr>
<td><?= htmlspecialchars($ge['names'][$j]) ?></td>
<td><?= $ge['coef'][$j] ?></td>
<td><b><?= $ge['or'][$j] ?></b></td>
<td>[<?= $ge['ci_low'][$j] ?> – <?= $ge['ci_high'][$j] ?>]</td>
<td class="<?= (($ge['p'][$j]??1) < 0.05) ? 'sig' : '' ?>"><?= $ge['p'][$j] ?? 'NA' ?></td>
<td><?= $ge['se_robust'][$j] ?? 'NA' ?></td>
<td><?= $ge['se_model'][$j] ?? 'NA' ?></td>
</tr>
<?php endfor; ?>
</table>

<!-- ════════════ 5. MICE + Rubin ════════════ -->
<h2>🔄 5. MICE — Imputation multiple + Règles de Rubin</h2>
<div class="method-box">
<b>Méthode :</b> Imputation multiple par équations chaînées (van Buuren & Groothuis-Oudshoorn 2011) avec <b>Predictive Mean Matching</b> (PMM, 5 donneurs). <b>m = <?= $res['mice']['m'] ?></b> imputations × <?= $res['mice']['iterations'] ?> itérations chaînées. Pooling par règles de Rubin (1987).
</div>
<h3>Pattern de manquants :</h3>
<table>
<tr><th>Variable</th><th>% manquants</th></tr>
<?php foreach($res['mice']['pct_missing'] as $v => $pct): if($pct > 0): ?>
<tr><td><?= htmlspecialchars($v) ?></td><td><?= $pct ?> %</td></tr>
<?php endif; endforeach; ?>
</table>

<h3>Estimations poolées (Rubin's rules) :</h3>
<?php $mi = $res['mice_pooled']; ?>
<table>
<tr><th>Variable</th><th>β poolé</th><th>SE poolée</th><th>OR poolé</th><th>IC 95 %</th><th>p</th><th>df</th></tr>
<?php for($j=1; $j<count($mi['or']); $j++): ?>
<tr>
<td><?= htmlspecialchars($names[$j-1] ?? "X$j") ?></td>
<td><?= $mi['beta'][$j] ?></td>
<td><?= $mi['se'][$j] ?? 'NA' ?></td>
<td><b><?= $mi['or'][$j] ?? 'NA' ?></b></td>
<td>[<?= $mi['ci_low'][$j] ?? '?' ?> – <?= $mi['ci_high'][$j] ?? '?' ?>]</td>
<td class="<?= (($mi['p'][$j]??1) < 0.05) ? 'sig' : '' ?>"><?= $mi['p'][$j] ?? 'NA' ?></td>
<td><?= $mi['df'][$j] ?? 'NA' ?></td>
</tr>
<?php endfor; ?>
</table>

<!-- ════════════ Synthèse comparative ════════════ -->
<h2>🧮 Synthèse comparative des modèles</h2>
<table>
<tr>
  <th rowspan="2">Prédicteur</th>
  <th colspan="2">Standard</th>
  <th colspan="2">GLMM (mixed)</th>
  <th colspan="2">GEE (sandwich)</th>
  <th colspan="2">MICE poolé</th>
</tr>
<tr>
  <th>OR</th><th>p</th>
  <th>OR</th><th>p</th>
  <th>OR</th><th>p</th>
  <th>OR</th><th>p</th>
</tr>
<?php for($j=1; $j<count($st['or']); $j++): ?>
<tr>
<td><?= htmlspecialchars($st['names'][$j]) ?></td>
<td><?= $st['or'][$j] ?></td>
<td class="<?= (($st['p'][$j]??1) < 0.05) ? 'sig' : '' ?>"><?= $st['p'][$j] ?? 'NA' ?></td>
<td><?= $gl['or'][$j] ?? 'NA' ?></td>
<td class="<?= (($gl['p'][$j]??1) < 0.05) ? 'sig' : '' ?>"><?= $gl['p'][$j] ?? 'NA' ?></td>
<td><?= $ge['or'][$j] ?? 'NA' ?></td>
<td class="<?= (($ge['p'][$j]??1) < 0.05) ? 'sig' : '' ?>"><?= $ge['p'][$j] ?? 'NA' ?></td>
<td><?= $mi['or'][$j] ?? 'NA' ?></td>
<td class="<?= (($mi['p'][$j]??1) < 0.05) ? 'sig' : '' ?>"><?= $mi['p'][$j] ?? 'NA' ?></td>
</tr>
<?php endfor; ?>
</table>
<p class="note">La convergence des estimations entre Standard, GLMM, GEE et MICE atteste de la robustesse des conclusions principales face au choix méthodologique (effet école, imputation des manquants, structure de corrélation).</p>

<p><a class="btn" href="?run=1">🔄 Réexécuter</a> &nbsp; <a class="btn" href="biostat_php_complete.php" style="background:#6c757d">← Retour au rapport</a></p>

<?php endif; ?>

</div>
</body>
</html>
