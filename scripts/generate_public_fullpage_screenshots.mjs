import { execFile } from 'node:child_process';
import fs from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';
import { promisify } from 'node:util';
import { chromium } from 'playwright';

const execFileAsync = promisify(execFile);

const rootDir = path.resolve('c:/laragon/www/ult-fkip-unila');
const outputDir = path.join(rootDir, 'docs', 'video tutorial', 'assets', 'public-full-pages');
const tempDir = path.join(rootDir, 'tmp', 'public-full-pages-stitch');
const baseUrl = process.env.BASE_URL || 'http://127.0.0.1:8000';
const onlyFile = (process.env.SCREENSHOT_ONLY || '').trim();
const viewport = { width: 1600, height: 900 };
const deviceScaleFactor = 1.5;

async function ensureDir(dir) {
  await fs.mkdir(dir, { recursive: true });
}

async function emptyDir(dir) {
  await fs.rm(dir, { recursive: true, force: true });
  await fs.mkdir(dir, { recursive: true });
}

async function getPublicSlugs() {
  const phpCode = [
    "require 'vendor/autoload.php';",
    "$app = require 'bootstrap/app.php';",
    "$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);",
    '$kernel->bootstrap();',
    "$data = ['services' => App\\Models\\Service::query()->where('is_active', 1)->pluck('slug')->values()->all(), 'blogs' => App\\Models\\CmsBlog::query()->where('is_published', 1)->pluck('slug')->values()->all(), 'announcements' => App\\Models\\CmsAnnouncement::query()->where('is_published', 1)->pluck('slug')->values()->all(), 'guides' => App\\Models\\UserGuide::query()->where('is_published', 1)->where('content_type', 'video')->pluck('slug')->values()->all()];",
    'echo json_encode($data, JSON_UNESCAPED_UNICODE);',
  ].join(' ');

  const { stdout } = await execFileAsync('php', ['-r', phpCode], {
    cwd: rootDir,
  });

  return JSON.parse(String(stdout).trim());
}

async function getServiceTemplateMap() {
  const phpCode = [
    "require 'vendor/autoload.php';",
    "$app = require 'bootstrap/app.php';",
    "$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);",
    '$kernel->bootstrap();',
    '$disk = config(\'ult.private_disk\');',
    '$services = App\\Models\\Service::query()->where(\'is_active\', 1)->with(\'templates\')->get();',
    '$result = [];',
    'foreach ($services as $service) {',
    '  $tpl = $service->templates->firstWhere(\'type\', App\\Enums\\ServiceTemplateType::MAIN_DOCX);',
    '  if (!$tpl || !$tpl->file_path) { continue; }',
    '  $result[$service->slug] = Storage::disk($disk)->path((string) $tpl->file_path);',
    '}',
    'echo json_encode($result, JSON_UNESCAPED_UNICODE);',
  ].join(' ');

  const { stdout } = await execFileAsync('php', ['-r', phpCode], {
    cwd: rootDir,
  });

  return JSON.parse(String(stdout).trim());
}

