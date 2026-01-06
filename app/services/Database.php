<?php
/**
 * Database Service
 * 
 * PDO singleton wrapper for MySQL connections.
 * Provides a single, reusable database connection throughout the application.
 */

declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;

class Database
{
    /**
     * Singleton PDO instance
     */
    private static ?PDO $instance = null;
    
    /**
     * Prevent direct instantiation
     */
    private function __construct() {}
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Get the database connection
     * 
     * Creates a new PDO instance on first call, returns existing instance thereafter.
     * 
     * @return PDO
     * @throws PDOException If connection fails
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(
                    DB_DSN,
                    DB_USER,
                    DB_PASS,
                    DB_OPTIONS
                );
            } catch (PDOException $e) {
                // Log error in production, show details in development
                if (APP_DEBUG) {
                    throw new PDOException("Database connection failed: " . $e->getMessage());
                }
                
                error_log("Database connection failed: " . $e->getMessage());
                throw new PDOException("Database connection failed. Please try again later.");
            }
        }
        
        return self::$instance;
    }
    
    /**
     * Shorthand alias for getConnection()
     * 
     * @return PDO
     */
    public static function get(): PDO
    {
        return self::getConnection();
    }
    
    /**
     * Execute a query and return all results
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Execute a query and return a single row
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array|null
     */
    public static function fetch(string $sql, array $params = []): ?array
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Execute a query and return a single column value
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return mixed
     */
    public static function fetchColumn(string $sql, array $params = []): mixed
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Execute a query (INSERT, UPDATE, DELETE)
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return int Number of affected rows
     */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    /**
     * Insert a row and return the last insert ID
     * 
     * @param string $sql SQL INSERT query with placeholders
     * @param array $params Parameters to bind
     * @return int Last insert ID
     */
    public static function insert(string $sql, array $params = []): int
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return (int) self::get()->lastInsertId();
    }
    
    /**
     * Begin a transaction
     */
    public static function beginTransaction(): void
    {
        self::get()->beginTransaction();
    }
    
    /**
     * Commit a transaction
     */
    public static function commit(): void
    {
        self::get()->commit();
    }
    
    /**
     * Rollback a transaction
     */
    public static function rollback(): void
    {
        self::get()->rollBack();
    }
}
