<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    protected $connection = 'mysql_master';
    protected $table      = 'tenants';

    protected $fillable = [
        'nama_gym',
        'subdomain',
        'logo',
        'alamat',
        'email',
        'no_hp',
        'timezone',
        'database_pool_id',
        'package_id',
        'status',
        'tgl_mulai',
        'tgl_selesai',
    ];

    protected function casts(): array
    {
        return [
            'tgl_mulai'   => 'date',
            'tgl_selesai' => 'date',
        ];
    }

    public function databasePool(): BelongsTo
    {
        return $this->belongsTo(DatabasePool::class, 'database_pool_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function module(): HasOne
    {
        return $this->hasOne(TenantModule::class, 'tenant_id');
    }

    public function backups(): HasMany
    {
        return $this->hasMany(TenantBackup::class, 'tenant_id');
    }
}
