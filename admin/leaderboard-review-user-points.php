<?php
$page='admin/leaderboard-review-user-points.php';
$page_role='admin';
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/data.php';
require __DIR__ . '/../includes/metrics.php';
?>
<!DOCTYPE html>
<html lang="en" class="workspace-page">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recyclables Administrator: Leaderboard.">
    <title>Leaderboard — Recyclables</title>
    <link rel="icon" href="../assets/brand-mark.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../styles.css">
  </head>
  <body>
    <a class="skip-link" href="#dialog-title">Skip to content</a>
    <!-- Keep this background container inert while the dialog is open. -->
    <div class="app-shell" inert>
      <!-- Workspace navigation -->
      <aside class="app-sidebar" aria-label="Administrator workspace">
        <a class="brand" href="../index.php">
          <img src="../assets/brand-mark.svg" alt="" width="32" height="32">
          <span>recyclables.</span>
        </a>
        <div class="sidebar-context">
          <span class="eyebrow">Your workspace</span>
          <strong>Administrator</strong>
        </div>
        <nav class="sidebar-nav" aria-label="Workspace navigation">
          <a class="sidebar-link" href="../admin/home.php">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="m3 10 9-7 9 7v10H3V10Zm6 10v-7h6v7"></path>
            </svg>
            <span>Home</span>
          </a>
          <a class="sidebar-link" href="../admin/users-users.php">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <circle cx="9" cy="7" r="3"></circle>
              <path d="M3 20v-2a6 6 0 0 1 12 0v2M16 4a3 3 0 0 1 0 6m2 4a5 5 0 0 1 3 4v2"></path>
            </svg>
            <span>Users</span>
          </a>
          <a class="sidebar-link" href="../admin/achievements-achievements.php">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <circle cx="12" cy="8" r="5"></circle>
              <path d="m8 12-1 9 5-3 5 3-1-9"></path>
            </svg>
            <span>Achievements</span>
          </a>
          <a class="sidebar-link is-active" href="../admin/leaderboard-view-rankings.php" aria-current="page">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="M4 3v17h17M8 16v-4m5 4V7m5 9v-6"></path>
            </svg>
            <span>Leaderboard</span>
          </a>
          <a class="sidebar-link" href="../admin/reports-faculty-reports.php">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="M5 3h9l5 5v13H5V3Zm9 0v5h5M8 12h8m-8 4h8"></path>
            </svg>
            <span>Reports</span>
          </a>
          <a class="sidebar-link" href="../admin/impact-analytics.php">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="M4 3v17h17M8 16v-4m5 4V7m5 9v-6"></path>
            </svg>
            <span>Impact Analytics</span>
          </a>
          <a class="sidebar-link" href="../admin/challenge-balancing.php">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <circle cx="12" cy="12" r="4"></circle>
              <path d="M12 2v3m0 14v3M2 12h3m14 0h3M5 5l2 2m10 10 2 2M5 19l2-2M17 7l2-2"></path>
            </svg>
            <span>Challenge Balancing</span>
          </a>
          <a class="sidebar-link" href="../admin/audit-log.php">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="M9 6h12M9 12h12M9 18h12M3 6h1m-1 6h1m-1 6h1"></path>
            </svg>
            <span>Audit Log</span>
          </a>
        </nav>
        <div class="sidebar-bottom">
          <a class="sidebar-back" href="../index.php">Back to public home</a>
        <form method="post"><?php echo csrf_field(); ?><input type="hidden" name="action" value="logout"><button class="button button--quiet" type="submit">Log out</button></form></div>
      </aside>
      <!-- Account and location -->
      <header class="workspace-header">
        <span class="workspace-location">Leaderboard</span>
        <div class="account-chip">
          <strong>Administrator</strong>
        </div>
      </header>
      <!-- Main page content -->
      <main id="main-content" class="workspace-content">
        <!-- Page title and actions -->
        <div class="page-header">
          <div>
            <h1>Leaderboard</h1>
            <p class="context-line">Administrator</p>
            <p class="page-description">Review the evidence before making a manual adjustment.</p>
          </div>
          <p class="note">No record selected.</p>
        </div>
        <div class="stack">
          <nav class="page-tabs" aria-label="Section views">
            <a href="../admin/leaderboard-view-rankings.php">View rankings</a>
            <a href="../admin/leaderboard-point-corrections.php" class="is-active" aria-current="page">Point corrections</a>
          </nav>
          <aside class="notice">
            <div>
              <strong>Careful review, clear reasons</strong>
              <p>Compare the evidence and the audit trail before making a manual correction. No automated fraud decision is implied.</p>
            </div>
          </aside>
          <section class="panel">
            <div class="panel-heading">
              <h2>Point corrections</h2>
              <p class="muted">No point corrections are awaiting review.</p>
            </div>
            <div class="table-wrap">
              <table class="data-table">
                <caption class="visually-hidden">Point correction review queue</caption>
                <thead>
                  <tr>
                    <th scope="col">Participant</th>
                    <th scope="col">Green Points</th>
                    <th scope="col">Reason</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody><?php $kind='rankings'; $columns=['Participant', 'Green Points', 'Reason', 'Action']; include __DIR__ . '/../includes/table.php'; ?></tbody>
              </table>
            </div>
          </section>
        </div>
      </main>
    </div>
    <div class="dialog-overlay">
      <!-- Dialog state: close returns to the parent page -->
      <section class="action-dialog" role="dialog" aria-modal="true" aria-labelledby="dialog-title">
        <header class="dialog-header">
          <div>
            <h2 id="dialog-title">Review user points</h2>
            <p class="context-line">Administrator</p>
          </div>
          <a class="close-button" href="../admin/leaderboard-point-corrections.php" aria-label="Close dialog">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="m6 6 12 12M6 18 18 6"></path>
            </svg>
          </a>
        </header>
        <?php include __DIR__ . '/../includes/notices.php'; ?><div class="dialog-content">
          <div class="stack">
            <p class="dialog-subtitle"><?php echo e($record['full_name'] ?? 'Select a participant from the list.'); ?></p>
            <figure class="media-figure evidence-preview">
              <svg viewbox="0 0 900 480" width="900" height="480" role="img" aria-label="No evidence selected">
                <rect width="900" height="480" fill="var(--sage)"></rect>
                <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" fill="currentColor" font-size="24">
                  No evidence selected
                </text>
              </svg>
              <figcaption class="muted">No evidence selected</figcaption>
            </figure>
            <section>
              <h3>Audit trail</h3>
              <div class="table-wrap">
                <table class="data-table">
                  <caption class="visually-hidden">Evidence audit trail</caption>
                  <thead>
                    <tr>
                      <th scope="col">Time</th>
                      <th scope="col">Record</th>
                      <th scope="col">Activity</th>
                    </tr>
                  </thead>
                  <tbody><?php $kind='transactions'; $columns=['Time', 'Record', 'Activity']; include __DIR__ . '/../includes/table.php'; ?></tbody>
                </table>
              </div>
            </section>
            <div class="stack" role="form">
              <div class="form-grid">
                <div class="field field--full">
                  <label for="review-reason">Review reason</label>
                  <textarea id="review-reason" name="review-reason" rows="4" placeholder="Review reason" form="backend-form"></textarea>
                </div>
                <div class="field">
                  <label for="points-to-deduct">Points to deduct</label>
                  <input id="points-to-deduct" name="points-to-deduct" type="number" placeholder="Points to deduct" min="0" step="1" form="backend-form">
                  <small class="field-help">Enter the number of points to deduct after reviewing the evidence.</small>
                </div>
              </div>
              <div class="form-actions">
                <button class="button button--primary" type="submit" form="backend-form" name="decision" value="deduct">Deduct points</button>
                <button class="button button--outline" type="submit" form="backend-form" name="decision" value="approve">Approve</button>
                <button class="button button--outline" type="submit" form="backend-form" name="decision" value="deny">Deny</button>
              </div>
            </div>
          </div>
        </div>
      <form id="backend-form" method="post" enctype="multipart/form-data" hidden=""><?php echo csrf_field(); ?><input type="hidden" name="action" value="correct-points" form="backend-form"><input type="hidden" name="id" value="<?php echo (int)$id; ?>" form="backend-form"></form></section>
    </div>
  <script src="../scripts/app.js" defer></script></body>
</html>