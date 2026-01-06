<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($metaDescription ?? 'Connecting needs, surplus, and volunteers across Tennessee communities.') ?>">
    
    <title><?= e(isset($pageTitle) ? "{$pageTitle} | " . APP_NAME : APP_NAME) ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="<?= asset('img/favicon.ico') ?>" type="image/x-icon">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="auth-page">
    <!-- Skip Link for Accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <!-- Minimal Header -->
    <header class="auth-header">
        <a href="<?= url('/') ?>" class="auth-brand">
            <span class="brand-icon" aria-hidden="true">⊞</span>
            <span class="brand-text"><?= e(APP_NAME) ?></span>
        </a>
    </header>
    
    <!-- Flash Messages -->
    <?php includePartial('partials/flash') ?>
    
    <!-- Main Content -->
    <main id="main-content" class="auth-main">
        <?= $content ?>
    </main>
    
    <!-- Minimal Footer -->
    <footer class="auth-footer">
        <p>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?></p>
    </footer>
    
    <!-- Scripts -->
    <script src="<?= asset('js/main.js') ?>" defer></script>
</body>
</html>
