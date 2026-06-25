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
<title><?=__('guide_title')?> — <?= SITE_NAME ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">

  <!-- HEADER -->
  <div class="card" style="background:linear-gradient(135deg,#1F4E79,#2E75B6);color:#fff;border:none">
    <div style="font-size:22px;font-weight:700;margin-bottom:6px"><?=__('guide_header_title')?></div>
    <div style="font-size:13px;opacity:0.85"><?=__('guide_header_sub')?></div>
    <div style="font-size:12px;opacity:0.7;margin-top:4px"><?=__('guide_header_desc')?></div>
  </div>

  <!-- SECTION 1 -->
  <div class="card">
    <div class="card-title"><?=__('guide_s1_title')?></div>
    <div class="guide-steps">
      <div class="guide-step">
        <div class="step-num">1</div>
        <div class="step-content">
          <strong><?=__('guide_s1_step1')?></strong>
          <code class="code-block"><?= htmlspecialchars(SITE_URL) ?>/</code>
        </div>
      </div>
      <div class="guide-step">
        <div class="step-num">2</div>
        <div class="step-content"><?=__('guide_s1_step2')?></div>
      </div>
      <div class="guide-step">
        <div class="step-num">3</div>
        <div class="step-content"><?=__('guide_s1_step3')?></div>
      </div>
    </div>
    <div class="alert alert-warning" style="margin-top:12px;font-size:12px">
      <?=__('guide_s1_warning')?>
    </div>
  </div>

  <!-- SECTION 2 -->
  <div class="card">
    <div class="card-title"><?=__('guide_s2_title')?></div>
    <div class="guide-steps">
      <div class="guide-step">
        <div class="step-num">1</div>
        <div class="step-content"><?=__('guide_s2_s1')?></div>
      </div>
      <div class="guide-step">
        <div class="step-num">2</div>
        <div class="step-content">
          <?=__('guide_s2_s2')?>
          <div class="info-box"><?=__('guide_s2_s2_info')?></div>
        </div>
      </div>
      <div class="guide-step">
        <div class="step-num">3</div>
        <div class="step-content">
          <?=__('guide_s2_s3')?>
          <div class="ffq-legend">
            <span class="ffq-col"><?=__('ffq_never')?></span>
            <span class="ffq-col"><?=__('ffq_lt1mo')?></span>
            <span class="ffq-col"><?=__('ffq_1_3mo')?></span>
            <span class="ffq-col"><?=__('ffq_1wk')?></span>
            <span class="ffq-col"><?=__('ffq_2_4wk')?></span>
            <span class="ffq-col"><?=__('ffq_5_6wk')?></span>
            <span class="ffq-col"><?=__('ffq_1day')?></span>
            <span class="ffq-col"><?=__('ffq_2_3day')?></span>
            <span class="ffq-col"><?=__('ffq_4_5day')?></span>
            <span class="ffq-col"><?=__('ffq_6plus')?></span>
          </div>
        </div>
      </div>
      <div class="guide-step">
        <div class="step-num">4</div>
        <div class="step-content">
          <?=__('guide_s2_s4')?>
          <div class="info-box important"><?=__('guide_s2_s4_info')?></div>
        </div>
      </div>
      <div class="guide-step">
        <div class="step-num">5</div>
        <div class="step-content"><?=__('guide_s2_s5')?></div>
      </div>
      <div class="guide-step">
        <div class="step-num">6</div>
        <div class="step-content"><?=__('guide_s2_s6')?></div>
      </div>
    </div>
  </div>

  <!-- SECTION 3 -->
  <div class="card">
    <div class="card-title"><?=__('guide_s3_title')?></div>
    <div style="overflow-x:auto">
    <table class="data-table">
      <thead>
        <tr>
          <th><?=__('guide_s3_col1')?></th>
          <th><?=__('guide_s3_col2')?></th>
          <th><?=__('guide_s3_col3')?></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><span class="chip chip-info">Minceur</span></td>
          <td>&lt; 17.5</td>
          <td><?=__('guide_thin_meaning')?></td>
        </tr>
        <tr>
          <td><span class="chip chip-success">Normal</span></td>
          <td>17.5 – 24.9</td>
          <td><?=__('guide_normal_meaning')?></td>
        </tr>
        <tr>
          <td><span class="chip chip-warning">Surpoids</span></td>
          <td>25.0 – 29.9</td>
          <td><?=__('guide_overw_meaning')?></td>
        </tr>
        <tr>
          <td><span class="chip chip-danger">Obésité</span></td>
          <td>≥ 30.0</td>
          <td><?=__('guide_obese_meaning')?></td>
        </tr>
      </tbody>
    </table>
    </div>
    <div class="alert alert-info" style="margin-top:12px;font-size:12px">
      <?=__('guide_s3_note')?>
    </div>
  </div>

  <!-- SECTION 4 -->
  <div class="card">
    <div class="card-title"><?=__('guide_s4_title')?></div>
    <div class="guide-steps">
      <div class="guide-step">
        <div class="step-num">!</div>
        <div class="step-content"><?=__('guide_s4_e1')?></div>
      </div>
      <div class="guide-step">
        <div class="step-num">!</div>
        <div class="step-content"><?=__('guide_s4_e2')?></div>
      </div>
      <div class="guide-step">
        <div class="step-num">!</div>
        <div class="step-content"><?=__('guide_s4_e3')?></div>
      </div>
    </div>
  </div>

  <!-- SECTION 5 -->
  <div class="card">
    <div class="card-title"><?=__('guide_s5_title')?></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div class="info-box">
        <strong><?=__('guide_do_title')?></strong><br>
        <?=nl2br(htmlspecialchars(__('guide_do_list')))?>
      </div>
      <div class="info-box important">
        <strong><?=__('guide_dont_title')?></strong><br>
        <?=nl2br(htmlspecialchars(__('guide_dont_list')))?>
      </div>
    </div>
  </div>

  <!-- CONTACT -->
  <div class="card" style="text-align:center;background:var(--primary-bg)">
    <div style="font-size:14px;color:var(--primary);font-weight:600;margin-bottom:8px"><?=__('guide_contact_title')?></div>
    <div style="font-size:13px;color:var(--text-muted)"><?=__('guide_contact_msg')?></div>
    <div style="margin-top:12px;font-size:12px;color:var(--text-muted)"><?=__('university')?></div>
  </div>

