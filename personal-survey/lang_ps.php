<?php
/**
 * ════════════════════════════════════════════════════════════════════
 *  personal-survey/lang_ps.php
 *  Trilingual translation strings for the well-being personal-survey.
 *  Must be included AFTER ../lang.php (which defines $t and __()).
 *
 *  Merged from three historical fragments:
 *    • base v1   — initial questionnaire keys
 *    • v2 (ext)  — 90+ items based on the 2024 literature
 *    • v3        — SES, puberty, Algerian FFQ, smoking, social jet-lag,
 *                  sitting time, early-life antibiotics
 *
 *  Adding new keys: simply append to one of the $sections below.
 * ════════════════════════════════════════════════════════════════════
 */

global $t;
if (!isset($t)) $t = [];


// ────────────────────────────────────────────────────────────────────
// Section 1: base v1 (initial keys)
// ────────────────────────────────────────────────────────────────────
$ps = [
    // ── Header / branding ────────────────────────────────────────
    'ps_site_name'      => ['ar'=>'استبيان شخصي', 'fr'=>'Questionnaire personnel', 'en'=>'Personal questionnaire'],
    'ps_tagline'        => ['ar'=>'تقييم نمط الحياة الغذائي والصحي — تقرير فوري ومخصص', 'fr'=>'Évaluation du mode de vie alimentaire et santé — rapport immédiat et personnalisé', 'en'=>'Lifestyle, diet & health self-assessment — instant personalized report'],
    'ps_back_to_main'   => ['ar'=>'العودة إلى الموقع الرئيسي', 'fr'=>'Retour au site principal', 'en'=>'Back to main site'],

    // ── Welcome / privacy ────────────────────────────────────────
    'ps_welcome_title'  => ['ar'=>'مرحباً بك في الاستبيان الشخصي', 'fr'=>'Bienvenue dans le questionnaire personnel', 'en'=>'Welcome to the personal questionnaire'],
    'ps_welcome_intro'  => ['ar'=>'هذا الاستبيان يقيّم نمط حياتك الغذائي والصحي ويولّد لك تقريراً فورياً ومخصصاً مع نصائح عملية.', 'fr'=>"Ce questionnaire évalue votre mode de vie alimentaire et de santé, et génère un rapport personnalisé immédiat avec des conseils pratiques.", 'en'=>'This questionnaire evaluates your nutritional and lifestyle profile and generates an instant personalized report with practical advice.'],
    'ps_privacy_title'  => ['ar'=>'🔒 خصوصية كاملة', 'fr'=>'🔒 Confidentialité totale', 'en'=>'🔒 Full privacy'],
    'ps_privacy_text'   => ['ar'=>'لن يتم حفظ أي بيانات في قاعدة البيانات. كل المعالجة تتم في الذاكرة ولمرة واحدة فقط. تقريرك يظهر بعد الإرسال ثم يختفي عند مغادرة الصفحة.', 'fr'=>"Aucune donnée n'est enregistrée en base de données. Tout le traitement se fait en mémoire, une seule fois. Votre rapport s'affiche après l'envoi puis disparaît lorsque vous quittez la page.", 'en'=>'No data is stored in any database. All processing happens in memory, once. Your report appears after submission and disappears when you leave the page.'],
    'ps_disclaimer'     => ['ar'=>'⚠ هذا التقرير لأغراض توعوية فقط ولا يحل محل استشارة طبيب أو أخصائي تغذية مؤهل.', 'fr'=>"⚠ Ce rapport est informatif uniquement et ne remplace pas la consultation d'un médecin ou d'un nutritionniste qualifié.", 'en'=>'⚠ This report is for educational purposes only and does not replace consultation with a qualified physician or nutritionist.'],
    'ps_time_needed'    => ['ar'=>'الوقت المطلوب: 5-8 دقائق', 'fr'=>'Temps requis : 5-8 minutes', 'en'=>'Time required: 5-8 minutes'],
    'ps_start_btn'      => ['ar'=>'ابدأ الاستبيان', 'fr'=>'Commencer le questionnaire', 'en'=>'Start questionnaire'],

    // ── Step labels ──────────────────────────────────────────────
    'ps_step1'          => ['ar'=>'البيانات الأساسية', 'fr'=>'Données de base', 'en'=>'Basic data'],
    'ps_step2'          => ['ar'=>'التغذية (FFQ)', 'fr'=>'Alimentation (FFQ)', 'en'=>'Diet (FFQ)'],
    'ps_step3'          => ['ar'=>'العادات اليومية', 'fr'=>'Habitudes quotidiennes', 'en'=>'Daily habits'],
    'ps_step4'          => ['ar'=>'النشاط والنوم', 'fr'=>'Activité & sommeil', 'en'=>'Activity & sleep'],
    'ps_step5'          => ['ar'=>'الأسرة والإدراك', 'fr'=>'Famille & perception', 'en'=>'Family & perception'],

    // ── Form labels - shared ─────────────────────────────────────
    'ps_required'       => ['ar'=>'مطلوب', 'fr'=>'requis', 'en'=>'required'],
    'ps_optional'       => ['ar'=>'(اختياري)', 'fr'=>'(facultatif)', 'en'=>'(optional)'],
    'ps_yes'            => ['ar'=>'نعم', 'fr'=>'Oui', 'en'=>'Yes'],
    'ps_no'             => ['ar'=>'لا', 'fr'=>'Non', 'en'=>'No'],
    'ps_choose'         => ['ar'=>'— اختر —', 'fr'=>'— Choisir —', 'en'=>'— Choose —'],

    // ── Step 1 — Basic data ──────────────────────────────────────
    'ps_s1_title'       => ['ar'=>'بياناتك الأساسية', 'fr'=>'Vos données de base', 'en'=>'Your basic data'],
    'ps_age'            => ['ar'=>'العمر (سنوات)', 'fr'=>'Âge (années)', 'en'=>'Age (years)'],
    'ps_sex'            => ['ar'=>'الجنس', 'fr'=>'Sexe', 'en'=>'Sex'],
    'ps_male'           => ['ar'=>'ذكر', 'fr'=>'Garçon / Homme', 'en'=>'Male'],
    'ps_female'         => ['ar'=>'أنثى', 'fr'=>'Fille / Femme', 'en'=>'Female'],
    'ps_height'         => ['ar'=>'الطول (سم)', 'fr'=>'Taille (cm)', 'en'=>'Height (cm)'],
    'ps_weight'         => ['ar'=>'الوزن (كغ)', 'fr'=>'Poids (kg)', 'en'=>'Weight (kg)'],
    'ps_bmi_preview'    => ['ar'=>'IMC المحسوب', 'fr'=>'IMC calculé', 'en'=>'Computed BMI'],
    'ps_birth_weight'   => ['ar'=>'وزن الولادة (كغ)', 'fr'=>'Poids de naissance (kg)', 'en'=>'Birth weight (kg)'],
    'ps_delivery_type'  => ['ar'=>'نوع الولادة', 'fr'=>'Type de naissance', 'en'=>'Birth type'],
    'ps_natural'        => ['ar'=>'طبيعية', 'fr'=>'Naturelle', 'en'=>'Natural'],
    'ps_caesarean'      => ['ar'=>'قيصرية', 'fr'=>'Césarienne', 'en'=>'Caesarean'],

    // ── Step 2 — FFQ instructions ────────────────────────────────
    'ps_s2_title'       => ['ar'=>'كم مرة تستهلك هذه الأطعمة عادةً؟', 'fr'=>'À quelle fréquence consommez-vous ces aliments ?', 'en'=>'How often do you usually eat these foods?'],
    'ps_ffq_note'       => ['ar'=>'استعمل سلمين: <strong>السلم اليومي</strong> (للأطعمة المعتادة) و<strong>السلم الأسبوعي</strong> (للأطعمة الأقل تكراراً).', 'fr'=>"Deux échelles : <strong>échelle quotidienne</strong> (aliments fréquents) et <strong>échelle hebdomadaire</strong> (aliments moins fréquents).", 'en'=>'Two scales: <strong>daily scale</strong> (frequent foods) and <strong>weekly scale</strong> (less frequent foods).'],
    'ps_daily_hdr'      => ['ar'=>'السلم اليومي', 'fr'=>'Échelle quotidienne', 'en'=>'Daily scale'],
    'ps_weekly_hdr'     => ['ar'=>'السلم الأسبوعي', 'fr'=>'Échelle hebdomadaire', 'en'=>'Weekly scale'],
    'ps_food_col'       => ['ar'=>'الطعام', 'fr'=>'Aliment', 'en'=>'Food'],
    'ps_never'          => ['ar'=>'أبداً', 'fr'=>'Jamais', 'en'=>'Never'],
    'ps_lt1wk'          => ['ar'=>'<1/أسبوع', 'fr'=>'<1/sem', 'en'=>'<1/week'],
    'ps_1wk'            => ['ar'=>'1/أسبوع', 'fr'=>'1/sem', 'en'=>'1/week'],
    'ps_2_4wk'          => ['ar'=>'2-4/أسبوع', 'fr'=>'2-4/sem', 'en'=>'2-4/week'],
    'ps_1day'           => ['ar'=>'1/يوم', 'fr'=>'1/jour', 'en'=>'1/day'],
    'ps_2_3day'         => ['ar'=>'2-3/يوم', 'fr'=>'2-3/jour', 'en'=>'2-3/day'],
    'ps_4plus_day'      => ['ar'=>'≥4/يوم', 'fr'=>'≥4/jour', 'en'=>'≥4/day'],
    'ps_lt1mo'          => ['ar'=>'<1/شهر', 'fr'=>'<1/mois', 'en'=>'<1/month'],
    'ps_1_3mo'          => ['ar'=>'1-3/شهر', 'fr'=>'1-3/mois', 'en'=>'1-3/month'],
    'ps_5_6wk'          => ['ar'=>'5-6/أسبوع', 'fr'=>'5-6/sem', 'en'=>'5-6/week'],

    // ── Step 3 — Daily habits ────────────────────────────────────
    'ps_s3_title'       => ['ar'=>'عاداتك اليومية', 'fr'=>'Vos habitudes quotidiennes', 'en'=>'Your daily habits'],
    'ps_breakfast'      => ['ar'=>'هل تأخذ وجبة الإفطار؟', 'fr'=>'Prenez-vous le petit-déjeuner ?', 'en'=>'Do you eat breakfast?'],
    'ps_everyday'       => ['ar'=>'كل يوم', 'fr'=>'Tous les jours', 'en'=>'Every day'],
    'ps_sugary_freq'    => ['ar'=>'تكرار المشروبات السكرية', 'fr'=>'Fréquence des boissons sucrées', 'en'=>'Sugary drinks frequency'],
    'ps_energy_freq'    => ['ar'=>'تكرار مشروبات الطاقة', 'fr'=>'Fréquence des boissons énergisantes', 'en'=>'Energy drinks frequency'],
    'ps_water_intake'   => ['ar'=>'كمية الماء يومياً', 'fr'=>"Quantité d'eau par jour", 'en'=>'Daily water intake'],
    'ps_meals_per_day'  => ['ar'=>'عدد الوجبات الرئيسية / يوم', 'fr'=>'Nombre de repas principaux / jour', 'en'=>'Main meals per day'],
    'ps_snacking_freq'  => ['ar'=>'تكرار الوجبات الخفيفة بين الوجبات', 'fr'=>'Fréquence des collations entre les repas', 'en'=>'Snacking between meals'],
    'ps_skip_meal_stress'=> ['ar'=>'هل تتخطى وجبات بسبب الضغط الدراسي/المهني؟', 'fr'=>"Sautez-vous des repas à cause du stress scolaire/professionnel ?", 'en'=>'Do you skip meals because of school/work stress?'],
    'ps_yes_often'      => ['ar'=>'نعم، كثيراً', 'fr'=>'Oui, souvent', 'en'=>'Yes, often'],
    'ps_yes_sometimes'  => ['ar'=>'نعم، أحياناً', 'fr'=>'Oui, parfois', 'en'=>'Yes, sometimes'],
    'ps_rarely'         => ['ar'=>'نادراً', 'fr'=>'Rarement', 'en'=>'Rarely'],
    'ps_meal_replace'   => ['ar'=>'ماذا تتناول بدلاً من الوجبة المتخطاة عادةً؟', 'fr'=>'Que prenez-vous habituellement à la place du repas sauté ?', 'en'=>'What do you usually have instead of a skipped meal?'],
    'ps_chips'          => ['ar'=>'وجبات سريعة/شيبس', 'fr'=>'Snacks/chips', 'en'=>'Snacks/chips'],
    'ps_sandwich'       => ['ar'=>'ساندويش', 'fr'=>'Sandwich', 'en'=>'Sandwich'],
    'ps_fruits'         => ['ar'=>'فواكه', 'fr'=>'Fruits', 'en'=>'Fruits'],
    'ps_nothing'        => ['ar'=>'لا شيء', 'fr'=>'Rien', 'en'=>'Nothing'],
    'ps_other'          => ['ar'=>'أخرى', 'fr'=>'Autre', 'en'=>'Other'],
    'ps_emot_eating'    => ['ar'=>'ماذا تأكل عند الضغط/الحزن/الفرح؟', 'fr'=>"Que mangez-vous sous stress/tristesse/joie ?", 'en'=>'What do you eat under stress/sadness/joy?'],
    'ps_choc_sweets'    => ['ar'=>'الشوكولاتة والحلويات', 'fr'=>'Chocolat / sucreries', 'en'=>'Chocolate / sweets'],
    'ps_nuts'           => ['ar'=>'مكسرات', 'fr'=>'Fruits à coque', 'en'=>'Nuts'],
    'ps_no_emot_eat'    => ['ar'=>'لا، لا آكل تحت تأثير المشاعر', 'fr'=>"Non, je ne mange pas en réponse aux émotions", 'en'=>'No, I do not eat emotionally'],
    'ps_sometimes'      => ['ar'=>'أحياناً', 'fr'=>'Parfois', 'en'=>'Sometimes'],
    'ps_often'          => ['ar'=>'كثيراً', 'fr'=>'Souvent', 'en'=>'Often'],
    'ps_very_often'     => ['ar'=>'كثيراً جداً', 'fr'=>'Très souvent', 'en'=>'Very often'],

    // ── Step 4 — Sleep & activity ────────────────────────────────
    'ps_s4_title'       => ['ar'=>'النوم والنشاط البدني', 'fr'=>'Sommeil et activité physique', 'en'=>'Sleep and physical activity'],
    'ps_sleep_duration' => ['ar'=>'مدة النوم في الليلة', 'fr'=>'Durée de sommeil par nuit', 'en'=>'Sleep duration per night'],
    'ps_screen_sleep'   => ['ar'=>'استخدام الشاشات قبل النوم', 'fr'=>'Écrans avant de dormir', 'en'=>'Screens before sleep'],
    'ps_wake_exhausted' => ['ar'=>'الاستيقاظ متعباً', 'fr'=>'Réveil épuisé', 'en'=>'Wake up exhausted'],
    'ps_insomnia'       => ['ar'=>'الأرق (صعوبة في النوم)', 'fr'=>"Insomnie (difficulté à s'endormir)", 'en'=>'Insomnia (trouble falling asleep)'],
    'ps_nightmares'     => ['ar'=>'الكوابيس', 'fr'=>'Cauchemars', 'en'=>'Nightmares'],
    'ps_bedtime'        => ['ar'=>'موعد النوم', 'fr'=>'Heure du coucher', 'en'=>'Bedtime'],
    'ps_waketime'       => ['ar'=>'موعد الاستيقاظ', 'fr'=>'Heure du réveil', 'en'=>'Wake time'],
    'ps_active_days'    => ['ar'=>'أيام النشاط البدني المعتدل/القوي في الأسبوع (≥30 دقيقة)', 'fr'=>"Jours d'activité physique modérée/forte par semaine (≥30 min)", 'en'=>'Days/week of moderate/vigorous activity (≥30 min)'],
    'ps_sports_club'    => ['ar'=>'هل تنتمي إلى نادٍ رياضي؟', 'fr'=>'Êtes-vous inscrit(e) dans un club sportif ?', 'en'=>'Are you in a sports club?'],
    'ps_transport'      => ['ar'=>'الوسيلة الرئيسية للتنقل', 'fr'=>'Mode de transport principal', 'en'=>'Main mode of transport'],
    'ps_walking'        => ['ar'=>'المشي', 'fr'=>'Marche', 'en'=>'Walking'],
    'ps_bicycle'        => ['ar'=>'دراجة', 'fr'=>'Vélo', 'en'=>'Bicycle'],
    'ps_car'            => ['ar'=>'سيارة', 'fr'=>'Voiture', 'en'=>'Car'],
    'ps_bus'            => ['ar'=>'حافلة', 'fr'=>'Bus', 'en'=>'Bus'],
    'ps_walk_duration'  => ['ar'=>'مدة المشي اليومية الإجمالية', 'fr'=>'Durée totale de marche quotidienne', 'en'=>'Total daily walking duration'],
    'ps_screen_phone'   => ['ar'=>'الوقت اليومي على الهاتف (وسائل التواصل)', 'fr'=>'Temps quotidien sur téléphone (réseaux sociaux)', 'en'=>'Daily phone time (social media)'],
    'ps_screen_tv'      => ['ar'=>'الوقت اليومي أمام التلفاز', 'fr'=>'Temps quotidien devant la TV', 'en'=>'Daily TV time'],
    'ps_screen_games'   => ['ar'=>'الوقت اليومي في ألعاب الفيديو', 'fr'=>'Temps quotidien jeux vidéo', 'en'=>'Daily video game time'],
    'ps_screen_pc'      => ['ar'=>'الوقت اليومي على الكمبيوتر', 'fr'=>'Temps quotidien sur ordinateur', 'en'=>'Daily computer time'],

    // ── Step 5 — Perception & family ─────────────────────────────
    'ps_s5_title'       => ['ar'=>'الإدراك والأسرة', 'fr'=>'Perception et famille', 'en'=>'Perception and family'],
    'ps_body_percept'   => ['ar'=>'كيف ترى جسمك؟', 'fr'=>'Comment percevez-vous votre corps ?', 'en'=>'How do you perceive your body?'],
    'ps_too_thin'       => ['ar'=>'نحيف جداً', 'fr'=>'Beaucoup trop maigre', 'en'=>'Much too thin'],
    'ps_a_bit_thin'     => ['ar'=>'نحيف نوعاً ما', 'fr'=>'Un peu trop maigre', 'en'=>'A bit too thin'],
    'ps_normal'         => ['ar'=>'طبيعي', 'fr'=>'Normal', 'en'=>'Normal'],
    'ps_a_bit_fat'      => ['ar'=>'بدين نوعاً ما', 'fr'=>'Un peu trop gros', 'en'=>'A bit too heavy'],
    'ps_tried_lose'     => ['ar'=>'هل حاولت إنقاص وزنك مؤخراً؟', 'fr'=>'Avez-vous tenté de perdre du poids récemment ?', 'en'=>'Have you tried to lose weight recently?'],
    'ps_want_change'    => ['ar'=>'ماذا تريد لوزنك؟', 'fr'=>'Que souhaitez-vous pour votre poids ?', 'en'=>'What do you want for your weight?'],
    'ps_want_lose'      => ['ar'=>'إنقاص', 'fr'=>'Perdre', 'en'=>'Lose'],
    'ps_want_maintain'  => ['ar'=>'الحفاظ عليه', 'fr'=>'Maintenir', 'en'=>'Maintain'],
    'ps_want_gain'      => ['ar'=>'زيادة', 'fr'=>'Prendre', 'en'=>'Gain'],
    'ps_meals_family'   => ['ar'=>'الوجبات مع العائلة', 'fr'=>'Repas avec la famille', 'en'=>'Meals with family'],
    'ps_always'         => ['ar'=>'دائماً', 'fr'=>'Toujours', 'en'=>'Always'],
    'ps_parent_obese'   => ['ar'=>'هل أحد والديك مصاب بسمنة؟', 'fr'=>'Un de vos parents est-il obèse ?', 'en'=>'Is one of your parents obese?'],
    'ps_mother_edu'     => ['ar'=>'المستوى التعليمي للأم', 'fr'=>'Niveau d\'éducation de la mère', 'en'=>"Mother's education level"],
    'ps_no_level'       => ['ar'=>'بدون مستوى', 'fr'=>'Sans niveau', 'en'=>'No level'],
    'ps_primary'        => ['ar'=>'ابتدائي', 'fr'=>'Primaire', 'en'=>'Primary'],
    'ps_moderate'       => ['ar'=>'متوسط', 'fr'=>'Moyen', 'en'=>'Moderate'],
    'ps_secondary'      => ['ar'=>'ثانوي', 'fr'=>'Secondaire', 'en'=>'Secondary'],
    'ps_university'     => ['ar'=>'جامعي', 'fr'=>'Universitaire', 'en'=>'University'],
    'ps_family_support' => ['ar'=>'الدعم الأسري في القرارات الصحية', 'fr'=>'Soutien familial pour les décisions de santé', 'en'=>'Family support for health decisions'],
    'ps_strong'         => ['ar'=>'قوي', 'fr'=>'Fort', 'en'=>'Strong'],
    'ps_weak'           => ['ar'=>'ضعيف', 'fr'=>'Faible', 'en'=>'Weak'],
    'ps_none'           => ['ar'=>'لا شيء', 'fr'=>'Aucun', 'en'=>'None'],
    'ps_academic_stress'=> ['ar'=>'مستوى الضغط الدراسي/المهني', 'fr'=>'Niveau de stress scolaire/professionnel', 'en'=>'Academic/work stress level'],
    'ps_low'            => ['ar'=>'منخفض', 'fr'=>'Faible', 'en'=>'Low'],
    'ps_high'           => ['ar'=>'مرتفع', 'fr'=>'Élevé', 'en'=>'High'],

    // ── Buttons & navigation ─────────────────────────────────────
    'ps_prev'           => ['ar'=>'السابق', 'fr'=>'Précédent', 'en'=>'Previous'],
    'ps_next'           => ['ar'=>'التالي', 'fr'=>'Suivant', 'en'=>'Next'],
    'ps_submit'         => ['ar'=>'احصل على تقريري', 'fr'=>'Obtenir mon rapport', 'en'=>'Get my report'],
    'ps_restart'        => ['ar'=>'استبيان جديد', 'fr'=>'Nouveau questionnaire', 'en'=>'New questionnaire'],
    'ps_print'          => ['ar'=>'🖨 طباعة التقرير', 'fr'=>'🖨 Imprimer le rapport', 'en'=>'🖨 Print report'],
    'ps_reset'          => ['ar'=>'مسح الكل', 'fr'=>'Tout effacer', 'en'=>'Clear all'],

    // ── Report headers ───────────────────────────────────────────
    'ps_report_title'   => ['ar'=>'تقريرك الشخصي', 'fr'=>'Votre rapport personnel', 'en'=>'Your personal report'],
    'ps_generated_on'   => ['ar'=>'تم إنشاؤه في', 'fr'=>'Généré le', 'en'=>'Generated on'],
    'ps_not_saved'      => ['ar'=>'✓ لم يتم حفظ بياناتك في أي مكان', 'fr'=>'✓ Vos données n\'ont été enregistrées nulle part', 'en'=>'✓ Your data has not been stored anywhere'],
    'ps_summary_title'  => ['ar'=>'الملخص العام', 'fr'=>'Résumé général', 'en'=>'Overall summary'],
    'ps_indicators'     => ['ar'=>'المؤشرات التفصيلية', 'fr'=>'Indicateurs détaillés', 'en'=>'Detailed indicators'],
    'ps_advice_title'   => ['ar'=>'نصائحك الشخصية', 'fr'=>'Vos conseils personnalisés', 'en'=>'Your personalized advice'],
    'ps_global_score'   => ['ar'=>'النتيجة العالمية للصحة الغذائية', 'fr'=>'Score global de nutrition-santé', 'en'=>'Global nutrition-health score'],
    'ps_strengths'      => ['ar'=>'نقاط قوتك', 'fr'=>'Vos points forts', 'en'=>'Your strengths'],
    'ps_weaknesses'     => ['ar'=>'مجالات التحسين', 'fr'=>'Axes d\'amélioration', 'en'=>'Areas for improvement'],

    // ── Indicator names ──────────────────────────────────────────
    'ps_ind_bmi'        => ['ar'=>'مؤشر كتلة الجسم (IMC) وتصنيفك', 'fr'=>'Indice de masse corporelle (IMC) et classification', 'en'=>'Body Mass Index (BMI) and classification'],
    'ps_ind_kidmed'     => ['ar'=>'النظام المتوسطي (KIDMED)', 'fr'=>'Régime méditerranéen (KIDMED)', 'en'=>'Mediterranean diet (KIDMED)'],
    'ps_ind_sleep'      => ['ar'=>'جودة النوم', 'fr'=>'Qualité du sommeil', 'en'=>'Sleep quality'],
    'ps_ind_activity'   => ['ar'=>'النشاط البدني', 'fr'=>'Activité physique', 'en'=>'Physical activity'],
    'ps_ind_sedent'     => ['ar'=>'الجلوس والشاشات', 'fr'=>'Sédentarité & écrans', 'en'=>'Sedentariness & screens'],
    'ps_ind_diversity'  => ['ar'=>'التنوع الغذائي', 'fr'=>'Diversité alimentaire', 'en'=>'Dietary diversity'],
    'ps_ind_obrisk'     => ['ar'=>'خطر زيادة الوزن', 'fr'=>"Risque d'obésité", 'en'=>'Obesity risk'],
    'ps_ind_perception' => ['ar'=>'إدراك الجسم', 'fr'=>'Perception corporelle', 'en'=>'Body perception'],
    'ps_ind_anemia'     => ['ar'=>'خطر فقر الدم', 'fr'=>"Risque d'anémie", 'en'=>'Anaemia risk'],
    'ps_ind_vitd'       => ['ar'=>'خطر نقص فيتامين د', 'fr'=>'Risque de carence en vit. D', 'en'=>'Vit. D deficiency risk'],
    'ps_ind_eatdis'     => ['ar'=>'خطر اضطراب الأكل', 'fr'=>'Risque de trouble alimentaire', 'en'=>'Eating disorder risk'],
    'ps_ind_family'     => ['ar'=>'البيئة الأسرية', 'fr'=>'Environnement familial', 'en'=>'Family environment'],
    'ps_ind_pressure'   => ['ar'=>'الضغط الدراسي/الغذائي', 'fr'=>'Pression scolaire-alimentaire', 'en'=>'School-eating pressure'],

    // ── Validation messages ──────────────────────────────────────
    'ps_err_required'   => ['ar'=>'يرجى ملء الحقول الإلزامية: العمر، الجنس، الطول، الوزن.', 'fr'=>'Veuillez remplir les champs obligatoires : âge, sexe, taille, poids.', 'en'=>'Please fill required fields: age, sex, height, weight.'],
    'ps_err_bmi'        => ['ar'=>'تحقق من الطول والوزن — قيمة IMC غير منطقية.', 'fr'=>'Vérifiez la taille et le poids — valeur IMC invraisemblable.', 'en'=>'Check height and weight — implausible BMI value.'],
    'ps_err_age'        => ['ar'=>'العمر يجب أن يكون بين 8 و 80 سنة.', 'fr'=>"L'âge doit être entre 8 et 80 ans.", 'en'=>'Age must be between 8 and 80 years.'],

    // ── Class labels (for report cards) ──────────────────────────
    'ps_cl_obese'       => ['ar'=>'سمنة', 'fr'=>'Obésité', 'en'=>'Obesity'],
    'ps_cl_overweight'  => ['ar'=>'وزن زائد', 'fr'=>'Surpoids', 'en'=>'Overweight'],
    'ps_cl_normal'      => ['ar'=>'وزن طبيعي', 'fr'=>'Poids normal', 'en'=>'Normal weight'],
    'ps_cl_thin'        => ['ar'=>'نحافة', 'fr'=>'Minceur', 'en'=>'Underweight'],
    'ps_cl_thin2'       => ['ar'=>'نحافة درجة 2', 'fr'=>'Minceur grade 2', 'en'=>'Underweight grade 2'],
    'ps_cl_thin3'       => ['ar'=>'نحافة درجة 3', 'fr'=>'Minceur grade 3', 'en'=>'Underweight grade 3'],
    'ps_cl_optimal'     => ['ar'=>'ممتاز', 'fr'=>'Optimal', 'en'=>'Optimal'],
    'ps_cl_improve'     => ['ar'=>'يحتاج تحسيناً', 'fr'=>'Amélioration nécessaire', 'en'=>'Needs improvement'],
    'ps_cl_bad'         => ['ar'=>'ضعيف', 'fr'=>'Mauvais', 'en'=>'Poor'],
    'ps_cl_good'        => ['ar'=>'جيد', 'fr'=>'Bon', 'en'=>'Good'],
    'ps_cl_disturbed'   => ['ar'=>'مضطرب', 'fr'=>'Perturbé', 'en'=>'Disturbed'],
    'ps_cl_inactive'    => ['ar'=>'غير نشط', 'fr'=>'Inactif', 'en'=>'Inactive'],
    'ps_cl_little'      => ['ar'=>'قليل النشاط', 'fr'=>'Peu actif', 'en'=>'Slightly active'],
    'ps_cl_active'      => ['ar'=>'نشط', 'fr'=>'Actif', 'en'=>'Active'],
    'ps_cl_veryactive'  => ['ar'=>'نشط جداً', 'fr'=>'Très actif', 'en'=>'Very active'],
    'ps_cl_sedentary'   => ['ar'=>'جالس', 'fr'=>'Sédentaire', 'en'=>'Sedentary'],
    'ps_cl_modsedent'   => ['ar'=>'متوسط الجلوس', 'fr'=>'Modérément sédentaire', 'en'=>'Moderately sedentary'],
    'ps_cl_low'         => ['ar'=>'منخفض', 'fr'=>'Faible', 'en'=>'Low'],
    'ps_cl_mod'         => ['ar'=>'متوسط', 'fr'=>'Modéré', 'en'=>'Moderate'],
    'ps_cl_high'        => ['ar'=>'مرتفع', 'fr'=>'Élevé', 'en'=>'High'],
    'ps_cl_excellent'   => ['ar'=>'ممتاز', 'fr'=>'Excellent', 'en'=>'Excellent'],
    'ps_cl_avg'         => ['ar'=>'متوسط', 'fr'=>'Moyen', 'en'=>'Average'],
    'ps_cl_concord'     => ['ar'=>'متطابق', 'fr'=>'Concordant', 'en'=>'Concordant'],
    'ps_cl_underest'    => ['ar'=>'يقلل من تقدير وزنه', 'fr'=>'Sous-estime son poids', 'en'=>'Underestimates weight'],
    'ps_cl_overest'     => ['ar'=>'يبالغ في تقدير وزنه', 'fr'=>'Surestime son poids', 'en'=>'Overestimates weight'],
    'ps_cl_favorable'   => ['ar'=>'مواتٍ', 'fr'=>'Favorable', 'en'=>'Favourable'],
    'ps_cl_neutral'     => ['ar'=>'محايد', 'fr'=>'Neutre', 'en'=>'Neutral'],
    'ps_cl_unfavorable' => ['ar'=>'غير موات', 'fr'=>'Défavorable', 'en'=>'Unfavourable'],
];

