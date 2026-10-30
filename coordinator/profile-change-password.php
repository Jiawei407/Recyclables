<?php
$page='coordinator/profile-change-password.php';
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
    <meta name="description" content="Recyclables Sustainability Coordinator: Your account.">
    <title>Your account — Recyclables</title>
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
        <a class="sidebar-link" href="../coordinator/analytics.php">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <path d="M4 3v17h17M8 16v-4m5 4V7m5 9v-6"></path>
          </svg>
          <span>Analytics</span>
        </a>
        <a class="sidebar-link is-active" href="../coordinator/profile-manage-account.php" aria-current="page">
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
      <span class="workspace-location">Profile</span>
      <div class="account-chip">
        <strong>Sustainability Coordinator</strong>
      </div>
    </header>
    <!-- Main page content -->
    <main id="main-content" class="workspace-content">
      <!-- Page title and actions -->
      <?php include __DIR__ . '/../includes/notices.php'; ?><div class="page-header">
        <div>
          <h1><?php echo e($user['full_name'] ?? 'Your account'); ?></h1>
          <p class="context-line">Sustainability Coordinator</p>
          <p class="page-description">A straightforward place for account and password details.</p>
        </div>
      </div>
      <nav class="page-tabs" aria-label="Account views">
        <a href="../coordinator/profile-manage-account.php#account-summary">Profile</a>
        <a href="../coordinator/profile-manage-account.php">Manage account</a>
        <a href="../coordinator/profile-change-password.php" class="is-active" aria-current="page">Change password</a>
      </nav>
      <div class="panel stack" aria-label="Change password" role="form">
        <h2>Change password</h2>
        <p class="note">Choose a password you do not use elsewhere.</p>
        <div class="form-grid">
          <div class="field">
            <label for="current-password">Current password</label>
            <input id="current-password" name="current-password" type="password" autocomplete="current-password" placeholder="Enter current password" form="backend-form" required>
          </div>
          <div class="field">
            <label for="new-password">New password</label>
            <input title="Use 8-72 characters including a special character, for example @, ! or #." pattern="(?=.*[^\p{L}\p{N}\s]).{8,72}" id="new-password" name="new-password" type="password" autocomplete="new-password" placeholder="Enter new password" minlength="8" maxlength="72" form="backend-form" required>
            <p class="field-hint">Use at least eight characters.</p>
          </div>
          <div class="field">
            <label for="confirm-password">Confirm password</label>
            <input title="Use 8-72 characters including a special character, for example @, ! or #." pattern="(?=.*[^\p{L}\p{N}\s]).{8,72}" id="confirm-password" name="confirm-password" type="password" autocomplete="new-password" placeholder="Re-enter new password" minlength="8" maxlength="72" form="backend-form" required>
          </div>
        </div>
        <div class="form-actions">
          <button class="button button--primary" type="submit" form="backend-form">Save password</button>
          <a class="button button--outline" href="../coordinator/profile-manage-account.php">Cancel</a>
        </div>
      </div>
    <form id="backend-form" method="post" enctype="multipart/form-data" hidden=""><?php echo csrf_field(); ?><input type="hidden" name="action" value="password" form="backend-form"><input type="hidden" name="id" value="<?php echo (int)$id; ?>" form="backend-form"></form></main>
  <script src="../scripts/app.js" defer></script></body>
</html>