</div>

<style>
.guide-steps { display: flex; flex-direction: column; gap: 14px; }
.guide-step { display: flex; gap: 14px; align-items: flex-start; }
.step-num {
  min-width: 32px; height: 32px;
  background: var(--primary); color: #fff;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 13px; flex-shrink: 0;
}
.step-content { font-size: 13px; color: var(--text); line-height: 1.7; padding-top: 4px; }
.info-box {
  background: var(--primary-bg);
  border-right: 3px solid var(--primary-light);
  padding: 8px 12px; border-radius: var(--radius);
  font-size: 12px; color: var(--text-muted);
  margin-top: 8px; line-height: 1.8;
}
.info-box.important { background: #fff3cd; border-right-color: #ffc107; color: #856404; }
.badge-guide {
  display: inline-block; background: var(--primary); color: #fff;
  padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;
}
.badge-guide.success { background: var(--success); }
.code-block {
  display: block; background: #f4f6f9; border: 1px solid var(--border);
  border-radius: var(--radius); padding: 8px 12px; font-size: 13px;
  margin-top: 6px; color: var(--primary); font-family: monospace;
  direction: ltr; text-align: left;
}
.ffq-legend { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 8px; }
.ffq-col { background: var(--primary); color: #fff; font-size: 10px; padding: 3px 7px; border-radius: 4px; }
@media(max-width:600px){
  .guide-step { flex-direction: column; }
  [style*="grid-template-columns:1fr 1fr"] { grid-template-columns: 1fr !important; display: grid !important; }
}
</style>

</body>
</html>
