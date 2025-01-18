<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model {
    protected $table = 'accesslogs';
    protected $fillable = ['LockID','UserID', 'Action'];
    protected $primaryKey = 'LogID'; 
    public $incrementing = true; 
    protected $keyType = 'int'; 
    public $timestamps = false;
}
