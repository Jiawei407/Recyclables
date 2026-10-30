<?php http_response_code(401); ?>
<!DOCTYPE html>
<html lang="en" class="error-page">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recyclables account and page recovery: Check your sign-in details.">
    <title>Check your sign-in details — Recyclables</title>
    <link rel="icon" href="../assets/brand-mark.svg" type="image/svg+xml">
    <link rel="stylesheet" href="../styles.css">
  </head>
  <body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <header class="error-header error-container">
      <a class="error-brand" href="../index.php">
        <img src="../assets/brand-mark.svg" alt="" width="32" height="32">
        <span>recyclables.</span>
      </a>
      <a class="text-link" href="../index.php">Back to home</a>
    </header>
    <!-- Main page content -->
    <main class="error-layout error-container" id="main-content">
      <div class="error-visual" aria-hidden="true">
        <span class="error-code error-code--word">Account</span>
        <img class="error-branch" src="../assets/nature/canopy.jpg" alt="" width="1600" height="1067">
        <span class="error-symbol">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <rect x="4" y="10" width="16" height="11" rx="3"></rect>
            <path d="M8 10V6a4 4 0 0 1 8 0v4m-4 5v2"></path>
          </svg>
        </span>
      </div>
      <section class="error-content" aria-labelledby="error-title">
        <h1 id="error-title">Check your sign-in details</h1>
        <p class="context-line">Let's get you back</p>
        <p class="error-description">The email and password do not match. Check both details, then try again.</p>
        <div class="error-actions">
          <a class="button button--primary" href="../login.php">
            Return to log in
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="M6 18 18 6M6 6h12v12"></path>
            </svg>
          </a>
          <a class="text-link" href="../register.php">Create an account</a>
        </div>
        <aside class="error-help" aria-labelledby="help-title">
          <h2 id="help-title">A useful next step</h2>
          <p>Check for an accidental space in your email. Passwords are case-sensitive.</p>
        </aside>
      </section>
    </main>
    <footer class="error-footer error-container">
      <a class="text-link" href="../index.php">
        Back to Recyclables
        <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
          <path d="M6 18 18 6M6 6h12v12"></path>
        </svg>
      </a>
    </footer>
  </body>
</html>