import { chromium } from 'playwright';

const base = 'http://127.0.0.1:8000/';
const browser = await chromium.launch({
  headless: true,
  executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
});
const page = await browser.newPage({
  viewport: { width: 1440, height: 900 },
  deviceScaleFactor: 1,
});

const routes = [
  ['Trang chủ', 'index.php', ['CIT Club', 'Khoảnh khắc của CIT', '.gallery-grid'], '/tmp/cit-ui-index.png'],
  ['Giới thiệu', 'about.php', ['Cộng đồng sinh viên yêu công nghệ', 'Dấu Mốc Lịch Sử CIT', 'Cấu trúc Câu lạc bộ'], '/tmp/cit-ui-about.png'],
  ['Hoạt động', 'activities.php', ['Chuyên môn, văn hóa và câu chuyện thành viên', 'AETERNUM', '.activity-card'], '/tmp/cit-ui-activities.png'],
  ['Tuyển thành viên', 'recruitment.php', ['Lợi ích khi trở thành thành viên', 'Để lại thông tin ứng tuyển', 'form'], '/tmp/cit-ui-recruitment.png'],
  ['Liên hệ', 'contact.php', ['Kết nối với Câu lạc bộ Công nghệ', 'Fanpage CLB Công nghệ CIT', 'Gửi đơn ứng tuyển'], '/tmp/cit-ui-contact.png'],
];

const results = [];
for (const [name, path, checks, screenshotPath] of routes) {
  const response = await page.goto(base + path, { waitUntil: 'networkidle' });
  const title = await page.title();
  const bodyText = await page.locator('body').innerText();
  const failures = [];

  if (!response?.ok()) {
    failures.push(`HTTP ${response?.status() ?? 'none'}`);
  }
  if (!(await page.locator('main').isVisible().catch(() => false))) {
    failures.push('main hidden/missing');
  }

  for (const check of checks) {
    if (check.startsWith('.')) {
      if ((await page.locator(check).count()) === 0) {
        failures.push(`missing selector ${check}`);
      }
    } else if (!bodyText.includes(check)) {
      failures.push(`missing text ${check}`);
    }
  }

  if (screenshotPath) {
    await page.screenshot({ path: screenshotPath, fullPage: false });
  }

  results.push({ name, status: failures.length ? 'FAIL' : 'OK', title, failures, screenshot: screenshotPath });
}

await page.goto(base + 'index.php#gallery', { waitUntil: 'networkidle' });
await page.locator('#gallery').scrollIntoViewIfNeeded();
const beforeY = await page.evaluate(() => window.scrollY);
await page.locator('.album-tabs a', { hasText: 'Teambuilding' }).click();
await page.waitForTimeout(700);
const afterY = await page.evaluate(() => window.scrollY);
const activeAlbum = await page.locator('.album-tabs .nav-link.active').innerText();
const galleryItems = await page.locator('#galleryGrid .gallery-item').count();
results.push({
  name: 'Tab album không nhảy scroll',
  status: Math.abs(afterY - beforeY) <= 2 && activeAlbum.includes('Teambuilding') && galleryItems > 0 ? 'OK' : 'FAIL',
  title: activeAlbum,
  details: { beforeY, afterY, galleryItems },
});

await browser.close();
console.log(JSON.stringify(results, null, 2));
