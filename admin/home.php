<?php
$page='admin/home.php';
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
    <meta name="description" content="Recyclables Administrator: Home.">
    <title>Home — Recyclables</title>
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
        <a class="sidebar-link is-active" href="../admin/home.php" aria-current="page">
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
      <span class="workspace-location">Home</span>
      <div class="account-chip">
        <strong>Administrator</strong>
      </div>
    </header>
    <!-- Main page content -->
    <main id="main-content" class="workspace-content">
      <!-- Page title and actions -->
      <?php include __DIR__ . '/../includes/notices.php'; ?><div class="page-header">
        <div>
          <h1>Home</h1>
          <p class="context-line">Administrator</p>
          <p class="page-description">A clear view of the platform, the people, and the progress we share.</p>
        </div>
      </div>
      <div class="stack">
        <div class="status-banner">
          <div>
            <h2>Good. Keep the campus growing.</h2>
            <p class="context-line">Platform status</p>
            <p>No point corrections are awaiting review.</p>
          </div>
          <a class="button button--lime" href="../admin/leaderboard-point-corrections.php">Review corrections</a>
        </div>
        <div class="metric-grid">
          <article class="metric">
            <div class="metric-label">
              Total users
              <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                <path d="M20 4c0 9-4 14-10 14-4 0-6-2-6-6C4 6 11 4 20 4Z"></path>
                <path d="m4 21 11-11"></path>
              </svg>
            </div>
            <p class="metric-value"><?php echo e(metric('Total users  No records available')); ?></p>
            <p class="metric-note">Based on saved records</p>
          </article>
          <article class="metric">
            <div class="metric-label">
              Active challenges
              <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                <path d="M20 4c0 9-4 14-10 14-4 0-6-2-6-6C4 6 11 4 20 4Z"></path>
                <path d="m4 21 11-11"></path>
              </svg>
            </div>
            <p class="metric-value"><?php echo e(metric('Active challenges  No records available')); ?></p>
            <p class="metric-note">Based on saved records</p>
          </article>
          <article class="metric">
            <div class="metric-label">
              Submissions
              <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                <path d="M20 4c0 9-4 14-10 14-4 0-6-2-6-6C4 6 11 4 20 4Z"></path>
                <path d="m4 21 11-11"></path>
              </svg>
            </div>
            <p class="metric-value"><?php echo e(metric('Submissions  No records available')); ?></p>
            <p class="metric-note">Based on saved records</p>
          </article>
          <article class="metric">
            <div class="metric-label">
              Active today
              <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                <path d="M20 4c0 9-4 14-10 14-4 0-6-2-6-6C4 6 11 4 20 4Z"></path>
                <path d="m4 21 11-11"></path>
              </svg>
            </div>
            <p class="metric-value"><?php echo e(metric('Active today  No records available')); ?></p>
            <p class="metric-note">Based on saved records</p>
          </article>
        </div>
        <div class="grid-2">
          <section class="panel">
            <div class="panel-heading">
              <h2>People making progress</h2>
              <p class="muted">Participation appears here when records are available.</p>
            </div>
            <figure class="chart">
              <svg class="chart-svg" viewbox="0 0 493 240" role="img" aria-label="No data available">
                <text x="246.5" y="120.0" text-anchor="middle" dominant-baseline="middle" fill="currentColor" font-size="16">
                  No data available
                </text>
              </svg>
              <figcaption class="muted">No data available</figcaption>
              <details class="disclosure">
                <summary>View chart data</summary>
                <div class="table-wrap">
                  <table class="data-table">
                    <caption class="visually-hidden">Participant count this week data</caption>
                    <thead>
                      <tr>
                        <th scope="col">Period</th>
                        <th scope="col">Participants</th>
                      </tr>
                    </thead>
                    <tbody><?php $kind='participants-by-month'; $columns=['Period', 'Participants']; include __DIR__ . '/../includes/table.php'; ?></tbody>
                  </table>
                </div>
              </details>
            </figure>
          </section>
          <div class="stack">
            <section class="panel panel--sage">
              <div class="panel-heading">
                <h2>Review now</h2>
              </div>
              <div class="queue-row">
                <div>
                  <strong>Point corrections</strong>
                  <p class="muted">No corrections to review.</p>
                </div>
                <span class="queue-count">—</span>
              </div>
              <a class="text-link" href="../admin/leaderboard-point-corrections.php">Open the review list <span aria-hidden="true">↗</span> </a>
            </section>
            <section class="panel">
              <div class="panel-heading">
                <h2>Sustainability impact</h2>
              </div>
              <p class="eyebrow">Reported waste collected</p>
              <p class="display-number">—</p>
              <p class="muted">Recorded from reviewed submissions and faculty reports.</p>
              <a class="text-link" href="../admin/impact-analytics.php">Explore campus impact <span aria-hidden="true">↗</span> </a>
            </section>
          </div>
        </div>
      </div>
    </main>
  <script src="../scripts/app.js" defer></script></body>
</html>