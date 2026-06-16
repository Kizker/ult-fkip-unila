import { Editor } from '@tiptap/core';
import { Extension } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import BaseImage from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import TextAlign from '@tiptap/extension-text-align';
import Youtube from '@tiptap/extension-youtube';

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
  } else if (host === 'youtube-nocookie.com') {
    if (url.pathname.startsWith('/embed/')) id = url.pathname.split('/')[2] || '';
  }

  if (!id) return null;
  id = id.split('?')[0].split('&')[0];

  return `https://www.youtube-nocookie.com/embed/${id}`;
};

const TabIndentExtension = Extension.create({
  name: 'tabIndent',
  addKeyboardShortcuts() {
    return {
      Tab: () => {
        // Always prevent browser from moving focus on Tab.
        // If we're in a list item, Tab indents (sink). Otherwise keep as no-op to avoid deleting selection.
        try {
          const { empty } = this.editor.state.selection;
          const canSink = this.editor.can().chain().focus().sinkListItem('listItem').run();
          if (canSink) {
            this.editor.chain().focus().sinkListItem('listItem').run();
            return true;
          }

          // Outside lists: only insert a tab character when selection is empty.
          if (empty) {
            this.editor.chain().focus().insertContent('\t').run();
          }
          return true;
        } catch {
          return true;
        }
      },
      'Shift-Tab': () => {
        // Always prevent browser from moving focus on Shift+Tab.
        try {
          const canLift = this.editor.can().chain().focus().liftListItem('listItem').run();
          if (canLift) {
            this.editor.chain().focus().liftListItem('listItem').run();
          }
          return true;
        } catch {
          return true;
        }
      },
    };
  },
});

