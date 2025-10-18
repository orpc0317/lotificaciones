<?php
/**
 * API Service
 * Handles all communication with the REST API backend
 * Provides a clean interface for the controller to interact with the API
 */

namespace App\Services;

class ApiService
{
    private $baseUrl;
    private $apiKey;

    public function __construct()
    {
        // Load API configuration from environment
        $env = parse_ini_file(__DIR__ . '/../../config/.env');
        
        // API base URL (default to localhost)
        $this->baseUrl = $env['API_BASE_URL'] ?? 'http://localhost/lotificaciones-api/public/api';
        
        // API key for authentication (optional)
        $this->apiKey = $env['API_KEY'] ?? null;
    }

    /**
     * Make a GET request to the API
     */
    public function get($endpoint, $params = [])
    {
        $url = $this->baseUrl . $endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $this->request('GET', $url);
    }

    /**
     * Make a POST request to the API
     */
    public function post($endpoint, $data = [], $files = [])
    {
        $url = $this->baseUrl . $endpoint;
        
        if (empty($files)) {
            // JSON request
            return $this->request('POST', $url, $data);
        } else {
            // Multipart form data (for file uploads)
            return $this->requestWithFiles('POST', $url, $data, $files);
        }
    }

    /**
     * Make a PUT request to the API
     */
    public function put($endpoint, $data = [])
    {
        $url = $this->baseUrl . $endpoint;
        return $this->request('PUT', $url, $data);
    }

    /**
     * Make a DELETE request to the API
     */
    public function delete($endpoint)
    {
        $url = $this->baseUrl . $endpoint;
        return $this->request('DELETE', $url);
    }

    /**
     * Generic HTTP request handler
     */
    private function request($method, $url, $data = null)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // Set headers
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        if ($this->apiKey) {
            $headers[] = 'X-API-Key: ' . $this->apiKey;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Add request body for POST/PUT
        if ($data !== null && in_array($method, ['POST', 'PUT'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        // Handle errors
        if ($error) {
            throw new \Exception('API Request Failed: ' . $error);
        }

        // Parse JSON response
        $result = json_decode($response, true);

        if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from API');
        }

        // Add HTTP status code to result
        if (is_array($result)) {
            $result['_http_code'] = $httpCode;
        }

        return $result;
    }

    /**
     * Request with file uploads (multipart/form-data)
     */
    private function requestWithFiles($method, $url, $data = [], $files = [])
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // Build multipart form data
        $postData = [];
        
        // Add regular fields
        foreach ($data as $key => $value) {
            $postData[$key] = $value;
        }

        // Add files
        foreach ($files as $fieldName => $file) {
            if (isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
                $postData[$fieldName] = new \CURLFile(
                    $file['tmp_name'],
                    $file['type'] ?? 'application/octet-stream',
                    $file['name'] ?? 'file'
                );
            }
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        // Set headers
        $headers = [
            'Accept: application/json'
        ];

        if ($this->apiKey) {
            $headers[] = 'X-API-Key: ' . $this->apiKey;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        // Handle errors
        if ($error) {
            throw new \Exception('API Request Failed: ' . $error);
        }

        // Parse JSON response
        $result = json_decode($response, true);

        if ($result === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from API');
        }

        // Add HTTP status code to result
        if (is_array($result)) {
            $result['_http_code'] = $httpCode;
        }

        return $result;
    }

    /**
     * Check if API is available
     */
    public function health()
    {
        try {
            $result = $this->get('/health');
            return isset($result['success']) && $result['success'] === true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
