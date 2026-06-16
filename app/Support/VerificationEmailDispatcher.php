<?php

namespace App\Support;

use App\Models\User;

class VerificationEmailDispatcher
{
    /**
     * @return array{sent:bool,error:?string,raw_error:?string}
     */
    public function send(User $user): array
    {
        try {
            $user->sendEmailVerificationNotification();

            return [
                'sent' => true,
                'error' => null,
                'raw_error' => null,
            ];
        } catch (\Throwable $e) {
            report($e);

            return [
                'sent' => false,
                'error' => $this->humanizeError($e),
                'raw_error' => $e->getMessage(),
            ];
        }
    }

    private function humanizeError(\Throwable $e): string
    {
        $msg = strtolower($e->getMessage());

        if (
            str_contains($msg, 'authentication required')
            || str_contains($msg, 'failed to authenticate')
            || str_contains($msg, 'invalid login')
        ) {
            return 'Email verifikasi gagal dikirim: SMTP membutuhkan autentikasi. Isi MAIL_USERNAME dan MAIL_PASSWORD, lalu klik "Kirim ulang".';
        }

        if (
            str_contains($msg, 'connection refused')
            || str_contains($msg, 'connection could not be established')
            || str_contains($msg, 'timed out')
        ) {
            return 'Email verifikasi gagal dikirim: koneksi ke server email bermasalah. Periksa MAIL_HOST, MAIL_PORT, dan MAIL_ENCRYPTION.';
        }

        return 'Email verifikasi gagal dikirim. Silakan klik "Kirim ulang" setelah konfigurasi email dipastikan benar.';
    }
}

