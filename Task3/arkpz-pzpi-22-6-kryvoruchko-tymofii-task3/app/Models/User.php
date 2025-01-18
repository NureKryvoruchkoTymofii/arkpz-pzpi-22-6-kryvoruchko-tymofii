<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model {
    protected $table = 'users';
    protected $fillable = ['Username', 'Email', 'PasswordHash', 'Role', 'CreatedAt'];
    protected $primaryKey = 'UserID'; 
    public $incrementing = true; 
    protected $keyType = 'int'; 
    public $timestamps = false;

    
}
?>
