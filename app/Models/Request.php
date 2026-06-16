<?php

namespace App\Models;

use App\Enums\UnitType;
use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Unit;
use App\Models\User;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'activity_title',
        'student_id',
        'current_status',
        'current_step_key',
        'current_unit_id',
        'submitted_at',
        'nomor_surat',
        'tanggal_surat',
        'last_required_approved_at',
        'current_signer_order_index',
        'resume_signer_order_index',
        'completed_at',
        'rejected_at',
    ];

    protected $casts = [
        'current_status' => RequestStatus::class,
        'submitted_at' => 'datetime',
        'tanggal_surat' => 'date',
        'last_required_approved_at' => 'datetime',
        'current_signer_order_index' => 'integer',
        'resume_signer_order_index' => 'integer',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function currentUnit()
    {
        return $this->belongsTo(Unit::class, 'current_unit_id');
    }

    public function fieldValues(): HasMany
    {
        return $this->hasMany(RequestFieldValue::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class)
            ->orderByDesc('updated_at')
            ->orderByDesc('id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(RequestStatusHistory::class)
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(RequestNote::class)
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class)
            ->orderByDesc('updated_at')
            ->orderByDesc('id');
    }

    public function data()
    {
        return $this->hasOne(RequestData::class);
    }

    public function signoffs(): HasMany
    {
        return $this->hasMany(RequestSignoff::class)->orderBy('order_index');
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(RequestOutput::class)
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function signaturePlacements(): HasMany
    {
        return $this->hasMany(RequestSignaturePlacement::class)
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function documentNumber()
    {
        return $this->hasOne(DocumentNumber::class);
    }

    public function letterNumber()
    {
        return $this->hasOne(LetterNumber::class);
    }

    public function localizedServiceTitle(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $service = $this->relationLoaded('service') ? $this->getRelation('service') : $this->service;

        if (!$service) {
            return $locale === 'en' ? 'Service' : 'Layanan';
        }

        if ($locale === 'en') {
            return (string) ($service->title_en ?: $service->title_id ?: 'Service');
        }

        return (string) ($service->title_id ?: $service->title_en ?: 'Layanan');
    }

    public function getDisplayTitleAttribute(): string
    {
        $activityTitle = trim((string) ($this->activity_title ?? ''));
        $serviceTitle = $this->localizedServiceTitle();

        return $activityTitle !== ''
            ? $activityTitle.' - '.$serviceTitle
            : $serviceTitle;
    }

    public function getRequestCodeAttribute(): string
    {
        static $seqCache = [];

        $dt = $this->submitted_at ?? $this->created_at ?? now();
        $id = (int) ($this->id ?? 0);
        $useSubmittedDate = (bool) $this->submitted_at;
        $dateKey = ($useSubmittedDate ? 'submitted:' : 'created:').$dt->format('Y-m-d');

        if (isset($seqCache[$dateKey][$id])) {
            return sprintf('%s-%02d', $dt->format('dmy'), (int) $seqCache[$dateKey][$id]);
        }

        $q = self::query()->where('id', '<=', $id);
        if ($useSubmittedDate) {
            $q->whereDate('submitted_at', $dt->toDateString());
        } else {
            $q->whereDate('created_at', $dt->toDateString());
        }

        $seq = max(1, (int) $q->count());
        $seqCache[$dateKey][$id] = $seq;

        return sprintf('%s-%02d', $dt->format('dmy'), $seq);
    }

    /**
     * Scope listing requests according to authorization rules.
     * - requests.view_any : full access
     * - requests.view_unit: unit scope access (own unit + all descendants)
     * - requests.review_ult: ULT gatekeeper (fakultas scope)
     */
    public function scopeForUser($query, User $user)
    {
        if ($user->can('requests.view_any')) {
            return $query;
        }

        if ($user->can('requests.review_ult')) {
            $unit = $user->unit;
            if (!$unit) return $query->whereRaw('1=0');

            // ULT is faculty-level gatekeeper: allow seeing everything under the faculty.
            $fakultas = $unit->ancestorOfType(\App\Enums\UnitType::fakultas) ?? $unit;
            $allowed = array_values(array_unique(array_merge([$fakultas->id], $fakultas->descendantIdsCached())));
            return $query->whereIn('current_unit_id', $allowed);
        }

        if ($user->can('requests.view_unit')) {
            $scope = self::resolveUnitAccessScope($user);
            if (empty($scope['allowed_unit_ids'])) {
                return $query->whereRaw('1=0');
            }

            $query->whereIn('current_unit_id', $scope['allowed_unit_ids']);

            // Prodi-scoped unit admins are restricted to requests from scoped mahasiswa prodi.
            // This allows managing jurusan-stage requests while keeping tenant isolation per prodi.
            if (!empty($scope['scoped_prodi_ids'])) {
                $query->whereHas('student', fn ($s) => $s->whereIn('unit_id', $scope['scoped_prodi_ids']));
            }

            return $query;
        }

        // default: no access
        return $query->whereRaw('1=0');
    }

    /**
     * Resolve unit access scope for unit-level admin roles.
     *
     * @return array{allowed_unit_ids:array<int,int>,scoped_prodi_ids:array<int,int>}
     */
    public static function resolveUnitAccessScope(User $user): array
    {
        $allowedUnitIds = [];
        $scopedProdiIds = [];

        $includeProdiScope = function (Unit $prodi) use (&$allowedUnitIds, &$scopedProdiIds): void {
            $allowedUnitIds[] = (int) $prodi->id;
            $scopedProdiIds[] = (int) $prodi->id;

            $jurusan = $prodi->ancestorOfType(UnitType::jurusan) ?? $prodi->parent;
            if ($jurusan) {
                $allowedUnitIds[] = (int) $jurusan->id;
            }
        };

        $includeUnitTree = function (Unit $unit) use (&$allowedUnitIds): void {
            $allowedUnitIds = array_merge($allowedUnitIds, [(int) $unit->id], array_map('intval', $unit->descendantIdsCached()));
        };

        $primaryUnit = $user->unit;
        if ($primaryUnit) {
            if ($primaryUnit->type === UnitType::prodi) {
                $includeProdiScope($primaryUnit);
            } else {
                $includeUnitTree($primaryUnit);
            }
        }

        try {
            $extraUnits = $user->unitScopes()->get();
            foreach ($extraUnits as $unit) {
                if (!$unit instanceof Unit) {
                    continue;
                }

                if ($unit->type === UnitType::prodi) {
                    $includeProdiScope($unit);
                    continue;
                }

                $includeUnitTree($unit);
            }
        } catch (\Throwable $e) {
            // Ignore if scope table is unavailable during early migration stage.
        }

        $allowedUnitIds = array_values(array_unique(array_map('intval', $allowedUnitIds)));
        $scopedProdiIds = array_values(array_unique(array_map('intval', $scopedProdiIds)));

        return [
            'allowed_unit_ids' => $allowedUnitIds,
            'scoped_prodi_ids' => $scopedProdiIds,
        ];
    }
}
