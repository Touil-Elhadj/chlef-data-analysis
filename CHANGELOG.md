# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- **Statistical engine extracted as a standalone Composer package**
  ([`touilelhadj/biostat-php`](https://github.com/Touil-Elhadj/biostat-php)):
  - The ~2000-line generic biostatistics engine that used to live as
    `BiostatPHP.class.php` in the project root is now an independent
    PSR-4 library distributed via Packagist.
  - The platform installs it via Composer (`composer install`); the
    library is loaded by `vendor/autoload.php` and used via
    `use TouilElhadj\BiostatPhp\BiostatAnalysis;`.
  - Domain-specific code (IOTF outcome constants, `isOWOB`,
    `countOWOB`, `testPrevalence` family, `ChartDataGenerator`) was
    moved from the generic class into two new platform-internal classes:
    `src/StudySpecificAnalyses.php` and `src/ChartDataGenerator.php`,
    both in namespace `TouilElhadj\ChlefPlatform\`.
  - `BiostatPHP.class.php` removed from the repository.
  - All call sites updated: `stats.php`, `report.php`,
    `advanced_stats.php`, `tests/run_tests.php`.
- **Added** `composer.json` declaring the dependency on
  `touilelhadj/biostat-php ^1.0`.
- **Added** `docs/installation-alwaysdata.md` — step-by-step deployment
  guide for alwaysdata.com (Arabic + French summary).
- **Added** `.htaccess` rule blocking direct HTTP access to `vendor/`
  and `src/`.

### Why this change?
Extracting the engine as a separate package serves two goals:

1. The library can be reused by other epidemiological projects in PHP
   environments where R / Python are unavailable (low-cost shared
   hosting, government tenant networks, etc.).
2. The library has its own peer-reviewable publication track
   (Journal of Open Source Software) independent of the master-thesis
   publication of the platform itself.


  - Repository renamed and re-organised for GitHub / JOSS publication.
  - Removed all hard-coded secrets from `config.php`; credentials now live in
    `.env` (gitignored).
  - Removed `logs/errors.log` from history (contained participant IP
    addresses).
  - Renamed `index1.php` → `index.php` (fixes broken `header('Location: /index.php')`
    redirects in `login.php`, `admin.php`, etc.).
  - Renamed `htaccess` → `.htaccess` (was being ignored by Apache).
  - Consolidated CSS/JS/img into a single `assets/` directory; removed
    duplicate dead files at the project root.
  - Consolidated `personal-survey/lang_ps.php`, `lang_ps_ext.php` and
    `lang_ps_v3.php` into a single `personal-survey/lang_ps.php`.
  - Removed duplicate dead endpoints: `save.php` (root), `data.php` (root).
  - Moved CLI / migration tools out of webroot: `backup.php` → `bin/`,
    `migration_screen_indicators.sql` → `database/migrations/`,
    `database.sql` → `database/schema.sql`, `tests.php` → `tests/run_tests.php`.
  - Removed `test_screen_indicators.php` (one-shot diagnostic script).
  - Removed `biostat_php_complete.php` (superseded by `index.php` + `stats.php`
    + `advanced_stats.php`).
  - Normalised line endings to LF and enforced via `.gitattributes`.

### Added
- `LICENSE` (MIT), `README.md`, `CONTRIBUTING.md`, `CODE_OF_CONDUCT.md`,
  `CITATION.cff`, `paper.md`, `paper.bib`, `.env.example`, `.editorconfig`,
  `.gitignore`, `.gitattributes`, GitHub Actions workflow `.github/workflows/ci.yml`.
- `docs/installation.md`, `docs/architecture.md`, `docs/statistical-methods.md`.
- `database/seed.sql` for bootstrapping a local admin user.

### Security
- All hard-coded production secrets removed from version control.
- `api/save.php` no longer leaks `PDOException::getMessage()` to the JSON
  response in production mode.

---

## [2.0.0] — 2026-05

Originally released as `CHANGELOG_v2.0.md` (preserved verbatim for the
historical record).

### Added — Six advanced biostatistical methods in `BiostatAnalysis`

| # | Method | Algorithm | Reference |
|---|--------|-----------|-----------|
| 1 | `vif` | OLS auxiliary + R²ⱼ → 1/(1 − R²ⱼ) | Allison (2012), thresholds 2.5 / 5 |
| 2 | `boxTidwell` | adds *X · ln X* term to multivariate logit | Box & Tidwell (1962) |
| 3 | `glmmLogistic` | PQL + Henderson equations, σ² by ML, ICC = σ² / (σ² + π²/3) | Breslow & Clayton (1993) |
| 4 | `geeLogistic` | Liang–Zeger, exchangeable correlation, sandwich variance | Liang & Zeger (1986) |
| 5 | `mice` | Predictive Mean Matching, chained Gibbs iterations | van Buuren & Groothuis-Oudshoorn (2011) |
| 6 | `rubinPool` | T = U + (1 + 1/m)B, Barnard–Rubin df, FMI | Rubin (1987) |

### Added — Linear-algebra helpers (protected)
- `matMul`, `matVec`, `matTranspose`, `olsRegression`.

### Changed
- `BiostatPHP.class.php`: 1 015 → ~2 000 LOC.
- `tests.php`: 291 → ~480 LOC; +6 tests (T9–T14), +12 assertions.
- `biostat_php_complete.php`: +1 tab "Advanced analyses".

### Added — New page
- `advanced_stats.php`: dashboard executing the five new analyses on live SQL
  data.

### Validation
- 20 / 20 assertions pass against R 4.x (`lme4`, `geepack`, `mice`, `car`)
  and SPSS 25.

---

## [1.4.0] — 2026 (in `INSTALL_v4.md`)

### Added
- `about.php` rewritten with new structure, CSS and new sections.
- `about_data.inc.php`: 62.9 KB of structured content data (new file).
- `lang.php`: 5 new translation keys.
- New section 8 — "Complete platform capabilities".
- New section 9 — "Complete catalogue of biostatistical tests" (22 cards).

---

## [1.0.0] — 2026 (initial deployment)

### Added
- Trilingual (ar / fr / en) data-collection web form.
- Pure-PHP biostatistics library covering descriptive, bivariate and
  univariate methods.
- Admin dashboard, statistics dashboard, map visualisation, exports
  (CSV / SPSS).
- Authentication with bcrypt, CSRF, brute-force lockout, audit log.
- Personal-survey extension (v1 → v3) with SES, puberty, FFQ Algerian,
  smoking, social jet-lag, sitting time and early-life antibiotics.

[Unreleased]: https://github.com/Touil-Elhadj/chlef-touilelhadj/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/Touil-Elhadj/chlef-touilelhadj/releases/tag/v2.0.0
[1.4.0]: https://github.com/Touil-Elhadj/chlef-touilelhadj/releases/tag/v1.4.0
[1.0.0]: https://github.com/Touil-Elhadj/chlef-touilelhadj/releases/tag/v1.0.0
