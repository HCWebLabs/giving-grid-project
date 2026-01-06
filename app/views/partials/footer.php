<?php
/**
 * Footer Partial
 * 
 * Site-wide footer with trust indicators and links.
 */
?>

<footer class="site-footer">
    <div class="footer-container">
        <!-- Trust Indicators -->
        <div class="footer-trust">
            <span class="trust-badge">
                <span aria-hidden="true">✓</span> Verified Organizations
            </span>
            <span class="trust-badge">
                <span aria-hidden="true">🛡</span> Safety-First
            </span>
            <span class="trust-badge">
                <span aria-hidden="true">$0</span> No Fees
            </span>
        </div>
        
        <!-- Footer Links -->
        <nav class="footer-nav" aria-label="Footer navigation">
            <ul class="footer-links">
                <li><a href="<?= url('/browse') ?>">Browse Needs</a></li>
                <li><a href="<?= url('/browse?type=offer') ?>">Browse Offers</a></li>
                <li><a href="<?= url('/organizations') ?>">Organizations</a></li>
            </ul>
            <ul class="footer-links">
                <li><a href="<?= url('/about') ?>">About</a></li>
                <li><a href="<?= url('/guidelines') ?>">Community Guidelines</a></li>
                <li><a href="<?= url('/privacy') ?>">Privacy Policy</a></li>
                <li><a href="<?= url('/terms') ?>">Terms of Use</a></li>
            </ul>
        </nav>
        
        <!-- Copyright & Attribution -->
        <div class="footer-bottom">
            <p class="footer-tagline">
                Built with care for East Tennessee. People over profit.
            </p>
            <p class="footer-copyright">
                &copy; <?= date('Y') ?> <?= e(APP_NAME) ?> &mdash; 
                A project of <a href="https://hcweblabs.com" target="_blank" rel="noopener">HC Web Labs</a>
            </p>
        </div>
    </div>
</footer>
