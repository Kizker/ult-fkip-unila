import fs from 'node:fs/promises';
import path from 'node:path';
import { execSync, spawn } from 'node:child_process';
import { chromium } from 'playwright';

const baseURL = process.env.MIRROR_BASE_URL || 'http://127.0.0.1:8099';
const loginEmail = process.env.MIRROR_LOGIN_EMAIL || 'qa-superadmin-audit@example.test';
const loginPassword = process.env.MIRROR_LOGIN_PASSWORD || 'Audit12345!';
const outRoot = process.env.MIRROR_OUT_DIR || path.resolve('docs/figma/admin');

const excludeNamePatterns = [
  /\.preview$/,
  /\.export$/,
  /\.autosave$/,
  /\.sequence$/,
];

function runJson(command) {
  return JSON.parse(execSync(command, { stdio: ['ignore', 'pipe', 'pipe'] }).toString());
}

function slug(input) {
  return String(input || '')
    .trim()
    .toLowerCase()
    .replace(/_/g, '-')
    .replace(/[^a-z0-9-]+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '');
}

function routeToFileInfo(routeName) {
  const parts = String(routeName || '')
    .split('.')
    .filter(Boolean);
  const withoutPrefix = parts[0] === 'admin' ? parts.slice(1) : parts.slice();
  const action = withoutPrefix[withoutPrefix.length - 1] || 'index';
  const scope = withoutPrefix.slice(0, -1);

  if (scope.length === 0) {
    return { relDir: '', fileName: `${slug(action)}-mirror.html` };
  }

  const dirParts = scope.slice(0, -1).map(slug).filter(Boolean);
  const base = slug(scope[scope.length - 1]);
  const suffix = slug(action);
  return {
    relDir: dirParts.join('/'),
    fileName: `${base}-${suffix}-mirror.html`,
  };
}

function shouldUseRoute(route) {
  if (!route?.name) return false;
  if (!String(route.method || '').includes('GET')) return false;
  if (!String(route.uri || '').startsWith('admin/')) return false;
  if (excludeNamePatterns.some((p) => p.test(route.name))) return false;
  return true;
}

function resolveUri(uri, replacements) {
  const missing = [];
  const resolved = String(uri || '').replace(/\{([^}]+)\}/g, (_, rawKey) => {
    const key = String(rawKey || '').split(':')[0];
    const value = replacements[key];
    if (value === undefined || value === null || value === '') {
      missing.push(key);
      return `{${key}}`;
    }
    return String(value);
  });
  return { resolved, missing };
}

function normalizeHtml(html, routeName) {
  let out = String(html || '');
  out = out.replace(/http:\/\/127\.0\.0\.1:8099/g, 'http://127.0.0.1:8099');
  out = out.replace(/<title>[\s\S]*?<\/title>/i, `<title>${routeName} Mirror</title>`);
  return out;
}

async function login(page) {
  await page.goto(`${baseURL}/login`, { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.locator('input[name="email"]').fill(loginEmail);
  await page.locator('input[name="password"]').fill(loginPassword);
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 60000 }),
    page.locator('button[type="submit"], input[type="submit"]').first().click(),
  ]);

  if (page.url().includes('/login')) {
    throw new Error('Login gagal. Periksa MIRROR_LOGIN_EMAIL / MIRROR_LOGIN_PASSWORD.');
  }
}

async function isServerReachable(url, timeoutMs = 3000) {
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), timeoutMs);
  try {
    const res = await fetch(url, { method: 'GET', redirect: 'manual', signal: controller.signal });
    return !!res;
  } catch {
    return false;
  } finally {
    clearTimeout(timer);
  }
}

async function ensureLocalServerUp(base) {
  const loginUrl = `${base}/login`;
  if (await isServerReachable(loginUrl)) {
    return { process: null, started: false };
  }

  const parsed = new URL(base);
  const host = parsed.hostname;
  const port = parsed.port || '8000';
  const outLog = path.resolve('storage/logs/mirror-serve.out.log');
  const errLog = path.resolve('storage/logs/mirror-serve.err.log');
  await fs.mkdir(path.dirname(outLog), { recursive: true });

  const serveProc = spawn('php', ['artisan', 'serve', `--host=${host}`, `--port=${port}`], {
    stdio: ['ignore', 'pipe', 'pipe'],
    windowsHide: true,
  });

  const outStream = (await import('node:fs')).createWriteStream(outLog, { flags: 'a' });
  const errStream = (await import('node:fs')).createWriteStream(errLog, { flags: 'a' });
  serveProc.stdout.pipe(outStream);
  serveProc.stderr.pipe(errStream);

  for (let i = 0; i < 30; i += 1) {
    await new Promise((resolve) => setTimeout(resolve, 1000));
    if (await isServerReachable(loginUrl, 1500)) {
      return { process: serveProc, started: true };
    }
  }

  try {
    serveProc.kill();
  } catch {}
  throw new Error(`Server ${base} tidak bisa dijangkau dan gagal dinyalakan otomatis.`);
}

