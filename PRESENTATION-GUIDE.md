# File-by-file presentation guide

This project follows the lecture pattern: HTML forms send values to PHP, PHP checks the values, MySQLi reads or saves records, and sessions remember the signed-in user. There are no controllers, models, services or framework classes to learn.

## Read these files in order

1. `includes/config.php`: the MySQL connection settings.
2. `includes/bootstrap.php`: start the session before HTML, connect to MySQL, load the current account, check the role and process POST requests.
3. `includes/functions.php`: small shared functions, including escaping, prepared queries, CSRF fields and file validation.
4. `includes/rules.php`: ordinary validation functions and if statements.
5. `actions/accounts.php`: registration, login, logout, profile and password changes.
6. `coordinator/challenges-create.php`: original UI with field names and a POST form. Each visible control uses the HTML `form` attribute to submit to the small `backend-form` on the same page. This avoids moving the existing layout around.
7. `actions/challenges.php`: saving challenges, joining, evidence uploads, manual review, campaign rewards and announcements.
8. `actions/faculty.php`: faculty activities, promotions and reports, always using the logged-in faculty.
9. `actions/admin.php`: administrator account edits, achievement rules and point deductions.
10. `includes/data.php`, `table.php`, `cards.php`: read MySQL records and use ordinary loops to display them in the existing UI.
11. `scripts/app.js`: password confirmation, file previews, list filtering, confirmation messages and dialog keyboard controls. PHP repeats all important validation.
12. `database/recyclables_db.sql`: the database your team must match in the final ERD and data dictionary.

## Suggested explanation by member

| Member / proposal role | First snippet to explain | Second snippet to explain |
| --- | --- | --- |
| Yun He / Registered User | The register and login branches in actions/accounts.php | Joining and submitting evidence in actions/challenges.php |
| Jia Wei / Sustainability Coordinator | The save-challenge branch | The review branch and its database transaction |
| Joachim / Administrator | The save-user branch in actions/admin.php | The correct-points branch and audit record |
| Ern Qi / Faculty Representative | The save-activity branch in actions/faculty.php | The save-report branch and faculty scope |

Use these as locations for your own report explanations. Demonstrate the records before and after each operation rather than memorising the code.

## Key PHP concepts

- `$_POST` receives submitted fields. `name` determines the key.
- `$_SESSION['user_id']` remembers which account logged in. The current role and status are reloaded from MySQL on every request.
- `include` / `require` reuse shared files.
- `query()` is a short MySQLi helper: prepare the SQL, bind values, then execute. User input never becomes part of the SQL command.
- `password_hash()` stores a password hash; `password_verify()` checks a login without storing the original password.
- `e()` escapes text before displaying it in HTML.
- A hidden CSRF token proves a POST came from a form in the current browser session.
- A transaction saves the review decision, points and audit entry together. If one step fails, the transaction rolls back.
- Images are checked on the server for file size, actual MIME type and image dimensions. Random filenames and a protected download page keep evidence separate from decorative assets.

## Demonstration order

Coordinator creates challenge -> user joins -> user submits photo -> coordinator reviews -> user sees points and feedback -> faculty submits report -> administrator views report and audit log.

The original CSS file is unchanged. New controls use existing form classes. The quantity field and challenge tracked-item selector support counted achievement requirements without counting paper recycling as plastic bottles.
