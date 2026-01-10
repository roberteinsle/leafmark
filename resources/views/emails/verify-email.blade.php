<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('app.email_verification.email_subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4F46E5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0;">Leafmark</h1>
        <p style="margin: 10px 0 0 0;">{{ __('app.email_verification.email_subject') }}</p>
    </div>
    
    <div style="background-color: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px;">
        <h2 style="color: #1f2937; margin-top: 0;">{{ __('app.email_verification.email_greeting', ['name' => $user->name]) }}</h2>
        
        <p>{{ __('app.email_verification.email_intro') }}</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $verificationUrl }}" 
               style="display: inline-block; padding: 12px 30px; background-color: #4F46E5; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">
                {{ __('app.email_verification.email_button') }}
            </a>
        </div>
        
        <p>{{ __('app.email_verification.email_outro') }}</p>
        
        <div style="background-color: white; padding: 15px; border-left: 4px solid #4F46E5; margin: 20px 0;">
            <p style="margin: 0; font-size: 14px;"><strong>{{ __('app.email_verification.email_notice') }}</strong></p>
            <p style="margin: 10px 0 0 0; font-size: 12px; color: #6b7280; word-break: break-all;">
                {{ $verificationUrl }}
            </p>
        </div>
        
        <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">
            {{ __('app.email_verification.email_ignore') }}
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 20px; color: #9ca3af; font-size: 12px;">
        <p>Leafmark - {{ __('app.email_verification.email_footer') }}</p>
    </div>
</body>
</html>
