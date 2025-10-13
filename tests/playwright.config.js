const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './',
  timeout: 30 * 1000,
  expect: { timeout: 5000 },
  reporter: process.env.CI ? 'dot' : 'list',
  use: {
    headless: true,
    viewport: { width: 1280, height: 800 },
    actionTimeout: 5000,
    ignoreHTTPSErrors: true
  },
  projects: [
    { name: 'chromium', use: { browserName: 'chromium' } }
  ]
});
