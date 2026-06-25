<?php
/**
 * ════════════════════════════════════════════════════════════════════
 *  personal-survey/extra_advice.php
 *  Conseils personnalisés pour les nouveaux indicateurs.
 *  Utilise psPick() défini dans advice.php (déjà chargé).
 * ════════════════════════════════════════════════════════════════════
 */
require_once __DIR__ . '/advice.php';

// ─── WHtR ────────────────────────────────────────────────────────────
function adviceWHtR($whtr, $class, $lang) {
    if ($whtr === null) return null;
    if ($class === 'Sain' || $class === 'Faible') {
        return [
            'level' => 'success',
            'title' => psPick(['ar'=>'محيط خصرك جيّد ✓', 'fr'=>'Tour de taille sain ✓', 'en'=>'Healthy waist circumference ✓'], $lang),
            'text'  => psPick(['ar'=>'نسبة الخصر/الطول لديك ضمن المجال الصحّي. هذا أهمّ من IMC وحده، لأنّه يقيس الدهون البطنية المرتبطة مباشرةً بأمراض القلب والسكّري.', 'fr'=>'Votre rapport taille/hauteur est dans la zone saine. C\'est plus important que l\'IMC seul car il mesure la graisse abdominale, directement liée aux maladies cardiovasculaires et au diabète.', 'en'=>'Your waist-to-height ratio is in the healthy zone. This is more important than BMI alone because it measures abdominal fat, directly linked to cardiovascular disease and diabetes.'], $lang),
            'bullets' => [],
        ];
    }
    $level = $class === 'Élevé' ? 'danger' : 'warning';
    return [
        'level' => $level,
        'title' => psPick(['ar'=>'تنبيه: السمنة البطنية (WHtR ≥ 0.5)', 'fr'=>'Alerte : obésité abdominale (WHtR ≥ 0.5)', 'en'=>'Alert: abdominal obesity (WHtR ≥ 0.5)'], $lang),
        'text'  => psPick(['ar'=>'الدهون المتراكمة حول البطن أخطر من الدهون تحت الجلد. حتى لو كان وزنك « طبيعياً »، محيط خصر مرتفع يضاعف خطر السكّري ومتلازمة الأيض.', 'fr'=>'La graisse autour du ventre est plus dangereuse que celle sous la peau. Même avec un poids "normal", un tour de taille élevé multiplie le risque de diabète et de syndrome métabolique.', 'en'=>'Belly fat is more dangerous than subcutaneous fat. Even at "normal" weight, a high waist circumference multiplies the risk of diabetes and metabolic syndrome.'], $lang),
        'bullets' => [
            psPick(['ar'=>'تمارين القلب 30 دقيقة، 5 مرات/أسبوع تخفض الدهون البطنية أسرع من أي تمرين آخر.', 'fr'=>'30 min de cardio 5 fois/sem réduit la graisse abdominale plus vite que tout autre exercice.', 'en'=>'30 min of cardio 5×/week reduces belly fat faster than any other exercise.'], $lang),
            psPick(['ar'=>'قلّل السكّر المضاف والمشروبات الغازية: هما الوقود الرئيسي للدهون البطنية.', 'fr'=>'Réduisez le sucre ajouté et les sodas : carburant principal de la graisse abdominale.', 'en'=>'Cut added sugar and sodas: the main fuel for belly fat.'], $lang),
            psPick(['ar'=>'الألياف (خضار، عدس، شوفان) تقلّل الدهون البطنية حتى بدون فقدان وزن إجمالي.', 'fr'=>'Les fibres (légumes, lentilles, avoine) réduisent la graisse abdominale même sans perte de poids globale.', 'en'=>'Fiber (vegetables, lentils, oats) reduces belly fat even without total weight loss.'], $lang),
            psPick(['ar'=>'استشر طبيباً لقياس السكّر الصائم والكوليسترول للوقاية المبكّرة.', 'fr'=>'Consultez un médecin pour glycémie à jeun et bilan lipidique en prévention.', 'en'=>'Consult a doctor for fasting glucose and lipid profile for early prevention.'], $lang),
        ],
    ];
}

// ─── UPF ─────────────────────────────────────────────────────────────
function adviceUPF($score, $class, $daily_items, $lang) {
    if ($class === 'Faible') {
        return [
            'level' => 'success',
            'title' => psPick(['ar'=>'استهلاكك للأطعمة المصنّعة منخفض ✓', 'fr'=>'Faible consommation d\'aliments ultra-transformés ✓', 'en'=>'Low ultra-processed food intake ✓'], $lang),
            'text'  => psPick(['ar'=>'أنت تأكل بشكل رئيسي طعاماً حقيقياً غير مصنّع. هذا أحد أقوى الحواجز ضد السمنة وأمراض الأيض. تابع هكذا.', 'fr'=>'Vous mangez majoritairement de la vraie nourriture non transformée. C\'est l\'une des meilleures barrières contre l\'obésité et les maladies métaboliques. Continuez.', 'en'=>'You eat mostly real, non-processed food. This is one of the strongest barriers against obesity and metabolic disease. Keep it up.'], $lang),
            'bullets' => [],
        ];
    }
    $level = ($class === 'Élevée' || $class === 'Très élevée') ? 'danger' : 'warning';
    return [
        'level' => $level,
        'title' => psPick(['ar'=>'استهلاك مرتفع للأطعمة فائقة المعالجة', 'fr'=>'Consommation élevée d\'aliments ultra-transformés', 'en'=>'High ultra-processed food intake'], $lang),
        'text'  => psPick(['ar'=>"تستهلك " . $daily_items . " صنفاً صناعياً يومياً تقريباً. الدراسات الحديثة تربط هذه الأطعمة مباشرةً بزيادة الوزن، حتى مع كميات قليلة.", 'fr'=>"Vous consommez quotidiennement environ " . $daily_items . " items industriels. Les études récentes lient directement ces aliments à la prise de poids, même en petites quantités.", 'en'=>"You consume about " . $daily_items . " industrial items daily. Recent studies directly link these foods to weight gain, even in small amounts."], $lang),
        'bullets' => [
            psPick(['ar'=>'قاعدة بسيطة: إذا كانت قائمة المكونات > 5 أسطر أو فيها أرقام E... فهو طعام صناعي. اختر طعاماً بمكوّن واحد.', 'fr'=>'Règle simple : si la liste d\'ingrédients fait > 5 lignes ou contient des codes E…, c\'est ultra-transformé. Préférez les aliments à 1 ingrédient.', 'en'=>'Simple rule: if the ingredient list is > 5 lines or has E-codes, it\'s ultra-processed. Choose 1-ingredient foods.'], $lang),
            psPick(['ar'=>'بدائل سريعة: ماء بدل الصودا، تمر بدل الحلوى الصناعية، فول سوداني بدل الشيبس، خبز بلدي بدل الخبز الصناعي.', 'fr'=>'Substitutions rapides : eau au lieu de soda, dattes au lieu de bonbons, cacahuètes au lieu de chips, pain traditionnel au lieu du pain industriel.', 'en'=>'Quick swaps: water for soda, dates for candy, peanuts for chips, traditional bread for packaged.'], $lang),
            psPick(['ar'=>'احضِّر وجبة واحدة في المنزل يومياً على الأقل. الطبخ مهارة وقاية مدى الحياة.', 'fr'=>'Préparez au moins un repas maison par jour. Cuisiner est une compétence de prévention à vie.', 'en'=>'Cook at least one home-made meal a day. Cooking is a lifelong prevention skill.'], $lang),
            psPick(['ar'=>'الفاست فود في أسبوع واحد قد يحتوي على سكر شهر كامل. اجعله مناسبة استثنائية لا روتيناً.', 'fr'=>'Le fast-food d\'une semaine peut contenir le sucre d\'un mois entier. Réservez-le aux occasions exceptionnelles.', 'en'=>'A week of fast-food can hold a month of sugar. Make it occasional, not routine.'], $lang),
        ],
    ];
}

