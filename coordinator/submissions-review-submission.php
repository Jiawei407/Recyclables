<?php
$page='coordinator/submissions-review-submission.php';
$page_role='coordinator';
require __DIR__ . '/../includes/bootstrap.php';
require __DIR__ . '/../includes/data.php';
require __DIR__ . '/../includes/metrics.php';
$can_decide = !empty($record) && $record['status'] === 'pending';
$status_label = $record ? ucfirst(str_replace('-', ' ', $record['status'])) : 'Not selected';
$return_page = 'submissions-' . ($record['status'] ?? 'pending') . '.php';
?>
<!DOCTYPE html>
<html lang="en" class="workspace-page">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recyclables Sustainability Coordinator: Submissions.">
    <title>Submissions — Recyclables</title>
    <link rel="icon" href="../assets/brand-mark.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../styles.css">
  </head>
  <body>
    <a class="skip-link" href="#dialog-title">Skip to content</a>
    <!-- Keep this background container inert while the dialog is open. -->
    <div class="app-shell" inert>
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
          <a class="sidebar-link is-active" href="../coordinator/submissions-pending.php" aria-current="page">
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
        <span class="workspace-location">Submissions</span>
        <div class="account-chip">
          <strong>Sustainability Coordinator</strong>
        </div>
      </header>
      <!-- Main page content -->
      <main id="main-content" class="workspace-content">
        <!-- Page title and actions -->
        <div class="page-header">
          <div>
            <h1>Submissions</h1>
            <p class="context-line">Sustainability Coordinator</p>
            <p class="page-description">Pending evidence awaiting a human review.</p>
          </div>
          <p class="note">No record selected.</p>
        </div>
        <nav class="page-tabs" aria-label="Section views">
          <a href="../coordinator/submissions-pending.php" class="is-active" aria-current="page">Pending</a>
          <a href="../coordinator/submissions-needs-revision.php">Needs revision</a>
          <a href="../coordinator/submissions-approved.php">Approved</a>
          <a href="../coordinator/submissions-rejected.php">Rejected</a>
        </nav>
        <div class="stack">
          <div class="toolbar">
            <div class="field">
              <label for="submission-search">Search submissions</label>
              <input id="submission-search" name="submission-search" type="search" placeholder="Search submissions" form="backend-form">
            </div>
            <div class="field">
              <label for="submission-challenge">Challenge</label>
              <select id="submission-challenge" name="submission-challenge" form="backend-form">
                <option value="" selected>All challenges</option>
              </select>
            </div>
          </div>
          <section class="panel stack" aria-labelledby="submissions-title">
            <h2 id="submissions-title">Pending submissions</h2>
            <div class="table-wrap">
              <table class="data-table">
                <caption class="visually-hidden">pending submission queue</caption>
                <thead>
                  <tr>
                    <th scope="col">Submission</th>
                    <th scope="col">Participant</th>
                    <th scope="col">Challenge</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody><?php $kind='submissions'; $columns=['Submission', 'Participant', 'Challenge', 'Action']; include __DIR__ . '/../includes/table.php'; ?></tbody>
              </table>
            </div>
            <p class="note">Open a submission to check its photo, item count and possible duplicates by hand.</p>
          </section>
          <div class="notice notice--info">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="M5 3h9l5 5v13H5V3Zm9 0v5h5M8 12h8m-8 4h8"></path>
            </svg>
            <p>No automatic fraud decisions. A coordinator reviews the evidence and records a clear reason for revision or rejection.</p>
          </div>
        </div>
      </main>
    </div>
    <div class="dialog-overlay">
      <!-- Dialog state: close returns to the parent page -->
      <section class="action-dialog" role="dialog" aria-modal="true" aria-labelledby="dialog-title">
        <header class="dialog-header">
          <div>
            <h2 id="dialog-title"><?= $can_decide ? 'Review submission' : 'Submission details' ?></h2>
            <p class="context-line">Sustainability Coordinator</p>
          </div>
          <a class="close-button" href="../coordinator/<?= e($return_page) ?>" aria-label="Close dialog">
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="m6 6 12 12M6 18 18 6"></path>
            </svg>
          </a>
        </header>
        <?php include __DIR__ . '/../includes/notices.php'; ?><div class="dialog-content">
          <div class="stack">
            <dl class="detail-list">
              <div>
                <dt>Participant</dt>
                <dd><?php echo e($record['full_name'] ?? 'Not selected'); ?></dd>
              </div>
              <div>
                <dt>Challenge</dt>
                <dd><?php echo e($record['title'] ?? 'Not selected'); ?></dd>
              </div>
              <div>
                <dt>Submission</dt>
                <dd><?php echo e($record['id'] ?? 'Not selected'); ?></dd>
              </div>
              <div>
                <dt>Current status</dt>
                <dd><?= e($status_label) ?></dd>
              </div>
            </dl>
            <figure class="media-figure"><?php if ($record) { echo '<img class="preview-image" src="' . e($base) . '/file.php?type=evidence&amp;id=' . (int)$record['id'] . '" alt="Submitted evidence" width="900" height="480">'; echo '<figcaption>' . e($record['description']) . ' · Quantity: ' . (int)$record['quantity'] . '</figcaption>'; } else echo 'Select a submission from the list.'; ?></figure>
            <blockquote><?php echo '<p>' . e($record['description'] ?? '') . '</p><p>Reviewer notes: ' . e($record['review_notes'] ?? 'Not reviewed') . '</p>'; ?></blockquote>
            <div class="stack" aria-label="Manual submission review" role="form">
              <fieldset class="check-group" <?= $can_decide ? '' : 'disabled' ?>>
                <legend>Reviewer checks</legend>
                <div class="check-list">
                  <label class="check-option" for="review-matches">
                    <input id="review-matches" name="review-matches" type="checkbox" form="backend-form">
                    <span>Photo matches the challenge</span>
                  </label>
                  <label class="check-option" for="review-count">
                    <input id="review-count" name="review-count" type="checkbox" form="backend-form">
                    <span>Item count checked</span>
                  </label>
                  <label class="check-option" for="review-duplicate">
                    <input id="review-duplicate" name="review-duplicate" type="checkbox" form="backend-form">
                    <span>Possible duplicate evidence checked by hand</span>
                  </label>
                </div>
              </fieldset>
              <div class="field field--full">
                <label for="review-notes">Review notes</label>
                <textarea id="review-notes" name="review-notes" rows="3" placeholder="Review notes" form="backend-form" <?= $can_decide ? '' : 'disabled' ?>><?= e(isset($_POST['review-notes']) && is_string($_POST['review-notes']) ? $_POST['review-notes'] : '') ?></textarea>
                <p class="field-hint">Record a useful reason before requesting a revision or rejecting evidence.</p>
              </div>
            </div>
            <div class="notice notice--info">
              <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                <path d="M5 3h9l5 5v13H5V3Zm9 0v5h5M8 12h8m-8 4h8"></path>
              </svg>
              <p><?php if ($can_decide): ?>Complete all three checks before approving. Add review notes when rejecting evidence or requesting revision.<?php elseif ($record): ?>This submission is already <?= e(strtolower($status_label)) ?>. Its decision is read-only. The participant must send a new submission before you can review the evidence again.<?php else: ?>Select a pending submission from the list before recording a decision.<?php endif; ?></p>
            </div>
          </div>
        </div>
        <footer class="dialog-actions">
          <button class="button button--primary" type="submit" form="backend-form" name="decision" value="approved" <?= $can_decide ? '' : 'disabled' ?>>Approve</button>
          <button class="button button--outline" type="submit" form="backend-form" name="decision" value="needs-revision" <?= $can_decide ? '' : 'disabled' ?>>Needs revision</button>
          <button class="button button--danger" type="submit" form="backend-form" name="decision" value="rejected" <?= $can_decide ? '' : 'disabled' ?>>Reject</button>
        </footer>
      <form id="backend-form" method="post" enctype="multipart/form-data" hidden=""><?php echo csrf_field(); ?><input type="hidden" name="action" value="review" form="backend-form"><input type="hidden" name="id" value="<?php echo (int)$id; ?>" form="backend-form"></form></section>
    </div>
  <script src="../scripts/app.js" defer></script></body>
</html>
