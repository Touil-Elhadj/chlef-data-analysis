<?php
/**
 * ════════════════════════════════════════════════════════════════════
 *  personal-survey/advice.php
 *  Bibliothèque de conseils personnalisés multilingues.
 *
 *  Chaque fonction prend les scores calculés et retourne un tableau
 *  ['title' => str, 'level' => 'success|warning|danger|info',
 *   'text' => str, 'bullets' => [str, ...]]
 *  pour l'affichage dans le rapport visiteur.
 *
 *  Toutes les fonctions sont language-aware via le paramètre $lang.
 * ════════════════════════════════════════════════════════════════════
 */

if (!function_exists('psPick')) {
/** Sélectionne la traduction selon $lang, fallback français/anglais. */
function psPick($arr, $lang) {
    return $arr[$lang] ?? $arr['fr'] ?? $arr['en'] ?? '';
}
}

// ═════════════════════════════════════════════════════════════════════
//  CONSEIL 1 — IMC / IOTF (poids)
// ═════════════════════════════════════════════════════════════════════
function adviceBMI($iotf, $bmi, $age, $lang) {
    $T = [
        'sec_title' => ['ar'=>'وزنك وتصنيف IMC', 'fr'=>'Votre poids et IMC', 'en'=>'Your weight and BMI'],

        // ── Minceur grade 2 / 3 ──────────────────────────────────
        'thin3_t' => ['ar'=>'نحافة شديدة — راجع طبيباً قريباً', 'fr'=>'Maigreur sévère — consultez rapidement un médecin', 'en'=>'Severe thinness — see a doctor soon'],
        'thin3_x' => ['ar'=>'مؤشر كتلة جسمك منخفض جداً مما قد يشير إلى نقص في الطاقة أو مشكلة صحية أساسية. الأولوية هي الفحص الطبي.', 'fr'=>"Votre IMC est très bas, ce qui peut indiquer un déficit énergétique ou un problème de santé sous-jacent. La priorité est un examen médical.", 'en'=>'Your BMI is very low, which can signal energy deficit or an underlying health issue. The priority is a medical check-up.'],
        'thin3_b1' => ['ar'=>'احجز موعداً مع طبيب عام لإجراء فحوصات أساسية (تعداد دم، حديد، فيتامين D، الغدة الدرقية).', 'fr'=>"Prenez rendez-vous chez un médecin généraliste pour des analyses de base (NFS, fer, vit. D, thyroïde).", 'en'=>'Book an appointment with a GP for basic tests (CBC, iron, vit. D, thyroid).'],
        'thin3_b2' => ['ar'=>'لا تحاول زيادة الوزن بالحلويات والوجبات السريعة — استشر أخصائي تغذية لخطة متوازنة عالية الكثافة الغذائية.', 'fr'=>"N'essayez pas de prendre du poids avec des sucreries et du fast-food — consultez un nutritionniste pour un plan dense et équilibré.", 'en'=>'Do not try to gain weight with sweets and fast food — see a nutritionist for a dense, balanced plan.'],
        'thin3_b3' => ['ar'=>'تناول 3 وجبات + 2-3 وجبات خفيفة، وأضف مكسرات وأفوكادو وزيت زيتون وبيضاً وبقوليات.', 'fr'=>"Prenez 3 repas + 2-3 collations, et ajoutez noix, avocat, huile d'olive, œufs et légumineuses.", 'en'=>'Eat 3 meals + 2-3 snacks, and add nuts, avocado, olive oil, eggs and legumes.'],

        // ── Minceur grade 1 ──────────────────────────────────────
        'thin_t' => ['ar'=>'نحافة معتدلة — تحتاج إلى تعزيز التغذية', 'fr'=>'Maigreur modérée — la nutrition est à renforcer', 'en'=>'Mild thinness — nutrition needs reinforcing'],
        'thin_x' => ['ar'=>'وزنك أقل قليلاً من الطبيعي. ليس بالضرورة خطيراً، لكن يستحسن الانتباه إلى جودة وكمية ما تتناوله.', 'fr'=>"Votre poids est un peu sous la normale. Pas forcément dangereux, mais à surveiller, en quantité et qualité.", 'en'=>'Your weight is a bit below normal. Not necessarily dangerous, but worth watching in both quantity and quality.'],
        'thin_b1' => ['ar'=>'ركّز على وجبات منتظمة (3 رئيسية + 1-2 وجبة خفيفة) ولا تتخطّ أبداً وجبة الإفطار.', 'fr'=>"Privilégiez des repas réguliers (3 principaux + 1-2 collations) et ne sautez jamais le petit-déjeuner.", 'en'=>'Aim for regular meals (3 main + 1-2 snacks) and never skip breakfast.'],
        'thin_b2' => ['ar'=>'أضف مصادر بروتين عالية الجودة في كل وجبة: بيض، دجاج، سمك، عدس، حمص، زبادي، جبن.', 'fr'=>"Ajoutez une source de protéines de qualité à chaque repas : œufs, poulet, poisson, lentilles, pois chiches, yaourt, fromage.", 'en'=>'Add a high-quality protein source at each meal: eggs, chicken, fish, lentils, chickpeas, yogurt, cheese.'],
        'thin_b3' => ['ar'=>'مارس تمارين القوة (مرنين-3 مرات أسبوعياً) لبناء الكتلة العضلية بدلاً من الدهون فقط.', 'fr'=>"Ajoutez du renforcement musculaire (2-3 fois/sem) pour gagner du muscle plutôt que de la masse grasse.", 'en'=>'Add strength training (2-3 sessions/week) to gain muscle, not just fat.'],

        // ── Normal ──────────────────────────────────────────────
        'normal_t' => ['ar'=>'وزنك في النطاق الصحي ✓', 'fr'=>'Votre poids est dans la zone saine ✓', 'en'=>'Your weight is in the healthy range ✓'],
        'normal_x' => ['ar'=>'IMC الخاص بك في النطاق الطبيعي حسب IOTF. تابع ما تفعله، وركّز على الجودة لا الكمية.', 'fr'=>"Votre IMC est dans la plage normale selon IOTF. Continuez sur cette voie, en privilégiant la qualité.", 'en'=>'Your BMI is in the normal range per IOTF. Keep going, focus on quality over quantity.'],
        'normal_b1' => ['ar'=>'حافظ على 5 أيام نشاط معتدل (≥30 دقيقة) أسبوعياً + يومين تمارين قوة.', 'fr'=>"Maintenez 5 jours d'activité modérée (≥30 min) par semaine + 2 jours de renforcement.", 'en'=>'Keep 5 days of moderate activity (≥30 min)/week + 2 strength sessions.'],
        'normal_b2' => ['ar'=>'حافظ على تنوع غذائي: 5 حصص فواكه/خضار يومياً، بقوليات 2 مرات/أسبوع، سمك 2 مرات/أسبوع.', 'fr'=>"Conservez la diversité : 5 portions fruits/légumes/jour, légumineuses 2×/sem, poisson 2×/sem.", 'en'=>'Maintain diversity: 5 portions fruit/vegetables/day, legumes 2×/wk, fish 2×/wk.'],
        'normal_b3' => ['ar'=>'الوقاية أسهل من العلاج: راقب وزنك مرة شهرياً، وانتبه إلى ساعات الشاشة والنوم.', 'fr'=>"La prévention est plus simple que le traitement : surveillez votre poids 1×/mois, et veillez aux écrans et au sommeil.", 'en'=>'Prevention is easier than cure: weigh yourself monthly, and mind screens and sleep.'],

        // ── Surpoids ─────────────────────────────────────────────
        'ow_t' => ['ar'=>'وزن زائد — تدخّل تدريجي ممكن وفعّال', 'fr'=>"Surpoids — une intervention progressive est possible et efficace", 'en'=>'Overweight — gradual changes are possible and effective'],
        'ow_x' => ['ar'=>'IMC الخاص بك أعلى من الطبيعي. لا داعي للقلق إذا تصرفت الآن: تخفيض 5-10٪ من الوزن يحسّن الصحة جذرياً.', 'fr'=>"Votre IMC dépasse la normale. Pas d'alarme si vous agissez maintenant : perdre 5-10 % du poids améliore radicalement la santé.", 'en'=>'Your BMI is above normal. No need to worry if you act now: losing 5-10% of body weight markedly improves health.'],
        'ow_b1' => ['ar'=>'ابدأ بهدف صغير: أنقص المشروبات السكرية والشيبس. هذا وحده يحدث فرقاً.', 'fr'=>"Commencez petit : éliminez les boissons sucrées et chips. Rien que ça fait la différence.", 'en'=>'Start small: cut sugary drinks and chips. That alone makes a difference.'],
        'ow_b2' => ['ar'=>'زد النشاط البدني تدريجياً: ابدأ بـ 30 دقيقة مشي يومياً، ثم زِد إلى 60 دقيقة.', 'fr'=>"Augmentez l'activité progressivement : 30 min de marche/jour, puis 60 min.", 'en'=>'Increase activity gradually: 30 min walking/day, then 60 min.'],
        'ow_b3' => ['ar'=>'تجنّب الحميات القاسية — قد تؤدي إلى استعادة الوزن أو اضطرابات الأكل.', 'fr'=>"Évitez les régimes restrictifs — risque de reprise et de troubles du comportement alimentaire.", 'en'=>'Avoid strict diets — they often cause weight regain or eating disorders.'],
        'ow_b4' => ['ar'=>'استشر أخصائي تغذية لخطة شخصية. الهدف: 0.5 كغ/أسبوع، ليس أكثر.', 'fr'=>"Consultez un nutritionniste pour un plan personnalisé. Objectif : 0,5 kg/sem, pas plus.", 'en'=>'See a nutritionist for a personalized plan. Aim: 0.5 kg/week, no more.'],

        // ── Obésité ──────────────────────────────────────────────
        'ob_t' => ['ar'=>'سمنة — التدخل الطبي مهم', 'fr'=>'Obésité — un accompagnement médical est important', 'en'=>'Obesity — medical follow-up is important'],
        'ob_x' => ['ar'=>'IMC الخاص بك ضمن نطاق السمنة وفقاً لتصنيف IOTF. الإجراء العلاجي المبكر ضروري لتفادي مضاعفات (سكري، ضغط دم، أمراض القلب).', 'fr'=>"Votre IMC est dans la zone d'obésité selon IOTF. Une prise en charge précoce est nécessaire pour éviter les complications (diabète, hypertension, maladies cardiovasculaires).", 'en'=>"Your BMI falls in the obesity range per IOTF. Early intervention is essential to prevent complications (diabetes, hypertension, cardiovascular disease)."],
        'ob_b1' => ['ar'=>'احجز موعداً مع طبيبك العام في الأيام القادمة — هذه ليست أزمة، بل خطوة عادية.', 'fr'=>"Prenez rendez-vous chez votre médecin traitant dans les prochains jours — ce n'est pas une urgence, c'est une démarche normale.", 'en'=>"Book an appointment with your GP in the next few days — not an emergency, just a normal step."],
        'ob_b2' => ['ar'=>'فريق مثالي: طبيب + أخصائي تغذية + مدرّب نشاط بدني. تابع وزنك كل 2-4 أسابيع.', 'fr'=>"Équipe idéale : médecin + nutritionniste + coach activité physique. Pesez-vous toutes les 2-4 semaines.", 'en'=>"Ideal team: GP + nutritionist + physical activity coach. Weigh yourself every 2-4 weeks."],
        'ob_b3' => ['ar'=>'إنقاص 5٪ من الوزن (مثال: 4 كغ من 80 كغ) يخفض خطر السكري بنسبة 50٪!', 'fr'=>"Perdre 5 % du poids (ex : 4 kg pour 80 kg) réduit de 50 % le risque de diabète !", 'en'=>"Losing 5% of body weight (e.g. 4 kg out of 80) cuts diabetes risk by 50%!"],
        'ob_b4' => ['ar'=>'الدعم النفسي مهم: العزلة والوصم يفاقمان المشكلة. تحدّث مع أحد تثق به.', 'fr'=>"Le soutien psychologique compte : l'isolement et la stigmatisation aggravent le problème. Parlez à quelqu'un de confiance.", 'en'=>"Psychological support matters: isolation and stigma worsen the problem. Talk to someone you trust."],
    ];

    // ── Map iotf → titre/texte/bullets ─────────────────────────
    if ($iotf === 'Obésité') {
        return [
            'level' => 'danger',
            'title' => psPick($T['ob_t'], $lang),
            'text'  => psPick($T['ob_x'], $lang),
            'bullets' => [
                psPick($T['ob_b1'], $lang),
                psPick($T['ob_b2'], $lang),
                psPick($T['ob_b3'], $lang),
                psPick($T['ob_b4'], $lang),
            ],
        ];
    }
    if ($iotf === 'Surpoids') {
        return [
            'level' => 'warning',
            'title' => psPick($T['ow_t'], $lang),
            'text'  => psPick($T['ow_x'], $lang),
            'bullets' => [
                psPick($T['ow_b1'], $lang),
                psPick($T['ow_b2'], $lang),
                psPick($T['ow_b3'], $lang),
                psPick($T['ow_b4'], $lang),
            ],
        ];
    }
    if (in_array($iotf, ['Minceur grade 2','Minceur grade 3'])) {
        return [
            'level' => 'danger',
            'title' => psPick($T['thin3_t'], $lang),
            'text'  => psPick($T['thin3_x'], $lang),
            'bullets' => [
                psPick($T['thin3_b1'], $lang),
                psPick($T['thin3_b2'], $lang),
                psPick($T['thin3_b3'], $lang),
            ],
        ];
    }
    if ($iotf === 'Minceur') {
        return [
            'level' => 'warning',
            'title' => psPick($T['thin_t'], $lang),
            'text'  => psPick($T['thin_x'], $lang),
            'bullets' => [
                psPick($T['thin_b1'], $lang),
                psPick($T['thin_b2'], $lang),
                psPick($T['thin_b3'], $lang),
            ],
        ];
    }
    // Normal
    return [
        'level' => 'success',
        'title' => psPick($T['normal_t'], $lang),
        'text'  => psPick($T['normal_x'], $lang),
        'bullets' => [
            psPick($T['normal_b1'], $lang),
            psPick($T['normal_b2'], $lang),
            psPick($T['normal_b3'], $lang),
        ],
    ];
}

