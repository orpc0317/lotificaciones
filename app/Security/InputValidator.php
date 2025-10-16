<?php

namespace App\Security;

/**
 * Input Validation and Sanitization Helper
 * 
 * Provides comprehensive input validation and sanitization for all user inputs.
 * Prevents injection attacks, validates data types, and ensures data integrity.
 * 
 * Usage:
 *   $validator = new InputValidator($_POST);
 *   $validator->required('nombres')->string()->maxLength(255);
 *   $validator->optional('email')->email();
 *   if ($validator->hasErrors()) {
 *       $errors = $validator->getErrors();
 *   }
 *   $cleanData = $validator->getSanitized();
 */
class InputValidator
{
    /**
     * Raw input data
     */
    private array $input;
    
    /**
     * Sanitized data
     */
    private array $sanitized = [];
    
    /**
     * Validation errors
     */
    private array $errors = [];
    
    /**
     * Current field being validated
     */
    private ?string $currentField = null;
    
    /**
     * Current field value
     */
    private mixed $currentValue = null;
    
    /**
     * Is current field required
     */
    private bool $isRequired = false;
    
    /**
     * Constructor
     * 
     * @param array $input Raw input data (usually $_POST)
     */
    public function __construct(array $input)
    {
        $this->input = $input;
    }
    
    /**
     * Start validating a required field
     * 
     * @param string $field Field name
     * @param string|null $label Custom label for error messages
     * @return self
     */
    public function required(string $field, ?string $label = null): self
    {
        $this->currentField = $field;
        $this->currentValue = $this->input[$field] ?? null;
        $this->isRequired = true;
        
        $label = $label ?? ucfirst($field);
        
        if ($this->currentValue === null || $this->currentValue === '') {
            $this->errors[$field] = "{$label} es obligatorio";
        }
        
        return $this;
    }
    
    /**
     * Start validating an optional field
     * 
     * @param string $field Field name
     * @return self
     */
    public function optional(string $field): self
    {
        $this->currentField = $field;
        $this->currentValue = $this->input[$field] ?? null;
        $this->isRequired = false;
        
        // Skip validation if field is empty
        if ($this->currentValue === null || $this->currentValue === '') {
            $this->sanitized[$field] = null;
        }
        
        return $this;
    }
    
    /**
     * Validate and sanitize as string
     * 
     * @return self
     */
    public function string(): self
    {
        if ($this->shouldSkipValidation()) {
            return $this;
        }
        
        if (!is_string($this->currentValue) && !is_numeric($this->currentValue)) {
            $this->errors[$this->currentField] = ucfirst($this->currentField) . ' debe ser texto';
            return $this;
        }
        
        // Sanitize: remove HTML tags and trim whitespace
        $sanitized = trim(strip_tags((string)$this->currentValue));
        $this->sanitized[$this->currentField] = $sanitized;
        $this->currentValue = $sanitized;
        
        return $this;
    }
    
    /**
     * Validate and sanitize as integer
     * 
     * @return self
     */
    public function integer(): self
    {
        if ($this->shouldSkipValidation()) {
            return $this;
        }
        
        if (!is_numeric($this->currentValue)) {
            $this->errors[$this->currentField] = ucfirst($this->currentField) . ' debe ser un número entero';
            return $this;
        }
        
        $sanitized = (int)$this->currentValue;
        $this->sanitized[$this->currentField] = $sanitized;
        $this->currentValue = $sanitized;
        
        return $this;
    }
    
    /**
     * Validate as email
     * 
     * @return self
     */
    public function email(): self
    {
        if ($this->shouldSkipValidation()) {
            return $this;
        }
        
        $sanitized = filter_var($this->currentValue, FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($sanitized, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$this->currentField] = 'Email no es válido';
            return $this;
        }
        
        $this->sanitized[$this->currentField] = $sanitized;
        $this->currentValue = $sanitized;
        
        return $this;
    }
    
    /**
     * Validate as date (YYYY-MM-DD format)
     * 
     * @return self
     */
    public function date(): self
    {
        if ($this->shouldSkipValidation()) {
            return $this;
        }
        
        // Try to parse date
        $timestamp = strtotime($this->currentValue);
        
        if ($timestamp === false) {
            $this->errors[$this->currentField] = 'Fecha no es válida';
            return $this;
        }
        
        // Validate date format YYYY-MM-DD
        $date = date('Y-m-d', $timestamp);
        
        // Additional validation: check if date is realistic
        $year = (int)date('Y', $timestamp);
        if ($year < 1900 || $year > 2100) {
            $this->errors[$this->currentField] = 'Fecha fuera de rango válido';
            return $this;
        }
        
        $this->sanitized[$this->currentField] = $date;
        $this->currentValue = $date;
        
        return $this;
    }
    
