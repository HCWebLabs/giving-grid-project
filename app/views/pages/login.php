<?php
/**
 * Login Page
 */

$oldInput = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Log In</h1>
        <p class="auth-subtitle">Welcome back to The Giving Grid</p>
        
        <form method="POST" action="<?= url('/login') ?>" class="auth-form">
            <?= csrfField() ?>
            
            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?= e($oldInput['email'] ?? '') ?>"
                    required 
                    autocomplete="email"
                    autofocus
                >
            </div>
            
            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    autocomplete="current-password"
                >
            </div>
            
            <!-- Submit -->
            <button type="submit" class="btn btn-primary btn-block">
                Log In
            </button>
        </form>
        
        <div class="auth-links">
            <p>
                Don't have an account? 
                <a href="<?= url('/register') ?>">Create one</a>
            </p>
        </div>
    </div>
</div>
