<?php
/* ════════════════════════════════════════════════════════════
   about_data.inc.php — Données structurées pour about.php (v4)
   Inline en PHP (et non dans lang.php) car ce contenu détaillé
   n'apparaît que sur about.php et bénéficie d'une co-localisation
   avec son rendu. lang.php reste réservé aux libellés courts.
   ════════════════════════════════════════════════════════════ */

// ── SECTION 8 : Capacités complètes de la plateforme ──────────
$capsGroups = [
  [
    'title' => ['ar'=>'A. جمع البيانات', 'fr'=>'A. Collecte des données', 'en'=>'A. Data collection'],
    'items' => [
      ['ar'=>'إدخال <strong>ثلاثي اللغات</strong> (عربي / فرنسي / إنجليزي) مع تبديل فوري دون إعادة تحميل', 'fr'=>'Saisie <strong>trilingue</strong> (AR/FR/EN) avec bascule dynamique sans rechargement', 'en'=>'<strong>Trilingual</strong> input (AR/FR/EN) with live switching, no reload'],
      ['ar'=>'استبيان مُهيكَل بـ <strong>84 سؤالاً</strong> موزَّعة على <strong>5 خطوات</strong> مع شريط تقدّم بصري', 'fr'=>'Questionnaire structuré de <strong>84 items</strong> en <strong>5 étapes</strong> avec barre de progression visuelle', 'en'=>'Structured <strong>84-item</strong> questionnaire across <strong>5 steps</strong> with visual progress bar'],
      ['ar'=>'تحقّق فوري من القيم (حدود قصوى/دنيا للوزن والطول والعمر، اتساق متعدد الحقول)', 'fr'=>'Contrôles temps-réel : bornes min/max (poids, taille, âge), champs obligatoires, cohérence inter-champs', 'en'=>'Real-time validation: min/max bounds (weight, height, age), required fields, cross-field consistency'],
      ['ar'=>'حماية <strong>CSRF tokens</strong> على كل POST + <strong>rate limiting</strong> (إرسال واحد لكل IP كل 30 ثانية)', 'fr'=>'Protection <strong>CSRF tokens</strong> sur chaque POST + <strong>rate limiting</strong> (1 envoi / IP / 30 s)', 'en'=>'<strong>CSRF token</strong> protection on every POST + <strong>rate limiting</strong> (1 submission / IP / 30 s)'],
      ['ar'=>'سجل تدقيق (<code>audit log</code>) لكل إدراج / تعديل / حذف : هويّة المُشغِّل، التاريخ، الساعة، عنوان IP', 'fr'=>'Journal d\'audit (<code>audit log</code>) complet : opérateur, date, heure, IP par enregistrement', 'en'=>'Complete <code>audit log</code>: operator identity, date, time, IP address per record'],
      ['ar'=>'حصانة ضدّ <strong>SQL injection</strong> بنسبة 100% عبر <code>PDO prepared statements</code> حصراً', 'fr'=>'Élimination 100% des <strong>injections SQL</strong> via <code>PDO prepared statements</code> exclusivement', 'en'=>'100% <strong>SQL injection</strong> prevention via exclusive <code>PDO prepared statements</code>'],
    ]
  ],
  [
    'title' => ['ar'=>'B. الحسابات الآلية (16+ مؤشّر)', 'fr'=>'B. Calculs automatiques (16+ scores)', 'en'=>'B. Automated computation (16+ scores)'],
    'items' => [
      ['ar'=>'تصنيف <strong>IOTF</strong> الفوري (Cole et al. 2000) لحالة الوزن حسب العمر والجنس بدقّة 0,5 سنة', 'fr'=>'Classification <strong>IOTF</strong> en temps réel (Cole et al. 2000) selon âge et sexe à 0,5 an près', 'en'=>'Real-time <strong>IOTF</strong> classification (Cole et al. 2000) by age and sex, 0.5-year precision'],
      ['ar'=>'<strong>16 مؤشّراً مُشتقَّاً</strong> : KIDMED، نشاط، سكون، نوم، أسرة، ضغط مدرسي، تنوّع غذائي، خطر سمنة مُركَّب، فجوة إدراك الوزن، مؤشرات بديلة لخطر فقر الدم ونقص فيتامين D واضطرابات الأكل', 'fr'=>'<strong>16 scores dérivés</strong> : KIDMED, activité, sédentarité, sommeil, famille, pression scolaire, diversité alimentaire, risque obésité composite, écart perception/réalité, proxies risque anémie / vit-D / troubles alimentaires', 'en'=>'<strong>16 derived scores</strong>: KIDMED, activity, sedentarity, sleep, family, school pressure, dietary diversity, composite obesity-risk, body-perception gap, anemia / vit-D / eating-disorder risk proxies'],
      ['ar'=>'<strong>مؤشّر مزدوج للشاشة</strong> : <code>screen_max</code> (الأقصى على دعامة واحدة، رئيسي وفق HBSC 2018) + <code>screen_sum</code> (المجموع، للمقارنة مع الأدبيات الأقدم)', 'fr'=>'<strong>Indicateur d\'écran à double mesure</strong> : <code>screen_max</code> (max sur un support — principal, HBSC 2018) + <code>screen_sum</code> (cumul, pour comparabilité avec littérature antérieure)', 'en'=>'<strong>Dual screen indicator</strong>: <code>screen_max</code> (max on a single device — primary, HBSC 2018) + <code>screen_sum</code> (cumulative, for older-literature comparability)'],
      ['ar'=>'حساب IMC تلقائي ومُوحَّد، وتعريف موحَّد لـ <code>OW+OB</code> عبر كل صفحات الموقع', 'fr'=>'Calcul automatique de l\'IMC et définition unifiée de l\'<code>outcome OW+OB</code> sur l\'ensemble du site', 'en'=>'Automatic BMI computation and unified <code>OW+OB outcome</code> across all site pages'],
    ]
  ],
  [
    'title' => ['ar'=>'C. الاستبيان الشخصي المجهول', 'fr'=>'C. Module de questionnaire personnel anonyme', 'en'=>'C. Anonymous personal survey module'],
    'items' => [
      ['ar'=>'وحدة مستقلّة (<code>/personal-survey/</code>) تتيح لأي زائر تقييماً تغذوياً شخصياً دون تسجيل أو حفظ بيانات', 'fr'=>'Module séparé (<code>/personal-survey/</code>) pour une évaluation nutritionnelle personnelle sans inscription ni stockage', 'en'=>'Separate module (<code>/personal-survey/</code>) for personal nutritional self-assessment without registration or storage'],
      ['ar'=>'توليد <strong>نصائح صحّية مُخصَّصة</strong> آلياً بناءً على الملف الشخصي وفئة IOTF الذاتية', 'fr'=>'Génération automatique de <strong>conseils santé personnalisés</strong> selon le profil et l\'auto-classification IOTF', 'en'=>'Automatic <strong>personalized health advice</strong> from profile and IOTF self-classification'],
      ['ar'=>'تقرير مُلوَّن قابل للطباعة بلغة المستخدم (AR/FR/EN)', 'fr'=>'Rapport coloré imprimable dans la langue de l\'utilisateur (AR/FR/EN)', 'en'=>'Printable colored report in the user\'s language (AR/FR/EN)'],
    ]
  ],
  [
    'title' => ['ar'=>'D. التحليل الإحصائي في الوقت الفعلي', 'fr'=>'D. Analyse statistique en temps réel', 'en'=>'D. Real-time statistical analysis'],
    'items' => [
      ['ar'=>'<strong>3 صفحات تحليلية مكمّلة</strong> : <code>stats.php</code> (وصفي وثنائي)، <code>report.php</code> (متعدّد المتغيّرات + تحليلات متقدّمة)، <code>advanced_stats.php</code> (لوحة قيادة v2.0)', 'fr'=>'<strong>3 pages d\'analyse complémentaires</strong> : <code>stats.php</code> (descriptif + bivarié), <code>report.php</code> (multivarié + avancé), <code>advanced_stats.php</code> (dashboard v2.0)', 'en'=>'<strong>3 complementary analysis pages</strong>: <code>stats.php</code> (descriptive + bivariate), <code>report.php</code> (multivariate + advanced), <code>advanced_stats.php</code> (v2.0 dashboard)'],
      ['ar'=>'<strong>11 تبويباً تفاعلياً</strong> في <code>stats.php</code> : وصفي، مقارنة، ANOVA، ارتباط، لوجستي، FFQ، KIDMED، إدراك، Spearman، ROC، Bland-Altman', 'fr'=>'<strong>11 onglets interactifs</strong> dans <code>stats.php</code> : Description, Comparaison, ANOVA, Corrélation, Logistique, FFQ, KIDMED, Perception, Spearman, ROC, Bland-Altman', 'en'=>'<strong>11 interactive tabs</strong> in <code>stats.php</code>: Description, Comparison, ANOVA, Correlation, Logistic, FFQ, KIDMED, Perception, Spearman, ROC, Bland-Altman'],
      ['ar'=>'رسوم بيانية حيّة عبر <strong>Chart.js</strong> ولوحات KPI مُلوَّنة حسب القيمة (أخضر / برتقالي / أحمر)', 'fr'=>'Graphiques live via <strong>Chart.js</strong> et cartes KPI colorées selon la valeur (vert / orange / rouge)', 'en'=>'Live charts via <strong>Chart.js</strong> and color-coded KPI cards based on value (green / orange / red)'],
      ['ar'=>'محرّك <strong><a href="https://github.com/Touil-Elhadj/biostat-php">biostat-php</a></strong> (~2000 سطر من PHP خالص، مُحمَّل عبر Composer) — مكتبة إحصائية مستقلّة من الصفر دون اعتمادية Python/R/SPSS', 'fr'=>'Moteur <strong><a href="https://github.com/Touil-Elhadj/biostat-php">biostat-php</a></strong> (~2000 lignes de PHP pur, chargé via Composer) — bibliothèque statistique autonome from-scratch sans dépendance Python/R/SPSS', 'en'=>'<strong><a href="https://github.com/Touil-Elhadj/biostat-php">biostat-php</a></strong> engine (~2000 lines of pure PHP, loaded via Composer) — standalone statistical library built from scratch with no Python/R/SPSS dependencies'],
    ]
  ],
  [
    'title' => ['ar'=>'E. التقارير والتمثيل البصري', 'fr'=>'E. Rapports et visualisation', 'en'=>'E. Reports and visualization'],
    'items' => [
      ['ar'=>'تقرير توليفي تلقائي (<code>report.php</code>) يشمل التحليل المتعدّد المتغيّرات وكل الفرضيات الـ 68', 'fr'=>'Rapport de synthèse automatique (<code>report.php</code>) couvrant l\'analyse multivariée et les 68 hypothèses', 'en'=>'Automatic synthesis report (<code>report.php</code>) covering multivariate analysis and all 68 hypotheses'],
      ['ar'=>'<strong>خريطة تفاعلية</strong> لولاية الشلف بدقّة الدائرة (<code>map.php</code>) مع مؤشّرات مُجمَّعة لكل بلدية', 'fr'=>'<strong>Carte interactive</strong> de la wilaya de Chlef par daïra (<code>map.php</code>) avec indicateurs agrégés par commune', 'en'=>'<strong>Interactive map</strong> of Chlef wilaya by daïra (<code>map.php</code>) with per-commune aggregated indicators'],
      ['ar'=>'منحنيات <strong>ROC</strong> ورسوم <strong>Bland-Altman</strong> للتحقّق البصري من جودة النموذج', 'fr'=>'Courbes <strong>ROC</strong> et graphiques <strong>Bland-Altman</strong> pour validation visuelle de la qualité du modèle', 'en'=>'<strong>ROC curves</strong> and <strong>Bland-Altman</strong> plots for visual validation of model quality'],
      ['ar'=>'جدول قيم مفقودة بعلامات تنبيه آلية (<code>critical ≥20%</code>، <code>warning 10–20%</code>، <code>ok &lt;5%</code>) مع توصية معالجة لكل متغيّر', 'fr'=>'Tableau de données manquantes avec flags automatiques (<code>critical ≥20%</code>, <code>warning 10–20%</code>, <code>ok &lt;5%</code>) et recommandation par variable', 'en'=>'Missing-data dashboard with automatic flags (<code>critical ≥20%</code>, <code>warning 10–20%</code>, <code>ok &lt;5%</code>) and per-variable recommendation'],
    ]
  ],
  [
    'title' => ['ar'=>'F. إدارة البيانات والوصول', 'fr'=>'F. Gestion des données et accès', 'en'=>'F. Data management and access'],
    'items' => [
      ['ar'=>'قاعدة <strong>MySQL 8</strong> تستوعب 1020 سجلاً × 127 متغيّراً مع فهارس مُحسَّنة', 'fr'=>'Base <strong>MySQL 8</strong> hébergeant 1020 enregistrements × 127 variables avec index optimisés', 'en'=>'<strong>MySQL 8</strong> database hosting 1020 records × 127 variables with optimised indexes'],
      ['ar'=>'تصدير كامل بصيغة <strong>JSON</strong> + نسخة احتياطية تلقائية (<code>backup.php</code>) للاسترداد', 'fr'=>'Export complet en <strong>JSON</strong> + sauvegarde automatique (<code>backup.php</code>) pour restauration', 'en'=>'Complete <strong>JSON</strong> export + automatic backup (<code>backup.php</code>) for restoration'],
      ['ar'=>'<strong>ثلاثة مستويات وصول</strong> : مشرف (قراءة-كتابة)، زائر (قراءة فقط)، استبيان شخصي مجهول', 'fr'=>'<strong>Trois niveaux d\'accès</strong> : admin (lecture/écriture), invité (lecture seule), questionnaire personnel anonyme', 'en'=>'<strong>Three access levels</strong>: admin (read/write), guest (read-only), anonymous personal survey'],
      ['ar'=>'سجلّات الأخطاء على الخادم (<code>logs/errors.log</code>) وتحصين <code>.htaccess</code> ضدّ الفهرسة المباشرة', 'fr'=>'Logs serveur (<code>logs/errors.log</code>) et durcissement <code>.htaccess</code> contre l\'indexation directe', 'en'=>'Server logs (<code>logs/errors.log</code>) and <code>.htaccess</code> hardening against direct indexing'],
    ]
  ],
];

