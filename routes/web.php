<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserSettingsController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

// Landing page
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('books.index');
    }
    return view('welcome');
})->name('welcome');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
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
    Route::post('/books/scrape-amazon', [BookController::class, 'scrapeAmazon'])->name('books.scrape-amazon');
    Route::post('/books/store-from-api', [BookController::class, 'storeFromApi'])->name('books.store-from-api');
    Route::post('/books/bulk-delete', [BookController::class, 'bulkDelete'])->name('books.bulk-delete');
    Route::post('/books/bulk-add-tags', [BookController::class, 'bulkAddTags'])->name('books.bulk-add-tags');
    Route::post('/books/bulk-remove-tag', [BookController::class, 'bulkRemoveTag'])->name('books.bulk-remove-tag');
    Route::get('/series/{series}', [BookController::class, 'showSeries'])->name('books.series');

    // Book view mode toggle
    Route::post('/books/toggle-view-mode', [BookController::class, 'toggleViewMode'])->name('books.toggle-view-mode');
    Route::post('/books/update-column-settings', [BookController::class, 'updateColumnSettings'])->name('books.update-column-settings');

    // Book-specific routes with numeric ID constraint
    Route::patch('/books/{book}/progress', [BookController::class, 'updateProgress'])->name('books.progress')->where('book', '[0-9]+');
    Route::patch('/books/{book}/status', [BookController::class, 'updateStatus'])->name('books.status')->where('book', '[0-9]+');
    Route::patch('/books/{book}/rating', [BookController::class, 'updateRating'])->name('books.update-rating')->where('book', '[0-9]+');
    Route::delete('/books/{book}/progress/{entry}', [BookController::class, 'deleteProgressEntry'])->name('books.progress.delete')->where(['book' => '[0-9]+', 'entry' => '[0-9]+']);
    Route::get('/books/{book}/fetch-api-data', [BookController::class, 'fetchApiData'])->name('books.fetch-api-data')->where('book', '[0-9]+');
    Route::post('/books/{book}/refresh-from-api', [BookController::class, 'refreshFromApi'])->name('books.refresh-from-api')->where('book', '[0-9]+');

    // Resource route MUST come AFTER all specific routes to avoid conflicts
    Route::resource('books', BookController::class)->where(['book' => '[0-9]+'])->except(['destroy']);

    // Define destroy route explicitly AFTER cover route to ensure proper ordering
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy')->where('book', '[0-9]+');

    // Tags routes
    Route::resource('tags', TagController::class);
    Route::post('/tags/{tag}/books/{book}', [TagController::class, 'addBook'])->name('tags.add-book');
    Route::delete('/tags/{tag}/books/{book}', [TagController::class, 'removeBook'])->name('tags.remove-book');

    // User settings routes
    Route::get('/settings', [UserSettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [UserSettingsController::class, 'update'])->name('settings.update');

    // Import routes
    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import/upload', [ImportController::class, 'upload'])->name('import.upload');
    Route::post('/import/execute', [ImportController::class, 'execute'])->name('import.execute');
    Route::post('/import/cancel', [ImportController::class, 'cancel'])->name('import.cancel');
    Route::get('/import/history', [ImportController::class, 'history'])->name('import.history');
    Route::get('/import/result/{importHistory}', [ImportController::class, 'result'])->name('import.result');
    Route::delete('/import/{importHistory}', [ImportController::class, 'destroy'])->name('import.destroy');

    // Reading Challenge routes
    Route::get('/challenge', [App\Http\Controllers\ReadingChallengeController::class, 'index'])->name('challenge.index');
    Route::post('/challenge', [App\Http\Controllers\ReadingChallengeController::class, 'store'])->name('challenge.store');
    Route::patch('/challenge/{challenge}', [App\Http\Controllers\ReadingChallengeController::class, 'update'])->name('challenge.update');
    Route::delete('/challenge/{challenge}', [App\Http\Controllers\ReadingChallengeController::class, 'destroy'])->name('challenge.destroy');

    // Statistics route
    Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');

    // Family routes
    Route::get('/family', [FamilyController::class, 'index'])->name('family.index');
    Route::get('/family/create', [FamilyController::class, 'create'])->name('family.create');
    Route::post('/family', [FamilyController::class, 'store'])->name('family.store');
    Route::get('/family/join', [FamilyController::class, 'showJoinForm'])->name('family.join');
    Route::post('/family/join', [FamilyController::class, 'join'])->name('family.join.submit');
    Route::post('/family/leave', [FamilyController::class, 'leave'])->name('family.leave');
    Route::delete('/family', [FamilyController::class, 'destroy'])->name('family.destroy');
    Route::post('/family/regenerate-code', [FamilyController::class, 'regenerateCode'])->name('family.regenerate-code');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::patch('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::patch('/users/{user}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('users.toggle-admin');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');

    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::patch('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
});

// Backward compatibility: Redirect old locale-prefixed URLs to non-prefixed versions
// This handles browser cache issues where old URLs like /de/login are still cached
Route::redirect('/{locale}', '/', 301)->where('locale', 'de|en|fr|it|es|pl');
Route::redirect('/{locale}/{path}', '/{path}', 301)
    ->where('locale', 'de|en|fr|it|es|pl')
    ->where('path', '.*');
