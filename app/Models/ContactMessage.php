<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'email',
    'phone',
    'subject',
    'message',
    'status',
    'admin_notes',
    'handled_at',
])]
class ContactMessage extends Model
{
    use BelongsToTenant;

    public const STATUS_NEW = 'new';

    public const STATUS_READ = 'read';

    public const STATUS_RESOLVED = 'resolved';

    protected function casts(): array
    {
        return [
            'handled_at' => 'datetime',
        ];
    }
}
