import Alpine from '@alpinejs/csp';

let mountTiptap = null;
let TextSelection = null;
let tiptapRuntimePromise = null;

function ensureTiptapRuntime() {
  if (mountTiptap && TextSelection) {
    return Promise.resolve({ mountTiptap, TextSelection });
  }

  if (!tiptapRuntimePromise) {
    tiptapRuntimePromise = Promise.all([
      import('./tiptap-editor'),
      import('@tiptap/pm/state'),
    ]).then(([tiptapModule, pmStateModule]) => {
      mountTiptap = tiptapModule.mountTiptap;
      TextSelection = pmStateModule.TextSelection;
      window.mountTiptap = mountTiptap;
      return { mountTiptap, TextSelection };
    });
  }

  return tiptapRuntimePromise;
}

window.Alpine = Alpine;
window.mountTiptap = (...args) =>
  ensureTiptapRuntime().then(({ mountTiptap: tiptapMount }) => tiptapMount(...args));

function prefersReducedMotion() {
  return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
}

function applyThemeMode(next, { persist = true, animate = true } = {}) {
  const isDark = !!next;
  const root = document.documentElement;
  const shouldAnimate = animate && !prefersReducedMotion();

  if (shouldAnimate) {
    window.clearTimeout(root._ultThemeSwitchTimer);
    root.classList.add('is-theme-switching', 'is-motion-paused');
  }

  root.classList.toggle('dark', isDark);

  if (persist) {
    try {
      localStorage.setItem('ult_theme', isDark ? 'dark' : 'light');
    } catch {
      // Browser storage can be unavailable in strict/private contexts.
    }
  }

  const uiStore = window.Alpine?.store?.('ui');
  if (uiStore) uiStore.dark = isDark;

  if (shouldAnimate) {
    root._ultThemeSwitchTimer = window.setTimeout(() => {
      root.classList.remove('is-theme-switching', 'is-motion-paused');
    }, 240);
  } else {
    root.classList.remove('is-theme-switching', 'is-motion-paused');
  }

  return isDark;
}

function showAvatarFallback(image) {
  if (!(image instanceof HTMLImageElement)) return;

  const root = image.closest('[data-avatar-root]');
  const fallback = root?.querySelector('[data-avatar-fallback]');

  image.hidden = true;
  if (fallback instanceof HTMLElement) fallback.hidden = false;
}

function initAvatarFallbacks(scope = document) {
  const images = Array.from(scope.querySelectorAll('[data-avatar-image]'));

  for (const image of images) {
    if (!(image instanceof HTMLImageElement)) continue;
    if (image.complete && image.naturalWidth < 1) showAvatarFallback(image);
  }
}

document.addEventListener('error', (event) => {
  const target = event.target;
  if (!(target instanceof HTMLImageElement)) return;
  if (!target.matches('[data-avatar-image]')) return;

  showAvatarFallback(target);
}, true);

function initTiptapEditors(scope = document) {
  const roots = Array.from(scope.querySelectorAll('[data-tiptap]'));
  if (!roots.length) return;

  if (!mountTiptap || !TextSelection) {
    ensureTiptapRuntime()
      .then(() => initTiptapEditors(scope))
      .catch((e) => {
        // eslint-disable-next-line no-console
        console.error('failed to load tiptap runtime', e);
      });
    return;
  }

  for (const root of roots) {
    if (root.dataset.tiptapMounted === '1') continue;

    const editorEl = root.querySelector('[data-tiptap-editor]');
    const inputId = root.getAttribute('data-tiptap-input-id') || '';
    const input = inputId ? document.getElementById(inputId) : null;
    if (!editorEl || !input) continue;

    const editor = mountTiptap({
      el: editorEl,
      input,
      initialHtml: input.value || '',
    });

    const countEl = root.querySelector('[data-tiptap-count]');
    const buttons = Array.from(root.querySelectorAll('[data-tiptap-action]'));
    const uploadInput = root.querySelector('[data-tiptap-upload-input]');
    const uploadUrl = root.getAttribute('data-tiptap-upload-url') || '';
    const csrfToken = root.closest('form')?.querySelector('input[name="_token"]')?.value || '';

    const run = (fn) => {
      try {
        fn();
      } catch (e) {
        // eslint-disable-next-line no-console
        console.error('tiptap toolbar failed', e);
      }
    };

    const hasSelection = () => !editor.state.selection.empty;

    const normalizeIndentString = () => '    ';

    const insertIndentAtCursor = () => {
      if (hasSelection()) return;
      run(() => editor.chain().focus().insertContent(normalizeIndentString()).run());
    };

    const removeIndentBeforeCursor = () => {
      if (hasSelection()) return;
      const pos = editor.state.selection.from;
      const doc = editor.state.doc;

      const read = (from, to) => {
        if (from < 0 || to < 0) return '';
        if (from > to) return '';
        const max = doc.content.size;
        if (to > max) return '';
        if (from > max) return '';
        return doc.textBetween(from, to, '', '');
      };

      const prev1 = read(pos - 1, pos);
      if (prev1 === '\t') {
        run(() => editor.chain().focus().deleteRange({ from: pos - 1, to: pos }).run());
        return;
      }

      const prev4 = read(pos - 4, pos);
      if (prev4 === '    ') {
        run(() => editor.chain().focus().deleteRange({ from: pos - 4, to: pos }).run());
      }
    };

    const indentSelectionInTextBlocks = () => {
      const { from, to } = editor.state.selection;

      const blocks = [];
      editor.state.doc.nodesBetween(from, to, (node, pos) => {
        if (node.isTextblock) blocks.push({ node, pos });
      });

      if (!blocks.length) return;

      run(() => {
        editor.commands.command(({ tr, dispatch }) => {
          const indent = normalizeIndentString();
          for (let i = blocks.length - 1; i >= 0; i -= 1) {
            const start = blocks[i].pos + 1;
            tr.insertText(indent, start);
          }

          const nextFrom = tr.mapping.map(from);
          const nextTo = tr.mapping.map(to);
          tr.setSelection(TextSelection.create(tr.doc, nextFrom, nextTo));

          if (dispatch) dispatch(tr);
          return true;
        });
        editor.commands.focus();
      });
    };

    const outdentSelectionInTextBlocks = () => {
      const { from, to } = editor.state.selection;

      const blocks = [];
      editor.state.doc.nodesBetween(from, to, (node, pos) => {
        if (node.isTextblock) blocks.push({ node, pos });
      });

      if (!blocks.length) return;

      run(() => {
        editor.commands.command(({ tr, dispatch }) => {
          const deletions = [];
          for (const b of blocks) {
            const start = b.pos + 1;
            const sample = editor.state.doc.textBetween(start, Math.min(start + 4, editor.state.doc.content.size), '', '');
            if (!sample) continue;
            if (sample.startsWith('\t')) deletions.push({ from: start, to: start + 1 });
            else if (sample.startsWith('    ')) deletions.push({ from: start, to: start + 4 });
            else if (sample.startsWith('   ')) deletions.push({ from: start, to: start + 3 });
            else if (sample.startsWith('  ')) deletions.push({ from: start, to: start + 2 });
            else if (sample.startsWith(' ')) deletions.push({ from: start, to: start + 1 });
          }

          for (let i = deletions.length - 1; i >= 0; i -= 1) {
            tr.delete(deletions[i].from, deletions[i].to);
          }

          const nextFrom = tr.mapping.map(from);
          const nextTo = tr.mapping.map(to);
          tr.setSelection(TextSelection.create(tr.doc, nextFrom, nextTo));

          if (dispatch) dispatch(tr);
          return true;
        });
        editor.commands.focus();
      });
    };

    const indent = () => {
      // Prefer list indentation if possible
      const didSink = editor.chain().focus().sinkListItem('listItem').run();
      if (didSink) {
        return;
      }

      if (hasSelection()) indentSelectionInTextBlocks();
      else insertIndentAtCursor();
    };

    const outdent = () => {
      // Prefer list outdent if possible
      const didLift = editor.chain().focus().liftListItem('listItem').run();
      if (didLift) {
        return;
      }

      if (hasSelection()) outdentSelectionInTextBlocks();
      else removeIndentBeforeCursor();
    };

    // Force Tab / Shift+Tab to work reliably (browser otherwise may move focus).
    editorEl.addEventListener(
      'keydown',
      (e) => {
      if (e.key !== 'Tab') return;
      e.preventDefault();
      e.stopPropagation();
      if (e.shiftKey) outdent();
      else indent();
      },
      true
    );

    // Ctrl+Click / Cmd+Click on a link opens in a new tab (editor keeps openOnClick=false).
    editorEl.addEventListener(
      'click',
      (e) => {
        if (!e.ctrlKey && !e.metaKey) return;
        const target = e.target instanceof Element ? e.target : null;
        const a = target ? target.closest('a[href]') : null;
        if (!a) return;
        e.preventDefault();
        e.stopPropagation();
        const href = a.getAttribute('href') || '';
        if (!href) return;
        try {
          window.open(href, '_blank', 'noopener,noreferrer');
        } catch {
          // ignore
        }
      },
      true
    );

    const normalizeYoutubeEmbedUrl = (raw) => {
      const input = (raw || '').toString().trim();
      if (!input) return null;

      // allow direct embed urls
      if (input.includes('youtube.com/embed/') || input.includes('youtube-nocookie.com/embed/')) {
        return input;
      }

      let url;
      try {
        url = new URL(input);
      } catch {
        return null;
      }

      const host = url.hostname.replace(/^www\./, '');
      let id = '';

      if (host === 'youtu.be') {
        id = url.pathname.replace(/^\//, '');
      } else if (host === 'youtube.com' || host === 'm.youtube.com') {
        if (url.pathname === '/watch') id = url.searchParams.get('v') || '';
        else if (url.pathname.startsWith('/shorts/')) id = url.pathname.split('/')[2] || '';
        else if (url.pathname.startsWith('/embed/')) id = url.pathname.split('/')[2] || '';
        else if (url.pathname.startsWith('/live/')) id = url.pathname.split('/')[2] || '';
      }

      if (!id) return null;
      // strip any playlist/time fragments
      id = id.split('?')[0].split('&')[0];

      return `https://www.youtube-nocookie.com/embed/${id}`;
    };

    const sync = () => {
      if (countEl) {
        const txt = editor.getText() || '';
        countEl.textContent = String(txt.trim().length);
      }

      const parseMediaAlign = (className) => {
        const cls = (className || '').toString();
        if (cls.includes('media-align-right')) return 'right';
        if (cls.includes('media-align-center')) return 'center';
        if (cls.includes('media-align-left')) return 'left';
        return null;
      };

      const selectionNode = editor.state.selection?.node || null;
      const mediaAlign =
        (selectionNode?.type?.name === 'image' ? parseMediaAlign(editor.getAttributes('image')?.class) : null) ||
        (selectionNode?.type?.name === 'youtube' ? parseMediaAlign(editor.getAttributes('youtube')?.class) : null);

      const alignAttr =
        mediaAlign ||
        editor.getAttributes('heading')?.textAlign ||
        editor.getAttributes('paragraph')?.textAlign ||
        'left';

      for (const btn of buttons) {
        const action = btn.dataset.tiptapAction || '';
        const level = parseInt(btn.dataset.tiptapLevel || '0', 10);
        const align = btn.dataset.tiptapAlign || '';

        let active = false;
        if (action === 'bold') active = editor.isActive('bold');
        if (action === 'italic') active = editor.isActive('italic');
        if (action === 'strike') active = editor.isActive('strike');
        if (action === 'bulletList') active = editor.isActive('bulletList');
        if (action === 'orderedList') active = editor.isActive('orderedList');
        if (action === 'blockquote') active = editor.isActive('blockquote');
        if (action === 'heading' && level) active = editor.isActive('heading', { level });
        if (action === 'align' && align) active = alignAttr === align;
        if (action === 'link') active = editor.isActive('link');

        btn.classList.toggle('tiptap-btn--active', !!active);

        // Keep undo/redo enabled (reliable availability checks differ per history state)
      }
    };

    const exec = (action, level = 0) => {
      run(() => {
        const c = editor.chain().focus();
        if (action === 'bold') c.toggleBold().run();
        else if (action === 'italic') c.toggleItalic().run();
        else if (action === 'strike') c.toggleStrike().run();
        else if (action === 'bulletList') c.toggleBulletList().run();
        else if (action === 'orderedList') c.toggleOrderedList().run();
        else if (action === 'blockquote') c.toggleBlockquote().run();
        else if (action === 'heading' && level) c.toggleHeading({ level }).run();
        else if (action === 'hr') c.setHorizontalRule().run();
        else if (action === 'indent') c.sinkListItem('listItem').run();
        else if (action === 'outdent') c.liftListItem('listItem').run();
        else if (action === 'undo') c.undo().run();
        else if (action === 'redo') c.redo().run();
      });
      sync();
    };

    for (const btn of buttons) {
      const action = btn.dataset.tiptapAction || '';
      const level = parseInt(btn.dataset.tiptapLevel || '0', 10);
      const align = btn.dataset.tiptapAlign || '';
      btn.addEventListener('mousedown', (e) => e.preventDefault());
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        if (action === 'image') {
          if (uploadInput) uploadInput.click();
          return;
        }
        if (action === 'align' && align) {
          run(() => {
            const selectionNode = editor.state.selection?.node || null;
            const isMedia = selectionNode?.type?.name === 'image' || selectionNode?.type?.name === 'youtube';
            const alignClasses = ['media-align-left', 'media-align-center', 'media-align-right'];
            const nextAlignClass =
              align === 'right' ? 'media-align-right' : align === 'center' ? 'media-align-center' : 'media-align-left';

            if (isMedia) {
              const pos = editor.state.selection.from;
              const nodeName = selectionNode.type.name;
              const attrs = editor.getAttributes(nodeName) || {};
              const cls = (attrs.class || '').toString();
              const kept = cls
                .split(/\s+/)
                .filter((c) => c && !alignClasses.includes(c))
                .join(' ');
              const merged = `${kept} ${nextAlignClass}`.trim();
              editor.commands.command(({ tr, dispatch }) => {
                tr.setNodeMarkup(pos, undefined, { ...selectionNode.attrs, ...attrs, class: merged });
                if (dispatch) dispatch(tr);
                return true;
              });
              editor.commands.focus();
            } else {
              editor.chain().focus().setTextAlign(align).run();
            }
          });
          sync();
          return;
        }
        if (action === 'indent') {
          indent();
          sync();
          return;
        }
        if (action === 'outdent') {
          outdent();
          sync();
          return;
        }
        if (action === 'link') {
          run(() => {
            const prevHref = (editor.getAttributes('link')?.href || '').toString();
            const hadSelection = hasSelection();
            const { from, to } = editor.state.selection;
            const selectedText = editor.state.doc.textBetween(from, to, ' ', ' ').trim();

            const url = window.prompt('Masukkan URL tautan:', prevHref || 'https://');
            if (!url) return;

            if (!hadSelection) {
              const label = window.prompt('Nama/teks tautan:', '') || '';
              const finalLabel = label.trim() || url;
              editor
                .chain()
                .focus()
                .insertContent(finalLabel)
                .setTextSelection({ from: from, to: from + finalLabel.length })
                .setLink({ href: url, target: '_blank', rel: 'noopener noreferrer' })
                .run();
              return;
            }

            const newLabel = window.prompt('Nama/teks tautan (opsional):', selectedText) || '';
            const finalLabel = newLabel.trim();
            if (finalLabel && finalLabel !== selectedText) {
              editor
                .chain()
                .focus()
                .insertContentAt({ from, to }, finalLabel)
                .setTextSelection({ from, to: from + finalLabel.length })
                .setLink({ href: url, target: '_blank', rel: 'noopener noreferrer' })
                .run();
            } else {
              editor
                .chain()
                .focus()
                .extendMarkRange('link')
                .setLink({ href: url, target: '_blank', rel: 'noopener noreferrer' })
                .run();
            }
          });
          sync();
          return;
        }
        if (action === 'unlink') {
          run(() => editor.chain().focus().unsetLink().run());
          sync();
          return;
        }
        if (action === 'video') {
          run(() => {
            const url = window.prompt('Masukkan link video (YouTube):', 'https://');
            if (!url) return;
            const embed = normalizeYoutubeEmbedUrl(url);
            if (!embed) return;
            editor.commands.setYoutubeVideo({ src: embed, width: 640, height: 360 });
          });
          sync();
          return;
        }
        exec(action, level);
      });
    }

    if (uploadInput) {
      uploadInput.addEventListener('change', async () => {
        const file = uploadInput.files && uploadInput.files[0] ? uploadInput.files[0] : null;
        uploadInput.value = '';
        if (!file) return;

        // Only allow common raster images (no svg).
        const okTypes = new Set(['image/png', 'image/jpeg', 'image/webp', 'image/gif']);
        if (!okTypes.has(file.type)) return;

        if (uploadUrl && csrfToken) {
          const fd = new FormData();
          fd.append('file', file);
          try {
            const res = await fetch(uploadUrl, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
              },
              body: fd,
            });
            if (!res.ok) return;
            const json = await res.json().catch(() => ({}));
            const url = (json?.url || '').toString();
            if (!url) return;
            run(() => editor.chain().focus().setImage({ src: url, width: 480 }).run());
            sync();
            return;
          } catch {
            // ignore
          }
        }
      });
    }

    editor.on('update', sync);
    editor.on('selectionUpdate', sync);
    editor.on('transaction', sync);
    sync();

    root.dataset.tiptapMounted = '1';
    root._tiptapEditor = editor;
  }
}

/**
 * Global UI store
 */
Alpine.store('ui', {
  dark: document.documentElement.classList.contains('dark'),
  sidebarOpen: false,
  toggleDark() {
    applyThemeMode(!this.dark);
  },
  toggleSidebar() {
    this.sidebarOpen = !this.sidebarOpen;
  },
  closeSidebar() {
    this.sidebarOpen = false;
  }
});

/**
 * Toast store
 */
Alpine.store('toast', {
  toasts: [],
  push({ type = 'info', title = '', message = '', timeout = 4000 }) {
    const id = crypto.randomUUID();
    this.toasts.push({ id, type, title, message });
    setTimeout(() => this.dismiss(id), timeout);
  },
  dismiss(id) {
    this.toasts = this.toasts.filter(t => t.id !== id);
  }
});

function syncCustomFileField(input) {
  if (!(input instanceof HTMLInputElement)) return;

  const field = input.closest('[data-file-field]');
  if (!field) return;

  const nameEl = field.querySelector('[data-file-name]');
  if (!nameEl) return;

  const emptyLabel = String(field.getAttribute('data-file-empty-label') || 'Belum ada file dipilih');
  const files = Array.from(input.files || []);

  if (files.length < 1) {
    nameEl.textContent = emptyLabel;
    field.classList.remove('has-file');
    return;
  }

  nameEl.textContent = files.map((file) => file.name).join(', ');
  field.classList.add('has-file');
}

function loadImageElement(file) {
  return new Promise((resolve, reject) => {
    const objectUrl = URL.createObjectURL(file);
    const image = new Image();

    image.onload = () => {
      URL.revokeObjectURL(objectUrl);
      resolve(image);
    };

    image.onerror = () => {
      URL.revokeObjectURL(objectUrl);
      reject(new Error('Gagal membaca file gambar.'));
    };

    image.src = objectUrl;
  });
}

async function squareCompressProfilePhoto(file, {
  maxBytes = 2 * 1024 * 1024,
  maxDimension = 1024,
} = {}) {
  const image = await loadImageElement(file);
  const sourceWidth = image.naturalWidth || image.width || 0;
  const sourceHeight = image.naturalHeight || image.height || 0;
  const side = Math.max(1, Math.min(sourceWidth, sourceHeight));

  if (sourceWidth < 1 || sourceHeight < 1) {
    throw new Error('Dimensi gambar tidak valid.');
  }

  const outputSize = Math.max(1, Math.min(side, maxDimension));
  const canvas = document.createElement('canvas');
  canvas.width = outputSize;
  canvas.height = outputSize;

  const ctx = canvas.getContext('2d');
  if (!ctx) {
    throw new Error('Browser tidak mendukung pemrosesan gambar.');
  }

  const offsetX = Math.max(0, Math.floor((sourceWidth - side) / 2));
  const offsetY = Math.max(0, Math.floor((sourceHeight - side) / 2));

  ctx.fillStyle = '#ffffff';
  ctx.fillRect(0, 0, outputSize, outputSize);
  ctx.drawImage(image, offsetX, offsetY, side, side, 0, 0, outputSize, outputSize);

  const qualities = [0.92, 0.86, 0.8, 0.74, 0.68, 0.62, 0.56, 0.5, 0.44];
  let blob = null;

  for (const quality of qualities) {
    // eslint-disable-next-line no-await-in-loop
    blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));
    if (blob && blob.size <= maxBytes) break;
  }

  if (!blob) {
    throw new Error('Gagal memproses gambar.');
  }

  if (blob.size > maxBytes) {
    throw new Error('Ukuran foto setelah dikompres masih di atas 2MB. Pilih foto lain yang lebih ringan.');
  }

  const originalName = String(file.name || 'profile-photo');
  const safeBaseName = originalName
    .replace(/\.[^.]+$/, '')
    .replace(/[^a-z0-9-_]+/gi, '-')
    .replace(/^-+|-+$/g, '') || 'profile-photo';

  return new File([blob], `${safeBaseName}.jpg`, {
    type: 'image/jpeg',
    lastModified: Date.now(),
  });
}

