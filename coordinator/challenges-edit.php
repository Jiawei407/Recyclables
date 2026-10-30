<?php
$page='coordinator/challenges-edit.php';
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
    <meta name="description" content="Recyclables Sustainability Coordinator: Challenges.">
    <title>Challenges — Recyclables</title>
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
        <a class="sidebar-link is-active" href="../coordinator/challenges-all-challenges.php" aria-current="page">
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
      <span class="workspace-location">Challenges</span>
      <div class="account-chip">
        <strong>Sustainability Coordinator</strong>
      </div>
    </header>
    <!-- Main page content -->
    <main id="main-content" class="workspace-content">
      <!-- Page title and actions -->
      <?php include __DIR__ . '/../includes/notices.php'; ?><div class="page-header">
        <div>
          <h1>Challenges</h1>
          <p class="context-line">Sustainability Coordinator</p>
          <p class="page-description">Shape a simple action that participants can complete and reviewers can verify.</p>
        </div>
        <p class="note">No record selected.</p>
      </div>
      <nav class="page-tabs" aria-label="Section views">
        <a href="../coordinator/challenges-all-challenges.php">All challenges</a>
        <a href="../coordinator/challenges-create.php">Create</a>
        <a href="../coordinator/challenges-edit.php<?php echo $id ? '?id=' . (int)$id : ''; ?>" class="is-active" aria-current="page">Edit</a>
      </nav>
      <div class="stack" aria-label="Edit challenge" role="form">
        <section class="panel stack" aria-labelledby="challenge-details-title">
          <h2 id="challenge-details-title">Edit challenge</h2>
          <p class="note">Keep the task specific, the evidence clear, and the reward easy to understand.</p>
          <div class="form-grid">
            <div class="field">
              <label for="challenge-title">Title</label>
              <input id="challenge-title" name="challenge-title" type="text" required placeholder="Title" form="backend-form" value="<?php echo value('challenge-title','title',''); ?>">
            </div>
            <div class="field">
              <label for="challenge-category">Category</label>
              <select id="challenge-category" name="challenge-category" form="backend-form">
                <option value="" <?php echo selected('challenge-category','category','','') ? 'selected' : ''; ?>>Choose an option</option>
                <option value="Cleanup" <?php echo selected('challenge-category','category','Cleanup','') ? 'selected' : ''; ?>>Cleanup</option>
                <option value="Recycling" <?php echo selected('challenge-category','category','Recycling','') ? 'selected' : ''; ?>>Recycling</option>
                <option value="Reuse" <?php echo selected('challenge-category','category','Reuse','') ? 'selected' : ''; ?>>Reuse</option>
                <option value="Everyday habits" <?php echo selected('challenge-category','category','Everyday habits','') ? 'selected' : ''; ?>>Everyday habits</option>
              </select>
            </div>
            <div class="field field--full">
              <label for="challenge-description">Description</label>
              <textarea id="challenge-description" name="challenge-description" rows="4" placeholder="Description" form="backend-form"><?php echo value('challenge-description','description'); ?></textarea>
              <p class="field-hint">Describe what participants should do and photograph.</p>
            </div>
            <div class="field">
              <label for="challenge-points">Green Points</label>
              <input id="challenge-points" name="challenge-points" type="number" min="0" max="1000" placeholder="Green Points" form="backend-form" value="<?php echo value('challenge-points','points','0'); ?>">
              <p class="field-hint">Awarded after a coordinator approves the evidence.</p>
            </div>
          <div class="field"><label for="tracked-item">Tracked item for achievements</label><select id="tracked-item" name="tracked-item" form="backend-form"><option value="Approved activity" <?php echo selected('tracked-item','tracked_item','Approved activity','Approved activity') ? 'selected' : ''; ?>>Approved activity</option><option value="Plastic bottles" <?php echo selected('tracked-item','tracked_item','Plastic bottles','Approved activity') ? 'selected' : ''; ?>>Plastic bottles</option><option value="Reusable cup" <?php echo selected('tracked-item','tracked_item','Reusable cup','Approved activity') ? 'selected' : ''; ?>>Reusable cup</option><option value="Campus cleanup" <?php echo selected('tracked-item','tracked_item','Campus cleanup','Approved activity') ? 'selected' : ''; ?>>Campus cleanup</option></select></div></div>
          <div class="upload-area">
            <svg class="upload-thumbnail challenge-illustration" viewbox="0 0 600 600" width="600" height="600" role="img" aria-label="No image selected">
              <rect width="600" height="600" fill="var(--sage)"></rect>
              <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" fill="currentColor" font-size="40">No image selected</text>
            </svg>
            <div>
              <strong>Challenge illustration</strong>
              <p class="note">No image selected</p>
            </div>
            <label class="button button--outline"><span data-file-name="backend-upload">Choose image</span><input class="visually-hidden" type="file" id="backend-upload" form="backend-form" name="image" accept="image/jpeg,image/png"></label></div>
        </section>
        <section class="panel stack" aria-labelledby="challenge-rules-title">
          <h2 id="challenge-rules-title">Rules and schedule</h2>
          <div class="form-grid">
            <div class="field">
              <label for="challenge-start">Start date</label>
              <input id="challenge-start" name="challenge-start" type="date" form="backend-form" value="<?php echo value('challenge-start','start_date',''); ?>">
            </div>
            <div class="field">
              <label for="challenge-end">End date</label>
              <input id="challenge-end" name="challenge-end" type="date" form="backend-form" value="<?php echo value('challenge-end','end_date',''); ?>">
            </div>
            <div class="field">
              <label for="challenge-evidence">Evidence type</label>
              <select id="challenge-evidence" name="challenge-evidence" form="backend-form">
                <option value="" selected>Choose an option</option>
                <option>Photo evidence</option>
              </select>
              <p class="field-hint">Evidence is reviewed manually.</p>
            </div>
            <div class="field">
              <label for="challenge-limit">Submission limit</label>
              <input id="challenge-limit" name="challenge-limit" type="number" min="1" placeholder="Submission limit" form="backend-form" value="<?php echo value('challenge-limit','submission_limit','1'); ?>">
              <p class="field-hint">Maximum approved submissions per participant.</p>
            </div>
            <div class="field">
              <label for="challenge-eligibility">Eligibility</label>
              <select id="challenge-eligibility" name="challenge-eligibility" form="backend-form">
                <option value="" <?php echo selected('challenge-eligibility','eligibility','','') ? 'selected' : ''; ?>>Choose an option</option>
                <option value="Students and staff" <?php echo selected('challenge-eligibility','eligibility','Students and staff','') ? 'selected' : ''; ?>>Students and staff</option>
                <option value="Students" <?php echo selected('challenge-eligibility','eligibility','Students','') ? 'selected' : ''; ?>>Students</option>
                <option value="Staff" <?php echo selected('challenge-eligibility','eligibility','Staff','') ? 'selected' : ''; ?>>Staff</option>
              </select>
            </div>
            <div class="field">
              <label for="challenge-completion">Completion requirement</label>
              <input id="challenge-completion" name="challenge-completion" type="text" placeholder="Completion requirement" form="backend-form" value="<?php echo value('challenge-completion','completion_requirement',''); ?>">
              <p class="field-hint">Write one clear completion rule.</p>
            </div>
          </div>
        </section>
        <div class="form-actions">
          <button class="button button--primary" type="submit" form="backend-form">Save challenge</button>
          <a class="button button--outline" href="../coordinator/challenges-all-challenges.php">Cancel</a>
        </div>
      </div>
    <form id="backend-form" method="post" enctype="multipart/form-data" hidden=""><?php echo csrf_field(); ?><input type="hidden" name="action" value="save-challenge" form="backend-form"><input type="hidden" name="id" value="<?php echo (int)$id; ?>" form="backend-form"></form></main>
  <script src="../scripts/app.js" defer></script></body>
</html>