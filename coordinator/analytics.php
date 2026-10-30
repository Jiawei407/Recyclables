<?php
$page='coordinator/analytics.php';
$page_role='coordinator';
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/data.php';
require __DIR__ . '/../includes/metrics.php';
?>
<!DOCTYPE html>
<html lang="en" class="workspace-page">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recyclables Sustainability Coordinator: Campus analytics.">
    <title>Campus analytics — Recyclables</title>
    <link rel="icon" href="../assets/brand-mark.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../styles.css">
  </head>
  <body class="app-shell">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <!-- Workspace navigation -->
    <aside class="app-sidebar" aria-label="Sustainability Coordinator workspace">
      <a class="brand" href="../index.php">
        <img src="../assets/brand-mark.svg" alt="" width="32" height="32">
        <span>recyclables.</span>
      </a>
      <div class="sidebar-context">
        <span class="eyebrow">Your workspace</span>
        <strong>Sustainability Coordinator</strong>
      </div>
      <nav class="sidebar-nav" aria-label="Workspace navigation">
        <a class="sidebar-link" href="../coordinator/home.php">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <path d="m3 10 9-7 9 7v10H3V10Zm6 10v-7h6v7"></path>
          </svg>
          <span>Home</span>
        </a>
        <a class="sidebar-link" href="../coordinator/challenges-all-challenges.php">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <path d="M20 4c0 9-4 14-10 14-4 0-6-2-6-6C4 6 11 4 20 4Z"></path>
            <path d="m4 21 11-11"></path>
          </svg>
          <span>Challenges</span>
        </a>
        <a class="sidebar-link" href="../coordinator/submissions-pending.php">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <path d="m5 12 4 4L19 6"></path>
          </svg>
          <span>Submissions</span>
        </a>
        <a class="sidebar-link" href="../coordinator/campaigns-campaigns.php">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <rect x="3" y="5" width="18" height="16" rx="3"></rect>
            <path d="M7 3v4m10-4v4M3 11h18"></path>
          </svg>
          <span>Campaigns</span>
        </a>
        <a class="sidebar-link" href="../coordinator/announcements-all-announcements.php">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <path d="m3 10 17-6v15L3 14v-4Zm4 5 2 6h4l-2-5"></path>
          </svg>
          <span>Announcements</span>
        </a>
        <a class="sidebar-link is-active" href="../coordinator/analytics.php" aria-current="page">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <path d="M4 3v17h17M8 16v-4m5 4V7m5 9v-6"></path>
          </svg>
          <span>Analytics</span>
        </a>
        <a class="sidebar-link" href="../coordinator/profile-manage-account.php">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <circle cx="12" cy="12" r="4"></circle>
            <path d="M12 2v3m0 14v3M2 12h3m14 0h3M5 5l2 2m10 10 2 2M5 19l2-2M17 7l2-2"></path>
          </svg>
          <span>Profile</span>
        </a>
      </nav>
      <div class="sidebar-bottom">
        <a class="sidebar-back" href="../index.php">Back to public home</a>
      <form method="post"><?php echo csrf_field(); ?><input type="hidden" name="action" value="logout"><button class="button button--quiet" type="submit">Log out</button></form></div>
    </aside>
    <!-- Account and location -->
    <header class="workspace-header">
      <span class="workspace-location">Analytics</span>
      <div class="account-chip">
        <strong>Sustainability Coordinator</strong>
      </div>
    </header>
    <!-- Main page content -->
    <main id="main-content" class="workspace-content">
      <!-- Page title and actions -->
      <?php include __DIR__ . '/../includes/notices.php'; ?><div class="page-header">
        <div>
          <h1>Campus analytics</h1>
          <p class="context-line">Sustainability Coordinator</p>
          <p class="page-description">Use participation and completed actions to understand the program’s progress.</p>
        </div>
      </div>
      <div class="stack">
        <div class="grid-3">
          <article class="metric">
            <div class="metric-label">
              Most popular
              <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                <circle cx="9" cy="7" r="3"></circle>
                <path d="M3 20v-2a6 6 0 0 1 12 0v2M16 4a3 3 0 0 1 0 6m2 4a5 5 0 0 1 3 4v2"></path>
              </svg>
            </div>
            <p class="metric-value"><?php echo e(metric('Most popular  No records available')); ?></p>
            <p class="metric-note">Based on saved records</p>
          </article>
          <article class="metric">
            <div class="metric-label">
              Least popular
              <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                <path d="M20 4c0 9-4 14-10 14-4 0-6-2-6-6C4 6 11 4 20 4Z"></path>
                <path d="m4 21 11-11"></path>
              </svg>
            </div>
            <p class="metric-value"><?php echo e(metric('Least popular  No records available')); ?></p>
            <p class="metric-note">Based on saved records</p>
          </article>
          <article class="metric">
            <div class="metric-label">
              Completion rate
              <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                <path d="m5 12 4 4L19 6"></path>
              </svg>
            </div>
            <p class="metric-value"><?php echo e(metric('Completion rate  No records available')); ?></p>
            <p class="metric-note">Based on saved records</p>
          </article>
        </div>
        <div class="grid-2">
          <section class="panel stack" aria-labelledby="activity-title">
            <h2 id="activity-title">Campus activity</h2>
            <p class="note">No reporting period selected</p>
            <figure class="chart-figure">
              <figcaption>Approved activities by month</figcaption>
              <div><?php include __DIR__ . '/../includes/chart.php'; ?></div></figure>
            <details>
              <summary>Daily and weekly views</summary>
              <p>Daily and weekly summaries will appear when participation data is available.</p>
            </details>
          </section>
          <section class="panel stack" aria-labelledby="faculty-chart-title">
            <h2 id="faculty-chart-title">Participation by faculty</h2>
            <p class="note">No participation data available.</p>
            <div class="progress-block">
              <div class="progress-label">
                <span>School of Computing</span>
                <strong><?php echo (int)one('SELECT COUNT(DISTINCT uc.user_id) AS total FROM user_challenges uc JOIN users u ON u.id=uc.user_id WHERE u.faculty=?',['School of Computing'])['total']; ?></strong>
              </div>
              <progress value="<?php echo (int)one('SELECT COUNT(DISTINCT uc.user_id) AS total FROM user_challenges uc JOIN users u ON u.id=uc.user_id WHERE u.faculty=?',['School of Computing'])['total']; ?>" max="<?php echo max(1,(int)one('SELECT COUNT(*) AS total FROM users WHERE faculty=?',['School of Computing'])['total']); ?>" aria-label="School of Computing">Faculty participants</progress>
              <p class="field-hint">Members who have joined a challenge</p>
            </div>
            <div class="progress-block">
              <div class="progress-label">
                <span>School of Engineering</span>
                <strong><?php echo (int)one('SELECT COUNT(DISTINCT uc.user_id) AS total FROM user_challenges uc JOIN users u ON u.id=uc.user_id WHERE u.faculty=?',['School of Engineering'])['total']; ?></strong>
              </div>
              <progress value="<?php echo (int)one('SELECT COUNT(DISTINCT uc.user_id) AS total FROM user_challenges uc JOIN users u ON u.id=uc.user_id WHERE u.faculty=?',['School of Engineering'])['total']; ?>" max="<?php echo max(1,(int)one('SELECT COUNT(*) AS total FROM users WHERE faculty=?',['School of Engineering'])['total']); ?>" aria-label="School of Engineering">Faculty participants</progress>
              <p class="field-hint">Members who have joined a challenge</p>
            </div>
            <div class="progress-block">
              <div class="progress-label">
                <span>School of Business</span>
                <strong><?php echo (int)one('SELECT COUNT(DISTINCT uc.user_id) AS total FROM user_challenges uc JOIN users u ON u.id=uc.user_id WHERE u.faculty=?',['School of Business'])['total']; ?></strong>
              </div>
              <progress value="<?php echo (int)one('SELECT COUNT(DISTINCT uc.user_id) AS total FROM user_challenges uc JOIN users u ON u.id=uc.user_id WHERE u.faculty=?',['School of Business'])['total']; ?>" max="<?php echo max(1,(int)one('SELECT COUNT(*) AS total FROM users WHERE faculty=?',['School of Business'])['total']); ?>" aria-label="School of Business">Faculty participants</progress>
              <p class="field-hint">Members who have joined a challenge</p>
            </div>
            <div class="progress-block">
              <div class="progress-label">
                <span>School of Media</span>
                <strong><?php echo (int)one('SELECT COUNT(DISTINCT uc.user_id) AS total FROM user_challenges uc JOIN users u ON u.id=uc.user_id WHERE u.faculty=?',['School of Media'])['total']; ?></strong>
              </div>
              <progress value="<?php echo (int)one('SELECT COUNT(DISTINCT uc.user_id) AS total FROM user_challenges uc JOIN users u ON u.id=uc.user_id WHERE u.faculty=?',['School of Media'])['total']; ?>" max="<?php echo max(1,(int)one('SELECT COUNT(*) AS total FROM users WHERE faculty=?',['School of Media'])['total']); ?>" aria-label="School of Media">Faculty participants</progress>
              <p class="field-hint">Members who have joined a challenge</p>
            </div>
          </section>
        </div>
        <section class="panel stack" aria-labelledby="analytics-table-title">
          <h2 id="analytics-table-title">Challenge performance</h2>
          <div class="table-wrap">
            <table class="data-table">
              <caption class="visually-hidden">participation and completion by challenge</caption>
              <thead>
                <tr>
                  <th scope="col">Activity</th>
                  <th scope="col">Participants</th>
                  <th scope="col">Completion</th>
                  <th scope="col">Trend</th>
                </tr>
              </thead>
              <tbody><?php $kind='challenges'; $columns=['Activity', 'Participants', 'Completion', 'Trend']; include __DIR__ . '/../includes/table.php'; ?></tbody>
            </table>
          </div>
          <details>
            <summary class="icon-disclosure" title="Show campus challenge analytics breakdown">
              <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                <path d="m6 9 6 6 6-6"></path>
              </svg>
              <span class="disclosure-open-label visually-hidden">Show campus challenge analytics breakdown</span>
              <span class="disclosure-collapse-label">Close details<span class="visually-hidden">: campus challenge analytics breakdown</span> </span>
            </summary>
            <div class="stack">
              <p>Participant counts describe joined challenges. Completion uses approved evidence divided by joined records. Trend compares this period with the previous period.</p>
              <dl class="detail-list">
                <div>
                  <dt>Period</dt>
                  <dd><?php echo e($record['period'] ?? 'Not selected'); ?></dd>
                </div>
                <div>
                  <dt>Evidence source</dt>
                  <dd>—</dd>
                </div>
                <div>
                  <dt>Scope</dt>
                  <dd>—</dd>
                </div>
              </dl>
            </div>
          </details>
        </section>
      </div>
    </main>
  <script src="../scripts/app.js" defer></script></body>
</html>