<?php
/**
 * 404 Not Found Error Page
 */
?>

<section class="error-page">
    <div class="error-container">
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Page Not Found</h2>
        <p class="error-message">
            The page you're looking for doesn't exist or may have been moved.
        </p>
        <div class="error-actions">
            <a href="<?= url('/') ?>" class="btn btn-primary">Go Home</a>
            <a href="<?= url('/browse') ?>" class="btn btn-secondary">Browse the Grid</a>
        </div>
    </div>
</section>