Alpine.data('profilePhotoUploader', ({
  initialUrl = null,
  maxBytes = 2 * 1024 * 1024,
} = {}) => ({
  previewUrl: initialUrl,
  previewLoadFailed: false,
  selectedFileName: '',
  uploadError: '',
  isProcessing: false,

  handlePreviewError() {
    if (!this.previewUrl) return;
    this.previewLoadFailed = true;
  },

  async handleChange(event) {
    const input = event?.target instanceof HTMLInputElement ? event.target : null;
    const file = input?.files?.[0] || null;

    this.uploadError = '';

    if (!input || !file) {
      this.selectedFileName = '';
      return;
    }

    const lowerName = String(file.name || '').toLowerCase();
    const type = String(file.type || '').toLowerCase();
    const supportedTypes = new Set(['image/jpeg', 'image/png', 'image/webp']);

    if (lowerName.endsWith('.heic') || lowerName.endsWith('.heif') || type === 'image/heic' || type === 'image/heif') {
      input.value = '';
      syncCustomFileField(input);
      this.selectedFileName = '';
      this.uploadError = 'Foto HEIC/HEIF dari iPhone belum didukung. Ubah ke JPG, PNG, atau WEBP lalu pilih ulang.';
      return;
    }

    if (!supportedTypes.has(type)) {
      input.value = '';
      syncCustomFileField(input);
      this.selectedFileName = '';
      this.uploadError = 'Format foto harus JPG, PNG, atau WEBP.';
      return;
    }

    this.isProcessing = true;

    try {
      const processed = await squareCompressProfilePhoto(file, { maxBytes });
      const transfer = new DataTransfer();
      transfer.items.add(processed);
      input.files = transfer.files;

      if (this.previewUrl && String(this.previewUrl).startsWith('blob:')) {
        URL.revokeObjectURL(this.previewUrl);
      }

      this.previewUrl = URL.createObjectURL(processed);
      this.previewLoadFailed = false;
      this.selectedFileName = processed.name;
      syncCustomFileField(input);
    } catch (error) {
      input.value = '';
      syncCustomFileField(input);
      this.selectedFileName = '';

      if (this.previewUrl && String(this.previewUrl).startsWith('blob:')) {
        URL.revokeObjectURL(this.previewUrl);
      }

      this.previewUrl = initialUrl;
      this.previewLoadFailed = false;
      this.uploadError = error instanceof Error && error.message
        ? error.message
        : 'Foto profil gagal diproses. Coba gunakan gambar lain.';
    } finally {
      this.isProcessing = false;
    }
  },

  handleSubmit(event) {
    if (!this.isProcessing) return;
    event.preventDefault();
    this.uploadError = 'Tunggu sebentar, foto profil masih diproses.';
  },
}));

/**
 * Alpine component: live-search for admin request cards without page reload.
 */
Alpine.data('adminRequestsLiveSearch', () => ({
  q: '',
  items: [],
  totalCount: 0,
  visibleCount: 0,

  init() {
    this.items = Array.from(this.$root.querySelectorAll('[data-request-search-item]'));
    this.totalCount = this.items.length;
    this.visibleCount = this.totalCount;
    const initialInput = this.$root.querySelector('input[name="q"], input[name="requests_live_q"]');
    this.q = String(initialInput?.value || '').trim();
    this.refreshCount();
    this.$watch('q', () => this.refreshCount());
  },

  normalize(value) {
    return String(value || '')
      .toLowerCase()
      .normalize('NFD')
      // eslint-disable-next-line no-control-regex
      .replace(/[\u0300-\u036f]/g, '')
      .trim();
  },

  hasQuery() {
    return this.normalize(this.q) !== '';
  },

  matches(el) {
    const needle = this.normalize(this.q);
    if (!needle) return true;
    const haystack = this.normalize(el?.getAttribute('data-request-search-text') || el?.textContent || '');
    return haystack.includes(needle);
  },

  refreshCount() {
    const needle = this.normalize(this.q);
    if (!needle) {
      this.visibleCount = this.totalCount;
      return;
    }

    let shown = 0;
    for (const el of this.items) {
      const haystack = this.normalize(el.getAttribute('data-request-search-text') || el.textContent || '');
      if (haystack.includes(needle)) shown += 1;
    }
    this.visibleCount = shown;
  },

  clear() {
    this.q = '';
    this.visibleCount = this.totalCount;
  },

  clearSearch() {
    this.clear();

    this.$nextTick(() => {
      const input = this.$refs.adminRequestsSearchInput || null;
      if (!input) return;

      input.value = '';
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new Event('change', { bubbles: true }));
      input.focus();
    });
  },
}));

/**
 * Alpine component: tiptap editor wrapper with toolbar actions.
 * Works with the StarterKit feature set. Server-side sanitization remains mandatory.
 */
Alpine.data('tiptap', ({ initial = '', inputId, placeholder = '' } = {}) => ({
  editor: null,
  mounted: false,
  count: 0,
  tick: 0,

  init() {
    const el = this.$refs.editor;
    const input = document.getElementById(inputId);
    if (!el || !input) return;

    this.editor = mountTiptap({ el, input, initialHtml: initial, placeholder });

    this.mounted = true;
    this.updateCount();
    this.editor.on('update', () => this.updateCount());
    this.editor.on('selectionUpdate', () => { this.tick++; });
    this.editor.on('transaction', () => { this.tick++; });
  },

  updateCount() {
    if (!this.editor) return;
    const text = this.editor.getText() || '';
    this.count = text.trim().length;
  },

  isActive(name, attrs = {}) {
    // Depend on tick so Alpine re-renders on selection changes.
    void this.tick;
    return this.editor ? this.editor.isActive(name, attrs) : false;
  },

  cmd(fn) {
    if (!this.editor) return;
    try {
      const out = fn(this.editor);
      this.tick++;
      return out;
    } catch (e) {
      try {
        Alpine.store('toast').push({
          type: 'info',
          title: 'Editor',
          message: 'Perintah tidak bisa dijalankan (cek console).',
        });
      } catch {
        // ignore
      }
      // eslint-disable-next-line no-console
      console.error('tiptap command failed', e);
      return null;
    }
  },

  focus() {
    if (!this.editor) return;
    try { this.editor.commands.focus(); } catch { /* ignore */ }
  },

  // Commands
  bold() { this.cmd(e => e.chain().focus().toggleBold().run()); },
  italic() { this.cmd(e => e.chain().focus().toggleItalic().run()); },
  strike() { this.cmd(e => e.chain().focus().toggleStrike().run()); },
  code() { this.cmd(e => e.chain().focus().toggleCode().run()); },
  bullet() { this.cmd(e => e.chain().focus().toggleBulletList().run()); },
  ordered() { this.cmd(e => e.chain().focus().toggleOrderedList().run()); },
  quote() { this.cmd(e => e.chain().focus().toggleBlockquote().run()); },
  h2() { this.cmd(e => e.chain().focus().toggleHeading({ level: 2 }).run()); },
  h3() { this.cmd(e => e.chain().focus().toggleHeading({ level: 3 }).run()); },
  hr() { this.cmd(e => e.chain().focus().setHorizontalRule().run()); },
  undo() { this.cmd(e => e.chain().focus().undo().run()); },
  redo() { this.cmd(e => e.chain().focus().redo().run()); },
}));

function normalizeUniqueStrings(list) {
  if (!Array.isArray(list)) return [];
  const out = [];
  const seen = new Set();
  for (const raw of list) {
    const v = (raw || '').toString().trim();
    if (!v) continue;
    if (seen.has(v)) continue;
    seen.add(v);
    out.push(v);
  }
  return out;
}

/**
 * Alpine component: Gate steps editor (array of step keys).
 * Produces JSON string for hidden gate_steps_json input.
 */
Alpine.data('gateStepsEditor', ({ initial = [] } = {}) => ({
  required: ['VERIFY_INITIAL', 'INPUT_NOMOR_SURAT'],
  steps: [],
  custom: '',

  init() {
    const initSteps = normalizeUniqueStrings(initial).map(s => s.toUpperCase());
    const required = this.required;
    this.steps = normalizeUniqueStrings([...required, ...initSteps]).map(s => s.toUpperCase());
  },

  get json() {
    return JSON.stringify(this.steps);
  },

  has(step) {
    return this.steps.includes(step);
  },

  toggle(step) {
    const s = (step || '').toString().trim().toUpperCase();
    if (!s) return;
    if (this.required.includes(s)) return; // locked

    if (this.steps.includes(s)) this.steps = this.steps.filter(x => x !== s);
    else this.steps = normalizeUniqueStrings([...this.steps, s]).map(x => x.toUpperCase());
  },

  addCustom() {
    const s = (this.custom || '').toString().trim().toUpperCase();
    if (!s) return;
    this.custom = '';
    if (this.required.includes(s)) return;
    this.steps = normalizeUniqueStrings([...this.steps, s]).map(x => x.toUpperCase());
  },
}));

/**
 * Alpine component: Signers editor.
 * Produces JSON array string for hidden signers_json input.
 */
Alpine.data('signersEditor', ({ initial = [], roleOptions = [] } = {}) => ({
  signers: [],
  roleOptions: [],
  roleValues: [],

  init() {
    const opts = Array.isArray(roleOptions) ? roleOptions : [];
    this.roleOptions = opts.filter((o) => o && typeof o.value === 'string' && typeof o.label === 'string')
      .map((o) => ({ value: o.value.toString().trim().toUpperCase(), label: o.label.toString().trim() }));
    this.roleValues = this.roleOptions.map((o) => o.value);

    const initItems = Array.isArray(initial) ? initial : [];
    this.signers = initItems.map((s) => ({
      role: (s?.role || '').toString().trim().toUpperCase(),
      custom_label: (s?.custom_label || '').toString().trim(),
      is_required: !!(s?.is_required ?? true),
      requires_signature_upload: !!(s?.requires_signature_upload ?? false),
      signature_file_types: Array.isArray(s?.signature_file_types) ? s.signature_file_types : ['image/png'],
      signature_max_size_kb: (s?.signature_max_size_kb ?? 256),
    }));

    if (this.signers.length === 0) this.add();
  },

  add() {
    this.signers.push({
      role: '',
      custom_label: '',
      is_required: true,
      requires_signature_upload: false,
      signature_file_types: ['image/png'],
      signature_max_size_kb: 256,
    });
  },

  remove(i) {
    this.signers.splice(i, 1);
    if (this.signers.length === 0) this.add();
  },

  moveUp(i) {
    if (i <= 0) return;
    const tmp = this.signers[i - 1];
    this.signers[i - 1] = this.signers[i];
    this.signers[i] = tmp;
  },

  moveDown(i) {
    if (i >= this.signers.length - 1) return;
    const tmp = this.signers[i + 1];
    this.signers[i + 1] = this.signers[i];
    this.signers[i] = tmp;
  },

  normalizeSigner(s, idx) {
    const role = (s.role || '').toString().trim().toUpperCase();
    const customLabel = (s.custom_label || '').toString().trim();
    const order_index = idx + 1;
    const reqSig = !!s.requires_signature_upload;
    const types = reqSig ? normalizeUniqueStrings(s.signature_file_types || []).map(t => t.toLowerCase()) : null;
    const maxKb = reqSig ? Math.max(1, parseInt(s.signature_max_size_kb || 0, 10) || 0) : null;

    return {
      role,
      custom_label: (role === 'CUSTOM' || role === 'DOSEN' || role === 'PEMOHON') ? customLabel : null,
      order_index,
      is_required: !!(s.is_required ?? true),
      requires_signature_upload: reqSig,
      signature_file_types: types,
      signature_max_size_kb: maxKb,
    };
  },

  get json() {
    const out = this.signers.map((s, idx) => this.normalizeSigner(s, idx))
      .filter(s => s.role !== '');
    return JSON.stringify(out);
  },
}));

/**
 * Alpine component: simple "one per line" list editor outputting JSON array.
 * Used to avoid typing raw JSON in UI.
 */
Alpine.data('linesToJsonArray', ({ initial = [] } = {}) => ({
  lines: '',
  init() {
    if (Array.isArray(initial) && initial.length > 0) {
      this.lines = initial.map(v => (v ?? '').toString()).join('\n');
    }
  },
  get json() {
    const arr = this.lines
      .split(/\r?\n/g)
      .map(s => s.trim())
      .filter(Boolean);
    return JSON.stringify(arr);
  },
}));

/**
 * Alpine component: Workflow steps editor.
 * Produces JSON array string for hidden steps_json input.
 */
Alpine.data('workflowStepsEditor', ({ initial = [] } = {}) => ({
  steps: [],
  recommendedKeys: [
    { key: 'submit', label: 'submit — Pengajuan' },
    { key: 'jurusan_verify', label: 'jurusan_verify — Verifikasi Jurusan' },
    { key: 'ult_review', label: 'ult_review — Review ULT' },
    { key: 'faculty_sign', label: 'faculty_sign — Tanda Tangan Fakultas' },
    { key: 'ult_issue', label: 'ult_issue — Penomoran' },
    { key: 'output', label: 'output — Output' },
    { key: 'done', label: 'done — Selesai' },
  ],

  stepTitle(step) {
    const key = (step?.key || '').toString().trim();
    if (!key) return 'Pengajuan';
    const found = this.recommendedKeys.find(o => o.key === key);
    if (!found) return key;
    const parts = (found.label || '').toString().split('—');
    const title = parts.slice(1).join('—').trim();
    return title || found.label;
  },

  currentKeys() {
    return new Set(
      this.steps
        .map(s => (s?.key || '').toString().trim())
        .filter(Boolean),
    );
  },

  nextAvailableKey(usedKeys, preferred = 'submit') {
    const used = usedKeys instanceof Set ? usedKeys : new Set();
    const pref = (preferred || '').toString().trim();
    if (pref && !used.has(pref)) {
      used.add(pref);
      return pref;
    }

    for (const opt of this.recommendedKeys) {
      const k = (opt?.key || '').toString().trim();
      if (!k) continue;
      if (!used.has(k)) {
        used.add(k);
        return k;
      }
    }

    let i = 1;
    while (used.has(`step_${i}`)) i += 1;
    const fallback = `step_${i}`;
    used.add(fallback);
    return fallback;
  },

  init() {
    const initItems = Array.isArray(initial) ? initial : [];
    const used = new Set();
    this.steps = initItems.map((s) => {
      let key = (s?.key || '').toString().trim();
      if (!key || used.has(key)) key = this.nextAvailableKey(used, 'submit');
      else used.add(key);

      return {
        key,
        label_id: (s?.label_id || '').toString(),
        label_en: (s?.label_en || '').toString(),
        role_required: (s?.role_required || '').toString(),
        unit_scope: 'jurusan',
        actions_allowed: Array.isArray(s?.actions_allowed) ? s.actions_allowed : [],
        can_request_revision: true,
      };
    });

    if (this.steps.length === 0) this.addPresetMinimal();
  },

  roleLabel(step) {
    const key = (step?.key || '').toString().trim();
    return this.fixedRoleForKey(key) || 'Admin Jurusan';
  },

  fixedRoleForKey(key) {
    const k = (key || '').toString().trim();
    if (k === '') return null;
    if (['submit', 'jurusan_verify', 'prodi_verify', 'output'].includes(k)) return 'Admin Jurusan';
    if (['ult_review', 'ult_process', 'ult_issue'].includes(k)) return 'Staf ULT';
    if (k === 'faculty_sign') return 'Fakultas (Dekan/WD)';
    if (k === 'done') return '—';
    return null;
  },

  addPresetMinimal() {
    if (this.steps.length > 0) return;
    this.steps.push({
      key: 'submit',
      label_id: 'Pengajuan',
      label_en: 'Submission',
      role_required: 'Admin Jurusan',
      unit_scope: 'jurusan',
      actions_allowed: ['verify', 'request_revision', 'reject'],
      can_request_revision: true,
    });
  },

  add() {
    const used = this.currentKeys();
    const key = this.nextAvailableKey(used, 'submit');
    this.steps.push({
      key,
      label_id: '',
      label_en: '',
      role_required: '',
      unit_scope: 'jurusan',
      actions_allowed: ['verify', 'request_revision', 'reject'],
      can_request_revision: true,
    });
  },

  remove(i) {
    this.steps.splice(i, 1);
    if (this.steps.length === 0) this.addPresetMinimal();
  },

  moveUp(i) {
    if (i <= 0) return;
    const tmp = this.steps[i - 1];
    this.steps[i - 1] = this.steps[i];
    this.steps[i] = tmp;
  },

  moveDown(i) {
    if (i >= this.steps.length - 1) return;
    const tmp = this.steps[i + 1];
    this.steps[i + 1] = this.steps[i];
    this.steps[i] = tmp;
  },

  toggleAction(step, action) {
    const a = (action || '').toString().trim();
    if (!a) return;
    const list = Array.isArray(step.actions_allowed) ? step.actions_allowed : [];
    if (list.includes(a)) step.actions_allowed = list.filter(x => x !== a);
    else step.actions_allowed = normalizeUniqueStrings([...list, a]);
  },

  normalizeStep(step) {
    const key = (step.key || '').toString().trim();
    if (!key) return null;

    const safeScope = 'jurusan';
    const fixedRole = this.fixedRoleForKey(key);

    return {
      key,
      label_id: (step.label_id || '').toString().trim() || key,
      label_en: (step.label_en || '').toString().trim() || null,
      role_required: fixedRole || (step.role_required || '').toString().trim() || null,
      unit_scope: safeScope,
      actions_allowed: normalizeUniqueStrings(step.actions_allowed || []),
      // Next step is derived from step order on backend; keep config simple on UI.
      next_on_approve: null,
      next_on_reject: null,
      can_request_revision: true,
    };
  },

  get json() {
    const out = [];
    for (const s of this.steps) {
      const n = this.normalizeStep(s);
      if (n) out.push(n);
    }
    return JSON.stringify(out);
  },
}));

/**
 * Alpine component: Placeholder mapping editor.
 * Produces JSON array string for hidden placeholders_items_json input.
 */
Alpine.data('placeholderMappingEditor', ({ initial = [] } = {}) => ({
  items: [],

  init() {
    const initItems = Array.isArray(initial) ? initial : [];
    this.items = initItems.map((it) => ({
      placeholder_key: (it?.placeholder_key || '').toString().trim(),
      source_type: (it?.source_type || 'FORM').toString().trim().toUpperCase(),
      source_ref: (it?.source_ref || '').toString(),
      is_required: !!(it?.is_required ?? true),
      notes: (it?.notes || '').toString(),
    }));
    if (this.items.length === 0) this.add();
  },

  add() {
    this.items.push({
      placeholder_key: '',
      source_type: 'FORM',
      source_ref: '',
      is_required: true,
      notes: '',
    });
  },

  remove(i) {
    this.items.splice(i, 1);
    if (this.items.length === 0) this.add();
  },

  normalizeItem(it) {
    const key = (it.placeholder_key || '').toString().trim().toUpperCase();
    if (!key) return null;

    const src = (it.source_type || 'FORM').toString().trim().toUpperCase();
    const allowed = ['FORM', 'PROFILE', 'INTERNAL', 'SYSTEM_AUTOFILL'];
    const source_type = allowed.includes(src) ? src : 'FORM';

    return {
      placeholder_key: key,
      source_type,
      source_ref: (it.source_ref || '').toString().trim() || null,
      is_required: !!(it.is_required ?? true),
      notes: (it.notes || '').toString().trim() || null,
    };
  },

  get json() {
    const out = [];
    for (const it of this.items) {
      const n = this.normalizeItem(it);
      if (n) out.push(n);
    }
    return JSON.stringify(out);
  },
}));

/**
 * Alpine component: Form builder editor (service_fields) for create page.
 * Produces JSON array string for hidden fields_json input.
 */
Alpine.data('serviceFieldsEditor', ({ initial = [] } = {}) => ({
  fields: [],

  init() {
    const initItems = Array.isArray(initial) ? initial : [];
    this.fields = initItems.map((f) => ({
      key: (f?.key || '').toString().trim(),
      label_id: (f?.label_id || '').toString(),
      type: (f?.type || 'text').toString(),
      required: !!(f?.required ?? false),
      sort_order: (f?.sort_order ?? 0),
      maps_to_placeholder_key: (f?.maps_to_placeholder_key || '').toString(),
      rules_lines: Array.isArray(f?.rules_json) ? f.rules_json.join('\n') : '',
      options_lines: Array.isArray(f?.options_json) ? f.options_json.join('\n') : '',
    }));
    if (this.fields.length === 0) this.add();
  },

  add() {
    this.fields.push({
      key: '',
      label_id: '',
      type: 'text',
      required: false,
      sort_order: 0,
      maps_to_placeholder_key: '',
      rules_lines: '',
      options_lines: '',
    });
  },

  remove(i) {
    this.fields.splice(i, 1);
    if (this.fields.length === 0) this.add();
  },

  moveUp(i) {
    if (i <= 0) return;
    const tmp = this.fields[i - 1];
    this.fields[i - 1] = this.fields[i];
    this.fields[i] = tmp;
  },

  moveDown(i) {
    if (i >= this.fields.length - 1) return;
    const tmp = this.fields[i + 1];
    this.fields[i + 1] = this.fields[i];
    this.fields[i] = tmp;
  },

  normalizeField(f) {
    const key = (f.key || '').toString().trim().toLowerCase();
    if (!key) return null;

    const type = (f.type || 'text').toString();
    const allowedTypes = ['text', 'textarea', 'richtext', 'number', 'date', 'select', 'checkbox', 'json', 'file'];
    const safeType = allowedTypes.includes(type) ? type : 'text';

    const rules_json = f.rules_lines
      ? f.rules_lines.split(/\r?\n/g).map(s => s.trim()).filter(Boolean)
      : null;
    const options_json = f.options_lines
      ? f.options_lines.split(/\r?\n/g).map(s => s.trim()).filter(Boolean)
      : null;

    return {
      key,
      label_id: (f.label_id || '').toString().trim() || key,
      type: safeType,
      required: !!(f.required ?? false),
      sort_order: parseInt(f.sort_order || 0, 10) || 0,
      maps_to_placeholder_key: (f.maps_to_placeholder_key || '').toString().trim().toUpperCase() || null,
      rules_json: rules_json && rules_json.length > 0 ? rules_json : null,
      options_json: options_json && options_json.length > 0 ? options_json : null,
    };
  },

  get json() {
    const out = [];
    for (const f of this.fields) {
      const n = this.normalizeField(f);
      if (n) out.push(n);
    }
    return JSON.stringify(out);
  },
}));