// ────────────────────────────────────────────────────────────────────
// Section 2: v2 extension (90+ literature-based items, 2024)
// ────────────────────────────────────────────────────────────────────
$psx = [

// ════ STEP 1 — Anthropométrie complète ═══════════════════════
'psx_step1' => ['ar'=>'الملامح والقياسات', 'fr'=>'Profil & mensurations', 'en'=>'Profile & measurements'],
'psx_waist' => ['ar'=>'محيط الخصر (سم) — اختياري لكن مفيد', 'fr'=>'Tour de taille (cm) — optionnel mais utile', 'en'=>'Waist circumference (cm) — optional but useful'],
'psx_waist_help' => ['ar'=>'قِس عند مستوى السرّة، الزفير الطبيعي، دون شدّ.', 'fr'=>'Mesurez au niveau du nombril, expiration normale, sans serrer.', 'en'=>'Measure at navel level, normal exhalation, without tightening.'],
'psx_whtr' => ['ar'=>'نسبة الخصر/الطول', 'fr'=>'Rapport taille/hauteur (WHtR)', 'en'=>'Waist-to-height ratio (WHtR)'],
'psx_residence' => ['ar'=>'منطقة الإقامة', 'fr'=>'Type de résidence', 'en'=>'Residence type'],
'psx_urban' => ['ar'=>'حضرية', 'fr'=>'Urbaine', 'en'=>'Urban'],
'psx_periurban' => ['ar'=>'شبه حضرية', 'fr'=>'Péri-urbaine', 'en'=>'Peri-urban'],
'psx_rural' => ['ar'=>'ريفية', 'fr'=>'Rurale', 'en'=>'Rural'],
'psx_dont_know' => ['ar'=>'لا أعرف', 'fr'=>'Je ne sais pas', 'en'=>'I don\'t know'],

// ════ STEP 2 — Histoire périnatale & familiale ════════════════
'psx_step2' => ['ar'=>'البدايات الأولى والعائلة', 'fr'=>'Premiers jours & famille', 'en'=>'Early life & family'],
'psx_perinatal_title' => ['ar'=>'الأشهر الأولى من حياتك (الأقوى تأثيراً علمياً)', 'fr'=>'Vos 1000 premiers jours (le plus déterminant scientifiquement)', 'en'=>'Your first 1000 days (the strongest scientific predictor)'],
'psx_perinatal_note' => ['ar'=>'إذا لا تعرف، اسأل والديك أو اختر "لا أعرف". هذه الأسئلة لا تُحكم عليك، بل تساعد في فهم العوامل المبكرة.', 'fr'=>'Si vous ne savez pas, demandez à vos parents ou cochez « je ne sais pas ». Ces questions ne sont pas un jugement, elles éclairent les facteurs précoces.', 'en'=>'If you don\'t know, ask your parents or select "don\'t know". These questions are not judgments — they illuminate early factors.'],

'psx_breastfed_type' => ['ar'=>'نوع الرضاعة في أوّل 6 أشهر', 'fr'=>'Type d\'allaitement durant les 6 premiers mois', 'en'=>'Feeding type in first 6 months'],
'psx_bf_exclusive' => ['ar'=>'طبيعية حصرية (دون أي حليب صناعي)', 'fr'=>'Maternel exclusif (aucun lait industriel)', 'en'=>'Exclusive breastfeeding (no formula)'],
'psx_bf_mixed' => ['ar'=>'مختلطة (طبيعية + صناعية)', 'fr'=>'Mixte (maternel + artificiel)', 'en'=>'Mixed (breast + formula)'],
'psx_bf_formula' => ['ar'=>'صناعية فقط', 'fr'=>'Artificiel uniquement', 'en'=>'Formula only'],

'psx_breastfed_duration' => ['ar'=>'مدّة الرضاعة الطبيعية الإجمالية', 'fr'=>'Durée totale d\'allaitement maternel', 'en'=>'Total breastfeeding duration'],
'psx_bf_never' => ['ar'=>'لم تُرضَع أبداً', 'fr'=>'Jamais allaité', 'en'=>'Never breastfed'],
'psx_bf_lt1' => ['ar'=>'أقل من شهر', 'fr'=>'Moins de 1 mois', 'en'=>'Less than 1 month'],
'psx_bf_1_3' => ['ar'=>'1-3 أشهر', 'fr'=>'1-3 mois', 'en'=>'1-3 months'],
'psx_bf_3_6' => ['ar'=>'3-6 أشهر', 'fr'=>'3-6 mois', 'en'=>'3-6 months'],
'psx_bf_6_12' => ['ar'=>'6-12 شهراً', 'fr'=>'6-12 mois', 'en'=>'6-12 months'],
'psx_bf_gt12' => ['ar'=>'أكثر من 12 شهراً', 'fr'=>'Plus de 12 mois', 'en'=>'More than 12 months'],

'psx_solid_intro' => ['ar'=>'عمر إدخال الأطعمة الصلبة', 'fr'=>'Âge d\'introduction des aliments solides', 'en'=>'Age of solid food introduction'],
'psx_solid_lt4' => ['ar'=>'قبل 4 أشهر (مبكّر)', 'fr'=>'Avant 4 mois (précoce)', 'en'=>'Before 4 months (early)'],
'psx_solid_4_6' => ['ar'=>'بين 4-6 أشهر', 'fr'=>'Entre 4-6 mois', 'en'=>'Between 4-6 months'],
'psx_solid_gt6' => ['ar'=>'بعد 6 أشهر (موصى به)', 'fr'=>'Après 6 mois (recommandé)', 'en'=>'After 6 months (recommended)'],

'psx_mother_smoked' => ['ar'=>'هل دخّنت والدتك أثناء الحمل بك؟', 'fr'=>'Votre mère a-t-elle fumé pendant la grossesse ?', 'en'=>'Did your mother smoke during pregnancy?'],
'psx_gest_diabetes' => ['ar'=>'هل أُصيبت والدتك بسكّري الحمل؟', 'fr'=>'Votre mère a-t-elle eu un diabète gestationnel ?', 'en'=>'Did your mother have gestational diabetes?'],
'psx_mother_overweight_preg' => ['ar'=>'هل كانت والدتك تعاني من زيادة وزن قبل/أثناء الحمل؟', 'fr'=>'Votre mère avait-elle un surpoids avant/pendant la grossesse ?', 'en'=>'Was your mother overweight before/during pregnancy?'],
'psx_rapid_weight_gain' => ['ar'=>'هل قيل لك أنّك كنت رضيعاً يكتسب وزناً بسرعة (قبل سنّ 2)؟', 'fr'=>'Vous a-t-on dit que vous étiez un bébé qui prenait du poids rapidement (avant 2 ans) ?', 'en'=>'Were you told you gained weight rapidly as a baby (before age 2)?'],

'psx_family_history_title' => ['ar'=>'تاريخ العائلة', 'fr'=>'Antécédents familiaux', 'en'=>'Family history'],
'psx_father_bmi' => ['ar'=>'تقدير وزن الأب', 'fr'=>'Estimation du poids du père', 'en'=>'Father\'s estimated weight'],
'psx_mother_bmi' => ['ar'=>'تقدير وزن الأم', 'fr'=>'Estimation du poids de la mère', 'en'=>'Mother\'s estimated weight'],
'psx_bmi_normal' => ['ar'=>'وزن طبيعي', 'fr'=>'Poids normal', 'en'=>'Normal'],
'psx_bmi_over' => ['ar'=>'زيادة وزن', 'fr'=>'Surpoids', 'en'=>'Overweight'],
'psx_bmi_obese' => ['ar'=>'سمنة', 'fr'=>'Obésité', 'en'=>'Obese'],
'psx_bmi_under' => ['ar'=>'نحافة', 'fr'=>'Maigreur', 'en'=>'Underweight'],

'psx_siblings_obese' => ['ar'=>'هل أحد إخوتك يعاني من زيادة الوزن أو السمنة؟', 'fr'=>'Avez-vous un frère/sœur en surpoids ou obèse ?', 'en'=>'Any sibling overweight or obese?'],
'psx_family_t2d' => ['ar'=>'هل يوجد في عائلتك (والدين، أعمام، أجداد) سكّري نوع 2؟', 'fr'=>'Diabète type 2 dans votre famille (parents, oncles, grands-parents) ?', 'en'=>'Type 2 diabetes in family (parents, uncles, grandparents)?'],
'psx_family_htn' => ['ar'=>'ضغط دم مرتفع في العائلة؟', 'fr'=>'Hypertension dans la famille ?', 'en'=>'Hypertension in family?'],
'psx_family_chol' => ['ar'=>'كوليسترول مرتفع أو مرض قلبي مبكّر (قبل 55 سنة) في العائلة؟', 'fr'=>'Cholestérol élevé ou cardiopathie précoce (< 55 ans) ?', 'en'=>'High cholesterol or early heart disease (<55y) in family?'],
'psx_family_thyroid' => ['ar'=>'مشاكل الغدّة الدرقيّة في العائلة؟', 'fr'=>'Problèmes de thyroïde dans la famille ?', 'en'=>'Thyroid problems in family?'],

// ════ STEP 3 — FFQ étendu (NOVA UPF screener) ══════════════════
'psx_step3' => ['ar'=>'تردّد الأغذية والأطعمة فائقة المعالجة', 'fr'=>'Fréquences alimentaires & UPF', 'en'=>'Food frequencies & UPF'],
'psx_upf_title' => ['ar'=>'الأطعمة فائقة المعالجة (NOVA-4)', 'fr'=>'Aliments ultra-transformés (NOVA-4)', 'en'=>'Ultra-processed foods (NOVA-4)'],
'psx_upf_note' => ['ar'=>'هذه الأطعمة (حسب تصنيف NOVA الدولي) مرتبطة علمياً بالسمنة. حدّد تردّد كل صنف.', 'fr'=>'Ces aliments (classification NOVA internationale) sont liés à l\'obésité par la littérature. Indiquez la fréquence de chacun.', 'en'=>'These foods (NOVA international classification) are scientifically linked to obesity. Indicate frequency of each.'],
'psx_upf_soda' => ['ar'=>'مشروبات غازية محلّاة (كوكا، سبرايت...)', 'fr'=>'Boissons gazeuses sucrées (cola, soda)', 'en'=>'Sugary sodas (cola, fizzy drinks)'],
'psx_upf_energy' => ['ar'=>'مشروبات طاقة (Red Bull، Monster)', 'fr'=>'Boissons énergisantes (Red Bull, Monster)', 'en'=>'Energy drinks (Red Bull, Monster)'],
'psx_upf_juices' => ['ar'=>'عصائر معلّبة/مصنّعة', 'fr'=>'Jus emballés / industriels', 'en'=>'Packaged industrial juices'],
'psx_upf_cookies' => ['ar'=>'بسكويتات وكعك صناعي', 'fr'=>'Biscuits et gâteaux industriels', 'en'=>'Industrial biscuits & cakes'],
'psx_upf_candy' => ['ar'=>'حلويات وشوكولاتة صناعية', 'fr'=>'Bonbons et chocolats industriels', 'en'=>'Industrial sweets & chocolate'],
'psx_upf_icecream' => ['ar'=>'بوظة صناعية', 'fr'=>'Glaces industrielles', 'en'=>'Industrial ice cream'],
'psx_upf_pizza' => ['ar'=>'بيتزا وسندويتشات جاهزة', 'fr'=>'Pizza et sandwichs préparés', 'en'=>'Pre-made pizza & sandwiches'],
'psx_upf_ready' => ['ar'=>'وجبات جاهزة (مجمّدة أو معلّبة)', 'fr'=>'Plats préparés (surgelés ou conserves)', 'en'=>'Ready meals (frozen or canned)'],
'psx_upf_chips' => ['ar'=>'شيبس ووجبات مالحة', 'fr'=>'Chips et snacks salés', 'en'=>'Chips & salty snacks'],
'psx_upf_meat' => ['ar'=>'لحوم مصنّعة (نقانق، ناغتس، برغر مجمّد)', 'fr'=>'Viandes transformées (saucisses, nuggets, steaks panés)', 'en'=>'Processed meats (sausages, nuggets, breaded patties)'],
'psx_upf_cereals' => ['ar'=>'حبوب إفطار صناعية محلّاة', 'fr'=>'Céréales du petit-déjeuner sucrées', 'en'=>'Sugary breakfast cereals'],
'psx_upf_yogurt' => ['ar'=>'ياغورت/زبادي محلّى ومنكّه صناعياً', 'fr'=>'Yaourts aromatisés/sucrés industriels', 'en'=>'Flavored/sugary industrial yogurt'],
'psx_upf_sauces' => ['ar'=>'صلصات صناعية (مايونيز، كاتشب)', 'fr'=>'Sauces industrielles (mayonnaise, ketchup)', 'en'=>'Industrial sauces (mayo, ketchup)'],
'psx_upf_white_bread' => ['ar'=>'خبز أبيض صناعي معلّب', 'fr'=>'Pain blanc industriel emballé', 'en'=>'Packaged industrial white bread'],
'psx_upf_fastfood' => ['ar'=>'وجبات سريعة (ماك، KFC، فاست فود محلي)', 'fr'=>'Fast food (McDo, KFC, fast food local)', 'en'=>'Fast food (McDo, KFC, local fast food)'],

// ════ STEP 4 — Comportements alimentaires ═════════════════════
'psx_step4' => ['ar'=>'السلوكيات الغذائية', 'fr'=>'Comportements alimentaires', 'en'=>'Eating behaviors'],
'psx_eating_speed' => ['ar'=>'سرعتك في الأكل عادةً', 'fr'=>'Votre vitesse habituelle en mangeant', 'en'=>'Your usual eating speed'],
'psx_speed_very_slow' => ['ar'=>'بطيء جداً', 'fr'=>'Très lent', 'en'=>'Very slow'],
'psx_speed_slow' => ['ar'=>'بطيء', 'fr'=>'Lent', 'en'=>'Slow'],
'psx_speed_normal' => ['ar'=>'متوسط', 'fr'=>'Moyen', 'en'=>'Average'],
'psx_speed_fast' => ['ar'=>'سريع', 'fr'=>'Rapide', 'en'=>'Fast'],
'psx_speed_very_fast' => ['ar'=>'سريع جداً (تنهي طبقك في أقل من 10 دقائق)', 'fr'=>'Très rapide (assiette finie en moins de 10 min)', 'en'=>'Very fast (plate finished in under 10 min)'],

'psx_portion_size' => ['ar'=>'حجم طبقك المعتاد مقارنةً بأقرانك', 'fr'=>'Taille de votre assiette habituelle vs vos pairs', 'en'=>'Your usual plate size vs peers'],
'psx_portion_smaller' => ['ar'=>'أصغر', 'fr'=>'Plus petite', 'en'=>'Smaller'],
'psx_portion_same' => ['ar'=>'متشابهة', 'fr'=>'Semblable', 'en'=>'Similar'],
'psx_portion_bigger' => ['ar'=>'أكبر', 'fr'=>'Plus grande', 'en'=>'Larger'],
'psx_portion_much_bigger' => ['ar'=>'أكبر بكثير', 'fr'=>'Beaucoup plus grande', 'en'=>'Much larger'],
'psx_second_serving' => ['ar'=>'هل تأخذ صحناً ثانياً (تَتزوّد) في معظم الوجبات؟', 'fr'=>'Vous resservez-vous (rab) à la plupart des repas ?', 'en'=>'Do you take seconds at most meals?'],

'psx_eat_screen' => ['ar'=>'هل تأكل أمام الشاشة (تلفاز/هاتف)؟', 'fr'=>'Mangez-vous devant un écran (TV/téléphone) ?', 'en'=>'Do you eat in front of a screen (TV/phone)?'],
'psx_screen_always' => ['ar'=>'دائماً', 'fr'=>'Toujours', 'en'=>'Always'],
'psx_screen_often' => ['ar'=>'غالباً', 'fr'=>'Souvent', 'en'=>'Often'],
'psx_screen_sometimes' => ['ar'=>'أحياناً', 'fr'=>'Parfois', 'en'=>'Sometimes'],
'psx_screen_rarely' => ['ar'=>'نادراً', 'fr'=>'Rarement', 'en'=>'Rarely'],
'psx_screen_never' => ['ar'=>'أبداً', 'fr'=>'Jamais', 'en'=>'Never'],

'psx_late_eating' => ['ar'=>'هل تأكل بعد الساعة 21h (وجبة كاملة، وليس فقط ماء)؟', 'fr'=>'Mangez-vous après 21h (repas, pas juste de l\'eau) ?', 'en'=>'Do you eat after 9 PM (a meal, not just water)?'],
'psx_late_daily' => ['ar'=>'كلّ يوم', 'fr'=>'Tous les jours', 'en'=>'Daily'],
'psx_late_freq' => ['ar'=>'3-5 مرات/أسبوع', 'fr'=>'3-5 fois/sem', 'en'=>'3-5 times/wk'],
'psx_late_sometimes' => ['ar'=>'1-2 مرات/أسبوع', 'fr'=>'1-2 fois/sem', 'en'=>'1-2 times/wk'],
'psx_late_rare' => ['ar'=>'نادراً', 'fr'=>'Rarement', 'en'=>'Rarely'],

'psx_outside_meals' => ['ar'=>'كم مرّة تأكل في الخارج (مطعم/فاست فود/شارع) في الأسبوع؟', 'fr'=>'Combien de repas hors-maison (resto/fast food/rue) par semaine ?', 'en'=>'How many meals out (restaurant/fast food/street) per week?'],
'psx_out_0' => ['ar'=>'0', 'fr'=>'0', 'en'=>'0'],
'psx_out_1' => ['ar'=>'1', 'fr'=>'1', 'en'=>'1'],
'psx_out_2_3' => ['ar'=>'2-3', 'fr'=>'2-3', 'en'=>'2-3'],
'psx_out_4_6' => ['ar'=>'4-6', 'fr'=>'4-6', 'en'=>'4-6'],
'psx_out_gt7' => ['ar'=>'7 أو أكثر', 'fr'=>'7 ou plus', 'en'=>'7 or more'],

'psx_bread_per_day' => ['ar'=>'عدد الأرغفة/الباغيتات يومياً (تقريباً)', 'fr'=>'Nombre de pains/baguettes par jour (approx.)', 'en'=>'Bread loaves per day (approx)'],
'psx_bread_lt1' => ['ar'=>'أقل من واحد', 'fr'=>'Moins d\'1', 'en'=>'Less than 1'],
'psx_bread_1_2' => ['ar'=>'1-2', 'fr'=>'1-2', 'en'=>'1-2'],
'psx_bread_3_4' => ['ar'=>'3-4', 'fr'=>'3-4', 'en'=>'3-4'],
'psx_bread_gt5' => ['ar'=>'5 أو أكثر', 'fr'=>'5 ou plus', 'en'=>'5 or more'],

'psx_sugar_drinks' => ['ar'=>'عدد ملاعق السكر في الشاي/القهوة (يومياً)', 'fr'=>'Nombre de cuillères de sucre dans thé/café (par jour)', 'en'=>'Teaspoons of sugar in tea/coffee (per day)'],
'psx_sugar_0' => ['ar'=>'0', 'fr'=>'0', 'en'=>'0'],
'psx_sugar_1_3' => ['ar'=>'1-3', 'fr'=>'1-3', 'en'=>'1-3'],
'psx_sugar_4_6' => ['ar'=>'4-6', 'fr'=>'4-6', 'en'=>'4-6'],
'psx_sugar_gt6' => ['ar'=>'أكثر من 6', 'fr'=>'Plus de 6', 'en'=>'More than 6'],

'psx_cooking_method' => ['ar'=>'طريقة الطهي الأكثر شيوعاً في منزلك', 'fr'=>'Mode de cuisson dominant chez vous', 'en'=>'Dominant cooking method at home'],
'psx_cook_fried' => ['ar'=>'قلي', 'fr'=>'Friture', 'en'=>'Frying'],
'psx_cook_grilled' => ['ar'=>'شواء', 'fr'=>'Grillade', 'en'=>'Grilling'],
'psx_cook_baked' => ['ar'=>'فرن', 'fr'=>'Four', 'en'=>'Oven baking'],
'psx_cook_steamed' => ['ar'=>'بخار', 'fr'=>'Vapeur', 'en'=>'Steaming'],
'psx_cook_slow' => ['ar'=>'طهي بطيء/مرق', 'fr'=>'Cuisson lente / mijoté', 'en'=>'Slow cooking / stewing'],

'psx_ramadan' => ['ar'=>'هل صمت رمضان السنة الماضية كاملاً؟', 'fr'=>'Avez-vous jeûné le Ramadan complet l\'an dernier ?', 'en'=>'Did you fast Ramadan fully last year?'],
'psx_ramadan_no' => ['ar'=>'لا (لم أصم بسبب السن/مرض/أخرى)', 'fr'=>'Non (pas jeûné — âge/maladie/autre)', 'en'=>'No (didn\'t fast — age/illness/other)'],
'psx_ramadan_yes' => ['ar'=>'نعم كاملاً', 'fr'=>'Oui, complet', 'en'=>'Yes, fully'],
'psx_ramadan_partial' => ['ar'=>'نعم جزئياً', 'fr'=>'Oui, partiel', 'en'=>'Yes, partially'],
'psx_ramadan_change' => ['ar'=>'تغيّر وزنك بعد رمضان مقارنةً بقبله', 'fr'=>'Variation de votre poids après le Ramadan vs avant', 'en'=>'Weight change after Ramadan vs before'],
'psx_ram_lost' => ['ar'=>'نقص (-2 كغ أو أكثر)', 'fr'=>'Perte (-2 kg ou plus)', 'en'=>'Loss (-2 kg or more)'],
'psx_ram_stable' => ['ar'=>'مستقر', 'fr'=>'Stable', 'en'=>'Stable'],
'psx_ram_gained' => ['ar'=>'زيادة (+2 كغ أو أكثر)', 'fr'=>'Prise (+2 kg ou plus)', 'en'=>'Gain (+2 kg or more)'],
'psx_ram_unknown' => ['ar'=>'لا أعرف', 'fr'=>'Je ne sais pas', 'en'=>'Unknown'],

// ════ STEP 5 — Activité, sédentarité, écrans (granularité fine), sommeil ══
'psx_step5' => ['ar'=>'النشاط، الشاشات، النوم', 'fr'=>'Activité, écrans, sommeil', 'en'=>'Activity, screens, sleep'],
'psx_screen_granular_help' => ['ar'=>'الوقت اليومي التقريبي خارج الدراسة/العمل. كن صادقاً، النتيجة لك وحدك.', 'fr'=>'Temps quotidien moyen hors études/travail. Soyez honnête, le rapport est pour vous seul.', 'en'=>'Average daily time outside school/work. Be honest — the report is for you alone.'],
'psx_st_lt10' => ['ar'=>'< 10 دقائق', 'fr'=>'< 10 min', 'en'=>'< 10 min'],
'psx_st_10_30' => ['ar'=>'10-30 دقيقة', 'fr'=>'10-30 min', 'en'=>'10-30 min'],
'psx_st_30_60' => ['ar'=>'30 د - 1 سا', 'fr'=>'30 min – 1 h', 'en'=>'30 min – 1 h'],
'psx_st_1_2' => ['ar'=>'1-2 سا', 'fr'=>'1-2 h', 'en'=>'1-2 h'],
'psx_st_2_4' => ['ar'=>'2-4 سا', 'fr'=>'2-4 h', 'en'=>'2-4 h'],
'psx_st_4_6' => ['ar'=>'4-6 سا', 'fr'=>'4-6 h', 'en'=>'4-6 h'],
'psx_st_gt6' => ['ar'=>'> 6 سا', 'fr'=>'> 6 h', 'en'=>'> 6 h'],
'psx_screen_weekday' => ['ar'=>'يوم دراسي/عمل', 'fr'=>'Jour d\'école/travail', 'en'=>'School/workday'],
'psx_screen_weekend' => ['ar'=>'يوم عطلة', 'fr'=>'Jour de repos', 'en'=>'Day off'],
'psx_screen_phone_social' => ['ar'=>'هاتف (شبكات اجتماعية، يوتيوب، تيك توك...)', 'fr'=>'Téléphone (réseaux, YouTube, TikTok)', 'en'=>'Phone (social, YouTube, TikTok)'],
'psx_screen_games_x' => ['ar'=>'ألعاب فيديو', 'fr'=>'Jeux vidéo', 'en'=>'Video games'],
'psx_screen_tv_x' => ['ar'=>'تلفاز / مسلسلات', 'fr'=>'TV / séries', 'en'=>'TV / series'],
'psx_screen_pc_x' => ['ar'=>'حاسوب/جهاز لوحي (ترفيه)', 'fr'=>'PC / tablette (loisir)', 'en'=>'Computer / tablet (leisure)'],
'psx_screen_in_bed' => ['ar'=>'هاتف/شاشة في السرير قبل النوم مباشرة', 'fr'=>'Téléphone/écran au lit juste avant de dormir', 'en'=>'Phone/screen in bed right before sleep'],

'psx_mvpa' => ['ar'=>'مدّة النشاط البدني المعتدل-القوي (تتنفّس بصعوبة) يومياً', 'fr'=>'Durée d\'activité physique modérée-vigoureuse (essoufflement) par jour', 'en'=>'Daily moderate-to-vigorous physical activity (breathing hard)'],
'psx_mvpa_none' => ['ar'=>'لا شيء', 'fr'=>'Aucune', 'en'=>'None'],
'psx_mvpa_lt30' => ['ar'=>'< 30 دقيقة', 'fr'=>'< 30 min', 'en'=>'< 30 min'],
'psx_mvpa_30_60' => ['ar'=>'30-60 دقيقة', 'fr'=>'30-60 min', 'en'=>'30-60 min'],
'psx_mvpa_1_2' => ['ar'=>'1-2 ساعة', 'fr'=>'1-2 h', 'en'=>'1-2 h'],
'psx_mvpa_gt2' => ['ar'=>'> 2 ساعة', 'fr'=>'> 2 h', 'en'=>'> 2 h'],
'psx_can_walk30' => ['ar'=>'هل تستطيع المشي 30 دقيقة دون استراحة؟', 'fr'=>'Pouvez-vous marcher 30 min sans pause ?', 'en'=>'Can you walk 30 min without a break?'],
'psx_fitness_self' => ['ar'=>'تقييمك الذاتي للياقتك البدنية (1 ضعيفة - 10 ممتازة)', 'fr'=>'Auto-évaluation de votre forme physique (1 mauvaise – 10 excellente)', 'en'=>'Self-rated fitness (1 poor – 10 excellent)'],

'psx_snoring' => ['ar'=>'هل قيل لك أنّك تشخر بقوّة أو تنقطع أنفاسك أثناء النوم؟', 'fr'=>'Vous a-t-on dit que vous ronflez fort ou que vous arrêtez de respirer la nuit ?', 'en'=>'Have you been told you snore loudly or stop breathing while sleeping?'],
'psx_daytime_sleepy' => ['ar'=>'هل تشعر بنعاس شديد خلال النهار حتّى بعد ليلة كاملة؟', 'fr'=>'Somnolence diurne intense même après une nuit complète ?', 'en'=>'Intense daytime sleepiness even after a full night?'],
'psx_sleep_quality' => ['ar'=>'جودة نومك الإجمالية (1 سيّئة - 10 ممتازة)', 'fr'=>'Qualité globale du sommeil (1 mauvaise – 10 excellente)', 'en'=>'Overall sleep quality (1 poor – 10 excellent)'],

// ════ STEP 6 — Psychosocial, SCOFF, médical ════════════════════
'psx_step6' => ['ar'=>'الجانب النفسي والطبي', 'fr'=>'Psycho-social & médical', 'en'=>'Psychosocial & medical'],

'psx_psycho_title' => ['ar'=>'الجانب النفسي والاجتماعي', 'fr'=>'Bien-être psycho-social', 'en'=>'Psychosocial well-being'],
'psx_psycho_note' => ['ar'=>'هذه الأسئلة سرّية تماماً. الإجابة الصادقة تعطيك تقريراً أدقّ.', 'fr'=>'Ces questions sont strictement confidentielles. L\'honnêteté améliore la précision du rapport.', 'en'=>'These questions are strictly confidential. Honesty improves report accuracy.'],

'psx_phq_down' => ['ar'=>'خلال الأسبوعين الماضيين: هل شعرت بالحزن، الكآبة، أو فقدان الأمل؟', 'fr'=>'Ces 2 dernières semaines : tristesse, déprime, ou perte d\'espoir ?', 'en'=>'Past 2 weeks: sadness, depression, or hopelessness?'],
'psx_phq_interest' => ['ar'=>'خلال الأسبوعين الماضيين: قلّ اهتمامك أو متعتك بأشياء كنت تحبّها؟', 'fr'=>'Ces 2 dernières semaines : moins d\'intérêt ou de plaisir pour ce que vous aimiez ?', 'en'=>'Past 2 weeks: less interest or pleasure in things you used to enjoy?'],
'psx_gad_worry' => ['ar'=>'خلال الأسبوعين الماضيين: شعرت بقلق لا يمكنك السيطرة عليه؟', 'fr'=>'Ces 2 dernières semaines : inquiétude incontrôlable ?', 'en'=>'Past 2 weeks: uncontrollable worry?'],
'psx_phq_almost_every' => ['ar'=>'تقريباً كل يوم', 'fr'=>'Presque tous les jours', 'en'=>'Almost every day'],
'psx_phq_morehalf' => ['ar'=>'أكثر من نصف الأيام', 'fr'=>'Plus de la moitié des jours', 'en'=>'More than half the days'],
'psx_phq_some' => ['ar'=>'عدّة أيام', 'fr'=>'Plusieurs jours', 'en'=>'Several days'],
'psx_phq_none' => ['ar'=>'أبداً', 'fr'=>'Jamais', 'en'=>'Not at all'],

'psx_body_satisfaction' => ['ar'=>'مدى رضاك عن جسمك', 'fr'=>'Satisfaction de votre corps', 'en'=>'Body satisfaction'],
'psx_bs_very_sat' => ['ar'=>'راضٍ جداً', 'fr'=>'Très satisfait', 'en'=>'Very satisfied'],
'psx_bs_sat' => ['ar'=>'راضٍ', 'fr'=>'Satisfait', 'en'=>'Satisfied'],
'psx_bs_neutral' => ['ar'=>'محايد', 'fr'=>'Neutre', 'en'=>'Neutral'],
'psx_bs_unsat' => ['ar'=>'غير راضٍ', 'fr'=>'Insatisfait', 'en'=>'Dissatisfied'],
'psx_bs_very_unsat' => ['ar'=>'غير راضٍ تماماً', 'fr'=>'Très insatisfait', 'en'=>'Very dissatisfied'],

'psx_weight_teasing' => ['ar'=>'هل تعرّضت لتنمّر أو ملاحظات حول وزنك؟', 'fr'=>'Avez-vous été moqué(e) ou critiqué(e) sur votre poids ?', 'en'=>'Have you been teased/criticized about your weight?'],
'psx_wt_often' => ['ar'=>'غالباً', 'fr'=>'Souvent', 'en'=>'Often'],
'psx_wt_some' => ['ar'=>'أحياناً', 'fr'=>'Parfois', 'en'=>'Sometimes'],
'psx_wt_never' => ['ar'=>'أبداً', 'fr'=>'Jamais', 'en'=>'Never'],

'psx_social_compare' => ['ar'=>'هل تقارن جسمك بأشخاص على إنستغرام/تيك توك؟', 'fr'=>'Vous comparez-vous à des personnes sur Instagram/TikTok ?', 'en'=>'Do you compare your body to people on Instagram/TikTok?'],
'psx_lonely' => ['ar'=>'هل تشعر بالوحدة؟', 'fr'=>'Vous sentez-vous seul(e) ?', 'en'=>'Do you feel lonely?'],

// SCOFF — outil validé (≥2 oui = positif)
'psx_scoff_title' => ['ar'=>'فحص علاقتك بالأكل (5 أسئلة)', 'fr'=>'Dépistage de votre relation à la nourriture (5 questions)', 'en'=>'Screening of your relationship with food (5 questions)'],
'psx_scoff_note' => ['ar'=>'هذه الأسئلة مأخوذة من أداة طبية معتمدة عالمياً (SCOFF) ولا تشخّص شيئاً. مجرّد فحص أوّلي.', 'fr'=>'Questions issues d\'un outil médical international reconnu (SCOFF). Aucun diagnostic — simple dépistage initial.', 'en'=>'Questions from an internationally recognized medical tool (SCOFF). No diagnosis — initial screening only.'],
'psx_scoff_sick' => ['ar'=>'هل تجبر نفسك على التقيّؤ عندما تشعر بامتلاء غير مريح؟', 'fr'=>'Vous faites-vous vomir parce que vous vous sentez désagréablement plein(e) ?', 'en'=>'Do you make yourself vomit because you feel uncomfortably full?'],
'psx_scoff_control' => ['ar'=>'هل تخاف من فقدان السيطرة على كمية ما تأكل؟', 'fr'=>'Avez-vous peur d\'avoir perdu le contrôle sur ce que vous mangez ?', 'en'=>'Are you worried you have lost control over how much you eat?'],
'psx_scoff_onestone' => ['ar'=>'هل فقدت أكثر من 6 كغ خلال آخر 3 أشهر؟', 'fr'=>'Avez-vous perdu plus de 6 kg en 3 mois ?', 'en'=>'Have you lost more than 6 kg in 3 months?'],
'psx_scoff_fat' => ['ar'=>'هل تشعر بأنّك "سمين" بينما يقول الآخرون أنّك نحيف؟', 'fr'=>'Pensez-vous être gros(se) alors que d\'autres vous trouvent mince ?', 'en'=>'Do you believe yourself fat when others say you are thin?'],
'psx_scoff_food' => ['ar'=>'هل تقول أنّ الطعام يهيمن على حياتك؟', 'fr'=>'Diriez-vous que la nourriture domine votre vie ?', 'en'=>'Would you say food dominates your life?'],

'psx_meds_title' => ['ar'=>'أدوية وأمراض', 'fr'=>'Médicaments et conditions médicales', 'en'=>'Medications and medical conditions'],
'psx_meds_taken' => ['ar'=>'هل تتناول حالياً أحد هذه الأدوية بانتظام؟', 'fr'=>'Prenez-vous régulièrement l\'un de ces médicaments ?', 'en'=>'Do you regularly take any of these medications?'],
'psx_med_cortico' => ['ar'=>'كورتيكوستيرويدات (كورتيزون)', 'fr'=>'Corticoïdes (cortisone)', 'en'=>'Corticosteroids (cortisone)'],
'psx_med_antidep' => ['ar'=>'مضادّات الاكتئاب', 'fr'=>'Antidépresseurs', 'en'=>'Antidepressants'],
'psx_med_antipsy' => ['ar'=>'مضادّات الذهان', 'fr'=>'Antipsychotiques', 'en'=>'Antipsychotics'],
'psx_med_contracep' => ['ar'=>'حبوب منع الحمل (للبنات)', 'fr'=>'Contraception hormonale (filles)', 'en'=>'Hormonal contraception (girls)'],
'psx_med_other' => ['ar'=>'دواء آخر مستمر', 'fr'=>'Autre médicament régulier', 'en'=>'Other regular medication'],
'psx_med_none' => ['ar'=>'لا شيء', 'fr'=>'Aucun', 'en'=>'None'],

'psx_conditions' => ['ar'=>'هل شُخّصت بأحد هذه الحالات؟', 'fr'=>'Vous a-t-on diagnostiqué l\'une de ces conditions ?', 'en'=>'Have you been diagnosed with any of these?'],
'psx_cond_thyroid' => ['ar'=>'الغدّة الدرقيّة (قصور أو فرط)', 'fr'=>'Thyroïde (hypo ou hyper)', 'en'=>'Thyroid (hypo or hyper)'],
'psx_cond_asthma' => ['ar'=>'ربو', 'fr'=>'Asthme', 'en'=>'Asthma'],
'psx_cond_diab' => ['ar'=>'سكّري', 'fr'=>'Diabète', 'en'=>'Diabetes'],
'psx_cond_pcos' => ['ar'=>'متلازمة المبيض المتعدّد الكيسات (للبنات)', 'fr'=>'Syndrome des ovaires polykystiques (filles)', 'en'=>'PCOS (girls)'],
'psx_cond_depression' => ['ar'=>'اكتئاب أو قلق مزمن', 'fr'=>'Dépression ou anxiété chronique', 'en'=>'Depression or chronic anxiety'],
'psx_cond_none' => ['ar'=>'لا شيء', 'fr'=>'Aucune', 'en'=>'None'],

'psx_weight_change6m' => ['ar'=>'كيف تطوّر وزنك في آخر 6 أشهر؟', 'fr'=>'Évolution de votre poids ces 6 derniers mois ?', 'en'=>'How has your weight evolved in the last 6 months?'],
'psx_wc_stable' => ['ar'=>'مستقر (±2 كغ)', 'fr'=>'Stable (±2 kg)', 'en'=>'Stable (±2 kg)'],
'psx_wc_gain' => ['ar'=>'زاد 3-5 كغ', 'fr'=>'Pris 3-5 kg', 'en'=>'Gained 3-5 kg'],
'psx_wc_gain_lot' => ['ar'=>'زاد أكثر من 5 كغ', 'fr'=>'Pris plus de 5 kg', 'en'=>'Gained more than 5 kg'],
'psx_wc_lost' => ['ar'=>'نقص 3-5 كغ', 'fr'=>'Perdu 3-5 kg', 'en'=>'Lost 3-5 kg'],
'psx_wc_lost_lot' => ['ar'=>'نقص أكثر من 5 كغ', 'fr'=>'Perdu plus de 5 kg', 'en'=>'Lost more than 5 kg'],

'psx_cycle_irregular' => ['ar'=>'دورة غير منتظمة (للبنات بعد البلوغ بسنتين)', 'fr'=>'Cycles menstruels irréguliers (filles, 2 ans post-puberté)', 'en'=>'Irregular menstrual cycles (girls, 2y post-puberty)'],

// ════ Indicateurs supplémentaires pour le rapport ═════════════
'psx_ind_whtr' => ['ar'=>'نسبة الخصر/الطول (السمنة البطنية)', 'fr'=>'Rapport taille/hauteur (obésité abdominale)', 'en'=>'Waist-to-height ratio (abdominal obesity)'],
'psx_ind_upf' => ['ar'=>'استهلاك الأطعمة فائقة المعالجة', 'fr'=>'Consommation d\'aliments ultra-transformés', 'en'=>'Ultra-processed food consumption'],
'psx_ind_perinatal' => ['ar'=>'العوامل المبكرة (1000 يوم الأولى)', 'fr'=>'Facteurs précoces (1000 premiers jours)', 'en'=>'Early-life factors (first 1000 days)'],
'psx_ind_genetic' => ['ar'=>'العامل الوراثي/العائلي', 'fr'=>'Risque génétique / familial', 'en'=>'Genetic / family risk'],
'psx_ind_scoff' => ['ar'=>'فحص اضطرابات الأكل (SCOFF)', 'fr'=>'Dépistage troubles alimentaires (SCOFF)', 'en'=>'Eating disorders screen (SCOFF)'],
'psx_ind_mental' => ['ar'=>'الصحّة النفسية (PHQ/GAD)', 'fr'=>'Santé mentale (PHQ/GAD)', 'en'=>'Mental health (PHQ/GAD)'],
'psx_ind_eating_behav' => ['ar'=>'السلوكيات الغذائية', 'fr'=>'Comportements alimentaires', 'en'=>'Eating behaviors'],
'psx_ind_apnea' => ['ar'=>'خطر انقطاع النفس النومي', 'fr'=>'Risque d\'apnée du sommeil', 'en'=>'Sleep apnea risk'],
'psx_ind_meds' => ['ar'=>'تأثير الأدوية على الوزن', 'fr'=>'Impact médicamenteux sur le poids', 'en'=>'Medication weight impact'],

// ════ Boutons UI supplémentaires ═════════════════════════════
'psx_save_progress' => ['ar'=>'حُفظ تلقائياً ✓', 'fr'=>'Sauvegardé auto ✓', 'en'=>'Auto-saved ✓'],
'psx_resume' => ['ar'=>'استئناف الاستبيان السابق', 'fr'=>'Reprendre le questionnaire précédent', 'en'=>'Resume previous questionnaire'],
'psx_methodology' => ['ar'=>'المنهجية والمراجع العلمية', 'fr'=>'Méthodologie & références scientifiques', 'en'=>'Methodology & scientific references'],

];

