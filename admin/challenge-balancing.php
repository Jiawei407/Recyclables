<?php
$page='admin/challenge-balancing.php';
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
    <meta name="description" content="Recyclables Administrator: Challenge Balancing.">
    <title>Challenge Balancing — Recyclables</title>
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
        <a class="sidebar-link is-active" href="../admin/challenge-balancing.php" aria-current="page">
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
      <span class="workspace-location">Challenge Balancing</span>
      <div class="account-chip">
        <strong>Administrator</strong>
      </div>
    </header>
    <!-- Main page content -->
    <main id="main-content" class="workspace-content">
      <!-- Page title and actions -->
      <?php include __DIR__ . '/../includes/notices.php'; ?><div class="page-header">
        <div>
          <h1>Challenge Balancing</h1>
          <p class="context-line">Administrator</p>
          <p class="page-description">Review challenge performance and make considered changes.</p>
        </div>
        <p class="note">No record selected.</p>
      </div>
      <div class="stack">
        <div class="metric-grid">
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
              Low participation
              <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                <path d="M20 4c0 9-4 14-10 14-4 0-6-2-6-6C4 6 11 4 20 4Z"></path>
                <path d="m4 21 11-11"></path>
              </svg>
            </div>
            <p class="metric-value"><?php echo e(metric('Low participation  No records available')); ?></p>
            <p class="metric-note">Based on saved records</p>
          </article>
          <article class="metric">
            <div class="metric-label">
              Low completion
              <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                <path d="M20 4c0 9-4 14-10 14-4 0-6-2-6-6C4 6 11 4 20 4Z"></path>
                <path d="m4 21 11-11"></path>
              </svg>
            </div>
            <p class="metric-value"><?php echo e(metric('Low completion  No records available')); ?></p>
            <p class="metric-note">Based on saved records</p>
          </article>
        </div>
        <section class="panel">
          <div class="panel-heading">
            <h2>Challenge performance</h2>
          </div>
          <div class="table-wrap">
            <table class="data-table">
              <caption class="visually-hidden">Challenge participation and completion ratios</caption>
              <thead>
                <tr>
                  <th scope="col">Challenge</th>
                  <th scope="col">Joined / eligible</th>
                  <th scope="col">Participation</th>
                  <th scope="col">Completed / joined</th>
                  <th scope="col">Completion</th>
                  <th scope="col">Action</th>
                </tr>
              </thead>
              <tbody><?php $kind='challenges'; $columns=['Challenge', 'Joined / eligible', 'Participation', 'Completed / joined', 'Completion', 'Action']; include __DIR__ . '/../includes/table.php'; ?></tbody>
            </table>
          </div>
        </section>
        <section class="panel">
          <div class="panel-heading">
            <h2>Edit challenge</h2>
            <p class="muted">Balancing is a human edit to points, dates, content and requirements.</p>
          </div>
          <div class="stack" id="edit-challenge" role="form">
            <div class="form-grid">
              <div class="field">
                <label for="challenge-title">Title</label>
                <input id="challenge-title" name="challenge-title" type="text" placeholder="Title" required form="backend-form" value="<?php echo value('challenge-title','title',''); ?>">
              </div>
              <div class="field">
                <label for="challenge-points">Green Points</label>
                <input id="challenge-points" name="challenge-points" type="number" placeholder="Green Points" min="0" step="1" form="backend-form" value="<?php echo value('challenge-points','points','0'); ?>">
              </div>
              <div class="field">
                <label for="challenge-end-date">End date</label>
                <input id="challenge-end-date" name="challenge-end-date" type="date" form="backend-form" value="<?php echo value('challenge-end-date','end_date',''); ?>">
              </div>
              <div class="field">
                <label for="challenge-status">Status</label>
                <select id="challenge-status" name="challenge-status" form="backend-form">
                  <option value="open" <?php echo selected('challenge-status','status','open','open') ? 'selected' : ''; ?>>Open</option><option value="closed" <?php echo selected('challenge-status','status','closed','open') ? 'selected' : ''; ?>>Closed</option><option value="draft" <?php echo selected('challenge-status','status','draft','open') ? 'selected' : ''; ?>>Draft</option></select>
              </div>
              <div class="field">
                <label for="challenge-description">Description</label>
                <textarea id="challenge-description" name="challenge-description" rows="4" placeholder="Description" form="backend-form"><?php echo value('challenge-description','description'); ?></textarea>
              </div>
              <div class="field">
                <label for="completion-requirement">Completion requirement</label>
                <input id="completion-requirement" name="completion-requirement" type="text" placeholder="Completion requirement" form="backend-form" value="<?php echo value('completion-requirement','completion_requirement',''); ?>">
              </div>
            </div>
            <div class="upload-preview">
              <svg class="upload-thumbnail challenge-illustration" viewbox="0 0 600 600" width="600" height="600" role="img" aria-label="No image selected">
                <rect width="600" height="600" fill="var(--sage)"></rect>
                <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" fill="currentColor" font-size="24">
                  No image selected
                </text>
              </svg>
              <div>
                <strong>Challenge illustration</strong>
                <a class="button button--outline" href="../admin/challenge-balancing-upload-challenge-photo.php">Change image</a>
              </div>
            </div>
            <div class="form-actions">
              <button class="button button--primary" type="submit" form="backend-form">Save changes</button>
              <a class="button button--danger" href="../admin/challenge-balancing-delete-challenge.php<?php echo $id ? '?id=' . (int)$id : ''; ?>">Delete challenge</a>
            </div>
          </div>
        </section>
        <aside class="notice">
          <div>
            <strong>Make changes with context</strong>
            <p>Compare participation and completion before adjusting a challenge. Keep instructions feasible and rewards proportionate to the task.</p>
          </div>
        </aside>
      </div>
    <form id="backend-form" method="post" enctype="multipart/form-data" hidden=""><?php echo csrf_field(); ?><input type="hidden" name="action" value="save-challenge" form="backend-form"><input type="hidden" name="id" value="<?php echo (int)$id; ?>" form="backend-form"></form></main>
  <script src="../scripts/app.js" defer></script></body>
</html>