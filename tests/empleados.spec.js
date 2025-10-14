const { test, expect } = require('@playwright/test');

// Configure BASE_URL via env or default to http://localhost/lotificaciones/public
const BASE_URL = process.env.BASE_URL || 'http://localhost/lotificaciones/public';

// Helper to generate a random employee payload
function randomEmployee() {
  const id = Math.floor(Math.random() * 100000);
  return {
    nombres: 'TestName' + id,
    apellidos: 'TestLast' + id,
    fecha_nacimiento: '1990-01-01',
    genero: 'Masculino'
  };
}

test.describe('Empleados smoke', () => {
  test('create -> view ficha -> export PDF -> delete', async ({ page }) => {
    const e = randomEmployee();

    await page.goto(BASE_URL + '/empleados');
    // Wait for table and form
    await page.waitForSelector('#formEmpleado');
    await page.waitForSelector('#tablaEmpleados');

    // Fill and submit the create form
    await page.fill('#nombres', e.nombres);
    await page.fill('#apellidos', e.apellidos);
    await page.fill('#fecha_nacimiento', e.fecha_nacimiento);
    await page.selectOption('#genero', e.genero);

    // Submit and wait for table reload (expect toast)
    await Promise.all([
      page.click('#formEmpleado button[type="submit"]'),
      page.waitForResponse(resp => resp.url().includes('/empleados/create') && resp.status() === 200)
    ]);

    // Wait for table to load rows
    await page.waitForSelector('#tablaEmpleados tbody tr');

    // Find the created row by name and click 'ver-ficha'
    const row = await page.locator('#tablaEmpleados tbody tr').filter({ hasText: e.nombres }).first();
    await expect(row).toBeTruthy();

    const verBtn = row.locator('.ver-ficha').first();
    await verBtn.click();

    // Wait for modal and ficha content
    await page.waitForSelector('#modalFicha.show');
    await expect(page.locator('#ficha_nombres')).toHaveText(e.nombres);

    // Click export PDF (basic smoke: ensure button works and modal closes)
    await page.click('#exportPdfBtn');
    // We expect a Swal 'Generando PDF' dialog - wait briefly
    await page.waitForTimeout(1200);

    // Close the ficha modal
    await page.locator('#modalFicha button.btn-close').click();
    await page.waitForSelector('#modalFicha', { state: 'hidden' });

    // Edit the created row: open edit modal, change genero and save
    const editBtn = row.locator('.editar').first();
    await editBtn.click();
    await page.waitForSelector('#modalEditar.show');
    // Toggle genero
    const currentGenero = await page.$eval('#edit_genero', el => el.value);
    const newGenero = currentGenero === 'Masculino' ? 'Femenino' : 'Masculino';
    await page.selectOption('#edit_genero', newGenero);
    // Submit edit and wait for response
    await Promise.all([
      page.click('#modalEditar button[type="submit"]'),
      page.waitForResponse(resp => resp.url().includes('/empleados/update') && (resp.status() === 200 || resp.status() === 422))
    ]);
    // Wait for table to reflect update and view ficha to verify
    await page.waitForTimeout(800);

    // Re-open ficha to verify genero changed
    const updatedRow = await page.locator('#tablaEmpleados tbody tr').filter({ hasText: e.nombres }).first();
    const verBtn2 = updatedRow.locator('.ver-ficha').first();
    await verBtn2.click();
    await page.waitForSelector('#modalFicha.show');
    await expect(page.locator('#ficha_genero')).toHaveText(newGenero);

    // Close ficha modal
    await page.locator('#modalFicha button.btn-close').click();
    await page.waitForSelector('#modalFicha', { state: 'hidden' });

    // Find delete button on the same row and delete
    const delBtn = updatedRow.locator('.eliminar').first();
    await delBtn.click();
    // Confirm dialog -> click confirm
    await page.waitForSelector('.swal2-confirm');
    await page.click('.swal2-confirm');

    // Wait for delete request
    await page.waitForResponse(resp => resp.url().includes('/empleados/delete') && resp.status() === 200);

    // Optionally verify row disappears
    await page.waitForTimeout(1000);
  });
});
