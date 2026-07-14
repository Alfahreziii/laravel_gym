<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $connection = 'mysql_master';
    protected $table      = 'packages';

    protected $fillable = [
        'nama',
        'label',
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

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'package_id');
    }
}
