<?php
/**
 * Samburu EWS — Shared Footer
 */
?>
    </main><!-- /#main-content -->

    <footer class="site-footer">
        <div class="container">
            <div class="footer-inner">
                <div class="footer-col">
                    <h3><?= SITE_NAME ?></h3>
                    <p><?= SITE_TAGLINE ?></p>
                    <p style="margin-top:var(--sp-sm);font-size:var(--fs-xs);color:rgba(255,255,255,.5);">
                        Samburu County, Kenya
                    </p>
                </div>
                <div class="footer-col">
                    <h3>Pages</h3>
                    <ul>
                        <li><a href="<?= base_url('index.php') ?>">Home</a></li>
                        <li><a href="<?= base_url('problem.php') ?>">Problem</a></li>
                        <li><a href="<?= base_url('resources.php') ?>">Resources</a></li>
                        <li><a href="<?= base_url('findings.php') ?>">Findings</a></li>
                        <li><a href="<?= base_url('current-alert.php') ?>">Current Alert</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Tools</h3>
                    <ul>
                        <li><a href="<?= base_url('channels.php') ?>">Channels</a></li>
                        <li><a href="<?= base_url('ussd-simulator.php') ?>">USSD Simulator</a></li>
                        <li><a href="<?= base_url('solution.php') ?>">Solution</a></li>
                        <li><a href="<?= base_url('stakeholders.php') ?>">Stakeholders</a></li>
                        <li><a href="<?= base_url('contact.php') ?>">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>External Resources</h3>
                    <ul>
                        <li><a href="https://www.ndma.go.ke" target="_blank" rel="noopener">NDMA</a></li>
                        <li><a href="https://www.meteo.go.ke" target="_blank" rel="noopener">KMD</a></li>
                        <li><a href="https://fews.net" target="_blank" rel="noopener">FEWS NET</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. CS Capstone Project &mdash; Samburu County Early Warning System.</p>
            </div>
        </div>
    </footer>

    <script src="<?= base_url('assets/js/main.js') ?>?v=<?= filemtime(__DIR__ . '/../assets/js/main.js') ?>"></script>

    <?php
    // Load page-specific scripts declared in each page file
    if (!empty($pageScripts)) {
        foreach ((array)$pageScripts as $script) {
            echo '<script src="' . base_url(htmlspecialchars($script)) . '"></script>' . "\n";
        }
    }
    ?>
</body>
</html>
