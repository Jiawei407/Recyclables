# Test results — 30 October 2026

Repository: `https://github.com/Jiawei407/Recyclables.git`, commit `c38ba36` (`just download`).

Verified with XAMPP PHP **8.0.30**, PHPUnit **9.6.38** and an isolated MariaDB **10.4.32** instance. JavaScript tests used Node **24.22.1**. Database fixtures were created only in disposable `recyclables_test_*` schemas on a private test instance, not the existing XAMPP database. The HTTP runner started and stopped its own PHP server.

| Suite | Tests | Passed | Failed |
| --- | ---: | ---: | ---: |
| PHP Unit | 207 | 207 | 0 |
| Identity requirements | 27 | 15 | 12 |
| Database integration, including agreed policies | 28 | 25 | 3 |
| HTTP workflows, pages, uploads and agreed policies | 205 | 199 | 6 |
| JavaScript units | 37 | 37 | 0 |
| **Total** | **504** | **483** | **21** |

The PHP suites made 3,321 assertions. All 20 PHP test/support files passed syntax checks. There were no skipped/incomplete tests in these configured runs. Database-free execution intentionally skips database/request suites unless their test environment is configured.

## Failing requirements

| Requirement | Current implementation | Failing cases |
| --- | --- | ---: |
| Every university account uses the exact `mail.apu.edu.my` domain | Domain restriction applies only to participant accounts. Faculty/coordinator/admin accept other domains. | 9 |
| TP prefix accepts uppercase, lowercase and mixed case | Participant validator requires uppercase `TP`. | 3 |
| Older account wins a points tie | Ranking sorts tied users alphabetically by name. | 1 |
| Earned badges remain unless explicitly removed by admin | Requirement-based badges are recalculated and can disappear after quantity/requirement changes. | 2 |
| HEIC/HEIF photo support | Evidence picker offers JPEG/PNG only. Real HEIF decoding is not yet covered. | 1 |
| Maximum-resolution phone photos are supported | A valid 48 MP PNG is rejected by the 40 MP ceiling. | 1 |
| Phone-photo size allowance includes 30 MB buffer | Even a valid 6 MB PNG is rejected by the 5 MB limit. The final upper cap remains undecided. | 1 |
| Review notes are optional | Blank notes block rejection and revision decisions. | 2 |
| Accounts are permanently deleted | Accounts with evidence cannot be deleted; the app directs administrators to suspend them. | 1 |

These assertions remain failing; the tests do not redefine the requirements to match the code. They are tagged `requirements` so existing-behaviour regressions can also be run separately. Excluding this group does **not** establish compliance with the agreed rules.

## Confirmed behaviour

- All 93 page routes render or take their documented legacy redirect, with intended HTTP statuses and no PHP diagnostics in page output.
- Valid/invalid login, sessions, CSRF, ownership/role checks, registration and profile/password workflows.
- Joining, challenge management, review finality, approval checklist and prevention of duplicate point awards.
- Valid PNG upload, file ownership, upload rollback/cleanup, submission limits and rejected-evidence resubmission with new image/description.
- Point deductions, balances, faculty scope, reports, activities, announcements and campaign rewards.
- Request validators, safe HTML/form helpers, query-backed rankings/achievement progress and JavaScript interactions.

No application implementation was changed. This is functional test evidence, not a complete browser visual/accessibility audit or a security certification. Remaining coverage and run commands are in [README.md](README.md).
