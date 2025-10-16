<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Test - CSRF Protection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-shield-check"></i> Security Tests
                        </h4>
                    </div>
                    <div class="card-body">
                        <h5>CSRF Protection Tests</h5>
                        <p class="text-muted">These tests verify that CSRF protection is working correctly.</p>
                        
                        <div class="test-section mb-4">
                            <h6 class="border-bottom pb-2">Test 1: Valid CSRF Token</h6>
                            <p>This should succeed (200 response)</p>
                            <button class="btn btn-success" onclick="testValidToken()">
                                <i class="bi bi-play-fill"></i> Run Test
                            </button>
                            <div id="result1" class="mt-2"></div>
                        </div>
                        
                        <div class="test-section mb-4">
                            <h6 class="border-bottom pb-2">Test 2: Invalid CSRF Token</h6>
                            <p>This should fail (403 response)</p>
                            <button class="btn btn-danger" onclick="testInvalidToken()">
                                <i class="bi bi-play-fill"></i> Run Test
                            </button>
                            <div id="result2" class="mt-2"></div>
                        </div>
                        
                        <div class="test-section mb-4">
                            <h6 class="border-bottom pb-2">Test 3: Missing CSRF Token</h6>
                            <p>This should fail (403 response)</p>
                            <button class="btn btn-warning" onclick="testMissingToken()">
                                <i class="bi bi-play-fill"></i> Run Test
                            </button>
                            <div id="result3" class="mt-2"></div>
                        </div>
                        
                        <div class="test-section mb-4">
                            <h6 class="border-bottom pb-2">Test 4: Security Headers</h6>
                            <p>Check if security headers are present</p>
                            <button class="btn btn-info" onclick="testHeaders()">
                                <i class="bi bi-play-fill"></i> Run Test
                            </button>
                            <div id="result4" class="mt-2"></div>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow mt-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Test Results Summary</h5>
                    </div>
                    <div class="card-body">
                        <div id="summary">
                            <p class="text-muted">Run tests to see results...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Get CSRF token from PHP
        const csrfToken = '<?php 
            require_once __DIR__ . "/../vendor/autoload.php";
            use App\Security\CsrfProtection;
            echo CsrfProtection::getToken();
        ?>';
        
        function api(path) {
            const base = window.location.origin + '/lotificaciones/public/';
            return base + path;
        }
        
        function showResult(elementId, success, message, details = '') {
            const el = document.getElementById(elementId);
            const icon = success ? 'check-circle-fill' : 'x-circle-fill';
            const color = success ? 'success' : 'danger';
            el.innerHTML = `
                <div class="alert alert-${color} alert-dismissible fade show">
                    <i class="bi bi-${icon}"></i>
                    <strong>${success ? 'Pass' : 'Fail'}:</strong> ${message}
                    ${details ? '<pre class="mt-2 mb-0 small">' + details + '</pre>' : ''}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            updateSummary();
        }
        
        async function testValidToken() {
            try {
                const response = await fetch(api('empleados/delete'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=999999&csrf_token=' + encodeURIComponent(csrfToken)
                });
                
                const data = await response.json();
                
                // We expect this to fail because ID doesn't exist, but CSRF should pass
                // So we're looking for a different error than CSRF
                if (response.status === 403) {
                    showResult('result1', false, 'CSRF validation failed unexpectedly', 
                        JSON.stringify(data, null, 2));
                } else {
                    showResult('result1', true, 'CSRF token accepted (status: ' + response.status + ')', 
                        JSON.stringify(data, null, 2));
                }
            } catch (error) {
                showResult('result1', false, 'Request error', error.message);
            }
        }
        
        async function testInvalidToken() {
            try {
                const response = await fetch(api('empleados/delete'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=999999&csrf_token=invalid_token_12345'
                });
                
                const data = await response.json();
                
                if (response.status === 403) {
                    showResult('result2', true, 'CSRF validation correctly rejected invalid token', 
                        JSON.stringify(data, null, 2));
                } else {
                    showResult('result2', false, 'Invalid token was accepted (security issue!)', 
                        JSON.stringify(data, null, 2));
                }
            } catch (error) {
                showResult('result2', false, 'Request error', error.message);
            }
        }
        
        async function testMissingToken() {
            try {
                const response = await fetch(api('empleados/delete'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=999999'
                });
                
                const data = await response.json();
                
                if (response.status === 403) {
                    showResult('result3', true, 'CSRF validation correctly rejected missing token', 
                        JSON.stringify(data, null, 2));
                } else {
                    showResult('result3', false, 'Missing token was accepted (security issue!)', 
                        JSON.stringify(data, null, 2));
                }
            } catch (error) {
                showResult('result3', false, 'Request error', error.message);
            }
        }
        
        async function testHeaders() {
            try {
                const response = await fetch(api('empleados'));
                const headers = {
                    'X-Frame-Options': response.headers.get('X-Frame-Options'),
                    'X-Content-Type-Options': response.headers.get('X-Content-Type-Options'),
                    'X-XSS-Protection': response.headers.get('X-XSS-Protection'),
                    'Referrer-Policy': response.headers.get('Referrer-Policy'),
                    'Content-Security-Policy': response.headers.get('Content-Security-Policy')
                };
                
                const missing = Object.entries(headers).filter(([k, v]) => !v).map(([k]) => k);
                
                if (missing.length === 0) {
                    showResult('result4', true, 'All security headers present', 
                        JSON.stringify(headers, null, 2));
                } else {
                    showResult('result4', false, 'Missing headers: ' + missing.join(', '), 
                        JSON.stringify(headers, null, 2));
                }
            } catch (error) {
                showResult('result4', false, 'Request error', error.message);
            }
        }
        
        function updateSummary() {
            const results = [];
            for (let i = 1; i <= 4; i++) {
                const el = document.getElementById('result' + i);
                if (el && el.innerHTML) {
                    const isPassed = el.innerHTML.includes('alert-success');
                    results.push({
                        test: i,
                        passed: isPassed
                    });
                }
            }
            
            if (results.length > 0) {
                const passed = results.filter(r => r.passed).length;
                const total = results.length;
                const percentage = Math.round((passed / total) * 100);
                
                const color = percentage === 100 ? 'success' : percentage >= 75 ? 'warning' : 'danger';
                
                document.getElementById('summary').innerHTML = `
                    <div class="alert alert-${color}">
                        <h5>Results: ${passed}/${total} tests passed (${percentage}%)</h5>
                        ${percentage === 100 ? 
                            '<p class="mb-0"><i class="bi bi-shield-check"></i> All security tests passed!</p>' :
                            '<p class="mb-0"><i class="bi bi-exclamation-triangle"></i> Some tests failed. Review implementation.</p>'
                        }
                    </div>
                `;
            }
        }
        
        // Display current CSRF token
        console.log('Current CSRF Token:', csrfToken);
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
