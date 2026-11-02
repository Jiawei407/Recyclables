# Recyclables tests

Tests run against the real PHP functions, SQL queries, action handlers and JavaScript in this repository, including the role layout and agreed account/activity rules.

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

The full agreed identity policy is included; run it without excluding requirements. [RESULTS.md](RESULTS.md) records the historical baseline, and [ROLE-LAYOUT-VERIFICATION.md](../ROLE-LAYOUT-VERIFICATION.md) records the current implementation results.

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

Both suites include the agreed-rule assertions. Do not exclude the `requirements` group for release checks.

Integration fixtures roll back after every test. HTTP tests use committed fixtures so the server can read them, then delete them in dependency order. They require the setup script's marker table and a private server handshake before any fixture cleanup. Never point them at an existing application database. Do not run database/request suites concurrently against the same test database.

`run-http.php` launches its own loopback PHP server on a free port, forces the test database settings, and stops the server afterwards. Apache and the production website are not needed for this command. Your normal `includes/config.php` is unchanged. Temporary logs and sessions live under ignored `tests/.runtime/`; uploaded test images are removed after each request test, without removing existing upload files.

For Linux/macOS, use `php` in place of the XAMPP executable and export the same environment variables.

## SQL installation regression

With MySQL running, set `RECYCLABLES_TEST_DB_NAME` to a new, unused `recyclables_test_*` prefix (at most 35 characters after the prefix), plus the test connection variables above, then run `php tests/check-sql-install.php`. It creates two disposable schemas, reproduces selection of an older users table without `university_id`, imports the current schema/demo accounts, checks all four role password hashes and repeat-import behavior, and verifies the legacy account/schema remain untouched. Only schemas successfully created by this invocation are removed. The application SQL and default connection now use `recyclables_ui_db`, separate from the old `recyclables_db` project.

## JavaScript tests

```powershell
node --test tests/js/*.test.cjs
```

These execute the production scripts with small DOM event doubles. They do not certify real browser image decoding, native focus order or responsive layout.

## Remaining coverage

- HEIC/HEIF containers are validated and served privately; browsers that cannot decode them get a download link. Server decoding, orientation and conversion are not implemented.
- Upload defaults are configurable (256 MiB and 256 megapixels). Tests cover valid 48 MP PNG and photos larger than 5 MB, malformed containers and configured bounds; they do not establish a hardware maximum for all phones.
- Proposed environmental formulas are not implemented in the application; no substitute reporting implementation was added just to test it.
- Real concurrent review races, failure injection during database commits and Apache deployment protections need further coverage. The role-layout browser checks cover all 82 role routes at 320px/1440px, with axe checks on representative pages.
- Account deletion tests verify private evidence/points/awards are purged while other accounts, shared records and nullable audit history remain. Test upload cleanup is limited to the owning disposable schema.

Run the unit and JavaScript suites after code changes, database suites after query/schema changes, and the HTTP suite before releases. Do not exclude `requirements` when deciding whether the application meets the agreed rules.
