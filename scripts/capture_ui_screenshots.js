const { chromium } = require('playwright');
const fs = require('fs');
(async () => {
  const BASE = process.env.BASE_URL || 'http://localhost:8080/lotificaciones/public';
  const outDir = 'test-results/screenshots';
  if (!fs.existsSync(outDir)) fs.mkdirSync(outDir, { recursive: true });
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
  try {
    // Light theme
    await page.goto(BASE + '/empleados', { waitUntil: 'networkidle' });
    await page.waitForSelector('.card');
    await page.screenshot({ path: outDir + '/empleados_light_full.png', fullPage: true });

    // Open modal to screenshot
    await page.waitForSelector('.editar');
    const editBtn = await page.$('.editar');
    if (editBtn) { await editBtn.click(); await page.waitForSelector('#modalEditar.show'); await page.screenshot({ path: outDir + '/modal_edit_light.png' }); await page.click('#modalEditar button.btn-close'); }

  // Palette-only: capture an additional full screenshot after picking a palette (if present)
  const firstSwatch = await page.$('.palette-swatch');
  if (firstSwatch) { await firstSwatch.click(); await page.waitForTimeout(200); await page.screenshot({ path: outDir + '/empleados_palette_full.png', fullPage: true }); }

    // Also capture the left form card and table card individually
    const leftCard = await page.$('.col-md-4 .card'); if (leftCard) await leftCard.screenshot({ path: outDir + '/leftcard.png' });
    const tableCard = await page.$('.col-md-8 .card'); if (tableCard) await tableCard.screenshot({ path: outDir + '/tablecard.png' });

    console.log('Screenshots saved to', outDir);
  } catch (e) {
    console.error('Error capturing screenshots:', e);
    process.exitCode = 2;
  } finally {
    await browser.close();
  }
})();