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
<title><?=__("records_title")?> — <?= SITE_NAME ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container">
  <div class="page-header">
    <h2><?=__("records_title")?></h2>
    <div style="display:flex;gap:8px;align-items:center">
      <input type="text" id="searchInput" placeholder="<?=__("search_ph")?>" class="search-input" oninput="loadRecords()">
      <?php if (isAdmin() || isGuest()): ?>
      <a href="/api/data.php?action=export_csv" class="btn btn-success"><?=__("btn_export_csv")?></a>
      <?php endif; ?>
    </div>
  </div>

  <?php if(isGuest()): ?><div class="guest-banner"><?=__('guest_banner')?></div><?php endif; ?>

  <div id="alertBox"></div>

  <div class="card" style="padding:0;overflow:hidden">
    <table class="data-table" id="recordsTable">
      <thead>
        <tr>
          <th><?=__("col_num")?></th><th><?=__("col_age")?></th><th><?=__("col_sex")?></th><th><?=__("col_height")?></th><th><?=__("col_weight")?></th>
          <th>IMC</th><th>IOTF</th><th><?=__("col_tutoring")?></th><th><?=__("col_parent_obese")?></th>
          <th><?=__("col_entered_by")?></th><th><?=__("col_date")?></th>
          <?php if (isAdmin()): ?><th><?=__("col_delete")?></th><?php endif; ?>
        </tr>
      </thead>
      <tbody id="tableBody">
        <tr><td colspan="12" class="empty-cell">جاري التحميل...</td></tr>
      </tbody>
    </table>
  </div>

  <div class="pagination" id="pagination"></div>
</div>

<script>
const _lang = {
  male: "<?=__('rec_male')?>",
  female: "<?=__('rec_female')?>",
  cm: "<?=__('rec_cm')?>",
  kg: "<?=__('rec_kg')?>",
  yes: "<?=__('rec_yes')?>",
  no_data: "<?=__('rec_no_data')?>",
  loading: "<?=__('rec_loading')?>",
  confirm_del: "<?=__('rec_confirm_del')?>",
  del_error: "<?=__('rec_del_error')?>",
  records: "<?=__('rec_records_count')?>"
};
</script>
<script>
let currentPage = 1;

function iotfChip(val) {
  const map = {
    'Normal':'chip-success','Surpoids':'chip-warning',
    'Obésité':'chip-danger','Minceur':'chip-info'
  };
  return `<span class="chip ${map[val]||''}">${val||'—'}</span>`;
}

function skipChip(val) {
  if (!val) return '—';
  const yes = val.includes('Oui');
  return `<span class="chip ${yes?'chip-danger':'chip-success'}">${yes?'نعم':'لا'}</span>`;
}

async function loadRecords(page=1) {
  currentPage = page;
  const q = document.getElementById('searchInput').value;
  const res = await fetch(`/api/data.php?action=list&page=${page}&q=${encodeURIComponent(q)}`);
  const json = await res.json();
  const tb = document.getElementById('tableBody');

  if (!json.data || json.data.length === 0) {
    tb.innerHTML = '<tr><td colspan="12" class="empty-cell">'+_lang.no_data+'</td></tr>';
    return;
  }

  const isAdmin = <?= isAdmin() ? 'true' : 'false' ?>;
  tb.innerHTML = json.data.map(r => `
    <tr>
      <td><strong>#${r.questionnaire_num}</strong></td>
      <td>${r.age}</td>
      <td>${r.sex==='Garçon'?_lang.male:_lang.female}</td>
      <td>${r.height} سم</td>
      <td>${r.weight} كغ</td>
      <td>${r.bmi}</td>
      <td>${iotfChip(r.iotf_class)}</td>
      <td>${skipChip(r.skip_meal_tutoring)}</td>
      <td>${r.parent_obese==='Oui'?'<span class="chip chip-danger">'+_lang.yes+'</span>':_lang.no??'—'}</td>
      <td>${r.entered_by_name||'—'}</td>
      <td>${r.entered_at?.slice(0,16)||'—'}</td>
      ${isAdmin ? `<td><button class="btn btn-sm btn-danger" onclick="deleteRecord(${r.id})">×</button></td>` : ''}
    </tr>
  `).join('');

  renderPagination(json.total, json.page, Math.ceil(json.total/50));
}

function renderPagination(total, page, pages) {
  if (pages <= 1) { document.getElementById('pagination').innerHTML=''; return; }
  let html = `<span style="color:var(--text-muted);font-size:13px">${total} ${_lang.records}</span> `;
  for (let i=1; i<=pages; i++) {
    html += `<button class="btn btn-sm ${i===page?'btn-primary':''}" onclick="loadRecords(${i})">${i}</button> `;
  }
  document.getElementById('pagination').innerHTML = html;
}

async function deleteRecord(id) {
  if (!confirm(_lang.confirm_del)) return;
  const res = await fetch(`/api/data.php?action=delete&id=${id}`);
  const json = await res.json();
  if (json.success) loadRecords(currentPage);
  else alert(_lang.del_error + json.message);
}

loadRecords();
</script>
</body>
</html>
