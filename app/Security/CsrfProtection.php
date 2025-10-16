<?php

namespace App\Security;

/**
 * CSRF Protection Helper
 * 
 * Provides token generation and validation for Cross-Site Request Forgery protection.
 * Uses session-based tokens with secure random generation.
 * 
 * Usage:
 *   // Generate token in forms:
 *   CsrfProtection::generateToken();
 *   
 *   // Output token input:
 *   echo CsrfProtection::getTokenInput();
 *   
 *   // Validate in controllers:
 *   if (!CsrfProtection::validateToken($_POST['csrf_token'] ?? '')) {
 *       // Invalid token - reject request
 *   }
 */
class CsrfProtection
{
    /**
     * Session key for storing CSRF token
     */
    private const TOKEN_KEY = 'csrf_token';
    
    /**
     * Session key for storing token timestamp
     */
    private const TOKEN_TIME_KEY = 'csrf_token_time';
    
    /**
     * Token expiration time in seconds (1 hour)
     */
    private const TOKEN_EXPIRATION = 3600;
    
    /**
     * Initialize session if not already started
     */
    private static function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Only start session if headers haven't been sent
            if (!headers_sent()) {
                session_start();
            } else {
                // Headers already sent - try to start session without warning
                @session_start();
            }
        }
    }
    
    /**
     * Generate a new CSRF token
     * 
     * @return string The generated token
     */
    public static function generateToken(): string
    {
        self::initSession();
        
        // Check if existing token is still valid
        if (self::hasValidToken()) {
            return $_SESSION[self::TOKEN_KEY];
        }
        
        // Generate new token
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::TOKEN_KEY] = $token;
        $_SESSION[self::TOKEN_TIME_KEY] = time();
        
        return $token;
    }
    
    /**
     * Get the current CSRF token (generates one if not exists)
     * 
     * @return string The current token
     */
    public static function getToken(): string
    {
        self::initSession();
        
        if (!isset($_SESSION[self::TOKEN_KEY]) || !self::hasValidToken()) {
            return self::generateToken();
        }
        
        return $_SESSION[self::TOKEN_KEY];
    }
    
    /**
     * Check if a valid token exists
     * 
     * @return bool True if valid token exists
     */
    private static function hasValidToken(): bool
    {
        if (!isset($_SESSION[self::TOKEN_KEY]) || !isset($_SESSION[self::TOKEN_TIME_KEY])) {
            return false;
        }
        
        // Check if token has expired
        $tokenAge = time() - $_SESSION[self::TOKEN_TIME_KEY];
        if ($tokenAge > self::TOKEN_EXPIRATION) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate a submitted CSRF token
     * 
     * @param string $token The token to validate
     * @return bool True if token is valid
     */
    public static function validateToken(string $token): bool
    {
        self::initSession();
        
        // Check if token exists in session
        if (!isset($_SESSION[self::TOKEN_KEY])) {
            return false;
        }
        
        // Check if token has expired
        if (!self::hasValidToken()) {
            return false;
        }
        
        // Use timing-safe comparison to prevent timing attacks
        return hash_equals($_SESSION[self::TOKEN_KEY], $token);
    }
    
    /**
     * Get HTML for CSRF token hidden input field
     * 
     * @return string HTML input field
     */
    public static function getTokenInput(): string
    {
        $token = self::getToken();
        return sprintf(
            '<input type="hidden" name="csrf_token" value="%s">',
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }
    
    /**
     * Get token as JSON for JavaScript usage
     * 
     * @return string JSON with token
     */
    public static function getTokenJson(): string
    {
        return json_encode([
            'csrf_token' => self::getToken()
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
    
    /**
     * Refresh the token (useful after successful form submission)
     */
    public static function refreshToken(): void
    {
        self::initSession();
        unset($_SESSION[self::TOKEN_KEY]);
        unset($_SESSION[self::TOKEN_TIME_KEY]);
        self::generateToken();
    }
    
    /**
     * Validate token and send error response if invalid
     * 
     * @param string $token The token to validate
     * @param bool $json Whether to send JSON response (default) or HTML
     * @return bool True if valid, exits if invalid
     */
    public static function validateOrDie(string $token, bool $json = true): bool
    {
        if (!self::validateToken($token)) {
            http_response_code(403);
            
            if ($json) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid or expired CSRF token. Please refresh the page and try again.'
                ]);
            } else {
                echo 'Invalid or expired CSRF token. Please refresh the page and try again.';
            }
            
            exit;
        }
        
        return true;
    }
}
