<?php http_response_code(408); ?>
<!DOCTYPE html>
<html lang="en" class="error-page">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recyclables account and page recovery: That took a little too long.">
    <title>That took a little too long — Recyclables</title>
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
        <span class="error-code">408</span>
        <img class="error-branch" src="../assets/nature/canopy.jpg" alt="" width="1600" height="1067">
        <span class="error-symbol">
          <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
            <path d="M5 3h9l5 5v13H5V3Zm9 0v5h5M8 12h8m-8 4h8"></path>
          </svg>
        </span>
      </div>
      <section class="error-content" aria-labelledby="error-title">
        <h1 id="error-title">That took a little too long</h1>
        <p class="context-line">Let's get you back</p>
        <p class="error-description">The request did not finish in time. Check your connection, then open the page again.</p>
        <div class="error-actions">
          <a class="button button--primary" href="../user/home.php">
            Reopen workspace
            <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
              <path d="M6 18 18 6M6 6h12v12"></path>
            </svg>
          </a>
          <a class="text-link" href="../index.php">Visit public home</a>
        </div>
        <aside class="error-help" aria-labelledby="help-title">
          <h2 id="help-title">A useful next step</h2>
          <p>Before repeating an upload or submission, check its status. A slow response does not always mean it failed.</p>
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