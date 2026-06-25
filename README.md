# Chlef-Biostat-2026

> Trilingual web platform and pure-PHP biostatistics library for the cross-sectional
> study on adolescent overweight and obesity in the Wilaya of Chlef, Algeria
> (academic year 2025–2026).

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-%E2%89%A58.0-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![CI](https://github.com/Touil-Elhadj/chlef-touilelhadj/actions/workflows/ci.yml/badge.svg)](https://github.com/Touil-Elhadj/chlef-touilelhadj/actions)
[![Status](https://img.shields.io/badge/status-active-success)](https://github.com/Touil-Elhadj/chlef-touilelhadj)

---

## 📖 Overview

**Chlef-Biostat-2026** is a self-contained research instrument written in pure PHP that
combines three components:

1. **A trilingual (Arabic / French / English, RTL-aware) data-collection web form**
   for ~115 anthropometric, dietary, behavioural and psychosocial variables.
2. **The [`biostat-php`](https://github.com/Touil-Elhadj/biostat-php) biostatistics
   library** (a pure-PHP package extracted from this project and now distributed
   independently via Packagist as `touilelhadj/biostat-php`) implementing the
   descriptive, bivariate and multivariate methods normally available only in
   R or SPSS — including logistic regression with the Hosmer–Lemeshow test,
   Benjamini–Hochberg FDR, **VIF**, **Box–Tidwell**, a generalised linear mixed
   model fitted by **PQL** (GLMM), **GEE** with Liang–Zeger sandwich variance,
   and **multiple imputation by chained equations** (MICE) with Rubin's pooling.
3. **An on-line analytical dashboard** that runs all 48 pre-registered
   hypotheses of the underlying master-thesis on live data, plus a set of
   study-specific helpers (`src/StudySpecificAnalyses.php`,
   `src/ChartDataGenerator.php`) that compose the generic library with the
   IOTF outcome definitions of the Chlef protocol.

The platform was used to enrol 1 220 adolescents (14–19 yrs) from the Wilaya of
Chlef. All statistical results in the underlying thesis have been verified
against the equivalent R packages (`stats`, `lme4`, `geepack`, `mice`, `car`)
and SPSS 25; tolerances are documented in `tests/`.

---

## ✨ Why a pure-PHP biostatistics library?

Conventional epidemiological studies depend on R / SPSS for the analytic
stage, which forces investigators to maintain two separate environments
(one for data collection, one for analysis) and exposes a long export /
re-import workflow that is error-prone and irreproducible. By implementing
the full pipeline — from the data-entry form to GLMM and MICE — in a single
PHP runtime, this project shows that a complete cross-sectional analysis can
be conducted on a low-cost shared-hosting environment with **zero external
statistical software**. The library is also reusable as a stand-alone
component for any survey-based study.

---

## 🚀 Quick start

### Prerequisites

- PHP **≥ 8.0** with `pdo_mysql`, `mbstring`, `json`, `intl`
- **Composer** (https://getcomposer.org) — pre-installed on most modern hosts including alwaysdata
- MySQL **8.0+** or MariaDB **10.5+** (with utf8mb4)
- Apache (with `mod_rewrite`) or Nginx
- *(Optional)* `git`, a shell, and `mysql` CLI

### Installation

```bash
# 1. Clone
git clone https://github.com/Touil-Elhadj/chlef-touilelhadj.git
cd chlef-touilelhadj

# 2. Install the biostat-php library (and any other Composer dependencies)
composer install --no-dev --optimize-autoloader

# 3. Configure environment
cp .env.example .env
nano .env          # set DB_HOST, DB_NAME, DB_USER, DB_PASS, SITE_URL, ADMIN_EMAIL

# 4. Create the schema
mysql -u <DB_USER> -p <DB_NAME> < database/schema.sql

# 5. (Optional) Seed a local admin
mysql -u <DB_USER> -p <DB_NAME> < database/seed.sql
#   default credentials: admin / change_me_immediately

# 6. Point your web server's document root to the project root.
#    Make sure logs/ is writable by the web user:
chmod -R 0755 logs/
```

Open `https://your-domain/login.php` and log in.

Detailed deployment notes (Apache vhost, alwaysdata, permission hardening) are
in [`docs/installation.md`](docs/installation.md) and the Arabic/French step-by-step
alwaysdata guide is in [`docs/installation-alwaysdata.md`](docs/installation-alwaysdata.md).

### Running the unit tests

```bash
# Web mode (login required, admin or guest)
open https://your-domain/tests/run_tests.php

# CLI mode (no auth)
php tests/run_tests.php
```

The 20 assertions reproduce the reference values computed in R 4.x and SPSS 25
(see `docs/statistical-methods.md`).

---

## 🧪 Statistical methods implemented

| Family | Methods |
|---|---|
| Descriptive | mean, SD, median, IQR, quantiles, skewness, kurtosis |
| 2 × 2 tables | χ² (with Yates), Fisher's exact, odds ratio + 95 % CI |
| Means | Welch's *t*, one-way ANOVA, Tukey HSD post-hoc |
| Correlation | Pearson *r*, Spearman ρ |
| Multivariate | logistic regression (Newton–Raphson), AUC, Hosmer–Lemeshow |
| Multiple testing | Benjamini–Hochberg FDR |
| Multicollinearity | Variance Inflation Factor (Allison 2012 thresholds) |
| Logit linearity | Box–Tidwell test (Box & Tidwell 1962) |
| Random effects | GLMM (logistic) by PQL & Henderson equations (Breslow & Clayton 1993) |
| Clustered data | GEE (exchangeable correlation, sandwich variance) (Liang & Zeger 1986) |
| Missing data | MICE with Predictive Mean Matching (van Buuren & Groothuis-Oudshoorn 2011) |
| Inference pooling | Rubin's rules (Rubin 1987) — pooled β, SE, df, FMI |

Full mathematical formulations and reference values are in
[`docs/statistical-methods.md`](docs/statistical-methods.md).

---

## 🛠️ Tech stack

- **Backend**: PHP ≥ 8.0 (no framework), PDO, server-side sessions
- **Database**: MySQL 8 / MariaDB (utf8mb4)
- **Frontend**: Vanilla HTML 5, CSS 3, JavaScript (no build tool, no bundler)
- **i18n**: trilingual dictionary in `lang.php` (ar / fr / en) with RTL support
- **Security**: bcrypt password hashing, CSRF tokens, prepared statements,
  brute-force lockout, audit log, HTTPS redirect, HSTS, `.env`-based secrets

---

## 📁 Repository layout

```
chlef-touilelhadj/
├── .github/workflows/    CI configuration
├── api/                  JSON endpoints (save, data export)
├── assets/               css/, js/, img/ (single source of truth)
├── bin/                  CLI utilities (backup, etc.)
├── database/             schema.sql, seed.sql, migrations/
├── docs/                 thesis PDF, install / methods / architecture docs
├── logs/                 runtime error log (gitignored)
├── personal-survey/      stand-alone v3 well-being questionnaire (115 Q)
├── src/                  platform-internal classes (PSR-4)
│   ├── StudySpecificAnalyses.php   IOTF outcomes + H1-H3 hypothesis tests
│   └── ChartDataGenerator.php      Chart.js JSON producers
├── tests/                unit tests vs. R / SPSS reference values
├── vendor/               Composer-installed dependencies (gitignored)
│   └── touilelhadj/biostat-php/   the statistical engine (~ 2000 LOC)
├── composer.json         declares the biostat-php dependency
├── computed_scores.php   reproducible derivation of IOTF / SES / FFQ scores
├── config.php            reads .env, no hardcoded secrets
├── index.php             main entry (data-entry form)
└── (other page-level scripts)
```

---

## 📸 Screenshots

*Replace these placeholders with real screenshots after the first deployment.*

| Login | Data entry | Admin dashboard | Stats |
|---|---|---|---|
| ![login](docs/screenshots/login.png) | ![form](docs/screenshots/form.png) | ![admin](docs/screenshots/admin.png) | ![stats](docs/screenshots/stats.png) |

---

## 🤝 Contributing

Contributions, bug reports and feature requests are welcome. Please read
[CONTRIBUTING.md](CONTRIBUTING.md) and the [Code of Conduct](CODE_OF_CONDUCT.md)
before opening an issue or a pull request.

For statistical contributions (new tests, new estimators) please include in your
PR (a) the closed-form reference value(s) obtained in R or SPSS and (b) the
corresponding assertion(s) in `tests/run_tests.php`.

---

## 📜 Citation

If you use this software in a research publication, please cite it. A
machine-readable citation file is included as [`CITATION.cff`](CITATION.cff).

### Recommended citation

> TOUIL, E. (2026). *Chlef-Biostat-2026: a pure-PHP biostatistics platform for
> a cross-sectional study on adolescent obesity in Chlef, Algeria.*
> Version 1.0.0. https://github.com/Touil-Elhadj/chlef-touilelhadj

### BibTeX

```bibtex
@software{touil_chlef_biostat_2026,
  author       = {TOUIL, Elhadj},
  title        = {Chlef-Biostat-2026: a pure-PHP biostatistics platform
                  for a cross-sectional study on adolescent obesity in
                  Chlef, Algeria},
  year         = {2026},
  version      = {1.0.0},
  url          = {https://github.com/Touil-Elhadj/chlef-touilelhadj}
}
```

---

## 🎓 Acknowledgements

This software was developed as part of a Master's thesis at the
**Faculty of Medicine, Hassiba Benbouali University of Chlef (UHBC)**,
under the supervision of **Dr Ali Haimoud S.** (academic year 2025–2026).

---

## 📄 License

Released under the [MIT License](LICENSE) — © 2026 TOUIL Elhadj.
