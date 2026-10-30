<?php
$page='admin/audit-log-audit-details.php';
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
    <meta name="description" content="Recyclables Administrator: Audit Log.">
    <title>Audit Log — Recyclables</title>
    <link rel="icon" href="../assets/brand-mark.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../styles.css">
  </head>
  <body class="app-shell">
    <a class="skip-link" href="#main-content">Skip to content</a>
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
        <a class="sidebar-link" href="../admin/leaderboard-view-rankings.php">
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
        <a class="sidebar-link is-active" href="../admin/audit-log.php" aria-current="page">
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
      <span class="workspace-location">Audit Log</span>
      <div class="account-chip">
        <strong>Administrator</strong>
      </div>
    </header>
    <!-- Main page content -->
    <main id="main-content" class="workspace-content">
      <!-- Page title and actions -->
      <?php include __DIR__ . '/../includes/notices.php'; ?><div class="page-header">
        <div>
          <h1>Audit Log</h1>
          <p class="context-line">Administrator</p>
        </div>
      </div>
      <div class="stack">
        <a class="button button--outline" href="../admin/audit-log.php">All audit records</a>
        <article class="panel stack">
          <h2>Audit record</h2>
          <p class="context-line">No audit record selected</p>
          <details class="disclosure" open>
            <summary class="icon-disclosure" title="Hide audit record details">
              <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                <path d="m6 9 6 6 6-6"></path>
              </svg>
              <span class="disclosure-open-label visually-hidden">Hide audit record details</span>
              <span class="disclosure-collapse-label">Close details<span class="visually-hidden">: audit record details</span> </span>
            </summary>
            <dl class="detail-list">
              <div>
                <dt>Administrator</dt>
                <dd>—</dd>
              </div>
              <div>
                <dt>Participant</dt>
                <dd><?php echo e($record['full_name'] ?? 'Not selected'); ?></dd>
              </div>
              <div>
                <dt>Submission</dt>
                <dd><?php echo e($record['id'] ?? 'Not selected'); ?></dd>
              </div>
              <div>
                <dt>Points</dt>
                <dd>—</dd>
              </div>
              <div>
                <dt>Reason</dt>
                <dd>—</dd>
              </div>
            </dl>
          </details>
        </article>
      </div>
    <div class="dialog-content"><?php if ($record) echo '<h3>' . e($record['action']) . '</h3><p>' . e($record['details']) . '</p><p>' . e($record['created_at']) . ' · ' . e($record['full_name']) . '</p>'; ?></div></main>
  <script src="../scripts/app.js" defer></script></body>
</html>