<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Province;
use App\Models\CityMuni;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AddressController extends Controller
{
    public function index()
    {
        // Fetch all provinces with their associated municipalities
        $province = Province::with('citymuni')->get();

        return Inertia::render('Admin/admin-reports', [
            'provinces' => $province,
        ]);
    }
    public function getProvinces()
    {
        return response()->json(Province::all());
    }

    // Get cities/municipalities based on selected province
    public function getCities($provincePsgc)
    {
        $cities = CityMuni::where('province_psgc', $provincePsgc)->get(['psgc', 'col_citymuni']);
        return response()->json($cities);
    }

    // Show create address form, now only dependent on provinces and cities
    public function create()
    {
        $provinces = Province::all();  // Get all provinces
        $cities = CityMuni::all();      // Get all cities/municipalities
        
        // Return the view with only provinces and cities
        return view('layouts.add', compact('provinces', 'cities'));
    }
    public function showAllocationForm()
    {
        $provinces = Province::all();  // Get all provinces
        $cities = CityMuni::all();     // Get all cities/municipalities
        
        return view('allocationform', compact('provinces', 'cities'));
    }

    // Get all provinces and cities and render them to the utilization form
    public function showUtilizationForm()
    {
        $provinces = Province::all();  // Get all provinces
        $cities = CityMuni::all();     // Get all cities/municipalities
        
        return view('utilizationform', compact('provinces', 'cities'));
    }
    public function showAdminReports()
    {
        $provinces = Province::all();  // Get all provinces
        $cities = CityMuni::all();     // Get all cities/municipalities

        // Return the view for the admin reports
        return view('admin-reports', compact('provinces', 'cities'));
    }
    
}