// ═════════════════════════════════════════════════════════════════════
//  CONSEIL 2 — KIDMED (régime méditerranéen)
// ═════════════════════════════════════════════════════════════════════
function adviceKIDMED($classe, $score, $lang) {
    if ($classe === 'Optimal') {
        return [
            'level' => 'success',
            'title' => psPick(['ar'=>'نظامك الغذائي ممتاز ⭐', 'fr'=>'Votre alimentation est optimale ⭐', 'en'=>'Your diet is optimal ⭐'], $lang),
            'text'  => psPick(['ar'=>'تتبع نمطاً غذائياً متوسطياً نموذجياً. هذا أحد أفضل الأنماط الغذائية في العالم — استمر!', 'fr'=>"Vous suivez un modèle méditerranéen exemplaire — l'un des meilleurs au monde. Continuez !", 'en'=>'You follow a model Mediterranean pattern — one of the best in the world. Keep it up!'], $lang),
            'bullets' => [
                psPick(['ar'=>'كن سفيراً: شارك عاداتك مع عائلتك وأصدقائك.', 'fr'=>"Soyez ambassadeur : partagez vos habitudes avec votre famille et vos amis.", 'en'=>'Be an ambassador: share your habits with family and friends.'], $lang),
                psPick(['ar'=>'تنوّع المصادر: بدّل بين السمك والدجاج والبقوليات، وجرّب فواكه موسمية.', 'fr'=>"Variez les sources : alternez poisson, volaille et légumineuses, essayez des fruits de saison.", 'en'=>'Vary sources: alternate fish, poultry and legumes, try seasonal fruits.'], $lang),
            ],
        ];
    }
    if ($classe === 'Amélioration nécessaire') {
        return [
            'level' => 'warning',
            'title' => psPick(['ar'=>'نظامك جيد ولكنه يحتاج تحسيناً', 'fr'=>'Votre alimentation est correcte mais perfectible', 'en'=>'Your diet is decent but can be improved'], $lang),
            'text'  => psPick(['ar'=>'تتبع جوانب من النظام المتوسطي لكن هناك نقاط ضعف. التحسينات الصغيرة تحدث فرقاً كبيراً.', 'fr'=>"Vous suivez partiellement le modèle méditerranéen, mais des points faibles subsistent. De petites améliorations comptent beaucoup.", 'en'=>'You partially follow a Mediterranean pattern but have weak spots. Small improvements matter a lot.'], $lang),
            'bullets' => [
                psPick(['ar'=>'أضف فاكهة واحدة على الإفطار وأخرى كوجبة خفيفة بعد الظهر.', 'fr'=>"Ajoutez un fruit au petit-déjeuner et un autre en collation l'après-midi.", 'en'=>'Add one fruit at breakfast and one as an afternoon snack.'], $lang),
                psPick(['ar'=>'استبدل المشروبات السكرية بالماء وعصير الليمون الطازج.', 'fr'=>"Remplacez les boissons sucrées par de l'eau et du citron frais.", 'en'=>'Replace sugary drinks with water and fresh lemon.'], $lang),
                psPick(['ar'=>'تناول بقوليات (عدس، حمص، فول) مرتين أسبوعياً على الأقل.', 'fr'=>"Mangez des légumineuses (lentilles, pois chiches, fèves) au moins 2 fois/semaine.", 'en'=>'Eat legumes (lentils, chickpeas, beans) at least 2×/week.'], $lang),
                psPick(['ar'=>'استخدم زيت الزيتون بدلاً من الزبدة والمارغرين كلما أمكن.', 'fr'=>"Privilégiez l'huile d'olive plutôt que beurre et margarine.", 'en'=>'Prefer olive oil over butter and margarine when possible.'], $lang),
            ],
        ];
    }
    // Mauvais
    return [
        'level' => 'danger',
        'title' => psPick(['ar'=>'نظامك الغذائي يحتاج إلى تغيير جوهري', 'fr'=>"Votre alimentation nécessite un changement profond", 'en'=>'Your diet needs significant change'], $lang),
        'text'  => psPick(['ar'=>'مؤشر KIDMED لديك منخفض. هذا قد يزيد من خطر زيادة الوزن ونقص الفيتامينات وفقر الدم على المدى البعيد.', 'fr'=>"Votre score KIDMED est bas. Cela augmente le risque à long terme de surpoids, carences vitaminiques et anémie.", 'en'=>'Your KIDMED score is low. This raises the long-term risk of overweight, vitamin deficiencies and anaemia.'], $lang),
        'bullets' => [
            psPick(['ar'=>'الخطوة 1: ابدأ بإضافة فاكهة واحدة وحصة خضار يومياً. ابدأ صغيراً!', 'fr'=>"Étape 1 : ajoutez un fruit et une portion de légumes par jour. Petit à petit !", 'en'=>'Step 1: add one fruit and one vegetable serving per day. Start small!'], $lang),
            psPick(['ar'=>'الخطوة 2: استبدل المشروبات السكرية والشيبس بالماء والمكسرات غير المملحة.', 'fr'=>"Étape 2 : remplacez sodas et chips par eau et fruits à coque non salés.", 'en'=>'Step 2: replace sodas and chips with water and unsalted nuts.'], $lang),
            psPick(['ar'=>'الخطوة 3: أضف سمكاً (سردين، تونة) مرتين أسبوعياً، وبقوليات مرتين.', 'fr'=>"Étape 3 : ajoutez du poisson (sardine, thon) 2×/sem et légumineuses 2×/sem.", 'en'=>'Step 3: add fish (sardine, tuna) 2×/wk and legumes 2×/wk.'], $lang),
            psPick(['ar'=>'الخطوة 4: لا تتخطّ الإفطار — حتى لو كان كوب حليب + قطعة خبز + موزة.', 'fr'=>"Étape 4 : ne sautez pas le petit-déjeuner — même simple (lait + pain + banane).", 'en'=>'Step 4: do not skip breakfast — even simple (milk + bread + banana).'], $lang),
            psPick(['ar'=>'فكر باستشارة أخصائي تغذية لخطة مخصصة. التغيير لا يحدث وحده.', 'fr'=>"Envisagez un nutritionniste pour un plan personnalisé. Le changement ne se fait pas seul.", 'en'=>'Consider seeing a nutritionist for a personalized plan. Change does not happen alone.'], $lang),
        ],
    ];
}

