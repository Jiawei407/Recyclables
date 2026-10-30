# Recyclables - simple PHP/MySQL version

The October 29 frontend is retained. styles.css and every original asset are unchanged. The 93 HTML page routes are now PHP. Working forms use ordinary POST requests, PHP sessions and MySQLi. No framework, Composer, npm or build step is required.

## Run with XAMPP

1. Extract the `Recyclables-PHP` folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL in XAMPP Control Panel.
3. Open phpMyAdmin. Import `database/recyclables_db.sql`.
4. For demonstration accounts, also import `database/demo_accounts.sql`. This is optional and does not replace existing accounts.
5. Open `http://localhost/Recyclables-PHP/`.

If PHP reports a lower upload limit, increase upload_max_filesize to 5M and post_max_size to at least 8M in XAMPP's php.ini, then restart Apache. The application also checks a 5 MB maximum.

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
3. Submit a JPG/PNG photo, a quantity, and a description. Files are limited to 5 MB.
4. Log in as coordinator. Open Pending submissions, choose View, check the evidence and all three review checks, and approve it.
5. Log in as user. Check submission details, reviewer notes, points, achievements and the leaderboard.
6. Try faculty activities, announcements and reports as the faculty account. Faculty records belong to its assigned faculty.
7. As administrator, inspect users, achievements, point corrections and the audit log.

Only pending submissions can be reviewed. Rejection/revision needs notes. A revision is a new evidence submission; the old review remains in history. Pending and approved submissions count against the challenge limit. A repeat approval cannot award points again.

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
