<?php
require_once 'config.php';
require_once 'lang.php';
require_once 'about_data.inc.php';
$L = currentLang();
?>
<!DOCTYPE html>
<html lang="<?=currentLang()?>" dir="<?=langDir()?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=__('about_title')?> — <?= SITE_NAME ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
<style>
.about-body { background: var(--bg); min-height: 100vh; }
.about-header {
  background: linear-gradient(135deg, #1F4E79 0%, #2E75B6 60%, #3a8fd4 100%);
  padding: 2.5rem 1rem 2rem;
  text-align: center;
  color: #fff;
  position: relative;
}
.about-header::after {
  content: '';
  position: absolute;
  bottom: -1px;
  left: 0; right: 0;
  height: 40px;
  background: var(--bg);
  border-radius: 40px 40px 0 0;
}
.about-logos {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  margin-bottom: 1.25rem;
}
.about-logos img {
  width: 72px;
  height: 72px;
  object-fit: contain;
  background: rgba(255,255,255,0.95);
  border-radius: 50%;
  padding: 5px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.2);
}
.about-header h1 {
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 6px;
  line-height: 1.4;
}
.about-header .subtitle {
  font-size: 13px;
  opacity: 0.85;
  margin-bottom: 4px;
}
.about-header .year {
  font-size: 12px;
  opacity: 0.65;
  display: inline-block;
  border: 1px solid rgba(255,255,255,0.3);
  padding: 2px 12px;
  border-radius: 20px;
  margin-top: 6px;
}
.about-lang {
  display: flex;
  justify-content: center;
  gap: 1px;
  margin-top: 12px;
}
.about-lang a {
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 500;
  color: rgba(255,255,255,0.7);
  text-decoration: none;
  transition: all 0.15s;
}
.about-lang a:hover { background: rgba(255,255,255,0.15); color: #fff; }
.about-lang a.active { background: rgba(255,255,255,0.25); color: #fff; font-weight: 700; }

.about-container {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 1rem 2rem;
  position: relative;
  z-index: 1;
}
.about-card {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-lg);
  padding: 1.25rem 1.5rem;
  margin-bottom: 1rem;
  box-shadow: var(--shadow);
}
.about-card-title {
  font-size: 14px;
  font-weight: 600;
  color: var(--primary);
  margin-bottom: 0.75rem;
  padding-bottom: 8px;
  border-bottom: 2px solid var(--primary-bg);
  display: flex;
  align-items: center;
  gap: 8px;
}
.about-card-title .icon {
  width: 24px;
  height: 24px;
  background: var(--primary-bg);
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  flex-shrink: 0;
}
.about-text {
  font-size: 13px;
  line-height: 1.8;
  color: var(--text-muted);
}
.about-objectives {
  list-style: none;
  padding: 0;
  margin: 0;
  counter-reset: obj;
}
.about-objectives li {
  counter-increment: obj;
  font-size: 13px;
  line-height: 1.7;
  color: var(--text-muted);
  padding: 6px 0 6px 0;
  border-bottom: 1px solid var(--border-light);
  display: flex;
  gap: 10px;
  align-items: flex-start;
}
.about-objectives li:last-child { border-bottom: none; }
.about-objectives li::before {
  content: counter(obj);
  min-width: 24px;
  height: 24px;
  background: var(--primary);
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 700;
  flex-shrink: 0;
}
.method-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}
.method-item {
  background: var(--bg);
  border-radius: var(--radius);
  padding: 10px 12px;
}
.method-label {
  font-size: 11px;
  font-weight: 600;
  color: var(--primary);
  margin-bottom: 4px;
}
.method-value {
  font-size: 12px;
  color: var(--text-muted);
  line-height: 1.6;
}
.team-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.team-member {
  background: var(--bg);
  border-radius: var(--radius);
  padding: 14px;
  text-align: center;
}
.team-role {
  font-size: 11px;
  font-weight: 600;
  color: var(--primary-light);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 6px;
}
.team-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--text);
  margin-bottom: 2px;
}
.team-detail {
  font-size: 11px;
  color: var(--text-muted);
  line-height: 1.5;
}
.contact-box {
  background: var(--primary-bg);
  border-radius: var(--radius);
  padding: 12px 16px;
  margin-top: 12px;
}
.contact-row {
  font-size: 12px;
  color: var(--text-muted);
  padding: 3px 0;
}
.contact-row a {
  color: var(--primary);
  text-decoration: none;
  font-weight: 500;
}
.contact-row a:hover { text-decoration: underline; }
.ref-list {
  font-size: 11px;
  color: var(--text-muted);
  line-height: 1.8;
}
.ref-list span {
  display: block;
  padding: 3px 0;
  border-bottom: 1px solid var(--border-light);
}
.ref-list span:last-child { border-bottom: none; }
.ethical-box {
  background: #f0f7ff;
  border-inline-start: 3px solid var(--primary-light);
  border-radius: var(--radius);
  padding: 10px 14px;
  font-size: 12px;
  line-height: 1.8;
  color: var(--text-muted);
}
@media (max-width: 600px) {
  .about-logos img { width: 56px; height: 56px; }
  .about-header h1 { font-size: 16px; }
  .method-grid, .team-grid { grid-template-columns: 1fr; }
  .about-card { padding: 1rem; }
}
/* LTR overrides for French/English */
html[dir="ltr"] .about-card-title { direction: ltr; text-align: left; }
html[dir="ltr"] .about-text { direction: ltr; text-align: left; }
html[dir="ltr"] .about-objectives li { direction: ltr; text-align: left; }
html[dir="ltr"] .method-label { direction: ltr; text-align: left; }
html[dir="ltr"] .method-value { direction: ltr; text-align: left; }
html[dir="ltr"] .team-detail { direction: ltr; }
html[dir="ltr"] .contact-row { direction: ltr; text-align: left; }
html[dir="ltr"] .contact-box { direction: ltr; text-align: left; }
html[dir="ltr"] .ref-list { direction: ltr; text-align: left; }
html[dir="ltr"] .about-card { direction: ltr; text-align: left; }
html[dir="ltr"] .about-card-title { direction: ltr; text-align: left; }
html[dir="ltr"] .about-text { direction: ltr; text-align: left; }
html[dir="ltr"] .about-objectives li { direction: ltr; text-align: left; }
html[dir="ltr"] .ethical-box { direction: ltr; text-align: left; }
html[dir="ltr"] .team-member { direction: ltr; }
html[dir="ltr"] ol { direction: ltr; text-align: left; }
.about-btns {
  display: flex; gap: 10px; margin-top: 1rem;
}
.about-login-btn, .about-thesis-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 12px 20px;
  border-radius: var(--radius);
  text-decoration: none;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s;
  text-align: center;
}
.about-login-btn { background: var(--primary); color: #fff; }
.about-login-btn:hover { background: #163d62; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(31,78,121,0.3); }
.about-thesis-btn { background: #fff; color: var(--primary); border: 2px solid var(--primary); }
.about-thesis-btn:hover { background: var(--primary-bg); transform: translateY(-1px); }
@media (max-width: 500px) { .about-btns { flex-direction: column; } }

/* ═══════════════════════════════════════════════════════════
   v4 — Comprehensive sections (capabilities + tests catalog)
   ═══════════════════════════════════════════════════════════ */
.cap-group { margin-bottom: 14px; }
.cap-group-title {
  font-size: 12px;
  font-weight: 700;
  color: var(--primary);
  margin-bottom: 8px;
  padding: 6px 10px;
  background: var(--primary-bg);
  border-radius: 6px;
  display: inline-block;
}
.cap-list {
  margin: 0;
  padding-inline-start: 22px;
  font-size: 12px;
  color: var(--text-muted);
  line-height: 1.85;
}
.cap-list li { padding: 2px 0; }
.cap-list li strong { color: var(--text); font-weight: 600; }
.cap-list li code {
  background: var(--bg);
  padding: 1px 5px;
  border-radius: 3px;
  font-size: 11px;
  color: var(--primary);
  font-family: monospace;
}

.test-cat { margin-bottom: 22px; }
.test-cat-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 14px;
  background: linear-gradient(135deg, #1F4E79 0%, #2E75B6 100%);
  color: #fff;
  border-radius: var(--radius);
  margin-bottom: 12px;
  box-shadow: 0 1px 4px rgba(31,78,121,0.2);
}
.test-cat-num {
  background: rgba(255,255,255,0.25);
  border-radius: 4px;
  padding: 2px 9px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.5px;
}
.test-cat-title { font-size: 13px; font-weight: 600; flex: 1; line-height: 1.4; }
.test-cat-count { font-size: 10px; opacity: 0.8; }

.test-card {
  background: var(--bg);
  border-radius: var(--radius);
  padding: 12px 14px;
  margin-bottom: 10px;
  border-inline-start: 3px solid var(--primary-light);
  transition: transform 0.12s ease, box-shadow 0.12s ease;
}
.test-card:hover { transform: translateX(-2px); box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
html[dir="ltr"] .test-card:hover { transform: translateX(2px); }

.test-name {
  font-size: 13px;
  font-weight: 700;
  color: var(--primary);
  margin-bottom: 6px;
  display: flex;
  align-items: baseline;
  gap: 8px;
  flex-wrap: wrap;
  line-height: 1.4;
}
.test-name-num {
  background: var(--primary);
  color: #fff;
  font-size: 10px;
  padding: 2px 7px;
  border-radius: 10px;
  font-weight: 600;
  flex-shrink: 0;
}
.test-ref {
  font-size: 10px;
  color: var(--text-muted);
  font-weight: 400;
  font-style: italic;
  margin-inline-start: auto;
}
.test-what {
  font-size: 12px;
  color: var(--text-muted);
  line-height: 1.65;
  margin-bottom: 8px;
}
.test-why {
  font-size: 12px;
  color: var(--text);
  line-height: 1.65;
  padding: 7px 11px;
  background: rgba(26,115,64,0.06);
  border-radius: 4px;
  border-inline-start: 2px solid var(--success, #1a7340);
}
.test-why-label {
  font-weight: 700;
  color: var(--success, #1a7340);
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  margin-inline-end: 4px;
}

html[dir="ltr"] .cap-group-title,
html[dir="ltr"] .cap-list,
html[dir="ltr"] .test-cat-title,
html[dir="ltr"] .test-name,
html[dir="ltr"] .test-what,
html[dir="ltr"] .test-why { text-align: left; direction: ltr; }

@media (max-width: 600px) {
  .test-name { font-size: 12px; }
  .test-ref { margin-inline-start: 0; width: 100%; }
  .test-what, .test-why { font-size: 11px; }
}
</style>
</head>
<body class="about-body">

<!-- HEADER -->
<div class="about-header">
  <div class="about-logos">
    <img src="/assets/img/logo-login.png" alt="UHBC">
    <img src="/assets/img/logo.png" alt="FSNV">
  </div>
  <h1><?=__('about_study_name')?></h1>
  <div class="subtitle"><?=__('about_study_type')?></div>
  <div class="year"><?=__('about_year')?></div>
  <div class="about-lang">
    <a href="?lang=ar" class="<?=currentLang()==='ar'?'active':''?>">العربية</a>
    <a href="?lang=fr" class="<?=currentLang()==='fr'?'active':''?>">Français</a>
    <a href="?lang=en" class="<?=currentLang()==='en'?'active':''?>">English</a>
  </div>
</div>

<div class="about-container">

  <!-- CONTEXT -->
  <div class="about-card">
    <div class="about-card-title"><span class="icon">1</span> <?=__('about_context_title')?></div>
    <div class="about-text"><?=__('about_context_text')?></div>
  </div>

  <!-- OBJECTIVES -->
  <div class="about-card">
    <div class="about-card-title"><span class="icon">2</span> <?=__('about_objectives')?></div>
    <ol class="about-objectives">
      <li><?=__('about_obj1')?></li>
      <li><?=__('about_obj2')?></li>
      <li><?=__('about_obj3')?></li>
      <li><?=__('about_obj4')?></li>
      <li><?=__('about_obj5')?></li>
    </ol>
  </div>

  <!-- METHODOLOGY -->
  <div class="about-card">
    <div class="about-card-title"><span class="icon">3</span> <?=__('about_method_title')?></div>
    <div class="method-grid">
      <div class="method-item">
        <div class="method-label"><?=__('about_method_pop')?></div>
        <div class="method-value"><?=__('about_method_pop_v')?></div>
      </div>
      <div class="method-item">
        <div class="method-label"><?=__('about_method_tool')?></div>
        <div class="method-value"><?=__('about_method_tool_v')?></div>
      </div>
      <div class="method-item">
        <div class="method-label"><?=__('about_method_anthro')?></div>
        <div class="method-value"><?=__('about_method_anthro_v')?></div>
      </div>
      <div class="method-item">
        <div class="method-label"><?=__('about_method_stats')?></div>
        <div class="method-value"><?=__('about_method_stats_v')?></div>
      </div>
    </div>
  </div>

  <!-- PLATFORM -->
  <div class="about-card">
    <div class="about-card-title"><span class="icon">4</span> <?=__('about_platform')?></div>
    <div class="about-text"><?=__('about_platform_text')?></div>
  </div>

  <!-- TEAM -->
  <div class="about-card">
    <div class="about-card-title"><span class="icon">5</span> <?=__('about_team')?></div>
    <div class="team-grid">
      <div class="team-member">
        <div class="team-role"><?=__('about_researcher')?></div>
        <div class="team-name">TOUIL El Hadj</div>
        <div class="team-detail">Master 2 — Biologie de la Nutrition</div>
      </div>
      <div class="team-member">
        <div class="team-role"><?=__('about_supervisor')?></div>
        <div class="team-name">Dr. ALI HAIMOUD Safia</div>
        <div class="team-detail"><?=__('about_faculty')?></div>
      </div>
    </div>
    <div style="text-align:center;margin-top:10px;font-size:12px;color:var(--text-muted);line-height:1.7">
      <?=__('about_faculty')?> : <?=currentLang()==='ar'?'كلية علوم الطبيعة والحياة':'Faculte des Sciences de la Nature et de la Vie'?><br>
      <?=__('about_dept')?> : <?=currentLang()==='ar'?'قسم التغذية وعلوم الأغذية':'Departement de Nutrition et Sciences des Aliments'?><br>
      <?=__('university')?>
    </div>

    <!-- CONTACT -->
    <div class="contact-box">
      <div style="font-size:12px;font-weight:600;color:var(--primary);margin-bottom:6px"><?=__('about_contact')?></div>
      <div style="font-size:11px;color:var(--text-muted);margin-bottom:8px;padding-bottom:8px;border-bottom:1px solid var(--border-light)">
        <strong><?=__('about_researcher')?> :</strong> TOUIL El Hadj
      </div>
      <div class="contact-row">Email : <a href="mailto:touilelhadj@live.com">touilelhadj@live.com</a></div>
      <div class="contact-row">Tel : <a href="tel:+213000000000">+213 000 000 000</a></div>
      <div style="font-size:11px;color:var(--text-muted);margin-top:10px;padding-top:8px;border-top:1px solid var(--border-light)">
        <strong><?=__('about_supervisor')?> :</strong> Dr. ALI HAIMOUD Safia
      </div>
      <div class="contact-row">Email : <a href="mailto:s.alihaimoud@univ-chlef.dz">s.alihaimoud@univ-chlef.dz</a></div>
      <div class="contact-row">Tel : <a href="tel:+213000000000">+213 000 000 000</a></div>
    </div>
  </div>

  <!-- CAPABILITIES & LIMITATIONS -->
  <div class="about-card">
    <div class="about-card-title"><span class="icon">6</span> <?=__('about_cap_title')?></div>
    <div class="about-text" style="margin-bottom:12px"><?=__('about_cap_intro')?></div>

    <div style="background:#e8f5e9;border-radius:var(--radius);padding:12px 14px;margin-bottom:12px">
      <div style="font-size:13px;font-weight:600;color:#1a7340;margin-bottom:8px">✅ <?=__('about_can_title')?></div>
      <ol style="margin:0;padding-inline-start:20px;font-size:12px;color:var(--text-muted);line-height:2">
        <li><?=__('about_can_1')?></li>
        <li><?=__('about_can_2')?></li>
        <li><?=__('about_can_3')?></li>
        <li><?=__('about_can_4')?></li>
        <li><?=__('about_can_5')?></li>
        <li><?=__('about_can_6')?></li>
        <li><?=__('about_can_7')?></li>
        <li><?=__('about_can_8')?></li>
        <li><?=__('about_can_9')?></li>
        <li><?=__('about_can_10')?></li>
      </ol>
    </div>

    <div style="background:#fde8e8;border-radius:var(--radius);padding:12px 14px;margin-bottom:12px">
      <div style="font-size:13px;font-weight:600;color:#9b2335;margin-bottom:8px">⚠️ <?=__('about_cannot_title')?></div>
      <ol style="margin:0;padding-inline-start:20px;font-size:12px;color:var(--text-muted);line-height:2">
        <li><?=__('about_cannot_1')?></li>
        <li><?=__('about_cannot_2')?></li>
        <li><?=__('about_cannot_3')?></li>
        <li><?=__('about_cannot_4')?></li>
        <li><?=__('about_cannot_5')?></li>
        <li><?=__('about_cannot_6')?></li>
        <li><?=__('about_cannot_7')?></li>
      </ol>
    </div>

    <div style="background:#f0f7ff;border-radius:var(--radius);padding:12px 14px;border-inline-start:3px solid var(--primary-light)">
      <div style="font-size:13px;font-weight:600;color:var(--primary);margin-bottom:6px">📋 <?=__('about_recommend')?></div>
      <div style="font-size:12px;color:var(--text-muted);line-height:1.9"><?=__('about_recommend_text')?></div>
    </div>
  </div>

  <!-- ETHICAL -->
  <div class="about-card">
    <div class="about-card-title"><span class="icon">7</span> <?=__('about_ethical')?></div>
    <div class="ethical-box"><?=__('about_ethical_text')?></div>
  </div>

  <!-- ═══════════ SECTION 8 : CAPACITÉS COMPLÈTES DE LA PLATEFORME ═══════════ -->
  <div class="about-card" style="border-inline-start:4px solid #1F4E79">
    <div class="about-card-title"><span class="icon" style="background:#1F4E79;color:#fff">8</span> <?=__('about_caps_v4')?></div>
    <div class="about-text" style="margin-bottom:14px"><?=__('about_caps_v4_intro')?></div>

    <?php foreach($capsGroups as $g): ?>
      <div class="cap-group">
        <div class="cap-group-title"><?=$g['title'][$L]?></div>
        <ul class="cap-list">
          <?php foreach($g['items'] as $item): ?>
            <li><?=$item[$L]?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>

    <!-- Version history (preserved from previous section 9) -->
    <div style="margin-top:18px;padding-top:14px;border-top:1px solid var(--border-light)">
      <div style="font-size:13px;font-weight:600;color:var(--primary);margin-bottom:8px">📜 <?=__('about_v3_versions_title')?></div>
      <div style="display:grid;grid-template-columns:1fr;gap:8px">
        <div style="background:#f8f9fa;border-radius:6px;padding:10px 12px;border-inline-start:3px solid #d0d0d0">
          <div style="font-weight:600;font-size:12px;color:#666"><?=__('about_v3_v1_label')?></div>
          <div style="font-size:11px;color:var(--text-muted);margin-top:2px"><?=__('about_v3_v1_desc')?></div>
        </div>
        <div style="background:#f8f9fa;border-radius:6px;padding:10px 12px;border-inline-start:3px solid #c9a961">
          <div style="font-weight:600;font-size:12px;color:#856404"><?=__('about_v3_v2_label')?></div>
          <div style="font-size:11px;color:var(--text-muted);margin-top:2px"><?=__('about_v3_v2_desc')?></div>
        </div>
        <div style="background:#e8f5e9;border-radius:6px;padding:10px 12px;border-inline-start:3px solid #1a7340">
          <div style="font-weight:600;font-size:12px;color:#1a7340"><?=__('about_v3_v3_label')?> ⭐</div>
          <div style="font-size:11px;color:#155724;margin-top:2px"><?=__('about_v3_v3_desc')?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════ SECTION 9 : CATALOGUE COMPLET DES TESTS BIOSTATISTIQUES ═══════════ -->
  <div class="about-card" style="border-inline-start:4px solid #2E75B6">
    <div class="about-card-title"><span class="icon" style="background:#2E75B6;color:#fff">9</span> <?=__('about_tests_v4')?></div>
    <div class="about-text" style="margin-bottom:16px"><?=__('about_tests_v4_intro')?></div>

    <?php
    $catLetters = ['A','B','C','D','E'];
    $catIdx = 0;
    $testIdx = 0;
    foreach($testsCatalog as $cat):
      $letter = $catLetters[$catIdx] ?? '';
      $catIdx++;
    ?>
      <div class="test-cat">
        <div class="test-cat-header">
          <span class="test-cat-num"><?=$letter?></span>
          <span class="test-cat-title"><?=$cat['cat_title'][$L]?></span>
          <span class="test-cat-count"><?=count($cat['tests'])?></span>
        </div>
        <?php foreach($cat['tests'] as $t): $testIdx++; ?>
          <div class="test-card">
            <div class="test-name">
              <span class="test-name-num"><?=$testIdx?></span>
              <span><?=htmlspecialchars($t['name'])?></span>
              <span class="test-ref"><?=htmlspecialchars($t['ref'])?></span>
            </div>
            <div class="test-what"><?=$t['what'][$L]?></div>
            <div class="test-why"><span class="test-why-label"><?=__('about_why_label')?> :</span> <?=$t['why'][$L]?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- ═══════════ SECTION 10 : VALIDATION SCIENTIFIQUE (préservée) ═══════════ -->
  <div class="about-card" style="border-inline-start:4px solid #1a7340">
    <div class="about-card-title"><span class="icon" style="background:#1a7340;color:#fff">10</span> <?=__('about_validation_title')?></div>
    <div class="about-text" style="margin-bottom:14px"><?=__('about_validation_intro')?></div>

    <!-- Big result badge -->
    <div style="background:linear-gradient(135deg,#e8f5e9 0%,#fff 100%);border-radius:var(--radius);padding:16px 20px;margin-bottom:14px;text-align:center;border:2px solid #1a7340">
      <div style="font-size:11px;color:#155724;text-transform:uppercase;letter-spacing:1px;font-weight:600;margin-bottom:4px">
        ✓ <?=__('about_validation_result')?>
      </div>
      <div style="font-size:24px;font-weight:700;color:#1a7340;margin:6px 0">
        <?=__('about_validation_pass')?>
      </div>
      <div style="font-size:11px;color:var(--text-muted);font-style:italic;line-height:1.6;margin-top:6px">
        <?=__('about_validation_meaning')?>
      </div>
    </div>

    <!-- List of test categories -->
    <div style="background:#f8f9fa;border-radius:var(--radius);padding:12px 14px">
      <div style="font-size:12px;font-weight:600;color:var(--primary);margin-bottom:8px">🧪 <?=__('about_validation_tests')?></div>
      <ul style="margin:0;padding-inline-start:20px;font-size:11px;color:var(--text-muted);line-height:2">
        <li><?=__('about_v_test_1')?></li>
        <li><?=__('about_v_test_2')?></li>
        <li><?=__('about_v_test_3')?></li>
        <li><?=__('about_v_test_4')?></li>
        <li><?=__('about_v_test_5')?></li>
        <li><?=__('about_v_test_6')?></li>
        <li><?=__('about_v_test_7')?></li>
        <li><?=__('about_v_test_8')?></li>
      </ul>
    </div>
  </div>

  <!-- ═══════════ SECTION 11 : RÉFÉRENCES BIBLIOGRAPHIQUES ÉTENDUES ═══════════ -->
  <div class="about-card">
    <div class="about-card-title"><span class="icon">11</span> <?=__('about_references')?></div>
    <div class="ref-list">
      <!-- Classification & study design -->
      <span><strong>Cole T.J., Bellizzi M.C., Flegal K.M., Dietz W.H. (2000).</strong> Establishing a standard definition for child overweight and obesity worldwide. <em>BMJ 320</em>, 1240-1243. — Classification IOTF.</span>
      <span><strong>Cole T.J. & Lobstein T. (2012).</strong> Extended IOTF body mass index cut-offs for thinness, overweight and obesity. <em>Pediatric Obesity, 7</em>(4), 284-294.</span>
      <span><strong>Serra-Majem L. et al. (2004).</strong> Food quality — Mediterranean Diet Quality Index for children and adolescents (KIDMED). <em>Public Health Nutrition, 7</em>(7), 931-935.</span>
      <span><strong>Inchley J. et al. (2020).</strong> Health Behaviour in School-aged Children (HBSC) 2017/2018. <em>WHO Regional Office for Europe</em>.</span>
      <span><strong>Ng K. et al. (2018).</strong> Multi-screen behaviour: HBSC methodology paper. — Justification de l'indicateur screen_max.</span>
      <span><strong>Oulamara H. et al. (2022).</strong> État nutritionnel des adolescents — Wilaya de Setif. <em>Antropo, 47</em>, 1-12.</span>
      <span><strong>WHO (2024).</strong> World Obesity Atlas. <em>World Health Organization</em>.</span>

      <!-- Statistical methods (organisés par section du catalogue) -->
      <span><strong>Pearson K. (1900).</strong> On the criterion that a given system of deviations from the probable... <em>Phil. Mag. Series 5, 50</em>, 157-175. — Test χ².</span>
      <span><strong>Yates F. (1934).</strong> Contingency tables involving small numbers. <em>JRSS Suppl. 1</em>, 217-235. — Correction Yates pour 2×2.</span>
      <span><strong>Welch B.L. (1947).</strong> The generalization of "Student's" problem when several different population variances are involved. <em>Biometrika 34</em>, 28-35.</span>
      <span><strong>Fisher R.A. (1925).</strong> Statistical Methods for Research Workers. <em>Oliver & Boyd</em>. — ANOVA.</span>
      <span><strong>Spearman C. (1904).</strong> The proof and measurement of association between two things. <em>Am. J. Psychol. 15</em>, 72-101.</span>
      <span><strong>Woolf B. (1955).</strong> On estimating the relation between blood group and disease. <em>Ann. Hum. Genet. 19</em>, 251-253. — Méthode OR + IC95%.</span>
      <span><strong>Cochran W.G. (1954) ; Armitage P. (1955).</strong> Trend tests for ordered categories — utilisés pour la progression 1AS→2AS→3AS.</span>
      <span><strong>McCullagh P. & Nelder J.A. (1989).</strong> Generalized Linear Models, 2nd ed. <em>Chapman & Hall</em>. — Régression logistique.</span>
      <span><strong>Hosmer D.W. & Lemeshow S. (2013).</strong> Applied Logistic Regression, 3rd ed. <em>Wiley</em>. — Test d'ajustement HL.</span>
      <span><strong>Hanley J.A. & McNeil B.J. (1982).</strong> The meaning and use of the area under a ROC curve. <em>Radiology 143</em>, 29-36.</span>
      <span><strong>McFadden D. (1974).</strong> Conditional logit analysis of qualitative choice behavior. <em>Frontiers in Econometrics</em>, 105-142. — Pseudo-R².</span>
      <span><strong>Akaike H. (1974).</strong> A new look at the statistical model identification. <em>IEEE Trans. Autom. Control 19</em>(6), 716-723. — Critère AIC.</span>

      <!-- Méthodes avancées v2.0 -->
      <span><strong>Allison P.D. (2012).</strong> Logistic Regression Using SAS, 2nd ed. <em>SAS Institute</em>. — VIF.</span>
      <span><strong>Box G.E.P. & Tidwell P.W. (1962).</strong> Transformation of the independent variables. <em>Technometrics 4</em>(4), 531-550.</span>
      <span><strong>Breslow N.E. & Clayton D.G. (1993).</strong> Approximate inference in generalized linear mixed models. <em>JASA 88</em>(421), 9-25. — GLMM/PQL.</span>
      <span><strong>Liang K.Y. & Zeger S.L. (1986).</strong> Longitudinal data analysis using generalized linear models. <em>Biometrika 73</em>(1), 13-22. — GEE.</span>
      <span><strong>van Buuren S. & Groothuis-Oudshoorn K. (2011).</strong> mice: Multivariate Imputation by Chained Equations in R. <em>J. Stat. Software 45</em>(3), 1-67.</span>
      <span><strong>Rubin D.B. (1987).</strong> Multiple Imputation for Nonresponse in Surveys. <em>Wiley</em>. — Règles de pooling.</span>
      <span><strong>Sterne J.A.C. et al. (2009).</strong> Multiple imputation for missing data in epidemiological and clinical research. <em>BMJ 338</em>, b2393. — Guide pratique MICE.</span>
      <span><strong>Benjamini Y. & Hochberg Y. (1995).</strong> Controlling the false discovery rate: a practical and powerful approach to multiple testing. <em>JRSS-B 57</em>(1), 289-300.</span>
      <span><strong>Bland J.M. & Altman D.G. (1986).</strong> Statistical methods for assessing agreement between two methods of clinical measurement. <em>Lancet 1</em>(8476), 307-310.</span>
      <span><strong>Diggle P.J., Heagerty P., Liang K.Y., Zeger S.L. (2002).</strong> Analysis of Longitudinal Data, 2nd ed. <em>Oxford University Press</em>. — Référence GLMM/GEE.</span>
      <span><strong>Goldstein H. (2010).</strong> Multilevel Statistical Models, 4th ed. <em>Wiley</em>. — Justification GLMM en épidémiologie scolaire.</span>
    </div>
  </div>

  <!-- BUTTONS -->
  <div class="about-btns">
    <a href="/login.php" class="about-login-btn">🔐 <?=__('about_login_btn')?></a>
    <a href="/docs/Memoire_Revise.pdf" target="_blank" class="about-thesis-btn">📄 <?=__('about_thesis_btn')?></a>
  </div>

</div>

</body>
</html>
