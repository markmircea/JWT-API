
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuotationFormController;
use App\Http\Controllers\BrowserAuthController;
use App\Http\Controllers\QuotationController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::post('auth/browser/login', [BrowserAuthController::class, 'login']);
Route::post('auth/browser/logout', [BrowserAuthController::class, 'logout'])->middleware('jwt.auth');
Route::post('auth/browser/refresh', [BrowserAuthController::class, 'refresh']);

Route::get('/', [QuotationFormController::class, 'showForm'])->name('quotation.form');
Route::get('quotation', [QuotationFormController::class, 'showForm'])->name('quotation.form');
Route::post('quotation', [QuotationController::class, 'getQuotation'])->middleware('jwt.auth');


Route::get('login', function () { return view('login'); })->name('login');