// ─── Perinatal ───────────────────────────────────────────────────────
function advicePerinatal($score, $class, $factors, $lang) {
    if ($class === 'Faible') {
        return [
            'level' => 'success',
            'title' => psPick(['ar'=>'بدايات صحّية ✓', 'fr'=>'Bonne entrée dans la vie ✓', 'en'=>'Healthy start ✓'], $lang),
            'text'  => psPick(['ar'=>'لديك القليل من عوامل الخطر المبكّرة. هذه أرضية ممتازة، استفد منها.', 'fr'=>'Vous avez peu de facteurs de risque précoces. Excellente base, capitalisez dessus.', 'en'=>'You have few early risk factors. Excellent foundation, build on it.'], $lang),
            'bullets' => [],
        ];
    }
    $level = ($class === 'Très élevé') ? 'danger' : 'warning';
    return [
        'level' => $level,
        'title' => psPick(['ar'=>'عوامل خطر مبكّرة (1000 يوم الأولى)', 'fr'=>'Facteurs de risque précoces (1000 premiers jours)', 'en'=>'Early-life risk factors (first 1000 days)'], $lang),
        'text'  => psPick(['ar'=>'ظروف الولادة والرضاعة ساهمت في برمجة الأيض. هذا لا يحدّد مصيرك، لكنّه يعني أنّ نمط حياتك الحالي يلعب دوراً أكبر مما يلعبه عند الآخرين.', 'fr'=>'Votre naissance et votre allaitement ont contribué à programmer votre métabolisme. Ce n\'est pas une fatalité, mais cela signifie que votre mode de vie actuel joue un rôle plus important que chez d\'autres.', 'en'=>'Your birth and feeding conditions helped program your metabolism. It\'s not destiny, but your current lifestyle matters more than for others.'], $lang),
        'bullets' => [
            psPick(['ar'=>'استشر اختصاصي غذية مرة في السنة على الأقل: متابعة وقائية لمؤشرات الأيض.', 'fr'=>'Consultez un nutritionniste une fois/an : suivi métabolique préventif.', 'en'=>'See a nutritionist once a year: preventive metabolic follow-up.'], $lang),
            psPick(['ar'=>'لأنك في خطر أعلى وراثياً/مبكّراً، الانضباط في النوم، الحركة، والأكل يجب أن يكون أعلى من المعتاد.', 'fr'=>'Votre risque précoce étant plus élevé, votre discipline sommeil/mouvement/alimentation doit être au-dessus de la moyenne.', 'en'=>'Since your early risk is higher, your discipline in sleep, movement, and eating should be above average.'], $lang),
            psPick(['ar'=>'تحاليل سنوية بعد سن 18: غلوكوز صائم، A1c، كوليسترول، فيريتين، فيتامين د.', 'fr'=>'Bilan annuel après 18 ans : glycémie à jeun, HbA1c, lipides, ferritine, vitamine D.', 'en'=>'Annual check after age 18: fasting glucose, HbA1c, lipids, ferritin, vitamin D.'], $lang),
        ],
    ];
}

// ─── Genetic / family ────────────────────────────────────────────────
function adviceGenetic($score, $class, $lang) {
    if ($class === 'Faible') return null;
    $level = ($class === 'Très élevé') ? 'danger' : ($class === 'Élevé' ? 'warning' : 'info');
    return [
        'level' => $level,
        'title' => psPick(['ar'=>'خطر وراثي / عائلي مرتفع', 'fr'=>'Risque génétique / familial élevé', 'en'=>'Elevated genetic / family risk'], $lang),
        'text'  => psPick(['ar'=>'العائلة تشترك في الجينات والثلاجة. الجينات تحدد القابلية، لكن نمط الحياة يقرّر ما إذا كانت ستظهر. أنت لست محكوماً بقدر عائلتك.', 'fr'=>'La famille partage les gènes ET le frigo. Les gènes définissent la prédisposition, le mode de vie décide si elle s\'exprime. Vous n\'êtes pas condamné(e) à suivre votre famille.', 'en'=>'Family shares genes AND the fridge. Genes define predisposition, lifestyle decides if it shows. You are not condemned to follow your family\'s path.'], $lang),
        'bullets' => [
            psPick(['ar'=>'تاريخ السكّري في العائلة = ابدأ مراقبة السكّر صائماً سنوياً من سن 16.', 'fr'=>'Antécédents de diabète familial = surveillez la glycémie à jeun annuellement dès 16 ans.', 'en'=>'Family diabetes history = annual fasting glucose monitoring from age 16.'], $lang),
            psPick(['ar'=>'تاريخ القلب/الكوليسترول = حدّ من الدهون المشبعة (سمن، قشدة، لحم أحمر دهني) وأكثر من الزيوت النباتية.', 'fr'=>'Antécédents cardiaques/cholestérol = limitez graisses saturées (beurre, crème, viande rouge grasse), privilégiez huiles végétales.', 'en'=>'Heart/cholesterol history = limit saturated fats (butter, cream, fatty red meat), favor vegetable oils.'], $lang),
            psPick(['ar'=>'الجين ليس قدراً. دراسات FTO تظهر أنّ النشاط البدني المنتظم يلغي 30-50% من الميل الوراثي للسمنة.', 'fr'=>'Le gène n\'est pas une fatalité : les études FTO montrent que l\'activité physique régulière annule 30-50% de la prédisposition génétique à l\'obésité.', 'en'=>'Genes are not destiny: FTO studies show regular physical activity cancels 30-50% of the genetic predisposition to obesity.'], $lang),
            psPick(['ar'=>'تكلّم مع والديك: ما يصلح لجسد قد يصلح للآخر. شاركوا التغيير العائلي.', 'fr'=>'Parlez à vos parents : ce qui marche pour un corps fonctionne souvent pour l\'autre. Faites du changement une affaire familiale.', 'en'=>'Talk to your parents: what works for one body often works for another. Make change a family affair.'], $lang),
        ],
    ];
}

