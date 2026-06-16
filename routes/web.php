<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CmsController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DocumentNumberFormatController;
use App\Http\Controllers\Admin\DocumentPlaceholderGuideController;
use App\Http\Controllers\Admin\DocumentRequestGateController;
use App\Http\Controllers\Admin\DocumentServiceSetupController;
use App\Http\Controllers\Admin\FeedbackAdminController;
use App\Http\Controllers\Admin\LetterNumberFormatController;
use App\Http\Controllers\Admin\RoleAdminController;
use App\Http\Controllers\Admin\ServiceAdminController;
use App\Http\Controllers\Admin\StudyProgramController;
use App\Http\Controllers\Admin\TranslateController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\UserGuideController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DocumentPreviewController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PublicUserGuideController;
use App\Http\Controllers\ServiceDocumentPreviewController;
use App\Http\Controllers\Signer\DocumentSignerController;
use App\Http\Controllers\Staff\DocumentAssemblyController;
use App\Http\Controllers\Student\DocumentOutputController as StudentDocumentOutputController;
use App\Http\Controllers\Student\RequestController as StudentRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', [LocaleController::class, 'set'])->whereIn('locale', ['id', 'en'])->name('locale.set');

Route::get('/offline', fn () => view('offline'))->name('offline');

// Public Portal
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/layanan', [PublicController::class, 'services'])->name('services.index');
Route::get('/layanan/{service:slug}', [PublicController::class, 'serviceShow'])->name('services.show');
Route::get('/layanan/{service:slug}/preview-dokumen', [ServiceDocumentPreviewController::class, 'show'])
    ->middleware('throttle:downloads')
    ->name('services.document_preview');
Route::get('/tentang-ult', [PublicController::class, 'about'])->name('about');

Route::get('/blog', [PublicController::class, 'blog'])->name('blog.index');
Route::get('/blog/{blog:slug}', [PublicController::class, 'blogShow'])->name('blog.show');
Route::get('/pengumuman', [PublicController::class, 'announcements'])->name('announcements.index');
Route::get('/pengumuman/{announcement:slug}', [PublicController::class, 'announcementShow'])->name('announcements.show');
Route::get('/panduan-pengguna', [PublicUserGuideController::class, 'index'])->name('user_guides.index');
Route::get('/panduan-pengguna/{user_guide:slug}', [PublicUserGuideController::class, 'show'])->name('user_guides.show');
Route::get('/panduan-pengguna/{user_guide:slug}/file', [PublicUserGuideController::class, 'file'])
    ->middleware('throttle:downloads')
    ->name('user_guides.file');
Route::get('/panduan-pengguna/{user_guide:slug}/download', [PublicUserGuideController::class, 'download'])
    ->middleware('throttle:downloads')
    ->name('user_guides.download');

