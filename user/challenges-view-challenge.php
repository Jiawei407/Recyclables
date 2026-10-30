<?php
$page='user/challenges-view-challenge.php';
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
    <meta name="description" content="Recyclables Registered User: Challenges.">
    <title>Challenges — Recyclables</title>
    <link rel="icon" href="../assets/brand-mark.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../styles.css">
  </head>
  <body class="app-shell">
    <a class="skip-link" href="#main-content">Skip to content</a>
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
        <a class="sidebar-link is-active" href="../user/challenges-browse.php" aria-current="page">
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
        <a class="sidebar-link" href="../user/profile-profile.php">
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
      <?php include __DIR__ . '/../includes/notices.php'; ?><div class="page-header">
        <div>
          <h1>Challenges</h1>
          <p class="context-line">Registered User</p>
          <p class="page-description">Choose a small action and keep a good habit going.</p>
        </div>
      </div>
      <div class="stack">
        <nav class="page-tabs" aria-label="Section views">
          <a href="../user/challenges-browse.php" class="is-active" aria-current="page">Browse</a>
          <a href="../user/challenges-my-challenges.php">My challenges</a>
          <a href="../user/challenges-completed.php">Completed</a>
        </nav>
        <div class="toolbar">
          <div class="field">
            <label for="challenge-search">Search challenges</label>
            <input id="challenge-search" name="challenge-search" type="search" placeholder="Search challenges">
          </div>
          <div class="field">
            <label for="challenge-search-filter">Category</label>
            <select id="challenge-search-filter" name="challenge-search-filter">
              <option>All categories</option>
              <option>Recycling</option>
              <option>Reuse</option>
              <option>Community</option>
            </select>
          </div>
        </div>
        <section class="panel">
          <div class="panel-heading">
            <h2>Available challenges</h2>
          </div>
          <div class="grid-3"><?php include __DIR__ . '/../includes/cards.php'; ?></div>
        </section>
      </div>
    </main>
  <script src="../scripts/app.js" defer></script></body>
</html>