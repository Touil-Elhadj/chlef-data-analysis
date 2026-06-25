<?php
require_once 'config.php';
require_once 'lang.php';
if (session_status()===PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user_id']) || !empty($_SESSION['is_guest'])){ header('Location: /index.php'); exit; }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST'){
    // ── Connexion en tant qu'invité ─────────────────────────
    if (($_POST['action']??'')==='guest_login') {
        if (!validateCSRF($_POST['csrf_token']??'')) {
            $error=__('login_err_csrf');
        } else {
            session_regenerate_id(true);
            $_SESSION['is_guest']  = true;
            $_SESSION['username']  = 'guest';
            $_SESSION['full_name'] = __('guest_label');
            $_SESSION['role']      = 'guest';
            $_SESSION['expires']   = time()+SESSION_DURATION;
            // Note: pas de user_id pour les invités
            auditLog('LOGIN','users',null,'Connexion invité (lecture seule)');
            header('Location: /index.php'); exit;
        }
    }
    else {
    $username=sanitize($_POST['username']??'');
    $password=$_POST['password']??'';
    if (!validateCSRF($_POST['csrf_token']??'')){
        $error=__('login_err_csrf');
    } elseif (checkBruteForce($username)){
        $error=__('login_err_lock');
        logError('Brute force',['user'=>$username]);
    } elseif ($username&&$password){
        $db=getDB();
        $stmt=$db->prepare('SELECT id,password_hash,full_name,role FROM users WHERE username=? LIMIT 1');
        $stmt->execute([$username]);
        $user=$stmt->fetch();
        if ($user&&password_verify($password,$user['password_hash'])){
            session_regenerate_id(true);
            $_SESSION['user_id']=$user['id'];
            $_SESSION['username']=$username;
            $_SESSION['full_name']=$user['full_name'];
            $_SESSION['role']=$user['role'];
            $_SESSION['expires']=time()+SESSION_DURATION;
            $db->prepare('UPDATE users SET last_login=NOW() WHERE id=?')->execute([$user['id']]);
            recordLoginAttempt($username,true);
            auditLog('LOGIN','users',$user['id'],'Connexion réussie');
            header('Location: /index.php'); exit;
        } else {
            recordLoginAttempt($username,false);
            $error=__('login_err_wrong');
        }
    } else { $error=__('login_err_empty'); }
    }
}
?>
<!DOCTYPE html>
<html lang="<?=currentLang()?>" dir="<?=langDir()?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=__('login_title')?> — <?= SITE_NAME ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="login-body">
<div class="login-container">
  <div class="login-header">
    <div class="login-logo"><img src="/assets/img/logo-login.png" alt="UHBC" style="width:90px;height:90px;object-fit:contain"></div>
    <h1><?= SITE_NAME ?></h1>
    <p><?=__('login_system')?></p>
  </div>
  <!-- Language switcher on login page -->
  <div style="display:flex;justify-content:center;margin-bottom:1rem">
    <div class="lang-switcher" style="background:rgba(31,78,121,0.1);padding:4px 6px">
      <a href="?lang=ar" class="lang-btn <?=currentLang()==='ar'?'active':''?>" style="color:var(--primary)" title="العربية">ع</a>
      <a href="?lang=fr" class="lang-btn <?=currentLang()==='fr'?'active':''?>" style="color:var(--primary)" title="Français">Fr</a>
      <a href="?lang=en" class="lang-btn <?=currentLang()==='en'?'active':''?>" style="color:var(--primary)" title="English">En</a>
    </div>
  </div>
  <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
  <form method="POST" class="login-form">
    <?= csrfField() ?>
    <div class="form-group">
      <label><?=__('login_username')?></label>
      <input type="text" name="username" required value="<?= htmlspecialchars($_POST['username']??'') ?>">
    </div>
    <div class="form-group">
      <label><?=__('login_password')?></label>
      <input type="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary btn-block"><?=__('login_btn')?></button>
  </form>

  <!-- Séparateur -->
  <div class="login-divider">
    <span><?=__('login_or')?></span>
  </div>

  <!-- Connexion invité (lecture seule) -->
  <form method="POST" class="login-form">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="guest_login">
    <button type="submit" class="btn btn-block btn-guest">
      👁  <?=__('login_guest_btn')?>
    </button>
    <p class="guest-hint"><?=__('login_guest_hint')?></p>
  </form>

  <p class="login-footer"><?=__('university')?></p>
</div>
</body>
</html>
