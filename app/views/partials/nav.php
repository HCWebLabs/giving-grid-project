<?php
/**
 * Navigation Partial
 * 
 * Main site navigation bar.
 * Expected variables: $currentPath
 */

// Get current user if logged in
$currentUser = $_SESSION['user'] ?? null;
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && ($currentUser['role'] ?? '') === 'admin';
$isOrgMember = $isLoggedIn && ($currentUser['role'] ?? '') === 'org_member';
?>

<header class="site-header">
    <nav class="main-nav" aria-label="Main navigation">
        <div class="nav-container">
            <!-- Logo / Brand -->
            <a href="<?= url('/') ?>" class="nav-brand" aria-label="<?= e(APP_NAME) ?> - Home">
                <span class="brand-icon" aria-hidden="true">⊞</span>
                <span class="brand-text"><?= e(APP_NAME) ?></span>
            </a>
            
            <!-- Mobile Menu Toggle -->
            <button 
                type="button" 
                class="nav-toggle" 
                aria-expanded="false" 
                aria-controls="main-menu"
                aria-label="Toggle navigation menu"
            >
                <span class="nav-toggle-icon" aria-hidden="true"></span>
            </button>
            
            <!-- Navigation Links -->
            <div class="nav-menu" id="main-menu">
                <ul class="nav-links">
                    <li>
                        <a 
                            href="<?= url('/browse') ?>" 
                            <?= isCurrentPathPrefix('/browse') || isCurrentPathPrefix('/listing') ? 'aria-current="page"' : '' ?>
                        >
                            Browse
                        </a>
                    </li>
                    <li>
                        <a 
                            href="<?= url('/organizations') ?>" 
                            <?= isCurrentPathPrefix('/organization') ? 'aria-current="page"' : '' ?>
                        >
                            Organizations
                        </a>
                    </li>
                    
                    <?php if ($isLoggedIn): ?>
                        <li>
                            <a 
                                href="<?= url('/post') ?>" 
                                <?= isCurrentPath('/post') ? 'aria-current="page"' : '' ?>
                            >
                                Post to Grid
                            </a>
                        </li>
                        <li>
                            <a 
                                href="<?= url('/dashboard') ?>" 
                                <?= isCurrentPathPrefix('/dashboard') ? 'aria-current="page"' : '' ?>
                            >
                                Dashboard
                            </a>
                        </li>
                        
                        <?php if ($isAdmin): ?>
                            <li>
                                <a 
                                    href="<?= url('/admin') ?>" 
                                    <?= isCurrentPathPrefix('/admin') ? 'aria-current="page"' : '' ?>
                                >
                                    Admin
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <!-- Auth Links -->
                <div class="nav-auth">
                    <?php if ($isLoggedIn): ?>
                        <span class="nav-user">
                            <?= e($currentUser['display_name'] ?? 'User') ?>
                        </span>
                        <form action="<?= url('/logout') ?>" method="POST" class="nav-logout-form">
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
                            <button type="submit" class="nav-link-button">Log Out</button>
                        </form>
                    <?php else: ?>
                        <a href="<?= url('/login') ?>" class="nav-auth-link">Log In</a>
                        <a href="<?= url('/register') ?>" class="nav-auth-link nav-auth-register">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>
