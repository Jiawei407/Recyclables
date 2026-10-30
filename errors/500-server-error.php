<?php http_response_code(500); ?>
<!DOCTYPE html>
<html lang="en" class="error-page">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recyclables account and page recovery: Something interrupted this page.">
    <title>Something interrupted this page — Recyclables</title>
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
        <span class="error-code">500</span>
        <img class="error-branch" src="../assets/nature/canopy.jpg" alt="" width="1600" height="1067">
        <span class="error-symbol">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <circle cx="12" cy="12" r="4"></circle>
            <path d="M12 2v3m0 14v3M2 12h3m14 0h3M5 5l2 2m10 10 2 2M5 19l2-2M17 7l2-2"></path>
          </svg>
        </span>
      </div>
      <section class="error-content" aria-labelledby="error-title">
        <h1 id="error-title">Something interrupted this page</h1>
        <p class="context-line">Let's get you back</p>
        <p class="error-description">We could not complete this request. Please try opening the page again in a moment.</p>
        <div class="error-actions">
          <a class="button button--primary" href="../index.php">
            Try the homepage
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="M6 18 18 6M6 6h12v12"></path>
            </svg>
          </a>
          <a class="text-link" href="../index.php#challenges">Browse challenges</a>
        </div>
        <aside class="error-help" aria-labelledby="help-title">
          <h2 id="help-title">A useful next step</h2>
          <p>If you just submitted evidence, check your submission list before repeating the action.</p>
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