function initPublicSite(root) {
  if (!root) return;

  const header = root.querySelector('[data-public-nav]');
  const navPanel = root.querySelector('#public-nav-panel');
  const toggleBtn = root.querySelector('[data-action="nav-toggle"]');

  const setNavOpen = (open) => {
    if (!navPanel || !toggleBtn) return;

    root.classList.toggle('is-nav-open', open);
    toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    navPanel.setAttribute('aria-hidden', open ? 'false' : 'true');
  };

  const toggleNav = () => {
    setNavOpen(!root.classList.contains('is-nav-open'));
  };

  const setThemeLabel = () => {
    const isDark = document.documentElement.classList.contains('dark');
    root.querySelectorAll('[data-theme-label]').forEach((el) => {
      el.textContent = isDark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap';
    });
  };

  const toggleDark = () => {
    const next = !document.documentElement.classList.contains('dark');
    applyThemeMode(next);
    setThemeLabel();
  };

  const scrollToPublicTarget = (rawTarget) => {
    let selector = String(rawTarget || '').trim();
    if (!selector) return false;

    if (!selector.startsWith('#')) {
      const hashIndex = selector.indexOf('#');
      if (hashIndex >= 0) selector = selector.slice(hashIndex);
    }
    if (!selector.startsWith('#')) return false;

    let target = null;
    try {
      target = document.querySelector(selector);
    } catch {
      target = null;
    }
    if (!target) return false;

    const prefersReducedMotion = !!(window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    const currentScroll = getViewportScrollY();
    const headerHeight = Math.round((header ? header.getBoundingClientRect().height : 72) || 72);
    const targetY = target.getBoundingClientRect().top + currentScroll - headerHeight - 14;
    if (!Number.isFinite(targetY)) return false;

    if (!prefersReducedMotion) fastSmoothScrollToY(targetY);
    else fastSmoothScrollToY(targetY, 0);

    return true;
  };

  // Initial states
  setNavOpen(false);
  setThemeLabel();

  // One reliable handler for all public hero "view section below" buttons.
  root.addEventListener('click', (e) => {
    const clickTarget = e.target instanceof Element ? e.target : null;
    const scrollLink = clickTarget?.closest('[data-scroll-to], a[href^="#"].services-v2-hero__scroll');
    if (!scrollLink || !root.contains(scrollLink)) return;

    const targetSelector = scrollLink.getAttribute('data-scroll-to')
      || scrollLink.getAttribute('href')
      || '';
    const didScroll = scrollToPublicTarget(targetSelector);
    if (!didScroll) return;

    e.preventDefault();
    e.stopPropagation();
  }, true);

  // Event delegation
  root.addEventListener('click', (e) => {
    const actionEl = e.target.closest('[data-action]');
    if (!actionEl || !root.contains(actionEl)) return;

    const action = actionEl.getAttribute('data-action');

    if (action === 'nav-toggle') {
      e.preventDefault();
      toggleNav();
      return;
    }

    if (action === 'toggle-dark') {
      e.preventDefault();
      toggleDark();
      return;
    }

    if (action === 'nav-close') {
      e.preventDefault();
      setNavOpen(false);
    }
  });

  // Close nav when clicking links
  if (navPanel) {
    navPanel.addEventListener('click', (e) => {
      const link = e.target.closest('a[href]');
      if (!link) return;
      setNavOpen(false);
    });
  }

  // Close nav on Escape
  root.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') setNavOpen(false);
  });

  // Close nav when clicking outside
  document.addEventListener('click', (e) => {
    if (!navPanel || !root.classList.contains('is-nav-open')) return;
    const target = e.target;
    if (!(target instanceof Element)) return;
    if (header && header.contains(target)) return;
    setNavOpen(false);
  });

  // PWA: register service worker (public pages only)
  registerServiceWorkerOnce();
}

function getViewportScrollY() {
  return Math.max(
    window.scrollY || 0,
    window.pageYOffset || 0,
    document.documentElement?.scrollTop || 0,
    document.body?.scrollTop || 0
  );
}

function writeViewportScrollY(nextY) {
  const y = Math.max(0, Math.round(Number(nextY) || 0));
  window.scrollTo(0, y);

  const scrollElement = document.scrollingElement || document.documentElement;
  if (scrollElement) scrollElement.scrollTop = y;
  if (document.documentElement) document.documentElement.scrollTop = y;
  if (document.body) document.body.scrollTop = y;
}

function fastSmoothScrollToY(targetY, duration = 260) {
  const destination = Math.max(0, Math.round(Number(targetY) || 0));
  const startY = getViewportScrollY();
  const distance = destination - startY;

  if (Math.abs(distance) < 2) {
    writeViewportScrollY(destination);
    return;
  }

  const reduceMotion = !!(window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches);

  if (reduceMotion || duration <= 0 || !('requestAnimationFrame' in window)) {
    writeViewportScrollY(destination);
    return;
  }

  const startTime = performance.now();
  const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3);

  const step = (now) => {
    const progress = Math.min(1, (now - startTime) / duration);
    writeViewportScrollY(startY + distance * easeOutCubic(progress));
    if (progress < 1) window.requestAnimationFrame(step);
  };

  window.requestAnimationFrame(step);
}

function initPublicHomeHero(root) {
  const hero = root.querySelector('#ultHeroCarousel');
  if (!hero) return;

  const header = document.querySelector('.public-header');
  const slides = Array.from(hero.querySelectorAll('[data-hero-slide]'));
  const dots = Array.from(hero.querySelectorAll('[data-hero-dot]'));
  if (!slides.length) return;

  const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const delay = reduced ? 0 : 5800;

  let idx = 0;
  let paused = false;
  let timer = null;

  const syncHeroHeight = () => {
    if (!header) return;
    const headerHeight = Math.round(header.getBoundingClientRect().height || 72);
    document.documentElement.style.setProperty('--ult-header-h', `${headerHeight}px`);
  };

  syncHeroHeight();
  window.addEventListener('resize', syncHeroHeight, { passive: true });

  if ('ResizeObserver' in window && header) {
    const ro = new ResizeObserver(syncHeroHeight);
    ro.observe(header);
  }

  const setActive = (next) => {
    idx = (next + slides.length) % slides.length;
    slides.forEach((slide, i) => slide.classList.toggle('is-active', i === idx));
    dots.forEach((dot, i) => {
      const active = i === idx;
      dot.classList.toggle('is-active', active);
      dot.setAttribute('aria-selected', active ? 'true' : 'false');
    });
  };

  const start = () => {
    if (!delay || slides.length < 2) return;
    if (timer) window.clearInterval(timer);
    timer = window.setInterval(() => {
      if (!paused) setActive(idx + 1);
    }, delay);
  };

  dots.forEach((dot) => {
    dot.addEventListener('click', () => {
      const next = Number(dot.dataset.heroDot || 0);
      setActive(next);
      start();
    });
  });

  hero.addEventListener('mouseenter', () => {
    paused = true;
  });
  hero.addEventListener('mouseleave', () => {
    paused = false;
  });

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      if (timer) window.clearInterval(timer);
      timer = null;
      return;
    }
    start();
  });

  setActive(0);
  start();
}

function initPublicHomeAnnouncements(root) {
  const carousel = root.querySelector('[data-ann-carousel]');
  if (!carousel) return;

  const track = carousel.querySelector('[data-ann-track]');
  const prev = carousel.querySelector('[data-ann-prev]');
  const next = carousel.querySelector('[data-ann-next]');
  const dotsWrap = root.querySelector('[data-ann-dots]');
  if (!track || !prev || !next || !dotsWrap) return;

  const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const cards = Array.from(track.querySelectorAll('.ult-ann-card'));
  if (!cards.length) {
    prev.hidden = true;
    next.hidden = true;
    dotsWrap.hidden = true;
    return;
  }

  let currentPage = 0;
  let pageCount = 1;
  let timer = null;
  let paused = false;
  let dots = [];

  const perView = () => {
    if (window.innerWidth <= 720) return 1;
    if (window.innerWidth <= 1100) return 2;
    return 3;
  };

  const getStep = () => {
    const first = cards[0];
    if (!first) return 0;
    const styles = window.getComputedStyle(track);
    const gap = parseFloat(styles.columnGap || styles.gap || '0');
    return first.getBoundingClientRect().width + gap;
  };

  const setPage = (nextPage, smooth = true) => {
    if (!cards.length) return;
    currentPage = (nextPage + pageCount) % pageCount;
    const firstIndex = Math.min(cards.length - 1, currentPage * perView());
    const step = getStep();
    if (step > 0) {
      track.scrollTo({
        left: step * firstIndex,
        behavior: smooth && !reduced ? 'smooth' : 'auto',
      });
    } else {
      track.scrollLeft = 0;
    }

    dots.forEach((dot, idx) => {
      const active = idx === currentPage;
      dot.classList.toggle('is-active', active);
      dot.setAttribute('aria-selected', active ? 'true' : 'false');
    });
  };

  const rebuildDots = () => {
    const shown = perView();
    pageCount = Math.max(1, Math.ceil(cards.length / shown));
    dotsWrap.innerHTML = '';
    dots = [];

    for (let i = 0; i < pageCount; i += 1) {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.className = 'ult-ann-dot';
      dot.setAttribute('aria-label', `Page ${i + 1}`);
      dot.setAttribute('aria-selected', i === currentPage ? 'true' : 'false');
      if (i === currentPage) dot.classList.add('is-active');
      dot.addEventListener('click', () => setPage(i));
      dotsWrap.appendChild(dot);
      dots.push(dot);
    }

    const shouldControl = pageCount > 1;
    prev.hidden = !shouldControl;
    next.hidden = !shouldControl;
    dotsWrap.hidden = !shouldControl;
    setPage(Math.min(currentPage, pageCount - 1), false);
  };

  const start = () => {
    if (reduced || pageCount < 2) return;
    if (timer) window.clearInterval(timer);
    timer = window.setInterval(() => {
      if (!paused) setPage(currentPage + 1);
    }, 6200);
  };

  prev.addEventListener('click', () => {
    setPage(currentPage - 1);
    start();
  });

  next.addEventListener('click', () => {
    setPage(currentPage + 1);
    start();
  });

  carousel.addEventListener('mouseenter', () => {
    paused = true;
  });
  carousel.addEventListener('mouseleave', () => {
    paused = false;
  });

  let scrollTicking = false;
  track.addEventListener('scroll', () => {
    if (scrollTicking) return;
    scrollTicking = true;
    requestAnimationFrame(() => {
      const step = getStep();
      if (step > 0) {
        const index = Math.round(track.scrollLeft / step);
        const page = Math.round(index / Math.max(1, perView()));
        if (page >= 0 && page < pageCount && page !== currentPage) {
          currentPage = page;
          dots.forEach((dot, idx) => {
            const active = idx === currentPage;
            dot.classList.toggle('is-active', active);
            dot.setAttribute('aria-selected', active ? 'true' : 'false');
          });
        }
      }
      scrollTicking = false;
    });
  }, { passive: true });

  let resizeTimer;
  window.addEventListener('resize', () => {
    window.clearTimeout(resizeTimer);
    resizeTimer = window.setTimeout(() => {
      rebuildDots();
      start();
    }, 120);
  }, { passive: true });

  rebuildDots();
  start();
}

function initPublicHomeReveal(root) {
  const items = Array.from(root.querySelectorAll('.ult-reveal'));
  if (!items.length) return;

  const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduced || !('IntersectionObserver' in window)) {
    items.forEach((item) => item.classList.add('is-visible'));
    return;
  }

  const io = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('is-visible');
      io.unobserve(entry.target);
    });
  }, {
    rootMargin: '0px 0px -8% 0px',
    threshold: 0.15,
  });

  items.forEach((item, idx) => {
    item.style.setProperty('--ult-reveal-delay', `${Math.min(140, idx * 20)}ms`);
    io.observe(item);
  });
}

function initPublicHomeServices(root) {
  const marquee = root.querySelector('[data-service-marquee]');
  if (!marquee) return;

  const motionQuery = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : null;
  const syncMotionPreference = () => {
    if (motionQuery?.matches) {
      marquee.classList.add('is-paused');
      return;
    }

    marquee.classList.toggle('is-paused', document.visibilityState !== 'visible');
  };

  marquee.addEventListener('mouseenter', () => marquee.classList.add('is-paused'));
  marquee.addEventListener('mouseleave', () => syncMotionPreference());
  marquee.addEventListener('focusin', () => marquee.classList.add('is-paused'));
  marquee.addEventListener('focusout', () => syncMotionPreference());
  document.addEventListener('visibilitychange', syncMotionPreference);

  if (motionQuery) {
    if (typeof motionQuery.addEventListener === 'function') {
      motionQuery.addEventListener('change', syncMotionPreference);
    } else if (typeof motionQuery.addListener === 'function') {
      motionQuery.addListener(syncMotionPreference);
    }
  }

  syncMotionPreference();
}

function initPublicHomeScrollPerformance(root) {
  if (!root || root.dataset.publicHomeScrollPerfBound === '1') return;
  root.dataset.publicHomeScrollPerfBound = '1';

  let clearTimer = null;
  const setScrolling = () => {
    root.classList.add('is-scrolling');
    window.clearTimeout(clearTimer);
    clearTimer = window.setTimeout(() => {
      root.classList.remove('is-scrolling');
    }, 140);
  };

  window.addEventListener('scroll', setScrolling, { passive: true });
  window.addEventListener('wheel', setScrolling, { passive: true });
  window.addEventListener('touchmove', setScrolling, { passive: true });
}

function initPublicHome(root) {
  if (!root || root.dataset.publicHomeBound === '1') return;
  root.dataset.publicHomeBound = '1';

  initPublicHomeHero(root);
  initPublicHomeAnnouncements(root);
  initPublicHomeServices(root);
  initPublicHomeScrollPerformance(root);
  initPublicHomeReveal(root);
}

function initPublicServiceShow(root) {
  if (!root) return;

  const header = document.querySelector('.public-header');
  const syncHeaderHeight = () => {
    if (!header) return;
    const headerHeight = Math.round(header.getBoundingClientRect().height || 72);
    document.documentElement.style.setProperty('--ult-header-h', `${headerHeight}px`);
  };

  syncHeaderHeight();
  window.addEventListener('resize', syncHeaderHeight, { passive: true });

  if ('ResizeObserver' in window && header) {
    const ro = new ResizeObserver(syncHeaderHeight);
    ro.observe(header);
  }

  const prefersReducedMotion = !!(window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches);

  const scrollToTarget = (targetSelector, smooth = true) => {
    if (!targetSelector) return false;

    let selector = String(targetSelector).trim();
    if (!selector) return false;

    if (!selector.startsWith('#')) {
      const hashIndex = selector.indexOf('#');
      if (hashIndex >= 0) selector = selector.slice(hashIndex);
    }
    if (!selector.startsWith('#')) return false;

    let target = null;
    try {
      target = root.querySelector(selector) || document.querySelector(selector);
    } catch {
      target = null;
    }
    if (!target) return false;

    syncHeaderHeight();
    const headerHeight = Math.round((header ? header.getBoundingClientRect().height : 72) || 72);
    const targetY = target.getBoundingClientRect().top + window.scrollY - headerHeight - 14;
    if (!Number.isFinite(targetY)) return false;

    if (smooth && !prefersReducedMotion) fastSmoothScrollToY(targetY);
    else window.scrollTo(0, Math.max(0, targetY));

    return true;
  };

  const heroScrollBtn = root.querySelector('.services-v2-hero__scroll');
  if (heroScrollBtn && heroScrollBtn.dataset.serviceShowScrollBound !== '1') {
    heroScrollBtn.dataset.serviceShowScrollBound = '1';

    heroScrollBtn.addEventListener('click', (event) => {
      const targetSelector = heroScrollBtn.getAttribute('data-scroll-to')
        || heroScrollBtn.getAttribute('href')
        || '#service-show-content';
      if (!targetSelector) return;

      const didScroll = scrollToTarget(targetSelector, true);
      if (!didScroll) return;

      event.preventDefault();
    });
  }

  const revealItems = Array.from(root.querySelectorAll('[data-service-show-reveal]'));
  if (!revealItems.length) return;

  const revealAll = () => {
    revealItems.forEach((el) => el.classList.add('is-visible'));
  };

  if (prefersReducedMotion || !('IntersectionObserver' in window)) {
    revealAll();
    return;
  }

  revealItems.forEach((el, idx) => {
    const delay = Math.min(35 * idx, 220);
    el.style.setProperty('--ss-reveal-delay', `${delay}ms`);
  });

  const io = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('is-visible');
      io.unobserve(entry.target);
    });
  }, {
    threshold: 0.18,
    rootMargin: '0px 0px -8% 0px',
  });

  revealItems.forEach((el) => io.observe(el));
}

function initPublicServicesIndex(root) {
  if (!root) return;

  const searchInput = root.querySelector('#services-q');
  const categorySelect = root.querySelector('#services-category');
  const clearButton = root.querySelector('[data-services-clear-search]');

  if (searchInput && categorySelect && clearButton && clearButton.dataset.servicesClearBound !== '1') {
    clearButton.dataset.servicesClearBound = '1';

    const isCategoryDefault = () => !String(categorySelect.value || '').trim();
    const hasKeyword = () => !!String(searchInput.value || '').trim();

    const syncClearState = () => {
      clearButton.classList.toggle('services-v2-search__clear--disabled', !hasKeyword() && isCategoryDefault());
    };

    clearButton.addEventListener('click', () => {
      if (!hasKeyword() && isCategoryDefault()) return;

      if (!isCategoryDefault()) {
        const resetUrl = clearButton.getAttribute('data-reset-url') || '';
        if (resetUrl) window.location.href = resetUrl;
        return;
      }

      searchInput.value = '';
      searchInput.dispatchEvent(new Event('input', { bubbles: true }));
      searchInput.dispatchEvent(new Event('change', { bubbles: true }));
      syncClearState();
    });

    searchInput.addEventListener('input', syncClearState, { passive: true });
    categorySelect.addEventListener('change', syncClearState, { passive: true });
    syncClearState();
  }

  if (searchInput && !categorySelect && clearButton && clearButton.dataset.servicesClearBound !== '1') {
    clearButton.dataset.servicesClearBound = '1';

    const syncClearState = () => {
      clearButton.classList.toggle('services-v2-search__clear--disabled', !String(searchInput.value || '').trim());
    };

    clearButton.addEventListener('click', () => {
      if (!String(searchInput.value || '').trim()) return;
      searchInput.value = '';
      searchInput.dispatchEvent(new Event('input', { bubbles: true }));
      searchInput.dispatchEvent(new Event('change', { bubbles: true }));
      searchInput.focus();
      syncClearState();
    });

    searchInput.addEventListener('input', syncClearState, { passive: true });
    syncClearState();
  }

  const hero = root.querySelector('.services-v2-hero');
  if (hero) {
    const heroImage = String(hero.getAttribute('data-services-hero-image') || '').trim();
    if (heroImage) {
      const escapedHeroImage = heroImage.replace(/\\/g, '\\\\').replace(/"/g, '\\"');
      hero.style.setProperty('--services-hero-image', `url("${escapedHeroImage}")`);
    }
  }

  const header = document.querySelector('.public-header');
  const syncHeaderHeight = () => {
    if (!header) return;
    const headerHeight = Math.round(header.getBoundingClientRect().height || 72);
    document.documentElement.style.setProperty('--ult-header-h', `${headerHeight}px`);
  };

  syncHeaderHeight();
  window.addEventListener('resize', syncHeaderHeight, { passive: true });

  if ('ResizeObserver' in window && header) {
    const ro = new ResizeObserver(syncHeaderHeight);
    ro.observe(header);
  }

  const prefersReducedMotion = !!(window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches);

  const scrollToTarget = (targetSelector, smooth = true) => {
    if (!targetSelector) return false;

    let selector = String(targetSelector).trim();
    if (!selector) return false;

    if (!selector.startsWith('#')) {
      const hashIndex = selector.indexOf('#');
      if (hashIndex >= 0) selector = selector.slice(hashIndex);
    }
    if (!selector.startsWith('#')) return false;

    let target = null;
    try {
      target = root.querySelector(selector) || document.querySelector(selector);
    } catch {
      target = null;
    }
    if (!target) return false;

    syncHeaderHeight();
    const headerHeight = Math.round((header ? header.getBoundingClientRect().height : 72) || 72);
    const targetY = target.getBoundingClientRect().top + window.scrollY - headerHeight - 14;
    if (!Number.isFinite(targetY)) return false;

    if (smooth && !prefersReducedMotion) fastSmoothScrollToY(targetY);
    else window.scrollTo(0, Math.max(0, targetY));

    return true;
  };

  const heroScrollBtn = root.querySelector('.services-v2-hero__scroll');
  if (heroScrollBtn && heroScrollBtn.dataset.servicesIndexScrollBound !== '1') {
    heroScrollBtn.dataset.servicesIndexScrollBound = '1';

    heroScrollBtn.addEventListener('click', (event) => {
      const targetSelector = heroScrollBtn.getAttribute('data-scroll-to')
        || heroScrollBtn.getAttribute('href')
        || '#services-catalog-section';
      if (!targetSelector) return;

      const didScroll = scrollToTarget(targetSelector, true);
      if (!didScroll) return;

      event.preventDefault();
    });
  }

  const revealItems = Array.from(root.querySelectorAll('[data-services-reveal]'));
  if (!revealItems.length) return;

  if (prefersReducedMotion || !('IntersectionObserver' in window)) {
    revealItems.forEach((item) => item.classList.add('is-visible'));
    return;
  }

  revealItems.forEach((item, idx) => {
    item.style.setProperty('--services-reveal-delay', `${Math.min(160, idx * 24)}ms`);
  });

  const io = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('is-visible');
      io.unobserve(entry.target);
    });
  }, {
    threshold: 0.18,
    rootMargin: '0px 0px -8% 0px',
  });

  revealItems.forEach((item) => io.observe(item));
}

function initInfiniteLists(root = document) {
  root.querySelectorAll('[data-infinite-list]').forEach((host) => {
    if (host.dataset.infiniteBound === '1') return;
    host.dataset.infiniteBound = '1';

    const scopeRoot = host.closest('#servicesIndexPage, #blogIndexPage, #userGuidesIndexPage, .page-announcements-index, .page-student-requests-index') || root;
    const container = scopeRoot.querySelector('[data-infinite-container]');
    const pagination = scopeRoot.querySelector('[data-infinite-pagination]');
    const sentinel = host.querySelector('[data-infinite-sentinel]');
    const loadMoreButton = host.querySelector('[data-infinite-load-more]');
    const status = host.querySelector('[data-infinite-status]');
    const loadingText = String(host.getAttribute('data-loading-text') || 'Loading...').trim();
    const errorText = String(host.getAttribute('data-error-text') || 'Failed to load. Try again.').trim();
    const isAutoOnly = host.getAttribute('data-infinite-auto') === '1';

    if (!container || !sentinel || !loadMoreButton) return;

    let nextPageUrl = String(host.getAttribute('data-next-page-url') || '').trim();
    let loading = false;
    let finished = !nextPageUrl;
    let observer = null;

    const setStatus = (text = '') => {
      if (status) status.textContent = text;
    };

    const updateUi = () => {
      if (pagination) pagination.classList.add('hidden');
      host.classList.remove('hidden');
      loadMoreButton.classList.toggle('hidden', isAutoOnly || finished || !nextPageUrl);
      loadMoreButton.style.display = isAutoOnly ? 'none' : '';
      loadMoreButton.disabled = loading || finished || !nextPageUrl;
      if (!nextPageUrl) {
        finished = true;
        setStatus(String(host.getAttribute('data-end-text') || '').trim());
      } else if (loading && isAutoOnly) {
        setStatus(loadingText);
      } else if (!loading) {
        setStatus('');
      }
    };

    const buildRequestUrl = (rawUrl) => {
      const url = new URL(rawUrl, window.location.origin);
      url.searchParams.set('_infinite', '1');
      return url.toString();
    };

    const appendItems = (html) => {
      if (!html) return;

      const template = document.createElement('template');
      template.innerHTML = html.trim();
      const nodes = Array.from(template.content.childNodes).filter((node) => {
        return node.nodeType !== Node.TEXT_NODE || String(node.textContent || '').trim() !== '';
      });

      const emptyCard = container.querySelector('[data-realtime-search-empty]');
      const anchor = emptyCard || null;

      nodes.forEach((node) => {
        if (node.nodeType === Node.ELEMENT_NODE) {
          node.classList.add('is-visible');
        }
        container.insertBefore(node, anchor);
      });
    };

    const loadNextPage = async () => {
      if (loading || finished || !nextPageUrl) return;

      loading = true;
      setStatus(loadingText);
      updateUi();

      try {
        const response = await fetch(buildRequestUrl(nextPageUrl), {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
        });

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const payload = await response.json();
        appendItems(String(payload.html || ''));
        nextPageUrl = String(payload.next_page_url || '').trim();
        finished = !payload.has_more || !nextPageUrl;
        updateUi();

        const searchInput = scopeRoot.querySelector('[data-realtime-search-input][data-realtime-search-mode="filter"]');
        if (searchInput) {
          searchInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
      } catch (error) {
        setStatus(errorText);
        finished = false;
        // eslint-disable-next-line no-console
        console.error('failed to load infinite list page', error);
      } finally {
        loading = false;
        updateUi();
      }
    };

    loadMoreButton.addEventListener('click', () => {
      void loadNextPage();
    });

    if ('IntersectionObserver' in window) {
      observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          void loadNextPage();
        });
      }, {
        rootMargin: '0px 0px 240px 0px',
      });

      observer.observe(sentinel);
    }

    updateUi();
  });
}

