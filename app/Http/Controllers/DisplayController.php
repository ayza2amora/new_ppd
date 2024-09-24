<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;
class DisplayController extends Controller
{
    public function fetchPrograms()
    {
        $programs = Program::all();
        return response()->json($programs);
    }
}
