<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorData extends Model {
    protected $table = 'sensordata';
    protected $fillable = ['SensorID', 'DataType', 'DataValue', 'Timestamp'];
    protected $primaryKey = 'DataID'; 
    public $incrementing = true; 
    protected $keyType = 'int'; 
    public $timestamps = false;

    public function sensor() {
        return $this->belongsTo(Sensor::class, 'SensorID', 'SensorID');
    }
}
?>
