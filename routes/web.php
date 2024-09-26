<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\AllocationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\DisplayController;
use Inertia\Inertia;
use App\Http\Controllers\ApproveUserController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CLogController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\AdminReportController;

Route::get('/', function () {
  return redirect()->route('login');
});

//Staff/User Controllers
Route::get('/users', [AuthController::class, 'index']);

Route::inertia('/register', 'Auth/Register')->name('register');
//Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::inertia('/login', 'Auth/Login')->name('login');
//Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class,'logout'])->name('logout');

//Routes for OTP
Route::middleware(['auth'])->group(function () {
  Route::get('/otp', [OtpController::class, 'show'])->name('otp.show');
  Route::post('/send-otp', [OtpController::class, 'send'])->name('otp.send');
  Route::post('/verify-otp', [OtpController::class, 'verify'])->name('otp.verify');
  Route::post('/resend-otp', [OtpController::class, 'resend'])->name('otp.resend');
});

//Route for Reports fetching and update
Route::get('/utilizations', [ReportsController::class, 'index'])->name('utilization.index');
Route::post('/utilizations', [ReportsController::class, 'store'])->name('utilization.store');
Route::post('/utilizations/{utilization}', [ReportsController::class, 'update'])->name('utilizations.update');

Route::get('/allocations', [AllocationController::class, 'index'])->name('allocation.index');
Route::post('/allocations', [AllocationController::class, 'store'])->name('allocation.store');
Route::post('/allocations/{allocation}', [AllocationController::class, 'update'])->name('allocation.update');
//Route::get('/allocations', [AllocationController::class, 'userProgramAllocations'])->name('allocations');



//Route for programs fetching and update 
Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
Route::post('/programs', [ProgramController::class, 'store'])->name('programs.store');
Route::post('/programs/{program}', [ProgramController::class, 'update'])->name('programs.update');

Route::get('/register', [ProgramController::class, 'getPrograms'])->name('register');
Route::post('/programs/update-status/{program}', [ProgramController::class, 'updateStatus'])->name('programs.updateStatus');

//Dropdown for provinces and city/municipality
Route::get('/allocations/create', [AllocationController::class, 'create'])->name('allocations.create');
Route::post('/allocations', [AllocationController::class, 'store'])->name('allocations.store');
Route::post('/Client/AllocationForm', [AllocationController::class, 'store'])->name('allocation.store');

Route::post('/utilizations/create', [ReportsController::class, 'create'])->name('utilizations.create');

Route::get('/profile', [AuthController::class, 'getUserInfo'])->middleware(['auth'])->name('profile');
Route::post('/update-password', [AuthController::class, 'updatePassword'])->middleware(['auth'])->name('password.update');

Route::middleware(['auth:web'])->group(function () {
  Route::inertia('/client-reports', 'Client/Client-reports')->name('client-reports');
  Route::inertia('/client-logs', 'Client/Client-logs')->name('client-logs');
  Route::get('/client-logs', [CLogController::class, 'index'])->name('client-logs');
});

Route::middleware(['auth:web'])->group(function () {
  Route::inertia('/admin-dashboard', 'Admin/Admin-dashboard')->name('admin-dashboard');
  Route::inertia('/admin-reports', 'Admin/Admin-reports')->name('admin-reports');
  Route::inertia('/admin-accounts', 'Admin/Admin-accounts')->name('admin-accounts');
  Route::inertia('/admin-programs', 'Admin/Admin-programs')->name('admin-programs');
  Route::inertia('/admin-logs', 'Admin/Admin-logs')->name('admin-logs');

  Route::get('/admin-dashboard', [DashboardController::class, 'index'])->name('admin-dashboard');
  Route::get('/programs', [DisplayController::class, 'fetchPrograms']);

  Route::get('/admin-reports', [ReportController::class, 'index'])->name('admin-reports');
 
  //Admin Logs
Route::get('/admin-logs', [LogController::class, 'index'])->name('admin-logs');
//Route::get('/admin-logs', [LogController::class, 'logs'])->name('admin-logs');
});


//Routes for OTP
Route::middleware(['auth'])->group(function () {
  Route::get('/otp', [OtpController::class, 'show'])->name('otp.show');
  Route::post('/send-otp', [OtpController::class, 'send'])->name('otp.send');
  Route::post('/verify-otp', [OtpController::class, 'verify'])->name('otp.verify');
  Route::post('/resend-otp', [OtpController::class, 'resend'])->name('otp.resend');
});

//Route for Approve and Decline Registered Accounts
Route::post('/users/{user}/approve', [ApproveUserController::class, 'approve'])
    ->name('users.approve')
    ->middleware(['auth']);
Route::post('/users/{user}/pending', [ApproveUserController::class, 'pending'])->name('users.pending');


//Route for Active and deactive accounts
Route::post('/users/{id}/activate', [AuthController::class, 'activate']);
Route::post('/users/{id}/deactivate', [AuthController::class, 'deactivate']);
//Route for changing of roles
Route::post('/users/{id}/role', [AuthController::class, 'updateRole']);

//Route for changing of designated program
//Route::post('/users/{user}/program', [AuthController::class, 'updateProgram']);
//Route for changing of designated program
Route::post('/users/{id}/program', [AuthController::class, 'updateProgram']);

Route::get('/admin-accounts', [ProgramController::class, 'userPrograms'])->name('admin-accounts');

//route for programs dropdown on forms
Route::get('/allocationForm', [ProgramController::class, 'allocationPrograms'])->name('allocationForm');
Route::get('/utilizationForm', [ProgramController::class, 'utilizationPrograms'])->name('utilizationForm');


//Route for Address
Route::get('/address/provinces', [AddressController::class, 'getProvinces']);
Route::get('/address/cities/{provincePsgc}', [AddressController::class, 'getCities']);
Route::get('/address/create', [AddressController::class, 'create']);

Route::get('/allocationform', [AddressController::class, 'showAllocationForm'])->name('allocationform');
Route::get('/utilizationform', [AddressController::class, 'showUtilizationForm'])->name('utilizationform');

Route::get('Admin/admin-reports', [AddressController::class, 'showAdminReports'])->name('admin.reports');
Route::get('Admin/admin-reports', [AddressController::class, 'index'])->name('admin.reports');

//Route for Autneticated Users
//Route::middleware('auth')->get('/user', [AuthController::class, 'getAuthenticatedUser']);
//Route for Autneticated Users
Route::middleware(['auth'])->group(function () {
  Route::get('/user', [AuthController::class, 'getAuthenticatedUser']);
});

Route::get('/allocations', [AllocationController::class, 'getAllocations']);
Route::get('/utilizations', [ReportsController::class, 'getUtilizations']);

Route::get('/programs/{id}', [ProgramController::class, 'show']);

use App\Http\Controllers\ProgramReportController;

Route::get('/admin/program-reports', [ProgramReportController::class, 'index'])->name('program.reports');


Route::get('/utilization-summary/{programId}/{provinceCode}', [ReportsController::class, 'getUtilizationSummary']);