function buildPages(slugs) {
  const pages = [
    { file: '001-home.png', label: 'Beranda', route: '/', waitFor: '#ultHome' },
    { file: '002-layanan-index.png', label: 'Daftar layanan', route: '/layanan', waitFor: '#servicesIndexPage' },
    { file: '003-tentang-ult.png', label: 'Tentang ULT', route: '/tentang-ult', waitFor: 'main' },
    { file: '004-blog-index.png', label: 'Daftar blog', route: '/blog', waitFor: 'main' },
    { file: '005-pengumuman-index.png', label: 'Daftar pengumuman', route: '/pengumuman', waitFor: 'main' },
    { file: '006-panduan-index.png', label: 'Daftar panduan pengguna', route: '/panduan-pengguna', waitFor: 'main' },
    { file: '006a-kritik-saran.png', label: 'Kritik dan saran', route: '/kritik-saran', waitFor: '#feedbackCreatePage' },
    { file: '007-login.png', label: 'Login', route: '/login', waitFor: '.auth-card' },
    { file: '008-register.png', label: 'Register', route: '/register', waitFor: '.auth-card' },
    { file: '009-forgot-password.png', label: 'Lupa password', route: '/forgot-password', waitFor: '.auth-card, main' },
  ];

  slugs.services.forEach((slug, index) => {
    pages.push({
      file: `1${String(index + 1).padStart(2, '0')}-service-${slug}.png`,
      label: `Detail layanan ${slug}`,
      route: `/layanan/${slug}`,
      waitFor: '#serviceShowPage',
      kind: 'service',
      slug,
    });
  });

  slugs.blogs.forEach((slug, index) => {
    pages.push({
      file: `2${String(index + 1).padStart(2, '0')}-blog-${slug}.png`,
      label: `Detail blog ${slug}`,
      route: `/blog/${slug}`,
      waitFor: 'main',
    });
  });

  slugs.announcements.forEach((slug, index) => {
    pages.push({
      file: `3${String(index + 1).padStart(2, '0')}-announcement-${slug}.png`,
      label: `Detail pengumuman ${slug}`,
      route: `/pengumuman/${slug}`,
      waitFor: 'main',
    });
  });

  slugs.guides.forEach((slug, index) => {
    pages.push({
      file: `4${String(index + 1).padStart(2, '0')}-guide-${slug}.png`,
      label: `Detail panduan ${slug}`,
      route: `/panduan-pengguna/${slug}`,
      waitFor: 'main',
    });
  });

  return pages;
}

async function gotoAndWait(page, route, waitForSelector) {
  await page.goto(`${baseUrl}${route}`, {
    waitUntil: 'domcontentloaded',
    timeout: 60000,
  });
  await page.waitForLoadState('networkidle', { timeout: 30000 }).catch(() => {});
  await page.locator(waitForSelector).first().waitFor({ state: 'visible', timeout: 30000 });
  await page.waitForTimeout(700);
}

async function exportDocxPreviewHtml(docxPath, outputDirForDocx) {
  await ensureDir(outputDirForDocx);
  const htmlPath = path.join(outputDirForDocx, 'preview.html');
  const vbsPath = path.join(outputDirForDocx, 'export_html.vbs');
  const ps1Path = path.join(outputDirForDocx, 'export_html.ps1');
  const vbs = [
    'On Error Resume Next',
    'Dim word, doc, inFile, outFile',
    'inFile = WScript.Arguments(0)',
    'outFile = WScript.Arguments(1)',
    'Set word = CreateObject("Word.Application")',
    'If Err.Number <> 0 Then',
    '  WScript.Quit 2',
    'End If',
    'word.Visible = False',
    'word.DisplayAlerts = 0',
    'Set doc = word.Documents.Open(inFile, False, True)',
    'If Err.Number <> 0 Then',
    '  word.Quit',
    '  WScript.Quit 3',
    'End If',
    '\' wdFormatFilteredHTML = 10',
    'Call doc.SaveAs(outFile, 10)',
    'If Err.Number <> 0 Then',
    '  doc.Close False',
    '  word.Quit',
    '  WScript.Quit 4',
    'End If',
    'doc.Close False',
    'word.Quit',
    'WScript.Quit 0',
    '',
  ].join('\n');
  const ps1 = [
    `$vbs = '${vbsPath.replace(/'/g, "''")}'`,
    `$docx = '${docxPath.replace(/'/g, "''")}'`,
    `$html = '${htmlPath.replace(/'/g, "''")}'`,
    "& 'C:\\Windows\\System32\\cscript.exe' //NoLogo $vbs $docx $html",
    'if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }',
    'Write-Output $html',
    '',
  ].join('\n');

  await fs.writeFile(vbsPath, vbs, 'utf8');
  await fs.writeFile(ps1Path, ps1, 'utf8');
  await execFileAsync('powershell', ['-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', ps1Path], {
    cwd: rootDir,
    timeout: 300000,
  });

  return htmlPath;
}

