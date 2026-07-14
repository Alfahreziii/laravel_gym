<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DatabasePool extends Model
{
    protected $connection = 'mysql_master';
    protected $table      = 'database_pool';

    protected $fillable = [
        'db_name',
        'db_host',
        'db_username',
        'db_password',
        'status',
    ];

    protected $hidden = ['db_password'];

    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class, 'database_pool_id');
    }
}
