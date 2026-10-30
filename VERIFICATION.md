# Verification - 29 October 2026

- 276 integration checks passed against a temporary MariaDB database, using PHP 8.2.12 and MariaDB 10.4.32.
- Both SQL files included in this package were imported for those tests; the four demo accounts logged in successfully.
- Tests covered registration and duplicates, session/role checks, challenge creation/joining, validated photo uploads, submission limits, reviews and once-only points, private evidence access, faculty announcements/activities/reports and faculty boundaries, achievements, campaigns and late reviews, administrator corrections/suspension, and profile/password changes.
- All 93 converted UI routes were requested under their intended role. Error pages return their intended HTTP status.
- 9 standalone PHP rule checks and 4 JavaScript filter checks passed.
- Original stylesheet and assets were compared byte for byte with the supplied handoff.
- The account-page structure was checked to retain its two original visible columns.
- An independent read-only code review found three issues; all were fixed and rechecked.

This verifies backend behavior and source preservation. Browser visual testing at every screen width was not performed. See FEATURES-AND-LIMITS.md before planning the demonstration.


## Account revision - 30 October 2026

All 12 existing accounts were updated to the APU student identity and role-specific staff email rules. New passwords contain special characters and are stored as hashes. Existing IDs, names, roles, faculties, statuses and permissions were verified unchanged. The 15 other MySQL table checksums were unchanged.

19 server policy checks, 22 form-policy checks and 12 browser-pattern/role-switching checks passed. Faculty achievement and password-form regression checks passed. PHP syntax checks passed. This is an account-only revision; earlier browser test results do not mean every form has been manually retested under the new credentials.
