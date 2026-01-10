@extends('layouts.app')

@section('title', __('app.admin.email_logs'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('app.admin.email_logs') }}</h1>
        <p class="mt-1 text-sm text-gray-600">{{ __('app.admin.email_logs_description') }}</p>
    </div>

    @if($logs->isEmpty())
        <div class="bg-white shadow rounded-lg p-6 text-center text-gray-500">
            {{ __('app.admin.no_email_logs') }}
        </div>
    @else
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('app.admin.email_log_time') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('app.admin.email_log_status') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('app.admin.email_log_recipient') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('app.admin.email_log_subject') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('app.admin.email_log_user') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('app.admin.email_log_details') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($logs as $log)
                            <tr class="{{ $log->status === 'failed' ? 'bg-red-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                                    <div class="text-xs text-gray-500">
                                        {{ $log->created_at->diffForHumans() }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->status === 'sent')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ __('app.admin.email_log_sent') }}
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ __('app.admin.email_log_failed') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log->recipient }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $log->subject }}
                                    <div class="text-xs text-gray-500">
                                        {{ __('app.admin.email_log_type_' . $log->type) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($log->user)
                                        {{ $log->user->name }}
                                        <div class="text-xs">{{ $log->user->email }}</div>
                                    @else
                                        <span class="text-gray-400"></span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($log->status === 'failed' && $log->error_message)
                                        <details class="cursor-pointer">
                                            <summary class="text-red-600 hover:text-red-800 font-medium">
                                                {{ __('app.admin.email_log_show_error') }}
                                            </summary>
                                            <div class="mt-2 p-3 bg-red-50 rounded text-xs font-mono text-red-900 overflow-x-auto">
                                                <div class="font-semibold mb-2">{{ __('app.admin.email_log_error_message') }}:</div>
                                                <div class="mb-3">{{ $log->error_message }}</div>

                                                @if($log->smtp_config)
                                                    <div class="font-semibold mb-2 mt-4">{{ __('app.admin.email_log_smtp_config') }}:</div>
                                                    <pre class="whitespace-pre-wrap">{{ json_encode($log->smtp_config, JSON_PRETTY_PRINT) }}</pre>
                                                @endif

                                                @if($log->stack_trace)
                                                    <div class="font-semibold mb-2 mt-4">{{ __('app.admin.email_log_stack_trace') }}:</div>
                                                    <pre class="whitespace-pre-wrap text-xs">{{ $log->stack_trace }}</pre>
                                                @endif
                                            </div>
                                        </details>
                                    @elseif($log->smtp_config)
                                        <details class="cursor-pointer">
                                            <summary class="text-gray-600 hover:text-gray-800">
                                                {{ __('app.admin.email_log_show_config') }}
                                            </summary>
                                            <div class="mt-2 p-3 bg-gray-50 rounded text-xs font-mono overflow-x-auto">
                                                <pre class="whitespace-pre-wrap">{{ json_encode($log->smtp_config, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        </details>
                                    @else
                                        <span class="text-gray-400"></span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $logs->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
