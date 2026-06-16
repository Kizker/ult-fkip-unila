import fs from 'node:fs/promises';
import path from 'node:path';
import { execSync } from 'node:child_process';
import { chromium } from 'playwright';

const baseURL = process.env.AUDIT_BASE_URL || 'http://127.0.0.1:8099';
const loginEmail = process.env.AUDIT_LOGIN_EMAIL || 'qa-superadmin-audit@example.test';
const loginPassword = process.env.AUDIT_LOGIN_PASSWORD || 'Audit12345!';
const outRoot = process.env.AUDIT_OUT_DIR || path.resolve('storage/app/visual-audit');

const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
const outDir = path.join(outRoot, `admin-${timestamp}`);
await fs.mkdir(outDir, { recursive: true });
await fs.mkdir(path.join(outDir, 'screenshots'), { recursive: true });

const fixturesRaw = execSync('php scripts/export-admin-audit-fixtures.php', { stdio: ['ignore', 'pipe', 'pipe'] }).toString();
const fixtures = JSON.parse(fixturesRaw);

const routeRows = JSON.parse(execSync('php artisan route:list --path=admin --json', { stdio: ['ignore', 'pipe', 'pipe'] }).toString());

const replacementByParam = {
  announcement: fixtures.sample_ids.announcement,
  blog: fixtures.sample_ids.blog,
  category: fixtures.sample_ids.category,
  doc_format: fixtures.sample_ids.doc_format,
  letter_format: fixtures.sample_ids.letter_format,
  role: fixtures.sample_ids.role,
  user: fixtures.sample_ids.user,
  jurusan: fixtures.sample_ids.jurusan,
  prodi: fixtures.sample_ids.prodi,
  layanan: fixtures.sample_ids.layanan,
  request: fixtures.sample_ids.request,
};

const excludeNamePatterns = [
  /\.index$/,
  /\.preview$/,
  /\.autosave$/,
  /\.sequence$/,
];

function shouldExcludeRoute(route) {
  if (!route.name) return true;
  if (route.name === 'admin.dashboard') return false;
  if (excludeNamePatterns.some((p) => p.test(route.name))) return true;
  return false;
}

function resolveUri(uri) {
  const missing = [];
  const resolved = uri.replace(/\{([^}]+)\}/g, (_, rawKey) => {
    const key = String(rawKey || '').split(':')[0];
    const value = replacementByParam[key];
    if (!value) {
      missing.push(key);
      return `{${key}}`;
    }
    return String(value);
  });

  return { resolved, missing };
}

const targetRoutes = [];
const skippedRoutes = [];

for (const route of routeRows) {
  if (!String(route.method || '').includes('GET')) continue;
  if (!String(route.uri || '').startsWith('admin/')) continue;
  if (shouldExcludeRoute(route)) continue;

  const { resolved, missing } = resolveUri(route.uri);
  if (missing.length > 0) {
    skippedRoutes.push({ route: route.name, uri: route.uri, reason: `missing fixture: ${missing.join(', ')}` });
    continue;
  }

  targetRoutes.push({
    name: route.name,
    uri: route.uri,
    resolvedUri: resolved,
    url: `${baseURL}/${resolved}`,
  });
}

const viewports = [
  { label: 'desktop-1366', width: 1366, height: 900 },
  { label: 'mobile-390', width: 390, height: 844 },
  { label: 'mobile-360', width: 360, height: 740 },
  { label: 'mobile-320', width: 320, height: 568 },
];

function normalizeName(input) {
  return String(input || '').replace(/[^a-zA-Z0-9_-]+/g, '_');
}

async function login(page) {
  await page.goto(`${baseURL}/login`, { waitUntil: 'domcontentloaded', timeout: 60000 });

  const emailInput = page.locator('input[name="email"]');
  const passInput = page.locator('input[name="password"]');
  await emailInput.fill(loginEmail);
  await passInput.fill(loginPassword);

  const submitBtn = page.locator('button[type="submit"], input[type="submit"]').first();
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 60000 }),
    submitBtn.click(),
  ]);

  if (page.url().includes('/login')) {
    throw new Error('Login gagal: masih di halaman login.');
  }
}

const browser = await chromium.launch({ headless: true });
const results = [];

