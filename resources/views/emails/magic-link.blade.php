<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('app.magic_link.email_subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4F46E5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0;">Leafmark</h1>
        <p style="margin: 10px 0 0 0;">{{ __('app.magic_link.email_subject') }}</p>
    </div>

    <div style="background-color: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px;">
        <p>{{ __('app.magic_link.email_intro') }}</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}"
               style="display: inline-block; padding: 12px 30px; background-color: #4F46E5; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">
                {{ __('app.magic_link.email_button') }}
            </a>
        </div>

        <p>{{ __('app.magic_link.email_expiry', ['minutes' => $minutes]) }}</p>

        <div style="background-color: white; padding: 15px; border-left: 4px solid #4F46E5; margin: 20px 0;">
            <p style="margin: 0; font-size: 12px; color: #6b7280; word-break: break-all;">{{ $url }}</p>
        </div>

        <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">{{ __('app.magic_link.email_ignore') }}</p>
    </div>
</body>
</html>
