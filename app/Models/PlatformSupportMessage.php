<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'thread_id',
    'user_id',
    'author_side',
    'body',
])]
class PlatformSupportMessage extends Model
{
    public const SIDE_TENANT = 'tenant';

    public const SIDE_PLATFORM = 'platform';

    public function thread(): BelongsTo
    {
        return $this->belongsTo(PlatformSupportThread::class, 'thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