// ═════════════════════════════════════════════════════════════════════
//  CONSEIL 3 — Sommeil
// ═════════════════════════════════════════════════════════════════════
function adviceSleep($classe, $score, $lang) {
    if ($classe === 'Bon') {
        return [
            'level' => 'success',
            'title' => psPick(['ar'=>'نومك جيد ✓', 'fr'=>'Votre sommeil est bon ✓', 'en'=>'Your sleep is good ✓'], $lang),
            'text'  => psPick(['ar'=>'النوم الجيد أساس الصحة الجسدية والعقلية. استمر!', 'fr'=>"Un bon sommeil est la base de la santé physique et mentale. Continuez !", 'en'=>'Good sleep underpins physical and mental health. Keep it up!'], $lang),
            'bullets' => [
                psPick(['ar'=>'حافظ على نفس مواعيد النوم والاستيقاظ حتى في عطلة الأسبوع.', 'fr'=>"Conservez les mêmes horaires de sommeil, même le week-end.", 'en'=>'Keep the same sleep/wake times, even on weekends.'], $lang),
            ],
        ];
    }
    if ($classe === 'Perturbé') {
        return [
            'level' => 'warning',
            'title' => psPick(['ar'=>'نومك مضطرب نوعاً ما', 'fr'=>"Votre sommeil est un peu perturbé", 'en'=>'Your sleep is somewhat disturbed'], $lang),
            'text'  => psPick(['ar'=>'هناك علامات على نوم غير كافٍ أو متقطع. النوم المتراكم القصير يؤثر على المزاج، التركيز، الشهية، والوزن.', 'fr'=>"Des signes d'un sommeil insuffisant ou fragmenté. La privation chronique nuit à l'humeur, la concentration, l'appétit et le poids.", 'en'=>'Signs of insufficient or fragmented sleep. Chronic deprivation harms mood, focus, appetite and weight.'], $lang),
            'bullets' => [
                psPick(['ar'=>'ضع الهاتف خارج غرفة النوم 30 دقيقة قبل النوم. الضوء الأزرق يثبط الميلاتونين.', 'fr'=>"Sortez le téléphone de la chambre 30 min avant de dormir : la lumière bleue inhibe la mélatonine.", 'en'=>'Keep the phone out of the bedroom 30 min before sleep — blue light suppresses melatonin.'], $lang),
                psPick(['ar'=>'تجنب الكافيين (قهوة، شاي، مشروبات الطاقة) بعد الساعة 4 مساءً.', 'fr'=>"Évitez caféine (café, thé, energy drinks) après 16 h.", 'en'=>'Avoid caffeine (coffee, tea, energy drinks) after 4 PM.'], $lang),
                psPick(['ar'=>'استهدف 8-9 ساعات للمراهقين، 7-9 للكبار. إذا استيقظت متعباً، فأنت لم تنم كفاية.', 'fr'=>"Visez 8-9 h chez l'adolescent, 7-9 h chez l'adulte. Réveil fatigué = sommeil insuffisant.", 'en'=>'Aim for 8-9 h for teens, 7-9 h for adults. Waking tired = not enough sleep.'], $lang),
                psPick(['ar'=>'غرفة مظلمة، باردة (18-20°)، وهادئة. لا تشاهد التلفاز في السرير.', 'fr'=>"Chambre sombre, fraîche (18-20°) et silencieuse. Pas de TV au lit.", 'en'=>'Dark, cool (18-20°C), quiet bedroom. No TV in bed.'], $lang),
            ],
        ];
    }
    // Mauvais
    return [
        'level' => 'danger',
        'title' => psPick(['ar'=>'نومك سيء — تدخل سريع مطلوب', 'fr'=>"Votre sommeil est mauvais — une action rapide est nécessaire", 'en'=>'Your sleep is poor — quick action is needed'], $lang),
        'text'  => psPick(['ar'=>'النوم القصير المزمن (<6 ساعات) يضاعف خطر السمنة، السكري، الاكتئاب، وضعف التحصيل الدراسي. هذا تنبيه جدي.', 'fr'=>"Un sommeil court chronique (<6 h) double le risque d'obésité, diabète, dépression et baisse scolaire. C'est un signal sérieux.", 'en'=>'Chronic short sleep (<6 h) doubles obesity, diabetes, depression and academic-decline risk. This is a serious signal.'], $lang),
        'bullets' => [
            psPick(['ar'=>'ابدأ هذه الليلة: نم قبل ساعة من المعتاد، وضع الهاتف بعيداً.', 'fr'=>"Commencez ce soir : couchez-vous 1 h plus tôt et éloignez le téléphone.", 'en'=>'Start tonight: sleep 1 hour earlier and put the phone away.'], $lang),
            psPick(['ar'=>'لا قيلولة بعد الساعة 3 عصراً، ولا قيلولة أطول من 30 دقيقة.', 'fr'=>"Pas de sieste après 15 h, ni de sieste >30 min.", 'en'=>'No nap after 3 PM, no nap longer than 30 min.'], $lang),
            psPick(['ar'=>'إذا استمرت المشكلة بعد أسبوعين من تطبيق نظافة النوم → استشر طبيباً (قد تكون أرقاً، توتراً، أو مشكلة طبية).', 'fr'=>"Si le problème persiste après 2 semaines de bonne hygiène de sommeil → consultez (insomnie, anxiété, ou autre).", 'en'=>'If unchanged after 2 weeks of sleep hygiene → see a doctor (insomnia, anxiety, or other).'], $lang),
            psPick(['ar'=>'تجنّب الوجبات الثقيلة في الـ 3 ساعات قبل النوم، وخاصة الدهون والسكريات.', 'fr'=>"Évitez les repas lourds dans les 3 h avant de dormir, surtout gras et sucrés.", 'en'=>'Avoid heavy meals in the 3 h before sleep, especially fatty and sugary.'], $lang),
        ],
    ];
}

