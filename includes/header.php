<?php
/**
 * Samburu EWS - Shared Header
 */
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'SamEWS Recommender') ?> | <?= SITE_NAME ?></title>
    <meta name="description" content="SamEWS Recommender: Drought early warning and action recommendation platform combining scientific forecasts with indigenous knowledge for Samburu County, Kenya.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/styles.css') ?>?v=<?= filemtime(__DIR__ . '/../assets/css/styles.css') ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='45' fill='%230f5132'/><text x='50' y='68' font-size='55' text-anchor='middle' fill='white' font-family='sans-serif' font-weight='bold'>E</text></svg>">
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <header class="site-header">

        <div class="header-top">
            <div class="container header-top-inner">

                <a href="<?= base_url() ?>" class="brand">
                    <div class="brand-text">
                        <span class="brand-name"><?= SITE_NAME ?></span>
                        <span class="brand-tagline">Early Warning &amp; Recommendation · Samburu County</span>
                    </div>
                </a>

                <div class="header-actions">
                    <a href="<?= base_url('contact.php') ?>" class="header-cta">
                        Contact Us <span aria-hidden="true">→</span>
                    </a>
                    <button class="nav-toggle" id="navToggle"
                            aria-expanded="false"
                            aria-controls="primaryNav"
                            aria-label="Toggle navigation">
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                    </button>
                </div>

            </div>
        </div>

        <div class="header-nav-bar">
            <div class="container">
                <?php require __DIR__ . '/nav.php'; ?>
            </div>
        </div>

    </header>

    <main id="main-content">
