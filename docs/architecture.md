# Architecture

## 1. Design principles

The platform deliberately follows a **conservative, no-framework, page-per-file**
PHP architecture. The reasoning is documented here so that future maintainers
do not assume the absence of a framework is an oversight.

1. **Reproducibility on shared hosting.** The primary deployment target is an
   academic shared host (alwaysdata) without Composer, root access or the
   ability to install PHP extensions. A framework-free codebase guarantees
   that anyone with FTP access can deploy the entire system.
2. **Auditability for thesis defence.** Each page is a single, self-contained
   PHP file. A jury member without web-development expertise can read one
   file and understand what one URL does.
3. **Minimal dependency footprint.** The only runtime requirements are PHP
   ≥ 8.0 with `pdo_mysql`, `mbstring`, `intl` and `json`. No Composer
   packages, no Node.js, no build step.
4. **Long-term archival.** A research artifact that runs on raw PHP today
   will still run on raw PHP in fifteen years; one built on a fast-moving
   framework probably will not.

## 2. High-level diagram

```
┌───────────────────────────────────────────────────────────────────────┐
│                        Browser  (ar / fr / en, RTL)                   │
└────────────────┬───────────────────────────────────┬──────────────────┘
                 │ HTML form submit                  │ fetch() AJAX
                 ▼                                   ▼
       ┌─────────────────────┐             ┌──────────────────┐
       │ index.php           │             │ api/save.php     │
       │ login.php           │             │ api/data.php     │
       │ admin.php           │             └──────────────────┘
       │ stats.php           │
       │ advanced_stats.php  │  ── reads ──►  vendor/touilelhadj/biostat-php/src/
       │ report.php          │                computed_scores.php
       │ about.php           │                          │
       │ guide.php / map.php │                          │
       └──────────┬──────────┘                          │
                  │                                     │
       ┌──────────▼──────────┐                          │
       │ config.php (loads   │                          │
       │ .env, getDB, CSRF,  │                          │
       │ session, audit)     │                          │
       │ lang.php (i18n)     │                          │
       └──────────┬──────────┘                          │
                  ▼                                     ▼
                ┌────────────────────────────────────────────┐
                │            MySQL / MariaDB                 │
                │   tables:  users, responses, audit_log,    │
                │            login_attempts, assignments,    │
                │            notifications                   │
                └────────────────────────────────────────────┘
```

## 3. Directory roles

| Path | Role |
|---|---|
| `index.php` | main 5-step questionnaire (data entry) |
| `login.php` / `logout.php` | session lifecycle |
| `admin.php` | user management, exports |
| `stats.php` | descriptive + bivariate analysis dashboard |
| `advanced_stats.php` | live execution of VIF / GLMM / GEE / MICE |
| `report.php` | PDF-style HTML report for a single record |
| `about.php` + `about_data.inc.php` | "about the study" page (heavy structured content) |
| `guide.php` | help page for data-entry operators |
| `map.php` | wilaya-of-Chlef commune-level visualisation |
| `records.php` | list / search / delete records (admin / guest) |
| `progress.php` | enrolment progress widget |
| `navbar.php` | shared top-navigation include |
| `config.php` | environment, DB, session, CSRF, audit, logger |
| `lang.php` | trilingual dictionary + `__()` helper |
| `vendor/touilelhadj/biostat-php/src/` | the statistical library (≈ 1 920 LOC) |
| `computed_scores.php` | reproducible derivation of IOTF, SES, FFQ, screen and other indicator scores |
| `api/save.php` | JSON endpoint for `index.php` form POST |
| `api/data.php` | JSON endpoint for listing / deleting / exporting records |
| `personal-survey/` | stand-alone v3 well-being extension (115 Q) |
| `tests/` | unit tests against R / SPSS reference values |
| `bin/` | CLI utilities (backup) |
| `database/` | schema + seed + migrations |
| `docs/` | thesis, installation, architecture, statistical-methods |
| `assets/` | css, js, img — single source of truth |
| `logs/` | runtime errors (gitignored) |

## 4. Request lifecycle (example: `index.php`)

