# Database tables and relationships

All tables use InnoDB and utf8mb4. Integer `id` fields are auto-incrementing unless a composite key is listed below. Foreign keys preserve relationships. The SQL file is the complete field-level reference for your data dictionary.

| Table | Purpose and relationships |
| --- | --- |
| users | Accounts, hashed passwords, role, faculty, status and join/submit permissions. Unique email and university ID. |
| challenges | Description, schedule, category, reward, submission limit and creator. |
| user_challenges | A user's joined challenges. Composite key user_id + challenge_id prevents duplicate enrolment. |
| submissions | Evidence photo, description, quantity, status, review notes, reviewer and awarded points. Belongs to user and challenge. |
| point_transactions | Positive review/campaign rewards and negative administrator corrections. A unique submission_id prevents double-awarding one evidence record. |
| achievements | Badge name, publication status and image. |
| achievement_rules | Count, item and condition for each badge. All configured requirements must be met. |
| user_achievements | Explicit badge awards from completed campaigns. Composite key user_id + achievement_id. |
| campaigns | Dates, target, bonus points, optional badge and creator. |
| campaign_challenges | The linked campaign challenges. Composite key campaign_id + challenge_id. |
| campaign_awards | One reward per user and campaign. Composite key campaign_id + user_id. |
| announcements | Publication date, audience, optional faculty scope and creator. |
| faculty_activities | Faculty activity date, location, capacity, details and creator. |
| faculty_promotions | Challenges featured for a faculty. Composite key faculty + challenge_id. |
| faculty_reports | Faculty, activity, period, participant count, reported outcome and optional attachment. |
| audit_logs | Actor, action, details and timestamp for review, point, account and challenge changes. |

Faculties are a small fixed list in includes/rules.php, matching the current account UI. They are stored by name to keep this version simple. Faculty representatives cannot change their faculty through their own profile form.

Delete behaviour: users with evidence/points and challenges with evidence cannot be deleted through the application. Suspend a user or close a challenge instead. This preserves presentation and audit history.
