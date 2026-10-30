<?php
$page='coordinator/announcements-create.php';
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
    <meta name="description" content="Recyclables Sustainability Coordinator: Announcements.">
    <title>Announcements — Recyclables</title>
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
        <a class="sidebar-link is-active" href="../coordinator/announcements-all-announcements.php" aria-current="page">
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
      <span class="workspace-location">Announcements</span>
      <div class="account-chip">
        <strong>Sustainability Coordinator</strong>
      </div>
    </header>
    <!-- Main page content -->
    <main id="main-content" class="workspace-content">
      <!-- Page title and actions -->
      <?php include __DIR__ . '/../includes/notices.php'; ?><div class="page-header">
        <div>
          <h1>Announcements</h1>
          <p class="context-line">Sustainability Coordinator</p>
          <p class="page-description">Keep the message focused on what happens, where and when.</p>
        </div>
      </div>
      <nav class="page-tabs" aria-label="Section views">
        <a href="../coordinator/announcements-all-announcements.php">All announcements</a>
        <a href="../coordinator/announcements-create.php" class="is-active" aria-current="page">Create</a>
        <a href="../coordinator/announcements-edit.php<?php echo $id ? '?id=' . (int)$id : ''; ?>">Edit</a>
      </nav>
      <div class="stack" aria-label="Create announcement" role="form">
        <section class="panel stack" aria-labelledby="announcement-form-title">
          <h2 id="announcement-form-title">Create an announcement</h2>
          <div class="form-grid">
            <div class="field field--full">
              <label for="announcement-title">Title</label>
              <input id="announcement-title" name="announcement-title" type="text" required placeholder="Title" form="backend-form" value="<?php echo value('announcement-title','title',''); ?>">
            </div>
            <div class="field field--full">
              <label for="announcement-message">Message</label>
              <textarea id="announcement-message" name="announcement-message" rows="6" placeholder="Message" form="backend-form"><?php echo value('announcement-message','message'); ?></textarea>
              <p class="field-hint">Include the action, place, date and any evidence instructions.</p>
            </div>
            <div class="field">
              <label for="announcement-date">Publish date</label>
              <input id="announcement-date" name="announcement-date" type="date" form="backend-form" value="<?php echo value('announcement-date','publish_date',''); ?>">
            </div>
            <div class="field">
              <label for="announcement-audience">Audience</label>
              <select id="announcement-audience" name="announcement-audience" form="backend-form">
                <option value="All users" <?php echo selected('announcement-audience','audience','All users','All users') ? 'selected' : ''; ?>>All users</option>
                <option value="Registered participants" <?php echo selected('announcement-audience','audience','Registered participants','All users') ? 'selected' : ''; ?>>Registered participants</option>
                <option value="Faculty representatives" <?php echo selected('announcement-audience','audience','Faculty representatives','All users') ? 'selected' : ''; ?>>Faculty representatives</option>
                <option value="Coordinators" <?php echo selected('announcement-audience','audience','Coordinators','All users') ? 'selected' : ''; ?>>Coordinators</option>
              </select>
            </div>
          </div>
        </section>
        <div class="form-actions">
          <button class="button button--primary" type="submit" form="backend-form">Publish</button>
          <a class="button button--outline" href="../coordinator/announcements-all-announcements.php">Cancel</a>
        </div>
      </div>
    <form id="backend-form" method="post" enctype="multipart/form-data" hidden=""><?php echo csrf_field(); ?><input type="hidden" name="action" value="save-announcement" form="backend-form"><input type="hidden" name="id" value="<?php echo (int)$id; ?>" form="backend-form"></form></main>
  <script src="../scripts/app.js" defer></script></body>
</html>