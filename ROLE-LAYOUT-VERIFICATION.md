# Role layout implementation and verification

Verified on 2 November 2026 against the working PHP application on branch `codex/agreed-rules-fixes`.

## Implemented scope

All 82 authenticated routes use the approved forest, paper and lime design. Home/profile pages have the more expressive treatment; forms, records and reports use restrained typography and divider lines. Selects, challenge selection, campaign selection, dates and quantities enhance the real native fields. The coordinator's duplicate review-queue shortcut and the administrator's platform-status banner are removed.

The shared server presentation runs after authentication and POST handling. It retains live database records, real CSRF tokens, native form actions/constraints, role permissions, private attachments and original destinations. No static prototype data, submit interception, role switcher or prototype `.html` navigation is deployed. `index.php` and `styles.css` are byte-identical to the branch baseline.

The test pass also resolved existing agreed-rule failures: university identity validation, ranking ties, retained achievements, optional review notes, supported photo uploads and account deletion with private activity. Student and faculty dashboards now show actual saved activity and scoped participation instead of unconditional empty placeholders.

## Final automated results

| Check | Result |
| --- | --- |
| PHP Unit + Requirements | 250 tests, 379 assertions; passed |
| Database Integration | 37 tests, 142 assertions; passed |
| Real PHP HTTP workflows and access checks | 209 tests, 3,036 assertions; passed |
| JavaScript regression tests | 51 tests; passed |
| PHP syntax | 144 source/test files; passed |
| Role routes at 320px and 1440px | 82 routes / 164 viewport checks; passed |
| axe-core 4.10.3 WCAG checks on representative routes | 22 page/viewport checks, zero violations |
| Live themed-control workflows | All six browser assertions passed |

The PHP suites total **496 tests and 3,557 assertions**, with no failures, warnings or skipped tests. The JavaScript suite has no failures or skipped tests.

Browser checks verify successful pages, role navigation, overflow, broken images, script errors, POST CSRF fields and absence of prototype controls/links. The separate live browser flow logs in as all four roles, submits evidence using themed controls, approves it, verifies the saved participant result, publishes a faculty announcement through the themed calendar/select, and checks administrator field payload/validation. Student/faculty home changes were visually checked after binding real records.

## Repeat the checks

```powershell
& C:\xampp\php\php.exe tools/phpunit-9.phar -c phpunit.xml --testsuite Unit,Requirements
node --test tests/js/*.test.cjs
```

For database and HTTP checks, first prepare a new empty `recyclables_test_*` schema as documented in [tests/README.md](tests/README.md), then run these sequentially against that schema:

```powershell
& C:\xampp\php\php.exe tools/phpunit-9.phar -c phpunit.xml --testsuite Integration
& C:\xampp\php\php.exe tests/run-http.php
```

The HTTP runner starts and stops its own loopback server and verifies the database identity/token before writing fixtures. Test upload cleanup removes only files referenced by its own disposable schema. Production databases and existing uploads are excluded from cleanup. Browser runners and fixture setup are in [tests/browser](tests/browser/README.md); local reports, sessions and screenshots are ignored under `tests/.runtime/`.

## Deployment requirements and limits

PHP DOM/XML enables the new role presentation; if absent, the original functional role UI is returned. XAMPP enables it by default. The application has no new frontend build dependency.

For the default upload allowance, set PHP `upload_max_filesize=256M` and `post_max_size=300M`. Application byte/pixel limits are configurable; these operational defaults do not claim a measured maximum for every phone. HEIC/HEIF is structurally validated and privately served, with a download fallback when the browser cannot decode it; server conversion/orientation is not implemented.

Unmeasured environmental conversion metrics remain explicitly unmeasured. No faculty participant target is invented when none is configured. Automated accessibility checks supplement the visual/keyboard checks and do not certify every possible assistive-technology interaction. See [FEATURES-AND-LIMITS.md](FEATURES-AND-LIMITS.md) for the existing application boundaries.
