<?php
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__.'/lang.php';
$current=basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">

  <div class="nav-brand">
    <img src="/assets/img/logo.png" alt="FSNV" class="nav-logo">
    <span><?= defined('SITE_NAME')?SITE_NAME:__('site_name') ?></span>
  </div>
  <div class="nav-links">
    <a href="/index.php"   class="nav-link <?=$current==='index.php'?'active':''?>"><?=__('nav_entry')?></a>
    <a href="/records.php" class="nav-link <?=$current==='records.php'?'active':''?>"><?=__('nav_records')?></a>
    <?php if(!empty($_SESSION['is_guest'])): ?>
    <a href="/personal-survey/" class="nav-link"><?=__('nav_personal_survey')?></a>
    <?php endif; ?>
    <?php if((isset($_SESSION['role'])&&$_SESSION['role']==='admin') || !empty($_SESSION['is_guest'])): ?>
    <a href="/stats.php"    class="nav-link <?=$current==='stats.php'?'active':''?>"><?=__('nav_stats')?></a>
    <a href="/report.php"  class="nav-link <?=$current==='report.php'?'active':''?>">📊</a>
    <a href="/admin.php"    class="nav-link <?=$current==='admin.php'?'active':''?>"><?=__('nav_admin')?></a>
    <?php endif; ?>
    <a href="/guide.php" class="nav-link <?=$current==='guide.php'?'active':''?>"><?=__('nav_guide')?></a>
    <a href="/about.php" class="nav-link <?=$current==='about.php'?'active':''?>"><?=__('about_title')?></a>
    <a href="/map.php" class="nav-link <?=$current==='map.php'?'active':''?>">🗺️</a>
    <?php if(isset($_SESSION['full_name'])): ?>
    <span class="nav-user">
      <?=htmlspecialchars($_SESSION['full_name'])?>
      <?php if(!empty($_SESSION['is_guest'])): ?><span class="guest-badge">👁 <?=__('guest_label')?></span><?php endif; ?>
    </span>
    <?php endif; ?>

    <!-- زر تبديل اللغة -->
    <div class="lang-switcher">
      <a href="?lang=ar" class="lang-btn <?=currentLang()==='ar'?'active':''?>" title="العربية">ع</a>
      <a href="?lang=fr" class="lang-btn <?=currentLang()==='fr'?'active':''?>" title="Français">Fr</a>
      <a href="?lang=en" class="lang-btn <?=currentLang()==='en'?'active':''?>" title="English">En</a>
    </div>

    <a href="/logout.php" class="nav-link nav-logout"><?=__('nav_logout')?></a>
  </div>
</nav>
