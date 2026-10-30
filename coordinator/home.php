<?php
$page='coordinator/home.php';
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
    <meta name="description" content="Recyclables Sustainability Coordinator: Home.">
    <title>Home — Recyclables</title>
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
        <a class="sidebar-link is-active" href="../coordinator/home.php" aria-current="page">
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
        <a class="sidebar-link" href="../coordinator/analytics.php">
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
      <span class="workspace-location">Home</span>
      <div class="account-chip">
        <strong>Sustainability Coordinator</strong>
      </div>
    </header>
    <!-- Main page content -->
    <main id="main-content" class="workspace-content">
      <!-- Page title and actions -->
      <?php include __DIR__ . '/../includes/notices.php'; ?><div class="page-header">
        <div>
          <h1>Home</h1>
          <p class="context-line">Sustainability Coordinator</p>
          <p class="page-description">A clear view of your campus program and the reviews that need a human eye.</p>
        </div>
      </div>
      <div class="stack">
        <section class="panel panel--forest grid-2" aria-labelledby="program-title">
          <div class="stack">
            <h2 id="program-title">A growing campus.<br>A thoughtful review.</h2>
            <p class="context-line">Program status</p>
            <p>Create challenges and review evidence to start tracking the campus program.</p>
            <div>
              <a class="button button--primary" href="../coordinator/submissions-pending.php">Open review queue</a>
            </div>
          </div>
          <figure class="media-figure">
            <img class="card-media" src="../assets/nature/meadow.jpg" alt="" width="1800" height="1010" loading="lazy">
          </figure>
        </section>
        <div class="grid-2">
          <section class="panel stack" aria-labelledby="urgent-title">
            <h2 id="urgent-title">Needs your attention</h2>
            <p class="context-line">Your next priorities</p>
            <div class="table-wrap">
              <table class="data-table">
                <caption class="visually-hidden">coordinator priority queues</caption>
                <thead>
                  <tr>
                    <th scope="col">Queue</th>
                    <th scope="col">State</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody><?php $kind='submissions'; $columns=['Queue', 'State', 'Action']; include __DIR__ . '/../includes/table.php'; ?></tbody>
              </table>
            </div>
            <p class="note">Evidence and suspected duplicates are checked by a person before a decision is made.</p>
          </section>
          <section class="panel stack" aria-labelledby="participation-title">
            <h2 id="participation-title">Participation</h2>
            <figure class="chart-figure">
              <figcaption>No data available</figcaption>
              <svg class="line-chart" viewbox="0 0 600 190" role="img" aria-label="No data available">
                <text x="300.0" y="95.0" text-anchor="middle" dominant-baseline="middle" fill="currentColor" font-size="16">
                  No data available
                </text>
              </svg>
            </figure>
            <a class="text-link" href="../coordinator/analytics.php">
              Explore analytics
              <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                <path d="M6 18 18 6M6 6h12v12"></path>
              </svg>
            </a>
          </section>
        </div>
        <section class="panel stack" aria-labelledby="target-title">
          <h2 id="target-title">Monthly campaign target</h2>
          <div class="progress-block">
            <div class="progress-label">
              <span>Participants reached</span>
              <strong>—</strong>
            </div>
            <progress value="0" max="1" aria-label="Participants reached">
              No progress data available
            </progress>
            <p class="field-hint">No progress data available.</p>
          </div>
          <p class="note">Celebrate progress across faculties, then use the campaign view to see where a reminder may help.</p>
          <details class="disclosure">
            <summary class="icon-disclosure" title="Show monthly campaign performance">
              <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                <path d="m6 9 6 6 6-6"></path>
              </svg>
              <span class="disclosure-open-label visually-hidden">Show monthly campaign performance</span>
              <span class="disclosure-collapse-label">Close details<span class="visually-hidden">: monthly campaign performance</span> </span>
            </summary>
            <div class="stack">
              <p>No campaign selected</p>
              <dl class="detail-list">
                <div>
                  <dt>Participants</dt>
                  <dd><?php echo e($record['participants'] ?? 'Not selected'); ?></dd>
                </div>
                <div>
                  <dt>Approved actions</dt>
                  <dd>—</dd>
                </div>
                <div>
                  <dt>Campaign period</dt>
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