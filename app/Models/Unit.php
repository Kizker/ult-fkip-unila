<?php

namespace App\Models;

use App\Enums\UnitType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'parent_id',
        'code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'type' => UnitType::class,
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Unit::class, 'parent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Resolve the first ancestor (including self) of a given type.
     *
     *  Unit hierarchy ULT FKIP is a tree:
     * Fakultas -> Jurusan -> Prodi.
     * Dampak: jika FKIP menambahkan level lain (mis. sub-prodi), method ini tetap aman (naik terus sampai null).
     */
    public function ancestorOfType(UnitType $type): ?Unit
    {
        $node = $this;
        $guard = 0;
        while ($node && $guard < 20) {
            if ($node->type === $type) return $node;
            $node = $node->parent;
            $guard++;
        }
        return null;
    }

    /**
     * Get all descendant unit IDs (recursive) with a small cache.
     *
     * Security note: this is used only for authorization scoping, not for UI.
     */
    public function descendantIdsCached(int $ttlSeconds = 60): array
    {
        return cache()->remember('unit_descendants_'.$this->id, $ttlSeconds, function () {
            $ids = [];
            $queue = [$this->id];
            while ($queue) {
                $current = array_shift($queue);
                $children = Unit::query()->where('parent_id', $current)->pluck('id')->all();
                foreach ($children as $cid) {
                    if (!in_array($cid, $ids, true)) {
                        $ids[] = $cid;
                        $queue[] = $cid;
                    }
                }
            }
            return $ids;
        });
    }
}
