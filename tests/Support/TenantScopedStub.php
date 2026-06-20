<?php

namespace Tests\Support;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TenantScopedStub extends Model
{
    use BelongsToTenant;

    protected $table = 'tenant_scoped_stubs';

    protected $fillable = ['label', 'tenant_id'];
}