// ═════════════════════════════════════════════════════════════════════
//  CONSEIL 4 — Activité physique
// ═════════════════════════════════════════════════════════════════════
function adviceActivity($classe, $score, $lang) {
    if (in_array($classe, ['Actif','Très actif'])) {
        return [
            'level' => 'success',
            'title' => psPick(['ar'=>'مستوى نشاطك ممتاز ⚡', 'fr'=>"Votre niveau d'activité est excellent ⚡", 'en'=>'Your activity level is excellent ⚡'], $lang),
            'text'  => psPick(['ar'=>'تمارس نشاطاً منتظماً — استمر، فهذا يحميك من معظم الأمراض المزمنة!', 'fr'=>"Vous bougez régulièrement — continuez, c'est la meilleure protection contre la plupart des maladies chroniques !", 'en'=>'You move regularly — keep it up, it is the best protection against most chronic diseases!'], $lang),
            'bullets' => [
                psPick(['ar'=>'تجنب الإفراط في التدريب: يومان للراحة في الأسبوع، نم 8 ساعات.', 'fr'=>"Évitez le surentraînement : 2 jours de repos/semaine, 8 h de sommeil.", 'en'=>'Avoid overtraining: 2 rest days/week, 8 hours of sleep.'], $lang),
                psPick(['ar'=>'تنوّع بين القلب (الجري، السباحة) والقوة (الأوزان، تمارين الجسم).', 'fr'=>"Variez : cardio (course, natation) + renforcement (poids, exercices au poids du corps).", 'en'=>'Vary: cardio (running, swimming) + strength (weights, bodyweight exercises).'], $lang),
            ],
        ];
    }
    if ($classe === 'Peu actif') {
        return [
            'level' => 'warning',
            'title' => psPick(['ar'=>'نشاطك البدني محدود — حسّنه تدريجياً', 'fr'=>"Votre activité physique est limitée — augmentez-la progressivement", 'en'=>'Your physical activity is limited — increase it gradually'], $lang),
            'text'  => psPick(['ar'=>'تتحرك قليلاً، لكن لا تصل إلى التوصيات الصحية (60 دقيقة/يوم للمراهقين، 30 دقيقة/يوم للكبار).', 'fr'=>"Vous bougez un peu, mais sans atteindre les recommandations (60 min/jour ado, 30 min/jour adulte).", 'en'=>'You move a bit, but not enough (recommended: 60 min/day teens, 30 min/day adults).'], $lang),
            'bullets' => [
                psPick(['ar'=>'ابدأ بـ 20 دقيقة مشي سريع يومياً. ابني الإيقاع تدريجياً.', 'fr'=>"Commencez par 20 min de marche rapide/jour. Augmentez progressivement.", 'en'=>'Start with 20 min of brisk walking/day. Build up gradually.'], $lang),
                psPick(['ar'=>'استخدم السلالم بدل المصعد، وامشِ مسافة قصيرة عوضاً عن السيارة.', 'fr'=>"Préférez les escaliers à l'ascenseur, et la marche à la voiture pour les courtes distances.", 'en'=>'Use stairs instead of the lift, walk instead of driving for short distances.'], $lang),
                psPick(['ar'=>'اختر رياضة ممتعة: كرة قدم، رقص، سباحة، حتى لو مرة في الأسبوع للبداية.', 'fr'=>"Choisissez un sport plaisant : foot, danse, natation — même 1×/semaine pour commencer.", 'en'=>'Pick an enjoyable sport: football, dance, swimming — even 1×/week to start.'], $lang),
            ],
        ];
    }
    // Inactif
    return [
        'level' => 'danger',
        'title' => psPick(['ar'=>'أنت غير نشط — هذا أحد أكبر عوامل خطر السمنة', 'fr'=>"Vous êtes inactif — c'est l'un des plus grands facteurs de risque d'obésité", 'en'=>'You are inactive — one of the biggest obesity risk factors'], $lang),
        'text'  => psPick(['ar'=>'الخمول البدني يضاعف خطر السمنة، السكري، أمراض القلب، والاكتئاب. لكن الأخبار الجيدة: التحسن يبدأ من اليوم الأول.', 'fr'=>"L'inactivité multiplie les risques (obésité, diabète, cardio, dépression). Bonne nouvelle : l'amélioration commence dès le 1er jour.", 'en'=>'Inactivity multiplies risks (obesity, diabetes, cardio, depression). Good news: improvement starts on day one.'], $lang),
        'bullets' => [
            psPick(['ar'=>'هدف الأسبوع الأول: 10 دقائق مشي يومياً. هذا فقط — لا أكثر، لتجعلها عادة.', 'fr'=>"Objectif semaine 1 : 10 min de marche/jour. Juste ça, pour créer l'habitude.", 'en'=>'Week 1 goal: 10 min walking/day. Just that, to build the habit.'], $lang),
            psPick(['ar'=>'الأسبوع الثاني: 15 دقيقة، الثالث: 20 دقيقة، الرابع: 30 دقيقة. تدرّج بطيء وثابت.', 'fr'=>"Semaine 2 : 15 min, semaine 3 : 20 min, semaine 4 : 30 min. Progression lente et constante.", 'en'=>'Week 2: 15 min, week 3: 20 min, week 4: 30 min. Slow, steady progress.'], $lang),
            psPick(['ar'=>'لا حاجة لقاعة رياضية — المشي، صعود الدرج، تمارين بسيطة في المنزل تكفي.', 'fr'=>"Pas besoin de salle — marche, escaliers et exos simples à la maison suffisent.", 'en'=>'No gym needed — walking, stairs and simple home exercises are enough.'], $lang),
            psPick(['ar'=>'ابحث عن شريك (صديق، فرد من العائلة). الالتزام المشترك أسهل بكثير.', 'fr'=>"Trouvez un partenaire (ami, famille). L'engagement partagé est bien plus facile.", 'en'=>'Find a partner (friend, family). Shared commitment is much easier.'], $lang),
        ],
    ];
}

