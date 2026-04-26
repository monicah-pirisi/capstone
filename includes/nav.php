<?php
/**
 * Samburu EWS - Primary Navigation
 */
if (defined('NAV_INCLUDED')) return;
define('NAV_INCLUDED', true);

$currentPage = basename($_SERVER['PHP_SELF'], '.php');

$parentMap = [
    'scientific-data' => 'findings',
    'indigenous-data' => 'findings',
    'prototype'       => 'prototype',
    'solution'        => 'solution',
];
$activePage = $parentMap[$currentPage] ?? $currentPage;

function navLink(string $href, string $label, string $page, string $activePage): string {
    $isActive = ($activePage === $page) ? ' active' : '';
    return '<a href="' . $href . '" class="nav-link' . $isActive . '">' . $label . '</a>';
}
function dropLink(string $href, string $label, string $page, string $currentPage): string {
    $isActive = ($currentPage === $page) ? ' active' : '';
    return '<a href="' . $href . '" class="dropdown-link' . $isActive . '">' . $label . '</a>';
}
?>
<nav class="primary-nav" id="primaryNav" aria-label="Main navigation">
    <ul class="nav-list" role="list">

        <li>
            <?= navLink(base_url('index.php'), 'Home', 'index', $activePage) ?>
        </li>

        <li>
            <?= navLink(base_url('problem.php'), 'Barriers', 'problem', $activePage) ?>
        </li>

        <li>
            <?= navLink(base_url('resources.php'), 'Education Hub', 'resources', $activePage) ?>
        </li>

        <li class="has-dropdown has-split-dropdown">
            <a href="<?= base_url('findings.php') ?>"
               class="nav-link <?= $activePage === 'findings' ? 'active' : '' ?>">
                Findings
            </a>
            <button class="dropdown-btn" aria-haspopup="true" aria-expanded="false" aria-label="Show visualization sub-pages">
                <span class="dropdown-arrow" aria-hidden="true">▾</span>
            </button>
            <ul class="dropdown" role="menu">
                <li role="none"><?= dropLink(base_url('scientific-data.php'), 'Scientific Data', 'scientific-data', $currentPage) ?></li>
                <li role="none"><?= dropLink(base_url('indigenous-data.php'), 'Indigenous Data', 'indigenous-data', $currentPage) ?></li>
            </ul>
        </li>

        <li>
            <?= navLink(base_url('current-alert.php'), 'Current Alert', 'current-alert', $activePage) ?>
        </li>

        <li>
            <?= navLink(base_url('prototype.php'), 'Prototype', 'prototype', $activePage) ?>
        </li>

        <li>
            <?= navLink(base_url('solution.php'), 'Recommendation', 'solution', $activePage) ?>
        </li>

        <li>
            <?= navLink(base_url('stakeholders.php'), 'Stakeholders', 'stakeholders', $activePage) ?>
        </li>

        <li class="nav-mobile-only">
            <?= navLink(base_url('contact.php'), 'Contact Us →', 'contact', $activePage) ?>
        </li>

        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
        <li>
            <a href="<?= base_url('admin.php') ?>" class="nav-link <?= $currentPage === 'admin' ? 'active' : '' ?>">Admin</a>
        </li>
        <li>
            <a href="<?= base_url('logout.php') ?>" class="nav-link">Logout</a>
        </li>
        <?php endif; ?>

    </ul>
</nav>
