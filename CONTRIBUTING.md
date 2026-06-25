# Contributing to Chlef-Biostat-2026

First of all: thank you for considering a contribution! This project is a
research instrument that benefits from external review and improvement.

## How to report a bug

1. Search the [issue tracker](https://github.com/Touil-Elhadj/chlef-touilelhadj/issues)
   to make sure the bug has not already been reported.
2. Open a new issue using the bug-report template. Include:
   - the version of PHP (`php -v`) and MySQL/MariaDB you are using,
   - the exact steps to reproduce the problem,
   - the expected vs. actual behaviour,
   - any relevant entries from `logs/errors.log` (redact IP addresses and any
     personal data before pasting).

## How to suggest a feature

Open an issue using the feature-request template. Briefly state:
- the use case you have in mind,
- why the current behaviour is insufficient,
- any references (paper, R package, SPSS procedure) that would help us
  evaluate the proposal.

## How to submit a pull request

1. Fork the repository and create a topic branch from `main`:
   ```bash
   git checkout -b feature/short-description
   ```
2. Make your changes following the **coding style** below.
3. Add or update tests in `tests/run_tests.php`. **Statistical methods must
   ship with at least one closed-form reference value computed independently
   in R or SPSS.**
4. Run the test suite locally:
   ```bash
   php tests/run_tests.php
   ```
   Every assertion must pass.
5. Update `docs/` and `CHANGELOG.md` if your change is user-visible.
6. Push your branch and open a pull request against `main`. The CI workflow
   will run the tests automatically.
7. A maintainer will review the PR; please be patient and responsive to
   comments.

## Coding style

- **PHP**: PSR-12-compatible, 4-space indentation, opening brace on the same
  line for control structures (`if (...) {`), strict comparisons (`===`,
  `!==`), early returns over deeply-nested `if`/`else`.
- **Encoding**: UTF-8 without BOM. Line endings: LF only (enforced by
  `.gitattributes` and `.editorconfig`).
- **Naming**:
  - functions and methods → `camelCase`
  - classes → `PascalCase`
  - SQL columns and PHP variables for survey data → `snake_case` (matches the
    database schema)
- **SQL**: keywords UPPERCASE, identifiers lowercase, one column per line in
  `CREATE TABLE`.
- **i18n**: every user-facing string goes through `__()` and must be defined
  in `lang.php` for the three supported languages (ar / fr / en).
- **Security**: prepared statements only (no string concatenation in SQL),
  `sanitize()` on every user-supplied scalar before echoing it,
  `validateCSRF()` on every state-mutating endpoint.

## Statistical contributions

If you contribute a new statistical method:

1. Cite the canonical reference in a header comment in
   `BiostatPHP.class.php`.
2. Add an entry to `docs/statistical-methods.md` with the formal
   formulation.
3. Add at least one assertion in `tests/run_tests.php` with the reference
   value obtained in R or SPSS and the tolerance used.
4. Update `paper.md` and `paper.bib` if the addition is substantial.

## Code of conduct

By participating in this project you agree to abide by the
[Code of Conduct](CODE_OF_CONDUCT.md). In short: be respectful, assume good
faith, and remember that this is a research project whose primary audience is
medical and public-health researchers, not necessarily software engineers.

## Questions

For any other question, open a [Discussion](https://github.com/Touil-Elhadj/chlef-touilelhadj/discussions)
or contact the maintainer via the email listed in `CITATION.cff`.
