<?php
require_once 'config.php';
require_once 'lang.php';
$session=checkSession();
// Admins peuvent tout faire ; les invités peuvent consulter mais pas modifier
if(!isAdmin() && !isGuest()){header('Location: /index.php');exit;}
$db=getDB();
$msg='';

// Bloquer toute action POST si invité (lecture seule)
if($_SERVER['REQUEST_METHOD']==='POST' && isGuest()){
    $msg=['t'=>'warning','m'=>__('guest_cannot_action')];
}
elseif($_SERVER['REQUEST_METHOD']==='POST'){
    if(!validateCSRF($_POST['csrf_token']??'')){ $msg=['t'=>'danger','m'=>__('admin_token_invalid')]; }
    elseif(($_POST['action']??'')==='add_user'){
        $un=sanitize($_POST['username']??''); $fn=sanitize($_POST['full_name']??'');
        $pw=$_POST['password']??''; $role=in_array($_POST['role'],['admin','assistant'])?$_POST['role']:'assistant';
        if($un&&$fn&&strlen($pw)>=6){
            try{
                $db->prepare('INSERT INTO users(username,password_hash,full_name,role)VALUES(?,?,?,?)')->execute([$un,password_hash($pw,PASSWORD_DEFAULT),$fn,$role]);
                auditLog('INSERT','users',$db->lastInsertId(),"Créé: $fn ($role)");
                $msg=['t'=>'success','m'=>sprintf(__('admin_user_created'),$fn)];
            }catch(PDOException $e){ $msg=['t'=>'danger','m'=>__('admin_user_exists')]; }
        } else { $msg=['t'=>'danger','m'=>__('admin_fill_fields')]; }
    }
    elseif(($_POST['action']??'')==='delete_user'){
        $uid=(int)($_POST['user_id']??0);
        if($uid&&$uid!==$session['user_id']){
            $u=$db->prepare('SELECT full_name FROM users WHERE id=?'); $u->execute([$uid]); $ur=$u->fetch();
            $db->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
            auditLog('DELETE','users',$uid,"Supprimé: ".($ur['full_name']??'?'));
            $msg=['t'=>'success','m'=>__('admin_user_deleted')];
        }
    }
    elseif(($_POST['action']??'')==='add_assignment'){
        $uid=(int)($_POST['assign_user']??0); $s=(int)($_POST['range_start']??0); $e=(int)($_POST['range_end']??0);
        $note=sanitize($_POST['note']??'');
        if($uid&&$s&&$e&&$s<=$e){
            $db->prepare('INSERT INTO assignments(user_id,range_start,range_end,note)VALUES(?,?,?,?)')->execute([$uid,$s,$e,$note]);
            $msg=['t'=>'success','m'=>sprintf(__('admin_range_assigned'),$s,$e)];
        } else { $msg=['t'=>'danger','m'=>__('admin_range_invalid')]; }
    }
    elseif(($_POST['action']??'')==='delete_assignment'){
        $aid=(int)($_POST['assign_id']??0);
        if($aid){ $db->prepare('DELETE FROM assignments WHERE id=?')->execute([$aid]); $msg=['t'=>'success','m'=>__('admin_assign_deleted')]; }
    }
    elseif(($_POST['action']??'')==='update_email'){
        $msg=['t'=>'success','m'=>__('admin_config_updated')];
    }
}

