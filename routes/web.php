<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserSettingsController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Middleware\SetLocaleFromUrl;
use Illuminate\Support\Facades\Route;

// Root redirect - detect language and redirect to appropriate locale
Route::get('/', function () {
    $locale = SetLocaleFromUrl::detectPreferredLocale(request());
    return redirect("/{$locale}");
})->name('root');

// Define supported locales
$supportedLocales = ['en', 'de', 'fr', 'it', 'es', 'pl'];

// Wrap all routes in language prefix
foreach ($supportedLocales as $locale) {
    Route::prefix($locale)
        ->middleware(['web', SetLocaleFromUrl::class . ':' . $locale])
        ->group(function () use ($locale) {

            // Landing page
            Route::get('/', function () use ($locale) {
                if (auth()->check()) {
                    return redirect()->route('books.index', ['locale' => app()->getLocale()]);
                }
                return view('welcome', ['locale' => $locale]);
            })->name('welcome.' . $locale);

            // Service pages with localized URLs
            if ($locale === 'en') {
                Route::get('/imprint', function () {
                    return view('impressum');
                })->name('impressum.' . $locale);
                Route::get('/privacy', function () {
                    return view('datenschutz');
                })->name('datenschutz.' . $locale);
                Route::get('/contact', [ContactController::class, 'show'])->name('kontakt.' . $locale);
                Route::post('/contact', [ContactController::class, 'submit'])->name('kontakt.submit.' . $locale);
            } elseif ($locale === 'de') {
                Route::get('/impressum', function () {
                    return view('impressum');
                })->name('impressum.' . $locale);
                Route::get('/datenschutz', function () {
                    return view('datenschutz');
                })->name('datenschutz.' . $locale);
                Route::get('/kontakt', [ContactController::class, 'show'])->name('kontakt.' . $locale);
                Route::post('/kontakt', [ContactController::class, 'submit'])->name('kontakt.submit.' . $locale);
            } elseif ($locale === 'fr') {
                Route::get('/mentions-legales', function () {
                    return view('impressum');
                })->name('impressum.' . $locale);
                Route::get('/confidentialite', function () {
                    return view('datenschutz');
                })->name('datenschutz.' . $locale);
                Route::get('/contact', [ContactController::class, 'show'])->name('kontakt.' . $locale);
                Route::post('/contact', [ContactController::class, 'submit'])->name('kontakt.submit.' . $locale);
            } elseif ($locale === 'es') {
                Route::get('/aviso-legal', function () {
                    return view('impressum');
                })->name('impressum.' . $locale);
                Route::get('/privacidad', function () {
                    return view('datenschutz');
                })->name('datenschutz.' . $locale);
                Route::get('/contacto', [ContactController::class, 'show'])->name('kontakt.' . $locale);
                Route::post('/contacto', [ContactController::class, 'submit'])->name('kontakt.submit.' . $locale);
            } elseif ($locale === 'it') {
                Route::get('/note-legali', function () {
                    return view('impressum');
                })->name('impressum.' . $locale);
                Route::get('/privacy', function () {
                    return view('datenschutz');
                })->name('datenschutz.' . $locale);
                Route::get('/contatto', [ContactController::class, 'show'])->name('kontakt.' . $locale);
                Route::post('/contatto', [ContactController::class, 'submit'])->name('kontakt.submit.' . $locale);
            } elseif ($locale === 'pl') {
                Route::get('/nota-prawna', function () {
                    return view('impressum');
                })->name('impressum.' . $locale);
                Route::get('/prywatnosc', function () {
                    return view('datenschutz');
                })->name('datenschutz.' . $locale);
                Route::get('/kontakt', [ContactController::class, 'show'])->name('kontakt.' . $locale);
                Route::post('/kontakt', [ContactController::class, 'submit'])->name('kontakt.submit.' . $locale);
            }

            Route::get('/changelog', function () {
                return view('changelog');
            })->name('changelog.' . $locale);

            // Authentication routes
            Route::middleware('guest')->group(function () use ($locale) {
                Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register.' . $locale);
                Route::post('/register', [RegisterController::class, 'register'])->name('register.submit.' . $locale);

                Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.' . $locale);
                Route::post('/login', [LoginController::class, 'login'])->name('login.submit.' . $locale);

                // Password reset routes
                Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request.' . $locale);
                Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email.' . $locale);
                Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset.' . $locale);
                Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update.' . $locale);

                // Email verification notice page
                Route::get('/verify-email', function () {
                    return view('auth.verify-email');
                })->name('verify.notice.' . $locale);
            });

            Route::post('/logout', [LoginController::class, 'logout'])
                ->middleware('auth')
                ->name('logout.' . $locale);

            // Email verification routes
            Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
                ->middleware(['signed'])
                ->name('verification.verify.' . $locale);

            Route::post('/email/resend', [VerificationController::class, 'resend'])
                ->middleware(['guest'])
                ->name('verification.resend.' . $locale);

            // Protected routes
            Route::middleware('auth')->group(function () use ($locale) {
                // CRITICAL: Cover routes MUST be first to avoid being caught by destroy route
                Route::delete('/books/{book}/delete-cover', [BookController::class, 'deleteCover'])->name('books.delete-cover.' . $locale)->where('book', '[0-9]+');
                Route::post('/books/{book}/covers', [BookController::class, 'uploadCover'])->name('books.covers.upload.' . $locale)->where('book', '[0-9]+');
                Route::delete('/books/{book}/covers/{cover}', [BookController::class, 'deleteSingleCover'])->name('books.covers.delete.' . $locale)->where(['book' => '[0-9]+', 'cover' => '[0-9]+']);
                Route::patch('/books/{book}/covers/{cover}/primary', [BookController::class, 'setPrimaryCover'])->name('books.covers.primary.' . $locale)->where(['book' => '[0-9]+', 'cover' => '[0-9]+']);

                // Books routes - specific routes MUST come before resource routes
                Route::post('/books/store-from-api', [BookController::class, 'storeFromApi'])->name('books.store-from-api.' . $locale);
                Route::post('/books/bulk-delete', [BookController::class, 'bulkDelete'])->name('books.bulk-delete.' . $locale);
                Route::post('/books/bulk-add-tags', [BookController::class, 'bulkAddTags'])->name('books.bulk-add-tags.' . $locale);
                Route::post('/books/bulk-remove-tag', [BookController::class, 'bulkRemoveTag'])->name('books.bulk-remove-tag.' . $locale);
                Route::get('/series/{series}', [BookController::class, 'showSeries'])->name('books.series.' . $locale);

                // Book view mode toggle
                Route::post('/books/toggle-view-mode', [BookController::class, 'toggleViewMode'])->name('books.toggle-view-mode.' . $locale);
                Route::post('/books/update-column-settings', [BookController::class, 'updateColumnSettings'])->name('books.update-column-settings.' . $locale);

                // Book-specific routes with numeric ID constraint
                Route::patch('/books/{book}/progress', [BookController::class, 'updateProgress'])->name('books.progress.' . $locale)->where('book', '[0-9]+');
                Route::patch('/books/{book}/status', [BookController::class, 'updateStatus'])->name('books.status.' . $locale)->where('book', '[0-9]+');
                Route::patch('/books/{book}/rating', [BookController::class, 'updateRating'])->name('books.update-rating.' . $locale)->where('book', '[0-9]+');
                Route::delete('/books/{book}/progress/{entry}', [BookController::class, 'deleteProgressEntry'])->name('books.progress.delete.' . $locale)->where(['book' => '[0-9]+', 'entry' => '[0-9]+']);
                Route::get('/books/{book}/fetch-api-data', [BookController::class, 'fetchApiData'])->name('books.fetch-api-data.' . $locale)->where('book', '[0-9]+');
                Route::post('/books/{book}/refresh-from-api', [BookController::class, 'refreshFromApi'])->name('books.refresh-from-api.' . $locale)->where('book', '[0-9]+');

                // Resource route MUST come AFTER all specific routes to avoid conflicts
                Route::resource('books', BookController::class)->where(['book' => '[0-9]+'])->except(['destroy'])->names([
                    'index' => 'books.index.' . $locale,
                    'create' => 'books.create.' . $locale,
                    'store' => 'books.store.' . $locale,
                    'show' => 'books.show.' . $locale,
                    'edit' => 'books.edit.' . $locale,
                    'update' => 'books.update.' . $locale,
                ]);

                // Define destroy route explicitly AFTER cover route to ensure proper ordering
                Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy.' . $locale)->where('book', '[0-9]+');

                // Tags routes
                Route::resource('tags', TagController::class)->names([
                    'index' => 'tags.index.' . $locale,
                    'create' => 'tags.create.' . $locale,
                    'store' => 'tags.store.' . $locale,
                    'show' => 'tags.show.' . $locale,
                    'edit' => 'tags.edit.' . $locale,
                    'update' => 'tags.update.' . $locale,
                    'destroy' => 'tags.destroy.' . $locale,
                ]);
                Route::post('/tags/{tag}/books/{book}', [TagController::class, 'addBook'])->name('tags.add-book.' . $locale);
                Route::delete('/tags/{tag}/books/{book}', [TagController::class, 'removeBook'])->name('tags.remove-book.' . $locale);

                // User settings routes
                Route::get('/settings', [UserSettingsController::class, 'edit'])->name('settings.edit.' . $locale);
                Route::patch('/settings', [UserSettingsController::class, 'update'])->name('settings.update.' . $locale);

                // Import routes
                Route::get('/import', [ImportController::class, 'index'])->name('import.index.' . $locale);
                Route::post('/import/upload', [ImportController::class, 'upload'])->name('import.upload.' . $locale);
                Route::post('/import/execute', [ImportController::class, 'execute'])->name('import.execute.' . $locale);
                Route::post('/import/cancel', [ImportController::class, 'cancel'])->name('import.cancel.' . $locale);
                Route::get('/import/history', [ImportController::class, 'history'])->name('import.history.' . $locale);
                Route::get('/import/result/{importHistory}', [ImportController::class, 'result'])->name('import.result.' . $locale);
                Route::delete('/import/{importHistory}', [ImportController::class, 'destroy'])->name('import.destroy.' . $locale);

                // Reading Challenge routes
                Route::get('/challenge', [App\Http\Controllers\ReadingChallengeController::class, 'index'])->name('challenge.index.' . $locale);
                Route::post('/challenge', [App\Http\Controllers\ReadingChallengeController::class, 'store'])->name('challenge.store.' . $locale);
                Route::patch('/challenge/{challenge}', [App\Http\Controllers\ReadingChallengeController::class, 'update'])->name('challenge.update.' . $locale);
                Route::delete('/challenge/{challenge}', [App\Http\Controllers\ReadingChallengeController::class, 'destroy'])->name('challenge.destroy.' . $locale);

                // Family routes
                Route::get('/family', [FamilyController::class, 'index'])->name('family.index.' . $locale);
                Route::get('/family/create', [FamilyController::class, 'create'])->name('family.create.' . $locale);
                Route::post('/family', [FamilyController::class, 'store'])->name('family.store.' . $locale);
                Route::get('/family/join', [FamilyController::class, 'showJoinForm'])->name('family.join.' . $locale);
                Route::post('/family/join', [FamilyController::class, 'join'])->name('family.join.submit.' . $locale);
                Route::post('/family/leave', [FamilyController::class, 'leave'])->name('family.leave.' . $locale);
                Route::delete('/family', [FamilyController::class, 'destroy'])->name('family.destroy.' . $locale);
                Route::post('/family/regenerate-code', [FamilyController::class, 'regenerateCode'])->name('family.regenerate-code.' . $locale);
            });

            // Admin routes
            Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () use ($locale) {
                Route::get('/', [AdminController::class, 'index'])->name('index.' . $locale);
                Route::get('/users', [AdminController::class, 'users'])->name('users.' . $locale);
                Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit.' . $locale);
                Route::patch('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update.' . $locale);
                Route::patch('/users/{user}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('users.toggle-admin.' . $locale);
                Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete.' . $locale);

                Route::get('/settings', [AdminController::class, 'settings'])->name('settings.' . $locale);
                Route::patch('/settings', [AdminController::class, 'updateSettings'])->name('settings.update.' . $locale);
                Route::post('/settings/test-email', [AdminController::class, 'sendTestEmail'])->name('settings.test-email.' . $locale);

                Route::get('/email-logs', [AdminController::class, 'emailLogs'])->name('email-logs.' . $locale);
            });
        });
}

