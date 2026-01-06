<?php
/**
 * Cause Service
 * 
 * Business logic and database operations for cause tags.
 */

declare(strict_types=1);

namespace App\Services;

use App\Models\Cause;

class CauseService
{
    /**
     * Get all active causes
     */
    public static function getAll(): array
    {
        $sql = "
            SELECT c.*
            FROM causes c
            WHERE c.is_active = 1
            ORDER BY c.name ASC
        ";
        
        $rows = Database::fetchAll($sql);
        
        return array_map(fn($row) => Cause::fromRow($row), $rows);
    }
    
    /**
     * Get causes with listing counts
     */
    public static function getAllWithCounts(): array
    {
        $sql = "
            SELECT 
                c.*,
                COUNT(DISTINCT lc.listing_id) AS listing_count
            FROM causes c
            LEFT JOIN listing_causes lc ON c.id = lc.cause_id
            LEFT JOIN listings l ON lc.listing_id = l.id AND l.status = 'open'
            WHERE c.is_active = 1
            GROUP BY c.id
            ORDER BY listing_count DESC, c.name ASC
        ";
        
        $rows = Database::fetchAll($sql);
        
        return array_map(fn($row) => Cause::fromRow($row), $rows);
    }
    
    /**
     * Get a cause by slug
     */
    public static function getBySlug(string $slug): ?Cause
    {
        $sql = "SELECT * FROM causes WHERE slug = :slug AND is_active = 1";
        
        $row = Database::fetch($sql, [':slug' => $slug]);
        
        if (!$row) {
            return null;
        }
        
        return Cause::fromRow($row);
    }
    
    /**
     * Get a cause by ID
     */
    public static function getById(int $id): ?Cause
    {
        $sql = "SELECT * FROM causes WHERE id = :id";
        
        $row = Database::fetch($sql, [':id' => $id]);
        
        if (!$row) {
            return null;
        }
        
        return Cause::fromRow($row);
    }
    
    /**
     * Get causes for select dropdown
     */
    public static function getForSelect(): array
    {
        $causes = self::getAll();
        
        $options = [];
        foreach ($causes as $cause) {
            $options[$cause->id] = $cause->name;
        }
        
        return $options;
    }
}
