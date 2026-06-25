<?php

declare(strict_types=1);

namespace TouilElhadj\ChlefPlatform;

use TouilElhadj\BiostatPhp\BiostatAnalysis;

/**
 * Study-specific analyses for the Chlef adolescent-overweight survey.
 *
 * This class wraps the generic `biostat-php` library with the
 * domain-specific definitions and hypothesis tests of the underlying
 * master-thesis protocol (UHBC, Chlef 2025–2026):
 *
 *  • IOTF cut-off classes from Cole et al. (2000, 2007).
 *  • Helpers to count overweight (`OW`) and obese (`OB`) subjects.
 *  • Pre-registered hypothesis tests H1.1 to H3.2.
 *
 * The class deliberately does NOT inherit from BiostatAnalysis; it
 * composes it instead so the generic library stays decoupled from the
 * Chlef-specific outcomes.
 *
 * @author  Elhadj TOUIL <touilelhadj@live.com>
 * @license MIT
 */
class StudySpecificAnalyses
{
    /**
     * IOTF outcome — "Surpoids + Obésité" (recommended WHO grouping).
     */
    public const OUTCOME_OW_OB = ['Surpoids', 'Obésité'];

    /**
     * IOTF outcome — Obesity only.
     */
    public const OUTCOME_OB_ONLY = ['Obésité'];

    /**
     * IOTF outcome — Underweight subcategories.
     */
    public const OUTCOME_UW = [
        'Insuffisance pondérale',
        'Minceur grade 1',
        'Minceur grade 2',
        'Minceur grade 3',
    ];

    /**
     * IOTF outcome — Normal weight.
     */
    public const OUTCOME_NORMAL = ['Normal'];

    /**
     * Survey rows (associative arrays as returned by PDO).
     *
     * @var array<int, array<string, mixed>>
     */
    private array $data;

    /**
     * Generic statistical engine — biostat-php library.
     */
    private BiostatAnalysis $stats;

    /**
     * @param array<int, array<string, mixed>> $data row-oriented dataset
     */
    public function __construct(array $data)
    {
        $this->data  = $data;
        $this->stats = new BiostatAnalysis($data);
    }

    // ════════════════════════════════════════════════════════════════
    // IOTF classification helpers (Cole et al. 2000, 2007)
    // ════════════════════════════════════════════════════════════════

    /**
     * Test whether a subject is in the principal outcome (Surpoids + Obésité).
     *
     * @param mixed $iotf_class the iotf_class field from the survey row
     */
    public static function isOWOB($iotf_class): bool
    {
        return in_array($iotf_class, self::OUTCOME_OW_OB, true);
    }

    /**
     * Test whether a subject is in the strict obesity outcome.
     */
    public static function isOB($iotf_class): bool
    {
        return in_array($iotf_class, self::OUTCOME_OB_ONLY, true);
    }

    /**
     * Count the number of overweight-or-obese subjects in a row-oriented
     * dataset, using the canonical `iotf_class` column unless another
     * field name is specified.
     *
     * @param array<int, array<string, mixed>> $rows
     */
    public static function countOWOB(array $rows, string $field = 'iotf_class'): int
    {
        $c = 0;
        foreach ($rows as $r) {
            if (self::isOWOB($r[$field] ?? '')) {
                $c++;
            }
        }
        return $c;
    }

    // ════════════════════════════════════════════════════════════════
    // Pre-registered hypothesis tests of the Chlef study
    // ════════════════════════════════════════════════════════════════

    /**
     * H1.1 — Prevalence of overweight + obesity ≥ 20 %.
     *
     * @return array<string, mixed>
     */
    public function testPrevalence(): array
    {
        $overweight = array_filter($this->data, fn($r) => $r['is_overweight'] == 1);
        $n = count($this->data);
        $k = count($overweight);

        $result               = $this->stats->binomialTest($k, $n, 0.20);
        $result['hypothesis'] = 'H1.1: Prévalence ≥20%';
        $result['confirmed']  = $result['p_obs'] >= 0.20;

        return $result;
    }

