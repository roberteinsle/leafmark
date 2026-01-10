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
        return redirect()->route('books.index');
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
    // CRITICAL: Cover routes MUST be first to avoid being caught by destroy route
    Route::delete('/books/{book}/delete-cover', [BookController::class, 'deleteCover'])->name('books.delete-cover')->where('book', '[0-9]+');
    Route::post('/books/{book}/covers', [BookController::class, 'uploadCover'])->name('books.covers.upload')->where('book', '[0-9]+');
    Route::delete('/books/{book}/covers/{cover}', [BookController::class, 'deleteSingleCover'])->name('books.covers.delete')->where(['book' => '[0-9]+', 'cover' => '[0-9]+']);
    Route::patch('/books/{book}/covers/{cover}/primary', [BookController::class, 'setPrimaryCover'])->name('books.covers.primary')->where(['book' => '[0-9]+', 'cover' => '[0-9]+']);

    // Books routes - specific routes MUST come before resource routes
    Route::post('/books/store-from-api', [BookController::class, 'storeFromApi'])->name('books.store-from-api');
    Route::post('/books/bulk-delete', [BookController::class, 'bulkDelete'])->name('books.bulk-delete');
    Route::get('/series/{series}', [BookController::class, 'showSeries'])->name('books.series');

    // Book-specific routes with numeric ID constraint
    Route::patch('/books/{book}/progress', [BookController::class, 'updateProgress'])->name('books.progress')->where('book', '[0-9]+');
    Route::patch('/books/{book}/status', [BookController::class, 'updateStatus'])->name('books.status')->where('book', '[0-9]+');
    Route::patch('/books/{book}/rating', [BookController::class, 'updateRating'])->name('books.update-rating')->where('book', '[0-9]+');
    Route::delete('/books/{book}/progress/{entry}', [BookController::class, 'deleteProgressEntry'])->name('books.progress.delete')->where(['book' => '[0-9]+', 'entry' => '[0-9]+']);
    Route::patch('/books/{book}/update-from-url', [BookController::class, 'updateFromUrl'])->name('books.update-from-url')->where('book', '[0-9]+');

    // Resource route MUST come AFTER all specific routes to avoid conflicts
    // The 'only' parameter ensures we don't generate conflicting routes
    Route::resource('books', BookController::class)->where(['book' => '[0-9]+'])->except(['destroy'])->names([
        'index' => 'books.index',
        'create' => 'books.create',
        'store' => 'books.store',
        'show' => 'books.show',
        'edit' => 'books.edit',
        'update' => 'books.update',
    ]);

    // Define destroy route explicitly AFTER cover route to ensure proper ordering
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy')->where('book', '[0-9]+');

    // Tags routes
    Route::resource('tags', TagController::class);
    Route::post('/tags/{tag}/books/{book}', [TagController::class, 'addBook'])->name('tags.add-book');
    Route::delete('/tags/{tag}/books/{book}', [TagController::class, 'removeBook'])->name('tags.remove-book');

    // User settings routes
    Route::get('/settings', [UserSettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [UserSettingsController::class, 'update'])->name('settings.update');

    // Reading Challenge routes
    Route::get('/challenge', [App\Http\Controllers\ReadingChallengeController::class, 'index'])->name('challenge.index');
    Route::post('/challenge', [App\Http\Controllers\ReadingChallengeController::class, 'store'])->name('challenge.store');
    Route::patch('/challenge/{challenge}', [App\Http\Controllers\ReadingChallengeController::class, 'update'])->name('challenge.update');
    Route::delete('/challenge/{challenge}', [App\Http\Controllers\ReadingChallengeController::class, 'destroy'])->name('challenge.destroy');
});