// ────────────────────────────────────────────────────────────────────
// Section 3: v3 (SES, puberty, Algerian FFQ, smoking, jet-lag, etc.)
// ────────────────────────────────────────────────────────────────────
$psv3 = [

// ════════════ A. SES — STATUT SOCIO-ÉCONOMIQUE ════════════
'psv_ses_title'      => ['ar'=>'الوضع الاجتماعي-الاقتصادي', 'fr'=>'Statut socio-économique', 'en'=>'Socioeconomic status'],
'psv_ses_note'       => ['ar'=>'هذه المعلومات تساعد في فهم العوامل البيئية والاقتصادية المؤثرة على النمط الغذائي. مجهولة الهوية تماماً.', 'fr'=>'Ces informations aident à comprendre les facteurs environnementaux et économiques qui influencent le mode alimentaire. Strictement anonymes.', 'en'=>'This information helps understand environmental and economic factors influencing diet. Strictly anonymous.'],

'psv_mother_edu'     => ['ar'=>'أعلى مستوى تعليمي للأم', 'fr'=>'Niveau d\'études maternel le plus élevé', 'en'=>'Mother\'s highest education level'],
'psv_father_edu'     => ['ar'=>'أعلى مستوى تعليمي للأب', 'fr'=>'Niveau d\'études paternel le plus élevé', 'en'=>'Father\'s highest education level'],
'psv_edu_none'       => ['ar'=>'لا تعليم', 'fr'=>'Aucun', 'en'=>'None'],
'psv_edu_primary'    => ['ar'=>'ابتدائي', 'fr'=>'Primaire', 'en'=>'Primary'],
'psv_edu_middle'     => ['ar'=>'متوسّط', 'fr'=>'Moyen / Collège', 'en'=>'Middle school'],
'psv_edu_secondary'  => ['ar'=>'ثانوي', 'fr'=>'Secondaire / Lycée', 'en'=>'Secondary / High school'],
'psv_edu_university' => ['ar'=>'جامعي', 'fr'=>'Universitaire', 'en'=>'University'],

'psv_father_work'    => ['ar'=>'مهنة الأب', 'fr'=>'Profession du père', 'en'=>'Father\'s occupation'],
'psv_mother_work'    => ['ar'=>'مهنة الأم', 'fr'=>'Profession de la mère', 'en'=>'Mother\'s occupation'],
'psv_work_unemp'     => ['ar'=>'بدون عمل / متقاعد', 'fr'=>'Sans emploi / retraité', 'en'=>'Unemployed / retired'],
'psv_work_informal'  => ['ar'=>'عمل غير رسمي / مياومة', 'fr'=>'Informel / journalier', 'en'=>'Informal / day-labor'],
'psv_work_manual'    => ['ar'=>'عمل يدوي / حرفي', 'fr'=>'Ouvrier / artisan', 'en'=>'Manual / craft'],
'psv_work_employee'  => ['ar'=>'موظّف', 'fr'=>'Employé', 'en'=>'Employee'],
'psv_work_prof'      => ['ar'=>'مهنة حرّة / إطار', 'fr'=>'Profession libérale / cadre', 'en'=>'Liberal / executive'],
'psv_work_housewife' => ['ar'=>'ربّة بيت', 'fr'=>'Femme au foyer', 'en'=>'Housewife'],

'psv_household'      => ['ar'=>'ظروف السكن (متعدّد الخيارات)', 'fr'=>'Conditions du foyer (plusieurs réponses possibles)', 'en'=>'Household conditions (check all that apply)'],
'psv_house_own'      => ['ar'=>'منزل مملوك للعائلة', 'fr'=>'Logement propriété de la famille', 'en'=>'Family-owned home'],
'psv_house_car'      => ['ar'=>'سيارة عائلية', 'fr'=>'Voiture familiale', 'en'=>'Family car'],
'psv_house_internet' => ['ar'=>'إنترنت في المنزل', 'fr'=>'Internet à la maison', 'en'=>'Internet at home'],
'psv_house_room'     => ['ar'=>'غرفة خاصّة (لا أتقاسمها)', 'fr'=>'Chambre personnelle (non partagée)', 'en'=>'Own bedroom (not shared)'],
'psv_house_heater'   => ['ar'=>'تدفئة مركزية أو مكيّفات', 'fr'=>'Chauffage central / climatisation', 'en'=>'Central heating / AC'],

'psv_food_security'  => ['ar'=>'في آخر 12 شهراً: هل حدث أن تخطّيت وجبة أو نقصت كميتها لأنّ الطعام لم يكن كافياً في البيت؟', 'fr'=>'Au cours des 12 derniers mois : avez-vous sauté un repas ou réduit la quantité parce qu\'il n\'y avait pas assez de nourriture à la maison ?', 'en'=>'In the past 12 months: have you skipped a meal or eaten less because there wasn\'t enough food at home?'],
'psv_food_never'     => ['ar'=>'أبداً', 'fr'=>'Jamais', 'en'=>'Never'],
'psv_food_rare'      => ['ar'=>'مرّة أو مرّتين فقط', 'fr'=>'1 ou 2 fois seulement', 'en'=>'Only 1-2 times'],
'psv_food_some'      => ['ar'=>'أحياناً', 'fr'=>'Parfois', 'en'=>'Sometimes'],
'psv_food_often'     => ['ar'=>'غالباً', 'fr'=>'Souvent', 'en'=>'Often'],

'psv_siblings_n'     => ['ar'=>'عدد إخوتك (ضمن نفس الأسرة)', 'fr'=>'Nombre de frères/sœurs (mêmes parents)', 'en'=>'Number of siblings'],

// ════════════ B. PUBERTÉ ════════════
'psv_puberty_title'  => ['ar'=>'مرحلة البلوغ', 'fr'=>'Stade pubertaire', 'en'=>'Pubertal stage'],
'psv_puberty_note'   => ['ar'=>'البلوغ المبكر مرتبط علمياً بزيادة خطر السمنة لاحقاً. هذه الأسئلة سرّية تماماً.', 'fr'=>'Une puberté précoce est scientifiquement liée à un risque accru d\'obésité ultérieure. Questions strictement confidentielles.', 'en'=>'Early puberty is scientifically linked to later obesity risk. Strictly confidential.'],

'psv_menarche'       => ['ar'=>'(للبنات) العمر عند أوّل دورة شهرية', 'fr'=>'(Filles) Âge des premières règles', 'en'=>'(Girls) Age at first period'],
'psv_men_not_yet'    => ['ar'=>'لم تأتِ بعد', 'fr'=>'Pas encore', 'en'=>'Not yet'],
'psv_men_lt10'       => ['ar'=>'قبل 10 سنوات', 'fr'=>'Avant 10 ans', 'en'=>'Before 10'],
'psv_men_10_11'      => ['ar'=>'10 - 11 سنة', 'fr'=>'10 - 11 ans', 'en'=>'10 - 11 yrs'],
'psv_men_12_13'      => ['ar'=>'12 - 13 سنة', 'fr'=>'12 - 13 ans', 'en'=>'12 - 13 yrs'],
'psv_men_14_15'      => ['ar'=>'14 - 15 سنة', 'fr'=>'14 - 15 ans', 'en'=>'14 - 15 yrs'],
'psv_men_gt15'       => ['ar'=>'بعد 15 سنة', 'fr'=>'Après 15 ans', 'en'=>'After 15'],

'psv_voice_change'   => ['ar'=>'(للأولاد) العمر تقريباً عند تغيّر الصوت', 'fr'=>'(Garçons) Âge approximatif de la mue de la voix', 'en'=>'(Boys) Approximate age of voice change'],
'psv_voi_not_yet'    => ['ar'=>'لم يحدث بعد', 'fr'=>'Pas encore', 'en'=>'Not yet'],
'psv_voi_lt12'       => ['ar'=>'قبل 12 سنة', 'fr'=>'Avant 12 ans', 'en'=>'Before 12'],
'psv_voi_12_13'      => ['ar'=>'12 - 13 سنة', 'fr'=>'12 - 13 ans', 'en'=>'12 - 13 yrs'],
'psv_voi_14_15'      => ['ar'=>'14 - 15 سنة', 'fr'=>'14 - 15 ans', 'en'=>'14 - 15 yrs'],
'psv_voi_gt15'       => ['ar'=>'بعد 15 سنة', 'fr'=>'Après 15 ans', 'en'=>'After 15'],

'psv_growth_spurt'   => ['ar'=>'متى لاحظت أكبر طفرة في الطول؟', 'fr'=>'Quand avez-vous remarqué votre plus grande poussée de croissance ?', 'en'=>'When did you notice your biggest growth spurt?'],
'psv_spurt_8_10'     => ['ar'=>'بين 8-10 سنوات (مبكّراً)', 'fr'=>'Entre 8-10 ans (précoce)', 'en'=>'Between 8-10 yrs (early)'],
'psv_spurt_11_13'    => ['ar'=>'بين 11-13 سنة', 'fr'=>'Entre 11-13 ans', 'en'=>'Between 11-13 yrs'],
'psv_spurt_14_16'    => ['ar'=>'بين 14-16 سنة', 'fr'=>'Entre 14-16 ans', 'en'=>'Between 14-16 yrs'],
'psv_spurt_late'     => ['ar'=>'بعد 16 سنة / لم تحدث', 'fr'=>'Après 16 ans / pas eu lieu', 'en'=>'After 16 / hasn\'t happened'],
'psv_spurt_adult'    => ['ar'=>'أنا بالغ، لا أتذكّر', 'fr'=>'Adulte, je ne me souviens pas', 'en'=>'Adult, don\'t remember'],

'psv_tanner_self'    => ['ar'=>'مرحلة البلوغ الذاتية (1 = طفولة، 5 = اكتمل البلوغ)', 'fr'=>'Stade pubertaire auto-évalué (1 = enfance, 5 = mature)', 'en'=>'Self-rated puberty stage (1 = child, 5 = mature)'],

// ════════════ C. FFQ ALGÉRIEN ÉTENDU ════════════
'psv_alg_title'      => ['ar'=>'أطعمة جزائرية شائعة', 'fr'=>'Aliments algériens courants', 'en'=>'Common Algerian foods'],
'psv_alg_note'       => ['ar'=>'هذه الأطعمة جزء من الواقع الغذائي اليومي في الجزائر. تردّدها يكمّل تقييم الـ NOVA.', 'fr'=>'Ces aliments font partie de la réalité alimentaire quotidienne en Algérie. Leur fréquence complète l\'évaluation NOVA.', 'en'=>'These foods are part of daily Algerian dietary reality. Their frequency complements the NOVA assessment.'],

'psv_dz_mint_tea'    => ['ar'=>'شاي بالنعناع محلّى (عدد الأكواب يومياً)', 'fr'=>'Thé à la menthe sucré (verres / jour)', 'en'=>'Sweet mint tea (glasses / day)'],
'psv_tea_0'          => ['ar'=>'0', 'fr'=>'0', 'en'=>'0'],
'psv_tea_1_2'        => ['ar'=>'1 - 2', 'fr'=>'1 - 2', 'en'=>'1 - 2'],
'psv_tea_3_4'        => ['ar'=>'3 - 4', 'fr'=>'3 - 4', 'en'=>'3 - 4'],
'psv_tea_5_6'        => ['ar'=>'5 - 6', 'fr'=>'5 - 6', 'en'=>'5 - 6'],
'psv_tea_gt6'        => ['ar'=>'أكثر من 6', 'fr'=>'Plus de 6', 'en'=>'More than 6'],

'psv_dz_harira'      => ['ar'=>'حريرة / شربة فريك (خاصة رمضان)', 'fr'=>'Harira / chorba (surtout Ramadan)', 'en'=>'Harira / chorba (esp. Ramadan)'],
'psv_dz_bourek'      => ['ar'=>'بوراك مقلي بالجبن/اللحم', 'fr'=>'Bourek frit (fromage/viande)', 'en'=>'Fried bourek (cheese/meat)'],
'psv_dz_mhajeb'      => ['ar'=>'محاجب / مسمن / خبز محشو', 'fr'=>'Mhajeb / msemen / pain farci', 'en'=>'Mhajeb / msemen / stuffed bread'],
'psv_dz_zlabia'      => ['ar'=>'زلابية / قلب اللوز / بقلاوة / مقروط', 'fr'=>'Zlabia / kalb el louz / baklawa / makroud', 'en'=>'Zlabia / kalb el louz / baklawa / makroud'],
'psv_dz_garantita'   => ['ar'=>'قرنطيطة / كرنتيكا (شارع)', 'fr'=>'Garantita / karantika (rue)', 'en'=>'Garantita / karantika (street)'],
'psv_dz_lham_dlou'   => ['ar'=>'لحم بالحلو / دوارة / كسكسي بالخروف الدسم', 'fr'=>'Lham hlou / douara / couscous gras', 'en'=>'Lham hlou / douara / fatty couscous'],
'psv_dz_leben'       => ['ar'=>'لبن / رايب / ياغورت تقليدي (غير محلّى)', 'fr'=>'Leben / raïb / yaourt traditionnel (nature)', 'en'=>'Leben / raïb / plain traditional yogurt'],
'psv_dz_dates'       => ['ar'=>'تمر طازج أو معجون', 'fr'=>'Dattes fraîches ou en pâte', 'en'=>'Fresh dates or date paste'],
'psv_dz_olives'      => ['ar'=>'زيتون أو زيت الزيتون', 'fr'=>'Olives ou huile d\'olive', 'en'=>'Olives or olive oil'],
'psv_dz_kefta_fry'   => ['ar'=>'كفتة مقلية / كرنتيكا / سفنج', 'fr'=>'Boulettes frites / sfenj / beignets', 'en'=>'Fried meatballs / sfenj / fritters'],
'psv_dz_smoothies'   => ['ar'=>'عصائر طبيعية بمضاف السكر', 'fr'=>'Jus naturels sucrés à la maison', 'en'=>'Home juices with added sugar'],

// ════════════ D. TABAC / CHICHA / VAPE ════════════
'psv_smoking_title'  => ['ar'=>'التدخين والشيشة والفيب', 'fr'=>'Tabac, chicha et vape', 'en'=>'Smoking, shisha & vape'],
'psv_smoking_note'   => ['ar'=>'هذه الأسئلة محميّة بسرّية تامّة، ولا يعرف معلّمك أو والداك بإجاباتك. صدقك يحسّن دقّة التقييم.', 'fr'=>'Ces questions sont strictement confidentielles, vos parents/enseignants n\'y ont pas accès. Votre franchise améliore la précision.', 'en'=>'These questions are strictly confidential — parents/teachers have no access. Your honesty improves accuracy.'],

'psv_smoke_status'   => ['ar'=>'هل تدخّن أحد ما يلي حالياً؟', 'fr'=>'Consommez-vous actuellement l\'un des produits suivants ?', 'en'=>'Do you currently use any of the following?'],
'psv_smoke_none'     => ['ar'=>'لا شيء', 'fr'=>'Aucun', 'en'=>'None'],
'psv_smoke_cig'      => ['ar'=>'سجائر', 'fr'=>'Cigarettes', 'en'=>'Cigarettes'],
'psv_smoke_shisha'   => ['ar'=>'شيشة (نرجيلة)', 'fr'=>'Chicha (narguilé)', 'en'=>'Shisha (hookah)'],
'psv_smoke_vape'     => ['ar'=>'فيب / سيجارة إلكترونية', 'fr'=>'Vape / e-cigarette', 'en'=>'Vape / e-cigarette'],
'psv_smoke_multi'    => ['ar'=>'أكثر من نوع', 'fr'=>'Plusieurs', 'en'=>'More than one'],

'psv_smoke_freq'     => ['ar'=>'تردّد التدخين (إن وُجد)', 'fr'=>'Fréquence (si applicable)', 'en'=>'Frequency (if applicable)'],
'psv_sf_never'       => ['ar'=>'لا ينطبق', 'fr'=>'N/A', 'en'=>'N/A'],
'psv_sf_tried'       => ['ar'=>'جرّبت فقط', 'fr'=>'Juste essayé', 'en'=>'Just tried'],
'psv_sf_occasion'    => ['ar'=>'مناسبات نادرة', 'fr'=>'Occasions rares', 'en'=>'Rare occasions'],
'psv_sf_weekly'      => ['ar'=>'كلّ أسبوع', 'fr'=>'Chaque semaine', 'en'=>'Weekly'],
'psv_sf_daily'       => ['ar'=>'يومياً', 'fr'=>'Quotidien', 'en'=>'Daily'],

'psv_smoke_around'   => ['ar'=>'هل يدخّن أحد في منزلك بانتظام (التدخين السلبي)؟', 'fr'=>'Quelqu\'un fume-t-il régulièrement chez vous (tabagisme passif) ?', 'en'=>'Does anyone smoke regularly at home (passive smoking)?'],

// ════════════ E. SOCIAL JETLAG + TEMPS ASSIS ════════════
'psv_jetlag_title'   => ['ar'=>'النوم في عطلة نهاية الأسبوع', 'fr'=>'Sommeil le week-end', 'en'=>'Weekend sleep'],
'psv_jetlag_note'    => ['ar'=>'الفرق الكبير بين نوم أيام الدراسة والعطلة يخلّ بإيقاع الجسم البيولوجي ويزيد خطر السمنة (Roenneberg).', 'fr'=>'Un grand décalage sommeil semaine/week-end perturbe l\'horloge biologique et augmente le risque d\'obésité (Roenneberg).', 'en'=>'A large weekday-to-weekend sleep shift disrupts the body clock and raises obesity risk (Roenneberg).'],
'psv_bedtime_we'     => ['ar'=>'وقت النوم في عطلة نهاية الأسبوع', 'fr'=>'Heure du coucher le week-end', 'en'=>'Weekend bedtime'],
'psv_waketime_we'    => ['ar'=>'وقت الاستيقاظ في عطلة نهاية الأسبوع', 'fr'=>'Heure du réveil le week-end', 'en'=>'Weekend wake time'],

'psv_sitting_title'  => ['ar'=>'وقت الجلوس خارج الشاشة', 'fr'=>'Temps assis hors écran', 'en'=>'Sitting time outside screens'],
'psv_sitting_note'   => ['ar'=>'الجلوس الطويل خارج الشاشة (مذاكرة، مواصلات، دروس) يزيد خطر السمنة بشكل مستقلّ.', 'fr'=>'Rester assis longtemps hors écran (cours, devoirs, transport) augmente le risque d\'obésité indépendamment.', 'en'=>'Prolonged sitting outside screens (class, study, transport) raises obesity risk independently.'],
'psv_study_hours'    => ['ar'=>'ساعات الدراسة/المذاكرة/الدروس الخصوصية يومياً (دون شاشة)', 'fr'=>'Heures d\'étude / cours particuliers par jour (hors écran)', 'en'=>'Hours of study / tutoring per day (no screen)'],
'psv_transport_min'  => ['ar'=>'مجموع وقت المواصلات يومياً (ذهاب وعودة)', 'fr'=>'Temps total de transport quotidien (aller-retour)', 'en'=>'Total daily commute time (round-trip)'],
'psv_sit_lt2'        => ['ar'=>'< 2 ساعة', 'fr'=>'< 2 h', 'en'=>'< 2 h'],
'psv_sit_2_4'        => ['ar'=>'2 - 4 ساعة', 'fr'=>'2 - 4 h', 'en'=>'2 - 4 h'],
'psv_sit_4_6'        => ['ar'=>'4 - 6 ساعة', 'fr'=>'4 - 6 h', 'en'=>'4 - 6 h'],
'psv_sit_6_8'        => ['ar'=>'6 - 8 ساعة', 'fr'=>'6 - 8 h', 'en'=>'6 - 8 h'],
'psv_sit_gt8'        => ['ar'=>'> 8 ساعة', 'fr'=>'> 8 h', 'en'=>'> 8 h'],
'psv_t_lt15'         => ['ar'=>'< 15 دقيقة', 'fr'=>'< 15 min', 'en'=>'< 15 min'],
'psv_t_15_30'        => ['ar'=>'15 - 30 د', 'fr'=>'15 - 30 min', 'en'=>'15 - 30 min'],
'psv_t_30_60'        => ['ar'=>'30 - 60 د', 'fr'=>'30 - 60 min', 'en'=>'30 - 60 min'],
'psv_t_1_2h'         => ['ar'=>'1 - 2 ساعة', 'fr'=>'1 - 2 h', 'en'=>'1 - 2 h'],
'psv_t_gt2h'         => ['ar'=>'> 2 ساعة', 'fr'=>'> 2 h', 'en'=>'> 2 h'],

// ════════════ F. ANTIBIOTIQUES EN BAS ÂGE ════════════
'psv_abx_title'      => ['ar'=>'المضادّات الحيويّة في الطفولة', 'fr'=>'Antibiotiques dans l\'enfance', 'en'=>'Antibiotics in childhood'],
'psv_abx_note'       => ['ar'=>'الاستعمال المتكرّر للمضادّات الحيوية قبل سن 2 يضرّ الميكروبيوم المعوي ويُربط بزيادة خطر السمنة (Trasande 2013).', 'fr'=>'L\'usage répété d\'antibiotiques avant 2 ans altère le microbiote intestinal et est lié à un risque accru d\'obésité (Trasande 2013).', 'en'=>'Repeated antibiotic use before age 2 disrupts gut microbiome and links to increased obesity risk (Trasande 2013).'],
'psv_abx_under2'     => ['ar'=>'هل تذكر والدتك إعطاءك مضادات حيوية متعدّدة قبل سن 2؟', 'fr'=>'Votre mère se souvient-elle de plusieurs antibiotiques avant vos 2 ans ?', 'en'=>'Does your mother remember multiple antibiotic courses before age 2?'],
'psv_abx_none'       => ['ar'=>'لم أتناول أو مرّة واحدة فقط', 'fr'=>'Aucun ou une seule fois', 'en'=>'None or just once'],
'psv_abx_few'        => ['ar'=>'2 - 3 مرّات', 'fr'=>'2 - 3 fois', 'en'=>'2 - 3 times'],
'psv_abx_many'       => ['ar'=>'4 مرّات أو أكثر', 'fr'=>'4 fois ou plus', 'en'=>'4 times or more'],
'psv_abx_dontknow'   => ['ar'=>'لا أعرف', 'fr'=>'Je ne sais pas', 'en'=>'Don\'t know'],

'psv_abx_recent'     => ['ar'=>'في آخر سنتين: كم مرّة تناولت مضادّاً حيوياً؟', 'fr'=>'Ces 2 dernières années : combien d\'antibiotiques avez-vous pris ?', 'en'=>'Past 2 years: how many antibiotic courses?'],
'psv_abx_0_1'        => ['ar'=>'0 - 1', 'fr'=>'0 - 1', 'en'=>'0 - 1'],
'psv_abx_2_3'        => ['ar'=>'2 - 3', 'fr'=>'2 - 3', 'en'=>'2 - 3'],
'psv_abx_gt3'        => ['ar'=>'4 أو أكثر', 'fr'=>'4 ou plus', 'en'=>'4 or more'],

// ════════════ Nouveaux indicateurs (pour rapport) ════════════
'psv_ind_ses'        => ['ar'=>'الوضع الاجتماعي-الاقتصادي', 'fr'=>'Statut socio-économique', 'en'=>'Socioeconomic status'],
'psv_ind_puberty'    => ['ar'=>'توقيت البلوغ', 'fr'=>'Timing pubertaire', 'en'=>'Pubertal timing'],
'psv_ind_dzdiet'     => ['ar'=>'النمط الغذائي الجزائري', 'fr'=>'Profil alimentaire algérien', 'en'=>'Algerian dietary profile'],
'psv_ind_smoking'    => ['ar'=>'التدخين', 'fr'=>'Tabac', 'en'=>'Smoking'],
'psv_ind_jetlag'     => ['ar'=>'الإيقاع الزمني الاجتماعي', 'fr'=>'Décalage chrono social', 'en'=>'Social jetlag'],
'psv_ind_sitting'    => ['ar'=>'الجلوس خارج الشاشة', 'fr'=>'Sédentarité hors écran', 'en'=>'Non-screen sitting'],
'psv_ind_abx'        => ['ar'=>'تأثير المضادّات الحيوية', 'fr'=>'Impact des antibiotiques', 'en'=>'Antibiotic impact'],

];

// ────────────────────────────────────────────────────────────────────
// Merge all sections into the global $t array used by __().
// ────────────────────────────────────────────────────────────────────
foreach (array_merge($ps, $psx, $psv3) as $k => $v) {
    $t[$k] = $v;
}
