<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sensor extends Model {
    protected $table = 'sensors';
    protected $fillable = ['UserID', 'LockID', 'SensorType', 'Status', 'LastUpdated'];
    protected $primaryKey = 'SensorID'; 
    public $incrementing = true; 
    protected $keyType = 'int'; 
    public $timestamps = false;

    public function sensorData() {
        return $this->hasMany(SensorData::class, 'SensorID', 'SensorID');
    }

    public function lock() {
        return $this->belongsTo(Lock::class, 'LockID', 'LockID');
    }
}
?>