const Image = BaseImage.extend({
  addAttributes() {
    return {
      ...this.parent?.(),
      width: {
        default: null,
        parseHTML: (element) => {
          const v = element.getAttribute('width');
          return v ? parseInt(v, 10) : null;
        },
        renderHTML: (attributes) => {
          const v = attributes.width;
          if (!v || Number.isNaN(Number(v))) return {};
          return { width: String(Math.max(1, parseInt(String(v), 10))) };
        },
      },
      height: {
        default: null,
        parseHTML: (element) => {
          const v = element.getAttribute('height');
          return v ? parseInt(v, 10) : null;
        },
        renderHTML: (attributes) => {
          const v = attributes.height;
          if (!v || Number.isNaN(Number(v))) return {};
          return { height: String(Math.max(1, parseInt(String(v), 10))) };
        },
      },
      class: {
        default: null,
        parseHTML: (element) => element.getAttribute('class'),
        renderHTML: (attributes) => {
          if (!attributes.class) return {};
          return { class: attributes.class };
        },
      },
    };
  },

  addNodeView() {
    return ({ node, editor, getPos }) => {
      let currentNode = node;

      const dom = document.createElement('span');
      dom.className = 'tiptap-image-resizer';
      dom.contentEditable = 'false';

      const img = document.createElement('img');
      img.draggable = false;
      img.alt = (currentNode.attrs.alt || '').toString();
      img.title = (currentNode.attrs.title || '').toString();
      img.src = (currentNode.attrs.src || '').toString();

      const alignClasses = ['media-align-left', 'media-align-center', 'media-align-right'];
      const applyAlignClass = (className) => {
        dom.classList.remove(...alignClasses);
        img.classList.remove(...alignClasses);
        dom.style.marginLeft = '';
        dom.style.marginRight = '';
        dom.style.width = '';
        dom.style.display = '';
        const cls = (className || '').toString();
        const found = alignClasses.find((c) => cls.split(/\s+/).includes(c));
        if (found) {
          dom.classList.add(found);
          img.classList.add(found);
          dom.style.display = 'block';
          dom.style.width = 'fit-content';
          if (found === 'media-align-center') {
            dom.style.marginLeft = 'auto';
            dom.style.marginRight = 'auto';
          } else if (found === 'media-align-right') {
            dom.style.marginLeft = 'auto';
            dom.style.marginRight = '0';
          } else {
            dom.style.marginLeft = '0';
            dom.style.marginRight = 'auto';
          }
        }
      };

      const applyAttrsToImg = (attrs) => {
        img.src = (attrs.src || '').toString();
        img.alt = (attrs.alt || '').toString();
        img.title = (attrs.title || '').toString();
        img.className = (attrs.class || '').toString();
        applyAlignClass(attrs.class);

        const w = attrs.width ? parseInt(String(attrs.width), 10) : null;
        const h = attrs.height ? parseInt(String(attrs.height), 10) : null;

        img.style.width = w ? `${w}px` : '';
        img.style.height = h ? `${h}px` : '';
      };

      applyAttrsToImg(currentNode.attrs);
      dom.appendChild(img);

      const makeHandle = (corner) => {
        const h = document.createElement('span');
        h.className = `tiptap-image-handle tiptap-image-handle--${corner}`;
        h.dataset.corner = corner;
        return h;
      };

      const handles = ['nw', 'ne', 'sw', 'se'].map(makeHandle);
      handles.forEach((h) => dom.appendChild(h));

      const clamp = (v, min, max) => Math.max(min, Math.min(max, v));

      const commitSize = (widthPx, heightPx) => {
        const pos = typeof getPos === 'function' ? getPos() : null;
        if (typeof pos !== 'number') return;

        const next = {
          ...currentNode.attrs,
          width: widthPx,
          height: heightPx,
        };

        editor.commands.command(({ tr }) => {
          tr.setNodeMarkup(pos, undefined, next);
          return true;
        });
      };

      let dragging = false;
      let startX = 0;
      let startY = 0;
      let startW = 0;
      let startH = 0;
      let ratio = 1;
      let corner = 'se';

      const onMove = (ev) => {
        if (!dragging) return;
        ev.preventDefault();

        const dx = ev.clientX - startX;
        const dy = ev.clientY - startY;

        const signX = corner.includes('w') ? -1 : 1;
        const signY = corner.includes('n') ? -1 : 1;

        // Use the dominant movement to resize proportionally
        const delta = Math.abs(dx) >= Math.abs(dy) ? dx : dy;
        let nextW = Math.round(startW + signX * delta);
        nextW = clamp(nextW, 60, 1200);
        const nextH = Math.round(nextW / ratio);

        img.style.width = `${nextW}px`;
        img.style.height = `${nextH}px`;
      };

      const onUp = (ev) => {
        if (!dragging) return;
        ev.preventDefault();

        dragging = false;
        document.removeEventListener('mousemove', onMove, true);
        document.removeEventListener('mouseup', onUp, true);

        const rect = img.getBoundingClientRect();
        const widthPx = clamp(Math.round(rect.width), 60, 1200);
        const heightPx = clamp(Math.round(rect.height), 40, 2000);
        commitSize(widthPx, heightPx);
      };

      for (const h of handles) {
        h.addEventListener('mousedown', (ev) => {
          ev.preventDefault();
          ev.stopPropagation();

          const rect = img.getBoundingClientRect();
          startX = ev.clientX;
          startY = ev.clientY;
          startW = rect.width || 1;
          startH = rect.height || 1;
          ratio = startW / startH;
          corner = h.dataset.corner || 'se';

          dragging = true;
          document.addEventListener('mousemove', onMove, true);
          document.addEventListener('mouseup', onUp, true);

          // ensure node selection
          try {
            const pos = typeof getPos === 'function' ? getPos() : null;
            if (typeof pos === 'number') editor.commands.setNodeSelection(pos);
          } catch {
            // ignore
          }
        });
      }

      dom.addEventListener('mousedown', (ev) => {
        // Click on image selects node
        const pos = typeof getPos === 'function' ? getPos() : null;
        if (typeof pos === 'number') {
          editor.commands.setNodeSelection(pos);
        }
      });

      return {
        dom,
        update(updatedNode) {
          if (updatedNode.type.name !== currentNode.type.name) return false;
          currentNode = updatedNode;
          applyAttrsToImg(currentNode.attrs);
          return true;
        },
        selectNode() {
          dom.classList.add('is-selected');
        },
        deselectNode() {
          dom.classList.remove('is-selected');
        },
        destroy() {
          document.removeEventListener('mousemove', onMove, true);
          document.removeEventListener('mouseup', onUp, true);
        },
      };
    };
  },
});

