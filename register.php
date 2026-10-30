<?php
$page='register.php';
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/data.php';
require __DIR__ . '/includes/metrics.php';
?>
<!DOCTYPE html>
<html lang="en" class="account-page">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Recyclables</title>
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
        <h2 id="account-title">A little change<br>starts with you.</h2>
        <?php include __DIR__ . '/includes/notices.php'; ?><p class="context-line">Register</p>
        <p class="auth-intro">Create your space for small actions and shared progress.</p>
        <div aria-labelledby="account-title">
          <div class="form-grid">
            <div class="field">
              <label for="full-name">Full name</label>
              <input id="full-name" type="text" required aria-describedby="full-name-error" placeholder="Your full name" autocomplete="name" maxlength="100" form="backend-form" name="full-name" value="<?php echo value('full-name','full_name',''); ?>">
              <p class="field-error" id="full-name-error">Enter your full name.</p>
            </div>
            <div class="field">
              <label for="student-id">Student ID</label>
              <input title="TP followed by six digits, for example TP090526." pattern="TP[0-9]{6}" id="student-id" type="text" required aria-describedby="student-id-error" placeholder="TP090526" maxlength="8" form="backend-form" name="student-id">
              <p class="field-error" id="student-id-error">Enter TP followed by six digits, for example TP090526.</p>
            </div>
            <div class="field">
              <label for="email">University email</label>
              <input title="Student emails must end with @mail.apu.edu.my." id="email" type="email" required aria-describedby="email-error" placeholder="tp090526@mail.apu.edu.my" autocomplete="email" maxlength="254" pattern="[^\s@]+@mail\.apu\.edu\.my" form="backend-form" name="email" value="<?php echo value('email','email',''); ?>">
              <p class="field-error" id="email-error">Use your @mail.apu.edu.my student email address.</p>
            </div>
            <div class="field">
              <label for="faculty">Faculty</label>
              <select required aria-describedby="faculty-error" id="faculty" form="backend-form" name="faculty">
                <option value="" <?php echo selected('faculty','faculty','','') ? 'selected' : ''; ?>>Choose your faculty</option>
                <option value="School of Computing" <?php echo selected('faculty','faculty','School of Computing','') ? 'selected' : ''; ?>>School of Computing</option>
                <option value="School of Engineering" <?php echo selected('faculty','faculty','School of Engineering','') ? 'selected' : ''; ?>>School of Engineering</option>
                <option value="School of Business" <?php echo selected('faculty','faculty','School of Business','') ? 'selected' : ''; ?>>School of Business</option>
              </select>
              <p class="field-error" id="faculty-error">Choose your faculty.</p>
            </div>
            <div class="field">
              <label for="password">Password</label>
              <input title="Use 8-72 characters including a special character, for example @, ! or #." maxlength="72" pattern="(?=.*[^\p{L}\p{N}\s]).{8,72}" id="password" type="password" required aria-describedby="password-error" placeholder="At least 8 characters" autocomplete="new-password" minlength="8" form="backend-form" name="password">
              <p class="field-error" id="password-error">Use 8-72 characters including a special character, for example @.</p>
            </div>
            <div class="field">
              <label for="confirm-password">Confirm password</label>
              <input title="Use 8-72 characters including a special character, for example @, ! or #." maxlength="72" pattern="(?=.*[^\p{L}\p{N}\s]).{8,72}" id="confirm-password" type="password" required aria-describedby="confirm-password-error" placeholder="Enter it again" minlength="8" autocomplete="new-password" form="backend-form" name="confirm-password">
              <p class="field-error" id="confirm-password-error">Enter the password again, using at least 8 characters.</p>
            </div>
          </div>
          <button class="button button--primary auth-submit" type="submit" form="backend-form">
            Create account
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="M6 18 18 6M6 6h12v12"></path>
            </svg>
          </button>
        </div>
        <p class="auth-account-link">Already part of the campus community? <a href="login.php">Log in</a> </p>
      </section>
    <form id="backend-form" method="post" enctype="multipart/form-data" hidden=""><?php echo csrf_field(); ?><input type="hidden" name="action" value="register" form="backend-form"><input type="hidden" name="id" value="<?php echo (int)$id; ?>" form="backend-form"></form></main>
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