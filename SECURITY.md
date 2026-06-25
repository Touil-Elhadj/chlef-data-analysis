# Security policy

## Supported versions

Only the latest released version is actively maintained for security
patches.

| Version  | Supported |
|----------|-----------|
| `1.x`    | ✅        |
| `< 1.0`  | ❌        |

## Reporting a vulnerability

If you discover a security vulnerability, please **do not** open a
public issue. Instead, send the details by email to the address listed
in `CITATION.cff`, with the subject line `[security] chlef-touilelhadj`.

Please include:

1. A description of the vulnerability and its potential impact.
2. Step-by-step instructions to reproduce it (proof-of-concept code is
   welcome).
3. Affected versions / commit SHAs.
4. Any suggested mitigation, if you have one.

You should receive an acknowledgement within **7 days**. We will keep
you updated on the progress of the fix and credit you in the release
notes unless you prefer to remain anonymous.

## Scope

In scope:

- Authentication and session handling (`config.php`, `login.php`,
  `logout.php`).
- SQL injection (any file performing DB queries).
- Cross-site scripting (any file producing HTML output).
- CSRF (any state-mutating endpoint).
- Information disclosure (file inclusion, path traversal, error
  messages).
- Misconfiguration in `.htaccess` or the example deployment guides.

Out of scope:

- Issues that require physical access to the server.
- Social-engineering attacks against participants.
- Denial-of-service through brute traffic (the application is rate-
  limited at the login endpoint only; broader DoS mitigation is the
  hosting provider's responsibility).
- Vulnerabilities in third-party software (PHP, MariaDB, Apache,
  nginx) — please report those upstream.

## Hardening recommendations for operators

If you deploy this software, please ensure that:

1. **`.env` is never committed**: it is already in `.gitignore`.
2. **The default `admin / change_me_immediately` password is changed
   on first login**, or replaced via `bin/seed-admin.php`.
3. **`logs/` is not exposed**: `.htaccess` denies it, but verify via
   a `curl https://your-host/logs/errors.log` test.
4. **HTTPS is enforced**: `config.php` redirects HTTP→HTTPS in
   production; HSTS is set in `.htaccess`.
5. **PHP error display is off** in production: `APP_DEBUG=0` in `.env`.
6. **Backups are encrypted at rest** if they contain participant data
   (see `bin/backup.php`).
