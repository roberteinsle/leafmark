<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4F46E5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0;">📚 Leafmark</h1>
        <p style="margin: 10px 0 0 0;">SMTP Test Email</p>
    </div>
    
    <div style="background-color: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px;">
        <h2 style="color: #1f2937; margin-top: 0;">✅ SMTP Configuration Successful!</h2>
        
        <p>Congratulations! Your SMTP settings are working correctly.</p>
        
        <p>This test email confirms that:</p>
        <ul style="line-height: 2;">
            <li>✅ SMTP host connection is successful</li>
            <li>✅ Authentication credentials are valid</li>
            <li>✅ Email sending is configured properly</li>
        </ul>
        
        <div style="background-color: white; padding: 15px; border-left: 4px solid #4F46E5; margin: 20px 0;">
            <p style="margin: 0;"><strong>Sent at:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
        </div>
        
        <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">
            You received this email because you tested the SMTP configuration in the Leafmark admin panel.
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 20px; color: #9ca3af; font-size: 12px;">
        <p>Leafmark - Your Personal Book Tracking Application</p>
    </div>
</body>
</html>
