<?php
/**
 * Authentication Service
 * 
 * Handles user authentication, registration, and session management.
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

class AuthService
{
    /**
     * Attempt to log in a user
     * 
     * @param string $email
     * @param string $password
     * @return User|null User on success, null on failure
     */
    public static function attempt(string $email, string $password): ?User
    {
        $email = strtolower(trim($email));
        
        $sql = "
            SELECT 
                u.*,
                o.name AS org_name,
                o.is_verified AS org_verified
            FROM users u
            LEFT JOIN organizations o ON u.org_id = o.id
            WHERE LOWER(u.email) = :email
            LIMIT 1
        ";
        
        $row = Database::fetch($sql, [':email' => $email]);
        
        if (!$row) {
            return null;
        }
        
        // Verify password
        if (!password_verify($password, $row['password_hash'])) {
            return null;
        }
        
        // Check if user is active
        if (!$row['is_active']) {
            return null;
        }
        
        $user = User::fromRow($row);
        
        // Start session for user
        self::login($user);
        
        return $user;
    }
    
    /**
     * Log in a user (set session)
     */
    public static function login(User $user): void
    {
        // Regenerate session ID to prevent fixation
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user'] = $user->toSessionArray();
        $_SESSION['logged_in_at'] = time();
    }
    
    /**
     * Log out the current user
     */
    public static function logout(): void
    {
        // Clear session data
        $_SESSION = [];
        
        // Delete session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();
        
        // Start a new session for CSRF token
        session_start();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    /**
     * Register a new user
     * 
     * @param array $data User data
     * @return User|array User on success, array of errors on failure
     */
    public static function register(array $data): User|array
    {
        $errors = [];
        
        // Validate email
        $email = strtolower(trim($data['email'] ?? ''));
        if (empty($email)) {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        } elseif (self::emailExists($email)) {
            $errors['email'] = 'An account with this email already exists.';
        }
        
        // Validate display name
        $displayName = trim($data['display_name'] ?? '');
        if (empty($displayName)) {
            $errors['display_name'] = 'Display name is required.';
        } elseif (strlen($displayName) < 2) {
            $errors['display_name'] = 'Display name must be at least 2 characters.';
        } elseif (strlen($displayName) > 100) {
            $errors['display_name'] = 'Display name must be less than 100 characters.';
        }
        
        // Validate password
        $password = $data['password'] ?? '';
        if (empty($password)) {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }
        
        // Validate password confirmation
        $passwordConfirm = $data['password_confirm'] ?? '';
        if ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Passwords do not match.';
        }
        
        // Validate county (optional but must be valid if provided)
        $county = $data['county'] ?? null;
        if ($county && !isset(COUNTIES[$county])) {
            $errors['county'] = 'Please select a valid county.';
        }
        
        // Return errors if any
        if (!empty($errors)) {
            return $errors;
        }
        
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $sql = "
            INSERT INTO users (email, password_hash, display_name, county, role, is_active, created_at, updated_at)
            VALUES (:email, :password_hash, :display_name, :county, 'individual', 1, NOW(), NOW())
        ";
        
        $userId = Database::insert($sql, [
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':display_name' => $displayName,
            ':county' => $county,
        ]);
        
        // Fetch and return the new user
        return self::getById($userId);
    }
    
    /**
     * Get a user by ID
     */
    public static function getById(int $id): ?User
    {
        $sql = "
            SELECT 
                u.*,
                o.name AS org_name,
                o.is_verified AS org_verified
            FROM users u
            LEFT JOIN organizations o ON u.org_id = o.id
            WHERE u.id = :id
        ";
        
        $row = Database::fetch($sql, [':id' => $id]);
        
        if (!$row) {
            return null;
        }
        
        return User::fromRow($row);
    }
    
    /**
     * Get a user by email
     */
    public static function getByEmail(string $email): ?User
    {
        $sql = "
            SELECT 
                u.*,
                o.name AS org_name,
                o.is_verified AS org_verified
            FROM users u
            LEFT JOIN organizations o ON u.org_id = o.id
            WHERE LOWER(u.email) = :email
        ";
        
        $row = Database::fetch($sql, [':email' => strtolower(trim($email))]);
        
        if (!$row) {
            return null;
        }
        
        return User::fromRow($row);
    }
    
    /**
     * Check if an email is already registered
     */
    public static function emailExists(string $email): bool
    {
        $count = Database::fetchColumn(
            "SELECT COUNT(*) FROM users WHERE LOWER(email) = :email",
            [':email' => strtolower(trim($email))]
        );
        
        return (int) $count > 0;
    }
    
    /**
     * Get the currently logged-in user
     */
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }
    
    /**
     * Get the current user ID
     */
    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Check if a user is logged in
     */
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Check if current user is an admin
     */
    public static function isAdmin(): bool
    {
        return self::check() && ($_SESSION['user']['role'] ?? '') === 'admin';
    }
    
    /**
     * Check if current user is an org member
     */
    public static function isOrgMember(): bool
    {
        return self::check() && ($_SESSION['user']['role'] ?? '') === 'org_member';
    }
    
    /**
     * Check if current user has a verified organization
     */
    public static function hasVerifiedOrg(): bool
    {
        return self::isOrgMember() && ($_SESSION['user']['org_verified'] ?? false);
    }
    
    /**
     * Update user's password
     */
    public static function updatePassword(int $userId, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $affected = Database::execute(
            "UPDATE users SET password_hash = :hash, updated_at = NOW() WHERE id = :id",
            [':hash' => $hash, ':id' => $userId]
        );
        
        return $affected > 0;
    }
    
    /**
     * Update user's profile
     */
    public static function updateProfile(int $userId, array $data): bool
    {
        $sets = ['updated_at = NOW()'];
        $params = [':id' => $userId];
        
        if (isset($data['display_name'])) {
            $sets[] = 'display_name = :display_name';
            $params[':display_name'] = trim($data['display_name']);
        }
        
        if (array_key_exists('county', $data)) {
            $sets[] = 'county = :county';
            $params[':county'] = $data['county'] ?: null;
        }
        
        $sql = "UPDATE users SET " . implode(', ', $sets) . " WHERE id = :id";
        
        $affected = Database::execute($sql, $params);
        
        // Update session if this is the current user
        if ($affected > 0 && self::id() === $userId) {
            $user = self::getById($userId);
            if ($user) {
                $_SESSION['user'] = $user->toSessionArray();
            }
        }
        
        return $affected > 0;
    }
    
    /**
     * Refresh the current user's session data from database
     */
    public static function refreshSession(): void
    {
        if (!self::check()) {
            return;
        }
        
        $user = self::getById(self::id());
        
        if ($user && $user->is_active) {
            $_SESSION['user'] = $user->toSessionArray();
        } else {
            // User no longer valid, log out
            self::logout();
        }
    }
}
