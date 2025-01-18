<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lock extends Model {
    protected $table = 'locks';
    protected $fillable = ['LockName', 'Location', 'Status', 'CreatedAt', 'OwnerID', 'auto_lock_time'];
    protected $primaryKey = 'LockID'; 
    public $incrementing = true; 
    protected $keyType = 'int'; 
    public $timestamps = false;

    public function sensors() {
        return $this->hasMany(Sensor::class, 'LockID', 'LockID');
    }
}
?>
