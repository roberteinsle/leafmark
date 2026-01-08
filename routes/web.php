<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserSettingsController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('books.index');
    })->name('dashboard');

    // Books routes
    Route::resource('books', BookController::class);
    Route::post('/books/store-from-api', [BookController::class, 'storeFromApi'])->name('books.store-from-api');
    Route::post('/books/bulk-delete', [BookController::class, 'bulkDelete'])->name('books.bulk-delete');
    Route::patch('/books/{book}/progress', [BookController::class, 'updateProgress'])->name('books.progress');
    Route::patch('/books/{book}/status', [BookController::class, 'updateStatus'])->name('books.status');
    Route::delete('/books/{book}/cover', [BookController::class, 'deleteCover'])->name('books.delete-cover');

    // Tags routes
    Route::resource('tags', TagController::class);
    Route::post('/tags/{tag}/books/{book}', [TagController::class, 'addBook'])->name('tags.add-book');
    Route::delete('/tags/{tag}/books/{book}', [TagController::class, 'removeBook'])->name('tags.remove-book');

    // User settings routes
    Route::get('/settings', [UserSettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [UserSettingsController::class, 'update'])->name('settings.update');
});