// ═════════════════════════════════════════════════════════════════════
//  CONSEIL 5 — Sédentarité (écrans)
// ═════════════════════════════════════════════════════════════════════
function adviceSedentary($classe, $hours, $lang) {
    if ($classe === 'Actif') {
        return [
            'level' => 'success',
            'title' => psPick(['ar'=>'ساعات الشاشات معقولة ✓', 'fr'=>"Votre temps d'écran est raisonnable ✓", 'en'=>'Your screen time is reasonable ✓'], $lang),
            'text'  => psPick(['ar'=>'تجنّبت فخّ الإفراط في الشاشات. هذا يحمي نومك ومزاجك ونشاطك.', 'fr'=>"Vous évitez le piège des écrans excessifs. Cela protège sommeil, humeur et activité.", 'en'=>'You avoid the over-screen trap. It protects sleep, mood and activity.'], $lang),
            'bullets' => [],
        ];
    }
    if ($classe === 'Modérément sédentaire') {
        return [
            'level' => 'warning',
            'title' => psPick(['ar'=>'الشاشات تشغل وقتاً كبيراً من يومك', 'fr'=>"Les écrans occupent une part importante de votre journée", 'en'=>'Screens take up a large part of your day'], $lang),
            'text'  => psPick(['ar'=>'الجلوس الطويل وقلة الحركة يزيدان من مخاطر السمنة والكسل ومشاكل الظهر.', 'fr'=>"La station assise prolongée et le manque de mouvement augmentent obésité, fatigue et douleurs dorsales.", 'en'=>'Prolonged sitting and lack of movement raise obesity, fatigue and back-pain risks.'], $lang),
            'bullets' => [
                psPick(['ar'=>'قاعدة 20-20-20: كل 20 دقيقة، انظر إلى شيء على بُعد 6م لمدة 20 ثانية، وقف.', 'fr'=>"Règle 20-20-20 : toutes les 20 min, regardez à 6 m pendant 20 s et levez-vous.", 'en'=>'20-20-20 rule: every 20 min, look 6 m away for 20 s and stand up.'], $lang),
                psPick(['ar'=>'حدّد ساعتين كحد أقصى لاستخدام الترفيهي (TikTok, YouTube, ألعاب) خارج الدراسة.', 'fr'=>"Limitez à 2 h max les écrans récréatifs (TikTok, YouTube, jeux) hors études.", 'en'=>'Cap recreational screens (TikTok, YouTube, games) at 2 h max outside study.'], $lang),
                psPick(['ar'=>'استبدل ساعة من الشاشة بنشاط: مشي، قراءة، حديث مع الأسرة.', 'fr'=>"Remplacez 1 h d'écran par une activité : marche, lecture, conversation en famille.", 'en'=>'Swap 1 h of screen for an activity: walking, reading, family talk.'], $lang),
            ],
        ];
    }
    // Sédentaire
    return [
        'level' => 'danger',
        'title' => psPick(['ar'=>'الإفراط في الجلوس والشاشات — يجب التقليل', 'fr'=>"Trop d'écran et de sédentarité — à réduire impérativement", 'en'=>'Too much sitting and screens — must be reduced'], $lang),
        'text'  => psPick(['ar'=>'تقضي وقتاً طويلاً جداً جالساً. هذا عامل خطر مستقل عن النشاط: حتى الأشخاص الرياضيون الذين يجلسون 8+ ساعات يومياً معرضون للأمراض.', 'fr'=>"Vous passez trop de temps assis. C'est un facteur de risque indépendant : même les sportifs qui restent assis 8+ h/jour sont à risque.", 'en'=>'You spend too much time sitting. An independent risk factor: even athletes who sit 8+ h/day are at risk.'], $lang),
        'bullets' => [
            psPick(['ar'=>'وقف-كل-30-دقيقة: ضع منبهاً، وقف وتحرك دقيقة أو دقيقتين.', 'fr'=>"Levez-vous toutes les 30 min : minuteur, debout 1-2 min, étirements.", 'en'=>'Stand every 30 min: timer, stand 1-2 min, stretch.'], $lang),
            psPick(['ar'=>'احذف تطبيقاً أو اثنين من الترفيه على الهاتف لمدة أسبوع — ستدهشك النتيجة.', 'fr'=>"Supprimez 1-2 apps récréatives du téléphone pendant 1 semaine — résultat bluffant.", 'en'=>'Delete 1-2 recreational apps from the phone for a week — surprising result.'], $lang),
            psPick(['ar'=>'لا شاشات أثناء الأكل ولا قبل النوم بنصف ساعة.', 'fr'=>"Pas d'écran aux repas, ni 30 min avant de dormir.", 'en'=>'No screens at meals, no screens 30 min before sleep.'], $lang),
            psPick(['ar'=>'استخدم وقت الشاشة بنشاط: شاهد التلفاز واقفاً أو افعل تمارين خفيفة.', 'fr'=>"Bougez pendant les écrans : regardez la TV debout ou faites des exercices légers.", 'en'=>'Make screens active: watch TV standing or do light exercise.'], $lang),
        ],
    ];
}