1. The browser requests `/index.php`.
2. Apache invokes PHP-FPM, which executes `index.php`.
3. `index.php` calls `require_once 'config.php'`.
4. `config.php` reads `.env`, defines constants, redirects to HTTPS if needed.
5. `index.php` calls `require_once 'lang.php'`.
6. `lang.php` resolves the active language from `$_SESSION['lang']` (default
   `ar`) and exposes `__('key')`.
7. `index.php` calls `checkSession()`; unauthenticated users are redirected
   to `/login.php`.
8. The form is rendered with `<?= __('idx_step1_title') ?>` calls and a CSRF
   token via `csrfField()`.
9. The user fills in the form and clicks *Submit*.
10. `assets/js/app.js` collects the form state and POSTs it as JSON to
    `/api/save.php`.
11. `api/save.php` re-validates CSRF, checks `canWrite()`, validates each
    field against a whitelist of allowed values (see `save.php` lines
    18–55), and inserts into `responses`.
12. The endpoint returns a JSON `{ success: true, id: <int> }` (or a
    localised error). The browser displays a toast and resets the form.

## 5. Data flow into the analyses

`stats.php` and `advanced_stats.php` query the `responses` table directly,
instantiate `BiostatAnalysis($rows)`, and call the relevant methods. The
class is **stateless** between calls (each public method takes its inputs
as arguments). The only state held by the constructor is the raw data set,
which is used internally for cross-tabulations.

For the advanced methods (`vif`, `boxTidwell`, `glmmLogistic`, `geeLogistic`,
`mice`, `rubinPool`), the page first builds the design matrix `X` and the
outcome vector `y` from the SQL rows, then calls the method. Results are
rendered as HTML tables with the same column order as R's `summary(glm(...))`
to ease verification.

## 6. Internationalisation

- All user-facing strings live in `lang.php` as a flat associative array
  `$t[<key>] = ['ar' => ..., 'fr' => ..., 'en' => ...]`.
- The active language is stored in `$_SESSION['lang']` and switched via the
  query string `?lang=ar|fr|en` (handled by `lang.php` itself).
- `langDir()` returns `rtl` for `ar`, `ltr` otherwise. The `<html>` tag of
  each page sets `dir="<?= langDir() ?>"`.
- The CSS provides RTL-aware rules via `[dir="rtl"]` selectors in
  `assets/css/style.css`.

## 7. Security model

| Concern | Mitigation |
|---|---|
| SQL injection | prepared statements throughout (`PDO`, `ATTR_EMULATE_PREPARES = false`) |
| XSS | every echoed user input goes through `sanitize()` or `htmlspecialchars()` |
| CSRF | per-session token in `$_SESSION['csrf_token']`; required on every POST |
| Brute force | `checkBruteForce()` blocks after `MAX_LOGIN_ATTEMPTS` failures from the same IP/username within `LOCKOUT_DURATION` |
| Password storage | `password_hash()` with bcrypt; `password_verify()` for checks |
| Session hijacking | `session_regenerate_id(true)` on login; HTTPS-only via `.htaccess` |
| Secret leakage | secrets live in `.env` (gitignored); `config.php` is denied by `.htaccess`; production hides `PDOException::getMessage()` |
| Auditing | every login, every write and every delete logged in `audit_log` |
| Information disclosure | sensitive backends (`config.php`, `vendor/touilelhadj/biostat-php/src/`, etc.) denied at the web-server level |

## 8. Known limitations

- The architecture has no autoload; every script lists its `require_once`
  statements explicitly. This is acceptable at the current scale (~15
  page-level files) but would become tedious for a much larger codebase.
- There is no abstraction layer between SQL and the controllers; queries
  are inlined in the pages. The trade-off is verbosity vs. transparency;
  the project chose transparency.
- The `BiostatPHP` library uses arrays of arrays for matrices. For
  thousand-row data sets the cost is acceptable; for tens of thousands of
  rows the PQL iterations of `glmmLogistic` become noticeably slow
  (~seconds). A future optimisation could rewrite the linear algebra
  using `\SplFixedArray` or a typed-array extension.