function initPublicAnnouncementsIndex(root) {
  if (!root) return;

  const searchForm = root.querySelector('.services-v2-search__form[role="search"]');
  if (searchForm && searchForm.dataset.announcementsSubmitBound !== '1') {
    searchForm.dataset.announcementsSubmitBound = '1';
    searchForm.addEventListener('submit', (event) => {
      event.preventDefault();
    });
  }

  const searchInput = root.querySelector(
    '[data-realtime-search-input][data-realtime-search-mode="filter"]'
  );
  const clearButton = root.querySelector('[data-announcements-clear-search]');

  if (searchInput && clearButton && clearButton.dataset.announcementsClearBound !== '1') {
    clearButton.dataset.announcementsClearBound = '1';

    const syncClearState = () => {
      clearButton.classList.toggle('services-v2-search__clear--disabled', !String(searchInput.value || '').trim());
    };

    clearButton.addEventListener('click', () => {
      if (!String(searchInput.value || '').trim()) return;
      searchInput.value = '';
      searchInput.dispatchEvent(new Event('input', { bubbles: true }));
      searchInput.dispatchEvent(new Event('change', { bubbles: true }));
      searchInput.focus();
      syncClearState();
    });

    searchInput.addEventListener('input', syncClearState, { passive: true });
    syncClearState();
  }
}

function initPublicBlogIndex(root) {
  if (!root) return;

  const hero = root.querySelector('.services-v2-hero');
  if (hero) {
    const heroImage = String(hero.getAttribute('data-services-hero-image') || '').trim();
    if (heroImage) {
      const escapedHeroImage = heroImage.replace(/\\/g, '\\\\').replace(/"/g, '\\"');
      hero.style.setProperty('--services-hero-image', `url("${escapedHeroImage}")`);
    }
  }

  const header = document.querySelector('.public-header');
  const syncHeaderHeight = () => {
    if (!header) return;
    const headerHeight = Math.round(header.getBoundingClientRect().height || 72);
    document.documentElement.style.setProperty('--ult-header-h', `${headerHeight}px`);
  };

  syncHeaderHeight();
  window.addEventListener('resize', syncHeaderHeight, { passive: true });

  if ('ResizeObserver' in window && header) {
    const ro = new ResizeObserver(syncHeaderHeight);
    ro.observe(header);
  }

  const prefersReducedMotion = !!(window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches);

  const scrollToTarget = (targetSelector, smooth = true) => {
    if (!targetSelector) return false;

    let selector = String(targetSelector).trim();
    if (!selector) return false;

    if (!selector.startsWith('#')) {
      const hashIndex = selector.indexOf('#');
      if (hashIndex >= 0) selector = selector.slice(hashIndex);
    }
    if (!selector.startsWith('#')) return false;

    let target = null;
    try {
      target = root.querySelector(selector) || document.querySelector(selector);
    } catch {
      target = null;
    }
    if (!target) return false;

    syncHeaderHeight();
    const headerHeight = Math.round((header ? header.getBoundingClientRect().height : 72) || 72);
    const targetY = target.getBoundingClientRect().top + window.scrollY - headerHeight - 14;
    if (!Number.isFinite(targetY)) return false;

    if (smooth && !prefersReducedMotion) fastSmoothScrollToY(targetY);
    else window.scrollTo(0, Math.max(0, targetY));

    return true;
  };

  const heroScrollBtn = root.querySelector('.services-v2-hero__scroll');
  if (heroScrollBtn && heroScrollBtn.dataset.blogIndexScrollBound !== '1') {
    heroScrollBtn.dataset.blogIndexScrollBound = '1';

    heroScrollBtn.addEventListener('click', (event) => {
      const targetSelector = heroScrollBtn.getAttribute('data-scroll-to')
        || heroScrollBtn.getAttribute('href')
        || '#blog-catalog-section';
      if (!targetSelector) return;

      const didScroll = scrollToTarget(targetSelector, true);
      if (!didScroll) return;

      event.preventDefault();
    });
  }

  const searchForm = root.querySelector('.services-v2-search__form[role="search"]');
  if (searchForm && searchForm.dataset.blogSubmitBound !== '1') {
    searchForm.dataset.blogSubmitBound = '1';
    searchForm.addEventListener('submit', (event) => {
      event.preventDefault();
    });
  }

  const searchInput = root.querySelector(
    '[data-realtime-search-input][data-realtime-search-mode="filter"]'
  );
  const clearButton = root.querySelector('[data-blog-clear-search]');

  if (searchInput && clearButton && clearButton.dataset.blogClearBound !== '1') {
    clearButton.dataset.blogClearBound = '1';

    const syncClearState = () => {
      clearButton.classList.toggle('services-v2-search__clear--disabled', !String(searchInput.value || '').trim());
    };

    clearButton.addEventListener('click', () => {
      if (!String(searchInput.value || '').trim()) return;
      searchInput.value = '';
      searchInput.dispatchEvent(new Event('input', { bubbles: true }));
      searchInput.dispatchEvent(new Event('change', { bubbles: true }));
      searchInput.focus();
      syncClearState();
    });

    searchInput.addEventListener('input', syncClearState, { passive: true });
    syncClearState();
  }

  const revealItems = Array.from(root.querySelectorAll('[data-services-reveal]'));
  if (!revealItems.length) return;

  if (prefersReducedMotion || !('IntersectionObserver' in window)) {
    revealItems.forEach((item) => item.classList.add('is-visible'));
    return;
  }

  revealItems.forEach((item, idx) => {
    item.style.setProperty('--services-reveal-delay', `${Math.min(160, idx * 24)}ms`);
  });

  const io = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add('is-visible');
      io.unobserve(entry.target);
    });
  }, {
    threshold: 0.18,
    rootMargin: '0px 0px -8% 0px',
  });

  revealItems.forEach((item) => io.observe(item));
}

function initStudentRequestsIndex(root) {
  if (!root) return;

  const searchInput = root.querySelector('#student-requests-live-search');
  const statusSelect = root.querySelector('#student-requests-status');
  const serviceSelect = root.querySelector('#student-requests-service');
  const clearButton = root.querySelector('[data-student-requests-clear-search]');
  const countEl = root.querySelector('[data-realtime-search-count]');
  const grid = root.querySelector('.student-requests-grid');
  const emptyEl = grid ? grid.querySelector('[data-realtime-search-empty]') : null;
  const items = grid ? Array.from(grid.querySelectorAll('[data-realtime-search-item]')) : [];
  const normalize = (v) => String(v || '')
    .toLowerCase()
    .normalize('NFD')
    // eslint-disable-next-line no-control-regex
    .replace(/[\u0300-\u036f]/g, '')
    .trim();

  const setShown = (el, shown) => {
    el.classList.toggle('hidden', !shown);
    if (!shown) el.setAttribute('hidden', 'hidden');
    else el.removeAttribute('hidden');
    el.style.display = shown ? '' : 'none';
  };

  const defaultCountText = countEl
    ? String(countEl.getAttribute('data-default-count-text') || countEl.textContent || '').trim()
    : '';

  const applyLocalSearch = () => {
    if (!searchInput || !grid || !items.length) return;

    const q = normalize(searchInput.value);
    let shown = 0;

    items.forEach((el) => {
      const hay = normalize(el.getAttribute('data-realtime-search-text') || el.textContent || '');
      const match = q === '' || hay.includes(q);
      setShown(el, match);
      if (match) shown += 1;
    });

    if (emptyEl) setShown(emptyEl, shown === 0 && q !== '');

    if (countEl) {
      countEl.textContent = q === ''
        ? defaultCountText
        : `Menampilkan ${shown} dari ${items.length}`;
    }
  };

  if (searchInput && countEl) {
    applyLocalSearch();
  }

  if (searchInput && statusSelect && serviceSelect && clearButton && clearButton.dataset.studentRequestsClearBound !== '1') {
    clearButton.dataset.studentRequestsClearBound = '1';

    const hasKeyword = () => !!String(searchInput.value || '').trim();
    const hasFilters = () => !!String(statusSelect.value || '').trim() || !!String(serviceSelect.value || '').trim();
    const hasActiveQueryFilters = () => {
      const params = new URLSearchParams(window.location.search || '');
      return ['status', 'service_id'].some((key) => !!String(params.get(key) || '').trim());
    };

    const syncClearState = () => {
      clearButton.classList.toggle('student-requests-search__clear--disabled', !hasKeyword() && !hasFilters() && !hasActiveQueryFilters());
    };

    clearButton.addEventListener('click', () => {
      if (!hasKeyword() && !hasFilters() && !hasActiveQueryFilters()) return;

      if (hasFilters() || hasActiveQueryFilters()) {
        const resetUrl = clearButton.getAttribute('data-reset-url') || '';
        if (resetUrl) window.location.href = resetUrl;
        return;
      }

      searchInput.value = '';
      searchInput.dispatchEvent(new Event('input', { bubbles: true }));
      searchInput.dispatchEvent(new Event('change', { bubbles: true }));
      searchInput.focus();
      applyLocalSearch();
      syncClearState();
    });

    searchInput.addEventListener('input', () => {
      applyLocalSearch();
      syncClearState();
    }, { passive: true });
    statusSelect.addEventListener('change', syncClearState, { passive: true });
    serviceSelect.addEventListener('change', syncClearState, { passive: true });
    applyLocalSearch();
    syncClearState();
  }
}

function initAuthPages() {
  const roots = document.querySelectorAll(
    '.page-auth-login, .page-auth-register, .page-auth-forgot-password, .page-auth-reset-password, .page-auth-verify-email'
  );
  if (!roots.length) return;

  const getRootType = (root) => {
    if (root.classList.contains('page-auth-register')) return 'register';
    if (root.classList.contains('page-auth-reset-password')) return 'reset';
    if (root.classList.contains('page-auth-login')) return 'login';
    return 'other';
  };

  const makePassword = (length = 14) => {
    const lowers = 'abcdefghijkmnopqrstuvwxyz';
    const uppers = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    const digits = '23456789';
    const symbols = '!@#$%*_-+?';

    const pick = (str) => str[Math.floor(Math.random() * str.length)];
    const all = lowers + uppers + digits + symbols;

    const out = [
      pick(lowers),
      pick(uppers),
      pick(digits),
      pick(symbols),
    ];

    while (out.length < length) out.push(pick(all));

    // shuffle
    for (let i = out.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [out[i], out[j]] = [out[j], out[i]];
    }

    return out.join('');
  };

  const setInputValue = (input, value) => {
    input.value = value;
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
  };

  const ensureId = (input) => {
    if (input.id) return input.id;
    const id = `auth-${(input.name || 'field')}-${crypto.randomUUID()}`;
    input.id = id;
    return id;
  };

  const decoratePasswordInput = (root, input) => {
    const id = ensureId(input);

    if (root.querySelector(`.auth-password-toggle[data-target="${CSS.escape(id)}"]`)) {
      input.dataset.authPassword = '1';
      return;
    }

    // Prefer explicit placeholder container near the field (more stable than DOM heuristics).
    const tools =
      root.querySelector(`[data-auth-tools-for="${CSS.escape(input.name || '')}"]`) ||
      root.querySelector(`[data-auth-tools-for="${CSS.escape(id)}"]`);

    // Fallback: Try to find the <div class="space-y-1"> wrapper from <x-input>
    const wrap = input.closest('div');
    if (!wrap && !tools) return;

    if (wrap) wrap.classList.add('auth-input-wrap');
    input.dataset.authPassword = '1';

    // Avoid duplicate decoration
    if (
      (tools && tools.querySelector(`[data-auth-action="toggle-password"][data-target="${CSS.escape(id)}"]`)) ||
      (wrap && wrap.querySelector(`[data-auth-action="toggle-password"][data-target="${CSS.escape(id)}"]`))
    ) return;

    const controls = document.createElement('div');
    controls.className = 'auth-pass-row';

    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'auth-toolbtn auth-toolbtn--toggle';
    toggle.dataset.authAction = 'toggle-password';
    toggle.dataset.target = id;
    toggle.setAttribute('aria-pressed', 'false');
    toggle.setAttribute('aria-label', 'Tampilkan password');

    controls.appendChild(toggle);

    if (tools) {
      tools.appendChild(controls);
    } else if (wrap) {
      wrap.appendChild(controls);
    }
  };

  const decorateGenerator = (root) => {
    const passwordInput = root.querySelector('input[name="password"]');
    if (!passwordInput) return;

    const id = ensureId(passwordInput);
    const tools =
      root.querySelector('[data-auth-tools-for="password"]') ||
      root.querySelector(`[data-auth-tools-for="${CSS.escape(id)}"]`);

    const wrap = passwordInput.closest('.auth-input-wrap') || passwordInput.closest('div');
    const host = tools || wrap;
    if (!host) return;

    if (host.querySelector('[data-auth-action="generate-password"]')) return;

    const generator = document.createElement('div');
    generator.className = 'auth-pass-row auth-pass-row--tools';

    const genBtn = document.createElement('button');
    genBtn.type = 'button';
    genBtn.className = 'auth-toolbtn auth-toolbtn--primary';
    genBtn.dataset.authAction = 'generate-password';
    genBtn.dataset.target = ensureId(passwordInput);
    genBtn.textContent = 'Buat password otomatis';

    const copyBtn = document.createElement('button');
    copyBtn.type = 'button';
    copyBtn.className = 'auth-toolbtn auth-toolbtn--ghost';
    copyBtn.dataset.authAction = 'copy-password';
    copyBtn.dataset.target = ensureId(passwordInput);
    copyBtn.textContent = 'Salin';

    const hint = document.createElement('div');
    hint.className = 'auth-pass-hint';
    hint.dataset.authGeneratedHint = '1';
    hint.hidden = true;
    hint.textContent = 'Password dibuat dan diisikan otomatis.';

    generator.appendChild(genBtn);
    generator.appendChild(copyBtn);

    host.appendChild(generator);
    host.appendChild(hint);
  };

  roots.forEach((root) => {
    const type = getRootType(root);

    root.querySelectorAll('input[type="password"]').forEach((input) => decoratePasswordInput(root, input));

    if (type === 'register' || type === 'reset') {
      decorateGenerator(root);
    }

    // Event delegation
    root.addEventListener('click', async (e) => {
      const btn = e.target.closest('[data-auth-action]');
      if (!btn || !root.contains(btn)) return;

      const action = btn.getAttribute('data-auth-action');
      const targetId = btn.getAttribute('data-target');
      const input = targetId ? root.querySelector(`#${CSS.escape(targetId)}`) : null;

      if (action === 'toggle-password' && input) {
        e.preventDefault();
        const nextType = input.type === 'password' ? 'text' : 'password';
        input.type = nextType;
        const isShown = nextType === 'text';
        btn.setAttribute('aria-pressed', isShown ? 'true' : 'false');

        const label = btn.querySelector('[data-auth-toggle-text]');
        if (label) {
          label.textContent = isShown ? 'Sembunyikan' : 'Tampilkan';
        }

        btn.setAttribute('aria-label', isShown ? 'Sembunyikan password' : 'Tampilkan password');
        input.focus({ preventScroll: true });
        return;
      }

      if (action === 'generate-password' && input) {
        e.preventDefault();
        const pw = makePassword(14);
        setInputValue(input, pw);

        const confirm = root.querySelector('input[name="password_confirmation"]');
        if (confirm) setInputValue(confirm, pw);

        const hintHost =
          root.querySelector('[data-auth-tools-for="password"]') ||
          input.closest('div');
        const hint = hintHost?.querySelector?.('[data-auth-generated-hint]');
        if (hint) {
          hint.hidden = false;
          window.clearTimeout(hint._t);
          hint._t = window.setTimeout(() => {
            hint.hidden = true;
          }, 2600);
        }

        const genText = btn.querySelector('.auth-toolbtn__text');
        if (genText) {
          const prev = genText.textContent;
          genText.textContent = 'Dibuat';
          window.clearTimeout(btn._t);
          btn._t = window.setTimeout(() => {
            genText.textContent = prev;
          }, 1400);
        }
        return;
      }

      if (action === 'copy-password' && input) {
        e.preventDefault();
        const value = input.value || '';
        if (!value) return;

        try {
          await navigator.clipboard.writeText(value);
        } catch {
          input.focus({ preventScroll: true });
          input.select?.();
          document.execCommand?.('copy');
        }

        const copyText = btn.querySelector('[data-auth-copy-text]') || btn.querySelector('.auth-toolbtn__text');
        if (copyText) {
          const prev = copyText.textContent;
          copyText.textContent = 'Tersalin';
          window.clearTimeout(btn._t);
          btn._t = window.setTimeout(() => {
            copyText.textContent = prev;
          }, 1400);
        }
      }
    });
  });
}

let swRegistered = false;
function registerServiceWorkerOnce() {
  if (swRegistered) return;
  swRegistered = true;

  if (!('serviceWorker' in navigator)) return;
  const pwaEnabled = false;
  const host = (window.location.hostname || '').toLowerCase();
  const isLocalDev =
    host === 'localhost' ||
    host === '127.0.0.1' ||
    host.endsWith('.test') ||
    host.endsWith('.local');

  const cleanupServiceWorkers = () => {
    const cleanupKey = 'ult_sw_disabled_cleanup_v1';
    if (sessionStorage.getItem(cleanupKey) === '1') return;

    const runCleanup = () => {
      Promise.allSettled([
        navigator.serviceWorker.getRegistrations?.().then((regs) => {
          regs.forEach((reg) => reg.unregister());
        }).catch(() => {}),
        'caches' in window
          ? caches.keys().then((keys) => {
              keys
                .filter((k) => k.startsWith('ultfkip-'))
                .forEach((k) => caches.delete(k));
            }).catch(() => {})
          : Promise.resolve(),
      ]).finally(() => {
        sessionStorage.setItem(cleanupKey, '1');
      });
    };

    if ('requestIdleCallback' in window) {
      window.requestIdleCallback(runCleanup, { timeout: 1600 });
    } else {
      window.setTimeout(runCleanup, 700);
    }
  };

  if (!pwaEnabled) {
    cleanupServiceWorkers();
    return;
  }

  // Avoid stale-cache issues during local development.
  if (isLocalDev) {
    const cleanupKey = 'ult_sw_local_cleaned_v1';
    if (sessionStorage.getItem(cleanupKey) !== '1') {
      navigator.serviceWorker.getRegistrations?.().then((regs) => {
        regs.forEach((reg) => reg.unregister());
      }).catch(() => {});
      if ('caches' in window) {
        caches.keys().then((keys) => {
          keys
            .filter((k) => k.startsWith('ultfkip-'))
            .forEach((k) => caches.delete(k));
        }).catch(() => {});
      }
      sessionStorage.setItem(cleanupKey, '1');
    }
    return;
  }

  const promoteWaitingWorker = (registration) => {
    if (!registration?.waiting) return;
    registration.waiting.postMessage({ type: 'SKIP_WAITING' });
  };

  const buildServiceWorkerUrl = () => {
    const moduleScript = document.querySelector('script[type="module"][src*="/build/assets/app-"]');
    const src = moduleScript?.getAttribute('src') || '';
    const match = src.match(/app-([A-Za-z0-9_-]+)\.js$/);
    if (!match?.[1]) return '/sw.js';
    return `/sw.js?v=${encodeURIComponent(match[1])}`;
  };

  window.addEventListener('load', () => {
    let didRefreshForNewController = false;

    navigator.serviceWorker.addEventListener('controllerchange', () => {
      if (didRefreshForNewController) return;
      didRefreshForNewController = true;
      window.location.reload();
    });

    navigator.serviceWorker.register(buildServiceWorkerUrl(), { updateViaCache: 'none' })
      .then((registration) => {
        promoteWaitingWorker(registration);

        registration.addEventListener('updatefound', () => {
          const worker = registration.installing;
          if (!worker) return;
          worker.addEventListener('statechange', () => {
            if (worker.state !== 'installed') return;
            promoteWaitingWorker(registration);
          });
        });

        const refreshWorker = () => registration.update().catch(() => {});
        refreshWorker();
        window.setInterval(refreshWorker, 5 * 60 * 1000);
        document.addEventListener('visibilitychange', () => {
          if (document.visibilityState === 'visible') {
            refreshWorker();
          }
        }, { passive: true });
      })
      .catch(() => {});
  }, { once: true });
}

function initAppShell(root) {
  if (!root) return;
  registerServiceWorkerOnce();

  root.addEventListener('click', (e) => {
    const link = e.target.closest('a[href]');
    if (!link || !root.contains(link)) return;
    window.Alpine?.store?.('ui')?.closeSidebar?.();
  });

  root.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    window.Alpine?.store?.('ui')?.closeSidebar?.();
  });
}

function initConfirmDialogs(root) {
  if (!root) return;

  root.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-confirm]');
    if (!btn || !root.contains(btn)) return;

    const message = btn.getAttribute('data-confirm') || 'Are you sure?';
    if (!window.confirm(message)) {
      e.preventDefault();
      e.stopImmediatePropagation();
    }
  }, { capture: true });
}

