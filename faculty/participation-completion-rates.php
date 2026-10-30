<?php
$page='faculty/participation-completion-rates.php';
$page_role='faculty';
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/data.php';
require __DIR__ . '/../includes/metrics.php';
?>
<!DOCTYPE html>
<html lang="en" class="workspace-page">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recyclables Faculty Representative: Participation.">
    <title>Participation — Recyclables</title>
    <link rel="icon" href="../assets/brand-mark.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../styles.css">
  </head>
  <body class="app-shell">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <!-- Workspace navigation -->
    <aside class="app-sidebar" aria-label="Faculty Representative workspace">
      <a class="brand" href="../index.php">
        <img src="../assets/brand-mark.svg" alt="" width="32" height="32">
        <span>recyclables.</span>
      </a>
      <div class="sidebar-context">
        <span class="eyebrow">Your workspace</span>
        <strong>Faculty Representative</strong>
      </div>
      <nav class="sidebar-nav" aria-label="Workspace navigation">
        <a class="sidebar-link" href="../faculty/home.php">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <path d="m3 10 9-7 9 7v10H3V10Zm6 10v-7h6v7"></path>
          </svg>
          <span>Home</span>
        </a>
        <a class="sidebar-link is-active" href="../faculty/participation-challenge-participation.php" aria-current="page">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <circle cx="9" cy="7" r="3"></circle>
            <path d="M3 20v-2a6 6 0 0 1 12 0v2M16 4a3 3 0 0 1 0 6m2 4a5 5 0 0 1 3 4v2"></path>
          </svg>
          <span>Participation</span>
        </a>
        <a class="sidebar-link" href="../faculty/promote-challenges-available-challenges.php">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <path d="M20 4c0 9-4 14-10 14-4 0-6-2-6-6C4 6 11 4 20 4Z"></path>
            <path d="m4 21 11-11"></path>
          </svg>
          <span>Promote Challenges</span>
        </a>
        <a class="sidebar-link" href="../faculty/faculty-activities-proposed-activities.php">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <rect x="3" y="5" width="18" height="16" rx="3"></rect>
            <path d="M7 3v4m10-4v4M3 11h18"></path>
          </svg>
          <span>Faculty Activities</span>
        </a>
        <a class="sidebar-link" href="../faculty/insights-overview.php">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <path d="M4 3v17h17M8 16v-4m5 4V7m5 9v-6"></path>
          </svg>
          <span>Insights</span>
        </a>
        <a class="sidebar-link" href="../faculty/announcements-announcements.php">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <path d="m3 10 17-6v15L3 14v-4Zm4 5 2 6h4l-2-5"></path>
          </svg>
          <span>Announcements</span>
        </a>
        <a class="sidebar-link" href="../faculty/submit-report-reports.php">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <path d="M5 3h9l5 5v13H5V3Zm9 0v5h5M8 12h8m-8 4h8"></path>
          </svg>
          <span>Submit Report</span>
        </a>
      </nav>
      <div class="sidebar-bottom">
        <a class="sidebar-back" href="../index.php">Back to public home</a>
      <form method="post"><?php echo csrf_field(); ?><input type="hidden" name="action" value="logout"><button class="button button--quiet" type="submit">Log out</button></form></div>
    </aside>
    <!-- Account and location -->
    <header class="workspace-header">
      <span class="workspace-location">Participation</span>
      <div class="account-chip">
        <span class="avatar">—</span>
        <div>
          <strong><?php echo e($user['full_name'] ?? 'Your account'); ?></strong>
          <span><?php echo e($user['faculty'] ?? ''); ?></span>
        </div>
      </div>
    </header>
    <!-- Main page content -->
    <main id="main-content" class="workspace-content">
      <!-- Page title and actions -->
      <?php include __DIR__ . '/../includes/notices.php'; ?><div class="page-header">
        <div>
          <h1>Participation</h1>
          <p class="context-line">Faculty Representative</p>
          <p class="page-description">See how your faculty is getting involved.</p>
        </div>
      </div>
      <div class="stack">
        <nav class="page-tabs" aria-label="Section views">
          <a href="../faculty/participation-challenge-participation.php">Challenge participation</a>
          <a href="../faculty/participation-completion-rates.php" class="is-active" aria-current="page">Completion rates</a>
        </nav>
        <div class="toolbar">
          <div class="field">
            <label for="participation-search">Search participation</label>
            <input id="participation-search" name="participation-search" type="search" placeholder="Search participation">
          </div>
          <div class="field">
            <label for="participation-search-filter">Period</label>
            <select id="participation-search-filter" name="participation-search-filter">
              <option value="">All time</option></select>
          </div>
        </div>
        <div class="grid-2">
          <article class="metric">
            <div class="metric-label">
              Completion rate
              <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                <path d="M20 4c0 9-4 14-10 14-4 0-6-2-6-6C4 6 11 4 20 4Z"></path>
                <path d="m4 21 11-11"></path>
              </svg>
            </div>
            <p class="metric-value"><?php echo e(metric('Completion rate  No activity recorded yet.')); ?></p>
            <p class="metric-note">No activity recorded yet.</p>
          </article>
          <article class="metric">
            <div class="metric-label">
              Completed activities
              <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                <path d="M20 4c0 9-4 14-10 14-4 0-6-2-6-6C4 6 11 4 20 4Z"></path>
                <path d="m4 21 11-11"></path>
              </svg>
            </div>
            <p class="metric-value"><?php echo e(metric('Completed activities  No activity recorded yet.')); ?></p>
            <p class="metric-note">No activity recorded yet.</p>
          </article>
        </div>
        <section class="panel">
          <div class="panel-heading">
            <h2>Completion rates</h2>
          </div>
          <div class="table-wrap">
            <table class="data-table">
              <caption class="visually-hidden">Faculty challenge completion rates</caption>
              <thead>
                <tr>
                  <th scope="col">Challenge</th>
                  <th scope="col">Completed</th>
                  <th scope="col">Rate</th>
                </tr>
              </thead>
              <tbody><?php $kind='challenges'; $columns=['Challenge', 'Completed', 'Rate']; include __DIR__ . '/../includes/table.php'; ?></tbody>
            </table>
          </div>
        </section>
      </div>
    </main>
  <script src="../scripts/app.js" defer></script></body>
</html>