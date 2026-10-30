<?php
$page='faculty/promote-challenges-feature-challenge.php';
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
    <meta name="description" content="Recyclables Faculty Representative: Promote Challenges.">
    <title>Promote Challenges — Recyclables</title>
    <link rel="icon" href="../assets/brand-mark.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../styles.css">
  </head>
  <body>
    <a class="skip-link" href="#dialog-title">Skip to content</a>
    <!-- Keep this background container inert while the dialog is open. -->
    <div class="app-shell" inert>
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
          <a class="sidebar-link" href="../faculty/participation-challenge-participation.php">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <circle cx="9" cy="7" r="3"></circle>
              <path d="M3 20v-2a6 6 0 0 1 12 0v2M16 4a3 3 0 0 1 0 6m2 4a5 5 0 0 1 3 4v2"></path>
            </svg>
            <span>Participation</span>
          </a>
          <a class="sidebar-link is-active" href="../faculty/promote-challenges-available-challenges.php" aria-current="page">
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
        <span class="workspace-location">Promote Challenges</span>
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
        <div class="page-header">
          <div>
            <h1>Promote Challenges</h1>
            <p class="context-line">Faculty Representative</p>
            <p class="page-description">Help your faculty find its next good habit.</p>
          </div>
        </div>
        <div class="stack">
          <nav class="page-tabs" aria-label="Section views">
            <a href="../faculty/promote-challenges-available-challenges.php" class="is-active" aria-current="page">Available challenges</a>
            <a href="../faculty/promote-challenges-featured-for-faculty.php">Featured for faculty</a>
          </nav>
          <div class="toolbar">
            <div class="field">
              <label for="promotion-search">Search challenges</label>
              <input id="promotion-search" name="promotion-search" type="search" placeholder="Search challenges">
            </div>
            <div class="field">
              <label for="promotion-search-filter">Featured state</label>
              <select id="promotion-search-filter" name="promotion-search-filter">
                <option>Not featured</option>
                <option>All challenges</option>
                <option>Featured</option>
              </select>
            </div>
          </div>
          <section class="panel">
            <div class="panel-heading">
              <h2>Available challenges</h2>
            </div>
            <div class="grid-3"><?php include __DIR__ . '/../includes/cards.php'; ?></div>
          </section>
          <p class="note">Featuring a challenge is a faculty promotion choice. It does not change the challenge's points or review rules.</p>
        </div>
      </main>
    </div>
    <div class="dialog-overlay">
      <!-- Dialog state: close returns to the parent page -->
      <section class="action-dialog" role="dialog" aria-modal="true" aria-labelledby="dialog-title">
        <header class="dialog-header">
          <div>
            <h2 id="dialog-title">Feature challenge</h2>
            <p class="context-line">Faculty Representative</p>
          </div>
          <a class="close-button" href="../faculty/promote-challenges-available-challenges.php" aria-label="Close dialog">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="m6 6 12 12M6 18 18 6"></path>
            </svg>
          </a>
        </header>
        <?php include __DIR__ . '/../includes/notices.php'; ?><div class="dialog-content">
          <h3>No challenge selected</h3>
          <p>Choose an available challenge and faculty before featuring it.</p>
          <dl class="detail-list">
            <div>
              <dt>Faculty</dt>
              <dd><?php echo e($record['faculty'] ?? 'Not selected'); ?></dd>
            </div>
            <div>
              <dt>Participants</dt>
              <dd><?php echo e($record['participants'] ?? 'Not selected'); ?></dd>
            </div>
          </dl>
          <div class="form-actions">
            <a class="button button--primary" href="../faculty/promote-challenges-available-challenges.php">Choose a challenge</a>
          </div>
        </div>
      </section>
    </div>
  <script src="../scripts/app.js" defer></script></body>
</html>