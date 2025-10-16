<?php

namespace App\Helpers;

/**
 * Path Helper
 * 
 * Provides utility methods for path and URL generation.
 * Centralizes base href calculation for consistent path resolution across the application.
 * 
 * Usage:
 *   use App\Helpers\PathHelper;
 *   
 *   $baseHref = PathHelper::getBaseHref();
 *   $appRoot = PathHelper::getAppRoot();
 *   $fullUrl = PathHelper::url('empleados/view/123');
 */
class PathHelper
{
    /**
     * Cached APP_ROOT value
     */
    private static ?string $appRoot = null;
    
    /**
     * Get the application root path
     * 
     * Calculates the base path from the SCRIPT_NAME server variable.
     * Normalizes Windows backslashes to forward slashes for URL compatibility.
     * 
     * Example:
     *   SCRIPT_NAME: /lotificaciones/public/index.php
     *   Returns: /lotificaciones/public
     * 
     * @return string Application root path (without trailing slash)
     */
    public static function getAppRoot(): string
    {
        if (self::$appRoot !== null) {
            return self::$appRoot;
        }
        
        // Get directory from SCRIPT_NAME and normalize path separators
        $appRoot = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
        
        // Normalize Windows backslashes to forward slashes for URLs
        $appRoot = str_replace('\\', '/', $appRoot);
        
        // Remove trailing slash
        $appRoot = rtrim($appRoot, '/');
        
        // Handle root directory case
        if ($appRoot === '' || $appRoot === '.') {
            $appRoot = '/';
        }
        
        // Cache the result
        self::$appRoot = $appRoot;
        
        return self::$appRoot;
    }
    
    /**
     * Get the base href for HTML <base> tag
     * 
     * Returns the application root with a trailing slash for use in <base href="">.
     * This ensures all relative URLs in the page are resolved correctly.
     * 
     * Example:
     *   Returns: /lotificaciones/public/
     *   Or: / (for root installation)
     * 
     * @return string Base href with trailing slash
     */
    public static function getBaseHref(): string
    {
        $appRoot = self::getAppRoot();
        
        // Ensure trailing slash
        return $appRoot === '/' ? '/' : $appRoot . '/';
    }
    
    /**
     * Generate a full URL relative to the application root
     * 
     * Useful for generating links, AJAX endpoints, redirects, etc.
     * 
     * Examples:
     *   PathHelper::url('empleados') => /lotificaciones/public/empleados
     *   PathHelper::url('empleados/view/123') => /lotificaciones/public/empleados/view/123
     *   PathHelper::url('/assets/css/style.css') => /lotificaciones/public/assets/css/style.css
     * 
     * @param string $path Relative path (without leading slash)
     * @return string Full URL path
     */
    public static function url(string $path): string
    {
        $appRoot = self::getAppRoot();
        
        // Remove leading slash from path if present
        $path = ltrim($path, '/');
        
        // If path is empty, return just the app root
        if ($path === '') {
            return $appRoot === '/' ? '/' : $appRoot;
        }
        
        // Combine app root with path
        return $appRoot === '/' ? '/' . $path : $appRoot . '/' . $path;
    }
    
    /**
     * Generate an absolute URL with protocol and domain
     * 
     * Useful for email links, external redirects, canonical URLs, etc.
     * 
     * Examples:
     *   PathHelper::absoluteUrl('empleados') => http://localhost:8080/lotificaciones/public/empleados
     *   PathHelper::absoluteUrl('api/users') => http://localhost:8080/lotificaciones/public/api/users
     * 
     * @param string $path Relative path
     * @return string Absolute URL with protocol and domain
     */
    public static function absoluteUrl(string $path = ''): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        $relativePath = self::url($path);
        
        return $protocol . '://' . $host . $relativePath;
    }
    
    /**
     * Generate HTML <base> tag
     * 
     * Convenience method to generate the complete <base href=""> tag.
     * 
     * Example:
     *   echo PathHelper::baseTag();
     *   // Outputs: <base href="/lotificaciones/public/">
     * 
     * @return string Complete HTML base tag
     */
    public static function baseTag(): string
    {
        $baseHref = self::getBaseHref();
        return '<base href="' . htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Check if current request is HTTPS
     * 
     * @return bool True if HTTPS, false otherwise
     */
    public static function isSecure(): bool
    {
        return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }
    
    /**
     * Get the current page URL
     * 
     * @param bool $includeQuery Include query string
     * @return string Current page URL
     */
    public static function currentUrl(bool $includeQuery = true): string
    {
        $url = $_SERVER['REQUEST_URI'] ?? '/';
        
        if (!$includeQuery && strpos($url, '?') !== false) {
            $url = substr($url, 0, strpos($url, '?'));
        }
        
        return $url;
    }
    
    /**
     * Redirect to a URL
     * 
     * @param string $path Path to redirect to (relative to app root)
     * @param int $statusCode HTTP status code (default: 302)
     * @return void
     */
    public static function redirect(string $path, int $statusCode = 302): void
    {
        $url = self::url($path);
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
    
    /**
     * Clear cached values (useful for testing)
     * 
     * @return void
     */
    public static function clearCache(): void
    {
        self::$appRoot = null;
    }
}