async function renderServicePreviewImage(browser, docxPath, slug) {
  const previewKey = slug.replace(/[^a-z0-9]+/gi, '').slice(0, 20) || 'servicepreview';
  const previewStamp = `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
  const previewDir = path.join(tempDir, 'svc-prev', `${previewKey}-${previewStamp}`);
  const htmlPath = await exportDocxPreviewHtml(docxPath, previewDir);
  const previewPage = await browser.newPage({
    viewport: { width: 1400, height: 1800 },
    deviceScaleFactor,
  });

  try {
    await previewPage.goto(pathToFileURL(htmlPath).href, {
      waitUntil: 'load',
      timeout: 60000,
    });
    await previewPage.addStyleTag({
      content: [
        'body{margin:0;background:#f3f0ff;padding:28px;}',
        'img{max-width:100%;height:auto;}',
        '.WordSection1{margin:0 auto;background:#fff;box-shadow:0 18px 40px rgba(35,13,78,.12);}',
      ].join(''),
    });
    await previewPage.waitForTimeout(400);

    const section = previewPage.locator('.WordSection1').first();
    const target = await section.count() ? section : previewPage.locator('body');
    return await target.screenshot({ type: 'png' });
  } finally {
    await previewPage.close();
  }
}

async function injectServicePreview(page, previewBuffer) {
  const dataUrl = `data:image/png;base64,${previewBuffer.toString('base64')}`;
  await page.evaluate((src) => {
    const wrap = document.querySelector('.service-doc-preview__wrap');
    if (!(wrap instanceof HTMLElement)) return;

    wrap.innerHTML = '';
    wrap.style.padding = '18px';
    wrap.style.background = 'linear-gradient(180deg, rgba(120,76,255,.08), rgba(120,76,255,.03))';
    wrap.style.display = 'flex';
    wrap.style.alignItems = 'flex-start';
    wrap.style.justifyContent = 'center';

    const img = document.createElement('img');
    img.src = src;
    img.alt = 'Preview dokumen layanan';
    img.style.display = 'block';
    img.style.width = '100%';
    img.style.maxWidth = '920px';
    img.style.height = 'auto';
    img.style.borderRadius = '18px';
    img.style.boxShadow = '0 22px 54px rgba(25, 15, 60, 0.18)';
    img.style.background = '#fff';

    wrap.appendChild(img);
  }, dataUrl);

  await page.waitForFunction(() => {
    const img = document.querySelector('.service-doc-preview__wrap img');
    return img instanceof HTMLImageElement && img.complete && img.naturalWidth > 0;
  }, { timeout: 15000 });
}

async function waitForImagesAndFrames(page) {
  await page.waitForFunction(() => {
    const mediaNodes = Array.from(document.querySelectorAll('img, iframe'));
    return mediaNodes.every((node) => {
      if (node instanceof HTMLImageElement) {
        if (!node.currentSrc && !node.src) return true;
        return node.complete && node.naturalWidth > 0;
      }

      if (node instanceof HTMLIFrameElement) {
        if (!node.src) return true;
        return node.dataset.screenshotReady === '1' || node.dataset.loaded === '1';
      }

      return true;
    });
  }, { timeout: 15000 }).catch(() => {});
}

async function getPageMetrics(page) {
  return page.evaluate(() => {
    const body = document.body;
    const doc = document.documentElement;
    const height = Math.max(
      body?.scrollHeight ?? 0,
      body?.offsetHeight ?? 0,
      doc?.clientHeight ?? 0,
      doc?.scrollHeight ?? 0,
      doc?.offsetHeight ?? 0,
    );

    return {
      bodyScrollHeight: body?.scrollHeight ?? 0,
      docScrollHeight: doc?.scrollHeight ?? 0,
      totalHeight: height,
      viewportHeight: window.innerHeight || 900,
    };
  });
}

async function setScrollTop(page, top) {
  await page.evaluate((scrollTop) => {
    document.body.scrollTop = scrollTop;
    document.documentElement.scrollTop = scrollTop;
  }, top);
}

async function preparePageForStitchedCapture(page) {
  await page.evaluate(() => {
    document.documentElement.style.setProperty('scroll-behavior', 'auto');
    document.body.style.setProperty('scroll-behavior', 'auto');

    document.querySelectorAll('img[loading="lazy"], iframe[loading="lazy"]').forEach((node) => {
      node.setAttribute('loading', 'eager');

      if (node instanceof HTMLImageElement && node.dataset.src && !node.src) {
        node.src = node.dataset.src;
      }
    });

    document.querySelectorAll('iframe').forEach((frame) => {
      if (frame.dataset.captureBound === '1') return;
      frame.dataset.captureBound = '1';
      frame.addEventListener('load', () => {
        frame.dataset.screenshotReady = '1';
      }, { once: true });
    });
  });

  let stablePasses = 0;
  let lastHeight = 0;

  for (let attempt = 0; attempt < 18; attempt += 1) {
    const metrics = await page.evaluate(async () => {
      const getHeight = () => Math.max(
        document.body?.scrollHeight ?? 0,
        document.body?.offsetHeight ?? 0,
        document.documentElement?.clientHeight ?? 0,
        document.documentElement?.scrollHeight ?? 0,
        document.documentElement?.offsetHeight ?? 0,
      );

      const targetHeight = getHeight();
      const viewportHeight = window.innerHeight || 900;
      const step = Math.max(480, Math.floor(viewportHeight * 0.75));

      for (let top = 0; top < targetHeight; top += step) {
        document.body.scrollTop = top;
        document.documentElement.scrollTop = top;
        await new Promise((resolve) => window.setTimeout(resolve, 150));
      }

      document.body.scrollTop = targetHeight;
      document.documentElement.scrollTop = targetHeight;
      await new Promise((resolve) => window.setTimeout(resolve, 450));

      return {
        height: getHeight(),
        hasPendingInfiniteList: Array.from(document.querySelectorAll('[data-infinite-list]')).some((host) => {
          const nextPageUrl = String(host.getAttribute('data-next-page-url') || '').trim();
          const loadingButton = host.querySelector('[data-infinite-load-more]');
          return Boolean(nextPageUrl) || Boolean(loadingButton && loadingButton.disabled);
        }),
      };
    });

    await page.waitForLoadState('networkidle', { timeout: 5000 }).catch(() => {});
    await waitForImagesAndFrames(page);

    if (metrics.height === lastHeight && !metrics.hasPendingInfiniteList) {
      stablePasses += 1;
      if (stablePasses >= 2) break;
    } else {
      stablePasses = 0;
      lastHeight = metrics.height;
    }
  }

  await setScrollTop(page, 0);
  await page.waitForTimeout(300);
}

function buildCapturePositions(totalHeight, viewportHeight) {
  const maxScroll = Math.max(0, totalHeight - viewportHeight);
  const positions = [];

  for (let top = 0; top < maxScroll; top += viewportHeight) {
    positions.push(top);
  }

  if (positions.length === 0 || positions[positions.length - 1] !== maxScroll) {
    positions.push(maxScroll);
  }

  return positions;
}

async function writeStitchScript(scriptPath, outputPath, totalHeight, pieces) {
  const rows = pieces.map((piece) => {
    return `  @{ Path = '${piece.path.replace(/'/g, "''")}'; Top = ${piece.top} }`;
  }).join('\n');

  const script = [
    'Add-Type -AssemblyName System.Drawing',
    '$pieces = @(',
    rows,
    ')',
    `$out = '${outputPath.replace(/'/g, "''")}'`,
    `$scale = ${deviceScaleFactor}`,
    `$totalHeightPx = [int]([math]::Ceiling(${totalHeight} * $scale))`,
    '$bitmaps = @()',
    'try {',
    '  foreach ($piece in $pieces) {',
    '    $bitmaps += [System.Drawing.Bitmap]::FromFile($piece.Path)',
    '  }',
    '  $canvas = New-Object System.Drawing.Bitmap($bitmaps[0].Width, $totalHeightPx)',
    '  $g = [System.Drawing.Graphics]::FromImage($canvas)',
    '  $g.Clear([System.Drawing.Color]::White)',
    '  try {',
    '    for ($i = 0; $i -lt $pieces.Count; $i++) {',
    '      $y = [int]([math]::Round($pieces[$i].Top * $scale))',
    '      $g.DrawImage($bitmaps[$i], 0, $y, $bitmaps[$i].Width, $bitmaps[$i].Height)',
    '    }',
    '    $canvas.Save($out, [System.Drawing.Imaging.ImageFormat]::Png)',
    '  } finally {',
    '    $g.Dispose()',
    '    $canvas.Dispose()',
    '  }',
    '} finally {',
    '  foreach ($bmp in $bitmaps) { if ($bmp) { $bmp.Dispose() } }',
    '}',
    'Write-Output $out',
    '',
  ].join('\n');

  await fs.writeFile(scriptPath, script, 'utf8');
}

