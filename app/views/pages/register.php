<?php
/**
 * Registration Page
 */

$oldInput = $_SESSION['old_input'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old_input'], $_SESSION['errors']);
?>

<div class="auth-container">
    <div class="auth-card">
        <h1>Create Account</h1>
        <p class="auth-subtitle">Join The Giving Grid community</p>
        
        <form method="POST" action="<?= url('/register') ?>" class="auth-form">
            <?= csrfField() ?>
            
            <!-- Display Name -->
            <div class="form-group <?= isset($errors['display_name']) ? 'has-error' : '' ?>">
                <label for="display_name">Display Name</label>
                <input 
                    type="text" 
                    id="display_name" 
                    name="display_name" 
                    value="<?= e($oldInput['display_name'] ?? '') ?>"
                    required 
                    autocomplete="name"
                    autofocus
                    maxlength="100"
                >
                <?php if (isset($errors['display_name'])): ?>
                    <span class="form-error"><?= e($errors['display_name']) ?></span>
                <?php endif; ?>
                <span class="form-hint">This is how you'll appear to others</span>
            </div>
            
            <!-- Email -->
            <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?= e($oldInput['email'] ?? '') ?>"
                    required 
                    autocomplete="email"
                >
                <?php if (isset($errors['email'])): ?>
                    <span class="form-error"><?= e($errors['email']) ?></span>
                <?php endif; ?>
            </div>
            
            <!-- County -->
            <div class="form-group <?= isset($errors['county']) ? 'has-error' : '' ?>">
                <label for="county">County <span class="optional">(optional)</span></label>
                <select id="county" name="county" autocomplete="address-level2">
                    <option value="">Select your county...</option>
                    <?php foreach (COUNTIES as $key => $label): ?>
                        <option 
                            value="<?= e($key) ?>"
                            <?= ($oldInput['county'] ?? '') === $key ? 'selected' : '' ?>
                        >
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['county'])): ?>
                    <span class="form-error"><?= e($errors['county']) ?></span>
                <?php endif; ?>
                <span class="form-hint">Helps us show relevant listings in your area</span>
            </div>
            
            <!-- Password -->
            <div class="form-group <?= isset($errors['password']) ? 'has-error' : '' ?>">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    autocomplete="new-password"
                    minlength="8"
                >
                <?php if (isset($errors['password'])): ?>
                    <span class="form-error"><?= e($errors['password']) ?></span>
                <?php endif; ?>
                <span class="form-hint">At least 8 characters</span>
            </div>
            
            <!-- Confirm Password -->
            <div class="form-group <?= isset($errors['password_confirm']) ? 'has-error' : '' ?>">
                <label for="password_confirm">Confirm Password</label>
                <input 
                    type="password" 
                    id="password_confirm" 
                    name="password_confirm" 
                    required 
                    autocomplete="new-password"
                >
                <?php if (isset($errors['password_confirm'])): ?>
                    <span class="form-error"><?= e($errors['password_confirm']) ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Terms -->
            <div class="form-group form-group-checkbox">
                <label class="checkbox-label">
                    <input type="checkbox" name="agree_terms" required>
                    <span>
                        I agree to the 
                        <a href="<?= url('/terms') ?>" target="_blank">Terms of Use</a> and 
                        <a href="<?= url('/privacy') ?>" target="_blank">Privacy Policy</a>
                    </span>
                </label>
            </div>
            
            <!-- Submit -->
            <button type="submit" class="btn btn-primary btn-block">
                Create Account
            </button>
        </form>
        
        <div class="auth-links">
            <p>
                Already have an account? 
                <a href="<?= url('/login') ?>">Log in</a>
            </p>
        </div>
        
        <div class="auth-org-note">
            <h3>Representing an Organization?</h3>
            <p>
                Create an individual account first, then apply to register your 
                organization. Our team will verify your nonprofit status.
            </p>
        </div>
    </div>
</div>