// ─── SCOFF ───────────────────────────────────────────────────────────
function adviceSCOFF($positive, $count, $items, $lang) {
    if (!$positive) return null;
    return [
        'level' => 'danger',
        'title' => psPick(['ar'=>'⚠ علاقتك بالطعام تستحق اهتماماً مختصّاً', 'fr'=>'⚠ Votre rapport à la nourriture mérite un avis spécialisé', 'en'=>'⚠ Your relationship with food deserves a specialist opinion'], $lang),
        'text'  => psPick(['ar'=>"أجبت بـ « نعم » على $count من 5 من أسئلة SCOFF (أداة طبية معتمدة). هذا لا يعني أنّك مريض، لكنه إشارة هامّة. اضطرابات الأكل ليست ضعف إرادة، بل حالات نفسية-جسديّة حقيقية. أبكر = أسهل علاجاً.", 'fr'=>"Vous avez répondu « oui » à $count des 5 questions SCOFF (outil médical validé). Cela ne signifie pas que vous êtes malade, mais c'est un signal important. Les troubles alimentaires ne sont pas une faiblesse de volonté, mais des conditions psycho-corporelles réelles. Tôt = plus facile à traiter.", 'en'=>"You answered \"yes\" to $count of 5 SCOFF questions (validated medical tool). It doesn't mean you're sick, but it's an important signal. Eating disorders aren't willpower failures — they're real psycho-somatic conditions. Earlier intervention = easier treatment."], $lang),
        'bullets' => [
            psPick(['ar'=>'تواصل مع طبيب أو اختصاصي تغذية تربطه بطبيب نفسي. عيادة الطبّ المدرسي/الجامعي نقطة بداية مجّانية.', 'fr'=>'Consultez un médecin ou un nutritionniste lié à un psychologue. La médecine scolaire/universitaire est un point de départ gratuit.', 'en'=>'Talk to a doctor or nutritionist linked to a psychologist. School/university medicine is a free starting point.'], $lang),
            psPick(['ar'=>'في الجزائر: مستشفيات Hôpital Drid Hocine (الجزائر) أو CHU وهران لديهم خدمات اضطرابات الأكل.', 'fr'=>'En Algérie : Hôpital Drid Hocine (Alger) ou CHU Oran ont des services TCA.', 'en'=>'In Algeria: Drid Hocine Hospital (Algiers) or CHU Oran have eating-disorder services.'], $lang),
            psPick(['ar'=>'لا تخفِ الأمر عن عائلتك خوفاً من الإحراج. الصمت يطيل المعاناة.', 'fr'=>'N\'occultez pas ce sujet à votre famille par peur de la gêne. Le silence prolonge la souffrance.', 'en'=>'Don\'t hide this from your family out of shame. Silence prolongs suffering.'], $lang),
            psPick(['ar'=>'تذكّر: الأكل أكثر/أقل ليس فشلاً أخلاقياً. أنت تستحق الدعم.', 'fr'=>'Rappel : manger plus/moins n\'est pas un échec moral. Vous méritez du soutien.', 'en'=>'Remember: eating more/less is not a moral failing. You deserve support.'], $lang),
        ],
    ];
}

// ─── Mental health (PHQ-2 + GAD) ─────────────────────────────────────
function adviceMental($phq2, $gad, $class, $lang) {
    if ($class === 'Bon') return null;
    $level = ($class === 'Critique') ? 'danger' : ($class === 'Préoccupant' ? 'warning' : 'info');
    return [
        'level' => $level,
        'title' => psPick(['ar'=>'صحّتك النفسية تحتاج اهتماماً', 'fr'=>'Votre santé mentale demande attention', 'en'=>'Your mental health needs attention'], $lang),
        'text'  => psPick(['ar'=>'العقل والجسم يتغذّيان من بعضهما. الاكتئاب والقلق يدفعان للأكل العاطفي ويعطّلان النوم والنشاط. علاج النفس = علاج الجسم.', 'fr'=>'Esprit et corps s\'alimentent mutuellement. La dépression et l\'anxiété poussent au grignotage émotionnel, perturbent sommeil et activité. Soigner l\'esprit = soigner le corps.', 'en'=>'Mind and body feed each other. Depression and anxiety drive emotional eating, disrupt sleep and activity. Healing the mind = healing the body.'], $lang),
        'bullets' => [
            psPick(['ar'=>'حدّث صديقاً موثوقاً أو فرداً من عائلتك. ليس عليك حمل هذا وحدك.', 'fr'=>'Parlez-en à un ami de confiance ou un proche. Vous n\'avez pas à porter cela seul(e).', 'en'=>'Talk to a trusted friend or family member. You don\'t have to carry this alone.'], $lang),
            psPick(['ar'=>'في الجزائر: خط مساعدة وزارة الصحّة 3030، أو عيادات الطب النفسي في الجامعات.', 'fr'=>'En Algérie : ligne d\'aide ministère de la santé 3030, ou consultations psy universitaires.', 'en'=>'In Algeria: Ministry of Health helpline 3030, or university psychiatric clinics.'], $lang),
            psPick(['ar'=>'30 دقيقة مشي يومياً تخفض القلق بقدر يعادل مضادات الاكتئاب الخفيفة في دراسات كثيرة.', 'fr'=>'30 min de marche/jour réduit l\'anxiété autant que des antidépresseurs légers selon plusieurs études.', 'en'=>'30 min daily walk reduces anxiety as much as light antidepressants per multiple studies.'], $lang),
            psPick(['ar'=>'قلّل من Instagram/TikTok قبل النوم: الدراسات تربطها مباشرةً بالاكتئاب لدى المراهقين.', 'fr'=>'Réduisez Instagram/TikTok avant de dormir : lien direct avec dépression adolescente démontré.', 'en'=>'Cut Instagram/TikTok before sleep: directly linked to teen depression in studies.'], $lang),
        ],
    ];
}

