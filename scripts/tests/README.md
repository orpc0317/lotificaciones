Quick tests for the lotificaciones app

How to run

1. From the project root run:

```bash
php scripts/tests/api_tests.php
```

Notes

- These tests are lightweight smoke tests. They do not require external testing frameworks.
- They assume the app is accessible at `http://localhost/lotificaciones/public/`. If your local base differs, edit `base_url()` in `api_tests.php`.

What they do

- Test 1: Request `empleados/ajax` and verify it returns valid JSON with a `data` array.
- Test 2: If the `data` array has at least one record, request `empleados/get?id={id}` and verify the response contains the same `id`.
- Test 3: Database smoke test that performs a temporary `UPDATE` inside a transaction and rolls back to confirm write capability on the `empleados` table.

Limitations

- The script tests read-only endpoints plus a DB transaction update; it does not perform multipart/form-data uploads. To test file uploads programmatically consider using `curl` with `-F` from shell or adding a PHP script that builds a multipart request.
- If your app runs under a different host/port/subpath, update `base_url()` accordingly.

Reporting

- Exit code 0 means all tests passed (or skipped where not applicable).
- Non-zero exit codes indicate a failure and will print diagnostic output to help debug.
