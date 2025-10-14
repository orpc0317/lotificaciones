const { chromium } = require('playwright');

(async () => {
  const BASE = process.env.BASE_URL || 'http://localhost/lotificaciones/public';
  const browser = await chromium.launch();
  const page = await browser.newPage();
  page.on('console', msg => { console.log('PAGE LOG>', msg.text()); });
  try {
    await page.goto(BASE + '/empleados', { waitUntil: 'networkidle' });
    // wait for table
    await page.waitForSelector('#tablaEmpleados', { timeout: 5000 }).catch(() => {});
    // if no edit buttons, just open new modal if exists
    const editBtn = await page.$('.editar');
    if (editBtn) {
      await editBtn.click();
    } else {
      console.log('No edit button found; trying to open edit modal directly');
      await page.evaluate(() => {
        const el = document.getElementById('modalEditar');
        if (el) new bootstrap.Modal(el).show();
      });
    }
  // wait for edit form to be present (modal may be shown or hidden briefly)
  await page.waitForSelector('#formEditar', { timeout: 5000 }).catch(() => {});
  // short delay to allow UI handlers to run
  await page.waitForTimeout(300);
  // attempt to read the badge if present
  const badge = await page.$('.badge-tab[data-tab="edit-generals"]');
  const text = badge ? (await badge.innerText()).trim() : '<no-badge>';
  console.log('Badge text for edit-generals:', text);
    // Also list any elements in the edit-generals pane that are considered invalid by our script
    const invalids = await page.evaluate(() => {
      const pane = document.getElementById('edit-generals');
      if (!pane) return [];
      const required = Array.from(pane.querySelectorAll('input[required], textarea[required], select[required]'));
      return required.map(el => ({selector: el.id || el.name || el.tagName, value: (el.value||'').toString().slice(0,50)}));
    });
    console.log('Required elements in edit-generals:', invalids);
    // Also print the selected puesto/departamento text to verify options were cloned
    const selects = await page.evaluate(() => {
      const p = document.getElementById('edit_puesto_id');
      const d = document.getElementById('edit_departamento_id');
      return {
        puesto: p ? { value: p.value, text: (p.options[p.selectedIndex] && p.options[p.selectedIndex].text) || null, options: p ? p.options.length : 0 } : null,
        departamento: d ? { value: d.value, text: (d.options[d.selectedIndex] && d.options[d.selectedIndex].text) || null, options: d ? d.options.length : 0 } : null
      };
    });
    console.log('edit selects:', selects);
  } catch (e) {
    console.error('Error in check script', e);
    process.exitCode = 2;
  } finally {
    await browser.close();
  }
})();
