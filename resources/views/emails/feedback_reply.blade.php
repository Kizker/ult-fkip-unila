<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tanggapan Kritik dan Saran</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fb;font-family:Arial,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f7fb;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;">
                    <tr>
                        <td style="padding:24px;">
                            <h1 style="margin:0 0 12px;font-size:20px;line-height:1.3;color:#111827;">Tanggapan Kritik dan Saran</h1>
                            <p style="margin:0 0 16px;font-size:14px;line-height:1.7;color:#374151;">
                                Halo {{ $feedback->name }}, terima kasih telah mengirimkan masukan ke ULT FKIP Unila.
                                Berikut tanggapan dari admin:
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e5e7eb;border-radius:8px;background:#ffffff;margin-bottom:12px;">
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:#6b7280;">Pesan Anda sebelumnya</p>
                                        <p style="margin:0;font-size:14px;line-height:1.8;color:#111827;">{!! nl2br(e((string) $feedback->message)) !!}</p>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e5e7eb;border-radius:8px;background:#f9fafb;">
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <p style="margin:0 0 8px;font-size:12px;color:#6b7280;">Kategori: {{ $feedback->category }}</p>
                                        <p style="margin:0 0 8px;font-size:12px;color:#6b7280;">Status: {{ $feedback->status }}</p>
                                        <p style="margin:0;font-size:14px;line-height:1.8;color:#111827;">{!! nl2br(e((string) $feedback->admin_reply)) !!}</p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:16px 0 0;font-size:13px;line-height:1.7;color:#4b5563;">
                                Salam,<br>
                                Admin ULT FKIP Unila
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