const fixtures = runJson('php scripts/export-admin-mirror-fixtures.php');
const routes = runJson('php artisan route:list --path=admin --json');

const replacements = {
  announcement: fixtures?.sample_ids?.announcement,
  blog: fixtures?.sample_ids?.blog,
  category: fixtures?.sample_ids?.category,
  doc_format: fixtures?.sample_ids?.doc_format,
  jurusan: fixtures?.sample_ids?.jurusan,
  feedback: fixtures?.sample_ids?.feedback,
  layanan: fixtures?.sample_ids?.layanan,
  user_guide: fixtures?.sample_ids?.user_guide,
  request: fixtures?.sample_ids?.request,
  prodi: fixtures?.sample_ids?.prodi,
  role: fixtures?.sample_ids?.role,
  letter_format: fixtures?.sample_ids?.letter_format,
  user: fixtures?.sample_ids?.user,
};

const targets = [];
const skipped = [];

for (const route of routes) {
  if (!shouldUseRoute(route)) continue;
  const { resolved, missing } = resolveUri(route.uri, replacements);
  if (missing.length > 0) {
    skipped.push({ route: route.name, uri: route.uri, reason: `missing fixture: ${missing.join(', ')}` });
    continue;
  }
  targets.push({
    name: route.name,
    uri: route.uri,
    resolvedUri: resolved,
    url: `${baseURL}/${resolved}`,
    ...routeToFileInfo(route.name),
  });
}

await fs.mkdir(outRoot, { recursive: true });
const serverState = await ensureLocalServerUp(baseURL);
const browser = await chromium.launch({ headless: true });
const context = await browser.newContext({ viewport: { width: 1366, height: 900 } });
const page = await context.newPage();

const reportPath = path.join(outRoot, 'admin-mirror-report.json');
const report = {
  generated_at: new Date().toISOString(),
  base_url: baseURL,
  out_root: outRoot,
  total_targets: targets.length,
  generated: [],
  skipped_existing: [],
  skipped,
  errors: [],
};

try {
  await login(page);

  for (const target of targets) {
    const outDir = path.join(outRoot, target.relDir);
    const outPath = path.join(outDir, target.fileName);
    await fs.mkdir(outDir, { recursive: true });
    const relOut = path.relative(process.cwd(), outPath).replace(/\\/g, '/');

    try {
      await fs.access(outPath);
      report.skipped_existing.push({
        route: target.name,
        file: relOut,
      });
      continue;
    } catch {}

    try {
      const resp = await page.goto(target.url, { waitUntil: 'domcontentloaded', timeout: 25000 });
      await page.waitForTimeout(450);
      const status = resp?.status() ?? null;
      const contentType = String(resp?.headers()?.['content-type'] || '').toLowerCase();
      const finalUrl = page.url();

      if (status && status >= 400) {
        report.errors.push({ route: target.name, url: target.url, reason: `HTTP ${status}` });
        continue;
      }
      if (!contentType.includes('text/html')) {
        report.skipped.push({ route: target.name, uri: target.uri, reason: `non-html content-type: ${contentType || 'unknown'}` });
        continue;
      }

      const html = await page.content();
      const normalized = normalizeHtml(html, target.name);
      await fs.writeFile(outPath, normalized, 'utf8');
      report.generated.push({
        route: target.name,
        uri: target.resolvedUri,
        final_url: finalUrl,
        file: relOut,
      });
    } catch (error) {
      report.errors.push({ route: target.name, url: target.url, reason: String(error?.message || error) });
    } finally {
      await fs.writeFile(reportPath, JSON.stringify(report, null, 2), 'utf8');
    }
  }
} finally {
  await context.close();
  await browser.close();
  if (serverState.process) {
    try {
      serverState.process.kill();
    } catch {}
  }
}

await fs.writeFile(reportPath, JSON.stringify(report, null, 2), 'utf8');

console.log(
  JSON.stringify(
    {
      total_targets: report.total_targets,
      generated: report.generated.length,
      skipped: report.skipped.length,
      errors: report.errors.length,
      report: path.relative(process.cwd(), reportPath).replace(/\\/g, '/'),
    },
    null,
    2,
  ),
);
