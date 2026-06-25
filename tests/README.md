# Tests

This folder contains the unit-test suite that verifies the `BiostatPHP`
library against reference values pre-computed in R 4.x (`stats`, `car`,
`lme4`, `geepack`, `mice`) and IBM SPSS Statistics 25.

## Running the tests

### From the command line

```bash
php tests/run_tests.php
```

No database connection is required for most assertions; the tests use
inline fixture vectors.

### From a browser

```
https://<your-host>/tests/run_tests.php
```

Authentication as an admin or guest user is required (the page is denied
to anonymous visitors as a defence-in-depth measure).

## Tolerances

| Family of tests | Tolerance |
|---|---|
| *p*-values | ± 0.01 |
| Odds ratios | ± 0.001 |
| Correlation coefficients | ± 0.001 |
| Regression coefficients | ± 0.01 |
| Variance components | ± 0.05 |

Divergences are dominated by rounding and the χ² / F continuous
approximations used in the PHP implementation.

## Test catalogue

The 14 tests (T1 → T14) check 20 assertions covering every public
method of `BiostatAnalysis`. See `docs/statistical-methods.md` for
the mathematical specification of each method.

| ID | Method | Reference source |
|----|--------|------------------|
| T1 | `chiSquare` (2 × 2) | R `chisq.test` |
| T2 | `oddsRatio` + 95 % CI | R `epitools::oddsratio.wald` |
| T3 | `welchT` | R `t.test(var.equal = FALSE)` |
| T4 | `anova` | R `aov` |
| T5 | `pearson` | R `cor.test` |
| T6 | `logisticRegression` | R `glm(..., binomial)` |
| T7 | `benjaminiHochberg` | R `p.adjust(..., method = 'BH')` |
| T8 | `vif` | R `car::vif` |
| T9 | `boxTidwell` | R `car::boxTidwell` |
| T10 | `glmmLogistic` | R `lme4::glmer` |
| T11 | `geeLogistic` | R `geepack::geeglm` (exchangeable) |
| T12 | `mice` | R `mice::mice` (PMM, m = 20) |
| T13 | `rubinPool` | R `mice::pool` |
| T14 | Integration: ICC, AUC, Hosmer–Lemeshow | R combination of the above |

## Adding a new test

1. Write or obtain the reference value in R or SPSS.
2. Add an assertion in `run_tests.php` using `assertNear($expected, $actual, $tolerance, 'short label')`.
3. Run the suite locally to confirm it passes.
4. Document the new test in this README and in `docs/statistical-methods.md`.
