# Modul Layanan Dokumen — API Contract (MVP)

Dokumen ini mencakup endpoint yang **khusus** untuk FASE AWAL + FASE UTAMA modul layanan dokumen.

## Auth & Security

- Semua endpoint di bawah wajib `auth` + `verified`.
- Semua file template / signature / output disimpan di **private storage** (`config('ult.private_disk')`).
- Semua download output harus lolos policy `RequestPolicy@view` + `RequestOutputPolicy@download` (anti-IDOR) + `throttle:downloads`.
- Semua aksi kritikal tercatat di `audit_logs` (append-only).

## A. FASE AWAL — Setup (SUPERADMIN)

Catatan: akses di-guard oleh permission `doc_services.manage` (hanya role `Superadmin`/`SUPERADMIN`).

### A1) UI Setup

- `GET /admin/layanan/{layanan}/dokumen`
  - Permission: `doc_services.manage`
  - Response: Blade view setup layanan dokumen

### A2) Upload Template DOCX (MAIN_DOCX)

- `POST /admin/layanan/{layanan}/dokumen/template`
  - Permission: `doc_templates.upload`
  - Body (multipart):
    - `file` (required) `.docx`
  - Success: `302` redirect + flash status
  - Errors:
    - `422` invalid file / mime / size

### A3) Extract Placeholder dari DOCX

- `POST /admin/layanan/{layanan}/dokumen/extract-placeholders`
  - Permission: `doc_placeholders.manage`
  - Success: `302` + flash “Placeholder diekstrak: N”
  - Output: upsert ke `service_placeholders`

### A4) Mapping Placeholder → Source

- `PUT /admin/layanan/{layanan}/dokumen/placeholders`
  - Permission: `doc_placeholders.manage`
  - Body (form):
    - `items[]`:
      - `placeholder_key` (required)
      - `source_type` (required) `FORM|PROFILE|INTERNAL|SYSTEM_AUTOFILL`
      - `source_ref` (optional)
      - `is_required` (optional boolean)
      - `notes` (optional)
  - Rules hard:
    - `NOMOR_SURAT` terkunci → `INTERNAL`
    - `TANGGAL_SURAT` terkunci → `SYSTEM_AUTOFILL`
  - Errors: `422` bila placeholder belum diekstrak / invalid source

### A5) Form Builder (service_fields)

- `POST /admin/layanan/{layanan}/dokumen/form-fields`
  - Permission: `doc_services.manage`
  - Body:
    - `key` (required) `a-z0-9_`
    - `label_id` (required)
    - `type` (required) `text|textarea|number|date|select|checkbox|json|file`
    - `required` (optional boolean)
    - `rules_json` (optional JSON array)
    - `options_json` (optional JSON array)
    - `maps_to_placeholder_key` (optional, untuk placeholder FORM)
    - `sort_order` (optional)

- `PATCH /admin/layanan/{layanan}/dokumen/form-fields/{field}`
  - Permission: `doc_services.manage`

### A6) Gate NOMOR_SURAT (wajib)

- `PUT /admin/layanan/{layanan}/dokumen/gate`
  - Permission: `doc_services.manage`
  - Body:
    - `gate_role` (required) `ADMIN_PRODI|ADMIN_JURUSAN`
    - `gate_steps_json` (required) JSON array, minimal memuat:
      - `VERIFY_INITIAL`
      - `INPUT_NOMOR_SURAT`

### A7) Signer Chain

- `PUT /admin/layanan/{layanan}/dokumen/signers`
  - Permission: `doc_signers.manage`
  - Body:
    - `signers_json` (required) JSON array:
      - `role` (required, string)
      - `order_index` (required, int, sequential mulai 1)
      - `is_required` (optional bool, default true)
      - `requires_signature_upload` (optional bool)
      - `signature_file_types` (required bila requires_signature_upload=true) `["image/png","image/jpeg","image/webp"]`
      - `signature_max_size_kb` (required bila requires_signature_upload=true)

### A8) Publish Gate

- `POST /admin/layanan/{layanan}/dokumen/publish`
  - Permission: `doc_services.publish`
  - Success:
    - `services.status = PUBLISHED`
    - `services.is_active = true`
  - Error: `422` bila readiness checker gagal

## B. FASE UTAMA — Operasional

### B1) Mahasiswa submit permohonan

Menggunakan endpoint existing:
- `POST /mahasiswa/permohonan` (route `student.requests.store`)
  - Permission: `requests.create_own`
  - Behavior tambahan:
    - Jika layanan memiliki `MAIN_DOCX`, sistem membuat `request_data` + `request_signoffs`.

### B2) Gate verify + input NOMOR_SURAT + start signing (Admin Prodi/Jurusan)

- `POST /admin/permohonan/{request}/gate/verify`
  - Permission: `doc_requests.gate` + policy `RequestPolicy@process`
  - Body: `{ decision: PASS|REVISION|REJECT, note? }`

- `POST /admin/permohonan/{request}/gate/nomor-surat`
  - Permission: `doc_requests.gate` + policy `RequestPolicy@process`
  - Body: `{ nomor_surat: string }`

- `POST /admin/permohonan/{request}/start-signing`
  - Permission: `doc_requests.gate` + policy `RequestPolicy@process`
  - Error `422` bila `nomor_surat` kosong atau status tidak valid.

### B3) Signer inbox + decide

- `GET /signer/permohonan/inbox`
  - Permission: `doc_signoffs.decide`
  - Hanya menampilkan request dimana signer adalah step aktif.

- `POST /signer/permohonan/{request}/decide`
  - Permission: `doc_signoffs.decide` + policy view (aktif signer)
  - Body:
    - `decision`: `APPROVE|REVISION|REJECT`
    - `note` (optional)
    - `signature_file` (optional; **wajib** jika signer step aktif `requires_signature_upload=true` dan decision=APPROVE)
  - Auto:
    - Jika required signer terakhir approve → set:
      - `requests.last_required_approved_at`
      - `requests.tanggal_surat` (YYYY-MM-DD)
      - `requests.current_status = READY_FOR_FINAL`

### B4) Assembly preview/finalize (Staff Final)

- `GET /staff/permohonan/{request}/assemble`
  - Permission: `doc_requests.assemble`

- `POST /staff/permohonan/{request}/assemble/preview`
  - Permission: `doc_requests.assemble`
  - Body: `placements_json` (JSON array)

- `POST /staff/permohonan/{request}/assemble/finalize`
  - Permission: `doc_requests.assemble`
  - Body: `placements_json`
  - Success:
    - Create `request_outputs` (PDF jika konversi tersedia; fallback DOCX)
    - Set `requests.current_status = COMPLETED`

### B5) Mahasiswa download output (private)

- `GET /mahasiswa/permohonan/{request}/output`
  - Permission: `attachments.download_private`
  - Policy: ownership check (anti-IDOR)
  - Rate limit: `throttle:downloads`

