-- ════════════════════════════════════════════════════════════════
-- MIGRATION SQL — Ajout des indicateurs screen_max et screen_sum
-- ────────────────────────────────────────────────────────────────
-- Ce script :
--   1. Ajoute les deux nouvelles colonnes à la table responses
--   2. Recalcule les valeurs pour TOUS les enregistrements existants
--   3. Vérifie la cohérence avec les anciennes valeurs
--
-- Conformément aux recommandations HBSC 2018 (Ng et al.) sur la
-- prise en compte du multi-screen behavior chez l'adolescent.
--
-- À exécuter UNE SEULE FOIS sur la base de production.
-- ════════════════════════════════════════════════════════════════

-- ─── ÉTAPE 1 : Ajouter les colonnes (si elles n'existent pas) ──
ALTER TABLE responses
  ADD COLUMN IF NOT EXISTS screen_sum DECIMAL(4,1) NULL
    COMMENT 'Heures écran cumulées (somme des 4 supports, h/jour) — secondaire'
    AFTER screen_hours_total,
  ADD COLUMN IF NOT EXISTS screen_max DECIMAL(3,1) NULL
    COMMENT 'Heures écran maximum sur un support unique (PRINCIPAL — HBSC 2018)'
    AFTER screen_sum;

-- ─── ÉTAPE 2 : Recalculer pour tous les enregistrements ────────
-- Mapping midpoints : <1h → 0.5 ; 1-2h → 1.5 ; 2-4h → 3.0 ; >4h → 5.0
-- Conformément à computeScreenHours() de computed_scores.php

UPDATE responses SET
  screen_sum = (
    CASE screen_phone    WHEN '<1h' THEN 0.5 WHEN '1-2h' THEN 1.5 WHEN '2-4h' THEN 3.0 WHEN '>4h' THEN 5.0 ELSE 0 END
  + CASE screen_tv       WHEN '<1h' THEN 0.5 WHEN '1-2h' THEN 1.5 WHEN '2-4h' THEN 3.0 WHEN '>4h' THEN 5.0 ELSE 0 END
  + CASE screen_games    WHEN '<1h' THEN 0.5 WHEN '1-2h' THEN 1.5 WHEN '2-4h' THEN 3.0 WHEN '>4h' THEN 5.0 ELSE 0 END
  + CASE screen_computer WHEN '<1h' THEN 0.5 WHEN '1-2h' THEN 1.5 WHEN '2-4h' THEN 3.0 WHEN '>4h' THEN 5.0 ELSE 0 END
  ),
  screen_max = GREATEST(
    CASE screen_phone    WHEN '<1h' THEN 0.5 WHEN '1-2h' THEN 1.5 WHEN '2-4h' THEN 3.0 WHEN '>4h' THEN 5.0 ELSE 0 END,
    CASE screen_tv       WHEN '<1h' THEN 0.5 WHEN '1-2h' THEN 1.5 WHEN '2-4h' THEN 3.0 WHEN '>4h' THEN 5.0 ELSE 0 END,
    CASE screen_games    WHEN '<1h' THEN 0.5 WHEN '1-2h' THEN 1.5 WHEN '2-4h' THEN 3.0 WHEN '>4h' THEN 5.0 ELSE 0 END,
    CASE screen_computer WHEN '<1h' THEN 0.5 WHEN '1-2h' THEN 1.5 WHEN '2-4h' THEN 3.0 WHEN '>4h' THEN 5.0 ELSE 0 END
  );

-- ─── ÉTAPE 3 : Vérification de cohérence ───────────────────────
-- Cette requête doit retourner :
--   - moyenne screen_sum proche de 6.45 h
--   - moyenne screen_max proche de 3.16 h
--   - 45.1% des élèves avec screen_max >= 4h
--   - 66.2% des élèves avec screen_sum >= 4h

SELECT
  COUNT(*) AS total_records,
  ROUND(AVG(screen_sum), 2) AS avg_screen_sum,
  ROUND(STDDEV(screen_sum), 2) AS sd_screen_sum,
  ROUND(AVG(screen_max), 2) AS avg_screen_max,
  ROUND(STDDEV(screen_max), 2) AS sd_screen_max,
  ROUND(100 * SUM(CASE WHEN screen_sum >= 4 THEN 1 ELSE 0 END) / COUNT(*), 1) AS pct_sum_ge_4h,
  ROUND(100 * SUM(CASE WHEN screen_max >= 4 THEN 1 ELSE 0 END) / COUNT(*), 1) AS pct_max_ge_4h
FROM responses;

-- ─── ÉTAPE 4 : Index pour performance ──────────────────────────
ALTER TABLE responses
  ADD INDEX IF NOT EXISTS idx_screen_max (screen_max),
  ADD INDEX IF NOT EXISTS idx_screen_sum (screen_sum);

-- ════════════════════════════════════════════════════════════════
-- FIN DE LA MIGRATION
-- ════════════════════════════════════════════════════════════════
