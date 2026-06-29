import docx
import re
import copy
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.text.run import Run
import sys

# Daftar kata asing (huruf kecil semua untuk pencocokan case-insensitive)
foreign_words = [
    "website", "web", "dashboard", "controller", "framework", "database", 
    "user", "experience", "tracking", "real-time", "online", "offline",
    "login", "logout", "password", "username", "email", "server", "client",
    "frontend", "backend", "interface", "use case", "flowchart", "sequence", 
    "diagram", "activity", "wireframe", "layout", "template", "file", "folder",
    "upload", "download", "export", "import", "error", "bug", "role-based", 
    "access", "control", "rbac", "content", "security", "policy", "single", 
    "sign-on", "sso", "openxml", "docx", "html", "xss", "view", "model", "form",
    "input", "output", "button", "submit", "query", "table", "column", "row",
    "primary key", "json", "xml", "api", "rest", "http", "request", "response",
    "token", "auth", "session", "cookie", "cache", "local", "storage",
    "public", "private", "protected", "function", "method", "class", "variable",
    "parameter", "route", "middleware", "filter", "event", "listener", "port",
    "host", "ip", "domain", "url", "uri", "ssl", "tls", "encrypt", "decrypt",
    "hash", "algorithm", "responsive", "mobile", "desktop", "tablet", "screen",
    "padding", "border", "color", "background", "font", "text", "align", "hover",
    "active", "focus", "disabled", "checked", "script", "style", "link", "meta",
    "title", "head", "body", "div", "span", "addie", "analysis", "design",
    "development", "implementation", "evaluation", "purposive", "sampling",
    "gatekeeper", "approver", "issue", "number", "assembly", "signer", "portal",
    "student", "admin", "staff", "placeholder", "document", "assembler",
    "service", "render", "wysiwyg", "tiptap", "editor", "richtext", "sanitizer",
    "clean", "tag", "inline", "break", "block", "list", "bullet", "degradation",
    "parser", "targeted", "unit", "test", "pass", "baseline", "center", "left",
    "page", "manual", "true", "false", "italic", "equation", "spacing", "before",
    "after", "caption", "high-res", "update", "delete", "insert", "select", 
    "margin", "bold", "underline", "testing", "deploy", "build", "run", "debug"
]

# Sort by length descending to match longest phrases first (e.g. "primary key" before "primary")
foreign_words.sort(key=len, reverse=True)

# Build a single regex pattern for all words
pattern_str = r'\b(' + '|'.join(re.escape(w) for w in foreign_words) + r')\b'
pattern = re.compile(pattern_str, re.IGNORECASE)

def process_document(file_path):
    print(f"Processing {file_path}...")
    doc = docx.Document(file_path)
    
    total_modified = 0
    
    for p in doc.paragraphs:
        in_field = False
        
        # We need to iterate over children of the paragraph element
        # But we might be adding elements, so we iterate over a snapshot
        children = list(p._element)
        
        for child in children:
            if child.tag == qn('w:fldSimple'):
                # It's a simple field (like page number, or simple citation), skip
                continue
                
            if child.tag == qn('w:r'):
                # Check for field chars
                fldChars = child.findall(qn('w:fldChar'))
                for fc in fldChars:
                    char_type = fc.get(qn('w:fldCharType'))
                    if char_type == 'begin':
                        in_field = True
                    elif char_type == 'end':
                        in_field = False
                        
                # Skip if we are inside a field (Mendeley citation, etc)
                if in_field:
                    continue
                    
                # Skip if it has instrText
                if child.find(qn('w:instrText')) is not None:
                    continue
                    
                # Safe to process this run
                run = Run(child, p)
                text = run.text
                if not text:
                    continue
                    
                matches = list(pattern.finditer(text))
                if not matches:
                    continue
                    
                # We found foreign words! Let's split the run
                current_run_element = child
                
                # We process matches from end to beginning so we don't mess up indices
                # Actually, simpler is to split the text into a list of (text, is_foreign)
                segments = []
                last_idx = 0
                for match in matches:
                    start, end = match.span()
                    if start > last_idx:
                        segments.append((text[last_idx:start], False))
                    segments.append((text[start:end], True))
                    last_idx = end
                if last_idx < len(text):
                    segments.append((text[last_idx:], False))
                    
                # Now we apply the segments
                # First segment replaces the current run's text
                first_seg_text, first_seg_is_foreign = segments[0]
                run.text = first_seg_text
                if first_seg_is_foreign:
                    run.font.italic = True
                    total_modified += 1
                    
                # Subsequent segments are inserted as new runs after the current one
                last_inserted_element = current_run_element
                for seg_text, seg_is_foreign in segments[1:]:
                    # Duplicate original run formatting
                    new_r = copy.deepcopy(child)
                    
                    # Clear text nodes
                    for t in new_r.findall(qn('w:t')):
                        new_r.remove(t)
                        
                    # Add new text node
                    t_el = OxmlElement('w:t')
                    t_el.text = seg_text
                    if seg_text.startswith(' ') or seg_text.endswith(' '):
                        t_el.set(qn('xml:space'), 'preserve')
                    new_r.append(t_el)
                    
                    # Insert it
                    last_inserted_element.addnext(new_r)
                    last_inserted_element = new_r
                    
                    # Modify font italic for the new run wrapper
                    new_run_wrapper = Run(new_r, p)
                    if seg_is_foreign:
                        new_run_wrapper.font.italic = True
                        total_modified += 1
                    else:
                        # Ensure it keeps original non-italic formatting if it was not italic
                        # (actually if the original was already italic, maybe it's fine)
                        pass

    # Save to the same file path
    doc.save(file_path)
    print(f"Completed! Total words italicized: {total_modified}")


if __name__ == "__main__":
    docs = [
        r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Clean.docx",
        r"C:\laragon\www\ult-fkip-unila\docs\skripsi\hasil_update\001_Skripsi_Andricha Dea Mitra_Highlighted.docx"
    ]
    for d in docs:
        process_document(d)