$users     = $db->query('SELECT id,username,full_name,role,last_login,created_at FROM users ORDER BY role,full_name')->fetchAll();
$perf      = $db->query("SELECT u.id,u.full_name,COUNT(r.id) as cnt,MAX(r.entered_at) as last,COUNT(CASE WHEN DATE(r.entered_at)=CURDATE() THEN 1 END) as today FROM users u LEFT JOIN responses r ON r.entered_by=u.id GROUP BY u.id ORDER BY cnt DESC")->fetchAll();
$assigns   = $db->query("SELECT a.*,u.full_name FROM assignments a JOIN users u ON a.user_id=u.id ORDER BY a.range_start")->fetchAll();
$auditRows = $db->query("SELECT a.*,u.full_name FROM audit_log a LEFT JOIN users u ON a.user_id=u.id ORDER BY a.created_at DESC LIMIT 50")->fetchAll();
$p         = getProgress();
$totalRecs = (int)$db->query('SELECT COUNT(*) FROM responses')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="<?=currentLang()?>" dir="<?=langDir()?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=__('nav_admin')?> — <?= SITE_NAME ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
<style>
.admin-tabs{display:flex;gap:4px;margin-bottom:1rem;background:var(--border-light);padding:4px;border-radius:var(--radius);direction:ltr}
.admin-tab{flex:1;padding:8px;border:none;background:none;border-radius:6px;font-size:13px;cursor:pointer;color:var(--text-muted);font-family:inherit;font-weight:500}
.admin-tab.active{background:#fff;color:var(--primary);box-shadow:0 1px 3px rgba(0,0,0,.1)}
.admin-panel{display:none}.admin-panel.active{display:block}
.audit-row{font-size:11px;font-family:monospace}
.assign-badge{background:var(--primary-bg);color:var(--primary);padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600}
@media(max-width:600px){.admin-tabs{flex-wrap:wrap}.admin-tab{min-width:70px;font-size:11px}}
</style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">
<?php if(isGuest()): ?><div class="guest-banner"><?=__('guest_banner')?></div><?php endif; ?>
<?php if($msg): ?><div class="alert alert-<?= $msg['t'] ?>"><?= $msg['m'] ?></div><?php endif; ?>

<!-- KPIs -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:1rem">
  <?php $kpis=[[__('admin_kpi_total'),$totalRecs.' / '.TARGET_N],[__('admin_kpi_progress'),$p['pct'].'%'],[__('admin_kpi_speed'),$p['rate'].'/'.(__('prog_per_day'))],[__('admin_kpi_eta'),$p['eta']?date('d/m/Y',strtotime('+'.$p['eta'].' days')):'N/A']]; ?>
  <?php foreach($kpis as [$l,$v]): ?>
  <div style="background:var(--bg-card);border:1px solid var(--border-light);border-radius:var(--radius-lg);padding:1rem;text-align:center">
    <div style="font-size:20px;font-weight:700;color:var(--primary)"><?= $v ?></div>
    <div style="font-size:11px;color:var(--text-muted);margin-top:3px"><?= $l ?></div>
  </div>
  <?php endforeach; ?>
</div>

<div class="admin-tabs">
  <button class="admin-tab active" onclick="sw('users')"><?=__('admin_tab_users')?></button>
  <button class="admin-tab" onclick="sw('assign')"><?=__('admin_tab_assign')?></button>
  <button class="admin-tab" onclick="sw('export')"><?=__('admin_tab_export')?></button>
  <button class="admin-tab" onclick="sw('audit')"><?=__('admin_tab_audit')?></button>
</div>

<!-- USERS -->
<div class="admin-panel active" id="p-users">
  <div style="display:grid;grid-template-columns:<?= isGuest()?'1fr':'1fr 1fr' ?>;gap:1rem">
    <?php if(!isGuest()): ?>
    <div class="card">
      <div class="card-title"><?=__('admin_add_user')?></div>
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add_user">
        <div class="form-group"><label><?=__('admin_full_name')?></label><input type="text" name="full_name" required placeholder="Ahmed Benali"></div>
        <div class="form-group"><label><?=__('admin_username')?></label><input type="text" name="username" required placeholder="ahmed.benali"></div>
        <div class="form-group"><label><?=__('admin_password')?></label><input type="password" name="password" required minlength="6"></div>
        <div class="form-group"><label><?=__('admin_role')?></label>
          <select name="role">
            <option value="assistant"><?=__('admin_role_assistant')?></option>
            <option value="admin"><?=__('admin_role_admin')?></option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px"><?=__('admin_add_btn')?></button>
      </form>
    </div>
    <?php endif; ?>
    <div class="card">
      <div class="card-title"><?=__('admin_perf_title')?></div>
      <table class="data-table">
        <thead><tr><th><?=__('admin_col_name')?></th><th><?=__('admin_col_total')?></th><th><?=__('admin_col_today')?></th><th><?=__('admin_col_last')?></th></tr></thead>
        <tbody>
        <?php foreach($perf as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['full_name']) ?></td>
          <td><strong><?= $u['cnt'] ?></strong></td>
          <td><?= $u['today']>0?'<span class="chip chip-success">'.$u['today'].'</span>':'—' ?></td>
          <td style="font-size:11px;color:var(--text-muted)"><?= $u['last']?substr($u['last'],0,16):'—' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card" style="margin-top:1rem">
    <div class="card-title"><?=__('admin_users_list')?> (<?= count($users) ?>)</div>
    <div style="overflow-x:auto">
    <table class="data-table">
      <thead><tr><th><?=__('admin_col_name')?></th><th><?=__('admin_col_id')?></th><th><?=__('admin_col_role')?></th><th><?=__('admin_col_lastlogin')?></th><th><?=__('admin_col_action')?></th></tr></thead>
      <tbody>
      <?php foreach($users as $u): ?>
      <tr>
        <td><?= htmlspecialchars($u['full_name']) ?></td>
        <td><code><?= htmlspecialchars($u['username']) ?></code></td>
        <td><span class="chip <?= $u['role']==='admin'?'chip-danger':'chip-info' ?>"><?= $u['role']==='admin'?__('admin_role_admin'):__('admin_role_assistant') ?></span></td>
        <td style="font-size:12px;color:var(--text-muted)"><?= $u['last_login']?substr($u['last_login'],0,16):__('admin_never') ?></td>
        <td>
          <?php if(isGuest()): ?>
            <span style="font-size:11px;color:var(--text-muted)">—</span>
          <?php elseif($u['id']!==$session['user_id']): ?>
          <form method="POST" style="display:inline" onsubmit="return confirm('<?=__('admin_confirm_del')?>')">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="delete_user">
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <button class="btn btn-sm btn-danger"><?=__('admin_delete')?></button>
          </form>
          <?php else: ?><span style="font-size:11px;color:var(--text-muted)"><?=__('admin_you')?></span><?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

<!-- ASSIGNATIONS -->
<div class="admin-panel" id="p-assign">
  <div style="display:grid;grid-template-columns:<?= isGuest()?'1fr':'1fr 1fr' ?>;gap:1rem">
    <?php if(!isGuest()): ?>
    <div class="card">
      <div class="card-title"><?=__('admin_assign_range')?></div>
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add_assignment">
        <div class="form-group"><label><?=__('admin_assistant')?></label>
          <select name="assign_user" required>
            <option value=""><?=__('choose')?></option>
            <?php foreach($users as $u): if($u['role']!=='assistant') continue; ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
          <div class="form-group"><label><?=__('admin_start')?></label><input type="number" name="range_start" min="1" max="9999" required placeholder="1"></div>
          <div class="form-group"><label><?=__('admin_end_range')?></label><input type="number" name="range_end" min="1" max="9999" required placeholder="500"></div>
        </div>
        <div class="form-group"><label><?=__('admin_note')?></label><input type="text" name="note" placeholder="Lycée Ibn Badis..."></div>
        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px"><?=__('admin_assign_btn')?></button>
      </form>
    </div>
    <?php endif; ?>
    <div class="card">
      <div class="card-title"><?=__('admin_current_assign')?></div>
      <?php if($assigns): ?>
      <div style="overflow-x:auto">
      <table class="data-table">
        <thead><tr><th><?=__('admin_assistant')?></th><th><?=__('admin_range_col')?></th><th><?=__('admin_note')?></th><th></th></tr></thead>
        <tbody>
        <?php foreach($assigns as $a): ?>
        <tr>
          <td><?= htmlspecialchars($a['full_name']) ?></td>
          <td><span class="assign-badge"><?= $a['range_start'] ?>–<?= $a['range_end'] ?></span></td>
          <td style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($a['note']??'') ?></td>
          <td>
            <?php if(!isGuest()): ?>
            <form method="POST" style="display:inline" onsubmit="return confirm('<?=__('admin_confirm_del')?>')">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="delete_assignment">
              <input type="hidden" name="assign_id" value="<?= $a['id'] ?>">
              <button class="btn btn-sm btn-danger">×</button>
            </form>
            <?php else: ?>—<?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
      <?php else: ?><p style="color:var(--text-muted);font-size:13px"><?=__('admin_no_assign')?></p><?php endif; ?>
    </div>
  </div>
</div>

<!-- EXPORT -->
<div class="admin-panel" id="p-export">
  <div class="card">
    <div class="card-title"><?=__('admin_export_title')?></div>
    <div style="display:grid;grid-template-columns:repeat(<?= isGuest()?2:3 ?>,1fr);gap:1rem;margin-bottom:1.5rem">
      <div style="border:1px solid var(--border-light);border-radius:var(--radius-lg);padding:1.25rem;text-align:center">
        <div style="font-size:28px;margin-bottom:8px">📊</div>
        <div style="font-weight:600;margin-bottom:4px"><?=__('admin_csv_title')?></div>
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px"><?=__('admin_csv_desc')?></div>
        <a href="/api/data.php?action=export_csv" class="btn btn-success" style="width:100%"><?=__('admin_csv_btn')?></a>
      </div>
      <div style="border:1px solid var(--border-light);border-radius:var(--radius-lg);padding:1.25rem;text-align:center">
        <div style="font-size:28px;margin-bottom:8px">📈</div>
        <div style="font-weight:600;margin-bottom:4px"><?=__('admin_spss_title')?></div>
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px"><?=__('admin_spss_desc')?></div>
        <a href="/api/data.php?action=export_spss" class="btn btn-primary" style="width:100%"><?=__('admin_spss_btn')?></a>
      </div>
      <?php if(!isGuest()): ?>
      <div style="border:1px solid var(--border-light);border-radius:var(--radius-lg);padding:1.25rem;text-align:center">
        <div style="font-size:28px;margin-bottom:8px">💾</div>
        <div style="font-weight:600;margin-bottom:4px"><?=__('admin_backup_title')?></div>
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px"><?=__('admin_backup_desc')?></div>
        <a href="/backup.php" class="btn" style="width:100%" onclick="this.textContent='<?=__('admin_backup_progress')?>'"><?=__('admin_backup_btn')?></a>
      </div>
      <?php endif; ?>
    </div>
    <div class="alert alert-info" style="font-size:12px">
      <strong><?=__('admin_auto_backup')?></strong> <?=__('admin_auto_backup_msg')?>
      <code>0 23 * * * php /home/biostat/www/backup.php</code>
    </div>
  </div>
</div>

<!-- JOURNAL AUDIT -->
<div class="admin-panel" id="p-audit">
  <div class="card">
    <div class="card-title"><?=__('admin_audit_title')?></div>
    <div style="overflow-x:auto">
    <table class="data-table">
      <thead><tr><th><?=__('admin_audit_datetime')?></th><th><?=__('admin_audit_user')?></th><th><?=__('admin_audit_action')?></th><th><?=__('admin_audit_table')?></th><th><?=__('admin_audit_details')?></th><th><?=__('admin_audit_ip')?></th></tr></thead>
      <tbody>
      <?php foreach($auditRows as $a): ?>
      <tr class="audit-row">
        <td><?= substr($a['created_at'],0,16) ?></td>
        <td><?= htmlspecialchars($a['full_name']??__('admin_system')) ?></td>
        <td><span class="chip <?= ['LOGIN'=>'chip-success','INSERT'=>'chip-info','DELETE'=>'chip-danger','EXPORT'=>'chip-warning'][$a['action']]??'' ?>"><?= $a['action'] ?></span></td>
        <td><?= $a['table_name']??'—' ?></td>
        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($a['details']??'') ?>"><?= htmlspecialchars(substr($a['details']??'',0,50)) ?></td>
        <td><?= $a['ip_address'] ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
</div>

</div>
<script>
function sw(id){
  document.querySelectorAll('.admin-tab').forEach((t,i)=>t.classList.toggle('active',['users','assign','export','audit'][i]===id));
  document.querySelectorAll('.admin-panel').forEach(p=>p.classList.toggle('active',p.id==='p-'+id));
}
</script>
</body>
</html>