function ensureCertificateSignerEditorFactory() {
  if (typeof window.certificateSignerEditor === 'function') return;

  window.certificateSignerEditor = function(initialSigners, internalSignerProfiles, pemohonSignerProfile, options) {
    const normalizeType = (value) => {
      const t = String(value || '').toLowerCase();
      if (['internal', 'pemohon', 'custom'].includes(t)) return t;
      return 'internal';
    };

    const clean = (value) => String(value ?? '').trim();
    const emptyLocks = () => ({
      name: false,
      id_number: false,
      jabatan: false,
    });

    const signerProfileMap = {};
    const profileRows = Array.isArray(internalSignerProfiles) ? internalSignerProfiles : [];
    profileRows.forEach((row) => {
      const id = clean(row?.id);
      if (id === '') return;
      signerProfileMap[id] = {
        name: clean(row?.name),
        id_number: clean(row?.id_number),
        jabatan: clean(row?.jabatan),
      };
    });

    const pemohonProfile = {
      name: clean(pemohonSignerProfile?.name),
      id_number: clean(pemohonSignerProfile?.id_number),
      jabatan: clean(pemohonSignerProfile?.jabatan),
    };
    const sourcePreviewEndpoint = clean(options?.source_preview_url);

    const normalize = (row) => ({
      type: normalizeType(row?.type),
      last_type: normalizeType(row?.type),
      internal_user_id: row?.internal_user_id ? String(row.internal_user_id) : '',
      name: row?.name ? String(row.name) : '',
      id_number: row?.id_number ? String(row.id_number) : '',
      jabatan: row?.jabatan ? String(row.jabatan) : '',
      signature_preview_url: row?.signature_preview_url ? String(row.signature_preview_url) : '',
      signature_live_preview_url: '',
      locked: emptyLocks(),
    });

    const resetAutofilledFields = (signer) => {
      signer.name = '';
      signer.id_number = '';
      signer.jabatan = '';
      signer.locked = emptyLocks();
    };

    const applyAutofillProfile = (signer) => {
      if (!signer || typeof signer !== 'object') return;

      const previousLocks = signer.locked && typeof signer.locked === 'object'
        ? {
            name: !!signer.locked.name,
            id_number: !!signer.locked.id_number,
            jabatan: !!signer.locked.jabatan,
          }
        : emptyLocks();
      signer.locked = emptyLocks();

      const type = normalizeType(signer.type);
      if (!['internal', 'pemohon'].includes(type)) {
        return;
      }

      let profile = null;
      if (type === 'internal') {
        const userId = clean(signer.internal_user_id);
        if (userId === '') {
          return;
        }
        profile = signerProfileMap[userId] ?? null;
      } else if (type === 'pemohon') {
        profile = pemohonProfile;
      }
      if (!profile) {
        return;
      }

      const fields = ['name', 'id_number', 'jabatan'];
      fields.forEach((field) => {
        const profileValue = clean(profile[field]);
        if (profileValue !== '') {
          signer[field] = profileValue;
          signer.locked[field] = true;
          return;
        }

        if (previousLocks[field]) {
          signer[field] = '';
        }
        signer.locked[field] = false;
      });
    };

    const base = Array.isArray(initialSigners) ? initialSigners.map(normalize) : [];
    const signers = base.length ? base : [normalize({ type: 'internal' })];
    signers.forEach((row) => applyAutofillProfile(row));

    return {
      signers,
      selectedSourcePptxName: '',
      selectedSourcePptxFile: null,
      sourceServerPreviewBusy: false,
      sourceServerPreviewError: '',
      sourceServerPreviewToken: 0,
      init() {
        window.addEventListener(
          'beforeunload',
          () => {
            this.clearSelectedSourcePptxPreview();
            this.revokeAllSignatureBlobUrls();
          },
          { once: true }
        );
      },
      clearSelectedSourcePptxPreview() {
        this.sourceServerPreviewToken += 1;
        this.sourceServerPreviewBusy = false;
        this.sourceServerPreviewError = '';
        this.selectedSourcePptxName = '';
        this.selectedSourcePptxFile = null;
      },
      onSourcePptxChange(event) {
        this.clearSelectedSourcePptxPreview();
        const input = event?.target;
        const file = input?.files?.[0] ?? null;
        if (!file) return;

        const rawName = clean(file.name);
        this.selectedSourcePptxName = rawName !== '' ? rawName : 'Dokumen sumber terpilih';
        this.selectedSourcePptxFile = file;
      },
      hasSelectedSourcePptx() {
        return clean(this.selectedSourcePptxName) !== '';
      },
      canPreviewSourceOnServer() {
        return !this.sourceServerPreviewBusy
          && this.selectedSourcePptxFile instanceof File
          && sourcePreviewEndpoint !== '';
      },
      async previewSourcePptxOnServer() {
        if (!this.canPreviewSourceOnServer()) return;

        const form = this.$root?.closest('form');
        if (!form) return;

        const csrf = form.querySelector('input[name=\"_token\"]')?.value || '';
        const serviceId = form.querySelector('input[name=\"service_id\"]')?.value || '';
        const file = this.selectedSourcePptxFile;
        if (!(file instanceof File) || csrf === '' || serviceId === '') return;

        const body = new FormData();
        body.append('_token', csrf);
        body.append('service_id', String(serviceId));
        body.append('certificate_source_pptx', file, file.name || 'source.pptx');

        const previewWindow = window.open('', '_blank');
        if (!previewWindow) {
          this.sourceServerPreviewError = 'Browser memblokir tab preview. Izinkan popup lalu coba lagi.';
          return;
        }

        previewWindow.document.title = 'Menyiapkan preview...';
        previewWindow.document.body.innerHTML = '<p style="font-family: Arial, sans-serif; padding: 24px;">Menyiapkan preview dokumen...</p>';

        const token = this.sourceServerPreviewToken + 1;
        this.sourceServerPreviewToken = token;
        this.sourceServerPreviewBusy = true;
        this.sourceServerPreviewError = '';
        try {
          const res = await fetch(sourcePreviewEndpoint, {
            method: 'POST',
            body,
            credentials: 'same-origin',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
            },
          });

          if (!res.ok) {
            let message = '';
            try {
              const json = await res.json();
              if (json && typeof json === 'object') {
                message = String(json.message || '');
                if (!message && json.errors && typeof json.errors === 'object') {
                  const firstKey = Object.keys(json.errors)[0] || '';
                  const firstErr = firstKey ? json.errors[firstKey]?.[0] : '';
                  if (firstErr) message = String(firstErr);
                }
              }
            } catch {
              const text = await res.text().catch(() => '');
              message = String(text || '');
            }
            if (token === this.sourceServerPreviewToken) {
              this.sourceServerPreviewError = message || 'Preview PDF server belum tersedia.';
            }
            if (!previewWindow.closed) {
              previewWindow.close();
            }
            return;
          }

          const blob = await res.blob();
          const url = URL.createObjectURL(blob);
          if (token !== this.sourceServerPreviewToken) {
            URL.revokeObjectURL(url);
            if (!previewWindow.closed) {
              previewWindow.close();
            }
            return;
          }

          previewWindow.location.href = url;
          window.setTimeout(() => {
            URL.revokeObjectURL(url);
          }, 120000);
        } catch {
          if (token === this.sourceServerPreviewToken) {
            this.sourceServerPreviewError = 'Preview server gagal dibuat.';
          }
          if (!previewWindow.closed) {
            previewWindow.close();
          }
        } finally {
          if (token === this.sourceServerPreviewToken) {
            this.sourceServerPreviewBusy = false;
          }
        }
      },
      revokeSignatureBlobUrl(url) {
        const raw = clean(url);
        if (raw === '' || !raw.startsWith('blob:')) return;
        URL.revokeObjectURL(raw);
      },
      clearLiveSignaturePreview(signer) {
        if (!signer || typeof signer !== 'object') return;
        this.revokeSignatureBlobUrl(signer.signature_live_preview_url);
        signer.signature_live_preview_url = '';
      },
      revokeAllSignatureBlobUrls() {
        this.signers.forEach((row) => this.clearLiveSignaturePreview(row));
      },
      onSignatureFileChange(event, signer) {
        if (!signer || typeof signer !== 'object') return;
        this.clearLiveSignaturePreview(signer);

        const input = event?.target;
        const file = input?.files?.[0] ?? null;
        if (!file) return;
        if (!String(file.type || '').startsWith('image/')) return;

        signer.signature_live_preview_url = URL.createObjectURL(file);
      },
      signaturePreviewUrl(signer) {
        const live = clean(signer?.signature_live_preview_url);
        if (live !== '') return live;
        return clean(signer?.signature_preview_url);
      },
      hasSignaturePreview(signer) {
        return this.signaturePreviewUrl(signer) !== '';
      },
      signaturePreviewLabel(signer) {
        if (clean(signer?.signature_live_preview_url) !== '') {
          return 'Preview baru (belum disimpan)';
        }
        if (clean(signer?.signature_preview_url) !== '') {
          return 'Tanda tangan tersimpan';
        }
        return 'Preview tanda tangan';
      },
      onTypeChange(signer) {
        if (!signer) return;
        const nextType = normalizeType(signer.type);
        const prevType = normalizeType(signer.last_type);
        signer.type = nextType;

        if (nextType !== prevType) {
          resetAutofilledFields(signer);
          if (nextType !== 'internal') {
            signer.internal_user_id = '';
          }
        }

        signer.last_type = nextType;
        applyAutofillProfile(signer);
      },
      onInternalUserChange(signer) {
        if (!signer) return;
        signer.internal_user_id = signer.internal_user_id ? String(signer.internal_user_id) : '';
        if (normalizeType(signer.type) !== 'internal') {
          return;
        }
        resetAutofilledFields(signer);
        applyAutofillProfile(signer);
      },
      isFieldLocked(signer, field) {
        return !!(signer?.locked && signer.locked[field]);
      },
      inputReadonlyClass(signer, field) {
        return this.isFieldLocked(signer, field) ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : '';
      },
      addSigner() {
        const signer = normalize({ type: 'internal' });
        this.signers.push(signer);
      },
      removeSigner(index) {
        if (this.signers.length <= 1) return;
        this.clearLiveSignaturePreview(this.signers[index]);
        this.signers.splice(index, 1);
      },
    };
  };
}

function initStudentRequestsShow(root) {
  if (!root) return;

  const items = Array.from(root.querySelectorAll('[data-signature-live-preview-item]'));
  if (items.length < 1) return;

  const cleanupFns = [];

  for (const item of items) {
    const input = item.querySelector('input[type="file"][data-signature-live-preview-input]');
    const previewBox = item.querySelector('[data-signature-live-preview-box]');
    const previewImg = item.querySelector('[data-signature-live-preview-img]');
    const previewLink = item.querySelector('[data-signature-live-preview-link]');
    const previewLabel = item.querySelector('[data-signature-live-preview-label]');
    if (!input || !previewBox || !previewImg || !previewLink) continue;

    const storedSrc = (previewImg.getAttribute('data-signature-stored-src') || '').trim();
    let blobUrl = '';

    const revokeBlob = () => {
      if (!blobUrl) return;
      URL.revokeObjectURL(blobUrl);
      blobUrl = '';
    };

    const enableLink = (href) => {
      previewLink.setAttribute('href', href);
      previewLink.classList.remove('pointer-events-none');
    };

    const disableLink = () => {
      previewLink.setAttribute('href', '#');
      previewLink.classList.add('pointer-events-none');
    };

    const showStoredPreview = () => {
      revokeBlob();
      if (!storedSrc) {
        previewImg.setAttribute('src', '');
        previewBox.classList.add('hidden');
        disableLink();
        if (previewLabel) previewLabel.textContent = 'Preview tanda tangan';
        return;
      }

      previewImg.setAttribute('src', storedSrc);
      previewBox.classList.remove('hidden');
      enableLink(storedSrc);
      if (previewLabel) previewLabel.textContent = 'Tanda tangan tersimpan';
    };

    input.addEventListener('change', () => {
      const file = input.files && input.files[0] ? input.files[0] : null;
      if (!file) {
        showStoredPreview();
        return;
      }

      if (!String(file.type || '').startsWith('image/')) {
        showStoredPreview();
        return;
      }

      revokeBlob();
      blobUrl = URL.createObjectURL(file);

      previewImg.setAttribute('src', blobUrl);
      previewBox.classList.remove('hidden');
      enableLink(blobUrl);
      if (previewLabel) previewLabel.textContent = 'Preview baru (belum disimpan)';
    }, { passive: true });

    cleanupFns.push(revokeBlob);
  }

  if (cleanupFns.length > 0) {
    window.addEventListener('beforeunload', () => {
      for (const fn of cleanupFns) fn();
    }, { once: true });
  }
}

function initScrollableUserSelects(root) {
  if (!root) return;

  const selects = Array.from(root.querySelectorAll('select[data-scrollable-select="1"], select[data-scrollable-user-select="1"]'))
    .filter((el) => !el.multiple && String(el.getAttribute('data-scrollable-select') || '1') !== '0');
  if (selects.length < 1) return;

  const closeOthers = (exceptWrap = null) => {
    selects.forEach((sel) => {
      const wrap = sel._scrollableUserSelectWrap;
      if (!wrap || wrap === exceptWrap) return;
      const panel = wrap.querySelector('.user-scroll-select__panel');
      if (!panel) return;
      panel.classList.add('hidden');
      panel.style.removeProperty('--uss-list-max-h');
      wrap.classList.remove('is-open', 'is-dropup');
    });
  };

  selects.forEach((selectEl) => {
    if (!selectEl || selectEl.dataset.scrollableEnhanced === '1') return;
    selectEl.dataset.scrollableEnhanced = '1';
    selectEl.classList.add('hidden');

    const wrap = document.createElement('div');
    wrap.className = 'user-scroll-select';

    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'as-input user-scroll-select__trigger';
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');

    const triggerText = document.createElement('span');
    triggerText.className = 'user-scroll-select__trigger-text';
    trigger.appendChild(triggerText);

    const triggerCaret = document.createElement('span');
    triggerCaret.className = 'user-scroll-select__trigger-caret';
    triggerCaret.textContent = '▾';
    trigger.appendChild(triggerCaret);

    const panel = document.createElement('div');
    panel.className = 'user-scroll-select__panel hidden';

    const search = document.createElement('input');
    search.type = 'search';
    search.className = 'as-input user-scroll-select__search';
    search.placeholder = String(selectEl.getAttribute('data-scrollable-search-placeholder') || 'Cari user...');

    const list = document.createElement('div');
    list.className = 'user-scroll-select__list';
    list.setAttribute('role', 'listbox');

    panel.appendChild(search);
    panel.appendChild(list);

    wrap.appendChild(trigger);
    wrap.appendChild(panel);

    selectEl.insertAdjacentElement('afterend', wrap);
    selectEl._scrollableUserSelectWrap = wrap;

    const getOptions = () => Array.from(selectEl.options || []);

    const getSelectedOption = () => {
      const selectedIdx = Number.isInteger(selectEl.selectedIndex) ? selectEl.selectedIndex : 0;
      const opts = getOptions();
      return opts[selectedIdx] || opts.find((o) => o.selected) || opts[0] || null;
    };

    const viewportGap = 12;
    const desiredPanelHeight = 260;
    const reservedPanelChrome = 72;
    const minListHeight = 96;
    const maxListHeight = 360;

    const syncTriggerText = () => {
      const selected = getSelectedOption();
      triggerText.textContent = selected ? String(selected.textContent || '').trim() : '-- pilih user --';
      const disabled = !!selectEl.disabled;
      trigger.disabled = disabled;
      wrap.classList.toggle('is-disabled', disabled);
      trigger.setAttribute('aria-expanded', panel.classList.contains('hidden') ? 'false' : 'true');
    };

    const renderList = () => {
      const q = String(search.value || '').trim().toLowerCase();
      const opts = getOptions();

      list.replaceChildren();

      const filtered = q
        ? opts.filter((o) => String(o.textContent || '').toLowerCase().includes(q))
        : opts;

      if (filtered.length < 1) {
        const empty = document.createElement('div');
        empty.className = 'user-scroll-select__empty';
        empty.textContent = String(selectEl.getAttribute('data-scrollable-empty-text') || 'Tidak ada user.');
        list.appendChild(empty);
        return;
      }

      filtered.forEach((opt) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'user-scroll-select__option';
        btn.textContent = String(opt.textContent || '').trim();
        btn.setAttribute('role', 'option');
        btn.setAttribute('aria-selected', opt.selected ? 'true' : 'false');
        if (opt.selected) btn.classList.add('is-selected');
        if (opt.disabled) {
          btn.disabled = true;
          btn.classList.add('is-disabled');
        }
        if (String(opt.value || '') === '') {
          btn.classList.add('is-placeholder');
        }

        btn.addEventListener('click', () => {
          if (opt.disabled) return;
          selectEl.value = String(opt.value ?? '');
          selectEl.dispatchEvent(new Event('change', { bubbles: true }));
          closePanel();
        });

        list.appendChild(btn);
      });
    };

    const updatePanelPlacement = () => {
      if (panel.classList.contains('hidden')) return;
      const rect = trigger.getBoundingClientRect();
      const viewportHeight = Math.max(window.innerHeight || 0, document.documentElement.clientHeight || 0);
      const availableBelow = Math.max(0, viewportHeight - rect.bottom - viewportGap);
      const availableAbove = Math.max(0, rect.top - viewportGap);
      const shouldDropUp = availableBelow < desiredPanelHeight && availableAbove > availableBelow;
      const activeSpace = Math.max(0, (shouldDropUp ? availableAbove : availableBelow) - 8);
      const listHeight = Math.max(minListHeight, Math.min(maxListHeight, activeSpace - reservedPanelChrome));

      wrap.classList.toggle('is-dropup', shouldDropUp);
      panel.style.setProperty('--uss-list-max-h', `${listHeight}px`);
    };

    const closePanel = () => {
      panel.classList.add('hidden');
      panel.style.removeProperty('--uss-list-max-h');
      wrap.classList.remove('is-open', 'is-dropup');
      syncTriggerText();
    };

    trigger.addEventListener('click', () => {
      if (selectEl.disabled) return;
      const willOpen = panel.classList.contains('hidden');
      closeOthers(wrap);
      if (willOpen) {
        panel.classList.remove('hidden');
        wrap.classList.add('is-open');
        renderList();
        updatePanelPlacement();
        syncTriggerText();
        search.focus();
        search.select();
      } else {
        closePanel();
      }
    });

    search.addEventListener('input', () => {
      renderList();
      updatePanelPlacement();
    }, { passive: true });
    selectEl.addEventListener('change', () => {
      syncTriggerText();
      renderList();
      updatePanelPlacement();
    }, { passive: true });

    const observer = new MutationObserver(() => {
      syncTriggerText();
      renderList();
    });
    observer.observe(selectEl, {
      attributes: true,
      attributeFilter: ['disabled'],
      childList: true,
      subtree: true,
    });

    wrap.addEventListener('keydown', (e) => {
      if (e.key !== 'Escape') return;
      closePanel();
      trigger.focus();
    });

    document.addEventListener('click', (e) => {
      if (!wrap.contains(e.target)) {
        closePanel();
      }
    });

    window.addEventListener('resize', updatePanelPlacement, { passive: true });
    window.addEventListener('scroll', updatePanelPlacement, { passive: true });

    syncTriggerText();
    renderList();
  });
}

let scrollableUserSelectObserverBooted = false;

function bootScrollableUserSelectsObserver(root) {
  if (scrollableUserSelectObserverBooted) return;
  if (!(window.MutationObserver)) return;

  const target = root instanceof Document ? root.body : root;
  if (!target) return;

  scrollableUserSelectObserverBooted = true;
  const selectQuery = 'select[data-scrollable-select="1"], select[data-scrollable-user-select="1"]';

  const observer = new MutationObserver((mutations) => {
    let shouldRescan = false;

    for (const mutation of mutations) {
      if (mutation.type !== 'childList' || mutation.addedNodes.length < 1) continue;
      for (const node of mutation.addedNodes) {
        if (!(node instanceof Element)) continue;

        if (node.matches(selectQuery) || node.querySelector(selectQuery)) {
          shouldRescan = true;
          break;
        }
      }

      if (shouldRescan) break;
    }

    if (shouldRescan) {
      initScrollableUserSelects(document);
    }
  });

  observer.observe(target, {
    childList: true,
    subtree: true,
  });
}

function initAdminRequestsShow(root) {
  if (!root) return;

  root.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-wf-action]');
    if (!btn || !root.contains(btn)) return;

    const form = btn.closest('form[data-wf-form]');
    if (!form) return;

    e.preventDefault();
    const action = btn.getAttribute('data-wf-action') || '';
    const input = form.querySelector('[data-wf-action-input]');
    if (input) input.value = action;

    if (typeof form.requestSubmit === 'function') form.requestSubmit();
    else form.submit();
  });
}

function initStaffAssembleShow(root) {
  if (!root) return;

  const toggle = root.querySelector('[data-manual-placement-toggle]');
  const grid = root.querySelector('[data-manual-placement-grid]');
  if (!toggle || !grid) return;

  const syncManualPlacement = () => {
    const useManual = !!toggle.checked;
    grid.classList.toggle('is-disabled', !useManual);

    grid.querySelectorAll('[data-placement-chip]').forEach((chip) => {
      chip.textContent = useManual ? 'Manual' : 'Auto';
    });

    grid.querySelectorAll('input').forEach((input) => {
      if (!(input instanceof HTMLInputElement)) return;
      if (input.type === 'hidden') return;
      input.disabled = !useManual;
    });
  };

  toggle.addEventListener('change', syncManualPlacement, { passive: true });
  syncManualPlacement();
}

