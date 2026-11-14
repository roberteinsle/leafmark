<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Services\GoogleBooksService;

class AdminController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function index(): View
    {
        $totalUsers = User::count();
        $totalAdmins = User::where('is_admin', true)->count();
        $totalBooks = \App\Models\Book::count();
        $recentUsers = User::latest()->limit(10)->get();

        return view('admin.index', compact('totalUsers', 'totalAdmins', 'totalBooks', 'recentUsers'));
    }

    /**
     * Show users management page
     */
    public function users(): View
    {
        $users = User::withCount('books')->latest()->paginate(50);

        return view('admin.users', compact('users'));
    }

    /**
     * Show edit user page
     */
    public function editUser(User $user): View
    {
        $user->loadCount([
            'books',
            'books as currently_reading_count' => function ($query) {
                $query->where('status', 'currently_reading');
            },
            'books as read_count' => function ($query) {
                $query->where('status', 'read');
            },
            'books as want_to_read_count' => function ($query) {
                $query->where('status', 'want_to_read');
            }
        ]);

        return view('admin.edit-user', compact('user'));
    }

    /**
     * Update user information
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        // Validate input
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'unique:users,email,' . $user->id . ',id'
            ],
            'password' => 'nullable|string|min:8|confirmed',
        ];

        $validated = $request->validate($rules);

        // Prepare data for update
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        // Update password if provided
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        // Only allow changing admin status if not changing own account
        if ($user->id !== auth()->id()) {
            $data['is_admin'] = $request->has('is_admin') ? true : false;
        }

        $user->update($data);

        return redirect()->route('admin.users.edit', $user)->with('success', 'User updated successfully.');
    }

    /**
     * Toggle admin status for a user
     */
    public function toggleAdmin(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot change your own admin status.']);
        }

        $user->update(['is_admin' => !$user->is_admin]);

        return back()->with('success', 'User admin status updated successfully.');
    }

    /**
     * 2FA eines Nutzers zurücksetzen (Gerät und Wiederherstellungscodes verloren).
     * Beim nächsten Passwort-Login muss der Nutzer 2FA neu einrichten.
     */
    public function resetTwoFactor(User $user, \App\Services\TwoFactorService $twoFactor): RedirectResponse
    {
        $twoFactor->disable($user);

        return back()->with('success', __('app.two_factor.admin_reset_done'));
    }

    /**
     * Delete a user
     */
    public function deleteUser(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }

    /**
     * Show system settings page
     */
    public function settings(): View
    {
        $registrationEnabled = SystemSetting::get('registration_enabled', 'true') === 'true';
        $registrationMode = SystemSetting::get('registration_mode', 'open');
        $allowedDomains = SystemSetting::get('allowed_email_domains', '');
        $registrationCode = SystemSetting::get('registration_code', '');

        $smtp = SystemSetting::getSmtpConfig();
        $smtpEnabled = SystemSetting::isSmtpEnabled();
        $hasSmtpPassword = (bool) SystemSetting::getSecret('smtp_password');
        $hasGoogleKey = (bool) SystemSetting::getSecret('google_books_api_key');
        $googleKeyFromEnv = ! $hasGoogleKey && (bool) config('services.google_books.api_key');
        $hasBigBookKey = (bool) SystemSetting::getSecret('bigbook_api_key');

        return view('admin.settings', compact(
            'registrationEnabled',
            'registrationMode',
            'allowedDomains',
            'registrationCode',
            'smtp',
            'smtpEnabled',
            'hasSmtpPassword',
            'hasGoogleKey',
            'googleKeyFromEnv',
            'hasBigBookKey'
        ));
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        // Registration settings
        $validated = $request->validate([
            'registration_enabled' => 'nullable|boolean',
            'registration_mode' => 'required|in:open,domain,code',
            'allowed_email_domains' => 'nullable|string|max:1000',
            'registration_code' => 'nullable|string|max:255',
        ]);

        // Fehlt das Feld (Checkbox nicht angehakt), ist die Registrierung aus
        SystemSetting::set('registration_enabled', $request->boolean('registration_enabled') ? 'true' : 'false');
        SystemSetting::set('registration_mode', $validated['registration_mode']);
        SystemSetting::set('allowed_email_domains', $validated['allowed_email_domains'] ?? '');
        SystemSetting::set('registration_code', $validated['registration_code'] ?? '');

        return redirect()->route('admin.settings')->with('success', __('app.admin.registration_saved'));
    }

    /**
     * E-Mail-Versand (SMTP). Das Passwort wird verschlüsselt gespeichert; ein leeres Feld behält es.
     */
    public function updateMail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'smtp_enabled' => ['nullable', 'boolean'],
            'smtp_host' => ['required_if_accepted:smtp_enabled', 'nullable', 'string', 'max:255'],
            'smtp_port' => ['required_if_accepted:smtp_enabled', 'nullable', 'integer', 'between:1,65535'],
            'smtp_encryption' => ['required', 'in:tls,ssl,none'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_from_address' => ['required_if_accepted:smtp_enabled', 'nullable', 'email', 'max:255'],
            'smtp_from_name' => ['nullable', 'string', 'max:255'],
        ]);

        SystemSetting::set('smtp_enabled', $request->boolean('smtp_enabled') ? 'true' : 'false');
        foreach (['smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_from_address', 'smtp_from_name'] as $key) {
            SystemSetting::set($key, (string) ($validated[$key] ?? ''));
        }
        if ($request->boolean('smtp_password_clear')) {
            SystemSetting::setSecret('smtp_password', null);
        } elseif (filled($validated['smtp_password'] ?? null)) {
            SystemSetting::setSecret('smtp_password', $validated['smtp_password']);
        }

        return redirect()->route('admin.settings')->with('success', __('app.admin.mail_saved'));
    }

    /**
     * Testmail an die Adresse des angemeldeten Admins (mit den gespeicherten Einstellungen).
     */
    public function testMail(Request $request): RedirectResponse
    {
        if (! SystemSetting::isSmtpEnabled()) {
            return redirect()->route('admin.settings')->withErrors(['smtp' => __('app.admin.mail_test_disabled')]);
        }

        try {
            Mail::raw(__('app.admin.mail_test_body'), function ($message) use ($request) {
                $message->to($request->user()->email)->subject(__('app.admin.mail_test_subject'));
            });
        } catch (\Throwable $e) {
            return redirect()->route('admin.settings')->withErrors(['smtp' => __('app.admin.mail_test_failed', ['error' => $e->getMessage()])]);
        }

        return redirect()->route('admin.settings')->with('success', __('app.admin.mail_test_sent', ['email' => $request->user()->email]));
    }

    /**
     * API-Schlüssel der Buchquellen. Verschlüsselt gespeichert; ein leeres Feld behält den Schlüssel.
     */
    public function updateApiKeys(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'google_books_api_key' => ['nullable', 'string', 'max:255'],
            'bigbook_api_key' => ['nullable', 'string', 'max:255'],
        ]);

        foreach (['google_books_api_key', 'bigbook_api_key'] as $key) {
            if ($request->boolean($key.'_clear')) {
                SystemSetting::setSecret($key, null);
            } elseif (filled($validated[$key] ?? null)) {
                SystemSetting::setSecret($key, trim($validated[$key]));
            }
        }

        return redirect()->route('admin.settings')->with('success', __('app.admin.api_keys_saved'));
    }

    /**
     * Prüft den Google-Books-Schlüssel mit einer echten Anfrage und zeigt Googles Antwort.
     */
    public function testGoogleBooks(GoogleBooksService $googleBooks): RedirectResponse
    {
        $result = $googleBooks->testConnection();

        return $result['ok']
            ? redirect()->route('admin.settings')->with('success', __('app.admin.google_test_ok'))
            : redirect()->route('admin.settings')->withErrors([
                'google' => __('app.admin.google_test_failed', ['status' => $result['status'] ?? '-', 'message' => $result['message']]),
            ]);
    }
}
