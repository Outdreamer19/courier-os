<?php

namespace App\Models;

use App\Enums\PackageStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageStatusHistory extends Model
{
    protected $fillable = [
        'package_id',
        'old_status',
        'new_status',
        'changed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'new_status' => PackageStatus::class,
        ];
    }

    public function oldStatusLabel(): ?string
    {
        return $this->old_status
            ? PackageStatus::from($this->old_status)->label()
            : null;
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
