<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'action', 
        'type', 
        'record_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function allocation()
{
    return $this->hasOne(Allocation::class, 'id', 'record_id'); // Assuming record_id refers to Allocation ID
}

public function utilization()
{
    return $this->hasOne(Utilization::class, 'id', 'record_id'); // Assuming record_id refers to Utilization ID
}

}
