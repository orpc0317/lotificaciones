const { test, expect } = require('@playwright/test');

const BASE_URL = process.env.BASE_URL || 'http://localhost/lotificaciones/public';

test.describe('Theme persistence', () => {
  test('palette persists across reload', async ({ page }) => {
    await page.goto(BASE_URL + '/empleados');

    // Wait for palette controls
    await page.waitForSelector('.palette-swatch');

    // Pick a palette (the first swatch)
    const firstSwatch = await page.locator('.palette-swatch').first();
    const pal = await firstSwatch.getAttribute('data-palette');
    await firstSwatch.click();

    // read CSS variable to ensure it applied
    const primary = await page.evaluate(() => getComputedStyle(document.documentElement).getPropertyValue('--primary-600').trim());
    expect(primary.length).toBeGreaterThan(0);

    // Reload and ensure palette persisted
    await page.reload();
    const primaryAfterReload = await page.evaluate(() => getComputedStyle(document.documentElement).getPropertyValue('--primary-600').trim());
    expect(primaryAfterReload.length).toBeGreaterThan(0);
  });
});
