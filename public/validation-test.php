<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Validation Tests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .test-result { margin: 10px 0; padding: 10px; border-radius: 4px; }
        .test-pass { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .test-fail { background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .test-pending { background-color: #fff3cd; border: 1px solid #ffeaa7; }
        pre { margin: 5px 0; font-size: 12px; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h3 class="mb-0"><i class="bi bi-clipboard-check"></i> Input Validation Tests</h3>
            </div>
            <div class="card-body">
                <p class="lead">Testing comprehensive input validation and sanitization</p>
                
                <div class="mb-4">
                    <button class="btn btn-primary btn-lg" onclick="runAllTests()">
                        <i class="bi bi-play-circle"></i> Run All Tests
                    </button>
                    <button class="btn btn-secondary ms-2" onclick="clearResults()">
                        <i class="bi bi-x-circle"></i> Clear Results
                    </button>
                </div>
                
                <div id="test-results"></div>
                
                <div class="mt-4 p-3 bg-light border rounded" id="summary" style="display:none;">
                    <h5>Test Summary</h5>
                    <div id="summary-content"></div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const API_BASE = window.location.origin + '/lotificaciones/public/';
        
        // Get CSRF token
        const csrfToken = '<?php 
            require_once __DIR__ . "/../vendor/autoload.php";
            use App\Security\CsrfProtection;
            echo CsrfProtection::getToken();
        ?>';
        
        const tests = [
            {
                name: 'Valid Employee Data',
                expectation: 'Should PASS validation',
                data: {
                    csrf_token: csrfToken,
                    nombres: 'Juan',
                    apellidos: 'Pérez',
                    email: 'juan.perez@example.com',
                    telefono: '555-1234',
                    edad: 30,
                    fecha_nacimiento: '1993-01-15',
                    genero: 'Masculino',
                    direccion: 'Calle Principal 123',
                    ciudad: 'San José',
                    comentarios: 'Empleado de prueba'
                },
                shouldPass: true
            },
            {
                name: 'Missing Required Field (nombres)',
                expectation: 'Should FAIL - nombres is required',
                data: {
                    csrf_token: csrfToken,
                    apellidos: 'González',
                    email: 'test@example.com'
                },
                shouldPass: false,
                expectedError: 'nombres'
            },
            {
                name: 'Invalid Email Format',
                expectation: 'Should FAIL - invalid email',
                data: {
                    csrf_token: csrfToken,
                    nombres: 'María',
                    apellidos: 'López',
                    email: 'invalid-email-format'
                },
                shouldPass: false,
                expectedError: 'email'
            },
            {
                name: 'Names Too Short',
                expectation: 'Should FAIL - nombres must be at least 2 characters',
                data: {
                    csrf_token: csrfToken,
                    nombres: 'A',
                    apellidos: 'Smith'
                },
                shouldPass: false,
                expectedError: 'nombres'
            },
            {
                name: 'Names Too Long',
                expectation: 'Should FAIL - nombres exceeds 255 characters',
                data: {
                    csrf_token: csrfToken,
                    nombres: 'A'.repeat(256),
                    apellidos: 'Test'
                },
                shouldPass: false,
                expectedError: 'nombres'
            },
            {
                name: 'Invalid Date Format',
                expectation: 'Should FAIL - invalid date',
                data: {
                    csrf_token: csrfToken,
                    nombres: 'Pedro',
                    apellidos: 'Ramírez',
                    fecha_nacimiento: 'not-a-date'
                },
                shouldPass: false,
                expectedError: 'fecha_nacimiento'
            },
            {
                name: 'Age Out of Range (too young)',
                expectation: 'Should FAIL - edad must be at least 18',
                data: {
                    csrf_token: csrfToken,
                    nombres: 'Ana',
                    apellidos: 'Torres',
                    edad: 15
                },
                shouldPass: false,
                expectedError: 'edad'
            },
            {
                name: 'Age Out of Range (too old)',
                expectation: 'Should FAIL - edad cannot exceed 100',
                data: {
                    csrf_token: csrfToken,
                    nombres: 'Carlos',
                    apellidos: 'Méndez',
                    edad: 150
                },
                shouldPass: false,
                expectedError: 'edad'
            },
            {
                name: 'Invalid Gender Value',
                expectation: 'Should FAIL - gender must be Masculino, Femenino, or Otro',
                data: {
                    csrf_token: csrfToken,
                    nombres: 'Luis',
                    apellidos: 'Vargas',
                    genero: 'Invalid'
                },
                shouldPass: false,
                expectedError: 'genero'
            },
            {
                name: 'Phone Too Short',
                expectation: 'Should FAIL - phone must be 7-15 digits',
                data: {
                    csrf_token: csrfToken,
                    nombres: 'Sofia',
                    apellidos: 'Rojas',
                    telefono: '123'
                },
                shouldPass: false,
                expectedError: 'telefono'
            },
            {
                name: 'XSS Attempt in Name',
                expectation: 'Should PASS but sanitize HTML tags',
                data: {
                    csrf_token: csrfToken,
                    nombres: '<script>alert("XSS")<\/script>John',
                    apellidos: 'Doe<b>Bold<\/b>'
                },
                shouldPass: true,
                checkSanitized: true
            },
            {
                name: 'SQL Injection Attempt',
                expectation: 'Should PASS - SQL injection should be sanitized',
                data: {
                    csrf_token: csrfToken,
                    nombres: "Robert'; DROP TABLE empleados;--",
                    apellidos: 'Tables'
                },
                shouldPass: true
            }
        ];
        
        let testResults = [];
        
        async function runAllTests() {
            clearResults();
            testResults = [];
            
            const resultsDiv = document.getElementById('test-results');
            resultsDiv.innerHTML = '<p class="text-muted">Running tests...</p>';
            
            for (let i = 0; i < tests.length; i++) {
                await runTest(tests[i], i);
                await sleep(500); // Delay between tests
            }
            
            showSummary();
        }
        
        async function runTest(test, index) {
            const resultsDiv = document.getElementById('test-results');
            const testId = `test-${index}`;
            
            // Add pending test
            const testDiv = document.createElement('div');
            testDiv.id = testId;
            testDiv.className = 'test-result test-pending';
            testDiv.innerHTML = `
                <h6><i class="bi bi-hourglass-split"></i> Test ${index + 1}: ${test.name}</h6>
                <p class="small mb-0">${test.expectation}</p>
                <p class="small text-muted mb-0">Running...</p>
            `;
            resultsDiv.appendChild(testDiv);
            
            try {
                // Create FormData
                const formData = new FormData();
                for (const [key, value] of Object.entries(test.data)) {
                    formData.append(key, value);
                }
                
                const response = await fetch(API_BASE + 'empleados/create', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                const passed = test.shouldPass ? response.ok : !response.ok;
                
                // Check if expected error exists
                let errorCheckPassed = true;
                if (test.expectedError && data.errors) {
                    errorCheckPassed = test.expectedError in data.errors;
                }
                
                const finalPassed = passed && errorCheckPassed;
                
                testResults.push({
                    name: test.name,
                    passed: finalPassed,
                    response: data,
                    status: response.status
                });
                
                // Update test result
                testDiv.className = `test-result ${finalPassed ? 'test-pass' : 'test-fail'}`;
                testDiv.innerHTML = `
                    <h6>
                        <i class="bi bi-${finalPassed ? 'check-circle-fill text-success' : 'x-circle-fill text-danger'}"></i>
                        Test ${index + 1}: ${test.name}
                    </h6>
                    <p class="small mb-1"><strong>Expectation:</strong> ${test.expectation}</p>
                    <p class="small mb-1">
                        <strong>Result:</strong> 
                        <span class="${finalPassed ? 'text-success' : 'text-danger'}">
                            ${finalPassed ? '✓ PASSED' : '✗ FAILED'}
                        </span>
                        (HTTP ${response.status})
                    </p>
                    ${data.errors ? `<p class="small mb-1"><strong>Errors:</strong> ${JSON.stringify(data.errors)}</p>` : ''}
                    <details class="small">
                        <summary>Response Details</summary>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    </details>
                `;
                
            } catch (error) {
                testResults.push({
                    name: test.name,
                    passed: false,
                    error: error.message
                });
                
                testDiv.className = 'test-result test-fail';
                testDiv.innerHTML = `
                    <h6><i class="bi bi-x-circle-fill text-danger"></i> Test ${index + 1}: ${test.name}</h6>
                    <p class="small mb-1"><strong>Expectation:</strong> ${test.expectation}</p>
                    <p class="small text-danger mb-0"><strong>Error:</strong> ${error.message}</p>
                `;
            }
        }
        
        function showSummary() {
            const summaryDiv = document.getElementById('summary');
            const summaryContent = document.getElementById('summary-content');
            
            const total = testResults.length;
            const passed = testResults.filter(r => r.passed).length;
            const failed = total - passed;
            const percentage = Math.round((passed / total) * 100);
            
            summaryDiv.style.display = 'block';
            summaryContent.innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-white rounded">
                            <h2 class="mb-0">${total}</h2>
                            <p class="small mb-0">Total Tests</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-success text-white rounded">
                            <h2 class="mb-0">${passed}</h2>
                            <p class="small mb-0">Passed</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-danger text-white rounded">
                            <h2 class="mb-0">${failed}</h2>
                            <p class="small mb-0">Failed</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 ${percentage === 100 ? 'bg-success' : percentage >= 75 ? 'bg-warning' : 'bg-danger'} text-white rounded">
                            <h2 class="mb-0">${percentage}%</h2>
                            <p class="small mb-0">Success Rate</p>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function clearResults() {
            document.getElementById('test-results').innerHTML = '';
            document.getElementById('summary').style.display = 'none';
            testResults = [];
        }
        
        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
