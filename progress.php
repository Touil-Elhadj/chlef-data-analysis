<?php
require_once 'config.php';
require_once 'lang.php';
$session = checkSession();
$db = getDB();
$p = getProgress();

// Données par jour pour le graphique
$daily = $db->query("
    SELECT DATE(entered_at) as d, COUNT(*) as n
    FROM responses
    GROUP BY DATE(entered_at)
    ORDER BY d DESC LIMIT 30
")->fetchAll();
$daily = array_reverse($daily);

// Stats par assistant
$byUser = $db->query("
    SELECT u.full_name, COUNT(r.id) as cnt,
           MAX(r.entered_at) as last_entry,
           COUNT(CASE WHEN DATE(r.entered_at)=CURDATE() THEN 1 END) as today
    FROM users u
    LEFT JOIN responses r ON r.entered_by=u.id
    WHERE u.role='assistant'
    GROUP BY u.id ORDER BY cnt DESC
")->fetchAll();

// Stats IOTF
$iotfStats = $db->query("
    SELECT iotf_class, COUNT(*) as n FROM responses
    WHERE iotf_class IS NOT NULL GROUP BY iotf_class
")->fetchAll();

// ETA date
$etaDate = $p['eta'] ? date('d/m/Y', strtotime('+'.$p['eta'].' days')) : 'N/A';
?>
<!DOCTYPE html>
<html lang="<?=currentLang()?>" dir="<?=langDir()?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="60">
<title><?=__("nav_progress")?> — <?= SITE_NAME ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
.progress-hero{background:linear-gradient(135deg,#1F4E79,#2E75B6);border-radius:var(--radius-lg);padding:2rem;color:#fff;margin-bottom:1rem;text-align:center}
.hero-num{font-size:56px;font-weight:700;line-height:1}
.hero-sub{font-size:16px;opacity:.8;margin-top:6px}
.prog-bar-wrap{background:rgba(255,255,255,.2);border-radius:20px;height:20px;margin:1.5rem 0 .5rem;overflow:hidden}
.prog-bar-fill{height:100%;background:#fff;border-radius:20px;transition:width .5s ease}
.prog-pct{font-size:18px;font-weight:600}
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:1rem}
.kpi{background:var(--bg-card);border:1px solid var(--border-light);border-radius:var(--radius-lg);padding:1rem;text-align:center}
.kpi-val{font-size:26px;font-weight:700;color:var(--primary)}
.kpi-lbl{font-size:11px;color:var(--text-muted);margin-top:3px}
.user-table{width:100%;border-collapse:collapse;font-size:13px}
.user-table th{background:var(--bg);padding:8px 12px;text-align:start;font-weight:600;color:var(--text-muted);border-bottom:2px solid var(--border)}
.user-table td{padding:8px 12px;border-bottom:1px solid var(--border-light)}
.mini-bar{height:8px;background:var(--primary-bg);border-radius:4px;overflow:hidden}
.mini-bar-fill{height:100%;background:var(--primary);border-radius:4px}
.eta-box{background:var(--bg-card);border:2px solid var(--primary-light);border-radius:var(--radius-lg);padding:1.5rem;text-align:center;margin-top:1rem}
@media(max-width:700px){.kpi-grid{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container" style="max-width:1000px">

  <!-- Hero Progress -->
  <div class="progress-hero">
    <div class="hero-num"><?= number_format($p['n']) ?></div>
    <div class="hero-sub"><?=__('prog_entries')?> <?=__('prog_of')?> <?= number_format(TARGET_N) ?> <?=__('prog_target')?></div>
    <div class="prog-bar-wrap">
      <div class="prog-bar-fill" style="width:<?= min($p['pct'],100) ?>%"></div>
    </div>
    <div class="prog-pct"><?= $p['pct'] ?>% <?=__('prog_completed')?></div>
  </div>

  <!-- KPIs -->
  <div class="kpi-grid">
    <div class="kpi">
      <div class="kpi-val"><?= $p['today'] ?></div>
      <div class="kpi-lbl"><?=__('prog_today')?></div>
    </div>
    <div class="kpi">
      <div class="kpi-val"><?= $p['rate'] ?>/j</div>
      <div class="kpi-lbl"><?=__('prog_rate')?></div>
    </div>
    <div class="kpi">
      <div class="kpi-val"><?= number_format($p['remaining']) ?></div>
      <div class="kpi-lbl"><?=__('prog_remaining')?></div>
    </div>
    <div class="kpi">
      <div class="kpi-val"><?= $etaDate ?></div>
      <div class="kpi-lbl"><?=__('prog_eta')?></div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem">

    <!-- Graphique journalier -->
    <div class="card">
      <div class="card-title"><?=__('prog_daily_chart')?></div>
      <div style="position:relative;height:220px">
        <canvas id="dailyChart"></canvas>
      </div>
    </div>

    <!-- <?=__('prog_iotf_dist')?> -->
    <div class="card">
      <div class="card-title"><?=__('prog_iotf_dist')?></div>
      <div style="position:relative;height:220px">
        <canvas id="iotfChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Performance par assistant -->
  <div class="card" style="margin-top:1rem">
    <div class="card-title"><?=__('prog_perf')?></div>
    <table class="user-table">
      <thead>
        <tr><th><?=__("prog_assistant")?></th><th><?=__("prog_total")?></th><th><?=__("prog_today_col")?></th><th><?=__("prog_progression")?></th><th><?=__("prog_last_active")?></th></tr>
      </thead>
      <tbody>
      <?php foreach($byUser as $u):
        $pctUser = $p['n']>0 ? round($u['cnt']/$p['n']*100) : 0;
      ?>
      <tr>
        <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
        <td><?= $u['cnt'] ?></td>
        <td><?= $u['today']>0 ? '<span class="chip chip-success">'.$u['today'].'</span>' : '<span style="color:var(--text-muted)">0</span>' ?></td>
        <td style="min-width:120px">
          <div style="font-size:11px;color:var(--text-muted);margin-bottom:3px"><?= $pctUser ?>% du total</div>
          <div class="mini-bar"><div class="mini-bar-fill" style="width:<?= $pctUser ?>%"></div></div>
        </td>
        <td style="color:var(--text-muted);font-size:12px"><?= $u['last_entry'] ? substr($u['last_entry'],0,16) : __('prog_never') ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="eta-box">
    <?php if ($p['eta']): ?>
    <div style="font-size:14px;color:var(--text-muted)"><?=__('prog_eta_box')?> <strong><?= $p['rate'] ?> saisies/jour</strong></div>
    <div style="font-size:20px;font-weight:700;color:var(--primary);margin:8px 0"><?=__('prog_end_est')?> <?= $etaDate ?></div>
    <div style="font-size:13px;color:var(--text-muted)"><?= $p['remaining'] ?> <?=__('prog_entries')?> <?=__('prog_remaining')?> — <?= $p['eta'] ?> <?=__('prog_days_left')?></div>
    <?php else: ?>
    <div style="font-size:18px;font-weight:700;color:var(--success)"><?=__('prog_goal_done')?></div>
    <?php endif; ?>
  </div>

</div>

<script>
const dailyLabels = <?= json_encode(array_column($daily,'d')) ?>;
const dailyData   = <?= json_encode(array_column($daily,'n')) ?>;
new Chart('dailyChart',{type:'bar',data:{labels:dailyLabels,datasets:[{
  label:'Saisies',data:dailyData,backgroundColor:'rgba(46,117,182,0.7)',borderColor:'#1F4E79',borderWidth:1
}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
scales:{x:{ticks:{maxRotation:45,font:{size:9}}},y:{beginAtZero:true}}}});

const iotfData  = <?= json_encode(array_column($iotfStats,'n')) ?>;
const iotfLabels= <?= json_encode(array_column($iotfStats,'iotf_class')) ?>;
new Chart('iotfChart',{type:'doughnut',data:{labels:iotfLabels,datasets:[{data:iotfData,
  backgroundColor:['#0c5460','#1a7340','#856404','#9b2335','#2E75B6']}]},
options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{font:{size:10}}}}}});
</script>
</body>
</html>
