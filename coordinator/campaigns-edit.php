<?php
$page='coordinator/campaigns-edit.php';
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
    <meta name="description" content="Recyclables Sustainability Coordinator: Campaigns.">
    <title>Campaigns — Recyclables</title>
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
        <a class="sidebar-link is-active" href="../coordinator/campaigns-campaigns.php" aria-current="page">
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
      <span class="workspace-location">Campaigns</span>
      <div class="account-chip">
        <strong>Sustainability Coordinator</strong>
      </div>
    </header>
    <!-- Main page content -->
    <main id="main-content" class="workspace-content">
      <!-- Page title and actions -->
      <?php include __DIR__ . '/../includes/notices.php'; ?><div class="page-header">
        <div>
          <h1>Campaigns</h1>
          <p class="context-line">Sustainability Coordinator</p>
          <p class="page-description">Give related challenges a purpose, a period and a practical target.</p>
        </div>
        <p class="note">No record selected.</p>
      </div>
      <nav class="page-tabs" aria-label="Section views">
        <a href="../coordinator/campaigns-campaigns.php">Campaigns</a>
        <a href="../coordinator/campaigns-create.php">Create</a>
        <a href="../coordinator/campaigns-edit.php<?php echo $id ? '?id=' . (int)$id : ''; ?>" class="is-active" aria-current="page">Edit</a>
        <a href="../coordinator/campaigns-performance.php<?php echo $id ? '?id=' . (int)$id : ''; ?>">Performance</a>
      </nav>
      <div class="stack" aria-label="Edit campaign" role="form">
        <section class="panel stack" aria-labelledby="campaign-form-title">
          <h2 id="campaign-form-title">Edit campaign</h2>
          <div class="form-grid">
            <div class="field">
              <label for="campaign-name">Name</label>
              <input id="campaign-name" name="campaign-name" type="text" required placeholder="Name" form="backend-form" value="<?php echo value('campaign-name','name',''); ?>">
            </div>
            <div class="field">
              <label for="campaign-target">Participant target</label>
              <input id="campaign-target" name="campaign-target" type="number" min="1" placeholder="Participant target" form="backend-form" value="<?php echo value('campaign-target','participant_target',''); ?>">
              <p class="field-hint">A simple target for the campaign.</p>
            </div>
            <div class="field field--full">
              <label for="campaign-description">Description</label>
              <textarea id="campaign-description" name="campaign-description" rows="4" placeholder="Description" form="backend-form"><?php echo value('campaign-description','description'); ?></textarea>
              <p class="field-hint">Explain the shared purpose of the included challenges.</p>
            </div>
            <div class="field">
              <label for="campaign-start">Start date</label>
              <input id="campaign-start" name="campaign-start" type="date" form="backend-form" value="<?php echo value('campaign-start','start_date',''); ?>">
            </div>
            <div class="field">
              <label for="campaign-end">End date</label>
              <input id="campaign-end" name="campaign-end" type="date" form="backend-form" value="<?php echo value('campaign-end','end_date',''); ?>">
            </div>
            <div class="field">
              <label for="campaign-bonus">Bonus points</label>
              <input id="campaign-bonus" name="campaign-bonus" type="number" min="0" placeholder="Bonus points" form="backend-form" value="<?php echo value('campaign-bonus','bonus_points','0'); ?>">
              <p class="field-hint">A proposed completion bonus for coordinator review.</p>
            </div>
            <div class="field">
              <label for="campaign-badge">Achievement badge</label>
              <select id="campaign-badge" name="campaign-badge" form="backend-form"><?php echo '<option value="">No badge</option>'; foreach (rows("SELECT id,name FROM achievements WHERE status='active'") as $badge) echo '<option value="' . (int)$badge['id'] . '"' . selected('campaign-badge','achievement_id',$badge['id']) . '>' . e($badge['name']) . '</option>';  ?></select>
            </div>
            <fieldset class="field field--full check-group">
              <legend>Included challenges</legend>
              <div class="check-list"><?php include __DIR__ . '/../includes/campaign_choices.php'; ?></div>
              <p class="field-hint">Select the challenges participants can complete within this campaign.</p>
            </fieldset>
          </div>
        </section>
        <div class="form-actions">
          <button class="button button--primary" type="submit" form="backend-form">Save campaign</button>
          <a class="button button--outline" href="../coordinator/campaigns-campaigns.php">Cancel</a>
        </div>
      </div>
    <form id="backend-form" method="post" enctype="multipart/form-data" hidden=""><?php echo csrf_field(); ?><input type="hidden" name="action" value="save-campaign" form="backend-form"><input type="hidden" name="id" value="<?php echo (int)$id; ?>" form="backend-form"></form></main>
  <script src="../scripts/app.js" defer></script></body>
</html>