function initAdminServicesDoc(root) {
  if (!root) return;

  const toast = window.Alpine?.store?.('toast');

  const copyText = async (text) => {
    if (!text) return false;

    if (navigator.clipboard?.writeText) {
      await navigator.clipboard.writeText(text);
      return true;
    }

    const ta = document.createElement('textarea');
    ta.value = text;
    ta.setAttribute('readonly', 'true');
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    ta.style.top = '0';
    document.body.appendChild(ta);
    ta.select();
    const ok = document.execCommand('copy');
    ta.remove();
    return ok;
  };

  root.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-copy-text]');
    if (!btn || !root.contains(btn)) return;

    e.preventDefault();

    const text = btn.getAttribute('data-copy-text') || '';
    try {
      const ok = await copyText(text);
      if (ok) toast?.push({ type: 'info', title: 'Disalin', message: text, timeout: 1800 });
      else toast?.push({ type: 'info', title: 'Copy', message: 'Tidak didukung di browser ini', timeout: 2200 });
    } catch {
      toast?.push({ type: 'info', title: 'Copy', message: 'Gagal menyalin', timeout: 2200 });
    }
  });

  // Placeholder mapping UX helper: Source Ref dropdowns are split by source type (FORM vs PROFILE).
  const initSourceRefHelper = () => {
    const rows = Array.from(root.querySelectorAll('[data-ph-row]'));
    if (rows.length < 1) return;

    for (const row of rows) {
      const typeSel = row.querySelector('[data-ph-source-type]');
      const refHidden = row.querySelector('[data-ph-source-ref-hidden]');

      const formWrap = row.querySelector('[data-ph-source-ref-form-wrap]');
      const formSel = row.querySelector('[data-ph-source-ref-form]');
      const formCustom = row.querySelector('[data-ph-source-ref-form-custom]');

      const profileWrap = row.querySelector('[data-ph-source-ref-profile-wrap]');
      const profileSel = row.querySelector('[data-ph-source-ref-profile]');
      const profileCustom = row.querySelector('[data-ph-source-ref-profile-custom]');

      if (!typeSel || !refHidden || !formWrap || !formSel || !formCustom || !profileWrap || !profileSel || !profileCustom) continue;
      if (typeSel.disabled) continue;

      const autoFormKey = (row.getAttribute('data-ph-form-key') || '').toString();

      const pickSelectForType = (t) => {
        if (t === 'FORM') return { wrap: formWrap, select: formSel, custom: formCustom };
        if (t === 'PROFILE') return { wrap: profileWrap, select: profileSel, custom: profileCustom };
        return null;
      };

      const setControlState = (control, enabled) => {
        if (!control) return;
        control.wrap.classList.toggle('hidden', !enabled);
        control.select.disabled = !enabled;
        control.custom.disabled = !enabled;
      };

      const syncUIFromHidden = (t) => {
        const control = pickSelectForType(t);
        if (!control) return;

        let hiddenVal = (refHidden.value || '').toString();

        // For FORM, default to placeholder-derived key to avoid forcing admin to pick an "acuan".
        if (t === 'FORM' && hiddenVal.trim() === '' && autoFormKey) {
          hiddenVal = autoFormKey;
          refHidden.value = autoFormKey;
        }

        // Backward-compatible alias: student_number -> user_number (UI only).
        // This avoids showing legacy `*.student_number` values as "custom" and nudges the new naming.
        if (t === 'PROFILE' && hiddenVal) {
          if (hiddenVal === 'user.student_number') hiddenVal = 'user.user_number';
          if (/^signer\\.[A-Z0-9_]+\\.student_number$/.test(hiddenVal)) {
            hiddenVal = hiddenVal.replace(/\\.student_number$/, '.user_number');
          }
          if (hiddenVal !== (refHidden.value || '').toString()) {
            refHidden.value = hiddenVal;
          }
        }
        const hasOpt = Array.from(control.select.options).some((o) => (o?.value || '') === hiddenVal);

        if (hiddenVal === '') {
          control.select.value = '';
          control.custom.value = '';
          control.custom.classList.add('hidden');
          return;
        }

        if (hasOpt) {
          control.select.value = hiddenVal;
          control.custom.value = '';
          control.custom.classList.add('hidden');
          return;
        }

        control.select.value = '__custom__';
        control.custom.value = hiddenVal;
        control.custom.classList.remove('hidden');
      };

      const syncHiddenFromUI = (t) => {
        const control = pickSelectForType(t);
        if (!control) return;

        const v = (control.select.value || '').toString();
        if (v === '__custom__') {
          control.custom.classList.remove('hidden');
          refHidden.value = (control.custom.value || '').toString();
          return;
        }

        control.custom.classList.add('hidden');
        refHidden.value = v;
      };

      const update = () => {
        const t = (typeSel.value || '').toUpperCase();
        const needsRef = t === 'FORM' || t === 'PROFILE';

        setControlState({ wrap: formWrap, select: formSel, custom: formCustom }, needsRef && t === 'FORM');
        setControlState({ wrap: profileWrap, select: profileSel, custom: profileCustom }, needsRef && t === 'PROFILE');

        if (!needsRef) {
          refHidden.value = '';
          return;
        }

        syncUIFromHidden(t);
        syncHiddenFromUI(t);
      };

      typeSel.addEventListener('change', update, { passive: true });
      formSel.addEventListener('change', () => syncHiddenFromUI('FORM'), { passive: true });
      formCustom.addEventListener('input', () => {
        if ((formSel.value || '').toString() !== '__custom__') return;
        refHidden.value = (formCustom.value || '').toString();
      }, { passive: true });

      profileSel.addEventListener('change', () => syncHiddenFromUI('PROFILE'), { passive: true });
      profileCustom.addEventListener('input', () => {
        if ((profileSel.value || '').toString() !== '__custom__') return;
        refHidden.value = (profileCustom.value || '').toString();
      }, { passive: true });

      update();
    }
  };

  initSourceRefHelper();

  // If user navigates to #doc-form, auto-open the advanced Form section (it's wrapped in <details>).
  const initDocFormDetailsAutoOpen = () => {
    const details = root.querySelector('[data-doc-form-details]');
    if (!details) return;

    const maybeOpen = () => {
      if (window.location.hash === '#doc-form') details.open = true;
    };

    // Open on initial load and when hash changes.
    maybeOpen();
    window.addEventListener('hashchange', maybeOpen, { passive: true });

    // Open when clicking any link to #doc-form (before/while scrolling).
    root.addEventListener('click', (e) => {
      const a = e.target?.closest?.('a[href="#doc-form"]');
      if (!a) return;
      details.open = true;
    });
  };

  initDocFormDetailsAutoOpen();

  // Form builder helper: make it easier to create fields for pemohon.
  const initDocFormBuilderHelper = () => {
    const form = root.querySelector('[data-doc-form-builder]');
    if (!form) return;

    const placeholderSel = form.querySelector('[data-doc-fb-from-placeholder]');
    const btnGenKey = form.querySelector('[data-doc-fb-generate-key]');
    const keyEl = form.querySelector('[name="key"]');
    const labelEl = form.querySelector('[name="label_id"]');
    const mapsEl = form.querySelector('[name="maps_to_placeholder_key"]');
    const typeEl = form.querySelector('select[name="type"]');
    const previewEl = form.querySelector('[data-doc-fb-preview]');
    const optionsWrap = form.querySelector('[data-doc-fb-options]');

    if (!keyEl || !labelEl || !mapsEl || !typeEl) return;

    const titleFromKey = (value) => String(value || '')
      .trim()
      .replace(/[_\\-]+/g, ' ')
      .toLowerCase()
      .replace(/\\b\\w/g, (m) => m.toUpperCase());

    const slugToKey = (value) => {
      let s = String(value || '').trim().toLowerCase();
      s = s.replace(/[^a-z0-9]+/g, '_');
      s = s.replace(/_+/g, '_').replace(/^_+|_+$/g, '');
      if (s === '') s = 'field';
      if (/^[0-9]/.test(s)) s = `field_${s}`;
      return s;
    };

    const updatePreview = () => {
      if (!previewEl) return;
      const label = String(labelEl.value || '').trim() || '(label belum diisi)';
      const key = String(keyEl.value || '').trim() || '(key belum diisi)';
      const maps = String(mapsEl.value || '').trim();
      previewEl.innerHTML = `Preview: <span class="doc-mono">${label}</span> → <span class="doc-mono">${key}</span>${maps ? ` <span class="text-muted">(maps: <span class="doc-mono">${maps}</span>)</span>` : ''}`;
    };

    const updateOptionsVisibility = () => {
      if (!optionsWrap) return;
      optionsWrap.classList.toggle('hidden', String(typeEl.value || '') !== 'select');
    };

    const maybeAutofillKeyFromLabel = () => {
      const currentKey = String(keyEl.value || '').trim();
      if (currentKey !== '' && keyEl.dataset.auto !== '1') return;
      const lbl = String(labelEl.value || '').trim();
      if (lbl === '') return;
      keyEl.value = slugToKey(lbl);
      keyEl.dataset.auto = '1';
    };

    keyEl.addEventListener('input', () => { keyEl.dataset.auto = '0'; updatePreview(); }, { passive: true });
    labelEl.addEventListener('input', () => { maybeAutofillKeyFromLabel(); updatePreview(); }, { passive: true });
    typeEl.addEventListener('change', () => { updateOptionsVisibility(); }, { passive: true });

    btnGenKey?.addEventListener('click', () => {
      const lbl = String(labelEl.value || '').trim();
      if (!lbl) return;
      keyEl.value = slugToKey(lbl);
      keyEl.dataset.auto = '1';
      updatePreview();
    });

    placeholderSel?.addEventListener('change', () => {
      const pk = String(placeholderSel.value || '').trim();
      if (!pk) {
        updatePreview();
        return;
      }

      mapsEl.value = pk;
      if (String(labelEl.value || '').trim() === '') {
        labelEl.value = titleFromKey(pk);
      }
      if (String(keyEl.value || '').trim() === '' || keyEl.dataset.auto === '1') {
        keyEl.value = slugToKey(labelEl.value || titleFromKey(pk));
        keyEl.dataset.auto = '1';
      }

      updatePreview();
    }, { passive: true });

    updateOptionsVisibility();
    updatePreview();
  };

  initDocFormBuilderHelper();
}

function initAdminServicesForm(root) {
  if (!root) return;

  const form = root.querySelector('form.as-form');
  if (!form) return;

  // Realtime preview: document flow options (admin/services create+edit)
  const initDocFlowPreview = () => {
    const flowRoot = root.querySelector('[data-doc-flow]');
    if (!flowRoot) return;

    const previewEl = flowRoot.querySelector('[data-doc-flow-preview]');
    if (!previewEl) return;

    const checkboxes = Array.from(flowRoot.querySelectorAll('input[type="checkbox"][data-doc-flow-flag]'));
    if (checkboxes.length < 1) return;
    const gateRoleInput = root.querySelector('select[name="gate_role"], input[name="gate_role"]');
    const sourceTypeInput = root.querySelector('select[name="document_source_type"], input[name="document_source_type"]');
    const disabledNoteEl = flowRoot.querySelector('[data-doc-flow-disabled-note]');

    const normalizeGateRole = (raw) => {
      const role = (raw || '').toString().trim();
      const normalized = role.toUpperCase().replace(/\s+/g, '_');
      if (['ADMIN_JURUSAN', 'ADMIN_JURUSAN_PER_PRODI', 'ADMIN_PRODI'].includes(normalized)) return 'Admin Jurusan';
      if (['STAF_ULT', 'STAFF_ULT'].includes(normalized)) return 'Staf ULT';
      return role;
    };

    const isRequestPptxMode = () => String(sourceTypeInput?.value || '').trim() === 'REQUEST_PPTX';

    const gateFlowLabel = () => {
      const role = normalizeGateRole(gateRoleInput?.value || '');
      if (role === 'Staf ULT') return 'Staf ULT (verifikasi & nomor surat)';
      return 'Admin Jurusan (verifikasi & nomor surat)';
    };

    const isOn = (flag) => {
      const el = checkboxes.find((c) => (c?.dataset?.docFlowFlag || '') === flag);
      return !!el?.checked;
    };

    const labels = {
      org_chair: 'TTD Ketua Organisasi',
      org_secretary: 'TTD Sekretaris Organisasi',
      pemohon: 'TTD Pemohon',
      kaprodi: 'TTD Ketua Prodi (Kaprodi)',
      kajur: 'TTD Ketua Jurusan (Kajur)',
      other_lecturer: 'TTD Dosen lainnya',
    };

    const syncAvailability = () => {
      const disabled = isRequestPptxMode();
      flowRoot.classList.toggle('is-disabled', disabled);

      if (disabledNoteEl) {
        disabledNoteEl.classList.toggle('hidden', !disabled);
      }

      checkboxes.forEach((checkbox) => {
        if (!(checkbox instanceof HTMLInputElement)) return;

        if (disabled) {
          if (checkbox.dataset.docFlowStoredChecked === undefined) {
            checkbox.dataset.docFlowStoredChecked = checkbox.checked ? '1' : '0';
          }
          checkbox.checked = false;
          checkbox.disabled = true;
          return;
        }

        checkbox.disabled = false;
        if (checkbox.dataset.docFlowStoredChecked !== undefined) {
          checkbox.checked = checkbox.dataset.docFlowStoredChecked === '1';
          delete checkbox.dataset.docFlowStoredChecked;
        }
      });
    };

    const render = () => {
      syncAvailability();

      const pre = [];
      if (isOn('pemohon')) pre.push(labels.pemohon);
      if (isOn('org_secretary')) pre.push(labels.org_secretary);
      if (isOn('org_chair')) pre.push(labels.org_chair);

      const mid = [];
      if (isOn('other_lecturer')) mid.push(labels.other_lecturer);
      if (isOn('kaprodi')) mid.push(labels.kaprodi);
      if (isOn('kajur')) mid.push(labels.kajur);

      const flow = [
        ...pre,
        gateFlowLabel(),
        ...mid,
        'Review ULT',
        'Penandatangan Fakultas (Dekan/WD)',
        'Selesai',
      ];

      // Render as pill steps for better readability (no HTML injection: textContent only).
      previewEl.replaceChildren();
      flow.forEach((label, idx) => {
        if (idx > 0) {
          const sep = document.createElement('span');
          sep.className = 'doc-flow-sep';
          sep.textContent = '➜';
          previewEl.appendChild(sep);
        }

        const chip = document.createElement('span');
        chip.className = 'doc-flow-step';
        chip.textContent = label;
        previewEl.appendChild(chip);
      });
    };

    for (const cb of checkboxes) {
      cb.addEventListener('change', render, { passive: true });
    }
    if (gateRoleInput) {
      gateRoleInput.addEventListener('change', render, { passive: true });
    }
    if (sourceTypeInput) {
      sourceTypeInput.addEventListener('change', render, { passive: true });
    }
    render();
  };

  initDocFlowPreview();

  // Translation helper (best-effort). If not available, skip without blocking other features.
  const translateUrl = root.getAttribute('data-translate-url') || '';
  const tokenEl = form.querySelector('input[name="_token"]');
  const csrfToken = tokenEl ? (tokenEl.value || '') : '';
  if (!translateUrl || !csrfToken) return;

  const cssEscape = (value) => {
    const v = String(value ?? '');
    if (window.CSS && typeof window.CSS.escape === 'function') return window.CSS.escape(v);
    return v.replace(/[^a-zA-Z0-9_\\-]/g, '\\$&');
  };

  const pick = (name) => form.querySelector(`[name="${cssEscape(name)}"]`);

  const pairs = [
    { from: 'title_id', to: 'title_en' },
    { from: 'summary_id', to: 'summary_en' },
  ];

  const state = new Map();

  const postTranslate = async (text) => {
    const res = await fetch(translateUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ text, from: 'id', to: 'en' }),
    });

    if (!res.ok) return '';
    const json = await res.json().catch(() => ({}));
    return (json?.translated || '').toString();
  };

  const schedule = (fromEl, toEl) => {
    const key = toEl.name;
    const prev = state.get(key) || { timer: null, inFlight: false, lastReq: '', lastOut: '' };

    if (prev.timer) window.clearTimeout(prev.timer);

    prev.timer = window.setTimeout(async () => {
      const fromText = (fromEl.value || '').trim();
      const toText = (toEl.value || '').trim();

      // Don't override if user already typed EN (unless it's still the last auto value).
      if (toEl.dataset.touched === '1' && toText !== (prev.lastOut || '').trim()) return;
      if (fromText.length < 2) return;
      if (prev.inFlight && prev.lastReq === fromText) return;

      prev.inFlight = true;
      prev.lastReq = fromText;
      state.set(key, prev);

      try {
        const out = await postTranslate(fromText);
        if (!out) return;

        // Ensure source text didn't change while awaiting.
        if (((fromEl.value || '').trim()) !== fromText) return;

        const currentTo = (toEl.value || '').trim();
        if (toEl.dataset.touched === '1' && currentTo !== (prev.lastOut || '').trim()) return;

        prev.lastOut = out;
        state.set(key, prev);

        toEl.dataset.autofilling = '1';
        toEl.value = out;
        toEl.dispatchEvent(new Event('input', { bubbles: true }));
        toEl.dispatchEvent(new Event('change', { bubbles: true }));
        delete toEl.dataset.autofilling;
      } catch {
        // silent: translation is best-effort
      } finally {
        prev.inFlight = false;
        state.set(key, prev);
      }
    }, 450);

    state.set(key, prev);
  };

  for (const pair of pairs) {
    const fromEl = pick(pair.from);
    const toEl = pick(pair.to);
    if (!fromEl || !toEl) continue;

    // If user edits EN, stop auto-overwriting.
    toEl.addEventListener('input', () => {
      if (toEl.dataset.autofilling === '1') return;
      if ((toEl.value || '').trim() !== '') toEl.dataset.touched = '1';
    }, { passive: true });

    fromEl.addEventListener('input', () => schedule(fromEl, toEl), { passive: true });
    fromEl.addEventListener('change', () => schedule(fromEl, toEl), { passive: true });

    // Initial fill when EN empty.
    if (((toEl.value || '').trim()) === '' && ((fromEl.value || '').trim()) !== '') {
      schedule(fromEl, toEl);
    }
  }

  // (doc flow preview initialized above)
}

function initCmsSettingsForm(root) {
  if (!root) return;

  const form = root.querySelector('form.cms-form');
  if (!form) return;

  const translateUrl = root.getAttribute('data-translate-url') || '';
  const csrfToken = form.querySelector('input[name="_token"]')?.value || '';
  if (!translateUrl || !csrfToken) return;

  const cssEscape = (value) => {
    const v = String(value ?? '');
    if (window.CSS && typeof window.CSS.escape === 'function') return window.CSS.escape(v);
    return v.replace(/[^a-zA-Z0-9_\\-]/g, '\\$&');
  };

  const pick = (name) => form.querySelector(`[name="${cssEscape(name)}"]`);
  const fromEl = pick('about_ult_html_id');
  const toEl = pick('about_ult_html_en');
  if (!fromEl || !toEl) return;

  const getEditorFromInput = (inputEl) => {
    const tiptapRoot = inputEl.closest('[data-tiptap]');
    if (!tiptapRoot) return null;
    return tiptapRoot._tiptapEditor || null;
  };

  const setTargetHtml = (html) => {
    const out = (html || '').toString();
    const editor = getEditorFromInput(toEl);

    toEl.dataset.autofilling = '1';
    try {
      if (editor && typeof editor.commands?.setContent === 'function') {
        editor.commands.setContent(out);
      } else {
        toEl.value = out;
        toEl.dispatchEvent(new Event('input', { bubbles: true }));
        toEl.dispatchEvent(new Event('change', { bubbles: true }));
      }
    } finally {
      delete toEl.dataset.autofilling;
    }
  };

  const stripHtmlText = (html) => String(html || '')
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/gi, ' ')
    .replace(/\s+/g, ' ')
    .trim();

  const postTranslate = async (text) => {
    const res = await fetch(translateUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ text, from: 'id', to: 'en' }),
    });

    if (!res.ok) return '';
    const json = await res.json().catch(() => ({}));
    return (json?.translated || '').toString();
  };

  const state = {
    timer: null,
    inFlight: false,
    lastReq: '',
    lastOut: '',
  };

  const schedule = () => {
    if (state.timer) window.clearTimeout(state.timer);

    state.timer = window.setTimeout(async () => {
      const fromHtml = (fromEl.value || '').trim();
      const fromText = stripHtmlText(fromHtml);
      const toHtml = (toEl.value || '').trim();

      if (fromText.length < 2) return;

      // If user manually edits EN, stop auto-overwrite unless EN is empty.
      if (toEl.dataset.touched === '1' && toHtml !== '' && toHtml !== (state.lastOut || '').trim()) return;
      if (state.inFlight && state.lastReq === fromHtml) return;

      state.inFlight = true;
      state.lastReq = fromHtml;

      try {
        const translated = await postTranslate(fromHtml);
        if (!translated) return;

        // Skip stale response.
        if (((fromEl.value || '').trim()) !== fromHtml) return;

        const currentTo = (toEl.value || '').trim();
        if (toEl.dataset.touched === '1' && currentTo !== '' && currentTo !== (state.lastOut || '').trim()) return;

        state.lastOut = translated;
        setTargetHtml(translated);
      } catch {
        // Silent best-effort helper.
      } finally {
        state.inFlight = false;
      }
    }, 900);
  };

  toEl.addEventListener('input', () => {
    if (toEl.dataset.autofilling === '1') return;
    if ((toEl.value || '').trim() !== '') toEl.dataset.touched = '1';
  }, { passive: true });

  fromEl.addEventListener('input', schedule, { passive: true });
  fromEl.addEventListener('change', schedule, { passive: true });

  // Initial fill when EN is empty.
  if (((toEl.value || '').trim()) === '' && ((fromEl.value || '').trim()) !== '') {
    schedule();
  }
}