// ═════════════════════════════════════════════════════════════════════
//  CONSEIL 6 — Risque d'obésité composite
// ═════════════════════════════════════════════════════════════════════
function adviceObRisk($classe, $score, $iotf, $lang) {
    if ($classe === 'Faible') {
        return [
            'level' => 'success',
            'title' => psPick(['ar'=>'خطر السمنة منخفض ✓', 'fr'=>"Risque d'obésité faible ✓", 'en'=>'Low obesity risk ✓'], $lang),
            'text'  => psPick(['ar'=>'عوامل الخطر لديك (الوراثة، النوم، النشاط، التغذية) مجتمعة منخفضة.', 'fr'=>"Vos facteurs de risque combinés (génétique, sommeil, activité, alimentation) sont faibles.", 'en'=>'Your combined risk factors (genetics, sleep, activity, diet) are low.'], $lang),
            'bullets' => [],
        ];
    }
    if ($classe === 'Modéré') {
        return [
            'level' => 'warning',
            'title' => psPick(['ar'=>'لديك خطر معتدل لزيادة الوزن', 'fr'=>"Vous avez un risque modéré de prise de poids", 'en'=>'You have a moderate risk of weight gain'], $lang),
            'text'  => psPick(['ar'=>'تجمع بعض عوامل الخطر: قد تكون وراثية + غذائية + قلة نشاط + نوم قصير. كل عامل وحده مقبول، لكن مجتمعةً قد تؤدي إلى زيادة وزن تدريجية.', 'fr'=>"Vous cumulez plusieurs facteurs : héréditaire + alimentaire + sédentaire + sommeil court. Chacun acceptable seul, mais leur cumul peut entraîner une prise de poids progressive.", 'en'=>'You combine multiple factors: hereditary + dietary + sedentary + short sleep. Each is manageable alone, but together they may cause gradual weight gain.'], $lang),
            'bullets' => [
                psPick(['ar'=>'اختر عاملاً واحداً لتغييره في الـ 4 أسابيع القادمة (أنصح: المشروبات السكرية).', 'fr'=>"Choisissez 1 facteur à changer dans les 4 prochaines semaines (conseil : boissons sucrées).", 'en'=>'Pick one factor to change over the next 4 weeks (tip: sugary drinks).'], $lang),
                psPick(['ar'=>'راقب وزنك مرة شهرياً (ليس أكثر) — اتجاهات الـ 6 أشهر هي المهمة.', 'fr'=>"Pesez-vous 1×/mois (pas plus) — la tendance sur 6 mois compte.", 'en'=>'Weigh yourself 1×/month (no more) — the 6-month trend matters.'], $lang),
            ],
        ];
    }
    // Élevé
    return [
        'level' => 'danger',
        'title' => psPick(['ar'=>'خطر مرتفع لزيادة الوزن — تحرّك الآن', 'fr'=>"Risque élevé de prise de poids — agissez maintenant", 'en'=>'High weight-gain risk — act now'], $lang),
        'text'  => psPick(['ar'=>'تجمع عدة عوامل خطر بشكل قوي. التدخل المبكر (قبل ظهور الوزن الزائد) أسهل بكثير من العلاج بعد ذلك.', 'fr'=>"Vous cumulez fortement plusieurs facteurs. L'intervention précoce (avant le surpoids) est bien plus simple que le traitement.", 'en'=>'You strongly combine several factors. Early intervention (before overweight) is far easier than treatment.'], $lang),
        'bullets' => [
            psPick(['ar'=>'الأولوية الأولى: تحسين النوم (8 ساعات) — قلة النوم تزيد هرمونات الجوع.', 'fr'=>"Priorité 1 : améliorer le sommeil (8 h) — la privation augmente les hormones de la faim.", 'en'=>'Priority 1: improve sleep (8 h) — deprivation raises hunger hormones.'], $lang),
            psPick(['ar'=>'الأولوية الثانية: حذف المشروبات السكرية كلياً (سعرات فارغة + رفع الأنسولين).', 'fr'=>"Priorité 2 : supprimer totalement les boissons sucrées (calories vides + insuline).", 'en'=>'Priority 2: cut sugary drinks entirely (empty calories + insulin spike).'], $lang),
            psPick(['ar'=>'الأولوية الثالثة: 30 دقيقة نشاط يومياً، حتى لو مشي بسيط بعد كل وجبة.', 'fr'=>"Priorité 3 : 30 min d'activité/jour, même marche après les repas.", 'en'=>'Priority 3: 30 min activity/day, even a walk after meals.'], $lang),
            psPick(['ar'=>'إذا كان أحد الوالدين بدين، أنت أكثر عرضة بـ 2-3 مرات. الوراثة تحدد القابلية، لكن السلوك يحدد النتيجة.', 'fr'=>"Si un parent est obèse, vous êtes 2-3× plus à risque. La génétique prédispose, le comportement décide.", 'en'=>'If a parent is obese, you are 2-3× more at risk. Genetics load the gun, behaviour pulls the trigger.'], $lang),
        ],
    ];
}

