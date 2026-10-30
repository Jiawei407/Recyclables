<?php
$page='user/profile-change-password-validation.php';
$page_role='user';
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/data.php';
require __DIR__ . '/../includes/metrics.php';
?>
<!DOCTYPE html>
<html lang="en" class="workspace-page">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recyclables Registered User: Profile.">
    <title>Profile — Recyclables</title>
    <link rel="icon" href="../assets/brand-mark.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../styles.css">
  </head>
  <body>
    <a class="skip-link" href="#dialog-title">Skip to content</a>
    <!-- Keep this background container inert while the dialog is open. -->
    <div class="app-shell" inert>
      <!-- Workspace navigation -->
      <aside class="app-sidebar" aria-label="Registered User workspace">
        <a class="brand" href="../index.php">
          <img src="../assets/brand-mark.svg" alt="" width="32" height="32">
          <span>recyclables.</span>
        </a>
        <div class="sidebar-context">
          <span class="eyebrow">Your workspace</span>
          <strong>Registered User</strong>
        </div>
        <nav class="sidebar-nav" aria-label="Workspace navigation">
          <a class="sidebar-link" href="../user/home.php">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="m3 10 9-7 9 7v10H3V10Zm6 10v-7h6v7"></path>
            </svg>
            <span>Home</span>
          </a>
          <a class="sidebar-link" href="../user/challenges-browse.php">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="M20 4c0 9-4 14-10 14-4 0-6-2-6-6C4 6 11 4 20 4Z"></path>
              <path d="m4 21 11-11"></path>
            </svg>
            <span>Challenges</span>
          </a>
          <a class="sidebar-link" href="../user/submit-evidence-new-submission.php">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="M12 16V3m-5 5 5-5 5 5M4 16v5h16v-5"></path>
            </svg>
            <span>Submit Evidence</span>
          </a>
          <a class="sidebar-link" href="../user/achievements-earned.php">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <circle cx="12" cy="8" r="5"></circle>
              <path d="m8 12-1 9 5-3 5 3-1-9"></path>
            </svg>
            <span>Achievements</span>
          </a>
          <a class="sidebar-link" href="../user/leaderboard-individual-ranking.php">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="M4 3v17h17M8 16v-4m5 4V7m5 9v-6"></path>
            </svg>
            <span>Leaderboard</span>
          </a>
          <a class="sidebar-link is-active" href="../user/profile-profile.php" aria-current="page">
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
            <h1>Profile</h1>
            <p class="context-line">Registered User</p>
            <p class="page-description">Your campus account, in one place.</p>
          </div>
        </div>
        <div class="stack">
          <nav class="page-tabs" aria-label="Section views">
            <a href="../user/profile-profile.php">Profile</a>
            <a href="../user/profile-edit-details.php" class="is-active" aria-current="page">Edit details</a>
          </nav>
          <section class="panel">
            <div class="panel-heading">
              <h2>Account details</h2>
            </div>
            <div class="stack" data-native-validation="" aria-label="Edit participant account">
              <div class="form-grid">
                <div class="field">
                  <label for="profile-name">Full name</label>
                  <input id="profile-name" type="text" placeholder="Full name" form="backend-form" name="profile-name" value="<?php echo value('profile-name','full_name',''); ?>">
                </div>
                <div class="field">
                  <label for="profile-email">Email</label>
                  <input title="Student emails must end with @mail.apu.edu.my." id="profile-email" type="email" aria-describedby="profile-email-error" required maxlength="254" pattern="[^\s@]+@mail\.apu\.edu\.my" autocomplete="email" placeholder="tp090526@mail.apu.edu.my" form="backend-form" name="profile-email" value="<?php echo value('profile-email','email',''); ?>">
                  <p class="field-error" id="profile-email-error">Use your @mail.apu.edu.my student email address.</p>
                </div>
                <div class="field">
                  <label for="profile-faculty">Faculty</label>
                  <select id="profile-faculty" form="backend-form" name="profile-faculty">
                    <option value="" <?php echo selected('profile-faculty','faculty','','') ? 'selected' : ''; ?>>Choose your faculty</option>
                    <option value="School of Computing" <?php echo selected('profile-faculty','faculty','School of Computing','') ? 'selected' : ''; ?>>School of Computing</option>
                    <option value="School of Business" <?php echo selected('profile-faculty','faculty','School of Business','') ? 'selected' : ''; ?>>School of Business</option>
                    <option value="School of Engineering" <?php echo selected('profile-faculty','faculty','School of Engineering','') ? 'selected' : ''; ?>>School of Engineering</option>
                    <option value="School of Design" <?php echo selected('profile-faculty','faculty','School of Design','') ? 'selected' : ''; ?>>School of Design</option>
                  </select>
                </div>
                <div class="field">
                  <label for="profile-id">University ID</label>
                  <input title="TP followed by six digits, for example TP090526." maxlength="8" pattern="TP[0-9]{6}" id="profile-id" type="text" placeholder="TP090526" form="backend-form" name="profile-id" value="<?php echo value('profile-id','university_id',''); ?>">
                </div>
              </div>
              <div class="form-actions">
                <button class="button button--primary" type="submit" form="backend-form">Save changes</button>
                <a class="button button--outline" href="../user/profile-profile.php">Cancel</a>
              </div>
              <p class="note">Review your details before saving.</p>
            </div>
          </section>
          <section class="panel">
            <div class="panel-heading">
              <h2>Account security</h2>
            </div>
            <p>Choose a password you do not use elsewhere.</p>
            <a class="button button--outline" href="../user/profile-change-password.php">Change password</a>
          </section>
        </div>
      </main>
    </div>
    <div class="dialog-overlay">
      <!-- Dialog state: close returns to the parent page -->
      <section class="action-dialog" role="dialog" aria-modal="true" aria-labelledby="dialog-title">
        <header class="dialog-header">
          <div>
            <h2 id="dialog-title">Passwords do not match</h2>
            <p class="context-line">Registered User</p>
          </div>
          <a class="close-button" href="../user/profile-edit-details.php" aria-label="Close dialog">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="m6 6 12 12M6 18 18 6"></path>
            </svg>
          </a>
        </header>
        <?php include __DIR__ . '/../includes/notices.php'; ?><div class="dialog-content">
          <div class="form-grid">
            <div class="field">
              <label for="password-current">Current password</label>
              <input id="password-current" name="password-current" type="password" placeholder="Current password" form="backend-form">
            </div>
            <div class="field">
              <label for="password-new">New password</label>
              <input title="Use 8-72 characters including a special character, for example @, ! or #." maxlength="72" minlength="8" pattern="(?=.*[^\p{L}\p{N}\s]).{8,72}" id="password-new" name="password-new" type="password" placeholder="New password" form="backend-form">
            </div>
            <div class="field">
              <label for="password-confirm">Confirm password</label>
              <input title="Use 8-72 characters including a special character, for example @, ! or #." maxlength="72" minlength="8" pattern="(?=.*[^\p{L}\p{N}\s]).{8,72}" id="password-confirm" aria-invalid="true" aria-describedby="password-error" name="password-confirm" type="password" placeholder="Confirm password" form="backend-form">
            </div>
          </div>
          <p class="error-message" id="password-error">Passwords do not match. Enter the same new password in both fields.</p>
          <div class="form-actions">
            <a class="button button--primary" href="../user/profile-change-password.php">Edit password</a>
          </div>
        </div>
      <form id="backend-form" method="post" enctype="multipart/form-data" hidden=""><?php echo csrf_field(); ?><input type="hidden" name="action" value="password" form="backend-form"><input type="hidden" name="id" value="<?php echo (int)$id; ?>" form="backend-form"></form></section>
    </div>
  <script src="../scripts/app.js" defer></script></body>
</html>