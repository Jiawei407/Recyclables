# Implemented behavior and limits

## Working backend

- Registration, hashed passwords, login/logout, profile changes, password changes and role protection.
- Administrator account creation, editing, suspension, permanent deletion, and join/submit permissions. Deletion removes that account's private participation, evidence, points and awards; shared records and audit entries retain nullable author references. Other accounts' records remain intact.
- Challenge creation, editing, dates, limits, status, eligibility and optional illustration uploads.
- Joining challenges, evidence photo uploads, submission history, reviewer feedback and revised submissions.
- Manual review with approval, rejection or revision and optional notes; transactional points; duplicate photo warnings for human review.
- Individual/faculty rankings based on saved point transactions and administrator deductions with a reason.
- Badge requirements based on approved records and reviewed quantities. Individual qualification is recorded as an award and retained after later quantity/requirement changes. Campaign completion can explicitly award a badge.
- Campaign records, linked challenges, targets, one-time bonus points and optional badges.
- Faculty promotions, scheduled activities, scoped announcements and reports with optional JPG/PNG/HEIC/HEIF/PDF attachments.
- Saved-record participation and completion tables, monthly approved-action charts and audit records.
- Search/category/status filters and simple browser interaction without a JavaScript framework.

## Limits to explain honestly

- Environmental weight/CO2 estimates are not fabricated. Kg/CO2 cards remain unmeasured because the UI and supplied data do not define verified weights or conversion rates. Approved-action and participation totals are available.
- Fraud checking highlights identical file hashes; a human decides whether evidence is valid. It is not image recognition or proof of misconduct. Changing/compressing an image changes its hash.
- Only the faculty activities shown in the UI are stored; there is no attendance-registration workflow for those activities. The participant count in a faculty report is entered by its author.
- Public registration follows the supplied Student ID form. It creates a student user account; staff-only challenges cannot be joined by student accounts.
- Password recovery follows the supplied contact-support message; there is no email-reset service.
- Collective faculty targets use current aggregate totals; they are not individual awards. Individual earned badges persist until their award is explicitly removed or the account is deleted.
- Upload defaults are configurable operational limits: 256 MiB and 256 megapixels (`RECYCLABLES_UPLOAD_MAX_BYTES`, `RECYCLABLES_UPLOAD_MAX_PIXELS`). Configure PHP `upload_max_filesize` and `post_max_size` consistently. These defaults cover the tested 48 MP and larger-than-5-MB fixtures; no particular largest-device-plus-30-MB maximum has been established.
- HEIC/HEIF files receive bounded ISO-BMFF brand, dimensions and media-payload validation and remain in their original format. Pixel decoding/conversion is not performed on the PHP server; browser support determines inline viewing, with a file link available when preview decoding is unavailable.
- Lists are intended for a small assignment demonstration, with browser filtering rather than large-dataset pagination. Audit listing shows the most recent 500 entries.
- Leaderboard period filters do not recalculate historical ranks. All-time balances include administrator corrections. Historical-rank options are removed from those pages to avoid implying unsupported calculations.
- Campaign targets are planning values; reward qualification depends on completing linked challenges, not reaching the campus participant target.
- No automatic difficulty adjustment is made. Challenge balancing is an administrator's manual choice based on participation and completion data.
- The supplied schema has more supporting tables than the earlier planning ERD. Update your final ERD/data dictionary to match DATABASE.md and the SQL file.
