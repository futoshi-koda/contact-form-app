<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ContactController;
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
Route::get('/', [ContactController::class, 'index'])->name('contacts.index');
Route::get('/contact/index', [ContactController::class, 'index']);
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('contacts.confirm');
Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');

// 管理画面トップ（/admin も /admin/index も同じコントローラーへ）
Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
Route::get('/admin/index', [AdminController::class, 'index']);

// タグ管理
Route::resource('admin/tags', TagController::class)
    ->only(['store', 'edit', 'update', 'destroy']);

// お問い合わせ詳細・削除など
Route::resource('admin/contacts', AdminController::class)
    ->only(['show', 'destroy'])
    ->names('admin.contacts');