function initAdminUserGuidesForm(root) {
  if (!root) return;

  const form = root.querySelector('form.ug-form');
  if (!form) return;

  const translateUrl = root.getAttribute('data-translate-url') || '';
  const csrfToken = form.querySelector('input[name="_token"]')?.value || '';
  if (!translateUrl || !csrfToken) return;

  const cssEscape = (value) => {
    const v = String(value ?? '');
    if (window.CSS && typeof window.CSS.escape === 'function') return window.CSS.escape(v);
    return v.replace(/[^a-zA-Z0-9_\\-]/g, '\\$&');
  };

  const pick = (name) => form.querySelector(`[name="${cssEscape(name)}"]`);
  const contentTypeFields = Array.from(form.querySelectorAll('[data-guide-content-type]'));
  const pdfPanel = form.querySelector('[data-guide-pdf-panel]');
  const videoPanel = form.querySelector('[data-guide-video-panel]');
  const pdfInput = pick('pdf');
  const videoInput = pick('video_url');
  const pairs = [
    { from: 'title_id', to: 'title_en' },
    { from: 'summary_id', to: 'summary_en' },
  ];

  const state = new Map();

  const postTranslate = async (text) => {
    const res = await fetch(translateUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ text, from: 'id', to: 'en' }),
    });

    if (!res.ok) return '';
    const json = await res.json().catch(() => ({}));
    return (json?.translated || '').toString();
  };

  const schedule = (fromEl, toEl) => {
    const key = toEl.name;
    const prev = state.get(key) || { timer: null, inFlight: false, lastReq: '', lastOut: '' };

    if (prev.timer) window.clearTimeout(prev.timer);

    prev.timer = window.setTimeout(async () => {
      const fromText = (fromEl.value || '').trim();
      const toText = (toEl.value || '').trim();

      // Don't overwrite manual EN text unless target is blank or last auto result.
      if (toEl.dataset.touched === '1' && toText !== (prev.lastOut || '').trim()) return;
      if (fromText.length < 2) return;
      if (prev.inFlight && prev.lastReq === fromText) return;

      prev.inFlight = true;
      prev.lastReq = fromText;
      state.set(key, prev);

      try {
        const out = await postTranslate(fromText);
        if (!out) return;

        // Skip stale response if source has changed.
        if (((fromEl.value || '').trim()) !== fromText) return;

        const currentTo = (toEl.value || '').trim();
        if (toEl.dataset.touched === '1' && currentTo !== (prev.lastOut || '').trim()) return;

        prev.lastOut = out;
        state.set(key, prev);

        toEl.dataset.autofilling = '1';
        toEl.value = out;
        toEl.dispatchEvent(new Event('input', { bubbles: true }));
        toEl.dispatchEvent(new Event('change', { bubbles: true }));
        delete toEl.dataset.autofilling;
      } catch {
        // Silent best-effort helper.
      } finally {
        prev.inFlight = false;
        state.set(key, prev);
      }
    }, 450);

    state.set(key, prev);
  };

  const syncContentType = () => {
    const activeType = contentTypeFields.find((field) => field.checked)?.value || 'pdf';
    const isPdf = activeType === 'pdf';

    if (pdfPanel) pdfPanel.hidden = !isPdf;
    if (videoPanel) videoPanel.hidden = isPdf;
    if (pdfInput) pdfInput.disabled = !isPdf;
    if (videoInput) videoInput.disabled = isPdf;
  };

  for (const pair of pairs) {
    const fromEl = pick(pair.from);
    const toEl = pick(pair.to);
    if (!fromEl || !toEl) continue;

    // Preserve existing EN value (esp. edit page) unless user clears it.
    if ((toEl.value || '').trim() !== '') toEl.dataset.touched = '1';

    toEl.addEventListener('input', () => {
      if (toEl.dataset.autofilling === '1') return;
      if ((toEl.value || '').trim() === '') {
        delete toEl.dataset.touched;
      } else {
        toEl.dataset.touched = '1';
      }
    }, { passive: true });

    fromEl.addEventListener('input', () => schedule(fromEl, toEl), { passive: true });
    fromEl.addEventListener('change', () => schedule(fromEl, toEl), { passive: true });

    if (((toEl.value || '').trim()) === '' && ((fromEl.value || '').trim()) !== '') {
      schedule(fromEl, toEl);
    }
  }

  contentTypeFields.forEach((field) => {
    field.addEventListener('change', syncContentType, { passive: true });
  });

  syncContentType();
}

function initJurusanProdiSelects(root) {
  if (!root) return;

  const containers = Array.from(root.querySelectorAll('[data-linked-jurusan-prodi]'));
  if (containers.length < 1) return;

  containers.forEach((container) => {
    if (!container || container.dataset.linkedJurusanProdiBound === '1') return;
    container.dataset.linkedJurusanProdiBound = '1';

    const jurusanSel = container.querySelector('select[name="jurusan_id"], select[data-jurusan-select]');
    const prodiSel = container.querySelector('select[name="prodi_id"], select[data-prodi-select]');
    if (!jurusanSel || !prodiSel) return;

    const parseProdiOptions = () => {
      try {
        const raw = container.dataset.prodiOptions || container.getAttribute('data-prodi-options') || '[]';
        const parsed = JSON.parse(raw || '[]');
        return Array.isArray(parsed) ? parsed : [];
      } catch {
        return [];
      }
    };

    const render = () => {
      const selectedJurusan = String(jurusanSel.value || '').trim();
      const currentProdi = String(prodiSel.value || container.getAttribute('data-selected-prodi') || '').trim();
      const allProdi = parseProdiOptions();
      const filtered = selectedJurusan === ''
        ? []
        : allProdi.filter((item) => String(item?.parent_id || '') === selectedJurusan);
      const selectedStillExists = filtered.some((item) => String(item?.id || '') === currentProdi);

      prodiSel.replaceChildren();

      const placeholder = document.createElement('option');
      placeholder.value = '';
      if (selectedJurusan === '') {
        placeholder.textContent = 'Pilih jurusan terlebih dahulu';
      } else if (filtered.length < 1) {
        placeholder.textContent = 'Program studi belum tersedia';
      } else {
        placeholder.textContent = 'Pilih program studi';
      }
      prodiSel.appendChild(placeholder);

      filtered.forEach((item) => {
        const option = document.createElement('option');
        option.value = String(item.id);
        option.textContent = String(item.name || item.id);
        if (selectedStillExists && option.value === currentProdi) option.selected = true;
        prodiSel.appendChild(option);
      });

      prodiSel.disabled = selectedJurusan === '' || filtered.length < 1;
      if (!selectedStillExists) prodiSel.value = '';
      container.setAttribute('data-selected-prodi', prodiSel.value || '');
    };

    jurusanSel.addEventListener('change', render, { passive: true });
    prodiSel.addEventListener('change', () => {
      container.setAttribute('data-selected-prodi', String(prodiSel.value || ''));
    }, { passive: true });

    render();
  });
}

function initCustomFileFields(root) {
  if (!root) return;

  const fields = Array.from(root.querySelectorAll('[data-file-field]'));
  if (fields.length < 1) return;

  fields.forEach((field) => {
    if (!field || field.dataset.fileFieldBound === '1') return;
    field.dataset.fileFieldBound = '1';

    const input = field.querySelector('[data-file-input]');
    const trigger = field.querySelector('[data-file-trigger]');
    const nameEl = field.querySelector('[data-file-name]');
    if (!(input instanceof HTMLInputElement) || !trigger || !nameEl) return;

    const emptyLabel = String(field.getAttribute('data-file-empty-label') || 'Belum ada file dipilih');

    const sync = () => {
      const files = Array.from(input.files || []);
      if (files.length < 1) {
        nameEl.textContent = emptyLabel;
        field.classList.remove('has-file');
        return;
      }

      nameEl.textContent = files.map((file) => file.name).join(', ');
      field.classList.add('has-file');
    };

    trigger.addEventListener('click', () => input.click());
    input.addEventListener('change', sync, { passive: true });

    sync();
  });
}

function initAdminUsersRoleScope(root) {
  if (!root) return;

  const roleSel = root.querySelector('select[name="role"]');
  const scopeBlock = root.querySelector('[data-scoped-units]');
  const scopeSel = scopeBlock?.querySelector('select[name="scoped_unit_ids[]"]');
  const initialScopeOptions = scopeSel
    ? Array.from(scopeSel.options).map((option) => ({
      value: String(option.value || ''),
      label: String(option.textContent || option.value || ''),
      selected: !!option.selected,
    }))
    : [];
  const jabatanBlock = root.querySelector('[data-jabatan-field]');
  const jabatanSelect = jabatanBlock?.querySelector('select[name="jabatan"]');
  const jabatanOtherWrap = jabatanBlock?.querySelector('[data-jabatan-other]');
  const jabatanOtherInput = jabatanBlock?.querySelector('input[name="jabatan_other"]');
  const unitPicker = root.querySelector('[data-unit-picker]');
  const unitIdHidden = unitPicker?.querySelector('[data-unit-id-hidden]');
  const fakultasWrap = unitPicker?.querySelector('[data-unit-fakultas-wrap]');
  const fakultasSel = unitPicker?.querySelector('[data-unit-fakultas]');
  const fakultasFixed = unitPicker?.querySelector('[data-unit-fakultas-id]');
  const jurusanWrap = unitPicker?.querySelector('[data-unit-jurusan-wrap]');
  const jurusanSel = unitPicker?.querySelector('[data-unit-jurusan]');
  const prodiWrap = unitPicker?.querySelector('[data-unit-prodi-wrap]');
  const prodiSel = unitPicker?.querySelector('[data-unit-prodi]');
  const manualWrap = unitPicker?.querySelector('[data-unit-manual-wrap]');
  const manualSel = unitPicker?.querySelector('[data-unit-manual]');
  const helper = unitPicker?.querySelector('[data-unit-helper]');

  const adminModeWrap = unitPicker?.querySelector('[data-admin-jurusan-mode-wrap]');
  const adminModeSel = unitPicker?.querySelector('[data-admin-jurusan-mode]');

  if (!roleSel) return;

  const allows = (role) => {
    const r = String(role || '').trim().toUpperCase();
    return r === 'SUPERADMIN' || r === 'ADMIN' || r.startsWith('ADMIN_') || r.startsWith('ADMIN ');
  };

  const allowsJabatan = (role) => String(role || '').trim().toUpperCase() !== 'MAHASISWA';

  const getJabatanText = () => {
    if (!jabatanSelect) return '';
    const v = String(jabatanSelect.value || '').trim();
    if (v && v !== '__other__') return v;
    if (v === '__other__' && jabatanOtherInput) return String(jabatanOtherInput.value || '').trim();
    return '';
  };

  const setHiddenUnitId = (v) => {
    if (!unitIdHidden) return;
    unitIdHidden.value = v ? String(v) : '';
  };

  const parseProdiByJurusan = () => {
    if (!unitPicker) return {};
    try {
      const script = unitPicker.querySelector('[data-prodi-by-jurusan-json]');
      const raw = script ? (script.textContent || '{}') : (unitPicker.getAttribute('data-prodi-by-jurusan') || '{}');
      return JSON.parse(raw || '{}') || {};
    } catch {
      return {};
    }
  };

  let hydrated = false;
  let pendingProdiId = '';
  let pendingScopes = [];

  const findJurusanForProdi = (prodiId) => {
    const map = parseProdiByJurusan();
    const pid = String(prodiId || '');
    if (!pid) return '';
    for (const [jurusanId, list] of Object.entries(map || {})) {
      if (!Array.isArray(list)) continue;
      if (list.some((it) => String(it?.id || '') === pid)) return String(jurusanId);
    }
    return '';
  };

  const renderProdiOptions = (targetSel, items, selectedValue, placeholderText = '-') => {
    if (!targetSel) return;
    const current = selectedValue ? String(selectedValue) : '';
    const list = Array.isArray(items) ? items : [];
    targetSel.replaceChildren();
    const opt0 = document.createElement('option');
    opt0.value = '';
    opt0.textContent = placeholderText;
    targetSel.appendChild(opt0);
    list.forEach((it) => {
      const opt = document.createElement('option');
      opt.value = String(it.id);
      opt.textContent = String(it.name || it.id);
      if (current && opt.value === current) opt.selected = true;
      targetSel.appendChild(opt);
    });
    targetSel.disabled = list.length < 1;
    if (!list.some((it) => String(it?.id || '') === current)) {
      targetSel.value = '';
    }
  };

  const scopedPickerState = new WeakMap();

  const enhanceScopedProdiPicker = (selectEl) => {
    if (!selectEl) return null;
    if (scopedPickerState.has(selectEl)) return scopedPickerState.get(selectEl);

    // Hide native select but keep it for form submission.
    selectEl.classList.add('hidden');

    const wrap = document.createElement('div');
    wrap.className = 'scoped-prodi-picker';

    const top = document.createElement('div');
    top.className = 'scoped-prodi-picker__top';

    const search = document.createElement('input');
    search.type = 'search';
    search.className = 'as-input scoped-prodi-picker__search';
    search.placeholder = 'Cari program studi...';

    const actions = document.createElement('div');
    actions.className = 'scoped-prodi-picker__actions';

    const btnAll = document.createElement('button');
    btnAll.type = 'button';
    btnAll.className = 'scoped-prodi-picker__btn';
    btnAll.textContent = 'Pilih semua';

    const btnClear = document.createElement('button');
    btnClear.type = 'button';
    btnClear.className = 'scoped-prodi-picker__btn';
    btnClear.textContent = 'Kosongkan';

    const counter = document.createElement('div');
    counter.className = 'scoped-prodi-picker__counter';

    actions.appendChild(btnAll);
    actions.appendChild(btnClear);
    actions.appendChild(counter);

    top.appendChild(search);
    top.appendChild(actions);

    const chips = document.createElement('div');
    chips.className = 'scoped-prodi-picker__chips';

    const list = document.createElement('div');
    list.className = 'scoped-prodi-picker__list';

    wrap.appendChild(top);
    wrap.appendChild(chips);
    wrap.appendChild(list);

    selectEl.insertAdjacentElement('afterend', wrap);

    const getOptions = () => Array.from(selectEl.options).filter((o) => String(o.value || '') !== '');

    const render = () => {
      const q = (search.value || '').trim().toLowerCase();
      const opts = getOptions();
      const selected = opts.filter((o) => o.selected);

      counter.textContent = `${selected.length} dipilih`;

      // Chips
      chips.replaceChildren();
      selected.forEach((o) => {
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'scoped-prodi-picker__chip';
        chip.textContent = `${o.textContent || o.value} ×`;
        chip.addEventListener('click', () => {
          o.selected = false;
          selectEl.dispatchEvent(new Event('change', { bubbles: true }));
          render();
        });
        chips.appendChild(chip);
      });

      // List
      list.replaceChildren();
      const filtered = q
        ? opts.filter((o) => (o.textContent || '').toLowerCase().includes(q))
        : opts;

      if (filtered.length < 1) {
        const empty = document.createElement('div');
        empty.className = 'scoped-prodi-picker__empty';
        empty.textContent = 'Tidak ada hasil.';
        list.appendChild(empty);
      } else {
        filtered.forEach((o) => {
          const row = document.createElement('label');
          row.className = `scoped-prodi-picker__item${o.selected ? ' is-selected' : ''}`;

          const cb = document.createElement('input');
          cb.type = 'checkbox';
          cb.checked = o.selected;
          cb.disabled = selectEl.disabled;
          cb.className = 'scoped-prodi-picker__checkbox';

          const txt = document.createElement('span');
          txt.className = 'scoped-prodi-picker__label';
          txt.textContent = (o.textContent || o.value);

          cb.addEventListener('change', () => {
            o.selected = cb.checked;
            selectEl.dispatchEvent(new Event('change', { bubbles: true }));
            render();
          });

          row.appendChild(cb);
          row.appendChild(txt);
          list.appendChild(row);
        });
      }

      // Disabled state
      const disabled = !!selectEl.disabled;
      wrap.classList.toggle('is-disabled', disabled);
      wrap.setAttribute('aria-disabled', disabled ? 'true' : 'false');
      search.disabled = disabled;
      btnAll.disabled = disabled;
      btnClear.disabled = disabled;
    };

    btnAll.addEventListener('click', () => {
      if (selectEl.disabled) return;
      getOptions().forEach((o) => { o.selected = true; });
      selectEl.dispatchEvent(new Event('change', { bubbles: true }));
      render();
    });

    btnClear.addEventListener('click', () => {
      if (selectEl.disabled) return;
      getOptions().forEach((o) => { o.selected = false; });
      selectEl.dispatchEvent(new Event('change', { bubbles: true }));
      render();
    });

    search.addEventListener('input', render, { passive: true });
    selectEl.addEventListener('change', render, { passive: true });
    selectEl.addEventListener('scoped-options-updated', render, { passive: true });

    const api = { render, wrap };
    scopedPickerState.set(selectEl, api);
    render();
    return api;
  };

  const filterScopedProdiOptions = (jurusanId, selectedValues = null) => {
    if (!scopeSel) return;
    const map = parseProdiByJurusan();
    const normalizedJurusanId = String(jurusanId || '');
    const list = normalizedJurusanId !== '' && Array.isArray(map?.[normalizedJurusanId])
      ? map[normalizedJurusanId]
      : [];

    const selected = selectedValues
      ? new Set((selectedValues || []).map((v) => String(v)))
      : new Set(Array.from(scopeSel.selectedOptions).map((o) => String(o.value)));
    scopeSel.replaceChildren();
    list.forEach((it) => {
      const opt = document.createElement('option');
      opt.value = String(it.id);
      opt.textContent = String(it.name || it.id);
      if (selected.has(opt.value)) opt.selected = true;
      scopeSel.appendChild(opt);
    });
    scopeSel.dispatchEvent(new CustomEvent('scoped-options-updated', { bubbles: true }));
    return list;
  };

  const restoreScopedProdiOptions = (selectedValues = null) => {
    if (!scopeSel) return;
    const selected = selectedValues
      ? new Set((selectedValues || []).map((v) => String(v)))
      : new Set(Array.from(scopeSel.selectedOptions).map((o) => String(o.value)));
    scopeSel.replaceChildren();
    initialScopeOptions.forEach((item) => {
      const opt = document.createElement('option');
      opt.value = item.value;
      opt.textContent = item.label;
      if (selected.has(item.value)) {
        opt.selected = true;
      }
      scopeSel.appendChild(opt);
    });
    scopeSel.dispatchEvent(new CustomEvent('scoped-options-updated', { bubbles: true }));
    return initialScopeOptions;
  };

  const show = (el, ok) => {
    if (!el) return;
    el.classList.toggle('hidden', !ok);
  };

  const render = () => {
    // One-time hydration from server-provided initial values (unit_id + scopes)
    if (!hydrated && unitPicker) {
      hydrated = true;
      pendingProdiId = String(unitPicker.getAttribute('data-initial-unit-id') || '');
      try {
        const raw = unitPicker.getAttribute('data-initial-scopes') || '[]';
        const arr = JSON.parse(raw);
        pendingScopes = Array.isArray(arr) ? arr.map((v) => String(v)) : [];
      } catch {
        pendingScopes = [];
      }

      // If initial unit is a prodi, infer jurusan; if jurusan, set directly.
      const initUnitId = pendingProdiId;
      if (jurusanSel && initUnitId) {
        const hasJurusanOpt = Array.from(jurusanSel.options).some((o) => String(o.value) === initUnitId);
        if (hasJurusanOpt) {
          jurusanSel.value = initUnitId;
        } else {
          const inferredJurusan = findJurusanForProdi(initUnitId);
          if (inferredJurusan) jurusanSel.value = inferredJurusan;
        }
      }

      // If scopes exist, ensure admin mode is set to per_prodi for Admin Jurusan (best-effort)
      const jab = getJabatanText();
      if (adminModeSel && jab === 'Admin Jurusan') {
        if (pendingScopes.length > 0) adminModeSel.value = 'per_prodi';
      }
      if (adminModeSel && jab === 'Admin Jurusan per Prodi') {
        adminModeSel.value = 'per_prodi';
      }

      if (manualSel && initUnitId) manualSel.value = initUnitId;
      if (unitIdHidden && initUnitId) unitIdHidden.value = initUnitId;
    }

    const jabatanTextUpper = getJabatanText().toUpperCase();
    const ok = allows(roleSel.value) || jabatanTextUpper === 'ADMIN JURUSAN' || jabatanTextUpper === 'ADMIN JURUSAN PER PRODI';
    if (scopeBlock && scopeSel) {
      scopeBlock.classList.toggle('hidden', !ok);
      scopeSel.disabled = !ok;
      if (ok) enhanceScopedProdiPicker(scopeSel)?.render?.();
    }

    if (jabatanBlock && jabatanSelect) {
      const showJabatan = allowsJabatan(roleSel.value);
      jabatanBlock.classList.toggle('hidden', !showJabatan);
      jabatanSelect.disabled = !showJabatan;
      if (!showJabatan) jabatanSelect.value = '';

      if (jabatanOtherWrap && jabatanOtherInput) {
        const isOther = showJabatan && String(jabatanSelect.value || '') === '__other__';
        jabatanOtherWrap.classList.toggle('hidden', !isOther);
        jabatanOtherInput.disabled = !isOther;
        if (!isOther) jabatanOtherInput.value = '';
      }
    }

    // Unit picker: driven by role + jabatan
    if (unitPicker && unitIdHidden) {
      const role = String(roleSel.value || '').trim().toUpperCase();
      const jabatan = getJabatanText();

      const facultyTitles = new Set([
        'Dekan',
        'Wakil Dekan Bidang Akademik dan Kerja Sama',
        'Wakil Dekan Bidang Umum dan Keuangan',
        'Wakil Dekan Bidang Kemahasiswaan dan Alumni',
      ]);
      const jurusanTitles = new Set([
        'Ketua Jurusan',
        'Sekretaris Jurusan',
      ]);
      const prodiTitles = new Set([
        'Ketua Program Studi',
        'Dosen',
        'Pembimbing Akademik',
      ]);

      const isMahasiswa = role === 'MAHASISWA';
      const isAdminRole = allows(roleSel.value) || jabatanTextUpper === 'ADMIN JURUSAN' || jabatanTextUpper === 'ADMIN JURUSAN PER PRODI';
      const isAdminJurusan = jabatan === 'Admin Jurusan' || jabatan === 'Admin Jurusan per Prodi';
      const isAdminJurusanPerProdi = jabatan === 'Admin Jurusan per Prodi';

      const level = isMahasiswa
        ? 'PRODI'
        : (facultyTitles.has(jabatan) ? 'FAKULTAS'
          : (jurusanTitles.has(jabatan) ? 'JURUSAN'
            : (isAdminJurusan ? 'ADMIN_JURUSAN'
              : (prodiTitles.has(jabatan) ? 'PRODI' : 'MANUAL'))));

      // Defaults
      show(fakultasWrap, false);
      show(jurusanWrap, false);
      show(prodiWrap, false);
      show(manualWrap, false);
      show(adminModeWrap, false);

      if (helper) helper.textContent = 'Unit akan menyesuaikan berdasarkan jabatan/role.';

      if (level === 'FAKULTAS') {
        show(fakultasWrap, true);
        const fid = (fakultasFixed?.getAttribute?.('data-unit-fakultas-id') || '').toString()
          || fakultasSel?.value
          || fakultasSel?.querySelector('option')?.value
          || '';
        setHiddenUnitId(fid);
        if (helper) helper.textContent = 'Dekan/Wakil Dekan otomatis di-set ke tingkat Fakultas.';

        if (scopeSel) {
          scopeSel.replaceChildren();
        }
      } else if (level === 'JURUSAN') {
        show(jurusanWrap, true);
        const jid = jurusanSel?.value || '';
        setHiddenUnitId(jid);
        if (helper) helper.textContent = 'Pilih Jurusan. Unit akan terset ke Jurusan.';

        if (scopeSel) {
          scopeSel.replaceChildren();
        }
      } else if (level === 'PRODI') {
        show(jurusanWrap, true);
        show(prodiWrap, true);
        const map = parseProdiByJurusan();
        const jid = jurusanSel?.value || '';
        const list = jid !== '' && Array.isArray(map?.[String(jid)])
          ? map[String(jid)]
          : [];

        // Keep current prodi if exists
        const currentProdi = prodiSel?.value || pendingProdiId || '';
        const placeholderText = jid === ''
          ? 'Pilih jurusan terlebih dahulu'
          : (list.length > 0 ? 'Pilih program studi' : 'Program studi belum tersedia');
        renderProdiOptions(prodiSel, list, currentProdi, placeholderText);
        if (pendingProdiId) {
          prodiSel.value = pendingProdiId;
          pendingProdiId = '';
        }
        const pid = prodiSel?.value || '';
        setHiddenUnitId(pid);
        if (helper) {
          if (jid === '') {
            helper.textContent = 'Pilih Jurusan terlebih dahulu agar Program Studi langsung terfilter.';
          } else if (list.length > 0) {
            helper.textContent = 'Pilih Jurusan lalu Program Studi. Unit akan terset ke Program Studi.';
          } else {
            helper.textContent = 'Belum ada Program Studi yang terhubung ke Jurusan ini.';
          }
        }

        if (scopeSel) {
          scopeSel.replaceChildren();
        }
      } else if (level === 'ADMIN_JURUSAN') {
        show(jurusanWrap, true);
        show(adminModeWrap, true);
        // If jabatan explicitly says "per Prodi", force per_prodi mode.
        if (adminModeSel) {
          adminModeSel.disabled = isAdminJurusanPerProdi;
          if (isAdminJurusanPerProdi) adminModeSel.value = 'per_prodi';
        }
        const mode = isAdminJurusanPerProdi
          ? 'per_prodi'
          : (String(adminModeSel?.value || 'utama'));

        if (mode === 'utama') {
          const jid = jurusanSel?.value || '';
          setHiddenUnitId(jid);
          if (helper) helper.textContent = 'Admin jurusan utama: pilih Jurusan, otomatis mengelola semua Prodi di Jurusan tersebut.';
          if (scopeBlock) scopeBlock.classList.add('hidden');
          if (scopeSel) {
            scopeSel.disabled = true;
            scopeSel.replaceChildren();
            scopeSel.dispatchEvent(new CustomEvent('scoped-options-updated', { bubbles: true }));
          }
        } else {
          // per_prodi: choose jurusan then pick one/more prodi via scoped multiselect.
          const jid = jurusanSel?.value || '';
          if (scopeBlock && scopeSel) {
            scopeBlock.classList.remove('hidden');
            const scopedOptions = pendingScopes.length > 0
              ? filterScopedProdiOptions(jid, pendingScopes)
              : filterScopedProdiOptions(jid);
            scopeSel.disabled = !isAdminRole || jid === '' || scopedOptions.length < 1;
            if (pendingScopes.length > 0) {
              pendingScopes = [];
            }
          }

          const selectedProdiIds = scopeSel ? Array.from(scopeSel.selectedOptions).map((o) => String(o.value)) : [];
          setHiddenUnitId(selectedProdiIds[0] || '');
          if (helper) {
            if (jid === '') {
              helper.textContent = 'Admin jurusan per prodi: pilih Jurusan terlebih dahulu agar daftar Program Studi terfilter.';
            } else if (scopeSel && scopeSel.options.length < 1) {
              helper.textContent = 'Belum ada Program Studi yang bisa dipilih untuk Jurusan ini.';
            } else {
              helper.textContent = 'Admin jurusan per prodi: pilih Jurusan lalu pilih 1+ Program Studi. Unit akan mengikuti pilihan prodi pertama.';
            }
          }
        }
      } else {
        // Manual fallback
        show(manualWrap, true);
        const mid = manualSel?.value || '';
        setHiddenUnitId(mid);
        if (helper) helper.textContent = 'Pilih unit secara manual.';
        if (scopeBlock && scopeSel && ok) {
          scopeBlock.classList.remove('hidden');
          restoreScopedProdiOptions();
          scopeSel.disabled = false;
        }
      }

      // Keep manual select in sync with hidden unit_id for consistency
      if (manualSel) {
        if (!manualSel.value && unitIdHidden.value) manualSel.value = unitIdHidden.value;
      }
    }
  };

  roleSel.addEventListener('change', render, { passive: true });
  jabatanSelect?.addEventListener('change', render, { passive: true });
  jabatanOtherInput?.addEventListener('input', render, { passive: true });
  jurusanSel?.addEventListener('change', render, { passive: true });
  prodiSel?.addEventListener('change', render, { passive: true });
  manualSel?.addEventListener('change', render, { passive: true });
  adminModeSel?.addEventListener('change', render, { passive: true });
  scopeSel?.addEventListener('change', render, { passive: true });
  render();
}

