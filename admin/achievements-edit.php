<?php
$page='admin/achievements-edit.php';
$page_role='admin';
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/data.php';
require __DIR__ . '/../includes/metrics.php';
$saved_rules=$id ? rows('SELECT * FROM achievement_rules WHERE achievement_id=? ORDER BY id',[$id]) : [];
?>
<!DOCTYPE html>
<html lang="en" class="workspace-page">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recyclables Administrator: Achievements.">
    <title>Achievements — Recyclables</title>
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
        <a class="sidebar-link is-active" href="../admin/achievements-achievements.php" aria-current="page">
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
      <span class="workspace-location">Achievements</span>
      <div class="account-chip">
        <strong>Administrator</strong>
      </div>
    </header>
    <!-- Main page content -->
    <main id="main-content" class="workspace-content">
      <!-- Page title and actions -->
      <?php include __DIR__ . '/../includes/notices.php'; ?><div class="page-header">
        <div>
          <h1>Achievements</h1>
          <p class="context-line">Administrator</p>
          <p class="page-description">Keep achievement requirements clear and meaningful.</p>
        </div>
        <p class="note">No record selected.</p>
      </div>
      <div class="stack">
        <nav class="page-tabs" aria-label="Section views">
          <a href="../admin/achievements-achievements.php">Achievements</a>
          <a href="../admin/achievements-create.php">Create</a>
          <a href="../admin/achievements-edit.php<?php echo $id ? '?id=' . (int)$id : ''; ?>" class="is-active" aria-current="page">Edit</a>
        </nav>
        <section class="panel">
          <div class="panel-heading">
            <h2>Edit achievement</h2>
          </div>
          <div class="stack" role="form">
            <div class="form-grid">
              <div class="field">
                <label for="achievement-name">Name</label>
                <input id="achievement-name" name="achievement-name" type="text" placeholder="Name" required form="backend-form" value="<?php echo value('achievement-name','name',''); ?>">
              </div>
              <div class="field">
                <label for="achievement-status">Status</label>
                <select id="achievement-status" name="achievement-status" form="backend-form">
                  <option value="choose an option" <?php echo selected('achievement-status','status','choose an option','active') ? 'selected' : ''; ?>>Choose an option</option>
                  <option value="draft" <?php echo selected('achievement-status','status','draft','active') ? 'selected' : ''; ?>>Draft</option>
                  <option value="active" <?php echo selected('achievement-status','status','active','active') ? 'selected' : ''; ?>>Active</option>
                  <option value="archived" <?php echo selected('achievement-status','status','archived','active') ? 'selected' : ''; ?>>Archived</option>
                </select>
              </div>
            </div>
            <div class="upload-preview">
              <svg class="achievement-icon" viewbox="0 0 96 96" width="96" height="96" role="img" aria-label="No image selected">
                <rect width="96" height="96" fill="var(--sage)"></rect>
                <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" fill="currentColor" font-size="10.666666666666666">
                  No image selected
                </text>
              </svg>
              <div>
                <strong>No badge selected</strong>
                <p class="muted">Use a simple badge that remains legible at small sizes.</p>
                <a class="button button--outline" href="../admin/achievements-edit-upload-badge.php">Upload badge</a>
              </div>
            </div>
          </div>
        </section>
        <section class="panel">
          <div class="panel-heading">
            <h2>Requirements</h2>
          </div>
          <p class="muted">Complete all requirements. Each row combines a count, an item or activity, and a condition.</p>
          <fieldset class="requirement-row">
            <legend>Requirement 1</legend>
            <div class="form-grid">
              <div class="field">
                <label for="requirement-1-count">Count</label>
                <input id="requirement-1-count" name="requirement-1-count" type="number" placeholder="Count" min="1" step="1" form="backend-form" value="<?php echo e($saved_rules[0]['required_count'] ?? ''); ?>">
              </div>
              <div class="field">
                <label for="requirement-1-item">Item or activity</label>
                <select id="requirement-1-item" name="requirement-1-item" form="backend-form">
                  <option value="" <?php echo ($saved_rules[0]['item'] ?? '') === '' ? 'selected' : ''; ?>>Choose an item</option>
                  <option <?php echo ($saved_rules[0]['item'] ?? '') === 'Plastic bottles' ? 'selected' : ''; ?>>Plastic bottles</option>
                  <option <?php echo ($saved_rules[0]['item'] ?? '') === 'Approved activity' ? 'selected' : ''; ?>>Approved activity</option>
                  <option <?php echo ($saved_rules[0]['item'] ?? '') === 'Reusable cup' ? 'selected' : ''; ?>>Reusable cup</option>
                  <option <?php echo ($saved_rules[0]['item'] ?? '') === 'Campus cleanup' ? 'selected' : ''; ?>>Campus cleanup</option>
                </select>
              </div>
              <div class="field">
                <label for="requirement-1-condition">Condition</label>
                <select id="requirement-1-condition" name="requirement-1-condition" form="backend-form">
                  <option value="" <?php echo ($saved_rules[0]['condition_name'] ?? '') === '' ? 'selected' : ''; ?>>Choose a condition</option>
                  <option <?php echo ($saved_rules[0]['condition_name'] ?? '') === 'At least' ? 'selected' : ''; ?>>At least</option>
                  <option <?php echo ($saved_rules[0]['condition_name'] ?? '') === 'Exactly' ? 'selected' : ''; ?>>Exactly</option>
                </select>
              </div>
            </div>
            <label class="check-option">
              <input type="checkbox" name="exclude-1" form="backend-form">
              <span>Exclude this requirement in your draft</span>
            </label>
          </fieldset>
          <fieldset class="requirement-row">
            <legend>Requirement 2</legend>
            <div class="form-grid">
              <div class="field">
                <label for="requirement-2-count">Count</label>
                <input id="requirement-2-count" name="requirement-2-count" type="number" placeholder="Count" min="1" step="1" form="backend-form" value="<?php echo e($saved_rules[1]['required_count'] ?? ''); ?>">
              </div>
              <div class="field">
                <label for="requirement-2-item">Item or activity</label>
                <select id="requirement-2-item" name="requirement-2-item" form="backend-form">
                  <option value="" <?php echo ($saved_rules[1]['item'] ?? '') === '' ? 'selected' : ''; ?>>Choose an item</option>
                  <option <?php echo ($saved_rules[1]['item'] ?? '') === 'Plastic bottles' ? 'selected' : ''; ?>>Plastic bottles</option>
                  <option <?php echo ($saved_rules[1]['item'] ?? '') === 'Approved activity' ? 'selected' : ''; ?>>Approved activity</option>
                  <option <?php echo ($saved_rules[1]['item'] ?? '') === 'Reusable cup' ? 'selected' : ''; ?>>Reusable cup</option>
                  <option <?php echo ($saved_rules[1]['item'] ?? '') === 'Campus cleanup' ? 'selected' : ''; ?>>Campus cleanup</option>
                </select>
              </div>
              <div class="field">
                <label for="requirement-2-condition">Condition</label>
                <select id="requirement-2-condition" name="requirement-2-condition" form="backend-form">
                  <option value="" <?php echo ($saved_rules[1]['condition_name'] ?? '') === '' ? 'selected' : ''; ?>>Choose a condition</option>
                  <option <?php echo ($saved_rules[1]['condition_name'] ?? '') === 'At least' ? 'selected' : ''; ?>>At least</option>
                  <option <?php echo ($saved_rules[1]['condition_name'] ?? '') === 'Exactly' ? 'selected' : ''; ?>>Exactly</option>
                </select>
              </div>
            </div>
            <label class="check-option">
              <input type="checkbox" name="exclude-2" form="backend-form">
              <span>Exclude this requirement in your draft</span>
            </label>
          </fieldset>
          <!-- Native disclosure reveals the third requirement slot; checked rows are marked for exclusion. -->
          <details class="disclosure">
            <summary>Add requirement</summary>
            <div class="stack">
              <fieldset class="requirement-row">
                <legend>Requirement 3</legend>
                <div class="form-grid">
                  <div class="field">
                    <label for="requirement-3-count">Count</label>
                    <input id="requirement-3-count" name="requirement-3-count" type="number" placeholder="Count" min="1" step="1" form="backend-form" value="<?php echo e($saved_rules[2]['required_count'] ?? ''); ?>">
                  </div>
                  <div class="field">
                    <label for="requirement-3-item">Item or activity</label>
                    <select id="requirement-3-item" name="requirement-3-item" form="backend-form">
                      <option value="" <?php echo ($saved_rules[2]['item'] ?? '') === '' ? 'selected' : ''; ?>>Choose an item</option>
                      <option <?php echo ($saved_rules[2]['item'] ?? '') === 'Plastic bottles' ? 'selected' : ''; ?>>Plastic bottles</option>
                      <option <?php echo ($saved_rules[2]['item'] ?? '') === 'Approved activity' ? 'selected' : ''; ?>>Approved activity</option>
                      <option <?php echo ($saved_rules[2]['item'] ?? '') === 'Reusable cup' ? 'selected' : ''; ?>>Reusable cup</option>
                      <option <?php echo ($saved_rules[2]['item'] ?? '') === 'Campus cleanup' ? 'selected' : ''; ?>>Campus cleanup</option>
                    </select>
                  </div>
                  <div class="field">
                    <label for="requirement-3-condition">Condition</label>
                    <select id="requirement-3-condition" name="requirement-3-condition" form="backend-form">
                      <option value="" <?php echo ($saved_rules[2]['condition_name'] ?? '') === '' ? 'selected' : ''; ?>>Choose a condition</option>
                      <option <?php echo ($saved_rules[2]['condition_name'] ?? '') === 'At least' ? 'selected' : ''; ?>>At least</option>
                      <option <?php echo ($saved_rules[2]['condition_name'] ?? '') === 'Exactly' ? 'selected' : ''; ?>>Exactly</option>
                    </select>
                  </div>
                </div>
                <label class="check-option">
                  <input type="checkbox" name="exclude-3" form="backend-form">
                  <span>Exclude this requirement in your draft</span>
                </label>
              </fieldset>
            </div>
          </details>
        </section>
        <div class="form-actions">
          <button class="button button--primary" type="submit" form="backend-form">Save achievement</button>
          <a class="button button--outline" href="../admin/achievements-achievements.php">Cancel</a>
        </div>
        <aside class="notice">
          <div>
            <strong>Achievement requirements</strong>
            <p>Open Add requirement to enter another requirement. Mark a row to exclude it from the draft.</p>
          </div>
        </aside>
      </div>
    <form id="backend-form" method="post" enctype="multipart/form-data" hidden=""><?php echo csrf_field(); ?><input type="hidden" name="action" value="save-achievement" form="backend-form"><input type="hidden" name="id" value="<?php echo (int)$id; ?>" form="backend-form"></form></main>
  <script src="../scripts/app.js" defer></script></body>
</html>