// ─── Eating behavior ─────────────────────────────────────────────────
function adviceEatingBehavior($score, $class, $issues, $lang) {
    if ($class === 'Sain') return null;
    $level = ($class === 'À risque élevé') ? 'danger' : ($class === 'Préoccupant' ? 'warning' : 'info');
    $bullets = [];
    if (in_array('very_fast', $issues) || in_array('fast', $issues)) {
        $bullets[] = psPick(['ar'=>'تباطأ: 20 دقيقة هو أقل وقت ليرسل المعدة إشارة الشبع. مضغ كل لقمة 15 مرة.', 'fr'=>'Ralentissez : 20 min minimum pour que l\'estomac signale la satiété. Mâchez 15 fois chaque bouchée.', 'en'=>'Slow down: 20 min minimum for stomach to signal fullness. Chew each bite 15 times.'], $lang);
    }
    if (in_array('huge_portions', $issues) || in_array('large_portions', $issues)) {
        $bullets[] = psPick(['ar'=>'قاعدة الطبق: نصف خضار، ربع نشويات، ربع بروتين. استخدم طبقاً أصغر تلقائياً.', 'fr'=>'Règle de l\'assiette : 1/2 légumes, 1/4 féculents, 1/4 protéines. Utilisez une assiette plus petite — la portion suit automatiquement.', 'en'=>'Plate rule: 1/2 vegetables, 1/4 starches, 1/4 protein. Use a smaller plate — portion follows automatically.'], $lang);
    }
    if (in_array('screen_always', $issues) || in_array('screen_often', $issues)) {
        $bullets[] = psPick(['ar'=>'لا تأكل أمام الشاشة. تفقد 30% من إحساس الشبع بسبب فقدان الانتباه.', 'fr'=>'Ne mangez pas devant un écran. Vous perdez 30% de votre sensation de satiété par défaut d\'attention.', 'en'=>'Don\'t eat in front of a screen. You lose 30% of satiety signaling from inattention.'], $lang);
    }
    if (in_array('late_daily', $issues) || in_array('late_freq', $issues)) {
        $bullets[] = psPick(['ar'=>'آخر وجبة قبل 3 ساعات من النوم. الأكل المتأخّر يربك إيقاع الساعة البيولوجية ويزيد الدهون البطنية.', 'fr'=>'Dernier repas 3 h avant de dormir. Manger tard dérègle l\'horloge biologique et augmente la graisse abdominale.', 'en'=>'Last meal 3 hours before sleep. Late eating disrupts circadian rhythm and increases belly fat.'], $lang);
    }
    if (in_array('fastfood_freq', $issues) || in_array('fastfood_some', $issues)) {
        $bullets[] = psPick(['ar'=>'حضّر « sandwich beurre » محلي يوم الجمعة لكامل الأسبوع. وفّر مال وصحة.', 'fr'=>'Préparez vos sandwichs maison le vendredi pour la semaine. Économisez argent et santé.', 'en'=>'Prep your own sandwiches on Friday for the week. Save money and health.'], $lang);
    }
    if (in_array('bread_excess', $issues)) {
        $bullets[] = psPick(['ar'=>'الخبز ليس عدوّاً لكن 5+ أرغفة يومياً = 2000 سعرة من الكربوهيدرات وحدها. قلّل تدريجياً.', 'fr'=>'Le pain n\'est pas l\'ennemi, mais 5+ pains/jour = 2000 kcal de glucides seuls. Réduisez progressivement.', 'en'=>'Bread isn\'t the enemy, but 5+ loaves/day = 2000 kcal of pure carbs. Reduce gradually.'], $lang);
    }
    if (in_array('sugar_excess', $issues) || in_array('sugar_high', $issues)) {
        $bullets[] = psPick(['ar'=>'الشاي بـ 6 ملاعق سكر = 96 سعرة. 5 أكواب/يوم = نصف كيلو سكر/أسبوع. جرّب الستيفيا أو القرفة.', 'fr'=>'Thé à 6 cuillères de sucre = 96 kcal. 5 verres/jour = 500 g de sucre/semaine. Essayez la stévia ou la cannelle.', 'en'=>'Tea with 6 spoons sugar = 96 kcal. 5 cups/day = 500 g sugar/week. Try stevia or cinnamon.'], $lang);
    }
    if (in_array('frying_dominant', $issues)) {
        $bullets[] = psPick(['ar'=>'القلي يضاعف السعرات. استبدله بالفرن أو البخار لنفس الطعم تقريباً.', 'fr'=>'La friture double les calories. Remplacez par le four ou la vapeur pour un goût quasi-identique.', 'en'=>'Frying doubles calories. Switch to oven or steam for almost the same taste.'], $lang);
    }

    return [
        'level' => $level,
        'title' => psPick(['ar'=>'سلوكياتك الغذائية تحتاج تعديلات', 'fr'=>'Vos comportements alimentaires demandent des ajustements', 'en'=>'Your eating behaviors need adjustments'], $lang),
        'text'  => psPick(['ar'=>'ليس "ماذا" تأكل فقط، بل "كيف". الدراسات تظهر أنّ السلوكيات (سرعة، حجم، انتباه، توقيت) تشرح حتى 40% من فرق الوزن بين شخصين يأكلان نفس الطعام.', 'fr'=>'Ce n\'est pas seulement « quoi » mais « comment » vous mangez. Les études montrent que les comportements (vitesse, taille, attention, horaire) expliquent jusqu\'à 40 % de la différence de poids entre deux personnes mangeant la même nourriture.', 'en'=>'It\'s not just "what" but "how" you eat. Studies show behaviors (speed, size, attention, timing) explain up to 40% of weight differences between two people eating the same food.'], $lang),
        'bullets' => $bullets,
    ];
}

// ─── Sleep apnea ─────────────────────────────────────────────────────
function adviceApnea($class, $lang) {
    if ($class === 'Faible') return null;
    $level = ($class === 'Élevé') ? 'danger' : 'warning';
    return [
        'level' => $level,
        'title' => psPick(['ar'=>'خطر محتمل لانقطاع النفس النومي', 'fr'=>'Risque possible d\'apnée du sommeil', 'en'=>'Possible sleep apnea risk'], $lang),
        'text'  => psPick(['ar'=>'الشخير القوي + نعاس النهار + الوزن الزائد = ثالوث انقطاع النفس النومي. هذه الحالة تربك الهرمونات وتزيد الوزن، فيدخلك في حلقة. يمكن علاجها.', 'fr'=>'Ronflement fort + somnolence diurne + surpoids = trio évoquant l\'apnée du sommeil. Cette condition perturbe les hormones et favorise la prise de poids, créant un cercle vicieux. Elle se traite.', 'en'=>'Loud snoring + daytime sleepiness + excess weight = sleep apnea trio. This disrupts hormones and promotes weight gain, creating a vicious cycle. It\'s treatable.'], $lang),
        'bullets' => [
            psPick(['ar'=>'استشر طبيب أنف وأذن وحنجرة أو طبيب نوم: قد تحتاج تخطيط نوم (polysomnographie).', 'fr'=>'Consultez ORL ou médecin du sommeil : polysomnographie possible.', 'en'=>'See an ENT or sleep doctor: polysomnography may be needed.'], $lang),
            psPick(['ar'=>'نم على جنبك (لا على ظهرك). ارفع رأس السرير 10 سم.', 'fr'=>'Dormez sur le côté (pas sur le dos). Surélevez la tête du lit de 10 cm.', 'en'=>'Sleep on your side (not back). Raise head of bed by 10 cm.'], $lang),
            psPick(['ar'=>'فقدان 5-10% من الوزن يخفض الشخير وانقطاع النفس بنسبة 50% في معظم الحالات.', 'fr'=>'Perdre 5-10 % du poids réduit ronflement et apnées de 50 % dans la plupart des cas.', 'en'=>'Losing 5-10% of weight cuts snoring and apnea by 50% in most cases.'], $lang),
        ],
    ];
}

