# Browser alert and review-screen update - 30 October 2026

The shared notices include now loads scripts/feedback.js when PHP returns a success or validation message. That script displays window.alert(), producing the browser's native localhost message. Existing confirmation banners remain readable after the alert is dismissed. A normal page load without a message does not display an alert.

The review screen shows the current submission status. Decisions are available only for pending records. Approved, rejected and needs-revision records are read-only; the participant submits new/revised evidence as a new pending record. No decision history or point awards are reset.

When validation fails, its message is also shown in a native alert so the reason cannot be missed. Success is announced only after the existing PHP action completes and redirects.

Verified locally without database access: native alert behavior, duplicate-alert prevention, safe HTML escaping, script loading, actual PHP review-template rendering for every status, unchanged decision guard, and PHP syntax. No new database integration run was performed.

After replacement, refresh the website. No SQL import or database changes are needed. Preserve the existing storage/uploads folder when merging a complete project ZIP.
