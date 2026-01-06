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
    
    <?php if (isset($extraStyles)): ?>
        <?= $extraStyles ?>
    <?php endif; ?>
</head>
<body>
    <!-- Skip Link for Accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <!-- Header & Navigation -->
    <?php includePartial('partials/nav', [
        'currentPath' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
    ]) ?>
    
    <!-- Flash Messages -->
    <?php includePartial('partials/flash') ?>
    
    <!-- Main Content -->
    <main id="main-content">
        <?= $content ?>
    </main>
    
    <!-- Footer -->
    <?php includePartial('partials/footer') ?>
    
    <!-- Scripts -->
    <script src="<?= asset('js/main.js') ?>" defer></script>
    
    <?php if (isset($extraScripts)): ?>
        <?= $extraScripts ?>
    <?php endif; ?>
</body>
</html>