// ─── Medication-induced weight risk ──────────────────────────────────
function adviceMedRisk($class, $lang) {
    if ($class === 'Faible') return null;
    $level = ($class === 'Élevé') ? 'warning' : 'info';
    return [
        'level' => $level,
        'title' => psPick(['ar'=>'بعض أدويتك قد تؤثر على وزنك', 'fr'=>'Certains de vos médicaments peuvent influencer votre poids', 'en'=>'Some of your medications may affect your weight'], $lang),
        'text'  => psPick(['ar'=>'الكورتيكوستيرويدات، بعض مضادات الاكتئاب ومضادات الذهان، والحبوب الهرمونية قد تسبّب زيادة وزن. لا توقف أي دواء وحدك، لكن ناقش مع طبيبك البدائل المتاحة.', 'fr'=>'Corticoïdes, certains antidépresseurs/antipsychotiques et contraception hormonale peuvent causer une prise de poids. N\'arrêtez aucun médicament sans avis médical, mais discutez des alternatives.', 'en'=>'Corticosteroids, some antidepressants/antipsychotics, and hormonal contraception can cause weight gain. Never stop medication alone — but discuss alternatives with your doctor.'], $lang),
        'bullets' => [
            psPick(['ar'=>'دوّن وزنك شهرياً منذ بدء الدواء. اعرض الرسم البياني على طبيبك.', 'fr'=>'Pesez-vous mensuellement depuis le début du traitement. Montrez la courbe à votre médecin.', 'en'=>'Weigh monthly since starting the drug. Show the trend to your doctor.'], $lang),
            psPick(['ar'=>'بعض البدائل تكافئ نفس الفعالية مع تأثير وزني أقل. مثلاً ضمن مضادات الاكتئاب: السيرترالين عوض الباروكسيتين.', 'fr'=>'Des alternatives à efficacité équivalente existent avec moins d\'effet pondéral. Exemple : sertraline plutôt que paroxétine.', 'en'=>'Equally effective alternatives exist with less weight effect. E.g., sertraline instead of paroxetine.'], $lang),
            psPick(['ar'=>'لا تقلّل الجرعة بمفردك أبداً. تأثير الدواء على المرض الأصلي قد يكون أهم من تأثيره على الوزن.', 'fr'=>'Ne baissez jamais la dose seul(e). L\'effet du médicament sur la maladie de base peut primer sur celui sur le poids.', 'en'=>'Never lower the dose alone. The drug\'s effect on the underlying condition may matter more than its weight impact.'], $lang),
        ],
    ];
}

// ════════════════════════════════════════════════════════════════════
// V3 ADVICE — SES, puberté, FFQ DZ, tabac, jetlag, sitting, abx
// ════════════════════════════════════════════════════════════════════

// ─── SES advice ─────────────────────────────────────────────────────
function adviceSES($class, $food_insecure, $lang) {
    if ($class === 'High SES' && !$food_insecure) return null;

    if ($food_insecure) {
        return [
            'level' => 'warning',
            'title' => psPick(['ar'=>'الأمن الغذائي يستحق اهتماماً', 'fr'=>'Votre sécurité alimentaire demande attention', 'en'=>'Your food security deserves attention'], $lang),
            'text'  => psPick(['ar'=>'تخطّي الوجبات بسبب نقص الموارد ليس عيباً منك، بل واقعاً يجب التعامل معه بذكاء. الجسم في حالة نقص يخزّن الدهون لاحقاً (paradox الجوع/السمنة).', 'fr'=>'Sauter des repas par manque de moyens n\'est pas votre faute, mais une réalité à gérer intelligemment. Un corps en restriction stocke ensuite plus de graisse (paradoxe faim/obésité).', 'en'=>'Skipping meals due to limited resources isn\'t your fault — it\'s a reality to manage smartly. A deprived body later stores more fat (hunger/obesity paradox).'], $lang),
            'bullets' => [
                psPick(['ar'=>'الأطعمة الأرخص الأكثر إشباعاً: عدس، حمّص، فول، بيض، خبز الشعير، حليب مجفّف، علب التونة، خضار موسمية. مغذّية أكثر من الفاست فود.', 'fr'=>'Aliments à bas coût les plus rassasiants : lentilles, pois chiches, fèves, œufs, pain d\'orge, lait en poudre, thon en conserve, légumes de saison. Plus nourrissants que le fast food.', 'en'=>'Cheapest most-filling foods: lentils, chickpeas, fava beans, eggs, barley bread, powdered milk, canned tuna, seasonal vegetables. More nourishing than fast food.'], $lang),
                psPick(['ar'=>'لا تتخطّى الإفطار حتى لو كان كأس شاي + خبز + زيت زيتون. الجسم الجائع في الصباح يخزّن السعرات في وجبات اليوم.', 'fr'=>'Ne sautez pas le petit-déjeuner même s\'il est juste thé + pain + huile d\'olive. Un corps affamé le matin stocke les calories des repas suivants.', 'en'=>'Never skip breakfast even if only tea + bread + olive oil. A hungry-morning body stores calories from later meals.'], $lang),
                psPick(['ar'=>'الجزائر تدعم خدمة التغذية المدرسية. اسأل المعلّم/المرشد عن الإمكانيات المتاحة لو احتجت.', 'fr'=>'L\'Algérie soutient la cantine scolaire. Renseignez-vous auprès du conseiller scolaire pour les aides disponibles.', 'en'=>'Algeria supports school meal programs. Ask your school counselor about available assistance.'], $lang),
            ],
        ];
    }

    if ($class === 'Low SES' || $class === 'Very low SES') {
        return [
            'level' => 'info',
            'title' => psPick(['ar'=>'النمط الغذائي والوضع الاقتصادي', 'fr'=>'Alimentation et statut économique', 'en'=>'Diet and economic status'], $lang),
            'text'  => psPick(['ar'=>'في الدول الانتقالية مثل الجزائر، الفئات منخفضة الدخل في المدن أكثر عرضة للسمنة بسبب الإغراق بالأطعمة فائقة المعالجة الرخيصة. لكن نمط حياتك ليس قدراً.', 'fr'=>'Dans les pays en transition comme l\'Algérie, les milieux à faible revenu en milieu urbain sont plus exposés à l\'obésité à cause de l\'inondation de produits ultra-transformés bon marché. Mais votre mode de vie n\'est pas une fatalité.', 'en'=>'In transitioning countries like Algeria, lower-income urban settings face more obesity risk due to cheap ultra-processed food flooding. But your lifestyle isn\'t fate.'], $lang),
            'bullets' => [
                psPick(['ar'=>'الأطعمة التقليدية الجزائرية الحقيقية (كسكسي بالخضار، مرقة، عدس، حمص، حريرة بسيطة) أرخص وأصحّ من أي بديل مصنّع.', 'fr'=>'La vraie cuisine algérienne traditionnelle (couscous légumes, marqa, lentilles, harira simple) est moins chère et plus saine que n\'importe quel substitut industriel.', 'en'=>'Authentic Algerian home cooking (vegetable couscous, marqa, lentils, simple harira) is cheaper AND healthier than any industrial substitute.'], $lang),
                psPick(['ar'=>'الفاست فود ليس وفراً اقتصادياً: 250 دج للسندويتش = 5 وجبات منزلية صحّية بنفس المبلغ.', 'fr'=>'Le fast food n\'est PAS une économie : 250 DA un sandwich = 5 repas maison sains pour le même prix.', 'en'=>'Fast food is NOT a saving: 250 DA per sandwich = 5 healthy home meals for the same price.'], $lang),
                psPick(['ar'=>'الرياضة المجانية موجودة: المشي، الجري، تمارين الجسم في المنزل، كرة القدم في الحي.', 'fr'=>'Le sport gratuit existe : marche, course, exercices au poids du corps, foot de quartier.', 'en'=>'Free sport exists: walking, running, bodyweight exercises, neighborhood football.'], $lang),
            ],
        ];
    }

    return null;
}

