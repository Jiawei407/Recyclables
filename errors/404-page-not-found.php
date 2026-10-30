<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="en" class="error-page">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recyclables account and page recovery: A little off the path.">
    <title>A little off the path — Recyclables</title>
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
        <span class="error-code">404</span>
        <img class="error-branch" src="../assets/nature/canopy.jpg" alt="" width="1600" height="1067">
        <span class="error-symbol">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <path d="M20 4c0 9-4 14-10 14-4 0-6-2-6-6C4 6 11 4 20 4Z"></path>
            <path d="m4 21 11-11"></path>
          </svg>
        </span>
      </div>
      <section class="error-content" aria-labelledby="error-title">
        <h1 id="error-title">A little off the path</h1>
        <p class="context-line">Let's get you back</p>
        <p class="error-description">We could not find that page. The link may be outdated, or the address may have a typing error.</p>
        <div class="error-actions">
          <a class="button button--primary" href="../index.php">
            Back to home
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="M6 18 18 6M6 6h12v12"></path>
            </svg>
          </a>
          <a class="text-link" href="../index.php#challenges">Browse challenges</a>
        </div>
        <aside class="error-help" aria-labelledby="help-title">
          <h2 id="help-title">A useful next step</h2>
          <p>Check the address for a missing letter, or return to the homepage.</p>
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