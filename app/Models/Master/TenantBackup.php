<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantBackup extends Model
{
    protected $connection = 'mysql_master';
    protected $table      = 'tenant_backups';

    protected $fillable = [
        'tenant_id',
        'sql_path',
        'storage_zip_path',
        'tgl_backup',
        'downloaded',
    ];

    protected function casts(): array
    {
        return [
            'tgl_backup' => 'date',
            'downloaded' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