// ═════════════════════════════════════════════════════════════════════
//  CONSEIL 7 — Discordance perception / IOTF réel
// ═════════════════════════════════════════════════════════════════════
function advicePerception($gap, $iotf, $lang) {
    if ($gap === 'Concordant') {
        return [
            'level' => 'success',
            'title' => psPick(['ar'=>'إدراكك لجسمك واقعي ✓', 'fr'=>"Votre perception corporelle est réaliste ✓", 'en'=>'Your body perception is realistic ✓'], $lang),
            'text'  => psPick(['ar'=>'ترى نفسك كما هي حالتك فعلياً. هذا أساس مهم لاتخاذ قرارات صحية صائبة.', 'fr'=>"Vous vous voyez tel(le) que vous êtes. C'est une base solide pour des décisions de santé justes.", 'en'=>'You see yourself as you are. A solid foundation for sound health decisions.'], $lang),
            'bullets' => [],
        ];
    }
    if ($gap === 'Surestime son poids') {
        return [
            'level' => 'warning',
            'title' => psPick(['ar'=>'ترى نفسك أثقل مما أنت عليه فعلاً', 'fr'=>"Vous vous voyez plus gros(se) que vous ne l'êtes", 'en'=>'You see yourself heavier than you actually are'], $lang),
            'text'  => psPick(['ar'=>'حسب IOTF، وزنك في النطاق الطبيعي أو حتى منخفض، لكن إدراكك يرى أنك زائد الوزن. هذا قد يدل على ضغط من وسائل التواصل، أو بداية اضطراب أكل.', 'fr'=>"Selon IOTF, vous êtes dans la norme (ou même mince), mais vous vous percevez en surpoids. Cela peut refléter la pression des réseaux sociaux ou un début de TCA.", 'en'=>'Per IOTF, you are in (or below) the normal range, but you see yourself as overweight. This can reflect social media pressure or an emerging eating disorder.'], $lang),
            'bullets' => [
                psPick(['ar'=>'الصور على إنستغرام/تيك توك معدّلة 90٪. لا تقارن نفسك بفلتر.', 'fr'=>"Les images sur Instagram/TikTok sont retouchées à 90 %. Ne vous comparez pas à un filtre.", 'en'=>'90% of Instagram/TikTok images are edited. Do not compare yourself to a filter.'], $lang),
                psPick(['ar'=>'إذا فكّرت كثيراً في وزنك، أو تتجنب الأكل، أو تعدّ السعرات بقلق — تحدث مع شخص تثق به أو مع طبيب نفسي.', 'fr'=>"Si vous pensez beaucoup au poids, évitez de manger, ou comptez les calories avec angoisse — parlez à un proche ou à un psy.", 'en'=>'If you obsess over weight, avoid eating, or anxiously count calories — talk to a trusted person or a therapist.'], $lang),
                psPick(['ar'=>'احتفل بما يفعله جسمك (الجري، الرقص، الاحتضان) لا فقط بشكله.', 'fr'=>"Célébrez ce que votre corps fait (courir, danser, embrasser) — pas seulement son apparence.", 'en'=>'Celebrate what your body does (running, dancing, hugging) — not only how it looks.'], $lang),
            ],
        ];
    }
    // Sous-estime
    return [
        'level' => 'warning',
        'title' => psPick(['ar'=>'تقلل من تقدير وزنك', 'fr'=>"Vous sous-estimez votre poids", 'en'=>'You underestimate your weight'], $lang),
        'text'  => psPick(['ar'=>'حسب IOTF، أنت في فئة وزن أعلى مما تظن. عدم إدراك الواقع قد يؤخر اتخاذ خطوات صحية.', 'fr'=>"Selon IOTF, vous êtes dans une catégorie plus élevée que vous ne le pensez. Cette non-conscience peut retarder des décisions saines.", 'en'=>'Per IOTF, you are in a higher category than you think. This blind spot can delay healthy decisions.'], $lang),
        'bullets' => [
            psPick(['ar'=>'الإدراك الواقعي = الخطوة الأولى. تجنّب الإنكار، لكن أيضاً تجنّب جلد الذات.', 'fr'=>"La conscience réaliste est la 1ère étape. Évitez le déni, mais aussi l'auto-flagellation.", 'en'=>'Realistic awareness is step 1. Avoid denial — but also avoid self-blame.'], $lang),
            psPick(['ar'=>'استخدم البيانات (الوزن، IMC) لا المرآة فقط — الأخيرة تخدعنا.', 'fr'=>"Utilisez les chiffres (poids, IMC), pas seulement le miroir — il nous trompe.", 'en'=>'Use numbers (weight, BMI), not only the mirror — it deceives us.'], $lang),
            psPick(['ar'=>'تحدث مع طبيبك عن خطة عمل واقعية ومخصصة.', 'fr'=>"Discutez avec votre médecin d'un plan réaliste et personnalisé.", 'en'=>'Talk to your doctor about a realistic, personalized plan.'], $lang),
        ],
    ];
}

// ═════════════════════════════════════════════════════════════════════
//  CONSEIL 8 — Anémie, Vitamine D, Trouble alimentaire (risques)
// ═════════════════════════════════════════════════════════════════════
function adviceAnemia($classe, $lang) {
    if ($classe === 'Faible') return null;
    $level = $classe === 'Élevé' ? 'danger' : 'warning';
    return [
        'level' => $level,
        'title' => psPick(['ar'=>'خطر فقر الدم — انتبه', 'fr'=>"Risque d'anémie — attention", 'en'=>'Anaemia risk — note'], $lang),
        'text'  => psPick(['ar'=>'استهلاكك للأطعمة الغنية بالحديد (لحم أحمر، سمك، بقوليات) منخفض. النحاس قد تظهر علامات: تعب، شحوب، صداع، تساقط شعر، ضيق نفس عند المجهود.', 'fr'=>"Votre apport en aliments riches en fer (viande rouge, poisson, légumineuses) est faible. Signes d'alerte : fatigue, pâleur, maux de tête, chute de cheveux, essoufflement à l'effort.", 'en'=>'Your intake of iron-rich foods (red meat, fish, legumes) is low. Watch for fatigue, paleness, headaches, hair loss, breathlessness on effort.'], $lang),
        'bullets' => [
            psPick(['ar'=>'أضف عدساً، حمصاً، أو فاصوليا (2 مرات/أسبوع على الأقل) — مع فيتامين C (ليمون، فلفل، طماطم) لامتصاص أفضل.', 'fr'=>"Ajoutez lentilles, pois chiches ou haricots (2×/sem minimum) — avec vitamine C (citron, poivron, tomate) pour mieux absorber.", 'en'=>'Add lentils, chickpeas or beans (2×/wk min) — with vitamin C (lemon, pepper, tomato) for better absorption.'], $lang),
            psPick(['ar'=>'لحوم حمراء مرة أو مرتين/أسبوع، أو دجاج/سمك يومياً تقريباً.', 'fr'=>"Viande rouge 1-2×/sem, ou volaille/poisson presque quotidien.", 'en'=>'Red meat 1-2×/wk, or poultry/fish almost daily.'], $lang),
            psPick(['ar'=>'إذا شعرت بأعراض، اطلب تحليل الحديد (Ferritine + NFS) عند الطبيب.', 'fr'=>"Si symptômes : demandez un bilan martial (Ferritine + NFS).", 'en'=>'If symptoms: ask for iron tests (ferritin + CBC).'], $lang),
            psPick(['ar'=>'لا تأخذ الشاي أو القهوة مع الوجبات الرئيسية — تثبط امتصاص الحديد.', 'fr'=>"Évitez thé/café pendant les repas — inhibe l'absorption du fer.", 'en'=>'Avoid tea/coffee with meals — they inhibit iron absorption.'], $lang),
        ],
    ];
}

function adviceVitD($classe, $lang) {
    if ($classe === 'Faible') return null;
    $level = $classe === 'Élevé' ? 'danger' : 'warning';
    return [
        'level' => $level,
        'title' => psPick(['ar'=>'خطر نقص فيتامين D', 'fr'=>"Risque de carence en vitamine D", 'en'=>'Vitamin D deficiency risk'], $lang),
        'text'  => psPick(['ar'=>'نقص النشاط في الهواء الطلق + قلة الحليب والسمك والبيض = نقص محتمل في فيتامين D، الذي يؤثر على العظام، المناعة، والمزاج.', 'fr'=>"Peu d'activité en plein air + peu de lait/poisson/œufs = carence probable en vit. D (os, immunité, humeur).", 'en'=>'Low outdoor activity + low milk/fish/eggs = likely vit D deficiency (bones, immunity, mood).'], $lang),
        'bullets' => [
            psPick(['ar'=>'تعرض للشمس 15-20 دقيقة يومياً (الذراعان والوجه) في الصباح أو نهاية النهار.', 'fr'=>"Exposez-vous au soleil 15-20 min/jour (bras et visage), matin ou fin de journée.", 'en'=>'Get 15-20 min sun/day (arms and face), morning or late afternoon.'], $lang),
            psPick(['ar'=>'أضف سمكاً دهنياً (سردين، سلمون، تونة) مرة أو مرتين/أسبوع.', 'fr'=>"Ajoutez poisson gras (sardine, saumon, thon) 1-2×/sem.", 'en'=>'Add fatty fish (sardine, salmon, tuna) 1-2×/wk.'], $lang),
            psPick(['ar'=>'في الجزائر، الشمس قوية لكن العديد من الناس لديهم نقص — لا تفترض، اطلب فحصاً عند الطبيب.', 'fr'=>"En Algérie, le soleil est fort mais la carence reste fréquente — faites un dosage.", 'en'=>'In Algeria, sun is strong but deficiency is still common — get tested.'], $lang),
        ],
    ];
}

