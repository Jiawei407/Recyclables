<?php
$page='index.php';
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/data.php';
require __DIR__ . '/includes/metrics.php';
?>
<!DOCTYPE html>
<html lang="en" class="home-page">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explore campus sustainability challenges, Green Points, and shared progress with Recyclables.">
    <title>Recyclables — A greener campus</title>
    <link rel="icon" type="image/svg+xml" href="assets/brand-mark.svg">
    <link rel="stylesheet" href="styles.css">
  </head>
  <body id="home">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <svg class="icon-definitions" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
      <defs>
        <symbol id="icon-arrow-up-right" viewbox="0 0 24 24">
          <path d="M6 18 18 6M6 6h12v12"></path>
        </symbol>
        <symbol id="icon-arrow-down" viewbox="0 0 24 24">
          <path d="M12 4v16m-6-6 6 6 6-6"></path>
        </symbol>
        <symbol id="icon-leaf" viewbox="0 0 24 24">
          <path d="M20 4c0 9-4 14-10 14-4 0-6-2-6-6C4 6 11 4 20 4Z"></path>
          <path d="m4 21 11-11"></path>
        </symbol>
        <symbol id="icon-cycle" viewbox="0 0 24 24">
          <path d="M4 9a8 8 0 0 1 13-4l3 3m0-5v5h-5M20 15a8 8 0 0 1-13 4l-3-3m0 5v-5h5"></path>
        </symbol>
        <symbol id="icon-people" viewbox="0 0 24 24">
          <circle cx="9" cy="7" r="3"></circle>
          <path d="M3 20v-2a6 6 0 0 1 12 0v2M16 4a3 3 0 0 1 0 6m2 4a5 5 0 0 1 3 4v2"></path>
        </symbol>
        <symbol id="icon-campus" viewbox="0 0 24 24">
          <path d="m3 9 9-6 9 6H3Zm2 1v10m5-10v10m4-10v10m5-10v10M3 21h18"></path>
        </symbol>
      </defs>
    </svg>
    <header class="site-header">
      <div class="container header-inner">
        <a class="brand" href="#home" aria-label="Recyclables home">
          <img src="assets/brand-mark.svg" width="32" height="32" alt="">
          <span>recyclables<span class="brand-dot">.</span> </span>
        </a>
        <nav class="main-nav" aria-label="Main navigation">
          <a class="nav-link nav-link--active" href="#home" aria-current="page">Home</a>
          <a class="nav-link" href="#challenges">Challenges</a>
          <a class="nav-link" href="#leaderboard">Leaderboard</a>
          <a class="nav-link" href="#about">About</a>
        </nav>
        <div class="account-actions">
          <a class="button button--quiet" href="login.php">Log in</a>
          <a class="button button--lime" href="register.php">Register</a>
        </div>
      </div>
    </header>
    <!-- Main page content -->
    <main id="main-content" class="container">
      <!-- Homepage introduction and focus controls -->
      <?php include __DIR__ . '/includes/notices.php'; ?><section class="hero" aria-labelledby="hero-title">
        <figure class="hero-figure">
          <div class="picture-plane">
            <img src="assets/nature/field-horizon.jpg" width="2000" height="1334" alt="A solitary tree on rolling green hills beneath a sky of clouds" fetchpriority="high">
          </div>
          <figcaption class="photo-caption"><span>Small steps. Shared impact.</span><span>One campus. Countless small beginnings.</span></figcaption>
        </figure>
        <div class="hero-message">
          <h1 id="hero-title"><span>Make a</span><span>difference today.</span></h1>
          <div class="hero-support">
            <p class="hero-description">Good habits grow into something greater. Find a campus challenge, take a small step, and make it count.</p>
            <div class="hero-actions">
              <a class="button button--primary primary-action" href="#challenges">
                Explore challenges
                <svg class="icon" aria-hidden="true">
                  <use href="#icon-arrow-up-right"></use>
                </svg>
              </a>
              <a class="hero-impact-link" href="#impact">
                Our shared impact
                <svg class="icon" aria-hidden="true">
                  <use href="#icon-arrow-down"></use>
                </svg>
              </a>
            </div>
          </div>
        </div>
        <fieldset class="scene-focus" aria-describedby="scene-focus-help">
          <legend>Where will you begin?</legend>
          <div class="scene-choices">
            <div class="scene-choice">
              <input type="radio" name="scene-focus" id="focus-recycle" checked>
              <label for="focus-recycle"><span class="visually-hidden">01</span> Recycle <span aria-hidden="true">↗</span></label>
            </div>
            <div class="scene-choice">
              <input type="radio" name="scene-focus" id="focus-reuse">
              <label for="focus-reuse"><span class="visually-hidden">02</span> Reuse <span aria-hidden="true">↗</span></label>
            </div>
            <div class="scene-choice">
              <input type="radio" name="scene-focus" id="focus-restore">
              <label for="focus-restore"><span class="visually-hidden">03</span> Restore <span aria-hidden="true">↗</span></label>
            </div>
          </div>
          <p id="scene-focus-help" class="visually-hidden">Choose a focus. Find your next small step.</p>
        </fieldset>
      </section>
      <!-- Campus impact -->
      <section class="impact-section" id="impact" aria-labelledby="impact-title">
        <div class="section-header section-header--compact">
          <h2 id="impact-title" class="eyebrow">Our campus, in action</h2>
          <span class="data-label">No campus activity recorded yet</span>
        </div>
        <dl class="impact-grid">
          <div class="metric-card">
            <dt>
              Waste diverted
              <svg class="icon metric-icon" aria-hidden="true">
                <use href="#icon-leaf">

                </use>
              </svg>
            </dt>
            <dd class="metric-value"><?php echo e(metric('Waste diverted  kg A little less in landfill.')); ?></dd>
            <dd class="metric-note">A little less in landfill.</dd>
          </div>
          <div class="metric-card">
            <dt>
              Sustainable actions
              <svg class="icon metric-icon" aria-hidden="true">
                <use href="#icon-cycle">

                </use>
              </svg>
            </dt>
            <dd class="metric-value"><?php echo e(metric('Sustainable actions  Every small habit adds up.')); ?></dd>
            <dd class="metric-note">Every small habit adds up.</dd>
          </div>
          <div class="metric-card">
            <dt>
              Active participants
              <svg class="icon metric-icon" aria-hidden="true">
                <use href="#icon-people">

                </use>
              </svg>
            </dt>
            <dd class="metric-value"><?php echo e(metric('Active participants  Making a difference together.')); ?></dd>
            <dd class="metric-note">Making a difference together.</dd>
          </div>
          <div class="metric-card">
            <dt>
              Faculties involved
              <svg class="icon metric-icon" aria-hidden="true">
                <use href="#icon-campus">

                </use>
              </svg>
            </dt>
            <dd class="metric-value"><?php echo e(metric('Faculties involved  One shared campus.')); ?></dd>
            <dd class="metric-note">One shared campus.</dd>
          </div>
        </dl>
      </section>
      <div class="scene-response">
        <a class="scene-response--recycle" href="#challenge-recycle"><span>A second life for your bottles</span><strong>Explore recycling challenges ↗</strong></a>
        <a class="scene-response--reuse" href="#challenge-reuse"><span>Small swaps. Lasting habits.</span><strong>Explore reuse challenges ↗</strong></a>
        <a class="scene-response--restore" href="user/challenges-browse.php"><span>A little care for the place we share</span><strong>Explore campus challenges ↗</strong></a>
      </div>
      <!-- Available challenges -->
      <section class="challenges-section" id="challenges" aria-labelledby="challenges-title">
        <div class="section-header">
          <div>
            <h2 id="challenges-title">Featured challenges</h2>
            <p class="context-line">Find your next small step</p>
          </div>
          <span class="section-note">New challenges will appear here.</span>
        </div>
        <div class="challenge-grid">
          <article class="challenge-card challenge-card--featured" id="challenge-recycle">
            <div class="challenge-topline">
              <span class="category-tag category-tag--light">Recycling</span>
            </div>
            <div class="featured-content">
              <div class="challenge-copy">
                <h3>Recycling</h3>
                <p><?php $public_challenge=one("SELECT * FROM challenges WHERE category=? AND status='open' AND start_date<=CURDATE() AND end_date>=CURDATE() ORDER BY id DESC LIMIT 1",['Recycling']); echo e($public_challenge['title'] ?? 'No active challenge yet.'); ?></p>
                <div class="challenge-meta">
                  <span class="points-label"><?php echo $public_challenge ? e($public_challenge['points']) . ' points · ' . e($public_challenge['end_date']) : 'No active challenge'; ?></span>
                </div>
              </div>
              <img class="bottle-art" src="assets/icons/challenge-bottles.svg" width="600" height="600" alt="" loading="lazy">
            </div>
            <details class="challenge-details">
              <summary title="About recycling challenges">
                <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                  <path d="m6 9 6 6 6-6"></path>
                </svg>
                <span class="disclosure-open-label visually-hidden">About recycling challenges</span>
                <span class="disclosure-collapse-label">Close details<span class="visually-hidden">: About recycling challenges</span> </span>
              </summary>
              <div class="challenge-instructions"><?php echo '<p>' . e($public_challenge['description'] ?? 'New challenges will appear here when published.') . '</p><a class="text-link" href="login.php">Log in to join</a>'; ?></div>
            </details>
          </article>
          <article class="challenge-card challenge-card--sand" id="challenge-reuse">
            <div class="challenge-topline">
              <span class="category-tag">Reuse</span>
            </div>
            <img class="challenge-object" src="assets/icons/challenge-cup.svg" width="600" height="600" alt="" loading="lazy">
            <div class="challenge-copy">
              <h3>Reuse</h3>
              <p><?php $public_challenge=one("SELECT * FROM challenges WHERE category=? AND status='open' AND start_date<=CURDATE() AND end_date>=CURDATE() ORDER BY id DESC LIMIT 1",['Reuse']); echo e($public_challenge['title'] ?? 'No active challenge yet.'); ?></p>
              <div class="challenge-meta">
                <span class="points-label"><?php echo $public_challenge ? e($public_challenge['points']) . ' points · ' . e($public_challenge['end_date']) : 'No active challenge'; ?></span>
              </div>
            </div>
            <details class="challenge-details">
              <summary title="About reuse challenges">
                <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                  <path d="m6 9 6 6 6-6"></path>
                </svg>
                <span class="disclosure-open-label visually-hidden">About reuse challenges</span>
                <span class="disclosure-collapse-label">Close details<span class="visually-hidden">: About reuse challenges</span> </span>
              </summary>
              <div class="challenge-instructions"><?php echo '<p>' . e($public_challenge['description'] ?? 'New challenges will appear here when published.') . '</p><a class="text-link" href="login.php">Log in to join</a>'; ?></div>
            </details>
          </article>
          <article class="challenge-card challenge-card--paper" id="challenge-bag">
            <div class="challenge-topline">
              <span class="category-tag">Everyday habits</span>
            </div>
            <img class="challenge-object" src="assets/icons/challenge-bag.svg" width="600" height="600" alt="" loading="lazy">
            <div class="challenge-copy">
              <h3>Everyday habits</h3>
              <p><?php $public_challenge=one("SELECT * FROM challenges WHERE category=? AND status='open' AND start_date<=CURDATE() AND end_date>=CURDATE() ORDER BY id DESC LIMIT 1",['Everyday habits']); echo e($public_challenge['title'] ?? 'No active challenge yet.'); ?></p>
              <div class="challenge-meta">
                <span class="points-label"><?php echo $public_challenge ? e($public_challenge['points']) . ' points · ' . e($public_challenge['end_date']) : 'No active challenge'; ?></span>
              </div>
            </div>
            <details class="challenge-details">
              <summary title="About everyday habit challenges">
                <svg class="icon" aria-hidden="true" viewbox="0 0 24 24">
                  <path d="m6 9 6 6 6-6"></path>
                </svg>
                <span class="disclosure-open-label visually-hidden">About everyday habit challenges</span>
                <span class="disclosure-collapse-label">Close details<span class="visually-hidden">: About everyday habit challenges</span> </span>
              </summary>
              <div class="challenge-instructions"><?php echo '<p>' . e($public_challenge['description'] ?? 'New challenges will appear here when published.') . '</p><a class="text-link" href="login.php">Log in to join</a>'; ?></div>
            </details>
          </article>
        </div>
      </section>
      <!-- Rankings and announcements -->
      <div class="community-grid">
        <section class="leaderboard-panel" id="leaderboard" aria-labelledby="leaderboard-title">
          <div class="panel-heading">
            <div>
              <h2 id="leaderboard-title">Campus leaderboard</h2>
              <p class="context-line">Better, together</p>
            </div>
          </div>
          <div class="table-wrapper">
            <table class="leaderboard-table">
              <caption class="visually-hidden">Faculty rankings by Green Points</caption>
              <thead>
                <tr>
                  <th scope="col">Rank</th>
                  <th scope="col">Faculty</th>
                  <th scope="col">Green Points</th>
                </tr>
              </thead>
              <tbody><?php $kind='faculty-rankings'; $columns=['Rank', 'Faculty', 'Green Points']; include __DIR__ . '/includes/table.php'; ?></tbody>
            </table>
          </div>
          <p class="panel-footnote">
            <svg class="icon" aria-hidden="true">
              <use href="#icon-people">

              </use>
            </svg>
            Every participant helps their faculty move forward.
          </p>
        </section>
        <section class="announcements-panel" aria-labelledby="announcements-title">
          <div class="panel-heading">
            <div>
              <h2 id="announcements-title">What’s happening</h2>
              <p class="context-line">Around campus</p>
            </div>
            <svg class="icon announcements-icon" aria-hidden="true">
              <use href="#icon-arrow-up-right">

              </use>
            </svg>
          </div>
          <ol class="announcements-list"><?php $list_kind='announcements'; include __DIR__ . '/includes/record_list.php'; ?></ol>
        </section>
      </div>
      <section class="campus-story" aria-labelledby="campus-story-title">
        <img src="assets/nature/meadow.jpg" width="1800" height="1010" alt="A green meadow beneath an open sky" loading="lazy">
        <div class="campus-story-copy">
          <h2 id="campus-story-title">Our campus.<br>Our shared tomorrow.</h2>
          <p class="context-line">A little care goes a long way</p>
        </div>
        <a class="campus-story-note" href="user/challenges-browse.php">
          <span>Take the next small step</span>
          <strong>Explore campus challenges</strong>
          <svg class="icon" aria-hidden="true">
            <use href="#icon-arrow-up-right">

            </use>
          </svg>
        </a>
      </section>
      <!-- How participation works -->
      <section class="participation-section" id="about" aria-labelledby="participation-title">
        <div class="participation-intro">
          <h2 id="participation-title">Good habits.<br>Greater together.</h2>
          <p class="context-line">How Recyclables works</p>
          <p>A place for our campus to turn sustainable choices into a shared everyday habit.</p>
          <a class="text-link" href="#challenges">
            Find your first challenge
            <svg class="icon" aria-hidden="true">
              <use href="#icon-arrow-up-right">

              </use>
            </svg>
          </a>
          <figure class="participation-sky" aria-hidden="true">
            <img src="assets/nature/clouds.jpg" width="1800" height="1350" alt="" loading="lazy">
          </figure>
        </div>
        <ol class="participation-steps">
          <li>
            <span class="step-number" aria-hidden="true">01</span>
            <div>
              <h3>Choose a small step</h3>
              <p>Find a challenge that fits your day and read what you need to do.</p>
            </div>
          </li>
          <li>
            <span class="step-number" aria-hidden="true">02</span>
            <div>
              <h3>Make it happen</h3>
              <p>Complete the action, then share a clear evidence photo through your account.</p>
            </div>
          </li>
          <li>
            <span class="step-number" aria-hidden="true">03</span>
            <div>
              <h3>Let your impact grow</h3>
              <p>The sustainability team reviews your evidence. Approved submissions earn Green Points for you and your faculty.</p>
            </div>
          </li>
        </ol>
      </section>
    </main>
    <!-- Site footer -->
    <footer class="site-footer">
      <div class="container footer-inner">
        <div class="footer-about">
          <a class="brand brand--footer" href="#home">
            <img src="assets/brand-mark.svg" width="32" height="32" alt="">
            <span>recyclables<span class="brand-dot">.</span> </span>
          </a>
          <p>A greener campus starts with us.</p>
        </div>
        <div class="footer-meta">
          <a class="back-to-top" href="#home">
            Back to top
            <svg class="icon" aria-hidden="true">
              <use href="#icon-arrow-up-right">

              </use>
            </svg>
          </a>
        </div>
      </div>
    </footer>
  <script src="scripts/app.js" defer></script></body>
</html>