// Auth (Breeze-compatible minimal)
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:auth');

    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:auth');

    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->name('password.email')->middleware('throttle:auth');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update')->middleware('throttle:auth');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware(['signed', 'throttle:auth'])->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])->middleware('throttle:auth')->name('verification.send');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/kritik-saran', [FeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/kritik-saran', [FeedbackController::class, 'store'])
        ->middleware('throttle:feedback-submit')
        ->name('feedback.store');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Notification center
    Route::get('/notifikasi', [PublicController::class, 'notifications'])->name('notifications.index');
    Route::post('/notifikasi/{id}/read', [PublicController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/notifikasi/{id}/delete', [PublicController::class, 'deleteNotification'])->name('notifications.delete');
    Route::post('/notifikasi/read-all', [PublicController::class, 'markAllNotificationsRead'])->name('notifications.read_all');
    Route::post('/notifikasi/delete-all', [PublicController::class, 'deleteAllNotifications'])->name('notifications.delete_all');

    // Profile
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profil', [ProfileController::class, 'update'])->name('profile.update');

    // Student Portal
    Route::prefix('mahasiswa')->name('student.')
        ->middleware([\App\Http\Middleware\RedirectAdminsFromStudentPortal::class, 'permission:requests.view_own'])
        ->group(function () {
            Route::get('/dashboard', [StudentRequestController::class, 'dashboard'])->name('dashboard');
            Route::get('/permohonan', [StudentRequestController::class, 'index'])->name('requests.index');
            Route::get('/permohonan/buat/{service:slug}', [StudentRequestController::class, 'create'])->middleware('permission:requests.create_own')->name('requests.create');
            Route::post('/permohonan', [StudentRequestController::class, 'store'])->middleware(['permission:requests.create_own', 'throttle:uploads'])->name('requests.store');
            Route::post('/permohonan/certificate/source-preview', [StudentRequestController::class, 'previewCertificateSource'])
                ->middleware(['permission:requests.create_own', 'throttle:uploads'])
                ->name('requests.certificate.source_preview');
            Route::get('/permohonan/{request}', [StudentRequestController::class, 'show'])->name('requests.show');
            Route::get('/permohonan/{request}/signature/{signoff}/preview', [StudentRequestController::class, 'signaturePreview'])
                ->middleware(['permission:requests.view_own', 'throttle:downloads'])
                ->name('requests.signature.preview');
            Route::post('/permohonan/{request}/perbaikan', [StudentRequestController::class, 'submitRevision'])->middleware(['permission:requests.update_own', 'throttle:status-change'])->name('requests.revision');
            Route::post('/permohonan/{request}/data', [StudentRequestController::class, 'updateData'])
                ->middleware(['permission:requests.update_own', 'throttle:status-change'])
                ->name('requests.data.update');
            Route::post('/permohonan/{request}/catatan', [StudentRequestController::class, 'addNote'])->middleware('throttle:status-change')->name('requests.note');

            Route::post('/permohonan/{request}/lampiran', [AttachmentController::class, 'uploadInput'])
                ->middleware(['permission:attachments.upload_own', 'throttle:uploads'])
                ->name('attachments.upload');

            // Document module output download (policy-gated, anti-IDOR)
            Route::get('/permohonan/{request}/output', [StudentDocumentOutputController::class, 'download'])
                ->middleware(['permission:attachments.download_private', 'throttle:downloads'])
                ->name('requests.output');

        });

    // Signer Portal (document module)
    // Access is policy-gated per request; custom signers may not have doc_signoffs.decide permission.
    Route::prefix('signer')->name('signer.')->group(function () {
        Route::get('/permohonan/inbox', [DocumentSignerController::class, 'inbox'])->name('requests.inbox');
        Route::get('/permohonan/{request}', [DocumentSignerController::class, 'show'])->name('requests.show');
        Route::post('/permohonan/{request}/decide', [DocumentSignerController::class, 'decide'])
            ->middleware(['throttle:approvals', 'throttle:uploads'])
            ->name('requests.decide');
    });

    // Staff Final Portal (document module)
    Route::prefix('staff')->name('staff.')->middleware('permission:doc_requests.assemble')->group(function () {
        Route::get('/permohonan/{request}/assemble', [DocumentAssemblyController::class, 'show'])->name('assemble.show');
        Route::get('/permohonan/{request}/assemble/output/{output}/inline', [DocumentAssemblyController::class, 'inlineOutput'])
            ->middleware(['throttle:downloads'])
            ->name('assemble.output_inline');
        Route::post('/permohonan/{request}/assemble/preview', [DocumentAssemblyController::class, 'preview'])
            ->middleware(['throttle:uploads'])
            ->name('assemble.preview');
        Route::post('/permohonan/{request}/assemble/finalize', [DocumentAssemblyController::class, 'finalize'])
            ->middleware(['throttle:uploads'])
            ->name('assemble.finalize');
    });

    // Shared private attachment download (student/admin) - still protected by policy + rate limit
    Route::get('/lampiran/{attachment}/download', [AttachmentController::class, 'download'])
        ->middleware(['permission:attachments.download_private', 'throttle:downloads'])
        ->name('attachments.download');

    // Shared document preview for in-process requests (policy-gated by request view access)
    Route::get('/permohonan/{request}/preview', [DocumentPreviewController::class, 'show'])
        ->middleware(['throttle:downloads'])
        ->name('requests.preview');

    // Signed preview for unpublished CMS content (requires cms.manage)
    Route::get('/preview/blogs/{blog}', [PublicController::class, 'previewBlog'])
        ->middleware(['signed', 'permission:cms.manage'])
        ->name('preview.blogs.show');
    Route::get('/preview/announcements/{announcement}', [PublicController::class, 'previewAnnouncement'])
        ->middleware(['signed', 'permission:cms.manage'])
        ->name('preview.announcements.show');

    // Admin/Staf Portal
    Route::prefix('admin')->name('admin.')
        ->middleware('permission:requests.view_any|requests.view_unit|requests.review_ult|services.manage|cms.manage|site_settings.manage|document_numbers.manage_formats|users.manage|audit_logs.view|academics.manage|doc_services.manage|doc_services.publish|doc_templates.upload|doc_placeholders.manage|doc_signers.manage|doc_requests.gate|doc_requests.assemble|feedbacks.manage')
        ->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            Route::get('/permohonan', [\App\Http\Controllers\Admin\RequestAdminController::class, 'index'])->name('requests.index');
            Route::get('/permohonan/{request}', [\App\Http\Controllers\Admin\RequestAdminController::class, 'show'])->name('requests.show');
            Route::post('/permohonan/{request}/status', [\App\Http\Controllers\Admin\RequestAdminController::class, 'changeStatus'])
                ->middleware(['permission:requests.process_unit', 'throttle:status-change'])
                ->name('requests.status');

            // Workflow action endpoint (preferred): enforces steps_json (no arbitrary status jumps)
            Route::post('/permohonan/{request}/action', [\App\Http\Controllers\Admin\RequestAdminController::class, 'action'])
                ->middleware(['permission:requests.process_unit|requests.review_ult|approvals.unit.sign|approvals.faculty.sign|document_numbers.issue', 'throttle:status-change'])
                ->name('requests.action');
            Route::post('/permohonan/{request}/approval', [\App\Http\Controllers\Admin\RequestAdminController::class, 'approve'])
                ->middleware(['permission:requests.review_ult|approvals.unit.sign|approvals.faculty.sign', 'throttle:approvals'])
                ->name('requests.approval');
            Route::post('/permohonan/{request}/forward-faculty', [\App\Http\Controllers\Admin\RequestAdminController::class, 'forwardFaculty'])
                ->middleware(['permission:requests.review_ult', 'throttle:status-change'])
                ->name('requests.forward_faculty');
            Route::post('/permohonan/{request}/issue-number', [\App\Http\Controllers\Admin\RequestAdminController::class, 'issueNumber'])
                ->middleware(['permission:document_numbers.issue', 'throttle:status-change'])
                ->name('requests.issue_number');

            // Kritik & Saran
            Route::get('/kritik-saran', [FeedbackAdminController::class, 'index'])
                ->middleware('permission:feedbacks.manage')
                ->name('feedback.index');
            Route::get('/kritik-saran/{feedback}', [FeedbackAdminController::class, 'show'])
                ->middleware('permission:feedbacks.manage')
                ->name('feedback.show');
            Route::post('/kritik-saran/{feedback}', [FeedbackAdminController::class, 'update'])
                ->middleware(['permission:feedbacks.manage', 'throttle:status-change'])
                ->name('feedback.update');

            // Laporan
            Route::get('/pelaporan', [\App\Http\Controllers\Admin\ReportController::class, 'index'])
                ->middleware('permission:requests.view_any')
                ->name('reports.index');

            // Document module: gate actions (ADMIN_JURUSAN)
            Route::post('/permohonan/{request}/gate/verify', [DocumentRequestGateController::class, 'verify'])
                ->middleware(['permission:doc_requests.gate', 'throttle:status-change'])
                ->name('doc_requests.gate.verify');
            Route::post('/permohonan/{request}/start-signing', [DocumentRequestGateController::class, 'startSigning'])
                ->middleware(['permission:doc_requests.gate', 'throttle:status-change'])
                ->name('doc_requests.start_signing');

            // Master layanan
            Route::get('/layanan/pedoman-placeholder', [DocumentPlaceholderGuideController::class, 'index'])
                ->middleware('permission:doc_services.manage|doc_placeholders.manage|doc_templates.upload')
                ->name('layanan.placeholder_guide');
            Route::resource('/layanan', ServiceAdminController::class)->middleware('permission:services.manage');
            Route::post('/utils/translate', [TranslateController::class, 'translate'])
                ->middleware(['permission:services.manage|cms.manage|site_settings.manage', 'throttle:translate'])
                ->name('utils.translate');
            Route::post('/utils/upload-media', [\App\Http\Controllers\Admin\MediaUploadController::class, 'upload'])
                ->middleware(['permission:services.manage|cms.manage', 'throttle:uploads'])
                ->name('utils.upload_media');

            // Document module setup for service (SUPERADMIN only)
            Route::middleware('permission:doc_services.manage')->group(function () {
                Route::post('/layanan/{layanan}/dokumen/template', [DocumentServiceSetupController::class, 'uploadTemplate'])
                    ->middleware(['permission:doc_templates.upload', 'throttle:uploads'])
                    ->name('layanan.dokumen.template');
                Route::post('/layanan/{layanan}/dokumen/extract-placeholders', [DocumentServiceSetupController::class, 'extractPlaceholders'])
                    ->middleware(['permission:doc_placeholders.manage', 'throttle:status-change'])
                    ->name('layanan.dokumen.extract');
                Route::put('/layanan/{layanan}/dokumen/placeholders', [DocumentServiceSetupController::class, 'saveMappings'])
                    ->middleware(['permission:doc_placeholders.manage', 'throttle:status-change'])
                    ->name('layanan.dokumen.placeholders');
                Route::put('/layanan/{layanan}/dokumen/gate', [DocumentServiceSetupController::class, 'saveGate'])
                    ->middleware(['throttle:status-change'])
                    ->name('layanan.dokumen.gate');
                Route::put('/layanan/{layanan}/dokumen/signers', [DocumentServiceSetupController::class, 'saveSigners'])
                    ->middleware(['permission:doc_signers.manage', 'throttle:status-change'])
                    ->name('layanan.dokumen.signers');
                Route::post('/layanan/{layanan}/dokumen/publish', [DocumentServiceSetupController::class, 'publish'])
                    ->middleware(['permission:doc_services.publish', 'throttle:status-change'])
                    ->name('layanan.dokumen.publish');

                Route::post('/layanan/{layanan}/dokumen/form-fields', [DocumentServiceSetupController::class, 'createField'])
                    ->middleware(['throttle:status-change'])
                    ->name('layanan.dokumen.fields.create');
                Route::patch('/layanan/{layanan}/dokumen/form-fields/{field}', [DocumentServiceSetupController::class, 'updateField'])
                    ->middleware(['throttle:status-change'])
                    ->name('layanan.dokumen.fields.update');
            });

            // Master akademik (Jurusan & Prodi) - only superadmin
            Route::middleware('role:Superadmin')->group(function () {
                Route::resource('/jurusan', DepartmentController::class)->except(['show']);
                Route::resource('/prodi', StudyProgramController::class)->except(['show']);
            });

            // CMS publik (maximal CRUD)
            Route::prefix('cms')->name('cms.')->middleware('permission:cms.manage')->group(function () {
                Route::get('/', [CmsController::class, 'index'])->name('index');

                Route::get('/hero', [\App\Http\Controllers\Admin\CmsHeroController::class, 'edit'])->name('hero.edit');
                Route::post('/hero', [\App\Http\Controllers\Admin\CmsHeroController::class, 'update'])->name('hero.update');

                Route::resource('/categories', \App\Http\Controllers\Admin\CmsCategoryController::class)->except(['show']);
                Route::resource('/blogs', \App\Http\Controllers\Admin\CmsBlogController::class)->except(['show']);
                Route::resource('/announcements', \App\Http\Controllers\Admin\CmsAnnouncementController::class)->except(['show']);

                // Autosave draft + safe preview link (signed route)
                Route::post('/blogs/{blog}/autosave', [\App\Http\Controllers\Admin\CmsBlogController::class, 'autosave'])
                    ->middleware('throttle:cms-autosave')
                    ->name('blogs.autosave');
                Route::get('/blogs/{blog}/preview', [\App\Http\Controllers\Admin\CmsBlogController::class, 'previewRedirect'])
                    ->name('blogs.preview');
                Route::post('/announcements/{announcement}/autosave', [\App\Http\Controllers\Admin\CmsAnnouncementController::class, 'autosave'])
                    ->middleware('throttle:cms-autosave')
                    ->name('announcements.autosave');
                Route::get('/announcements/{announcement}/preview', [\App\Http\Controllers\Admin\CmsAnnouncementController::class, 'previewRedirect'])
                    ->name('announcements.preview');

                Route::get('/settings', [\App\Http\Controllers\Admin\SiteSettingsController::class, 'edit'])->name('settings.edit');
                Route::post('/settings', [\App\Http\Controllers\Admin\SiteSettingsController::class, 'update'])->name('settings.update');
            });

            // Panduan pengguna (superadmin only)
            Route::middleware('role:Superadmin')->group(function () {
                Route::resource('/panduan-pengguna', UserGuideController::class)
                    ->parameters(['panduan-pengguna' => 'user_guide'])
                    ->names('user_guides')
                    ->except(['show']);
            });

            // Legacy master format nomor dokumen (per unit)
            Route::resource('/format-nomor', DocumentNumberFormatController::class)
                ->parameters(['format-nomor' => 'doc_format'])
                ->names('doc_formats');

            // Master template nomor surat (per unit)
            Route::resource('/template-nomor-surat', LetterNumberFormatController::class)
                ->parameters(['template-nomor-surat' => 'letter_format'])
                ->names('letter_formats');
            Route::get('/template-nomor-surat/{letter_format}/export', [LetterNumberFormatController::class, 'export'])
                ->name('letter_formats.export');
            Route::post('/template-nomor-surat/{letter_format}/sequence', [LetterNumberFormatController::class, 'updateSequence'])
                ->name('letter_formats.sequence');

            // Users only superadmin
            Route::middleware('permission:users.manage')->group(function () {
                Route::resource('/users', UserAdminController::class)->except(['show']);
            });

            // Role management (prepare roles once, then assign on users)
            Route::middleware('permission:roles.manage')->group(function () {
                Route::resource('/roles', RoleAdminController::class)->except(['show']);
            });

            // Audit log viewer
            Route::get('/audit-logs', [AuditLogController::class, 'index'])
                ->middleware('permission:audit_logs.view')
                ->name('audit.index');
        });
});
