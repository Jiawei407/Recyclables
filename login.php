<?php
$page='login.php';
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/data.php';
require __DIR__ . '/includes/metrics.php';
?>
<!DOCTYPE html>
<html lang="en" class="account-page">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in — Recyclables</title>
    <meta name="description" content="Recyclables account access.">
    <link rel="icon" href="assets/brand-mark.svg" type="image/svg+xml">
    <link rel="stylesheet" href="styles.css">
  </head>
  <body class="public-account">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <header class="site-header">
      <div class="container header-inner">
        <a class="brand" href="index.php">
          <img src="assets/brand-mark.svg" alt="" width="32" height="32">
          <span>recyclables<span class="brand-dot">.</span> </span>
        </a>
        <nav class="main-nav" aria-label="Main navigation">
          <a class="nav-link" href="index.php">Home</a>
          <a class="nav-link" href="index.php#challenges">Challenges</a>
          <a class="nav-link" href="index.php#leaderboard">Leaderboard</a>
          <a class="nav-link" href="index.php#about">About</a>
        </nav>
        <div class="account-actions">
          <a class="button button--quiet" href="login.php">Log in</a>
          <a class="button button--lime" href="register.php">Register</a>
        </div>
      </div>
    </header>
    <!-- Main page content -->
    <main class="container auth-layout" id="main-content">
      <section class="auth-story" aria-labelledby="story-title">
        <img class="auth-photo" src="assets/nature/clouds.jpg" alt="" width="1800" height="1350">
        <h1 id="story-title">A habit.<br>A movement.<br> <span>A greener<br>campus.</span> </h1>
        <p class="context-line">Small steps. Shared impact.</p>
        <div class="auth-note">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <path d="M20 4c0 9-4 14-10 14-4 0-6-2-6-6C4 6 11 4 20 4Z"></path>
            <path d="m4 21 11-11"></path>
          </svg>
          <p>Progress starts with the choices<br>we make every day.</p>
        </div>
      </section>
      <section class="auth-card" aria-labelledby="account-title">
        <h2 id="account-title">Good to have<br>you back.</h2>
        <?php include __DIR__ . '/includes/notices.php'; ?><p class="context-line">Log in</p>
        <p class="auth-intro">Your next small step is waiting.</p>
        <div aria-labelledby="account-title">
          <div class="form-grid">
            <div class="field field--full">
              <label for="email">University email</label>
              <input id="email" type="email" required aria-describedby="email-error" placeholder="name@example.com" autocomplete="email" maxlength="254" pattern="[^\s@]+@[^\s@]+\.[^\s@]+" form="backend-form" name="email" value="<?php echo value('email','email',''); ?>">
              <p class="field-error" id="email-error">Enter a valid email address, for example name@example.com.</p>
            </div>
            <div class="field field--full">
              <label for="password">Password</label>
              <input id="password" type="password" required aria-describedby="password-error" placeholder="Enter your password" autocomplete="current-password" form="backend-form" name="password">
              <p class="field-error" id="password-error">Enter your password.</p>
            </div>
          </div>
          <details class="auth-help">
            <summary>Forgot password?</summary>
            <div>
              <p>Contact your campus sustainability team for account support.</p>
              <a class="text-link" href="register.php">Create an account</a>
            </div>
          </details>
          <button class="button button--primary auth-submit" type="submit" form="backend-form">
            Log in
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="M6 18 18 6M6 6h12v12"></path>
            </svg>
          </button>
        </div>
        <p class="auth-account-link">New to Recyclables? <a href="register.php">Create an account</a> </p>
      </section>
    <form id="backend-form" method="post" enctype="multipart/form-data" hidden=""><?php echo csrf_field(); ?><input type="hidden" name="action" value="login" form="backend-form"><input type="hidden" name="id" value="<?php echo (int)$id; ?>" form="backend-form"></form></main>
    <!-- Site footer -->
    <footer class="site-footer">
      <div class="container footer-inner">
        <div class="footer-about">
          <a class="brand brand--footer" href="index.php">
            <img src="assets/brand-mark.svg" alt="" width="32" height="32">
            <span>recyclables.</span>
          </a>
          <p>A greener campus starts with us.</p>
        </div>
        <div class="footer-meta">
          <a class="back-to-top" href="index.php">Back to home</a>
        </div>
      </div>
    </footer>
  <script src="scripts/app.js" defer></script></body>
</html>