// ─── Puberty timing advice ──────────────────────────────────────────
function advicePuberty($timing, $class, $reason, $lang) {
    if ($timing === 'normal') return null;

    if ($timing === 'early') {
        return [
            'level' => 'warning',
            'title' => psPick(['ar'=>'بلوغ مبكّر — اهتمام إضافي بنمط الحياة', 'fr'=>'Puberté précoce — vigilance accrue sur le mode de vie', 'en'=>'Early puberty — extra lifestyle vigilance'], $lang),
            'text'  => psPick(['ar'=>'البلوغ المبكّر يرتبط علمياً بزيادة خطر السمنة ومتلازمة الأيض في البلوغ. هذا لا يعني الكارثة، لكنّ نمط حياتك يجب أن يكون أكثر انضباطاً من المتوسط.', 'fr'=>'Une puberté précoce est associée à un risque accru d\'obésité et de syndrome métabolique à l\'âge adulte. Ce n\'est pas une catastrophe, mais votre hygiène de vie doit être plus rigoureuse que la moyenne.', 'en'=>'Early puberty is associated with higher adult obesity and metabolic syndrome risk. Not a catastrophe, but your lifestyle must be stricter than average.'], $lang),
            'bullets' => [
                psPick(['ar'=>'فحوصات سنوية بعد سن 16: ضغط، سكّر صائم، كوليسترول. هذا فحص بسيط ومجاني.', 'fr'=>'Bilan annuel après 16 ans : tension, glycémie à jeun, cholestérol. Examen simple et gratuit.', 'en'=>'Annual screening after age 16: BP, fasting glucose, cholesterol. Simple and free.'], $lang),
                psPick(['ar'=>'تجنّب الحميات الصارمة في هذه المرحلة. الجسم لا يزال ينمو. ركّز على نوعية الطعام، لا الكمية.', 'fr'=>'Évitez les régimes restrictifs à cet âge. Le corps grandit encore. Privilégiez la qualité plutôt que la quantité.', 'en'=>'Avoid restrictive diets at this age. Body is still growing. Focus on food quality, not quantity.'], $lang),
                psPick(['ar'=>'النشاط البدني ضروري لتوازن الهرمونات في فترة البلوغ المبكّر.', 'fr'=>'L\'activité physique est essentielle pour l\'équilibre hormonal en puberté précoce.', 'en'=>'Physical activity is essential for hormonal balance in early puberty.'], $lang),
            ],
        ];
    }

    if ($timing === 'late') {
        return [
            'level' => 'info',
            'title' => psPick(['ar'=>'بلوغ متأخّر — التغذية الكافية ضرورية', 'fr'=>'Puberté tardive — nutrition adéquate cruciale', 'en'=>'Late puberty — adequate nutrition is critical'], $lang),
            'text'  => psPick(['ar'=>'البلوغ المتأخّر قد يكون طبيعياً وراثياً، أو علامة على نقص تغذية مزمن. لا تقم بحميات في هذه المرحلة.', 'fr'=>'La puberté tardive peut être génétiquement normale, ou indiquer une sous-nutrition chronique. Ne suivez pas de régime maintenant.', 'en'=>'Late puberty may be genetically normal or signal chronic undernutrition. Don\'t diet now.'], $lang),
            'bullets' => [
                psPick(['ar'=>'تناول 3 وجبات + 1-2 سناك يومياً. الجسم يحتاج لإكمال نموّه.', 'fr'=>'Mangez 3 repas + 1-2 collations par jour. Le corps doit compléter sa croissance.', 'en'=>'Eat 3 meals + 1-2 snacks daily. Body must complete growth.'], $lang),
                psPick(['ar'=>'لو لم تأت الدورة عند 16 سنة، أو لم يتغيّر صوتك عند 16 سنة، استشر طبيباً للتأكّد.', 'fr'=>'Si pas de règles à 16 ans, ou pas de mue à 16 ans : consultez un médecin pour vérification.', 'en'=>'No menstruation by 16, or no voice change by 16: consult a doctor for evaluation.'], $lang),
            ],
        ];
    }

    return null;
}

// ─── Algerian dietary excess advice ─────────────────────────────────
function adviceDZDiet($score, $class, $flags, $lang) {
    if ($class === 'Équilibré') return null;
    $level = ($class === 'Très élevé') ? 'danger' : ($class === 'Élevé' ? 'warning' : 'info');

    $bullets = [];
    if (in_array('tea_excess', $flags) || in_array('tea_high', $flags)) {
        $bullets[] = psPick(['ar'=>'الشاي بالنعناع تقليد جميل، لكن 5 أكواب × 4 ملاعق سكّر = 320 سعرة من السكّر النقي. جرّب الشاي بدون سكّر أو بنصف الكمية.', 'fr'=>'Le thé à la menthe est une belle tradition, mais 5 verres × 4 c. de sucre = 320 kcal de sucre pur. Essayez sans sucre ou réduisez de moitié.', 'en'=>'Mint tea is a beautiful tradition, but 5 glasses × 4 spoons of sugar = 320 kcal of pure sugar. Try unsweetened or halve it.'], $lang);
    }
    if (in_array('psv_dz_zlabia', $flags)) {
        $bullets[] = psPick(['ar'=>'الزلابية والمقروط والقلب اللوز = حلويات للمناسبات، ليس للروتين اليومي. قطعة واحدة = 200-300 سعرة و 30غ سكّر.', 'fr'=>'Zlabia, makroud, kalb el louz = pâtisseries d\'occasion, pas du quotidien. Une pièce = 200-300 kcal et 30 g de sucre.', 'en'=>'Zlabia, makroud, kalb el louz = occasion sweets, not daily. One piece = 200-300 kcal and 30 g sugar.'], $lang);
    }
    if (in_array('psv_dz_bourek', $flags) || in_array('psv_dz_kefta_fry', $flags)) {
        $bullets[] = psPick(['ar'=>'البوراك المقلي والكفتة المقلية يستحقّان النسخة المخبوزة. نفس الطعم، نصف السعرات.', 'fr'=>'Bourek et boulettes frites méritent leur version au four. Même goût, moitié des calories.', 'en'=>'Fried bourek and meatballs deserve their oven version. Same taste, half the calories.'], $lang);
    }
    if (in_array('psv_dz_mhajeb', $flags)) {
        $bullets[] = psPick(['ar'=>'المسمن/المحاجب مع العسل أو السكّر يومياً = 600 سعرة في وجبة واحدة. اجعله مناسبة لا روتين.', 'fr'=>'Msemen/mhajeb au miel ou sucre tous les jours = 600 kcal en un repas. Faites-en une occasion.', 'en'=>'Daily msemen/mhajeb with honey or sugar = 600 kcal in one meal. Make it occasional.'], $lang);
    }
    if (in_array('psv_dz_lham_dlou', $flags)) {
        $bullets[] = psPick(['ar'=>'لحم بالحلو والكسكسي الدسم بقدر، لكن الإكثار يجعل الكبد دهنياً ويزيد الكوليسترول.', 'fr'=>'Lham hlou et couscous gras avec modération ; l\'excès rend le foie gras et augmente le cholestérol.', 'en'=>'Lham hlou and fatty couscous in moderation; excess fattens the liver and raises cholesterol.'], $lang);
    }
    $bullets[] = psPick(['ar'=>'الكسكسي بالخضار، حريرة بسيطة، شخشوخة بالعدس = الجزائر الحقيقية، طعام صحّي بشكل لا يصدّق.', 'fr'=>'Couscous légumes, harira simple, chakhchoukha aux lentilles = la vraie Algérie, nourriture incroyablement saine.', 'en'=>'Vegetable couscous, simple harira, chakhchoukha with lentils = the real Algeria, incredibly healthy food.'], $lang);

    return [
        'level' => $level,
        'title' => psPick(['ar'=>'النمط الجزائري يحتاج توازناً', 'fr'=>'Votre profil culinaire algérien à rééquilibrer', 'en'=>'Algerian dietary pattern needs balancing'], $lang),
        'text'  => psPick(['ar'=>'المطبخ الجزائري التقليدي صحّي جداً في أصله، لكن النسخة الحديثة (الإفراط في القلي، الحلويات، الشاي المحلّى) تنحرف عن الأصل. العودة للجذور = العودة للصحّة.', 'fr'=>'La cuisine algérienne traditionnelle est très saine à l\'origine, mais la version moderne (excès de friture, pâtisseries, thé sucré) s\'en éloigne. Revenir aux racines = revenir à la santé.', 'en'=>'Traditional Algerian cuisine is very healthy at its root, but the modern version (over-frying, pastries, sweet tea) drifts away. Returning to roots = returning to health.'], $lang),
        'bullets' => $bullets,
    ];
}

