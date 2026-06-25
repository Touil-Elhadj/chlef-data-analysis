-- ════════════════════════════════════════════════════════════════
-- seed.sql — Chlef-Biostat-2026
-- Bootstrap a default administrator account.
--
-- ⚠️  SECURITY WARNING
-- The default credentials are:
--    username : admin
--    password : change_me_immediately
--
-- IMMEDIATELY after the first login, change this password from
-- the admin panel. Never deploy a public instance without changing
-- this default.
-- ════════════════════════════════════════════════════════════════

SET NAMES utf8mb4;

-- bcrypt hash of "change_me_immediately" (cost 10)
-- Generate your own with:
--   php -r "echo password_hash('your_password', PASSWORD_BCRYPT), PHP_EOL;"
INSERT IGNORE INTO users (username, password_hash, full_name, role) VALUES
  ('admin',
   '$2y$10$E1k7zPaY3qg7Y5kPxLZjB.M7vJW9oNlT8RuYAZJOXqG3K0xR3l5oa',
   'Administrator',
   'admin');