for (const vp of viewports) {
  const context = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
  const page = await context.newPage();

  await login(page);

  for (const route of targetRoutes) {
    const row = {
      viewport: vp.label,
      route: route.name,
      uri: route.resolvedUri,
      url: route.url,
      status: 'ok',
      httpStatus: null,
      overflowX: 0,
      overflowY: 0,
      outOfViewportCount: 0,
      offenders: [],
      screenshot: null,
      note: '',
    };

    try {
      const response = await page.goto(route.url, { waitUntil: 'domcontentloaded', timeout: 60000 });
      row.httpStatus = response?.status() ?? null;

      await page.waitForLoadState('networkidle', { timeout: 12000 }).catch(() => {});
      await page.waitForTimeout(350);

      const metrics = await page.evaluate(() => {
        const doc = document.documentElement;
        const body = document.body;
        const overflowX = Math.max(
          0,
          (doc?.scrollWidth || 0) - (doc?.clientWidth || 0),
          (body?.scrollWidth || 0) - (body?.clientWidth || 0),
        );
        const overflowY = Math.max(
          0,
          (doc?.scrollHeight || 0) - (doc?.clientHeight || 0),
          (body?.scrollHeight || 0) - (body?.clientHeight || 0),
        );

        // Only flag strict offenders that likely cause true horizontal overflow.
        const offenders = [];
        const ww = window.innerWidth;

        const elements = Array.from(document.querySelectorAll('*'));
        for (const el of elements) {
          if (offenders.length >= 20) break;
          if (!el || el.closest('[x-cloak], [aria-hidden="true"], .hidden')) continue;
          if (el.closest('.app-sidebar, .app-sidebar-mobile, [data-mobile-menu], .user-scroll-select__panel')) continue;

          const cs = getComputedStyle(el);
          if (cs.display === 'none' || cs.visibility === 'hidden' || Number(cs.opacity) === 0) continue;
          if (cs.position === 'fixed' || cs.position === 'absolute') continue;

          const r = el.getBoundingClientRect();
          if (r.width < 6 || r.height < 6) continue;
          if ((r.right > ww + 2 || r.left < -2) && ((el.scrollWidth - el.clientWidth) > 2 || overflowX > 1)) {
            offenders.push({
              selector: (() => {
                const tag = (el.tagName || 'node').toLowerCase();
                if (el.id) return `${tag}#${el.id}`;
                const cls = (el.className || '').toString().trim().split(/\s+/).filter(Boolean).slice(0, 2);
                return cls.length ? `${tag}.${cls.join('.')}` : tag;
              })(),
              left: Math.round(r.left),
              right: Math.round(r.right),
              width: Math.round(r.width),
            });
          }
        }

        return {
          overflowX,
          overflowY,
          outOfViewportCount: offenders.length,
          offenders,
        };
      });

      row.overflowX = Math.round(metrics.overflowX);
      row.overflowY = Math.round(metrics.overflowY);
      row.outOfViewportCount = metrics.outOfViewportCount;
      row.offenders = metrics.offenders;

      if ((row.httpStatus && row.httpStatus >= 400) || page.url().includes('/login')) {
        row.status = 'error';
        row.note = page.url().includes('/login') ? 'redirected to login' : `HTTP ${row.httpStatus}`;
      } else if (row.overflowX > 1) {
        row.status = 'issue';
        row.note = `horizontal overflow ${row.overflowX}px`;
      }
    } catch (error) {
      row.status = 'error';
      row.note = String(error?.message || error);
    }

    const shotName = `${normalizeName(route.name)}__${vp.label}.png`;
    const shotPath = path.join(outDir, 'screenshots', shotName);
    await page.screenshot({ path: shotPath, fullPage: true }).catch(() => {});
    row.screenshot = path.relative(outDir, shotPath).replace(/\\/g, '/');

    results.push(row);
  }

  await context.close();
}

await browser.close();

const summary = {
  generatedAt: new Date().toISOString(),
  baseURL,
  fixtures,
  totalRoutes: targetRoutes.length,
  totalRuns: results.length,
  issues: results.filter((r) => r.status === 'issue').length,
  errors: results.filter((r) => r.status === 'error').length,
  skippedRoutes,
  viewports,
};

await fs.writeFile(path.join(outDir, 'results.json'), JSON.stringify({ summary, routes: targetRoutes, results }, null, 2));

const issueRows = results.filter((r) => r.status !== 'ok');
const lines = [];
lines.push('# Admin Visual Audit');
lines.push('');
lines.push(`- Base URL: ${baseURL}`);
lines.push(`- Total route diuji: ${targetRoutes.length}`);
lines.push(`- Total run (route x viewport): ${results.length}`);
lines.push(`- Issue: ${summary.issues}`);
lines.push(`- Error: ${summary.errors}`);
lines.push('');

if (issueRows.length === 0) {
  lines.push('Tidak ada issue visual terdeteksi otomatis.');
} else {
  lines.push('## Temuan');
  lines.push('');
  lines.push('| Status | Viewport | Route | URI | OverflowX | OutOfViewport | Catatan | Screenshot |');
  lines.push('|---|---|---|---|---:|---:|---|---|');
  for (const r of issueRows) {
    lines.push(`| ${r.status} | ${r.viewport} | ${r.route} | ${r.uri} | ${r.overflowX} | ${r.outOfViewportCount} | ${String(r.note || '').replace(/\|/g, '\\|')} | ${r.screenshot} |`);
  }
}

if (skippedRoutes.length > 0) {
  lines.push('');
  lines.push('## Skipped Routes');
  lines.push('');
  for (const s of skippedRoutes) {
    lines.push(`- ${s.route} (${s.uri}) - ${s.reason}`);
  }
}

await fs.writeFile(path.join(outDir, 'report.md'), lines.join('\n'));
console.log(outDir);
