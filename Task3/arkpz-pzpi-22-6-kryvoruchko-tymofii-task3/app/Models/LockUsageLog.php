<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LockUsageLog extends Model {
    protected $table = 'LockUsageLogs';
    protected $fillable = ['LockID', 'Action', 'Timestamp'];
    public $timestamps = false;
    public $incrementing = true; 
    protected $keyType = 'int'; 
}