function initCmsPostsEditAutosave(root) {
  if (!root) return;

  const form = root.querySelector('#cms_post_form');
  const statusEl = root.querySelector('[data-cms-autosave-status]') || root.querySelector('#autosave_status');
  const autosaveUrl = root.getAttribute('data-cms-autosave-url') || '';
  const csrfToken = root.getAttribute('data-cms-csrf-token') || '';

  if (!form || !statusEl || !autosaveUrl || !csrfToken) return;

  let dirty = false;
  let inFlight = false;
  let lastHash = '';

  const pick = (name) => {
    const el = form.querySelector(`[name="${CSS.escape(name)}"]`);
    return el ? (el.value || '') : '';
  };

  const computeHash = () => {
    return [
      pick('title_id'),
      pick('title_en'),
      pick('content_html_id'),
      pick('content_html_en'),
    ].join('::');
  };

  lastHash = computeHash();

  const markDirty = () => { dirty = true; };
  form.addEventListener('input', markDirty, { passive: true });
  form.addEventListener('change', markDirty, { passive: true });

  const autosave = async () => {
    if (inFlight) return;
    const hash = computeHash();
    if (!dirty || hash === lastHash) return;

    inFlight = true;
    statusEl.textContent = 'Autosave: menyimpan…';

    try {
      const res = await fetch(autosaveUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          title_id: pick('title_id'),
          title_en: pick('title_en'),
          content_html_id: pick('content_html_id'),
          content_html_en: pick('content_html_en'),
        }),
      });

      if (!res.ok) {
        const t = await res.text();
        throw new Error(t || ('HTTP ' + res.status));
      }

      const json = await res.json().catch(() => ({}));
      lastHash = hash;
      dirty = false;
      statusEl.textContent = `Autosave: tersimpan (${json.saved_at || 'ok'})`;
    } catch {
      statusEl.textContent = 'Autosave: gagal — cek koneksi/permission';
    } finally {
      inFlight = false;
    }
  };

  window.setInterval(autosave, 10000);
}

function initRealtimeSearchModule(root = document) {
  const normalize = (v) => String(v || '')
    .toLowerCase()
    .normalize('NFD')
    // eslint-disable-next-line no-control-regex
    .replace(/[\u0300-\u036f]/g, '')
    .trim();

  const debounce = (fn, waitMs) => {
    let timer = null;
    return (...args) => {
      if (timer) window.clearTimeout(timer);
      timer = window.setTimeout(() => fn(...args), waitMs);
    };
  };

  // 1) Filter mode: filter existing items on the page (no request).
  root.querySelectorAll('[data-realtime-search-input][data-realtime-search-mode="filter"]').forEach((input) => {
    if (input.dataset.realtimeSearchBound === '1') return;
    input.dataset.realtimeSearchBound = '1';

    const scopeSel = input.getAttribute('data-realtime-search-scope') || '';
    const itemSel = input.getAttribute('data-realtime-search-item-selector') || '[data-realtime-search-item]';
    const emptySel = input.getAttribute('data-realtime-search-empty-selector') || '[data-realtime-search-empty]';
    const countSel = input.getAttribute('data-realtime-search-count-selector') || '[data-realtime-search-count]';

    const scope = scopeSel ? root.querySelector(scopeSel) : root;
    if (!scope) return;

    const emptyEl = scope.querySelector(emptySel) || root.querySelector(emptySel);
    const countEl = scope.querySelector(countSel) || root.querySelector(countSel);
    const defaultCountText = countEl
      ? String(countEl.getAttribute('data-default-count-text') || countEl.textContent || '').trim()
      : '';
    const setShown = (el, shown) => {
      // `hidden` utility can be overridden by component display styles (e.g. display:flex).
      // Force visibility at inline-style level for reliable filtering.
      el.classList.toggle('hidden', !shown);
      if (!shown) el.setAttribute('hidden', 'hidden');
      else el.removeAttribute('hidden');
      el.style.display = shown ? '' : 'none';
    };

    const apply = () => {
      const items = Array.from(scope.querySelectorAll(itemSel));
      const total = items.length;
      const q = normalize(input.value);
      let shown = 0;
      items.forEach((el) => {
        const hay = normalize(el.getAttribute('data-realtime-search-text') || el.textContent || '');
        const match = q === '' || hay.includes(q);
        setShown(el, match);
        if (match) shown += 1;
      });

      if (emptyEl) setShown(emptyEl, shown === 0 && q !== '');
      if (countEl) countEl.textContent = q === '' ? defaultCountText : `Menampilkan ${shown} dari ${total}`;
    };

    input.addEventListener('input', apply, { passive: true });
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') e.preventDefault();
    });
    apply();
  });

  // 2) Submit mode: auto-submit GET filters while typing (debounced).
  root.querySelectorAll('form[data-realtime-search-form="submit"]').forEach((form) => {
    const delay = Number(form.getAttribute('data-realtime-search-delay') || 350);

    const submit = () => {
      const page = form.querySelector('[name="page"]');
      if (page) page.value = '1';
      if (typeof form.requestSubmit === 'function') form.requestSubmit();
      else form.submit();
    };

    const submitDebounced = debounce(submit, delay);

    form.querySelectorAll('input, textarea, select').forEach((el) => {
      const tag = el.tagName.toLowerCase();
      const type = (el.getAttribute('type') || '').toLowerCase();
      if (type === 'hidden' || type === 'submit' || type === 'button') return;
      if (el.disabled) return;

      if (tag === 'select' || type === 'checkbox' || type === 'radio') {
        el.addEventListener('change', submit, { passive: true });
      } else {
        el.addEventListener('input', submitDebounced, { passive: true });
      }
    });
  });
}

function initAdminSearchCards(root = document) {
  root.querySelectorAll('[data-admin-search-card]').forEach((card) => {
    if (card.dataset.adminSearchBound === '1') return;
    card.dataset.adminSearchBound = '1';

    const input = card.querySelector('[data-realtime-search-input]');
    const clearButton = card.querySelector('[data-admin-search-clear]');
    const trackedFilters = Array.from(card.querySelectorAll('[data-admin-search-track]'));
    if (!input || !clearButton) return;

    const resetUrl = String(clearButton.getAttribute('data-reset-url') || window.location.pathname || '').trim();
    const inputName = String(input.getAttribute('name') || '').trim();
    const hasKeyword = () => !!String(input.value || '').trim();
    const hasFilters = () => trackedFilters.some((el) => !!String(el.value || '').trim());
    const hasActiveQueryFilters = () => {
      const params = new URLSearchParams(window.location.search || '');
      const trackedFilterActive = trackedFilters.some((el) => {
        const name = String(el.getAttribute('name') || '').trim();
        return name ? !!String(params.get(name) || '').trim() : false;
      });
      if (trackedFilterActive) return true;
      return inputName ? !!String(params.get(inputName) || '').trim() : false;
    };

    const syncClearState = () => {
      clearButton.classList.toggle('admin-search__clear--disabled', !hasKeyword() && !hasFilters() && !hasActiveQueryFilters());
    };

    input.addEventListener('input', syncClearState, { passive: true });
    trackedFilters.forEach((el) => {
      el.addEventListener('change', syncClearState, { passive: true });
    });

    clearButton.addEventListener('click', (event) => {
      event.preventDefault();
      if (hasKeyword()) {
        input.value = '';
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
        input.focus();
        syncClearState();
        return;
      }

      if (hasFilters() || hasActiveQueryFilters()) {
        if (resetUrl) window.location.assign(resetUrl);
      }
    });

    syncClearState();
  });
}

function initSignerRequestShow(root) {
  if (!root) return;

  const mainColumn = root.querySelector('[data-ss-main-column]');
  const snapshotCard = root.querySelector('[data-ss-snapshot-card]');
  const snapshotScroll = root.querySelector('[data-ss-snapshot-scroll]');
  if (!mainColumn || !snapshotCard || !snapshotScroll) return;

  const syncSnapshotHeight = () => {
    const isDesktop = window.matchMedia('(min-width: 1200px)').matches;
    if (!isDesktop) {
      snapshotCard.style.maxHeight = '';
      snapshotScroll.style.maxHeight = '';
      snapshotScroll.style.overflowY = '';
      return;
    }

    const mainHeight = mainColumn.getBoundingClientRect().height;
    if (!mainHeight || mainHeight <= 0) return;

    const cardStyle = window.getComputedStyle(snapshotCard);
    const cardPaddingY =
      (parseFloat(cardStyle.paddingTop || '0') || 0) +
      (parseFloat(cardStyle.paddingBottom || '0') || 0);
    const headerHeight =
      snapshotCard.querySelector('.ars-card-header')?.getBoundingClientRect().height || 0;
    const scrollMarginTop =
      parseFloat(window.getComputedStyle(snapshotScroll).marginTop || '0') || 0;

    const cardMaxHeight = Math.max(260, Math.floor(mainHeight));
    const scrollMaxHeight = Math.max(
      180,
      Math.floor(mainHeight - cardPaddingY - headerHeight - scrollMarginTop),
    );

    snapshotCard.style.maxHeight = `${cardMaxHeight}px`;
    snapshotScroll.style.maxHeight = `${scrollMaxHeight}px`;
    snapshotScroll.style.overflowY = 'auto';
  };

  if ('ResizeObserver' in window) {
    const observer = new ResizeObserver(() => syncSnapshotHeight());
    observer.observe(mainColumn);
    observer.observe(snapshotCard);
  }

  window.addEventListener('resize', syncSnapshotHeight, { passive: true });
  window.addEventListener('load', syncSnapshotHeight);
  requestAnimationFrame(syncSnapshotHeight);
}

ensureCertificateSignerEditorFactory();

document.addEventListener('DOMContentLoaded', () => {
  let saved = null;
  try {
    saved = localStorage.getItem('ult_theme');
  } catch {
    saved = null;
  }
  if (saved) {
    applyThemeMode(saved === 'dark', { persist: false, animate: false });
  }

  initAvatarFallbacks(document);

  const publicRoot = document.querySelector('.page-public-site');
  if (publicRoot) initPublicSite(publicRoot);
  const publicHomeRoot = document.querySelector('.page-public-home.ult-home');
  if (publicHomeRoot) initPublicHome(publicHomeRoot);
  document.querySelectorAll('#servicesIndexPage.page-services-index, #aboutPage.page-services-index, #userGuidesIndexPage.page-services-index, #feedbackCreatePage.page-services-index').forEach(
    (servicesIndexRoot) => {
      initPublicServicesIndex(servicesIndexRoot);
    }
  );
  const announcementsIndexRoot = document.querySelector('.page-announcements-index.page-services-index.services-v2');
  if (announcementsIndexRoot) initPublicAnnouncementsIndex(announcementsIndexRoot);
  const blogIndexRoot = document.querySelector('#blogIndexPage.page-blog-index.page-services-index.services-v2');
  if (blogIndexRoot) initPublicBlogIndex(blogIndexRoot);
  const serviceShowRoot = document.querySelector(
    '#serviceShowPage.page-services-show, #announcementShowPage.page-services-show, #blogShowPage.page-services-show, #userGuideShowPage.page-services-show'
  );
  if (serviceShowRoot) initPublicServiceShow(serviceShowRoot);

  initTiptapEditors(document);

  const appRoot = document.querySelector('.page-app-shell');
  if (appRoot) {
    initAppShell(appRoot);
    initConfirmDialogs(appRoot);
  }

  const adminUsersAuditWideRoot = document.querySelector(
    '.page-admin-users-index[data-admin-users-index-page], .page-admin-users-roles[data-admin-users-roles-page], .page-admin-audit-index[data-admin-audit-index-page]'
  );
  if (adminUsersAuditWideRoot) {
    const contentContainer = adminUsersAuditWideRoot.closest('.app-shell__container');
    if (contentContainer) contentContainer.classList.add('page-admin-users-audit-wide');

    const topbarContainer = document.querySelector('.page-app-shell .app-topbar .app-shell__container');
    if (topbarContainer) topbarContainer.classList.add('page-admin-users-audit-wide');
  }

  // CMS pages: widen layout container without relying on :has()
  const cmsWideRoot = document.querySelector('.page-admin-cms[data-cms-page]');
  if (cmsWideRoot) {
    const container = cmsWideRoot.closest('.app-shell__container');
    if (container) container.classList.add('is-cms-wide');

    const topbarContainer = document.querySelector('.page-app-shell .app-topbar .app-shell__container');
    if (topbarContainer) topbarContainer.classList.add('is-cms-wide');
  }

  const docFormatsWideRoot = document.querySelector(
    '.page-admin-doc-formats-index, .page-admin-doc-formats-create, .page-admin-doc-formats-edit'
  );
  if (docFormatsWideRoot) {
    const container = docFormatsWideRoot.closest('.app-shell__container');
    if (container) container.classList.add('is-doc-formats-wide');
  }

  const userGuidesWideRoot = document.querySelector('.page-admin-user-guides-create, .page-admin-user-guides-edit');
  if (userGuidesWideRoot) {
    const container = userGuidesWideRoot.closest('.app-shell__container');
    if (container) container.classList.add('is-user-guides-wide');
  }

  const adminServicesGuideRoot = document.querySelector('.page-admin-services-guide[data-services-guide-page]');
  if (adminServicesGuideRoot) {
    const container = adminServicesGuideRoot.closest('.app-shell__container');
    if (container) container.classList.add('is-services-guide-wide');
  }

  const profileEditRoot = document.querySelector('.page-profile-edit[data-profile-edit-page]');
  if (profileEditRoot) {
    const contentContainer = profileEditRoot.closest('.app-shell__container');
    if (contentContainer) contentContainer.classList.add('is-profile-edit-wide');

    const topbarContainer = document.querySelector('.page-app-shell .app-topbar .app-shell__container');
    if (topbarContainer) topbarContainer.classList.add('is-profile-edit-wide');
  }

  const adminServicesDocRoot = document.querySelector('.page-admin-services-doc[data-services-doc-page]');
  if (adminServicesDocRoot) {
    const container = adminServicesDocRoot.closest('.app-shell__container');
    if (container) container.classList.add('is-services-doc-wide');
    initAdminServicesDoc(adminServicesDocRoot);
  }

  const adminServicesFormRoot = document.querySelector('[data-services-form][data-translate-url]');
  if (adminServicesFormRoot) {
    const container = adminServicesFormRoot.closest('.app-shell__container');
    if (container) container.classList.add('is-services-wide');
    initAdminServicesForm(adminServicesFormRoot);
  }

  const studentRequestsCreateRoot = document.querySelector('.page-student-requests-create[data-student-requests-create-page]');
  if (studentRequestsCreateRoot) {
    const contentContainer = studentRequestsCreateRoot.closest('.app-shell__container');
    if (contentContainer) contentContainer.classList.add('is-student-requests-create-wide');

    const topbarContainer = document.querySelector('.page-app-shell .app-topbar .app-shell__container');
    if (topbarContainer) topbarContainer.classList.add('is-student-requests-create-wide');
  }

  const certificateEditorRoot = document.querySelector('.page-student-requests-certificate[data-student-requests-certificate-page]');
  if (certificateEditorRoot) {
    const hostPage = certificateEditorRoot.closest('.page-student-requests-create, .page-student-requests-show');
    if (hostPage) hostPage.classList.add('has-certificate-editor');

    const contentContainer = certificateEditorRoot.closest('.app-shell__container');
    if (contentContainer) contentContainer.classList.add('is-student-requests-certificate-wide');

    const topbarContainer = document.querySelector('.page-app-shell .app-topbar .app-shell__container');
    if (topbarContainer) topbarContainer.classList.add('is-student-requests-certificate-wide');
  }

  const cmsSettingsFormRoot = document.querySelector('[data-cms-settings-form][data-translate-url]');
  if (cmsSettingsFormRoot) {
    initCmsSettingsForm(cmsSettingsFormRoot);
  }

  initJurusanProdiSelects(document);
  initCustomFileFields(document);

  const adminUserGuidesFormRoot = document.querySelector('[data-user-guides-form][data-translate-url]');
  if (adminUserGuidesFormRoot) {
    initAdminUserGuidesForm(adminUserGuidesFormRoot);
  }

  const staffAssembleRoot = document.querySelector('.page-staff-assemble-show[data-staff-assemble-show-page]');
  if (staffAssembleRoot) {
    const contentContainer = staffAssembleRoot.closest('.app-shell__container');
    if (contentContainer) contentContainer.classList.add('is-staff-assemble-wide');

    const topbarContainer = document.querySelector('.page-app-shell .app-topbar .app-shell__container');
    if (topbarContainer) topbarContainer.classList.add('is-staff-assemble-wide');

    initStaffAssembleShow(staffAssembleRoot);
  }

  const adminRequestsShowRoot = document.querySelector('.page-admin-requests-show');
  if (adminRequestsShowRoot) initAdminRequestsShow(adminRequestsShowRoot);

  const signerRequestShowRoot = document.querySelector('.page-signer-show[data-signer-request-show-page]');
  if (signerRequestShowRoot) initSignerRequestShow(signerRequestShowRoot);

  const adminUsersRoot = document.querySelector('.page-admin-users-create, .page-admin-users-edit');
  if (adminUsersRoot) initAdminUsersRoleScope(adminUsersRoot);

  const cmsPostsEditRoot = document.querySelector('.page-admin-cms-posts-edit');
  if (cmsPostsEditRoot) initCmsPostsEditAutosave(cmsPostsEditRoot);

  const studentRequestsShowRoot = document.querySelector('.page-student-requests-show');
  if (studentRequestsShowRoot) {
    initStudentRequestsShow(studentRequestsShowRoot);
  }

  const studentRequestsIndexRoot = document.querySelector('.page-student-requests-index[data-student-requests-index-page]');
  if (studentRequestsIndexRoot) {
    initStudentRequestsIndex(studentRequestsIndexRoot);
  }

  initScrollableUserSelects(document);
  bootScrollableUserSelectsObserver(document);

  initRealtimeSearchModule(document);
  initAdminSearchCards(document);
  initInfiniteLists(document);

  initAuthPages();
});

Alpine.start();
