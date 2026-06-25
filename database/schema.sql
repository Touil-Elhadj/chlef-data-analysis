-- ════════════════════════════════════════════════════════════════
-- schema.sql — Chlef-Biostat-2026
-- Complete database schema for the trilingual (ar/fr/en) study.
-- Apply once on an empty database, then load seed.sql for the
-- default admin user.
-- ════════════════════════════════════════════════════════════════

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ── UTILISATEURS ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  role ENUM('admin','assistant','guest') DEFAULT 'assistant',
  last_login DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── TENTATIVES DE CONNEXION ───────────────────────────────
CREATE TABLE IF NOT EXISTS login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50),
  ip_address VARCHAR(45),
  success TINYINT(1) DEFAULT 0,
  attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_username (username),
  INDEX idx_ip (ip_address),
  INDEX idx_time (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── ASSIGNATIONS ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  range_start INT NOT NULL,
  range_end INT NOT NULL,
  note VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── JOURNAL D'AUDIT ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(50) NOT NULL,
  table_name VARCHAR(50) NULL,
  record_id INT NULL,
  details TEXT NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_action (action),
  INDEX idx_table (table_name),
  INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── NOTIFICATIONS / JALONS ────────────────────────────────
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type VARCHAR(50) DEFAULT 'count_milestone',
  threshold INT NOT NULL,
  sent_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Jalons prédéfinis
INSERT IGNORE INTO notifications (type, threshold) VALUES
  ('count_milestone', 100),
  ('count_milestone', 250),
  ('count_milestone', 500),
  ('count_milestone', 750),
  ('count_milestone', 1000),
  ('count_milestone', 1500),
  ('count_milestone', 2000),
  ('count_milestone', 2500),
  ('count_milestone', 3000),
  ('count_milestone', 3500);

-- ── RÉPONSES (TABLE PRINCIPALE) ───────────────────────────
CREATE TABLE IF NOT EXISTS responses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  questionnaire_num INT NOT NULL UNIQUE,
  entered_by INT NULL,
  entered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  is_validated TINYINT(1) DEFAULT 0,

  -- ═══ Section I — Données démographiques et anthropométriques ═══
  school VARCHAR(255) NULL,              -- Établissement scolaire
  commune VARCHAR(100) NULL,             -- Commune
  age DECIMAL(4,1) NULL,                 -- Q1. Âge (ans)
  sex VARCHAR(10) NULL,                  -- Q1. Sexe (Garçon/Fille)
  grade VARCHAR(10) NULL,                -- Q1. Niveau (1AS/2AS/3AS)
  height DECIMAL(5,1) NULL,              -- Q2. Taille (cm)
  weight DECIMAL(5,1) NULL,              -- Q2. Poids (kg)
  bmi DECIMAL(4,1) NULL,                 -- Q3. IMC calculé
  iotf_class VARCHAR(30) NULL,           -- Classification IOTF
  birth_weight DECIMAL(4,2) NULL,        -- Q4. Poids naissance (kg)
  delivery_type VARCHAR(30) NULL,        -- Q5. Type accouchement
  school_avg VARCHAR(20) NULL,           -- Q6. Moyenne scolaire (catégoriel)

  -- ═══ Section II — Perception corporelle ═══
  body_perception VARCHAR(50) NULL,      -- Q7. Perception du poids
  tried_lose_weight VARCHAR(10) NULL,    -- Q8. Essayé de perdre du poids
  lose_method VARCHAR(50) NULL,          -- Q8. Méthode (si oui)
  weight_variation VARCHAR(50) NULL,     -- Q9. Variation 12 mois
  weight_last VARCHAR(50) NULL,          -- Q10. Dernier poids rappelé
  want_weight_change VARCHAR(30) NULL,   -- Q15. Souhaite changer

  -- ═══ Section III — Antécédents médicaux ═══
  traditional_remedies VARCHAR(100) NULL, -- Q11. Remèdes traditionnels
  specialist_consult VARCHAR(100) NULL,   -- Q12. Consultation spécialiste
  medical_diagnosis VARCHAR(100) NULL,    -- Q13. Diagnostic médical
  smoker VARCHAR(30) NULL,                -- Q14. Tabagisme
  cig_per_day INT DEFAULT 0,             -- Q14. Cigarettes/jour

  -- ═══ Section IV — FFQ (23 aliments, index 0-6) ═══
  -- Échelle quotidienne (D): 0=Jamais|1=<1/sem|2=1/sem|3=2-4/sem|4=1/jour|5=2-3/jour|6=≥4/jour
  ffq_fruits_frais TINYINT NULL,         -- Q14. Fruits frais (D)
  ffq_legumes_crus TINYINT NULL,         -- Q16. Légumes crus (D)
  ffq_legumes_cuits TINYINT NULL,        -- Q17. Légumes cuits (D)
  ffq_pain TINYINT NULL,                 -- Q19. Pain & viennoiseries (D)
  ffq_riz_semoule TINYINT NULL,          -- Q20. Riz, semoule, pâtes (D)
  ffq_lait TINYINT NULL,                 -- Q23. Lait (D)
  ffq_yaourt TINYINT NULL,              -- Q24. Yaourts (D)
  ffq_huile_olive TINYINT NULL,          -- Q35. Huile d'olive (D)
  ffq_autres_huiles TINYINT NULL,        -- Q36. Autres huiles (D)

  -- Échelle hebdomadaire (W): 0=Jamais|1=<1/mois|2=1-3/mois|3=1/sem|4=2-4/sem|5=5-6/sem|6=1/jour
  ffq_fruits_secs TINYINT NULL,          -- Q15. Fruits secs (W)
  ffq_legumineuses TINYINT NULL,         -- Q18. Légumineuses (W)
  ffq_couscous TINYINT NULL,             -- Q21. Couscous (W)
  ffq_pommes_terre TINYINT NULL,         -- Q22. Pommes de terre (W)
  ffq_fromage TINYINT NULL,              -- Q25. Fromages (W)
  ffq_viande_rouge TINYINT NULL,         -- Q26. Viande rouge (W)
  ffq_volaille TINYINT NULL,             -- Q27. Volaille (W)
  ffq_poisson TINYINT NULL,              -- Q28. Poisson (W)
  ffq_oeufs TINYINT NULL,               -- Q29. Œufs (W)
  ffq_boissons_sucrees TINYINT NULL,     -- Q30. Boissons sucrées (W)
  ffq_gateaux TINYINT NULL,              -- Q31. Gâteaux, biscuits (W)
  ffq_chocolat TINYINT NULL,             -- Q32. Chocolat, bonbons (W)
  ffq_noix TINYINT NULL,                 -- Q33. Noix, oléagineux (W)
  ffq_beurre TINYINT NULL,              -- Q34. Beurre, margarine (W)

  -- ═══ Section V — Habitudes alimentaires ═══
  coffee_freq VARCHAR(30) NULL,          -- Q37. Fréquence café
  coffee_cups VARCHAR(30) NULL,          -- Q38. Tasses café/thé par jour
  sugary_freq VARCHAR(30) NULL,          -- Q39. Fréquence boissons sucrées
  energy_freq VARCHAR(30) NULL,          -- Q40. Fréquence boissons énergisantes
  water_intake VARCHAR(30) NULL,         -- Q41. Eau/jour
  snacking_freq VARCHAR(30) NULL,        -- Q42. Grignotage
  meals_per_day VARCHAR(10) NULL,        -- Q43. Repas/jour
  breakfast_freq VARCHAR(30) NULL,       -- Q44. Petit-déjeuner
  skip_meal_tutoring VARCHAR(30) NULL,   -- Q45. Saute repas — cours
  meal_replacement VARCHAR(50) NULL,     -- Q46. Remplacement
  snacks_outside VARCHAR(30) NULL,       -- Q47. Snacks hors école
  food_influence VARCHAR(30) NULL,       -- Q48. Influence alimentaire
  reads_labels VARCHAR(30) NULL,         -- Q49. Étiquettes
  fv_portions INT NULL,                  -- Q50. Portions F/L par jour
  school_canteen VARCHAR(30) NULL,       -- Q51. Cantine scolaire
  canteen_fv VARCHAR(30) NULL,           -- Q52. F/L à la cantine
  canteen_quality VARCHAR(30) NULL,      -- Q53. Qualité cantine

  -- ═══ Section VI — Sommeil et comportement émotionnel ═══
  wake_exhausted VARCHAR(30) NULL,       -- Q54. Réveil épuisé
  insomnia VARCHAR(30) NULL,             -- Q55. Insomnie
  nightmares VARCHAR(30) NULL,           -- Q56. Cauchemars
  emotional_eating VARCHAR(100) NULL,    -- Q57. Alimentation émotionnelle
  bedtime TIME NULL,                     -- Q58. Heure de coucher
  waketime TIME NULL,                    -- Q59. Heure de lever
  sleep_duration VARCHAR(10) NULL,       -- Q60. Durée sommeil
  screen_before_sleep VARCHAR(20) NULL,  -- Q61. Écrans avant sommeil

  -- ═══ Section VII — Activité physique ═══
  sports_club VARCHAR(10) NULL,          -- Q62. Club sportif
  sport_type VARCHAR(100) NULL,          -- Q62. Type de sport
  active_days_week TINYINT NULL,         -- Q63. Jours actifs ≥60min
  walk_to_school VARCHAR(20) NULL,       -- Q64. Marche école
  transport VARCHAR(20) NULL,            -- Q65. Transport
  sports_facilities VARCHAR(10) NULL,    -- Q66. Installations sportives
  nutrition_education VARCHAR(30) NULL,  -- Q67. Éducation nutritionnelle

  -- ═══ Section VIII — Environnement familial ═══
  meals_with_family VARCHAR(20) NULL,    -- Q68. Repas en famille
  parents_employment VARCHAR(50) NULL,   -- Q69. Emploi parents
  follows_nutrition_social VARCHAR(10) NULL, -- Q70. Réseaux sociaux nutrition
  mother_education VARCHAR(30) NULL,     -- Q71. Éducation mère

  -- Temps écran (Q73-Q76)
  screen_tv VARCHAR(10) NULL,            -- Q73. TV
  screen_games VARCHAR(10) NULL,         -- Q74. Jeux vidéo
  screen_computer VARCHAR(10) NULL,      -- Q75. Ordinateur
  screen_phone VARCHAR(10) NULL,         -- Q76. Téléphone

  -- ═══ Section IX — Antécédents familiaux ═══
  general_health VARCHAR(20) NULL,       -- Q76/77. Santé générale
  academic_stress VARCHAR(20) NULL,      -- Q78. Stress scolaire
  family_support VARCHAR(20) NULL,       -- Q79. Soutien familial
  peer_pressure_fastfood VARCHAR(20) NULL, -- Q80. Pression amis
  family_overweight VARCHAR(10) NULL,    -- Q81. Famille en surpoids
  parent_obese VARCHAR(10) NULL,         -- Q82. Parent obèse

  -- ═══ Section X — Connaissances nutritionnelles ═══
  knows_fv_portions VARCHAR(30) NULL,    -- Q83. Portions F/L recommandées
  knows_bmi VARCHAR(50) NULL,            -- Q84. Connaît l'IMC
  diet_balanced VARCHAR(30) NULL,        -- Q85. Alimentation équilibrée

  -- ═══ SCORES CALCULÉS AUTOMATIQUEMENT ═══
  score_sommeil TINYINT NULL,            -- Score qualité sommeil (0-13)
  classe_sommeil VARCHAR(20) NULL,       -- Bon/Perturbé/Mauvais
  score_activite TINYINT NULL,           -- Score activité physique (0-12)
  classe_activite VARCHAR(20) NULL,      -- Inactif/Peu actif/Actif/Très actif
  sleep_hours_real DECIMAL(4,2) NULL,    -- Heures réelles de sommeil
  score_kidmed TINYINT NULL,             -- Score KIDMED (-3 à +12)
  classe_kidmed VARCHAR(40) NULL,        -- Optimal/Amélioration/Mauvais
  score_protective TINYINT NULL,         -- Score aliments protecteurs
  score_risk TINYINT NULL,               -- Score aliments à risque
  ratio_diet DECIMAL(4,2) NULL,          -- Ratio protecteurs/risque
  dietary_diversity TINYINT NULL,        -- Diversité alimentaire (0-7)
  classe_diversity VARCHAR(20) NULL,     -- Faible/Moyen/Bon
  score_pression_scolaire TINYINT NULL,  -- Score pression scolaire (0-9)
  classe_pression VARCHAR(20) NULL,      -- Faible/Modérée/Élevée
  score_sedentarite TINYINT NULL,        -- Score sédentarité (0-15)
  classe_sedentarite VARCHAR(40) NULL,   -- Actif/Modérément sédentaire/Sédentaire
  score_famille TINYINT NULL,            -- Score famille (-3 à +8)
  classe_famille VARCHAR(20) NULL,       -- Défavorable/Neutre/Favorable
  obesity_risk_score TINYINT NULL,       -- Score risque obésité (0-13)
  obesity_risk_class VARCHAR(20) NULL,   -- Faible/Modéré/Élevé
  perception_gap VARCHAR(40) NULL,       -- Concordant/Sous-estime/Surestime
  screen_hours_total TINYINT NULL,       -- LEGACY : heures écran cumulées (entier, somme directe)
  screen_sum DECIMAL(4,1) NULL,          -- Heures écran cumulées (somme des 4 supports, h/jour)
  screen_max DECIMAL(3,1) NULL,          -- Heures écran maximum sur un support unique (PRINCIPAL — HBSC 2018)
  anemia_risk VARCHAR(20) NULL,          -- Risque anémie proxy
  vitd_risk VARCHAR(20) NULL,            -- Risque déficit Vit D proxy
  eating_disorder_risk VARCHAR(20) NULL, -- Risque trouble alimentaire proxy
  global_nutrition_score TINYINT NULL,   -- Score global nutrition (0-100)
  global_nutrition_class VARCHAR(20) NULL, -- Excellent/Bon/Moyen/Mauvais

  -- ═══ INDEX ═══
  INDEX idx_qnum (questionnaire_num),
  INDEX idx_iotf (iotf_class),
  INDEX idx_sex (sex),
  INDEX idx_date (entered_at),
  INDEX idx_school (school(100)),
  FOREIGN KEY (entered_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

