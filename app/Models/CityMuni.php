<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CityMuni extends Model
{
    use HasFactory;
    protected $table = 'tbl_citymuni';
    protected $primaryKey = 'psgc';
    public $timestamps = false;

    protected $fillable =['psgc','col_citymuni','province_psgc'];
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_psgc', 'psgc');
    }
    public function allocations()
    {
        return $this->hasMany(Allocation::class, 'city_municipality', 'psgc');
    }
    
    public function utilizations()
    {
        return $this->hasMany(Utilization::class, 'city_municipality', 'psgc'); // 'city_municipality' is the foreign key in utilizations
    }
    public function program()
    {
        return $this->belongsTo(Program::class, 'program', 'id');
    }
}
