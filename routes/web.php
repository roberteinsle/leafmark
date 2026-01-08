<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ShelfController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
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
        return view('dashboard');
    })->name('dashboard');

    // Books routes
    Route::resource('books', BookController::class);
    Route::patch('/books/{book}/progress', [BookController::class, 'updateProgress'])->name('books.progress');
    Route::patch('/books/{book}/status', [BookController::class, 'updateStatus'])->name('books.status');

    // Shelves routes
    Route::resource('shelves', ShelfController::class);
    Route::post('/shelves/{shelf}/books/{book}', [ShelfController::class, 'addBook'])->name('shelves.add-book');
    Route::delete('/shelves/{shelf}/books/{book}', [ShelfController::class, 'removeBook'])->name('shelves.remove-book');
});
