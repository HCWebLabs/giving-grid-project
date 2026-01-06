<?php
/**
 * 403 Forbidden Error Page
 */
?>

<section class="error-page error-403">
    <div class="error-container">
        <h1 class="error-code">403</h1>
        <h2 class="error-title">Access Denied</h2>
        <p class="error-message">
            You don't have permission to access this page.
        </p>
        <div class="error-actions">
            <a href="<?= url('/') ?>" class="btn btn-primary">Go Home</a>
            <a href="<?= url('/dashboard') ?>" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>
</section>
