<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/admin/index', [AdminController::class, 'index'])->name('admin.index');
Route::resource('admin/tags', TagController::class)->only(['store', 'edit', 'update', 'destroy']);
Route::redirect('/admin', '/admin/index');
Route::resource('admin/contacts', AdminController::class)
    ->only(['index', 'store', 'show', 'destroy'])
    ->names('admin.contacts');