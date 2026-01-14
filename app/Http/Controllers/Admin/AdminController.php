<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SystemSetting;
use App\Models\Invitation;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

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

        $smtpEnabled = SystemSetting::get('smtp_enabled', 'false') === 'true';
        $smtpHost = SystemSetting::get('smtp_host', '');
        $smtpPort = SystemSetting::get('smtp_port', '587');
        $smtpEncryption = SystemSetting::get('smtp_encryption', 'tls');
        $smtpUsername = SystemSetting::get('smtp_username', '');
        $smtpPassword = SystemSetting::get('smtp_password', '');
        $smtpFromAddress = SystemSetting::get('smtp_from_address', '');
        $smtpFromName = SystemSetting::get('smtp_from_name', 'Leafmark');

        $turnstileEnabled = SystemSetting::get('turnstile_enabled', 'false') === 'true';
        $turnstileSiteKey = SystemSetting::get('turnstile_site_key', '');
        $turnstileSecretKey = SystemSetting::get('turnstile_secret_key', '');

        $googleBooksApiKey = SystemSetting::get('google_books_api_key', '');

        $invitations = Invitation::with('invitedBy')->latest()->paginate(20);

        return view('admin.settings', compact(
            'registrationEnabled',
            'registrationMode',
            'allowedDomains',
            'registrationCode',
            'smtpEnabled',
            'smtpHost',
            'smtpPort',
            'smtpEncryption',
            'smtpUsername',
            'smtpPassword',
            'smtpFromAddress',
            'smtpFromName',
            'turnstileEnabled',
            'turnstileSiteKey',
            'turnstileSecretKey',
            'googleBooksApiKey',
            'invitations'
        ));
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $section = $request->input('section');

        if ($section === 'smtp') {
            $validated = $request->validate([
                'smtp_enabled' => 'nullable|boolean',
                'smtp_host' => 'nullable|string|max:255',
                'smtp_port' => 'nullable|integer|min:1|max:65535',
                'smtp_encryption' => 'nullable|string|in:tls,ssl,',
                'smtp_username' => 'nullable|string|max:255',
                'smtp_password' => 'nullable|string|max:255',
                'smtp_from_address' => 'nullable|email|max:255',
                'smtp_from_name' => 'nullable|string|max:255',
            ]);

            SystemSetting::set('smtp_enabled', $request->has('smtp_enabled') ? 'true' : 'false');
            SystemSetting::set('smtp_host', $validated['smtp_host'] ?? '');
            SystemSetting::set('smtp_port', $validated['smtp_port'] ?? '587');
            SystemSetting::set('smtp_encryption', $validated['smtp_encryption'] ?? 'tls');
            SystemSetting::set('smtp_username', $validated['smtp_username'] ?? '');
            SystemSetting::set('smtp_password', $validated['smtp_password'] ?? '');
            SystemSetting::set('smtp_from_address', $validated['smtp_from_address'] ?? '');
            SystemSetting::set('smtp_from_name', $validated['smtp_from_name'] ?? 'Leafmark');

            return back()->with('success', 'SMTP settings updated successfully.');
        }

        if ($section === 'turnstile') {
            $validated = $request->validate([
                'turnstile_enabled' => 'nullable|boolean',
                'turnstile_site_key' => 'nullable|string|max:255',
                'turnstile_secret_key' => 'nullable|string|max:255',
            ]);

            SystemSetting::set('turnstile_enabled', $request->has('turnstile_enabled') ? 'true' : 'false');
            SystemSetting::set('turnstile_site_key', $validated['turnstile_site_key'] ?? '');
            SystemSetting::set('turnstile_secret_key', $validated['turnstile_secret_key'] ?? '');

            return back()->with('success', 'Turnstile settings updated successfully.');
        }

        if ($section === 'api') {
            $validated = $request->validate([
                'google_books_api_key' => 'nullable|string|max:255',
            ]);

            SystemSetting::set('google_books_api_key', $validated['google_books_api_key'] ?? '');

            return back()->with('success', 'API settings updated successfully.');
        }

        // Default: registration settings
        $validated = $request->validate([
            'registration_enabled' => 'required|boolean',
            'registration_mode' => 'required|in:open,domain,invitation,code',
            'allowed_email_domains' => 'nullable|string',
            'registration_code' => 'nullable|string|max:255',
        ]);

        SystemSetting::set('registration_enabled', $validated['registration_enabled'] ? 'true' : 'false');
        SystemSetting::set('registration_mode', $validated['registration_mode']);
        SystemSetting::set('allowed_email_domains', $validated['allowed_email_domains'] ?? '');
        SystemSetting::set('registration_code', $validated['registration_code'] ?? '');

        return back()->with('success', 'Registration settings updated successfully.');
    }

    /**
     * Show invitations management page
     */
    public function invitations(): View
    {
        $invitations = Invitation::with('invitedBy')->latest()->paginate(20);

        return view('admin.invitations', compact('invitations'));
    }

    /**
     * Create an invitation
     */
    public function createInvitation(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|unique:users,email|unique:invitations,email',
            ]);

            Invitation::create([
                'email' => $validated['email'],
                'invited_by' => auth()->id(),
            ]);

            return back()->with('success', 'Invitation created successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to create invitation', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Failed to create invitation: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete an invitation
     */
    public function deleteInvitation(Invitation $invitation): RedirectResponse
    {
        $invitation->delete();

        return back()->with('success', 'Invitation deleted successfully.');
    }

    /**
     * Send test email to specified recipient
     */
    public function sendTestEmail(Request $request): RedirectResponse
    {
        // Validate recipient email
        $validated = $request->validate([
            'test_email' => 'required|email',
        ]);

        $recipient = $validated['test_email'];
        $subject = 'Leafmark SMTP Test Email';

        // Check if SMTP is enabled
        if (!SystemSetting::isSmtpEnabled()) {
            \App\Models\EmailLog::logFailure(
                $recipient,
                $subject,
                'SMTP is not enabled',
                'test',
                auth()->id(),
                null,
                null
            );
            return back()->withErrors(['error' => 'SMTP is not enabled. Please enable and configure SMTP settings first.']);
        }

        // Check if required SMTP settings are configured
        $smtp = SystemSetting::getSmtpConfig();
        if (empty($smtp['host']) || empty($smtp['from_address'])) {
            \App\Models\EmailLog::logFailure(
                $recipient,
                $subject,
                'SMTP settings incomplete: missing host or from_address',
                'test',
                auth()->id(),
                $smtp,
                null
            );
            return back()->withErrors(['error' => 'SMTP settings are incomplete. Please configure SMTP host and from address.']);
        }

        // Log detailed SMTP configuration (sanitize password)
        $smtpConfigForLog = $smtp;
        $smtpConfigForLog['password'] = $smtp['password'] ? '***HIDDEN***' : '';

        \Log::info('Attempting to send test email', [
            'recipient' => $recipient,
            'smtp_host' => $smtp['host'],
            'smtp_port' => $smtp['port'],
            'smtp_encryption' => $smtp['encryption'],
            'smtp_username' => $smtp['username'],
            'smtp_from' => $smtp['from_address'],
        ]);

        try {
            \Mail::to($recipient)->send(new \App\Mail\TestEmail());

            // Log success
            \App\Models\EmailLog::logSuccess(
                $recipient,
                $subject,
                'test',
                auth()->id(),
                $smtpConfigForLog
            );

            \Log::info('Test email sent successfully', [
                'recipient' => $recipient,
            ]);

            return back()->with('success', 'Test email sent successfully to ' . $recipient);
        } catch (\Exception $e) {
            // Log detailed error
            $errorMessage = $e->getMessage();
            $stackTrace = $e->getTraceAsString();

            \Log::error('Test email failed', [
                'recipient' => $recipient,
                'error' => $errorMessage,
                'exception_class' => get_class($e),
                'smtp_host' => $smtp['host'],
                'smtp_port' => $smtp['port'],
                'stack_trace' => $stackTrace,
            ]);

            // Log to database
            \App\Models\EmailLog::logFailure(
                $recipient,
                $subject,
                $errorMessage,
                'test',
                auth()->id(),
                $smtpConfigForLog,
                $stackTrace
            );

            return back()->withErrors(['error' => 'Failed to send test email: ' . $errorMessage]);
        }
    }

    /**
     * Show email logs
     */
    public function emailLogs(): View
    {
        $logs = EmailLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.email-logs', compact('logs'));
    }
}
