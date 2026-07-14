<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantModule extends Model
{
    protected $connection = 'mysql_master';
    protected $table      = 'tenant_modules';

    protected $fillable = [
        'tenant_id',
        'trainer',
        'pos',
        'keuangan',
    ];

    protected function casts(): array
    {
        return [
            'trainer'  => 'boolean',
            'pos'      => 'boolean',
            'keuangan' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