// ─── Smoking advice ─────────────────────────────────────────────────
function adviceSmoking($score, $class, $passive, $lang) {
    if ($class === 'Non-fumeur' && !$passive) return null;

    if ($class === 'Non-fumeur' && $passive) {
        return [
            'level' => 'warning',
            'title' => psPick(['ar'=>'التدخين السلبي يحيط بك', 'fr'=>'Vous êtes exposé(e) au tabagisme passif', 'en'=>'You are exposed to passive smoking'], $lang),
            'text'  => psPick(['ar'=>'استنشاق دخان الآخرين يؤذيك تقريباً مثل التدخين المباشر. الأطفال والمراهقون أكثر تأثّراً.', 'fr'=>'Inhaler la fumée d\'autrui vous nuit presque autant que fumer. Enfants et adolescents sont plus vulnérables.', 'en'=>'Breathing others\' smoke harms you almost as much as smoking. Children and teens are more vulnerable.'], $lang),
            'bullets' => [
                psPick(['ar'=>'تحدّث بلطف مع المدخّن في عائلتك: « دخانك يصل إلى رئتي رغم محبّتي لك ».', 'fr'=>'Parlez doucement au fumeur de la famille : « ta fumée atteint mes poumons malgré mon affection ».', 'en'=>'Speak gently to the smoker at home: "your smoke reaches my lungs despite my love."'], $lang),
                psPick(['ar'=>'لا تسمح بالتدخين في غرفتك أو سيّارة العائلة.', 'fr'=>'N\'autorisez pas la fumée dans votre chambre ou la voiture familiale.', 'en'=>'Don\'t allow smoking in your room or the family car.'], $lang),
            ],
        ];
    }

    $level = ($class === 'Risque élevé') ? 'danger' : 'warning';
    return [
        'level' => $level,
        'title' => psPick(['ar'=>'⚠ التدخين يضرّ صحّتك (وقد يضرّ وزنك أيضاً لاحقاً)', 'fr'=>'⚠ Le tabac nuit à votre santé (et à votre poids plus tard)', 'en'=>'⚠ Smoking harms your health (and your weight later)'], $lang),
        'text'  => psPick(['ar'=>'التدخين يبدو في البداية أنّه يقلّل الشهيّة، لكن عند الإقلاع يستعيد الجسم 5-10 كغ في المتوسط. إضافة إلى الضرر القلبي والرئوي. الإقلاع كلما كان أبكر، كان أسهل.', 'fr'=>'Le tabac semble couper l\'appétit au début, mais à l\'arrêt le corps reprend 5-10 kg en moyenne. S\'ajoutent les dégâts cardio-pulmonaires. Plus on arrête tôt, plus c\'est facile.', 'en'=>'Smoking seems to suppress appetite initially, but on quitting people regain 5-10 kg on average. Plus heart/lung damage. Earlier you quit, easier it gets.'], $lang),
        'bullets' => [
            psPick(['ar'=>'الشيشة جلسة واحدة = دخان 100 سيجارة (دراسة WHO). ليست بديلاً صحّياً.', 'fr'=>'Une chicha = fumée de 100 cigarettes (étude OMS). Pas un substitut sain.', 'en'=>'One shisha session = smoke from 100 cigarettes (WHO). Not a healthy substitute.'], $lang),
            psPick(['ar'=>'الفيب يحتوي نيكوتين مركّز يخلق إدماناً أسرع من السجائر. ليس "أمناً".', 'fr'=>'Le vape contient une nicotine concentrée qui crée l\'addiction plus vite que la cigarette. Pas "safe".', 'en'=>'Vapes contain concentrated nicotine creating addiction faster than cigarettes. Not "safe".'], $lang),
            psPick(['ar'=>'في الجزائر: استشارات إقلاع عن التدخين مجانية في عيادات الـ Tabacologie. اطلب من طبيبك التحويل.', 'fr'=>'En Algérie : consultations sevrage gratuites en tabacologie. Demandez l\'orientation à votre médecin.', 'en'=>'In Algeria: free smoking-cessation consultations in tabacology clinics. Ask your doctor for referral.'], $lang),
            psPick(['ar'=>'بديل صحّي للضغط النفسي: المشي، الرياضة، الموسيقى، التنفّس البطني (4-7-8).', 'fr'=>'Alternative saine au stress : marche, sport, musique, respiration abdominale (4-7-8).', 'en'=>'Healthy stress alternative: walking, sport, music, belly breathing (4-7-8).'], $lang),
        ],
    ];
}

