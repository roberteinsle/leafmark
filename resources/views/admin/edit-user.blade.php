@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                    Admin Dashboard
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <a href="{{ route('admin.users') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600">Users</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="ml-1 text-sm font-medium text-gray-500">{{ $user->name }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <h1 class="text-3xl font-bold text-gray-900 mb-8">Edit User: {{ $user->name }}</h1>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <p class="text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <ul class="list-disc list-inside text-red-800">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">User Information</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-medium">
                            @if($user->is_admin)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Admin</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">User</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Joined</p>
                        <p class="font-medium">{{ $user->created_at->format('d.m.Y H:i') }}</p>
                        <p class="text-xs text-gray-400">{{ $user->created_at->diffForHumans() }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email Status</p>
                        <p class="font-medium">
                            @if($user->email_verified_at)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Verified
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Not Verified
                            </span>
                            @endif
                        </p>
                        @if($user->email_verified_at)
                        <p class="text-xs text-gray-400">{{ $user->email_verified_at->format('d.m.Y H:i') }}</p>
                        @endif
                    </div>
                    @if($user->last_login_at)
                    <div>
                        <p class="text-sm text-gray-500">Last Login</p>
                        <p class="font-medium">{{ $user->last_login_at->format('d.m.Y H:i') }}</p>
                        <p class="text-xs text-gray-400">{{ $user->last_login_at->diffForHumans() }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-500">Language</p>
                        <p class="font-medium">{{ strtoupper($user->preferred_language) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Reading Statistics</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Total Books</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $user->books_count }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Currently Reading</span>
                            <span class="font-semibold text-gray-900">{{ $user->currently_reading_count }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Read</span>
                            <span class="font-semibold text-gray-900">{{ $user->read_count }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Want to Read</span>
                            <span class="font-semibold text-gray-900">{{ $user->want_to_read_count }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Events Section -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Email Events</h2>

                @if($recentEmailEvents->isEmpty())
                    <div class="text-center py-6">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No email events</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($recentEmailEvents as $event)
                            <div class="border border-gray-200 rounded-lg p-3">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $event->status === 'sent' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $event->status === 'sent' ? 'Sent' : 'Failed' }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $event->type }}</span>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $event->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm font-medium text-gray-900 mb-1">{{ $event->subject }}</p>
                                <p class="text-xs text-gray-500">To: {{ $event->recipient }}</p>
                                @if($event->status === 'failed' && $event->error_message)
                                    <p class="mt-2 text-xs text-red-600">Error: {{ Str::limit($event->error_message, 80) }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 text-center">
                        <a href="{{ route('admin.email-logs') }}?user={{ $user->id }}" class="text-sm text-blue-600 hover:text-blue-800">
                            View all email logs for this user →
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Edit User Details</h2>

                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" name="password" id="password"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <p class="mt-1 text-sm text-gray-500">Leave blank to keep current password</p>
                    </div>

                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>

                    @if($user->id !== auth()->id())
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_admin" value="1" {{ $user->is_admin ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Grant admin privileges</span>
                        </label>
                    </div>
                    @endif

                    <div class="flex items-center justify-between pt-4 border-t">
                        <a href="{{ route('admin.users') }}" class="text-gray-600 hover:text-gray-900">← Back to Users</a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-medium">Save Changes</button>
                    </div>
                </form>

                @if($user->id !== auth()->id())
                <div class="mt-8 border-t pt-6">
                    <h3 class="text-lg font-semibold text-red-900 mb-4">Danger Zone</h3>
                    
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-red-900">Delete this user</h4>
                                <p class="text-sm text-red-700 mt-1">Once you delete a user, there is no going back. All their books and data will be permanently deleted.</p>
                            </div>
                            <form method="POST" action="{{ route('admin.users.delete', $user) }}" class="ml-4" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone!')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium whitespace-nowrap">
                                    Delete User
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