// Backward compatibility routes - redirect old URLs to new language-prefixed URLs
// These catch old non-prefixed URLs and redirect to the appropriate language version

// Old service pages - redirect to detected language version
Route::get('/impressum', function () {
    $locale = SetLocaleFromUrl::detectPreferredLocale(request());
    return redirect("/{$locale}/impressum", 301);
});
Route::get('/datenschutz', function () {
    $locale = SetLocaleFromUrl::detectPreferredLocale(request());
    return redirect("/{$locale}/datenschutz", 301);
});
Route::get('/kontakt', function () {
    $locale = SetLocaleFromUrl::detectPreferredLocale(request());
    return redirect("/{$locale}/kontakt", 301);
});
Route::get('/changelog', function () {
    $locale = SetLocaleFromUrl::detectPreferredLocale(request());
    return redirect("/{$locale}/changelog", 301);
});

// Old auth routes - redirect to detected language
Route::get('/login', function () {
    $locale = SetLocaleFromUrl::detectPreferredLocale(request());
    return redirect("/{$locale}/login", 301);
})->middleware('guest')->name('login');

Route::get('/register', function () {
    $locale = SetLocaleFromUrl::detectPreferredLocale(request());
    return redirect("/{$locale}/register", 301);
})->middleware('guest')->name('register');

// Old app routes - redirect to detected language for authenticated users
Route::get('/books', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    $locale = auth()->user()->preferred_language ?? 'en';
    return redirect("/{$locale}/books", 301);
})->middleware('auth')->name('books.index');

Route::get('/tags', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    $locale = auth()->user()->preferred_language ?? 'en';
    return redirect("/{$locale}/tags", 301);
})->middleware('auth');

Route::get('/settings', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    $locale = auth()->user()->preferred_language ?? 'en';
    return redirect("/{$locale}/settings", 301);
})->middleware('auth');

Route::get('/dashboard', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    $locale = auth()->user()->preferred_language ?? 'en';
    return redirect("/{$locale}/books", 301);
});
