<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Utilization extends Model
{
    use HasFactory;
    protected $table = 'utilizations';
    protected $fillable = [
        'province',
        'city_municipality',
        'program',
        'physical',
        'fund_utilized',
    ];
    public function province()
    {
        return $this->belongsTo(Province::class, 'province', 'psgc');
    }

    public function citymuni()
    {
        return $this->belongsTo(CityMuni::class, 'city_municipality', 'psgc');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program', 'id');
    }

}


