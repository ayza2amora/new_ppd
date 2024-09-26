<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'tbl_province';
    protected $primaryKey = 'psgc';
    public $timestamps = false;

    protected $fillable = [
        'psgc', // This should be your primary key or unique identifier
        'col_province', // The name of the province
      
    ];

  
    public function cityMuni()
    {
        return $this->hasMany(CityMuni::class, 'province_psgc', 'psgc');
    }
    public function allocations()
    {
        return $this->hasMany(Allocation::class, 'province', 'psgc');
    }
    public function utilizations()
    {
        return $this->hasMany(Utilization::class, 'province', 'psgc'); // 'city_municipality' is the foreign key in utilizations
    }
}