// ─── Social jetlag advice ───────────────────────────────────────────
function adviceJetlag($hours, $class, $lang) {
    if ($hours === null || $class === 'Faible' || $class === 'Non évalué') return null;
    $level = ($class === 'Élevé') ? 'danger' : 'warning';
    return [
        'level' => $level,
        'title' => psPick(['ar'=>'إيقاع نومك بين الأسبوع والعطلة غير متوازن', 'fr'=>'Votre rythme de sommeil semaine/week-end est déséquilibré', 'en'=>'Your weekday/weekend sleep rhythm is unbalanced'], $lang),
        'text'  => psPick(['ar'=>"النوم 5 ساعات في أيام الدراسة و11 في العطلة = jetlag اجتماعي يخلّ بالساعة البيولوجية. الجسم يعتقد أنّك تسافر إلى دولة أخرى كل أسبوع، فيرتبك أيضك ويرفع خطر السمنة (Roenneberg 2012).", 'fr'=>"Dormir 5 h en semaine et 11 h le week-end = jetlag social qui dérègle l'horloge biologique. Le corps croit voyager chaque semaine et déstabilise le métabolisme, augmentant le risque d'obésité (Roenneberg 2012).", 'en'=>"Sleeping 5 h on weekdays and 11 h on weekends = social jetlag that disrupts the body clock. Your body thinks it travels every week, destabilizing metabolism and raising obesity risk (Roenneberg 2012)."], $lang),
        'bullets' => [
            psPick(['ar'=>'حافظ على فرق أقلّ من ساعة بين نوم الأسبوع والعطلة. هذا الفرق هو الـ jetlag الاجتماعي.', 'fr'=>'Gardez moins d\'1 h d\'écart entre semaine et week-end. C\'est ce gap qu\'on appelle jetlag social.', 'en'=>'Keep less than 1 h gap between weekday and weekend sleep. That gap is the "social jetlag."'], $lang),
            psPick(['ar'=>'لو نمت متأخّراً ليلة الخميس، استيقظ في موعدك المعتاد ثم نم قيلولة 20 دقيقة بعد الظهر.', 'fr'=>'Si vous vous couchez tard jeudi soir, levez-vous à l\'heure habituelle puis sieste de 20 min après-midi.', 'en'=>'If you sleep late Thursday night, wake at usual time then nap 20 min in the afternoon.'], $lang),
            psPick(['ar'=>'15 دقيقة من ضوء الصباح كل يوم تساعد على ضبط الساعة البيولوجية.', 'fr'=>'15 min de lumière du matin chaque jour aident à recaler l\'horloge biologique.', 'en'=>'15 min of morning light each day helps reset the body clock.'], $lang),
        ],
    ];
}

// ─── Non-screen sitting advice ──────────────────────────────────────
function adviceSitting($hours, $class, $lang) {
    if ($class === 'Faible') return null;
    $level = ($class === 'Très élevé') ? 'danger' : 'warning';
    return [
        'level' => $level,
        'title' => psPick(['ar'=>'الجلوس الطويل خارج الشاشة', 'fr'=>'Trop d\'heures assis hors écran', 'en'=>'Too many hours sitting outside screens'], $lang),
        'text'  => psPick(['ar'=>"تجلس حوالي $hours ساعات إضافية يومياً (دراسة، مواصلات، دروس) خارج وقت الشاشة. الجلوس المتواصل ولو بدون شاشة يُبطئ الأيض ويزيد دهون البطن.", 'fr'=>"Vous restez assis environ $hours h supplémentaires par jour (cours, transport, étude) hors écran. La position assise prolongée ralentit le métabolisme et augmente la graisse abdominale.", 'en'=>"You sit about $hours additional hours daily (class, transport, study) outside screens. Continuous sitting slows metabolism and increases belly fat."], $lang),
        'bullets' => [
            psPick(['ar'=>'انهض كل 30 دقيقة لمدّة 2-3 دقائق. تمدّد، اشرب ماء، انزل درجاً.', 'fr'=>'Levez-vous toutes les 30 min pendant 2-3 min. Étirez, buvez de l\'eau, descendez un escalier.', 'en'=>'Stand up every 30 min for 2-3 min. Stretch, drink water, go down a staircase.'], $lang),
            psPick(['ar'=>'ادرس واقفاً 15 دقيقة من كل ساعة. (طاولة عالية أو حامل كتب فوق طاولة)', 'fr'=>'Étudiez debout 15 min/heure. (bureau haut ou support de livres sur table)', 'en'=>'Study standing 15 min/hour. (high desk or book stand on table)'], $lang),
            psPick(['ar'=>'استبدل جزء من المواصلات بالمشي إذا ممكن: حتى محطّة سابقة أو لاحقة.', 'fr'=>'Remplacez une partie du transport par la marche si possible : descendez un arrêt avant.', 'en'=>'Replace part of commute with walking if possible: get off one stop earlier.'], $lang),
        ],
    ];
}

// ─── Antibiotic exposure advice ─────────────────────────────────────
function adviceAntibiotic($score, $class, $unknown, $lang) {
    if ($class === 'Faible' && !$unknown) return null;

    if ($score === 0 && $unknown) {
        return [
            'level' => 'info',
            'title' => psPick(['ar'=>'تاريخك مع المضادّات الحيوية مجهول', 'fr'=>'Votre historique antibiotique est inconnu', 'en'=>'Your antibiotic history is unknown'], $lang),
            'text'  => psPick(['ar'=>'لو ممكن، اسأل والديك عن مرّات تناولك للمضادّات قبل سنّ السنتين. هذه معلومة طبية مفيدة طوال حياتك.', 'fr'=>'Si possible, demandez à vos parents combien d\'antibiotiques avant vos 2 ans. C\'est une information médicale utile à vie.', 'en'=>'If possible, ask your parents how many antibiotics before age 2. This is medical info useful for life.'], $lang),
            'bullets' => [],
        ];
    }

    $level = ($class === 'Élevé') ? 'warning' : 'info';
    return [
        'level' => $level,
        'title' => psPick(['ar'=>'تأثير المضادّات الحيوية على ميكروبيوم الأمعاء', 'fr'=>'Impact des antibiotiques sur votre microbiote intestinal', 'en'=>'Antibiotic impact on your gut microbiome'], $lang),
        'text'  => psPick(['ar'=>'الاستعمال المتكرّر للمضادّات يقتل البكتيريا النافعة. الدراسات الحديثة (Trasande 2013، Mueller 2015) تربطها بزيادة خطر السمنة لاحقاً. النبأ السار: الميكروبيوم قابل للترميم.', 'fr'=>'Les antibiotiques répétés tuent aussi les bonnes bactéries. Études récentes (Trasande 2013, Mueller 2015) : lien avec risque d\'obésité ultérieur. Bonne nouvelle : le microbiote se restaure.', 'en'=>'Repeated antibiotics also kill good bacteria. Recent studies (Trasande 2013, Mueller 2015) link them to later obesity risk. Good news: microbiome can recover.'], $lang),
        'bullets' => [
            psPick(['ar'=>'أطعمة غنيّة بالبروبيوتيك: لبن، رايب، ياغورت طبيعي (غير محلّى)، مخلّلات بدون خل صناعي.', 'fr'=>'Aliments probiotiques : leben, raïb, yaourt nature, légumes lacto-fermentés (sans vinaigre industriel).', 'en'=>'Probiotic foods: leben, raïb, plain yogurt, lacto-fermented vegetables (no industrial vinegar).'], $lang),
            psPick(['ar'=>'الألياف غذاء البكتيريا النافعة: الحمّص، العدس، الفول، الخضار المتنوّعة، الفواكه بقشرها.', 'fr'=>'Les fibres nourrissent les bonnes bactéries : pois chiches, lentilles, fèves, légumes variés, fruits avec peau.', 'en'=>'Fiber feeds good bacteria: chickpeas, lentils, fava beans, varied vegetables, fruits with peel.'], $lang),
            psPick(['ar'=>'لا تأخذ مضادّاً حيوياً دون وصفة طبية. التهاب الحلق العادي والزكام لا يحتاجان مضادّات (فيروسية).', 'fr'=>'Ne prenez pas d\'antibiotique sans ordonnance. Mal de gorge et rhume = viral, pas besoin d\'antibiotiques.', 'en'=>'Don\'t take antibiotics without prescription. Sore throat and cold are viral — no antibiotics needed.'], $lang),
        ],
    ];
}
