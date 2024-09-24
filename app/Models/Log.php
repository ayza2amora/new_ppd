<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $table = 'admin_logs'; // Custom table name

    protected $fillable = [
        'user_id', 'action', 'previous_value', 'new_value',
    ];

    // Relationship to the User model
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
