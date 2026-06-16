import fs from 'node:fs/promises';
import { existsSync } from 'node:fs';
import path from 'node:path';

const root = path.resolve('docs/figma/admin');
const cssCache = new Map();

function toLocalCssPath(href) {
  try {
    if (!href) return null;
    if (/^https?:\/\//i.test(href)) {
      const url = new URL(href);
      const fromRoot = path.resolve(`.${url.pathname}`);
      const fromPublic = path.resolve(`./public${url.pathname}`);
      return { fromRoot, fromPublic };
    }
    if (href.startsWith('/')) {
      const fromRoot = path.resolve(`.${href}`);
      const fromPublic = path.resolve(`./public${href}`);
      return { fromRoot, fromPublic };
    }
    const fromRelative = path.resolve(path.join(root, href));
    return { fromRelative };
  } catch {
    return null;
  }
}

async function readCss(href) {
  if (cssCache.has(href)) return cssCache.get(href);
  const localPathSet = toLocalCssPath(href);
  if (!localPathSet) return '';
  for (const localPath of Object.values(localPathSet)) {
    if (!localPath) continue;
    try {
      const css = await fs.readFile(localPath, 'utf8');
      cssCache.set(href, css);
      return css;
    } catch {}
  }
  cssCache.set(href, '');
  return '';
}

async function getMirrorFiles(dir) {
  const out = [];
  const stack = [dir];
  while (stack.length) {
    const current = stack.pop();
    const entries = await fs.readdir(current, { withFileTypes: true });
    for (const entry of entries) {
      const full = path.join(current, entry.name);
      if (entry.isDirectory()) {
        stack.push(full);
      } else if (entry.isFile() && entry.name.endsWith('-mirror.html')) {
        out.push(full);
      }
    }
  }
  return out;
}

function extractCssHrefs(html) {
  const hrefs = [];
  const re = /<link\b[^>]*\brel=(?:"|')([^"']+)(?:"|')[^>]*>/gi;
  let m;
  while ((m = re.exec(html))) {
    const rel = (m[1] || '').toLowerCase();
    if (!rel.includes('stylesheet') && !rel.includes('preload')) continue;
    if (rel.includes('preload') && !/as=(?:"|')style(?:"|')/i.test(m[0])) continue;
    const hrefMatch = m[0].match(/\bhref=(?:"|')([^"']+)(?:"|')/i);
    if (!hrefMatch?.[1]) continue;
    hrefs.push(hrefMatch[1]);
  }
  return [...new Set(hrefs)];
}

function stripExternalCssLinks(html) {
  return html
    .replace(/<link\b[^>]*\brel=(?:"|')preload(?:"|')[^>]*\bas=(?:"|')style(?:"|')[^>]*>\s*/gi, '')
    .replace(/<link\b[^>]*\brel=(?:"|')stylesheet(?:"|')[^>]*>\s*/gi, '');
}

async function inlineFile(filePath) {
  let html = await fs.readFile(filePath, 'utf8');
  const cssHrefs = extractCssHrefs(html);
  const fallbackCssPath = (() => {
    const candidates = [
      path.resolve('public/build/assets/app-Cob1zxeE.css'),
      path.resolve('public/build/assets/app-DBCBhiB5.css'),
      path.resolve('build/assets/app-DBCBhiB5.css'),
    ];
    return candidates.find((p) => !!p && existsSync(p));
  })();

  const cssChunks = [];
  for (const href of cssHrefs) {
    const css = await readCss(href);
    if (css.trim()) cssChunks.push(`/* source: ${href} */\n${css}`);
  }

  if (cssChunks.length === 0 && fallbackCssPath) {
    const css = await fs.readFile(fallbackCssPath, 'utf8');
    if (css.trim()) cssChunks.push(`/* source: ${fallbackCssPath.replace(/\\/g, '/')} */\n${css}`);
  }

  if (cssChunks.length === 0) return { updated: false, reason: 'no css resolved' };

  html = stripExternalCssLinks(html);

  if (cssChunks.length > 0 && !html.includes('data-mirror-inline-css="admin"')) {
    const inlineBlock = `\n<style data-mirror-inline-css="admin">\n${cssChunks.join('\n\n')}\n</style>\n`;
    if (/<\/head>/i.test(html)) {
      html = html.replace(/<\/head>/i, `${inlineBlock}</head>`);
    } else {
      html = `${inlineBlock}${html}`;
    }
  }

  await fs.writeFile(filePath, html, 'utf8');
  return { updated: true, cssCount: cssChunks.length };
}

const files = await getMirrorFiles(root);
let updated = 0;
for (const file of files) {
  const res = await inlineFile(file);
  if (res.updated) updated += 1;
}

console.log(JSON.stringify({ total: files.length, updated }, null, 2));