// ── SECTION 9 : Catalogue des tests biostatistiques ──────────
// Chaque test : nom, référence, ce qu'il fait, pourquoi nous l'avons choisi
$testsCatalog = [

/* ═════ Catégorie A — Descriptives & univariées (3 tests) ═════ */
[
  'cat_title' => ['ar'=>'A. الإحصاءات الوصفية والأحادية', 'fr'=>'A. Statistiques descriptives et univariées', 'en'=>'A. Descriptive and univariate statistics'],
  'tests' => [
    [
      'name' => 'Mesures descriptives (moyenne, écart-type, médiane, quartiles, fréquences)',
      'ref'  => 'BiostatPHP::mean / std / median / quantile',
      'what' => [
        'ar'=>'يحسب مقاييس النزعة المركزية (المتوسط، الوسيط) والتشتّت (الانحراف المعياري، الرباعيات Q1–Q3) للمتغيرات الكمّية، والتكرارات والنسب للمتغيرات النوعية.',
        'fr'=>'Calcule la tendance centrale (moyenne, médiane), la dispersion (écart-type, quartiles Q1–Q3) pour les variables quantitatives, et les fréquences pour les qualitatives.',
        'en'=>'Computes central tendency (mean, median), dispersion (standard deviation, quartiles Q1–Q3) for quantitative variables, and frequencies for qualitative ones.',
      ],
      'why' => [
        'ar'=>'الخطوة الأولى الإلزامية في كل دراسة وبائية. نستخدم المتوسط ± SD للتوزيعات القريبة من النرماليّة (IMC = 21,27 ± 3,14)، والوسيط + IQR للتوزيعات المائلة (KIDMED). يتيح المقارنة المباشرة مع الأدبيات الدولية.',
        'fr'=>'Étape obligatoire première de toute étude épidémiologique. Moyenne ± SD pour les distributions proches de la normale (IMC = 21,27 ± 3,14), médiane + IQR pour les distributions asymétriques (KIDMED). Permet la comparaison directe avec la littérature internationale.',
        'en'=>'Mandatory first step of any epidemiological study. Mean ± SD for near-normal distributions (BMI = 21.27 ± 3.14), median + IQR for skewed ones (KIDMED). Enables direct comparison with international literature.',
      ],
    ],
    [
      'name' => 'Classification IOTF (cut-offs internationaux pédiatriques)',
      'ref'  => 'Cole et al. (2000) BMJ ; Cole & Lobstein (2012) Pediatr Obes',
      'what' => [
        'ar'=>'يصنّف كل تلميذ ضمن أربع فئات (نحافة / وزن طبيعي / زيادة وزن / سمنة) باستخدام قيم قطع IMC الخاصة بالعمر والجنس (5–18 سنة) من International Obesity Task Force.',
        'fr'=>'Classe chaque élève en quatre catégories (minceur / normal / surpoids / obésité) à partir des cut-offs IMC spécifiques à l\'âge et au sexe (5–18 ans) de l\'International Obesity Task Force.',
        'en'=>'Classifies each student into four categories (thinness / normal / overweight / obesity) using age- and sex-specific BMI cut-offs (5–18 years) from the International Obesity Task Force.',
      ],
      'why' => [
        'ar'=>'اختير IOTF عوض WHO وCDC لأن قيم القطع مُعايرة لتلتقي مع عتبات البالغين (18,5 / 25 / 30 kg/m²)، فتتيح المقارنة المباشرة مع أدبيات سمنة البالغين في الجزائر والمتوسّط.',
        'fr'=>'IOTF préféré à WHO/CDC car ses cut-offs sont calibrés pour rejoindre les seuils adultes (18,5 / 25 / 30 kg/m²), permettant la comparaison directe avec la littérature obésité adulte en Algérie et au Maghreb.',
        'en'=>'IOTF chosen over WHO/CDC because its cut-offs are calibrated to align with adult thresholds (18.5 / 25 / 30 kg/m²), enabling direct comparison with adult obesity literature in Algeria and the Maghreb.',
      ],
    ],
    [
      'name' => 'Indicateur d\'écran à double mesure (screen_max / screen_sum)',
      'ref'  => 'Ng et al. (2018) HBSC Methodology ; OMS 2019',
      'what' => [
        'ar'=>'يحسب الوقت أمام الشاشات وفق مؤشّرَين : <code>screen_max</code> هو الزمن الأقصى على دعامة واحدة (هاتف / تلفاز / ألعاب / حاسوب)، و<code>screen_sum</code> هو المجموع التراكمي للدعامات الأربع.',
        'fr'=>'Mesure le temps d\'écran par deux indicateurs : <code>screen_max</code> est la durée maximale sur un support unique (téléphone / TV / jeux / ordinateur), <code>screen_sum</code> est la somme cumulée des quatre.',
        'en'=>'Computes screen time via two indicators: <code>screen_max</code> is the max on a single device (phone / TV / gaming / computer), <code>screen_sum</code> is the cumulative sum of all four.',
      ],
      'why' => [
        'ar'=>'وثَّق تقرير HBSC 2018 (Ng et al.) أن جمع الدعامات يُبالغ ميكانيكيّاً في تقدير الزمن الفعلي بسبب <strong>multi-screen behavior</strong>. استخدام المؤشّرَين كشف في دراستنا أن العلاقة "الواقية" بين الشاشات والـ IMC (OR=0,489 بـ screen_sum) تختفي مع المؤشّر الرئيسي المُصحَّح (OR=0,781 بـ screen_max، p=0,205) — اكتشاف منهجي قابل للنشر بحدّ ذاته.',
        'fr'=>'Le rapport HBSC 2018 (Ng et al.) a documenté que la sommation surestime mécaniquement la durée réelle à cause du <strong>multi-screen behavior</strong>. L\'emploi des deux indicateurs a révélé que l\'association "protectrice" entre écrans et IMC (OR=0,489 avec screen_sum) disparaît avec l\'indicateur principal corrigé (OR=0,781 avec screen_max, p=0,205) — résultat méthodologique publiable en soi.',
        'en'=>'HBSC 2018 (Ng et al.) documented that summation mechanically overestimates real duration via <strong>multi-screen behavior</strong>. Using both indicators revealed the "protective" screen–BMI association (OR=0.489 with screen_sum) vanishes with the corrected primary indicator (OR=0.781 with screen_max, p=0.205) — a publishable methodological finding in itself.',
      ],
    ],
  ]
],

/* ═════ Catégorie B — Tests bivariés (7 tests) ═════ */
[
  'cat_title' => ['ar'=>'B. الاختبارات الثنائية المتغيّر', 'fr'=>'B. Tests bivariés', 'en'=>'B. Bivariate tests'],
  'tests' => [
    [
      'name' => 'χ² (Chi-deux de Pearson) avec correction de Yates',
      'ref'  => 'Pearson (1900) ; Yates (1934) ; BiostatPHP::chi2Test2x2',
      'what' => [
        'ar'=>'يختبر استقلاليّة متغيّرَين نوعيَّين في جدول 2×2 أو r×c بمقارنة التكرارات الملاحَظة بالمتوقَّعة. يُرجع χ² وp-value و(للجداول 2×2) OR + IC95%.',
        'fr'=>'Teste l\'indépendance entre deux variables qualitatives dans un tableau 2×2 ou r×c en comparant effectifs observés et attendus. Retourne χ², p-value, et (pour 2×2) OR + IC95%.',
        'en'=>'Tests independence between two qualitative variables in a 2×2 or r×c table by comparing observed vs expected counts. Returns χ², p-value, and (for 2×2) OR + 95% CI.',
      ],
      'why' => [
        'ar'=>'الاختبار المرجعي لربط متغيّرنا التابع (OW/OB) بكل المتنبّئات النوعية (الجنس، سمنة الوالدَين، تجاوز الوجبات، إلخ.) — المُحكّ المنهجي المُعلَن في القسم 2.8.3 من المذكرة. <strong>correction Yates</strong> تمنع رفض H₀ الزائد عندما تكون التكرارات المتوقَّعة صغيرة، وفق ممارسة SPSS وR القياسية.',
        'fr'=>'Test de référence pour croiser notre outcome principal (OW/OB) avec tous les prédicteurs qualitatifs (sexe, obésité parentale, saut de repas, etc.) — pivot méthodologique annoncé en section 2.8.3 du mémoire. La <strong>correction de Yates</strong> évite le sur-rejet de H₀ pour les petits effectifs attendus, suivant la pratique standard SPSS et R.',
        'en'=>'Reference test for crossing our main outcome (OW/OB) with all qualitative predictors (sex, parental obesity, meal skipping, etc.) — methodological pivot from thesis section 2.8.3. <strong>Yates correction</strong> prevents over-rejection of H₀ for small expected counts, per standard SPSS and R practice.',
      ],
    ],
    [
      'name' => 'Test t de Welch (Student t modifié pour variances inégales)',
      'ref'  => 'Welch (1947) Biometrika ; BiostatPHP::tTest',
      'what' => [
        'ar'=>'يقارن متوسطَي مجموعتين كمّيَّتين عندما تكون التباينات غير متساوية. يستخدم درجات الحرّية المعدَّلة بطريقة <strong>Satterthwaite</strong>، الأدقّ من Student الكلاسيكي تحت heteroskedasticity.',
        'fr'=>'Compare les moyennes de deux groupes quantitatifs quand les variances sont inégales. Utilise les ddl ajustés par <strong>Satterthwaite</strong>, plus précis que le Student classique sous hétéroscédasticité.',
        'en'=>'Compares means of two quantitative groups when variances are unequal. Uses <strong>Satterthwaite</strong>-adjusted degrees of freedom, more accurate than classical Student under heteroskedasticity.',
      ],
      'why' => [
        'ar'=>'فُضِّل Welch على Student الكلاسيكي لأن التباينات تختلف بنيوياً بين OW/OB والوزن الطبيعي في كل المتغيرات تقريباً (تباين IMC أكبر عند OW/OB بطبيعته). استُخدم 9 مرّات : مقارنات الجنسَين (KIDMED، النشاط، النوم، الضغط) ومقارنات OW/OB. Student العادي كان سيرفع معدّل الخطأ من النوع الأول بنحو 5–15%.',
        'fr'=>'Welch a été préféré au Student car les variances diffèrent structurellement entre OW/OB et poids normal sur quasi toutes les variables (variance d\'IMC intrinsèquement plus large chez les OW/OB). Employé 9 fois : comparaisons garçons/filles (KIDMED, activité, sommeil, pression) et OW/OB vs normal. Student aurait gonflé le risque α de 5–15 % sous hétéroscédasticité.',
        'en'=>'Welch preferred over Student because variances differ structurally between OW/OB and normal on nearly all variables (BMI variance intrinsically larger in OW/OB). Used 9× : boys/girls comparisons (KIDMED, activity, sleep, pressure) and OW/OB vs normal. Student would have inflated Type I error by 5–15% under heteroskedasticity.',
      ],
    ],
    [
      'name' => 'ANOVA à un facteur (analyse de variance)',
      'ref'  => 'Fisher (1925) ; BiostatPHP::anova',
      'what' => [
        'ar'=>'يقارن متوسّطات 3 مجموعات أو أكثر دفعةً واحدة بحساب نسبة التباين بين-المجموعات إلى التباين داخل-المجموعات (إحصائيّة F). يُرجع F، df_B، df_W، وp-value.',
        'fr'=>'Compare les moyennes de 3 groupes ou plus simultanément en calculant le ratio variance inter / variance intra (statistique F). Retourne F, df_B, df_W, p-value.',
        'en'=>'Compares means of 3+ groups simultaneously via the between- / within-group variance ratio (F statistic). Returns F, df_B, df_W, p-value.',
      ],
      'why' => [
        'ar'=>'حاسم لمقارنات المستوى الدراسي (1AS / 2AS / 3AS) — أحد محاور الدراسة الكبرى. إجراء ثلاث مقارنات Welch منفصلة سيرفع معدّل الخطأ الإجمالي إلى ~14% عوض 5%. ANOVA يحلّ هذا بمقارنة شاملة واحدة، وقد كشف فروقاً معنوية في KIDMED (F=6,85 ; p=0,001) والضغط المدرسي (F=7,77 ; p<0,001) بين المستويات.',
        'fr'=>'Indispensable pour les comparaisons par niveau scolaire (1AS / 2AS / 3AS) — un des axes majeurs. Trois Welch séparés feraient grimper le risque α global à ≈14 % au lieu de 5 %. L\'ANOVA résout cela en une comparaison globale, révélant des écarts significatifs en KIDMED (F=6,85 ; p=0,001) et pression scolaire (F=7,77 ; p<0,001) entre niveaux.',
        'en'=>'Essential for grade-level comparisons (1AS / 2AS / 3AS) — a major study axis. Three separate Welch tests would inflate overall α to ≈14% instead of 5%. ANOVA solves this with a single global test, revealing significant gaps in KIDMED (F=6.85; p=0.001) and school pressure (F=7.77; p<0.001) across grades.',
      ],
    ],
    [
      'name' => 'Corrélation de Pearson (r)',
      'ref'  => 'Pearson (1895) ; BiostatPHP::pearson',
      'what' => [
        'ar'=>'يقيس قوّة واتّجاه العلاقة الخطّية بين متغيّرَين كمّيَّين متّصلَين، مع p-value باختبار Student-t. r ∈ [-1, +1].',
        'fr'=>'Mesure la force et le sens de la relation linéaire entre deux variables quantitatives continues, avec p-value via test t. r ∈ [-1, +1].',
        'en'=>'Measures strength and direction of linear relationship between two continuous quantitative variables, with p-value via t test. r ∈ [-1, +1].',
      ],
      'why' => [
        'ar'=>'استُخدم لاختبار <strong>"مفارقة KIDMED"</strong> — العلاقة الموجبة المتناقضة ظاهرياً بين جودة الحمية المتوسّطية والـ IMC (r=0,153 ; p<0,001). الفرضية البديهية كانت ارتباطاً سالباً، فجاءت النتيجة العكسية وأطلقت قراءة منهجية (التحوّل التغذوي، الكثافة الطاقية للنمط المتوسّطي الجزائري). Pearson هو المعيار الذهبي للبيانات المتّصلة قريبة النرماليّة.',
        'fr'=>'Utilisé pour tester le <strong>"paradoxe KIDMED"</strong> — la corrélation positive contre-intuitive entre qualité du régime méditerranéen et IMC (r=0,153 ; p<0,001). L\'intuition prédisait une corrélation négative ; le résultat inverse a généré une interprétation méthodologique (transition nutritionnelle, densité énergétique du régime méditerranéen algérien). Gold standard pour données continues proches de la normalité.',
        'en'=>'Used to test the <strong>"KIDMED paradox"</strong> — the counter-intuitive positive correlation between Mediterranean diet quality and BMI (r=0.153; p<0.001). Intuition predicted a negative correlation; the opposite generated a methodological interpretation (nutritional transition, energy density of Algerian Mediterranean diet). Gold standard for near-normal continuous data.',
      ],
    ],
    [
      'name' => 'Corrélation de Spearman (ρ) — non paramétrique',
      'ref'  => 'Spearman (1904) ; BiostatPHP::spearman',
      'what' => [
        'ar'=>'مرادف Pearson مُطبَّق على رتب القيم بدل القيم ذاتها. غير حسّاس للقيم المتطرّفة ولا يفترض النرماليّة. يُرجع ρ ∈ [-1, +1] وp-value.',
        'fr'=>'Équivalent non paramétrique de Pearson : appliqué aux rangs plutôt qu\'aux valeurs brutes. Insensible aux valeurs aberrantes, n\'exige pas la normalité. Retourne ρ ∈ [-1, +1] et p-value.',
        'en'=>'Non-parametric equivalent of Pearson: applied to value ranks rather than raw values. Insensitive to outliers, no normality assumption. Returns ρ ∈ [-1, +1] and p-value.',
      ],
      'why' => [
        'ar'=>'يُجرى بالتوازي مع Pearson للتحقّق من أن العلاقات لا تعتمد على فرضية النرماليّة. في حالة KIDMED–IMC، Spearman ρ=0,124 ; p<0,001 (مقابل Pearson 0,153) يُؤكِّد أن المفارقة ليست أثر قيم متطرّفة أو توزيع غير طبيعي بل ظاهرة حقيقية. هذا التحقّق المتبادل ضروري للنشر في مجلّات رفيعة.',
        'fr'=>'Exécuté en parallèle de Pearson pour vérifier que les associations ne dépendent pas de la normalité. Pour KIDMED–IMC, Spearman ρ=0,124 ; p<0,001 (vs Pearson 0,153) confirme que le paradoxe n\'est pas un artefact de valeurs extrêmes ni de distribution non gaussienne, mais un phénomène réel. Validation croisée exigée par les revues à comité de lecture.',
        'en'=>'Run in parallel with Pearson to verify associations don\'t depend on normality. For KIDMED–BMI, Spearman ρ=0.124; p<0.001 (vs Pearson 0.153) confirms the paradox is not an outlier artefact or non-Gaussian distribution but a real phenomenon. Cross-validation required by peer-reviewed journals.',
      ],
    ],
    [
      'name' => 'Odds Ratio + Intervalle de confiance à 95% (méthode de Woolf)',
      'ref'  => 'Woolf (1955) ; Bland & Altman (2000) BMJ ; BiostatPHP::oddsRatio',
      'what' => [
        'ar'=>'يقيس قوّة العلاقة في جدول 2×2 : OR = (a·d)/(b·c). يُرفَق بمجال ثقة 95% بطريقة <strong>Woolf</strong> (تحويل لوغاريتمي + Wald). تفسير : OR=1 لا أثر، OR=2 خطر مضاعف.',
        'fr'=>'Mesure la force d\'association dans un tableau 2×2 : OR = (a·d)/(b·c). Avec IC95% par méthode de <strong>Woolf</strong> (transformation log + Wald). OR=1 : pas d\'effet ; OR=2 : risque doublé.',
        'en'=>'Measures association strength in a 2×2 table: OR = (a·d)/(b·c). With 95% CI via <strong>Woolf</strong>\'s method (log transform + Wald). OR=1: no effect; OR=2: doubled risk.',
      ],
      'why' => [
        'ar'=>'OR هو المقياس الوبائي القياسي لقوّة الأثر : أكثر قابلية للتفسير من p-value (التي تخبر فقط بوجود الأثر، لا بحجمه). يتيح للقارئ غير الإحصائي قول : "أبناء الوالدَين البَدِنَين معرّضون لخطر X أضعاف." طريقة Woolf اختيرت لبساطتها الحسابية وتطابقها التامّ مع SPSS v.25 وR epitools.',
        'fr'=>'L\'OR est la métrique épidémiologique standard de la taille d\'effet : plus interprétable que la p-value (qui indique seulement l\'existence de l\'effet, pas son ampleur). Permet de dire : « les élèves d\'un parent obèse ont X fois plus de risque ». Méthode Woolf retenue pour sa simplicité et sa concordance parfaite avec SPSS v.25 et R epitools.',
        'en'=>'OR is the standard epidemiological metric of effect size: more interpretable than p-value (which only signals existence, not magnitude). Lets readers say: "students of obese parents have X-fold higher risk." Woolf method chosen for simplicity and exact concordance with SPSS v.25 and R epitools.',
      ],
    ],
    [
      'name' => 'Test de tendance Cochran-Armitage',
      'ref'  => 'Cochran (1954) ; Armitage (1955) ; report.php::cochranArmitageTrend',
      'what' => [
        'ar'=>'يختبر وجود اتّجاه خطّي في نِسب الحدث عبر فئات مرتَّبة (المستوى 1AS<2AS<3AS، الضغط منخفض<متوسط<مرتفع). يُرجع إحصائية Z ذات اتجاه وp-value.',
        'fr'=>'Teste l\'existence d\'une progression linéaire des proportions à travers des catégories ordonnées (niveau 1AS<2AS<3AS, pression bas<moyen<haut). Retourne une statistique Z signée et p-value.',
        'en'=>'Tests for a linear trend in event proportions across ordered categories (grade 1AS<2AS<3AS, pressure low<medium<high). Returns a signed Z statistic and p-value.',
      ],
      'why' => [
        'ar'=>'χ² الكلاسيكي يكشف عدم الاستقلاليّة لكنه لا يميّز بين تشتّت عشوائي وتدرّج منتظم. حالتنا تستلزم اختبار التدرّج : <strong>تجاوز الوجبات للدروس الخصوصية يرتفع 47,3% → 52,4% → 60,7% عبر 1AS→2AS→3AS</strong>. χ² العادي يعطي p=0,003، لكن Cochran-Armitage يعطي Z=2,33 ; p=0,020 ويُؤكِّد أن التدرّج <em>خطّي ومنتظم</em> — يعزّز فرضية تأثير النظام التربوي.',
        'fr'=>'Le χ² classique détecte la non-indépendance mais ne distingue pas dispersion aléatoire et progression monotone. Notre cas exige un test de tendance : <strong>le saut de repas pour cours de soutien grimpe 47,3 % → 52,4 % → 60,7 % de 1AS à 3AS</strong>. Le χ² ordinaire donne p=0,003, mais Cochran-Armitage donne Z=2,33 ; p=0,020 et confirme que la progression est <em>linéaire et monotone</em> — renforce l\'hypothèse d\'effet du système éducatif.',
        'en'=>'Plain χ² detects non-independence but doesn\'t distinguish random dispersion from monotone progression. Our case requires a trend test: <strong>meal-skipping for private tutoring rises 47.3% → 52.4% → 60.7% from 1AS to 3AS</strong>. Plain χ² gives p=0.003, but Cochran-Armitage yields Z=2.33; p=0.020 and confirms the progression is <em>linear and monotone</em> — reinforces the education-system hypothesis.',
      ],
    ],
  ]
],

/* ═════ Catégorie C — Régression logistique multivariée (4 tests) ═════ */
[
  'cat_title' => ['ar'=>'C. الانحدار اللوجستي المتعدّد المتغيرات', 'fr'=>'C. Régression logistique multivariée', 'en'=>'C. Multivariate logistic regression'],
  'tests' => [
    [
      'name' => 'Régression logistique binaire (Newton-Raphson)',
      'ref'  => 'McCullagh & Nelder (1989) ; BiostatPHP::logisticRegressionMulti',
      'what' => [
        'ar'=>'يُنمذِج احتمال متغيّر ثنائي (OW/OB) كدالّة لوجستية لعدّة متنبّئات في آن واحد. <strong>Newton-Raphson</strong> يحلّ معادلات الـ score بتقارب تربيعي (≤ 6 تكرارات عادةً، تسامح 10⁻⁶). يُرجع β، SE، OR المعدَّلة، AUC، Hosmer-Lemeshow، وAIC.',
        'fr'=>'Modélise la probabilité d\'un outcome binaire (OW/OB) comme fonction logistique de plusieurs prédicteurs simultanément. <strong>Newton-Raphson</strong> résout les équations du score à convergence quadratique (≤ 6 itérations typiquement, tolérance 10⁻⁶). Retourne β, SE, OR ajustés, AUC, Hosmer-Lemeshow, AIC.',
        'en'=>'Models the probability of a binary outcome (OW/OB) as a logistic function of several predictors. <strong>Newton-Raphson</strong> solves the score equations with quadratic convergence (≤ 6 iterations typically, tolerance 10⁻⁶). Returns β, SE, adjusted OR, AUC, Hosmer-Lemeshow, AIC.',
      ],
      'why' => [
        'ar'=>'المعيار الذهبي عالمياً لـ outcome ثنائي مع ضبط المُربكات. KIDMED والنشاط والنوم والضغط مترابطة فيما بينها : التحليل الثنائي وحده يضخّم أو يخفي الأثر الحقيقي. الانحدار المتعدّد يفصل التأثير الصافي لكل متنبّئ. <strong>Newton-Raphson</strong> اختير لتقاربه التربيعي (4 مرّات أسرع من gradient descent) وتطابقه التامّ مع <code>glm(family=binomial)</code> في R وSPSS Logistic.',
        'fr'=>'Gold standard mondial pour outcome binaire avec contrôle des facteurs de confusion. KIDMED, activité, sommeil et pression sont inter-corrélés : une analyse bivariée seule amplifie ou masque les vrais effets. La régression multivariée isole l\'effet net de chaque prédicteur. <strong>Newton-Raphson</strong> choisi pour sa convergence quadratique (4× plus rapide que gradient descent) et sa concordance parfaite avec <code>glm(family=binomial)</code> de R et SPSS Logistic.',
        'en'=>'World gold standard for binary outcomes with confounder control. KIDMED, activity, sleep, and pressure are inter-correlated: bivariate analysis alone amplifies or masks real effects. Multivariate regression isolates each predictor\'s net effect. <strong>Newton-Raphson</strong> chosen for quadratic convergence (4× faster than gradient descent) and exact concordance with R\'s <code>glm(family=binomial)</code> and SPSS Logistic.',
      ],
    ],
    [
      'name' => 'Test de Hosmer-Lemeshow (qualité d\'ajustement)',
      'ref'  => 'Hosmer & Lemeshow (2013) 3e éd. Wiley ; BiostatPHP::hosmerLemeshow',
      'what' => [
        'ar'=>'يختبر تطابق الاحتمالات المتنبَّأ بها مع التكرارات المُلاحَظة بتقسيم العيّنة إلى 10 ديسيلات (g=10) من احتمال الخطر. p > 0,05 يعني نموذجاً مُعايَراً جيداً.',
        'fr'=>'Teste si les probabilités prédites correspondent aux fréquences observées en partitionnant l\'échantillon en 10 déciles (g=10) de probabilité de risque. p > 0,05 indique un bon ajustement.',
        'en'=>'Tests whether predicted probabilities match observed frequencies by partitioning the sample into 10 deciles (g=10) of risk probability. p > 0.05 indicates good fit.',
      ],
      'why' => [
        'ar'=>'AUC يقيس "قدرة التمييز" لكنه لا يقيس "المعايرة" (هل الاحتمالات المتنبّأ بها واقعية ؟). نموذج قد يفصل OW/OB عن الطبيعي جيداً لكن يبالغ في الاحتمالات بشكل منظومي. Hosmer-Lemeshow هو الاختبار المعياري لهذه المشكلة. نموذجنا الرئيسي ينجح بـ χ²=7,16 ; p=0,520 (ممتاز).',
        'fr'=>'L\'AUC mesure la "discrimination" mais pas la "calibration" (les probabilités prédites sont-elles réalistes ?). Un modèle peut bien séparer OW/OB du normal mais surestimer systématiquement les probabilités. Hosmer-Lemeshow est le test standard pour ce défaut. Notre modèle passe avec χ²=7,16 ; p=0,520 (excellent).',
        'en'=>'AUC measures "discrimination" but not "calibration" (are predicted probabilities realistic?). A model may separate OW/OB from normal well yet systematically overestimate probabilities. Hosmer-Lemeshow is the standard test for this flaw. Our main model passes with χ²=7.16; p=0.520 (excellent).',
      ],
    ],
    [
      'name' => 'AUC ROC (Aire sous la courbe ROC) — Hanley-McNeil',
      'ref'  => 'Hanley & McNeil (1982) Radiology 143:29-36',
      'what' => [
        'ar'=>'يقيس قدرة النموذج على التمييز بين الحالات (OW/OB) والشواهد (طبيعي). يساوي احتمال أن تلميذاً OW/OB عشوائيّاً يحصل على درجة خطر أعلى من تلميذ طبيعي عشوائي. 0,5 عشوائي، 0,7 مقبول، 0,8 جيّد، 0,9 ممتاز.',
        'fr'=>'Mesure la capacité du modèle à distinguer cas (OW/OB) et témoins (normal). Égal à la probabilité qu\'un élève OW/OB tiré au hasard obtienne un score supérieur à un élève normal. 0,5 aléatoire ; 0,7 acceptable ; 0,8 bon ; 0,9 excellent.',
        'en'=>'Measures the model\'s ability to discriminate cases (OW/OB) from controls (normal). Equals the probability that a randomly chosen OW/OB student gets a higher score than a random normal student. 0.5 random; 0.7 acceptable; 0.8 good; 0.9 excellent.',
      ],
      'why' => [
        'ar'=>'مقياس مُلخِّص واحد لقوّة التمييز، مستقلٌّ عن العتبة المختارة. أفضل من الحساسية والنوعية وحدهما (اللتَين تعتمدان على عتبة). AUC نموذجنا = 0,663 (متواضع لكنه في نطاق الأدبيات : 0,60–0,70 للنماذج السلوكية البحتة عند المراهقين). <strong>Hanley-McNeil</strong> اختيرت لسرعتها (30× أسرع من Trapezoidal) ومطابقتها R::pROC.',
        'fr'=>'Métrique synthétique unique de la puissance discriminative, indépendante du seuil choisi. Supérieur à sensibilité/spécificité (dépendantes d\'un seuil). AUC = 0,663 dans notre modèle (modeste mais dans la fourchette littérature : 0,60–0,70 pour modèles purement comportementaux chez l\'adolescent). <strong>Hanley-McNeil</strong> retenue : 30× plus rapide que trapézoïdale et alignée sur R::pROC.',
        'en'=>'Single summary measure of discriminative power, independent of any chosen threshold. Superior to sensitivity/specificity alone (which depend on a cutoff). Our model\'s AUC = 0.663 (modest but in the literature range: 0.60–0.70 for purely behavioural adolescent models). <strong>Hanley-McNeil</strong> chosen: 30× faster than trapezoidal and aligned with R::pROC.',
      ],
    ],
    [
      'name' => 'Pseudo-R² de McFadden + AIC + LLR',
      'ref'  => 'McFadden (1974) ; Akaike (1974)',
      'what' => [
        'ar'=>'مؤشّرات إضافية لجودة النموذج : <strong>McFadden R²</strong> = 1 − (ℓ_model/ℓ_null) كنسبة log-likelihood المُفسَّر؛ <strong>AIC</strong> = 2k − 2ℓ لاختيار النماذج (الأصغر أفضل)؛ <strong>LLR p-value</strong> لمعنويّة النموذج الكلّية.',
        'fr'=>'Indicateurs complémentaires : <strong>R² de McFadden</strong> = 1 − (ℓ_modèle/ℓ_nul) comme fraction de log-vraisemblance expliquée ; <strong>AIC</strong> = 2k − 2ℓ pour sélection de modèles (plus petit = meilleur) ; <strong>LLR p-value</strong> pour significativité globale.',
        'en'=>'Additional model-quality indicators: <strong>McFadden R²</strong> = 1 − (ℓ_model/ℓ_null) as the fraction of log-likelihood explained; <strong>AIC</strong> = 2k − 2ℓ for model selection (smaller = better); <strong>LLR p-value</strong> for overall significance.',
      ],
      'why' => [
        'ar'=>'R² العادي لا يعمل في الانحدار اللوجستي (outcome ثنائي). McFadden هو البديل المعتمد، أعطى 0,053 (V1) و 0,066 (V2) — استنتاج صادق أن البيانات السلوكية المُصرَّح بها وحدها تُفسِّر جزءاً متواضعاً من تباين IMC. AIC يقارن V1 (=804,9) و V2 (=794,0) موضوعياً : فرق 11 يميل قليلاً نحو V2. LLR يؤكِّد تفوّق النموذج على النموذج الفارغ بـ p < 0,001.',
        'fr'=>'Le R² ordinaire ne fonctionne pas en régression logistique. McFadden est l\'alternative établie ; il a donné 0,053 (V1) et 0,066 (V2) — conclusion honnête : les données comportementales auto-déclarées seules n\'expliquent qu\'une fraction modeste de la variance d\'IMC. AIC compare objectivement V1 (=804,9) et V2 (=794,0) : un écart de 11 favorise marginalement V2. LLR confirme la supériorité du modèle sur le nul à p < 0,001.',
        'en'=>'Ordinary R² doesn\'t work in logistic regression. McFadden is the established alternative; it gave 0.053 (V1) and 0.066 (V2) — honest conclusion that self-reported behavioural data alone explain only a modest fraction of BMI variance. AIC objectively compares V1 (=804.9) and V2 (=794.0): a gap of 11 marginally favours V2. LLR confirms model superiority over null at p < 0.001.',
      ],
    ],
  ]
],

/* ═════ Catégorie D — Méthodes avancées v2.0 (7 tests) ═════ */
[
  'cat_title' => ['ar'=>'D. الطرق المتقدّمة (v2.0)', 'fr'=>'D. Méthodes avancées (v2.0)', 'en'=>'D. Advanced methods (v2.0)'],
  'tests' => [
    [
      'name' => 'VIF (Variance Inflation Factor) — détection de multicolinéarité',
      'ref'  => 'Allison (2012) ; BiostatPHP::vif',
      'what' => [
        'ar'=>'يقيس درجة ارتباط كل متنبّئ X_j بالمتنبّئات الأخرى عبر انحدار مساعد. VIF_j = 1 / (1 − R²_j). VIF < 2,5 مقبول؛ 2,5–5 يستحق المراقبة؛ > 5 مشكل؛ > 10 خطير.',
        'fr'=>'Mesure la corrélation de chaque prédicteur X_j avec les autres via une régression auxiliaire. VIF_j = 1 / (1 − R²_j). VIF < 2,5 acceptable ; 2,5–5 à surveiller ; > 5 problématique ; > 10 critique.',
        'en'=>'Measures each predictor X_j\'s correlation with others via an auxiliary regression. VIF_j = 1 / (1 − R²_j). VIF < 2.5 acceptable; 2.5–5 monitor; > 5 problematic; > 10 critical.',
      ],
      'why' => [
        'ar'=>'قبل الوثوق بـ OR من النموذج المتعدّد، يجب التحقّق من أن المتنبّئات ليست مكرّرة. إذا كان مؤشّر النشاط مرتبطاً بشدّة بمؤشّر السكون، تصبح معاملاتهما غير مستقرّة. VIF ينبّه إلى المشكلة قبل التفسير. كل VIFs نموذجنا < 2,5، فالنموذج "محدَّد التحديد" (identifiable).',
        'fr'=>'Avant de faire confiance aux OR multivariés, il faut vérifier que les prédicteurs ne sont pas redondants. Si activité corrèle fort avec sédentarité, leurs coefficients deviennent instables. Le VIF alerte sur ce problème <em>avant</em> l\'interprétation. Tous nos VIF < 2,5, le modèle est "identifiable".',
        'en'=>'Before trusting multivariate ORs, predictors must not be redundant. If activity correlates strongly with sedentarity, their coefficients become unstable. VIF flags this <em>before</em> interpretation. All our VIFs < 2.5, model is "identifiable".',
      ],
    ],
    [
      'name' => 'Test de Box-Tidwell — linéarité du logit',
      'ref'  => 'Box & Tidwell (1962) Technometrics 4:531-550',
      'what' => [
        'ar'=>'يختبر ما إذا كانت العلاقة بين كل متنبّئ متّصل ولوغاريتم الـ odds خطّيّةً، بإضافة الحدّ X·ln(X) للنموذج وفحص معنويّته. p < 0,05 يرفض الخطّيّة.',
        'fr'=>'Teste si la relation entre chaque prédicteur continu et le log-odds est linéaire, en ajoutant le terme X·ln(X) au modèle et testant sa significativité. p < 0,05 rejette la linéarité.',
        'en'=>'Tests whether each continuous predictor\'s relationship with the log-odds is linear, by adding X·ln(X) to the model and testing its significance. p < 0.05 rejects linearity.',
      ],
      'why' => [
        'ar'=>'الانحدار اللوجستي يفترض أن أثر كل متنبّئ متّصل على log-odds <strong>خطّي</strong>. إذا كانت العلاقة على شكل U (مثل : النوم القصير والطويل خطر مقابل المعتدل)، فـ OR من النموذج يصبح عديم المعنى. Box-Tidwell هو الاختبار المعياري لهذا الافتراض، وفحصه قبل التفسير يميّز النشر الجادّ عن الفولكلور.',
        'fr'=>'La régression logistique suppose que l\'effet de chaque prédicteur continu sur le log-odds est <strong>linéaire</strong>. Si la relation est en U (ex. : sommeil court et long tous deux à risque vs sommeil moyen), l\'OR perd son sens. Box-Tidwell teste cette hypothèse fondamentale ; son examen avant interprétation distingue la publication sérieuse du folklore.',
        'en'=>'Logistic regression assumes each continuous predictor\'s effect on the log-odds is <strong>linear</strong>. If U-shaped (e.g., both short and long sleep are risky vs medium), the OR becomes meaningless. Box-Tidwell tests this foundational assumption; checking it before interpretation separates serious publication from folklore.',
      ],
    ],
    [
      'name' => 'GLMM logistique (Generalized Linear Mixed Model) — PQL',
      'ref'  => 'Breslow & Clayton (1993) JASA 88:9-25',
      'what' => [
        'ar'=>'انحدار لوجستي مع تأثير عشوائي على مستوى المدرسة (random intercept). يحلّ بـ <strong>PQL</strong> (Penalized Quasi-Likelihood)، يقدّر σ²_school، ويُرجع <strong>ICC</strong> = σ²/(σ² + π²/3).',
        'fr'=>'Régression logistique avec effet aléatoire école (intercept aléatoire). Résolue par <strong>PQL</strong> (Penalized Quasi-Likelihood), estime σ²_école, et retourne <strong>ICC</strong> = σ²/(σ² + π²/3).',
        'en'=>'Logistic regression with a school random effect (random intercept). Solved by <strong>PQL</strong> (Penalized Quasi-Likelihood), estimates σ²_school, returns <strong>ICC</strong> = σ²/(σ² + π²/3).',
      ],
      'why' => [
        'ar'=>'تلاميذ المدرسة نفسها يتشاركون عوامل غير ملاحَظة (المقصف، الحيّ، الأساتذة)، فإجاباتهم ليست مستقلّة. تجاهل هذا التجميع يُقلِّل الأخطاء المعيارية بنسبة قد تتجاوز 30%، فترتفع false positives. مع 14 مدرسة، GLMM ضروري وفق Goldstein (2010). تطبيقه يصادق على نتائج الانحدار العادي ويُمكّن من النشر في مجلّات عالية المعايير.',
        'fr'=>'Les élèves d\'une même école partagent des facteurs non observés (cantine, quartier, enseignants), leurs réponses ne sont pas indépendantes. Ignorer ce clustering sous-estime les SE jusqu\'à 30 %, gonflant les faux positifs. Avec 14 écoles, le GLMM est requis (Goldstein 2010). Son emploi valide les résultats de la régression ordinaire et autorise la publication dans des revues exigeantes.',
        'en'=>'Students in the same school share unobserved factors (canteen, neighbourhood, teachers); their responses aren\'t independent. Ignoring this clustering underestimates SEs by up to 30%, inflating false positives. With 14 schools, GLMM is required (Goldstein 2010). Its use validates ordinary regression results and enables publication in demanding journals.',
      ],
    ],
    [
      'name' => 'GEE logistique + variance sandwich',
      'ref'  => 'Liang & Zeger (1986) Biometrika 73:13-22',
      'what' => [
        'ar'=>'بديل لـ GLMM، يُقدِّر معاملات "متوسط السكّان" (population-averaged). يستخدم بنية ارتباط <strong>exchangeable</strong> داخل العنقود (المدرسة)، ويُرجع تباين <strong>sandwich</strong> الذي يصمد أمام سوء تحديد بنية الارتباط.',
        'fr'=>'Alternative au GLMM, estime des coefficients "population-averaged" (vs subject-specific). Utilise une structure de corrélation <strong>échangeable</strong> intra-cluster, et retourne la variance <strong>sandwich</strong>, robuste à la mauvaise spécification de la corrélation.',
        'en'=>'Alternative to GLMM, estimates "population-averaged" coefficients (vs subject-specific). Uses an <strong>exchangeable</strong> intra-cluster correlation structure, returns the <strong>sandwich</strong> variance, robust to correlation-structure misspecification.',
      ],
      'why' => [
        'ar'=>'GLMM يفترض شكلاً محدّداً (نرمالي) لتوزيع التأثير العشوائي. GEE يتحرّر من هذا الافتراض. تشغيل الاثنَين بالتوازي هو <strong>أفضل ممارسة</strong> للبيانات العنقودية : إذا تطابقت النتائج، تتعزّز الثقة كثيراً؛ إذا اختلفت، يكشف ذلك عن مشكلة في تحديد التوزيع (Diggle et al. 2002).',
        'fr'=>'GLMM suppose une distribution particulière (normale) de l\'effet aléatoire. GEE s\'en affranchit. Faire tourner les deux en parallèle est la <strong>meilleure pratique</strong> pour les données clusterisées : si les deux concordent, la confiance est renforcée ; sinon, cela révèle un problème de spécification (Diggle et al. 2002).',
        'en'=>'GLMM assumes a specific (normal) random-effect distribution. GEE frees itself from this. Running both in parallel is <strong>best practice</strong> for clustered data: if both agree, confidence is reinforced; if they diverge, this reveals a specification issue (Diggle et al. 2002).',
      ],
    ],
    [
      'name' => 'MICE (Multiple Imputation by Chained Equations)',
      'ref'  => 'van Buuren & Groothuis-Oudshoorn (2011) JSS 45(3)',
      'what' => [
        'ar'=>'يُولِّد m=20 مجموعة بيانات كاملة بطريقة <strong>Predictive Mean Matching</strong> : كل قيمة مفقودة تُعوَّض من 5 "متبرّعين" الأقرب في الاحتمال المتنبَّأ، عبر 20 تكراراً Gibbs مُسلسلاً. يحافظ على البنية الكاملة للارتباطات بين المتغيرات.',
        'fr'=>'Génère m=20 jeux de données complets par <strong>Predictive Mean Matching</strong> : chaque valeur manquante est imputée à partir des 5 donneurs les plus proches en probabilité prédite, via 20 itérations Gibbs chaînées. Préserve la structure complète des corrélations entre variables.',
        'en'=>'Generates m=20 complete datasets via <strong>Predictive Mean Matching</strong>: each missing value imputed from the 5 closest donors by predicted probability, through 20 chained Gibbs iterations. Preserves the full correlation structure between variables.',
      ],
      'why' => [
        'ar'=>'إقصاء التحليل للحالات غير الكاملة (complete-case) — الحلّ الافتراضي في SPSS — يفقد المعلومات ويُحيِّز النتائج عندما تكون البيانات المفقودة غير عشوائية تماماً (MAR/MNAR). MICE هو المعيار الذهبي الحديث (Sterne et al. 2009 BMJ) خاصة عندما يتجاوز نقص البيانات 5% على متغيّرات مفتاحية.',
        'fr'=>'L\'analyse en cas complets (complete-case) — solution par défaut de SPSS — perd de l\'information et biaise les résultats quand les données manquantes ne sont pas complètement aléatoires (MAR/MNAR). MICE est le gold standard moderne (Sterne et al. 2009 BMJ) dès que le manquement dépasse 5 % sur des variables clés.',
        'en'=>'Complete-case analysis — SPSS\'s default — loses information and biases results when missingness is not completely at random (MAR/MNAR). MICE is the modern gold standard (Sterne et al. 2009 BMJ) once missingness exceeds 5% on key variables.',
      ],
    ],
    [
      'name' => 'Règles de Rubin pour le pooling des imputations',
      'ref'  => 'Rubin (1987) Multiple Imputation ; BiostatPHP::rubinPool',
      'what' => [
        'ar'=>'يدمج تقديرات m مجموعات MICE في تقدير وحيد بـ SE صحيح : <strong>T = U + (1 + 1/m)·B</strong> حيث U = متوسط التباينات داخل-الإسقاط، B = تباين بين-الإسقاطات. درجات الحرّية بـ <strong>Barnard-Rubin</strong>.',
        'fr'=>'Combine les estimations des m jeux MICE en une estimation unique avec SE correct : <strong>T = U + (1 + 1/m)·B</strong> où U = moyenne des variances intra-imputation, B = variance inter-imputations. Degrés de liberté par <strong>Barnard-Rubin</strong>.',
        'en'=>'Combines estimates from m MICE datasets into a single estimate with correct SE: <strong>T = U + (1 + 1/m)·B</strong> where U = mean within-imputation variance, B = between-imputation variance. Degrees of freedom via <strong>Barnard-Rubin</strong>.',
      ],
      'why' => [
        'ar'=>'بعد توليد MICE لـ 20 مجموعة، لا يكفي أخذ متوسّط الـ β : يجب جمع تباين <em>داخل</em> كل إسقاط مع تباين <em>بين</em> الإسقاطات بصيغة دقيقة. هذا ما تفعله قواعد Rubin بدقّة رياضية لا غنى عنها للحصول على p-values وICs غير منحازة. تطبيقها يتمم MICE.',
        'fr'=>'Après génération MICE de 20 jeux, prendre la moyenne des β ne suffit pas : il faut combiner correctement variance <em>intra-imputation</em> et variance <em>inter-imputations</em>. C\'est ce que font les règles de Rubin, avec une rigueur mathématique indispensable pour obtenir p-values et IC non biaisés. Leur application complète MICE.',
        'en'=>'After MICE generates 20 datasets, averaging β\'s is insufficient: <em>within</em>-imputation variance must be properly combined with <em>between</em>-imputation variance. Rubin\'s rules do this with mathematical rigour indispensable to obtain unbiased p-values and CIs. Their application completes MICE.',
      ],
    ],
    [
      'name' => 'Correction Benjamini-Hochberg (FDR)',
      'ref'  => 'Benjamini & Hochberg (1995) JRSS-B 57:289-300',
      'what' => [
        'ar'=>'يضبط p-values عبر اختبارات متعدّدة للحفاظ على <strong>FDR</strong> (نسبة الاكتشافات الزائفة بين الاكتشافات المُعلَنة) تحت q=0,05. ترتيب الـ p-values ثم ضربها بـ m/i تنازلياً.',
        'fr'=>'Ajuste les p-values sur de multiples tests pour maintenir le <strong>FDR</strong> (proportion de fausses découvertes parmi les découvertes déclarées) sous q=0,05. Tri des p-values puis multiplication par m/i de manière décroissante.',
        'en'=>'Adjusts p-values across multiple tests to keep the <strong>FDR</strong> (proportion of false discoveries among declared) below q=0.05. Sort p-values then multiply by m/i in decreasing order.',
      ],
      'why' => [
        'ar'=>'دراستنا تُجري ~68 اختباراً. بدون تصحيح عند p<0,05 سنتوقّع 3–4 اكتشافات خاطئة كأنها معنوية. <strong>Bonferroni</strong> (p < 0,00074) صارم جداً ويُفقد القوّة. <strong>Benjamini-Hochberg FDR</strong> أذكى : يتسامح مع قليل من الكذب لكنه يحافظ على القوّة. أصبح المعيار في كل منشورات -omics ودراسات سلوكية بتعدد اختبارات منذ 2000.',
        'fr'=>'Notre étude effectue ~68 tests. Sans correction à p<0,05, on attendrait 3–4 découvertes erronées comme significatives. <strong>Bonferroni</strong> (p < 0,00074) trop conservateur, fait perdre la puissance. <strong>Benjamini-Hochberg FDR</strong> est plus intelligent : tolère quelques faux positifs mais préserve la puissance. Standard dans toutes les publications -omiques et études comportementales à tests multiples depuis 2000.',
        'en'=>'Our study runs ~68 tests. Without correction at p<0.05, we\'d expect 3–4 false discoveries passing as significant. <strong>Bonferroni</strong> (p < 0.00074) is too conservative, eroding power. <strong>Benjamini-Hochberg FDR</strong> is smarter: tolerates a few false discoveries while preserving power. Standard in all -omics publications and behavioural studies with multiple tests since 2000.',
      ],
    ],
  ]
],

/* ═════ Catégorie E — Analyse de concordance (1 test) ═════ */
[
  'cat_title' => ['ar'=>'E. تحليل التطابق', 'fr'=>'E. Analyse de concordance', 'en'=>'E. Agreement analysis'],
  'tests' => [
    [
      'name' => 'Analyse de Bland-Altman',
      'ref'  => 'Bland & Altman (1986) Lancet 1:307-310',
      'what' => [
        'ar'=>'يُقيِّم التطابق بين طريقتَين لقياس نفس الكمّية (IMC الذاتي مقابل المقاس). يرسم الفرق بين القياسَين كدالّة لمتوسّطهما، ويحدّد <strong>التحيّز</strong> (mean bias) و<strong>حدود التطابق</strong> (LoA) عند ± 1,96·SD.',
        'fr'=>'Évalue la concordance entre deux méthodes mesurant la même grandeur (IMC déclaré vs mesuré). Trace la différence entre les deux mesures en fonction de leur moyenne, et détermine le <strong>biais moyen</strong> et les <strong>limites de concordance</strong> (LoA) à ± 1,96·SD.',
        'en'=>'Assesses agreement between two methods measuring the same quantity (self-reported vs measured BMI). Plots the difference between the two measurements as a function of their mean, determines the <strong>mean bias</strong> and <strong>limits of agreement</strong> (LoA) at ± 1.96·SD.',
      ],
      'why' => [
        'ar'=>'مُعامل الارتباط (Pearson) <em>لا</em> يقيس التطابق — قياسان قد يكونان مرتبطَين تماماً (r=1) ومع ذلك متباعدَين دوماً (الفهرنهايت والسلسيوس). Bland-Altman هو المعيار في Lancet وBMJ لتقييم الاتفاق بين طريقَتَي قياس. مفيد للتحقّق من اتساق القياسات الموضوعية مع المُعلَنة، وكشف انحراف منهجي محتمل.',
        'fr'=>'Le coefficient de corrélation <em>ne mesure pas</em> la concordance — deux mesures peuvent être parfaitement corrélées (r=1) tout en restant systématiquement éloignées (Fahrenheit vs Celsius). Bland-Altman est la référence Lancet/BMJ pour évaluer l\'accord entre deux méthodes. Utile pour vérifier la cohérence entre mesures objectives et déclarées, et détecter un biais systématique.',
        'en'=>'Correlation coefficient <em>does not measure</em> agreement — two measurements can be perfectly correlated (r=1) yet systematically far apart (Fahrenheit vs Celsius). Bland-Altman is the Lancet/BMJ reference for assessing agreement between two methods. Useful to verify consistency between objective and self-reported measurements, and detect systematic bias.',
      ],
    ],
  ]
],

]; // end $testsCatalog

// Compteur global pour les en-têtes
$nTests = 0;
foreach($testsCatalog as $cat) $nTests += count($cat['tests']);
