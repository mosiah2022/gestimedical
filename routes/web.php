<?php

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PatientLmController;
use App\Http\Controllers\Api\FileUploadController;
use App\Http\Controllers\Api\PreInvoiceController;

use App\Http\Controllers\Api\PresentationController;
use App\Http\Controllers\Api\PatientAddressController;
use App\Http\Controllers\Api\PatientLmDetailController;
use App\Http\Controllers\Api\PatientDiagnosticController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return Inertia::render('Auth/Login', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'canForgot'   => Route::has('forgot_password'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

//Components Routes
Route::group(['auth', 'verified'], function () {
    Route::inertia('/dashboard', 'Dashboard')->name('dashboard');
    Route::inertia('/brandpage', 'BrandPage')->name('brandpage');
    Route::inertia('/patientpage', 'PatientPage')->name('patientpage');
    Route::inertia('/orderpage', 'Orders/OrderPage')->name('orderpage');
    Route::inertia('/reportspage', 'Reports/ReportPage')->name('reportspage');
    Route::inertia('/invoices', 'Invoices/ListInvoice')->name('invoices');
    Route::inertia('/values', 'ChargeValues/ListValues')->name('values');
    Route::inertia('/admin', 'Dashboard/Admin')->name('admin');
});

//Api Rest Routes
Route::group(['middleware' => 'auth', 'prefix' => 'api'], function () {

    //Only for allmethods with apiResource
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('patients', PatientController::class);
    Route::apiResource('patient_lms', PatientLmController::class);
    Route::apiResource('cities', CityController::class);
    Route::apiResource('doctors', DoctorController::class);
    Route::apiResource('address', PatientAddressController::class);
    Route::apiResource('patient_lm_details', PatientLmDetailController::class);
    Route::apiResource('patient_diagnostics', PatientDiagnosticController::class);
    Route::apiResource('pre_invoices', PreInvoiceController::class);
    Route::apiResource('presentations', PresentationController::class);
    Route::apiResource('invoices', InvoiceController::class);
    Route::apiResource('patient_addresses', PatientAddressController::class);

    //Only for method get
    Route::get('showlmdetail/{id}', [PatientLmDetailController::class, 'showlmdetail']);
    Route::get('findOrders/{id}', [PatientLmController::class, 'findOrders']);
    Route::get('address_patient/{id}/{category}', [PatientAddressController::class, 'getAddress']);
    Route::get('diagnostic_patient/{id}', [PatientDiagnosticController::class, 'getDiagnostics']);
    Route::get('getOrdersLm/{dateini}/{dateend}', [PatientLmController::class, 'getOrdersLm']);
    Route::get('getOrdersCheck', [PatientLmController::class, 'getOrdersCheck']);
    Route::get('patientByLm/{id}', [PatientLmController::class, 'patientByLm']);
    Route::get('getCountPreInvoices/{id}', [PreInvoiceController::class, 'getCountPreInvoices']);
    Route::get('getInvoiceActive', [PreInvoiceController::class,  'getInvoiceActive']);
    Route::get('getLmInfo/{id}', [PatientLmController::class, 'getLmInfo']);
    Route::get('getPatientsByDate/{dateini}/{dateend}',[PatientController::class, 'getPatientsByDate']);
    Route::get('getMedicines', [ProductController::class, 'getMedicines']);
    Route::get('list_files/{patient_id}', [FileUploadController::class, 'listFiles']);

    //Exports Excel
    Route::get('export_patients/{iniDate}/{endDate}', [PatientController::class, 'export']);
    Route::get('export_orders/{id}', [PatientLmController::class, 'export']);
    Route::get('export_values/{dateini}/{dateend}', [PatientLmDetailController::class, 'export']);

    //Only for method update&patch
    Route::patch('update_order/{id}', [PatientLmController::class, 'update_order']);
    Route::patch('update_diagnostic/{id}', [PatientDiagnosticController::class, 'update_diagnostic']);
    Route::patch('update_price/{id}', [ProductController::class, 'update_price']);
    Route::patch('update_lmcode/{id}', [PatientLmController::class, 'update_lmcode']);
    Route::patch('update_preinvoice', [PreInvoiceController::class, 'update_preinvoice']);

    //Uploads Methods
    Route::get('upload_file', function() { return view('upload'); });
    Route::post('store_file', [FileUploadController::class, 'fileStore']);

    //Delete post on cloud
    Route::post('delete_file', [FileUploadController::class, 'delete']);

    //Email Methods
    Route::post('send-files', [FileUploadController::class, 'sendFilesByEmail']);
});

require __DIR__.'/auth.php';
