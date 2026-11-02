# Recyclables - simple PHP/MySQL version

The public homepage keeps its original frontend. The 82 authenticated role pages use the approved paper/forest/lime layout, functional descriptions and themed selections. Their content comes from the existing live PHP forms and queries. Working forms use ordinary POST requests, PHP sessions and MySQLi. No framework, Composer, npm or build step is required.

Role presentation is shared in `includes/role-presentation.php`, with scoped styles in `assets/role-ui/` and native-backed controls in `scripts/role-controls.js` and `role-selections.js`. It runs after authentication and POST processing, preserves real CSRF tokens, media authorization and form actions, and never adds prototype submission interception or role switching. PHP's DOM/XML extension is required for the new layout (enabled in XAMPP); without it the original role UI remains usable. Existing `styles.css` and `index.php` are unchanged.

## Run with XAMPP

1. Extract the `Recyclables-PHP` folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL in XAMPP Control Panel.
3. Open phpMyAdmin. Import `database/recyclables_db.sql`.
4. For demonstration accounts, also import `database/demo_accounts.sql`. This is optional and does not replace existing accounts.
5. Open `http://localhost/Recyclables-PHP/`.

For the default upload allowance, set `upload_max_filesize=256M` and `post_max_size=300M` in XAMPP's php.ini, then restart Apache. The application validates up to 256 MiB and 256 megapixels by default; `RECYCLABLES_UPLOAD_MAX_BYTES` and `RECYCLABLES_UPLOAD_MAX_PIXELS` can configure lower or higher limits. These are operational defaults, not a measured maximum for every phone model. JPG, PNG and structurally validated HEIC/HEIF are supported; unsupported browser previews offer the original file for download. RAW/DNG is excluded. See `FEATURES-AND-LIMITS.md` for decoding limits.

Database defaults are `127.0.0.1`, port `3306`, database `recyclables_db`, user `root`, empty password. If yours differs, edit `includes/config.php`. Use a new database for this version if an older Recyclables database already exists; its schema may differ. The supplied SQL intentionally does not drop or overwrite existing tables.

The SQL creates the current schema; it is not a migration for an earlier backend. Change the database name in both SQL files and config.php when installing alongside an older database.

## Demo accounts

Every account now uses the revised APU identity policy.

| Role | Email | Password |
| --- | --- | --- |
| user | tp900001@mail.apu.edu.my | `student123@` |
| faculty | faculty@mail.apu.edu.my | `faculty123@` |
| coordinator | coordinator@mail.apu.edu.my | `coordinator123@` |
| admin | admin@mail.apu.edu.my | `admin123@` |

See TEST-ACCOUNTS.md for all accounts, including your existing registration tester and Engineering representative. Suspended accounts remain suspended.

On the existing website the account revision is applied separately; do not re-import database/account_updates.sql after it has already been applied. Fresh installations import the main schema and demo_accounts.sql as usual.

## Try the main workflow

1. Log in as coordinator. Create a challenge with valid dates, points and a submission limit.
2. Log out. Log in as user. Browse and join that challenge.
3. Submit a JPG, PNG or HEIC/HEIF photo, a quantity, and a description.
4. Log in as coordinator. Open Pending submissions, choose View, check the evidence and all three review checks, and approve it.
5. Log in as user. Check submission details, reviewer notes, points, achievements and the leaderboard.
6. Try faculty activities, announcements and reports as the faculty account. Faculty records belong to its assigned faculty.
7. As administrator, inspect users, achievements, point corrections and the audit log.

Only pending submissions can be reviewed. Review notes are optional; approval still requires all three evidence checks. A revision is a new evidence submission; the old review remains in history. Pending and approved submissions count against the challenge limit. A repeat approval cannot award points again. Earned individual achievements remain earned if thresholds later change, and equal-point rankings use the older account first.

Deleting an account permanently removes its private challenge participation, evidence, points and awards. Other accounts, shared challenges and shared faculty records remain; existing audit/shared-record foreign keys retain history with a null author. The confirmation explicitly describes the irreversible removal.

## Tests

Run `C:\xampp\php\php.exe tools/phpunit-9.phar -c phpunit.xml --testsuite Unit,Requirements` and `node --test tests/js/*.test.cjs` before publishing. Database and full HTTP tests use dedicated `recyclables_test_*` schemas only; follow `tests/README.md`. Final layout, browser and regression results are recorded in `ROLE-LAYOUT-VERIFICATION.md`.

Campaigns contain selected challenges. A participant completes a campaign by having approved evidence for every linked challenge, with each evidence record submitted during the campaign dates. The last review may happen after the campaign ends. Bonus points and the optional badge are granted once. Campaigns with issued rewards cannot be edited.

## Files to understand for presentation

Read `PRESENTATION-GUIDE.md` for the file order and role contributions. Read `DATABASE.md` for table relationships and `FEATURES-AND-LIMITS.md` for the precise behavior and remaining limits. Your team should review and understand the implementation, and record assistance in References / Tools Used as described in the assignment brief.

## Optional PHP development server

From this folder run:

```text
C:\xampp\php\php.exe -S localhost:8000 router.php
```

Then open `http://localhost:8000/`. MySQL must still be running. Use the supplied router so private upload and configuration folders cannot be opened directly. XAMPP Apache uses the supplied .htaccess protections.


## Installing this account revision

Copy the Coding folder from the ZIP to C:\xampp\htdocs\Coding. The existing MySQL account update has already been applied; no SQL import is needed on this computer. Use the new credentials in TEST-ACCOUNTS.md. Old account emails and passwords have been replaced.

The ZIP includes the original upload files recovered from your saved project copies. Keep any additional storage/uploads files you have when merging folders. Do not delete the MySQL database or re-import its schema on this existing installation.
