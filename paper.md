---
title: 'Chlef-Biostat-2026: a pure-PHP biostatistics platform for a cross-sectional
  study on adolescent obesity in Chlef, Algeria'
tags:
  - PHP
  - biostatistics
  - epidemiology
  - cross-sectional-study
  - adolescent-obesity
  - GLMM
  - GEE
  - multiple-imputation
authors:
  - name: Elhadj TOUIL
    orcid: 0009-0000-2400-459X
    affiliation: 1
affiliations:
  - name: Faculty of Medicine, Hassiba Benbouali University of Chlef (UHBC), Algeria
    index: 1
date: 18 May 2026
bibliography: paper.bib
---

# Summary

`Chlef-Biostat-2026` is a self-contained research instrument written entirely in
PHP that combines three functionalities usually distributed across several
independent tools: (i) a trilingual (Arabic/French/English, RTL-aware)
web form for the collection of anthropometric, dietary, behavioural and
psychosocial variables from adolescents; (ii) a thin domain layer
(`StudySpecificAnalyses`) on top of the separately-distributed
[`biostat-php`](https://github.com/Touil-Elhadj/biostat-php) Composer
library — implementing descriptive, bivariate and multivariate methods
including logistic regression, Benjamini–Hochberg FDR control [@benjamini1995controlling],
Variance Inflation Factor, the Box–Tidwell test for linearity of the logit
[@box1962transformation], a generalised linear mixed model fitted by Penalised
Quasi-Likelihood [@breslow1993approximate], Generalised Estimating Equations
with the Liang–Zeger sandwich variance [@liang1986longitudinal], and Multiple
Imputation by Chained Equations [@vanbuuren2011mice] with inference pooling
under Rubin's rules [@rubin1987multiple]; and (iii) an online dashboard that
runs the 48 pre-registered hypotheses of the underlying master-thesis on live
data with no human intervention.

The software was used to enrol 1 220 adolescents (14–19 years) from the Wilaya
of Chlef, Algeria, during the 2025–2026 academic year. Reference values
produced by the `biostat-php` library have been verified against the
corresponding R packages (`stats`, `lme4`, `geepack`, `mice`, `car`) and IBM
SPSS Statistics 25; the cross-checking suite is part of the `biostat-php`
package and is reproducible via the R script
`vendor/touilelhadj/biostat-php/tests/fixtures/reference-values.R`.

# Statement of need

Cross-sectional studies in low-resource settings face a recurring software
problem: the data-collection toolchain (typically a web form) and the
analytical toolchain (typically R or SPSS) are entirely disjoint. This
separation forces investigators to maintain two environments, export and
re-import data sets, and accept a long, error-prone, and difficult-to-reproduce
workflow. Web-based platforms such as REDCap [@harris2009redcap] address the
collection side but rely on external software for the analytical stage; on the
other hand, statistical software does not provide a deployable trilingual
data-collection front-end. The gap is especially acute when a study uses
non-Latin scripts (Arabic), requires right-to-left rendering, runs on
low-budget shared-hosting environments that do not allow R or Python execution,
and must accommodate participants reached through schools with intermittent
internet connectivity.

`Chlef-Biostat-2026` fills this gap. By implementing the full pipeline — from
the trilingual form to GLMM and MICE — in a single PHP runtime, it shows that
a complete cross-sectional analysis can be conducted in a low-cost shared
hosting environment with zero dependency on R or SPSS. The underlying
`biostat-php` library is published independently on Packagist
(`touilelhadj/biostat-php`) so that other epidemiological projects in
similar PHP-only deployment environments can reuse the analytic engine
without adopting the Chlef-specific data-entry layer.

# Functionality

The platform's analytic stack uses the public methods of the
`biostat-php` library (documented in its own JOSS submission and
project README) plus the study-specific helpers in
`src/StudySpecificAnalyses.php`. The library exposes:

- **Descriptive statistics**: `mean`, `std`, `median`, `quantiles`, `iqr`,
  `skewness`, `kurtosis`.
- **2 × 2 tables**: `chiSquare` (with Yates' correction), `fisherExact`,
  `oddsRatio` with 95 % Wald and exact confidence intervals.
- **Comparison of means**: Welch's *t*-test (`welchT`), one-way ANOVA (`anova`),
  Tukey HSD post-hoc test.
- **Correlation**: Pearson's *r* and Spearman's ρ.
- **Logistic regression**: Newton–Raphson maximum likelihood (`logisticRegression`),
  area under the ROC curve, Hosmer–Lemeshow goodness-of-fit test.
- **Multiple-testing control**: Benjamini–Hochberg FDR procedure.
- **Multicollinearity diagnostics**: `vif` (Variance Inflation Factor) using
  auxiliary OLS regressions and the conventional thresholds of 2.5 / 5
  [@allison2012logistic].
- **Logit linearity**: `boxTidwell` for continuous predictors.
- **GLMM**: `glmmLogistic` solves Henderson's mixed-model equations by PQL;
  the within-cluster variance ratio is reported together with the
  intra-cluster correlation $\sigma^2 / (\sigma^2 + \pi^2/3)$ on the latent
  scale.
- **GEE**: `geeLogistic` (exchangeable working correlation) reports both the
  model-based and the robust sandwich variance estimators.
- **Multiple imputation**: `mice` performs $m$ imputations of mixed-type data
  by chained equations with Predictive Mean Matching for continuous variables
  and proportional-odds / multinomial logit for ordered / unordered factors;
  `rubinPool` aggregates the $m$ estimates using $T = U + (1 + 1/m)B$, the
  Barnard–Rubin adjusted degrees of freedom, and the fraction of missing
  information.

Helper linear-algebra routines (matrix multiplication, transposition, normal
equations, OLS) are also provided as protected methods of the class so that
the library is self-contained and requires no PHP extension beyond the
defaults of any standard installation.

The data-collection layer comprises a 5-step main questionnaire (~80 questions)
and a 6-step *personal-survey* extension (~115 questions) covering
socio-economic status, pubertal stage (Tanner self-rating), an Algerian-specific
Food-Frequency Questionnaire, NOVA ultra-processed-food classification,
SCOFF eating-disorder screening, screen time, social jet-lag, sedentary
behaviour, tobacco/shisha/vape consumption, and early-life antibiotics.
Trilingual translations and right-to-left layout are handled through a single
dictionary (`lang.php`) without any external i18n library.

# Quality assurance

A 20-assertion test suite (`tests/run_tests.php`) checks every method against
reference values pre-computed in R 4.x and SPSS 25 at the tolerances
documented in the source. The suite covers all routines listed above. A GitHub
Actions workflow runs the tests on every push.

# Acknowledgements

The author acknowledges the supervision of Dr Ali Haimoud S. (UHBC, Faculty
of Medicine) and the participation of the schools of the Wilaya of Chlef.

# References
