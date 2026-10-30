<?php
$page='admin/users-create-user.php';
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
    <meta name="description" content="Recyclables Administrator: Users.">
    <title>Users — Recyclables</title>
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
        <a class="sidebar-link is-active" href="../admin/users-users.php" aria-current="page">
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
      <span class="workspace-location">Users</span>
      <div class="account-chip">
        <strong>Administrator</strong>
      </div>
    </header>
    <!-- Main page content -->
    <main id="main-content" class="workspace-content">
      <!-- Page title and actions -->
      <?php include __DIR__ . '/../includes/notices.php'; ?><div class="page-header">
        <div>
          <h1>Users</h1>
          <p class="context-line">Administrator</p>
          <p class="page-description">Give a new member a clear place on the platform.</p>
        </div>
      </div>
      <div class="stack">
        <nav class="page-tabs" aria-label="Section views">
          <a href="../admin/users-users.php">Users</a>
          <a href="../admin/users-create-user.php" class="is-active" aria-current="page">Create user</a>
          <a href="../admin/users-edit-user.php">Edit user</a>
        </nav>
        <section class="panel">
          <div class="panel-heading">
            <h2>Create a user</h2>
            <p class="muted">Review the account details before selecting permissions.</p>
          </div>
          <div class="stack" data-native-validation="" aria-label="Create user account">
            <div class="form-grid">
              <div class="field">
                <label for="full-name">Full name</label>
                <input id="full-name" type="text" placeholder="Full name" required form="backend-form" name="full-name" value="<?php echo value('full-name','full_name',''); ?>">
              </div>
              <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" placeholder="name@example.com" required maxlength="254" pattern="[^\s@]+@[^\s@]+\.[^\s@]+" autocomplete="email" aria-describedby="email-error" form="backend-form" name="email" value="<?php echo value('email','email',''); ?>">
                <p class="field-error" id="email-error">Enter a valid email address, for example name@example.com.</p>
              </div>
              <div class="field">
                <label for="university-id">University ID</label>
                <input id="university-id" type="text" placeholder="University ID" required form="backend-form" name="university-id" value="<?php echo value('university-id','university_id',''); ?>">
              </div>
              <div class="field">
                <label for="faculty">Faculty</label>
                <select id="faculty" form="backend-form" name="faculty">
                  <option value="" <?php echo selected('faculty','faculty','','') ? 'selected' : ''; ?>>Choose an option</option>
                  <option value="School of Computing" <?php echo selected('faculty','faculty','School of Computing','') ? 'selected' : ''; ?>>School of Computing</option>
                  <option value="School of Engineering" <?php echo selected('faculty','faculty','School of Engineering','') ? 'selected' : ''; ?>>School of Engineering</option>
                  <option value="School of Business" <?php echo selected('faculty','faculty','School of Business','') ? 'selected' : ''; ?>>School of Business</option>
                  <option value="Campus Sustainability" <?php echo selected('faculty','faculty','Campus Sustainability','') ? 'selected' : ''; ?>>Campus Sustainability</option>
                </select>
              </div>
              <div class="field">
                <label for="designated-role">Designated role</label>
                <select id="designated-role" form="backend-form" name="designated-role">
                  <option value="" <?php echo selected('designated-role','role','','') ? 'selected' : ''; ?>>Choose an option</option>
                  <option value="user" <?php echo selected('designated-role','role','user','') ? 'selected' : ''; ?>>Registered User</option>
                  <option value="faculty" <?php echo selected('designated-role','role','faculty','') ? 'selected' : ''; ?>>Faculty Representative</option>
                  <option value="coordinator" <?php echo selected('designated-role','role','coordinator','') ? 'selected' : ''; ?>>Sustainability Coordinator</option>
                  <option value="admin" <?php echo selected('designated-role','role','admin','') ? 'selected' : ''; ?>>Administrator</option>
                </select>
              </div>
              <div class="field">
                <label for="account-status">Account status</label>
                <select id="account-status" form="backend-form" name="account-status">
                  <option value="" <?php echo selected('account-status','status','','active') ? 'selected' : ''; ?>>Choose an option</option>
                  <option value="active" <?php echo selected('account-status','status','active','active') ? 'selected' : ''; ?>>Active</option>
                  <option value="suspended" <?php echo selected('account-status','status','suspended','active') ? 'selected' : ''; ?>>Suspended</option>
                  <option value="inactive" <?php echo selected('account-status','status','inactive','active') ? 'selected' : ''; ?>>Inactive</option>
                </select>
              </div>
              <div class="field">
                <label for="new-password">Set password</label>
                <input title="Use 8-72 characters including a special character, for example @, ! or #." maxlength="72" minlength="8" pattern="(?=.*[^\p{L}\p{N}\s]).{8,72}" id="new-password" type="password" autocomplete="new-password" form="backend-form" name="new-password" required="">
                <small class="field-help">Enter the account details.</small>
              </div>
              <div class="field">
                <label for="confirm-password">Confirm password</label>
                <input title="Use 8-72 characters including a special character, for example @, ! or #." maxlength="72" minlength="8" pattern="(?=.*[^\p{L}\p{N}\s]).{8,72}" id="confirm-password" type="password" autocomplete="new-password" form="backend-form" name="confirm-password" required="">
              </div>
            </div>
            <fieldset class="check-group">
              <legend>Permissions</legend>
              <label class="check-option">
                <input type="checkbox" form="backend-form" name="can-join" value="1" checked>
                <span>Join challenges</span>
              </label>
              <label class="check-option">
                <input type="checkbox" form="backend-form" name="can-submit" value="1" checked>
                <span>Submit evidence</span>
              </label>
              </fieldset>
            <div class="form-actions">
              <button class="button button--primary" type="submit" form="backend-form">Create user</button>
              <a class="button button--outline" href="../admin/users-users.php">Cancel</a>
            </div>
          </div>
        </section>
      </div>
    <form id="backend-form" method="post" enctype="multipart/form-data" hidden=""><?php echo csrf_field(); ?><input type="hidden" name="action" value="save-user" form="backend-form"><input type="hidden" name="id" value="<?php echo (int)$id; ?>" form="backend-form"></form></main>
  <script src="../scripts/app.js" defer></script></body>
</html>