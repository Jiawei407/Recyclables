<?php http_response_code(403); ?>
<!DOCTYPE html>
<html lang="en" class="error-page">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recyclables account and page recovery: This page needs a different role.">
    <title>This page needs a different role — Recyclables</title>
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
        <span class="error-code">403</span>
        <img class="error-branch" src="../assets/nature/canopy.jpg" alt="" width="1600" height="1067">
        <span class="error-symbol">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6l8-3Z"></path>
          </svg>
        </span>
      </div>
      <section class="error-content" aria-labelledby="error-title">
        <h1 id="error-title">This page needs a different role</h1>
        <p class="context-line">Let's get you back</p>
        <p class="error-description">Your account does not have access to this area. You can return to the pages available in your workspace.</p>
        <div class="error-actions">
          <a class="button button--primary" href="../user/home.php">
            Return to workspace
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="M6 18 18 6M6 6h12v12"></path>
            </svg>
          </a>
          <a class="text-link" href="../login.php">Use another account</a>
        </div>
        <aside class="error-help" aria-labelledby="help-title">
          <h2 id="help-title">A useful next step</h2>
          <p>If your assigned role looks incorrect, ask your campus sustainability team to check your account access.</p>
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