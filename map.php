<?php
require_once 'config.php';
require_once 'lang.php';
$session = checkSession();

$db = getDB();

// ── Charger les données par école ──
$rows = $db->query("SELECT school, COUNT(*) as n, ROUND(AVG(bmi),1) as bmi_avg,
    ROUND(SUM(CASE WHEN iotf_class='Obésité' THEN 1 ELSE 0 END)/COUNT(*)*100,1) as obesity_pct,
    ROUND(SUM(CASE WHEN iotf_class='Surpoids' THEN 1 ELSE 0 END)/COUNT(*)*100,1) as surpoids_pct,
    ROUND(AVG(score_kidmed),1) as kidmed_avg,
    ROUND(AVG(global_nutrition_score),1) as global_avg,
    ROUND(SUM(CASE WHEN sex='Garçon' THEN 1 ELSE 0 END)/COUNT(*)*100,1) as boys_pct
    FROM responses GROUP BY school")->fetchAll(PDO::FETCH_ASSOC);

// Mapper écoles → districts
$districtMap = [
    'أبو الحسن' => ['id'=>1, 'schools'=>['يطو بن أحمد','حباشي عبد القادر','حباس عدّة']],
    'الشلف'     => ['id'=>5, 'schools'=>['ابن باديس','علي شاشو','بلحاج قاسم نور الدين']],
    'أولاد فارس'=> ['id'=>10,'schools'=>['امحمدي بوزينة','الومي الجيلالي','قادري احمد','دعلوز الحاج','الخوارزمي']],
    'تنس'       => ['id'=>12,'schools'=>['بن ساحلي حسان','مرسلي عبد الله']],
    'الزبوجة'   => ['id'=>13,'schools'=>['18 فبراير']],
];

// Agréger par district
$districtData = [];
foreach($districtMap as $name => $info) {
    $dRows = array_filter($rows, function($r) use ($info) {
        foreach($info['schools'] as $s) {
            if(stripos($r['school']??'', $s) !== false) return true;
        }
        return false;
    });
    $n = array_sum(array_column($dRows,'n'));
    $districtData[$info['id']] = [
        'name'    => $name,
        'n'       => $n,
        'bmi'     => $n > 0 ? round(array_sum(array_map(fn($r)=>$r['bmi_avg']*$r['n'],$dRows))/$n, 1) : 0,
        'obesity' => $n > 0 ? round(array_sum(array_map(fn($r)=>$r['obesity_pct']*$r['n'],$dRows))/$n, 1) : 0,
        'surpoids'=> $n > 0 ? round(array_sum(array_map(fn($r)=>$r['surpoids_pct']*$r['n'],$dRows))/$n, 1) : 0,
        'kidmed'  => $n > 0 ? round(array_sum(array_map(fn($r)=>$r['kidmed_avg']*$r['n'],$dRows))/$n, 1) : 0,
        'global'  => $n > 0 ? round(array_sum(array_map(fn($r)=>$r['global_avg']*$r['n'],$dRows))/$n, 1) : 0,
        'schools' => $info['schools'],
        'schoolData' => array_values($dRows),
    ];
}

$totalN = array_sum(array_column($districtData,'n'));
$jsonData = json_encode($districtData, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="<?=currentLang()?>" dir="<?=langDir()?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>خريطة الدراسة — <?= SITE_NAME ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
<style>
.map-page { max-width: 1200px; margin: 0 auto; padding: 1rem; }
.map-header { text-align: center; margin-bottom: 1rem; }
.map-header h2 { color: var(--primary); font-size: 18px; margin-bottom: 4px; }
.map-header p { color: var(--text-muted); font-size: 12px; }

.map-layout { display: grid; grid-template-columns: 1fr 340px; gap: 1rem; align-items: start; }

.map-container {
    background: var(--bg-card);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    padding: 1rem;
    box-shadow: var(--shadow);
    position: relative;
}

.map-svg-wrap { position: relative; width: 100%; }
.map-svg-wrap svg { width: 100%; height: auto; }

/* District paths */
.district { cursor: pointer; transition: all 0.2s; stroke: #fff; stroke-width: 1.5; }
.district:hover { filter: brightness(0.85); stroke-width: 2.5; }
.district.active { stroke: var(--primary); stroke-width: 3; filter: brightness(0.9); }
.district.covered { opacity: 1; }
.district.not-covered { opacity: 0.3; fill: #ccc !important; }

.district-label {
    font-size: 11px; font-weight: 700; fill: #fff;
    text-anchor: middle; pointer-events: none;
    text-shadow: 0 1px 2px rgba(0,0,0,0.5);
}
.district-num {
    font-size: 18px; font-weight: 700; fill: rgba(255,255,255,0.4);
    text-anchor: middle; pointer-events: none;
}

/* Legend */
.map-legend {
    display: flex; gap: 12px; justify-content: center;
    margin-top: 10px; flex-wrap: wrap;
}
.legend-item { display: flex; align-items: center; gap: 4px; font-size: 11px; color: var(--text-muted); }
.legend-dot { width: 12px; height: 12px; border-radius: 3px; border: 1px solid rgba(0,0,0,0.1); }

/* Info panel */
.info-panel {
    background: var(--bg-card);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
}
.info-header {
    background: var(--primary);
    color: #fff;
    padding: 14px 16px;
    font-size: 15px;
    font-weight: 600;
}
.info-body { padding: 12px 16px; }
.info-placeholder {
    text-align: center;
    padding: 2rem 1rem;
    color: var(--text-muted);
    font-size: 13px;
}
.info-stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px; }
.info-stat {
    background: var(--bg);
    border-radius: var(--radius);
    padding: 10px;
    text-align: center;
}
.info-stat-val { font-size: 20px; font-weight: 700; color: var(--primary); }
.info-stat-lbl { font-size: 10px; color: var(--text-muted); margin-top: 2px; }
.info-schools { margin-top: 10px; }
.info-schools-title { font-size: 12px; font-weight: 600; color: var(--primary); margin-bottom: 6px; }
.school-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 5px 0;
    border-bottom: 1px solid var(--border-light);
    font-size: 12px;
    color: var(--text-muted);
}
.school-row:last-child { border-bottom: none; }
.school-n { font-weight: 600; color: var(--primary); }

/* Metric selector */
.metric-tabs {
    display: flex; gap: 3px; margin-bottom: 10px;
    background: var(--border-light); padding: 3px; border-radius: var(--radius);
}
.metric-tab {
    flex: 1; padding: 6px 4px; border: none; background: none;
    border-radius: 5px; font-size: 11px; cursor: pointer;
    color: var(--text-muted); font-family: inherit; font-weight: 500;
    transition: all 0.15s;
}
.metric-tab.active { background: #fff; color: var(--primary); box-shadow: 0 1px 3px rgba(0,0,0,0.1); }

/* Summary bar */
.summary-bar {
    display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px;
    margin-bottom: 1rem;
}
.summary-card {
    background: var(--bg-card); border: 1px solid var(--border-light);
    border-radius: var(--radius); padding: 8px; text-align: center;
}
.summary-val { font-size: 18px; font-weight: 700; color: var(--primary); }
.summary-lbl { font-size: 9px; color: var(--text-muted); margin-top: 2px; }

@media (max-width: 768px) {
    .map-layout { grid-template-columns: 1fr; }
    .summary-bar { grid-template-columns: repeat(3, 1fr); }
}
</style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="map-page">
    <div class="map-header">
        <h2>خريطة توزيع الدراسة — ولاية الشلف</h2>
        <p>Répartition géographique de l'étude — Wilaya de Chlef | n = <?=$totalN?></p>
    </div>

    <!-- Summary -->
    <div class="summary-bar">
        <?php foreach($districtData as $id => $d): ?>
        <div class="summary-card" style="cursor:pointer" onclick="selectDistrict(<?=$id?>)">
            <div class="summary-val"><?=$d['n']?></div>
            <div class="summary-lbl"><?=$d['name']?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="map-layout">
        <!-- MAP -->
        <div class="map-container">
            <div class="metric-tabs">
                <button class="metric-tab active" onclick="setMetric('n',this)">العدد n</button>
                <button class="metric-tab" onclick="setMetric('obesity',this)">% سمنة</button>
                <button class="metric-tab" onclick="setMetric('bmi',this)">IMC</button>
                <button class="metric-tab" onclick="setMetric('kidmed',this)">KIDMED</button>
            </div>

            <div class="map-svg-wrap">
                <svg viewBox="0 0 500 400" xmlns="http://www.w3.org/2000/svg">
                    <!-- District 1: أبو الحسن (top-center-left) -->
                    <path id="d1" class="district covered" d="M160,40 L200,30 L230,50 L240,80 L220,100 L190,105 L160,90 L145,65 Z" fill="#E8A838" onclick="selectDistrict(1)"/>
                    <text class="district-label" x="190" y="72">أبو الحسن</text>

                    <!-- District 2: عين مران (center-left) -->
                    <path id="d2" class="district not-covered" d="M80,160 L130,140 L165,155 L175,190 L160,220 L120,230 L85,210 L70,185 Z" fill="#C8A8D8" onclick="selectDistrict(2)"/>
                    <text class="district-num" x="125" y="192">2</text>

                    <!-- District 3: بني حواء (top-right) -->
                    <path id="d3" class="district not-covered" d="M390,20 L440,15 L475,30 L480,65 L460,80 L420,75 L395,55 Z" fill="#E878A8" onclick="selectDistrict(3)"/>
                    <text class="district-num" x="435" y="52">3</text>

                    <!-- District 4: بوقادير (center-bottom-left) -->
                    <path id="d4" class="district not-covered" d="M100,235 L170,220 L210,240 L230,280 L220,330 L170,350 L120,340 L90,300 L85,260 Z" fill="#F8B888" onclick="selectDistrict(4)"/>
                    <text class="district-num" x="160" y="290">4</text>

                    <!-- District 5: الشلف (center) -->
                    <path id="d5" class="district covered" d="M230,240 L290,220 L330,240 L350,280 L340,330 L300,350 L260,340 L230,300 Z" fill="#E8E848" onclick="selectDistrict(5)"/>
                    <text class="district-label" x="290" y="290">الشلف</text>

                    <!-- District 6: الكريمية (bottom-right) -->
                    <path id="d6" class="district not-covered" d="M400,280 L450,260 L480,290 L485,340 L460,370 L420,380 L395,350 L390,310 Z" fill="#68A8D8" onclick="selectDistrict(6)"/>
                    <text class="district-num" x="440" y="325">6</text>

                    <!-- District 7: المرسى (top-left) -->
                    <path id="d7" class="district not-covered" d="M80,50 L120,35 L150,45 L155,70 L140,90 L110,95 L85,80 L70,65 Z" fill="#E85828" onclick="selectDistrict(7)"/>
                    <text class="district-num" x="115" y="70">7</text>

                    <!-- District 8: وادي الفضة (right) -->
                    <path id="d8" class="district not-covered" d="M380,150 L420,130 L460,145 L470,185 L455,220 L420,235 L390,220 L375,190 Z" fill="#D8D0C0" onclick="selectDistrict(8)"/>
                    <text class="district-num" x="425" y="185">8</text>

                    <!-- District 9: أولاد بن عبد القادر (bottom-center) -->
                    <path id="d9" class="district not-covered" d="M260,345 L320,335 L355,360 L350,400 L310,415 L270,405 L250,380 Z" fill="#98D888" onclick="selectDistrict(9)"/>
                    <text class="district-num" x="305" y="378">9</text>

                    <!-- District 10: أولاد فارس (center) -->
                    <path id="d10" class="district covered" d="M220,105 L280,95 L340,110 L370,150 L365,200 L330,230 L280,220 L235,195 L210,155 L215,120 Z" fill="#88B8E8" onclick="selectDistrict(10)"/>
                    <text class="district-label" x="290" y="165">أولاد فارس</text>

                    <!-- District 11: تاوقريت (far-left) -->
                    <path id="d11" class="district not-covered" d="M10,100 L55,80 L80,95 L85,140 L75,180 L50,200 L20,190 L5,155 Z" fill="#48D8C8" onclick="selectDistrict(11)"/>
                    <text class="district-num" x="48" y="148">11</text>

                    <!-- District 12: تنس (top-center) -->
                    <path id="d12" class="district covered" d="M230,10 L290,5 L330,15 L350,40 L340,70 L310,85 L260,80 L235,55 Z" fill="#4848C8" onclick="selectDistrict(12)"/>
                    <text class="district-label" x="290" y="48" fill="#fff">تنس</text>

                    <!-- District 13: الزبوجة (center-top) -->
                    <path id="d13" class="district covered" d="M290,85 L340,75 L370,90 L375,125 L355,145 L320,140 L295,120 L285,100 Z" fill="#28A848" onclick="selectDistrict(13)"/>
                    <text class="district-label" x="332" y="115">الزبوجة</text>
                </svg>
            </div>

            <div class="map-legend">
                <div class="legend-item"><div class="legend-dot" style="background:#28a745"></div> مشمولة بالدراسة</div>
                <div class="legend-item"><div class="legend-dot" style="background:#ccc"></div> غير مشمولة</div>
            </div>
        </div>

        <!-- INFO PANEL -->
        <div class="info-panel">
            <div class="info-header" id="infoTitle">انقر على دائرة لعرض البيانات</div>
            <div class="info-body" id="infoBody">
                <div class="info-placeholder">
                    اختر دائرة من الخريطة<br>
                    <span style="font-size:11px;opacity:0.7">Cliquez sur un district</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const DATA = <?=$jsonData?>;
let currentMetric = 'n';
let activeDistrict = null;

function selectDistrict(id) {
    // Remove previous active
    document.querySelectorAll('.district').forEach(d => d.classList.remove('active'));
    const el = document.getElementById('d' + id);
    if (el) el.classList.add('active');

    activeDistrict = id;
    const d = DATA[id];

    if (!d || d.n === 0) {
        document.getElementById('infoTitle').textContent = getDistrictName(id) + ' — غير مشمولة';
        document.getElementById('infoBody').innerHTML = '<div class="info-placeholder">لا توجد بيانات لهذه الدائرة<br><span style="font-size:11px;opacity:0.7">Aucune donnée pour ce district</span></div>';
        return;
    }

    document.getElementById('infoTitle').textContent = d.name + ' — n=' + d.n;
    let html = '<div class="info-stat-grid">';
    html += statBox(d.n, 'العدد n');
    html += statBox(d.obesity + '%', '% سمنة');
    html += statBox(d.surpoids + '%', '% زيادة وزن');
    html += statBox(d.bmi, 'IMC متوسط');
    html += statBox(d.kidmed, 'KIDMED');
    html += statBox(d.global + '/100', 'Score global');
    html += '</div>';

    if (d.schoolData && d.schoolData.length > 0) {
        html += '<div class="info-schools"><div class="info-schools-title">الثانويات / Lycées</div>';
        d.schoolData.forEach(s => {
            const name = s.school ? s.school.split('—')[0].trim() : '?';
            html += '<div class="school-row"><span>' + name + '</span><span class="school-n">n=' + s.n + '</span></div>';
        });
        html += '</div>';
    }

    document.getElementById('infoBody').innerHTML = html;
}

function statBox(val, label) {
    return '<div class="info-stat"><div class="info-stat-val">' + val + '</div><div class="info-stat-lbl">' + label + '</div></div>';
}

function getDistrictName(id) {
    const names = {1:'أبو الحسن',2:'عين مران',3:'بني حواء',4:'بوقادير',5:'الشلف',6:'الكريمية',7:'المرسى',8:'وادي الفضة',9:'أولاد بن عبد القادر',10:'أولاد فارس',11:'تاوقريت',12:'تنس',13:'الزبوجة'};
    return names[id] || 'District ' + id;
}

function setMetric(metric, btn) {
    currentMetric = metric;
    document.querySelectorAll('.metric-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    updateColors();
}

function updateColors() {
    const covered = [1, 5, 10, 12, 13];
    covered.forEach(id => {
        const d = DATA[id];
        if (!d) return;
        const el = document.getElementById('d' + id);
        if (!el) return;

        let val = 0;
        if (currentMetric === 'n') val = d.n;
        else if (currentMetric === 'obesity') val = d.obesity;
        else if (currentMetric === 'bmi') val = d.bmi;
        else if (currentMetric === 'kidmed') val = d.kidmed;

        let color;
        if (currentMetric === 'n') {
            const maxN = Math.max(...covered.map(i => DATA[i]?.n || 0));
            const ratio = maxN > 0 ? d.n / maxN : 0;
            color = interpolateColor('#a8d8ea', '#1F4E79', ratio);
        } else if (currentMetric === 'obesity') {
            const ratio = Math.min(d.obesity / 30, 1);
            color = interpolateColor('#ffd59e', '#9b2335', ratio);
        } else if (currentMetric === 'bmi') {
            const ratio = Math.min(Math.max((d.bmi - 18) / 12, 0), 1);
            color = interpolateColor('#a8e6cf', '#e8593c', ratio);
        } else if (currentMetric === 'kidmed') {
            const ratio = Math.min(Math.max(d.kidmed / 8, 0), 1);
            color = interpolateColor('#e8593c', '#28a745', ratio);
        }
        el.style.fill = color;
    });
}

function interpolateColor(c1, c2, t) {
    const r1 = parseInt(c1.slice(1,3),16), g1 = parseInt(c1.slice(3,5),16), b1 = parseInt(c1.slice(5,7),16);
    const r2 = parseInt(c2.slice(1,3),16), g2 = parseInt(c2.slice(3,5),16), b2 = parseInt(c2.slice(5,7),16);
    const r = Math.round(r1 + (r2-r1)*t), g = Math.round(g1 + (g2-g1)*t), b = Math.round(b1 + (b2-b1)*t);
    return '#' + [r,g,b].map(v => v.toString(16).padStart(2,'0')).join('');
}

// Init colors
updateColors();
</script>
</body>
</html>
