{{-- QR-Code und manueller Schlüssel für die Authenticator-App --}}
<div class="flex flex-col items-center gap-3">
    <div class="p-3 bg-white border border-gray-200 rounded-lg" aria-label="{{ __('app.two_factor.qr_label') }}" role="img">
        {!! $qrCode !!}
    </div>
    <div class="text-center">
        <p class="text-xs text-gray-500">{{ __('app.two_factor.manual_key') }}</p>
        <code class="text-sm font-mono tracking-wider break-all select-all">{{ trim(chunk_split($secret, 4, ' ')) }}</code>
    </div>
</div>