async function captureStitchedPage(page, item) {
  const metrics = await getPageMetrics(page);
  const positions = buildCapturePositions(metrics.totalHeight, metrics.viewportHeight);
  const captureDir = path.join(tempDir, item.file.replace(/\.png$/i, ''));
  await emptyDir(captureDir);

  const pieces = [];

  for (const [index, top] of positions.entries()) {
    await setScrollTop(page, top);
    await page.waitForTimeout(450);

    if (index > 0) {
      await page.addStyleTag({
        content: '.public-header{visibility:hidden !important;opacity:0 !important;pointer-events:none !important;}',
      }).catch(() => {});
      await page.waitForTimeout(120);
    }

    const piecePath = path.join(captureDir, `${String(index).padStart(2, '0')}.png`);
    await page.screenshot({
      path: piecePath,
      animations: 'disabled',
      omitBackground: false,
    });
    pieces.push({ path: piecePath, top });
  }

  const scriptPath = path.join(captureDir, 'stitch.ps1');
  const outputPath = path.join(outputDir, item.file);
  await writeStitchScript(scriptPath, outputPath, metrics.totalHeight, pieces);
  await execFileAsync('powershell', ['-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', scriptPath], {
    cwd: rootDir,
  });
}

async function writeManifest(pages) {
  const lines = [
    '# Screenshot Full Page Publik',
    '',
    '| No | Halaman | Route | File |',
    '| --- | --- | --- | --- |',
  ];

  pages.forEach((page, index) => {
    lines.push(`| ${index + 1} | ${page.label} | \`${page.route}\` | ${page.file} |`);
  });

  lines.push('');
  lines.push(`Base URL: ${baseUrl}`);
  lines.push(`Total halaman: ${pages.length}`);
  lines.push(`Dibuat: ${new Date().toISOString()}`);
  lines.push('');

  return fs.writeFile(path.join(outputDir, 'README.md'), lines.join('\n'), 'utf8');
}

async function main() {
  await ensureDir(outputDir);
  await ensureDir(tempDir);
  const slugs = await getPublicSlugs();
  const serviceTemplateMap = await getServiceTemplateMap();
  const pages = buildPages(slugs).filter((item) => !onlyFile || item.file === onlyFile);

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({
    viewport,
    deviceScaleFactor,
  });
  await page.emulateMedia({ reducedMotion: 'reduce' });

  page.setDefaultTimeout(30000);

  try {
    for (const item of pages) {
      console.log(`Capturing ${item.file} (${item.route})`);
      await gotoAndWait(page, item.route, item.waitFor);
      if (item.kind === 'service' && serviceTemplateMap[item.slug]) {
        const previewBuffer = await renderServicePreviewImage(browser, serviceTemplateMap[item.slug], item.slug);
        await injectServicePreview(page, previewBuffer);
      }
      await preparePageForStitchedCapture(page);
      await captureStitchedPage(page, item);
    }
    await writeManifest(pages);
  } finally {
    await browser.close();
  }
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