function adviceEatDis($classe, $lang) {
    if ($classe === 'Faible') return null;
    $level = $classe === 'Élevé' ? 'danger' : 'warning';
    return [
        'level' => $level,
        'title' => psPick(['ar'=>'علامات سلوك أكل قلق', 'fr'=>"Signes de comportement alimentaire à risque", 'en'=>'Signs of disordered eating'], $lang),
        'text'  => psPick(['ar'=>'لاحظنا مزيجاً من العوامل: محاولات إنقاص الوزن + إدراك غير واقعي + الأكل العاطفي + إرهاق. لا تتجاهل هذه الإشارات.', 'fr'=>"Nous observons un faisceau : tentatives de perte + perception décalée + alimentation émotionnelle + fatigue. N'ignorez pas ces signaux.", 'en'=>'We see a cluster: weight-loss attempts + skewed perception + emotional eating + exhaustion. Do not ignore these signals.'], $lang),
        'bullets' => [
            psPick(['ar'=>'العلاقة الصحية مع الطعام أهم من الميزان. الطعام صديق، ليس عدواً.', 'fr'=>"La relation saine à l'alimentation passe avant la balance. Manger est un allié, pas un ennemi.", 'en'=>'A healthy relationship with food matters more than the scale. Food is a friend, not an enemy.'], $lang),
            psPick(['ar'=>'تجنب الحميات القاسية والعقابية — تؤدي إلى نوبات أكل وذنب وندم.', 'fr'=>"Évitez les régimes restrictifs/punitifs — ils mènent à des crises et culpabilité.", 'en'=>'Avoid restrictive/punitive diets — they trigger binges and guilt.'], $lang),
            psPick(['ar'=>'إذا كنت تأكل سراً، أو تتقيأ، أو تتعب من التفكير في الطعام — تحدث مع طبيب نفسي. هذا قابل للعلاج.', 'fr'=>"Si vous mangez en cachette, vomissez, ou êtes obsédé(e) par la nourriture — parlez à un psy. C'est soignable.", 'en'=>'If you eat secretly, vomit, or are obsessed with food — see a therapist. This is treatable.'], $lang),
            psPick(['ar'=>'لست وحدك. اضطرابات الأكل شائعة وعلاجها فعّال إذا بدأ مبكراً.', 'fr'=>"Vous n'êtes pas seul(e). Les TCA sont fréquents et se soignent bien si pris tôt.", 'en'=>'You are not alone. Eating disorders are common and respond well to early treatment.'], $lang),
        ],
    ];
}

// ═════════════════════════════════════════════════════════════════════
//  CONSEIL 9 — Environnement familial / pression scolaire
// ═════════════════════════════════════════════════════════════════════
function adviceFamily($classe, $lang) {
    if ($classe === 'Favorable') return null;
    if ($classe === 'Défavorable') {
        return [
            'level' => 'warning',
            'title' => psPick(['ar'=>'البيئة الأسرية غير مواتية لعاداتك', 'fr'=>"L'environnement familial n'est pas en votre faveur", 'en'=>'Your family environment is not in your favour'], $lang),
            'text'  => psPick(['ar'=>'عوامل مثل قلة الوجبات العائلية، ضغط الأصدقاء على الوجبات السريعة، أو سمنة الوالدين تجعل مهمتك أصعب — لكنها ليست مستحيلة.', 'fr'=>"Manque de repas familiaux, pression des amis pour fast-food, ou obésité parentale rendent la tâche plus dure — mais pas impossible.", 'en'=>'Few family meals, peer pressure for fast food, or parental obesity make it harder — but not impossible.'], $lang),
            'bullets' => [
                psPick(['ar'=>'ابحث عن حليف داخل الأسرة: شخص واحد فقط يدعمك يكفي للنجاح.', 'fr'=>"Trouvez un allié dans la famille : 1 seule personne qui vous soutient suffit.", 'en'=>'Find a family ally: one supportive person is enough.'], $lang),
                psPick(['ar'=>'اقترح وجبة عائلية واحدة في الأسبوع — يمكن أن تكون عشاء يوم الجمعة.', 'fr'=>"Proposez 1 repas familial/semaine — par exemple dîner du vendredi.", 'en'=>'Suggest one family meal/week — e.g. Friday dinner.'], $lang),
                psPick(['ar'=>'بشأن ضغط الأصدقاء على الوجبات السريعة: اقترح بدائل (مطعم سمك، عصير، خبز محشي).', 'fr'=>"Pour la pression amis : proposez des alternatives (poissonnerie, jus, sandwich maison).", 'en'=>'For peer pressure: suggest alternatives (fish shop, juice, homemade sandwich).'], $lang),
            ],
        ];
    }
    return null;
}

function adviceSchoolPressure($classe, $lang) {
    if ($classe === 'Faible') return null;
    $level = $classe === 'Élevée' ? 'danger' : 'warning';
    return [
        'level' => $level,
        'title' => psPick(['ar'=>'الضغط الدراسي يؤثر على تغذيتك', 'fr'=>"La pression scolaire affecte votre alimentation", 'en'=>'Academic pressure affects your eating'], $lang),
        'text'  => psPick(['ar'=>'تخطّي الوجبات بسبب الدراسة، عدم تناول الإفطار، وكثرة الوجبات الخفيفة خارج المنزل تشير إلى أن جدولك يأكل وقت تغذيتك. هذا يضعف التركيز والذاكرة!', 'fr'=>"Sauter des repas pour étudier, manquer le petit-déjeuner, multiplier les snacks dehors = votre emploi du temps grignote votre nutrition. Cela affaiblit mémoire et concentration !", 'en'=>'Skipping meals to study, missing breakfast, frequent outside snacks = your schedule eats your nutrition. This weakens memory and focus!'], $lang),
        'bullets' => [
            psPick(['ar'=>'الإفطار غير قابل للتفاوض. حتى 5 دقائق: حليب + خبز + موزة = طاقة 4 ساعات.', 'fr'=>"Le petit-déjeuner n'est pas négociable. 5 min : lait + pain + banane = 4 h d'énergie.", 'en'=>'Breakfast is non-negotiable. 5 min: milk + bread + banana = 4 h of energy.'], $lang),
            psPick(['ar'=>'حضّر وجبات خفيفة صحية: مكسرات، فاكهة، زبادي. لا تذهب جائعاً للدروس.', 'fr'=>"Préparez des collations saines : noix, fruits, yaourt. N'allez jamais en cours affamé.", 'en'=>'Prep healthy snacks: nuts, fruit, yogurt. Never go to class hungry.'], $lang),
            psPick(['ar'=>'الدماغ الذي لا يأكل لا يحفظ. التغذية = جزء من الدراسة، ليست خصمها.', 'fr'=>"Un cerveau qui ne mange pas ne mémorise pas. La nutrition fait partie des études.", 'en'=>'A brain that does not eat does not remember. Nutrition is part of studying.'], $lang),
        ],
    ];
}
