<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SystemSetting;
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

        return view('admin.settings', compact(
            'registrationEnabled',
            'registrationMode',
            'allowedDomains',
            'registrationCode'
        ));
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        // Registration settings
        $validated = $request->validate([
            'registration_enabled' => 'required|boolean',
            'registration_mode' => 'required|in:open,domain,code',
            'allowed_email_domains' => 'nullable|string',
            'registration_code' => 'nullable|string|max:255',
        ]);

        SystemSetting::set('registration_enabled', $validated['registration_enabled'] ? 'true' : 'false');
        SystemSetting::set('registration_mode', $validated['registration_mode']);
        SystemSetting::set('allowed_email_domains', $validated['allowed_email_domains'] ?? '');
        SystemSetting::set('registration_code', $validated['registration_code'] ?? '');

        return back()->with('success', 'Registration settings updated successfully.');
    }
}