const ResizableYoutube = Youtube.extend({
  addAttributes() {
    return {
      ...this.parent?.(),
      class: {
        default: null,
        parseHTML: (element) => element.getAttribute('class'),
        renderHTML: (attributes) => {
          if (!attributes.class) return {};
          return { class: attributes.class };
        },
      },
      width: {
        default: 640,
        parseHTML: (element) => {
          const v = element.getAttribute('width');
          const n = v ? parseInt(v, 10) : 640;
          return Number.isFinite(n) ? n : 640;
        },
        renderHTML: (attributes) => {
          const v = attributes.width;
          const n = v ? parseInt(String(v), 10) : null;
          if (!n || Number.isNaN(Number(n))) return {};
          return { width: String(Math.max(1, n)) };
        },
      },
      height: {
        default: 360,
        parseHTML: (element) => {
          const v = element.getAttribute('height');
          const n = v ? parseInt(v, 10) : 360;
          return Number.isFinite(n) ? n : 360;
        },
        renderHTML: (attributes) => {
          const v = attributes.height;
          const n = v ? parseInt(String(v), 10) : null;
          if (!n || Number.isNaN(Number(n))) return {};
          return { height: String(Math.max(1, n)) };
        },
      },
    };
  },

  addNodeView() {
    return ({ node, editor, getPos }) => {
      let currentNode = node;

      const dom = document.createElement('span');
      dom.className = 'tiptap-video-resizer';
      dom.contentEditable = 'false';

      const iframe = document.createElement('iframe');
      iframe.setAttribute('frameborder', '0');
      iframe.setAttribute('allowfullscreen', 'true');
      iframe.setAttribute(
        'allow',
        'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share'
      );
      iframe.tabIndex = -1;

      const alignClasses = ['media-align-left', 'media-align-center', 'media-align-right'];
      const applyAlignClass = (className) => {
        dom.classList.remove(...alignClasses);
        iframe.classList.remove(...alignClasses);
        dom.style.marginLeft = '';
        dom.style.marginRight = '';
        dom.style.width = '';
        dom.style.display = '';
        const cls = (className || '').toString();
        const found = alignClasses.find((c) => cls.split(/\s+/).includes(c));
        if (found) {
          dom.classList.add(found);
          iframe.classList.add(found);
          dom.style.display = 'block';
          dom.style.width = 'fit-content';
          if (found === 'media-align-center') {
            dom.style.marginLeft = 'auto';
            dom.style.marginRight = 'auto';
          } else if (found === 'media-align-right') {
            dom.style.marginLeft = 'auto';
            dom.style.marginRight = '0';
          } else {
            dom.style.marginLeft = '0';
            dom.style.marginRight = 'auto';
          }
        }
      };

      const applyAttrsToIframe = (attrs) => {
        const src = (attrs.src || '').toString();
        iframe.src = src;

        iframe.className = (attrs.class || '').toString();
        applyAlignClass(attrs.class);

        const w = attrs.width ? parseInt(String(attrs.width), 10) : 640;
        const h = attrs.height ? parseInt(String(attrs.height), 10) : 360;
        iframe.style.width = `${Math.max(200, w)}px`;
        iframe.style.height = `${Math.max(120, h)}px`;
        // Make it selectable/resizable in editor (iframes don't bubble events reliably).
        iframe.style.pointerEvents = 'none';
      };

      applyAttrsToIframe(currentNode.attrs);
      dom.appendChild(iframe);

      const makeHandle = (corner) => {
        const h = document.createElement('span');
        h.className = `tiptap-video-handle tiptap-video-handle--${corner}`;
        h.dataset.corner = corner;
        return h;
      };

      const handles = ['nw', 'ne', 'sw', 'se'].map(makeHandle);
      handles.forEach((h) => dom.appendChild(h));

      const clamp = (v, min, max) => Math.max(min, Math.min(max, v));

      const commitSize = (widthPx, heightPx) => {
        const pos = typeof getPos === 'function' ? getPos() : null;
        if (typeof pos !== 'number') return;

        const next = {
          ...currentNode.attrs,
          width: widthPx,
          height: heightPx,
        };

        editor.commands.command(({ tr }) => {
          tr.setNodeMarkup(pos, undefined, next);
          return true;
        });
      };

      let dragging = false;
      let startX = 0;
      let startY = 0;
      let startW = 0;
      let startH = 0;
      let ratio = 16 / 9;
      let corner = 'se';

      const onMove = (ev) => {
        if (!dragging) return;
        ev.preventDefault();

        const dx = ev.clientX - startX;
        const dy = ev.clientY - startY;

        const signX = corner.includes('w') ? -1 : 1;
        const signY = corner.includes('n') ? -1 : 1;

        const delta = Math.abs(dx) >= Math.abs(dy) ? dx : dy;
        let nextW = Math.round(startW + signX * delta);
        nextW = clamp(nextW, 240, 1200);
        const nextH = clamp(Math.round(nextW / ratio), 135, 2000);

        iframe.style.width = `${nextW}px`;
        iframe.style.height = `${nextH}px`;
      };

      const onUp = (ev) => {
        if (!dragging) return;
        ev.preventDefault();

        dragging = false;
        document.removeEventListener('mousemove', onMove, true);
        document.removeEventListener('mouseup', onUp, true);

        const rect = iframe.getBoundingClientRect();
        const widthPx = clamp(Math.round(rect.width), 240, 1200);
        const heightPx = clamp(Math.round(rect.height), 135, 2000);
        commitSize(widthPx, heightPx);
      };

      for (const h of handles) {
        h.addEventListener('mousedown', (ev) => {
          ev.preventDefault();
          ev.stopPropagation();

          const rect = iframe.getBoundingClientRect();
          startX = ev.clientX;
          startY = ev.clientY;
          startW = rect.width || 640;
          startH = rect.height || 360;
          ratio = startW / startH;
          corner = h.dataset.corner || 'se';

          dragging = true;
          document.addEventListener('mousemove', onMove, true);
          document.addEventListener('mouseup', onUp, true);

          try {
            const pos = typeof getPos === 'function' ? getPos() : null;
            if (typeof pos === 'number') editor.commands.setNodeSelection(pos);
          } catch {
            // ignore
          }
        });
      }

      dom.addEventListener('mousedown', () => {
        const pos = typeof getPos === 'function' ? getPos() : null;
        if (typeof pos === 'number') editor.commands.setNodeSelection(pos);
      });

      return {
        dom,
        update(updatedNode) {
          if (updatedNode.type.name !== currentNode.type.name) return false;
          currentNode = updatedNode;
          applyAttrsToIframe(currentNode.attrs);
          return true;
        },
        selectNode() {
          dom.classList.add('is-selected');
        },
        deselectNode() {
          dom.classList.remove('is-selected');
        },
        destroy() {
          document.removeEventListener('mousemove', onMove, true);
          document.removeEventListener('mouseup', onUp, true);
        },
      };
    };
  },

  renderHTML({ HTMLAttributes }) {
    const src = normalizeYoutubeEmbedUrl(HTMLAttributes.src) || (HTMLAttributes.src || '');
    const width = HTMLAttributes.width || this.options.width || 640;
    const height = HTMLAttributes.height || this.options.height || 360;
    const cls = HTMLAttributes.class || null;

    return [
      'div',
      { 'data-youtube-video': '' },
      [
        'iframe',
        {
          src,
          width,
          height,
          ...(cls ? { class: cls } : {}),
          frameborder: '0',
          allowfullscreen: 'true',
          allow: 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share',
        },
      ],
    ];
  },
});

/**
 * Simple Tiptap wrapper for Blade (StarterKit).
 * - Renders to a contenteditable element
 * - Syncs HTML to a hidden textarea input
 *
 * Security note:
 * - HTML MUST be sanitized server-side (allowlist) before saving (anti XSS).
 */
export function mountTiptap({ el, input, initialHtml = '' }) {
  const editor = new Editor({
    element: el,
    extensions: [
      StarterKit,
      TextAlign.configure({ types: ['heading', 'paragraph'] }),
      Link.configure({
        openOnClick: false,
        autolink: true,
        linkOnPaste: true,
        HTMLAttributes: {
          target: '_blank',
          rel: 'noopener noreferrer',
        },
      }),
      ResizableYoutube.configure({ controls: true, nocookie: true }),
      Image.configure({ allowBase64: false }),
      TabIndentExtension,
    ],
    content: initialHtml,
    onUpdate({ editor }) {
      input.value = editor.getHTML();
    },
  });

  // initial sync
  input.value = editor.getHTML();

  return editor;
}
