# Recyclables tests

Tests run against the real PHP functions, SQL queries, action handlers and JavaScript in this repository. No application behaviour has been changed.

## What is covered

| Suite | Coverage |
| --- | --- |
| Unit | Account/password validation, review transitions, request input, date/number boundaries, HTML escaping, CSRF fields and retained form values. No database needed. |
| Requirements | Your exact university email and case-insensitive TP rules. These expose differences in the current implementation. |
| Integration | Real MySQL/MariaDB queries for rankings, balances, faculty/participant record scope, monthly totals and achievement progress/retention. |
| HTTP | Actual login/session/CSRF, roles, registration, profiles/passwords, challenge joining/management, evidence uploads/reviews, campaign awards, faculty activities/reports/announcements, point deductions and all 93 page routes. |
| JavaScript | Search/filter behaviour, month boundaries, password confirmation, confirmation prompts, dialog keyboard controls, filename/preview handling and feedback messages. |

Agreed-rule failures use the `requirements` group. They are normal failing assertions, not skipped or marked as expected failures. They stay visible until the application implements your rules.

## Requirements

- XAMPP PHP 8.0 or later, with its usual mysqli, curl, DOM, fileinfo and zlib extensions.
- PHPUnit 9.6, downloaded below. No Composer or application framework required.
- MySQL/MariaDB for Integration and HTTP only.
- Node.js 18 or later for JavaScript tests only; no npm packages required.

Keep tests and development tools out of the published website. `tests/.htaccess` denies Apache access where overrides are enabled; the private test server also blocks development/private folders. The test server verifies PHP handling, not Apache configuration or every browser layout.

## Quick start: unit tests

Open PowerShell in the repository root. Adjust the PHP path if XAMPP is installed elsewhere.

```powershell
New-Item -ItemType Directory -Force tools | Out-Null
Invoke-WebRequest https://phar.phpunit.de/phpunit-9.6.phar -OutFile tools/phpunit-9.phar
& C:\xampp\php\php.exe tools/phpunit-9.phar --testsuite Unit
```

Run the identity requirements as well:

```powershell
& C:\xampp\php\php.exe tools/phpunit-9.phar --testsuite Unit,Requirements
```

This second command currently reports policy failures. See [RESULTS.md](RESULTS.md).

## Database and request tests

Start XAMPP MySQL. Choose a **new, disposable** database name beginning with `recyclables_test_`. The setup script refuses to overwrite an existing database. It imports the real schema without its production `CREATE DATABASE` / `USE` statements. Do not import demo accounts into the test database.

```powershell
$env:RECYCLABLES_TEST_DB_NAME = 'recyclables_test_local_1'
$env:RECYCLABLES_TEST_DB_HOST = '127.0.0.1'
$env:RECYCLABLES_TEST_DB_PORT = '3306'
$env:RECYCLABLES_TEST_DB_USER = 'root'
$env:RECYCLABLES_TEST_DB_PASSWORD = ''
& C:\xampp\php\php.exe tests/prepare-database.php

& C:\xampp\php\php.exe tools/phpunit-9.phar --testsuite Integration
& C:\xampp\php\php.exe tests/run-http.php
```

Both test suites include agreed-rule assertions that currently fail. To check the existing behaviour separately:

```powershell
& C:\xampp\php\php.exe tools/phpunit-9.phar --testsuite Unit,Integration --exclude-group requirements
& C:\xampp\php\php.exe tests/run-http.php --exclude-group requirements
```

Integration fixtures roll back after every test. HTTP tests use committed fixtures so the server can read them, then delete them in dependency order. They require the setup script's marker table and a private server handshake before any fixture cleanup. Never point them at an existing application database. Do not run database/request suites concurrently against the same test database.

`run-http.php` launches its own loopback PHP server on a free port, forces the test database settings, and stops the server afterwards. Apache and the production website are not needed for this command. Your normal `includes/config.php` is unchanged. Temporary logs and sessions live under ignored `tests/.runtime/`; uploaded test images are removed after each request test, without removing existing upload files.

For Linux/macOS, use `php` in place of the XAMPP executable and export the same environment variables.

## JavaScript tests

```powershell
node --test tests/js/*.test.cjs
```

These execute the production scripts with small DOM event doubles. They do not certify real browser image decoding, native focus order or responsive layout.

## Remaining coverage

- Real HEIC/HEIF decoding and orientation need representative valid fixtures and an implemented conversion path. The picker requirement is already tested and currently fails.
- The final numerical upload cap is unresolved. Current tests enforce two clear lower bounds: a valid 48 MP PNG and a valid photo larger than 5 MB. They do not invent the maximum-plus-30-MB value.
- Proposed environmental formulas are not implemented in the application; no substitute reporting implementation was added just to test it.
- Real concurrent review races, failure injection during database commits, complete browser accessibility/visual checks, and Apache deployment protections need further integration/browser coverage.
- Account deletion is tested without choosing whether linked evidence/history should be removed or anonymized; that policy still needs agreement.

Run the unit and JavaScript suites after code changes, database suites after query/schema changes, and the HTTP suite before releases. Do not exclude `requirements` when deciding whether the application meets the agreed rules.