    /**
     * H1.2 — Female predominance (girls more overweight than boys).
     *
     * @return array<string, mixed>
     */
    public function testSexDifference(): array
    {
        $girls = array_filter($this->data, fn($r) => $r['sex'] === 'Fille');
        $boys  = array_filter($this->data, fn($r) => $r['sex'] === 'Garçon');

        $girls_ow = count(array_filter($girls, fn($r) => $r['is_overweight'] == 1));
        $girls_n  = count($girls);
        $boys_ow  = count(array_filter($boys, fn($r) => $r['is_overweight'] == 1));
        $boys_n   = count($boys);

        $a = $girls_ow;
        $b = $girls_n - $girls_ow;
        $c = $boys_ow;
        $d = $boys_n - $boys_ow;

        $chi2 = $this->stats->chi2Test2x2($a, $b, $c, $d);
        $or   = $this->stats->oddsRatio($a, $b, $c, $d);

        return [
            'hypothesis' => 'H1.2: Prédominance féminine',
            'prev_girls' => $girls_n ? round($girls_ow / $girls_n * 100, 1) : 0.0,
            'prev_boys'  => $boys_n  ? round($boys_ow  / $boys_n  * 100, 1) : 0.0,
            'chi2'       => $chi2,
            'or'         => $or,
            'confirmed'  => ($chi2['significant'] ?? false) && ($or['or'] ?? 0) > 1,
        ];
    }

    /**
     * H2.1 — Correlation between BMI and KIDMED Mediterranean-diet score.
     *
     * @return array<string, mixed>
     */
    public function testBMIKidmed(): array
    {
        $bmi    = array_column($this->data, 'bmi');
        $kidmed = array_column($this->data, 'score_kidmed');

        $corr = $this->stats->pearson($bmi, $kidmed);
        $corr['hypothesis'] = 'H2.1: Corrélation BMI × KIDMED';
        $corr['confirmed']  = abs((float)$corr['r']) >= 0.25 && ($corr['significant'] ?? false);

        return $corr;
    }

    /**
     * H2.2 — Daily soda consumption predicts overweight.
     *
     * @return array<string, mixed>
     */
    public function testDailySodas(): array
    {
        $daily      = array_filter($this->data, fn($r) => ($r['daily_sodas'] ?? 0) == 1);
        $not_daily  = array_filter($this->data, fn($r) => ($r['daily_sodas'] ?? 0) == 0);

        $a = count(array_filter($daily,     fn($r) => $r['is_overweight'] == 1));
        $b = count($daily) - $a;
        $c = count(array_filter($not_daily, fn($r) => $r['is_overweight'] == 1));
        $d = count($not_daily) - $c;

        $chi2 = $this->stats->chi2Test2x2($a, $b, $c, $d);
        $or   = $this->stats->oddsRatio($a, $b, $c, $d);

        return [
            'hypothesis' => 'H2.2: Sodas quotidiens',
            'chi2'       => $chi2,
            'or'         => $or,
            'confirmed'  => ($or['or'] ?? 0) >= 1.5 && ($chi2['significant'] ?? false),
        ];
    }

    /**
     * H3.1 — High screen time (> 4 h/day) predicts overweight.
     *
     * @return array<string, mixed>
     */
    public function testScreenTime(): array
    {
        $high = array_filter($this->data, fn($r) => ($r['screen_high'] ?? 0) == 1);
        $low  = array_filter($this->data, fn($r) => ($r['screen_high'] ?? 0) == 0);

        $a = count(array_filter($high, fn($r) => $r['is_overweight'] == 1));
        $b = count($high) - $a;
        $c = count(array_filter($low,  fn($r) => $r['is_overweight'] == 1));
        $d = count($low) - $c;

        $chi2 = $this->stats->chi2Test2x2($a, $b, $c, $d);
        $or   = $this->stats->oddsRatio($a, $b, $c, $d);

        return [
            'hypothesis' => 'H3.1: Temps écran élevé',
            'chi2'       => $chi2,
            'or'         => $or,
            'confirmed'  => ($or['or'] ?? 0) >= 1.5 && ($chi2['significant'] ?? false),
        ];
    }

    /**
     * Expose the underlying generic statistical engine so callers can
     * combine custom analyses with library methods without instantiating
     * `BiostatAnalysis` twice.
     */
    public function stats(): BiostatAnalysis
    {
        return $this->stats;
    }
}
