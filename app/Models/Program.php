<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;
    protected $table = 'programs';
    protected $fillable = ['name', 'logo','status'];
    
    public function allocations()
{
    return $this->hasMany(Allocation::class, 'program', 'id');

}

public function utilizations()
{
    return $this->hasMany(Utilization::class, 'program', 'id');
}

}

