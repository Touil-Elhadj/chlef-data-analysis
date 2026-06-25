## Summary

<!-- One-paragraph description of what this PR does and why. -->

## Type of change

- [ ] Bug fix
- [ ] New feature (non-statistical)
- [ ] New statistical method or modification of an existing one
- [ ] Documentation update
- [ ] Refactor / cleanup
- [ ] Other (please describe)

## Checklist

- [ ] I have run `php -l` on every changed PHP file (no syntax errors).
- [ ] I have run `php tests/run_tests.php` locally and every assertion passes.
- [ ] My code follows the style described in `CONTRIBUTING.md`.
- [ ] All user-facing strings go through `__()` and are defined in `lang.php` for `ar`, `fr` and `en`.
- [ ] I have updated `CHANGELOG.md` under `## [Unreleased]`.
- [ ] If this PR adds or modifies a statistical method:
  - [ ] I have added a reference value computed in R or SPSS to `tests/run_tests.php`.
  - [ ] I have updated `docs/statistical-methods.md`.

## Related issues

<!-- Closes #..., refs #... -->
