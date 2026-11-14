<?php

use App\Http\Controllers\NewsCategoryController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\StaffNewsController;
use App\Http\Controllers\StaffSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [NewsController::class, 'home'])->name('home');
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{post}', [NewsController::class, 'show'])->name('news.show');
Route::get('/attachments/{attachment}', [NewsController::class, 'download'])->name('news.download');
Route::get('/staff/login', [StaffSessionController::class, 'create'])->name('login');
Route::post('/staff/login', [StaffSessionController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
Route::middleware(['auth', 'can:manage-news'])->prefix('staff')->name('staff.')->group(function (): void {
    Route::post('/logout', [StaffSessionController::class, 'destroy'])->name('logout');
    Route::resource('posts', StaffNewsController::class)->except('show');
    Route::delete('/posts/{post}/attachments/{attachment}', [StaffNewsController::class, 'destroyAttachment'])->name('attachments.destroy');
    Route::resource('categories', NewsCategoryController::class)->only(['store', 'update', 'destroy']);
});