    /**
     * Validate as phone number
     * Accepts: +1234567890, (123) 456-7890, 123-456-7890, etc.
     * 
     * @return self
     */
    public function phone(): self
    {
        if ($this->shouldSkipValidation()) {
            return $this;
        }
        
        // Remove all non-digit characters except +
        $sanitized = preg_replace('/[^0-9+]/', '', $this->currentValue);
        
        // Validate length (between 7 and 15 digits)
        $digitCount = strlen(str_replace('+', '', $sanitized));
        
        if ($digitCount < 7 || $digitCount > 15) {
            $this->errors[$this->currentField] = 'Teléfono debe tener entre 7 y 15 dígitos';
            return $this;
        }
        
        $this->sanitized[$this->currentField] = $sanitized;
        $this->currentValue = $sanitized;
        
        return $this;
    }
    
    /**
     * Validate maximum length
     * 
     * @param int $max Maximum allowed length
     * @return self
     */
    public function maxLength(int $max): self
    {
        if ($this->shouldSkipValidation()) {
            return $this;
        }
        
        if (strlen($this->currentValue) > $max) {
            $this->errors[$this->currentField] = ucfirst($this->currentField) . " no puede exceder {$max} caracteres";
        }
        
        return $this;
    }
    
    /**
     * Validate minimum length
     * 
     * @param int $min Minimum required length
     * @return self
     */
    public function minLength(int $min): self
    {
        if ($this->shouldSkipValidation()) {
            return $this;
        }
        
        if (strlen($this->currentValue) < $min) {
            $this->errors[$this->currentField] = ucfirst($this->currentField) . " debe tener al menos {$min} caracteres";
        }
        
        return $this;
    }
    
    /**
     * Validate minimum value (for numbers)
     * 
     * @param int|float $min Minimum value
     * @return self
     */
    public function min($min): self
    {
        if ($this->shouldSkipValidation()) {
            return $this;
        }
        
        if ($this->currentValue < $min) {
            $this->errors[$this->currentField] = ucfirst($this->currentField) . " debe ser al menos {$min}";
        }
        
        return $this;
    }
    
    /**
     * Validate maximum value (for numbers)
     * 
     * @param int|float $max Maximum value
     * @return self
     */
    public function max($max): self
    {
        if ($this->shouldSkipValidation()) {
            return $this;
        }
        
        if ($this->currentValue > $max) {
            $this->errors[$this->currentField] = ucfirst($this->currentField) . " no puede exceder {$max}";
        }
        
        return $this;
    }
    
    /**
     * Validate value is in allowed list
     * 
     * @param array $allowed Allowed values
     * @return self
     */
    public function in(array $allowed): self
    {
        if ($this->shouldSkipValidation()) {
            return $this;
        }
        
        if (!in_array($this->currentValue, $allowed, true)) {
            $this->errors[$this->currentField] = ucfirst($this->currentField) . ' contiene un valor no válido';
        }
        
        return $this;
    }
    
    /**
     * Custom validation with callback
     * 
     * @param callable $callback Validation function (receives value, returns bool)
     * @param string $errorMessage Error message if validation fails
     * @return self
     */
    public function custom(callable $callback, string $errorMessage): self
    {
        if ($this->shouldSkipValidation()) {
            return $this;
        }
        
        if (!$callback($this->currentValue)) {
            $this->errors[$this->currentField] = $errorMessage;
        }
        
        return $this;
    }
    
    /**
     * Check if validation should be skipped
     * 
     * @return bool
     */
    private function shouldSkipValidation(): bool
    {
        // Skip if field already has errors
        if (isset($this->errors[$this->currentField])) {
            return true;
        }
        
        // Skip if optional and empty
        if (!$this->isRequired && ($this->currentValue === null || $this->currentValue === '')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if there are validation errors
     * 
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
    
    /**
     * Get validation errors
     * 
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Get sanitized data
     * 
     * @return array
     */
    public function getSanitized(): array
    {
        return $this->sanitized;
    }
    
    /**
     * Get a specific sanitized value
     * 
     * @param string $field Field name
     * @param mixed $default Default value if not set
     * @return mixed
     */
    public function get(string $field, $default = null)
    {
        return $this->sanitized[$field] ?? $default;
    }
    
    /**
     * Merge sanitized data with original input (for fields not validated)
     * 
     * @return array
     */
    public function getAll(): array
    {
        return array_merge($this->input, $this->sanitized);
    }
}
